# 10/10 Compliance Changes Needed

## Summary of Required Changes

Based on the 8-point compliance plan, here are all the changes needed to reach 10/10 compliance:

---

## 1. ✅ Add Missing Static Analysis Configurations

**Status:** PARTIALLY COMPLETE - Configs exist at repo root but referenced from plugin

### Current State:
- ✅ `phpstan.neon` exists at repo root
- ✅ `psalm.xml` exists at repo root  
- ❌ `phpstan.neon.dist` missing from plugin directory
- ❌ `psalm.xml.dist` missing from plugin directory

### Required Actions:

**Option A:** Copy repo-level configs to plugin directory (Recommended for standalone plugin)
```bash
# Copy configs from repo root to plugin directory
copy phpstan.neon wp-content\plugins\affiliate-product-showcase\phpstan.neon.dist
copy psalm.xml wp-content\plugins\affiliate-product-showcase\psalm.xml.dist
```

**Option B:** Update composer.json to reference repo-level configs
```json
"psalm": "vendor/bin/psalm --config=../../psalm.xml --show-info=false --threads=4",
"phpstan": "vendor/bin/phpstan analyse --configuration=../../phpstan.neon --memory-limit=1G"
```

**Decision Needed:** Which approach to use?

---

## 2. ⚠️ Update Plugin composer.json Scripts

**Status:** NEEDS UPDATES

