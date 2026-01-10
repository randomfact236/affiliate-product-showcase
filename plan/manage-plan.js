#!/usr/bin/env node
/*
  plan/manage-plan.js

  Single entrypoint for plan workflow.

  Commands:
    node plan/manage-plan.js set <code> <pending|in-progress|completed>
    node plan/manage-plan.js regenerate
    node plan/manage-plan.js validate [--staged]
*/

const fs = require('fs');
const path = require('path');
const os = require('os');
const cp = require('child_process');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');

const PATHS = {
  source: path.join(PLAN_DIR, 'plan_source.md'),
  state: path.join(PLAN_DIR, 'plan_state.json'),
  outPlan: path.join(PLAN_DIR, 'plan_sync.md'),
  outTodoMd: path.join(PLAN_DIR, 'plan_sync_todo.md'),
  outTodoJson: path.join(PLAN_DIR, 'plan_todos.json')
};

const GENERATED_FILES = ['plan/plan_sync.md', 'plan/plan_sync_todo.md', 'plan/plan_todos.json'];
const SOURCE_FILES = ['plan/plan_source.md', 'plan/plan_state.json'];
const VALID_STATUSES = new Set(['pending', 'in-progress', 'completed']);

function exec(cmd, args, opts = {}) {
  return cp.execFileSync(cmd, args, { cwd: ROOT, stdio: 'pipe', ...opts }).toString('utf8');
}

function execInherit(cmd, args, opts = {}) {
  cp.execFileSync(cmd, args, { cwd: ROOT, stdio: 'inherit', ...opts });
}

function die(msg, code = 1) {
  console.error(msg);
  process.exit(code);
}

function parseArgs(argv) {
  const args = argv.slice(2);
  const flags = new Set(args.filter(a => a.startsWith('--')));
  const positional = args.filter(a => !a.startsWith('--'));
  return { positional, flags };
}

function readJson(p) {
  if (!fs.existsSync(p)) return null;
  const raw = fs.readFileSync(p, 'utf8');
  return raw.trim() ? JSON.parse(raw) : null;
}

function writeJson(p, obj) {
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, JSON.stringify(obj, null, 2) + '\n', 'utf8');
}

function normalizeStatus(input) {
  const s = String(input || '').trim().toLowerCase();
  if (s === 'todo' || s === 'pending') return 'pending';
  if (s === 'doing' || s === 'inprogress' || s === 'in-progress' || s === 'progress') return 'in-progress';
  if (s === 'done' || s === 'complete' || s === 'completed') return 'completed';
  return null;
}

function listChangedPaths({ stagedOnly }) {
  const cmdArgs = stagedOnly ? ['diff', '--cached', '--name-only'] : ['diff', '--name-only'];
  const out = exec('git', cmdArgs, { stdio: 'pipe' }).trim();
  if (!out) return [];
  return out.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
}

function hasAny(paths, set) {
  return paths.some(p => set.has(p));
}

function stage(files) {
  try {
    execInherit('git', ['add', ...files]);
  } catch (e) {
    die('ERROR: git add failed (is this a git repo?)');
  }
}

function runGenerator() {
  execInherit(process.execPath, [path.join('plan', 'plan_sync_todos.cjs')]);
}

function runNormalizer() {
  // Keep this step, but normalize script should be safe.
  execInherit(process.execPath, [path.join('plan', 'normalize_generated.js')]);
}

function showChanges() {
  try {
    const stat = exec('git', ['--no-pager', 'diff', '--stat']);
    const s = stat.trim();
    if (s) console.log(s);
  } catch (_) {
    // ignore
  }
}

function validateNoManualEdits({ stagedOnly }) {
  const changed = listChangedPaths({ stagedOnly });
  const changedSet = new Set(changed);
  const generatedTouched = hasAny(GENERATED_FILES, changedSet);
  const sourceTouched = hasAny(SOURCE_FILES, changedSet);

  if (generatedTouched && !sourceTouched) {
    die(
      [
        'ERROR: Generated plan files changed without source-of-truth changes.',
        'Edit plan/plan_source.md or plan/plan_state.json and run:',
        '  node plan/manage-plan.js regenerate',
        ''
      ].join('\n'),
      2
    );
  }
}

function readText(p) {
  if (!fs.existsSync(p)) return '';
  return fs.readFileSync(p, 'utf8').replace(/\r\n/g, '\n');
}

function stableStringifyJsonText(text) {
  const obj = JSON.parse(String(text || '') || '{}');
  const normalize = (v) => {
    if (Array.isArray(v)) return v.map(normalize);
    if (v && typeof v === 'object') {
      const out = {};
      for (const k of Object.keys(v).sort()) out[k] = normalize(v[k]);
      return out;
    }
    return v;
  };
  return JSON.stringify(normalize(obj));
}

function stableStringifyPlanStateText(text) {
  const obj = JSON.parse(String(text || '') || '{}');
  // `lastSyncAt` / `lastUpdated` can legitimately differ based on generator
  // output comparison and is not a useful signal for drift validation.
  delete obj.lastSyncAt;
  delete obj.lastUpdated;
  return stableStringifyJsonText(JSON.stringify(obj));
}

function stripGeneratedHeader(md) {
  const s = String(md || '');
  // Remove the generated header block (which includes a state path) so
  // validate can compare semantic content even when using a temp state file.
  return s.replace(/^(<!--\s*GENERATED_BY_SYNC_TODOS:[\s\S]*?\n\n)/, '');
}

function ensureDir(p) {
  fs.mkdirSync(p, { recursive: true });
}

