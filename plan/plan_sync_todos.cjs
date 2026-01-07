#!/usr/bin/env node
/*
  plan_sync_todos.cjs

  Generator for plan files. Features:
  - UTF-8 NFC normalization and LF line endings
  - Preserve `lastSyncAt` unless status/code data meaningfully changes
  - Accept optional badge before "Step N" headings
  - Render nested items attached directly to steps
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
  const args = Object.assign({
    source: DEFAULTS.source,
    state: DEFAULTS.state,
    outPlan: DEFAULTS.outPlan,
    outTodoMd: DEFAULTS.outTodoMd,
    outTodoJson: DEFAULTS.outTodoJson,
    bootstrap: false,
    quiet: false,
    copySource: false,
    copyTodo: false
  }, {});
  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--bootstrap') args.bootstrap = true;
    else if (a === '--quiet') args.quiet = true;
    else if (a === '--copy-source') args.copySource = true;
    else if (a === '--copy-todo') args.copyTodo = true;
    else if (a === '--print-source') args.printSource = true;
    else if (a === '--print-todo') args.printTodo = true;
    else if (a === '--source') args.source = path.resolve(argv[++i]);
    else if (a === '--state') args.state = path.resolve(argv[++i]);
    else if (a === '--out-plan') args.outPlan = path.resolve(argv[++i]);
    else if (a === '--out-todo-md') args.outTodoMd = path.resolve(argv[++i]);
    else if (a === '--out-todo-json') args.outTodoJson = path.resolve(argv[++i]);
    else throw new Error(`Unknown arg: ${a}`);
  }
  return args;
}

// Optional use of `unicode-normalize` if installed; fall back to String.prototype.normalize
let unicodeNormalize = null;
try { unicodeNormalize = require('unicode-normalize'); } catch (e) { unicodeNormalize = null; }

function sha1(text) { return crypto.createHash('sha1').update(String(text), 'utf8').digest('hex'); }

function normalizeText(txt) {
  let s = String(txt || '');
  s = s.replace(/\r\n/g, '\n');
  try {
    if (unicodeNormalize && typeof unicodeNormalize.nfc === 'function') return unicodeNormalize.nfc(s);
    if (typeof s.normalize === 'function') return s.normalize('NFC');
    return s;
  } catch (e) { return s; }
}

function readUtf8IfExists(filePath) {
  if (!fs.existsSync(filePath)) return null;
  const raw = fs.readFileSync(filePath, 'utf8');
  return normalizeText(raw);
}

function writeUtf8(filePath, content) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  fs.writeFileSync(filePath, normalizeText(content), 'utf8');
}

function loadJson(filePath, fallback) { if (!fs.existsSync(filePath)) return fallback; const raw = fs.readFileSync(filePath, 'utf8'); if (!raw.trim()) return fallback; return JSON.parse(normalizeText(raw)); }
function saveJsonPretty(filePath, obj) { writeUtf8(filePath, JSON.stringify(obj, null, 2) + '\n'); }

function stripGeneratedHeader(md) {
  const lines = String(md || '').split(/\r?\n/);
  let i = 0; while (i < lines.length && /^<!--\s*GENERATED_BY_SYNC_TODOS/.test(lines[i].trim())) i++;
  while (i < lines.length && !lines[i].trim()) i++;
  return lines.slice(i).join('\n');
}

function extractCode(line) { const match = line.match(/^\s*(\d+(?:\.\d+)*)?(?:\b|[^\d])/); return match ? match[1] : null; }
function getLevel(code) { return String(code).split('.').length; }
function getParentCode(code) { const parts = String(code).split('.'); return parts.slice(0, -1).join('.'); }

// Accept optional emoji or badge before 'Step N' (e.g., '# 🔴 Step 1 — Title')
function isStepHeader(line) { return /^#\s+(?:[^\s]+\s+)?Step\s+\d+/i.test(line); }
function isTopicHeader(line) { return /^#{2,}\s+\d+\.\d+\s+/i.test(line); }

function parsePlanSource(md) {
  const allLines = String(md || '').split(/\r?\n/);
  let headerEnd = 0; for (let i = 0; i < allLines.length; i++) { if (isStepHeader(allLines[i])) { headerEnd = i; break; } }
  const headerLines = allLines.slice(0, headerEnd); const contentLines = allLines.slice(headerEnd);
  const structure = { headerLines, steps: [] };
  let currentStep = null; let currentTopic = null; const itemRegistry = new Map();
  for (let i = 0; i < contentLines.length; i++) {
    const line = contentLines[i]; const trimmed = String(line || '').trim(); if (!trimmed) continue;
    if (isStepHeader(line)) {
      // Match optional badge then Step number and title, allowing various dash chars
      const stepMatch = line.match(/^#\s+(?:[^\s]+\s+)?Step\s+(\d+)\s+[—–\-]\s+(.*)$/i);
      const fallback = line.match(/^#\s+(?:[^\s]+\s+)?Step\s+(\d+)\s*(.*)$/i);
      const code = stepMatch ? stepMatch[1] : (fallback ? fallback[1] : null);
      const title = stepMatch ? stepMatch[2].trim() : (fallback ? fallback[2].trim() : '');
      if (!code) continue;
      currentStep = { kind: 'step', code, title, rawLine: line.trim(), topics: [], items: [], status: 'pending' };
      structure.steps.push(currentStep); itemRegistry.set(code, currentStep); currentTopic = null; continue;
    }
    if (isTopicHeader(line)) {
      const m = line.match(/^#{2,}\s+(\d+\.\d+)\s+(.*)$/i); if (!m) continue;
      const topicCode = m[1]; const topicTitle = m[2].trim(); currentTopic = { kind: 'topic', code: topicCode, title: topicTitle, rawLine: line.trim(), items: [], status: 'pending' };
      const stepNum = topicCode.split('.')[0]; if (!currentStep || currentStep.code !== stepNum) currentStep = structure.steps.find(s => s.code === stepNum) || currentStep;
      if (currentStep) currentStep.topics.push(currentTopic); itemRegistry.set(topicCode, currentTopic); continue;
    }
    const headingItemMatch = line.match(/^\s*#{3,}\s+(\d+(?:\.\d+)*)\s+(.*)$/);
    let code = null; let title = null;
    if (headingItemMatch) {
      code = headingItemMatch[1]; title = headingItemMatch[2].trim();
    } else {
      const stripped = trimmed.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
      code = extractCode(stripped);
      if (!code) continue;
      const titleMatch = stripped.match(/^\s*\d+(?:\.\d+)*\s+(.*)$/);
      title = titleMatch ? titleMatch[1].trim() : stripped.replace(/^\s*\d+(?:\.\d+)*\s*/, '').trim();
    }
    const level = getLevel(code); if (level < 3) continue;
    const parentCode = getParentCode(code); let parent = itemRegistry.get(parentCode);
    if (!parent) { const parts = code.split('.'); for (let lvl = parts.length - 1; lvl >= 2; lvl--) { const ancestorCode = parts.slice(0, lvl).join('.'); if (itemRegistry.has(ancestorCode)) { parent = itemRegistry.get(ancestorCode); break; } } if (!parent) parent = currentTopic || currentStep; }
    const node = { kind: 'item', code, title, rawLine: trimmed, items: [], status: 'pending' };
    if (parent) { if (!parent.items) parent.items = []; parent.items.push(node); } itemRegistry.set(code, node);
  }
  function sortNodes(nodes) { if (!nodes) return; nodes.sort((a,b)=>{const aNum=parseInt(a.code.split('.').pop(),10);const bNum=parseInt(b.code.split('.').pop(),10);return aNum-bNum;}); for (const n of nodes) sortNodes(n.items); }
  for (const step of structure.steps) { step.topics.sort((a,b)=>{const aNum=parseInt(a.code.split('.')[1],10);const bNum=parseInt(b.code.split('.')[1],10);return aNum-bNum;}); for (const topic of step.topics) sortNodes(topic.items); if (step.items) sortNodes(step.items); }
  return structure;
}

