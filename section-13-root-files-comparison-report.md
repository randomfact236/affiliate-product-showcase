# Section 13: Root Files Comparison Report

**User Request:** "now scan section 13 and also compare with related root files, to confirm whether root file have related code or not?"

**Date:** 2026-01-16  
**Section:** 13 (vite-plugins/)  
**Task:** Compare vite-plugins/ with root files to identify related code

---

## Executive Summary

**Overall Status:** ✅ **CLEAN INTEGRATION WITH ONE ISSUE FOUND**

Section 13 (vite-plugins/) is properly integrated with root files. The `wordpress-manifest` plugin is only referenced in `vite.config.js` with no duplicate code. However, one unused function was discovered in `vite.config.js`.

**Integration Quality:** 9/10 (Excellent - one minor cleanup needed)  
**Production Ready:** ✅ YES  
**Issues Found:** 1 (unused function)

---

## Search Results

### Search Pattern: `(wordpress-manifest|vite-plugins|generateSRI|sriAlgorithm)`

**Matches Found:** 16 results across 2 files

#### File 1: vite.config.js (8 matches)
```
Line 28: import wordpressManifest from './vite-plugins/wordpress-manifest.js';
Line 102: const generateSRIHash = (filePath, algorithm = 'sha384') => {
Line 193: outputFile: resolve(paths.plugin, 'includes/asset-manifest.php'),
Line 194: generateSRI: true,
Line 195: sriAlgorithm: 'sha384'
```

#### File 2: vite-plugins/wordpress-manifest.js (8 matches)
```
Line 62: const generateSRI = opts.generateSRI !== false; // Default: true
Line 63: const sriAlgorithm = opts.sriAlgorithm || 'sha384'; // Default: sha384
Line 65: name: 'wordpress-manifest',
Line 66: apply: 'build',
Line 80: this.warn(`wordpress-manifest: manifest.json not found at ${manifestPath}`);
Line 94: this.warn(`wordpress-manifest: skipped ${fileRel} - ${err.message}`);
Line 99: this.warn(`wordpress-manifest: asset not found, skipping ${assetPath}`);
Line 106: const { hash } = await computeFileHash(assetPath, sriAlgorithm);
Line 107: entry.integrity = `${sriAlgorithm}-${hash}`;
Line 110: this.warn(`wordpress-manifest: failed to hash ${assetPath} - ${err.message}`);
Line 120: this.warn(`wordpress-manifest: wrote PHP manifest to ${outputFile}`);
Line 122: this.error(`wordpress-manifest: failed to generate manifest - ${err.message}`);
```

---

## Detailed Analysis

### 1. vite.config.js Integration ✅

**Plugin Import (Correct):**
```javascript
import wordpressManifest from './vite-plugins/wordpress-manifest.js';
```
- ✅ Correct import path
- ✅ Uses ES module syntax
- ✅ No issues found

**Plugin Configuration (Correct):**
```javascript
if (isProd) {
  plugins.push(
    wordpressManifest({ 
      outputFile: resolve(paths.plugin, 'includes/asset-manifest.php'),
      generateSRI: true,
      sriAlgorithm: 'sha384'
    })
  );
}
```
- ✅ Plugin added to production builds only
- ✅ Output file path correctly configured
- ✅ `generateSRI` option set to `true`
- ✅ `sriAlgorithm` option set to `'sha384'`
- ✅ Options are now used by the plugin (fixed)

---

### 2. Issue Found: Unused Function in vite.config.js 🟡

**Problem:** `generateSRIHash` function is defined but never used.

**Code Location:** `vite.config.js`, Lines 102-110

```javascript
// Generate SRI hash for a file
const generateSRIHash = (filePath, algorithm = 'sha384') => {
  try {
    const content = readFileSync(filePath);
    const hash = createHash(algorithm).update(content).digest('base64');
    return `${algorithm}-${hash}`;
  } catch (error) {
    console.warn(`Failed to generate SRI hash for ${filePath}:`, error.message);
    return null;
  }
};
```

**Analysis:**
- ❌ Function is defined but never called
- ❌ SRI generation is handled by `wordpressManifest` plugin
- ❌ This is duplicate/dead code
- ❌ Uses different implementation than the plugin (synchronous vs async)

**Why It Exists:**
This function was likely created before the `wordpressManifest` plugin was fully implemented, or as a utility for other purposes that never materialized.

**Impact:**
- Code clutter (dead code)
- Potential confusion (two SRI generation methods)
- Minor memory impact (function loaded but unused)

**Severity:** MINOR (Code Quality)

---

## Root Files Verification

### Files Checked:

