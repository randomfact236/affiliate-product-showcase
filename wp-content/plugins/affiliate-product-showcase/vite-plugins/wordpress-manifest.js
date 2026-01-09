import { promises as fs } from 'fs';
import path from 'path';
import crypto from 'crypto';

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

        // Compute SRI for each asset we can find
        for (const [key, entry] of Object.entries(manifest)) {
          const fileRel = entry.file || entry.src || entry['file'] || null;
          if (!fileRel) continue;
          const assetPath = path.resolve(outDir, fileRel);
          const ok = await fs.stat(assetPath).then(() => true).catch(() => false);
          if (!ok) continue;
          const buf = await fs.readFile(assetPath);
          const hash = crypto.createHash('sha384').update(buf).digest('base64');
          entry.integrity = `sha384-${hash}`;
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
