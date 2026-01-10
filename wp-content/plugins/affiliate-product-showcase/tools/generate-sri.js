import { createHash } from 'crypto';
import { promises as fs } from 'fs';
import path from 'path';
import { brotliCompressSync, gzipSync, constants as zlibConstants } from 'zlib';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');
const distDir = path.resolve(rootDir, 'assets/dist');
const outputFile = path.join(distDir, 'sri-hashes.json');

const brotliOptions = {
	params: {
		[zlibConstants.BROTLI_PARAM_QUALITY]: 11,
	},
};

const gzipOptions = { level: 9 };

async function walk(dir) {
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

function shouldSkip(filePath) {
	const basename = path.basename(filePath).toLowerCase();
	const ext = path.extname(filePath).toLowerCase();

	if (basename === 'sri-hashes.json') {
		return true;
	}

	if (ext === '.map' || ext === '.gz' || ext === '.br') {
		return true;
	}

	if (ext === '.json' && basename !== 'manifest.json') {
		return true;
	}

	return false;
}

function buildIntegrity(buffer) {
	return `sha384-${createHash('sha384').update(buffer).digest('base64')}`;
}

async function processFile(filePath) {
	const buffer = await fs.readFile(filePath);
	const stats = await fs.stat(filePath);
	const integrity = buildIntegrity(buffer);

	const gzipSize = gzipSync(buffer, gzipOptions).byteLength;
	const brotliSize = brotliCompressSync(buffer, brotliOptions).byteLength;

	const ratio = (size) => Number((size / Math.max(stats.size, 1)).toFixed(4));

	return {
		integrity,
		size: stats.size,
		gzip: { size: gzipSize, ratio: ratio(gzipSize) },
		brotli: { size: brotliSize, ratio: ratio(brotliSize) },
	};
}

async function main() {
	try {
		await fs.access(distDir);
		const files = (await walk(distDir)).filter((file) => !shouldSkip(file));
		const results = {};

		for (const file of files) {
			const relative = path.relative(distDir, file).replace(/\\/g, '/');
			const data = await processFile(file);
			results[relative] = data;
		}

		const output = JSON.stringify(results, null, 2);
		await fs.writeFile(outputFile, output, 'utf8');
		console.log(`SRI hashes written to ${outputFile}`);
	} catch (error) {
		console.error('SRI generation failed:', error.message || error);
		process.exitCode = 1;
	}
}

main();
