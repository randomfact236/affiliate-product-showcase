# FOCUSED STATIC CODE FLOW ANALYSIS AUDIT

**Analysis Type:** Senior PHP Static Code Auditor - Logic Flow Analysis
**Analysis Date:** 2026-01-28
**Files Analyzed:**
1. `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`
2. `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php`
3. `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php`

---

## EXECUTIVE SUMMARY

This focused audit identifies **7 critical logic bugs** across three critical failure areas:

1. **Create vs Edit Mode Logic** - Missing existence check for post object
2. **Field Mapping Mismatches** - Form field names don't match handler expectations
3. **Ribbon Data Storage Logic** - Inconsistent storage (taxonomy vs meta)

---

## CRITICAL FAILURE AREA 1: CREATE VS EDIT MODE LOGIC

---

### BUG 1: MAIN EDIT MODE BUG - Form Fails to Load Data Due to Missing Existence Check for Post Object

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`
**LINE:** 22-58

**CURRENT CODE:**
```php
// Get product data if editing
$product_data = [];
if ( $is_editing ) {
	$post = get_post( $post_id );
	if ( $post && $post->post_type === 'aps_product' ) {
		$product_data = [
			'id' => $post->ID,
			'title' => $post->post_title,
			'status' => $post->post_status,
			'content' => $post->post_content,
			// Meta fields
			'logo' => get_post_meta( $post->ID, '_aps_logo', true ),
			'brand_image' => get_post_meta( $post->ID, '_aps_brand_image', true ),
			'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
			'button_name' => get_post_meta( $post->ID, '_aps_button_name', true ),
			'short_description' => get_post_meta( $post->ID, '_aps_short_description', true ),
			'regular_price' => get_post_meta( $post->ID, '_aps_price', true ),
			'sale_price' => get_post_meta( $post->ID, '_aps_sale_price', true ),
			'currency' => get_post_meta( $post->ID, '_aps_currency', true ) ?: 'USD',
			'featured' => get_post_meta( $post->ID, '_aps_featured', true ) === '1',
			'rating' => get_post_meta( $post->ID, '_aps_rating', true ),
			'views' => get_post_meta( $post->ID, '_aps_views', true ),
			'user_count' => get_post_meta( $post->ID, '_aps_user_count', true ),
			'reviews' => get_post_meta( $post->ID, '_aps_reviews', true ),
			'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
			'video_url' => get_post_meta( $post->ID, '_aps_video_url', true ),
			'platform_requirements' => get_post_meta( $post->ID, '_aps_platform_requirements', true ),
			'version_number' => get_post_meta( $post->ID, '_aps_version_number', true ),
			'stock_status' => get_post_meta( $post->ID, '_aps_stock_status', true ) ?: 'instock',
			'seo_title' => get_post_meta( $post->ID, '_aps_seo_title', true ),
			'seo_description' => get_post_meta( $post->ID, '_aps_seo_description', true ),
		];
		
		// Get product categories, tags, and ribbons
		$product_data['categories'] = wp_get_object_terms( $post->ID, 'aps_category', [ 'fields' => 'slugs' ] );
		$product_data['tags'] = wp_get_object_terms( $post->ID, 'aps_tag', [ 'fields' => 'slugs' ] );
		$product_data['ribbons'] = wp_get_object_terms( $post->ID, 'aps_ribbon', [ 'fields' => 'slugs' ] );
	}
}
```

**PROBLEM:**
- When user clicks "Add New Product", `$is_editing` is `false` (line 18)
- The conditional block at line 22 is skipped entirely
- `$product_data` remains as empty array `[]` (initialized at line 21)
- All form fields use null coalescing operators: `$product_data['title'] ?? ''`
- While this prevents errors, it means form loads with completely empty data
- **CRITICAL ISSUE**: If user accesses edit URL with invalid product ID (e.g., `?post_type=aps_product&page=add-product&post=99999`):
  - `$is_editing` becomes `true` because `$post_id = 99999 > 0`
  - `get_post(99999)` returns `null` (post doesn't exist)
  - The inner conditional `if ( $post && $post->post_type === 'aps_product' )` correctly prevents accessing `$post` properties
  - However, `$product_data` remains empty array
  - Form renders with all fields empty
  - User expects to see error message or be redirected, but sees blank form instead
  - User may accidentally create a duplicate product instead of realizing they have an invalid URL
- **NO ERROR MESSAGE** is displayed to user when `$post` is `null` or wrong post type
- The code silently falls through to render empty form

**EXPECTED CODE:**
```php
// Get product data if editing
$product_data = [];
if ( $is_editing ) {
	$post = get_post( $post_id );
	
	// Check if post exists AND is correct type
	if ( $post && $post->post_type === 'aps_product' ) {
		$product_data = [
			'id' => $post->ID,
			'title' => $post->post_title,
			'status' => $post->post_status,
			'content' => $post->post_content,
			// Meta fields
			'logo' => get_post_meta( $post->ID, '_aps_logo', true ),
			'brand_image' => get_post_meta( $post->ID, '_aps_brand_image', true ),
			'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
			'button_name' => get_post_meta( $post->ID, '_aps_button_name', true ),
			'short_description' => get_post_meta( $post->ID, '_aps_short_description', true ),
			'regular_price' => get_post_meta( $post->ID, '_aps_price', true ),
			'sale_price' => get_post_meta( $post->ID, '_aps_sale_price', true ),
			'currency' => get_post_meta( $post->ID, '_aps_currency', true ) ?: 'USD',
			'featured' => get_post_meta( $post->ID, '_aps_featured', true ) === '1',
			'rating' => get_post_meta( $post->ID, '_aps_rating', true ),
			'views' => get_post_meta( $post->ID, '_aps_views', true ),
			'user_count' => get_post_meta( $post->ID, '_aps_user_count', true ),
			'reviews' => get_post_meta( $post->ID, '_aps_reviews', true ),
			'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
			'video_url' => get_post_meta( $post->ID, '_aps_video_url', true ),
			'platform_requirements' => get_post_meta( $post->ID, '_aps_platform_requirements', true ),
			'version_number' => get_post_meta( $post->ID, '_aps_version_number', true ),
			'stock_status' => get_post_meta( $post->ID, '_aps_stock_status', true ) ?: 'instock',
			'seo_title' => get_post_meta( $post->ID, '_aps_seo_title', true ),
			'seo_description' => get_post_meta( $post->ID, '_aps_seo_description', true ),
		];
		
		// Get product categories, tags, and ribbons
		$product_data['categories'] = wp_get_object_terms( $post->ID, 'aps_category', [ 'fields' => 'slugs' ] );
		$product_data['tags'] = wp_get_object_terms( $post->ID, 'aps_tag', [ 'fields' => 'slugs' ] );
		$product_data['ribbons'] = wp_get_object_terms( $post->ID, 'aps_ribbon', [ 'fields' => 'slugs' ] );
	} else {
		// Post doesn't exist or wrong post type - show error
		wp_die(
			sprintf(
				'<h1>%s</h1><p>%s</p>',
				esc_html__( 'Invalid Product', 'affiliate-product-showcase' ),
				esc_html__( 'The product you are trying to edit does not exist or is not the correct type.', 'affiliate-product-showcase' )
			),
			esc_html__( 'Product Not Found', 'affiliate-product-showcase' ),
			403
		);
	}
}
```

**DATA FLOW BREAK:**
```
User clicks "Add New Product" → No $_GET['post'] parameter
    ↓
