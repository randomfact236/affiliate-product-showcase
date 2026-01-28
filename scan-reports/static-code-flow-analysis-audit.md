# STATIC CODE FLOW ANALYSIS AUDIT REPORT

**Project:** Affiliate Product Showcase
**Analysis Type:** Static Code Flow Analysis
**Analysis Date:** 2026-01-28
**Files Analyzed:**
1. `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/add-product-page.php`
2. `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php`
3. `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php`
4. `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php`

---

## EXECUTIVE SUMMARY

This audit identified **16 logic bugs** across 4 PHP backend files. The bugs are categorized by severity:

| Severity | Count | Bug Numbers |
|----------|-------|-------------|
| Critical (Data Loss/Corruption) | 4 | #6, #9, #12, #13 |
| High (Functionality Breaks) | 6 | #1, #2, #3, #4, #7, #11 |
| Medium (Data Inconsistency) | 4 | #8, #14, #15, #16 |
| Low (UX Issues) | 2 | #5, #10 |

**Key Findings:**
- Multiple taxonomy dropdowns lack error handling, causing potential page crashes
- Form field name mismatches prevent data from being saved (description, logo, gallery)
- Ribbon data is inconsistently stored (taxonomy vs meta) across different code paths
- Cache deletion uses non-standard cache groups, leaving stale data

---

## DETAILED FINDINGS

---

## FILE: add-product-page.php

### BUG 1: JSON Decode Failure Without Error Handling

**LINE:** 44

**CURRENT CODE:**
```php
'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
```

**PROBLEM:**
- The `json_decode()` function returns `null` on failure when the stored JSON is malformed
- The fallback `?: '[]'` only applies if `get_post_meta()` returns an empty string or false
- If `get_post_meta()` returns malformed JSON, `json_decode()` returns `null`, which is then stored in `$product_data['features']`
- JavaScript code at line 545 expects an array but receives `null`, causing runtime errors

**EXPECTED CODE:**
```php
$features_json = get_post_meta( $post->ID, '_aps_features', true );
$features_decoded = json_decode( $features_json ?: '[]', true );
'features' => is_array( $features_decoded ) ? $features_decoded : [],
```

**DATA FLOW BREAK:**
1. Form loads in edit mode → `$post_id` set from `$_GET['post']`
2. `get_post_meta()` retrieves malformed JSON from database
3. `json_decode()` fails and returns `null`
4. `null` stored in `$product_data['features']`
5. JavaScript at line 545 checks `apsProductData.features && Array.isArray(apsProductData.features)`
6. `Array.isArray(null)` returns `false`
7. Features array remains empty despite having data in database
8. Empty output displayed to user

---

### BUG 2: Category Dropdown Iteration Without Error Checking

**LINE:** 250-265

**CURRENT CODE:**
```php
$categories = get_terms( [ 'taxonomy' => 'aps_category', 'hide_empty' => false ] );
foreach ( $categories as $category ) :
	$category_image = get_term_meta( $category->term_id, '_aps_category_image', true );
	$category_featured = get_term_meta( $category->term_id, '_aps_category_featured', true ) === '1';
?>
	<div class="dropdown-item aps-taxonomy-item" data-value="<?php echo esc_attr( $category->slug ); ?>">
```

**PROBLEM:**
- `get_terms()` can return a `WP_Error` object if the taxonomy doesn't exist or has database issues
- The `foreach` loop will fail with a fatal error when trying to iterate over a `WP_Error` object
- No validation is performed before the loop to check if `$categories` is a valid array
- The entire page will crash with "Invalid argument supplied for foreach()" error

**EXPECTED CODE:**
```php
$categories = get_terms( [ 'taxonomy' => 'aps_category', 'hide_empty' => false ] );
if ( is_wp_error( $categories ) ) {
	$categories = [];
}
foreach ( $categories as $category ) :
	$category_image = get_term_meta( $category->term_id, '_aps_category_image', true );
	$category_featured = get_term_meta( $category->term_id, '_aps_category_featured', true ) === '1';
?>
	<div class="dropdown-item aps-taxonomy-item" data-value="<?php echo esc_attr( $category->slug ); ?>">
```

