# Phase 2: Code Deduplication - Implementation Plan

**Phase Duration:** Weeks 2-3  
**Priority:** MEDIUM  
**Status:** Pending Implementation  
**Dependencies:** Phase 1 (Security Fixes) must be completed first

---

## Overview

This phase addresses code duplication issues identified in category-related files. The goal is to extract common patterns into reusable service classes and utilities, improving maintainability and reducing code redundancy.

---

## Issues to Resolve

### Issue 2.1: Duplicate Default Category Logic

**Files Affected:**
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php) (Lines 224-258)
- [`wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`](wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php) (Lines 502-511)

**Severity:** MEDIUM  
**Type:** Code Duplication

**Description:**
Default category logic is duplicated across two files. Both files contain similar code to:
1. Remove default flag from all categories
2. Set specific category as default
3. Update global default category option

**Solution:**
Create a dedicated `DefaultCategoryService` class to handle all default category operations.

**Implementation Steps:**
1. Create new file: `src/Services/DefaultCategoryService.php`
2. Implement service class with methods:
   - `setDefaultCategory(int $category_id): void`
   - `removeDefaultFromAll(): void`
   - `getDefault(): ?Category`
   - `isDefault(int $category_id): bool`
3. Update `CategoryFields.php` to use service
4. Update `CategoryRepository.php` to use service
5. Remove duplicate code from both files

**Expected New File Structure:**
```php
<?php
/**
 * Default Category Service
 *
 * Handles default category operations including setting, removing,
 * and retrieving the default category.
 *
 * @package AffiliateProductShowcase\Services
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Models\Category;

/**
 * Default Category Service
 *
 * Centralized service for managing default category operations.
 *
 * @package AffiliateProductShowcase\Services
 * @since 2.1.0
 */
final class DefaultCategoryService {
    /**
     * Category repository
     *
     * @var CategoryRepository
     */
    private CategoryRepository $repository;

    /**
     * Option key for default category ID
     *
     * @var string
     */
    private const DEFAULT_CATEGORY_OPTION = 'aps_default_category_id';

    /**
     * Constructor
     *
     * @param CategoryRepository $repository Category repository
     */
    public function __construct(CategoryRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Set category as default
     *
     * Removes default flag from all other categories and sets
     * the specified category as default.
     *
     * @param int $category_id Category ID to set as default
     * @return void
     */
    public function setDefaultCategory(int $category_id): void {
        // Remove default flag from all other categories
        $this->removeDefaultFromAll();

        // Set this category as default
        update_term_meta($category_id, '_aps_category_is_default', 1);
        delete_term_meta($category_id, 'aps_category_is_default');

        // Update global option
        update_option(self::DEFAULT_CATEGORY_OPTION, $category_id);
    }

    /**
     * Remove default flag from all categories
     *
     * @return void
     */
    public function removeDefaultFromAll(): void {
        $categories = $this->repository->all();
        foreach ($categories as $category) {
            delete_term_meta($category->id, '_aps_category_is_default');
            delete_term_meta($category->id, 'aps_category_is_default');
        }
    }

    /**
     * Get default category
     *
     * @return Category|null Default category or null if not set
     */
    public function getDefault(): ?Category {
        $default_id = get_option(self::DEFAULT_CATEGORY_OPTION, 0);
        if ($default_id > 0) {
            return $this->repository->find($default_id);
        }
        return null;
    }

    /**
     * Check if category is default
     *
     * @param int $category_id Category ID to check
     * @return bool True if category is default
     */
    public function isDefault(int $category_id): bool {
        $value = get_term_meta($category_id, '_aps_category_is_default', true);
        $global_default = get_option(self::DEFAULT_CATEGORY_OPTION, 0);
        
        return $value === '1' || (int) $global_default === $category_id;
    }
}
```

**Expected Code Changes in CategoryFields.php:**
```php
// Add property
private DefaultCategoryService $default_category_service;

// Update constructor
public function __construct(?CategoryRepository $repository = null, ?DefaultCategoryService $default_category_service = null) {
    $this->repository = $repository ?? new CategoryRepository();
    $this->default_category_service = $default_category_service ?? new DefaultCategoryService($this->repository);
}

// Replace lines 224-258 with:
if ($is_default === '1') {
    $this->default_category_service->setDefaultCategory($category_id);
    
    // Get category name for notice
    $category = get_term($category_id, 'aps_category');
    $category_name = $category && !is_wp_error($category) ? $category->name : sprintf('Category #%d', $category_id);
    
    // Add admin notice for auto-assignment feedback
    add_action('admin_notices', function() use ($category_name) {
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            wp_kses_post(
                sprintf(
                    __('%s has been set as default category. Products without a category will be automatically assigned to this category.', 'affiliate-product-showcase'),
                    esc_html($category_name)
                )
            )
        );
    });
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
```

