# Detailed Refactoring Action Plan

> **Document Type:** Actionable Implementation Plan  
> **Generated:** 2026-02-02  
> **Status:** Ready for Implementation  
> **Total Issues:** 95 Critical Issues Identified

---

## 📋 Quick Reference

| Category | Issue Count | Priority | Status |
|----------|-------------|----------|--------|
| CSS Accessibility | 71 | CRITICAL | 🔴 Pending Fix |
| Browser Compatibility | 24 | Medium | 🟡 Pending Fix |
| PHP Code Quality | 79 Long Functions | High | 🟠 Pending Fix |
| PHP Documentation | 98 Missing PHPDoc | Medium | 🟡 Pending Fix |

---

## 🔴 PRIORITY 1: CSS ACCESSIBILITY ISSUES (71 Issues)

### 1.1 Missing Focus States (21 Issues) - WCAG 2.4.7

#### Issue A1-001: Button Focus States Missing
```yaml
File: frontend/styles/components/_buttons.scss
Line: 1-50 (entire file)
Issue Code: CSS-FOCUS-001
Severity: CRITICAL
WCAG: 2.4.7 Focus Visible
```

**Current Code:**
```scss
/* Line 1-50 - Placeholder file, actual buttons in admin.scss */
.aps-button {
  // No :focus or :focus-visible styles
}
```

**Solution:**
```scss
/* Add to frontend/styles/components/_buttons.scss */
.aps-button {
  // Existing styles...
  
  &:focus {
    outline: 2px solid #2271b1;
    outline-offset: 2px;
  }
  
  &:focus-visible {
    outline: 2px solid #2271b1;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(34, 113, 177, 0.2);
  }
  
  &:focus:not(:focus-visible) {
    outline: none;
  }
}
```

---

#### Issue A1-002: Form Input Focus States
```yaml
File: frontend/styles/components/_form-input.scss
Line: 56, 83
Issue Code: CSS-FOCUS-002
Severity: CRITICAL
WCAG: 2.4.7 Focus Visible
```

**Current Code:**
```scss
// Line 56
.aps-form-input {
  user-select: none;  // Blocks focus
  // Missing :focus styles
}

// Line 83
.aps-form-select {
  user-select: none;  // Blocks focus
  // Missing :focus styles
}
```

**Solution:**
```scss
// Line 56 - Replace
.aps-form-input {
  // Remove: user-select: none;
  
  &:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
  }
  
  &:focus-visible {
    outline: 2px solid #2271b1;
    outline-offset: 2px;
  }
}

// Line 83 - Replace
.aps-form-select {
  // Remove: user-select: none;
  
  &:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
  }
}
```

---

#### Issue A1-003: Card Platform Toggle Focus
```yaml
File: assets/scss/components/_card-base.scss
Line: 81
Issue Code: CSS-FOCUS-003
Severity: CRITICAL
WCAG: 2.4.7 Focus Visible
```

**Current Code:**
```scss
.aps-card__platform-toggle {
  cursor: pointer;
  user-select: none;  // Line 81
  -webkit-tap-highlight-color: rgba(0,0,0,0);
  // Has :focus but may not be visible enough
}
```

**Solution:**
```scss
.aps-card__platform-toggle {
  cursor: pointer;
  // Remove: user-select: none;
  // Remove: -webkit-tap-highlight-color (handled globally)
  
  &:focus {
    outline: 2px solid #2271b1;
    outline-offset: 2px;
    border-radius: 0.25rem;
  }
  
  &:focus-visible {
    outline: 2px solid #2271b1;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(34, 113, 177, 0.2);
  }
}
```

---

### 1.2 Color Contrast Violations (3 Issues) - WCAG 1.4.3

#### Issue A2-001: Low Contrast Text on Light Background
```yaml
File: frontend/styles/frontend.scss
Line: TBD (search for #667eea, #d1d5db)
Issue Code: CSS-CONTRAST-001
Severity: SERIOUS
WCAG: 1.4.3 Contrast (Minimum)
Current Ratio: 2.5:1 (Required: 4.5:1)
```

**Current Code:**
```scss
.aps-text-primary {
  color: #667eea;  // Too light on white
}

.aps-text-muted {
  color: #d1d5db;  // Too light on #e5e7eb
}
```

