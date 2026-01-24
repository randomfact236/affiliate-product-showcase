# Meta Key Prefix Verification Report

## User Feedback Request

**Request:** Check what meta key prefix is actually used in CategoryFields.php

**Verification Tasks:**
1. Open file: src/Admin/CategoryFields.php
2. Search for all instances of: "aps_category_"
3. Search for all instances of: "_aps_category_"
4. Count occurrences of each pattern
5. Check what prefix is used in get_term_meta() calls
6. Check what prefix is used in update_term_meta() calls

---

## Verification Results

### Pattern Search Results

**Total Occurrences:** 54 instances found

---

### Finding 1: `_aps_category_` (WITH Underscore Prefix)

**Count:** 34 occurrences
**Status:** ✅ **CURRENT/ACTIVE PREFIX**

**Usage in Code:**

#### 1. In get_category_meta() Helper Method (Line 103)
```php
// Try new format with underscore prefix
$value = get_term_meta( $term_id, '_aps_category_' . $meta_key, true );
```
**Purpose:** Primary lookup with new format (WITH underscore)

---

#### 2. In Form Field HTML (Lines 128, 142, 155, 162, 174)

```php
<!-- Featured Category -->
<input
    type="checkbox"
    id="_aps_category_featured"
    name="_aps_category_featured"
    value="1"
    <?php checked( $featured, '1' ); ?>
/>

<!-- Default Category -->
<input
    type="checkbox"
    id="_aps_category_is_default"
    name="_aps_category_is_default"
    value="1"
    <?php checked( $is_default, '1' ); ?>
/>

<!-- Image URL -->
<input
    type="url"
    id="_aps_category_image"
    name="_aps_category_image"
    value="<?php echo esc_attr( $image_url ); ?>"
/>

<!-- Sort Order -->
<select
    id="_aps_category_sort_order"
    name="_aps_category_sort_order"
    class="postform"
>

<!-- Status -->
<select
    id="_aps_category_status"
    name="_aps_category_status"
    class="postform"
>
```
**Purpose:** Form field IDs and names for POST submission

---

#### 3. In save_category_fields() Method - update_term_meta() Calls

```php
// Sanitize and save featured
$featured = isset( $_POST['_aps_category_featured'] ) ? '1' : '0';
update_term_meta( $category_id, '_aps_category_featured', $featured );

// Sanitize and save image URL
$image_url = isset( $_POST['_aps_category_image'] ) 
    ? esc_url_raw( wp_unslash( $_POST['_aps_category_image'] ) ) 
    : '';
update_term_meta( $category_id, '_aps_category_image', $image_url );

// Sanitize and save sort order
$sort_order = isset( $_POST['_aps_category_sort_order'] )
    ? sanitize_text_field( wp_unslash( $_POST['_aps_category_sort_order'] ) )
    : 'date';
update_term_meta( $category_id, '_aps_category_sort_order', $sort_order );

// Sanitize and save status
$status = isset( $_POST['_aps_category_status'] )
    ? sanitize_text_field( wp_unslash( $_POST['_aps_category_status'] ) )
    : 'published';
update_term_meta( $category_id, '_aps_category_status', $status );

// Handle default category
$is_default = isset( $_POST['_aps_category_is_default'] ) ? '1' : '0';
update_term_meta( $category_id, '_aps_category_is_default', '1' );
```
**Purpose:** Saving category metadata WITH underscore prefix

---

#### 4. In Bulk Actions - update_term_meta() Calls

```php
// Move to Draft action
$result = update_term_meta( $term_id, '_aps_category_status', 'draft' );

// Move to Trash action
$result = update_term_meta( $term_id, '_aps_category_status', 'draft' );
```
**Purpose:** Updating category status in bulk operations

---

### Finding 2: `aps_category_` (WITHOUT Underscore Prefix)

**Count:** 20 occurrences
**Status:** ⚠️ **LEGACY PREFIX (BEING DELETED)**

**Usage in Code:**

#### 1. In get_category_meta() Helper Method (Line 107)
```php
// If empty, try legacy format without underscore
if ( $value === '' || $value === false ) {
    $value = get_term_meta( $term_id, 'aps_category_' . $meta_key, true );
}
```
**Purpose:** Legacy fallback for backward compatibility

---

#### 2. In save_category_fields() Method - delete_term_meta() Calls