**DATA FLOW BREAK:**
1. Form loads → Category taxonomy query executed
2. Database error occurs or taxonomy is missing
3. `get_terms()` returns `WP_Error` object
4. `foreach` attempts to iterate over `WP_Error` object
5. PHP fatal error: "Invalid argument supplied for foreach()"
6. Page rendering halts
7. Empty output displayed to user with PHP error

---

### BUG 3: Ribbon Dropdown Iteration Without Error Checking

**LINE:** 279-293

**CURRENT CODE:**
```php
$ribbons = get_terms( [ 'taxonomy' => 'aps_ribbon', 'hide_empty' => false ] );
foreach ( $ribbons as $ribbon ) :
	$ribbon_color = get_term_meta( $ribbon->term_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
	$ribbon_bg = get_term_meta( $ribbon->term_id, '_aps_ribbon_bg_color', true ) ?: '#ff0000';
	$ribbon_icon = get_term_meta( $ribbon->term_id, '_aps_ribbon_icon', true ) ?: '';
?>
	<div class="dropdown-item aps-ribbon-item" data-value="<?php echo esc_attr( $ribbon->slug ); ?>">
```

**PROBLEM:**
- Same issue as categories - `get_terms()` can return `WP_Error` object
- No validation before `foreach` loop
- Will cause fatal error if taxonomy query fails
- Ribbon selection functionality completely broken on error

**EXPECTED CODE:**
```php
$ribbons = get_terms( [ 'taxonomy' => 'aps_ribbon', 'hide_empty' => false ] );
if ( is_wp_error( $ribbons ) ) {
	$ribbons = [];
}
foreach ( $ribbons as $ribbon ) :
	$ribbon_color = get_term_meta( $ribbon->term_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
	$ribbon_bg = get_term_meta( $ribbon->term_id, '_aps_ribbon_bg_color', true ) ?: '#ff0000';
	$ribbon_icon = get_term_meta( $ribbon->term_id, '_aps_ribbon_icon', true ) ?: '';
?>
	<div class="dropdown-item aps-ribbon-item" data-value="<?php echo esc_attr( $ribbon->slug ); ?>">
```

**DATA FLOW BREAK:**
1. Form loads → Ribbon taxonomy query executed
2. Database connection issue or taxonomy corruption
3. `get_terms()` returns `WP_Error` object
4. `foreach` loop fails with fatal error
5. Page crashes mid-render
6. User sees broken page with PHP error

---

### BUG 4: Tags Checkbox Iteration Without Error Checking

**LINE:** 304-324

**CURRENT CODE:**
```php
$tags = get_terms( [ 'taxonomy' => 'aps_tag', 'hide_empty' => false ] );
foreach ( $tags as $tag ) :
	$tag_color = get_term_meta( $tag->term_id, '_aps_tag_color', true ) ?: '#ffffff';
	$tag_bg = get_term_meta( $tag->term_id, '_aps_tag_bg_color', true ) ?: '#ff6b6b';
	$tag_icon = get_term_meta( $tag->term_id, '_aps_tag_icon', true ) ?: '';
	$tag_featured = get_term_meta( $tag->term_id, '_aps_tag_featured', true ) === '1';
?>
	<label class="aps-checkbox-label aps-tag-checkbox">
		<input type="checkbox" name="aps_tags[]" value="<?php echo esc_attr( $tag->slug ); ?>">
```

**PROBLEM:**
- Same pattern - `get_terms()` can return `WP_Error` object
- No validation before iteration
- Fatal error on taxonomy query failure
- Tag selection completely unavailable on error

