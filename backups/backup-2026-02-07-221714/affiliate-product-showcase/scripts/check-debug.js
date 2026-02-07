#!/usr/bin/env node

/**
 * Debug Artifact Scanner
 * 
 * Scans staged files for debug code that should not be committed:
 * - PHP: var_dump, var_export, print_r, dd, dump, error_log
 * - JS: console.log, console.debug, console.warn, debugger, alert
 * 
 * @version 1.0.0
 */

import { readFileSync } from 'fs';
import { execSync } from 'child_process';

// Color codes for terminal output
const colors = {
  reset: '\x1b[0m',
  red: '\x1b[31m',
  yellow: '\x1b[33m',
  green: '\x1b[32m',
  cyan: '\x1b[36m'
};

// Get staged files
let stagedFiles;
try {
  stagedFiles = execSync('git diff --cached --name-only --diff-filter=ACM', {
    encoding: 'utf8'
  })
    .split('\n')
    .filter(Boolean)
    .filter(file => 
      (file.endsWith('.php') || file.endsWith('.js') || file.endsWith('.jsx') || 
       file.endsWith('.ts') || file.endsWith('.tsx')) &&
      !file.includes('vendor/') &&
      !file.includes('node_modules/') &&
      !file.includes('build/') &&
      !file.includes('dist/') &&
      !file.includes('tests/') &&
      !file.includes('.min.js')
    );
} catch (error) {
  console.error(`${colors.red}Error getting staged files:${colors.reset}`, error.message);
  process.exit(1);
}

if (stagedFiles.length === 0) {
  console.log(`${colors.green}✅ No PHP/JS files staged for commit${colors.reset}`);
  process.exit(0);
}

// PHP debug patterns
const phpPatterns = [
  { pattern: /\bvar_dump\s*\(/g, name: 'var_dump()', severity: 'CRITICAL' },
  { pattern: /\bvar_export\s*\(/g, name: 'var_export()', severity: 'CRITICAL' },
  { pattern: /\bprint_r\s*\(/g, name: 'print_r()', severity: 'CRITICAL' },
  { pattern: /\bdd\s*\(/g, name: 'dd()', severity: 'CRITICAL' },
  { pattern: /\bdump\s*\(/g, name: 'dump()', severity: 'CRITICAL' },
  { pattern: /\berror_log\s*\(/g, name: 'error_log()', severity: 'WARNING' }
];

// JavaScript debug patterns
const jsPatterns = [
  { pattern: /\bconsole\.log\s*\(/g, name: 'console.log()', severity: 'CRITICAL' },
  { pattern: /\bconsole\.debug\s*\(/g, name: 'console.debug()', severity: 'CRITICAL' },
  { pattern: /\bconsole\.warn\s*\(/g, name: 'console.warn()', severity: 'WARNING' },
  { pattern: /\bconsole\.error\s*\(/g, name: 'console.error()', severity: 'WARNING' },
  { pattern: /\bdebugger\s*;/g, name: 'debugger;', severity: 'CRITICAL' },
  { pattern: /\balert\s*\(/g, name: 'alert()', severity: 'CRITICAL' }
];

// Whitelist patterns (acceptable debug code)
const whitelist = [
  /if\s*\(\s*(?:defined\s*\(\s*)?WP_DEBUG(?:\s*\))?\s*(?:&&|and)\s*/i,
  /\/\*\*.*@debug.*\*\//,
  /\/\/\s*@debug/i,
  /\/\/\s*DEBUG:/i,
  /\/\*\s*DEBUG:/i
];

let violations = [];

console.log(`${colors.cyan}🔍 Scanning ${stagedFiles.length} staged files for debug code...${colors.reset}\n`);

for (const file of stagedFiles) {
  try {
    const content = readFileSync(file, 'utf8');
    const patterns = file.endsWith('.php') ? phpPatterns : jsPatterns;
    const lines = content.split('\n');

    for (const { pattern, name, severity } of patterns) {
      const matches = [...content.matchAll(pattern)];
      
      for (const match of matches) {
        const lineNumber = content.substring(0, match.index).split('\n').length;
        const line = lines[lineNumber - 1].trim();
        
        // Check whitelist
        const isWhitelisted = whitelist.some(wl => wl.test(line));
        if (isWhitelisted) continue;

        // Check if it's in a comment
        if (line.startsWith('//') || line.startsWith('/*') || line.startsWith('*')) {
          continue;
        }

        violations.push({
          file,
          line: lineNumber,
          code: name,
          severity,
          content: line.substring(0, 80) + (line.length > 80 ? '...' : '')
        });
      }
    }
  } catch (error) {
    console.error(`${colors.red}Error reading ${file}:${colors.reset}`, error.message);
  }
}

// Report violations
if (violations.length > 0) {
  console.error(`\n${colors.red}❌ Debug artifacts detected in staged files:${colors.reset}\n`);
  
  const critical = violations.filter(v => v.severity === 'CRITICAL');
  const warnings = violations.filter(v => v.severity === 'WARNING');

  if (critical.length > 0) {
    console.error(`${colors.red}🔴 CRITICAL (commit blocked):${colors.reset}`);
    critical.forEach(v => {
      console.error(`  ${v.file}:${v.line} - ${v.code}`);
      console.error(`    ${colors.yellow}${v.content}${colors.reset}`);
    });
  }

  if (warnings.length > 0) {
    console.warn(`\n${colors.yellow}⚠️  WARNINGS (review recommended):${colors.reset}`);
    warnings.forEach(v => {
      console.warn(`  ${v.file}:${v.line} - ${v.code}`);
      console.warn(`    ${v.content}`);
    });
  }

  console.error(`\n${colors.red}Remove debug code before committing.${colors.reset}`);
  console.error(`${colors.cyan}Tip: Use conditional debug statements like:${colors.reset}`);
  console.error(`  PHP: if (WP_DEBUG) { error_log(...); }`);
  console.error(`  JS:  if (process.env.NODE_ENV === 'development') { console.log(...); }\n`);
  
  process.exit(critical.length > 0 ? 1 : 0);
}

console.log(`${colors.green}✅ No debug artifacts detected${colors.reset}`);
process.exit(0);
