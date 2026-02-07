import { createHash } from 'crypto';
import { promises as fs } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');
const outputFile = path.join(rootDir, 'external-requests-report.json');

interface ExternalRequest {
	file: string;
	line: number;
	type: string;
	url: string;
	domain: string;
}

interface ExternalRequestsReport {
	totalRequests: number;
	requestsByFile: Record<string, ExternalRequest[]>;
	requestsByDomain: Record<string, number>;
	suspiciousRequests: ExternalRequest[];
	scanTime: string;
}

// Common external request patterns
const EXTERNAL_PATTERNS = [
	// HTTP/HTTPS requests
	{
		pattern: /(https?:\/\/[^\s'"]+)/gi,
		type: 'HTTP/HTTPS Request',
		isSuspicious: (url: string): boolean => {
			const domain = new URL(url).hostname;
			// Whitelist common safe domains
			const safeDomains = [
				'wordpress.org',
				'wordpress.com',
				'cdn.jsdelivr.net',
				'cdnjs.cloudflare.com',
				'fonts.googleapis.com',
				'fonts.gstatic.com',
				'api.wordpress.org',
				'downloads.wordpress.org',
			];
			return !safeDomains.some((safe) => domain.includes(safe));
		},
	},
	// API calls
	{
		pattern: /(fetch\s*\(|axios\(|wp_remote_get\s*\(|wp_remote_post\s*\()[^;)]+https?:\/\/[^\s'")]+/gi,
		type: 'API Call',
		isSuspicious: (url: string): boolean => {
			// Check for non-HTTPS or suspicious patterns
			if (url.startsWith('http://')) return true;
			return false;
		},
	},
	// External script includes
	{
		pattern: /<script[^>]*src=["'](https?:\/\/[^"']+)["']/gi,
		type: 'External Script',
		isSuspicious: (url: string): boolean => {
			const domain = new URL(url).hostname;
			const safeCdnDomains = [
				'cdn.jsdelivr.net',
				'cdnjs.cloudflare.com',
				'unpkg.com',
				'npmcdn.com',
			];
			return !safeCdnDomains.some((safe) => domain.includes(safe));
		},
	},
	// External stylesheets
	{
		pattern: /<link[^>]*stylesheet[^>]*href=["'](https?:\/\/[^"']+)["']/gi,
		type: 'External Stylesheet',
		isSuspicious: (url: string): boolean => {
			const domain = new URL(url).hostname;
			const safeCdnDomains = [
				'cdn.jsdelivr.net',
				'cdnjs.cloudflare.com',
			];
			return !safeCdnDomains.some((safe) => domain.includes(safe));
		},
	},
];

// File extensions to scan
const SCAN_EXTENSIONS = [
	'.php',
	'.js',
	'.jsx',
	'.ts',
	'.tsx',
	'.html',
	'.css',
	'.scss',
];

// Directories to skip
const SKIP_DIRECTORIES = [
	'node_modules',
	'vendor',
	'.git',
	'build',
	'dist',
	'.vscode',
	'.idea',
	'coverage',
];

export async function walk(dir: string): Promise<string[]> {
	const entries = await fs.readdir(dir, { withFileTypes: true });
	const files = await Promise.all(
		entries.map(async (entry) => {
			const fullPath = path.join(dir, entry.name);
			if (entry.isDirectory()) {
				// Skip certain directories
				if (SKIP_DIRECTORIES.includes(entry.name)) {
					return [];
				}
				return walk(fullPath);
			}
			return [fullPath];
		})
	);

	return files.flat();
}

export function shouldScan(filePath: string): boolean {
	const ext = path.extname(filePath).toLowerCase();
	return SCAN_EXTENSIONS.includes(ext);
}

export async function scanFile(filePath: string): Promise<ExternalRequest[]> {
	const content = await fs.readFile(filePath, 'utf8');
	const requests: ExternalRequest[] = [];
	const lines = content.split('\n');

	for (const pattern of EXTERNAL_PATTERNS) {
		const matches = content.matchAll(pattern.pattern);

		for (const match of matches) {
			const url = match[1] || match[0];
			const matchIndex = match.index || 0;
			
			// Find line number
			let lineNum = 1;
			let charCount = 0;
			for (let i = 0; i < lines.length; i++) {
				if (charCount + lines[i].length >= matchIndex) {
					lineNum = i + 1;
					break;
				}
				charCount += lines[i].length + 1; // +1 for newline
			}

			// Extract domain from URL
			let domain = 'unknown';
			try {
				domain = new URL(url).hostname;
			} catch (e) {
				// Invalid URL, try to extract domain manually
				const domainMatch = url.match(/https?:\/\/([^\/]+)/);
				if (domainMatch) {
					domain = domainMatch[1];
				}
			}

			requests.push({
				file: path.relative(rootDir, filePath).replace(/\\/g, '/'),
				line: lineNum,
				type: pattern.type,
				url: url,
				domain: domain,
			});
		}
	}

	return requests;
}

export async function main(): Promise<void> {
	try {
		console.log('Scanning for external requests...');
		
		// Scan all files in project
		const allFiles = await walk(rootDir);
		const filesToScan = allFiles.filter((file) => shouldScan(file));
		
		console.log(`Found ${filesToScan.length} files to scan...`);
		
		const allRequests: ExternalRequest[] = [];
		const requestsByFile: Record<string, ExternalRequest[]> = {};
		const requestsByDomain: Record<string, number> = {};

		// Scan each file
		for (const file of filesToScan) {
			const requests = await scanFile(file);
			if (requests.length > 0) {
				allRequests.push(...requests);
				const relativePath = path.relative(rootDir, file).replace(/\\/g, '/');
				requestsByFile[relativePath] = requests;
				
				// Count by domain
				for (const request of requests) {
					requestsByDomain[request.domain] = (requestsByDomain[request.domain] || 0) + 1;
				}
			}
		}

		// Identify suspicious requests
		const suspiciousRequests: ExternalRequest[] = [];
		for (const pattern of EXTERNAL_PATTERNS) {
			for (const request of allRequests) {
				if (request.type === pattern.type && pattern.isSuspicious(request.url)) {
					suspiciousRequests.push(request);
				}
			}
		}

		// Create report
		const report: ExternalRequestsReport = {
			totalRequests: allRequests.length,
			requestsByFile,
			requestsByDomain,
			suspiciousRequests,
			scanTime: new Date().toISOString(),
		};

		// Write report
		await fs.writeFile(outputFile, JSON.stringify(report, null, 2), 'utf8');
		
		// Print summary
		console.log(`\n✅ Scan complete!`);
		console.log(`Total external requests found: ${allRequests.length}`);
		console.log(`Files with external requests: ${Object.keys(requestsByFile).length}`);
		console.log(`Unique domains: ${Object.keys(requestsByDomain).length}`);
		console.log(`Suspicious requests: ${suspiciousRequests.length}`);
		console.log(`\nTop domains by request count:`);
		
		const sortedDomains = Object.entries(requestsByDomain)
			.sort(([, a], [, b]) => b - a)
			.slice(0, 10);
		
		for (const [domain, count] of sortedDomains) {
			console.log(`  ${domain}: ${count} requests`);
		}

		if (suspiciousRequests.length > 0) {
			console.log(`\n⚠️  ${suspiciousRequests.length} suspicious requests found:`);
			for (const req of suspiciousRequests) {
				console.log(`  ${req.file}:${req.line} - ${req.type}`);
				console.log(`    URL: ${req.url}`);
			}
		}

		console.log(`\n📄 Report saved to: ${outputFile}`);
		
		process.exitCode = suspiciousRequests.length > 0 ? 1 : 0;
	} catch (error) {
		console.error('❌ External request scan failed:', error instanceof Error ? error.message : error);
		process.exitCode = 1;
	}
}

main();