**EXPECTED CODE:**
```php
$tags = get_terms( [ 'taxonomy' => 'aps_tag', 'hide_empty' => false ] );
if ( is_wp_error( $tags ) ) {
	$tags = [];
}
foreach ( $tags as $tag ) :
	$tag_color = get_term_meta( $tag->term_id, '_aps_tag_color', true ) ?: '#ffffff';
	$tag_bg = get_term_meta( $tag->term_id, '_aps_tag_bg_color', true ) ?: '#ff6b6b';
	$tag_icon = get_term_meta( $tag->term_id, '_aps_tag_icon', true ) ?: '';
	$tag_featured = get_term_meta( $tag->term_id, '_aps_tag_featured', true ) === '1';
?>
	<label class="aps-checkbox-label aps-tag-checkbox">
		<input type="checkbox" name="aps_tags[]" value="<?php echo esc_attr( $tag->slug ); ?>">
```

**DATA FLOW BREAK:**
1. Form loads → Tag taxonomy query executed
2. Database error or taxonomy missing
3. `get_terms()` returns `WP_Error` object
4. `foreach` loop triggers fatal error
5. Page rendering fails
6. User cannot select tags for product

---

### BUG 5: Undefined $post When Edit Mode Check Passes But Post Doesn't Exist

**LINE:** 23-24

**CURRENT CODE:**
```php
$post = get_post( $post_id );
if ( $post && $post->post_type === 'aps_product' ) {
```

