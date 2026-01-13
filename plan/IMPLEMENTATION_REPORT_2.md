# Affiliate Product Showcase - Audit Findings Implementation Report (v2)

**Date:** January 13, 2026  
**Audit Reference:** plan/Combined-L2 and G2.md  
**Scope:** Only the 11 findings from the combined audit + the remaining gaps identified during strict verification of plan/IMPLEMENTATION_REPORT.md.

---

## Executive Summary

This v2 report provides **the exact code changes that must be implemented** (or explicitly confirmed) to close the remaining gaps and eliminate regression risk for the audit findings.

Key focus areas:
- Block asset handle wiring and action priority ordering (Finding #7)
- TypeScript strategy inconsistency resolution (Finding #9)
- CI matrix minimum-version alignment (Finding #10)
- Dependency removal verification for Illuminate usage (Finding #11)

---

## Implementation Details (v2)

### Finding #1: Docker Volume Mount Path (IMMEDIATE BLOCKER)

**Status:** ✅ No further changes required

**Code to implement:** None.

---

### Finding #2: .env Setup (ALREADY CORRECT)

**Status:** ✅ No further changes required

**Code to implement:** None.

---

### Finding #3: Update PHP Requirement to 8.1+ (IMMEDIATE BLOCKER)

**Status:** ✅ No further changes required

**Code to implement:** None.

---

### Finding #4: Update WordPress Requirement to 6.7+ (IMMEDIATE BLOCKER)

**Status:** ✅ No further changes required

**Code to implement:** None.

---

### Finding #5: Handle package-lock.json (OPTIONAL)

**Status:** ✅ No further changes required (audit allows keeping gitignored)

**Code to implement:** None.

---

### Finding #6: Resolve Marketplace Distribution Issue (ADDITIONAL MUST-FIX)

**Status:** ✅ Implemented via distribution build script

**Required code (must exist exactly once and be used):**

**File:** scripts/build-distribution.sh

```bash
#!/bin/bash

# Affiliate Product Showcase - Distribution Build Script
# Creates a distribution package including compiled assets for WordPress.org marketplace

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Distribution Build Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Get plugin directory
PLUGIN_DIR="wp-content/plugins/affiliate-product-showcase"
DIST_DIR="dist"
DIST_ZIP="affiliate-product-showcase-${1:-latest}.zip"

# Check if plugin directory exists
if [ ! -d "$PLUGIN_DIR" ]; then
    echo -e "${RED}Error: Plugin directory not found: $PLUGIN_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Building assets...${NC}"
cd "$PLUGIN_DIR"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${RED}Error: npm is not installed${NC}"
    exit 1
fi

# Build production assets
npm run build
echo -e "${GREEN}✓ Assets built successfully${NC}"
echo ""

cd ../..

echo -e "${YELLOW}Step 2: Creating distribution package...${NC}"

# Clean previous distribution
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

# Copy plugin files to distribution directory
echo "Copying plugin files..."
cp -r "$PLUGIN_DIR" "$DIST_DIR/"

# Create distribution package
echo "Creating zip archive..."
cd "$DIST_DIR"

# Create zip excluding development files
zip -r "../$DIST_ZIP" "affiliate-product-showcase" \
    -x "affiliate-product-showcase/node_modules/*" \
    -x "affiliate-product-showcase/.git/*" \
    -x "affiliate-product-showcase/.gitignore" \
    -x "affiliate-product-showcase/.env.example" \
    -x "affiliate-product-showcase/.DS_Store" \
    -x "affiliate-product-showcase/*.log" \
    -x "affiliate-product-showcase/tests/*" \
    -x "affiliate-product-showcase/.vscode/*" \
    -x "affiliate-product-showcase/.idea/*" \
    -x "affiliate-product-showcase/tsconfig.json" \
    -x "affiliate-product-showcase/vite.config.js" \
    -x "affiliate-product-showcase/tailwind.config.js" \
    -x "affiliate-product-showcase/postcss.config.js" \
    -x "affiliate-product-showcase/package.json" \
    -x "affiliate-product-showcase/package-lock.json"

cd ..

# Verify zip was created
if [ ! -f "$DIST_ZIP" ]; then
    echo -e "${RED}Error: Distribution package was not created${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Distribution package created: $DIST_ZIP${NC}"
echo ""

# Get file size
FILE_SIZE=$(du -h "$DIST_ZIP" | cut -f1)
echo "Package size: $FILE_SIZE"

# List package contents
echo ""
echo -e "${YELLOW}Package contents:${NC}"
unzip -l "$DIST_ZIP" | head -20

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Build Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Distribution package: $DIST_ZIP"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Upload $DIST_ZIP to WordPress.org"
echo "2. Test the plugin from the downloaded package"
echo "3. Update version number and changelog if needed"
echo ""
```

---

### Finding #7: Enhance block.json Files (NICE-TO-HAVE)

**Status:** ⚠️ Requires additional implementation to prevent block asset handle regressions

**Problem to resolve (must be fixed):**
- block.json uses these handles:
  - editorScript/editorStyle: `aps-blocks-editor`
  - style: `aps-blocks`
  - viewScript: `aps-blocks-frontend`
- Current asset enqueuing only registers/enqueues:
  - script handle `aps-blocks` (blocks.js)
  - style handle `aps-editor-style` (editor.css)
- WordPress core enqueues registered block handles on `enqueue_block_editor_assets` at priority 10.
  - Therefore, these handles must be **registered before priority 10**.

#### Required code to implement

**1) Register the block editor assets BEFORE core enqueues block assets**

**File:** wp-content/plugins/affiliate-product-showcase/src/Plugin/Loader.php

Change the action definition to set priority **9**:

```php
protected function actions(): array {
	return [
		[ 'init', 'register_product_cpt' ],
		[ 'init', 'register_blocks' ],
		[ 'init', 'register_shortcodes' ],
		[ 'widgets_init', 'register_widgets' ],
		// IMPORTANT: run before WP core priority-10 block enqueue.
		[ 'enqueue_block_editor_assets', 'enqueue_block_editor_assets', 9 ],
		// IMPORTANT: ensure block front-end handles exist before core enqueues them.
		[ 'enqueue_block_assets', 'enqueue_block_assets', 9 ],
		[ 'rest_api_init', 'register_rest_controllers' ],
		[ 'cli_init', 'register_cli' ],
	];
}

public function enqueue_block_assets(): void {
	$this->public->enqueue_block_assets();
}
```

**2) Add a frontend block-assets entrypoint in Public_**

**File:** wp-content/plugins/affiliate-product-showcase/src/Public/Public_.php

```php
public function enqueue_block_assets(): void {
	$this->assets->enqueue_block_assets();
}
```

**3) Ensure the handles referenced in block.json are registered**

**File:** wp-content/plugins/affiliate-product-showcase/src/Assets/Assets.php

Add a new method that registers/enqueues the handles used by block.json:

```php
public function enqueue_block_assets(): void {
	// Provides the front-end style handle used by block.json: "style": "aps-blocks".
	$this->manifest->enqueue_style( 'aps-blocks', 'frontend.css' );

	// Provides the viewScript handle used by block.json: "viewScript": "aps-blocks-frontend".
	$this->manifest->enqueue_script( 'aps-blocks-frontend', 'frontend.js', [ 'wp-element' ], true );
}

public function enqueue_editor(): void {
	// Provides the editorScript/editorStyle handles used by block.json.
	$this->manifest->enqueue_script(
		'aps-blocks-editor',
		'blocks.js',
		[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
		true
	);
	$this->manifest->enqueue_style( 'aps-blocks-editor', 'editor.css' );

	// Keep existing handles for backwards compatibility (if other code uses them).
	$this->manifest->enqueue_script(
		'aps-blocks',
		'blocks.js',
		[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
		true
	);
	$this->manifest->enqueue_style( 'aps-editor-style', 'editor.css' );
}
```

---

### Finding #8: Fix Vite Manifest Location (ADDITIONAL MUST-FIX)

**Status:** ✅ No further changes required

**Code to implement:** None.

---

### Finding #9: Decide on TypeScript Strategy (NICE-TO-HAVE)

**Status:** ⚠️ Must resolve the inconsistency (tsconfig + TypeScript deps present, but TS not actually used)

**Decision for v2 (choose one, implement fully):**
- **Option B (remove TypeScript config)** is the minimal-change fix that fully resolves the audit’s inconsistency without a migration.

#### Required code to implement (Option B)

**1) Delete TS config**
- Remove file: wp-content/plugins/affiliate-product-showcase/tsconfig.json