```php
// Delete legacy key
delete_term_meta( $category_id, 'aps_category_featured' );

// Delete legacy key
delete_term_meta( $category_id, 'aps_category_image' );

// Delete legacy key
delete_term_meta( $category_id, 'aps_category_sort_order' );

// Delete legacy key
delete_term_meta( $category_id, 'aps_category_status' );

// Delete legacy key
delete_term_meta( $category_id, 'aps_category_is_default' );
```
**Purpose:** Cleaning up legacy keys after migration

---

#### 3. In remove_default_from_all_categories() Method

```php
foreach ( $terms as $term_id ) {
    delete_term_meta( $term_id, '_aps_category_is_default' );
    delete_term_meta( $term_id, 'aps_category_is_default' );
}
```
**Purpose:** Deleting both new and legacy keys when removing default flag

---

## Prefix Comparison

| Prefix | Occurrences | Usage | Status |
|---------|------------|---------|---------|
| **_aps_category_** (WITH underscore) | 34 | get_term_meta(), update_term_meta(), form fields | ✅ **CURRENT/ACTIVE** |
| **aps_category_** (WITHOUT underscore) | 20 | get_term_meta() fallback, delete_term_meta() cleanup | ⚠️ **LEGACY** |

---

## Detailed Breakdown by Function

### get_category_meta() Method (Lines 98-110)

```php
private function get_category_meta( int $term_id, string $meta_key ) {
    // Try new format with underscore prefix
    $value = get_term_meta( $term_id, '_aps_category_' . $meta_key, true );
    
    // If empty, try legacy format without underscore
    if ( $value === '' || $value === false ) {
        $value = get_term_meta( $term_id, 'aps_category_' . $meta_key, true );
    }
    
    return $value;
}
```

**Primary Prefix:** `_aps_category_` (WITH underscore)
**Fallback Prefix:** `aps_category_` (WITHOUT underscore)

**Lookup Order:**
1. First: `_aps_category_{field}` (new format)
2. Second: `aps_category_{field}` (legacy format, if first empty)

---

### update_term_meta() Calls

**All update_term_meta() calls use `_aps_category_` prefix:**

```php
update_term_meta( $category_id, '_aps_category_featured', $featured );
update_term_meta( $category_id, '_aps_category_image', $image_url );
update_term_meta( $category_id, '_aps_category_sort_order', $sort_order );
update_term_meta( $category_id, '_aps_category_status', $status );
update_term_meta( $category_id, '_aps_category_is_default', '1' );
update_term_meta( $category_id, '_aps_category_is_default', '0' );
```

**Prefix:** ✅ **WITH underscore**

---

### delete_term_meta() Calls

**Two types of delete operations:**

1. **Deleting legacy keys (cleanup):**
```php
delete_term_meta( $category_id, 'aps_category_featured' );
delete_term_meta( $category_id, 'aps_category_image' );
delete_term_meta( $category_id, 'aps_category_sort_order' );
delete_term_meta( $category_id, 'aps_category_status' );
delete_term_meta( $category_id, 'aps_category_is_default' );
```

2. **Deleting current keys:**
```php
delete_term_meta( $term_id, '_aps_category_is_default' );
```

---

## Meta Key Format Analysis

### Current Active Prefix
**Format:** `_aps_category_{field}`
**Example:** `_aps_category_featured`, `_aps_category_status`
**Prefix:** `_aps_category_` (WITH underscore)
**Status:** ✅ **ACTIVE**

### Legacy Prefix
**Format:** `aps_category_{field}`
**Example:** `aps_category_featured`, `aps_category_status`
**Prefix:** `aps_category_` (WITHOUT underscore)
**Status:** ⚠️ **LEGACY (being removed)**

---

## Migration Strategy

The code implements a **progressive migration strategy:**

### Step 1: Write (Save)
```php
// Always save with new format
update_term_meta( $category_id, '_aps_category_featured', $featured );
```

### Step 2: Cleanup (Delete Legacy)
```php
// Delete old format after saving
delete_term_meta( $category_id, 'aps_category_featured' );
```

### Step 3: Read (Dual Lookup)
```php
// Try new format first
$value = get_term_meta( $term_id, '_aps_category_featured', true );

// Fallback to legacy format if empty
if ( $value === '' || $value === false ) {
    $value = get_term_meta( $term_id, 'aps_category_featured', true );
}
```

### Step 4: Automatic Migration
Every time a category is edited:
1. New value saved with `_aps_category_` prefix
2. Old key with `aps_category_` prefix deleted
3. Result: Automatic migration on next edit

