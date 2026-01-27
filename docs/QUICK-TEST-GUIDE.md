# Quick Testing Guide - Inline Editing Feature

## 🚀 Quick Start (5 Minutes)

### 1. Clear Browser Cache
- Press `Ctrl + Shift + R` (Windows/Linux) or `Cmd + Shift + R` (Mac)
- Or open DevTools (F12) → Network tab → Check "Disable cache"

### 2. Navigate to Products Page
```
WordPress Admin → Affiliate Product Showcase → All Products
```

### 3. Test Basic Inline Editing

**Test Category Editing:**
1. Hover over any category cell → See pencil icon (✎)
2. Click the category cell
3. Dropdown should appear instantly
4. Select a different category
5. Click outside the dropdown
6. Green toast notification should appear: "Category updated"
7. Category badge updates without page reload

**Test Price Editing:**
1. Hover over any price cell → See pencil icon (✎)
2. Click the price cell
3. Input field appears with current price
4. Change the price (e.g., from 99.99 to 79.99)
5. Watch discount preview update in real-time
6. Press Enter or click outside
7. Green toast notification: "Price updated"
8. Price and discount badge update

**Test Status Editing:**
1. Hover over any status cell → See pencil icon (✎)
2. Click the status cell
3. Dropdown shows "Published" and "Draft"
4. Select opposite status
5. Click outside
6. Green toast notification: "Status updated"
7. Status badge color changes

### 4. Test "Add New" Feature

**Create New Category:**
1. Click any category cell
2. Select "+ Add New Category" from dropdown
3. Enter name: "Test Category" in prompt
4. Click OK
5. Green toast appears: "Category created successfully!"
6. Dropdown refreshes with new category
7. New category is automatically selected
8. Auto-save happens

### 5. Test Bulk Actions

**Move Products to Draft:**
1. Check 2-3 product checkboxes
2. Select "Move to Draft" from bulk actions dropdown (top-left)
3. Click "Apply" button
4. Confirm in dialog
5. Green toast appears with success message
6. Page reloads showing updated statuses

---

## ✅ Expected Results

### What Should Work:
- ✅ Inline editing opens instantly on click
- ✅ All dropdowns show current values
- ✅ Changes save automatically on blur
- ✅ Toast notifications appear for all actions
- ✅ No page reloads required (except bulk actions)
- ✅ Loading spinners show during save
- ✅ Success indicators appear after save
- ✅ Error messages show if save fails

### Browser Console Should Show:
```
[APS Inline Edit] Table found, initializing...
[APS Inline Edit] Event listeners attached
[APS Inline Edit] Initialized successfully
```

### Network Tab Should Show:
- POST requests to `/wp-json/affiliate-product-showcase/v1/products/{id}/field`
- Response 200 OK with success message
- Headers include `X-WP-Nonce`

---

## 🐛 If Something Doesn't Work

### Inline Editing Not Opening?

**Check:**
1. Browser console for errors (F12)
2. Run debug script (see below)
3. Verify data attributes exist on cells

**Debug Script:**
Open browser console and paste:
```javascript
// Check table
const table = document.querySelector('#the-list');
console.log('Table found:', !!table);

// Check first editable cell
const cell = document.querySelector('.column-category');
console.log('Cell found:', !!cell);
console.log('Cell data-field:', cell?.querySelector('[data-field]')?.dataset?.field);

// Check event listeners
console.log('Click listeners:', getEventListeners(table));
```

### Toast Not Appearing?

**Check:**
1. CSS file loaded: DevTools → Sources → Look for `products-table-inline-edit.css`
2. JavaScript file loaded: Look for `products-table-inline-edit.js`
3. Console for errors

### Save Not Working?

**Check:**
1. Network tab shows POST request
2. Response is 200 OK
3. Nonce is included in headers
4. User has edit permissions

### "Add New" Not Working?

**Check:**
1. Console shows API call to `/categories` or `/tags` or `/ribbons`
2. Response has `code: 'success'`
3. Cache cleared after creation
4. New item has ID in response

---

## 📊 Quick Verification Checklist

- [ ] Category cell: Click → Dropdown → Select → Auto-save → Toast
- [ ] Tags cell: Click → Multi-select → Select → Auto-save → Toast
- [ ] Ribbon cell: Click → Dropdown → Select → Auto-save → Toast
- [ ] Price cell: Click → Input → Change → Auto-save → Toast → Discount updates
- [ ] Status cell: Click → Dropdown → Select → Auto-save → Toast → Color changes
- [ ] Add New Category: Works and auto-selects
- [ ] Add New Tag: Works and auto-selects
- [ ] Add New Ribbon: Works and auto-selects
- [ ] Bulk "Publish": Works with toast
- [ ] Bulk "Move to Draft": Works with toast
- [ ] Hover effects: Pencil icon appears
- [ ] Loading state: Spinner shows during save
- [ ] Success state: Checkmark shows after save
- [ ] Error handling: Error message if save fails

---

## 🎯 Critical Test Cases

### Test Case 1: Edit Multiple Fields on Same Product
1. Edit category → Save successfully
2. Edit price → Save successfully
3. Edit status → Save successfully
4. Verify all changes persist

### Test Case 2: Create and Immediately Use New Category
1. Click category cell on Product A
2. Create new category "Electronics"
3. Verify it's auto-selected and saved
4. Click category cell on Product B
5. Verify "Electronics" appears in dropdown

### Test Case 3: Bulk Action on Mixed Status
1. Select 3 products: 2 published, 1 draft
2. Bulk action "Move to Draft"
3. Verify all 3 become drafts

### Test Case 4: Error Handling
1. Disconnect network (DevTools → Network → Offline)
2. Try to edit a cell
3. Should show error toast
4. Reconnect network
5. Try again → Should work

---

## 📞 Need Help?

If issues persist:

1. **Check Files:**
   - [docs/inline-editing-implementation-complete.md](docs/inline-editing-implementation-complete.md) - Full documentation
   - [plan/inline-editing-full-implementation-plan.md](plan/inline-editing-full-implementation-plan.md) - Original plan

2. **Debug Resources:**
   - Browser Console: F12 → Console tab
   - Network Tab: F12 → Network tab
   - PHP Errors: Check `/wp-content/debug.log`

3. **Report Bug:**
   Include:
   - Browser and version
   - Steps to reproduce
   - Console errors (screenshot)
   - Network requests (screenshot)
   - Expected vs actual behavior

---

**Happy Testing! 🎉**
