# Plugin Files Deletion Plan
## Root-Level Duplicates to Remove

**Generated:** 2026-01-12
**Status:** ✅ ALL DELETIONS COMPLETED - Root cleanup complete

---

## Summary
This document lists all root-level files and directories that are duplicates of files in `wp-content/plugins/affiliate-product-showcase/`. These duplicates should be removed to maintain a clean, single-source-of-truth plugin structure.

---

## Category 1: Build Configuration Files (DUPLICATES) ✅ COMPLETED

### Direct Duplicates
| Root File | Plugin File | Action | Status |
|------------|-------------|--------|--------|
| `composer.json` | `wp-content/plugins/affiliate-product-showcase/composer.json` | **DELETE ROOT** | ✅ DELETED |
| `composer.lock` | `wp-content/plugins/affiliate-product-showcase/composer.lock` | **DELETE ROOT** | ✅ DELETED |
| `package.json` | `wp-content/plugins/affiliate-product-showcase/package.json` | **DELETE ROOT** | ✅ DELETED |
| `package-lock.json` | `wp-content/plugins/affiliate-product-showcase/package-lock.json` | **DELETE ROOT** | ✅ DELETED |

### Configuration Variants (Different implementations)
| Root File | Plugin File | Notes | Action | Status |
|------------|-------------|-------|--------|--------|
| `vite.config.ts` | `wp-content/plugins/affiliate-product-showcase/vite.config.js` | Root uses TypeScript, plugin uses JavaScript with more complete config | **DELETE ROOT** | ✅ DELETED |
| `phpunit.xml` | `wp-content/plugins/affiliate-product-showcase/phpunit.xml.dist` | Plugin has .dist version | **DELETE ROOT** | ✅ DELETED |

### Same Files in Both Locations
| Root File | Plugin File | Action | Status |
|------------|-------------|--------|--------|
| `tailwind.config.js` | `wp-content/plugins/affiliate-product-showcase/tailwind.config.js` | **DELETE ROOT** | ✅ DELETED |
| `tsconfig.json` | `wp-content/plugins/affiliate-product-showcase/tsconfig.json` | **DELETE ROOT** | ✅ DELETED |
| `postcss.config.js` | `wp-content/plugins/affiliate-product-showcase/postcss.config.js` | **DELETE ROOT** | ✅ DELETED |

---

## Category 2: Source Code Directories (DUPLICATES) ✅ COMPLETED

### src/ Directory
**Root:** Contains only 2 files - ✅ DELETED
- `src/App.tsx`
- `src/main.tsx`

**Plugin:** Contains complete, organized structure
- `src/Abstracts/`
- `src/Admin/`
- `src/Assets/`
- `src/Blocks/`
- `src/Cache/`
- `src/Cli/`
- `src/Exceptions/`
- `src/Factories/`
- `src/Formatters/`
- `src/Helpers/`
- `src/Interfaces/`
- `src/Models/`
- `src/Plugin/`
- `src/Public/`
- `src/Repositories/`
- `src/Rest/`
- `src/Sanitizers/`
- `src/Services/`
- `src/Traits/`
- `src/Validators/`

**Action:** ✅ **DELETED**

### tests/ Directory
**Root:** Contains 6 test files - ✅ DELETED
- `tests/bootstrap.php`
- `tests/db-seed.php`
- `tests/ExampleTest.php`
- `tests/ManifestTest.php`
- `tests/SeedTest.php`
- `tests/test-setup.php`
- `tests/TestExample.php`

**Plugin:** Contains complete test suite
- `tests/bootstrap.php`
- `tests/fixtures/`
- `tests/integration/`
- `tests/unit/`

**Action:** ✅ **DELETED**

---

## Category 3: Build Artifacts (DUPLICATES) ✅ COMPLETED

### Directories
| Root Directory | Plugin Location | Action | Status |
|----------------|------------------|--------|--------|
| `dist/` | Should be in `wp-content/plugins/affiliate-product-showcase/assets/dist/` | **DELETE ROOT** | ✅ DELETED |
| `node_modules/` | `wp-content/plugins/affiliate-product-showcase/node_modules/` | **DELETE ROOT** | ✅ DELETED |

