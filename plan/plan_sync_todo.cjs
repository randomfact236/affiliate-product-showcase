// plan/plan_sync_todos.cjs
const fs = require('fs');

// ============================================================================
// PARSER: Extract items from plan_source.md
// ============================================================================

function parseSource(sourceText) {
  const lines = sourceText.split('\n');
  const items = [];

  for (let line of lines) {
    line = line.trimEnd();
    if (!line.trim()) continue;

    // Level 1: # Step N — emoji Step N — Title
    // Extract the last part after the last —
    const step1Match = line.match(/^#\s+Step\s+(\d+)\s+—.*?—\s*(.+)$/);
    if (step1Match) {
      const code = step1Match[1];
      let text = step1Match[2].trim();
      const marker = extractMarkers(text);
      text = removeMarkers(text);

      items.push({ code, text, marker });
      continue;
    }

    // Any heading level (##, ###, ####, ...): codes drive hierarchy, unlimited depth
    const headingMatch = line.match(/^#{2,}\s+([\d.]+)\s+(.+)$/);
    if (headingMatch) {
      const code = headingMatch[1];
      let text = headingMatch[2].trim();
      const marker = extractMarkers(text);
      text = removeMarkers(text);

      items.push({ code, text, marker });
      continue;
    }

    // Indented numeric codes (with or without a leading dash for bullets)
    const indentMatch = line.match(/^\s*-?\s*([\d.]+)\s+(.+)$/);
    if (indentMatch) {
      const code = indentMatch[1];
      let text = indentMatch[2].trim();
      const marker = extractMarkers(text);
      text = removeMarkers(text);

      items.push({ code, text, marker });
      continue;
    }
  }

  return items;
}

function extractMarkers(text) {
  const match = text.match(/([⛔⏳✅❌]+)(?:\s*\(.*?\))?\s*$/);
  return match ? match[1] : '';
}

function removeMarkers(text) {
  return text.replace(/\s*[⛔⏳✅❌]+(?:\s*\(.*?\))?\s*$/, '').trim();
}

// ============================================================================
// TREE BUILDER: Build parent-child relationships based ONLY on numeric codes
// ============================================================================

function buildTree(items) {
  const nodeMap = {};

  const ensureNode = (code) => {
    if (!nodeMap[code]) {
      nodeMap[code] = {
        code,
        text: '',
        ownMarker: '',
        children: [],
        aggregatedMarker: ''
      };
    }
    return nodeMap[code];
  };

  // Create/update nodes from parsed items
  items.forEach((item) => {
    const node = ensureNode(item.code);
    node.text = item.text;
    node.ownMarker = item.marker || '';
  });

  // Build parent-child relationships purely by numeric codes
  items.forEach((item) => {
    const parts = item.code.split('.');
    if (parts.length > 1) {
      const parentCode = parts.slice(0, -1).join('.');
      const parent = ensureNode(parentCode);
      const child = ensureNode(item.code);

      if (!parent.children.includes(child)) {
        parent.children.push(child);
      }
    }
  });

  // Roots: anything without a parent (typically single-segment codes like "1")
  const roots = Object.values(nodeMap)
    .filter((node) => {
      const parts = node.code.split('.');
      if (parts.length === 1) return true;
      const parentCode = parts.slice(0, -1).join('.');
      return !nodeMap[parentCode];
    })
    .sort(compareCodes);

  // Sort children at every level numerically
  roots.forEach(sortChildrenRecursively);

  return { code: 'root', text: '', ownMarker: '', aggregatedMarker: '', children: roots };
}

function compareCodes(a, b) {
  const left = a.code.split('.').map(Number);
  const right = b.code.split('.').map(Number);
  const max = Math.max(left.length, right.length);

  for (let i = 0; i < max; i++) {
    const l = left[i] ?? -1;
    const r = right[i] ?? -1;
    if (l !== r) return l - r;
  }
  return left.length - right.length;
}

function sortChildrenRecursively(node) {
  node.children.sort(compareCodes);
  node.children.forEach(sortChildrenRecursively);
}

// ============================================================================
// STATUS AGGREGATION: Bottom-up with completion rule
// ============================================================================

function aggregateStatus(node) {
  const marker = node.ownMarker || '';

  const hasBlockedSelf = marker.includes('⛔');
  const hasInprogressSelf = marker.includes('⏳');
  const hasCompletedSelf = marker.includes('✅');
  const hasCancelledSelf = marker.includes('❌');
  const isPendingSelf = marker.trim() === '' && node.children.length === 0;

  let hasPending = isPendingSelf;
  let hasBlocked = hasBlockedSelf;
  let hasInprogress = hasInprogressSelf;
  let hasCompleted = hasCompletedSelf;
  let hasCancelled = hasCancelledSelf;

  node.children.forEach((child) => {
    const childStatus = aggregateStatus(child);
    hasPending = hasPending || childStatus.pending;
    hasBlocked = hasBlocked || childStatus.blocked;
    hasInprogress = hasInprogress || childStatus.inprogress;
    hasCompleted = hasCompleted || childStatus.completed;
    hasCancelled = hasCancelled || childStatus.cancelled;
  });

  const markers = [];
  if (hasBlocked) markers.push('⛔');
  if (hasInprogress) markers.push('⏳');
  if (hasCompleted && !hasPending) markers.push('✅');
  if (hasCancelled) markers.push('❌');

  node.aggregatedMarker = markers.join('');

  return {
    pending: hasPending,
    blocked: hasBlocked,
    inprogress: hasInprogress,
    completed: hasCompleted,
    cancelled: hasCancelled
  };
}

// ============================================================================
// RENDERER: Hierarchical plan_sync.md (recursive, unlimited depth)
// ============================================================================

function renderHierarchical(node, depth = 0) {
  // Root node: just render children
  if (depth === 0) {
    return node.children.map(child => renderHierarchical(child, 1)).join('\n\n');
  }

  // Calculate heading depth dynamically, but cap at 3 to avoid '####' and deeper headings
  const effectiveDepth = Math.min(depth, 3);
  const hashes = '#'.repeat(effectiveDepth);
  const markerStr = node.aggregatedMarker ? ` ${node.aggregatedMarker}` : '';

  // Format title based on depth
  let title;
  if (depth === 1) {
    title = `Step ${node.code} — ${node.text}`;
  } else {
    title = `${node.code} ${node.text}`;
  }

  // For depths >= 3, render leaves as indented bullets instead of deeper headings
  if (depth >= 3 && node.children.length === 0) {
    const indentStr = '  '.repeat(depth - 1);
    const bulletMarker = node.aggregatedMarker ? `${node.aggregatedMarker} ` : '';
    return `${indentStr}- ${bulletMarker}${node.code} ${node.text}`;
  }

  let output = `${hashes} ${title}${markerStr}`;

  // Recursively render all children (works for ANY depth)
  if (node.children.length > 0) {
    const childrenOutput = node.children
      .map(child => renderHierarchical(child, depth + 1))
      .join('\n');
    output += '\n' + childrenOutput;
  }

  return output;
}

// ============================================================================
// RENDERER: Flat plan_sync_todo.md (recursive, visits ALL descendants)
// ============================================================================

function renderFlat(node, indent = 0) {
  // Root node: just render children
  if (indent === 0) {
    return node.children.map(child => renderFlat(child, 1)).join('');
  }

  const indentStr = '  '.repeat(indent - 1);
  const markerStr = node.aggregatedMarker ? `${node.aggregatedMarker} ` : '';

  let output = `${indentStr}- ${markerStr}${node.code} ${node.text}\n`;

  // Recursively render ALL children (works for ANY depth)
  node.children.forEach(child => {
    output += renderFlat(child, indent + 1);
  });

  return output;
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

function main() {
  try {
    // Read source
    const sourceText = fs.readFileSync('plan/plan_source.md', 'utf8');

    // Parse items
    const items = parseSource(sourceText);
    console.log(`✓ Parsed ${items.length} items`);

    // Build tree (purely based on numeric codes)
    const tree = buildTree(items);
    console.log(`✓ Built tree structure`);

    // Aggregate statuses (bottom-up with completion rule)
    aggregateStatus(tree);
    console.log(`✓ Aggregated statuses`);

    // Render hierarchical plan
    const intro = `# Affiliate Product Showcase — Step-by-step Plan

> Numbered step plan with priority levels.

## Priority
- High  🔴 — Critical milestones and blockers
- Medium 🟠 — Important features
- Low 🟢 — Nice-to-have, docs, marketing

---

`;

    const hierarchicalContent = intro + renderHierarchical(tree);
    fs.writeFileSync('plan/plan_sync.md', hierarchicalContent, 'utf8');
    console.log(`✓ Generated plan_sync.md`);

    // Render flat todo
    const todoIntro = `# Affiliate Product Showcase — TODO List

> Flat TODO list with statuses.

`;

    const flatContent = todoIntro + renderFlat(tree);
    fs.writeFileSync('plan/plan_sync_todo.md', flatContent, 'utf8');
    console.log(`✓ Generated plan_sync_todo.md`);

    // Generate JSON
    const jsonContent = JSON.stringify(tree, null, 2);
    fs.writeFileSync('plan/plan_todos.json', jsonContent, 'utf8');
    console.log(`✓ Generated plan_todos.json`);

    console.log('\n✅ All files generated successfully!');
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

main();