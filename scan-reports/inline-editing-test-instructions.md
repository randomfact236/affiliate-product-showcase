# Inline Editing Testing Instructions

**Created:** 2026-01-27  
**Purpose:** Debug and test inline editing functionality

---

## Step 1: Clear Browser Cache

Before testing, clear your browser cache to ensure you're loading the latest JavaScript file.

**Chrome/Edge:**
- Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
- Or press `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac) for hard refresh

**Firefox:**
- Press `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
- Or press `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac) for hard refresh

---

## Step 2: Navigate to Products Page

1. Log in to WordPress admin
2. Navigate to: **Affiliate Product Showcase → All Products**
3. Wait for the page to fully load

---

## Step 3: Open Browser DevTools

1. Press `F12` (or right-click → Inspect)
2. Click on the **Console** tab
3. Look for initialization messages

### Expected Console Messages

You should see these messages (in order):
```
[APS Inline Edit] Table found, initializing...
[APS Inline Edit] Event listeners attached
[APS Inline Edit] Initialized successfully
```

### If You See Errors

**Error 1:** `[APS Inline Edit] Table not found: #the-list`
- **Problem:** Table selector is wrong or table hasn't rendered yet
- **Action:** Run debug script (Step 4) to investigate

**Error 2:** `[APS Inline Edit] Cannot attach listeners: table not found`
- **Problem:** MutationObserver detected table update but table is now missing
- **Action:** Refresh page and try again

**Error 3:** `[APS Inline Edit] Could not determine cell type: <td>`
- **Problem:** Cell doesn't have expected class or data attribute
- **Action:** Run debug script to check cell structure

---

## Step 4: Run Debug Script

### Method A: Copy-Paste (Recommended)

1. Open `wp-content/plugins/affiliate-product-showcase/assets/js/inline-edit-debug.js`
2. Copy all the code (Ctrl+A, Ctrl+C)
3. Paste into browser console (Ctrl+V)
4. Press Enter

### Method B: Load via Script Tag (Advanced)

Add this to your browser console:
```javascript
const script = document.createElement('script');
script.src = '/wp-content/plugins/affiliate-product-showcase/assets/js/inline-edit-debug.js';
document.head.appendChild(script);
```

### Debug Script Output

The debug script will show:

**1. Table Detection:**
```
=== APS Inline Edit Debug ===
1. Table found: true ✅
✅ Table ID: the-list
✅ Table classes: posts type-aps_product
```

**2. Row Detection:**
```
2. Rows found: 10
✅ First row: <tr>
✅ Checkbox found: true ✅
✅ Product ID from checkbox: 123
```

**3. Cell Detection:**
```
3. Cells found: 10
Cell 0: {
  class: "cb column-cb",
  hasDataField: false,
  dataField: undefined,
  text: ""
}
Cell 4: {
  class: "column-category",
  hasDataField: false,
  dataField: undefined,
  text: "Electronics"
}
```

**4. Editable Cells Check:**
```
4. Checking editable cells:
category: 10 cells found
  - data-field: undefined
  - class: column-category
tags: 10 cells found
  - data-field: undefined
  - class: column-tags
ribbon: 10 cells found
  - data-field: undefined
  - class: column-ribbon
price: 10 cells found
  - data-field: undefined
  - class: column-price
status: 10 cells found
  - data-field: undefined
  - class: column-status
```

**5. Script Loading:**
```
5. Script loading:
✅ apsInlineEditData exists
  - restUrl: https://yoursite.com/wp-json/affiliate-product-showcase/v1/
  - nonce: ✅
```

**6. Click Test:**
```
6. Adding test click listener...
✅ Test click listener added

=== Click on any cell to see debug info ===
=== Debug complete ===
```

### Click on Cells

After running debug script, click on any table cell. You should see:
```
🖱️ Cell clicked: {
  class: "column-category",
  dataField: undefined,
  text: "Electronics"
}
```

---

## Step 5: Test Inline Editing

If initialization is successful, try clicking on editable cells:

### Test Category Column
1. Click on any category cell (e.g., "Electronics")
2. **Expected:** Dropdown appears with categories + "Add New" option
3. **Actual:** What happens?

### Test Tags Column
1. Click on any tags cell
2. **Expected:** Multi-select appears with checkboxes for tags + "Add New" button
3. **Actual:** What happens?

### Test Ribbon Column
1. Click on any ribbon cell
2. **Expected:** Dropdown appears with ribbons + "None" + "Add New" option
3. **Actual:** What happens?

