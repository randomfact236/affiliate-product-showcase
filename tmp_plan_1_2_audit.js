const fs = require('fs');
const path = require('path');

function readJson(p) {
  return JSON.parse(fs.readFileSync(p, 'utf8'));
}

function normalizeCandidate(text) {
  let label = text.replace(/\s*\(.*\)\s*$/g, '').trim();

  // Remove non-file descriptive prefixes
  label = label.replace(/^Structure\s*/i, '').trim();
  label = label.replace(/^Framework:\s*/i, '').trim();
  label = label.replace(/^Plugin Name:\s*/i, '').trim();

  // Normalize common “filename + description” lines
  label = label.replace(/^affiliate-product-showcase\.php\b.*$/i, 'affiliate-product-showcase.php');
  label = label.replace(/^readme\.txt\b.*$/i, 'readme.txt');
  label = label.replace(/^uninstall\.php\b.*$/i, 'uninstall.php');
  label = label.replace(/^composer\.json\b.*$/i, 'composer.json');
  label = label.replace(/^package\.json\b.*$/i, 'package.json');
  label = label.replace(/^phpcs\.xml\b.*$/i, 'phpcs.xml');
  label = label.replace(/^README\.md\b.*$/i, 'README.md');
  label = label.replace(/^\.gitignore\b.*$/i, '.gitignore');

  // Allow leading folder names to be treated as paths
  for (const folder of [
    'includes/',
    'admin/',
    'public/',
    'blocks/',
    'src/',
    'assets/',
    'api/',
    'cli/',
    'languages/',
    'tests/',
    'docs/',
    'vendor/',
  ]) {
    if (new RegExp('^' + folder.replace('/', '\\/'), 'i').test(label)) {
      return label;
    }
  }

  return label;
}

function looksLikePath(label) {
  const lc = label.replace(/\/$/, '').toLowerCase();
  if (label.includes('/')) return true;
  if (/\.(php|md|txt|json|xml|pot|js|css)$/.test(label)) return true;
  if (
    [
      'vendor',
      'docs',
      'assets',
      'src',
      'includes',
      'admin',
      'public',
      'blocks',
      'api',
      'cli',
      'languages',
      'tests',
    ].includes(lc)
  ) {
    return true;
  }
  return false;
}

function main() {
  const state = readJson('plan/plan_state.json');
  const srcLines = fs.readFileSync('plan/plan_source.md', 'utf8').split(/\r?\n/);
  const base = 'plugins/affiliate-product-showcase';

  const entries = [];
  for (const line of srcLines) {
    const m = line.match(/^\s*(1\.2(?:\.\d+)*)\s+(.*)$/);
    if (!m) continue;
    entries.push({ code: m[1], text: m[2].trim() });
  }

  const rows = entries.map((e) => {
    const label = normalizeCandidate(e.text);
    const isPath = looksLikePath(label);
    const rel = isPath ? path.join(base, label) : null;
    const exists = rel ? fs.existsSync(rel) : null;
    const status = (state.statusByCode || {})[e.code] || 'pending';
    return { ...e, label, rel, exists, status };
  });

  const mismatches = [];
  for (const r of rows) {
    if (r.rel && r.exists === false && r.status === 'completed') {
      mismatches.push({ kind: 'completed-but-missing', ...r });
    }
    if (r.rel && r.exists === true && r.status === 'pending') {
      mismatches.push({ kind: 'pending-but-exists', ...r });
    }
  }

  console.log('1.2 total entries:', rows.length);
  console.log('1.2 path-like entries:', rows.filter((r) => r.rel).length);
  console.log('Mismatches:', mismatches.length);
  for (const m of mismatches.slice(0, 120)) {
    console.log('-', m.kind, m.code, m.status, '=>', m.rel);
  }
  if (mismatches.length > 120) console.log('...and', mismatches.length - 120, 'more');

  // Exit code useful for scripts
  process.exitCode = mismatches.length ? 2 : 0;
}

main();
