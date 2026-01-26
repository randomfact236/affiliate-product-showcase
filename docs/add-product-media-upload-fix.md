# Add Product Media Upload Fix

## Issue Description
The WordPress Media Library upload functionality was not working in the Add Product form. When clicking on "Select from Media Library" buttons for Product Image or Brand Image, nothing happened - the buttons were not clickable and the media library modal did not open.

## Root Cause Analysis

### Multiple Issues Identified

1. **Incorrect Admin Hook Name**
   - **Location**: `src/Admin/Enqueue.php` (line 621)
   - **Problem**: The `isProductEditPage()` method was checking for the wrong hook name
   - **Expected**: `'aps_product_page_add-product'`
   - **Found**: `'affiliate-product-showcase_page_affiliate-manager-add-product'`
   - **Impact**: `wp_enqueue_media()` was not being called, so the WordPress Media Library scripts were not loaded

2. **Missing Asset Files Breaking Script Chain**
   - **Missing Files**:
     - `assets/js/admin.js`
     - `assets/css/admin.css`
     - `assets/js/dashboard.js`
     - `assets/css/dashboard.css`
     - `assets/js/analytics.js`
     - `assets/css/analytics.css`
     - `assets/js/settings.js`
   - **Impact**: When WordPress tried to enqueue these non-existent files, it caused 404 errors which broke the JavaScript dependency chain, preventing subsequent scripts from loading properly

3. **No Error Checking in JavaScript**
   - **Problem**: The inline JavaScript in `add-product-page.php` didn't check if `wp.media` was available before trying to use it
   - **Impact**: Silent failures with no user feedback

## Solutions Implemented

### 1. Fixed Admin Hook Name
**File**: `src/Admin/Enqueue.php`

```php
private function isProductEditPage( string $hook ): bool {
    return $hook === 'post.php'
        || $hook === 'post-new.php'
        || $hook === 'aps_product_page_add-product';  // ✓ Fixed
}
```

**Explanation**: WordPress generates submenu hook names using the pattern `{post_type}_page_{menu_slug}`. Since the menu is registered under `'edit.php?post_type=aps_product'` with slug `'add-product'`, the correct hook is `'aps_product_page_add-product'`.

### 2. Added Direct wp_enqueue_media() Calls
**Files Modified**:
- `src/Admin/Menu.php` - Added `wp_enqueue_media()` in `renderAddProductPage()` method
- `src/Admin/partials/add-product-page.php` - Added `wp_enqueue_media()` at the top of the template

**Explanation**: Since the hook-based enqueuing wasn't working reliably, we added direct calls to `wp_enqueue_media()` in both the render method and the template file itself. This ensures the media library scripts are always loaded when the page is rendered.

### 3. Created Missing Asset Files
Created placeholder files to prevent 404 errors:
- ✓ `assets/js/admin.js`
- ✓ `assets/css/admin.css`
- ✓ `assets/js/dashboard.js`
- ✓ `assets/css/dashboard.css`
- ✓ `assets/js/analytics.js`
- ✓ `assets/css/analytics.css`
- ✓ `assets/js/settings.js`

### 4. Added Error Checking and Debug Logging
**File**: `src/Admin/partials/add-product-page.php`

#### Debug Output on Page Load:
```javascript
jQuery(document).ready(function($) {
    // Debug: Check if required libraries are loaded
    console.log('jQuery loaded:', typeof jQuery !== 'undefined');
    console.log('wp object loaded:', typeof wp !== 'undefined');
    console.log('wp.media loaded:', typeof wp !== 'undefined' && typeof wp.media !== 'undefined');
    // ...
});
```

#### Error Checking in Upload Handlers:
```javascript
$('#aps-upload-image-btn').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('Product Image button clicked');
    
    // Check if wp.media is available
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('WordPress media library is not loaded');
        alert('WordPress media library is not loaded. Please refresh the page.');
        return;
    }
    
    const mediaUploader = wp.media({
        title: 'Select Image',
        button: { text: 'Use This Image' },
        multiple: false
    });
    
    mediaUploader.on('select', function() {
        const attachment = mediaUploader.state().get('selection').first().toJSON();
        console.log('Image selected:', attachment);
        // ... rest of the code
    });
    
    mediaUploader.open();
});
```

## Testing Instructions

### 1. Clear Browser Cache
Clear your browser cache or do a hard refresh (Ctrl+F5 / Cmd+Shift+R) to ensure you're loading the latest JavaScript files.

### 2. Open Browser Console
Open your browser's developer console (F12) to see debug messages.

### 3. Navigate to Add Product Page
Go to: **Affiliate Products → Add Product**

### 4. Check Console Output
You should see:
```
jQuery loaded: true
wp object loaded: true
wp.media loaded: true
Affiliate Product Showcase Admin JS loaded
```

### 5. Test Product Image Upload
1. Click the "Select from Media Library" button under "Product Image (Featured)"
2. Console should show: `Product Image button clicked`
3. WordPress Media Library modal should open
4. Select an image and click "Use This Image"
5. Console should show: `Image selected: {Object}`
6. Image preview should appear in the upload area

### 6. Test Brand Image Upload
Same steps as above for the Brand Image section.

## Troubleshooting

### If Media Library Still Doesn't Open

1. **Check Console for Errors**
   - Look for any JavaScript errors in the browser console
   - Check if there are 404 errors for missing files

2. **Verify Hook Name**
   - Add temporary debug code to verify the hook:
   ```php
   add_action('admin_enqueue_scripts', function($hook) {
       error_log('Current hook: ' . $hook);
   });
   ```
   - Check WordPress debug.log to see what hook is actually being called

3. **Check WordPress Version**
   - Ensure WordPress is up to date
   - The `wp.media` API requires WordPress 3.5+

4. **Check for Plugin Conflicts**
   - Temporarily deactivate other plugins to check for conflicts
   - Some plugins may interfere with the WordPress Media Library

5. **Verify File Permissions**
   - Ensure the newly created asset files are readable
   - Check that the assets directory has proper permissions

## Related Files Modified

1. `src/Admin/Enqueue.php` - Fixed hook name check, added debug logging
2. `src/Admin/Menu.php` - Added `wp_enqueue_media()` in render method
3. `src/Admin/partials/add-product-page.php` - Added `wp_enqueue_media()` and error checking with debug logging
4. `assets/js/admin.js` - Created
5. `assets/css/admin.css` - Created
6. `assets/js/dashboard.js` - Created
7. `assets/css/dashboard.css` - Created
8. `assets/js/analytics.js` - Created
9. `assets/css/analytics.css` - Created
10. `assets/js/settings.js` - Created

## Technical Notes

### WordPress Media Library Dependencies
The WordPress Media Library requires:
1. `wp_enqueue_media()` must be called on the page
2. jQuery must be loaded (dependency)
3. WordPress core media scripts must be loaded
4. No JavaScript errors preventing script execution

### Admin Hook Naming Convention
WordPress submenu pages create hooks using this pattern:
- For CPT submenus: `{post_type}_page_{menu_slug}`
- For top-level submenus: `{parent_slug}_page_{menu_slug}`

In our case:
- Parent: `'edit.php?post_type=aps_product'` → Post type: `aps_product`
- Menu slug: `'add-product'`
- Resulting hook: `'aps_product_page_add-product'`

## Date Fixed
January 26, 2026
