#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');
function load(file){ return JSON.parse(fs.readFileSync(file,'utf8')); }
function save(file,obj){ fs.writeFileSync(file, JSON.stringify(obj,null,2)+'\n','utf8'); }
const ROOT = path.join(__dirname,'..');
const STATE = path.join(__dirname,'plan_state.json');
if(!process.argv[2]){
  console.error('Usage: node plan/start_task.cjs <code>');
  process.exit(2);
}
const code = process.argv[2];
if(!fs.existsSync(STATE)){ console.error('plan_state.json not found'); process.exit(1); }
const state = load(STATE);
state.statusByCode = state.statusByCode || {};
state.statusByCode[code] = 'in-progress';
state.lastManualUpdate = new Date().toISOString();
save(STATE,state);
console.log('Marked',code,'as in-progress in plan_state.json');
// Re-run sync
const res = spawnSync('node',[path.join(__dirname,'plan_sync_todos.cjs')],{ stdio: 'inherit', cwd: ROOT });
process.exit(res.status);
