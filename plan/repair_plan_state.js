const fs = require('fs');
const p = require('path').join(__dirname, 'plan_state.json');
let s = fs.readFileSync(p, 'utf8');
try {
  JSON.parse(s);
  console.log('already valid');
  process.exit(0);
} catch (e) {
  console.log('parse failed:', e.message);
}

let repaired = s;
// Collapse accidental newlines between numeric code fragments inside quotes, e.g. "1.1.\n8.11" -> "1.1.8.11"
// If a quoted token contains only digits, dots and whitespace, collapse whitespace.
repaired = repaired.replace(/"([0-9.\s]+)"/g, (m, g1) => {
  if (/\d/.test(g1) && !/[A-Za-z]/.test(g1)) {
    const cleaned = g1.replace(/\s+/g, '');
    return '"' + cleaned + '"';
  }
  return m;
});

// Also fix any quoted strings that accidentally contain literal newlines
repaired = repaired.replace(/"([^"]*\n[^"]*)"/g, (m, g1) => {
  // collapse whitespace inside the quoted fragment
  const cleaned = g1.replace(/\s+/g, ' ');
  // if the cleaned value looks like a short token (no spaces), remove spaces entirely
  if (!/\s/.test(cleaned.trim())) return '"' + cleaned.trim() + '"';
  return '"' + cleaned.trim() + '"';
});

try {
  JSON.parse(repaired);
  // debug: show context around previously failing position (approx)
  const pos = 73714;
  const before = repaired.slice(Math.max(0,pos-80), pos);
  const after = repaired.slice(pos, pos+80);
  console.log('context before:', JSON.stringify(before));
  console.log('context after :', JSON.stringify(after));
  fs.writeFileSync(p, repaired, 'utf8');
  console.log('repaired and wrote file');
  process.exit(0);
} catch (e) {
  console.error('repair failed:', e.message);
  process.exit(1);
}
