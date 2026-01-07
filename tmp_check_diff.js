const fs = require('fs');
const p = 'plan/plan_sync_todo.md';
let s = fs.readFileSync(p, 'utf8').replace(/\r\n/g, '\n');
const lines = s.split('\n');
function formatLine(line){
  if (/^\s*####\s+/.test(line)){
    const rest = line.replace(/^\s*####\s+/, '').trim();
    return '   - ' + rest;
  }
  if (/^\s*-\s+\d+(?:\.\d+)+/.test(line)){
    const rest = line.replace(/^\s*-\s+/, '').trim();
    return '   - ' + rest;
  }
  return line;
}
const formattedLines = lines.map(formatLine);
let any = false;
for (let i = 0; i < lines.length; i++){
  if (lines[i] !== formattedLines[i]){
    any = true;
    console.log((i+1) + ":\nORIG: " + lines[i] + "\nEXPECT: " + formattedLines[i] + "\n");
  }
}
if (!any) console.log('No formatting differences found');
