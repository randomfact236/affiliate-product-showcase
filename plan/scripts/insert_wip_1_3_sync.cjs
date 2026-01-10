#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const filePath = path.join(process.cwd(), 'plan', 'plan_sync.md');
if (!fs.existsSync(filePath)) {
  console.error('plan_sync.md not found at', filePath);
  process.exit(2);
}

let src = fs.readFileSync(filePath, 'utf8');
const EMOJI = '⏳ ';

const updated = src.replace(/^([ \t]*)(1\.3\.\d+)([\s\S]*?)$/gm, (m, indent, num, rest) => {
  // Only target lines that start with the 1.3.x number and don't already contain the emoji
  if (m.includes(EMOJI)) return m;
  // Remove any leading 'WIP' markers from the rest of the line
  const lineRest = rest.replace(/^\s*/, '');
  const withoutWip = lineRest.replace(/^(\[?WIP\]?[:\-–—\s]*)/i, '').trimStart();
  return `${indent}${EMOJI}${num} ${withoutWip}`;
});

if (updated === src) {
  console.log('No changes required (no matching lines or already updated).');
  process.exit(0);
}

fs.writeFileSync(filePath, updated, 'utf8');
console.log('Inserted emoji marker into 1.3 subtopics in', filePath);
process.exit(0);