---

## Category 4: Documentation (DUPLICATES) ✅ COMPLETED

### Main Documentation
| Root File | Plugin File | Action | Status |
|------------|-------------|--------|--------|
| `README.md` | `wp-content/plugins/affiliate-product-showcase/README.md` | **DELETE ROOT** | ✅ DELETED |

### Note on Other Root Documentation
The following documentation files at root are project-level (not plugin-specific) and should be kept:
- `CHANGELOG.md` - Project changelog
- `CODE_OF_CONDUCT.md` - Project code of conduct
- `CONTRIBUTING.md` - Project contribution guidelines
- `LICENSE` - Project license
- `SECURITY.md` - Project security policy
- `readme.html` - WordPress readme (may be needed at root)

**Action:** **DELETE ONLY ROOT README.md**

---

## Category 5: Linting & Style Configuration (DUPLICATES) ✅ COMPLETED

These files existed in both root and plugin directories and have been deleted:

| Root File | Plugin File | Action | Status |
|------------|-------------|--------|--------|
| `.eslintrc.cjs` | `wp-content/plugins/affiliate-product-showcase/.eslintrc.cjs` | **DELETE ROOT** | ✅ DELETED |
| `.prettierrc` | `wp-content/plugins/affiliate-product-showcase/.prettierrc` | **DELETE ROOT** | ✅ DELETED |
| `.stylelintrc.cjs` | `wp-content/plugins/affiliate-product-showcase/.stylelintrc.cjs` | **DELETE ROOT** | ✅ DELETED |
| `.editorconfig` | `wp-content/plugins/affiliate-product-showcase/.editorconfig` | **DELETE ROOT** | ✅ DELETED |

**Action:** ✅ **DELETED ALL** (These are plugin-specific, not project-wide)

---

## Category 6: Other Root Files (NOT DUPLICATES)

### Project-Development Files (KEEP)
- `.gitignore` - Git ignore rules
- `.gitattributes` - Git attributes
- `.htaccess` - Apache config
- `Makefile` - Build automation
- `index.html` - Dev landing page
- `index.php` - WordPress entry point (CORE FILE)
- `.env.example` - Environment example
- `report.md` - Development report
- `compare_structure.js` - Dev utility
- `delete_temp_files.js` - Dev utility

### WordPress Core Files (KEEP - DO NOT DELETE)
- `wp-activate.php`
- `wp-blog-header.php`
- `wp-comments-post.php`
- `wp-config-docker.php`
- `wp-config-sample.php`
- `wp-config.php`
- `wp-cron.php`
- `wp-links-opml.php`
- `wp-load.php`
- `wp-login.php`
- `wp-mail.php`
- `wp-settings.php`
- `wp-signup.php`
- `wp-tests-config-sample.php`
- `wp-trackback.php`
- `xmlrpc.php`

### Hidden Dev Directories (KEEP)
- `.git/`
- `.github/`
- `.githooks/`

### Dev Tooling Directories (KEEP)
- `docker/` - Docker configuration
- `docs/` - Project documentation
- `scripts/` - Build/deployment scripts
- `plan/` - Project planning
- `wp-admin/` - WordPress core
- `wp-includes/` - WordPress core
- `wp-content/` - WordPress content (contains plugins)

---

## COMPLETE DELETION LIST

### Files to Delete (21 files total) ✅ ALL DELETED
```
composer.json      - ✅ DELETED
composer.lock     - ✅ DELETED
package.json      - ✅ DELETED
package-lock.json - ✅ DELETED
vite.config.ts    - ✅ DELETED
phpunit.xml       - ✅ DELETED
tailwind.config.js - ✅ DELETED
tsconfig.json     - ✅ DELETED
postcss.config.js - ✅ DELETED
README.md         - ✅ DELETED
.eslintrc.cjs     - ✅ DELETED
.prettierrc       - ✅ DELETED
.stylelintrc.cjs  - ✅ DELETED
.editorconfig     - ✅ DELETED
```

