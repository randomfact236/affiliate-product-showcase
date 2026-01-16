import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { promises as fs } from 'fs';
import path from 'path';
import { brotliCompressSync, gzipSync } from 'zlib';
import { fileURLToPath } from 'url';

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

// Import the module after mocking
const { walk, shouldSkip, compressFile, main } = await import('../../tools/compress.ts');

describe('compress.ts', () => {
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
		it('should skip compression-report.json', () => {
			expect(shouldSkip('/path/to/compression-report.json')).toBe(true);
		});

		it('should skip .gz files', () => {
			expect(shouldSkip('/path/to/file.js.gz')).toBe(true);
		});

		it('should skip .br files', () => {
			expect(shouldSkip('/path/to/file.js.br')).toBe(true);
		});

		it('should skip .map files', () => {
			expect(shouldSkip('/path/to/file.js.map')).toBe(true);
		});

		it('should not skip regular files', () => {
			expect(shouldSkip('/path/to/file.js')).toBe(false);
			expect(shouldSkip('/path/to/file.css')).toBe(false);
			expect(shouldSkip('/path/to/file.html')).toBe(false);
		});

		it('should be case-insensitive for extensions', () => {
			expect(shouldSkip('/path/to/file.JS.GZ')).toBe(true);
			expect(shouldSkip('/path/to/file.js.GZ')).toBe(true);
		});

		it('should be case-insensitive for report file', () => {
			expect(shouldSkip('/path/to/COMPRESSION-REPORT.JSON')).toBe(true);
		});
	});

	describe('compressFile', () => {
		const testBuffer = Buffer.from('test content');
		const testStats = { size: testBuffer.length };

		beforeEach(() => {
			vi.mocked(fs.readFile).mockResolvedValue(testBuffer);
			vi.mocked(fs.stat).mockResolvedValue(testStats);
			vi.mocked(fs.writeFile).mockResolvedValue(undefined);
		});

		it('should compress a file and create .gz and .br versions', async () => {
			const result = await compressFile('/test/file.js');

			expect(fs.writeFile).toHaveBeenCalledWith('/test/file.js.gz', expect.any(Buffer));
			expect(fs.writeFile).toHaveBeenCalledWith('/test/file.js.br', expect.any(Buffer));
		});

		it('should return compression report with correct structure', async () => {
			const result = await compressFile('/test/file.js');

			expect(result).toHaveProperty('file');
			expect(result).toHaveProperty('original');
			expect(result).toHaveProperty('gzip');
			expect(result).toHaveProperty('brotli');
			expect(result.gzip).toHaveProperty('size');
			expect(result.gzip).toHaveProperty('ratio');
			expect(result.brotli).toHaveProperty('size');
			expect(result.brotli).toHaveProperty('ratio');
		});

		it('should calculate compression ratios correctly', async () => {
			const result = await compressFile('/test/file.js');

			expect(result.gzip.ratio).toBeGreaterThanOrEqual(0);
			expect(result.gzip.ratio).toBeLessThanOrEqual(1);
			expect(result.brotli.ratio).toBeGreaterThanOrEqual(0);
			expect(result.brotli.ratio).toBeLessThanOrEqual(1);
		});

		it('should handle files with size 0', async () => {
			vi.mocked(fs.stat).mockResolvedValue({ size: 0 });
			vi.mocked(fs.readFile).mockResolvedValue(Buffer.from(''));

			const result = await compressFile('/test/empty.js');

			expect(result.original).toBe(0);
			expect(result.gzip.size).toBeGreaterThanOrEqual(0);
			expect(result.brotli.size).toBeGreaterThanOrEqual(0);
		});

		it('should normalize file paths to use forward slashes', async () => {
			const result = await compressFile('C:\\test\\path\\file.js');

			expect(result.file).not.toContain('\\');
			expect(result.file).toContain('/');
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

		it('should run successfully and write compression report', async () => {
			await main();

			expect(fs.writeFile).toHaveBeenCalledWith(
				expect.stringContaining('compression-report.json'),
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

			expect(fs.writeFile).toHaveBeenCalledTimes(5); // 2 files * 2 compressions + 1 report
		});

		it('should skip files that match shouldSkip criteria', async () => {
			vi.mocked(fs.readdir).mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file1.js.gz', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);

			await main();

			// Should only process file1.js and file2.js, skip file1.js.gz
			expect(fs.writeFile).toHaveBeenCalledTimes(5);
		});
	});

	describe('integration tests', () => {
		it('should compress multiple files with different extensions', async () => {
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

			expect(fs.writeFile).toHaveBeenCalledTimes(7); // 3 files * 2 + 1 report
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

			expect(fs.writeFile).toHaveBeenCalledTimes(5); // 2 files * 2 + 1 report
		});
	});
});
