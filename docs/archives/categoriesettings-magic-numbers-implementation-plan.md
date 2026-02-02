# CategoriesSettings.php - Magic Numbers Refactoring Implementation Plan

**Date:** January 31, 2026  
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Settings/CategoriesSettings.php`  
**Issue:** Hard-coded Magic Numbers and Strings (Issue #1 from category-code-review-verified-report.md)

## Status: ✅ Partially Fixed - Requires Additional Refinement

## Issues Identified

### Issue 1: Incorrect Validation Logic (Lines 200, 214) ❌
**Problem:**
```php
// Current implementation - confusing and inefficient
$sanitized['category_products_per_page'] = max(min(self::PRODUCTS_PER_PAGE_OPTIONS), min(max(self::PRODUCTS_PER_PAGE_OPTIONS), $sanitized['category_products_per_page']));
```

**Why It's Wrong:**
- `min()` and `max()` on array constants is confusing
- Doesn't validate against allowed options
- Difficult to maintain and understand
- Clamps to range but doesn't ensure value is in the allowed set

**Solution:**
```php
// Validate that value exists in allowed options, fallback to default
if (!in_array($sanitized['category_products_per_page'], self::PRODUCTS_PER_PAGE_OPTIONS, true)) {
    $sanitized['category_products_per_page'] = self::DEFAULT_PRODUCTS_PER_PAGE;
}
```

**Location:** Lines 200 and 214

---

### Issue 2: Duplicate Arrays in Render Methods ❌
**Problem:**
Render methods contain hardcoded arrays that duplicate constant values:

1. **render_category_display_style_field** (Lines 287-292)
   ```php
   $styles = [
       'grid' => __('Grid', 'affiliate-product-showcase'),
       'list' => __('List', 'affiliate-product-showcase'),
       'compact' => __('Compact', 'affiliate-product-showcase'),
   ];
   ```

2. **render_category_default_sort_field** (Lines 317-323)
   ```php
   $options = [
       'name' => __('Name', 'affiliate-product-showcase'),
       'price' => __('Price', 'affiliate-product-showcase'),
       // ... etc
   ];
   ```

3. **render_category_default_sort_order_field** (Lines 342-345)
   ```php
   $options = [
       'ASC' => __('Ascending', 'affiliate-product-showcase'),
       'DESC' => __('Descending', 'affiliate-product-showcase'),
   ];
   ```

**Why It's Wrong:**
- Violates DRY principle
- Values duplicated between constants and render methods
- If options change, must update in multiple places
- Potential for inconsistency

**Solution:**
Create label constants or build labels from existing constants:

```php
// Add to class constants section
const DISPLAY_STYLE_LABELS = [
    'grid' => 'Grid',
    'list' => 'List',
    'compact' => 'Compact',
];

const SORT_OPTION_LABELS = [
    'name' => 'Name',
    'price' => 'Price',
    'date' => 'Date',
    'popularity' => 'Popularity',
    'random' => 'Random',
];

const SORT_ORDER_LABELS = [
    'ASC' => 'Ascending',
    'DESC' => 'Descending',
];
```

**Location:** Lines 287-292, 317-323, 342-345

---

## Already Fixed ✅

### ✅ Issue #1 from Review Report: WP_Error Handling (Lines 207-209)
**Before:**
```php
$categories = get_terms(['taxonomy' => 'aps_category', 'hide_empty' => false]);
// No error handling - could crash if WP_Error returned
```

**After:**
```php
$categories = get_terms(['taxonomy' => 'aps_category', 'hide_empty' => false]);