function walkPlan(structure, visitor) {
  function walkItems(items) {
    if (!items) return;
    for (const it of items) { visitor(it); walkItems(it.items); }
  }
  for (const step of structure.steps) {
    visitor(step);
    walkItems(step.items);
    for (const topic of step.topics) { visitor(topic); walkItems(topic.items); }
  }
}

function mergeState(structure, state) {
  const statusByCode = state.statusByCode || {};
  const prevStatusHash = sha1(JSON.stringify(state.statusByCode || {}));

  const prevKnown = new Set(Array.isArray(state.knownCodes) ? state.knownCodes : []);
  const currKnown = new Set();
  walkPlan(structure, node => { if (node && node.code) currKnown.add(node.code); });
  let addedCodes = [];
  if (Array.isArray(state.knownCodes) && state.knownCodes.length > 0) {
    for (const c of currKnown) if (!prevKnown.has(c)) addedCodes.push(c);
  }

  walkPlan(structure, node => {
    const s = statusByCode[node.code];
    node.status = VALID_STATUSES.has(s) ? s : 'pending';
  });

  walkPlan(structure, node => { if (!statusByCode[node.code]) statusByCode[node.code] = node.status; });

  for (const added of addedCodes) {
    let parent = getParentCode(added);
    while (parent) {
      if (statusByCode[parent] === 'completed') statusByCode[parent] = 'in-progress';
      parent = getParentCode(parent);
    }
  }

  const startedCodes = Object.keys(statusByCode).filter(c => statusByCode[c] === 'in-progress');
  for (const started of startedCodes) {
    let parent = getParentCode(started);
    while (parent) {
      if (statusByCode[parent] === 'completed') statusByCode[parent] = 'in-progress';
      parent = getParentCode(parent);
    }
  }

  function anyDescendantNotCompleted(items) {
    if (!items || items.length === 0) return false;
    for (const it of items) {
      if (statusByCode[it.code] !== 'completed') return true;
      if (anyDescendantNotCompleted(it.items)) return true;
    }
    return false;
  }
  walkPlan(structure, node => {
    if (!node || !node.code) return;
    const s = statusByCode[node.code];
    const children = node.kind === 'step' ? node.topics : (node.items || []);
    if (s === 'completed' && anyDescendantNotCompleted(children)) statusByCode[node.code] = 'in-progress';
  });

  const newStatusHash = sha1(JSON.stringify(statusByCode));
  state.statusByCode = statusByCode;
  state.knownCodes = Array.from(currKnown);
  if (!state.lastSyncAt || prevStatusHash !== newStatusHash || addedCodes.length > 0) {
    state.lastSyncAt = new Date().toISOString();
  }
  return state;
}