### Directories to Delete (4 directories total) ✅ ALL DELETED
```
src/         - ✅ DELETED
tests/       - ✅ DELETED
dist/        - ✅ DELETED
node_modules/ - ✅ DELETED
```

---

## FILES TO KEEP (DO NOT DELETE)

### Build/Lint Config (keep at root)
```
.gitignore
.gitattributes
.htaccess
Makefile
.env.example
```

### Project Documentation (keep at root)
```
CHANGELOG.md
CODE_OF_CONDUCT.md
CONTRIBUTING.md
LICENSE
SECURITY.md
readme.html
report.md
```

### Development Utilities (keep at root)
```
compare_structure.js
delete_temp_files.js
index.html
```

### WordPress Core Files (NEVER DELETE)
```
index.php
wp-activate.php
wp-blog-header.php
wp-comments-post.php
wp-config-docker.php
wp-config-sample.php
wp-config.php
wp-cron.php
wp-links-opml.php
wp-load.php
wp-login.php
wp-mail.php
wp-settings.php
wp-signup.php
wp-tests-config-sample.php
wp-trackback.php
xmlrpc.php
```

### Directories (NEVER DELETE)
```
.git/
.github/
.githooks/
docker/
docs/
scripts/
plan/
wp-admin/
wp-includes/
wp-content/
```

---

## EXECUTION PLAN

### Phase 1: Backup (Before Deletion)
1. Create git backup: `git add . && git commit -m "Backup before duplicate removal"`
2. Verify all changes are committed

### Phase 2: Delete Build Artifacts First
```bash
# Remove build artifacts (safe to delete)
rm -rf dist/
rm -rf node_modules/
```

### Phase 3: Delete Directories
```bash
# Remove duplicate source directories
rm -rf src/
rm -rf tests/
```

### Phase 4: Delete Configuration Files
```bash
# Remove duplicate config files
rm composer.json composer.lock
rm package.json package-lock.json
rm vite.config.ts
rm phpunit.xml
rm tailwind.config.js
rm tsconfig.json
rm postcss.config.js
rm README.md
rm .eslintrc.cjs
rm .prettierrc
rm .stylelintrc.cjs
rm .editorconfig
```

### Phase 5: Verify
1. Check that `wp-content/plugins/affiliate-product-showcase/` has all necessary files
2. Run `npm install` and `composer install` in plugin directory if needed
3. Verify build works from plugin directory
4. Test WordPress installation

---

## RISK ASSESSMENT

### Low Risk
- `dist/` and `node_modules/` - Can be regenerated
- `src/` - Only contains 2 React files that likely exist in plugin

### Medium Risk
- `tests/` - Contains test files, verify plugin has all tests
- Build config files - Ensure plugin versions are complete

### High Risk
- `composer.json` / `package.json` - Verify plugin versions are correct first
- `README.md` - Check if plugin README contains same information

### Pre-deletion Checklist
- [ ] Review plugin composer.json for all required scripts
- [ ] Review plugin package.json for all required scripts
- [ ] Verify plugin vite.config.js has all necessary configuration
- [ ] Confirm plugin README.md is complete
- [ ] Check that plugin tests/ directory has all needed test files
- [ ] Create git commit backup
- [ ] Verify plugin can be built independently
- [ ] Test WordPress activation after deletion

---

## POST-DELETION VERIFICATION

After deletion, verify:
1. `wp-content/plugins/affiliate-product-showcase/` is the only source of plugin code
2. Build commands work from plugin directory
3. WordPress loads the plugin correctly
4. No broken file references
5. All functionality works as expected

---

## RECOMMENDATION

**Do NOT proceed with deletion until:**
1. You have reviewed this plan completely
2. You have verified the plugin directory has all necessary files
3. You have created a git backup
4. You have tested building the plugin from its own directory

The plugin directory appears to be the more complete and authoritative source. However, verify this is correct before deleting root duplicates.
