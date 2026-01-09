const fs = require('fs');
const path = require('path');

const diagramPath = 'plan/plugin-tree diagram.md';
const pluginRoot = 'plugins/affiliate-product-showcase';

function readDiagram() {
  const raw = fs.readFileSync(diagramPath, 'utf8');
  const lines = raw.split(/\r?\n/);
  const nodes = [];
  let rootFound = false;

  function cleanLine(l) {
    // remove tree characters (box-drawing and ascii)
    return l.replace(/[\u2500-\u257F\|`\-\+\s>]*?/, '').trim();
  }

  for (let i = 0; i < lines.length; i++) {
    let raw = lines[i];
    if (/^```/.test(raw)) continue;
    const mRoot = raw.match(/affiliate-product-showcase\/?/i);
    if (mRoot && !rootFound) {
      rootFound = true;
      continue;
    }
    if (!rootFound) continue;

    // measure indent by counting leading spaces and box chars
    const indentMatch = raw.match(/^[\s\u2500-\u2502\u2514\u251C\u2510\u250C\|`\-]*/);
    const indentStr = indentMatch ? indentMatch[0] : '';
    const indent = indentStr.replace(/[^ ]/g, '').length || indentStr.length; // fallback

    const cleaned = raw.replace(/^[\s\u2500-\u257F\|`\-]*/,'').trim();
    if (!cleaned) continue;
    // strip trailing comments like '# ...'
    const name = cleaned.replace(/\s+#.*$/,'').trim();

    nodes.push({ lineIndex: i, indent, name });
  }

  // Build paths using indentation stack
  const stack = [];
  const paths = new Set();
  for (let i = 0; i < nodes.length; i++) {
    const node = nodes[i];
    const nameRaw = node.name;
    // remove trailing commas or bullets
    let name = nameRaw.replace(/^[\-\u2022\*\s]*/,'').trim();

    // determine if directory: explicit trailing '/' OR next node has greater indent
    let isDir = /\/$/.test(name);
    const next = nodes[i+1];
    if (!isDir && next && next.indent > node.indent) isDir = true;

    // normalize name: remove trailing '/'
    let cleanName = name.replace(/\/$/,'').trim();

    // adjust stack by indent
    while (stack.length && stack[stack.length-1].indent >= node.indent) stack.pop();
    stack.push({ name: cleanName, indent: node.indent, isDir });

    // build full path from stack
    const parts = stack.map(s => s.name);
    const full = parts.join('/');
    if (isDir) paths.add(full.replace(/\\/g,'/') + '/'); else paths.add(full.replace(/\\/g,'/'));
  }

  return paths;
}

function walkFs() {
  const results = new Set();
  function walk(dir, relParts=[]) {
    const list = fs.readdirSync(dir, { withFileTypes: true });
    for (const d of list) {
      const parts = relParts.concat([d.name]);
      const rel = parts.join('/');
      if (d.isDirectory()) {
        results.add(rel + '/');
        walk(path.join(dir, d.name), parts);
      } else {
        results.add(rel);
      }
    }
  }
  walk(pluginRoot);
  return results;
}

function compare() {
  if (!fs.existsSync(pluginRoot)) {
    console.error('Plugin root not found:', pluginRoot);
    process.exit(2);
  }
  const diag = readDiagram();
  const fsItems = walkFs();

  const missing = [...diag].filter(x => !fsItems.has(x));
  const extra = [...fsItems].filter(x => !diag.has(x));

  console.log('Diagram items:', diag.size);
  console.log('Filesystem items:', fsItems.size);
  console.log('Missing on filesystem (in diagram, not in fs):', missing.length);
  missing.slice(0,200).forEach(i => console.log('  -', i));
  console.log('Extra on filesystem (in fs, not in diagram):', extra.length);
  extra.slice(0,200).forEach(i => console.log('  -', i));
}

compare();
