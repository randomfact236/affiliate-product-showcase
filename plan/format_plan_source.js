#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');

function parseFilesArg(argv) {
  const idx = argv.indexOf('--files');
  if (idx === -1) return null;
  const files = [];
  for (let i = idx + 1; i < argv.length; i++) {
    const a = argv[i];
    if (a.startsWith('--')) break;
    files.push(a);
  }
  return files;
}

function walk(dir) {
  const results = [];
  const list = fs.readdirSync(dir, { withFileTypes: true });
  for (const ent of list) {
    if (ent.name === 'node_modules' || ent.name === 'vendor' || ent.name === '.git') continue;
    const full = path.join(dir, ent.name);
    if (ent.isDirectory()) {
      results.push(...walk(full));
    } else if (ent.isFile() && full.toLowerCase().endsWith('.md')) {
      results.push(full);
    }
  }
  return results;
}

function format(text) {
  const lines = text.split(/\r?\n/);
  const out = lines.map((line) => {
    if (/^\s*####\s+/.test(line)) {
      const rest = line.replace(/^\s*####\s+/, '').trim();
      return '   - ' + rest;
    }

    if (/^\s*-\s+\d+(?:\.\d+)+/.test(line)) {
      const rest = line.replace(/^\s*-\s+/, '').trim();
      return '   - ' + rest;
    }

    return line;
  });
  return out.join('\n');
}

const filesArg = parseFilesArg(process.argv);
const files = Array.isArray(filesArg)
  ? filesArg
      .map((fp) => (path.isAbsolute(fp) ? fp : path.join(root, fp)))
      .filter((fp) => fp.toLowerCase().endsWith('.md'))
  : walk(root);
if (files.length === 0) {
  console.log('No markdown files found.');
  process.exit(0);
}

const checkOnly = process.argv.includes('--check');
const changed = [];

for (const fp of files) {
  let content = fs.readFileSync(fp, 'utf8');
  // Normalize CRLF to LF before formatting/comparing to avoid
  // differences caused solely by line endings across OSes.
  const contentNorm = content.replace(/\r\n/g, '\n');
  const formatted = format(contentNorm);
  if (formatted !== contentNorm) {
    changed.push(fp);
    if (!checkOnly) {
      // Write files with LF line endings consistently.
      fs.writeFileSync(fp, formatted, 'utf8');
      console.log(path.relative(root, fp) + ' formatted and written.');
    }
  }
}

if (checkOnly) {
  if (changed.length > 0) {
    console.error('Formatted files differ:');
    changed.forEach((f) => console.error(' - ' + path.relative(root, f)));
    process.exit(1);
  }
  console.log('All markdown files formatted.');
  process.exit(0);
} else {
  if (changed.length === 0) {
    console.log('All markdown files already formatted.');
  } else {
    console.log('Formatted ' + changed.length + ' files.');
  }
}