---

## Expected vs Actual

| Item | Expected | Actual | Match |
|-------|-----------|---------|---------|
| Primary Prefix | NO underscore prefix | ✅ **WITH underscore** prefix | ❌ MISMATCH |
| Secondary Prefix | N/A | ⚠️ WITHOUT underscore (legacy) | - |
| Migration Strategy | N/A | ✅ Dual lookup + cleanup | - |

**Analysis Correction Required:** The expected result was "NO underscore prefix" but the **actual result is WITH underscore prefix**.

---

## Conclusion

### Actual Meta Key Prefix: ✅ **WITH UNDERSCORE**

**Primary Prefix:** `_aps_category_` (WITH underscore)
**Occurrences:** 34 (active operations)
**Usage:** 
- ✅ Form fields (HTML)
- ✅ get_term_meta() primary lookup
- ✅ update_term_meta() all saves
- ✅ Bulk actions updates

**Legacy Prefix:** `aps_category_` (WITHOUT underscore)
**Occurrences:** 20 (cleanup operations)
**Usage:**
- ⚠️ get_term_meta() fallback (backward compatibility)
- ⚠️ delete_term_meta() cleanup (removing old keys)

### Migration Status: ✅ **IN PROGRESS**

- ✅ New format: `_aps_category_*` (WITH underscore)
- ✅ Legacy format: `aps_category_*` (WITHOUT underscore)
- ✅ Automatic migration on category edit
- ✅ Dual lookup for backward compatibility
- ✅ Legacy key cleanup on save

### Impact on Database

**Current State:**
- Categories edited after migration: Use `_aps_category_*` keys
- Categories not yet edited: Still have `aps_category_*` keys
- No data loss: Both formats work during transition

**Future State:**
- After all categories edited once: Only `_aps_category_*` keys remain
- Legacy cleanup: Automatic on each edit
- Single format: `_aps_category_*` (WordPress standard)

---

## Recommendations

### ✅ Current Implementation is Correct

The meta key prefix **WITH underscore** (`_aps_category_*`) is the correct format:

1. **WordPress Standard:** Private meta keys should start with underscore
2. **WordPress Codex:** Underscore prefix prevents custom fields from appearing in custom fields list
3. **Best Practice:** Follows WordPress conventions for private meta
4. **Backward Compatible:** Dual lookup ensures no data loss
5. **Auto-Migrating:** Legacy keys cleaned up on edit

### No Changes Required

The current implementation is **correct and follows WordPress standards**:
- ✅ Primary prefix: `_aps_category_` (WITH underscore)
- ✅ Legacy fallback: `aps_category_` (WITHOUT underscore)
- ✅ Automatic migration: Cleanup on save
- ✅ No data loss: Dual lookup
- ✅ WordPress compliant: Private meta with underscore

---

## WordPress Meta Key Best Practices

### Private vs Public Meta Keys

**Public Meta Keys (no underscore):**
- Example: `product_color`
- Visible: Custom Fields meta box in post editor
- Use case: User-editable fields

**Private Meta Keys (with underscore):**
- Example: `_product_internal_data`
- Hidden: Not visible in Custom Fields meta box
- Use case: Plugin-internal data, system fields

### Our Implementation

**Category Meta Keys:**
- `_aps_category_featured` - Private (plugin internal)
- `_aps_category_status` - Private (plugin internal)
- `_aps_category_is_default` - Private (plugin internal)

**Rationale:** These are plugin-internal fields, not user-editable via Custom Fields

---

## Summary

### Verification Question Answers

| Question | Answer |
|----------|---------|
| How many times does "aps_category_" appear? | **20** (legacy cleanup) |
| How many times does "_aps_category_" appear? | **34** (active operations) |
| Which prefix is used in get_term_meta()? | **_aps_category_** (primary), `aps_category_` (fallback) |
| Which prefix is used in update_term_meta()? | **_aps_category_** (all operations) |
| Is there underscore prefix or not? | **YES** - underscore prefix is used |

### Expected Result: NO underscore prefix (according to analysis) ❌
### Actual Result: **YES** underscore prefix is used ✅

**Analysis Correction:** The previous analysis was incorrect. The meta key prefix **IS** with underscore (`_aps_category_*`), which is **CORRECT** and follows WordPress best practices.

---

*Report Generated: 2026-01-24 18:37*
*Verification Method: Code search + pattern analysis*