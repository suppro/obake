const fs = require('fs');
const path = require('path');

const filePath = process.argv[2] || path.join('resources','views','kanji','index.blade.php');
const s = fs.readFileSync(filePath, 'utf8');

function isEscaped(str, i) {
  let backslashes = 0;
  for (let j = i-1; j >= 0 && str[j] === '\\'; j--) backslashes++;
  return backslashes % 2 === 1;
}

const stack = [];
const pairs = { '{': '}', '(': ')', '[': ']' };
const opening = new Set(Object.keys(pairs));
const closing = new Set(Object.values(pairs));

let inSingle = false, inDouble = false, inBacktick = false, inLineComment = false, inBlockComment = false;

for (let i = 0; i < s.length; i++) {
  const ch = s[i];
  const next = s[i+1];

  if (inLineComment) {
    if (ch === '\n') inLineComment = false;
    continue;
  }
  if (inBlockComment) {
    if (ch === '*' && next === '/') { inBlockComment = false; i++; }
    continue;
  }

  if (!inSingle && !inDouble && !inBacktick) {
    if (ch === '/' && next === '/') { inLineComment = true; i++; continue; }
    if (ch === '/' && next === '*') { inBlockComment = true; i++; continue; }
  }

  if (ch === "'" && !inDouble && !inBacktick && !isEscaped(s, i)) { inSingle = !inSingle; continue; }
  if (ch === '"' && !inSingle && !inBacktick && !isEscaped(s, i)) { inDouble = !inDouble; continue; }
  if (ch === '`' && !inSingle && !inDouble && !isEscaped(s, i)) { inBacktick = !inBacktick; continue; }

  if (inSingle || inDouble || inBacktick) continue;

  if (opening.has(ch)) {
    stack.push({ch, i});
  } else if (closing.has(ch)) {
    if (stack.length === 0) {
      const before = s.slice(Math.max(0, i-40), i+40);
      const line = s.slice(0, i).split(/\n/).length;
      console.error(`UNMATCHED CLOSING '${ch}' at index ${i} (approx line ${line})\n...${before}...`);
      process.exit(2);
    }
    const last = stack.pop();
    if (pairs[last.ch] !== ch) {
      const line = s.slice(0, i).split(/\n/).length;
      console.error(`MISMATCH ${last.ch} (at ${last.i}) ... ${ch} (at ${i}) approx line ${line}`);
      process.exit(3);
    }
  }
}

if (stack.length > 0) {
  const last = stack[stack.length-1];
  const line = s.slice(0, last.i).split(/\n/).length;
  console.error(`UNMATCHED OPENING '${last.ch}' at index ${last.i} (approx line ${line})`);
  process.exit(4);
}

console.log('Braces/parentheses/brackets appear balanced in raw file.');
process.exit(0);
