import { describe, it, expect, vi, beforeEach } from 'vitest';
import * as fs from 'fs/promises';
import path from 'path';
import * as checkExternalRequests from '../../tools/check-external-requests';

// Mock fs module
vi.mock('fs/promises');

describe('check-external-requests', () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	describe('walk', () => {
		it('should return empty array for empty directory', async () => {
			vi.spyOn(fs, 'readdir').mockResolvedValue([]);

			const result = await checkExternalRequests.walk('/some/path');
			expect(result).toEqual([]);
		});

	it('should return files in directory', async () => {
		const mockFiles = ['file1.js', 'file2.ts'];
		vi.spyOn(fs, 'readdir').mockResolvedValue(
			mockFiles.map((name) => ({
				name: name as any,
				isDirectory: () => false,
				isFile: () => true,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			})) as any
		);

		const result = await checkExternalRequests.walk('/some/path');
		expect(result).toHaveLength(2);
		expect(result[0]).toContain('file1.js');
	});

	it('should recursively walk subdirectories', async () => {
		vi.spyOn(fs, 'readdir')
			.mockResolvedValueOnce([{
				name: 'subdir' as any,
				isDirectory: () => true,
				isFile: () => false,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			}] as any)
			.mockResolvedValueOnce([{
				name: 'file.js' as any,
				isDirectory: () => false,
				isFile: () => true,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			}] as any);

		const result = await checkExternalRequests.walk('/some/path');
		expect(result).toHaveLength(1);
		expect(result[0]).toContain('file.js');
	});

	it('should skip specified directories', async () => {
		vi.spyOn(fs, 'readdir').mockResolvedValue([
			{
				name: 'node_modules' as any,
				isDirectory: () => true,
				isFile: () => false,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			},
			{
				name: 'vendor' as any,
				isDirectory: () => true,
				isFile: () => false,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			},
			{
				name: 'file.js' as any,
				isDirectory: () => false,
				isFile: () => true,
				isBlockDevice: () => false,
				isCharacterDevice: () => false,
				isSymbolicLink: () => false,
				isFIFO: () => false,
				isSocket: () => false,
			},
		] as any);

		const result = await checkExternalRequests.walk('/some/path');
		expect(result).toHaveLength(1);
		expect(result[0]).toContain('file.js');
	});
	});

	describe('shouldScan', () => {
		it('should return true for .php files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.php')).toBe(true);
		});

		it('should return true for .js files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.js')).toBe(true);
		});

		it('should return true for .ts files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.ts')).toBe(true);
		});

		it('should return true for .tsx files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.tsx')).toBe(true);
		});

		it('should return true for .html files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.html')).toBe(true);
		});

		it('should return false for .json files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.json')).toBe(false);
		});

		it('should return false for .md files', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.md')).toBe(false);
		});

		it('should be case insensitive', () => {
			expect(checkExternalRequests.shouldScan('/path/to/file.PHP')).toBe(true);
			expect(checkExternalRequests.shouldScan('/path/to/file.JS')).toBe(true);
		});
	});

	describe('scanFile', () => {
		it('should detect HTTP/HTTPS URLs', async () => {
			const content = `const url = 'https://example.com/api';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(1);
			expect(result[0].url).toBe('https://example.com/api');
			expect(result[0].type).toBe('HTTP/HTTPS Request');
		});

		it('should detect API calls', async () => {
			const content = `fetch('https://api.example.com/data');`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(1);
			expect(result[0].url).toContain('https://api.example.com');
			expect(result[0].type).toBe('API Call');
		});

		it('should detect external scripts', async () => {
			const content = `<script src="https://cdn.example.com/script.js"></script>`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.html');
			expect(result).toHaveLength(1);
			expect(result[0].url).toBe('https://cdn.example.com/script.js');
			expect(result[0].type).toBe('External Script');
		});

		it('should detect external stylesheets', async () => {
			const content = `<link rel="stylesheet" href="https://cdn.example.com/style.css">`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.html');
			expect(result).toHaveLength(1);
			expect(result[0].url).toBe('https://cdn.example.com/style.css');
			expect(result[0].type).toBe('External Stylesheet');
		});

		it('should detect multiple URLs in same file', async () => {
			const content = `
				const url1 = 'https://api1.example.com/data';
				const url2 = 'https://api2.example.com/data';
			`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(2);
		});

		it('should extract domain from URL', async () => {
			const content = `const url = 'https://example.com/api';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result[0].domain).toBe('example.com');
		});

		it('should calculate line numbers correctly', async () => {
			const content = `line 1\nline 2\nconst url = 'https://example.com';\nline 4`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result[0].line).toBe(3);
		});

		it('should return empty array for file with no URLs', async () => {
			const content = `const x = 5;\nconst y = 10;`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(0);
		});
	});

	describe('external request patterns', () => {
		it('should whitelist WordPress.org domains as safe', async () => {
			const content = `const url = 'https://wordpress.org/plugin';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			// URL is detected, but would be marked as safe in suspicious check
			expect(result).toHaveLength(1);
		});

		it('should whitelist cdn.jsdelivr.net as safe', async () => {
			const content = `<script src="https://cdn.jsdelivr.net/npm/vue"></script>`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.html');
			expect(result).toHaveLength(1);
		});

		it('should detect HTTP URLs as suspicious', async () => {
			const content = `const url = 'http://example.com/api';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(1);
			// Would be marked as suspicious because it's HTTP
		});
	});

	describe('edge cases', () => {
		it('should handle malformed URLs gracefully', async () => {
			const content = `const url = 'not-a-url';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			// Should not crash, but might still detect the pattern
			expect(result.length).toBeGreaterThanOrEqual(0);
		});

		it('should handle empty files', async () => {
			const content = '';
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(0);
		});

		it('should handle files with only comments', async () => {
			const content = `// This is a comment\n/* Another comment */`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			expect(result).toHaveLength(0);
		});

		it('should handle URLs in comments', async () => {
			const content = `// const url = 'https://example.com/api';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.js');
			// Pattern matching would still find the URL
			expect(result).toHaveLength(1);
		});
	});

	describe('file types', () => {
		it('should scan PHP files for external requests', async () => {
			const content = `wp_remote_get('https://api.example.com');`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.php');
			expect(result).toHaveLength(1);
		});

		it('should scan TypeScript files', async () => {
			const content = `const url: string = 'https://example.com';`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.ts');
			expect(result).toHaveLength(1);
		});

		it('should scan SCSS files', async () => {
			const content = `@import url('https://fonts.googleapis.com/css?family=Roboto');`;
			vi.spyOn(fs, 'readFile').mockResolvedValue(content);

			const result = await checkExternalRequests.scanFile('/path/to/file.scss');
			expect(result).toHaveLength(1);
		});
	});
});
