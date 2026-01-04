#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

// ============================================
// CONFIGURATION
// ============================================
const root = path.join(__dirname, '..');
const inArg = process.argv[2];
const inPath = inArg ? path.resolve(inArg) : path.join(root, 'plan_workflow', 'plan_workflow_sync.md');
const outPath = inArg ? path.join(path.dirname(inPath), path.basename(inPath, path.extname(inPath)) + '_flat.md') : path.join(root, 'plan_workflow', 'plan_workflow_sync_root_copy.md');

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

function isStepHeader(line) {
  return /^#\s+Step\s+\d+/i.test(line);
}

function isTopicHeader(line) {
  return /^##\s+\d+\.\d+\s+/i.test(line);
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
// PARSE
// ============================================
console.log('🔍 Parsing hierarchy...');

const structure = { steps: [] };
let currentStep = null;
let currentTopic = null;
const warnings = [];

for (let i = 0; i < contentLines.length; i++) {
  const line = contentLines[i];
  const trimmed = line.trim();
  if (!trimmed) continue;

  if (isStepHeader(line)) {
    const stepMatch = line.match(/^#\s+Step\s+(\d+)\s+[—–-]\s+(.*)$/i);
    if (stepMatch) {
      currentStep = { code: stepMatch[1], title: stepMatch[2].trim(), line: line.trim(), topics: [] };
      structure.steps.push(currentStep);
      currentTopic = null;
      console.log(`  ✓ Step ${currentStep.code}: ${currentStep.title}`);
    } else {
      // Fallback: capture step number even without dash
      const fallback = line.match(/^#\s+Step\s+(\d+)\s+(.*)$/i);
      if (fallback) {
        currentStep = { code: fallback[1], title: fallback[2].trim(), line: line.trim(), topics: [] };
        structure.steps.push(currentStep);
        currentTopic = null;
      }
    }
    continue;
  }

  if (isTopicHeader(line)) {
    const topicMatch = line.match(/^##\s+(\d+\.\d+)\s+(.*)$/i);
    if (topicMatch) {
      const topicCode = topicMatch[1];
      const topicTitle = topicMatch[2].trim();
      const stepNum = topicCode.split('.')[0];
      if (!currentStep || currentStep.code !== stepNum) {
        warnings.push(`Line ${headerEnd + i + 1}: Topic ${topicCode} outside its step`);
        // still attach to last step if exists
        if (!currentStep && structure.steps.length) currentStep = structure.steps[structure.steps.length - 1];
      }
      currentTopic = { code: topicCode, title: topicTitle, line: `## ${topicCode} ${topicTitle}`, subtopics: [] };
      if (currentStep) currentStep.topics.push(currentTopic);
    }
    continue;
  }

  // Subtopic detection: leading numbering like 1.1.1
  const code = extractCode(trimmed);
  if (code && getLevel(code) === 3) {
    const parentCode = code.split('.').slice(0, 2).join('.');
    if (!currentTopic || currentTopic.code !== parentCode) {
      warnings.push(`Line ${headerEnd + i + 1}: Subtopic ${code} outside its topic (expected ${currentTopic?.code})`);
      continue;
    }
    // keep the original trimmed line (may include title)
    currentTopic.subtopics.push({ code: code, line: trimmed });
  }
}

// ============================================
// SORT + DEDUP
// ============================================
console.log('🔢 Sorting...');
for (const step of structure.steps) {
  step.topics.sort((a, b) => parseInt(a.code.split('.')[1], 10) - parseInt(b.code.split('.')[1], 10));
  for (const topic of step.topics) {
    topic.subtopics.sort((a, b) => parseInt(a.code.split('.')[2], 10) - parseInt(b.code.split('.')[2], 10));
    const seen = new Set();
    topic.subtopics = topic.subtopics.filter(s => {
      if (seen.has(s.code)) return false; seen.add(s.code); return true;
    });
  }
}

// ============================================
// BUILD OUTPUT
// ============================================
console.log('📝 Building output...');
const output = [];
output.push(...headerLines);
output.push('');

for (const step of structure.steps) {
  output.push(`# Step ${step.code} — ${step.title}`);
  output.push('');
  for (const topic of step.topics) {
    output.push(`## ${topic.code} ${topic.title}`);
    for (const sub of topic.subtopics) {
      output.push(`		${sub.line}`);
    }
    output.push('');
  }
}

const finalOutput = output.join('\n').replace(/\n{4,}/g, '\n\n\n');
fs.writeFileSync(outPath, finalOutput, 'utf8');

// STATS + WARNINGS
console.log('');
console.log('✅ Output written to:', outPath);
console.log('');
console.log('📊 Statistics:');
console.log(`   Steps: ${structure.steps.length}`);
let totalTopics = 0; let totalSubtopics = 0;
for (const step of structure.steps) {
  totalTopics += step.topics.length;
  for (const topic of step.topics) totalSubtopics += topic.subtopics.length;
}
console.log(`   Topics: ${totalTopics}`);
console.log(`   Subtopics: ${totalSubtopics}`);
console.log('');
if (warnings.length) {
  console.log('⚠️  Warnings:');
  warnings.forEach(w => console.log('   ' + w));
} else {
  console.log('✅ No hierarchy warnings');
}
console.log('');
console.log('✨ Done!');
