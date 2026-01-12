#!/usr/bin/env node
/**
 * SRI (Subresource Integrity) Hash Generator
 * 
 * Generates SHA-384 hashes for all built assets in the dist directory
 * and adds them to the Vite manifest.json file.
 *
 * Usage: node tools/generate-sri.js [dist-path]
 * 
 * @package AffiliateProductShowcase
 */

import { createHash } from 'crypto';
import { readFileSync, writeFileSync, existsSync } from 'fs';
import { resolve, dirname, join } from 'path';
import { fileURLToPath } from 'url';

// Get CLI arguments
const distPath = process.argv[2] || resolve(process.cwd(), 'assets/dist');
const manifestPath = join(distPath, 'manifest.json');
const outputPath = join(distPath, 'manifest-sri.json');

console.log(`\n🔐 SRI Hash Generator\n`);
console.log(`Dist Path: ${distPath}`);
console.log(`Manifest: ${manifestPath}\n`);

// Check if manifest exists
if (!existsSync(manifestPath)) {
  console.error(`❌ Error: manifest.json not found at ${manifestPath}`);
  console.error(`Run Vite build first to generate manifest.json`);
  process.exit(1);
}

// Read manifest
let manifest;
try {
  manifest = JSON.parse(readFileSync(manifestPath, 'utf-8'));
  console.log(`✅ Loaded manifest with ${Object.keys(manifest).length} files\n`);
} catch (error) {
  console.error(`❌ Error parsing manifest.json: ${error.message}`);
  process.exit(1);
}

// Generate SRI hashes
const sriManifest = {};
const errors = [];

for (const [file, path] of Object.entries(manifest)) {
  const fullPath = resolve(distPath, path);
  
  try {
    if (!existsSync(fullPath)) {
      throw new Error(`File not found`);
    }
    
    const content = readFileSync(fullPath);
    const hash = createHash('sha384').update(content).digest('base64');
    const sri = `sha384-${hash}`;
    
    sriManifest[file] = {
      path: path,
      integrity: sri,
      size: content.length
    };
    
    console.log(`  ✓ ${file}`);
  } catch (error) {
    errors.push({ file, error: error.message });
    console.log(`  ✗ ${file}: ${error.message}`);
  }
}

// Write SRI manifest
try {
  writeFileSync(outputPath, JSON.stringify(sriManifest, null, 2) + '\n');
  console.log(`\n✅ Generated SRI manifest: ${outputPath}`);
  console.log(`   Files processed: ${Object.keys(sriManifest).length}`);
  if (errors.length > 0) {
    console.log(`   Errors: ${errors.length}`);
  }
  
  // Merge SRI data into original manifest
  const mergedManifest = { ...manifest };
  for (const [file, data] of Object.entries(sriManifest)) {
    if (mergedManifest[file]) {
      mergedManifest[file] = {
        ...mergedManifest[file],
        integrity: data.integrity
      };
    }
  }
  
  writeFileSync(manifestPath, JSON.stringify(mergedManifest, null, 2) + '\n');
  console.log(`\n✅ Updated original manifest with integrity hashes\n`);
  
} catch (error) {
  console.error(`❌ Error writing manifest: ${error.message}`);
  process.exit(1);
}

// Exit with error code if there were errors
process.exit(errors.length > 0 ? 1 : 0);
