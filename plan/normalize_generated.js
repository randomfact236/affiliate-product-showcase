#!/usr/bin/env node
const fs = require('fs');
const cp = require('child_process');
const path = require('path');

function safeShow(refPath){
  try{ return cp.execSync(`git show HEAD:${refPath}`, {stdio:['pipe','pipe','ignore']}).toString(); }catch(e){ return null; }
}

(async function main(){
  const root = path.join(__dirname, '..');
  // NOTE:
  // We intentionally DO NOT rewrite the generated markdown headers by default.
  // Those headers include a checksum derived from `plan_source.md`; replacing
  // them from HEAD can create confusing drift.
  // If you need the legacy behavior, set: NORMALIZE_GENERATED_HEADERS=1
  if (process.env.NORMALIZE_GENERATED_HEADERS === '1') {
    const mdFiles = ['plan/plan_sync.md','plan/plan_sync_todo.md'];
    for(const f of mdFiles){
      const head = safeShow(f);
      if(!head) continue;
      try{
        const curPath = path.join(root, f);
        if(!fs.existsSync(curPath)) continue;
        const cur = fs.readFileSync(curPath,'utf8');
        const hdrMatch = head.match(/^(<!--\s*GENERATED_BY_SYNC_TODOS[\s\S]*?\n\n)/);
        if(hdrMatch){
          const newCur = cur.replace(/^(<!--\s*GENERATED_BY_SYNC_TODOS[\s\S]*?\n\n)/, hdrMatch[1]);
          if(newCur !== cur){ fs.writeFileSync(curPath, newCur, 'utf8'); console.log('Normalized header for', f); }
        }
      }catch(e){ /* ignore */ }
    }
  }

  // Legacy timestamp normalization can also create confusing drift.
  // Enable only when explicitly requested.
  if (process.env.NORMALIZE_GENERATED_TIMESTAMPS === '1') {
    try{
      const headState = safeShow('plan/plan_state.json');
      const curPath = path.join(root, 'plan', 'plan_state.json');
      if(headState && fs.existsSync(curPath)){
        const head = JSON.parse(headState);
        const cur = JSON.parse(fs.readFileSync(curPath,'utf8'));
        let changed = false;
        if(head.lastSyncAt && cur.lastSyncAt !== head.lastSyncAt){ cur.lastSyncAt = head.lastSyncAt; changed = true; }
        if(head.lastUpdated && cur.lastUpdated !== head.lastUpdated){ cur.lastUpdated = head.lastUpdated; changed = true; }
        if(changed){ fs.writeFileSync(curPath, JSON.stringify(cur, null, 2) + '\n', 'utf8'); console.log('Normalized timestamps for plan_state.json'); }
      }
    }catch(e){ /* ignore */ }
  }
})();
