#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const filePath = path.join(process.cwd(), 'plan', 'plan_source.md');
if (!fs.existsSync(filePath)) {
  console.error('plan_source.md not found at', filePath);
  process.exit(2);
}

let src = fs.readFileSync(filePath, 'utf8');
const EMOJI = '⏳ ';

// Target lines starting with optional indent then `1.3.` number (e.g., '   1.3.1 ...')
const updated = src.replace(/^([ \t]*)(1\.3\.\d+)([.)]?\s*)(.*)$/gm, (m, indent, num, sep, rest) => {
  const trimmed = rest.trimStart();
  if (trimmed.startsWith(EMOJI)) return `${indent}${num}${sep}${rest}`;
  const withoutWip = trimmed.replace(/^(\[?WIP\]?[:\-\s]*)/i, '').trimStart();
  return `${indent}${num}${sep}${EMOJI}${withoutWip}`;
});

if (updated === src) {
  console.log('No changes required (no matching lines or already updated).');
  process.exit(0);
}

fs.writeFileSync(filePath, updated, 'utf8');
console.log('Inserted emoji marker into 1.3 subtopics in', filePath);
process.exit(0);
#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const filePath = path.join(process.cwd(), 'plan', 'plan_source.md');
if (!fs.existsSync(filePath)) {
  console.error('plan_source.md not found at', filePath);
  process.exit(2);
}

let src = fs.readFileSync(filePath, 'utf8');
#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const filePath = path.join(process.cwd(), 'plan', 'plan_source.md');
if (!fs.existsSync(filePath)) {
  console.error('plan_source.md not found at', filePath);
  process.exit(2);
}

let src = fs.readFileSync(filePath, 'utf8');
const EMOJI = '⏳ ';

// Target lines starting with optional indent then `1.3.` number (e.g., '   1.3.1 ...')
const updated = src.replace(/^([ \t]*)(1\.3\.\d+)([.)]?\s*)(.*)$/gm, (m, indent, num, sep, rest) => {
  const trimmed = rest.trimStart();
  if (trimmed.startsWith(EMOJI)) return `${indent}${num}${sep}${rest}`;
  const withoutWip = trimmed.replace(/^(\[?WIP\]?[:\-\s]*)/i, '').trimStart();
  return `${indent}${num}${sep}${EMOJI}${withoutWip}`;
});

if (updated === src) {
  console.log('No changes required (no matching lines or already updated).');
  process.exit(0);
}

fs.writeFileSync(filePath, updated, 'utf8');
console.log('Inserted emoji marker into 1.3 subtopics in', filePath);
process.exit(0);
