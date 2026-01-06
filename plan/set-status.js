#!/usr/bin/env node
/*
  plan/set-status.js
  Usage: node plan/set-status.js <code> <status> [--commit] [--push]
  Example: node plan/set-status.js 1.1.3 in-progress --commit --push

  Updates plan/plan_state.json -> statusByCode[code] = status,
  runs the sync script, and optionally commits & pushes the generated files.
*/

const fs = require('fs');
const path = require('path');
const child = require('child_process');

function usage() {
  console.log('Usage: node plan/set-status.js <code> <status> [--commit] [--push]');
  process.exit(1);
}

const argv = process.argv.slice(2);
if (argv.length < 2) usage();

const code = argv[0];
const status = argv[1];
const doCommit = argv.includes('--commit');
const doPush = argv.includes('--push');

const repoRoot = path.resolve(__dirname);
const statePath = path.join(repoRoot, 'plan_state.json');

if (!fs.existsSync(statePath)) {
  console.error('plan_state.json not found at', statePath);
  process.exit(2);
}

const stateRaw = fs.readFileSync(statePath, 'utf8');
let state;
try {
  state = JSON.parse(stateRaw);
} catch (err) {
  console.error('Failed to parse plan_state.json:', err.message);
  process.exit(2);
}

state.statusByCode = state.statusByCode || {};
state.statusByCode[code] = status;

fs.writeFileSync(statePath, JSON.stringify(state, null, 2) + '\n', 'utf8');
console.log(`Updated ${path.relative(process.cwd(), statePath)}: ${code} -> ${status}`);

// Run sync script
try {
  child.execSync('node plan/plan_sync_todos.cjs', { stdio: 'inherit' });
} catch (err) {
  console.error('plan sync failed:', err.message);
  process.exit(3);
}

if (doCommit) {
  try {
    child.execSync('git add plan/plan_state.json plan/plan_sync.md plan/plan_sync_todo.md plan/plan_todos.json plan/plan_state.json', { stdio: 'inherit' });
    child.execSync(`git commit -m "chore(plan): set ${code}=${status} via set-status.js"`, { stdio: 'inherit' });
    console.log('Committed changes');
  } catch (err) {
    console.error('Git commit failed:', err.message);
  }
}

if (doPush) {
  try {
    child.execSync('git push', { stdio: 'inherit' });
    console.log('Pushed to remote');
  } catch (err) {
    console.error('Git push failed:', err.message);
  }
}

console.log('Done.');
