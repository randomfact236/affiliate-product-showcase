const fs = require('fs');
const path = require('path');
const s = fs.readFileSync(path.join(__dirname,'plan_state.json'),'utf8');
const regex = /"([^"]*?)"/g;
let m; let found = false;
while((m=regex.exec(s))){
  if(m[1].includes('\n')||m[1].includes('\r')){
    found = true;
    console.log('Found quoted token with newline at index', m.index);
    console.log('token preview:', JSON.stringify(m[0].slice(0,60)));
  }
}
if(!found) console.log('no quoted tokens with newlines found');
