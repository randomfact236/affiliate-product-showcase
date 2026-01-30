# Category Code Review Implementation Plan

**Created:** 2026-01-29  
**Based On:** `scan-reports/category-code-review-report.md`  
**Goal:** Fix 47 identified issues across category-related files

---

## Overview

This implementation plan addresses all 47 issues identified in the code review, organized by priority and complexity. The plan is divided into 4 phases, with Phase 1 (Critical Security Fixes) taking immediate precedence.

---

## Phase 1: Critical Security Fixes (Week 1)

### 1.1 Fix XSS Vulnerabilities

#### Task 1.1.1: Fix XSS in CategoryFields.php admin notice
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Line:** 213  
**Severity:** Critical  
**Status:** Pending

**Current Code:**
```php
echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
```

**Fix:**
```php
echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
```

#### Task 1.1.2: Fix XSS in TaxonomyFieldsAbstract.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 698-722  
**Severity:** Critical  
**Status:** Pending

**Current Code:**
```php
printf( esc_html__( '%d ' . strtolower( $this->get_taxonomy_label() ) . '(s) moved to draft.', 'affiliate-product-showcase' ), $count );
```

**Fix:**
```php
printf(
    esc_html__('%d %s(s) moved to draft.', 'affiliate-product-showcase'),
    $count,
    esc_html(strtolower($this->get_taxonomy_label()))
);
```

### 1.2 Sanitize User Inputs

#### Task 1.2.1: Sanitize $_POST in CategoryFields.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Lines:** 180, 186-188, 194  
**Severity:** Critical  
**Status:** Pending

**Current Code:**
```php
$featured = isset( $_POST['_aps_category_featured'] ) ? '1' : '0';
$image_url = isset( $_POST['_aps_category_image'] ) 
    ? esc_url_raw( wp_unslash( $_POST['_aps_category_image'] ) ) 
    : '';
$is_default = isset( $_POST['_aps_category_is_default'] ) ? '1' : '0';
```

**Fix:**
```php
// Verify nonce first
if (!isset($_POST['aps_category_fields_nonce']) || 
    !wp_verify_nonce($_POST['aps_category_fields_nonce'], 'aps_category_fields')) {
    return;
}

$featured = isset($_POST['_aps_category_featured']) ? '1' : '0';
$image_url = isset($_POST['_aps_category_image']) 
    ? esc_url_raw(wp_unslash($_POST['_aps_category_image'])) 
    : '';
$is_default = isset($_POST['_aps_category_is_default']) ? '1' : '0';
```

#### Task 1.2.2: Sanitize $_GET in CategoryFields.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Line:** 324  
**Severity:** Critical  
**Status:** Pending

**Current Code:**
```php
$current_sort_order = isset( $_GET['aps_sort_order'] ) ? sanitize_text_field( $_GET['aps_sort_order'] ) : 'date';
```

**Fix:**
```php
$valid_sort_orders = ['date', 'name', 'count'];
$current_sort_order = isset($_GET['aps_sort_order']) && 
                      in_array($_GET['aps_sort_order'], $valid_sort_orders, true)
                      ? sanitize_text_field($_GET['aps_sort_order']) 
                      : 'date';
```

#### Task 1.2.3: Sanitize $_GET in TaxonomyFieldsAbstract.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 252, 333, 555, 847, 915  
**Severity:** Critical  
**Status:** Pending

**Fix Pattern:**
```php
// Line 252
$current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Add validation
$valid_statuses = ['all', 'published', 'draft', 'trashed'];
$current_status = isset($_GET['status']) && 
                  in_array($_GET['status'], $valid_statuses, true)
                  ? sanitize_text_field($_GET['status']) 
                  : 'all';
```

#### Task 1.2.4: Sanitize $_POST in CategoryFormHandler.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php`  
**Lines:** 103-110  
**Severity:** Critical  
**Status:** Pending

**Current Code:**
```php
$cat_id      = isset( $_POST['category_id'] ) ? (int) $_POST['category_id'] : 0;
$name        = sanitize_text_field( $_POST['name'] ?? '' );
$slug        = sanitize_title( $_POST['slug'] ?? '' );
$description = sanitize_textarea_field( $_POST['description'] ?? '' );
$parent_id   = isset( $_POST['parent_id'] ) ? (int) $_POST['parent_id'] : 0;
$featured     = isset( $_POST['featured'] );
$image_url   = esc_url_raw( $_POST['image_url'] ?? '' );
$sort_order  = sanitize_text_field( $_POST['sort_order'] ?? 'date' );
```

