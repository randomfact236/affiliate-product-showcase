/*
  plan_sync_todos.cjs

  Generates:
  - plan/plan_sync.md
  - plan/plan_sync_todo.md
  - plan/plan_todos.json
  - plan/plan_state.json

  Source of truth:
  - plan/plan_source.md

  This script intentionally does NOT write to plan_source.md.
*/

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const cp = require('child_process');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');

const DEFAULTS = {
  source: path.join(PLAN_DIR, 'plan_source.md'),
  state: path.join(PLAN_DIR, 'plan_state.json'),
  outPlan: path.join(PLAN_DIR, 'plan_sync.md'),
  outTodoMd: path.join(PLAN_DIR, 'plan_sync_todo.md'),
  outTodoJson: path.join(PLAN_DIR, 'plan_todos.json')
};

const VALID_STATUSES = new Set(['pending', 'in-progress', 'completed']);

function sha1(text) {
  return crypto.createHash('sha1').update(String(text ?? ''), 'utf8').digest('hex');
}

function normalizeText(text) {
  let out = String(text ?? '').replace(/\r\n/g, '\n');
  try {
    out = out.normalize('NFC');
  } catch (_) {
    // ignore
  }
  return out;
}

function readUtf8IfExists(filePath) {
  if (!fs.existsSync(filePath)) return null;
  return normalizeText(fs.readFileSync(filePath, 'utf8'));
}

function writeUtf8(filePath, content) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  fs.writeFileSync(filePath, normalizeText(content), 'utf8');
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

function formatGeneratedMarkdownFiles(files, quiet) {
  const formatter = path.join(__dirname, 'format_plan_source.js');
  const res = cp.spawnSync(process.execPath, [formatter, '--files', ...files], {
    stdio: quiet ? ['ignore', 'ignore', 'pipe'] : 'inherit'
  });

  if (res.status !== 0) {
    const stderr = quiet && res.stderr ? String(res.stderr) : '';
    throw new Error(`plan formatter failed${stderr ? `: ${stderr.trim()}` : ''}`);
  }
}

function parseArgs(argv) {
  const args = {
    source: DEFAULTS.source,
    state: DEFAULTS.state,
    outPlan: DEFAULTS.outPlan,
    outTodoMd: DEFAULTS.outTodoMd,
    outTodoJson: DEFAULTS.outTodoJson,

    validate: false,
    strict: false,
    fixMissing: false,
    preview: false,

    // disabled (would write plan_source.md)
    bootstrap: false,
    apply: false,

    copySource: false,
    copyTodo: false,
    printSource: false,
    printTodo: false,
    quiet: false
  };

  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--validate') args.validate = true;
    else if (a === '--strict') args.strict = true;
    else if (a === '--fix-missing') args.fixMissing = true;
    else if (a === '--preview') args.preview = true;
    else if (a === '--quiet') args.quiet = true;
    else if (a === '--copy-source') args.copySource = true;
    else if (a === '--copy-todo') args.copyTodo = true;
    else if (a === '--print-source') args.printSource = true;
    else if (a === '--print-todo') args.printTodo = true;

    else if (a === '--bootstrap') args.bootstrap = true;
    else if (a === '--apply') args.apply = true;

    else if (a === '--source') args.source = path.resolve(argv[++i]);
    else if (a === '--state') args.state = path.resolve(argv[++i]);
    else if (a === '--out-plan') args.outPlan = path.resolve(argv[++i]);
    else if (a === '--out-todo-md') args.outTodoMd = path.resolve(argv[++i]);
    else if (a === '--out-todo-json') args.outTodoJson = path.resolve(argv[++i]);
    else throw new Error(`Unknown arg: ${a}`);
  }

  return args;
}

function extractCode(line) {
  const m = String(line ?? '').match(/^\s*(\d+(?:\.\d+)*)(?:\b|[^\d])/);
  return m ? m[1] : null;
}

function getLevel(code) {
  return String(code).split('.').length;
}

function getParentCode(code) {
  const parts = String(code).split('.');
  return parts.slice(0, -1).join('.');
}

function isStepHeader(line) {
  return /^#\s+Step\s+\d+/i.test(String(line ?? ''));
}

