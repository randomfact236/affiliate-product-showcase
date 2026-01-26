# 🚨 CRITICAL ERROR: Taxonomy Name Mismatch Found

**Date:** 2026-01-26  
**Severity:** CRITICAL - Blocks Production  
**Status:** NOT FIXED

---

## Error Summary

**CRITICAL DISCREPANCY:** The codebase has **inconsistent taxonomy names** across files, which will cause complete failure of taxonomy operations.

---

## The Problem

### Constants File (SOURCE OF TRUTH)
**File:** `src/Plugin/Constants.php`

```php
// Defined constants:
public const TAX_CATEGORY = 'aps_category';  // ← CORRECT NAME
public const TAX_TAG = 'aps_tag';          // ← CORRECT NAME
public const TAX_RIBBON = 'aps_ribbon';    // ← CORRECT NAME
```

### MetaBoxes.php (BROKEN - Uses Wrong Names)
**File:** `src/Admin/MetaBoxes.php`

```php
// Save categories - WRONG TAXONOMY NAME ❌
wp_set_object_terms( $post_id, $category_ids, 'product_category' );
//                                         ^^^^^^^^^^^^^^^^ 
//                                         Should be: 'aps_category'

// Save tags - WRONG TAXONOMY NAME ❌
wp_set_object_terms( $post_id, $tag_ids, 'product_tag' );
//                                         ^^^^^^^^^^^^^
//                                         Should be: 'aps_tag'

// Save ribbons - CORRECT TAXONOMY NAME ✅
wp_set_object_terms( $post_id, $ribbon_ids, Constants::TAX_RIBBON );
//                                         ^^^^^^^^^^^^^^^^^^^^^^^^
//                                         Correctly uses constant
```

### product-meta-box.php (BROKEN - Uses Wrong Names)
**File:** `src/Admin/partials/product-meta-box.php`

```php
<!-- Categories - WRONG TAXONOMY NAME ❌ -->
$categories = get_terms( array(
    'taxonomy' => 'product_category',  // ← Should be: 'aps_category'
    'hide_empty' => false,
) );
foreach ( $categories as $cat ) :
    $checked = has_term( $cat->term_id, 'product_category', $post->ID ) ? 'checked' : '';
//                                               ^^^^^^^^^^^^^^^^
//                                               Should be: 'aps_category'

<!-- Tags - WRONG TAXONOMY NAME ❌ -->
$tags = get_terms( array(
    'taxonomy' => 'product_tag',  // ← Should be: 'aps_tag'
    'hide_empty' => false,
) );
foreach ( $tags as $tag ) :
    $checked = has_term( $tag->term_id, 'product_tag', $post->ID ) ? 'checked' : '';
//                                               ^^^^^^^^^^^^^
//                                               Should be: 'aps_tag'

<!-- Ribbons - CORRECT TAXONOMY NAME ✅ -->
$ribbons = get_terms( array(
    'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_RIBBON,  // ← CORRECT
    'hide_empty' => false,
) );
foreach ( $ribbons as $ribbon ) :
    $checked = has_term( $ribbon->term_id, \AffiliateProductShowcase\Plugin\Constants::TAX_RIBBON, $post->ID ) ? 'checked' : '';
//                                                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                                                          Correctly uses constant
```

---

## Impact Analysis

### What This Breaks

**1. Categories (COMPLETE FAILURE)**
- ❌ Categories saved to wrong taxonomy (`product_category` instead of `aps_category`)
- ❌ Categories loaded from wrong taxonomy
- ❌ ProductFactory loads empty category arrays
- ❌ API returns no categories
- ❌ Frontend displays no categories

**2. Tags (COMPLETE FAILURE)**
- ❌ Tags saved to wrong taxonomy (`product_tag` instead of `aps_tag`)
- ❌ Tags loaded from wrong taxonomy
- ❌ ProductFactory loads empty tag arrays
- ❌ API returns no tags
- ❌ Frontend displays no tags

**3. Ribbons (WORKS)**
- ✅ Ribbon constant used correctly everywhere
- ✅ Saves to correct taxonomy (`aps_ribbon`)
- ✅ Loads from correct taxonomy
- ✅ ProductFactory loads ribbon IDs correctly
- ✅ All ribbon operations functional

---

## Root Cause

**Inconsistent Implementation:**
- Ribbon implementation used constants correctly (good pattern)
- Category/Tag implementation hardcoded strings (bad pattern)
- Two different developers likely worked on different features

