#!/usr/bin/env node
/*
  plan/set-status.cjs

  Usage:
    node plan/set-status.cjs 1.1.7.1 start
    node plan/set-status.cjs "1.1.7.1- start"

  Behavior:
    - Updates `plan/plan_state.json` by setting the provided code to the mapped status
    - Runs `node plan/plan_sync_todos.cjs` to regenerate outputs
    - Writes `plan/.generated_by` with `generated-by: plan-generator`
    - Stages the generated files so the pre-commit hook will allow the commit

*/

const fs = require('fs');
const path = require('path');
const child = require('child_process');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');
const STATE_PATH = path.join(PLAN_DIR, 'plan_state.json');
const GENERATED_MARKER = path.join(PLAN_DIR, '.generated_by');

function usageAndExit() {
  console.error('Usage: node plan/set-status.cjs <code> <action>');
  console.error('  Examples:');
  console.error('    node plan/set-status.cjs 1.1.7.1 start');
  console.error('    node plan/set-status.cjs "1.1.7.1- start"');
  process.exit(2);
}

function parseArgs(argv) {
  if (argv.length < 3) usageAndExit();
  // Support two forms: "code action" or single string like "1.1.7.1- start"
  let raw = argv.slice(2).join(' ').trim();
  let code, action;
  const m = raw.match(/^([0-9]+(?:\.[0-9]+)*)\s*[-–—]?\s*(\w+)$/i);
  if (m) {
    code = m[1];
    action = m[2];
  } else {
    // fallback: assume first token is code, second is action
    const parts = argv.slice(2).filter(Boolean);
    if (parts.length < 2) usageAndExit();
    code = parts[0];
    action = parts[1];
  }
  return { code, action: action.toLowerCase() };
}

function mapActionToStatus(action) {
  if (!action) return null;
  if (['start', 'started', 'in-progress', 'progress', 'doing'].includes(action)) return 'in-progress';
  if (['done', 'completed', 'complete', 'finish', 'finished'].includes(action)) return 'completed';
  // 'blocked' and 'cancelled' statuses intentionally removed — unsupported.
  return null;
}

function readState(p) {
  if (!fs.existsSync(p)) return { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} };
  const raw = fs.readFileSync(p, 'utf8');
  if (!raw.trim()) return { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} };
  return JSON.parse(raw);
}

function writeState(p, obj) {
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, JSON.stringify(obj, null, 2) + '\n', 'utf8');
}

function runGenerator() {
  console.log('Running plan generator...');
  try {
    child.execFileSync(process.execPath, [path.join(PLAN_DIR, 'plan_sync_todos.cjs')], { stdio: 'inherit' });
  } catch (err) {
    console.error('Generator failed:', err && err.message ? err.message : err);
    process.exit(3);
  }
}

function readTodosJson(p) {
  if (!fs.existsSync(p)) return null;
  try {
    const raw = fs.readFileSync(p, 'utf8');
    return raw.trim() ? JSON.parse(raw) : null;
  } catch (err) {
    console.warn('Warning: failed to read plan_todos.json:', err && err.message ? err.message : err);
    return null;
  }
}

function writeTodosJson(p, obj) {
  try {
    fs.mkdirSync(path.dirname(p), { recursive: true });
    fs.writeFileSync(p, JSON.stringify(obj, null, 2) + '\n', 'utf8');
  } catch (err) {
    console.warn('Warning: failed to write plan_todos.json:', err && err.message ? err.message : err);
  }
}

function writeMarker() {
  const content = 'generated-by: plan-generator\n';
  fs.writeFileSync(GENERATED_MARKER, content, 'utf8');
}

function gitAdd(files) {
  try {
    child.execFileSync('git', ['add', ...files], { stdio: 'inherit' });
  } catch (err) {
    console.warn('Warning: git add failed or git not available. You may need to stage files manually.');
  }
}

(function main(){
  const { code, action } = parseArgs(process.argv);
  const status = mapActionToStatus(action);
  if (!status) {
    console.error('Unknown action:', action);
    usageAndExit();
  }

  const state = readState(STATE_PATH);
  state.statusByCode = state.statusByCode || {};
  state.statusByCode[code] = status;
  // ensure knownCodes persists if present
  if (!Array.isArray(state.knownCodes)) state.knownCodes = state.knownCodes || [];
  writeState(STATE_PATH, state);
  console.log(`Updated ${path.relative(ROOT, STATE_PATH)}: ${code} => ${status}`);

  // Run generator to refresh outputs and state-derived markers
  runGenerator();

  // After the generator runs, update plan/plan_todos.json to reflect the new status for the changed code.
  // This keeps the flattened JSON in sync for consumers that read it directly.
  const TODOS_PATH = path.join(PLAN_DIR, 'plan_todos.json');
  const todosJson = readTodosJson(TODOS_PATH);
  if (todosJson && Array.isArray(todosJson.todos)) {
    let updated = 0;
    todosJson.todos = todosJson.todos.map(t => {
      if (t && t.code === code) {
        if (t.status !== status) {
          t.status = status;
          updated += 1;
        }
      }
      return t;
    });
    if (updated > 0) {
      writeTodosJson(TODOS_PATH, todosJson);
      console.log(`Updated ${path.relative(ROOT, TODOS_PATH)}: ${updated} entries changed`);
    }
  }

  // Write marker and stage generated files for commit
  writeMarker();
  const filesToAdd = [path.relative(ROOT, GENERATED_MARKER), 'plan/plan_sync.md', 'plan/plan_sync_todo.md', 'plan/plan_todos.json', path.relative(ROOT, STATE_PATH)];
  gitAdd(filesToAdd);

  console.log('Done. Generated files updated and staged (if git available).');
})();