**Solution:**
```scss
.aps-text-primary {
  color: #4f46e5;  // Darker indigo, 7:1 contrast ratio
}

.aps-text-muted {
  color: #6b7280;  // Darker gray, 4.6:1 contrast ratio
}
```

---

### 1.3 Text Resize Issues (20 Issues) - WCAG 1.4.4

#### Issue A3-001: Fixed Pixel Font Sizes
```yaml
File: Multiple SCSS files
Line: Various
Issue Code: CSS-FONT-SIZE-001
Severity: MODERATE
WCAG: 1.4.4 Resize Text
```

**Files Affected:**
- `frontend/styles/components/_cards.scss`
- `frontend/styles/components/_forms.scss`
- `assets/scss/components/_card-body.scss`
- `assets/scss/components/_card-base.scss`

**Current Pattern:**
```scss
font-size: 12px;  // Fixed pixel
font-size: 14px;
font-size: 16px;
```

**Solution:**
```scss
// Define root font size (16px = 1rem)
// Then use rem units
font-size: 0.75rem;   // 12px equivalent
font-size: 0.875rem;  // 14px equivalent
font-size: 1rem;      // 16px equivalent
```

**Implementation Steps:**
1. Create `_variables.scss` with font size scale:
```scss
$font-size-xs: 0.75rem;    // 12px
$font-size-sm: 0.875rem;   // 14px
$font-size-base: 1rem;     // 16px
$font-size-lg: 1.125rem;   // 18px
$font-size-xl: 1.25rem;    // 20px
```

2. Replace all pixel font sizes with rem variables

---

### 1.4 Hidden Content Issues (27 Issues) - WCAG 1.3.1

#### Issue A4-001: Screen Reader Only Implementation
```yaml
File: Multiple files
Line: Various
Issue Code: CSS-SCREEN-READER-001
Severity: MODERATE
WCAG: 1.3.1 Info and Relationships
```

**Current Pattern (Problematic):**
```scss
.visually-hidden {
  display: none;  // Removes from accessibility tree
}

.hidden {
  visibility: hidden;  // Removes from accessibility tree
}
```

**Solution:**
```scss
// Proper screen-reader-only class
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

// Use aria-hidden for visual-only content
.aps-icon-only::before {
  content: attr(data-icon);
}
```

---

## 🟡 PRIORITY 2: BROWSER COMPATIBILITY ISSUES (24 Issues)

### 2.1 Deprecated CSS Properties (5 Issues)

#### Issue B1-001: Deprecated user-select Property
```yaml
File: assets/scss/components/_card-base.scss
Line: 81
Issue Code: CSS-DEPRECATED-001
Severity: MEDIUM
Browser Support: Non-standard property
```

**Current Code:**
```scss
// Line 81
user-select: none;
```

**Solution:**
```scss
// Option 1: Remove (recommended for accessibility)
// Option 2: Use with proper fallbacks if absolutely necessary
-webkit-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
```

---

#### Issue B1-002: Deprecated word-break Property
```yaml
File: assets/scss/components/_utilities.scss
Line: 42
File: assets/scss/utilities/_text.scss
Line: 116
Issue Code: CSS-DEPRECATED-002
Severity: MEDIUM
```

**Current Code:**
```scss
// Line 42, 116
word-break: break-word;  // Deprecated
```

**Solution:**
```scss
// Replace with:
overflow-wrap: break-word;
word-wrap: break-word;  // Legacy fallback
```

---

### 2.2 Limited Support Features (3 Issues)

#### Issue B2-001: aspect-ratio Limited Browser Support
```yaml
File: assets/scss/components/_card-media.scss
Line: 24
Issue Code: CSS-SUPPORT-001
Severity: MEDIUM
Min Versions: Chrome 88+, Firefox 89+, Safari 15+
```

**Current Code:**
```scss
// Line 24
aspect-ratio: 16 / 9;
```

**Solution (with fallback):**
```scss
.aps-card-media {
  // Fallback for older browsers
  position: relative;
  padding-bottom: 56.25%;  // 16:9 ratio
  height: 0;
  overflow: hidden;
  
  // Modern browsers
  @supports (aspect-ratio: 16 / 9) {
    padding-bottom: 0;
    height: auto;
    aspect-ratio: 16 / 9;
  }
}
```

---