function isTopicHeader(line) {
  return /^#{2,}\s+\d+\.\d+\s+/i.test(String(line ?? ''));
}

function parsePlanSource(md) {
  const allLines = String(md ?? '').split(/\r?\n/);

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
  const itemRegistry = new Map();
  let currentStep = null;
  let currentTopic = null;

  for (const line of contentLines) {
    const trimmed = String(line ?? '').trim();
    if (!trimmed) continue;

    if (isStepHeader(line)) {
      const m = String(line).match(/^#\s+Step\s+(\d+)\s*(?:[—–-]+\s*)?(.*)$/i);
      if (!m) continue;
      const code = m[1];
      const title = (m[2] || '').trim();
      currentStep = { kind: 'step', code, title, rawLine: String(line).trim(), topics: [], status: 'pending' };
      structure.steps.push(currentStep);
      itemRegistry.set(code, currentStep);
      currentTopic = null;
      continue;
    }

    if (isTopicHeader(line)) {
      const m = String(line).match(/^#{2,}\s+(\d+\.\d+)\s+(.*)$/i);
      if (!m) continue;
      const topicCode = m[1];
      const topicTitle = (m[2] || '').trim();
      currentTopic = { kind: 'topic', code: topicCode, title: topicTitle, rawLine: String(line).trim(), items: [], status: 'pending' };

      const stepNum = topicCode.split('.')[0];
      if (!currentStep || currentStep.code !== stepNum) {
        currentStep = structure.steps.find(s => s.code === stepNum) || currentStep;
      }
      if (currentStep) currentStep.topics.push(currentTopic);
      itemRegistry.set(topicCode, currentTopic);
      continue;
    }

    const headingItemMatch = String(line).match(/^\s*#{3,}\s+(\d+(?:\.\d+)*)\s+(.*)$/);
    let code = null;
    let title = null;

    if (headingItemMatch) {
      code = headingItemMatch[1];
      title = (headingItemMatch[2] || '').trim();
    } else {
      const stripped = trimmed.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
      code = extractCode(stripped);
      if (!code) continue;
      const tm = stripped.match(/^\s*\d+(?:\.\d+)*\s+(.*)$/);
      title = tm ? (tm[1] || '').trim() : stripped.replace(/^\s*\d+(?:\.\d+)*\s*/, '').trim();
    }

    if (getLevel(code) < 3) continue;

    const parentCode = getParentCode(code);
    let parent = itemRegistry.get(parentCode);

    if (!parent) {
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

    const node = { kind: 'item', code, title: title || '', rawLine: trimmed, items: [], status: 'pending' };
    if (parent) {
      if (!parent.items) parent.items = [];
      parent.items.push(node);
    }
    itemRegistry.set(code, node);
  }

  function sortNodes(nodes) {
    if (!nodes) return;
    nodes.sort((a, b) => {
      const aNum = parseInt(String(a.code).split('.').pop(), 10);
      const bNum = parseInt(String(b.code).split('.').pop(), 10);
      return aNum - bNum;
    });
    for (const n of nodes) sortNodes(n.items);
  }

  for (const step of structure.steps) {
    step.topics.sort((a, b) => {
      const aNum = parseInt(String(a.code).split('.')[1], 10);
      const bNum = parseInt(String(b.code).split('.')[1], 10);
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

function validateStructure(sourceText, parsed) {
  const errors = [];
  const codes = [];
  const malformed = [];

  const lines = String(sourceText ?? '').split(/\r?\n/);
  for (const raw of lines) {
    const line = raw.trim();
    if (!line) continue;
    const m = line.match(/^\s*(?:>\s*)?(?:[-*+]\s+)?(?:#+\s*)?([^\s]+)/);
    if (!m) continue;
    const token = m[1];
    if (!/^[0-9]/.test(token)) continue;
    if (/^\d+(?:\.\d+)*$/.test(token)) codes.push(token);
    else malformed.push(token);
  }

  for (const t of malformed) errors.push(`Malformed code: ${t}`);

  const counts = {};
  for (const c of codes) counts[c] = (counts[c] || 0) + 1;
  for (const [c, n] of Object.entries(counts)) {
    if (n > 1) errors.push(`Duplicate code: ${c} appears ${n} times`);
  }

  const codeSet = new Set(codes);

  for (const c of Array.from(codeSet)) {
    if (!c.includes('.')) continue;
    const parent = getParentCode(c);
    if (!codeSet.has(parent) && !parsed.steps.some(s => s.code === parent)) {
      errors.push(`Orphan item: ${c} (parent ${parent} not found)`);
    }
  }

  const childrenByParent = {};
  for (const c of Array.from(codeSet)) {
    const parent = c.includes('.') ? getParentCode(c) : '__root';
    const last = parseInt(c.split('.').pop(), 10);
    if (!childrenByParent[parent]) childrenByParent[parent] = [];
    childrenByParent[parent].push(last);
  }

  const missingSiblings = [];
  for (const [parent, arr] of Object.entries(childrenByParent)) {
    const max = Math.max(...arr);
    for (let i = 1; i <= max; i++) {
      if (!arr.includes(i)) {
        missingSiblings.push({
          parent: parent === '__root' ? null : parent,
          code: parent === '__root' ? `${i}` : `${parent}.${i}`
        });
      }
    }
  }

  return { errors, missingSiblings };
}

function mergeState(structure, state) {
  const statusByCode = state.statusByCode || {};

  const prevKnown = new Set(Array.isArray(state.knownCodes) ? state.knownCodes : []);
  const currKnown = new Set();
  walkPlan(structure, node => {
    if (node && node.code) currKnown.add(node.code);
  });

  // Prune stale/orphan status entries.
  for (const code of Object.keys(statusByCode)) {
    if (!currKnown.has(code)) delete statusByCode[code];
  }

  walkPlan(structure, node => {
    const s = statusByCode[node.code];
    node.status = VALID_STATUSES.has(s) ? s : 'pending';
  });

  walkPlan(structure, node => {
    if (!statusByCode[node.code]) statusByCode[node.code] = node.status;
  });

  // Simplified propagation rules (bottom-up):
  // 1) If ANY child is `in-progress` -> parent becomes `in-progress`
  // 2) If ALL children are `completed` -> parent becomes `completed`
  // Build a list of nodes and process deepest-first so parents reflect child states.
  const allNodes = [];
  walkPlan(structure, node => {
    if (node && node.code) allNodes.push(node);
  });

  // Sort by depth descending (deepest first)
  allNodes.sort((a, b) => getLevel(b.code) - getLevel(a.code));

  for (const node of allNodes) {
    const children = node.kind === 'step' ? node.topics : (node.items || []);
    if (!children || children.length === 0) continue;

    const childStatuses = children.map(c => statusByCode[c.code] || 'pending');
    if (childStatuses.some(s => s === 'in-progress')) {
      statusByCode[node.code] = 'in-progress';
    } else if (childStatuses.length > 0 && childStatuses.every(s => s === 'completed')) {
      statusByCode[node.code] = 'completed';
    }
    // otherwise leave parent status as-is
  }

  // Apply propagated statuses back onto nodes so rendering/markers match.
  walkPlan(structure, node => {
    const s = statusByCode[node.code];
    node.status = VALID_STATUSES.has(s) ? s : 'pending';
  });

  state.generatedBy = 'plan/plan_sync_todos.cjs';
  state.statusByCode = statusByCode;
  state.knownCodes = Array.from(currKnown);
  // `lastSyncAt` is managed by the caller to avoid timestamp churn when
  // generated files haven't actually changed.
  return state;
}

function deriveMarkers(structure) {
  // Simplified marker mapping: direct status -> marker (no inheritance/derivation)
  walkPlan(structure, node => {
    const s = node.status || 'pending';
    node.derivedStatus = s;
    let marker = '';
    if (s === 'completed') marker = '✅';
    else if (s === 'in-progress') marker = '⏳';
    node.marker = marker;
  });
}

function renderHeader(opts, checksum) {
  const sourceRel = path.relative(ROOT, opts.source).replace(/\\/g, '/');
  const stateRel = path.relative(ROOT, opts.state).replace(/\\/g, '/');
  return [
    '<!-- GENERATED_BY_SYNC_TODOS: true -->',
    `<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`,
    `<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`,
    `<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`,
    ''
  ];
}

function renderPlanMd(structure, opts) {
  const sourceText = readUtf8IfExists(opts.source) || '';
  const checksum = sha1(sourceText);
  const out = [];
  out.push(...renderHeader(opts, checksum));

  const headerLines = structure.headerLines || [];
  if (headerLines.length) {
    out.push(...headerLines);
    out.push('');
  }

  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    out.push(`# ${stepMarker}Step ${step.code} — ${step.title || ''}`.trimEnd());
    out.push('');

    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`## ${topicMarker}${topic.code} ${topic.title}`.trimEnd());

      function renderItems(items, indentLevel) {
        if (!items || items.length === 0) return;
        for (const item of items) {
          const marker = item.marker ? `${item.marker} ` : '';
          if (item.rawLine && /^#+\s+/.test(item.rawLine)) {
            const hashes = (item.rawLine.match(/^#+/) || [''])[0];
            out.push(`${hashes} ${marker}${item.code} ${item.title}`.trimEnd());
          } else {
            const indent = '   '.repeat(Math.max(0, indentLevel));
            out.push(`${indent}${marker}${item.code} ${item.title}`.trimEnd());
          }
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
  out.push('Legend: ✅ completed · ⏳ in-progress');
  out.push('');

  function renderItemsAsList(items, indent) {
    if (!items) return;
    const pad = '  '.repeat(indent);
    for (const item of items) {
      const marker = item.marker ? `${item.marker} ` : '';
      out.push(`${pad}- ${marker}${item.code} ${item.title}`.trimEnd());
      renderItemsAsList(item.items, indent + 1);
    }
  }

  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    out.push(`- ${stepMarker}Step ${step.code} — ${step.title}`.trimEnd());
    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`  - ${topicMarker}${topic.code} ${topic.title}`.trimEnd());
      renderItemsAsList(topic.items, 2);
    }
    out.push('');
  }

  return out.join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
}

function renderTodoJson(structure) {
  const todos = [];
  walkPlan(structure, node => {
    todos.push({
      code: node.code,
      kind: node.kind,
      title: node.title,
      status: node.status,
      derivedStatus: node.derivedStatus,
      marker: node.marker
    });
  });
  return { generatedBy: 'plan/plan_sync_todos.cjs', todos };
}

function buildMarkerMap(structure) {
  const map = Object.create(null);
  walkPlan(structure, node => {
    map[node.code] = node.marker || '';
  });
  return map;
}

function injectMarkersIntoSource(sourceText, structure) {
  const markerMap = buildMarkerMap(structure);
  const lines = String(sourceText ?? '').split(/\r?\n/);

  return lines
    .map(line => {
      if (!line || !line.trim()) return line;

      const headingMatch = line.match(/^(\s*#+\s*)(\d+(?:\.\d+)*)(\b.*)$/);
      if (headingMatch) {
        const pre = headingMatch[1];
        const code = headingMatch[2];
        const rest = headingMatch[3];
        const mk = markerMap[code] ? markerMap[code] + ' ' : '';
        return `${pre}${mk}${code}${rest}`;
      }

      const stripped = line.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
      const code = extractCode(stripped);
      if (code && markerMap[code]) {
        return line.replace(code, `${markerMap[code]} ${code}`);
      }

      return line;
    })
    .join('\n');
}

function main() {
  const args = parseArgs(process.argv);

  if (args.bootstrap) throw new Error('Refusing to write plan_source.md (--bootstrap disabled).');
  if (args.apply) throw new Error('Refusing to write plan_source.md (--apply disabled).');

  if (!fs.existsSync(args.source)) throw new Error(`Plan source missing: ${args.source}`);

  const sourceMd = readUtf8IfExists(args.source) || '';
  const parsed = parsePlanSource(sourceMd);

  if (args.validate) {
    const vres = validateStructure(sourceMd, parsed);

    if (vres.errors.length) {
      console.error('❌ Validation failed (errors):');
      for (const e of vres.errors) console.error(' - ' + e);
    }

    const hasErrors = vres.errors.length > 0;
    const hasMissing = vres.missingSiblings.length > 0;

    if (hasMissing) {
      if (args.strict) {
        console.error('❌ Validation failed (missing siblings):');
        for (const m of vres.missingSiblings) {
          console.error(' - Missing sibling: ' + m.code + (m.parent ? ` (parent ${m.parent})` : ''));
        }
      } else {
        console.warn('⚠️ Validation warnings (missing siblings):');
        for (const m of vres.missingSiblings) {
          console.warn(' - Missing sibling: ' + m.code + (m.parent ? ` (parent ${m.parent})` : ''));
        }
      }
    }

    if (hasErrors) process.exit(2);

    if (args.fixMissing) {
      if (!hasMissing) {
        console.log('✅ No missing siblings to fix');
        process.exit(0);
      }
      console.log('🔍 Preview of missing siblings (source auto-fix disabled):');
      for (const m of vres.missingSiblings) console.log(' - ' + m.code + (m.parent ? ` (parent ${m.parent})` : ''));
      process.exit(1);
    }

    if (!hasErrors && hasMissing && !args.strict) {
      console.log('✅ Validation passed with warnings');
      process.exit(1);
    }
    if (!hasErrors && !hasMissing) {
      console.log('✅ Validation passed — no issues found');
      process.exit(0);
    }
    if (!hasErrors && hasMissing && args.strict) process.exit(2);
  }

  let state = loadJson(args.state, { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} });

  // Prefer explicit statuses from the synced todo JSON if available (allows
  // editing `plan/plan_todos.json` and having those statuses reflected in
  // generated plan files). Merge so existing state keys are preserved.
  try {
    const todoJson = loadJson(args.outTodoJson, null);
    if (todoJson && Array.isArray(todoJson.todos)) {
      const statusByTodos = Object.create(null);
      for (const t of todoJson.todos) {
        if (t && t.code && t.status) statusByTodos[t.code] = t.status;
      }
      state.statusByCode = Object.assign({}, state.statusByCode || {}, statusByTodos);
    }
  } catch (err) {
    // ignore — fall back to state as-is
  }

  const mergedState = mergeState(parsed, state);
  deriveMarkers(parsed);
  // Do not persist `lastSyncAt` yet — update it only when generated outputs change.

  const checksum = sha1(sourceMd);
  const header = renderHeader(args, checksum).join('\n');

  let planMd;
  if (args.copySource) {
    const injected = injectMarkersIntoSource(sourceMd, parsed);
    planMd = header + injected + (injected.endsWith('\n') ? '' : '\n');
  } else {
    planMd = renderPlanMd(parsed, args);
  }

  let todoMd;
  if (args.copyTodo) {
    todoMd = header + sourceMd + (sourceMd.endsWith('\n') ? '' : '\n');
  } else {
    todoMd = renderTodoMd(parsed);
  }

  if (args.printSource) {
    console.log(planMd.replace(/\n{3,}/g, '\n\n'));
    process.exit(0);
  }
  if (args.printTodo) {
    console.log(todoMd.replace(/\n{3,}/g, '\n\n'));
    process.exit(0);
  }

  // Read previous outputs so we can decide whether the generated files
  // actually changed. Only update `lastSyncAt` when something differs.
  const prevPlan = readUtf8IfExists(args.outPlan) || '';
  const prevTodoMd = readUtf8IfExists(args.outTodoMd) || '';
  const prevTodoJson = readUtf8IfExists(args.outTodoJson) || '';

  writeUtf8(args.outPlan, planMd);
  writeUtf8(args.outTodoMd, todoMd);
  saveJsonPretty(args.outTodoJson, renderTodoJson(parsed));

  // Ensure generated markdown matches CI formatting expectations.
  formatGeneratedMarkdownFiles([args.outPlan, args.outTodoMd], args.quiet);

  // Read new outputs and compare to previous contents.
  const newPlan = readUtf8IfExists(args.outPlan) || '';
  const newTodoMd = readUtf8IfExists(args.outTodoMd) || '';
  const newTodoJson = readUtf8IfExists(args.outTodoJson) || '';

  if (prevPlan !== newPlan || prevTodoMd !== newTodoMd || prevTodoJson !== newTodoJson) {
    mergedState.lastSyncAt = new Date().toISOString();
  } else {
    mergedState.lastSyncAt = (state && state.lastSyncAt) ? state.lastSyncAt : null;
  }

  // Persist state after deciding on lastSyncAt so timestamps only change
  // when generated outputs actually differ.
  saveJsonPretty(args.state, mergedState);

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
