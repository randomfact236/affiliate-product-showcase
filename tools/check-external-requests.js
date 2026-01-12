#!/usr/bin/env node

/**
 * External Request Scanner
 * 
 * Scans project files for suspicious external resource patterns
 * to ensure the plugin remains 100% standalone and privacy-first.
 * 
 * Usage: node tools/check-external-requests.js [directory]
 * Example: node tools/check-external-requests.js wp-content/plugins/affiliate-product-showcase
 * 
 * @version 1.0.0
 * @date January 13, 2026
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Configuration
const CONFIG = {
	// Directories to scan (relative to project root)
	directories: [
		'wp-content/plugins/affiliate-product-showcase',
	],
	
	// File extensions to scan
	extensions: ['.php', '.js', '.ts', '.vue', '.css', '.scss', '.less', '.json', '.html'],
	
	// Patterns that indicate external requests
	patterns: [
		// JavaScript fetch/XHR
		{
			name: 'fetch() call',
			regex: /\bfetch\s*\(/gi,
			severity: 'HIGH',
			description: 'Direct fetch() call to external URL'
		},
		{
			name: 'XMLHttpRequest',
			regex: /XMLHttpRequest|new\s+XHR/gi,
			severity: 'HIGH',
			description: 'XMLHttpRequest object instantiation'
		},
		{
			name: 'axios.get/post/etc',
			regex: /\baxios\.\w+\s*\(/gi,
			severity: 'HIGH',
			description: 'Axios HTTP request'
		},
		
		// Image loading
		{
			name: 'new Image()',
			regex: /new\s+Image\s*\(\)/gi,
			severity: 'MEDIUM',
			description: 'Image object creation (could be used for tracking)'
		},
		
		// CSS external resources
		{
			name: 'CSS url() with http/https',
			regex: /url\s*\(\s*['"]?(?:https?:|\/\/)/gi,
			severity: 'MEDIUM',
			description: 'External URL in CSS url() function'
		},
		{
			name: '@import with http/https',
			regex: /@import\s+['"]?(?:https?:|\/\/)/gi,
			severity: 'HIGH',
			description: 'External CSS import'
		},
		
		// PHP external requests
		{
			name: 'wp_remote_get/post/request',
			regex: /\bwp_remote_(get|post|request|head)\s*\(/gi,
			severity: 'MEDIUM',
			description: 'WordPress remote API call'
		},
		{
			name: 'curl_init',
			regex: /\bcurl_init\s*\(/gi,
			severity: 'HIGH',
			description: 'cURL initialization'
		},
		{
			name: 'file_get_contents with http',
			regex: /\bfile_get_contents\s*\([^)]*https?:/gi,
			severity: 'HIGH',
			description: 'file_get_contents with HTTP URL'
		},
		{
			name: 'fopen with http',
			regex: /\bfopen\s*\([^)]*https?:/gi,
			severity: 'HIGH',
			description: 'fopen with HTTP URL'
		},
		
		// External scripts/styles
		{
			name: '<script src="http',
			regex: /<script[^>]+src\s*=\s*['"]https?:/gi,
			severity: 'CRITICAL',
			description: 'External script tag'
		},
		{
			name: '<link rel="stylesheet" http',
			regex: /<link[^>]+href\s*=\s*['"]https?:[^>]*stylesheet/gi,
			severity: 'CRITICAL',
			description: 'External stylesheet link'
		},
		
		// Common tracking/analytics domains
		{
			name: 'Google Analytics',
			regex: /google-analytics\.com|googletagmanager\.com|gtag\(|ga\(/gi,
			severity: 'CRITICAL',
			description: 'Google Analytics tracking code'
		},
		{
			name: 'Facebook Pixel',
			regex: /connect\.facebook\.net|fbq\(|facebook\.com\/tr\//gi,
			severity: 'CRITICAL',
			description: 'Facebook Pixel tracking code'
		},
		{
			name: 'Other analytics',
			regex: /(segment\.io|mixpanel\.com|amplitude\.com|hotjar\.com|fullstory\.com)/gi,
			severity: 'CRITICAL',
			description: 'Third-party analytics service'
		},
		
		// CDNs and external libraries
		{
			name: 'CDN references',
			regex: /(cdn\.(jsdelivr|cloudflare|unpkg|cdnjs)\.com)|(cdnjs\.cloudflare\.com)|(unpkg\.com)|(jsdelivr\.net)/gi,
			severity: 'CRITICAL',
			description: 'CDN usage'
		},
		{
			name: 'Google Fonts',
			regex: /fonts\.(googleapis|gstatic)\.com/gi,
			severity: 'HIGH',
			description: 'Google Fonts external resource'
		},
		{
			name: 'Cloudflare',
			regex: /cloudflare\.com\/(ajax|cdn)/gi,
			severity: 'HIGH',
			description: 'Cloudflare CDN'
		},
		
		// Data exfiltration patterns
		{
			name: 'base64 encoded URL (potential data exfiltration)',
			regex: /['"](?:https?:)?\/\/[a-z0-9]+\/[a-z0-9]+==['"]/gi,
			severity: 'MEDIUM',
			description: 'Potential base64-encoded tracking URL'
		},
	],
	
	// Allowed domains/patterns (false positives to ignore)
	allowedPatterns: [
		// WordPress.org URLs (allowed for updates/documentation)
		/wordpress\.org/gi,
		// Localhost references (for development)
		/localhost/gi,
		/127\.0\.0\.1/gi,
		// Data URLs
		/^data:/,
		// Blob URLs (for local file access)
		/^blob:/,
	],
};

// ANSI color codes for terminal output
const colors = {
	reset: '\x1b[0m',
	bright: '\x1b[1m',
	dim: '\x1b[2m',
	
	red: '\x1b[31m',
	green: '\x1b[32m',
	yellow: '\x1b[33m',
	blue: '\x1b[34m',
	magenta: '\x1b[35m',
	cyan: '\x1b[36m',
	
	bgRed: '\x1b[41m',
	bgGreen: '\x1b[42m',
	bgYellow: '\x1b[43m',
};

// Severity levels
const SEVERITY_LEVELS = {
	CRITICAL: { color: colors.bgRed, order: 0 },
	HIGH: { color: colors.red, order: 1 },
	MEDIUM: { color: colors.yellow, order: 2 },
	LOW: { color: colors.cyan, order: 3 },
};

/**
 * Get all files matching extensions in directory recursively
 */
