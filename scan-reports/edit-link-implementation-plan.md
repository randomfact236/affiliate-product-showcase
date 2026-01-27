# Edit Link Implementation Plan
**Generated:** 2026-01-26
**Task:** Add edit capability to custom Add Product page and disable WordPress native editor

---

## 📋 Implementation Status

### ✅ Completed
1. Added edit mode detection to add-product-page.php
2. Added product data loading for edit mode
3. Pre-populated Product Info section (title, status, featured)
4. Pre-populated Images section (logo, brand_image)
5. Pre-populated Affiliate Details section (affiliate_url, button_name)
6. Pre-populated Pricing section (regular_price, sale_price)
7. Fixed header to show "Edit Product" or "Add Product" based on mode

### ⏳ In Progress
8. Pre-populate remaining fields (features, categories, ribbons, tags, stats)
9. Add JavaScript initialization for edit mode
10. Update Menu.php to redirect to custom page
11. Update ProductsTable.php edit link
12. Disable WordPress native editor (optional)

---

## 🔧 Remaining Tasks

### Task 8-9: Complete Edit Mode JavaScript

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`

**Changes Needed:**

Add to JavaScript section (after line 665):
```javascript
// Edit Mode Initialization
const isEditMode = <?php echo $is_editing ? 'true' : 'false' ?>;

// Initialize features from product data if editing
if (isEditMode && <?php echo json_encode($product_data['features'] ?? []); ?>) {
    features = <?php echo json_encode($product_data['features'] ?? []); ?>;
    renderFeatures();
}

// Initialize categories from product data if editing
if (isEditMode) {
    const productCategories = <?php echo json_encode(wp_get_post_terms($post_id, 'aps_category', ['fields' => 'slug'])); ?>;
    productCategories.forEach(cat => {
        if (!selectedCategories.includes(cat.slug)) {
            selectedCategories.push(cat.slug);
        }
    });
    renderCategories();
    $('#aps-categories-input').val(selectedCategories.join(','));
}

// Initialize ribbons from product data if editing
if (isEditMode) {
    const productRibbons = <?php echo json_encode(wp_get_post_terms($post_id, 'aps_ribbon', ['fields' => 'slug'])); ?>;
    productRibbons.forEach(ribbon => {
        if (!selectedRibbons.includes(ribbon.slug)) {
            selectedRibbons.push(ribbon.slug);
        }
    });
    renderRibbons();
    $('#aps-ribbons-input').val(selectedRibbons.join(','));
}

// Initialize tags from product data if editing
if (isEditMode) {
    const productTags = <?php echo json_encode(wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'slug'])); ?>;
    productTags.forEach(tag => {
        $(`input[name="aps_tags[]"][value="${tag.slug}"]`).prop('checked', true);
    });
}

// Initialize stats from product data if editing
if (isEditMode) {
    $('#aps-rating').val(<?php echo json_encode($product_data['rating'] ?? ''); ?>);
    $('#aps-views').val(<?php echo json_encode($product_data['views'] ?? ''); ?>);
    $('#aps-user-count').val(<?php echo json_encode($product_data['user_count'] ?? ''); ?>);
    $('#aps-reviews').val(<?php echo json_encode($product_data['reviews'] ?? ''); ?>);
}
```

**Location:** After `// Discount Calculator` section (around line 665)

---

### Task 10: Update Menu.php to Redirect to Custom Page

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php`

**Current Code:**
```php
public function redirectOldAddNewForm(): void {
    global $pagenow, $typenow;
    
    if ( $pagenow === 'post-new.php' && $typenow === 'aps_product' ) {
        wp_safe_redirect( admin_url( 'admin.php?page=affiliate-manager-add-product' ) );
        exit;
    }
}
```

**New Code:**
```php
public function redirectNativeEditor(): void {
    global $pagenow, $typenow;
    
    // Redirect post-new.php (Add New) to custom page
    if ( $pagenow === 'post-new.php' && $typenow === 'aps_product' ) {
        wp_safe_redirect( admin_url( 'admin.php?page=affiliate-manager-add-product' ) );
        exit;
    }
    
    // Redirect post.php (Edit) to custom page
    if ( $pagenow === 'post.php' && $typenow === 'aps_product' ) {
        if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
            $post_id = (int) $_GET['post'];
            wp_safe_redirect( admin_url( 'admin.php?page=affiliate-manager-add-product&post=' . $post_id ) );
            exit;
        }
    }
}
```

**Change:** Rename method from `redirectOldAddNewForm()` to `redirectNativeEditor()` and add post.php redirect logic

---

### Task 11: Update ProductsTable.php Edit Link

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`

**Current Code (Line 147):**
```php
$edit_url = get_edit_post_link( $item->ID );
```

**New Code:**
```php
$edit_url = admin_url( 'admin.php?page=affiliate-manager-add-product&post=' . $item->ID );
```

**Change:** Replace `get_edit_post_link()` with custom edit URL pointing to custom page

---

### Task 12: Disable WordPress Native Editor (Optional)

**Option A: Hide the native editor via CSS**
```php
// Add to Menu.php constructor
add_action( 'admin_head', function() {
    global $pagenow, $typenow;
    if ( $pagenow === 'post.php' && $typenow === 'aps_product' ) {
        echo '<style>#post, #titlediv, #submitdiv { display: none !important; }</style>';
    }
});
```

**Option B: Remove meta boxes (not recommended - keeps them as fallback)**
Keep MetaBoxes.php as is - provides good fallback functionality

---

## 📝 Summary of Changes

### Files to Modify:
1. ✅ `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`
   - Add edit mode JavaScript initialization
   - Pre-populate all fields with existing data

2. ⏳ `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php`
   - Update redirect method to handle both add and edit
   - Redirect post.php to custom page

3. ⏳ `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`
   - Update edit link to point to custom page

### Files to Keep:
- `wp-content/plugins/affiliate-product-showcase/src/Admin/MetaBoxes.php` - Keep as fallback

---

## 🎯 Expected Result

**After Implementation:**

1. **Add Product:**
   - Click "Add New" → Custom Add Product page
   - Empty form ready for new product

2. **Edit Product:**
   - Click "Edit" in Products Table → Custom Add Product page (in edit mode)
   - All fields pre-populated with existing data
   - Header shows "Edit Product"

3. **WordPress Native Editor:**
   - Redirects to custom page (both post-new.php and post.php)
   - Still accessible via direct URL (fallback option)

4. **Single Interface:**
   - All product management through custom page
   - Consistent UX across all operations

---

**Status:** Implementation Plan Created
**Next Step:** Implement changes to Menu.php and ProductsTable.php