function deriveMarkers(structure) {
  function deriveNode(node, inheritedInProgress) {
    const children = node.kind === 'step' ? node.topics : (node.items || []);
    const currentInherited = !!inheritedInProgress || node.status === 'in-progress';
    const derivedChildren = (children || []).map(c => deriveNode(c, currentInherited));
    const allChildrenCompleted = (children && children.length > 0) ? derivedChildren.every(c => c.derivedStatus === 'completed') : false;
    const anyCancelled = node.status === 'cancelled' || derivedChildren.some(c => c.derivedStatus === 'cancelled');
    const anyBlocked = node.status === 'blocked' || derivedChildren.some(c => c.derivedStatus === 'blocked');
    const anyInProgress = currentInherited || derivedChildren.some(c => c.derivedStatus === 'in-progress');
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
  for (const step of structure.steps) deriveNode(step, false);
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
  const headerLines = structure.headerLines || []; if (headerLines.length) { out.push(...headerLines); out.push(''); }
  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    const stepTitle = step.title ? step.title : '';
    out.push(`# ${stepMarker}Step ${step.code} — ${stepTitle}`.trimEnd());
    out.push('');
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
    renderItems(step.items, 1);
    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`## ${topicMarker}${topic.code} ${topic.title}`.trimEnd());
      renderItems(topic.items, 1);
      out.push('');
    }
  }
  return out.join('\n').replace(/\n{3,}/g,'\n\n')+'\n';
}

