const fs = require('fs');
const path = require('path');

const STATE_PATH = path.join(__dirname, 'plan_state.json');

console.log('Reading plan_state.json...');
const content = fs.readFileSync(STATE_PATH, 'utf8');

// Find the position of the malformed lastSyncAt
const lastGood = content.lastIndexOf('"2.1.49"');
if (lastGood === -1) {
  console.error('Could not find "2.1.49" in file');
  process.exit(1);
}

// Extract good portion and rebuild lastSyncAt properly
const goodPart = content.substring(0, lastGood + 9);
const fixed = goodPart + ']\n' + '  "lastSyncAt": "' + new Date().toISOString() + '"\n}';

console.log('Writing fixed plan_state.json...');
fs.writeFileSync(STATE_PATH, fixed, 'utf8');
console.log('Fixed! File saved successfully.');