**PROBLEM:**
- When `$is_editing = true` (line 18), the code assumes `$post` will be a valid object
- If `get_post()` returns `null` (post doesn't exist), the conditional correctly prevents accessing `$post` properties
- However, `$product_data` array remains empty (line 21 initialized as `[]`)
- All subsequent uses of `$product_data` throughout the file rely on null coalescing operators (`??`)
- While this prevents errors, it means the form loads with empty data even when editing a valid product ID that doesn't exist as 'aps_product' type
- User sees empty form instead of error message indicating product doesn't exist

**EXPECTED CODE:**
```php
$post = get_post( $post_id );
if ( $post && $post->post_type === 'aps_product' ) {
	$product_data = [
		'id' => $post->ID,
		// ... rest of product data
	];
} else if ( $is_editing ) {
	// Invalid product ID or wrong post type
	wp_die( esc_html__( 'Invalid product. The product does not exist or is not the correct type.', 'affiliate-product-showcase' ) );
}
```

**DATA FLOW BREAK:**
1. User accesses edit URL with invalid product ID
2. `$is_editing` set to `true` based on `$_GET['post']` parameter
3. `get_post($post_id)` returns `null` (post doesn't exist)
4. Conditional check fails, `$product_data` remains empty array
5. Form renders with all fields empty
6. User expects to see existing product data but sees blank form
7. User may accidentally create duplicate product instead of editing

---

## FILE: ProductFormHandler.php

### BUG 6: Missing Form Field 'aps_description' Causes Empty Post Content

**LINE:** 125

**CURRENT CODE:**
```php
$data['description'] = isset( $raw_data['aps_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_description'] ) ) : '';
```

**PROBLEM:**
- The form in `add-product-page.php` does NOT contain a field named `aps_description`
- The form only has `aps_short_description` field (line 193)
- Therefore, `$data['description']` will ALWAYS be an empty string
- At line 339, `$data['description']` is used as `post_content` when creating product
- At line 378, `$data['description']` is used as `post_content` when updating product
- Product post content will always be empty, regardless of what user enters in short description field
- Data flow break: user input in short description field never reaches post_content

**EXPECTED CODE:**
```php
$data['description'] = isset( $raw_data['aps_short_description'] ) ? sanitize_textarea_field( wp_unslash( $raw_data['aps_short_description'] ) ) : '';
```

**DATA FLOW BREAK:**
1. User fills in "Short Description" field in form
2. Form submitted to `admin-post.php` with `aps_short_description` parameter
3. `sanitize_form_data()` looks for `aps_description` field (line 125)
4. Field not found, `$data['description']` set to empty string
5. `create_product()` or `update_product()` called with empty description
6. `wp_insert_post()` or `wp_update_post()` saves empty `post_content`
7. Product created/updated with no content despite user input

---

### BUG 7: Missing Form Field 'aps_gallery' Causes Empty Gallery Data

**LINE:** 164

**CURRENT CODE:**
```php
$data['gallery'] = isset( $raw_data['aps_gallery'] ) ? $this->sanitize_gallery_urls( wp_unslash( $raw_data['aps_gallery'] ) ) : [];
```

**PROBLEM:**
- The form in `add-product-page.php` does NOT contain any gallery input field
- No `<input>` or `<textarea>` with name `aps_gallery` exists in the form
- Therefore, `$data['gallery']` will ALWAYS be an empty array
- At line 439, empty gallery array is saved to post meta
- Gallery functionality completely broken - users cannot add product gallery images
- The feature is referenced in code but not implemented in UI

**EXPECTED CODE:**
```php
// Gallery field must be added to add-product-page.php form first
$data['gallery'] = isset( $raw_data['aps_gallery'] ) ? $this->sanitize_gallery_urls( wp_unslash( $raw_data['aps_gallery'] ) ) : [];
```

**DATA FLOW BREAK:**
1. User expects to add product gallery images
2. Form has no gallery input field (UI missing)
3. Form submitted without gallery data
4. `sanitize_form_data()` looks for `aps_gallery` field
5. Field not found, `$data['gallery']` set to empty array
6. Empty array saved to `_aps_gallery` post meta
7. Product created with no gallery images

---

### BUG 8: Default Category Assignment Fails Silently

**LINE:** 461-469

**CURRENT CODE:**
```php
if ( ! empty( $data['categories'] ) ) {
	wp_set_object_terms( $post_id, $data['categories'], 'aps_category', false );
} else {
	// Auto-assign default category if no categories specified
	$default_category_id = get_option( 'aps_default_category_id', 0 );
	if ( $default_category_id > 0 ) {
		wp_set_object_terms( $post_id, [ (int) $default_category_id ], 'aps_category', false );
	}
}
```

**PROBLEM:**
- If no categories selected, code attempts to assign default category
- `$default_category_id` may exist in options but the actual category term may have been deleted
- `wp_set_object_terms()` returns `WP_Error` if term doesn't exist, but return value is not checked
- Product may be saved without any categories assigned
- No error message displayed to user
- Products without categories may not appear in category-filtered views

**EXPECTED CODE:**
```php
if ( ! empty( $data['categories'] ) ) {
	$result = wp_set_object_terms( $post_id, $data['categories'], 'aps_category', false );
	if ( is_wp_error( $result ) ) {
		error_log( 'Failed to set categories: ' . $result->get_error_message() );
	}
} else {
	// Auto-assign default category if no categories specified
	$default_category_id = get_option( 'aps_default_category_id', 0 );
	if ( $default_category_id > 0 ) {
		$term = get_term( $default_category_id, 'aps_category' );
		if ( $term && ! is_wp_error( $term ) ) {
			wp_set_object_terms( $post_id, [ (int) $default_category_id ], 'aps_category', false );
		}
	}
}
```

**DATA FLOW BREAK:**
1. User creates product without selecting categories
2. Code retrieves `$default_category_id` from options
3. Default category ID exists but actual category term was deleted
4. `wp_set_object_terms()` called with invalid term ID
5. Function fails silently (return value not checked)
6. Product saved without any categories
7. Product may not appear in category listings
8. User unaware of the issue

---

### BUG 9: Logo Field Mismatch - Form Sends URL, Handler Expects Attachment ID

**LINE:** 130

**CURRENT CODE:**
```php
$data['logo'] = isset( $raw_data['aps_logo'] ) ? absint( $raw_data['aps_logo'] ) : 0;
```

**PROBLEM:**
- The form at line 138 sends logo as a URL: `<input type="hidden" name="aps_image_url" id="aps-image-url" value="...">`
- The form at line 157 sends brand image as a URL: `<input type="hidden" name="aps_brand_image_url" id="aps-brand-image-url" value="...">`
- The handler at line 130 looks for `aps_logo` field which DOES NOT EXIST in the form
- The handler expects an attachment ID (integer) but the form sends URLs (strings)
- `absint()` of a URL string returns 0
- Logo will always be saved as 0 (no attachment)
- Brand image at line 133 correctly uses `aps_brand_image_url` field name

**EXPECTED CODE:**
```php
$data['logo'] = isset( $raw_data['aps_image_url'] ) ? esc_url_raw( wp_unslash( $raw_data['aps_image_url'] ) ) : '';
```

**DATA FLOW BREAK:**
1. User uploads/selects logo image via media library
2. JavaScript stores image URL in `aps_image_url` hidden field
3. Form submitted with `aps_image_url` parameter containing URL string
4. Handler looks for `aps_logo` field (doesn't exist)
5. `$data['logo']` set to 0 (default from `absint()` of non-existent field)
6. Logo saved as attachment ID 0 (no logo)
7. Product created without logo despite user selection

---

### BUG 10: Redundant Price Update When Sale Price Exists

**LINE:** 404-425

**CURRENT CODE:**
```php
// Save basic meta fields
update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
update_post_meta( $post_id, '_aps_currency', $data['currency'] );
update_post_meta( $post_id, '_aps_affiliate_url', $data['affiliate_url'] );
// ... more updates ...
// Handle sale price logic
if ( null !== $data['sale_price'] && ! empty( $data['sale_price'] ) ) {
	update_post_meta( $post_id, '_aps_original_price', $data['regular_price'] );
	update_post_meta( $post_id, '_aps_price', $data['sale_price'] );
} else {
	delete_post_meta( $post_id, '_aps_original_price' );
	update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
}
```

**PROBLEM:**
- Line 404 sets `_aps_price` to `$data['regular_price']`
- Lines 419-421 overwrite `_aps_price` with `$data['sale_price']` if sale price exists
- Line 424 sets `_aps_price` to `$data['regular_price']` again if no sale price
- The initial update at line 404 is redundant and wasteful
- When sale price exists, database is written 3 times for the same meta key
- Performance issue with unnecessary database writes

**EXPECTED CODE:**
```php
// Save basic meta fields (excluding price - handled below)
update_post_meta( $post_id, '_aps_currency', $data['currency'] );
update_post_meta( $post_id, '_aps_affiliate_url', $data['affiliate_url'] );
// ... more updates ...
// Handle price logic
if ( null !== $data['sale_price'] && ! empty( $data['sale_price'] ) ) {
	update_post_meta( $post_id, '_aps_original_price', $data['regular_price'] );
	update_post_meta( $post_id, '_aps_price', $data['sale_price'] );
} else {
	delete_post_meta( $post_id, '_aps_original_price' );
	update_post_meta( $post_id, '_aps_price', $data['regular_price'] );
}
```

**DATA FLOW BREAK:**
1. Product data with sale price submitted
2. Handler executes `update_post_meta()` for `_aps_price` with regular price
3. Database write #1 completed
4. Handler checks sale price condition
5. Handler executes `update_post_meta()` for `_aps_original_price` with regular price
6. Database write #2 completed
7. Handler executes `update_post_meta()` for `_aps_price` with sale price
8. Database write #3 completed (overwrites write #1)
9. Unnecessary database operation performed

---

## FILE: Menu.php

### BUG 11: get_post_meta() Return Value Used Without Validation in Column Rendering

**LINE:** 213-221

**CURRENT CODE:**
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
- Code assumes `_aps_logo` meta contains an attachment ID (integer)
- However, based on Bug #9 analysis, the form handler saves logo as a URL string, not attachment ID
- `get_post_meta()` will return the URL string
- `wp_get_attachment_image_url()` expects an attachment ID, not a URL
- When passed a URL string, it returns `false`
- Logo column will always be empty in products table
- User sees no logo despite having set one

**EXPECTED CODE:**
```php
case 'logo':
	$logo_value = get_post_meta($post_id, '_aps_logo', true);
	if ($logo_value) {
		// Check if it's an attachment ID or URL
		if (is_numeric($logo_value)) {
			$logo_url = wp_get_attachment_image_url($logo_value, 'thumbnail');
		} else {
			$logo_url = $logo_value; // It's a URL
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
1. Products table renders
2. For each product, `renderCustomColumns()` called with 'logo' column
3. `get_post_meta()` retrieves `_aps_logo` value (URL string from form handler)
4. URL string passed to `wp_get_attachment_image_url()`
5. Function expects attachment ID, returns `false` for invalid input
6. Logo column displays nothing
7. User sees empty logo column despite product having logo

---

## FILE: AjaxHandler.php

### BUG 12: Ribbon Retrieved as Post Meta Instead of Taxonomy Term

**LINE:** 156

**CURRENT CODE:**
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
	'ribbon' => get_post_meta($post_id, '_aps_ribbon', true),
	'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
	'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
	'affiliate_url' => get_post_meta($post_id, '_aps_affiliate_url', true),
];
```

**PROBLEM:**
- Ribbons are stored as taxonomy terms (`aps_ribbon`), not as post meta
- The form handler at lines 479-483 uses `wp_set_object_terms()` to save ribbons
- This code uses `get_post_meta()` to retrieve ribbons, which will return empty string
- Ribbon data will always be empty in AJAX response
- Frontend will not display ribbon badges
- Categories and tags correctly use `wp_get_post_terms()`, but ribbon uses wrong function

**EXPECTED CODE:**
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
	'ribbon' => !empty($ribbon_terms) ? $ribbon_terms[0] : '',
	'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
	'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
	'affiliate_url' => get_post_meta($post_id, '_aps_affiliate_url', true),
];
```

**DATA FLOW BREAK:**
1. AJAX request to filter products
2. Handler queries products
3. For each product, builds response array
4. `get_post_meta($post_id, '_aps_ribbon', true)` called
5. Returns empty string (ribbons stored as taxonomy, not meta)
6. Response sent with empty ribbon field
7. Frontend receives products without ribbon data
8. Ribbon badges not displayed in product table

---

### BUG 13: Ribbon Saved as Post Meta Instead of Taxonomy Term in Quick Edit

**LINE:** 638-642

**CURRENT CODE:**
```php
// Update ribbon
if (isset($product_data['ribbon'])) {
	$ribbon = sanitize_text_field($product_data['ribbon']);
	update_post_meta($product_id, '_aps_ribbon', $ribbon);
	$updated_fields['ribbon'] = $ribbon;
}
```

**PROBLEM:**
- Ribbons are taxonomy terms, should be saved using `wp_set_object_terms()`
- This code saves ribbon as post meta
- Creates data inconsistency: ribbons saved via form use taxonomy, ribbons saved via quick edit use meta
- Ribbon display logic expects taxonomy terms
- Products edited via quick edit will not show ribbons correctly
- Data flow break between two different edit paths

**EXPECTED CODE:**
```php
// Update ribbon
if (isset($product_data['ribbon'])) {
	$ribbon = sanitize_text_field($product_data['ribbon']);
	wp_set_object_terms($product_id, [$ribbon], 'aps_ribbon', false);
	$updated_fields['ribbon'] = $ribbon;
}
```

**DATA FLOW BREAK:**
1. User performs quick edit on product
2. Changes ribbon selection
3. AJAX request sent to `handleQuickEditProduct()`
4. Ribbon saved via `update_post_meta()` as post meta
5. Other parts of code expect ribbons as taxonomy terms
6. Ribbon not displayed correctly in product listings
7. Data inconsistency between form edit and quick edit

---

### BUG 14: Cache Deletion Uses Non-Standard Cache Group

**LINE:** 450

**CURRENT CODE:**
```php
// Clear product cache
foreach ($trashed_ids as $product_id) {
	wp_cache_delete("product_{$product_id}", 'products');
}
```

**PROBLEM:**
- The cache group `'products'` is not a standard WordPress cache group
- WordPress default cache groups include: 'posts', 'post_meta', 'terms', etc.
- Custom cache groups require explicit implementation in caching layer
- Without custom cache implementation, this `wp_cache_delete()` call has no effect
- Cached product data remains after trashing
- Stale data may be displayed to users
- Same issue at lines 509 and 645

**EXPECTED CODE:**
```php
// Clear product cache
foreach ($trashed_ids as $product_id) {
	wp_cache_delete($product_id, 'posts');
	clean_post_cache($product_id);
}
```

**DATA FLOW BREAK:**
1. Products trashed via bulk action
2. Handler attempts to clear cache
3. `wp_cache_delete()` called with custom cache group 'products'
4. Caching layer doesn't recognize this group
5. Cache deletion has no effect
6. Stale product data remains in cache
7. Users may still see trashed products in listings

---

### BUG 15: get_post_meta() Return Values Not Validated Before Use in Filter

**LINE:** 150-152

**CURRENT CODE:**
```php
$products[] = [
	'id' => $post_id,
	'title' => get_the_title(),
	'logo' => get_post_meta($post_id, '_aps_logo', true),
	'price' => get_post_meta($post_id, '_aps_price', true),
	'original_price' => get_post_meta($post_id, '_aps_original_price', true),
```

**PROBLEM:**
- `get_post_meta()` returns empty string if meta key doesn't exist
- `logo` may be empty string or invalid URL
- `price` may be empty string, causing `NaN` when used in calculations
- `original_price` may be empty string
- No validation or default values provided
- Frontend JavaScript may fail when trying to use these values
- Discount calculation at line 377-384 expects numeric values but may receive strings

**EXPECTED CODE:**
```php
$logo = get_post_meta($post_id, '_aps_logo', true);
$price = get_post_meta($post_id, '_aps_price', true);
$original_price = get_post_meta($post_id, '_aps_original_price', true);

$products[] = [
	'id' => $post_id,
	'title' => get_the_title(),
	'logo' => is_string($logo) && !empty($logo) ? $logo : '',
	'price' => is_numeric($price) ? floatval($price) : 0.0,
	'original_price' => is_numeric($original_price) ? floatval($original_price) : 0.0,
```

**DATA FLOW BREAK:**
1. AJAX filter request for products
2. Handler retrieves product data via `get_post_meta()`
3. Meta values may be empty strings or non-numeric
4. Unvalidated values sent in JSON response
5. Frontend JavaScript receives invalid data types
6. Price calculations fail with NaN
7. UI displays incorrect or broken data

---

### BUG 16: calculateDiscount() Doesn't Handle Empty Meta Values

**LINE:** 377-384

**CURRENT CODE:**
```php
private function calculateDiscount(int $product_id): int {
	$price = (float) get_post_meta($product_id, '_aps_price', true);
	$original_price = (float) get_post_meta($product_id, '_aps_original_price', true);

	if ($original_price > 0 && $original_price > $price) {
		return (int) round((($original_price - $price) / $original_price) * 100);
	}

	return 0;
}
```

**PROBLEM:**
- When `get_post_meta()` returns empty string, `(float)` converts it to `0.0`
- This is handled correctly by the conditional
- However, if both values are 0, discount is 0 (correct)
- If `$price` is 0 and `$original_price` is positive, division results in 100% discount
- This may be mathematically correct but semantically incorrect (free product vs missing data)
- No distinction between "product is free" and "price data missing"

**EXPECTED CODE:**
```php
private function calculateDiscount(int $product_id): int {
	$price = (float) get_post_meta($product_id, '_aps_price', true);
	$original_price = (float) get_post_meta($product_id, '_aps_original_price', true);

	// Only calculate discount if we have valid price data
	if ($price > 0 && $original_price > 0 && $original_price > $price) {
		return (int) round((($original_price - $price) / $original_price) * 100);
	}

	return 0;
}
```

**DATA FLOW BREAK:**
1. Product has missing or invalid price data
2. `get_post_meta()` returns empty string for `_aps_price`
3. Empty string converted to `0.0`
4. Original price exists (e.g., 100.0)
5. Discount calculated: ((100 - 0) / 100) * 100 = 100%
6. 100% discount displayed for product with missing price
7. Misleading information shown to user

---

## SUMMARY BY SEVERITY

### Critical Bugs (Data Loss/Corruption)

| Bug # | File | Issue | Impact |
|--------|------|-------|--------|
| #6 | ProductFormHandler.php | Missing form field 'aps_description' | Product content always empty |
| #9 | ProductFormHandler.php | Logo field mismatch | Logo never saved correctly |
| #12 | AjaxHandler.php | Ribbon retrieved as meta instead of taxonomy | Ribbon data always empty |
| #13 | AjaxHandler.php | Ribbon saved as meta instead of taxonomy | Data inconsistency |

### High Priority Bugs (Functionality Breaks)

| Bug # | File | Issue | Impact |
|--------|------|-------|--------|
| #1 | add-product-page.php | JSON decode failure without error handling | Features not loaded |
| #2 | add-product-page.php | Category dropdown without error checking | Page crash on error |
| #3 | add-product-page.php | Ribbon dropdown without error checking | Page crash on error |
| #4 | add-product-page.php | Tags dropdown without error checking | Page crash on error |
| #7 | ProductFormHandler.php | Missing gallery field | Gallery functionality broken |
| #11 | Menu.php | Logo column rendering failure | Logos not displayed in table |

### Medium Priority Bugs (Data Inconsistency)

| Bug # | File | Issue | Impact |
|--------|------|-------|--------|
| #8 | ProductFormHandler.php | Default category assignment fails silently | Products without categories |
| #14 | AjaxHandler.php | Cache deletion uses non-standard group | Stale cache data |
| #15 | AjaxHandler.php | get_post_meta() values not validated | Invalid data in AJAX responses |
| #16 | AjaxHandler.php | calculateDiscount() doesn't handle empty values | Misleading discount display |

### Low Priority Bugs (UX Issues)

| Bug # | File | Issue | Impact |
|--------|------|-------|--------|
| #5 | add-product-page.php | Undefined $post handling | Empty form instead of error message |
| #10 | ProductFormHandler.php | Redundant price update | Performance issue |

---

## RECOMMENDATIONS

1. **Immediate Action Required:**
   - Fix form field name mismatches (description, logo)
   - Standardize ribbon storage (taxonomy vs meta)
   - Add error checking to all `get_terms()` calls
   - Add JSON decode error handling

2. **High Priority:**
   - Implement gallery UI field
   - Fix logo column rendering to handle both IDs and URLs
   - Add validation to all `get_post_meta()` return values

3. **Medium Priority:**
   - Fix cache deletion to use standard WordPress cache groups
   - Add return value checking for taxonomy operations
   - Improve discount calculation logic

4. **Code Quality:**
   - Remove redundant database operations
   - Add proper error messages for invalid product IDs
   - Implement consistent data validation patterns

---

**Total Bugs Identified:** 16
**Files Analyzed:** 4
**Lines of Code Analyzed:** ~2,600

---

*Report generated via static code flow analysis. No code was executed during this audit.*
