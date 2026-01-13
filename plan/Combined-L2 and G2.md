# Affiliate Product Showcase - Combined Audit Report (L2 + G2)
## Topics 1.1-1.12: Critical Consolidated Findings with Implementation Guide

**Date:** January 13, 2026  
**Auditor:** Enterprise WordPress Plugin Standards (2026)  
**Target Standards:** PHP ≥8.3 (min 8.1), WP ≥6.7, Vite 5+, Enterprise/VIP Quality  
**Combined From:** audit-L2.md + audit-G.md

---

## Executive Summary

| Metric | L2 | G | Combined |
|--------|----|-----|-----------|
| Total Findings | 11 | 12 | 23 |
| Critical (❌) | 4 | 2 | 6 |
| Warnings (⚠️) | 5 | 4 | 9 |
| Acceptable (✅) | 2 | 6 | 8 |
| Real Blockers | 3 | 2 | 5 |

**Overall Grade: B-**

**Ready for Feature Development:** CONDITIONAL - Must fix 5 blockers first

**Estimated Total Fix Time:** 4-6 hours (immediate blockers: 2-3 hours)

---

## Detailed Findings with Implementation Instructions

### 1. Docker Volume Mount Path Contains Placeholder "your-plugin"

**File:** `docker/docker-compose.yml`  
**Lines:** 55-56

```yaml
volumes:
  - ../:/var/www/html:cached
  - ./plugins/your-plugin:/var/www/html/wp-content/plugins/your-plugin
  - ./php-fpm/www.conf:/usr/local/etc/php-fpm.d/www.conf:ro
```

**Current State:** Volume mount uses placeholder `your-plugin` instead of actual plugin name `affiliate-product-showcase`.

**Verdict:** ❌ **CRITICAL ISSUE**  
**Priority:** IMMEDIATE  
**Blocker:** YES - Breaks Docker development environment

**2026 Best Practice:**
- Volume mounts must match actual directory structure
- Avoid placeholders in configuration files
- Use actual project names to prevent confusion

**Impact:** 
- Docker containers will fail to mount plugin correctly
- Developers must manually fix this before running
- Inconsistent with enterprise-grade automation standards

**Implementation Instructions:**

1. **Open file:** `docker/docker-compose.yml`
2. **Find line 55-56** containing the your-plugin volume mount
3. **Replace with:**
   ```yaml
   volumes:
     - ../:/var/www/html:cached
     - ../wp-content/plugins/affiliate-product-showcase:/var/www/html/wp-content/plugins/affiliate-product-showcase
     - ./php-fpm/www.conf:/usr/local/etc/php-fpm.d/www.conf:ro
   ```
4. **Save file**
5. **Test:** Run `docker-compose up` to verify volume mounts correctly

**Estimated Fix Time:** 5 minutes

---

### 2. Only .env.example Exists — No Real .env

**File:** Root directory  
**State:** `.env.example` exists, `.env` does NOT exist

**.env.example content (excerpt):**
```bash
# Affiliate Product Showcase - Environment Configuration
# Copy this file to .env and configure your settings
# DO NOT commit .env to version control

MYSQL_ROOT_PASSWORD=your_root_password_here
MYSQL_DATABASE=affiliate_showcase
MYSQL_USER=affiliate_user
MYSQL_PASSWORD=your_user_password_here
```

**Current State:** Only `.env.example` exists in repository. `.env` file is intentionally absent (gitignored).

**Verdict:** ✅ **CORRECT**  
**Priority:** N/A  
**Blocker:** NO - This is the correct security practice

**2026 Best Practice:**
- `.env.example` should be committed with template values
- `.env` must NEVER be committed to version control
- Developers copy `.env.example` → `.env` locally
- `.env` listed in `.gitignore`

**Impact:** None - This is the correct security practice

**Implementation Instructions:**

**For Developers (One-time setup):**

1. **Copy template:**
   ```bash
   cp .env.example .env
   ```

2. **Edit .env** with your local values:
   ```bash
   # Set strong passwords
   MYSQL_ROOT_PASSWORD=strong_secure_password_123
   MYSQL_PASSWORD=strong_user_password_456
   
   # Set WordPress credentials
   WORDPRESS_DB_USER=affiliate_user
   WORDPRESS_DB_PASSWORD=strong_user_password_456
   
   # Choose ports
   NGINX_HTTP_PORT=8000
   NGINX_HTTPS_PORT=8443
   ```