function copyIfExists(src, dst) {
  if (!fs.existsSync(src)) return;
  ensureDir(path.dirname(dst));
  fs.copyFileSync(src, dst);
}

function validateNoDrift() {
  // Generate into a temp directory (seeded with current outputs) so we can detect drift
  // without touching the working tree.
  const tmpBase = fs.mkdtempSync(path.join(os.tmpdir(), 'plan-validate-'));
  const tmpPlan = path.join(tmpBase, 'plan_sync.md');
  const tmpTodoMd = path.join(tmpBase, 'plan_sync_todo.md');
  const tmpTodoJson = path.join(tmpBase, 'plan_todos.json');
  const tmpState = path.join(tmpBase, 'plan_state.json');

  // Seed temp outputs so generator's lastSyncAt logic doesn't churn.
  copyIfExists(PATHS.outPlan, tmpPlan);
  copyIfExists(PATHS.outTodoMd, tmpTodoMd);
  copyIfExists(PATHS.outTodoJson, tmpTodoJson);
  copyIfExists(PATHS.state, tmpState);

  const args = [
    path.join('plan', 'plan_sync_todos.cjs'),
    '--source', PATHS.source,
    '--state', tmpState,
    '--out-plan', tmpPlan,
    '--out-todo-md', tmpTodoMd,
    '--out-todo-json', tmpTodoJson,
    '--quiet'
  ];

  cp.execFileSync(process.execPath, args, { cwd: ROOT, stdio: 'ignore' });

  const curPlan = readText(PATHS.outPlan);
  const curTodoMd = readText(PATHS.outTodoMd);
  const curTodoJson = readText(PATHS.outTodoJson);
  const curState = readText(PATHS.state);

  const expPlan = readText(tmpPlan);
  const expTodoMd = readText(tmpTodoMd);
  const expTodoJson = readText(tmpTodoJson);
  const expState = readText(tmpState);

  const diffs = [];
  if (stripGeneratedHeader(curPlan) !== stripGeneratedHeader(expPlan)) diffs.push('plan/plan_sync.md');
  if (stripGeneratedHeader(curTodoMd) !== stripGeneratedHeader(expTodoMd)) diffs.push('plan/plan_sync_todo.md');
  if (stableStringifyJsonText(curTodoJson) !== stableStringifyJsonText(expTodoJson)) diffs.push('plan/plan_todos.json');
  if (stableStringifyPlanStateText(curState) !== stableStringifyPlanStateText(expState)) diffs.push('plan/plan_state.json');

  if (diffs.length) {
    die(
      [
        'ERROR: Plan files have drift from generator output.',
        'Run:',
        '  node plan/manage-plan.js regenerate',
        'Drifted files:',
        ...diffs.map(f => ' - ' + f),
        ''
      ].join('\n'),
      2
    );
  }
}

function cmdSet(code, status) {
  if (!code || !/^\d+(?:\.\d+)*$/.test(code)) die('Usage: node plan/manage-plan.js set <code> <status>', 2);
  const normalized = normalizeStatus(status);
  if (!normalized || !VALID_STATUSES.has(normalized)) die('Status must be: pending | in-progress | completed', 2);

  const state = readJson(PATHS.state) || { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} };
  state.statusByCode = state.statusByCode || {};
  state.statusByCode[code] = normalized;
  writeJson(PATHS.state, state);

  // Also update the flattened todos JSON so the generator does not override
  // the desired status. This keeps plan_state.json authoritative when using
  // the `set` command.
  try {
    const todosRaw = readText(PATHS.outTodoJson) || '';
    const todosObj = todosRaw ? JSON.parse(todosRaw) : { generatedBy: 'plan/plan_sync_todos.cjs', todos: [] };
    todosObj.todos = todosObj.todos || [];
    let found = false;
    for (const t of todosObj.todos) {
      if (t && t.code === code) {
        t.status = normalized;
        found = true;
        break;
      }
    }
    if (!found) {
      todosObj.todos.push({ code, kind: 'item', title: '', status: normalized, derivedStatus: normalized, marker: '' });
    }
    fs.writeFileSync(PATHS.outTodoJson, JSON.stringify(todosObj, null, 2) + '\n', 'utf8');
  } catch (e) {
    // ignore — best effort update
  }

  runGenerator();
  runNormalizer();
  showChanges();

  stage(['plan/plan_state.json', ...GENERATED_FILES]);
}

function cmdRegenerate() {
  runGenerator();
  runNormalizer();
  showChanges();
  stage(['plan/plan_state.json', ...GENERATED_FILES]);
}

function cmdValidate(flags) {
  const stagedOnly = flags.has('--staged');
  validateNoManualEdits({ stagedOnly });
  validateNoDrift();
  console.log('✅ Plan workflow valid (no drift, no manual edits).');
}

(function main() {
  const { positional, flags } = parseArgs(process.argv);
  const [cmd, a, b] = positional;
  if (!cmd) {
    die(
      [
        'Usage:',
        '  node plan/manage-plan.js set <code> <pending|in-progress|completed>',
        '  node plan/manage-plan.js regenerate',
        '  node plan/manage-plan.js validate [--staged]'
      ].join('\n'),
      2
    );
  }

  if (!fs.existsSync(PATHS.source)) die('Missing plan/plan_source.md', 2);

  if (cmd === 'set') return cmdSet(a, b);
  if (cmd === 'regenerate') return cmdRegenerate();
  if (cmd === 'validate') return cmdValidate(flags);
  die(`Unknown command: ${cmd}`, 2);
})();
