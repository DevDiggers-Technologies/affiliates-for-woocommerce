const fs = require('fs');
const path = require('path');

const targetDir = 'devdiggers-framework';
const sourceDomain = 'devdiggers-framework';
const targetDomain = process.env.npm_package_name || path.basename(path.resolve(__dirname, '..'));

if (!targetDomain) {
    console.error('Error: Could not determine target domain.');
    process.exit(1);
}

function walk(dir) {
    if (!fs.existsSync(dir)) return;
    
    fs.readdirSync(dir).forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);
        
        if (stat.isDirectory()) {
            walk(filePath);
        } else if (filePath.endsWith('.php')) {
            let content = fs.readFileSync(filePath, 'utf8');
            if (content.includes(sourceDomain)) {
                const regex = new RegExp(sourceDomain, 'g');
                content = content.replace(regex, targetDomain);
                fs.writeFileSync(filePath, content);
                console.log(`Updated text domain in: ${filePath}`);
            }
        }
    });
}

console.log(`Replacing text domain "${sourceDomain}" with "${targetDomain}" in "${targetDir}"...`);
walk(targetDir);
console.log('Done!');
