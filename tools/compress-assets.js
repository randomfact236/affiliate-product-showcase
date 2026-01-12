#!/usr/bin/env node
/**
 * Pre-compression Script
 * 
 * Generates gzip and brotli compressed versions of all build assets.
 * This allows the server to serve pre-compressed files without
 * real-time compression overhead.
 *
 * Usage: node tools/compress-assets.js [dist-path]
 * 
 * @package AffiliateProductShowcase
 */

import { createReadStream, createWriteStream, existsSync, statSync } from 'fs';
import { join, resolve } from 'path';
import { createGzip, createBrotliCompress } from 'zlib';
import { promisify } from 'util';

const stat = promisify(stat);
const readdir = promisify(require('fs').readdir);

// Get CLI arguments
const distPath = process.argv[2] || resolve(process.cwd(), 'assets/dist');
const formats = process.argv.slice(3).length > 0 
  ? process.argv.slice(3) 
  : ['gzip', 'br'];

console.log(`\n🗜️  Asset Compression Tool\n`);
console.log(`Dist Path: ${distPath}`);
console.log(`Formats: ${formats.join(', ')}\n`);

// Verify dist path exists
if (!existsSync(distPath)) {
  console.error(`❌ Error: dist directory not found at ${distPath}`);
  process.exit(1);
}

// Compress a file
const compressFile = async (filePath, format) => {
  const input = createReadStream(filePath);
  const outputPath = `${filePath}.${format}`;
  const output = createWriteStream(outputPath);
  
  return new Promise((resolve, reject) => {
    const compressor = format === 'gzip' 
      ? createGzip({ level: 9 }) 
      : createBrotliCompress({ 
          params: {
            [require('zlib').constants.BROTLI_PARAM_MODE]: 2,
            [require('zlib').constants.BROTLI_PARAM_QUALITY]: 11,
            [require('zlib').constants.BROTLI_PARAM_SIZE]: 22,
          }
        });

    input
      .pipe(compressor)
      .on('error', reject)
      .pipe(output)
      .on('error', reject)
      .on('finish', () => {
        const inputSize = statSync(filePath).size;
        try {
          const outputSize = statSync(outputPath).size;
          const savings = ((1 - outputSize / inputSize) * 100).toFixed(1);
          console.log(`   ✓ ${filePath.substring(distPath.length + 1)} ${format}: ${(outputSize / 1024).toFixed(1)}KB (${savings}% saved)`);
          resolve();
        } catch {
          console.log(`   ✓ ${filePath.substring(distPath.length + 1)} ${format}`);
          resolve();
        }
      });
  });
};

// Compress all files recursively
const compressDirectory = async (dir, format) => {
  const files = await readdir(dir);
  let compressedCount = 0;

  for (const file of files) {
    const fullPath = join(dir, file);
    const stat = await stat(fullPath);

    if (stat.isDirectory()) {
      compressedCount += await compressDirectory(fullPath, format);
    } else {
      // Only compress certain file types
      const ext = file.split('.').pop().toLowerCase();
      if (['js', 'css', 'json', 'svg', 'txt', 'html', 'xml'].includes(ext)) {
        await compressFile(fullPath, format);
        compressedCount++;
      }
    }
  }

  return compressedCount;
};

// Main execution
const runCompression = async () => {
  try {
    const startTime = Date.now();
    const results = {};

    console.log('Scanning files for compression...\n');

    for (const format of formats) {
      console.log(`\n📦 Generating ${format.toUpperCase()} files...\n`);
      const count = await compressDirectory(distPath, format);
      results[format] = count;
    }

    const duration = ((Date.now() - startTime) / 1000).toFixed(2);
    
    console.log(`\n\n✅ Compression complete!`);
    console.log(`   Duration: ${duration}s`);
    console.log(`   Results:`);
    for (const [format, count] of Object.entries(results)) {
      console.log(`      ${format.toUpperCase()}: ${count} files`);
    }
    console.log();
  } catch (error) {
    console.error('\n❌ Compression failed:', error.message);
    process.exit(1);
  }
};

runCompression();
