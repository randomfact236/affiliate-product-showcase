#!/usr/bin/env node
/*
  plan_sync_todos.cjs

  Source of truth:
    - plan/plan_source.md  (editable outline)
    - plan/plan_state.json (status mapping)

  Generated outputs (DO NOT EDIT MANUALLY):
    - plan/plan_sync.md
    - plan/plan_sync_todo.md
    - plan/plan_todos.json

  Statuses:
    - pending
    - in-progress
    - blocked
    - cancelled
    - completed

  Marker priority:
    ✅ all completed
    ❌ any cancelled
    ⛔ any blocked
    ⏳ any in-progress
    (no marker) all pending
*/

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');

const DEFAULTS = {
  source: path.join(PLAN_DIR, 'plan_source.md'),
  state: path.join(PLAN_DIR, 'plan_state.json'),
  outPlan: path.join(PLAN_DIR, 'plan_sync.md'),
  outTodoMd: path.join(PLAN_DIR, 'plan_sync_todo.md'),
  outTodoJson: path.join(PLAN_DIR, 'plan_todos.json')
};

const VALID_STATUSES = new Set(['pending', 'in-progress', 'blocked', 'cancelled', 'completed']);

function parseArgs(argv) {
  const args = {
    source: DEFAULTS.source,
    state: DEFAULTS.state,
    outPlan: DEFAULTS.outPlan,
    outTodoMd: DEFAULTS.outTodoMd,
    outTodoJson: DEFAULTS.outTodoJson,
    bootstrap: false,
    quiet: false
  };

  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--bootstrap') args.bootstrap = true;
    else if (a === '--quiet') args.quiet = true;
    else if (a === '--source') args.source = path.resolve(argv[++i]);
    else if (a === '--state') args.state = path.resolve(argv[++i]);
    else if (a === '--out-plan') args.outPlan = path.resolve(argv[++i]);
    else if (a === '--out-todo-md') args.outTodoMd = path.resolve(argv[++i]);
    else if (a === '--out-todo-json') args.outTodoJson = path.resolve(argv[++i]);
    else {
      throw new Error(`Unknown arg: ${a}`);
    }
  }

  return args;
}

function sha1(text) {
  return crypto.createHash('sha1').update(text, 'utf8').digest('hex');
}

function readUtf8IfExists(filePath) {
  if (!fs.existsSync(filePath)) return null;
  return fs.readFileSync(filePath, 'utf8');
}

function writeUtf8(filePath, content) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  fs.writeFileSync(filePath, content, 'utf8');
}

function loadJson(filePath, fallback) {
  if (!fs.existsSync(filePath)) return fallback;
  const raw = fs.readFileSync(filePath, 'utf8');
  if (!raw.trim()) return fallback;
  return JSON.parse(raw);
}

function saveJsonPretty(filePath, obj) {
  writeUtf8(filePath, JSON.stringify(obj, null, 2) + '\n');
}

function stripGeneratedHeader(md) {
  // Remove leading GENERATED_BY_SYNC_TODOS comment block.
  const lines = md.split(/\r?\n/);
  let i = 0;
  while (i < lines.length && /^<!--\s*GENERATED_BY_SYNC_TODOS/.test(lines[i].trim())) i++;
  // Also strip blank lines immediately after.
  while (i < lines.length && !lines[i].trim()) i++;
  return lines.slice(i).join('\n');
}

function extractCode(line) {
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
  return /^#{2,}\s+\d+\.\d+\s+/i.test(line);
}

