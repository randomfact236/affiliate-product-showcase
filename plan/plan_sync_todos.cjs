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
  // Find first line that starts with a top-level numeric code (level 1)
  let headerEnd = allLines.length;
  for (let i = 0; i < allLines.length; i++) {
    const line = String(allLines[i] || '').trim(); if (!line) continue;
    const stripped = line.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
    const code = extractCode(stripped);
    if (code && getLevel(code) === 1) { headerEnd = i; break; }
  }
  const headerLines = allLines.slice(0, headerEnd);
  const contentLines = allLines.slice(headerEnd);

  // Collect nodes indexed by numeric code
  const nodeMap = Object.create(null);
  const nodesInOrder = [];
  for (const raw of contentLines) {
    const line = String(raw || ''); const trimmed = line.trim(); if (!trimmed) continue;
    // Accept heading forms like '### 1.1.1 Title' or inline forms like '- 1.1.1 Title' or '1.1.1 Title'
    let m = line.match(/^\s*#{1,}\s*(\d+(?:\.\d+)*)\s*(?:[—–\-]\s*)?(.*)$/);
    let code = null; let title = null; let rawLine = line.trim();
    if (m) { code = m[1]; title = (m[2] || '').trim(); }
    else {
      const stripped = trimmed.replace(/^\s*(?:>\s*)?(?:[-*+]\s+)+/, '').trim();
      code = extractCode(stripped);
      if (!code) continue;
      const t = stripped.replace(new RegExp('^' + code.replace(/\./g,'\\.') + '\\s*'), '');
      title = t.trim();
      rawLine = line.trim();
    }
    const node = { code, title: title || '', rawLine, items: [], status: 'pending', marker: '' };
    nodeMap[code] = node; nodesInOrder.push(code);
  }

  // Assemble tree purely by numeric parent relationships
  const roots = [];
  for (const code of nodesInOrder) {
    const node = nodeMap[code]; if (!node) continue;
    const parentCode = getParentCode(code);
    if (parentCode && nodeMap[parentCode]) {
      nodeMap[parentCode].items.push(node);
    } else {
      roots.push(node);
    }
  }

  // Numeric sort comparator by code segments
  function cmpCodes(a, b) {
    const ap = a.split('.').map(n => parseInt(n, 10) || 0);
    const bp = b.split('.').map(n => parseInt(n, 10) || 0);
    for (let i = 0; i < Math.max(ap.length, bp.length); i++) {
      const av = ap[i] || 0; const bv = bp[i] || 0; if (av !== bv) return av - bv;
    }
    return 0;
  }
  function sortTree(nodes) {
    nodes.sort((A,B)=>cmpCodes(A.code, B.code));
    for (const n of nodes) if (n.items && n.items.length) sortTree(n.items);
  }
  sortTree(roots);

  return { headerLines, steps: roots };
}

function walkPlan(structure, visitor) {
  function walk(node) {
    if (!node) return;
    visitor(node);
    if (node.items && node.items.length) for (const c of node.items) walk(c);
  }
  for (const root of (structure.steps || [])) walk(root);
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
    const children = node.items || [];
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
    node.derivedStatus = derivedStatus; node.marker = marker;
    return { derivedStatus, marker };
  }
  for (const root of (structure.steps || [])) deriveNode(root, false);
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

  function renderNode(node) {
    const level = getLevel(node.code);
    const hashes = '#'.repeat(Math.max(1, level));
    const marker = node.marker ? `${node.marker} ` : '';
    out.push(`${hashes} ${marker}${node.code} ${node.title}`.trimEnd());
    out.push('');
    if (node.items && node.items.length) for (const c of node.items) renderNode(c);
  }

  for (const root of (structure.steps || [])) renderNode(root);
  return out.join('\n').replace(/\n{3,}/g, '\n\n') + '\n';
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

