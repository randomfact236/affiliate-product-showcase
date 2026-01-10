const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const PLUGIN = path.join(ROOT, 'wp-content', 'plugins', 'affiliate-product-showcase');
const TODOS = path.join(ROOT, 'plan', 'plan_todos.json');

function load() {
  return JSON.parse(fs.readFileSync(TODOS, 'utf8'));
}

function save(obj) {
  fs.writeFileSync(TODOS, JSON.stringify(obj, null, 2) + '\n', 'utf8');
}

function guessPaths(title) {
  // extract candidate path token from title (only tokens without spaces around slashes)
  const m = title.match(/([^\s\/]+\/[^\s\/]+(?:\/[^\s\/]+)*)/);
  const candidates = [];
  if (m) {
    const token = m[1];
    candidates.push(path.join(ROOT, token));
    candidates.push(path.join(PLUGIN, token));
    // also try under plugin root without leading folders
    candidates.push(path.join(PLUGIN, token.replace(/^\//, '')));
  }
  return candidates;
}

function existsAny(cands) {
  for (const p of cands) {
    if (!p) continue;
    try {
      if (fs.existsSync(p)) return true;
    } catch (e) {}
  }
  return false;
}

function main() {
  const doc = load();
  const todos = Array.isArray(doc.todos) ? doc.todos : [];
  let changed = false;
  for (const node of todos) {
    if (!node.code || !String(node.code).startsWith('1.2')) continue;
    if (!node.title) continue;
    // Conservative whitelist: only verify entries that clearly reference repo files/folders
    const whitelist = [
      'src/', 'frontend/', 'blocks/', 'assets/', 'assets/dist', 'assets/images', 'assets/fonts',
      'tests/', 'docs/', 'languages/', '.github/', 'composer.json', 'composer.lock', 'package.json',
      'package-lock.json', 'vite.config.js', 'tsconfig.json', 'postcss.config.js', 'tailwind.config.js',
      'phpcs.xml.dist', 'phpunit.xml.dist', '.gitignore', '.editorconfig', '.eslintrc.cjs', '.prettierrc',
      'readme.txt', 'README.md', 'uninstall.php', 'affiliate-product-showcase.php', 'wp-tests-config-sample.php',
      'vendor/', 'tools/'
    ];
    const titleLower = String(node.title || '').toLowerCase();
    if (!whitelist.some(w => titleLower.includes(w))) continue;

    const cands = guessPaths(node.title);
    const found = existsAny(cands);
    const shouldBeCompleted = found;
    if (shouldBeCompleted && node.status !== 'completed') {
      node.status = 'completed';
      node.derivedStatus = 'completed';
      node.marker = '✅';
      changed = true;
      console.log('MARKED completed:', node.code, node.title, '-> found');
    } else if (!shouldBeCompleted && node.status === 'completed') {
      node.status = 'pending';
      node.derivedStatus = 'in-progress';
      node.marker = '⏳';
      changed = true;
      console.log('UNMARKED completed:', node.code, node.title, '-> NOT found');
    }
  }

  if (changed) {
    save(doc);
    console.log('Updated', TODOS);
    process.exit(0);
  } else {
    console.log('No changes');
    process.exit(0);
  }
}

main();