**2) Remove TypeScript tooling from package.json**

**File:** wp-content/plugins/affiliate-product-showcase/package.json

- Remove devDependencies:
  - `typescript`
  - `@types/node`
- Remove scripts:
  - `typecheck`
- Update composite scripts that call `typecheck`:
  - `quality`
  - `prepush`

Example patch (conceptual):
```json
{
  "scripts": {
    "quality": "npm run lint && npm run test",
    "prepush": "npm run quality && npm run assert-coverage"
  },
  "devDependencies": {
    "typescript": "REMOVED",
    "@types/node": "REMOVED"
  }
}
```

---

### Finding #10: Add PHP 8.3 to CI Matrix (IMMEDIATE BLOCKER)

**Status:** ⚠️ Must align CI with declared minimum supported PHP

**Problem to resolve (must be fixed):**
- Plugin minimum is declared as PHP 8.1.
- CI matrix currently tests 8.2, 8.3, 8.4 (missing 8.1).

#### Required code to implement

**File:** .github/workflows/ci.yml

Add PHP 8.1 back to the matrix **or** raise the plugin minimum to 8.2. For this project’s stated target (min 8.1), CI must include 8.1.

```yaml
strategy:
  matrix:
    include:
      - os: ubuntu-22.04
        php: '8.1'
      - os: ubuntu-22.04
        php: '8.2'
      - os: ubuntu-22.04
        php: '8.3'
      - os: ubuntu-22.04
        php: '8.4'
```

