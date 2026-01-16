import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { createHash } from 'crypto';
import { promises as fs } from 'fs';
import { brotliCompressSync, gzipSync } from 'zlib';

// Mock fs module
vi.mock('fs', async () => {
	const actual = await vi.importActual('fs');
	return {
		...actual,
		promises: {
			readdir: vi.fn(),
			readFile: vi.fn(),
			writeFile: vi.fn(),
			stat: vi.fn(),
			access: vi.fn(),
		},
	};
});

// Mock console.log and console.error
const consoleLogSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
const consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

// Import module after mocking
const { walk, shouldSkip, buildIntegrity, processFile, main } = await import('../../tools/generate-sri.ts');

describe('generate-sri.ts', () => {
	beforeEach(() => {
		vi.clearAllMocks();
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	describe('walk', () => {
		it('should return array of file paths for a flat directory', async () => {
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);

			const files = await walk('/test/dir');
			expect(files).toEqual(['/test/dir/file1.js', '/test/dir/file2.js']);
		});

		it('should recursively walk through subdirectories', async () => {
			vi.mocked(fs.readdir)
				.mockResolvedValueOnce([
					{ name: 'file1.js', isDirectory: () => false },
					{ name: 'subdir', isDirectory: () => true },
				])
				.mockResolvedValueOnce([
					{ name: 'file2.js', isDirectory: () => false },
				]);

			const files = await walk('/test/dir');
			expect(files).toEqual(['/test/dir/file1.js', '/test/dir/subdir/file2.js']);
		});

		it('should handle empty directory', async () => {
			vi.mocked(fs.readdir).mockResolvedValue([]);

			const files = await walk('/test/dir');
			expect(files).toEqual([]);
		});

		it('should handle nested directories deeply', async () => {
			vi.mocked(fs.readdir)
				.mockResolvedValueOnce([
					{ name: 'dir1', isDirectory: () => true },
				])
				.mockResolvedValueOnce([
					{ name: 'dir2', isDirectory: () => true },
				])
				.mockResolvedValueOnce([
					{ name: 'file.js', isDirectory: () => false },
				]);

			const files = await walk('/test/dir');
			expect(files).toEqual(['/test/dir/dir1/dir2/file.js']);
		});
	});

	describe('shouldSkip', () => {
		it('should skip sri-hashes.json', () => {
			expect(shouldSkip('/path/to/sri-hashes.json')).toBe(true);
		});

		it('should skip .map files', () => {
			expect(shouldSkip('/path/to/file.js.map')).toBe(true);
		});

		it('should skip .gz files', () => {
			expect(shouldSkip('/path/to/file.js.gz')).toBe(true);
		});

		it('should skip .br files', () => {
			expect(shouldSkip('/path/to/file.js.br')).toBe(true);
		});

		it('should skip JSON files except manifest.json', () => {
			expect(shouldSkip('/path/to/data.json')).toBe(true);
			expect(shouldSkip('/path/to/config.json')).toBe(true);
			expect(shouldSkip('/path/to/manifest.json')).toBe(false);
		});

		it('should not skip regular files', () => {
			expect(shouldSkip('/path/to/file.js')).toBe(false);
			expect(shouldSkip('/path/to/file.css')).toBe(false);
			expect(shouldSkip('/path/to/file.html')).toBe(false);
			expect(shouldSkip('/path/to/file.svg')).toBe(false);
		});

		it('should be case-insensitive for extensions', () => {
			expect(shouldSkip('/path/to/file.JS.MAP')).toBe(true);
			expect(shouldSkip('/path/to/file.js.GZ')).toBe(true);
			expect(shouldSkip('/path/to/file.js.BR')).toBe(true);
		});

		it('should be case-insensitive for SRI hashes file', () => {
			expect(shouldSkip('/path/to/SRI-HASHES.JSON')).toBe(true);
		});
	});

	describe('buildIntegrity', () => {
		it('should generate sha384 base64 hash', () => {
			const buffer = Buffer.from('test content');
			const integrity = buildIntegrity(buffer);

			expect(integrity).toMatch(/^sha384-[A-Za-z0-9+/=]+$/);
		});

		it('should generate consistent hashes for same input', () => {
			const buffer = Buffer.from('test content');
			const hash1 = buildIntegrity(buffer);
			const hash2 = buildIntegrity(buffer);

			expect(hash1).toBe(hash2);
		});

		it('should generate different hashes for different inputs', () => {
			const buffer1 = Buffer.from('content 1');
			const buffer2 = Buffer.from('content 2');
			const hash1 = buildIntegrity(buffer1);
			const hash2 = buildIntegrity(buffer2);

			expect(hash1).not.toBe(hash2);
		});

		it('should handle empty buffer', () => {
			const buffer = Buffer.from('');
			const integrity = buildIntegrity(buffer);

			expect(integrity).toMatch(/^sha384-/);
		});

		it('should handle large buffers', () => {
			const largeContent = 'x'.repeat(10000);
			const buffer = Buffer.from(largeContent);
			const integrity = buildIntegrity(buffer);

			expect(integrity).toMatch(/^sha384-[A-Za-z0-9+/=]+$/);
		});
	});

	describe('processFile', () => {
		const testBuffer = Buffer.from('test content');
		const testStats = { size: testBuffer.length };

		beforeEach(() => {
			vi.mocked(fs.readFile).mockResolvedValue(testBuffer);
			vi.mocked(fs.stat).mockResolvedValue(testStats);
		});

		it('should process file and return SRI entry', async () => {
			const result = await processFile('/test/file.js');

			expect(result).toHaveProperty('integrity');
			expect(result).toHaveProperty('size');
			expect(result).toHaveProperty('gzip');
			expect(result).toHaveProperty('brotli');
		});

		it('should generate correct integrity hash', async () => {
			const result = await processFile('/test/file.js');

			expect(result.integrity).toMatch(/^sha384-[A-Za-z0-9+/=]+$/);
		});

		it('should include original file size', async () => {
			const result = await processFile('/test/file.js');

			expect(result.size).toBe(testBuffer.length);
		});

		it('should include gzip compression data', async () => {
			const result = await processFile('/test/file.js');

			expect(result.gzip).toHaveProperty('size');
			expect(result.gzip).toHaveProperty('ratio');
			expect(result.gzip.size).toBeGreaterThan(0);
		});

		it('should include brotli compression data', async () => {
			const result = await processFile('/test/file.js');

			expect(result.brotli).toHaveProperty('size');
			expect(result.brotli).toHaveProperty('ratio');
			expect(result.brotli.size).toBeGreaterThan(0);
		});

		it('should calculate compression ratios correctly', async () => {
			const result = await processFile('/test/file.js');

			expect(result.gzip.ratio).toBeGreaterThanOrEqual(0);
			expect(result.gzip.ratio).toBeLessThanOrEqual(1);
			expect(result.brotli.ratio).toBeGreaterThanOrEqual(0);
			expect(result.brotli.ratio).toBeLessThanOrEqual(1);
		});

		it('should handle files with size 0', async () => {
			vi.mocked(fs.stat).mockResolvedValue({ size: 0 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from(''));

			const result = await processFile('/test/empty.js');

			expect(result.size).toBe(0);
			expect(result.gzip.size).toBeGreaterThanOrEqual(0);
			expect(result.brotli.size).toBeGreaterThanOrEqual(0);
		});

		it('should handle large files', async () => {
			const largeContent = 'x'.repeat(100000);
			const largeBuffer = Buffer.from(largeContent);
			vi.mocked(fs.readFile).mockResolvedValue(largeBuffer);
			vi.mocked(fs.stat).mockResolvedValue({ size: largeBuffer.length });

			const result = await processFile('/test/large.js');

			expect(result.size).toBe(largeBuffer.length);
			expect(result.integrity).toBeDefined();
		});
	});

	describe('main', () => {
		beforeEach(() => {
			vi.mocked(fs.access).mockResolvedValue(undefined);
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
			]);
			vi.mocked(fs.stat).mockResolvedValue({ size: 100 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from('test'));
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);
		});

		it('should run successfully and write SRI hashes', async () => {
			await main();

			expect(fs.writeFile).toHaveBeenCalledWith(
				expect.stringContaining('sri-hashes.json'),
				expect.any(String),
				'utf8'
			);
		});

		it('should handle errors gracefully', async () => {
			vi.mocked(fs.access).mockRejectedValue(new Error('Directory not found'));

			await main();

			expect(consoleErrorSpy).toHaveBeenCalled();
		});

		it('should set process.exitCode on error', async () => {
			vi.mocked(fs.access).mockRejectedValue(new Error('Error'));

			await main();

			expect(process.exitCode).toBe(1);
		});

		it('should process all files in dist directory', async () => {
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file2.css', isDirectory: () => false },
			]);

			await main();

			expect(fs.writeFile).toHaveBeenCalledWith(
				expect.stringContaining('sri-hashes.json'),
				expect.stringContaining('file1'),
				'utf8'
			);
		});

		it('should skip files that match shouldSkip criteria', async () => {
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file1.js.map', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);

			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			expect(output).not.toContain('file1.js.map');
		});

		it('should write JSON output with correct structure', async () => {
			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			const parsed = JSON.parse(output);

			expect(parsed).toBeInstanceOf(Object);
			expect(Object.keys(parsed)).toHaveLength(1);
		});
	});

	describe('integration tests', () => {
		it('should process multiple files with different extensions', async () => {
			vi.mocked(fs.access).mockResolvedValue(undefined);
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'app.js', isDirectory: () => false },
				{ name: 'style.css', isDirectory: () => false },
				{ name: 'index.html', isDirectory: () => false },
			]);
			vi.mocked(fs.stat).mockResolvedValue({ size: 1000 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from('content'));
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);

			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			const parsed = JSON.parse(output);

			expect(Object.keys(parsed)).toHaveLength(3);
			expect(parsed).toHaveProperty('app.js');
			expect(parsed).toHaveProperty('style.css');
			expect(parsed).toHaveProperty('index.html');
		});

		it('should handle subdirectories in dist folder', async () => {
			vi.mocked(fs.access).mockResolvedValue(undefined);
			vi.mocked(fs.readdir)
				.mockResolvedValueOnce([
					{ name: 'app.js', isDirectory: () => false },
					{ name: 'css', isDirectory: () => true },
				])
				.mockResolvedValueOnce([
					{ name: 'style.css', isDirectory: () => false },
				]);
			vi.mocked(fs.stat).mockResolvedValue({ size: 500 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from('content'));
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);

			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			const parsed = JSON.parse(output);

			expect(Object.keys(parsed)).toHaveLength(2);
			expect(parsed).toHaveProperty('app.js');
			expect(parsed).toHaveProperty('css/style.css');
		});

		it('should skip manifest.json but process other JSON files', async () => {
			vi.mocked(fs.access).mockResolvedValue(undefined);
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'manifest.json', isDirectory: () => false },
				{ name: 'data.json', isDirectory: () => false },
			]);
			vi.mocked(fs.stat).mockResolvedValue({ size: 200 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from('{}'));
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);

			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			const parsed = JSON.parse(output);

			expect(parsed).toHaveProperty('manifest.json');
			expect(parsed).not.toHaveProperty('data.json');
		});

		it('should generate unique hashes for different files', async () => {
			vi.mocked(fs.access).mockResolvedValue(undefined);
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);
			vi.mocked(fs.stat).mockResolvedValue({ size: 100 });
			vi.mocked(fs.readFile)
				.mockResolvedValueOnce(Buffer.from('content1'))
				.mockResolvedValueOnce(Buffer.from('content2'));
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);

			await main();

			const writeCall = vi.mocked(fs.writeFile).mock.calls[0];
			const output = writeCall[1] as string;
			const parsed = JSON.parse(output);

			expect(parsed['file1.js'].integrity).not.toBe(parsed['file2.js'].integrity);
		});
	});
});
