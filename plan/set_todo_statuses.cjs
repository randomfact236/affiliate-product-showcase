'use strict';
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const TODOS = path.join(ROOT, 'plan', 'plan_todos.json');

function load() {
  if (!fs.existsSync(TODOS)) throw new Error('plan_todos.json missing');
  return JSON.parse(fs.readFileSync(TODOS, 'utf8'));
}

function save(obj) {
  fs.writeFileSync(TODOS, JSON.stringify(obj, null, 2) + '\n', 'utf8');
}

function usage() {
  console.error('Usage: node plan/set_todo_statuses.cjs <json-mapping>');
  console.error('Example: node plan/set_todo_statuses.cjs "{\"1.2.140\":\"pending\",\"1.2.141\":\"pending\"}"');
  process.exit(2);
}

function validStatus(s) {
  return ['pending','in-progress','blocked','cancelled','completed'].includes(String(s));
}

function main() {
  const rawArgs = process.argv.slice(2);
  if (!rawArgs || rawArgs.length === 0) return usage();
  let mapping = null;
  // Try parse as single JSON arg
  if (rawArgs.length === 1) {
    try { mapping = JSON.parse(rawArgs[0]); } catch (e) { /* fallthrough */ }
  }
  // Otherwise parse as pairs like code=status
  if (!mapping) {
    mapping = {};
    for (const a of rawArgs) {
      const m = String(a).split('=');
      if (m.length !== 2) continue;
      mapping[m[0]] = m[1];
    }
    if (Object.keys(mapping).length === 0) return usage();
  }

  const doc = load();
  if (!Array.isArray(doc.todos)) throw new Error('Invalid plan_todos.json');

  let changed = false;
  for (const [code, status] of Object.entries(mapping)) {
    if (!validStatus(status)) throw new Error('Invalid status: ' + status);
    const found = doc.todos.find(t => t.code === code);
    if (!found) {
      console.warn('Code not found in todos:', code);
      continue;
    }
    if (found.status === status) {
      console.log('No change for', code, found.status);
      continue;
    }
    found.status = status;
    // clear derived/marker — sync script will recalc
    delete found.derivedStatus;
    delete found.marker;
    changed = true;
    console.log('Updated', code, '->', status);
  }

  if (changed) {
    save(doc);
    console.log('Saved', TODOS);
  } else {
    console.log('No changes written');
  }
}

main();