---

### Issue 2.2: Duplicate Legacy Meta Deletion

**Files Affected:**
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php) (Lines 423-426)
- [`wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`](wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php) (Lines 582-596)

**Severity:** MEDIUM  
**Type:** Code Duplication

**Description:**
Legacy meta deletion patterns are repeated in multiple locations. Both files have similar code deleting new and legacy format meta keys.

**Solution:**
Create utility methods in `TermMetaHelper` class to handle legacy meta operations.

**Implementation Steps:**
1. Open `src/Helpers/TermMetaHelper.php`
2. Add method `deleteLegacyMeta(int $term_id, string $meta_key): void`
3. Add method `deleteAllLegacyMeta(int $term_id): void`
4. Update `CategoryFields.php` to use new methods
5. Update `CategoryRepository.php` to use new methods
6. Remove duplicate code from both files

**Expected Code Addition to TermMetaHelper.php:**
```php
/**
 * Delete legacy term meta key
 *
 * Deletes both new format (_aps_taxonomy_key) and legacy format
 * (aps_taxonomy_key) meta keys for a term.
 *
 * @param int $term_id Term ID
 * @param string $meta_key Meta key name (without prefix)
 * @return void
 * @since 2.1.0
 */
public static function deleteLegacyMeta(int $term_id, string $meta_key): void {
    delete_term_meta($term_id, '_aps_category_' . $meta_key);
    delete_term_meta($term_id, 'aps_category_' . $meta_key);
}

/**
 * Delete all legacy meta keys for a term
 *
 * Deletes all legacy meta keys for a category term.
 *
 * @param int $term_id Term ID
 * @return void
 * @since 2.1.0
 */
public static function deleteAllLegacyMeta(int $term_id): void {
    $meta_keys = ['featured', 'image', 'sort_order', 'status', 'is_default'];
    foreach ($meta_keys as $key) {
        self::deleteLegacyMeta($term_id, $key);
    }
}
```

**Expected Code Changes in CategoryFields.php:**
```php
// Replace lines 423-426 with:
private function delete_legacy_meta(int $term_id, string $meta_key): void {
    TermMetaHelper::deleteLegacyMeta($term_id, $meta_key);
}

// Update save_taxonomy_specific_fields to use new method:
// Line 187
$this->delete_legacy_meta($category_id, 'featured');

// Line 218
$this->delete_legacy_meta($category_id, 'image');

// Line 251
$this->delete_legacy_meta($category_id, 'is_default');
```

---

### Issue 2.3: Duplicate Status Validation

**Files Affected:**
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php) (Lines 1038-1043, 1051-1056)
- [`wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`](wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php) (Lines 364-373, 326-337)

**Severity:** MEDIUM  
**Type:** Code Duplication

**Description:**
Status validation logic is duplicated across multiple files. Both files have similar patterns for validating status from URL and validating category IDs.

**Solution:**
Create a shared `RequestValidator` utility class for common validation patterns.

**Implementation Steps:**
1. Create new file: `src/Rest/RequestValidator.php`
2. Implement validator class with methods:
   - `validateStatusFromUrl(): string`
   - `validateActionFromUrl(): string`
   - `validateCategoryId(int $id): ?WP_REST_Response`
3. Update `TaxonomyFieldsAbstract.php` to use validator
4. Update `CategoriesController.php` to use validator
5. Remove duplicate validation code from both files

**Expected New File Structure:**
```php
<?php
/**
 * Request Validator
 *
 * Provides common validation methods for REST and admin requests.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Request Validator
 *
 * Centralized validation for request parameters.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 2.1.0
 */
final class RequestValidator {
    /**
     * Valid status values
     *
     * @var array<string>
     */
    private const VALID_STATUSES = ['all', 'published', 'draft', 'trashed'];

    /**
     * Valid action values
     *
     * @var array<string>
     */
    private const VALID_ACTIONS = ['draft', 'trash', 'restore', 'delete_permanently'];

    /**
     * Get and validate status from URL
     *
     * @return string Valid status value
     */
    public static function validateStatusFromUrl(): string {
        if (isset($_GET['status']) && in_array($_GET['status'], self::VALID_STATUSES, true)) {
            return sanitize_text_field($_GET['status']);
        }
        return 'all';
    }

    /**
     * Get and validate action from URL
     *
     * @return string Valid action value
     */
    public static function validateActionFromUrl(): string {
        if (isset($_GET['do']) && in_array($_GET['do'], self::VALID_ACTIONS, true)) {
            return sanitize_text_field($_GET['do']);
        }
        return '';
    }

    /**
     * Validate category ID parameter
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|null Response if invalid, null otherwise
     */
    public static function validateCategoryId(WP_REST_Request $request): ?WP_REST_Response {
        $category_id = $request->get_param('id');
        if (empty($category_id)) {
            return new WP_REST_Response([
                'message' => __('Category ID is required.', 'affiliate-product-showcase'),
                'code' => 'missing_category_id',
            ], 400);
        }
        return null;
    }
}
```

