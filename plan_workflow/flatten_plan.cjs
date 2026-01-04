#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const inPath = path.join(root, 'plan_workflow', 'plan_workflow_sync.md');
const outPath = inPath; // overwrite
if (!fs.existsSync(inPath)) {
  console.error('input file not found:', inPath);
  process.exit(1);
}
let txt = fs.readFileSync(inPath, 'utf8');

// Remove region comments
txt = txt.replace(/<!--\s*#region[^>]*-->/gmi, '');
txt = txt.replace(/<!--\s*#endregion[^>]*-->/gmi, '');

// Replace either <details><summary>... or <details>NUMBER TITLE with a plain heading line
txt = txt.replace(/(^[ \t]*)(?:<details>\s*<summary>\s*([^<]+?)\s*(?:<span[^>]*>\s*([^<]+?)\s*<\/span>)?\s*<\/summary>|<details>\s*(\d+\.\d+)\s*(.*))\s*\n?/gmi, (m, indent, prefix, spanTitle, numOnly, restOnly) => {
  if (numOnly) {
    const num = numOnly.trim();
    const rest = (restOnly || '').trim();
    return `${indent}## ${num}  ${rest}\n`;
  }
  const titleText = spanTitle ? spanTitle.trim() : prefix.trim();
  const parts = prefix.trim().split(/\s+/);
  const num = parts[0];
  const rest = titleText.replace(/^\d+\.\d+\s*-?\s*/,'');
  return `${indent}## ${num}  ${rest}\n`;
});

// Remove closing </details> tags
txt = txt.replace(/<\/details>/gmi, '');
// Remove any remaining summary tags
txt = txt.replace(/<summary[^>]*>/gmi, '');
txt = txt.replace(/<\/summary>/gmi, '');

// Remove stray span tags
txt = txt.replace(/<span[^>]*>/gmi, '');
txt = txt.replace(/<\/span>/gmi, '');

// Clean excessive blank lines (more than 2)
txt = txt.replace(/\n{3,}/g, '\n\n');

// Indent third-level items (e.g. 1.1.1) for readability if they are not already indented
txt = txt.replace(/^[ \t]*(\d+\.\d+\.\d+\b.*)$/gm, '\t\t$1');

// Group, sort and deduplicate contiguous numeric subpoint blocks with the same major number
(() => {
  const lines = txt.split(/\r?\n/);
  const out = [];
  let buf = [];
  let bufMajor = null;

  function flush() {
    if (!buf.length) return;
    const items = buf.map((l, i) => {
      const m = l.match(/^[ \t]*(\d+)\.(\d+)(?:\.(\d+))?\b/);
      const a = m ? parseInt(m[1], 10) : 0;
      const b = m ? parseInt(m[2], 10) : 0;
      const c = m && m[3] ? parseInt(m[3], 10) : 0;
      return { line: l, key: l.trim(), nums: [a, b, c], idx: i };
    });
    items.sort((x, y) => {
      for (let i = 0; i < 3; i++) {
        if (x.nums[i] !== y.nums[i]) return x.nums[i] - y.nums[i];
      }
      return x.idx - y.idx;
    });
    const seen = new Set();
    for (const it of items) {
      if (!seen.has(it.key)) {
        out.push(it.line);
        seen.add(it.key);
      }
    }
    buf = [];
    bufMajor = null;
  }

  for (let i = 0; i < lines.length; i++) {
    const L = lines[i];
    const m = L.match(/^[ \t]*(\d+)\.(\d+)(?:\.(\d+))?\b/);
    if (m) {
      const major = m[1];
      if (buf.length === 0) {
        bufMajor = major;
        buf.push(L);
      } else if (bufMajor === major) {
        buf.push(L);
      } else {
        flush();
        bufMajor = major;
        buf.push(L);
      }
    } else {
      flush();
      out.push(L);
    }
  }
  flush();
  txt = out.join('\n');
})();

fs.writeFileSync(outPath, txt, 'utf8');
console.log('Flattened plan written to', outPath);
// --- Attach misplaced numeric subpoints to correct sections (previously a separate fix script)
(() => {
  const lines = txt.split(/\r?\n/);
  const headerRe = /^##\s+(\d+)(?:\.\d+)?\b/;
  const numericRe = /^\s*([0-9]+(?:\.[0-9]+)+)\b/;

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

  for (const sec of sections) {
    const key = sec.stepNum;
    if (key == null) continue;
    const extra = misplaced[key];
    if (!extra || extra.length === 0) continue;

    const existing = sec.lines.filter(l => numericRe.test(l)).map(l => l.trim());
    const merged = existing.concat(extra.map(s => s.trim()));
    const unique = Array.from(new Set(merged));

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

    const nonNumeric = sec.lines.filter(l => !numericRe.test(l));
    sec.lines = nonNumeric.concat(['']).concat(unique);
  }

  const out = [];
  for (const sec of sections) {
    if (sec.headerLine) out.push(sec.headerLine);
    out.push(...sec.lines);
  }
  txt = out.join('\n');
})();

fs.writeFileSync(outPath, txt, 'utf8');
console.log('Flattened + fixed plan written to', outPath);

// --- Validation: check for any remaining misplaced numeric subpoints ---
(() => {
  const lines = txt.split(/\r?\n/);
  const headerRe = /^##\s+(\d+)(?:\.\d+)?\b/;
  const numericRe = /^\s*([0-9]+(?:\.[0-9]+)+)\b/;

  let curHeader = null;
  let curStep = null;
  let lineNo = 0;
  let problems = 0;
  for (const line of lines) {
    lineNo++;
    const mh = line.match(headerRe);
    if (mh) {
      curHeader = line.trim();
      curStep = parseInt(mh[1], 10);
      continue;
    }
    const mm = line.match(numericRe);
    if (mm && curStep != null) {
      const full = mm[1];
      const major = parseInt(full.split('.')[0], 10);
      if (major !== curStep) {
        console.log(`Mismatch at line ${lineNo}: header="${curHeader}" expected major=${curStep} but found ${full} -> ${line.trim()}`);
        problems++;
      }
    }
  }
  if (problems === 0) console.log('Validation: No misplaced numeric subpoints found.');
  else console.log('Validation: Found', problems, 'misplaced lines.');
})();
