const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const vm = require('vm');

const dir = 'c:/xampp/htdocs/calibration certificate/certificates';
const files = fs.readdirSync(dir).filter(f => f.endsWith('.php'));

console.log(`Checking syntax for ${files.length} PHP files...`);

for (const file of files) {
  try {
    const html = execSync(`C:\\xampp\\php\\php.exe "c:\\xampp\\htdocs\\calibration certificate\\scratch\\render_page.php" "${file}"`, {
      encoding: 'utf8'
    });
    
    const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
    let match;
    let count = 0;
    while ((match = scriptRegex.exec(html)) !== null) {
      const code = match[1];
      count++;
      if (!code.trim()) continue;
      
      try {
        new vm.Script(code);
      } catch (err) {
        console.error(`\n❌ SYNTAX ERROR in ${file} (Script #${count}): ${err.message}`);
        const lines = code.split('\n');
        const matchLine = err.stack.match(/evalmachine\.<anonymous>:(\d+)/);
        if (matchLine) {
          const lineNum = parseInt(matchLine[1], 10);
          console.error(`Line ${lineNum}: ${lines[lineNum - 1]}`);
        } else {
          console.error(err.stack);
        }
      }
    }
  } catch (err) {
    console.error(`Failed to execute PHP for ${file}:`, err.message);
  }
}
console.log('Finished checking all files.');