---

### Finding #11: Remove Unnecessary Production Dependencies (IMMEDIATE BLOCKER)

**Status:** ⚠️ Must add explicit verification for Illuminate usage (Monolog was checked; Illuminate must be checked too)

#### Required code / changes to implement

**1) Add explicit verification steps (required evidence)**

These commands must be executed and the results recorded in the report:

```bash
# From repo root
cd wp-content/plugins/affiliate-product-showcase

# Ensure lock/vendor reflect removals
composer update

# Confirm no Illuminate references remain
grep -R "use Illuminate" -n src/ || true
grep -R "Illuminate\\\\" -n src/ || true

# Run tests
composer test
```

**2) If Illuminate references exist, replace with native PHP arrays**

Example transformation:

```php
// Before
use Illuminate\\Support\\Collection;

$filtered = ( new Collection( $items ) )->filter( fn( $item ) => $item->isActive() );

// After
$filtered = array_filter( $items, static fn( $item ) => $item->isActive() );
```

---

## Completion Checklist (v2)

- [ ] Finding #7 implemented (priority ordering + handle registration)
- [ ] Finding #9 implemented (TypeScript strategy fully resolved)
- [ ] Finding #10 implemented (CI includes PHP 8.1 if minimum is 8.1)
- [ ] Finding #11 implemented (explicit Illuminate verification + composer update + tests)

---

## Conclusion

Once the v2 items are implemented exactly as above, the combined audit findings will be both (1) implemented and (2) regression-safe under WordPress 6.7+ behavior for block asset enqueue ordering, with consistent platform/version declarations for January 2026 standards.
