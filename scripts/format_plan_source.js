#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, '..', 'plan', 'plan_source.md');
let content = fs.readFileSync(filePath, 'utf8');

function format(text) {
  const lines = text.split(/\r?\n/);
  const out = lines.map((line) => {
    // convert '#### ' headings to 3-space indented bullets
    if (/^\s*####\s+/.test(line)) {
      const rest = line.replace(/^\s*####\s+/, '').trim();
      return '   - ' + rest;
    }

    // ensure lines like '- 1.1.1.1' have exactly 3 spaces before the '- '
    if (/^\s*-\s+\d+\.\d+\.\d+/.test(line) || /^\s*-\s+\d+\.\d+/.test(line)) {
      const rest = line.replace(/^\s*-\s+/, '').trim();
      return '   - ' + rest;
    }

    return line;
  });
  return out.join('\n');
}

const formatted = format(content);
if (process.argv.includes('--check')) {
  if (formatted !== content) {
    console.error('plan_source.md is not formatted. Run the formatter to fix it.');
    process.exit(1);
  }
  console.log('plan_source.md formatting OK');
  process.exit(0);
} else {
  if (formatted !== content) {
    fs.writeFileSync(filePath, formatted, 'utf8');
    console.log('plan_source.md formatted and written.');
  } else {
    console.log('plan_source.md already formatted.');
  }
}