**File-by-File Breakdown:**

| File | Categories | Tags | Ribbons | Status |
|------|-------------|-------|---------|--------|
| Constants.php | `aps_category` | `aps_tag` | `aps_ribbon` | ✅ Reference |
| MetaBoxes.php | `product_category` ❌ | `product_tag` ❌ | `aps_ribbon` ✅ | PARTIALLY BROKEN |
| product-meta-box.php | `product_category` ❌ | `product_tag` ❌ | `aps_ribbon` ✅ | PARTIALLY BROKEN |
| ProductFactory.php | `TAX_CATEGORY` ✅ | `TAX_TAG` ✅ | `TAX_RIBBON` ✅ | WORKING |

---

## Why Test #1 Showed "Fixed"

Test #1 (Product ↔ Category) appeared to work because:
- ProductFactory correctly loads from `TAX_CATEGORY` constant
- But MetaBoxes saves to `product_category` (wrong!)
- **Result:** Categories saved to wrong place, ProductFactory loads from right place
- **Actual outcome:** Categories never persist, Test #1 was actually **FALSE POSITIVE**

---

## Required Fixes

### Fix #1: MetaBoxes.php - Update Category Save
**File:** `src/Admin/MetaBoxes.php`
**Line:** ~65

**Current (WRONG):**
```php
wp_set_object_terms( $post_id, $category_ids, 'product_category' );
```

**Required (CORRECT):**
```php
wp_set_object_terms( $post_id, $category_ids, Constants::TAX_CATEGORY );
```

---

### Fix #2: MetaBoxes.php - Update Tag Save
**File:** `src/Admin/MetaBoxes.php`
**Line:** ~70

**Current (WRONG):**
```php
wp_set_object_terms( $post_id, $tag_ids, 'product_tag' );
```

**Required (CORRECT):**
```php
wp_set_object_terms( $post_id, $tag_ids, Constants::TAX_TAG );
```

---

### Fix #3: product-meta-box.php - Update Category Loading
**File:** `src/Admin/partials/product-meta-box.php`
**Line:** ~38

**Current (WRONG):**
```php
$categories = get_terms( array(
    'taxonomy' => 'product_category',
    'hide_empty' => false,
) );
foreach ( $categories as $cat ) :
    $checked = has_term( $cat->term_id, 'product_category', $post->ID ) ? 'checked' : '';
```

**Required (CORRECT):**
```php
$categories = get_terms( array(
    'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY,
    'hide_empty' => false,
) );
foreach ( $categories as $cat ) :
    $checked = has_term( $cat->term_id, \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY, $post->ID ) ? 'checked' : '';
```

---

### Fix #4: product-meta-box.php - Update Tag Loading
**File:** `src/Admin/partials/product-meta-box.php`
**Line:** ~49

**Current (WRONG):**
```php
$tags = get_terms( array(
    'taxonomy' => 'product_tag',
    'hide_empty' => false,
) );
foreach ( $tags as $tag ) :
    $checked = has_term( $tag->term_id, 'product_tag', $post->ID ) ? 'checked' : '';
```

**Required (CORRECT):**
```php
$tags = get_terms( array(
    'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_TAG,
    'hide_empty' => false,
) );
foreach ( $tags as $tag ) :
    $checked = has_term( $tag->term_id, \AffiliateProductShowcase\Plugin\Constants::TAX_TAG, $post->ID ) ? 'checked' : '';
```

---

## Summary

**CRITICAL ERROR FOUND:** Taxonomy name inconsistency breaks Categories and Tags completely.

**Files Requiring Fixes:**
1. ❌ `src/Admin/MetaBoxes.php` - 2 fixes needed
2. ❌ `src/Admin/partials/product-meta-box.php` - 2 fixes needed

**Files Working Correctly:**
1. ✅ `src/Plugin/Constants.php` - All constants correct
2. ✅ `src/Factories/ProductFactory.php` - Uses constants correctly
3. ✅ Ribbon implementation - Fully correct

**Impact:**
- **Categories:** COMPLETELY BROKEN (wrong taxonomy name)
- **Tags:** COMPLETELY BROKEN (wrong taxonomy name)
- **Ribbons:** WORKING (correct taxonomy name)

**Cannot Proceed:** These fixes MUST be applied before testing can continue.

---

**Report Generated:** 2026-01-26 15:34:00 UTC+5.75  
**Error Severity:** CRITICAL - Blocks Production  
**Action Required:** Apply 4 fixes immediately