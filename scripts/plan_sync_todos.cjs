#!/usr/bin/env node
// shim to preserve compatibility: forward to plan/plan_sync_todos.cjs
const child_process = require('child_process');
const path = require('path');
const target = path.join(__dirname, '..', 'plan', 'plan_sync_todos.cjs');
const args = process.argv.slice(2);
const cp = child_process.spawn(process.execPath, [target, ...args], { stdio: 'inherit' });
cp.on('exit', code => process.exit(code));
  let headerEnd = 0;
  for (let i = 0; i < allLines.length; i++) {
    if (isStepHeader(allLines[i])) {
      headerEnd = i;
      break;
    }
  }

  const headerLines = allLines.slice(0, headerEnd);
  const contentLines = allLines.slice(headerEnd);

  const structure = { headerLines, steps: [] };
  let currentStep = null;
  let currentTopic = null;

  const itemRegistry = new Map();

  for (let i = 0; i < contentLines.length; i++) {
    const line = contentLines[i];
    const trimmed = line.trim();
    if (!trimmed) continue;

    if (isStepHeader(line)) {
      const stepMatch = line.match(/^#\s+Step\s+(\d+)\s+[——-]\s+(.*)$/i);
      const fallback = line.match(/^#\s+Step\s+(\d+)\s*(.*)$/i);
      const code = stepMatch ? stepMatch[1] : (fallback ? fallback[1] : null);
      const title = stepMatch ? stepMatch[2].trim() : (fallback ? fallback[2].trim() : '');
      if (!code) continue;

      currentStep = {
        kind: 'step',
        code,
        title,
        rawLine: line.trim(),
        topics: [],
        status: 'pending'
      };
      structure.steps.push(currentStep);
      itemRegistry.set(code, currentStep);
      currentTopic = null;
      continue;
    }

    if (isTopicHeader(line)) {
      const m = line.match(/^#{2,}\s+(\d+\.\d+)\s+(.*)$/i);
      if (!m) continue;

      const topicCode = m[1];
      const topicTitle = m[2].trim();
      currentTopic = {
        kind: 'topic',
        code: topicCode,
        title: topicTitle,
        rawLine: line.trim(),
        items: [],
        status: 'pending'
      };

      // Attach to its step (by code prefix)
      const stepNum = topicCode.split('.')[0];
      if (!currentStep || currentStep.code !== stepNum) {
        // Attempt to find matching step.
        currentStep = structure.steps.find(s => s.code === stepNum) || currentStep;
      }
      if (currentStep) {
        currentStep.topics.push(currentTopic);
      }
      itemRegistry.set(topicCode, currentTopic);
      continue;
    }

    const code = extractCode(trimmed);
    if (!code) continue;

    const level = getLevel(code);
    if (level < 3) continue;

    const parentCode = getParentCode(code);
    let parent = itemRegistry.get(parentCode);

    // If missing parent, attach to closest known ancestor (topic or step)
    if (!parent) {
      // Search upward for any ancestor
      const parts = code.split('.');
      for (let lvl = parts.length - 1; lvl >= 2; lvl--) {
        const ancestorCode = parts.slice(0, lvl).join('.');
        if (itemRegistry.has(ancestorCode)) {
          parent = itemRegistry.get(ancestorCode);
          break;
        }
      }
      if (!parent) parent = currentTopic || currentStep;
    }

    const title = trimmed.replace(/^\s*\d+(?:\.\d+)*\s+/, '').trim();

    const node = {
      kind: 'item',
      code,
      title,
      rawLine: trimmed,
      items: [],
      status: 'pending'
    };

    if (parent) {
      if (!parent.items) parent.items = [];
      parent.items.push(node);
    }
    itemRegistry.set(code, node);
  }

  // Sort children deterministically by numeric suffix
  function sortNodes(nodes) {
    if (!nodes) return;
    nodes.sort((a, b) => {
      const aNum = parseInt(a.code.split('.').pop(), 10);
      const bNum = parseInt(b.code.split('.').pop(), 10);
      return aNum - bNum;
    });
    for (const n of nodes) sortNodes(n.items);
  }

  for (const step of structure.steps) {
    step.topics.sort((a, b) => {
      const aNum = parseInt(a.code.split('.')[1], 10);
      const bNum = parseInt(b.code.split('.')[1], 10);
      return aNum - bNum;
    });
    for (const topic of step.topics) sortNodes(topic.items);
  }

  return structure;
}

function walkPlan(structure, visitor) {
  for (const step of structure.steps) {
    visitor(step);
    for (const topic of step.topics) {
      visitor(topic);
      (function walkItems(items) {
        if (!items) return;
        for (const it of items) {
          visitor(it);
          walkItems(it.items);
        }
      })(topic.items);
    }
  }
}

#!/usr/bin/env node
// shim to preserve compatibility: forward to plan/plan_sync_todos.cjs
const child_process = require('child_process');
const path = require('path');
const target = path.join(__dirname, '..', 'plan', 'plan_sync_todos.cjs');
const args = process.argv.slice(2);
const cp = child_process.spawn(process.execPath, [target, ...args], { stdio: 'inherit' });
cp.on('exit', code => process.exit(code));
  // Ensure state has entries for every node