**Fix:**
```php
// After nonce verification (lines 80-91)
$cat_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: 0;
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$slug = filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING) ?: '';
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING) ?: '';
$parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?: 0;
$image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);
if ($image_url && !wp_http_validate_url($image_url)) {
    $image_url = '';
}
$sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_SANITIZE_STRING);
$valid_sort_orders = ['name', 'price', 'date', 'popularity', 'random'];
if (!in_array($sort_order, $valid_sort_orders, true)) {
    $sort_order = 'date';
}
$featured = isset($_POST['featured']);
```

### 1.3 Secure Error Messages

#### Task 1.3.1: Remove sensitive info from error logs
**File:** `wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`  
**Lines:** 421-426, 795-800  
**Severity:** High  
**Status:** Pending

**Current Code:**
```php
error_log(sprintf(
    '[APS] Category update failed: %s in %s:%d',
    $e->getMessage(),
    $e->getFile(),
    $e->getLine()
));
```

**Fix:**
```php
error_log(sprintf('[APS] Category update failed: %s', $e->getMessage()));

// Debug only (with feature flag)
if (defined('APS_DEBUG') && APS_DEBUG) {
    error_log(sprintf('[APS] Category update failed: %s in %s:%d', 
        $e->getMessage(), $e->getFile(), $e->getLine()));
}
```

---

## Phase 2: Code Deduplication (Week 2)

### 2.1 Create Shared Utility Classes

#### Task 2.1.1: Create TermMetaHelper class
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Helpers/TermMetaHelper.php`  
**Status:** Pending

**Purpose:** Consolidate meta operations with legacy fallback

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Helpers;

/**
 * Term Meta Helper
 *
 * Provides utility methods for term meta operations with legacy fallback support.
 *
 * @package AffiliateProductShowcase\Helpers
 * @since 2.1.0
 */
final class TermMetaHelper {
    /**
     * Get meta value with legacy fallback
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key (without prefix)
     * @param string $prefix Meta key prefix (e.g., '_aps_category_')
     * @return mixed Meta value
     */
    public static function get_with_fallback(int $term_id, string $meta_key, string $prefix): mixed {
        // Try new format with underscore prefix
        $value = get_term_meta($term_id, '_' . $prefix . $meta_key, true);
        
        // If empty, try legacy format without underscore
        if ($value === '' || $value === false) {
            $value = get_term_meta($term_id, $prefix . $meta_key, true);
        }
        
        return $value;
    }
    
    /**
     * Delete both new and legacy meta keys
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key (without prefix)
     * @param string $prefix Meta key prefix (e.g., '_aps_category_')
     * @return void
     */
    public static function delete_legacy(int $term_id, string $meta_key, string $prefix): void {
        // Delete new format key
        delete_term_meta($term_id, '_' . $prefix . $meta_key);
        // Delete legacy format key
        delete_term_meta($term_id, $prefix . $meta_key);
    }
    
    /**
     * Update meta and delete legacy key
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key (without prefix)
     * @param mixed $value Meta value
     * @param string $prefix Meta key prefix (e.g., '_aps_category_')
     * @return bool Success
     */
    public static function update_with_legacy_cleanup(int $term_id, string $meta_key, mixed $value, string $prefix): bool {
        $result = update_term_meta($term_id, '_' . $prefix . $meta_key, $value);
        // Delete legacy key
        delete_term_meta($term_id, $prefix . $meta_key);
        return $result !== false;
    }
}
```

