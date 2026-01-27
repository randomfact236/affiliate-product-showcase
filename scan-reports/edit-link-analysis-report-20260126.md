# Edit Link Analysis Report
**Date:** January 26, 2026  
**Status:** ISSUE IDENTIFIED & SOLUTION PROPOSED

---

## Executive Summary

**Issue:** Clicking "Edit" in products table redirects to default WordPress edit page instead of custom add-product page  
**Impact:** User experience inconsistency - custom add page exists but edit link doesn't use it  
**Priority:** HIGH - Affects core user workflow  
**Solution:** Update edit link to use custom page and make custom page handle editing

---

## Current Behavior

### 1. Edit Link in ProductsTable

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`  
**Line:** 153  
**Current Code:**
```php
public function column_title( $item ): string {
    $edit_url = get_edit_post_link( $item->ID );
    // ...
}
```

**Problem:** `get_edit_post_link()` always returns WordPress default edit URL:  
- Format: `post.php?post={ID}&action=edit`
- Opens: Default WordPress classic/block editor
- **Does NOT** use custom add-product page

### 2. Default WordPress Edit Page

**URL:** `post.php?post={ID}&action=edit`  
**Features:**
- ✅ WordPress classic editor
- ✅ WordPress block editor
- ❌ NO custom fields (affiliated, features, pricing sections)
- ❌ NO WooCommerce-style layout
- ❌ NO quick navigation
- ❌ NO feature list management
- ❌ NO ribbon/category/tag multi-select
- ❌ NO media library integration (custom UI)

### 3. Custom Add Product Page

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`  
**Route:** `admin.php?page=affiliate-manager-add-product`  
**Features:**
- ✅ WooCommerce-style single-page form
- ✅ Quick navigation (Product Info, Images, Affiliate, Features, Pricing, Categories & Tags, Stats)
- ✅ Product Info (title, status, featured)
- ✅ Image uploads with media library integration
- ✅ Affiliate details (URL, button name)
- ✅ Short description with word counter
- ✅ Feature list management (add, remove, reorder, highlight)
- ✅ Pricing (regular, sale, auto-discount calculation)
- ✅ Categories & Ribbons multi-select
- ✅ Tags checkboxes
- ✅ Product statistics (rating, views, reviews)
- ✅ Inline styles and JavaScript
- ✅ Responsive design

---

## Analysis

### Is Custom Page Necessary?

**Answer: YES - ABSOLUTELY NECESSARY**

**Reasons:**

1. **User Experience Consistency**
   - Add Product uses custom WooCommerce-style page
   - Edit Product should use same page for consistency
   - Different UI for add vs edit = confusing for users

2. **Feature Completeness**
   - Custom page has features NOT available in WordPress default editor:
     - Feature list management
     - Quick navigation sections
     - WooCommerce-style layout
     - Custom pricing (regular + sale with discount calculation)
     - Categories/Tags/Ribbons multi-select UI
     - Product statistics fields

3. **Brand Consistency**
   - Plugin aims to provide WooCommerce-like experience
   - Custom page delivers that experience
   - Default WordPress editor breaks that brand promise

4. **Workflow Efficiency**
   - Custom page is faster (single-page form)
   - Default editor requires navigating multiple meta boxes
   - Custom page groups related fields together

5. **Investment Protection**
   - Significant development effort went into custom page (~700 lines)
   - Removing it would waste that investment
   - Custom page provides unique value proposition

### Should Default WordPress Edit Page Be Removed?

**Answer: YES - REDIRECT TO CUSTOM PAGE**

**Implementation Strategy:**

1. **Redirect WordPress edit to custom page**
   - Hook into `load-post.php`
   - Detect if editing `aps_product`
   - Redirect to custom page with product ID

2. **Update custom page to handle editing**
   - Check for product ID in URL
   - Load existing product data
   - Pre-fill form fields
   - Update form action to handle both add and edit

3. **Remove default WordPress editor access**
   - Prevent users from accidentally using inferior editor
   - Ensure all edits go through custom page

---

## Proposed Solution

### Solution A: Update Edit Link + Enhance Custom Page (RECOMMENDED)

**Steps:**

1. **Update ProductsTable edit link**
   ```php
   // Change from:
   $edit_url = get_edit_post_link( $item->ID );
   
   // Change to:
   $edit_url = admin_url( 'edit.php?post_type=aps_product&page=add-product&post=' . $item->ID );
   ```

2. **Update custom add-product page to handle editing**
   - Add `post` parameter to URL
   - Load product data if editing
   - Pre-fill all form fields
   - Change "Update Product" vs "Add Product" button text

3. **Redirect WordPress default edit to custom page**
   - Add hook to intercept default editor
   - Redirect to custom page with product ID
   - Provide smooth user experience

**Pros:**
- ✅ Consistent UI for add and edit
- ✅ Users get full feature set
- ✅ Maintains brand consistency
- ✅ Minimal code changes
- ✅ Protects development investment

**Cons:**
- ⚠️ Requires testing of edit mode
- ⚠️ Need to load existing data into form

### Solution B: Keep Both Pages (NOT RECOMMENDED)

**Approach:** Keep WordPress default editor as option

**Pros:**
- ✅ Users can choose which editor to use
- ✅ Backup option if custom page has bugs

**Cons:**
- ❌ Inconsistent user experience
- ❌ Confusing for users (which one to use?)
- ❌ WordPress editor missing custom features
- ❌ Doubles maintenance burden
- ❌ Breaks brand promise