function getFiles(dir, extensions) {
	const files = [];
	
	if (!fs.existsSync(dir)) {
		return files;
	}
	
	const entries = fs.readdirSync(dir, { withFileTypes: true });
	
	for (const entry of entries) {
		const fullPath = path.join(dir, entry.name);
		
		if (entry.isDirectory()) {
			// Skip common exclusion directories
			if (['node_modules', 'vendor', '.git', 'dist', 'build', 'coverage'].includes(entry.name)) {
				continue;
			}
			files.push(...getFiles(fullPath, extensions));
		} else if (entry.isFile()) {
			const ext = path.extname(entry.name);
			if (extensions.includes(ext)) {
				files.push(fullPath);
			}
		}
	}
	
	return files;
}

/**
 * Check if a URL or pattern is allowed
 */
function isAllowed(text) {
	for (const pattern of CONFIG.allowedPatterns) {
		if (pattern.test(text)) {
			return true;
		}
	}
	return false;
}

/**
 * Scan a single file for external request patterns
 */
function scanFile(filePath) {
	const findings = [];
	
	try {
		const content = fs.readFileSync(filePath, 'utf8');
		const lines = content.split('\n');
		
		for (const pattern of CONFIG.patterns) {
			for (let i = 0; i < lines.length; i++) {
				const line = lines[i];
				const matches = line.matchAll(pattern.regex);
				
				for (const match of matches) {
					const matchedText = match[0] || '';
					
					// Skip if this is an allowed pattern
					if (isAllowed(matchedText)) {
						continue;
					}
					
					findings.push({
						line: i + 1,
						column: match.index + 1,
						pattern: pattern.name,
						severity: pattern.severity,
						description: pattern.description,
						match: matchedText.trim(),
						filePath,
					});
				}
			}
		}
	} catch (error) {
		console.error(`${colors.red}Error reading file: ${filePath}${colors.reset}`);
		console.error(error.message);
	}
	
	return findings;
}

/**
 * Print a single finding
 */
