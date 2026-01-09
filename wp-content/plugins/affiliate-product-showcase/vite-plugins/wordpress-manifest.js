import { createReadStream, promises as fs } from 'fs';
import path from 'path';
import crypto from 'crypto';

// Security constants
const SECURITY = {
  MAX_FILE_SIZE: 50 * 1024 * 1024, // 50MB
  ALLOWED_EXTENSIONS: new Set([
    '.js', '.mjs', '.cjs', '.css', '.json',
    '.png', '.jpg', '.jpeg', '.svg', '.webp', '.avif',
    '.woff', '.woff2', '.ttf', '.eot', '.otf',
  ]),
  DISALLOWED_PATHS: ['node_modules', '.git', 'vendor', 'tests', '__tests__'],
};

async function computeFileHash(filePath, algorithm = 'sha384') {
  const stats = await fs.stat(filePath);
  if (stats.size > SECURITY.MAX_FILE_SIZE) {
    const err = new Error(`File too large: ${filePath}`);
    err.code = 'FILE_TOO_LARGE';
    throw err;
  }

  const hash = crypto.createHash(algorithm);
  const stream = createReadStream(filePath);
  for await (const chunk of stream) {
    hash.update(chunk);
  }

  return {
    hash: hash.digest('base64'),
    stats: { size: stats.size, mtime: stats.mtimeMs },
  };
}

function validateFilePath(filePath, baseDir) {
  const absolute = path.resolve(baseDir, filePath);
  const rel = path.relative(baseDir, absolute);

  if (!rel || rel.startsWith('..') || path.isAbsolute(rel)) {
    const err = new Error(`Path traversal attempt: ${filePath}`);
    err.code = 'SECURITY_VIOLATION';
    throw err;
  }

  const normalized = rel.replace(/\\/g, '/');
  const parts = normalized.split('/').filter(Boolean);

  for (const segment of SECURITY.DISALLOWED_PATHS) {
    if (parts.includes(segment)) {
      const err = new Error(`File in disallowed directory: ${segment}`);
      err.code = 'DISALLOWED_PATH';
      throw err;
    }
  }

  const ext = path.extname(filePath).toLowerCase();
  if (ext && !SECURITY.ALLOWED_EXTENSIONS.has(ext)) {
    const err = new Error(`Disallowed file extension: ${ext}`);
    err.code = 'INVALID_EXTENSION';
    throw err;
  }

  return absolute;
}

function jsToPhp(value) {
  if (value === null) return 'null';
  if (typeof value === 'boolean') return value ? 'true' : 'false';
  if (typeof value === 'number') return String(value);
  if (typeof value === 'string') return `'${value.replace(/'/g, "\\'")}'`;
  if (Array.isArray(value)) {
    const items = value.map((v) => jsToPhp(v)).join(', ');
    return '[' + items + ']';
  }
  if (typeof value === 'object') {
    const entries = Object.entries(value)
      .map(([k, v]) => `${jsToPhp(k)} => ${jsToPhp(v)}`)
      .join(',\n');
    return '[\n' + entries + '\n]';
  }
  return 'null';
}

export default function wordpressManifestPlugin(opts = {}) {
  const outputFile = opts.outputFile || path.resolve(process.cwd(), 'includes/asset-manifest.php');

  return {
    name: 'wordpress-manifest',
    apply: 'build',

    async writeBundle(outputOptions) {
      try {
        const outDir = outputOptions && outputOptions.dir
          ? outputOptions.dir
          : path.resolve(process.cwd(), 'assets/dist');

        const manifestPath = path.resolve(outDir, 'manifest.json');
        const exists = await fs.stat(manifestPath).then(() => true).catch(() => false);
        if (!exists) {
          this.warn(`wordpress-manifest: manifest.json not found at ${manifestPath}`);
          return;
        }

        const raw = await fs.readFile(manifestPath, 'utf8');
        const manifest = JSON.parse(raw);

        // Compute SRI for each asset we can find (streaming hash, robust path checks)
        for (const [key, entry] of Object.entries(manifest)) {
          const fileRel = entry.file || entry.src || entry['file'] || null;
          if (!fileRel) continue;

          let assetPath;
          try {
            assetPath = validateFilePath(fileRel, outDir);
          } catch (err) {
            this.warn(`wordpress-manifest: skipped ${fileRel} - ${err.message}`);
            continue;
          }

          const ok = await fs.stat(assetPath).then(() => true).catch(() => false);
          if (!ok) {
            this.warn(`wordpress-manifest: asset not found, skipping ${assetPath}`);
            continue;
          }

          try {
            const { hash } = await computeFileHash(assetPath, 'sha384');
            entry.integrity = `sha384-${hash}`;
          } catch (err) {
            this.warn(`wordpress-manifest: failed to hash ${assetPath} - ${err.message}`);
            continue;
          }
        }

        // Update manifest.json on disk (preserve readable formatting)
        await fs.writeFile(manifestPath, JSON.stringify(manifest, null, 2), 'utf8');

        // Write PHP manifest helper
        const phpDir = path.dirname(outputFile);
        await fs.mkdir(phpDir, { recursive: true }).catch(() => {});

        const phpArray = jsToPhp(manifest);
        const phpContent = `<?php\n/** Auto-generated asset manifest - do not edit. */\nreturn ${phpArray};\n`;
        await fs.writeFile(outputFile, phpContent, 'utf8');

        this.warn(`wordpress-manifest: wrote PHP manifest to ${outputFile}`);
      } catch (err) {
        this.error(`wordpress-manifest: failed to generate manifest - ${err.message}`);
      }
    },
  };
}
