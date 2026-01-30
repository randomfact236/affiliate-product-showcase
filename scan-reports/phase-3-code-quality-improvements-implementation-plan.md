# Phase 3: Code Quality Improvements - Implementation Plan

**Phase Duration:** Week 4  
**Priority:** LOW-MEDIUM  
**Status:** Pending Implementation  
**Dependencies:** Phase 1 (Security Fixes) and Phase 2 (Code Deduplication) must be completed first

---

## Overview

This phase addresses code quality issues identified in category-related files. The goal is to improve code maintainability, follow best practices, and enhance documentation.

---

## Issues to Resolve

### Issue 3.1: Magic Numbers Without Constants

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php)

**Locations:**
- Line 107: `aps-hidden` class usage without documentation
- Line 160: `class="regular-text"` - WordPress class but not documented

**Severity:** LOW  
**Type:** Code Quality - Maintainability

**Description:**
Magic strings and numbers are used without constants or documentation, making code harder to maintain.

**Solution:**
Define class constants for CSS classes and other magic values.

**Implementation Steps:**
1. Open `CategoryFields.php`
2. Add class constants section at top of class
3. Replace magic strings with constants
4. Add inline comments for WordPress standard classes

**Expected Code Change:**
```php
final class CategoryFields extends TaxonomyFieldsAbstract {
    /**
     * CSS class constants
     *
     * @var string
     * @since 2.1.0
     */
    private const CSS_CLASS_HIDDEN = 'aps-hidden';
    private const CSS_CLASS_CATEGORY_CHECKBOXES = 'aps-category-checkboxes-wrapper';
    private const CSS_CLASS_CATEGORY_FEATURED = 'aps-category-featured';
    private const CSS_CLASS_CATEGORY_DEFAULT = 'aps-category-default';
    private const CSS_CLASS_CATEGORY_FIELDS = 'aps-category-fields';
    private const CSS_CLASS_REGULAR_TEXT = 'regular-text';

    /**
     * WordPress standard CSS class for regular text inputs
     * @see https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/
     * @var string
     * @since 2.1.0
     */
    private const WP_CSS_REGULAR_TEXT = 'regular-text';

    // ... existing properties and methods

    /**
     * Render category-specific fields
     * 
     * @param int $category_id Category ID (0 for new category)
     * @return void
     * @since 2.0.0
     */
    protected function render_taxonomy_specific_fields( int $category_id ): void {
        // ... existing code ...

        ?>
    <!-- Featured and Default Checkboxes (side by side) -->
    <fieldset class="<?php echo esc_attr(self::CSS_CLASS_CATEGORY_CHECKBOXES); ?> <?php echo esc_attr(self::CSS_CLASS_HIDDEN); ?>" aria-label="<?php esc_attr_e( 'Category options', 'affiliate-product-showcase' ); ?>">
        <!-- ... rest of HTML ... -->
        <input
            type="url"
            id="_aps_category_image"
            name="_aps_category_image"
            value="<?php echo esc_attr( $image_url ); ?>"
            class="<?php echo esc_attr(self::WP_CSS_REGULAR_TEXT); ?>"
            placeholder="<?php esc_attr_e( 'https://example.com/image.jpg', 'affiliate-product-showcase' ); ?>"
            aria-describedby="_aps_category_image_description"
            aria-label="<?php esc_attr_e( 'Category image URL input field', 'affiliate-product-showcase' ); ?>"
        />
        <!-- ... rest of HTML ... -->
        <?php
    }
}
```

---

### Issue 3.2: Long Method - save_taxonomy_specific_fields

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php)

**Location:** Lines 183-259 (77 lines)

**Severity:** MEDIUM  
**Type:** Code Quality - Single Responsibility

**Description:**
Method is too long and handles multiple responsibilities: saving featured, image URL, and default category logic.

**Solution:**
Extract to smaller, single-responsibility methods.

**Implementation Steps:**
1. Open `CategoryFields.php`
2. Create `save_featured_field()` method
3. Create `save_image_url_field()` method
4. Create `handle_default_category()` method
5. Update `save_taxonomy_specific_fields()` to call new methods

