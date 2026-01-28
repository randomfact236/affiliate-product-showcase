# Ribbon Background Colors - Implementation Verification

**Date:** January 28, 2026  
**Status:** Verification Required

---

## What Should Be Implemented

### Files Modified

1. **RibbonFields.php** (`src/Admin/RibbonFields.php`)
   - Added background color field to form
   - Added background color save to database
   - Added background color column to table

2. **admin-ribbon.js** (`assets/js/admin-ribbon.js`)
   - Added live preview functionality
   - Added color preset buttons
   - Added automatic text contrast calculation

3. **admin-ribbon.css** (`assets/css/admin-ribbon.css`)
   - Added styles for background color picker
   - Added styles for color presets
   - Added styles for live preview
   - Added styles for background color column

---

## Expected Content Verification

### RibbonFields.php Should Contain:

```php
// In render_taxonomy_specific_fields():
$bg_color = get_term_meta( $ribbon_id, '_aps_ribbon_bg_color', true ) ?: '#ff0000';
<input type="color" name="aps_ribbon_bg_color" id="aps_ribbon_bg_color" class="aps-bg-color-picker"/>
<div class="color-presets">...8 preset buttons...</div>
<div class="ribbon-live-preview" id="ribbon-preview-container">...preview...</div>

// In save_taxonomy_specific_fields():
if ( isset( $_POST['aps_ribbon_bg_color'] ) ) {
    $bg_color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_bg_color'] ) );
    if ( $bg_color ) {
        update_term_meta( $ribbon_id, '_aps_ribbon_bg_color', $bg_color );
    }
}

// In add_custom_columns():
if ( $key === 'slug' ) {
    $new_columns['color'] = __( 'Color', 'affiliate-product-showcase' );
    $new_columns['bg_color'] = __( 'Background', 'affiliate-product-showcase' );
}

// In render_custom_columns():
if ( $column_name === 'bg_color' ) {
    $bg_color = get_term_meta( $term_id, '_aps_ribbon_bg_color', true );
    // Render swatch...
}
```

### admin-ribbon.js Should Contain:

```javascript
// New functions:
function initRibbonPreview() { ... }
function updateRibbonPreview() { ... }
function calculateContrastColor(bgColor) { ... }

// Event listeners:
$colorInput.on('input', function() { updateRibbonPreview(); });
$bgColorPicker.on('input', function() { ...sync and update... });
$bgColorText.on('input', function() { ...validate and sync... });
$presetButtons.on('click', function() { ...apply color... });

// Page load:
$(document).ready(function() {
    if ($('body').hasClass('taxonomy-aps_ribbon')) {
        initRibbonPreview();
    }
});
```

### admin-ribbon.css Should Contain:

```css
/* Background color field */
.ribbon-bg-color-wrapper { display: flex; align-items: center; gap: 10px; }
.aps-bg-color-picker { width: 50px; height: 35px; border: 1px solid #ddd; border-radius: 4px; }

/* Color presets */
.color-presets { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; }
.preset-color { width: 35px; height: 35px; border: 2px solid transparent; border-radius: 50%; }
.preset-color:hover { transform: scale(1.1); }
.preset-color.active { border-color: #0073aa; }
.color-swatch { width: 100%; height: 100%; }

/* Live preview */
.ribbon-live-preview { margin-top: 25px; padding: 20px; border: 2px solid #e0e0e; }
.preview-label { display: block; font-size: 12px; font-weight: 600; color: #444; }
.ribbon-preview-badge { display: inline-block; padding: 8px 16px; border-radius: 4px; font-weight: bold; }

/* Background color column */
.aps-ribbon-bg-color-swatch { display: inline-block; width: 20px; height: 20px; border-radius: 4px; border: 2px solid #ccc; }
.aps-ribbon-bg-color-empty { color: #999; font-style: italic; }

/* Responsive */
@media (max-width: 768px) { ... }
@media (max-width: 480px) { ... }
```

