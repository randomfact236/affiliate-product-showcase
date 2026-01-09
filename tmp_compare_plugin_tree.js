const fs = require('fs');
const path = require('path');

const diagramPath = 'plan/plugin-tree diagram.md';
const pluginRoot = 'plugins/affiliate-product-showcase';

function readDiagram() {
  const raw = fs.readFileSync(diagramPath, 'utf8');
  const lines = raw.split(/\r?\n/);
  const items = new Set();
  let seenRoot = false;
  for (let l of lines) {
    // remove code fence markers
    if (/^```/.test(l)) continue;
    // strip leading tree chars
    const m = l.match(/^[\s\u2500-\u2502\u2514\u251C\|`\-]*([\w\-\. \/\[\]]+?)(?:\s+#.*)?$/);
    if (!m) continue;
    let name = m[1].trim();
    if (!name) continue;
    // root
    if (name === 'affiliate-product-showcase/' || name === 'affiliate-product-showcase') {
      seenRoot = true;
      continue;
    }
    if (!seenRoot) continue;
    // normalize: make directories end with '/'
    const cleaned = name.replace(/\s+#.*$/, '').trim();
    let entry = cleaned;
    // if it looks like a directory (ends with / or has no extension but no space), append '/'
    if (/\/$/.test(entry)) {
      // keep
    } else if (!/\.[a-zA-Z0-9]+$/.test(entry) && !entry.includes(' ')) {
      entry = entry + '/';
    }
    // convert backslashes to slashes
    entry = entry.replace(/\\/g, '/');
    items.add(entry);
  }
  return items;
}

function walk(dir, root) {
  const results = new Set();
  const list = fs.readdirSync(dir, { withFileTypes: true });
  for (const d of list) {
    const full = path.join(dir, d.name);
    const rel = path.relative(root, full).replace(/\\/g, '/');
    if (d.isDirectory()) {
      results.add(rel + '/');
      const sub = walk(full, root);
      for (const s of sub) results.add(s);
    } else {
      results.add(rel);
    }
  }
  return results;
}

function compare() {
  if (!fs.existsSync(pluginRoot)) {
    console.error('Plugin root not found:', pluginRoot);
    process.exit(2);
  }
  const diag = readDiagram();
  const fsItems = walk(pluginRoot, pluginRoot);

  // normalize some typical names: diagram may include folder names without trailing '/', ensure both sides comparable
  const diagNorm = new Set([...diag].map(s => s.replace(/\/+$/, (m)=>'/')));

  const missing = [...diagNorm].filter(x => !fsItems.has(x));
  const extra = [...fsItems].filter(x => !diagNorm.has(x));

  console.log('Diagram items:', diagNorm.size);
  console.log('Filesystem items:', fsItems.size);
  console.log('Missing on filesystem (in diagram, not in fs):', missing.length);
  missing.slice(0,200).forEach(i => console.log('  -', i));
  console.log('Extra on filesystem (in fs, not in diagram):', extra.length);
  extra.slice(0,200).forEach(i => console.log('  -', i));
}

compare();
