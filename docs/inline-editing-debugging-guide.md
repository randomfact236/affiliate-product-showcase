# Inline Editing - Debugging Guide

## 🐛 Why Features Might Not Be Working

### Issue: Scripts Loading Before Table Renders

The table is rendered via the `admin_notices` hook, which fires **AFTER** JavaScript files are loaded. This means:

1. ✅ JavaScript file loads
2. ✅ JavaScript tries to find `#the-list` table
3. ❌ Table doesn't exist yet (not rendered)
4. ✅ Table renders later via `admin_notices` hook
5. ❌ JavaScript already gave up looking for table

**Solution:** The JavaScript has retry logic and MutationObserver to handle this.

---

## 🔧 Step-by-Step Debugging

### Step 1: Open Products Page
```
WordPress Admin → Products (aps_product) → All Products
```

### Step 2: Open Browser Console
- Press `F12` or `Ctrl+Shift+I` (Windows)
- Go to "Console" tab

### Step 3: Run Diagnostic Script

Copy and paste this into the console:

```javascript
// Quick diagnostic
console.log('=== Quick Diagnostic ===');
console.log('1. apsInlineEditData:', typeof apsInlineEditData !== 'undefined' ? '✅ Loaded' : '❌ Not loaded');
console.log('2. Table #the-list:', document.querySelector('#the-list') ? '✅ Found' : '❌ Not found');
console.log('3. Editable cells:', document.querySelectorAll('[data-field]').length, 'found');
console.log('4. Init messages:', 'Check console for [APS Inline Edit] messages');
```

### Step 4: Check Expected Console Messages

You should see:
```
[APS Inline Edit] Table found, initializing...
[APS Inline Edit] Event listeners attached
[APS Inline Edit] Initialized successfully
```

If you see:
```
[APS Inline Edit] Table not found: #the-list
[APS Inline Edit] Retrying... (1/10)
```
This means the table wasn't ready yet (normal, should retry and find it).

If you see:
```
[APS Inline Edit] Max retries reached. Table not found.
```
This means the table never appeared (problem!).

---

## 🔍 Common Issues & Fixes

### Issue 1: `apsInlineEditData is not defined`

**Cause:** JavaScript file not properly enqueued or localized script missing.

**Fix:**
1. Check that you're on the correct page: `edit.php?post_type=aps_product`
2. View page source (Ctrl+U) and search for `apsInlineEditData`
3. Verify the script tag exists:
```html
<script id='affiliate-product-showcase-products-table-inline-edit-js-before'>
var apsInlineEditData = {"restUrl":"...","nonce":"..."};
</script>
```