**Expected Code Changes in TaxonomyFieldsAbstract.php:**
```php
// Add use statement
use AffiliateProductShowcase\Rest\RequestValidator;

// Replace get_valid_status_from_url method (Lines 1038-1043) with:
private function get_valid_status_from_url(): string {
    return RequestValidator::validateStatusFromUrl();
}

// Replace get_valid_action_from_url method (Lines 1051-1056) with:
private function get_valid_action_from_url(): string {
    return RequestValidator::validateActionFromUrl();
}
```

---

### Issue 2.4: Duplicate Error Response Pattern

**Files Affected:**
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php) (Lines 719-763)
- [`wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`](wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php) (Lines 404-414, 430-444, 489-503)

**Severity:** MEDIUM  
**Type:** Code Duplication

**Description:**
Error response patterns are repeated across multiple methods. The pattern of checking errors and returning early is duplicated.

**Solution:**
Create a `ValidationMiddleware` class using chain of responsibility pattern.

**Implementation Steps:**
1. Create new file: `src/Rest/ValidationMiddleware.php`
2. Implement middleware class with:
   - `addValidator(callable $validator): self`
   - `validate(WP_REST_Request $request): ?WP_REST_Response`
3. Update `CategoriesController.php` to use middleware
4. Remove duplicate error checking code

**Expected New File Structure:**
```php
<?php
/**
 * Validation Middleware
 *
 * Provides chain-of-responsibility pattern for request validation.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use WP_REST_Request;
use WP_REST_Response;
use AffiliateProductShowcase\Plugin\Constants;

/**
 * Validation Middleware
 *
 * Chains validation checks for REST requests.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 2.1.0
 */
final class ValidationMiddleware {
    /**
     * Validators to run
     *
     * @var array<callable>
     */
    private array $validators = [];

    /**
     * Add a validator to the chain
     *
     * @param callable $validator Validator function returning WP_REST_Response or null
     * @return self
     */
    public function addValidator(callable $validator): self {
        $this->validators[] = $validator;
        return $this;
    }

    /**
     * Run all validators in sequence
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|null First error response or null if all pass
     */
    public function validate(WP_REST_Request $request): ?WP_REST_Response {
        foreach ($this->validators as $validator) {
            $error = $validator($request);
            if ($error !== null) {
                return $error;
            }
        }
        return null;
    }

    /**
     * Create taxonomy exists validator
     *
     * @return callable Validator function
     */
    public static function taxonomyExists(): callable {
        return function(WP_REST_Request $request): ?WP_REST_Response {
            if (!taxonomy_exists(Constants::TAX_CATEGORY)) {
                return new WP_REST_Response([
                    'message' => sprintf(
                        __('Taxonomy %s is not registered. Please ensure plugin is properly activated.', 'affiliate-product-showcase'),
                        Constants::TAX_CATEGORY
                    ),
                    'code' => 'taxonomy_not_registered',
                ], 500);
            }
            return null;
        };
    }

    /**
     * Create nonce validator
     *
     * @return callable Validator function
     */
    public static function nonceVerified(string $action): callable {
        return function(WP_REST_Request $request) use ($action): ?WP_REST_Response {
            $nonce = $request->get_header('X-WP-Nonce');
            if (empty($nonce) || !wp_verify_nonce($nonce, 'wp_rest')) {
                return new WP_REST_Response([
                    'message' => __('Invalid nonce. Please refresh page and try again.', 'affiliate-product-showcase'),
                    'code' => 'invalid_nonce',
                ], 403);
            }
            return null;
        };
    }

    /**
     * Create category ID validator
     *
     * @return callable Validator function
     */
    public static function categoryIdValid(): callable {
        return function(WP_REST_Request $request): ?WP_REST_Response {
            $category_id = $request->get_param('id');
            if (empty($category_id)) {
                return new WP_REST_Response([
                    'message' => __('Category ID is required.', 'affiliate-product-showcase'),
                    'code' => 'missing_category_id',
                ], 400);
            }
            return null;
        };
    }

    /**
     * Create category exists validator
     *
     * @param CategoryRepository $repository Category repository
     * @return callable Validator function
     */
    public static function categoryExists(CategoryRepository $repository): callable {
        return function(WP_REST_Request $request) use ($repository): ?WP_REST_Response {
            $category_id = (int) $request->get_param('id');
            $category = $repository->find($category_id);
            if ($category === null) {
                return new WP_REST_Response([
                    'message' => __('Category not found.', 'affiliate-product-showcase'),
                    'code' => 'category_not_found',
                ], 404);
            }
            return null;
        };
    }
}
```

