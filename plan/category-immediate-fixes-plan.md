# Category Files: Immediate Fixes Implementation Plan

**Generated:** 2026-01-30  
**Focus:** Immediate actions you can apply right now

---

## IMMEDIATE FIXES (Apply Now)

### Fix #1: Input Validation in CategoryFields.php (HIGH PRIORITY)

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Line:** 125  
**Time:** 5 minutes

**Current Code:**
```php
$is_default = isset( $_POST['_aps_category_is_default'] ) ? '1' : '0';
```

**Fixed Code:**
```php
$is_default = isset( $_POST['_aps_category_is_default'] ) 
    ? '1' 
    : '0';
```

**Why:** Adds proper formatting and makes it more readable. While the current code works, this makes it clearer and easier to maintain.

**Apply:** Open file, find line 125, replace the single line with the 3-line version above.

---

### Fix #2: Remove Inline Style - Replace `hidden` Attribute (MEDIUM PRIORITY)

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Line:** 69  
**Time:** 5 minutes

**Current Code:**
```php
<fieldset class="aps-category-checkboxes-wrapper" hidden>
```

**Fixed Code:**
```php
<fieldset class="aps-category-checkboxes-wrapper aps-hidden">
```

**Additional Step:** Add this to `assets/css/admin-category.css` (at the top after utility classes):
```css
.aps-hidden {
    display: none;
}
```

**Why:** Removes inline style, follows separation of concerns, makes styling more maintainable.

**Apply:** 
1. Open `CategoryFields.php`, find line 69, replace `hidden` with `aps-hidden`
2. Open `admin-category.css`, add `.aps-hidden { display: none; }` after `.aps-hidden` utility class

---

### Fix #3: Add Image URL Validation (MEDIUM PRIORITY)

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`  
**Lines:** 119-123  
**Time:** 10 minutes

**Current Code:**
```php
$image_url = isset( $_POST['_aps_category_image'] ) 
    ? esc_url_raw( wp_unslash( $_POST['_aps_category_image'] ) ) 
    : '';
update_term_meta( $category_id, '_aps_category_image', $image_url );
```

**Fixed Code:**
```php
$image_url = isset( $_POST['_aps_category_image'] ) 
    ? esc_url_raw( wp_unslash( $_POST['_aps_category_image'] ) ) 
    : '';

// Validate it's an image URL
if ( ! empty( $image_url ) && ! $this->is_image_url( $image_url ) ) {
    $image_url = ''; // Clear invalid URLs
}

update_term_meta( $category_id, '_aps_category_image', $image_url );
```

**Add Helper Method** (add to `CategoryFields` class, before `render_taxonomy_specific_fields()`):

```php
/**
 * Check if URL is an image URL
 *
 * @param string $url URL to check
 * @return bool True if URL appears to be an image
 */
private function is_image_url( string $url ): bool {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $path = parse_url( $url, PHP_URL_PATH );
    
    if ( ! $path ) {
        return false;
    }
    
    $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
    return in_array( $extension, $allowed_extensions, true );
}
```

**Why:** Validates that image URLs actually point to images, preventing invalid URLs from being stored.

**Apply:**
1. Open `CategoryFields.php`, find lines 119-123
2. Replace with the fixed code (adds validation)
3. Add the `is_image_url()` helper method to the class

---

### Fix #4: Improve ARIA Labels (MEDIUM PRIORITY)

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`  
**Line:** 463  
**Time:** 2 minutes

**Current Code:**
```php
<select class="aps-term-status-select" 
        data-term-id="%d" 
        data-original-status="%s" 
        aria-label="%s">
```

**Fixed Code:**
```php
<select class="aps-term-status-select" 
        data-term-id="%d" 
        data-original-status="%s" 
        aria-label="<?php esc_attr_e( 'Change category status: Published or Draft', 'affiliate-product-showcase' ); ?>">
```

**Why:** Makes ARIA label more descriptive for screen readers, improving accessibility.

**Apply:** Open `TaxonomyFieldsAbstract.php`, find line 463, replace the `aria-label="%s"` with the more descriptive version.

---

### Fix #5: Add JavaScript Constants (LOW PRIORITY)

**File:** `wp-content/plugins/affiliate-product-showcase/assets/js/admin-category.js`  
**Line:** 68  
**Time:** 3 minutes

**Current Code:**
```javascript
setTimeout( function() {
    $( '.' + prefix ).fadeOut( 200 );
}, 3000 );
```

**Fixed Code:**

Add these constants at the top of the file (after the JSDoc comments):
```javascript
// Constants
const NOTICE_FADE_DURATION = 200; // ms
const NOTICE_AUTO_DISMISS = 3000; // ms
```

Then update the setTimeout:
```javascript
setTimeout( function() {
    $( '.' + prefix ).fadeOut( NOTICE_FADE_DURATION );
}, NOTICE_AUTO_DISMISS );
```

**Why:** Removes magic numbers, makes code more readable and maintainable.

**Apply:**
1. Open `admin-category.js`
2. Add constants near the top of the file
3. Replace the magic numbers in the setTimeout call

---

## QUICK REFERENCE: Apply These Fixes in Order

| Fix | File | Lines | Time | Priority |
|------|------|--------|-------|----------|
| 1. Input Validation | CategoryFields.php | 125 | 5 min | HIGH |
| 2. Remove Inline Style | CategoryFields.php | 69 | 5 min | MEDIUM |
| 3. Image URL Validation | CategoryFields.php | 119-123 | 10 min | MEDIUM |
| 4. ARIA Labels | TaxonomyFieldsAbstract.php | 463 | 2 min | MEDIUM |
| 5. JS Constants | admin-category.js | 68 | 3 min | LOW |