**Expected Code Change:**
```php
/**
 * Save category-specific fields
 * 
 * @param int $category_id Category ID
 * @return void
 * @since 2.0.0
 */
protected function save_taxonomy_specific_fields( int $category_id ): void {
    // Save each field separately
    $this->save_featured_field($category_id);
    $this->save_image_url_field($category_id);
    $this->handle_default_category($category_id);
}

/**
 * Save featured field
 *
 * @param int $category_id Category ID
 * @return void
 * @since 2.1.0
 */
private function save_featured_field(int $category_id): void {
    $featured = isset($_POST['_aps_category_featured']) && '1' === $_POST['_aps_category_featured'] ? '1' : '0';
    update_term_meta($category_id, '_aps_category_featured', $featured);
    $this->delete_legacy_meta($category_id, 'featured');
}

/**
 * Save image URL field
 *
 * @param int $category_id Category ID
 * @return void
 * @since 2.1.0
 */
private function save_image_url_field(int $category_id): void {
    $image_url = isset($_POST['_aps_category_image'])
        ? esc_url_raw(wp_unslash($_POST['_aps_category_image']))
        : '';
    
    // Validate URL format
    $validated_url = $this->validateImageUrl($image_url);
    
    update_term_meta($category_id, '_aps_category_image', $validated_url);
    $this->delete_legacy_meta($category_id, 'image');
}

/**
 * Validate image URL
 *
 * @param string $url URL to validate
 * @return string Valid URL or empty string if invalid
 * @since 2.1.0
 */
private function validateImageUrl(string $url): string {
    if (empty($url)) {
        return '';
    }
    
    $parsed_url = wp_parse_url($url);
    
    if (!$parsed_url || empty($parsed_url['scheme'])) {
        $this->add_invalid_url_notice();
        return '';
    }
    
    if (!in_array($parsed_url['scheme'], ['http', 'https'], true)) {
        $this->add_invalid_url_notice();
        return '';
    }
    
    if (empty($parsed_url['host'])) {
        $this->add_invalid_url_notice();
        return '';
    }
    
    return $url;
}

/**
 * Handle default category setting
 *
 * @param int $category_id Category ID
 * @return void
 * @since 2.1.0
 */
private function handle_default_category(int $category_id): void {
    $is_default = isset($_POST['_aps_category_is_default']) && '1' === $_POST['_aps_category_is_default']
        ? '1'
        : '0';
    
    if ($is_default === '1') {
        // Remove default flag from all other categories
        $this->repository->remove_default_from_all_categories();
        // Set this category as default
        $this->set_is_default($category_id, true);
        // Update global option
        update_option('aps_default_category_id', $category_id);
        
        // Get category name for notice
        $category = get_term($category_id, 'aps_category');
        $category_name = $category && !is_wp_error($category) ? $category->name : sprintf('Category #%d', $category_id);
        
        // Add admin notice for auto-assignment feedback
        $this->notice_service->success(
            sprintf(
                __('%s has been set as default category. Products without a category will be automatically assigned to this category.', 'affiliate-product-showcase'),
                esc_html($category_name)
            )
        );
    } else {
        // Remove default flag from this category
        $this->set_is_default($category_id, false);
        $this->delete_legacy_meta($category_id, 'is_default');
        
        // Clear global option if this was default
        $current_default = get_option('aps_default_category_id', 0);
        if ((int) $current_default === $category_id) {
            delete_option('aps_default_category_id');
        }
    }
}
```

---

### Issue 3.3: Inconsistent Error Handling

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`](wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php)

**Locations:**
- Lines 204-211: Throws `PluginException` for WP_Error
- Lines 258-265: Same pattern
- Lines 303-310: Same pattern

**Severity:** LOW  
**Type:** Code Quality - Consistency

**Description:**
Repetitive error handling pattern across multiple methods.

**Solution:**
Create error handling helper method.

**Implementation Steps:**
1. Open `CategoryRepository.php`
2. Create `handleWpError()` private method
3. Update all methods to use new helper
4. Ensure consistent error messages

**Expected Code Change:**
```php
/**
 * Handle WordPress error and throw PluginException
 *
 * @param WP_Error $error WordPress error object
 * @param string $operation Operation being performed
 * @return void
 * @throws PluginException Always throws exception
 * @since 2.1.0
 */
private function handleWpError(WP_Error $error, string $operation): void {
    throw new PluginException(
        sprintf(
            'Failed to %s category: %s',
            $operation,
            $error->get_error_message()
        )
    );
}

// Update create() method (Lines 193-220):
public function create(Category $category): Category {
    $result = wp_insert_term(
        $category->name,
        Constants::TAX_CATEGORY,
        [
            'slug'        => $category->slug,
            'description' => $category->description,
            'parent'      => $category->parent_id,
        ]
    );

    if (is_wp_error($result)) {
        $this->handleWpError($result, 'create');
    }

    $term_id = (int) $result['term_id'];

    // Save metadata
    $this->save_metadata($term_id, $category);

    // Return category with ID
    return $this->find($term_id);
}

// Update update() method (Lines 242-271):
public function update(Category $category): Category {
    if ($category->id <= 0) {
        throw new PluginException('Category ID is required for update.');
    }

    $result = wp_update_term(
        $category->id,
        Constants::TAX_CATEGORY,
        [
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'parent'      => $category->parent_id,
        ]
    );

    if (is_wp_error($result)) {
        $this->handleWpError($result, 'update');
    }

    // Save metadata
    $this->save_metadata($category->id, $category);

    return $this->find($category->id);
}