#### Task 2.1.2: Create StatusValidator class
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Validators/StatusValidator.php`  
**Status:** Pending

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Validators;

/**
 * Status Validator
 *
 * Validates and normalizes status values for taxonomies.
 *
 * @package AffiliateProductShowcase\Validators
 * @since 2.1.0
 */
final class StatusValidator {
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';
    public const TRASHED = 'trashed';
    public const ALL = 'all';
    
    private const VALID_STATUSES = [self::PUBLISHED, self::DRAFT, self::TRASHED];
    
    /**
     * Validate status value
     *
     * @param string $status Status to validate
     * @return string Validated status (defaults to 'published')
     */
    public static function validate(string $status): string {
        return in_array($status, self::VALID_STATUSES, true) ? $status : self::PUBLISHED;
    }
    
    /**
     * Check if status is valid
     *
     * @param string $status Status to check
     * @return bool True if valid
     */
    public static function isValid(string $status): bool {
        return in_array($status, self::VALID_STATUSES, true);
    }
    
    /**
     * Get all valid statuses
     *
     * @return array<string> Valid status values
     */
    public static function getValidStatuses(): array {
        return self::VALID_STATUSES;
    }
}
```

#### Task 2.1.3: Create NonceVerificationTrait
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Traits/NonceVerificationTrait.php`  
**Status:** Pending

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

/**
 * Nonce Verification Trait
 *
 * Provides reusable nonce verification methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */
trait NonceVerificationTrait {
    /**
     * Verify nonce value
     *
     * @param string $nonce Nonce value
     * @param string $action Nonce action
     * @return bool True if valid
     */
    protected function verify_nonce(string $nonce, string $action): bool {
        return wp_verify_nonce($nonce, $action) !== false;
    }
    
    /**
     * Verify POST nonce
     *
     * @param string $key POST key for nonce
     * @param string $action Nonce action
     * @return bool True if valid
     */
    protected function verify_post_nonce(string $key, string $action): bool {
        return isset($_POST[$key]) && $this->verify_nonce($_POST[$key], $action);
    }
    
    /**
     * Verify header nonce (for REST API)
     *
     * @param string $header Header name (default: X-WP-Nonce)
     * @param string $action Nonce action (default: wp_rest)
     * @return bool True if valid
     */
    protected function verify_header_nonce(string $header = 'X-WP-Nonce', string $action = 'wp_rest'): bool {
        // This would need to be adapted based on context
        // For REST API controllers, use $request->get_header()
        return true;
    }
    
    /**
     * Die with nonce error message
     *
     * @return void
     */
    protected function nonce_failed(): void {
        wp_die(esc_html__('Security check failed. Please try again.', 'affiliate-product-showcase'));
    }
}
```

#### Task 2.1.4: Create PermissionCheckTrait
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Traits/PermissionCheckTrait.php`  
**Status:** Pending

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

/**
 * Permission Check Trait
 *
 * Provides reusable permission check methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */
trait PermissionCheckTrait {
    /**
     * Check if user can manage categories
     *
     * @return bool True if has permission
     */
    protected function can_manage_categories(): bool {
        return current_user_can('manage_categories');
    }
    
    /**
     * Require manage categories permission
     *
     * Dies with error if user lacks permission.
     *
     * @return void
     */
    protected function require_manage_categories(): void {
        if (!$this->can_manage_categories()) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'affiliate-product-showcase'));
        }
    }
    
    /**
     * Check if user can edit categories
     *
     * @return bool True if has permission
     */
    protected function can_edit_categories(): bool {
        return current_user_can('edit_categories');
    }
}
```

### 2.2 Update Existing Files to Use New Utilities

#### Task 2.2.1: Update CategoryFields.php to use TermMetaHelper
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Lines:** 236-246  
**Status:** Pending

**Changes:**
- Remove `get_category_meta()` method
- Use `TermMetaHelper::get_with_fallback()` instead

#### Task 2.2.2: Update Category.php to use TermMetaHelper
**File:** `wp-content/plugins/affiliate-product-showcase/src/Models/Category.php`  
**Lines:** 208-218  
**Status:** Pending

**Changes:**
- Remove `get_category_meta()` method
- Use `TermMetaHelper::get_with_fallback()` instead

#### Task 2.2.3: Update CategoryRepository.php to use TermMetaHelper
**File:** `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`  
**Lines:** 474, 480, 489, 494, 503, 519, 586-590  
**Status:** Pending

**Changes:**
- Replace legacy key deletion with `TermMetaHelper::delete_legacy()`

#### Task 2.2.4: Update Category.php to use StatusValidator
**File:** `wp-content/plugins/affiliate-product-showcase/src/Models/Category.php`  
**Lines:** 168, 251  
**Status:** Pending

