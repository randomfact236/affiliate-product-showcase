import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import path from 'path';
import type { Stats } from 'fs';

let consoleLogSpy: ReturnType<typeof vi.spyOn>;
let consoleErrorSpy: ReturnType<typeof vi.spyOn>;

// Mock fsContext
const mockFsContext = vi.hoisted(() => ({
	readdir: vi.fn(),
	readFile: vi.fn(),
	writeFile: vi.fn(),
	stat: vi.fn(),
	access: vi.fn(),
}));

vi.mock('../../tools/compress', async () => {
	const actual = await vi.importActual('../../tools/compress');
	return {
		...actual,
		fsContext: mockFsContext,
	};
});

// Import module after mocking
import { walk, shouldSkip, compressFile, main } from '../../tools/compress';

describe('compress.ts', () => {
	beforeEach(() => {
		vi.restoreAllMocks();
		consoleLogSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
		consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
		vi.clearAllMocks();
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});

	describe('walk', () => {
		it('should return array of file paths for a flat directory', async () => {
			mockFsContext.readdir.mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);

			const files = await walk('/test/dir');
			expect(files).toEqual(['/test/dir/file1.js', '/test/dir/file2.js']);
		});

		it('should recursively walk through subdirectories', async () => {
			mockFsContext.readdir
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
			mockFsContext.readdir.mockResolvedValue([]);

			const files = await walk('/test/dir');
			expect(files).toEqual([]);
		});

		it('should handle nested directories deeply', async () => {
			mockFsContext.readdir
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
		const testStats: Stats = { size: testBuffer.length } as Stats;

		beforeEach(() => {
			mockFsContext.readFile.mockResolvedValue(testBuffer);
			mockFsContext.stat.mockResolvedValue(testStats);
			mockFsContext.writeFile.mockResolvedValue(undefined);
		});

		it('should compress a file and create .gz and .br versions', async () => {
			const result = await compressFile('/test/file.js');

			expect(mockFsContext.writeFile).toHaveBeenCalledWith('/test/file.js.gz', expect.any(Buffer));
			expect(mockFsContext.writeFile).toHaveBeenCalledWith('/test/file.js.br', expect.any(Buffer));
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
			mockFsContext.stat.mockResolvedValue({ size: 0 } as Stats);
			mockFsContext.readFile.mockResolvedValue(Buffer.from(''));

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
			mockFsContext.access.mockResolvedValue(undefined);
			mockFsContext.readdir.mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
			]);
			mockFsContext.stat.mockResolvedValue({ size: 100 });
			mockFsContext.readFile.mockResolvedValue(Buffer.from('test'));
			mockFsContext.writeFile.mockResolvedValue(undefined);
		});

		it('should run successfully and write compression report', async () => {
			await main();

			expect(mockFsContext.writeFile).toHaveBeenCalledWith(
				expect.stringContaining('compression-report.json'),
				expect.any(String),
				'utf8'
			);
		});

		it('should handle errors gracefully', async () => {
			mockFsContext.access.mockRejectedValue(new Error('Directory not found'));

			await main();

			expect(consoleErrorSpy).toHaveBeenCalled();
		});

		it('should set process.exitCode on error', async () => {
			mockFsContext.access.mockRejectedValue(new Error('Error'));

			await main();

			expect(process.exitCode).toBe(1);
		});

		it('should process all files in dist directory', async () => {
			mockFsContext.readdir.mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file2.css', isDirectory: () => false },
			]);

			await main();

			expect(mockFsContext.writeFile).toHaveBeenCalledTimes(5); // 2 files * 2 compressions + 1 report
		});

		it('should skip files that match shouldSkip criteria', async () => {
			mockFsContext.readdir.mockResolvedValue([
				{ name: 'file1.js', isDirectory: () => false },
				{ name: 'file1.js.gz', isDirectory: () => false },
				{ name: 'file2.js', isDirectory: () => false },
			]);

			await main();

			// Should only process file1.js and file2.js, skip file1.js.gz
			expect(mockFsContext.writeFile).toHaveBeenCalledTimes(5);
		});
	});

	describe('integration tests', () => {
		it('should compress multiple files with different extensions', async () => {
			mockFsContext.access.mockResolvedValue(undefined);
			mockFsContext.readdir.mockResolvedValue([
				{ name: 'app.js', isDirectory: () => false },
				{ name: 'style.css', isDirectory: () => false },
				{ name: 'index.html', isDirectory: () => false },
			]);
			mockFsContext.stat.mockResolvedValue({ size: 1000 });
			mockFsContext.readFile.mockResolvedValue(Buffer.from('content'));
			mockFsContext.writeFile.mockResolvedValue(undefined);

			await main();

			expect(mockFsContext.writeFile).toHaveBeenCalledTimes(7); // 3 files * 2 + 1 report
		});

		it('should handle subdirectories in dist folder', async () => {
			mockFsContext.access.mockResolvedValue(undefined);
			mockFsContext.readdir
				.mockResolvedValueOnce([
					{ name: 'app.js', isDirectory: () => false },
					{ name: 'css', isDirectory: () => true },
				])
				.mockResolvedValueOnce([
					{ name: 'style.css', isDirectory: () => false },
				]);
			mockFsContext.stat.mockResolvedValue({ size: 500 });
			mockFsContext.readFile.mockResolvedValue(Buffer.from('content'));
			mockFsContext.writeFile.mockResolvedValue(undefined);

			await main();

			expect(mockFsContext.writeFile).toHaveBeenCalledTimes(5); // 2 files * 2 + 1 report
		});
	});
});
