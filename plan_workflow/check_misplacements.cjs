const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const inPath = process.argv[2] || path.join(root, 'plan_workflow', 'plan_workflow_sync_fixed.md');
const txt = fs.readFileSync(inPath, 'utf8');
const lines = txt.split(/\r?\n/);

const headerRe = /^##\s+(\d+)(?:\.\d+)?\b/;
const numericRe = /^\s*([0-9]+(?:\.[0-9]+)+)\b/;

let curHeader = null;
let curStep = null;
let lineNo = 0;
let problems = 0;
for (const line of lines) {
  lineNo++;
  const mh = line.match(headerRe);
  if (mh) {
    curHeader = line.trim();
    curStep = parseInt(mh[1], 10);
    continue;
  }
  const mm = line.match(numericRe);
  if (mm && curStep != null) {
    const full = mm[1];
    const major = parseInt(full.split('.')[0], 10);
    if (major !== curStep) {
      console.log(`Mismatch at line ${lineNo}: header="${curHeader}" expected major=${curStep} but found ${full} -> ${line.trim()}`);
      problems++;
    }
  }
}

if (problems === 0) console.log('No misplaced numeric subpoints found.');
else console.log('Found', problems, 'misplaced lines.');
