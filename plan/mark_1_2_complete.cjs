#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const child = require('child_process');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');
const SOURCE = path.join(PLAN_DIR, 'plan_source.md');
const SET_STATUS = path.join(PLAN_DIR, 'set-status.cjs');

function readSource() {
  return fs.existsSync(SOURCE) ? fs.readFileSync(SOURCE, 'utf8') : '';
}

function collectFiles(root) {
  const out = [];
  const ignored = new Set(['.git', 'node_modules', 'vendor', 'plan', '.github']);
  function walk(dir) {
    let entries;
    try { entries = fs.readdirSync(dir, { withFileTypes: true }); }
    catch (_) { return; }
    for (const e of entries) {
      if (ignored.has(e.name)) continue;
      const p = path.join(dir, e.name);
      if (e.isDirectory()) walk(p);
      else if (e.isFile()) out.push(p);
    }
  }
  walk(root);
  return out;
}

function normalizeLabel(text) {
  if (!text) return '';
  let s = String(text).trim();
  // Remove em-dash style descriptions
  s = s.replace(/\s+[–—-]\s+.*/u, '').trim();
  // Remove trailing parentheses descriptions
  s = s.replace(/\s*\(.*\)$/, '').trim();
  // If the line begins with a filename-like token, keep it
  // else keep as-is — we only attempt path-like labels
  return s;
}

function findMatch(files, label) {
  if (!label) return null;
  const normLabel = label.replace(/\//g, path.sep).toLowerCase();
  // Prefer matches under the plugin folder
  const pluginPrefix = path.join('wp-content', 'plugins', 'affiliate-product-showcase') + path.sep;
  for (const f of files) {
    const fn = f.toLowerCase();
    // exact ending match
    if (fn.endsWith(normLabel)) return f;
    // match with leading separator
    if (fn.endsWith(path.sep + normLabel)) return f;
    // match inside plugin path
    if (fn.includes((pluginPrefix + normLabel).toLowerCase())) return f;
  }

  // fallback: try any file that endsWith the basename
  const base = path.basename(normLabel);
  for (const f of files) {
    if (f.toLowerCase().endsWith(base)) return f;
  }
  return null;
}

function run() {
  const src = readSource();
  const lines = src.split(/\r?\n/);
  const entries = [];
  for (const line of lines) {
    const m = line.match(/^\s*(1\.2(?:\.\d+)*)\s+(.*)$/);
    if (!m) continue;
    entries.push({ code: m[1], text: m[2].trim() });
  }

  const files = collectFiles(ROOT);

  let updated = 0;
  for (const e of entries) {
    const label = normalizeLabel(e.text);
    // Only consider entries that look like paths or filenames
    if (!label || (!label.includes('/') && !/\.[a-z0-9]+$/i.test(label))) continue;
    const cand = findMatch(files, label);
    if (cand) {
      try {
        console.log('Found', e.code, '->', label, 'as', path.relative(ROOT, cand));
        child.execFileSync(process.execPath, [SET_STATUS, e.code, 'done'], { stdio: 'inherit' });
        updated++;
      } catch (err) {
        console.error('Failed to set status for', e.code, err && err.message ? err.message : err);
      }
    } else {
      // not found
    }
  }

  console.log(`Marked ${updated} items completed (if any).`);
}

run();