**Expected Code Changes in CategoriesController.php:**
```php
// Add property
private ValidationMiddleware $validation_middleware;

// Update constructor
public function __construct(CategoryRepository $repository) {
    $this->rate_limiter = new RateLimiter();
    $this->repository = $repository;
    
    // Build validation middleware
    $this->validation_middleware = (new ValidationMiddleware())
        ->addValidator(ValidationMiddleware::taxonomyExists())
        ->addValidator(ValidationMiddleware::nonceVerified('wp_rest'))
        ->addValidator(ValidationMiddleware::categoryIdValid())
        ->addValidator(ValidationMiddleware::categoryExists($repository));
}

// Update get_item method (Lines 402-417):
public function get_item(WP_REST_Request $request): WP_REST_Response {
    // Run validation middleware
    if ($error = $this->validation_middleware->validate($request)) {
        return $error;
    }

    return $this->respond(
        $this->repository->find((int) $request->get_param('id'))->to_array(),
        200
    );
}

// Similar updates for update(), delete(), trash(), restore(), delete_permanently() methods
```

---

### Issue 2.5: Duplicate Admin Notice Rendering

**Files Affected:**
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php) (Lines 237-247, 435-441)
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php) (Lines 186-194)
- [`wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php) (Lines 719-763)

**Severity:** MEDIUM  
**Type:** Code Duplication

**Description:**
Admin notice rendering patterns are repeated across multiple files. Similar code for adding admin notices with success/error/warning types.

**Solution:**
Create a centralized `NoticeService` class for all admin notice operations.

**Implementation Steps:**
1. Create new file: `src/Services/NoticeService.php`
2. Implement service class with methods:
   - `success(string $message): void`
   - `error(string $message): void`
   - `warning(string $message): void`
   - `info(string $message): void`
   - `transient(string $type, string $message, int $duration): void`
3. Update `CategoryFields.php` to use service
4. Update `CategoryFormHandler.php` to use service
5. Update `TaxonomyFieldsAbstract.php` to use service
6. Remove duplicate notice code from all files

**Expected New File Structure:**
```php
<?php
/**
 * Notice Service
 *
 * Centralized service for displaying WordPress admin notices.
 *
 * @package AffiliateProductShowcase\Services
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

/**
 * Notice Service
 *
 * Handles all admin notice operations with consistent formatting.
 *
 * @package AffiliateProductShowcase\Services
 * @since 2.1.0
 */
final class NoticeService {
    /**
     * Notice prefix for CSS classes
     *
     * @var string
     */
    private const NOTICE_PREFIX = 'aps-notice';

    /**
     * Auto-dismiss duration in milliseconds
     *
     * @var int
     */
    private const AUTO_DISMISS_DURATION = 3000;

    /**
     * Display success notice
     *
     * @param string $message Notice message
     * @return void
     */
    public function success(string $message): void {
        $this->render('success', $message);
    }

    /**
     * Display error notice
     *
     * @param string $message Notice message
     * @return void
     */
    public function error(string $message): void {
        $this->render('error', $message);
    }

    /**
     * Display warning notice
     *
     * @param string $message Notice message
     * @return void
     */
    public function warning(string $message): void {
        $this->render('warning', $message);
    }

    /**
     * Display info notice
     *
     * @param string $message Notice message
     * @return void
     */
    public function info(string $message): void {
        $this->render('info', $message);
    }

    /**
     * Display transient notice (auto-dismisses)
     *
     * @param string $type Notice type
     * @param string $message Notice message
     * @param int $duration Duration in milliseconds
     * @return void
     */
    public function transient(string $type, string $message, int $duration = self::AUTO_DISMISS_DURATION): void {
        add_action('admin_notices', function() use ($type, $message, $duration) {
            $this->renderTransient($type, $message, $duration);
        });
    }

    /**
     * Render notice to admin
     *
     * @param string $type Notice type (success, error, warning, info)
     * @param string $message Notice message
     * @return void
     */
    private function render(string $type, string $message): void {
        add_action('admin_notices', function() use ($type, $message) {
            printf(
                '<div class="notice notice-%s is-dismissible %s"><p>%s</p></div>',
                esc_attr($type),
                self::NOTICE_PREFIX,
                wp_kses_post($message)
            );
        });
    }

    /**
     * Render transient notice with auto-dismiss
     *
     * @param string $type Notice type
     * @param string $message Notice message
     * @param int $duration Duration in milliseconds
     * @return void
     */
    private function renderTransient(string $type, string $message, int $duration): void {
        // Remove existing notices
        echo '<script>';
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        echo '  var notices = document.querySelectorAll(".' . self::NOTICE_PREFIX . '");';
        echo '  notices.forEach(function(notice) { notice.remove(); });';
        echo '});';
        echo '</script>';

        // Add new notice
        printf(
            '<div class="notice notice-%s is-dismissible %s"><p>%s</p></div>',
            esc_attr($type),
            self::NOTICE_PREFIX,
            wp_kses_post($message)
        );

        // Auto-dismiss after duration
        echo '<script>';
        echo 'setTimeout(function() {';
        echo '  var notices = document.querySelectorAll(".' . self::NOTICE_PREFIX . '");';
        echo '  notices.forEach(function(notice) { notice.style.opacity = "0"; });';
        echo '  setTimeout(function() {';
        echo '    notices.forEach(function(notice) { notice.remove(); });';
        echo '  }, 200);';
        echo '}, ' . $duration . ');';
        echo '</script>';
    }
}
```

**Expected Code Changes in CategoryFields.php:**
```php
// Add property
private NoticeService $notice_service;

// Update constructor
public function __construct(
    ?CategoryRepository $repository = null,
    ?DefaultCategoryService $default_category_service = null,
    ?NoticeService $notice_service = null
) {
    $this->repository = $repository ?? new CategoryRepository();
    $this->default_category_service = $default_category_service ?? new DefaultCategoryService($this->repository);
    $this->notice_service = $notice_service ?? new NoticeService();
}

// Replace lines 237-247 with:
$this->notice_service->success(
    sprintf(
        __('%s has been set as default category. Products without a category will be automatically assigned to this category.', 'affiliate-product-showcase'),
        esc_html($category_name)
    )
);

// Replace lines 435-441 with:
$this->notice_service->warning(
    __('Invalid image URL. Please enter a valid HTTP or HTTPS URL.', 'affiliate-product-showcase')
);
```

---

## Verification Checklist

For each issue, verify the following:

### Pre-Implementation Verification
- [ ] Issue location confirmed in source file(s)
- [ ] Duplicate code pattern identified correctly
- [ ] Solution approach is appropriate
- [ ] No dependencies on other Phase 2 issues

### Post-Implementation Verification
- [ ] New service/utility class created
- [ ] All affected files updated to use new class
- [ ] Duplicate code removed from source files
- [ ] No syntax errors in modified files
- [ ] No PHP warnings/errors on page load
- [ ] Original functionality preserved

### Functional Testing
- [ ] Default category operations work correctly
- [ ] Legacy meta deletion works
- [ ] Status validation works
- [ ] Error responses display correctly
- [ ] Admin notices display correctly
- [ ] All category operations function as before

### Code Quality Check
- [ ] Code follows PSR-12 standards
- [ ] PHPDoc comments are complete
- [ ] No dead code introduced
- [ ] No TODO/FIXME comments left
- [ ] Code is testable

---

## Full Phase Verification

After completing all issues in this phase:

### Regression Testing
- [ ] All category-related functionality works as before
- [ ] No performance degradation
- [ ] No new console errors
- [ ] No PHP errors in debug log
- [ ] No database errors

### Code Duplication Analysis
- [ ] All 5 duplication issues resolved
- [ ] No new duplication introduced
- [ ] Code is more maintainable
- [ ] Service classes are reusable

### Integration Testing
- [ ] New service classes integrate correctly
- [ ] Dependency injection works properly
- [ ] No circular dependencies
- [ ] All tests pass

---

## Sign-Off

**Implementation Start Date:** _______________  
**Implementation End Date:** _______________  
**Implemented By:** _______________  
**Reviewed By:** _______________  
**All Issues Resolved:** [ ] Yes [ ] No  
**No New Issues Introduced:** [ ] Yes [ ] No  
**Ready for Phase 3:** [ ] Yes [ ] No

**Notes:**
_________________________________________________________________________
_________________________________________________________________________
_________________________________________________________________________