### Current Issues:
1. Scripts reference `psalm.xml.dist` and `phpstan.neon.dist` (don't exist in plugin dir)
2. PHP version mismatch:
   - `require.php`: `"^8.1"` ✅
   - `config.platform.php`: `"8.3.0"` ⚠️ (should be 8.1 minimum)
   - `extra.wordpress-plugin.minimum-php`: `"8.1"` ✅

### Required Changes:

#### Change 1: Fix config paths OR add missing files
See Option A/B above

#### Change 2: Align PHP version in composer.json
```json
// Current:
"platform": {
  "php": "8.3.0"
}

// Should be:
"platform": {
  "php": "8.1.0"
}
```

#### Change 3: Ensure scripts work with working-dir
The `composer analyze` command in docs uses `--working-dir`, so scripts should work relative to plugin directory.

---

## 3. ❌ Update CI Workflow

**Status:** CRITICAL - CI is incomplete

### Current CI Issues:
1. Only runs PHPUnit (tests)
2. Missing: PHPCS (code style)
3. Missing: PHPStan (static analysis)
4. Missing: Psalm (static analysis)
5. Runs on root directory but plugin code is in subdirectory
6. No frontend linting/tests (npm)

### Required Changes to `.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [ main, master ]
  pull_request:
    branches: [ main, master ]

jobs:
  php-analyze:
    runs-on: ubuntu-22.04
    strategy:
      matrix:
        php: ['8.1', '8.2', '8.3']
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          tools: composer:v2
          extensions: mbstring, dom, xml, xmlwriter
      
      - name: Setup Composer cache
        uses: actions/cache@v4
        with:
          path: ~/.composer/cache
          key: composer-${{ matrix.php }}-${{ hashFiles('wp-content/plugins/affiliate-product-showcase/composer.lock') }}
          restore-keys: composer-
      
      - name: Install dependencies
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer install --no-interaction --no-progress --prefer-dist
      
      - name: Run PHPCS
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer phpcs
      
      - name: Run PHPStan
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer phpstan
      
      - name: Run Psalm
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer psalm
  
  php-test:
    runs-on: ubuntu-22.04
    strategy:
      matrix:
        php: ['8.1', '8.2', '8.3']
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          tools: composer:v2
          extensions: mbstring, dom, xml, xmlwriter
          coverage: xdebug
      
      - name: Setup Composer cache
        uses: actions/cache@v4
        with:
          path: ~/.composer/cache
          key: composer-${{ matrix.php }}-${{ hashFiles('wp-content/plugins/affiliate-product-showcase/composer.lock') }}
          restore-keys: composer-
      
      - name: Install dependencies
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer install --no-interaction --no-progress --prefer-dist
      
      - name: Run PHPUnit
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: composer phpunit
  
  frontend-test:
    runs-on: ubuntu-22.04
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '18'
          cache: 'npm'
          cache-dependency-path: wp-content/plugins/affiliate-product-showcase/package-lock.json
      
      - name: Install dependencies
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: npm ci
      
      - name: Run ESLint
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: npm run lint
      
      - name: Run frontend tests
        working-directory: wp-content/plugins/affiliate-product-showcase
        run: npm run test
```

---

## 4. ⚠️ Align PHP Version Across All Files

**Status:** NEEDS ALIGNMENT

### Current PHP Versions:
| File | Version | Status |
|------|---------|--------|
| `composer.json` (require.php) | `"^8.1"` | ✅ Correct |
| `composer.json` (config.platform.php) | `"8.3.0"` | ❌ Should be 8.1.0 |
| `composer.json` (extra.wordpress-plugin.minimum-php) | `"8.1"` | ✅ Correct |
| `.github/workflows/ci.yml` | Tests 8.1, 8.2, 8.3 | ✅ Correct |
| Documentation (assistant-instructions.md) | PHP 8.1+ | ✅ Correct |
| Documentation (assistant-quality-standards.md) | PHP 8.1+ | ✅ Correct |

### Required Change:
Update `wp-content/plugins/affiliate-product-showcase/composer.json`:
```json
"platform": {
  "php": "8.1.0"  // Changed from 8.3.0
}
```

---

## 5. ⚠️ Run Local Verification Commands

**Status:** NEEDS EXECUTION AND FIX

### Commands to Run (from repo root):
```bash
# PHP analysis
composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze

# PHP tests
composer --working-dir=wp-content/plugins/affiliate-product-showcase test

# Frontend lint
npm --prefix wp-content/plugins/affiliate-product-showcase run lint

# Frontend tests
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```

### Expected Issues to Fix:
1. **Psalm config not found** - Fix missing `psalm.xml.dist`
2. **PHPStan config not found** - Fix missing `phpstan.neon.dist`
3. **Any code style violations** - Fix PHPCS issues
4. **Any static analysis errors** - Fix PHPStan/Psalm errors
5. **Any test failures** - Fix PHPUnit failures
6. **Any frontend linting issues** - Fix ESLint/Stylelint issues
7. **Any frontend test failures** - Fix Jest/Vitest failures

### Action Required:
Run these commands and document all failures, then fix them systematically.

---

## 6. ⚠️ Verify Pre-commit/Pre-push Hooks

**Status:** NEEDS VERIFICATION

### Current Hook Configuration:
In `composer.json`:
```json
"extra": {
  "hooks": {
    "pre-commit": [
      "composer parallel-lint",
      "composer phpcs",
      "composer phpstan"
    ],
    "commit-msg": "vendor/bin/validate-commit-msg",
    "pre-push": [
      "composer test"
    ]
  }
}
```

### Verification Steps:
1. Check if `validate-commit-msg` exists: `vendor/bin/validate-commit-msg`
2. Test pre-commit hook: Make a dummy commit
3. Test pre-push hook: Try to push to a test branch

### Likely Issues:
1. `validate-commit-msg` command may not exist
2. Hook scripts reference tools with missing configs
3. Hooks don't include npm linting/testing

### Required Fixes:
1. Install commit-msg validator or remove the hook
2. Add npm commands to hooks if applicable
3. Ensure all hook commands work with fixed config paths

---

## 7. ⚠️ Add Documentation Updates

**Status:** PARTIALLY COMPLETE

### Already Documented ✅:
- Local Verification Commands (in both docs files)
- PHP 8.1+ requirement (in both docs files)
- WPCS/PSR-12 standards clarified

### Still Needed ❌:

#### Add to `README.md`:
```markdown
## Local Development

### Prerequisites
- PHP 8.1 or higher
- Composer 2.x
- Node.js 18 or higher
- npm 9 or higher

### Installation
```bash
# Clone repository
git clone https://github.com/randomfact236/affiliate-product-showcase.git
cd affiliate-product-showcase

# Install PHP dependencies
cd wp-content/plugins/affiliate-product-showcase
composer install

# Install JavaScript dependencies
npm install
```

### Running Quality Checks

Before committing code, run these commands to ensure quality:

```bash
# From repo root:

# PHP static analysis
composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze

# PHP tests
composer --working-dir=wp-content/plugins/affiliate-product-showcase test

# Frontend linting
npm --prefix wp-content/plugins/affiliate-product-showcase run lint

# Frontend tests
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```

All checks must pass before committing code.
```

#### Add to `CONTRIBUTING.md`:
- Pre-commit checklist reference
- How to run local verification
- What to do if checks fail
- CI workflow explanation

---

## 8. ❌ Create Branch and Validate CI

**Status:** NOT STARTED

### Required Actions:

1. **Create feature branch:**
   ```bash
   git checkout -b compliance/10-10-compliance
   ```

2. **Implement all changes** (items 1-7 above)

3. **Run local verification:**
   ```bash
   composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze
   composer --working-dir=wp-content/plugins/affiliate-product-showcase test
   npm --prefix wp-content/plugins/affiliate-product-showcase run lint
   npm --prefix wp-content/plugins/affiliate-product-showcase run test
   ```

4. **Commit and push:**
   ```bash
   git add .
   git commit -m "compliance: Achieve 10/10 compliance

   - Add missing static analysis configs
   - Update CI workflow for full analysis
   - Align PHP version to 8.1 minimum
   - Fix composer.json config paths
   - Add verification documentation
   - Update README with local dev setup"
   git push origin compliance/10-10-compliance
   ```

5. **Create Pull Request**
   - Link to this compliance document
   - Request review
   - Verify CI passes all checks

6. **Fix any remaining CI failures**
   - Address each failure systematically
   - Update documentation as needed

---

## Priority Order

### CRITICAL (Must complete first):
1. **Fix missing configs** - Blocks analysis tools
2. **Update CI workflow** - Currently only runs PHPUnit
3. **Align PHP version** - Prevents deployment issues

### HIGH (Complete second):
4. **Run local verification** - Find and fix code issues
5. **Verify hooks** - Ensure pre-commit works properly

### MEDIUM (Complete third):
6. **Add documentation** - Update README and CONTRIBUTING

### FINAL:
7. **Create branch and validate** - Test everything in CI

---

## Success Criteria

You'll know you've achieved 10/10 compliance when:

✅ All config files exist in correct locations
✅ `composer analyze` runs successfully with 0 errors
✅ `composer test` passes with 90%+ coverage
✅ `npm run lint` passes with 0 errors
✅ `npm run test` passes all tests
✅ CI workflow runs PHPStan, Psalm, PHPCS, PHPUnit, ESLint, and Jest
✅ PHP version is consistently 8.1+ across all files
✅ Pre-commit hooks work correctly
✅ Documentation includes local verification commands
✅ CI passes on all branches

---

## Next Steps

**Question for you:** Would you like me to:

1. **Start implementing these changes** (begin with fixing missing configs)
2. **Run the verification commands first** and report all errors before fixing
3. **Create the CI workflow update** as a separate PR
4. **Something else?**

Let me know how you'd like to proceed!