**Decision:** REJECTED

### Solution C: Remove Custom Page (NOT RECOMMENDED)

**Approach:** Remove custom page, use WordPress default editor

**Pros:**
- ✅ Less code to maintain
- ✅ Uses WordPress standard UI

**Cons:**
- ❌ Loses all custom features
- ❌ Breaks WooCommerce-style brand promise
- ❌ Wastes development investment
- ❌ Inferior user experience
- ❌ Loses feature list management
- ❌ Loses custom pricing UI

**Decision:** REJECTED

---

## Recommended Implementation Plan

### Phase 1: Quick Fix (15 minutes)

1. **Update ProductsTable edit link**
   - File: `src/Admin/ProductsTable.php`
   - Change line 153 from `get_edit_post_link()` to custom page URL
   - Test edit link points to custom page

2. **Update custom page to handle editing**
   - Add logic to detect `post` parameter
   - Load existing product data
   - Pre-fill form fields
   - Change submit button text based on mode

### Phase 2: Redirect Protection (30 minutes)

1. **Add redirect hook**
   - File: Create or update `src/Admin/EditorRedirect.php`
   - Hook into `load-post.php`
   - Redirect `aps_product` edits to custom page

2. **Test redirect**
   - Verify default edit URL redirects correctly
   - Verify existing products can be edited
   - Verify no infinite redirect loops

### Phase 3: Testing & Documentation (45 minutes)

1. **Manual testing**
   - Test editing existing products
   - Test adding new products
   - Test all form fields save correctly
   - Test media library uploads
   - Test feature list management

2. **Documentation update**
   - Update user guide to reflect single-page editor
   - Add screenshots of edit mode
   - Document URL parameters

---

## Technical Details

### Custom Page URL Structure

**Add New Product:**
```
admin.php?page=affiliate-manager-add-product
```

**Edit Existing Product:**
```
admin.php?page=affiliate-manager-add-product&post={ID}
```

### Form Handling

**Current (Add Only):**
```php
<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
    <input type="hidden" name="action" value="aps_save_product">
</form>
```

**Proposed (Add + Edit):**
```php
<?php
$post_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : 0;
$is_editing = $post_id > 0;
?>
<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
    <input type="hidden" name="action" value="<?php echo $is_editing ? 'aps_update_product' : 'aps_save_product'; ?>">
    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
</form>
```

### Data Loading for Edit Mode

```php
<?php
if ( $is_editing ) {
    $post = get_post( $post_id );
    $title = $post->post_title;
    $status = $post->post_status;
    $price = get_post_meta( $post_id, 'aps_price', true );
    // ... load all other fields
}
?>
```

---

## Files to Modify

### 1. `src/Admin/ProductsTable.php`

**Line:** 153  
**Change:** Update edit link to use custom page

### 2. `src/Admin/partials/add-product-page.php`

**Changes:**
- Add logic to detect edit mode (check for `post` parameter)
- Load existing product data if editing
- Pre-fill all form fields with existing data
- Change button text based on mode
- Update form action to handle both add and update

### 3. `src/Admin/Menu.php` (Optional)

**Change:** Add method to get edit URL for products

### 4. `src/Admin/EditorRedirect.php` (New)

**Purpose:** Redirect default WordPress editor to custom page

---

## Risk Assessment

### Risks

1. **Data Loss Risk: LOW**
   - Custom page saves to same post meta fields
   - No risk of data loss
   - Backup can be taken before implementing

2. **User Confusion: LOW**
   - Users may notice UI change
   - Better UI will be seen as improvement
   - Clear documentation will help

3. **Regression Risk: LOW**
   - Change is isolated to edit link
   - Add functionality remains unchanged
   - Easy to rollback if issues arise

### Mitigation

1. **Backup before implementation**
2. **Test on staging environment first**
3. **Gradual rollout (optional)**
4. **Monitor for issues after deployment**
5. **Keep rollback plan ready**

---

## Testing Checklist

### Before Implementation
- [ ] Create backup branch
- [ ] Document current edit link behavior
- [ ] Identify all edit link usages in codebase

### After Implementation
- [ ] Test edit link points to custom page
- [ ] Test editing existing product
- [ ] Test all form fields populate correctly
- [ ] Test media library uploads work
- [ ] Test feature list saves correctly
- [ ] Test categories/tags/ribbons save correctly
- [ ] Test pricing saves correctly
- [ ] Test "Update Product" button works
- [ ] Test "Save Draft" button works
- [ ] Test redirect from default WordPress editor
- [ ] Test no infinite redirect loops
- [ ] Test on mobile devices
- [ ] Test with different user roles
- [ ] Verify no PHP errors
- [ ] Verify no JavaScript console errors

---

## Conclusion

**Recommendation:** Implement Solution A  
**Effort:** 1.5 hours  
**Risk:** LOW  
**Impact:** HIGH positive impact on user experience  

**Summary:** The custom add-product page is necessary and provides significant value. The edit link should be updated to use this page, and the page should be enhanced to handle editing existing products. This will provide consistent user experience and fully utilize the investment in the custom page.

**Next Steps:**
1. Implement Phase 1 (quick fix)
2. Test thoroughly
3. Implement Phase 2 (redirect protection)
4. Final testing and documentation
5. Commit and deploy

---

**Generated:** January 26, 2026  
**Report By:** Affiliate Product Showcase Code Analysis  
**Status:** READY FOR IMPLEMENTATION