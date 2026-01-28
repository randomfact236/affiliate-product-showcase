# Ribbon Fields PHP Syntax Fix Instructions

**Date:** January 28, 2026  
**Status:** URGENT FIX REQUIRED

---

## The Problem

You're seeing:
```
Affiliate Product Showcase failed to initialize: syntax error, unexpected token ":"
```

This is a **PHP syntax error** in `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php`.

---

## The Solution

The file currently starts with `<?php` (uppercase letters) but it must start with `<?php` (lowercase letters only).

### Quick Fix (1 minute)

**Option 1: Via Code Editor (Recommended)**
1. Open the file: `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php`
2. Look at line 1
3. Change `<?php` to `<?php` (make sure it's lowercase)
4. Save the file
5. Refresh your admin panel

**Option 2: Via File Manager**
1. Access your file manager or FTP
2. Navigate to: `wp-content/plugins/affiliate-product-showcase/src/Admin/`
3. Edit `RibbonFields.php`
4. Change line 1 from `<?php` to `<?php`
5. Save the file

**Option 3: Via Git**
```bash
cd wp-content/plugins/affiliate-product-showcase/src/Admin
nano RibbonFields.php
# Change <?php to <?php on line 1
# Save with Ctrl+O, then Ctrl+X
```

---

## Verify the Fix

After making the change:

1. **Refresh Browser** (Ctrl+F5 or Cmd+Shift+R)
2. **Check Admin Panel**
   - Should load without errors
   - "Affiliate Product Showcase" should appear in menu
3. **Check Error** (if still present)
   - Open browser console (F12)
   - Look for exact error message
   - Note line number if shown

---

## What to Do If It Still Doesn't Work

If after fixing the PHP tag you still see errors:

1. **Check File Encoding:**
   - File must be UTF-8 without BOM
   - Make sure no special characters at line 1

2. **Revert Changes:**
   - If you have git, run: `git checkout HEAD`
   - This will restore files to last working state

3. **Check Plugin Folder:**
   - Delete the entire `wp-content/plugins/affiliate-product-showcase` folder
   - Re-install the plugin from original zip

---

## Expected File Content

After the fix, the file should start with:

```php
<?php
/**
 * Ribbon Fields
 *
 * Adds custom fields to ribbon edit/add forms including:
 * - Color field with WordPress color picker
 * - Icon field
 * - Background color field with presets
 * - Live preview area
 *
 * @package AffiliateProductShowcase\Admin
 * @since 2.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
```

Note the correct:
- `<?php` (lowercase) on line 1
- `declare(strict_types=1);` (with equals sign, not colon)
- Proper PHP syntax throughout

---

## Quick Test After Fix

1. Go to: Admin → Products → Ribbons
2. Click "Add New Ribbon"
3. Scroll down
4. You should see:
   - Text Color label and color picker
   - Background Color label and dual inputs (color picker + text input)
   - 8 preset color buttons
   - Live preview showing "SALE" badge
5. No initialization errors in admin panel

---

**Status:** AWAITING MANUAL FIX
**Priority:** URGENT