// Update delete() method (Lines 286-316):
public function delete(int $category_id): bool {
    if ($category_id <= 0) {
        throw new PluginException('Category ID is required.');
    }

    $category = $this->find($category_id);
    if (!$category) {
        throw new PluginException('Category not found.');
    }

    // Prevent deleting default category
    if ($category->is_default) {
        throw new PluginException('Cannot delete default category. Please select another default category first.');
    }

    $result = wp_delete_term($category_id, Constants::TAX_CATEGORY);

    if (is_wp_error($result)) {
        $this->handleWpError($result, 'delete');
    }

    // Delete metadata
    $this->delete_metadata($category_id);

    return true;
}
```

---

### Issue 3.4: Unused Parameter/Method

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php)

**Location:** Line 204

**Severity:** LOW  
**Type:** Code Quality - Dead Code

**Description:**
Method `display_admin_notices()` is empty and unused. Notices are added via `add_admin_notice()` method.

**Solution:**
Remove unused method or implement proper notice display logic.

**Implementation Steps:**
1. Open `CategoryFormHandler.php`
2. Remove `display_admin_notices()` method (Lines 196-206)
3. Remove corresponding hook registration in `init()` method

**Expected Code Change:**
```php
// Remove this entire method (Lines 196-206):
/**
 * Display admin notices
 *
 * @return void
 * @since 1.0.0
 *
 * @action admin_notices
 */
public function display_admin_notices(): void {
    // Notices are added via add_admin_notice() method
}

// Update init() method (Lines 67-70):
public function init(): void {
    add_action('admin_init', [$this, 'handle_form_submission']);
    // Remove this line:
    // add_action('admin_notices', [$this, 'display_admin_notices']);
}
```

---

### Issue 3.5: Missing Type Hints for Return Values

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php)

**Locations:**
- Line 220: `final public function save_fields( int $term_id, int $tt_id ): void`
- Line 254: `final public function add_status_view_tabs( array $views ): array`

**Note:** Some methods have return types, others don't.

**Severity:** LOW  
**Type:** Code Quality - Type Safety

**Description:**
Inconsistent type hints across public methods.

**Solution:**
Ensure all public methods have explicit return type declarations.

**Implementation Steps:**
1. Open `TaxonomyFieldsAbstract.php`
2. Review all public methods
3. Add missing return type declarations
4. Ensure consistency with PHP 8.1+ features

**Expected Code Changes:**
```php
// All methods already have return types, verify and add any missing:

/**
 * Render fields in add form
 *
 * @param string $taxonomy Taxonomy name
 * @return void
 */
public function render_add_fields( string $taxonomy ): void {
    $this->render_taxonomy_specific_fields( 0 );
    wp_nonce_field( $this->get_nonce_action( 'fields' ), $this->get_nonce_action( 'fields_nonce' ) );
}

/**
 * Render fields in edit form
 *
 * @param \WP_Term $term Term object
 * @return void
 */
public function render_edit_fields( \WP_Term $term ): void {
    $this->render_taxonomy_specific_fields( $term->term_id );
    wp_nonce_field( $this->get_nonce_action( 'fields' ), $this->get_nonce_action( 'fields_nonce' ) );
}

/**
 * Add custom columns to WordPress native taxonomy table
 *
 * @param array $columns Existing columns
 * @return array Modified columns
 * @since 2.0.0
 */
public function add_custom_columns( array $columns ): array {
    // ... existing code ...
}

/**
 * Render custom column content
 *
 * @param string $content Column content
 * @param string $column_name Column name
 * @param int $term_id Term ID
 * @return string Column content
 * @since 2.0.0
 */
public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
    // ... existing code ...
}

