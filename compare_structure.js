const fs = require('fs');
const path = require('path');

// We consider 'dist/' as the built "plugin folder"
// We consider '.' (root) as the root directory

const ROOT_PATH = '.';
const PLUGIN_PATH = 'dist';

// Folders to ignore entirely during comparison
const IGNORE_PATHS = new Set([
  '.git',
  'node_modules',
  'src',     // source files might differ from build output
  'plan',    // plan files are dev only
  'docs',
  'tests',
  'tmp',
  'scripts',
  'docker',
  'wp-admin', // core
  'wp-includes', // core
  'wp-content', // core
  'dist', // plugin folder itself
  '.github',
  '.githooks'
]);

// Files that are standard/dev config and not part of plugin package
const IGNORE_FILES = new Set([
  '.gitignore',
  '.gitattributes',
  '.editorconfig',
  '.env.example',
  '.assistant_rules.md',
  'scan_repo_structure.js',
  'compare_structure.js',
  'package.json',
  'package-lock.json',
  'composer.json',
  'composer.lock',
  'phpunit.xml',
  'tsconfig.json',
  'vite.config.ts',
  'tailwind.config.js',
  'postcss.config.js',
  'Makefile',
  '.eslintrc.cjs',
  '.prettierrc',
  '.stylelintrc.cjs',
  'README.md',
  'CHANGELOG.md',
  'SECURITY.md',
  'CODE_OF_CONDUCT.md',
  'CONTRIBUTING.md',
  'license.txt',
  'LICENSE',
  'readme.html',
  'index.html',
  'report.md',
  '.htaccess'
]);

// WordPress core files often found at root in dev env
const CORE_FILES = [
  'index.php',
  'wp-config.php',
  'wp-config-sample.php',
  'wp-settings.php',
  'wp-load.php',
  'wp-blog-header.php',
  'wp-comments-post.php',
  'wp-mail.php',
  'wp-activate.php',
  'xmlrpc.php',
  'wp-cron.php',
  'wp-links-opml.php',
  'wp-login.php',
  'wp-signup.php',
  'wp-trackback.php',
  'wp-admin', // dir
  'wp-includes', // dir
  'wp-content' // dir
];

function getFiles(dir) {
  if (!fs.existsSync(dir)) return [];
  const result = [];
  
  function walk(currentDir) {
    const items = fs.readdirSync(currentDir);
    for (const item of items) {
      const fullPath = path.join(currentDir, item);
      const relPath = path.relative(ROOT_PATH, fullPath);
      const stat = fs.statSync(fullPath);
      
      if (IGNORE_PATHS.has(item) || IGNORE_PATHS.has(relPath)) continue;
      
      if (stat.isDirectory()) {
        walk(fullPath);
      } else {
        if (IGNORE_FILES.has(item)) continue;
        // Also ignore core files at root
        if (CORE_FILES.includes(item)) continue;
        result.push(relPath);
      }
    }
  }
  
  walk(dir);
  return result;
}

function main() {
  console.log('=== STRUCTURE COMPARISON REPORT ===');
  console.log(`Comparing Root ('.') vs Plugin Folder ('${PLUGIN_PATH}')`);
  console.log('');

  const rootFiles = new Set(getFiles('.'));
  const pluginFiles = new Set(getFiles(PLUGIN_PATH));

  const duplicates = [];
  const rootOnly = [];
  const pluginOnly = [];

  // 1. Duplicates
  for (const f of rootFiles) {
    if (pluginFiles.has(f)) duplicates.push(f);
  }

  // 2. Root Only (exist at root, not in plugin folder)
  for (const f of rootFiles) {
    if (!pluginFiles.has(f)) rootOnly.push(f);
  }

  // 3. Plugin Only (exist in plugin folder, not at root)
  for (const f of pluginFiles) {
    if (!rootFiles.has(f)) pluginOnly.push(f);
  }

  if (duplicates.length > 0) {
    console.log('1. DUPLICATES (Exist in both Root and Plugin Folder)');
    duplicates.forEach(f => console.log('   [DUP] ' + f));
    console.log('');
  }

  if (rootOnly.length > 0) {
    console.log('2. ROOT ONLY (Exist at Root, NOT in Plugin Folder)');
    rootOnly.forEach(f => console.log('   [OUT] ' + f));
    console.log('');
  }

  if (pluginOnly.length > 0) {
    console.log('3. PLUGIN ONLY (Exist in Plugin Folder, NOT at Root)');
    pluginOnly.forEach(f => console.log('   [IN]  ' + f));
    console.log('');
  }
  
  console.log('--- Report Finished ---');
}

main();
