#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const cp = require('child_process');

const PLAN_DIR = __dirname;
const SOURCE = path.join(PLAN_DIR, 'plan_source.md');
const BACKUP = path.join(PLAN_DIR, `plan_source.md.bak`);
const GEN = path.join(PLAN_DIR, 'plan_sync_todos.cjs');

function runNode(args, opts = {}){
  const res = cp.spawnSync(process.execPath, [GEN, ...args], Object.assign({ cwd: PLAN_DIR, encoding: 'utf8', env: process.env }, opts));
  return { status: res.status, stdout: res.stdout || '', stderr: res.stderr || '' };
}

function saveBackup(){ fs.copyFileSync(SOURCE, BACKUP); }
function restoreBackup(){ if (fs.existsSync(BACKUP)) fs.copyFileSync(BACKUP, SOURCE); if (fs.existsSync(BACKUP)) fs.unlinkSync(BACKUP); }

function writeSource(content){ fs.writeFileSync(SOURCE, content, 'utf8'); }
function readSource(){ return fs.readFileSync(SOURCE, 'utf8'); }

const orig = readSource();

const tests = [
  {
    name: 'Normal generation',
    setup: () => writeSource(orig),
    cmd: [],
    expectExit: 0,
    expectStdoutContains: '✅ Sync complete'
  },
  {
    name: 'Validation passes',
    setup: () => writeSource(orig),
    cmd: ['--validate'],
    // After auto-fix placeholders have been applied validation should pass with no issues
    expectExit: 0,
    expectStdoutContains: 'Validation passed — no issues found'
  },
  {
    name: 'Duplicate codes',
    setup: () => {
      const s = orig + '\n### 1.1.1 First\n### 1.1.1 Duplicate\n'; writeSource(s);
    },
    cmd: ['--validate'],
    expectExit: 2,
    expectStderrMatch: /Duplicate/i
  },
  {
    name: 'Malformed codes',
    setup: () => {
      const s = orig + '\n### 1.1.A Bad\n### 1..1 Bad\n'; writeSource(s);
    },
    cmd: ['--validate'],
    expectExit: 2,
    expectStderrMatch: /Malformed|malform/i
  },
  {
    name: 'Missing siblings (warning)',
    setup: () => {
      // create a minimal step/topic with 1.1.1 and 1.1.3 but no 1.1.2
      const s = '# Step 1 — Test\n\n## 1.1 Topic\n\n   1.1.1 Item one\n   1.1.3 Item three\n'; writeSource(s);
    },
    cmd: ['--validate'],
    expectExit: 1,
    expectStdoutContains: 'warnings' 
  },
  {
    name: 'Orphan item',
    setup: () => {
      // Use a high-numbered code unlikely to exist to test orphan detection
      const s = orig + '\n### 99.5.1 Item\n'; writeSource(s);
    },
    cmd: ['--validate'],
    expectExit: 2,
    expectStderrMatch: /Orphan|orphan/i
  },
  {
    name: 'Auto-fix preview',
    setup: () => {
      const s = '# Step 1 — Test\n\n## 1.1 Topic\n\n   1.1.1 Item one\n   1.1.3 Item three\n'; writeSource(s);
    },
    cmd: ['--validate','--fix-missing','--preview'],
    expectExit: 1,
    expectStdoutContains: 'Preview' 
  },
  {
    name: 'Auto-fix apply',
    setup: () => {
      const s = '# Step 1 — Test\n\n## 1.1 Topic\n\n   1.1.1 Item one\n   1.1.3 Item three\n'; writeSource(s);
    },
    cmd: ['--validate','--fix-missing','--apply'],
    expectExit: 0,
    verify: () => {
      const after = readSource();
      return /1\.1\.2\s+TODO|1\.1\.2\s+TODO\s*\(auto-inserted\)/.test(after);
    }
  }
];

function runAll(){
  const results = [];
  saveBackup();
  try{
    for (const t of tests){
      t.setup();
      const r = runNode(t.cmd);
      const passedExit = (r.status === t.expectExit);
      let passedContent = true;
      if (t.expectStdoutContains) passedContent = (r.stdout + r.stderr).toLowerCase().includes(String(t.expectStdoutContains).toLowerCase());
      if (t.expectStderrMatch) passedContent = passedContent && (t.expectStderrMatch.test(r.stderr + r.stdout));
      if (t.verify) passedContent = passedContent && !!t.verify();
      const pass = passedExit && passedContent;
      // Print per-test details to aid debugging
      console.log(`\n=== Test: ${t.name} ===`);
      console.log(`Command: node ${path.basename(GEN)} ${t.cmd.join(' ')}`);
      console.log(`Exit: ${r.status} (expected ${t.expectExit})  => ${pass ? 'PASS' : 'FAIL'}`);
      if (r.stdout) console.log('--- stdout ---\n' + r.stdout.trim());
      if (r.stderr) console.log('--- stderr ---\n' + r.stderr.trim());
      results.push({ name: t.name, pass, status: r.status, stdout: r.stdout, stderr: r.stderr });
    }
  } finally {
    restoreBackup();
  }
  return results;
}

if (require.main === module){
  const res = runAll();
  console.log('\nTest Summary:');
  let all = true;
  for (const r of res){
    console.log(` - ${r.name}: ${r.pass ? 'PASS' : 'FAIL'} (exit=${r.status})`);
    if (!r.pass) all = false;
  }
  process.exit(all ? 0 : 3);
}