if (is_wp_error($categories)) {
    $categories = [];
}
```
**Status:** ✅ Correctly implemented

### ✅ Constants Added for Default Values
**Implementation:**
```php
const DEFAULT_CATEGORY_NONE = 0;
const DEFAULT_ENABLE_HIERARCHY = true;
const DEFAULT_DISPLAY_STYLE = 'grid';
// ... etc (all defaults properly defined)
```
**Status:** ✅ Correctly implemented

### ✅ Constants Added for Validation Options
**Implementation:**
```php
const PRODUCTS_PER_PAGE_OPTIONS = [6, 12, 18, 24, 36, 48];
const FEATURED_PRODUCTS_LIMIT_OPTIONS = [1, 2, 3, 4, 6, 8];
const DISPLAY_STYLES = ['grid', 'list', 'compact'];
// ... etc
```
**Status:** ✅ Correctly implemented

### ✅ get_defaults() Method Refactored
**Before:**
```php
return [
    'default_category' => 0,
    'category_products_per_page' => 12,
    // ...
];
```

**After:**
```php
return [
    'default_category' => self::DEFAULT_CATEGORY_NONE,
    'category_products_per_page' => self::DEFAULT_PRODUCTS_PER_PAGE,
    // ...
];
```
**Status:** ✅ Correctly implemented

---

## Implementation Steps

### Step 1: Fix Validation Logic ⏳
**File:** CategoriesSettings.php  
**Lines:** 200, 214

Replace complex min/max logic with proper validation:

```php
// For category_products_per_page (Line 200)
$sanitized['category_products_per_page'] = intval($input['category_products_per_page'] ?? self::DEFAULT_PRODUCTS_PER_PAGE);
if (!in_array($sanitized['category_products_per_page'], self::PRODUCTS_PER_PAGE_OPTIONS, true)) {
    $sanitized['category_products_per_page'] = self::DEFAULT_PRODUCTS_PER_PAGE;
}

// For category_featured_products_limit (Line 214)
$sanitized['category_featured_products_limit'] = intval($input['category_featured_products_limit'] ?? self::DEFAULT_FEATURED_LIMIT);
if (!in_array($sanitized['category_featured_products_limit'], self::FEATURED_PRODUCTS_LIMIT_OPTIONS, true)) {
    $sanitized['category_featured_products_limit'] = self::DEFAULT_FEATURED_LIMIT;
}
```

**Estimated Time:** 15 minutes

### Step 2: Add Label Constants ⏳
**File:** CategoriesSettings.php  
**Lines:** After line 38 (after existing constants)

Add label constants for internationalization:

```php
// Label mappings for render methods
const DISPLAY_STYLE_LABELS = [
    'grid' => 'Grid',
    'list' => 'List',
    'compact' => 'Compact',
];

const SORT_OPTION_LABELS = [
    'name' => 'Name',
    'price' => 'Price',
    'date' => 'Date',
    'popularity' => 'Popularity',
    'random' => 'Random',
];

const SORT_ORDER_LABELS = [
    'ASC' => 'Ascending',
    'DESC' => 'Descending',
];
```

**Estimated Time:** 10 minutes

### Step 3: Refactor Render Methods ⏳
**File:** CategoriesSettings.php  
**Lines:** 287-292, 317-323, 342-345

Update render methods to use constants:

```php
// render_category_display_style_field
foreach (self::DISPLAY_STYLES as $value) {
    $label = __(self::DISPLAY_STYLE_LABELS[$value], 'affiliate-product-showcase');
    $selected = selected($settings['category_display_style'], $value, false);
    echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
}

// Similar pattern for other methods
```

**Estimated Time:** 20 minutes

### Step 4: Verify Implementation ⏳
**Tasks:**
- Run syntax checks
- Verify no errors in VS Code
- Check that all constants are referenced correctly
- Ensure no duplicate arrays remain

**Estimated Time:** 10 minutes

---

## Total Estimated Effort
**Total Time:** ~55 minutes

---

## Success Criteria

✅ All validation logic uses proper `in_array()` checks  
✅ No duplicate arrays in render methods  
✅ All hardcoded strings replaced with constants  
✅ Code passes syntax validation  
✅ Maintains backward compatibility  
✅ Follows WordPress coding standards  

---

## Risk Assessment
**Risk Level:** Low

**Potential Issues:**
- None - changes are purely internal refactoring
- No API changes
- No database changes
- Constants maintain same values

**Mitigation:**
- Comprehensive testing of settings page
- Verify all dropdowns populate correctly
- Check validation behavior with invalid inputs

---

## Notes
- This refactoring improves maintainability without changing functionality
- All changes maintain backward compatibility
- Constants make it easier to add/modify options in the future
- Follows the principle: "Make the change easy, then make the easy change"