function buildMarkerMap(structure){ const map = Object.create(null); walkPlan(structure, node => { if (node && node.code) map[node.code] = node.marker || ''; }); return map; }

function injectMarkersIntoSource(sourceText, structure){
  const markerMap = buildMarkerMap(structure);
  const lines = String(sourceText || '').split(/\r?\n/);
  return lines.map(line => {
    if (!line || !line.trim()) return line;
    const headingMatch = line.match(/^(\s*#+\s*)(\d+(?:\.\d+)*)(\b.*)$/);
    if (headingMatch){ const pre = headingMatch[1]; const code = headingMatch[2]; const rest = headingMatch[3]; const mk = markerMap[code] ? markerMap[code] + ' ' : ''; return `${pre}${mk}${code}${rest}`; }
    const stripped = line.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
    const code = extractCode(stripped);
    if (code && markerMap[code]) return line.replace(code, `${markerMap[code]} ${code}`);
    return line;
  }).join('\n');
}

function renderTodoMd(structure){
  const out = [];
  out.push('# Synced Todo List (Flattened)');
  out.push('');
  out.push('Legend: ✅ completed · ❌ cancelled · ⛔ blocked · ⏳ in-progress');
  out.push('');
  walkPlan(structure, node => {
    if (!node || !node.code) return;
    const level = getLevel(node.code);
    const pad = '  '.repeat(Math.max(0, level - 1));
    const marker = node.marker ? `${node.marker} ` : '';
    if (node.kind === 'step') out.push(`${pad}- ${marker}Step ${node.code} — ${node.title}`.trimEnd());
    else out.push(`${pad}- ${marker}${node.code} ${node.title}`.trimEnd());
  });
  out.push('');
  return out.join('\n').replace(/\n{3,}/g,'\n\n')+'\n';
}

function renderTodoJson(structure){ const todos=[]; walkPlan(structure,node=>{ todos.push({ code: node.code, kind: node.kind, title: node.title, status: node.status, derivedStatus: node.derivedStatus, marker: node.marker }); }); return { generatedBy: 'plan/plan_sync_todos.cjs', todos }; }

function main(){
  const args = parseArgs(process.argv);
  if (args.bootstrap && !fs.existsSync(args.source)){
    const currentPlan = readUtf8IfExists(args.outPlan);
    if (!currentPlan) throw new Error(`Bootstrap failed: no existing generated plan at ${args.outPlan}`);
    const stripped = stripGeneratedHeader(currentPlan);
    writeUtf8(args.source, stripped.trimEnd() + '\n');
    if (!args.quiet) console.log(`🧩 Bootstrapped plan source: ${args.source}`);
  }
  if (!fs.existsSync(args.source)) throw new Error(`Plan source missing: ${args.source} (run with --bootstrap once)`);
  const sourceMd = fs.readFileSync(args.source,'utf8');
  const parsed = parsePlanSource(sourceMd);
  const state = loadJson(args.state, { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} });
  const mergedState = mergeState(parsed, state);
  deriveMarkers(parsed);
  saveJsonPretty(args.state, mergedState);

  const sourceText = sourceMd;
  const checksum = sha1(sourceText);
  const sourceRel = path.relative(ROOT, args.source).replace(/\\/g, '/');
  const stateRel = path.relative(ROOT, args.state).replace(/\\/g, '/');
  let planMd = renderPlanMd(parsed, args);

  let todoMd = renderTodoMd(parsed);

  if (args.copySource) {
    const header = [];
    header.push('<!-- GENERATED_BY_SYNC_TODOS: true -->');
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`);
    header.push('');
    const injected = injectMarkersIntoSource(sourceText.replace(/\r\n/g, '\n'), parsed);
    planMd = header.join('\n') + injected;
  }
  if (args.copyTodo) {
    const header = [];
    header.push('<!-- GENERATED_BY_SYNC_TODOS: true -->');
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`);
    header.push('');
    todoMd = header.join('\n') + sourceText.replace(/\r\n/g, '\n');
  }

  if (args.printSource) { console.log(planMd.replace(/\n{3,}/g,'\n\n')); process.exit(0); }
  if (args.printTodo) { console.log(todoMd.replace(/\n{3,}/g,'\n\n')); process.exit(0); }

  writeUtf8(args.outPlan, planMd);
  writeUtf8(args.outTodoMd, todoMd);
  const todoJson = renderTodoJson(parsed);
  saveJsonPretty(args.outTodoJson, todoJson);
  if (!args.quiet){
    console.log('✅ Sync complete');
    console.log(' - Plan:', args.outPlan);
    console.log(' - Todo MD:', args.outTodoMd);
    console.log(' - Todo JSON:', args.outTodoJson);
    console.log(' - State:', args.state);
  }
}