### Test Price Column
1. Click on any price cell
2. **Expected:** Input field appears with currency symbol and discount preview
3. **Actual:** What happens?

### Test Status Column
1. Click on any status cell
2. **Expected:** Dropdown appears with "Published" and "Draft" options
3. **Actual:** What happens?

---

## Step 6: Document Results

### If Inline Editing Works ✅

**Success!** Document what works:

```
✅ Script initializes successfully
✅ Event listeners attached
✅ Click on category cell → opens dropdown
✅ Click on tags cell → opens multi-select
✅ Click on ribbon cell → opens dropdown
✅ Click on price cell → opens input field
✅ Click on status cell → opens dropdown
✅ Changes auto-save when clicking away
✅ Success indicator appears
```

### If Inline Editing Doesn't Work ❌

**Debug Output:** Copy and paste the debug script output (from Step 4)

**Console Errors:** Copy any red error messages from console

**Click Behavior:** Describe what happens when you click on cells
- Does nothing happen?
- Does the cell highlight?
- Does an error appear in console?
- Does the page reload?

---

## Common Issues & Solutions

### Issue 1: "Table not found" Error

**Symptom:** Console shows `[APS Inline Edit] Table not found`

**Cause:** Table selector `#the-list` doesn't exist

**Solutions:**

1. Check if table has different ID:
```javascript
// Run in console
document.querySelectorAll('table').forEach(t => console.log(t.id, t.className));
```

2. Wait longer for table to render (increase timeout in init function)

3. Table might be inside an iframe (unlikely but possible)

### Issue 2: "Could not determine cell type" Warning

**Symptom:** Console shows warning when clicking on cells

**Cause:** Cells don't have expected class names

**Solutions:**

1. Check actual cell classes (debug script shows this)
2. Update `getCellType()` function with correct class names
3. Add data attributes to cells (Phase 1)

### Issue 3: Click Events Not Firing

**Symptom:** No reaction when clicking on cells

**Cause:** Event listener not attached or event delegation issue

**Solutions:**

1. Verify event listener is attached:
```javascript
// Run in console
const table = document.querySelector('#the-list');
console.log('Has click listener:', table?.onclick !== null);
```

2. Check if something is stopping propagation:
```javascript
// Run in console
document.addEventListener('click', (e) => {
    console.log('Document clicked:', e.target);
});
```

### Issue 4: Script Not Loading

**Symptom:** `apsInlineEditData` is undefined

**Cause:** Script not enqueued or localized incorrectly

**Solutions:**

1. Check script tag in page source:
```html
<!-- Look for this in page source -->
<script id="affiliate-product-showcase-products-table-inline-edit-js-extra">
var apsInlineEditData = {"restUrl":"...","nonce":"..."};
</script>
```

2. Check if script file exists:
```bash
# In terminal
ls -la wp-content/plugins/affiliate-product-showcase/assets/js/products-table-inline-edit.js
```

3. Clear cache (server and browser)

---

## Phase 1: Add Data Attributes (HIGH PRIORITY)

After debugging, the next step is to add data attributes to cells. This makes cell detection more reliable.

**Files to Modify:**
- `src/Admin/ProductsTable.php`

**Changes Needed:**
1. Add `data-field="category"` to category cells
2. Add `data-field="tags"` to tags cells
3. Add `data-field="ribbon"` to ribbon cells
4. Add `data-field="price"` + `data-price`, `data-original-price`, `data-currency` to price cells
5. Add `data-field="status"` + `data-status` to status cells
6. Add `data-product-id` to all cells (from row checkbox)

---

## Reporting Back

When reporting results, include:

1. **Debug Script Output:** Full output from Step 4
2. **Console Messages:** Any error/warning messages
3. **Click Behavior:** What happens when clicking on cells
4. **Screenshot (optional):** If something visual is happening

**Report Template:**

```markdown
## Test Results

### Debug Script Output
[Paste debug script output here]

### Console Messages
[Paste any console errors/warnings here]

### Click Behavior
- Category: [What happens?]
- Tags: [What happens?]
- Ribbon: [What happens?]
- Price: [What happens?]
- Status: [What happens?]

### Additional Notes
[Any other observations]
```

---

**Next Steps After Testing:**

- ✅ If working: Proceed to Phase 1 (add data attributes)
- ⚠️ If partially working: Fix specific issues
- ❌ If not working: Use debug output to identify root cause