function parsePlanSource(md) {
  const allLines = md.split(/\r?\n/);

  // Keep any header content before first Step heading.
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

function mergeState(structure, state) {
  const statusByCode = state.statusByCode || {};

  walkPlan(structure, node => {
    const s = statusByCode[node.code];
    node.status = VALID_STATUSES.has(s) ? s : 'pending';
  });

  // Ensure state has entries for every node
  walkPlan(structure, node => {
    if (!statusByCode[node.code]) statusByCode[node.code] = node.status;
  });

  state.statusByCode = statusByCode;
  state.lastSyncAt = new Date().toISOString();

  return state;
}

function deriveMarkers(structure) {
  function deriveNode(node) {
    const children = node.kind === 'step' ? node.topics : (node.items || []);

    const derivedChildren = (children || []).map(deriveNode);

    const allChildrenCompleted = (children && children.length > 0)
      ? derivedChildren.every(c => c.derivedStatus === 'completed')
      : false;

    const anyCancelled = node.status === 'cancelled' || derivedChildren.some(c => c.derivedStatus === 'cancelled');
    const anyBlocked = node.status === 'blocked' || derivedChildren.some(c => c.derivedStatus === 'blocked');
    const anyInProgress = node.status === 'in-progress' || derivedChildren.some(c => c.derivedStatus === 'in-progress');

    const leafCompleted = (!children || children.length === 0) && node.status === 'completed';

    let derivedStatus = 'pending';
    if (leafCompleted || allChildrenCompleted) derivedStatus = 'completed';
    else if (anyCancelled) derivedStatus = 'cancelled';
    else if (anyBlocked) derivedStatus = 'blocked';
    else if (anyInProgress) derivedStatus = 'in-progress';

    let marker = '';
    if (derivedStatus === 'completed') marker = '✅';
    else if (derivedStatus === 'cancelled') marker = '❌';
    else if (derivedStatus === 'blocked') marker = '⛔';
    else if (derivedStatus === 'in-progress') marker = '⏳';

    node.derivedStatus = derivedStatus;
    node.marker = marker;

    return { derivedStatus, marker };
  }

  for (const step of structure.steps) deriveNode(step);
}

function renderPlanMd(structure, opts) {
  const sourceRel = path.relative(ROOT, opts.source).replace(/\\/g, '/');
  const stateRel = path.relative(ROOT, opts.state).replace(/\\/g, '/');

  const sourceText = readUtf8IfExists(opts.source) || '';
  const checksum = sha1(sourceText);

  const out = [];
  out.push('<!-- GENERATED_BY_SYNC_TODOS: true -->');
  out.push(`<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`);
  out.push(`<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`);
  out.push(`<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`);
  out.push('');

  // Preserve header area from the source outline (minus any generated markers)
  const headerLines = structure.headerLines || [];
  if (headerLines.length) {
    out.push(...headerLines);
    out.push('');
  }

  // Render Steps / Topics / Items with markers
  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    const stepTitle = step.title ? step.title : '';
    out.push(`# ${stepMarker}Step ${step.code} — ${stepTitle}`.trimEnd());
    out.push('');

    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`## ${topicMarker}${topic.code} ${topic.title}`.trimEnd());

      function renderItems(items, indentLevel) {
        if (!items || items.length === 0) return;
        for (const item of items) {
          const marker = item.marker ? `${item.marker} ` : '';
          const indent = '   '.repeat(Math.max(0, indentLevel));
          out.push(`${indent}${marker}${item.code} ${item.title}`.trimEnd());
          renderItems(item.items, indentLevel + 1);
        }
      }

      renderItems(topic.items, 1);
      out.push('');
    }
  }

  return out.join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
}

function renderTodoMd(structure) {
  const out = [];
  out.push('# Synced Todo List (Flattened)');
  out.push('');
  out.push('Legend: ✅ completed · ❌ cancelled · ⛔ blocked · ⏳ in-progress');
  out.push('');

  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    out.push(`${stepMarker}Step ${step.code} — ${step.title}`.trimEnd());

    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`  ${topicMarker}${topic.code} ${topic.title}`.trimEnd());

      function renderFlat(items) {
        if (!items) return;
        for (const item of items) {
          const marker = item.marker ? `${item.marker} ` : '';
          out.push(`    ${marker}${item.code} ${item.title}`.trimEnd());
          renderFlat(item.items);
        }
      }

      renderFlat(topic.items);
    }

    out.push('');
  }

  return out.join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
}

function renderTodoJson(structure) {
  const todos = [];

  walkPlan(structure, node => {
    // Keep all nodes (step/topic/item) but flatten to quick list
    todos.push({
      code: node.code,
      kind: node.kind,
      title: node.title,
      status: node.status,
      derivedStatus: node.derivedStatus,
      marker: node.marker
    });
  });

  return {
    generatedBy: 'scripts/plan_sync_todos.cjs',
    generatedAt: new Date().toISOString(),
    todos
  };
}

function main() {
  const args = parseArgs(process.argv);

  // Bootstrap: if plan_source.md is missing, create it from current plan_sync.md
  if (args.bootstrap && !fs.existsSync(args.source)) {
    const currentPlan = readUtf8IfExists(args.outPlan);
    if (!currentPlan) {
      throw new Error(`Bootstrap failed: no existing generated plan at ${args.outPlan}`);
    }

    const stripped = stripGeneratedHeader(currentPlan);
    writeUtf8(args.source, stripped.trimEnd() + '\n');
    if (!args.quiet) {
      console.log(`🧩 Bootstrapped plan source: ${args.source}`);
    }
  }

  if (!fs.existsSync(args.source)) {
    throw new Error(`Plan source missing: ${args.source} (run with --bootstrap once)`);
  }

  const sourceMd = fs.readFileSync(args.source, 'utf8');
  const parsed = parsePlanSource(sourceMd);

  const state = loadJson(args.state, {
    generatedBy: 'scripts/plan_sync_todos.cjs',
    statusByCode: {}
  });

  const mergedState = mergeState(parsed, state);
  deriveMarkers(parsed);

  saveJsonPretty(args.state, mergedState);

  const planMd = renderPlanMd(parsed, args);
  writeUtf8(args.outPlan, planMd);

  const todoMd = renderTodoMd(parsed);
  writeUtf8(args.outTodoMd, todoMd);

  const todoJson = renderTodoJson(parsed);
  saveJsonPretty(args.outTodoJson, todoJson);

  if (!args.quiet) {
    console.log('✅ Sync complete');
    console.log(' - Plan:', args.outPlan);
    console.log(' - Todo MD:', args.outTodoMd);
    console.log(' - Todo JSON:', args.outTodoJson);
    console.log(' - State:', args.state);
  }
}

try {
  main();
} catch (err) {
  console.error('❌', err && err.message ? err.message : err);
  process.exit(1);
}