try{ main(); } catch(err){ console.error('❌', err && err.message ? err.message : err); process.exit(1); }
function stripGeneratedHeader(md) {
  const lines = md.split(/\r?\n/);
  let i = 0; while (i < lines.length && /^<!--\s*GENERATED_BY_SYNC_TODOS/.test(lines[i].trim())) i++;
  while (i < lines.length && !lines[i].trim()) i++;
  return lines.slice(i).join('\n');
}

function extractCode(line) { const match = line.match(/^\s*(\d+(?:\.\d+)*)(?:\b|[^\d])/); return match ? match[1] : null; }
function getLevel(code) { return code.split('.').length; }
function getParentCode(code) { const parts = code.split('.'); return parts.slice(0, -1).join('.'); }
function isStepHeader(line) { return /^#\s+Step\s+\d+/i.test(line); }
function isTopicHeader(line) { return /^#{2,}\s+\d+\.\d+\s+/i.test(line); }

function parsePlanSource(md) {
  const allLines = md.split(/\r?\n/);
  let headerEnd = 0; for (let i = 0; i < allLines.length; i++) { if (isStepHeader(allLines[i])) { headerEnd = i; break; } }
  const headerLines = allLines.slice(0, headerEnd); const contentLines = allLines.slice(headerEnd);
  const structure = { headerLines, steps: [] };
  let currentStep = null; let currentTopic = null; const itemRegistry = new Map();
  for (let i = 0; i < contentLines.length; i++) {
    const line = contentLines[i]; const trimmed = line.trim(); if (!trimmed) continue;
    if (isStepHeader(line)) {
      const stepMatch = line.match(/^#\s+Step\s+(\d+)\s+[——-]\s+(.*)$/i);
      const fallback = line.match(/^#\s+Step\s+(\d+)\s*(.*)$/i);
      const code = stepMatch ? stepMatch[1] : (fallback ? fallback[1] : null);
      const title = stepMatch ? stepMatch[2].trim() : (fallback ? fallback[2].trim() : '');
      if (!code) continue;
      currentStep = { kind: 'step', code, title, rawLine: line.trim(), topics: [], status: 'pending' };
      structure.steps.push(currentStep); itemRegistry.set(code, currentStep); currentTopic = null; continue;
    }
    if (isTopicHeader(line)) {
      const m = line.match(/^#{2,}\s+(\d+\.\d+)\s+(.*)$/i); if (!m) continue;
      const topicCode = m[1]; const topicTitle = m[2].trim(); currentTopic = { kind: 'topic', code: topicCode, title: topicTitle, rawLine: line.trim(), items: [], status: 'pending' };
      const stepNum = topicCode.split('.')[0]; if (!currentStep || currentStep.code !== stepNum) currentStep = structure.steps.find(s => s.code === stepNum) || currentStep;
      if (currentStep) currentStep.topics.push(currentTopic); itemRegistry.set(topicCode, currentTopic); continue;
    }
    // Support numbered item headings such as '### 1.1.1 Title' in addition to
    // plain lines that start with '1.1.1 ...'. This lets authors use headings
    // for subtopics while keeping the numeric structure parseable.
    const headingItemMatch = line.match(/^\s*#{3,}\s+(\d+(?:\.\d+)*)\s+(.*)$/);
    let code = null;
    let title = null;
    if (headingItemMatch) {
      code = headingItemMatch[1];
      title = headingItemMatch[2].trim();
    } else {
      // Strip common list markers and blockquote markers before parsing.
      // This allows lines like '- 1.1.1.1 ...' to be recognized.
      const stripped = trimmed.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
      code = extractCode(stripped);
      if (!code) continue;
      const titleMatch = stripped.match(/^\s*\d+(?:\.\d+)*\s+(.*)$/);
      title = titleMatch ? titleMatch[1].trim() : stripped.replace(/^\s*\d+(?:\.\d+)*\s*/, '').trim();
    }
    const level = getLevel(code); if (level < 3) continue;
    const parentCode = getParentCode(code); let parent = itemRegistry.get(parentCode);
    if (!parent) { const parts = code.split('.'); for (let lvl = parts.length - 1; lvl >= 2; lvl--) { const ancestorCode = parts.slice(0, lvl).join('.'); if (itemRegistry.has(ancestorCode)) { parent = itemRegistry.get(ancestorCode); break; } } if (!parent) parent = currentTopic || currentStep; }
    const node = { kind: 'item', code, title, rawLine: trimmed, items: [], status: 'pending' };
    if (parent) { if (!parent.items) parent.items = []; parent.items.push(node); } itemRegistry.set(code, node);
  }
  function sortNodes(nodes) { if (!nodes) return; nodes.sort((a,b)=>{const aNum=parseInt(a.code.split('.').pop(),10);const bNum=parseInt(b.code.split('.').pop(),10);return aNum-bNum;}); for (const n of nodes) sortNodes(n.items); }
  for (const step of structure.steps) { step.topics.sort((a,b)=>{const aNum=parseInt(a.code.split('.')[1],10);const bNum=parseInt(b.code.split('.')[1],10);return aNum-bNum;}); for (const topic of step.topics) sortNodes(topic.items); }
  return structure;
}

function walkPlan(structure, visitor) { for (const step of structure.steps) { visitor(step); for (const topic of step.topics) { visitor(topic); (function walkItems(items){ if (!items) return; for (const it of items) { visitor(it); walkItems(it.items); } })(topic.items); } } }

function mergeState(structure, state) {
  const statusByCode = state.statusByCode || {};

  // Build sets of known codes to detect newly added items since last run.
  // If `state.knownCodes` is missing (first bootstrap), treat this as
  // a noop for added-code detection to avoid mass-changing statuses.
  const prevKnown = new Set(Array.isArray(state.knownCodes) ? state.knownCodes : []);
  const currKnown = new Set();
  walkPlan(structure, node => { if (node && node.code) currKnown.add(node.code); });
  let addedCodes = [];
  if (Array.isArray(state.knownCodes) && state.knownCodes.length > 0) {
    for (const c of currKnown) if (!prevKnown.has(c)) addedCodes.push(c);
  } else {
    addedCodes = [];
  }

  // Apply existing statuses from state to nodes (or default to 'pending')
  walkPlan(structure, node => {
    const s = statusByCode[node.code];
    node.status = VALID_STATUSES.has(s) ? s : 'pending';
  });

  // Ensure every node has an entry in statusByCode
  walkPlan(structure, node => { if (!statusByCode[node.code]) statusByCode[node.code] = node.status; });

  // If new child was added under an ancestor that was previously completed,
  // mark that ancestor (and its ancestors) as in-progress so the badge updates.
  // Only do this when we detected actual added codes (i.e. not during initial
  // bootstrapping where `knownCodes` was missing).
  for (const added of addedCodes) {
    let parent = getParentCode(added);
    while (parent) {
      if (statusByCode[parent] === 'completed') {
        statusByCode[parent] = 'in-progress';
      }
      parent = getParentCode(parent);
    }
  }

  // Only propagate "in-progress" up the tree when a child is explicitly
  // marked as in-progress (i.e. the task was started by a person). This
  // avoids automatically marking all newly added tasks as started.
  const startedCodes = Object.keys(statusByCode).filter(c => statusByCode[c] === 'in-progress');
  for (const started of startedCodes) {
    let parent = getParentCode(started);
    while (parent) {
      if (statusByCode[parent] === 'completed') {
        statusByCode[parent] = 'in-progress';
      }
      parent = getParentCode(parent);
    }
  }

  // Additional safety: if a node is marked `completed` but any of its
  // children (or descendants) are not completed, mark the node as
  // `in-progress`. This handles the common workflow where a parent was
  // previously completed and new child items were later added.
  function anyDescendantNotCompleted(items) {
    if (!items || items.length === 0) return false;
    for (const it of items) {
      if (statusByCode[it.code] !== 'completed') return true;
      if (anyDescendantNotCompleted(it.items)) return true;
    }
    return false;
  }
  walkPlan(structure, node => {
    if (!node || !node.code) return;
    const s = statusByCode[node.code];
    const children = node.kind === 'step' ? node.topics : (node.items || []);
    if (s === 'completed' && anyDescendantNotCompleted(children)) {
      statusByCode[node.code] = 'in-progress';
    }
  });

  state.statusByCode = statusByCode;
  state.knownCodes = Array.from(currKnown);
  state.lastSyncAt = new Date().toISOString();
  return state;
}

function deriveMarkers(structure) {
  function deriveNode(node, inheritedInProgress) {
    const children = node.kind === 'step' ? node.topics : (node.items || []);
    const currentInherited = !!inheritedInProgress || node.status === 'in-progress';
    const derivedChildren = (children || []).map(c => deriveNode(c, currentInherited));
    const allChildrenCompleted = (children && children.length > 0) ? derivedChildren.every(c => c.derivedStatus === 'completed') : false;
    const anyCancelled = node.status === 'cancelled' || derivedChildren.some(c => c.derivedStatus === 'cancelled');
    const anyBlocked = node.status === 'blocked' || derivedChildren.some(c => c.derivedStatus === 'blocked');
    const anyInProgress = currentInherited || derivedChildren.some(c => c.derivedStatus === 'in-progress');
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

  for (const step of structure.steps) deriveNode(step, false);
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
  const headerLines = structure.headerLines || []; if (headerLines.length) { out.push(...headerLines); out.push(''); }
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
          // If the original source line for this item started with one or
          // more '#' characters, preserve that heading level in the
          // generated plan so editors can fold/expand the section.
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
  return out.join('\n').replace(/\n{3,}/g,'\n\n')+'\n';
}

function buildMarkerMap(structure){
  const map = Object.create(null);
  walkPlan(structure, node => { if (node && node.code) map[node.code] = node.marker || ''; });
  return map;
}

function injectMarkersIntoSource(sourceText, structure){
  const markerMap = buildMarkerMap(structure);
  const lines = sourceText.split(/\r?\n/);
  return lines.map(line => {
    if (!line || !line.trim()) return line;
    // Preserve heading hashes, e.g. '### 1.1.1 Title'
    const headingMatch = line.match(/^(\s*#+\s*)(\d+(?:\.\d+)*)(\b.*)$/);
    if (headingMatch){
      const pre = headingMatch[1]; const code = headingMatch[2]; const rest = headingMatch[3];
      const mk = markerMap[code] ? markerMap[code] + ' ' : '';
      return `${pre}${mk}${code}${rest}`;
    }
    // Strip common list markers before attempting to find a numeric code
    const stripped = line.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
    const code = extractCode(stripped);
    if (code && markerMap[code]){
      // Replace first occurrence of the code in the original line with marker + code
      return line.replace(code, `${markerMap[code]} ${code}`);
    }
    return line;
  }).join('\n');
}



function renderTodoJson(structure){ const todos=[]; walkPlan(structure,node=>{ todos.push({ code: node.code, kind: node.kind, title: node.title, status: node.status, derivedStatus: node.derivedStatus, marker: node.marker }); }); return { generatedBy: 'plan/plan_sync_todos.cjs', todos }; }

function main(){ const args = parseArgs(process.argv); if (args.bootstrap && !fs.existsSync(args.source)){ const currentPlan = readUtf8IfExists(args.outPlan); if (!currentPlan) throw new Error(`Bootstrap failed: no existing generated plan at ${args.outPlan}`); const stripped = stripGeneratedHeader(currentPlan); writeUtf8(args.source, stripped.trimEnd()+'\n'); if (!args.quiet) console.log(`🧩 Bootstrapped plan source: ${args.source}`); }
  if (!fs.existsSync(args.source)) throw new Error(`Plan source missing: ${args.source} (run with --bootstrap once)`);
  const sourceMd = fs.readFileSync(args.source,'utf8');
  const parsed = parsePlanSource(sourceMd);
  const state = loadJson(args.state, { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} });
  const mergedState = mergeState(parsed, state);
  deriveMarkers(parsed);
  saveJsonPretty(args.state, mergedState);

  // If requested, emit the original source file as the generated plan (preserving formatting),
  // but still generate the todo outputs and update state. Use --copy-source to enable.
  const sourceText = sourceMd;
  const checksum = sha1(sourceText);
  const sourceRel = path.relative(ROOT, args.source).replace(/\\/g, '/');
  const stateRel = path.relative(ROOT, args.state).replace(/\\/g, '/');
  let planMd;
  if (args.copySource) {
    const header = [];
    header.push('<!-- GENERATED_BY_SYNC_TODOS: true -->');
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`);
    header.push('');
    // Inject derived markers into the copied source so progress tracking
    // remains visible even when preserving original formatting.
    const injected = injectMarkersIntoSource(sourceText.replace(/\r\n/g, '\n'), parsed);
    planMd = header.join('\n') + injected;
  } else {
    planMd = renderPlanMd(parsed, args);
  }

  // Prepare todo MD (either copy or generated)
  let todoMd;
  if (args.copyTodo) {
    const header = [];
    header.push('<!-- GENERATED_BY_SYNC_TODOS: true -->');
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_CHECKSUM: ${checksum} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_SOURCE: ${sourceRel} -->`);
    header.push(`<!-- GENERATED_BY_SYNC_TODOS_STATE: ${stateRel} -->`);
    header.push('');
    todoMd = header.join('\n') + sourceText.replace(/\r\n/g, '\n');
  } else {
    todoMd = renderTodoMd(parsed);
  }

  // If requested, only print outputs to stdout (preview) and exit without writing files.
  if (args.printSource) {
    console.log(planMd.replace(/\n{3,}/g,'\n\n'));
    process.exit(0);
  }
  if (args.printTodo) {
    console.log(todoMd.replace(/\n{3,}/g,'\n\n'));
    process.exit(0);
  }

  writeUtf8(args.outPlan, planMd);
  writeUtf8(args.outTodoMd, todoMd);
  const todoJson = renderTodoJson(parsed);
  saveJsonPretty(args.outTodoJson, todoJson);
  if (!args.quiet){
    console.log('✅ Sync complete');
    console.log(' - Plan:', args.outPlan);
    console.log(' - Todo MD:', args.outTodoMd);
    console.log(' - Todo JSON:', args.outTodoJson);
    console.log(' - State:', args.state);
  }
}

try{ main(); } catch(err){ console.error('❌', err && err.message ? err.message : err); process.exit(1); }
