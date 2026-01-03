#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const inPath = path.join(process.cwd(), 'plan_workflow', 'plan_workflow_sync.md');
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
    return `${indent}${num}  ${rest}\n`;
  }
  const titleText = spanTitle ? spanTitle.trim() : prefix.trim();
  const parts = prefix.trim().split(/\s+/);
  const num = parts[0];
  const rest = titleText.replace(/^\d+\.\d+\s*-?\s*/,'');
  return `${indent}${num}  ${rest}\n`;
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

fs.writeFileSync(outPath, txt, 'utf8');
console.log('Flattened plan written to', outPath);