---

## Manual Verification Checklist

Please check if the following is present in your files:

### RibbonFields.php
- [ ] Field label changed to "Text Color" (not just "Color")
- [ ] Background color field exists with input type="color"
- [ ] Background color field exists with input type="text"
- [ ] Color presets div exists with 8 preset buttons
- [ ] Live preview container exists with id="ribbon-preview-container"
- [ ] Preview badge exists with id="ribbon-preview"
- [ ] Save method handles 'aps_ribbon_bg_color' POST
- [ ] add_custom_columns() adds 'bg_color' column
- [ ] render_custom_columns() handles 'bg_color' column case

### admin-ribbon.js
- [ ] initRibbonPreview() function exists
- [ ] updateRibbonPreview() function exists
- [ ] calculateContrastColor() function exists
- [ ] Event listeners for $colorInput.on('input')
- [ ] Event listeners for $bgColorPicker.on('input')
- [ ] Event listeners for $bgColorText.on('input')
- [ ] Event listeners for $presetButtons.on('click')
- [ ] $(document).ready() check for taxonomy-aps_ribbon
- [ ] initRibbonPreview() called on page load

### admin-ribbon.css
- [ ] .ribbon-bg-color-wrapper class exists
- [ ] .aps-bg-color-picker class exists
- [ ] .color-presets class exists
- [ ] .preset-color class exists
- [ ] .preset-color:hover exists
- [ ] .preset-color.active exists
- [ ] .color-swatch class exists
- [ ] .ribbon-live-preview class exists
- [ ] .preview-label class exists
- [ ] .ribbon-preview-badge class exists
- [ ] .aps-ribbon-bg-color-swatch class exists
- [ ] .aps-ribbon-bg-color-empty class exists
- [ ] Responsive media queries exist
- [ ] No CSS syntax errors (check for missing brackets, semicolons, etc.)

---

## Known Potential Issues

If you see errors, they might be:

1. **PHP Parse Error:**
   - Error: "syntax error, unexpected ... in RibbonFields.php"
   - Check for: Missing semicolon, unmatched brackets, unclosed strings

2. **JavaScript Error:**
   - Error: "initRibbonPreview is not defined" or similar
   - Check for: Function exists before use, correct scope

3. **CSS Error:**
   - Error: "Unexpected token" or "at-rule or selector expected"
   - Check for: Malformed CSS rule, missing closing bracket

---

## Testing Instructions

### 1. Navigate to Ribbon Page
   - Go to WordPress Admin
   - Navigate to Products → Ribbons
   - Click "Add New Ribbon" or edit existing

### 2. Check Admin Form
   - Scroll down to form fields
   - Should see "Text Color" and "Background Color" labels
   - Should see color picker (native HTML5)
   - Should see text input for hex codes
   - Should see 8 preset color buttons
   - Should see live preview area showing "SALE" badge

### 3. Open Browser Console
   - Right-click → Inspect
   - Go to Console tab
   - Reload page
   - Look for:
     - Red error messages
     - JavaScript errors (yellow)
     - "initRibbonPreview is not defined" errors

### 4. Check Network Tab (if visible)
   - Look for failed requests
   - Look for 404/500 errors
   - Check if AJAX requests fire

---

## What to Report

If you see errors, please provide:

1. **File Path:** Which file shows the error?
2. **Error Message:** Exact error text you see
3. **Line Number:** If provided, which line has the error?
4. **Browser:** Which browser are you using?
5. **Steps:** What were you doing when error occurred?

---

## Quick Test

Try this simple test to verify implementation works:

1. **Edit a Ribbon**
   - Click "Edit" on any existing ribbon
   - Change background color to green using color picker
   - Click "Update"

2. **Check Result**
   - Did the ribbon save without errors?
   - Do you see the green color in the ribbon list?

3. **Check Console**
   - Any JavaScript errors?

---

**Status:** Awaiting user feedback on specific errors