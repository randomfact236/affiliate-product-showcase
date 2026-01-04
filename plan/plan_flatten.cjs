#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

// ============================================
// CONFIGURATION
// ============================================
const root = path.join(__dirname, '..');
const inArg = process.argv[2];
const outArg = process.argv[3];
const inPath = inArg ? path.resolve(inArg) : path.join(root, 'plan', 'plan_sync.md');
let outPath;
if (outArg) {
  outPath = path.resolve(outArg);
} else if (inArg) {
  outPath = path.join(path.dirname(inPath), path.basename(inPath, path.extname(inPath)) + '_flat.md');
} else {
  outPath = path.join(root, 'plan', 'plan_sync_root_copy.md');
}

if (!fs.existsSync(inPath)) {
  console.error('❌ Input file not found:', inPath);
  process.exit(1);
}

console.log('📖 Reading:', inPath);

// ============================================
// HELPERS
// ============================================

function extractCode(line) {
  // Anchor to start so we only match leading numbering like 1.2 or 1.2.3
  const match = line.match(/^\s*(\d+(?:\.\d+)*)(?:\b|[^\d])/);
  return match ? match[1] : null;
}

function getLevel(code) {
  return code.split('.').length;
}

function getParentCode(code) {
  const parts = code.split('.');
  return parts.slice(0, -1).join('.');
}

function isStepHeader(line) {
  return /^#\s+Step\s+\d+/i.test(line);
}

function isTopicHeader(line) {
  // Accept any heading level >= 2 that starts with a numeric topic code like "8.2"
  return /^#{2,}\s+\d+\.\d+\s+/i.test(line);
}

// ============================================
// READ AND PARSE
// ============================================
const txt = fs.readFileSync(inPath, 'utf8');
const allLines = txt.split(/\r?\n/);

let headerEnd = 0;
for (let i = 0; i < allLines.length; i++) {
  if (isStepHeader(allLines[i])) {
    headerEnd = i;
    break;
  }
}

const headerLines = allLines.slice(0, headerEnd);
const contentLines = allLines.slice(headerEnd);

console.log(`📄 Header: ${headerLines.length} lines`);
console.log(`📊 Content: ${contentLines.length} lines`);

// ============================================
// PARSE WITH NESTED SUPPORT
// ============================================
console.log('🔍 Parsing hierarchy...');

const structure = { steps: [] };
let currentStep = null;
let currentTopic = null;
const itemRegistry = new Map(); // Track all items by code for parent lookups
const warnings = [];