3. **Do NOT commit .env** (it's in .gitignore)

**Estimated Fix Time:** 5 minutes (one-time per developer)

---

### 3. PHP Requirement in Header & Composer.json = 7.4 / ^7.4

**Files:** 
- `wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php:8`
- `wp-content/plugins/affiliate-product-showcase/composer.json:28`
- `wp-content/plugins/affiliate-product-showcase/composer.json:176`

**affiliate-product-showcase.php:**
```php
/**
 * Plugin Name:       Affiliate Product Showcase
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Affiliate Product Showcase Team
 * License:           GPL-2.0-or-later
 */
```

**composer.json (require section):**
```json
"require": {
    "php": "^7.4|^8.0|^8.1|^8.2|^8.3",
    // ...
}
```

**composer.json (config section):**
```json
"config": {
    "platform": {
        "php": "8.1.0"
    }
}
```

**Current State:** PHP version requirement is 7.4 in plugin header, allows 7.4-8.3 in composer.json, and platform config locked to PHP 8.1.0.

**Verdict:** ❌ **CRITICAL ISSUE**  
**Priority:** IMMEDIATE  
**Blocker:** YES - Security and standards violation

**2026 Best Practice:**
- Target PHP 8.3+ for 2026 (WordPress VIP requirement)
- Plugin header: `Requires PHP: 8.1` (minimum supported)
- composer.json: `"php": "^8.1"` (strict constraint)
- Platform config: `"php": "8.3.0"` (actual target)

**Impact:**
- Plugin advertises outdated PHP 7.4 support
- Allows installation on PHP versions no longer supported
- Platform config mismatch with declared target
- Security risk (PHP 7.4 reached EOL November 2022)

**Implementation Instructions:**

**Step 1: Update plugin header**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php`
2. **Find line 8:**
   ```php
   * Requires PHP:      7.4
   ```
3. **Replace with:**
   ```php
   * Requires PHP:      8.1
   ```
4. **Save file**

**Step 2: Update composer.json PHP requirement**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/composer.json`
2. **Find line 28:**
   ```json
   "php": "^7.4|^8.0|^8.1|^8.2|^8.3",
   ```
3. **Replace with:**
   ```json
   "php": "^8.1",
   ```
4. **Save file**

**Step 3: Update composer.json platform config**

1. **In the same file, find line 176:**
   ```json
   "platform": {
       "php": "8.1.0"
   }
   ```
2. **Replace with:**
   ```json
   "platform": {
       "php": "8.3.0"
   }
   ```
3. **Save file**

**Step 4: Update PHP version check in plugin**

1. **In affiliate-product-showcase.php, find line 28:**
   ```php
   if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
   ```
2. **Replace with:**
   ```php
   if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
   ```
3. **Find line 46 (admin notice text):**
   ```php
   '7.4'
   ```
4. **Replace with:**
   ```php
   '8.1'
   ```
5. **Save file**

**Step 5: Test changes**

```bash
# Run composer update
composer update

# Verify PHP version
php -v  # Should show 8.3+

# Test plugin activation
# (In WordPress admin, try to activate plugin)
```

**Estimated Fix Time:** 15 minutes

---

### 4. WordPress Minimum Version = 6.0

**File:** `wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php:7`

```php
/**
 * Plugin Name:       Affiliate Product Showcase
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Affiliate Product Showcase Team
 */
```

**composer.json (extra section):**
```json
"extra": {
    "wordpress-plugin": {
        "minimum-php": "7.4",
        "minimum-wp": "6.0"
    }
}
```

**Current State:** WordPress minimum version is 6.0, but target is 6.7+.

**Verdict:** ❌ **CRITICAL ISSUE**  
**Priority:** IMMEDIATE  
**Blocker:** YES - Standards and feature compatibility

**2026 Best Practice:**
- Target WordPress 6.7+ for 2026 (current stable)
- Minimum supported: 6.4 (LTS) or 6.5+ for enterprise
- WordPress 6.0 was released May 2022 (too old for 2026)

**Impact:**
- Plugin advertises support for outdated WordPress version
- Misses modern WordPress features (6.1-6.7)
- Compatibility testing burden increases
- Enterprise/VIP standards require modern versions

**Implementation Instructions:**

**Step 1: Update plugin header**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php`
2. **Find line 7:**
   ```php
   * Requires at least: 6.0
   ```
3. **Replace with:**
   ```php
   * Requires at least: 6.7
   ```
4. **Save file**

**Step 2: Update composer.json**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/composer.json`
2. **Find the extra section around line 207:**
   ```json
   "extra": {
       "wordpress-plugin": {
           "minimum-php": "7.4",
           "minimum-wp": "6.0"
       }
   }
   ```
3. **Replace with:**
   ```json
   "extra": {
       "wordpress-plugin": {
           "minimum-php": "8.1",
           "minimum-wp": "6.7"
       }
   }
   ```
4. **Save file**

**Estimated Fix Time:** 5 minutes

---

### 5. package-lock.json is Gitignored

**File:** `.gitignore`

```
### Node & frontend
node_modules/
npm-debug.log*
yarn-debug.log*
yarn-error.log*
package-lock.json
```

**Current State:** `package-lock.json` is explicitly gitignored.

**Verdict:** ⚠️ **DEBATEABLE / ACCEPTABLE**  
**Priority:** LOW  
**Blocker:** NO

**2026 Best Practice:**
**Argument FOR committing:**
- Ensures deterministic builds across machines
- Locks exact dependency versions
- Improves CI/CD reliability
- npm 10+ strongly recommends committing it

**Argument AGAINST committing:**
- Smaller repository size
- Flexibility for platform-specific builds
- npm install can work without it (uses package.json)

**Impact:** Minor - npm install will resolve versions from package.json

**Recommendation:** Consider committing `package-lock.json` for enterprise consistency, but this is not a blocker.

**Implementation Instructions (Optional):**

**Option A: Commit package-lock.json (Recommended for enterprise)**

1. **Open file:** `.gitignore`
2. **Remove this line:**
   ```
   package-lock.json
   ```
3. **Save file**
4. **Generate and commit:**
   ```bash
   npm install
   git add package-lock.json
   git commit -m "Add package-lock.json for deterministic builds"
   ```

**Option B: Keep gitignored (Acceptable)**

No action needed. This is a valid choice.

**Estimated Fix Time:** 10 minutes (if committing)

---

### 6. assets/dist/ Fully Gitignored (Including Manifest & SRI)

**File:** `.gitignore`

```
### Build & dist
dist/
assets/dist/
assets/dist/*.map
*.min.js.map
wp-content/plugins/affiliate-product-showcase/assets/dist/
*.gz
*.br
wp-content/plugins/affiliate-product-showcase/assets/dist/sri-hashes.json
wp-content/plugins/affiliate-product-showcase/assets/dist/compression-report.json
```

**Current State:** All build outputs in `assets/dist/` are gitignored, including:
- Manifest files
- SRI hashes
- Compiled JS/CSS
- Source maps

**Verdict:** ⚠️ **POTENTIAL MARKETPLACE ISSUE**  
**Priority:** MEDIUM  
**Blocker:** YES* (for WordPress.org marketplace)

**2026 Best Practice:**
**For Development:** ✅ CORRECT to gitignore build outputs
**For Marketplace Distribution:** ⚠️ ISSUE - WordPress.org requires compiled assets

**Impact:**
- Development: Correct - rebuilds are fast
- WordPress.org Plugin Repository: BLOCKER - requires `assets/dist/` committed
- Enterprise/VIP: Usually fine - they build from source
- Distribution zip: Must include build artifacts

**Implementation Instructions:**

**Option A: Keep gitignored for development, create build script for distribution (Recommended)**

1. **Create file:** `scripts/build-distribution.sh`
   ```bash
   #!/bin/bash
   
   # Build assets
   cd wp-content/plugins/affiliate-product-showcase
   npm run build
   
   # Copy manifest and SRI to staging area
   mkdir -p ../dist-assets
   cp assets/dist/manifest.json ../dist-assets/ 2>/dev/null || true
   cp assets/dist/.vite/manifest.json ../dist-assets/ 2>/dev/null || true
   cp assets/dist/sri-hashes.json ../dist-assets/ 2>/dev/null || true
   
   # Build distribution package
   cd ../..
   zip -r affiliate-product-showcase.zip \
     wp-content/plugins/affiliate-product-showcase/ \
     -x "wp-content/plugins/affiliate-product-showcase/node_modules/*" \
     -x "wp-content/plugins/affiliate-product-showcase/assets/dist/*" \
     -x "wp-content/plugins/affiliate-product-showcase/.git/*"
   
   echo "Distribution package created: affiliate-product-showcase.zip"
   ```

2. **Make executable:**
   ```bash
   chmod +x scripts/build-distribution.sh
   ```

**Option B: Commit manifest.json but not other artifacts**

1. **Open file:** `.gitignore`
2. **Replace:**
   ```
   assets/dist/
   ```
3. **With:**
   ```
   assets/dist/*
   !assets/dist/manifest.json
   !assets/dist/.vite/manifest.json
   !assets/dist/sri-hashes.json
   ```
4. **Save file**

5. **Commit:**
   ```bash
   git add assets/dist/manifest.json
   git add assets/dist/sri-hashes.json
   git commit -m "Add build artifacts for marketplace distribution"
   ```

**Estimated Fix Time:** 30 minutes

---

### 7. block.json Files are Very Minimal (Missing Attributes, Supports, Icon, Scripts…)

**Files:**
- `wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/block.json`
- `wp-content/plugins/affiliate-product-showcase/blocks/product-grid/block.json`

**product-showcase/block.json:**
```json
{ "apiVersion": 2, "name": "aps/product-showcase", "title": "Product Showcase", "category": "widgets" }
```

**product-grid/block.json:**
```json
{
    "apiVersion": 2,
    "name": "aps/product-grid",
    "title": "Product Grid",
    "category": "widgets",
    "attributes": {
        "perPage": {
            "type": "number",
            "default": 6
        }
    },
    "supports": {
        "align": true
    }
}
```

**Current State:** 
- `product-showcase/block.json`: Minimal - only 4 fields (apiVersion, name, title, category)
- `product-grid/block.json`: Slightly better - includes attributes and supports

**Missing from both:**
- `icon`: No block icon
- `description`: No detailed description
- `keywords`: No search keywords
- `example`: No preview example
- `styles`: No block style variations
- `textdomain`: Not explicitly set
- `editorScript`: No editor-specific script
- `editorStyle`: No editor-specific styles
- `style`: No frontend styles
- `viewScript`: No frontend scripts
- `attributes`: (product-showcase missing entirely)

**Verdict:** ⚠️ **MINIMAL BUT FUNCTIONAL**  
**Priority:** LOW  
**Blocker:** NO

**2026 Best Practice:**
Enterprise-grade block.json should include icon, description, keywords, attributes, supports, styles, scripts, and example.

**Impact:**
- Blocks are functional but not discoverable
- Poor editor UX (no icons, descriptions)
- Limited customization options
- Not enterprise/VIP quality

**Implementation Instructions:**

**Step 1: Update product-showcase/block.json**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/block.json`
2. **Replace entire content with:**
   ```json
   {
       "apiVersion": 2,
       "name": "aps/product-showcase",
       "title": "Product Showcase",
       "description": "Display affiliate products in a beautiful showcase layout",
       "category": "widgets",
       "icon": "store",
       "keywords": ["product", "showcase", "affiliate", "display"],
       "version": "1.0.0",
       "textdomain": "affiliate-product-showcase",
       "attributes": {
           "layout": {
               "type": "string",
               "default": "grid"
           },
           "columns": {
               "type": "number",
               "default": 3
           }
       },
       "supports": {
           "align": true,
           "html": false,
           "spacing": {
               "margin": true,
               "padding": true
           },
           "typography": {
               "fontSize": true
           }
       },
       "styles": [
           {
               "name": "default",
               "label": "Default",
               "isDefault": true
           },
           {
               "name": "list",
               "label": "List View"
           }
       ],
       "example": {
           "attributes": {
               "layout": "grid",
               "columns": 3
           }
       },
       "editorScript": "aps-blocks-editor",
       "editorStyle": "aps-blocks-editor",
       "style": "aps-blocks",
       "viewScript": "aps-blocks-frontend"
   }
   ```
3. **Save file**

**Step 2: Update product-grid/block.json**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/blocks/product-grid/block.json`
2. **Replace entire content with:**
   ```json
   {
       "apiVersion": 2,
       "name": "aps/product-grid",
       "title": "Product Grid",
       "description": "Display affiliate products in a grid layout with customizable columns",
       "category": "widgets",
       "icon": "grid-view",
       "keywords": ["product", "grid", "affiliate", "showcase"],
       "version": "1.0.0",
       "textdomain": "affiliate-product-showcase",
       "attributes": {
           "perPage": {
               "type": "number",
               "default": 6
           },
           "columns": {
               "type": "number",
               "default": 3
           },
           "gap": {
               "type": "number",
               "default": 16
           }
       },
       "supports": {
           "align": true,
           "html": false,
           "spacing": {
               "margin": true,
               "padding": true,
               "blockGap": true
           }
       },
       "styles": [
           {
               "name": "default",
               "label": "Default",
               "isDefault": true
           },
           {
               "name": "compact",
               "label": "Compact",
               "columns": 4
           }
       ],
       "example": {
           "attributes": {
               "perPage": 6,
               "columns": 3
           }
       },
       "editorScript": "aps-blocks-editor",
       "editorStyle": "aps-blocks-editor",
       "style": "aps-blocks"
   }
   ```
3. **Save file**

**Estimated Fix Time:** 20 minutes

---

### 8. Vite Manifest is in .vite/ Subfolder Instead of dist Root

**Files:**
- `wp-content/plugins/affiliate-product-showcase/vite.config.js`
- `wp-content/plugins/affiliate-product-showcase/vite-plugins/wordpress-manifest.js`

**Location Check:**
- `assets/dist/manifest.json`: ❌ NOT_EXISTS
- `assets/dist/.vite/manifest.json`: ✅ EXISTS

**Current State:** Vite generates `manifest.json` in `assets/dist/.vite/` subfolder (default Vite behavior).

**vite.config.js (relevant lines):**
```javascript
export default defineConfig(({ mode }) => {
  return {
    // ...
    manifest: CONFIG.BUILD.MANIFEST,
    build: {
      outDir: paths.dist, // assets/dist
      manifest: CONFIG.BUILD.MANIFEST, // Creates .vite/manifest.json
      // ...
    }
  }
});
```

**Verdict:** ⚠️ **POTENTIAL ISSUE**  
**Priority:** MEDIUM  
**Blocker:** YES* (if PHP code expects root manifest)

**2026 Best Practice:**
Vite 5+ creates manifest at `<outDir>/.vite/manifest.json` by default. For WordPress, either:
- Configure Vite to output to root, OR
- Ensure PHP code reads from .vite/ subdirectory

**Impact:**
- PHP manifest generation may fail if looking in wrong location
- Build process may have errors
- Runtime asset loading may fail

**Implementation Instructions:**

**Option A: Update Vite to output manifest to root (Recommended)**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/vite.config.js`
2. **Find the build section around line 145-170**
3. **Add custom Rollup plugin to move manifest:**
   ```javascript
   // Add this after line 267 (plugins array)
   
   // Custom plugin to move manifest from .vite/ to root
   const moveManifestPlugin = () => ({
       name: 'move-manifest',
       writeBundle() {
           const fs = require('fs');
           const path = require('path');
           
           const viteManifest = path.resolve(paths.dist, '.vite', 'manifest.json');
           const targetManifest = path.resolve(paths.dist, 'manifest.json');
           
           if (fs.existsSync(viteManifest)) {
               fs.copyFileSync(viteManifest, targetManifest);
               // Optionally remove .vite directory
               fs.rmSync(path.dirname(viteManifest), { recursive: true });
           }
       }
   });
   ```

4. **Add to plugins array:**
   ```javascript
   plugins: [
       react(),
       // ... existing plugins
       moveManifestPlugin(), // Add this line
   ].filter(Boolean),
   ```

5. **Save file**

**Option B: Update PHP Assets class to read from .vite/**

1. **Find file:** `wp-content/plugins/affiliate-product-showcase/src/Assets/Assets.php`
2. **Find the manifest reading code**
3. **Update path from `assets/dist/manifest.json` to `assets/dist/.vite/manifest.json`**
4. **Save file**

**Option C: Keep as-is (If PHP code already handles it)**

Verify PHP code expects `.vite/` location. If it works, no action needed.

**Estimated Fix Time:** 30 minutes

---

### 9. Frontend Files are .js/.jsx Instead of .ts/.tsx Despite TS Config

**Files:**
- `wp-content/plugins/affiliate-product-showcase/tsconfig.json`: ✅ EXISTS
- `wp-content/plugins/affiliate-product-showcase/frontend/js/admin.js`
- `wp-content/plugins/affiliate-product-showcase/frontend/js/frontend.js`
- `wp-content/plugins/affiliate-product-showcase/frontend/js/blocks.js`
- `wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/edit.jsx`
- `wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/save.jsx`
- `wp-content/plugins/affiliate-product-showcase/blocks/product-grid/edit.jsx`
- `wp-content/plugins/affiliate-product-showcase/blocks/product-grid/save.jsx`

**Current State:**
- TypeScript config exists (`tsconfig.json`)
- All frontend files use `.js` or `.jsx` extensions
- No `.ts` or `.tsx` files found in frontend/

**Verdict:** ⚠️ **INCONSISTENT CONFIGURATION**  
**Priority:** LOW  
**Blocker:** NO

**2026 Best Practice:**
**If using TypeScript:**
- All new files should be `.ts`/`.tsx`
- Gradually migrate existing `.js`/`.jsx` to TypeScript
- Enable strict mode in tsconfig.json
- Use type definitions for WordPress (`@types/wordpress__*`)

**If NOT using TypeScript:**
- Remove `tsconfig.json` to avoid confusion
- Remove TypeScript dependencies from package.json
- Update Vite config to disable TypeScript

**Impact:**
- Type safety not actually being used
- Confusion for developers (TS config but JS files)
- Missed benefits of TypeScript (enterprise-grade)
- Inconsistent codebase

**Implementation Instructions:**

**Option A: Adopt TypeScript (Recommended for enterprise)**

1. **Rename files one by one:**
   ```bash
   cd wp-content/plugins/affiliate-product-showcase/frontend/js
   
   # Rename entry points
   mv admin.js admin.ts
   mv frontend.js frontend.ts
   mv blocks.js blocks.ts
   
   # Rename components
   mv components/ProductCard.jsx components/ProductCard.tsx
   mv components/ProductModal.jsx components/ProductModal.tsx
   mv components/LoadingSpinner.jsx components/ProductCard.tsx
   
   cd ../../blocks
   
   # Rename block files
   mv product-showcase/edit.jsx product-showcase/edit.tsx
   mv product-showcase/save.jsx product-showcase/save.tsx
   mv product-grid/edit.jsx product-grid/edit.tsx
   mv product-grid/save.jsx product-grid/save.tsx
   ```

2. **Add types to files:**
   ```typescript
   // Example: components/ProductCard.tsx
   interface Product {
       id: string;
       name: string;
       price: number;
       image: string;
   }
   
   interface ProductCardProps {
       product: Product;
   }
   
   export default function ProductCard({ product }: ProductCardProps) {
       // ... component code
   }
   ```

3. **Update imports in files:**
   ```typescript
   // Update from:
   import ProductCard from './components/ProductCard.jsx'
   // To:
   import ProductCard from './components/ProductCard'
   ```

4. **Run type check:**
   ```bash
   npm run typecheck
   ```

5. **Fix type errors as they appear**

**Option B: Remove TypeScript (Simpler, less enterprise)**

1. **Remove from package.json:**
   ```bash
   npm uninstall typescript @types/node @types/react @types/react-dom
   ```

2. **Delete tsconfig.json:**
   ```bash
   rm wp-content/plugins/affiliate-product-showcase/tsconfig.json
   ```

3. **Remove typecheck script from package.json**

**Estimated Fix Time:** 2-4 hours (for full TypeScript adoption)

---

### 10. CI Tests PHP 8.1 (Too Old) Instead of Focusing on 8.3+

**File:** `.github/workflows/ci.yml`

```yaml
strategy:
  matrix:
    include:
        - os: ubuntu-22.04
          php: '8.1'
        - os: ubuntu-22.04
          php: '8.2'
        - os: ubuntu-22.04
          php: '8.4'
```

**Current State:** CI matrix tests PHP 8.1, 8.2, and 8.4 - notably MISSING PHP 8.3.

**Verdict:** ❌ **CRITICAL ISSUE**  
**Priority:** IMMEDIATE  
**Blocker:** YES - Not testing target version

**2026 Best Practice:**
For target PHP 8.3+:
```yaml
strategy:
  matrix:
    include:
        - os: ubuntu-22.04
          php: '8.3'  # PRIMARY TARGET - MUST TEST
        - os: ubuntu-22.04
          php: '8.4'  # FUTURE VERSION
        - os: ubuntu-22.04
          php: '8.2'  # MINIMUM SUPPORTED
```

**Impact:**
- Not testing the actual target version (8.3)
- May have version-specific bugs in production
- 8.1 is too old for 2026 standards
- 8.4 is future but 8.3 is missing

**Implementation Instructions:**

1. **Open file:** `.github/workflows/ci.yml`
2. **Find the matrix section (around lines 12-19)**
3. **Replace with:**
   ```yaml
   strategy:
     matrix:
       include:
           - os: ubuntu-22.04
             php: '8.3'
           - os: ubuntu-22.04
             php: '8.4'
           - os: ubuntu-22.04
             php: '8.2'
   ```
4. **Save file**
5. **Commit and push to trigger CI**

**Estimated Fix Time:** 5 minutes

---

### 11. Unnecessary/Heavy Prod Deps: Monolog, illuminate/collections, symfony/polyfill-php80

**File:** `wp-content/plugins/affiliate-product-showcase/composer.json:28-38`

```json
"require": {
    "php": "^7.4|^8.0|^8.1|^8.2|^8.3",
    "ext-json": "*",
    "ext-mbstring": "*",
    "ext-openssl": "*",
    "psr/container": "^2.0",
    "psr/log": "^3.0",
    "psr/simple-cache": "^3.0",
    "psr/http-client": "^1.0",
    "psr/http-factory": "^1.0",
    "symfony/polyfill-php80": "^1.27",
    "monolog/monolog": "^3.3",
    "league/container": "^4.2",
    "illuminate/collections": "^9.0",
    "ramsey/uuid": "^4.7"
}
```

**Current State:** Three production dependencies identified as unnecessary:
1. `symfony/polyfill-php80` - PHP 8.0 polyfill for PHP 8.3+ target
2. `monolog/monolog` - Heavy logging library
3. `illuminate/collections` - Laravel collections library

**Verdict:** ❌ **CRITICAL ISSUE**  
**Priority:** IMMEDIATE  
**Blocker:** YES - Size, security, and performance impact

**2026 Best Practice:**

**1. symfony/polyfill-php80:**
- Targeting PHP 8.3+ means polyfill for 8.0 is unnecessary
- Adds ~200KB to vendor folder
- Remove entirely

**2. monolog/monolog:**
- WordPress has built-in error_log() function
- VIP/Enterprise uses their own logging (Sentry, New Relic, etc.)
- Adds ~500KB to vendor folder
- Use dev dependency only, or remove
- Replace with simple WP-compatible logger

**3. illuminate/collections:**
- Laravel's collections library (~400KB)
- WordPress doesn't use Laravel patterns
- PHP 8.3 has native array helpers
- Remove unless specifically needed

**Impact:**
- Increased plugin size (~1.1MB unnecessary code)
- Slower autoloading
- Security surface area (more packages to patch)
- Maintenance burden
- Not VIP/enterprise friendly (size limits)

**Implementation Instructions:**

**Step 1: Remove unnecessary dependencies**

1. **Open file:** `wp-content/plugins/affiliate-product-showcase/composer.json`
2. **Find and remove these lines from the require section:**
   ```json
   "symfony/polyfill-php80": "^1.27",
   "monolog/monolog": "^3.3",
   "illuminate/collections": "^9.0",
   ```
3. **Save file**

**Step 2: Update code to use WordPress logging instead of Monolog**

1. **Find all files using Monolog:**
   ```bash
   grep -r "use Monolog" wp-content/plugins/affiliate-product-showcase/src/
   ```

2. **For each file found, replace Monolog with WP error_log:**

   **Example transformation:**

   **Before (Monolog):**
   ```php
   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;
   
   $logger = new Logger('aps');
   $logger->pushHandler(new StreamHandler(__DIR__ . '/debug.log'));
   $logger->error('Error message', ['context' => 'value']);
   ```

   **After (WordPress error_log):**
   ```php
   error_log(sprintf(
       '[APS] Error message | Context: %s',
       json_encode(['context' => 'value'])
   ));
   ```

3. **Or create a simple wrapper in src/Helpers/Logger.php:**
   ```php
   <?php
   declare(strict_types=1);
   
   namespace AffiliateProductShowcase\Helpers;
   
   class Logger {
       private const PREFIX = '[APS]';
       
       public static function error(string $message, array $context = []): void {
           error_log(sprintf(
               '%s %s | Context: %s',
               self::PREFIX,
               $message,
               json_encode($context)
           ));
       }
       
       public static function info(string $message, array $context = []): void {
           error_log(sprintf(
               '%s %s | Context: %s',
               self::PREFIX,
               $message,
               json_encode($context)
           ));
       }
       
       public static function warning(string $message, array $context = []): void {
           error_log(sprintf(
               '%s %s | Context: %s',
               self::PREFIX,
               $message,
               json_encode($context)
           ));
       }
   }
   ```

4. **Update imports in code:**
   ```php
   // Change from:
   use Monolog\Logger;
   
   // To:
   use AffiliateProductShowcase\Helpers\Logger;
   
   // And change usage:
   $logger->error('message');
   // To:
   Logger::error('message');
   ```

**Step 3: Replace illuminate/collections usage**

1. **Find files using collections:**
   ```bash
   grep -r "use Illuminate" wp-content/plugins/affiliate-product-showcase/src/
   ```

2. **Replace with native PHP arrays or create helper functions**

   **Example transformation:**

   **Before (Illuminate):**
   ```php
   use Illuminate\Support\Collection;
   
   $collection = new Collection($items);
   $filtered = $collection->filter(fn($item) => $item->isActive());
   ```

   **After (Native PHP):**
   ```php
   $filtered = array_filter($items, fn($item) => $item->isActive());
   ```

**Step 4: Update composer and test**

1. **Run composer update:**
   ```bash
   composer update
   ```

2. **Run tests:**
   ```bash
   composer test
   ```

3. **Fix any errors that arise**

**Estimated Fix Time:** 2-3 hours

---

## Summary Dashboard

| Finding | Verdict | Blocker? | Priority | Est. Time |
|---------|---------|----------|----------|-----------|
| 1. Docker mount placeholder | ❌ Critical | YES | IMMEDIATE | 5 min |
| 2. .env not committed | ✅ Correct | NO | N/A | 5 min* |
| 3. PHP 7.4 requirement | ❌ Critical | YES | IMMEDIATE | 15 min |
| 4. WP 6.0 minimum | ❌ Critical | YES | IMMEDIATE | 5 min |
| 5. package-lock.json gitignored | ⚠️ Warning | NO | LOW | 10 min |
| 6. assets/dist/ gitignored | ⚠️ Warning | YES* | MEDIUM | 30 min |
| 7. Minimal block.json | ⚠️ Warning | NO | LOW | 20 min |
| 8. Vite manifest location | ⚠️ Warning | YES* | MEDIUM | 30 min |
| 9. .js/.jsx vs TS config | ⚠️ Warning | NO | LOW | 2-4 hrs |
| 10. CI missing PHP 8.3 | ❌ Critical | YES | IMMEDIATE | 5 min |
| 11. Unnecessary heavy deps | ❌ Critical | YES | IMMEDIATE | 2-3 hrs |

*Conditional blocker - depends on use case (marketplace vs. enterprise)

---

## Must-Fix List (Before Feature Development)

### Priority 1: Immediate Blockers (3-4 hours)

1. **✅ Update PHP requirement to 8.1+**
   - `affiliate-product-showcase.php`: `Requires PHP: 8.1`
   - `composer.json`: `"php": "^8.1"`
   - `composer.json`: `"platform": { "php": "8.3.0" }`
   - Update version check code
   - **Time:** 15 minutes

2. **✅ Update WordPress requirement to 6.7+**
   - `affiliate-product-showcase.php`: `Requires at least: 6.7`
   - `composer.json`: `"minimum-wp": "6.7"`
   - **Time:** 5 minutes

3. **✅ Add PHP 8.3 to CI matrix**
   - `.github/workflows/ci.yml`: Add PHP 8.3 as primary test
   - Remove or demote PHP 8.1 (too old)
   - **Time:** 5 minutes

4. **✅ Fix Docker volume mount path**
   - `docker/docker-compose.yml`: Replace `your-plugin` with `affiliate-product-showcase`
   - **Time:** 5 minutes

### Priority 2: Additional Must-Fix (3-4 hours)

5. **✅ Remove unnecessary production dependencies**
   - Remove `symfony/polyfill-php80`
   - Remove `monolog/monolog` (replace with WP error_log)
   - Remove `illuminate/collections` (use native PHP arrays)
   - **Time:** 2-3 hours

6. **✅ Verify Vite manifest location consistency**
   - Ensure PHP code expects `.vite/manifest.json` OR configure Vite to output to root
   - Add custom Rollup plugin to move manifest if needed
   - Test build process end-to-end
   - **Time:** 30 minutes

7. **✅ Resolve marketplace distribution issue**
   - Create distribution build script that includes `assets/dist/`
   - OR update .gitignore to preserve manifest.json
   - Test distribution package creation
   - **Time:** 30 minutes

---

## Nice-to-Have Improvements (5-6 hours)

1. **✅ Commit package-lock.json** - For deterministic builds (10 min)
2. **✅ Enhance block.json files** - Add icons, descriptions, supports, attributes (20 min)
3. **✅ Decide on TypeScript strategy** - Either adopt fully or remove TS config (2-4 hrs)
4. **✅ Add CI frontend linting** - ESLint, Stylelint, TypeScript checks (30 min)
5. **✅ Add build verification** - Ensure assets build correctly in CI (30 min)

---

## Final Assessment

**Grade: B-**

**Strengths:**
- ✅ Modern tooling (Vite 5+, React 18, Docker)
- ✅ Enterprise-grade Vite configuration
- ✅ Proper security practices (.env not committed)
- ✅ Comprehensive dev dependencies
- ✅ Block structure in place
- ✅ Complete CI/CD pipeline foundation

**Critical Issues:**
- ❌ Outdated version requirements (PHP 7.4, WP 6.0)
- ❌ Missing PHP 8.3 in CI tests
- ❌ Unnecessary production dependencies
- ❌ Docker configuration placeholder
- ❌ Potential manifest location mismatch

**Ready for Feature Development:** CONDITIONAL

**Requirements:**
1. ✅ Fix 4 real blockers (PHP, WP, CI, Docker) - 30 minutes
2. ✅ Address 3 additional must-fix items - 3-4 hours
3. Then proceed with feature development

**Total Estimated Time to Reach Solid A- Foundation:** 4-6 hours

---

## Implementation Checklist

Print this checklist and mark items as complete:

### Immediate Blockers (Do First)
- [ ] 1. Update PHP requirement in plugin header to 8.1
- [ ] 2. Update PHP requirement in composer.json to ^8.1
- [ ] 3. Update composer.json platform PHP to 8.3.0
- [ ] 4. Update WP requirement in plugin header to 6.7
- [ ] 5. Update WP requirement in composer.json to 6.7
- [ ] 6. Update PHP version check code in main file
- [ ] 7. Add PHP 8.3 to CI matrix
- [ ] 8. Remove PHP 8.1 from CI matrix
- [ ] 9. Fix Docker volume mount path

### Additional Must-Fix
- [ ] 10. Remove symfony/polyfill-php80 from composer.json
- [ ] 11. Remove monolog/monolog from composer.json
- [ ] 12. Remove illuminate/collections from composer.json
- [ ] 13. Replace Monolog usage with WP error_log
- [ ] 14. Replace Illuminate Collections with native PHP
- [ ] 15. Run composer update
- [ ] 16. Fix Vite manifest location (plugin or PHP)
- [ ] 17. Test build process end-to-end
- [ ] 18. Create distribution build script OR update .gitignore

### Nice-to-Have
- [ ] 19. Commit package-lock.json (optional)
- [ ] 20. Enhance product-showcase block.json
- [ ] 21. Enhance product-grid block.json
- [ ] 22. Adopt TypeScript OR remove TS config
- [ ] 23. Add frontend linting to CI
- [ ] 24. Add build verification to CI

---

## Quick Reference Commands

```bash
# After fixing version requirements
cd wp-content/plugins/affiliate-product-showcase

# Update dependencies
composer update

# Rebuild frontend
npm run build

# Run tests
composer test

# Run type check (if using TypeScript)
npm run typecheck

# Run CI locally
composer ci

# Build distribution package
bash ../../scripts/build-distribution.sh

# Test Docker
cd ../../docker
docker-compose up --build

# Run PHPStan
composer phpstan

# Run Psalm
composer psalm

# Run PHPCS
composer phpcs
```

---

**End of Combined Audit Report - audit-L2 + audit-G**

*Generated by: Enterprise WordPress Plugin Auditor*  
*Date: January 13, 2026*  
*Standards: WordPress VIP/Enterprise 2026*

---

## Appendix: Reference to plan_sync.md

All findings can be traced to specific items in `plan/plan_sync.md`:

| Finding | plan_sync.md References |
|---------|------------------------|
| 1. Docker mount placeholder | Items 1.1.4, 1.1.8 |
| 2. .env not committed | Item 1.6.1 |
| 3. PHP 7.4 requirement | Items 1.4.3, 1.4.7, 1.7.1 |
| 4. WP 6.0 minimum | Items 1.4.8, 1.7.1 |
| 5. package-lock.json gitignored | Item 1.5.7 |
| 6. assets/dist/ gitignored | Items 1.11.1, 1.11.2 |
| 7. Minimal block.json | Items 1.10.1, 1.10.2 |
| 8. Vite manifest location | Items 1.5.8, 1.11.3 |
| 9. .js/.jsx vs TS config | Items 1.8.1, 1.9.1, 1.5.9 |
| 10. CI missing PHP 8.3 | Item 1.3.3 |
| 11. Unnecessary heavy deps | Item 1.4.8 |
