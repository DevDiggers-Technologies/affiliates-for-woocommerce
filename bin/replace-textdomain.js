const fs = require('fs');
const path = require('path');

const targetDir = 'devdiggers-framework';

/**
 * Get text domain from a PHP file header
 */
function getTextDomainFromFile(filePath) {
    if (fs.existsSync(filePath)) {
        const content = fs.readFileSync(filePath, 'utf8');
        const match = content.match(/^[ \t/*#@]*Text Domain:\s*(.*)$/mi);
        if (match && match[1]) {
            return match[1].trim();
        }
    }
    return null;
}

const targetDomain = getTextDomainFromFile(path.resolve(__dirname, '../functions.php')) || process.env.npm_package_name || path.basename(path.resolve(__dirname, '..'));
const currentFrameworkDomain = getTextDomainFromFile(path.resolve(__dirname, '../devdiggers-framework/init.php'));

if (!targetDomain) {
    console.error('Error: Could not determine target domain.');
    process.exit(1);
}

const sourceDomains = ['devdiggers-framework'];
if (currentFrameworkDomain && currentFrameworkDomain !== targetDomain) {
    sourceDomains.push(currentFrameworkDomain);
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
            let updated = false;

            sourceDomains.forEach(sourceDomain => {
                if (content.includes(sourceDomain)) {
                    const regex = new RegExp(sourceDomain, 'g');
                    content = content.replace(regex, targetDomain);
                    updated = true;
                }
            });

            if (updated) {
                fs.writeFileSync(filePath, content);
                console.log('Updated text domain in: ' + filePath);
            }
        }
    });
}

console.log('Replacing text domains [' + sourceDomains.join(', ') + '] with "' + targetDomain + '" in "' + targetDir + '"...');
walk(targetDir);
console.log('Done!');
