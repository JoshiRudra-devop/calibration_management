const fs = require('fs');
const vm = require('vm');

const html = fs.readFileSync(process.argv[2], 'utf8');
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = scriptRegex.exec(html)) !== null) {
  const code = match[1];
  count++;
  if (!code.trim()) continue;
  try {
    new vm.Script(code);
    console.log(`Script ${count} is valid.`);
  } catch (err) {
    console.error(`Error in script ${count}:`, err.message);
    const lines = code.split('\n');
    console.error(err.stack);
    // Find the line number
    const matchLine = err.stack.match(/evalmachine\.<anonymous>:(\d+)/);
    if (matchLine) {
      const lineNum = parseInt(matchLine[1], 10);
      console.error(`Error line (${lineNum}): ${lines[lineNum - 1]}`);
    }
  }
}