// Verify all public methods have return type declarations
```

---

### Issue 3.6: Complex Conditional Logic

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php)

**Location:** Lines 194-215

**Severity:** MEDIUM  
**Type:** Code Quality - Complexity

**Description:**
Nested conditionals with repeated code. URL validation logic is complex and hard to read.

**Solution:**
Extract validation logic into separate method (already addressed in Issue 3.2).

**Implementation Steps:**
1. Already covered in Issue 3.2 with `validateImageUrl()` method

---

### Issue 3.7: Inconsistent Use of Closures

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php)

**Locations:**
- Line 237: `add_action( 'admin_notices', function () use ( $message, $type ) {`
- Line 435: `add_action( 'admin_notices', function() {`

**Severity:** LOW  
**Type:** Code Quality - Consistency

**Description:**
Some closures use `use`, others don't. Inconsistent style makes code harder to read.

**Solution:**
Create reusable methods instead of inline closures (already addressed in Issue 2.5 with NoticeService).

**Implementation Steps:**
1. Already covered in Issue 2.5 with `NoticeService` class

---

### Issue 3.8: Missing Documentation for Public Methods

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`](wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php)

**Locations:**
- Line 402: `public function get_item( WP_REST_Request $request ): WP_REST_Response`
- Line 428: `public function update( WP_REST_Request $request ): WP_REST_Response`

**Severity:** LOW  
**Type:** Code Quality - Documentation

**Description:**
Some methods lack proper PHPDoc blocks with complete parameter and return type documentation.

**Solution:**
Add comprehensive PHPDoc blocks for all public methods.

**Implementation Steps:**
1. Open `CategoriesController.php`
2. Review all public methods
3. Add complete PHPDoc blocks
4. Include @param, @return, @since, @route tags

**Expected Code Changes:**
```php
/**
 * Get single category
 *
 * Retrieves a single category by ID with validation.
 *
 * @param WP_REST_Request $request Request object containing category ID
 * @return WP_REST_Response Response with category data or error
 * @since 1.0.0
 *
 * @route GET /affiliate-showcase/v1/categories/{id}
 */
public function get_item( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Update a category
 *
 * Updates an existing category with validation and security checks.
 *
 * @param WP_REST_Request $request Request object containing category data
 * @return WP_REST_Response Response with updated category or error
 * @throws PluginException If category update fails
 * @since 1.0.0
 *
 * @route POST /affiliate-showcase/v1/categories/{id}
 */
public function update( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Delete a category (move to trash)
 *
 * Deletes a category with validation and security checks.
 *
 * @param WP_REST_Request $request Request object containing category ID
 * @return WP_REST_Response Response with success/error
 * @throws PluginException If category deletion fails
 * @since 1.0.0
 *
 * @route DELETE /affiliate-showcase/v1/categories/{id}
 */
public function delete( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Trash category (move to trash)
 *
 * Note: WordPress doesn't have native trash for terms.
 * This endpoint deletes category permanently.
 *
 * @param WP_REST_Request $request Request object containing category ID
 * @return WP_REST_Response Response with success/error
 * @since 1.0.0
 *
 * @route POST /affiliate-showcase/v1/categories/{id}/trash
 */
public function trash( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Restore category from trash
 *
 * Note: WordPress doesn't have native trash for terms.
 * This endpoint returns an error.
 *
 * @param WP_REST_Request $request Request object containing category ID
 * @return WP_REST_Response Response with error
 * @since 1.0.0
 *
 * @route POST /affiliate-showcase/v1/categories/{id}/restore
 */
public function restore( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Delete category permanently
 *
 * Permanently deletes a category with validation.
 *
 * @param WP_REST_Request $request Request object containing category ID
 * @return WP_REST_Response Response with success/error
 * @throws PluginException If category deletion fails
 * @since 1.0.0
 *
 * @route DELETE /affiliate-showcase/v1/categories/{id}/delete-permanently
 */
public function delete_permanently( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Empty trash
 *
 * Note: WordPress doesn't have native trash for terms.
 * This endpoint returns an error.
 *
 * @param WP_REST_Request $request Request object (unused)
 * @return WP_REST_Response Response with error
 * @since 1.0.0
 *
 * @route POST /affiliate-showcase/v1/categories/trash/empty
 */
public function empty_trash( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * List categories
 *
 * Returns paginated list of categories with rate limiting.
 * Rate limit: 60 requests/minute for public, 120 for authenticated users.
 *
 * @param WP_REST_Request $request Request object containing query parameters
 * @return WP_REST_Response Response with categories list or error
 * @throws RateLimitException If rate limit is exceeded
 * @since 1.0.0
 *
 * @route GET /affiliate-showcase/v1/categories
 */
public function list( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}

/**
 * Create a new category
 *
 * Creates a new category with CSRF protection and stricter rate limiting.
 * Rate limit: 20 requests/minute (stricter than list operations).
 * Nonce verification required in X-WP-Nonce header.
 *
 * @param WP_REST_Request $request Request object containing category data
 * @return WP_REST_Response Response with created category or error
 * @throws ValidationException If category data is invalid
 * @throws RateLimitException If rate limit is exceeded
 * @throws PluginException If category creation fails
 * @since 1.0.0
 *
 * @route POST /affiliate-showcase/v1/categories
 */
public function create( WP_REST_Request $request ): WP_REST_Response {
    // ... existing code ...
}
```

---

### Issue 3.9: Hardcoded Strings

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`](wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php)

**Locations:**
- Line 56: `error_log( sprintf( '[APS] Taxonomy %s not registered', Constants::TAX_CATEGORY ) );`
- Line 309: `error_log(sprintf('[APS] Auto-assigned default category #%d to product #%d', ...`

**Severity:** LOW  
**Type:** Code Quality - Maintainability

**Description:**
Log prefix `[APS]` is hardcoded in multiple locations.

**Solution:**
Define constant for log prefix.

**Implementation Steps:**
1. Open `CategoryRepository.php`
2. Add class constant for log prefix
3. Replace all hardcoded log prefixes with constant

**Expected Code Change:**
```php
final class CategoryRepository {
    /**
     * Log prefix for error logging
     *
     * @var string
     * @since 2.1.0
     */
    private const LOG_PREFIX = '[APS]';

    /**
     * Get a category by ID
     *
     * @param int $category_id Category ID (term_id)
     * @return Category|null Category instance or null if not found
     * @since 1.0.0
     */
    public function find( int $category_id ): ?Category {
        if ( $category_id <= 0 ) {
            return null;
        }

        // Ensure taxonomy is registered
        if ( ! taxonomy_exists( Constants::TAX_CATEGORY ) ) {
            error_log( sprintf( self::LOG_PREFIX . ' Taxonomy %s not registered', Constants::TAX_CATEGORY ) );
            return null;
        }

        $term = get_term( $category_id, Constants::TAX_CATEGORY );

        if ( ! $term || is_wp_error( $term ) ) {
            return null;
        }

        return CategoryFactory::from_wp_term( $term );
    }

    // Update auto_assign_default_category() method (Lines 275-315):
    public function auto_assign_default_category( int $post_id, \WP_Post $post, bool $update ): void {
        // ... existing code ...

        if ( ! is_wp_error( $result ) ) {
            // Log auto-assignment
            error_log( sprintf(
                self::LOG_PREFIX . ' Auto-assigned default category #%d to product #%d',
                $default_category_id,
                $post_id
            ));
        }
    }
}
```

---

### Issue 3.10: Inconsistent Naming Convention

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Factories/CategoryFactory.php`](wp-content/plugins/affiliate-product-showcase/src/Factories/CategoryFactory.php)

**Locations:**
- Line 175: `build_tree()`
- Line 208: `sort_by_name()`
- Line 227: `sort_by_count()`

**Severity:** LOW  
**Type:** Code Quality - PSR Compliance

**Description:**
Some methods use underscores, others don't. Inconsistent with PSR-12 naming conventions.

**Solution:**
Follow PSR-12 naming conventions consistently (snake_case for methods is acceptable in WordPress context).

**Implementation Steps:**
1. Open `CategoryFactory.php`
2. Review all method names
3. Ensure consistent naming convention
4. Update any inconsistent names

**Expected Code Changes:**
```php
// WordPress coding standards use snake_case for methods, which is acceptable
// All methods already follow this pattern, verify consistency:

/**
 * Sort categories by name (alphabetical)
 *
 * @param array<int, Category> $categories Categories to sort
 * @param string $order 'ASC' or 'DESC'
 * @return array<int, Category> Sorted categories
 * @since 1.0.0
 */
public static function sort_by_name( array $categories, string $order = 'ASC' ): array {
    $categories = $categories;
    
    usort( $categories, function( $a, $b ) use ( $order ) {
        $compare = strcasecmp( $a->name, $b->name );
        return $order === 'ASC' ? $compare : -$compare;
    } );

    return $categories;
}

/**
 * Sort categories by count (most products first)
 *
 * @param array<int, Category> $categories Categories to sort
 * @param string $order 'ASC' or 'DESC'
 * @return array<int, Category> Sorted categories
 * @since 1.0.0
 */
public static function sort_by_count( array $categories, string $order = 'DESC' ): array {
    $categories = $categories;
    
    usort( $categories, function( $a, $b ) use ( $order ) {
        $compare = $a->count <=> $b->count;
        return $order === 'DESC' ? -$compare : $compare;
    } );

    return $categories;
}

/**
 * Filter categories by featured status
 *
 * @param array<int, Category> $categories Categories to filter
 * @param bool $featured Featured status to filter by
 * @return array<int, Category> Filtered categories
 * @since 1.0.0
 */
public static function filter_by_featured( array $categories, bool $featured = true ): array {
    return array_filter( $categories, fn( $category ) => $category->featured === $featured );
}

/**
 * Filter categories by parent
 *
 * @param array<int, Category> $categories Categories to filter
 * @param int $parent_id Parent category ID (0 for top-level)
 * @return array<int, Category> Filtered categories
 * @since 1.0.0
 */
public static function filter_by_parent( array $categories, int $parent_id = 0 ): array {
    return array_filter( $categories, fn( $category ) => $category->parent_id === $parent_id );
}
```

---

### Issue 3.11: Missing Input Validation in Settings

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Admin/Settings/CategoriesSettings.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/Settings/CategoriesSettings.php)

**Location:** Lines 170-189

**Severity:** MEDIUM  
**Type:** Code Quality - Validation

**Description:**
No validation that default category ID actually exists.

**Solution:**
Add existence check for default category ID.

**Implementation Steps:**
1. Open `CategoriesSettings.php`
2. Locate `sanitize_options()` method
3. Add validation for `default_category` option
4. Check if category exists before saving

**Expected Code Change:**
```php
/**
 * Sanitize section options
 *
 * @param array $input Input values from form
 * @return array Sanitized options
 * @since 1.0.0
 */
public function sanitize_options(array $input): array {
    $sanitized = [];
    
    $sanitized['default_category'] = intval($input['default_category'] ?? 0);
    
    // Validate that default category exists
    if ($sanitized['default_category'] > 0) {
        $category = get_term($sanitized['default_category'], 'aps_category');
        if (!$category || is_wp_error($category)) {
            // Category doesn't exist, reset to 0
            $sanitized['default_category'] = 0;
            $this->add_admin_notice(
                __('Selected default category no longer exists. Default category has been reset.', 'affiliate-product-showcase'),
                'warning'
            );
        }
    }
    
    $sanitized['enable_category_hierarchy'] = isset($input['enable_category_hierarchy']);
    $sanitized['category_display_style'] = in_array($input['category_display_style'] ?? 'grid', ['grid', 'list', 'compact']) ? $input['category_display_style'] : 'grid';
    $sanitized['category_products_per_page'] = intval($input['category_products_per_page'] ?? 12);
    $sanitized['category_products_per_page'] = max(6, min(48, $sanitized['category_products_per_page']));
    $sanitized['category_default_sort'] = in_array($input['category_default_sort'] ?? 'date', ['name', 'price', 'date', 'popularity', 'random']) ? $input['category_default_sort'] : 'date';
    $sanitized['category_default_sort_order'] = in_array($input['category_default_sort_order'] ?? 'DESC', ['ASC', 'DESC']) ? $input['category_default_sort_order'] : 'DESC';
    $sanitized['show_category_description'] = isset($input['show_category_description']);
    $sanitized['show_category_image'] = isset($input['show_category_image']);
    $sanitized['show_category_count'] = isset($input['show_category_count']);
    $sanitized['enable_category_featured_products'] = isset($input['enable_category_featured_products']);
    $sanitized['category_featured_products_limit'] = intval($input['category_featured_products_limit'] ?? 4);
    $sanitized['category_featured_products_limit'] = max(1, min(8, $sanitized['category_featured_products_limit']));
    $sanitized['enable_empty_category_display'] = isset($input['enable_empty_category_display']);
    
    return $sanitized;
}

/**
 * Add admin notice helper
 *
 * @param string $message Notice message
 * @param string $type Notice type
 * @return void
 * @since 2.1.0
 */
private function add_admin_notice(string $message, string $type = 'info'): void {
    add_action('admin_notices', function() use ($message, $type) {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($type),
            wp_kses_post($message)
        );
    });
}
```

---

### Issue 3.12: Potential Performance Issue - N+1 Query

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`](wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php)

**Location:** Lines 520-526

**Severity:** MEDIUM  
**Type:** Code Quality - Performance

**Description:**
Fetches all categories then loops to delete meta. Inefficient for large datasets.

**Solution:**
Use direct SQL query for bulk operations.

**Implementation Steps:**
1. Open `CategoryRepository.php`
2. Locate `remove_default_from_all_categories()` method
3. Replace with direct SQL query
4. Add proper escaping and error handling

**Expected Code Change:**
```php
/**
 * Remove default flag from all categories
 *
 * Uses direct SQL for better performance with large datasets.
 *
 * @return void
 * @since 1.1.0
 */
public function remove_default_from_all_categories(): void {
    global $wpdb;
    
    // Use direct SQL for better performance
    // Delete new format meta
    $wpdb->delete(
        $wpdb->termmeta,
        ['meta_key' => '_aps_category_is_default']
    );
    
    // Delete legacy format meta
    $wpdb->delete(
        $wpdb->termmeta,
        ['meta_key' => 'aps_category_is_default']
    );
    
    // Clear cache to ensure consistency
    wp_cache_flush();
}
```

---

### Issue 3.13: Missing Null Check

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Models/Category.php`](wp-content/plugins/affiliate-product-showcase/src/Models/Category.php)

**Location:** Lines 233-237

**Severity:** LOW  
**Type:** Code Quality - Safety

**Description:**
No null check before casting `$term->term_id`.

**Solution:**
Add validation before using term properties.

**Implementation Steps:**
1. Open `Category.php`
2. Locate `from_wp_term()` method
3. Add validation for term properties
4. Add null check before casting

**Expected Code Change:**
```php
/**
 * Create Category from WP_Term
 *
 * Factory method to create Category instance from WP_Term object.
 *
 * @param \WP_Term $term WordPress term object
 * @return self Category instance
 * @throws \InvalidArgumentException If term is not a category
 * @since 1.0.0
 */
public static function from_wp_term( \WP_Term $term ): self {
    if ($term->taxonomy !== Constants::TAX_CATEGORY) {
        throw new \InvalidArgumentException(
            sprintf(
                'Term must be a category, got taxonomy: %s',
                $term->taxonomy
            )
        );
    }

    // Validate term has required properties
    if (!isset($term->term_id) || empty($term->term_id)) {
        throw new \InvalidArgumentException('Invalid term: missing term_id');
    }

    // Get category metadata with legacy fallback
    $featured = (bool) TermMetaHelper::get_with_fallback( $term->term_id, 'featured', 'aps_category_' );
    $image_url = TermMetaHelper::get_with_fallback( $term->term_id, 'image', 'aps_category_' ) ?: null;
    $sort_order = TermMetaHelper::get_with_fallback( $term->term_id, 'sort_order', 'aps_category_' ) ?: 'date';
    $status = StatusValidator::validate(TermMetaHelper::get_with_fallback( $term->term_id, 'status', 'aps_category_' ));
    $is_default = (bool) TermMetaHelper::get_with_fallback( $term->term_id, 'is_default', 'aps_category_' );

    // Check if this is global default category
    $global_default_id = get_option( 'aps_default_category_id', 0 );
    $is_default = $is_default || ( (int) $global_default_id === (int) $term->term_id );

    return new self(
        (int) $term->term_id,
        $term->name,
        $term->slug,
        $term->description ?? '',
        (int) $term->parent,
        (int) $term->count,
        $featured,
        $image_url,
        $sort_order,
        $term->term_group ? date( 'Y-m-d H:i:s', $term->term_group ) : current_time( 'mysql' ),
        $status,
        $is_default
    );
}
```

---

### Issue 3.14: Inconsistent Error Messages

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`](wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php)

**Locations:**
- Line 471: `'message' => __('Failed to update category', 'affiliate-product-showcase')`
- Line 786: `'message' => __('Failed to create category', 'affiliate-product-showcase')`
- Line 806: `'message' => __('An unexpected error occurred', 'affiliate-product-showcase')`

**Severity:** LOW  
**Type:** Code Quality - Consistency

**Description:**
Inconsistent error message formatting across different methods.

**Solution:**
Use consistent error message format with error codes.

**Implementation Steps:**
1. Already addressed in Issue 1.4 with generic error messages
2. Define error code constants

**Expected Code Changes:**
```php
// Add error code constants at top of class:
final class CategoriesController extends RestController {
    /**
     * Error codes
     *
     * @var string
     * @since 2.1.0
     */
    private const ERROR_CATEGORY_NOT_FOUND = 'category_not_found';
    private const ERROR_CATEGORY_UPDATE = 'category_update_error';
    private const ERROR_CATEGORY_CREATE = 'category_creation_error';
    private const ERROR_SERVER_ERROR = 'server_error';
    private const ERROR_INVALID_NONCE = 'invalid_nonce';
    private const ERROR_RATE_LIMIT = 'rate_limit_exceeded';
    private const ERROR_TAXONOMY_NOT_REGISTERED = 'taxonomy_not_registered';
    
    // ... existing code ...
}
```

---

### Issue 3.15: Unused/Placeholder Methods

**File:** [`wp-content/plugins/affiliate-product-showcase/src/Factories/CategoryFactory.php`](wp-content/plugins/affiliate-product-showcase/src/Factories/CategoryFactory.php)

**Location:** Lines 175-198

**Severity:** LOW  
**Type:** Code Quality - Dead Code

**Description:**
Method `build_tree()` is incomplete and marked as placeholder.

**Solution:**
Either complete implementation or remove the method.

**Implementation Steps:**
1. Open `CategoryFactory.php`
2. Review `build_tree()` method
3. Either:
   a. Complete the implementation to build hierarchical tree
   b. Remove the method and add @deprecated tag

**Expected Code Change (Option A - Complete Implementation):**
```php
/**
 * Build hierarchical tree from flat category list
 *
 * Creates a nested structure where each category has a children property.
 *
 * @param array<int, Category> $categories Flat array of categories
 * @return array<int, Category> Hierarchical tree of top-level categories with children
 * @since 1.0.0
 */
public static function build_tree( array $categories ): array {
    $tree = [];
    $lookup = [];
    $children = [];

    // Build lookup table and collect children
    foreach ( $categories as $category ) {
        $lookup[ $category->id ] = $category;
        if ( $category->parent_id > 0 && isset( $lookup[ $category->parent_id ] ) ) {
            $children[ $category->parent_id ][] = $category->id;
        }
    }

    // Build tree structure
    foreach ( $categories as $category ) {
        if ( $category->parent_id === 0 ) {
            // Top-level category - add to tree
            $tree[ $category->id ] = $category;
            // Add children if any
            if ( isset( $children[ $category->id ] ) ) {
                $category->children = [];
                foreach ( $children[ $category->id ] as $child_id ) {
                    $category->children[ $child_id ] = $lookup[ $child_id ];
                    // Recursively add grandchildren
                    if ( isset( $children[ $child_id ] ) ) {
                        self::addChildrenToCategory( $lookup[ $child_id ], $children, $lookup );
                    }
                }
            }
        }
    }

    return array_values( $tree );
}

/**
 * Recursively add children to category
 *
 * @param Category $category Category to add children to
 * @param array<int, array<int>> $children Children lookup
 * @param array<int, Category> $lookup Category lookup
 * @return void
 * @since 2.1.0
 */
private static function addChildrenToCategory(Category $category, array $children, array $lookup): void {
    if (!isset($category->children)) {
        $category->children = [];
    }
    
    if (isset($children[$category->id])) {
        foreach ($children[$category->id] as $child_id) {
            $category->children[$child_id] = $lookup[$child_id];
            if (isset($children[$child_id])) {
                self::addChildrenToCategory($lookup[$child_id], $children, $lookup);
            }
        }
    }
}
```

**Expected Code Change (Option B - Remove with Deprecation):**
```php
/**
 * Build hierarchical tree from flat category list
 *
 * @deprecated 2.1.0 Use Category::get_children() instead
 * @see Category::get_children()
 *
 * @param array<int, Category> $categories Flat array of categories
 * @return array<int, Category> Hierarchical tree of categories
 * @since 1.0.0
 */
#[\Deprecated('2.1.0', 'Use Category::get_children() instead')]
public static function build_tree( array $categories ): array {
    _deprecated_function( __METHOD__, '2.1.0', 'Category::get_children()' );
    
    $tree = [];
    $lookup = [];

    // Build lookup table
    foreach ( $categories as $category ) {
        $lookup[ $category->id ] = $category;
    }

    // Build tree structure
    foreach ( $categories as $category ) {
        if ( $category->parent_id === 0 ) {
            // Top-level category
            $tree[] = $category;
        } elseif ( isset( $lookup[ $category->parent_id ] ) ) {
            // Child category - add to parent's children
            $parent = $lookup[ $category->parent_id ];
            // Note: We'd need to modify Category model to store children
            // For now, this is a placeholder for future enhancement
        }
    }

    return $tree;
}
```

---

## Verification Checklist

For each issue, verify the following:

### Pre-Implementation Verification
- [ ] Issue location confirmed in source file
- [ ] Issue severity assessed correctly
- [ ] Solution approach is appropriate
- [ ] No dependencies on other Phase 3 issues

### Post-Implementation Verification
- [ ] Code change implemented as specified
- [ ] No syntax errors in modified file
- [ ] No PHP warnings/errors on page load
- [ ] Original issue is resolved
- [ ] No new code quality issues introduced
- [ ] No existing functionality broken

### Functional Testing
- [ ] All category operations work correctly
- [ ] Constants are used consistently
- [ ] Methods have appropriate length
- [ ] Error handling is consistent
- [ ] Type hints are complete
- [ ] Documentation is complete

### Code Quality Check
- [ ] Code follows PSR-12 standards
- [ ] Code follows WordPress coding standards
- [ ] PHPDoc comments are complete
- [ ] No TODO/FIXME comments left in code
- [ ] No dead code introduced
- [ ] Performance is not degraded

---

## Full Phase Verification

After completing all issues in this phase:

### Regression Testing
- [ ] All category-related functionality works as before
- [ ] No performance degradation
- [ ] No new console errors
- [ ] No PHP errors in debug log
- [ ] No database errors

### Code Quality Analysis
- [ ] All 15 quality issues resolved
- [ ] No new quality issues introduced
- [ ] Code is more maintainable
- [ ] Documentation is complete

### Standards Compliance
- [ ] PSR-12 standards followed
- [ ] WordPress coding standards followed
- [ ] Type hints are complete
- [ ] PHPDoc is comprehensive

---

## Sign-Off

**Implementation Start Date:** _______________  
**Implementation End Date:** _______________  
**Implemented By:** _______________  
**Reviewed By:** _______________  
**All Issues Resolved:** [ ] Yes [ ] No  
**No New Issues Introduced:** [ ] Yes [ ] No  
**Ready for Phase 4:** [ ] Yes [ ] No

**Notes:**
_________________________________________________________________________
_________________________________________________________________________
_________________________________________________________________________