function renderTodoMd(structure) {
  const out = [];
  out.push('# Synced Todo List (Flattened)');
  out.push('');
  out.push('Legend: ✅ completed · ❌ cancelled · ⛔ blocked · ⏳ in-progress');
  out.push('');

  // Build a map from code -> node so we can robustly find children by code
  // even if the parser didn't populate `items` arrays consistently.
  const codeMap = Object.create(null);
  walkPlan(structure, node => { if (node && node.code) codeMap[node.code] = node; });
  try { writeUtf8(path.join(PLAN_DIR,'code_map_keys.txt'), Object.keys(codeMap).sort().join('\n') + '\n'); } catch(e) {}
  try { writeUtf8(path.join(PLAN_DIR,'node_dump_1_1_5.json'), JSON.stringify({ '1.1.5': codeMap['1.1.5']||null, '1.1.5.1': codeMap['1.1.5.1']||null }, null, 2)); } catch(e) {}
  const printedCodes = new Set();

  function childrenOf(code) {
    const list = [];
    for (const k of Object.keys(codeMap)) {
      if (getParentCode(k) === String(code) && codeMap[k].kind === 'item') list.push(codeMap[k]);
    }
    list.sort((a,b)=>{ const aNum = parseInt(String(a.code).split('.').pop(),10)||0; const bNum = parseInt(String(b.code).split('.').pop(),10)||0; return aNum-bNum; });
    return list;
  }

  function renderItemsByCode(parentCode, indentLevel) {
    const base = String(parentCode);
    const baseDepth = base.split('.').length;
    const descendants = Object.keys(codeMap).filter(k => k.indexOf(base + '.') === 0 && codeMap[k].kind === 'item');
    try { if (base === '1.1') writeUtf8(path.join(PLAN_DIR, 'descendants_1_1.txt'), descendants.join('\n')+'\n'); } catch(e) {}
    if (!descendants.length) return;
    // Sort by numeric segments to keep order
    descendants.sort((a, b) => {
      const aa = a.split('.').map(n => parseInt(n, 10) || 0);
      const bb = b.split('.').map(n => parseInt(n, 10) || 0);
      for (let i = 0; i < Math.max(aa.length, bb.length); i++) {
        const av = aa[i] || 0; const bv = bb[i] || 0; if (av !== bv) return av - bv;
      }
      return 0;
    });
    for (const code of descendants) {
      if (printedCodes.has(code)) continue;
      const node = codeMap[code];
      const depth = code.split('.').length - baseDepth;
      const pad = '  '.repeat(Math.max(0, indentLevel + depth - 1));
      const marker = node.marker ? `${node.marker} ` : '';
      out.push(`${pad}- ${marker}${node.code} ${node.title}`.trimEnd());
      printedCodes.add(code);
    }
  }

  // Prefer rendering using the parsed `items` arrays (recursive). This is
  // more faithful to the source structure when the parser attached children
  // to parents. Fall back to `renderItemsByCode` if `items` is empty.
  function renderNodeList(items, indentLevel) {
    if (!items || items.length === 0) return;
    for (const node of items) {
      printedCodes.add(node.code);
      const pad = '  '.repeat(Math.max(0, indentLevel));
      const marker = node.marker ? `${node.marker} ` : '';
      out.push(`${pad}- ${marker}${node.code} ${node.title}`.trimEnd());
      renderNodeList(node.items, indentLevel + 1);
    }
  }

  for (const step of structure.steps) {
    const stepMarker = step.marker ? `${step.marker} ` : '';
    // Preserve the original step heading text if available. Strip leading
    // heading hashes so the flattened list remains a bullet list, but keep
    // any badge/emoji and trailing text verbatim to avoid duplication.
    // Build a canonical flattened step heading from the parsed title.
    // Strip any accidental leading 'Step N —' fragments in the parsed title
    // so we don't duplicate them when constructing the bullet.
    const titleVal = String(step.title || '');
    const titleClean = titleVal.replace(new RegExp(`^Step\\s+${step.code}\\s*[—–\\-]\\s*`, 'i'), '').trim();
    const raw = `Step ${step.code} — ${titleClean}`;
    out.push(`- ${stepMarker}${raw}`.trimEnd());
    // Render any items directly under the step, preferring the parsed
    // `items` arrays (which preserve original nesting); fall back to
    // code-based descendant rendering if missing.
    if (step.items && step.items.length) renderNodeList(step.items, 1);
    // Always attempt to render code-based descendants to catch any items
    // that were parsed but not attached in the `items` arrays.
    renderItemsByCode(step.code, 1);
    for (const topic of step.topics) {
      const topicMarker = topic.marker ? `${topic.marker} ` : '';
      out.push(`  - ${topicMarker}${topic.code} ${topic.title}`.trimEnd());
      if (topic.items && topic.items.length) renderNodeList(topic.items, 2);
      renderItemsByCode(topic.code, 2);
    }
  }

  out.push('');
  // Dump the raw flattened output before post-processing for debugging.
  try { writeUtf8(path.join(PLAN_DIR,'todo_before_postprocess.md'), out.join('\n') + '\n'); } catch (e) {}
  // Post-process: clean any leftover stray 'hat' tokens and normalize spacing.
  return out.join('\n')
    .replace(/\n{3,}/g,'\n\n')
    .replace(/\n[ \t]*hat[ \t]*-+/gi,'\n  -')
    .replace(/\n[ \t]*hat[ \t]*\n/gi,'\n')
    .replace(/\n{2,}/g,'\n\n') + '\n';
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
  try { writeUtf8(path.join(PLAN_DIR,'parsed_step1.json'), JSON.stringify(parsed.steps[0] || null, null, 2)); } catch(e) {}
  try { const t0 = parsed.steps[0] && parsed.steps[0].topics && parsed.steps[0].topics[0] ? parsed.steps[0].topics[0] : null; writeUtf8(path.join(PLAN_DIR,'parsed_step1_topic1_items_codes.txt'), t0 && t0.items ? t0.items.map(i=>i.code).join('\n')+'\n' : ''); } catch(e) {}
  // DEBUG: print parsed Step 1 structure to stderr for inspection.
  try {
    const step1 = parsed.steps.find(s => s.code === '1');
    console.error('PARSED_STEP1_DEBUG:', JSON.stringify(step1 || { steps: parsed.steps.slice(0,3) }, null, 2));
  } catch (e) { console.error('PARSE_DEBUG_ERROR', e && e.message); }
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

  // Final pass: collapse accidental duplicated 'Step N — ... Step N — ...' patterns
  // and remove stray 'hat' tokens in the generated outputs.
  for (const step of parsed.steps) {
    const token = `Step ${step.code}`;
    const dupRe = new RegExp(token + "\\s*[—–\\-]\\s*([^\\n]*?)" + token + "\\s*[—–\\-]\\s*([^\\n]*)", 'g');
    planMd = planMd.replace(dupRe, `${token} — $1$2`);
  }
  planMd = planMd.replace(/\n[ \t]*hat[ \t]*/g, '\n');
  todoMd = todoMd.replace(/\n[ \t]*hat[ \t]*/g, '\n');

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