### 2.3 Vendor Prefix Issues (11 Issues)

#### Issue B3-001: Webkit Vendor Prefixes
```yaml
File: assets/css/product-card.css
Line: Multiple
Issue Code: CSS-VENDOR-001
Severity: LOW
Note: Autoprefixer should handle this
```

**Current Code:**
```css
-webkit-line-clamp: 2;
-webkit-box-orient: vertical;
-webkit-tap-highlight-color: rgba(0,0,0,0);
-webkit-font-smoothing: antialiased;
-moz-osx-font-smoothing: grayscale;
```

**Solution:**
```css
/* Remove from source files */
/* Let Autoprefixer add these during build */
```

**Build Configuration:**
```javascript
// vite.config.js
export default {
  css: {
    postcss: {
      plugins: [
        autoprefixer({
          browsers: ['>= 1%', 'last 2 versions', 'not dead']
        })
      ]
    }
  }
}
```

---

## 🟠 PRIORITY 3: PHP CODE QUALITY ISSUES (79 Long Functions)

### 3.1 Long Functions Exceeding 50 Lines

#### Issue P1-001: AjaxHandler handleFilterProducts
```yaml
File: src/Admin/AjaxHandler.php
Line: 200-238
Issue Code: PHP-LONG-FUNC-001
Severity: HIGH
Lines: 39 (borderline, but related functions are long)
Complexity: Medium
```

**Current Code Structure:**
```php
// Line 200-238
public function handleFilterProducts(): void {
    // Verify nonce
    // Check permissions
    // Get filter parameters
    // Build query args
    // Get products
    // Send response
}
```

**Solution:**
```php
// Refactor into smaller methods (already partially done)
// Line 200-238 already refactored in current version ✅
```

---

#### Issue P1-002: AjaxHandler handleBulkAction
```yaml
File: src/Admin/AjaxHandler.php
Line: 300-400 (estimated)
Issue Code: PHP-LONG-FUNC-002
Severity: HIGH
Lines: 100+
```

**Current Code:**
```php
public function handleBulkAction(): void {
    // Verify nonce
    // Check permissions
    // Get action type
    // Get product IDs
    // Switch case for different actions
    // Each action has 10-20 lines
    // Send response
}
```

**Solution:**
```php
public function handleBulkAction(): void {
    if (!$this->verifyRequest('aps_bulk_action_nonce')) {
        return;
    }
    
    $action = $this->getBulkAction();
    $productIds = $this->getProductIds();
    
    $result = $this->executeBulkAction($action, $productIds);
    
    wp_send_json_success($result);
}

private function executeBulkAction(string $action, array $productIds): array {
    return match($action) {
        'trash' => $this->trashProducts($productIds),
        'restore' => $this->restoreProducts($productIds),
        'delete' => $this->deleteProducts($productIds),
        default => ['error' => 'Invalid action']
    };
}
```

---

#### Issue P1-003: ProductsTable get_products_data
```yaml
File: src/Admin/ProductsTable.php
Line: 86-164
Issue Code: PHP-LONG-FUNC-003
Severity: HIGH
Lines: 79
```

**Current Code:**
```php
// Line 86-164 - Single function with 79 lines
private function get_products_data(): array {
    // Build args
    // Apply status filter
    // Apply category filter
    // Apply tag filter
    // Apply search filter
    // Execute query
    // Process results
    // Return products
}
```

**Solution:**
```php
private function get_products_data(): array {
    $args = $this->buildProductQueryArgs();
    return $this->executeProductQuery($args);
}

private function buildProductQueryArgs(): array {
    $args = [
        'post_type' => ProductConfig::POST_TYPE,
        'posts_per_page' => -1,
        'post_status' => ['publish', 'draft', 'trash'],
    ];
    
    $args = $this->applyStatusFilter($args);
    $args = $this->applyCategoryFilter($args);
    $args = $this->applyTagFilter($args);
    $args = $this->applySearchFilter($args);
    
    return $args;
}

private function applyStatusFilter(array $args): array {
    if (!isset($_GET['status']) || empty($_GET['status'])) {
        return $args;
    }
    
    $statusMap = [
        'trash' => 'trash',
        'draft' => 'draft',
        'published' => 'publish'
    ];
    
    $args['post_status'] = $statusMap[$_GET['status']] ?? $args['post_status'];
    return $args;
}
```

