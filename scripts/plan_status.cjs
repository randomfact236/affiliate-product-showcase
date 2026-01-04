#!/usr/bin/env node
/*
  plan_status.cjs

  Updates plan/plan_state.json for a specific code, then runs scripts/sync_todos.cjs.

  Usage:
    node scripts/plan_status.cjs --code 1.2.3 --status in-progress
*/

const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, '..');
const PLAN_DIR = path.join(ROOT, 'plan');

const DEFAULT_STATE = path.join(PLAN_DIR, 'plan_state.json');
const SYNC_SCRIPT = path.join(__dirname, 'sync_todos.cjs');

const VALID_STATUSES = new Set(['pending', 'in-progress', 'blocked', 'cancelled', 'completed']);

function parseArgs(argv) {
  const args = { state: DEFAULT_STATE, code: null, status: null, quiet: false };

  for (let i = 2; i < argv.length; i++) {
    const a = argv[i];
    if (a === '--quiet') args.quiet = true;
    else if (a === '--state') args.state = path.resolve(argv[++i]);
    else if (a === '--code') args.code = argv[++i];
    else if (a === '--status') args.status = argv[++i];
    else throw new Error(`Unknown arg: ${a}`);
  }

  if (!args.code) throw new Error('Missing --code');
  if (!args.status) throw new Error('Missing --status');
  if (!VALID_STATUSES.has(args.status)) {
    throw new Error(`Invalid --status: ${args.status}. Valid: ${Array.from(VALID_STATUSES).join(', ')}`);
  }

  return args;
}

function loadJson(filePath, fallback) {
  if (!fs.existsSync(filePath)) return fallback;
  const raw = fs.readFileSync(filePath, 'utf8');
  if (!raw.trim()) return fallback;
  return JSON.parse(raw);
}

function saveJsonPretty(filePath, obj) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  fs.writeFileSync(filePath, JSON.stringify(obj, null, 2) + '\n', 'utf8');
}

function runNode(scriptPath, args) {
  const { spawnSync } = require('child_process');
  const res = spawnSync(process.execPath, [scriptPath, ...args], { stdio: 'inherit' });
  if (res.status !== 0) process.exit(res.status || 1);
}

function main() {
  const args = parseArgs(process.argv);

  const state = loadJson(args.state, {
    generatedBy: 'scripts/sync_todos.cjs',
    statusByCode: {}
  });

  if (!state.statusByCode) state.statusByCode = {};

  state.statusByCode[String(args.code)] = args.status;
  state.lastUpdatedAt = new Date().toISOString();

  saveJsonPretty(args.state, state);

  if (!args.quiet) {
    console.log(`✅ Updated status: ${args.code} = ${args.status}`);
    console.log('🔄 Running sync...');
  }

  runNode(SYNC_SCRIPT, ['--quiet']);
}

try {
  main();
} catch (err) {
  console.error('❌', err && err.message ? err.message : err);
  process.exit(1);
}