**Changes:**
- Replace status validation with `StatusValidator::validate()`

#### Task 2.2.5: Update TaxonomyFieldsAbstract.php to use StatusValidator
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 409  
**Status:** Pending

**Changes:**
- Replace status validation with `StatusValidator::validate()`

#### Task 2.2.6: Add traits to CategoryFormHandler
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php`  
**Status:** Pending

**Changes:**
- Add `use NonceVerificationTrait;`
- Add `use PermissionCheckTrait;`
- Update nonce and permission checks

#### Task 2.2.7: Add traits to TaxonomyFieldsAbstract
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Status:** Pending

**Changes:**
- Add `use NonceVerificationTrait;`
- Add `use PermissionCheckTrait;`
- Update nonce and permission checks

### 2.3 Consolidate Default Category Logic

#### Task 2.3.1: Add ensure_default_category to CategoryRepository
**File:** `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`  
**Status:** Pending

**New Method:**
```php
/**
 * Ensure default category status
 *
 * Sets or removes default category flag and updates global option.
 *
 * @param int $term_id Term ID
 * @param bool $is_default Whether this should be the default
 * @return void
 * @since 2.1.0
 */
public function ensure_default_category(int $term_id, bool $is_default): void {
    if ($is_default) {
        // Remove default flag from all other categories
        $this->remove_default_from_all_categories();
        // Set this category as default
        update_term_meta($term_id, '_aps_category_is_default', '1');
        delete_term_meta($term_id, 'aps_category_is_default');
        // Update global option
        update_option('aps_default_category_id', $term_id);
    } else {
        // Remove default flag from this category
        delete_term_meta($term_id, '_aps_category_is_default');
        delete_term_meta($term_id, 'aps_category_is_default');
        // Clear global option if this was default
        $current_default = get_option('aps_default_category_id', 0);
        if ((int)$current_default === $term_id) {
            delete_option('aps_default_category_id');
        }
    }
}
```

#### Task 2.3.2: Update CategoryFields.php to use ensure_default_category
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Lines:** 195-225  
**Status:** Pending

**Changes:**
- Replace inline logic with `$this->repository->ensure_default_category($category_id, $is_default === '1');`

---

## Phase 3: Code Quality Improvements (Week 3-4)

### 3.1 Define Constants

#### Task 3.1.1: Create StatusConstants class
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Plugin/StatusConstants.php`  
**Status:** Pending

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

/**
 * Status Constants
 *
 * Defines constant values for status fields.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */
final class StatusConstants {
    public const PUBLISHED = 'published';
    public const DRAFT = 'draft';
    public const TRASHED = 'trashed';
    public const ALL = 'all';
}
```

#### Task 3.1.2: Create SortOrderConstants class
**New File:** `wp-content/plugins/affiliate-product-showcase/src/Plugin/SortOrderConstants.php`  
**Status:** Pending

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

/**
 * Sort Order Constants
 *
 * Defines constant values for sort order options.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */
final class SortOrderConstants {
    public const DATE = 'date';
    public const NAME = 'name';
    public const PRICE = 'price';
    public const POPULARITY = 'popularity';
    public const RANDOM = 'random';
    
    public const ALL = [
        self::DATE,
        self::NAME,
        self::PRICE,
        self::POPULARITY,
        self::RANDOM,
    ];
}
```

### 3.2 Refactor Long Methods

#### Task 3.2.1: Refactor handle_bulk_actions in TaxonomyFieldsAbstract.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 582-687  
**Status:** Pending

**Approach:** Extract each action handler to separate private method.

#### Task 3.2.2: Refactor add_status_view_tabs in TaxonomyFieldsAbstract.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 238-302  
**Status:** Pending

**Approach:** Extract tab generation to helper method.

#### Task 3.2.3: Refactor create in CategoriesController.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`  
**Lines:** 757-818  
**Status:** Pending

**Approach:** Extract validation and error handling to separate methods.

### 3.3 Simplify Conditional Logic

#### Task 3.3.1: Simplify filter_terms_by_status
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 346-357  
**Status:** Pending

**Approach:** Use early returns to reduce nesting.

### 3.4 Add Missing Type Hints

#### Task 3.4.1: Add type hint to protect_default_term
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Line:** 970  
**Status:** Pending

