const fs = require('fs');
const path = require('path');

const filePath = path.join(process.cwd(), 'plan', 'plan_sync.md');
if (!fs.existsSync(filePath)) {
  console.error('plan_sync.md not found at', filePath);
  process.exit(2);
}

const src = fs.readFileSync(filePath, 'utf8');
const EMOJI = '⏳';

// For each line that contains a top-level 1.3 subtopic (e.g. '   1.3.1 ...'),
// ensure the emoji appears immediately before the number: '   ⏳ 1.3.1 ...'.
// Also handle cases where emoji appears after the number or is already present.
const updated = src.replace(/^([ \t]*)(?:⏳\s*)?(1\.3\.\d+)(?:\s*⏳\s*)?(?:\s*)(.*)$/gm, (m, indent, num, rest) => {
  const restTrim = rest.replace(/^\s+/, '');
  return `${indent}${EMOJI} ${num}${restTrim ? ' ' + restTrim : ''}`;
});

if (updated === src) {
  console.log('No changes required (already prefixed).');
  process.exit(0);
}

fs.writeFileSync(filePath, updated, 'utf8');
console.log('Prefixed ⏳ before 1.3 subtopic numbers in', filePath);
process.exit(0);
