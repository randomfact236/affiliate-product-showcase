#!/usr/bin/env node
// shim: forward to plan/plan_status.cjs
const child_process = require('child_process');
const path = require('path');
const target = path.join(__dirname, '..', 'plan', 'plan_status.cjs');
const args = process.argv.slice(2);
const cp = child_process.spawn(process.execPath, [target, ...args], { stdio: 'inherit' });
cp.on('exit', code => process.exit(code));
