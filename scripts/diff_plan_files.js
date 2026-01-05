const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const src = path.join(root, 'plan', 'plan_source.md');
const sync = path.join(root, 'plan', 'plan_sync.md');
const todo = path.join(root, 'plan', 'plan_sync_todo.md');

function read(p){ return fs.existsSync(p) ? fs.readFileSync(p,'utf8').split(/\r?\n/) : []; }

function normalizeLine(l){
  if (!l) return '';
  // remove generated header comment lines
  if (/^<!--\s*GENERATED_BY_SYNC_TODOS/.test(l)) return '';
  // strip Markdown heading markers and list markers
  let s = l.replace(/^\s*[#>-]+\s*/, '');
  // remove leading status markers/emojis (non-alphanumeric)
  s = s.replace(/^[^0-9A-Za-z`]+\s*/, '');
  // collapse multiple spaces
  s = s.replace(/\s+/g, ' ').trim();
  return s;
}

function setFromLines(lines){
  const s = new Set();
  for (const l of lines){
    const n = normalizeLine(l);
    if (n) s.add(n);
  }
  return s;
}

const srcLines = read(src);
const syncLines = read(sync);
const todoLines = read(todo);

const srcSet = setFromLines(srcLines);
const syncSet = setFromLines(syncLines);
const todoSet = setFromLines(todoLines);

function diff(a,b){
  const out = [];
  for (const x of a) if (!b.has(x)) out.push(x);
  return out;
}

const src_not_in_sync = diff(srcSet, syncSet);
const sync_not_in_src = diff(syncSet, srcSet);
const src_not_in_todo = diff(srcSet, todoSet);
const todo_not_in_src = diff(todoSet, srcSet);

function printSample(title, arr){
  console.log(`\n${title} (count: ${arr.length})`);
  arr.slice(0,25).forEach((x,i)=> console.log(`${i+1}. ${x}`));
  if (arr.length>25) console.log('...');
}

console.log('Diff report between plan/plan_source.md and generated outputs:');
printSample('Lines in plan_source.md but NOT in plan_sync.md', src_not_in_sync);
printSample('Lines in plan_sync.md but NOT in plan_source.md', sync_not_in_src);
printSample('Lines in plan_source.md but NOT in plan_sync_todo.md', src_not_in_todo);
printSample('Lines in plan_sync_todo.md but NOT in plan_source.md', todo_not_in_src);

console.log('\nNote: comparisons normalize headings/list markers and strip generated header.');