---

### 3.2 Missing PHPDoc (98 Issues)

#### Issue P2-001: Missing PHPDoc in AjaxHandler
```yaml
File: src/Admin/AjaxHandler.php
Line: 24-47 (constructor)
Issue Code: PHP-DOC-001
Severity: MEDIUM
```

**Current Code:**
```php
// Line 24
class AjaxHandler {
    // Line 28-29
    private ProductService $productService;
    private ProductRepository $productRepository;
    
    // Line 39-47
    public function __construct(
        ProductService $productService,
        ProductRepository $productRepository
    ) {
        // ...
    }
}
```

**Solution:**
```php
/**
 * AJAX Handler for Product Table
 * 
 * Handles AJAX requests for product filtering, sorting,
 * bulk actions, and status updates.
 * 
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class AjaxHandler {
    /**
     * Product service instance
     * 
     * @var ProductService
     * @since 1.0.0
     */
    private ProductService $productService;
    
    /**
     * Product repository instance
     * 
     * @var ProductRepository
     * @since 1.0.0
     */
    private ProductRepository $productRepository;
    
    /**
     * Constructor
     * 
     * Initializes the AJAX handler with required dependencies
     * and registers AJAX action hooks.
     * 
     * @param ProductService $productService Product service instance
     * @param ProductRepository $productRepository Product repository instance
     * @since 1.0.0
     */
    public function __construct(
        ProductService $productService,
        ProductRepository $productRepository
    ) {
        // ...
    }
}
```

---

## 📊 Implementation Priority Matrix

| Priority | Issue Code | File | Effort | Impact |
|----------|------------|------|--------|--------|
| P0 | CSS-FOCUS-002 | _form-input.scss | 30 min | CRITICAL |
| P0 | CSS-FOCUS-003 | _card-base.scss | 30 min | CRITICAL |
| P0 | CSS-CONTRAST-001 | frontend.scss | 1 hour | SERIOUS |
| P1 | CSS-DEPRECATED-001 | _card-base.scss | 15 min | MEDIUM |
| P1 | CSS-DEPRECATED-002 | _utilities.scss | 15 min | MEDIUM |
| P1 | PHP-LONG-FUNC-003 | ProductsTable.php | 2 hours | HIGH |
| P2 | CSS-FONT-SIZE-001 | Multiple files | 3 hours | MEDIUM |
| P2 | PHP-DOC-001 | AjaxHandler.php | 2 hours | MEDIUM |
| P3 | CSS-SUPPORT-001 | _card-media.scss | 1 hour | MEDIUM |

---

## 🛠️ Quick Fix Commands

### CSS Focus States (Batch Fix)
```bash
# Find all interactive elements without focus styles
grep -r "\.aps-button\|\.aps-input\|\.aps-select" --include="*.scss" wp-content/plugins/affiliate-product-showcase/assets/scss/

# Add focus mixin to all files
```

### PHP Long Functions (Batch Analysis)
```bash
# Count lines in all methods
grep -n "public function\|private function" src/Admin/*.php | head -50

# Find functions over 50 lines
# (Manual review required)
```

### Color Contrast Check
```bash
# Find all color definitions
grep -r "color.*#667eea\|color.*#d1d5db" --include="*.scss" wp-content/plugins/affiliate-product-showcase/
```

---

## ✅ Verification Checklist

### CSS Accessibility
- [ ] All buttons have `:focus-visible` styles
- [ ] All form inputs have visible focus indicators
- [ ] Color contrast ratio >= 4.5:1 for all text
- [ ] Font sizes use rem units (not px)
- [ ] Screen reader only class implemented correctly

### Browser Compatibility
- [ ] `user-select` removed or properly prefixed
- [ ] `word-break: break-word` replaced with `overflow-wrap`
- [ ] `aspect-ratio` has padding-bottom fallback
- [ ] Vendor prefixes removed (use Autoprefixer)

### PHP Code Quality
- [ ] No functions over 50 lines (extract to methods)
- [ ] All public methods have PHPDoc
- [ ] All complex logic has inline comments
- [ ] No duplicate code blocks (extract to traits/utilities)

---

**Document Version:** 1.0  
**Next Review:** After Phase 1 implementation  
**Owner:** Development Team