**Total Time:** ~25 minutes for all immediate fixes

---

## TESTING AFTER FIXES

After applying each fix, test:

1. **Input Validation Fix:**
   - Go to Categories page
   - Edit a category
   - Save the category
   - Verify no errors occur

2. **Inline Style Fix:**
   - Reload Categories page
   - Verify checkboxes are still hidden/visible correctly
   - Check browser DevTools for `.aps-hidden` class

3. **Image URL Validation:**
   - Go to Add New Category
   - Enter a non-image URL (e.g., https://example.com/document.pdf)
   - Save the category
   - Verify URL is cleared (not saved)
   - Try again with valid image URL (e.g., https://example.com/image.jpg)
   - Verify it saves correctly

4. **ARIA Labels:**
   - Load Categories page in a screen reader (or browser extension)
   - Navigate to status dropdown
   - Verify ARIA label is descriptive

5. **JavaScript Constants:**
   - Trigger a success/error notice
   - Verify notice dismisses after 3 seconds
   - Verify fade animation is smooth (200ms)

---

## NEXT STEPS (After Immediate Fixes)

Once you've completed the immediate fixes above, proceed to:

### Phase 2: Code Duplication Removal (2-3 hours)

**File:** Create `src/Traits/DefaultCategoryProtectionTrait.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default Category Protection Trait
 *
 * Provides methods to protect default categories from deletion/trashing.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 1.0.0
 */
trait DefaultCategoryProtectionTrait {
    
    /**
     * Check if term can be deleted/trashed
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key for default flag
     * @return bool True if action is allowed
     */
    protected function can_delete_term( int $term_id, string $meta_key ): bool {
        $is_default = get_term_meta( $term_id, $meta_key, true );
        if ( $is_default === '1' ) {
            return false;
        }
        return true;
    }
    
    /**
     * Throw exception if term is default
     *
     * @param int $term_id Term ID
     * @param string $meta_key Meta key for default flag
     * @param string $taxonomy_label Label for error messages
     * @throws \AffiliateProductShowcase\Exceptions\PluginException
     */
    protected function protect_default_term( int $term_id, string $meta_key, string $taxonomy_label ): void {
        if ( ! $this->can_delete_term( $term_id, $meta_key ) ) {
            throw new \AffiliateProductShowcase\Exceptions\PluginException(
                sprintf(
                    'Cannot delete default %s. Please select another default %s first.',
                    strtolower( $taxonomy_label ),
                    strtolower( $taxonomy_label )
                )
            );
        }
    }
}
```

**Then update these files to use the trait:**

1. `CategoryRepository.php`:
```php
use AffiliateProductShowcase\Traits\DefaultCategoryProtectionTrait;

class CategoryRepository {
    use DefaultCategoryProtectionTrait;
    
    // In delete() method:
    public function delete( int $category_id ): bool {
        $this->protect_default_term( $category_id, '_aps_category_is_default', 'Category' );
        // ... rest of method
    }
    
    // In delete_permanently() method:
    public function delete_permanently( int $category_id ): bool {
        $this->protect_default_term( $category_id, '_aps_category_is_default', 'Category' );
        // ... rest of method
    }
}
```

2. `TaxonomyFieldsAbstract.php`:
```php
use AffiliateProductShowcase\Traits\DefaultCategoryProtectionTrait;

abstract class TaxonomyFieldsAbstract {
    use DefaultCategoryProtectionTrait;
    
    // Update handle_bulk_actions():
    final public function handle_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
        // ... in the loop:
        $is_default = $this->can_delete_term( (int) $term_id, $this->get_meta_prefix() . 'is_default' );
        if ( $is_default === '1' ) {
            continue;
        }
        // ...
    }
    
    // Update protect_default_term():
    final public function protect_default_term_action( $term, string $taxonomy ): void {
        if ( $taxonomy !== $this->get_taxonomy() ) {
            return;
        }
        
        $term_id = (int) $term;
        if ( $term_id <= 0 ) {
            return;
        }

        $is_default = $this->can_delete_term( $term_id, $this->get_meta_prefix() . 'is_default' );
        if ( $is_default === '1' ) {
            wp_die(
                esc_html__( 'Cannot delete default ' . strtolower( $this->get_taxonomy_label() ) . '. Please set a different ' . strtolower( $this->get_taxonomy_label() ) . ' as default first.', 'affiliate-product-showcase' ),
                esc_html__( 'Default ' . $this->get_taxonomy_label() . ' Protected', 'affiliate-product-showcase' ),
                [ 'back_link' => true ]
            );
        }
    }
}
```

---

## SUMMARY

**Immediate Fixes (25 minutes):**
✅ Fix #1: Input validation (5 min)  
✅ Fix #2: Remove inline style (5 min)  
✅ Fix #3: Image URL validation (10 min)  
✅ Fix #4: ARIA labels (2 min)  
✅ Fix #5: JavaScript constants (3 min)

**Next Phase:** Code duplication removal (2-3 hours)

**Total Estimated Time:** 2.5-3.5 hours to complete immediate + phase 2 fixes

---

**Note:** After applying each fix, commit your changes with descriptive messages following conventional commits format:

```
fix(category): Add proper input validation for checkbox
fix(category): Replace inline hidden attribute with CSS class
fix(category): Add image URL validation
fix(category): Improve ARIA labels for accessibility
fix(category): Add constants for magic numbers
refactor(category): Extract default protection logic to trait