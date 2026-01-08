const fs = require('fs');
const path = require('path');
const p = path.join(__dirname, 'plan_state.json');
const s = fs.readFileSync(p, 'utf8');
const pos = 73714;
console.log('orig context before:', JSON.stringify(s.slice(pos-40,pos)));
console.log('orig context after :', JSON.stringify(s.slice(pos,pos+40)));
let repaired = s.replace(/"([0-9.\s]+)"/g, (m, g1) => {
  if (/\d/.test(g1) && !/[A-Za-z]/.test(g1)) {
    const cleaned = g1.replace(/\s+/g, '');
    return '"' + cleaned + '"';
  }
  return m;
});
console.log('repaired context before:', JSON.stringify(repaired.slice(pos-40,pos)));
console.log('repaired context after :', JSON.stringify(repaired.slice(pos,pos+40)));
// print char codes of a small window
function codes(str){return str.split('').map(c=>c.charCodeAt(0));}
console.log('orig codes before:', codes(s.slice(pos-10,pos+10)));
console.log('repaired codes before:', codes(repaired.slice(pos-10,pos+10)));
fs.writeFileSync(path.join(__dirname,'plan_state.repaired.preview.json'), repaired, 'utf8');
console.log('wrote preview to plan_state.repaired.preview.json');