function printFinding(finding) {
	const severityConfig = SEVERITY_LEVELS[finding.severity];
	const severityColor = severityConfig ? severityConfig.color : colors.reset;
	
	console.log(`\n${severityColor}[${finding.severity}]${colors.reset} ${colors.bright}${finding.pattern}${colors.reset}`);
	console.log(`  ${colors.dim}File:${colors.reset} ${finding.filePath}`);
	console.log(`  ${colors.dim}Line:${colors.reset} ${finding.line}:${finding.column}`);
	console.log(`  ${colors.dim}Description:${colors.reset} ${finding.description}`);
	console.log(`  ${colors.dim}Match:${colors.reset} ${finding.match}`);
}

/**
 * Main scan function
 */
function scan() {
	console.log(`\n${colors.bright}${colors.cyan}╔════════════════════════════════════════════════════════════╗${colors.reset}`);
	console.log(`${colors.bright}${colors.cyan}║   External Request Scanner - Affiliate Product Showcase    ║${colors.reset}`);
	console.log(`${colors.bright}${colors.cyan}╚════════════════════════════════════════════════════════════╝${colors.reset}\n`);

	const targetDir = process.argv[2];
	const scanDirs = targetDir ? [targetDir] : CONFIG.directories;
	
	console.log(`${colors.dim}Scanning directories:${colors.reset}`);
	for (const dir of scanDirs) {
		console.log(`  ${colors.cyan}•${colors.reset} ${dir}`);
	}
	console.log();

	let totalFiles = 0;
	let totalFindings = 0;
	let criticalFindings = 0;
	let highFindings = 0;
	let mediumFindings = 0;

	const allFindings = [];

	// Scan each directory
	for (const dir of scanDirs) {
		const files = getFiles(dir, CONFIG.extensions);
		totalFiles += files.length;
		
		console.log(`${colors.dim}Scanning ${files.length} files...${colors.reset}`);
		
		for (const file of files) {
			const findings = scanFile(file);
			allFindings.push(...findings);
			totalFindings += findings.length;
		}
	}

	// Sort findings by severity and line number
	allFindings.sort((a, b) => {
		const severityOrder = SEVERITY_LEVELS[a.severity].order - SEVERITY_LEVELS[b.severity].order;
		if (severityOrder !== 0) return severityOrder;
		return a.line - b.line;
	});

	// Count by severity
	for (const finding of allFindings) {
		switch (finding.severity) {
			case 'CRITICAL': criticalFindings++; break;
			case 'HIGH': highFindings++; break;
			case 'MEDIUM': mediumFindings++; break;
		}
	}

	// Print summary
	console.log(`\n${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}`);
	console.log(`${colors.bright}${colors.cyan}SCAN SUMMARY${colors.reset}`);
	console.log(`${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}\n`);

	console.log(`Files scanned: ${totalFiles}`);
	console.log(`Total findings: ${totalFindings}`);
	console.log();

	if (totalFindings > 0) {
		console.log(`${colors.red}Critical: ${criticalFindings}${colors.reset}`);
		console.log(`${colors.red}High:      ${highFindings}${colors.reset}`);
		console.log(`${colors.yellow}Medium:    ${mediumFindings}${colors.reset}`);
		console.log();
	}

	// Print findings
	if (allFindings.length > 0) {
		console.log(`${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}`);
		console.log(`${colors.bright}${colors.cyan}FINDINGS${colors.reset}`);
		console.log(`${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}\n`);

		for (const finding of allFindings) {
			printFinding(finding);
		}

		console.log(`\n${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}`);
		console.log(`\n${colors.yellow}⚠️  Issues found! Review the findings above to ensure the plugin remains 100% standalone.${colors.reset}\n`);
		console.log(`${colors.dim}Some findings may be false positives. Review each one carefully.${colors.reset}\n`);
	} else {
		console.log(`${colors.bgGreen}${colors.white}${colors.bright}✓ NO EXTERNAL REQUESTS DETECTED${colors.reset}\n`);
		console.log(`${colors.green}The plugin appears to be 100% standalone with no external dependencies.${colors.reset}\n`);
	}

	console.log(`${colors.bright}${colors.cyan}════════════════════════════════════════════════════════════${colors.reset}\n`);

	// Exit with error code if critical/high findings
	if (criticalFindings > 0 || highFindings > 0) {
		process.exit(1);
	}
}

// Run the scanner
try {
	scan();
} catch (error) {
	console.error(`${colors.red}Fatal error:${colors.reset}`, error.message);
	process.exit(1);
}
