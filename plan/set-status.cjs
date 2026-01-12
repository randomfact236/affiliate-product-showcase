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

  FIX: Added backup/restore mechanism to ensure status changes persist even if generator overwrites them
*/

const fs = require('fs');
const path = require('path');
const child = require('child_process');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');
const STATE_PATH = path.join(PLAN_DIR, 'plan_state.json');
const GENERATED_MARKER = path.join(PLAN_DIR, '.generated_by');
const STATE_BACKUP_PATH = path.join(PLAN_DIR, 'plan_state.json.backup');

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

function restoreStateBackup() {
  if (fs.existsSync(STATE_BACKUP_PATH)) {
    try {
      fs.copyFileSync(STATE_BACKUP_PATH, STATE_PATH);
      console.log(`Restored state from ${path.relative(ROOT, STATE_BACKUP_PATH)}`);
    } catch (err) {
      console.warn('Warning: failed to restore state backup:', err && err.message ? err.message : err);
    }
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

  // Create backup before running generator
  const stateBefore = readState(STATE_PATH);
  
  // Validate that code exists in known codes or statusByCode
  stateBefore.knownCodes = stateBefore.knownCodes || [];
  if (!stateBefore.knownCodes.includes(code) && !stateBefore.statusByCode.hasOwnProperty(code)) {
    console.error(`Error: Code "${code}" not found in plan_state.json`);
    console.error('Please verify the code exists in plan/plan_state.json');
    process.exit(4);
  }
  
  writeState(STATE_BACKUP_PATH, stateBefore);
  
  // Update state with new status
  stateBefore.statusByCode = stateBefore.statusByCode || {};
  stateBefore.explicitStatusCodes = stateBefore.explicitStatusCodes || [];
  
  // Remove this code from explicit list if it's already there
  stateBefore.explicitStatusCodes = stateBefore.explicitStatusCodes.filter(c => c !== code);
  
  // Add code to explicit list
  stateBefore.explicitStatusCodes.push(code);
  
  // Set the status
  stateBefore.statusByCode[code] = status;
  
  // If setting to "completed", also mark all children as explicitly "completed"
  if (status === 'completed') {
    for (const existingCode of Object.keys(stateBefore.statusByCode)) {
      if (existingCode.startsWith(code + '.')) {
        stateBefore.statusByCode[existingCode] = 'completed';
        // Ensure child is also in explicit list
        if (!stateBefore.explicitStatusCodes.includes(existingCode)) {
          stateBefore.explicitStatusCodes.push(existingCode);
        }
        console.log(`Setting child ${existingCode} to ${status}`);
      }
    }
  }
  
  writeState(STATE_PATH, stateBefore);
  console.log(`Updated ${path.relative(ROOT, STATE_PATH)}: ${code} => ${status}`);

  // Run generator (which may overwrite our status change)
  runGenerator();

  // After generator runs, verify or restore our status change
  const stateAfter = readState(STATE_PATH);
  let needsUpdate = false;
  
  if (stateAfter.statusByCode[code] !== status) {
    console.log(`Generator changed status of ${code} to ${stateAfter.statusByCode[code]}, restoring to ${status}`);
    needsUpdate = true;
  }
  
  // If code is a parent topic, ensure all children inherit the status
  if (status === 'completed') {
    // Find all child codes that should be completed
    stateAfter.explicitStatusCodes = stateAfter.explicitStatusCodes || [];
    for (const childCode of Object.keys(stateAfter.statusByCode)) {
      if (childCode.startsWith(code + '.')) {
        if (stateAfter.statusByCode[childCode] !== 'completed') {
          console.log(`Setting child ${childCode} to ${status}`);
          stateAfter.statusByCode[childCode] = 'completed';
          needsUpdate = true;
        }
        // Ensure ALL children are in explicitStatusCodes, not just ones that were changed
        if (!stateAfter.explicitStatusCodes.includes(childCode)) {
          stateAfter.explicitStatusCodes.push(childCode);
          needsUpdate = true;
        }
      }
    }
  }
  
  if (needsUpdate) {
    writeState(STATE_PATH, stateAfter);
  }

  // After restoring state, update plan_todos.json to reflect the new status
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
      // Also update children to completed if parent is completed
      if (t && status === 'completed' && t.code.startsWith(code + '.')) {
        if (t.status !== 'completed') {
          t.status = 'completed';
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

  // Clean up backup only after all operations succeed
  if (fs.existsSync(STATE_BACKUP_PATH)) {
    try {
      fs.unlinkSync(STATE_BACKUP_PATH);
    } catch (err) {
      console.warn('Warning: failed to clean up backup file:', err && err.message ? err.message : err);
    }
  }

  console.log('Done. Generated files updated and staged (if git available).');
})();