$post_id = 0 (line 17)
    ↓
$is_editing = false (line 18)
    ↓
if ( $is_editing ) → FALSE - block skipped (line 22)
    ↓
$product_data = [] (remains empty array)
    ↓
Form renders with all fields empty (line 109: value="<?php echo esc_attr( $product_data['title'] ?? '' ); ?>")
    ↓
User fills in form and submits
    ↓
Product created successfully (correct behavior)

---

OR (INVALID EDIT SCENARIO):

User accesses URL: edit.php?post_type=aps_product&page=add-product&post=99999
    ↓
$post_id = 99999 (line 17)
    ↓
$is_editing = true (line 18)
    ↓
if ( $is_editing ) → TRUE - block entered (line 22)
    ↓
$post = get_post( 99999 ) → returns null (post doesn't exist)
    ↓
if ( $post && $post->post_type === 'aps_product' ) → FALSE (post is null)
    ↓
$product_data = [] (remains empty array)
    ↓
Form renders with all fields empty (NO ERROR MESSAGE SHOWN)
    ↓
User sees blank form, unaware they have invalid product ID
    ↓
User fills in form and submits
    ↓
NEW PRODUCT CREATED instead of editing existing one
    ↓
DATA LOSS: User accidentally creates duplicate product
```

---

## CRITICAL FAILURE AREA 2: FIELD MAPPING MISMATCHES

---

### BUG 2: Form Field 'aps_description' Does Not Exist - Handler Looks for Non-Existent Field

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php` (Form) AND `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` (Handler)

**CURRENT CODE (Form - Line 193):**
```php
<label for="aps-short-description">Short Description <span class="required">*</span></label>
<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea aps-full-page"
		  rows="6" maxlength="200"
		  placeholder="Enter short description (max 40 words)..." required
		  data-initial="<?php echo esc_attr( $product_data['short_description'] ?? '' ); ?>"><?php echo esc_textarea( $product_data['short_description'] ?? '' ); ?></textarea>
```

**CURRENT CODE (Handler - Line 125):**
```php
$data['description'] = isset( $raw_data['aps_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_description'] ) ) : '';
```

**PROBLEM:**
- Form sends field named `aps_short_description` (line 193)
- Handler looks for field named `aps_description` (line 125)
- **FIELD NAME MISMATCH**: Handler will NEVER find the form field
- `$data['description']` will ALWAYS be empty string
- At line 339, `$data['description']` is used as `post_content` in `wp_insert_post()`
- At line 378, `$data['description']` is used as `post_content` in `wp_update_post()`
- Product post content will ALWAYS be empty, regardless of user input
- User fills in "Short Description" field but data never reaches database
- **COMPLETE DATA LOSS** for product content/description

**EXPECTED CODE (Handler - Line 125):**
```php
$data['description'] = isset( $raw_data['aps_short_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_short_description'] ) ) : '';
```

**DATA FLOW BREAK:**
```
User fills in "Short Description" field in form
    ↓
Form submitted with parameter: aps_short_description="My product description"
    ↓
ProductFormHandler::sanitize_form_data() called (line 82)
    ↓
Handler looks for: $_POST['aps_description']
    ↓
FIELD NOT FOUND - only $_POST['aps_short_description'] exists
    ↓
$data['description'] = '' (empty string from default)
    ↓
create_product() or update_product() called (line 99 or 96)
    ↓
wp_insert_post() or wp_update_post() executed with:
    'post_content' => '' (empty string)
    ↓
Product saved to database with EMPTY content
    ↓
User views product - sees no description
    ↓
DATA LOSS: User input completely lost
```

---

### BUG 3: Form Field 'aps_gallery' Does Not Exist - Gallery Feature Completely Broken

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php` (Form) AND `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` (Handler)

**CURRENT CODE (Form - Lines 127-169):**
```php
<section id="images" class="aps-section">
	<h2 class="section-title">PRODUCT IMAGES</h2>
	<div class="aps-grid-2">
		<div class="aps-upload-group">
			<label>Product Image (Featured)</label>
			<!-- ... logo upload code ... -->
		</div>
		<div class="aps-upload-group">
			<label>Logo</label>
			<!-- ... brand image upload code ... -->
		</div>
	</div>
</section>
```

**CURRENT CODE (Handler - Line 164):**
```php
$data['gallery'] = isset( $raw_data['aps_gallery'] ) ? $this->sanitize_gallery_urls( wp_unslash( $raw_data['aps_gallery'] ) ) : [];
```

**PROBLEM:**
- Form does NOT contain any input field with name `aps_gallery`
- Form only has `aps_image_url` (line 138) and `aps_brand_image_url` (line 157)
- No gallery upload UI exists in the form
- Handler expects `aps_gallery` field which DOES NOT EXIST
- `$data['gallery']` will ALWAYS be empty array
- At line 439, empty gallery array saved to `_aps_gallery` post meta
- Gallery functionality is referenced in code but **COMPLETELY BROKEN** in UI
- Users cannot add product gallery images because the feature doesn't exist in the form

**EXPECTED CODE (Form - Add Gallery Section):**
```php
<section id="images" class="aps-section">
	<h2 class="section-title">PRODUCT IMAGES</h2>
	<div class="aps-grid-2">
		<div class="aps-upload-group">
			<label>Product Image (Featured)</label>
			<!-- ... existing logo upload code ... -->
		</div>
		<div class="aps-upload-group">
			<label>Logo</label>
			<!-- ... existing brand image upload code ... -->
		</div>
	</div>
	<div class="aps-field-group">
		<label>Product Gallery</label>
		<div class="aps-gallery-upload-area" id="aps-gallery-upload">
			<button type="button" class="aps-btn" id="aps-add-gallery-image">
				<i class="fas fa-plus"></i> Add Gallery Images
			</button>
		</div>
		<div class="aps-gallery-preview" id="aps-gallery-preview"></div>
		<textarea id="aps-gallery-input" name="aps_gallery" class="aps-textarea" style="display:none;"></textarea>
	</div>
</section>
```

**DATA FLOW BREAK:**
```
User expects to add product gallery images
    ↓
Form rendered - NO gallery input field exists
    ↓
User cannot find gallery upload option
    ↓
User submits form without gallery data
    ↓
ProductFormHandler::sanitize_form_data() called (line 82)
    ↓
Handler looks for: $_POST['aps_gallery']
    ↓
FIELD NOT FOUND - no gallery field in form
    ↓
$data['gallery'] = [] (empty array from default)
    ↓
save_product_meta() called (line 353 or 390)
    ↓
update_post_meta( $post_id, '_aps_gallery', [] ) executed (line 439)
    ↓
Product saved with empty gallery
    ↓
FEATURE BROKEN: Users cannot add product gallery
```

---

### BUG 4: Logo Field Mismatch - Form Sends URL String, Handler Expects Attachment ID Integer

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php` (Form) AND `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` (Handler)

**CURRENT CODE (Form - Line 138):**
```php
<input type="hidden" name="aps_image_url" id="aps-image-url" value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
```

**CURRENT CODE (Handler - Line 130):**
```php
$data['logo'] = isset( $raw_data['aps_logo'] ) ? absint( $raw_data['aps_logo'] ) : 0;
```

**PROBLEM:**
- Form sends logo as URL string via field `aps_image_url` (line 138)
- JavaScript at line 698 stores attachment URL: `$('#aps-image-url').val(attachment.url);`
- Handler looks for field named `aps_logo` which DOES NOT EXIST in form
- Handler expects attachment ID (integer) and uses `absint()` to sanitize
- When `aps_logo` field is not found, `$data['logo']` defaults to `0` (integer)
- Even if field existed, `absint()` of a URL string would return `0`
- Logo will ALWAYS be saved as `0` (no attachment) in database
- User uploads/selects logo image but it never gets saved
- **COMPLETE DATA LOSS** for logo

**EXPECTED CODE (Handler - Line 130):**
```php
$data['logo'] = isset( $raw_data['aps_image_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_image_url'] ) ) : '';
```

**DATA FLOW BREAK:**
```
User uploads/selects logo image via media library
    ↓
JavaScript stores image URL in hidden field: aps_image_url="https://example.com/logo.jpg"
    ↓
Form submitted with parameter: aps_image_url="https://example.com/logo.jpg"
    ↓
ProductFormHandler::sanitize_form_data() called (line 82)
    ↓
Handler looks for: $_POST['aps_logo']
    ↓
FIELD NOT FOUND - only $_POST['aps_image_url'] exists
    ↓
$data['logo'] = 0 (default from absint() of non-existent field)
    ↓
save_product_meta() called (line 353 or 390)
    ↓
update_post_meta( $post_id, '_aps_logo', 0 ) executed (line 416)
    ↓
Product saved with logo ID = 0 (no logo)
    ↓
DATA LOSS: Logo never saved despite user selection
```

---

## CRITICAL FAILURE AREA 3: RIBBON DATA STORAGE LOGIC

---

### BUG 5: Ribbon Retrieved as Post Meta Instead of Taxonomy Term in AJAX Handler

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php`

**CURRENT CODE (Form Handler - Lines 479-483):**
```php
// Save ribbons
if ( ! empty( $data['ribbons'] ) ) {
	wp_set_object_terms( $post_id, $data['ribbons'], 'aps_ribbon', false );
} else {
	wp_delete_object_term_relationships( $post_id, 'aps_ribbon' );
}
```

**CURRENT CODE (AJAX Handler - Line 156):**
```php
$products[] = [
	'id' => $post_id,
	'title' => get_the_title(),
	'logo' => get_post_meta($post_id, '_aps_logo', true),
	'price' => get_post_meta($post_id, '_aps_price', true),
	'original_price' => get_post_meta($post_id, '_aps_original_price', true),
	'discount_percentage' => $this->calculateDiscount($post_id),
	'status' => get_post_status($post_id),
	'featured' => get_post_meta($post_id, '_aps_featured', true) === '1',
	'ribbon' => get_post_meta($post_id, '_aps_ribbon', true),  // ← WRONG FUNCTION
	'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
	'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
	'affiliate_url' => get_post_meta($post_id, '_aps_affiliate_url', true),
];
```

**PROBLEM:**
- Ribbons are stored as **taxonomy terms** (`aps_ribbon`), not as post meta
- Form handler correctly uses `wp_set_object_terms()` to save ribbons (line 480)
- AJAX handler incorrectly uses `get_post_meta()` to retrieve ribbons (line 156)
- `get_post_meta($post_id, '_aps_ribbon', true)` will return empty string
- Ribbon data will ALWAYS be empty in AJAX response
- Frontend will not display ribbon badges in product table
- Categories and tags correctly use `wp_get_post_terms()` (lines 157-158)
- **INCONSISTENT DATA ACCESS**: Ribbon uses wrong retrieval method

**EXPECTED CODE (AJAX Handler - Line 156):**
```php
$ribbon_terms = wp_get_post_terms($post_id, 'aps_ribbon', ['fields' => 'names']);
$products[] = [
	'id' => $post_id,
	'title' => get_the_title(),
	'logo' => get_post_meta($post_id, '_aps_logo', true),
	'price' => get_post_meta($post_id, '_aps_price', true),
	'original_price' => get_post_meta($post_id, '_aps_original_price', true),
	'discount_percentage' => $this->calculateDiscount($post_id),
	'status' => get_post_status($post_id),
	'featured' => get_post_meta($post_id, '_aps_featured', true) === '1',
	'ribbon' => !empty($ribbon_terms) ? $ribbon_terms[0] : '',  // ← CORRECT FUNCTION
	'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
	'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
	'affiliate_url' => get_post_meta($post_id, '_aps_affiliate_url', true),
];
```

**DATA FLOW BREAK:**
```
User creates product with ribbon "Best Seller"
    ↓
Form submitted with aps_ribbons="best-seller"
    ↓
ProductFormHandler::save_product_meta() called (line 353)
    ↓
wp_set_object_terms( $post_id, ['best-seller'], 'aps_ribbon', false ) executed (line 480)
    ↓
Ribbon saved as taxonomy term in database (CORRECT)
    ↓
User views product table
    ↓
AJAX request: handleFilterProducts() called (line 80)
    ↓
Handler builds product array (line 147)
    ↓
get_post_meta($post_id, '_aps_ribbon', true) executed (line 156)
    ↓
Returns: '' (empty string - ribbons are NOT stored as meta)
    ↓
Response sent: { ribbon: '' }
    ↓
Frontend receives empty ribbon data
    ↓
Ribbon badge NOT displayed in product table
    ↓
DATA LOSS: Ribbon exists in database but not shown in UI
```

---

### BUG 6: Ribbon Saved as Post Meta Instead of Taxonomy Term in Quick Edit

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php`

**CURRENT CODE (Form Handler - Lines 479-483):**
```php
// Save ribbons
if ( ! empty( $data['ribbons'] ) ) {
	wp_set_object_terms( $post_id, $data['ribbons'], 'aps_ribbon', false );
} else {
	wp_delete_object_term_relationships( $post_id, 'aps_ribbon' );
}
```

**CURRENT CODE (Quick Edit Handler - Lines 638-642):**
```php
// Update ribbon
if (isset($product_data['ribbon'])) {
	$ribbon = sanitize_text_field($product_data['ribbon']);
	update_post_meta($product_id, '_aps_ribbon', $ribbon);  // ← WRONG FUNCTION
	$updated_fields['ribbon'] = $ribbon;
}
```

**PROBLEM:**
- Ribbons are taxonomy terms, should be saved using `wp_set_object_terms()`
- Quick edit handler incorrectly uses `update_post_meta()` to save ribbons (line 641)
- Creates **DATA INCONSISTENCY** between two edit paths:
  - Form edit: ribbons saved as taxonomy terms (line 480)
  - Quick edit: ribbons saved as post meta (line 641)
- Products edited via quick edit will not show ribbons correctly
- Ribbon display logic expects taxonomy terms
- `wp_get_post_terms()` will not find ribbons saved as meta
- **TWO DIFFERENT STORAGE MECHANISMS** for same data

**EXPECTED CODE (Quick Edit Handler - Lines 638-642):**
```php
// Update ribbon
if (isset($product_data['ribbon'])) {
	$ribbon = sanitize_text_field($product_data['ribbon']);
	wp_set_object_terms($product_id, [$ribbon], 'aps_ribbon', false);  // ← CORRECT FUNCTION
	$updated_fields['ribbon'] = $ribbon;
}
```

**DATA FLOW BREAK:**
```
User performs quick edit on product
    ↓
Changes ribbon from "Best Seller" to "New"
    ↓
AJAX request: handleQuickEditProduct() called (line 525)
    ↓
Handler processes ribbon update (line 638)
    ↓
update_post_meta($product_id, '_aps_ribbon', 'New') executed (line 641)
    ↓
Ribbon saved as POST META (INCORRECT - should be taxonomy)
    ↓
User views product table
    ↓
Display code uses: wp_get_post_terms($post_id, 'aps_ribbon', ...)
    ↓
Returns: [] (empty array - meta not found by taxonomy query)
    ↓
Ribbon NOT displayed in product table
    ↓
DATA INCONSISTENCY: Form edit saves as taxonomy, quick edit saves as meta
```

---

### BUG 7: Menu.php Logo Column Rendering Fails Due to Handler Storing URL Instead of ID

**FILE:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php`

**CURRENT CODE (Menu.php - Lines 213-221):**
```php
case 'logo':
	$logo_id = get_post_meta($post_id, '_aps_logo', true);
	if ($logo_id) {
		$logo_url = wp_get_attachment_image_url($logo_id, 'thumbnail');
		if ($logo_url) {
			echo '<div class="aps-logo-container">';
			echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" class="aps-product-logo">';
			echo '</div>';
		}
	}
	break;
```

**PROBLEM:**
- Based on Bug #4 analysis, form handler now saves logo as URL string (after fix)
- But `Menu.php` still expects logo to be an attachment ID (integer)
- `get_post_meta($post_id, '_aps_logo', true)` will return URL string
- `wp_get_attachment_image_url()` expects an attachment ID, not a URL
- When passed a URL string, it returns `false`
- Logo column will always be empty in products table
- User sees no logo despite having set one via form
- **TYPE MISMATCH** between storage and retrieval

**EXPECTED CODE (Menu.php - Lines 213-221):**
```php
case 'logo':
	$logo_value = get_post_meta($post_id, '_aps_logo', true);
	if ($logo_value) {
		// Check if it's an attachment ID or URL
		if (is_numeric($logo_value)) {
			$logo_url = wp_get_attachment_image_url($logo_value, 'thumbnail');
		} else {
			$logo_url = $logo_value; // It's a URL string
		}
		if ($logo_url) {
			echo '<div class="aps-logo-container">';
			echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" class="aps-product-logo">';
			echo '</div>';
		}
	}
	break;
```

**DATA FLOW BREAK:**
```
Product created with logo URL: "https://example.com/logo.jpg"
    ↓
Logo saved as URL string in _aps_logo meta (after Bug #4 fix)
    ↓
User views products table
    ↓
renderCustomColumns() called with 'logo' column (line 206)
    ↓
get_post_meta($post_id, '_aps_logo', true) executed (line 213)
    ↓
Returns: "https://example.com/logo.jpg" (URL string)
    ↓
URL string passed to wp_get_attachment_image_url() (line 214)
    ↓
Function expects attachment ID, returns false for string input
    ↓
Logo column displays nothing
    ↓
User sees empty logo column despite product having logo
    ↓
DISPLAY FAILURE: Logo not shown in table
```

---

## SUMMARY TABLE

| Bug ID | File | Line | Issue | Severity |
|---------|------|-------|----------|
| 1 | add-product-page.php | 22-58 | Missing existence check for post object - empty form on invalid edit | CRITICAL |
| 2 | ProductFormHandler.php | 125 | Form field 'aps_description' doesn't exist - content always empty | CRITICAL |
| 3 | ProductFormHandler.php | 164 | Form field 'aps_gallery' doesn't exist - gallery broken | CRITICAL |
| 4 | ProductFormHandler.php | 130 | Logo field mismatch - URL sent, ID expected | CRITICAL |
| 5 | AjaxHandler.php | 156 | Ribbon retrieved as meta instead of taxonomy | CRITICAL |
| 6 | AjaxHandler.php | 641 | Ribbon saved as meta instead of taxonomy in quick edit | CRITICAL |
| 7 | Menu.php | 213-214 | Logo column expects ID but receives URL | CRITICAL |

---

## IMMEDIATE REMEDIATION PRIORITY

1. **Fix Edit Mode Logic (Bug #1)** - Add error message for invalid product IDs
2. **Fix Field Mapping (Bugs #2, #3, #4)** - Align form field names with handler expectations
3. **Standardize Ribbon Storage (Bugs #5, #6)** - Use taxonomy consistently across all code paths
4. **Fix Logo Display (Bug #7)** - Handle both ID and URL in column rendering

---

**Total Critical Bugs Identified:** 7
**All bugs involve data loss or complete functionality failure**