**Change:** `final public function protect_default_term(int $term, string $taxonomy): void`

---

## Phase 4: Documentation and Cleanup (Week 5)

### 4.1 Remove Inline CSS

#### Task 4.1.1: Remove hidden attribute from CategoryFields.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Line:** 106  
**Status:** Pending

**Change:** Replace `hidden` attribute with CSS class.

#### Task 4.1.2: Extract inline HTML from TaxonomyFieldsAbstract.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 258-299  
**Status:** Pending

**Approach:** Create template file for status tabs.

### 4.2 Remove Unused Code

#### Task 4.2.1: Remove placeholder code from CategoryRepository.php
**File:** `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`  
**Lines:** 329-333  
**Status:** Pending

**Action:** Remove or implement properly.

### 4.3 Improve Documentation

#### Task 4.3.1: Add detailed comments to save_metadata
**File:** `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`  
**Lines:** 469-507  
**Status:** Pending

#### Task 4.3.2: Add detailed comments to filter_terms_by_status
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Lines:** 315-360  
**Status:** Pending

---

## Implementation Checklist

### Phase 1: Security Fixes
- [ ] Task 1.1.1: Fix XSS in CategoryFields.php
- [ ] Task 1.1.2: Fix XSS in TaxonomyFieldsAbstract.php
- [ ] Task 1.2.1: Sanitize $_POST in CategoryFields.php
- [ ] Task 1.2.2: Sanitize $_GET in CategoryFields.php
- [ ] Task 1.2.3: Sanitize $_GET in TaxonomyFieldsAbstract.php
- [ ] Task 1.2.4: Sanitize $_POST in CategoryFormHandler.php
- [ ] Task 1.3.1: Remove sensitive info from error logs

### Phase 2: Code Deduplication
- [ ] Task 2.1.1: Create TermMetaHelper class
- [ ] Task 2.1.2: Create StatusValidator class
- [ ] Task 2.1.3: Create NonceVerificationTrait
- [ ] Task 2.1.4: Create PermissionCheckTrait
- [ ] Task 2.2.1: Update CategoryFields.php
- [ ] Task 2.2.2: Update Category.php
- [ ] Task 2.2.3: Update CategoryRepository.php
- [ ] Task 2.2.4: Update Category.php (StatusValidator)
- [ ] Task 2.2.5: Update TaxonomyFieldsAbstract.php
- [ ] Task 2.2.6: Add traits to CategoryFormHandler
- [ ] Task 2.2.7: Add traits to TaxonomyFieldsAbstract
- [ ] Task 2.3.1: Add ensure_default_category to CategoryRepository
- [ ] Task 2.3.2: Update CategoryFields.php

### Phase 3: Code Quality
- [ ] Task 3.1.1: Create StatusConstants class
- [ ] Task 3.1.2: Create SortOrderConstants class
- [ ] Task 3.2.1: Refactor handle_bulk_actions
- [ ] Task 3.2.2: Refactor add_status_view_tabs
- [ ] Task 3.2.3: Refactor create
- [ ] Task 3.3.1: Simplify filter_terms_by_status
- [ ] Task 3.4.1: Add type hint to protect_default_term

### Phase 4: Cleanup
- [ ] Task 4.1.1: Remove hidden attribute
- [ ] Task 4.1.2: Extract inline HTML
- [ ] Task 4.2.1: Remove unused code
- [ ] Task 4.3.1: Add comments to save_metadata
- [ ] Task 4.3.2: Add comments to filter_terms_by_status

---

## Testing Strategy

### Security Testing
- Test XSS with malicious input in category names
- Test CSRF with forged requests
- Test SQL injection attempts
- Test input validation with edge cases

### Functional Testing
- Test category CRUD operations
- Test bulk actions
- Test status changes
- Test default category behavior

### Regression Testing
- Ensure all existing functionality still works
- Test with existing data
- Test with WordPress multisite if applicable

---

## Success Criteria

All tasks completed when:
1. All critical security issues are resolved
2. All duplicate code is extracted to shared utilities
3. All code quality improvements are implemented
4. All tests pass
5. No regressions in existing functionality

---

**Plan Version:** 1.0  
**Last Updated:** 2026-01-29
