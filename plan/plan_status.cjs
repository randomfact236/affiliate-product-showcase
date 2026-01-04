#!/usr/bin/env node
/* plan_status.cjs (moved into plan/) - update a single code's status then re-run sync */

const fs = require('fs');
const path = require('path');
const child_process = require('child_process');

const PLAN_DIR = __dirname; // this script now lives in plan/
const ROOT = path.join(PLAN_DIR, '..');
const STATE_FILE = path.join(PLAN_DIR, 'plan_state.json');
const SYNC_SCRIPT = path.join(PLAN_DIR, 'plan_sync_todos.cjs');

function usage(){
  console.log('Usage: node plan/plan_status.cjs --code <code> --status <pending|in-progress|blocked|cancelled|completed> [--quiet]');
}

function parseArgs(argv){ const out = { code: null, status: null, quiet: false }; for (let i=2;i<argv.length;i++){ const a=argv[i]; if (a==='--code') out.code=argv[++i]; else if (a==='--status') out.status=argv[++i]; else if (a==='--quiet') out.quiet=true; else { console.error('Unknown arg',a); usage(); process.exit(1);} } return out; }

function loadState(){ if (!fs.existsSync(STATE_FILE)) return { generatedBy: 'plan/plan_sync_todos.cjs', statusByCode: {} }; return JSON.parse(fs.readFileSync(STATE_FILE,'utf8') || '{}'); }
function saveState(s){ fs.writeFileSync(STATE_FILE, JSON.stringify(s,null,2)+'\n','utf8'); }

function main(){ const args = parseArgs(process.argv); if (!args.code || !args.status) { usage(); process.exit(1);} const allowed = ['pending','in-progress','blocked','cancelled','completed']; if (!allowed.includes(args.status)) { console.error('Invalid status'); usage(); process.exit(1); }
  const state = loadState(); state.statusByCode = state.statusByCode || {}; state.statusByCode[args.code] = args.status; state.lastUpdated = new Date().toISOString(); saveState(state);
  const spawnArgs = [SYNC_SCRIPT]; if (args.quiet) spawnArgs.push('--quiet'); const proc = child_process.spawn(process.execPath, spawnArgs, { stdio: 'inherit' }); proc.on('exit', code=>process.exit(code)); }

main();