1. ✅ **vite.config.js** - Related code found (plugin usage + unused function)
2. ❌ **package.json** - No related code (correct - plugin loaded by vite.config.js)
3. ❌ **package-lock.json** - No related code (correct)
4. ❌ **composer.json** - No related code (correct - PHP dependencies only)
5. ❌ **composer.lock** - No related code (correct)
6. ❌ **tsconfig.json** - No related code (correct - plugin is JavaScript, not TypeScript)
7. ❌ **tailwind.config.js** - No related code (correct - CSS framework config)
8. ❌ **postcss.config.js** - No related code (correct - PostCSS config)
9. ❌ **phpcs.xml.dist** - No related code (correct - PHP linting)
10. ❌ **phpunit.xml.dist** - No related code (correct - PHP testing)
11. ❌ **infection.json.dist** - No related code (correct - PHP mutation testing)
12. ❌ **commitlint.config.cjs** - No related code (correct - Git commit linting)
13. ❌ **.lintstagedrc.json** - No related code (correct - Pre-commit linting)
14. ❌ **.a11y.json** - No related code (correct - Accessibility testing)
15. ❌ **.env.example** - No related code (correct - Environment variables)
16. ❌ **run_phpunit.php** - No related code (correct - PHPUnit runner)

---

## Code Duplication Check

### SRI Generation Methods:

#### Method 1: vite.config.js (Unused - Dead Code)
```javascript
const generateSRIHash = (filePath, algorithm = 'sha384') => {
  try {
    const content = readFileSync(filePath);
    const hash = createHash(algorithm).update(content).digest('base64');
    return `${algorithm}-${hash}`;
  } catch (error) {
    console.warn(`Failed to generate SRI hash for ${filePath}:`, error.message);
    return null;
  }
};
```
**Status:** ❌ UNUSED - Dead code

**Differences from Plugin:**
- Synchronous (`readFileSync`)
- No file size validation
- No path validation
- No extension whitelist
- Simpler error handling

---

#### Method 2: vite-plugins/wordpress-manifest.js (Active - Used)
```javascript
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
```
**Status:** ✅ ACTIVE - Used by plugin

**Advantages over vite.config.js version:**
- Asynchronous (non-blocking)
- File size validation (50MB limit)
- Path validation (security)
- Extension whitelist (security)
- Streaming API (memory efficient)
- Returns file stats
- Better error codes

---

## Comparison Summary

### Integration Status

| Aspect | Status | Notes |
|---------|--------|-------|
| Plugin Import | ✅ Perfect | Correct import in vite.config.js |
| Plugin Configuration | ✅ Perfect | Options passed correctly |
| Plugin Usage | ✅ Perfect | Production builds only |
| Code Duplication | 🟡 Minor | Unused function in vite.config.js |
| Related Code in Other Roots | ✅ None | No unrelated code found |

---

### Issues Found

#### Issue 1: Unused `generateSRIHash` Function 🟡

**File:** `vite.config.js`, Lines 102-110

**Severity:** MINOR (Code Quality)

**Description:** Function defined but never used. SRI generation is handled by `wordpressManifest` plugin.

**Recommendation:** Remove the unused function.

---

## Recommendations

### High Priority
1. **Remove unused `generateSRIHash` function** from `vite.config.js` (lines 102-110)

### Medium Priority
2. Consider adding JSDoc comments to the plugin for better documentation
3. Add unit tests for the `wordpressManifest` plugin

### Low Priority
4. None - integration is clean and well-structured

---

## Proposed Fix

### Remove Unused Function from vite.config.js

**Location:** `vite.config.js`, Lines 102-110

**Before:**
```javascript
// Generate SRI hash for a file
const generateSRIHash = (filePath, algorithm = 'sha384') => {
  try {
    const content = readFileSync(filePath);
    const hash = createHash(algorithm).update(content).digest('base64');
    return `${algorithm}-${hash}`;
  } catch (error) {
    console.warn(`Failed to generate SRI hash for ${filePath}:`, error.message);
    return null;
  }
};
```

**After:**
```javascript
// Function removed - SRI generation handled by wordpressManifest plugin
```

**Impact:**
- ✅ Removes dead code
- ✅ Eliminates confusion
- ✅ Reduces codebase size
- ✅ No functional impact (function was never used)

---

## Verification

### Current State:
- ✅ Plugin properly imported in vite.config.js
- ✅ Plugin configured with correct options
- ✅ Plugin used in production builds only
- 🟡 Unused function exists in vite.config.js

### After Fix:
- ✅ Plugin properly imported in vite.config.js
- ✅ Plugin configured with correct options
- ✅ Plugin used in production builds only
- ✅ No dead code

---

## Conclusion

**Section 13 (vite-plugins/) has clean integration with root files.**

**Positive Findings:**
- ✅ `wordpress-manifest` plugin is only referenced in `vite.config.js`
- ✅ No code duplication in root files
- ✅ Plugin options work correctly (fixed in previous resolution)
- ✅ Proper separation of concerns

**Issue Found:**
- 🟡 One unused function (`generateSRIHash`) in `vite.config.js`

**Recommendation:** Remove the unused function to clean up dead code and eliminate potential confusion.

**Overall Assessment:** Excellent integration with minor cleanup opportunity.

---

## Standards Applied

**Files Used for This Analysis:**
- ✅ docs/assistant-instructions.md (Comparison reporting, user request documentation, brutal truth rule)
- ✅ docs/assistant-quality-standards.md (Code quality assessment, error classification)
