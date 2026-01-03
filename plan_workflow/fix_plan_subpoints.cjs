#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const inPath = process.argv[2] || path.join(root, 'plan_workflow', 'plan_workflow_sync_root.md');
const outPath = process.argv[3] || path.join(root, 'plan_workflow', 'plan_workflow_sync_fixed.md');

let txt = fs.readFileSync(inPath, 'utf8');
const lines = txt.split(/\r?\n/);

// Identify sections by top-level headers like "## 1.12 ..." — capture the major step number (first number)
const headerRe = /^##\s+(\d+)(?:\.\d+)?\b/;
const numericRe = /^\s*([0-9]+(?:\.[0-9]+)+)\b/; // full numeric id, e.g. 1.12.1 or 1.2.40

const sections = [];
let cur = null;
for (let i = 0; i < lines.length; i++) {
  const line = lines[i];
  const m = line.match(headerRe);
  if (m) {
    if (cur) sections.push(cur);
    cur = { headerLine: line, stepNum: parseInt(m[1], 10), lines: [] };
  } else {
    if (!cur) cur = { headerLine: null, stepNum: null, lines: [] };
    cur.lines.push(line);
  }
}
if (cur) sections.push(cur);

// Collect misplaced numeric lines keyed by their actual major step number
const misplaced = {};
for (const sec of sections) {
  const kept = [];
  for (const l of sec.lines) {
    const mm = l.match(numericRe);
    if (mm && sec.stepNum != null) {
      const full = mm[1];
      const major = parseInt(full.split('.')[0], 10);
      if (major !== sec.stepNum) {
        if (!misplaced[major]) misplaced[major] = [];
        misplaced[major].push(l.trim());
        continue; // drop from current section
      }
    }
    kept.push(l);
  }
  sec.lines = kept;
}

// Attach misplaced lines to their correct sections, then sort & dedupe numeric lines
for (const sec of sections) {
  const key = sec.stepNum;
  if (key == null) continue;
  const extra = misplaced[key];
  if (!extra || extra.length === 0) continue;

  // Extract existing numeric lines in this section
  const existing = sec.lines.filter(l => numericRe.test(l)).map(l => l.trim());
  const merged = existing.concat(extra.map(s => s.trim()));
  const unique = Array.from(new Set(merged));

  // Sort by numeric components
  unique.sort((a, b) => {
    const pa = a.match(numericRe)[1].split('.').map(Number);
    const pb = b.match(numericRe)[1].split('.').map(Number);
    for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
      const na = pa[i] || 0;
      const nb = pb[i] || 0;
      if (na !== nb) return na - nb;
    }
    return 0;
  });

  // Remove existing numeric lines, preserve other content, then append a blank line and the sorted numeric lines
  const nonNumeric = sec.lines.filter(l => !numericRe.test(l));
  sec.lines = nonNumeric.concat(['']).concat(unique);
}

// Reassemble file
const out = [];
for (const sec of sections) {
  if (sec.headerLine) out.push(sec.headerLine);
  out.push(...sec.lines);
}

fs.writeFileSync(outPath, out.join('\n'), 'utf8');
console.log('Wrote fixed plan to', outPath);
