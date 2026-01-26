# Category Management Debug Plan

## Issues Reported
1. Cancel button not appearing on edit page
2. Status edit not working
3. Category move to Draft not working
4. Category move to Trash not working
5. Delete button not working
6. No notification after status update

## Diagnostic Steps

### Step 1: Verify JavaScript Loading
**Check:** Is `admin-category.js` being loaded on category pages?

**Methods:**
1. Check browser DevTools Network tab → Look for `admin-category.js`
2. Check browser Console for 404 errors
3. Inspect `<script>` tags in page source

**Possible Issues:**
- Wrong asset URL path (nested folder structure issue)
- File not enqueued on correct screen
- Wrong dependency loading order

**Debug Code Needed:**
```javascript
console.log('admin-category.js loaded');
console.log('aps_admin_vars:', typeof aps_admin_vars !== 'undefined' ? aps_admin_vars : 'NOT DEFINED');
```

### Step 2: Verify AJAX Handler Registration
**Check:** Is `aps_toggle_category_status` action registered?

**Methods:**
1. Check browser Network tab → Look for `admin-ajax.php` requests
2. Check browser Console for AJAX errors
3. Verify action name matches

**Debug Code Needed:**
```php
error_log('APS: aps_toggle_category_status action registered');
```

### Step 3: Verify Bulk Action Handlers
**Check:** Are bulk actions registered and working?

**Methods:**
1. Check if bulk actions appear in dropdown
2. Submit bulk action and check Network tab
3. Verify handler receives POST data

**Debug Code Needed:**
```php
error_log('APS: Bulk action received: ' . $_POST['action']);
error_log('APS: Bulk action handler: ' . $_POST['action2']);
```

### Step 4: Verify Cancel Button Script
**Check:** Is Cancel button JavaScript running?

**Methods:**
1. Check browser Console for JS errors
2. Verify `admin_footer-term.php` hook fires
3. Check if `#edittag` element exists

**Debug Code Needed:**
```javascript
console.log('Cancel button script running');
console.log('#edittag found:', $('#edittag').length);
console.log('p.submit found:', $('#edittag').find('p.submit').length);
```

### Step 5: Verify Screen Detection
**Check:** Is `enqueue_admin_assets()` detecting correct screen?

**Methods:**
1. Add console log to check screen object
2. Verify `taxonomy === 'aps_category'` condition

**Debug Code Needed:**
```php
error_log('APS: Screen object: ' . print_r($screen, true));
error_log('APS: Screen taxonomy: ' . ($screen ? $screen->taxonomy : 'NO SCREEN'));
```

### Step 6: Verify WordPress Taxonomy Registration
**Check:** Is `aps_category` taxonomy registered correctly?

**Methods:**
1. Check if taxonomy exists: `get_taxonomy('aps_category')`
2. Verify edit tags page loads correctly
3. Check if custom columns are added

**Debug Code Needed:**
```php
error_log('APS: Taxonomy registered: ' . (taxonomy_exists('aps_category') ? 'YES' : 'NO'));
```

## Root Cause Analysis

### Scenario 1: JavaScript Not Loading
**Symptoms:** No console errors, but features don't work
**Root Cause:** Wrong asset URL or enqueuing on wrong screen
**Fix:** Correct `Constants::assetUrl()` or screen detection

### Scenario 2: AJAX Handler Not Registered
**Symptoms:** 400 Bad Request or 0 response from AJAX
**Root Cause:** Action name mismatch or hook priority issue
**Fix:** Verify action name string matches exactly

### Scenario 3: Selector Not Found
**Symptoms:** JavaScript errors in console
**Root Cause:** DOM element doesn't exist when script runs
**Fix:** Use event delegation or defer script execution

### Scenario 4: Bulk Action Name Mismatch
**Symptoms:** Bulk actions not appearing or not working
**Root Cause:** WordPress bulk action filter hook issue
**Fix:** Verify filter hook and action handling

## Debug Implementation

### Add Debug Logging to CategoryFields

**Location:** `src/Admin/CategoryFields.php`

**Add to `init()` method:**
```php
error_log('APS: CategoryFields::init() called');
error_log('APS: Current screen: ' . (get_current_screen() ? get_current_screen()->id : 'NULL'));
```

**Add to `enqueue_admin_assets()`:**
```php
error_log('APS: enqueue_admin_assets() called');
error_log('APS: Screen taxonomy: ' . ($screen ? $screen->taxonomy : 'NO SCREEN'));
error_log('APS: Should enqueue: ' . (($screen && $screen->taxonomy === 'aps_category') ? 'YES' : 'NO'));
```

**Add to `ajax_toggle_category_status()`:**
```php
error_log('APS: AJAX request received');
error_log('APS: POST data: ' . print_r($_POST, true));
```

### Add Debug Logging to JavaScript

**Location:** `assets/js/admin-category.js`

**Add at top of file:**
```javascript
console.log('=== APS Category Debug ===');
console.log('admin-category.js loaded');
console.log('aps_admin_vars:', typeof aps_admin_vars !== 'undefined' ? aps_admin_vars : 'NOT DEFINED');
```

**Add to status change handler:**
```javascript
console.log('Status change detected');
console.log('Term ID:', termId);
console.log('New status:', newStatus);
console.log('Original status:', originalStatus);
```

## Verification Checklist

After implementing debug code:

- [ ] Check WordPress debug.log for initialization messages
- [ ] Check browser console for JavaScript debug messages
- [ ] Check Network tab for AJAX requests
- [ ] Verify asset files are loaded (200 status, not 404)
- [ ] Verify screen detection is correct
- [ ] Verify AJAX action names match
- [ ] Verify DOM elements exist when scripts run

## Next Steps After Debug

1. Analyze debug.log output
2. Identify where the chain breaks
3. Fix the specific issue (not guess)
4. Remove debug code
5. Test thoroughly