#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
function load(file){ return JSON.parse(fs.readFileSync(file,'utf8')); }
function save(file,obj){ fs.writeFileSync(file, JSON.stringify(obj,null,2)+'\n','utf8'); }
const PLAN = path.join(__dirname,'plan_state.json');
if(!fs.existsSync(PLAN)) { console.error('plan_state.json not found'); process.exit(1); }
const state = load(PLAN);
const now = new Date().toISOString().replace(/[:.]/g,'-');
const bak = path.join(__dirname,`plan_state.json.bak.${now}`);
fs.copyFileSync(PLAN,bak);
console.log('Backup written to', bak);
const statusByCode = state.statusByCode || {};
let changed=0;
for(const k of Object.keys(statusByCode)){
  if(statusByCode[k] === 'in-progress'){
    statusByCode[k] = 'pending';
    changed++;
  }
}
state.statusByCode = statusByCode;
state.cleanedAt = new Date().toISOString();
save(PLAN,state);
console.log('Changed',changed,'entries from in-progress -> pending.');
process.exit(0);
