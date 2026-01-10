#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

// Usage: node scripts/mark-task-complete.js --code=1.2.3

function parseArg(name) {
  const prefix = `--${name}=`;
  const arg = process.argv.slice(2).find(a => a.startsWith(prefix));
  return arg ? arg.slice(prefix.length) : null;
}

const code = parseArg('code');
const title = parseArg('title');
if (!code && !title) {
  console.error('Usage: node scripts/mark-task-complete.js --code=1.1.2  OR --title="Some title"');
  process.exit(2);
}

const filePath = path.join(__dirname, '..', 'plan', 'plan_todos.json');
if (!fs.existsSync(filePath)) {
  console.error('plan_todos.json not found at', filePath);
  process.exit(1);
}

const raw = fs.readFileSync(filePath, 'utf8');
let data;
try {
  data = JSON.parse(raw);
} catch (err) {
  console.error('Failed to parse JSON:', err.message);
  process.exit(1);
}

const todos = data.todos || [];
const match = todos.find(t => (code && t.code === code) || (title && t.title === title));
if (!match) {
  console.error('No todo found for', code || title);
  process.exit(1);
}

match.status = 'completed';
match.derivedStatus = 'completed';
match.marker = '✅';
match.completedAt = new Date().toISOString();

// backup
try {
  fs.copyFileSync(filePath, filePath + '.bak');
} catch (err) {
  // ignore backup errors
}

fs.writeFileSync(filePath, JSON.stringify(data, null, 2) + '\n', 'utf8');
console.log(`Marked todo ${match.code} - ${match.title} as completed (${match.marker})`);
