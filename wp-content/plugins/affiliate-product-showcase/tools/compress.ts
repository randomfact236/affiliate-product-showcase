import { promises as fs } from 'fs';
import path from 'path';
import { brotliCompressSync, gzipSync, constants as zlibConstants } from 'zlib';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');
const distDir = path.resolve(rootDir, 'assets/dist');
const reportFile = path.join(distDir, 'compression-report.json');

interface CompressionOptions {
	params: {
		[key: number]: number;
	};
}

interface CompressionReportEntry {
	file: string;
	original: number;
	gzip: {
		size: number;
		ratio: number;
	};
	brotli: {
		size: number;
		ratio: number;
	};
}

interface BrotiliOptions {
	params: {
		[zlibConstants.BROTLI_PARAM_QUALITY]: number;
	};
}

const brotliOptions: BrotiliOptions = {
	params: {
		[zlibConstants.BROTLI_PARAM_QUALITY]: 11,
	},
};

const gzipOptions = { level: 9 };

export async function walk(dir: string): Promise<string[]> {
	const entries = await fs.readdir(dir, { withFileTypes: true });
	const files = await Promise.all(
		entries.map(async (entry) => {
			const fullPath = path.join(dir, entry.name);
			if (entry.isDirectory()) {
				return walk(fullPath);
			}
			return [fullPath];
		})
	);

	return files.flat();
}

export function shouldSkip(filePath: string): boolean {
	const ext = path.extname(filePath).toLowerCase();
	const basename = path.basename(filePath).toLowerCase();

	if (basename === 'compression-report.json') {
		return true;
	}

	return ext === '.gz' || ext === '.br' || ext === '.map';
}

export async function compressFile(filePath: string): Promise<CompressionReportEntry> {
	const buffer = await fs.readFile(filePath);
	const stats = await fs.stat(filePath);

	const gzipBuffer = gzipSync(buffer, gzipOptions);
	const brotliBuffer = brotliCompressSync(buffer, brotliOptions);

	await fs.writeFile(`${filePath}.gz`, gzipBuffer);
	await fs.writeFile(`${filePath}.br`, brotliBuffer);

	const gzipRatio = Number((gzipBuffer.byteLength / Math.max(stats.size, 1)).toFixed(4));
	const brotliRatio = Number((brotliBuffer.byteLength / Math.max(stats.size, 1)).toFixed(4));

	console.log(`${path.basename(filePath)}: ${(gzipRatio * 100).toFixed(2)}% (gzip), ${(brotliRatio * 100).toFixed(2)}% (brotli)`);

	return {
		file: path.relative(distDir, filePath).replace(/\\/g, '/'),
		original: stats.size,
		gzip: {
			size: gzipBuffer.byteLength,
			ratio: gzipRatio,
		},
		brotli: {
			size: brotliBuffer.byteLength,
			ratio: brotliRatio,
		},
	};
}

export async function main(): Promise<void> {
	try {
		await fs.access(distDir);
		const files = (await walk(distDir)).filter((file) => !shouldSkip(file));
		const report: CompressionReportEntry[] = [];

		for (const file of files) {
			report.push(await compressFile(file));
		}

		await fs.writeFile(reportFile, JSON.stringify(report, null, 2), 'utf8');
		console.log(`Compression report written to ${reportFile}`);
	} catch (error) {
		console.error('Compression failed:', error instanceof Error ? error.message : error);
		process.exitCode = 1;
	}
}

main();
