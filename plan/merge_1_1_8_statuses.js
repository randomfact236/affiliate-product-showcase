const fs = require('fs');
const path = require('path');
const bp = path.join(__dirname, 'plan_state.broken.json');
const nb = path.join(__dirname, 'plan_state.json');
const text = fs.readFileSync(bp,'utf8');
const m = {};
const re = /\"(1\.1\.8(?:\.\d+)*)\"\s*:\s*\"([^\"]*)\"/g;
let r;
while((r=re.exec(text))){ m[r[1]]=r[2]; }
console.log('found',Object.keys(m).length,'entries');
const state = JSON.parse(fs.readFileSync(nb,'utf8'));
for(const k of Object.keys(m)){ state.statusByCode[k]=m[k]; }
fs.writeFileSync(nb, JSON.stringify(state,null,2)+'\n','utf8');
console.log('merged into new state');