for (let i = 0; i < contentLines.length; i++) {
  const line = contentLines[i];
  const trimmed = line.trim();
  if (!trimmed) continue;

  // STEP HEADER: # Step 1 — Title
  if (isStepHeader(line)) {
    const stepMatch = line.match(/^#\s+Step\s+(\d+)\s+[——-]\s+(.*)$/i);
    if (stepMatch) {
      currentStep = { 
        code: stepMatch[1], 
        title: stepMatch[2].trim(), 
        line: line.trim(), 
        topics: [] 
      };
      structure.steps.push(currentStep);
      itemRegistry.set(currentStep.code, currentStep);
      currentTopic = null;
      console.log(`  ✓ Step ${currentStep.code}: ${currentStep.title}`);
    } else {
      // Fallback: capture step number even without dash
      const fallback = line.match(/^#\s+Step\s+(\d+)\s+(.*)$/i);
      if (fallback) {
        currentStep = { 
          code: fallback[1], 
          title: fallback[2].trim(), 
          line: line.trim(), 
          topics: [] 
        };
        structure.steps.push(currentStep);
        itemRegistry.set(currentStep.code, currentStep);
        currentTopic = null;
      }
    }
    continue;
  }

  // TOPIC HEADER: ## / ### / #### 1.1 Topic Title
  if (isTopicHeader(line)) {
    const topicMatch = line.match(/^#{2,}\s+(\d+\.\d+)\s+(.*)$/i);
    if (topicMatch) {
      const topicCode = topicMatch[1];
      const topicTitle = topicMatch[2].trim();
      const stepNum = topicCode.split('.')[0];
      
      if (!currentStep || currentStep.code !== stepNum) {
        warnings.push(`Line ${headerEnd + i + 1}: Topic ${topicCode} outside its step`);
        if (!currentStep && structure.steps.length) {
          currentStep = structure.steps[structure.steps.length - 1];
        }
      }
      
      currentTopic = { 
        code: topicCode, 
        title: topicTitle, 
        line: `## ${topicCode} ${topicTitle}`, 
        items: [] // Changed from 'subtopics' to 'items' for nested support
      };
      
      if (currentStep) {
        currentStep.topics.push(currentTopic);
        itemRegistry.set(topicCode, currentTopic);
      }
    }
    continue;
  }

  // NESTED ITEMS: Any line with numeric code at level 3+
  const code = extractCode(trimmed);
  if (code) {
    const level = getLevel(code);
    
    // Only process level 3+ (subtopics and deeper nesting)
    if (level >= 3) {
      const parentCode = getParentCode(code);
      let parent = itemRegistry.get(parentCode);
      
      if (!parent) {
        // Attempt to recover: create missing parent chain up to the topic level (level 2)
        const parts = code.split('.');
        const parentParts = parentCode.split('.');

        // Find nearest existing ancestor (any registered code like 8.2 or 8.2.1)
        let ancestor = null;
        for (let j = parts.length - 1; j >= 1; j--) {
          const pc = parts.slice(0, j).join('.');
          if (itemRegistry.has(pc)) {
            ancestor = itemRegistry.get(pc);
            break;
          }
        }

        // If no ancestor found, ensure the top-level topic (level 2) exists under the current step
        if (!ancestor) {
          if (parts.length >= 2) {
            const topCode = parts.slice(0, 2).join('.');
            if (!itemRegistry.has(topCode)) {
              const topicNode = {
                code: topCode,
                title: '(generated)',
                line: `## ${topCode} (generated)`,
                items: []
              };
              if (currentStep) {
                currentStep.topics.push(topicNode);
              }
              itemRegistry.set(topCode, topicNode);
            }
            ancestor = itemRegistry.get(parts.slice(0, 2).join('.'));
          } else {
            ancestor = currentTopic || currentStep;
          }
        }

        // Create any intermediate nodes from ancestor up to the expected parentCode
        const ancestorLevel = (ancestor && ancestor.code && ancestor.code.indexOf('.') >= 0) ? ancestor.code.split('.').length : (ancestor && ancestor.code ? 1 : 1);
        const targetLevel = parentParts.length;

        for (let lvl = ancestorLevel + 1; lvl <= targetLevel; lvl++) {
          const nodeCode = parts.slice(0, lvl).join('.');
          if (!itemRegistry.has(nodeCode)) {
            if (lvl === 2) {
              // create a topic under the current step
              const topicNode = {
                code: nodeCode,
                title: '(generated)',
                line: `## ${nodeCode} (generated)`,
                items: []
              };
              if (currentStep) {
                currentStep.topics.push(topicNode);
              }
              itemRegistry.set(nodeCode, topicNode);
            } else {
              // create an intermediate item and attach to its parent
              const parentOfNode = itemRegistry.get(getParentCode(nodeCode)) || ancestor || currentTopic || currentStep;
              const node = {
                code: nodeCode,
                level: lvl,
                line: `${nodeCode} (generated)`,
                items: []
              };
              if (!parentOfNode.items) parentOfNode.items = [];
              parentOfNode.items.push(node);
              itemRegistry.set(nodeCode, node);
            }
          }
        }

        // Now parent should exist
        parent = itemRegistry.get(parentCode) || itemRegistry.get(parts.slice(0, targetLevel).join('.'));
        warnings.push(`Line ${headerEnd + i + 1}: Item ${code} had missing parent; created ${parentCode} (generated)`);
      }
      
      // Create item with children array for potential nesting
      const item = {
        code: code,
        level: level,
        line: trimmed,
        items: [] // Support infinite nesting
      };
      
      // Add to parent's items array
      if (!parent.items) parent.items = [];
      parent.items.push(item);
      
      // Register for future parent lookups
      itemRegistry.set(code, item);
    }
  }
}

// ============================================
// SORT + DEDUP (RECURSIVE)
// ============================================
console.log('🔢 Sorting and deduplicating...');

function sortAndDedupeItems(items) {
  if (!items || items.length === 0) return items;
  
  // Sort by last number in code (e.g., 1.1.3.2 → sort by 2)
  items.sort((a, b) => {
    const aNum = parseInt(a.code.split('.').pop(), 10);
    const bNum = parseInt(b.code.split('.').pop(), 10);
    return aNum - bNum;
  });

  // Remove duplicates
  const seen = new Set();
  const unique = [];
  
  for (const item of items) {
    if (!seen.has(item.code)) {
      unique.push(item);
      seen.add(item.code);
      
      // Recursively process nested items
      if (item.items && item.items.length > 0) {
        item.items = sortAndDedupeItems(item.items);
      }
    }
  }
  
  return unique;
}

for (const step of structure.steps) {
  // Sort topics by number
  step.topics.sort((a, b) => {
    const aNum = parseInt(a.code.split('.')[1], 10);
    const bNum = parseInt(b.code.split('.')[1], 10);
    return aNum - bNum;
  });

  // Sort and deduplicate all nested items under each topic
  for (const topic of step.topics) {
    topic.items = sortAndDedupeItems(topic.items);
  }
}

// ============================================
// BUILD OUTPUT WITH COLLAPSE/EXPAND MARKERS
// ============================================
console.log('📝 Building output...');

const output = [];

function outputItems(items, baseIndent = 2, parentLevel = 2) {
  if (!items || items.length === 0) return;
  
  for (const item of items) {
    // Check if this item has nested children
    const hasChildren = item.items && item.items.length > 0;
    
    if (hasChildren) {
      // Extract the title from the line (everything after the code)
      const lineMatch = item.line.match(/^\s*\d+(?:\.\d+)*\s+(.*)$/);
      const title = lineMatch ? lineMatch[1] : item.line;
      
      // Render items with children as sub-topic headers (###, ####, etc.)
      // Level 3 → ###, Level 4 → ####, Level 5 → #####
      const headingLevel = '#'.repeat(item.level);
      
      // Output as a heading instead of indented text (no details/summary tags)
      output.push(`${headingLevel} ${item.code} ${title}`);
      
      // Recursively output nested items
      outputItems(item.items, baseIndent, item.level);
      
      output.push('');
    } else {
      // No children - render as regular indented item
      const tabCount = (item.level - 1) * 2;
      const indent = '\t'.repeat(tabCount);
      output.push(`${indent}${item.line}`);
    }
  }
}

// Add header
output.push(...headerLines);
output.push('');

// Add content
for (const step of structure.steps) {
  output.push(`# Step ${step.code} — ${step.title}`);
  output.push('');
  
  for (const topic of step.topics) {
    // Output topic header (no collapse markers)
    output.push(topic.line);
    
    // Output all items recursively
    outputItems(topic.items, 2, 2);
    
    output.push('');
  }
}

const finalOutput = output.join('\n').replace(/\n{3,}/g, '\n\n');
fs.writeFileSync(outPath, finalOutput, 'utf8');

// ============================================
// STATS + WARNINGS (RECURSIVE COUNTING)
// ============================================

function countItems(items) {
  const counts = { total: 0, byLevel: {} };
  
  if (!items) return counts;
  
  for (const item of items) {
    counts.total++;
    counts.byLevel[item.level] = (counts.byLevel[item.level] || 0) + 1;
    
    if (item.items && item.items.length > 0) {
      const nested = countItems(item.items);
      counts.total += nested.total;
      
      for (const [level, count] of Object.entries(nested.byLevel)) {
        counts.byLevel[level] = (counts.byLevel[level] || 0) + count;
      }
    }
  }
  
  return counts;
}

console.log('');
console.log('✅ Output written to:', outPath);
console.log('');
console.log('📊 Statistics:');
console.log(`   Steps: ${structure.steps.length}`);

let totalTopics = 0;
let allItemCounts = { total: 0, byLevel: {} };

for (const step of structure.steps) {
  totalTopics += step.topics.length;
  
  for (const topic of step.topics) {
    const counts = countItems(topic.items);
    allItemCounts.total += counts.total;
    
    for (const [level, count] of Object.entries(counts.byLevel)) {
      allItemCounts.byLevel[level] = (allItemCounts.byLevel[level] || 0) + count;
    }
  }
}

console.log(`   Topics: ${totalTopics}`);
console.log(`   Total Items: ${allItemCounts.total}`);

// Show breakdown by level
const sortedLevels = Object.keys(allItemCounts.byLevel).sort();
for (const level of sortedLevels) {
  const levelName = level === '3' ? 'Subtopics (Level 3)' : `Nested Level ${level}`;
  console.log(`     - ${levelName}: ${allItemCounts.byLevel[level]}`);
}

console.log('');

if (warnings.length) {
  console.log('⚠️  Warnings:');
  warnings.slice(0, 10).forEach(w => console.log('   ' + w));
  if (warnings.length > 10) {
    console.log(`   ... and ${warnings.length - 10} more`);
  }
} else {
  console.log('✅ No hierarchy warnings');
}

console.log('');
console.log('✨ Done!');