If missing, check [src/Admin/Enqueue.php](../src/Admin/Enqueue.php#L267-L277)

---

### Issue 2: Table Not Found

**Cause:** Table HTML not rendered or selector is wrong.

**Fix:**
1. View page source and search for `id="the-list"`
2. If found, the table exists - timing issue
3. If not found, check if ProductTableUI is rendering

**Check ProductTableUI rendering:**
```php
// In src/Admin/Admin.php line 68-72
public function render_product_table_on_products_page(): void {
    $screen = get_current_screen();
    
    if ($screen && $screen->id === 'edit-aps_product') {
        $this->product_table_ui->render(); // Should be called
    }
}
```

---

### Issue 3: Data Attributes Missing

**Cause:** ProductsTable column methods not updated with data attributes.

**Check in browser:**
```javascript
// Should return elements with data-field
document.querySelectorAll('[data-field]');

// Should return elements with data-product-id  
document.querySelectorAll('[data-product-id]');

// Should return category badges with IDs
document.querySelectorAll('[data-category-id]');
```

If these return 0 elements, the data attributes are not being added.

**Fix:** Verify changes were made to [src/Admin/ProductsTable.php](../src/Admin/ProductsTable.php):
- `column_category()` - line 218-233
- `column_tags()` - line 241-256
- `column_ribbon()` - line 266-278
- `column_price()` - line 305-353
- `column_status()` - line 355-387

---

### Issue 4: Click Not Working

**Cause:** Event listeners not attached or duplicate listeners.

**Check:**
```javascript
// Check if listeners attached flag
// (internal to IIFE, but check console logs)

// Check if clicking logs anything
// Click a cell and watch console
```

**Manual Test:**
1. Hover over a category/tags/price cell
2. You should see:
   - Background highlight
   - Pencil icon (✎) appear
3. Click the cell
4. Editor should open

If hover works but click doesn't:
- Event listener issue
- Check console for JavaScript errors

---

### Issue 5: REST API Errors

**Cause:** Nonce verification failing or endpoints not registered.

**Check REST API endpoints:**
```javascript
// Test category endpoint
fetch(apsInlineEditData.restUrl + 'categories', {
    headers: {
        'X-WP-Nonce': apsInlineEditData.nonce
    }
})
.then(r => r.json())
.then(d => console.log('Categories:', d))
.catch(e => console.error('Error:', e));
```

Should return array of categories.

If 403 Forbidden:
- Nonce invalid
- User doesn't have permission
- Check `wp_create_nonce('wp_rest')` in Enqueue.php

If 404 Not Found:
- REST routes not registered
- Check controllers are loaded
- Check Plugin.php registers routes

---

## 🧪 Manual Testing Checklist

### Test 1: Hover Effects
- [ ] Hover over category cell → Background highlights
- [ ] Hover over category cell → Pencil icon (✎) appears
- [ ] Hover over tags cell → Same effects
- [ ] Hover over price cell → Same effects
- [ ] Hover over status cell → Same effects

### Test 2: Click to Edit
- [ ] Click category cell → Dropdown appears
- [ ] Click tags cell → Multi-select appears
- [ ] Click price cell → Input field appears
- [ ] Click status cell → Dropdown appears

### Test 3: Auto-Save
- [ ] Change category → Click outside → Auto-saves
- [ ] Change price → Click outside → Auto-saves
- [ ] Change status → Click outside → Auto-saves

### Test 4: Visual Feedback
- [ ] During save → Loading spinner shows
- [ ] After save success → Green checkmark shows
- [ ] After save error → Red error message shows
- [ ] Toast notification appears (bottom-right)

### Test 5: Bulk Actions
- [ ] Select 2-3 products (checkboxes)
- [ ] Bulk actions dropdown shows "Publish" and "Move to Draft"
- [ ] Select "Move to Draft" → Click Apply
- [ ] Confirmation dialog appears
- [ ] Toast notification shows success
- [ ] Page reloads with updated statuses

---

## 🔬 Advanced Debugging

### Enable WordPress Debug Mode

Edit `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

This will:
- Log PHP errors to `wp-content/debug.log`
- Use unminified JavaScript files
- Show more detailed error messages

### Check PHP Error Log

```
wp-content/debug.log
```

Look for errors related to:
- `affiliate-product-showcase`
- `ProductsTable`
- `ProductTableUI`
- `Enqueue`

### Check Browser Network Tab

1. Open DevTools → Network tab
2. Reload page
3. Look for:
   - `products-table-inline-edit.js` - Should be 200 OK
   - `products-table-inline-edit.css` - Should be 200 OK
   - REST API calls to `/wp-json/affiliate-product-showcase/v1/*`

### Check JavaScript Console for Errors

Look for:
- `Uncaught ReferenceError: apsInlineEditData is not defined`
- `Uncaught TypeError: Cannot read property...`
- `[APS Inline Edit] ...` messages

---

## 📞 Reporting Issues

If debugging doesn't resolve the issue, report with:

1. **Browser Console Output:**
   - All `[APS Inline Edit]` messages
   - Any error messages (red text)
   - Result of diagnostic script

2. **Network Tab:**
   - Screenshot showing loaded JS/CSS files
   - Any failed requests (red/4xx/5xx codes)

3. **Page Source:**
   - Search for `apsInlineEditData` and paste the line
   - Search for `id="the-list"` and confirm if found

4. **Environment:**
   - WordPress version
   - PHP version
   - Browser and version
   - Active theme
   - Other active plugins

5. **Steps to Reproduce:**
   - Exact steps you took
   - What you expected to happen
   - What actually happened

---

## ✅ Success Indicators

When everything works correctly, you should see:

### In Console:
```
[APS Inline Edit] Table found, initializing...
[APS Inline Edit] Event listeners attached
[APS Inline Edit] Initialized successfully
```

### On Page:
- ✅ Hover shows pencil icon on editable cells
- ✅ Click opens editor immediately
- ✅ Changes save automatically
- ✅ Toast notifications appear
- ✅ No JavaScript errors in console
- ✅ Bulk actions include "Move to Draft"

### In Network Tab:
- ✅ products-table-inline-edit.js loaded (200 OK)
- ✅ products-table-inline-edit.css loaded (200 OK)
- ✅ POST requests to REST API return 200 OK

---

**Last Updated:** 2026-01-27
