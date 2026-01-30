# Category Files: Long-Term Implementation Plan

**Generated:** 2026-01-30  
**Duration:** 3-5 days  
**Focus:** Testing, optimization, and enterprise-grade improvements

---

## OVERVIEW

This plan addresses testing, performance optimization, and other improvements needed to reach enterprise-grade standards:

1. ✅ Add comprehensive unit tests
2. ✅ Add integration tests for REST API
3. ✅ Add E2E tests for category workflows
4. ✅ Optimize database queries with caching

**Estimated Time:** 24-40 hours (3-5 days)  
**Files to Create:** 15+ test files  
**Files to Modify:** 5  
**Test Coverage Target:** 90%+

---

## TASK 1: Comprehensive Unit Tests (10-14 hours)

### 1.1 Test Infrastructure Setup [2 hours]

**File:** `tests/Unit/Admin/CategoryTestBootstrap.php`

**Purpose:** Bootstrap unit tests for category functionality

**Code:**
```php
<?php
/**
 * Bootstrap file for category unit tests
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Define WP_TESTS_DIR if not set
if ( ! defined( 'WP_TESTS_DIR' ) ) {
    define( 'WP_TESTS_DIR', $_tests_dir );
}

// Load Composer autoloader
require_once __DIR__ . '/../../../vendor/autoload.php';

// Load test functions
require_once __DIR__ . '/includes/functions.php';

// Manually load the plugin being tested
require_once __DIR__ . '/../../../wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php';

// Activate plugin
tests_add_filter( 'muplugins_active', function( $active_plugins ) {
    $active_plugins[] = 'affiliate-product-showcase/affiliate-product-showcase.php';
    return $active_plugins;
} );

// Start up the WP testing environment
require __DIR__ . '/includes/bootstrap.php';
```

**File:** `tests/Unit/Admin/includes/functions.php`

**Code:**
```php
<?php
/**
 * Test helper functions
 */

/**
 * Create a test category
 *
 * @param array $args Category arguments
 * @return int Category term ID
 */
function create_test_category( array $args = [] ): int {
    $defaults = [
        'taxonomy' => 'aps_category',
        'name'     => 'Test Category ' . uniqid(),
    ];
    
    $args = wp_parse_args( $args, $defaults );
    $term = wp_insert_term( $args['name'], $args['taxonomy'], $args );
    
    if ( is_wp_error( $term ) ) {
        throw new RuntimeException( 'Failed to create test category: ' . $term->get_error_message() );
    }
    
    return (int) $term['term_id'];
}

/**
 * Delete a test category
 *
 * @param int $term_id Category term ID
 * @return void
 */
function delete_test_category( int $term_id ): void {
    wp_delete_term( $term_id, 'aps_category' );
}

/**
 * Create test product
 *
 * @param array $args Product arguments
 * @return int Product ID
 */
function create_test_product( array $args = [] ): int {
    $defaults = [
        'post_type'   => 'aps_product',
        'post_title'  => 'Test Product ' . uniqid(),
        'post_status' => 'publish',
        'post_content' => 'Test product content',
    ];
    
    $args = wp_parse_args( $args, $defaults );
    $post_id = wp_insert_post( $args );
    
    if ( is_wp_error( $post_id ) ) {
        throw new RuntimeException( 'Failed to create test product: ' . $post_id->get_error_message() );
    }
    
    return $post_id;
}

/**
 * Delete test product
 *
 * @param int $post_id Product ID
 * @return void
 */
function delete_test_product( int $post_id ): void {
    wp_delete_post( $post_id, true );
}
```

---

### 1.2 Category Model Unit Tests [2 hours]

**File:** `tests/Unit/Models/CategoryTest.php`

**Purpose:** Test Category model functionality

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use AffiliateProductShowcase\Models\Category;
use PHPUnit\Framework\TestCase;

/**
 * Category Model Unit Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Models
 * @since 1.0.0
 */
class CategoryTest extends TestCase {
    
    /**
     * Test category creation from WordPress term
     */
    public function test_from_wp_term(): void {
        // Create test category
        $term_id = create_test_category( [ 'name' => 'Test Category' ] );
        $wp_term = get_term( $term_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertInstanceOf( Category::class, $category );
        $this->assertEquals( $term_id, $category->id );
        $this->assertEquals( 'Test Category', $category->name );
        $this->assertEquals( 'test-category', $category->slug );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test category creation from array
     */
    public function test_from_array(): void {
        $data = [
            'id'          => 1,
            'name'        => 'Test Category',
            'slug'        => 'test-category',
            'description'  => 'Test description',
            'parent_id'    => 0,
            'count'        => 5,
            'featured'     => true,
            'image'       => 'https://example.com/image.jpg',
            'sort_order'  => 10,
            'status'       => 'published',
            'is_default'  => false,
        ];
        
        // Create Category from array
        $category = Category::from_array( $data );
        
        // Assertions
        $this->assertInstanceOf( Category::class, $category );
        $this->assertEquals( 1, $category->id );
        $this->assertEquals( 'Test Category', $category->name );
        $this->assertEquals( 'test-category', $category->slug );
        $this->assertEquals( 'Test description', $category->description );
        $this->assertEquals( 0, $category->parent_id );
        $this->assertEquals( 5, $category->count );
        $this->assertTrue( $category->featured );
        $this->assertEquals( 'https://example.com/image.jpg', $category->image );
        $this->assertEquals( 10, $category->sort_order );
        $this->assertEquals( 'published', $category->status );
        $this->assertFalse( $category->is_default );
    }
    
    /**
     * Test category with parent
     */
    public function test_from_wp_term_with_parent(): void {
        // Create parent category
        $parent_id = create_test_category( [ 'name' => 'Parent Category' ] );
        
        // Create child category
        $child_id = create_test_category( [ 
            'name'   => 'Child Category',
            'parent' => $parent_id,
        ] );
        $wp_term = get_term( $child_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertEquals( $parent_id, $category->parent_id );
        $this->assertTrue( $category->has_parent() );
        $this->assertEquals( $parent_id, $category->get_parent()->id );
        
        // Cleanup
        delete_test_category( $child_id );
        delete_test_category( $parent_id );
    }
    
    /**
     * Test category without parent
     */
    public function test_from_wp_term_without_parent(): void {
        // Create category without parent
        $term_id = create_test_category( [ 'name' => 'No Parent Category' ] );
        $wp_term = get_term( $term_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertEquals( 0, $category->parent_id );
        $this->assertFalse( $category->has_parent() );
        $this->assertNull( $category->get_parent() );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test featured property
     */
    public function test_featured_property(): void {
        // Create featured category
        $term_id = create_test_category( [ 
            'name'     => 'Featured Category',
            'featured'  => true,
        ] );
        $wp_term = get_term( $term_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertTrue( $category->featured );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test sort order
     */
    public function test_sort_order(): void {
        // Create categories with different sort orders
        $term_id1 = create_test_category( [ 
            'name'        => 'First Category',
            'sort_order'  => 10,
        ] );
        $term_id2 = create_test_category( [ 
            'name'        => 'Second Category',
            'sort_order'  => 20,
        ] );
        
        $wp_term1 = get_term( $term_id1, 'aps_category' );
        $wp_term2 = get_term( $term_id2, 'aps_category' );
        
        // Create Categories from WP_Term
        $category1 = Category::from_wp_term( $wp_term1 );
        $category2 = Category::from_wp_term( $wp_term2 );
        
        // Assertions
        $this->assertEquals( 10, $category1->sort_order );
        $this->assertEquals( 20, $category2->sort_order );
        
        // Cleanup
        delete_test_category( $term_id1 );
        delete_test_category( $term_id2 );
    }
    
    /**
     * Test status property
     */
    public function test_status_property(): void {
        // Create published category
        $term_id = create_test_category( [ 
            'name'   => 'Published Category',
            'status'  => 'published',
        ] );
        $wp_term = get_term( $term_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertEquals( 'published', $category->status );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test is_default property
     */
    public function test_is_default_property(): void {
        // Create default category
        $term_id = create_test_category( [ 
            'name'       => 'Default Category',
            'is_default' => true,
        ] );
        $wp_term = get_term( $term_id, 'aps_category' );
        
        // Create Category from WP_Term
        $category = Category::from_wp_term( $wp_term );
        
        // Assertions
        $this->assertTrue( $category->is_default );
        
        // Cleanup
        delete_test_category( $term_id );
    }
}
```

---

### 1.3 Category Repository Unit Tests [3 hours]

**File:** `tests/Unit/Repositories/CategoryRepositoryTest.php`

**Purpose:** Test CategoryRepository functionality

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Repositories;

use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Models\Category;
use PHPUnit\Framework\TestCase;

/**
 * Category Repository Unit Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Repositories
 * @since 1.0.0
 */
class CategoryRepositoryTest extends TestCase {
    
    private CategoryRepository $repository;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repository = new CategoryRepository();
    }
    
    protected function tearDown(): void {
        // Clean up all test categories
        $terms = get_terms( [
            'taxonomy'   => 'aps_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );
        
        foreach ( $terms as $term_id ) {
            wp_delete_term( (int) $term_id, 'aps_category' );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test find category by ID
     */
    public function test_find_by_id(): void {
        // Create test category
        $term_id = create_test_category( [ 'name' => 'Find Test Category' ] );
        
        // Find category
        $category = $this->repository->find( $term_id );
        
        // Assertions
        $this->assertInstanceOf( Category::class, $category );
        $this->assertEquals( $term_id, $category->id );
        $this->assertEquals( 'Find Test Category', $category->name );
    }
    
    /**
     * Test find non-existent category
     */
    public function test_find_non_existent_category(): void {
        // Try to find non-existent category
        $category = $this->repository->find( 999999 );
        
        // Assertion
        $this->assertNull( $category );
    }
    
    /**
     * Test find category with invalid ID
     */
    public function test_find_category_with_invalid_id(): void {
        $this->expectException( \Exception::class );
        $this->repository->find( -1 );
    }
    
    /**
     * Test create category
     */
    public function test_create_category(): void {
        // Create category data
        $category_data = new Category();
        $category_data->name = 'Create Test Category';
        $category_data->slug = 'create-test-category';
        $category_data->description = 'Test description';
        $category_data->featured = true;
        $category_data->sort_order = 10;
        $category_data->status = 'published';
        $category_data->is_default = false;
        
        // Create category
        $result = $this->repository->create( $category_data );
        
        // Assertions
        $this->assertTrue( $result );
        $this->assertIsInt( $result );
        $this->assertGreaterThan( 0, $result );
    }
    
    /**
     * Test create category with duplicate slug
     */
    public function test_create_category_with_duplicate_slug(): void {
        // Create first category
        create_test_category( [ 'name' => 'Duplicate Category', 'slug' => 'duplicate-category' ] );
        
        // Try to create category with same slug
        $category_data = new Category();
        $category_data->name = 'Duplicate Category 2';
        $category_data->slug = 'duplicate-category';
        $category_data->status = 'published';
        $category_data->is_default = false;
        
        // Should throw exception or return false
        $result = $this->repository->create( $category_data );
        
        // Assertion
        $this->assertFalse( $result );
    }
    
    /**
     * Test update category
     */
    public function test_update_category(): void {
        // Create test category
        $term_id = create_test_category( [ 
            'name'        => 'Update Test Category',
            'description'  => 'Original description',
            'featured'     => false,
        ] );
        
        // Get category
        $category = $this->repository->find( $term_id );
        
        // Update category
        $category->description = 'Updated description';
        $category->featured = true;
        
        $result = $this->repository->update( $category );
        
        // Assertions
        $this->assertTrue( $result );
        
        // Verify update
        $updated = $this->repository->find( $term_id );
        $this->assertEquals( 'Updated description', $updated->description );
        $this->assertTrue( $updated->featured );
    }
    
    /**
     * Test delete category
     */
    public function test_delete_category(): void {
        // Create test category
        $term_id = create_test_category( [ 'name' => 'Delete Test Category' ] );
        
        // Verify category exists
        $category = $this->repository->find( $term_id );
        $this->assertNotNull( $category );
        
        // Delete category
        $result = $this->repository->delete( $term_id );
        
        // Assertions
        $this->assertTrue( $result );
        
        // Verify deletion
        $deleted = $this->repository->find( $term_id );
        $this->assertNull( $deleted );
    }
    
    /**
     * Test delete default category should fail
     */
    public function test_delete_default_category_should_fail(): void {
        // Create default category
        $term_id = create_test_category( [ 
            'name'       => 'Default Test Category',
            'is_default' => true,
        ] );
        
        // Try to delete default category
        $this->expectException( \Exception::class );
        $this->repository->delete( $term_id );
    }
    
    /**
     * Test list all categories
     */
    public function test_list_all_categories(): void {
        // Create multiple categories
        $term_id1 = create_test_category( [ 'name' => 'Category 1' ] );
        $term_id2 = create_test_category( [ 'name' => 'Category 2' ] );
        $term_id3 = create_test_category( [ 'name' => 'Category 3' ] );
        
        // List all categories
        $categories = $this->repository->list_all();
        
        // Assertions
        $this->assertIsArray( $categories );
        $this->assertGreaterThanOrEqual( 3, count( $categories ) );
        $this->assertContainsOnlyInstancesOf( Category::class, $categories );
    }
    
    /**
     * Test list published categories
     */
    public function test_list_published_categories(): void {
        // Create published category
        $published_id = create_test_category( [ 
            'name'   => 'Published Category',
            'status'  => 'published',
        ] );
        
        // Create draft category
        $draft_id = create_test_category( [ 
            'name'   => 'Draft Category',
            'status'  => 'draft',
        ] );
        
        // List published categories
        $categories = $this->repository->list_published();
        
        // Assertions
        $this->assertIsArray( $categories );
        $this->assertCount( 1, $categories );
        $this->assertEquals( $published_id, $categories[0]->id );
    }
    
    /**
     * Test count by status
     */
    public function test_count_by_status(): void {
        // Create published categories
        create_test_category( [ 'name' => 'Published 1', 'status' => 'published' ] );
        create_test_category( [ 'name' => 'Published 2', 'status' => 'published' ] );
        
        // Create draft categories
        create_test_category( [ 'name' => 'Draft 1', 'status' => 'draft' ] );
        create_test_category( [ 'name' => 'Draft 2', 'status' => 'draft' ] );
        
        // Count published
        $published_count = $this->repository->count_by_status( 'published' );
        $this->assertEquals( 2, $published_count );
        
        // Count draft
        $draft_count = $this->repository->count_by_status( 'draft' );
        $this->assertEquals( 2, $draft_count );
    }
}
```

---

### 1.4 CategoryFields Unit Tests [2 hours]

**File:** `tests/Unit/Admin/CategoryFieldsTest.php`

**Purpose:** Test CategoryFields admin functionality

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Admin;

use AffiliateProductShowcase\Admin\CategoryFields;
use AffiliateProductShowcase\Tests\Helpers\TestHelpers;
use PHPUnit\Framework\TestCase;

/**
 * Category Fields Unit Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Admin
 * @since 1.0.0
 */
class CategoryFieldsTest extends TestCase {
    
    private CategoryFields $category_fields;
    
    protected function setUp(): void {
        parent::setUp();
        $this->category_fields = new CategoryFields();
        // Set up WordPress environment
        wp_set_current_user( 1 ); // Admin user
    }
    
    protected function tearDown(): void {
        // Clean up test data
        TestHelpers::cleanup_test_data();
        parent::tearDown();
    }
    
    /**
     * Test get taxonomy
     */
    public function test_get_taxonomy(): void {
        $taxonomy = $this->category_fields->get_taxonomy();
        
        $this->assertEquals( 'aps_category', $taxonomy );
    }
    
    /**
     * Test get taxonomy label
     */
    public function test_get_taxonomy_label(): void {
        $label = $this->category_fields->get_taxonomy_label();
        
        $this->assertEquals( 'Category', $label );
    }
    
    /**
     * Test get meta prefix
     */
    public function test_get_meta_prefix(): void {
        $prefix = $this->category_fields->get_meta_prefix();
        
        $this->assertEquals( '_aps_category_', $prefix );
    }
    
    /**
     * Test field rendering
     */
    public function test_render_taxonomy_specific_fields(): void {
        // Start output buffer
        ob_start();
        $this->category_fields->render_taxonomy_specific_fields( 0 );
        $output = ob_get_clean();
        
        // Assertions
        $this->assertNotEmpty( $output );
        $this->assertStringContainsString( 'aps-category-name', $output );
        $this->assertStringContainsString( 'aps-category-description', $output );
    }
    
    /**
     * Test field saving
     */
    public function test_save_taxonomy_specific_fields(): void {
        // Create test category
        $term_id = create_test_category( [ 'name' => 'Save Test Category' ] );
        
        // Simulate POST data
        $_POST = [
            '_aps_category_name'        => 'Updated Name',
            '_aps_category_featured'     => '1',
            '_aps_category_image'        => 'https://example.com/image.jpg',
            '_aps_category_sort_order'   => '20',
        ];
        
        // Save fields
        $this->category_fields->save_taxonomy_specific_fields( $term_id );
        
        // Verify saved data
        $featured = get_term_meta( $term_id, '_aps_category_featured', true );
        $image = get_term_meta( $term_id, '_aps_category_image', true );
        $sort_order = get_term_meta( $term_id, '_aps_category_sort_order', true );
        
        $this->assertEquals( '1', $featured );
        $this->assertEquals( 'https://example.com/image.jpg', $image );
        $this->assertEquals( '20', $sort_order );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test default category auto-assignment
     */
    public function test_default_category_auto_assignment(): void {
        // Create test category
        $term_id = create_test_category( [ 
            'name'       => 'Default Test Category',
            'is_default' => true,
        ] );
        
        // Create test product
        $product_id = create_test_product();
        
        // Assign category to product
        wp_set_object_terms( $product_id, [ $term_id ], 'aps_category' );
        
        // Verify only one default exists
        $default_categories = get_terms( [
            'taxonomy'   => 'aps_category',
            'meta_query' => [
                [
                    'key'     => '_aps_category_is_default',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'hide_empty' => false,
        ] );
        
        $this->assertCount( 1, $default_categories );
        
        // Cleanup
        delete_test_product( $product_id );
        delete_test_category( $term_id );
    }
}
```

---

### 1.5 Taxonomy Traits Unit Tests [1 hour]

**File:** `tests/Unit/Admin/Traits/TaxonomyStatusManagerTest.php`

**Purpose:** Test TaxonomyStatusManager trait

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Admin\Traits;

use AffiliateProductShowcase\Admin\CategoryFields;
use AffiliateProductShowcase\Tests\Helpers\TestHelpers;
use PHPUnit\Framework\TestCase;

/**
 * Taxonomy Status Manager Unit Tests
 *
 * @package AffiliateProductShowcase\Tests\Unit\Admin\Traits
 * @since 2.0.0
 */
class TaxonomyStatusManagerTest extends TestCase {
    
    private CategoryFields $category_fields;
    
    protected function setUp(): void {
        parent::setUp();
        $this->category_fields = new CategoryFields();
    }
    
    protected function tearDown(): void {
        TestHelpers::cleanup_test_data();
        parent::tearDown();
    }
    
    /**
     * Test get term status with published status
     */
    public function test_get_term_status_published(): void {
        // Create published category
        $term_id = create_test_category( [ 
            'name'   => 'Published Test',
            'status'  => 'published',
        ] );
        
        // Get status
        $status = $this->category_fields->get_status_manager()->get_term_status( $term_id );
        
        // Assertion
        $this->assertEquals( 'published', $status );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test get term status with default published status
     */
    public function test_get_term_status_default_published(): void {
        // Create category without status (should default to published)
        $term_id = create_test_category( [ 
            'name' => 'Default Published Test',
        ] );
        
        // Get status
        $status = $this->category_fields->get_status_manager()->get_term_status( $term_id );
        
        // Assertion
        $this->assertEquals( 'published', $status );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test get is default
     */
    public function test_get_is_default(): void {
        // Create default category
        $term_id = create_test_category( [ 
            'name'       => 'Is Default Test',
            'is_default' => true,
        ] );
        
        // Get is_default
        $is_default = $this->category_fields->get_status_manager()->get_is_default( $term_id );
        
        // Assertion
        $this->assertEquals( '1', $is_default );
        
        // Cleanup
        delete_test_category( $term_id );
    }
    
    /**
     * Test get valid status from URL
     */
    public function test_get_valid_status_from_url(): void {
        // Set $_GET
        $_GET['status'] = 'published';
        
        // Get valid status
        $status = $this->category_fields->get_status_manager()->get_valid_status_from_url();
        
        // Assertion
        $this->assertEquals( 'published', $status );
        
        // Cleanup
        unset( $_GET['status'] );
    }
    
    /**
     * Test get valid status from URL with invalid status
     */
    public function test_get_valid_status_from_url_invalid(): void {
        // Set $_GET with invalid status
        $_GET['status'] = 'invalid_status';
        
        // Get valid status
        $status = $this->category_fields->get_status_manager()->get_valid_status_from_url();
        
        // Assertion - should default to 'all'
        $this->assertEquals( 'all', $status );
        
        // Cleanup
        unset( $_GET['status'] );
    }
    
    /**
     * Test get valid status from URL with no status
     */
    public function test_get_valid_status_from_url_no_status(): void {
        // Unset $_GET['status']
        unset( $_GET['status'] );
        
        // Get valid status
        $status = $this->category_fields->get_status_manager()->get_valid_status_from_url();
        
        // Assertion - should default to 'all'
        $this->assertEquals( 'all', $status );
    }
}
```

---

## TASK 2: Integration Tests for REST API (6-8 hours)

### 2.1 Test Infrastructure Setup [1 hour]

**File:** `tests/Integration/Rest/CategoriesApiTestBootstrap.php`

**Purpose:** Bootstrap integration tests for REST API

**Code:**
```php
<?php
/**
 * Bootstrap file for REST API integration tests
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

define( 'WP_TESTS_DIR', $_tests_dir );

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/../../../wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php';

tests_add_filter( 'muplugins_active', function( $active_plugins ) {
    $active_plugins[] = 'affiliate-product-showcase/affiliate-product-showcase.php';
    return $active_plugins;
} );

require __DIR__ . '/includes/bootstrap.php';

// Set up REST API server
$GLOBALS['wp_rest_server'] = new WP_REST_Server();
do_action( 'rest_api_init' );
```

---

### 2.2 CategoriesController Integration Tests [5 hours]

**File:** `tests/Integration/Rest/CategoriesControllerTest.php`

**Purpose:** Test CategoriesController REST endpoints

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Rest;

use WP_REST_Request;
use WP_REST_Response;
use PHPUnit\Framework\TestCase;

/**
 * Categories Controller Integration Tests
 *
 * @package AffiliateProductShowcase\Tests\Integration\Rest
 * @since 1.0.0
 */
class CategoriesControllerTest extends TestCase {
    
    private $server;
    private $namespace = 'aps/v1';
    
    protected function setUp(): void {
        parent::setUp();
        $this->server = $GLOBALS['wp_rest_server'];
        
        // Create test user with permissions
        $this->user_id = wp_create_user( [
            'user_login' => 'test_rest_user',
            'user_pass'  => 'test_pass',
            'user_email' => 'test@example.com',
        ] );
        $this->user = new WP_User( $this->user_id );
        $this->user->set_role( 'administrator' );
        wp_set_current_user( $this->user_id );
    }
    
    protected function tearDown(): void {
        // Clean up test data
        if ( isset( $this->user_id ) ) {
            wp_delete_user( $this->user_id );
        }
        
        // Clean up all test categories
        $terms = get_terms( [
            'taxonomy'   => 'aps_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );
        
        foreach ( $terms as $term_id ) {
            wp_delete_term( (int) $term_id, 'aps_category' );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test GET /categories - list categories
     */
    public function test_get_categories(): void {
        // Create test categories
        create_test_category( [ 'name' => 'API Category 1', 'status' => 'published' ] );
        create_test_category( [ 'name' => 'API Category 2', 'status' => 'published' ] );
        create_test_category( [ 'name' => 'API Category 3', 'status' => 'draft' ] );
        
        // Make request
        $request = new WP_REST_Request( 'GET', '/' . $this->namespace . '/categories' );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 200, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertIsArray( $data );
        $this->assertGreaterThanOrEqual( 2, count( $data ) ); // At least published ones
    }
    
    /**
     * Test GET /categories/{id} - get single category
     */
    public function test_get_single_category(): void {
        // Create test category
        $term_id = create_test_category( [ 
            'name' => 'Single API Category',
            'description' => 'API test description',
            'status' => 'published',
        ] );
        
        // Make request
        $request = new WP_REST_Request( 'GET', '/' . $this->namespace . '/categories/' . $term_id );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 200, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertIsArray( $data );
        $this->assertArrayHasKey( 'id', $data );
        $this->assertArrayHasKey( 'name', $data );
        $this->assertEquals( 'Single API Category', $data['name'] );
        $this->assertEquals( 'API test description', $data['description'] );
    }
    
    /**
     * Test GET /categories/{id} - non-existent category
     */
    public function test_get_non_existent_category(): void {
        // Make request for non-existent category
        $request = new WP_REST_Request( 'GET', '/' . $this->namespace . '/categories/999999' );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 404, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertArrayHasKey( 'message', $data );
    }
    
    /**
     * Test POST /categories - create category
     */
    public function test_create_category(): void {
        $category_data = [
            'name'        => 'New API Category',
            'description'  => 'API created description',
            'slug'        => 'new-api-category',
            'status'       => 'published',
            'is_default'  => false,
        ];
        
        // Make request
        $request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/categories' );
        $request->set_body_params( $category_data );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 201, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertIsArray( $data );
        $this->assertArrayHasKey( 'id', $data );
        $this->assertGreaterThan( 0, $data['id'] );
        
        // Verify category was created
        $term = get_term( $data['id'], 'aps_category' );
        $this->assertNotFalse( $term );
        $this->assertEquals( 'New API Category', $term->name );
    }
    
    /**
     * Test POST /categories - validation errors
     */
    public function test_create_category_validation_errors(): void {
        // Test missing name
        $category_data = [
            'description' => 'No name category',
        ];
        
        $request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/categories' );
        $request->set_body_params( $category_data );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 400, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertArrayHasKey( 'message', $data );
    }
    
    /**
     * Test PUT /categories/{id} - update category
     */
    public function test_update_category(): void {
        // Create test category
        $term_id = create_test_category( [ 
            'name' => 'Update API Category',
            'description' => 'Original description',
        ] );
        
        $update_data = [
            'name'        => 'Updated API Category',
            'description'  => 'Updated description',
            'status'       => 'published',
        ];
        
        // Make request
        $request = new WP_REST_Request( 'PUT', '/' . $this->namespace . '/categories/' . $term_id );
        $request->set_body_params( $update_data );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 200, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertIsArray( $data );
        $this->assertArrayHasKey( 'id', $data );
        
        // Verify update
        $term = get_term( $term_id, 'aps_category' );
        $this->assertEquals( 'Updated API Category', $term->name );
        $this->assertEquals( 'Updated description', $term->description );
    }
    
    /**
     * Test DELETE /categories/{id} - delete category
     */
    public function test_delete_category(): void {
        // Create test category
        $term_id = create_test_category( [ 
            'name' => 'Delete API Category',
        ] );
        
        // Verify category exists
        $term = get_term( $term_id, 'aps_category' );
        $this->assertNotFalse( $term );
        
        // Make request
        $request = new WP_REST_Request( 'DELETE', '/' . $this->namespace . '/categories/' . $term_id );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 200, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertIsArray( $data );
        $this->assertArrayHasKey( 'message', $data );
        
        // Verify deletion
        $deleted_term = get_term( $term_id, 'aps_category' );
        $this->assertFalse( $deleted_term );
    }
    
    /**
     * Test DELETE /categories/{id} - delete default category fails
     */
    public function test_delete_default_category_fails(): void {
        // Create default category
        $term_id = create_test_category( [ 
            'name'       => 'Default API Category',
            'is_default' => true,
        ] );
        
        // Make request
        $request = new WP_REST_Request( 'DELETE', '/' . $this->namespace . '/categories/' . $term_id );
        $response = $this->server->dispatch( $request );
        
        // Assertions
        $this->assertEquals( 403, $response->get_status() );
        
        $data = $response->get_data();
        $this->assertArrayHasKey( 'message', $data );
        $this->assertStringContainsString( 'Cannot delete default', $data['message'] );
    }
    
    /**
     * Test rate limiting on create
     */
    public function test_rate_limiting_on_create(): void {
        $category_data = [
            'name'   => 'Rate Limit Category',
            'status'  => 'published',
        ];
        
        // Make multiple requests quickly
        $responses = [];
        for ( $i = 0; $i < 25; $i++ ) {
            $request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/categories' );
            $request->set_body_params( $category_data + ['name' => 'Category ' . $i ] );
            $responses[] = $this->server->dispatch( $request );
        }
        
        // Check if any requests were rate limited
        $rate_limited = false;
        foreach ( $responses as $response ) {
            if ( $response->get_status() === 429 ) {
                $rate_limited = true;
                break;
            }
        }
        
        // Assertion - rate limiting should trigger
        $this->assertTrue( $rate_limited, 'Rate limiting should be enforced' );
    }
}
```

---

## TASK 3: E2E Tests for Category Workflows (8-10 hours)

### 3.1 Test Infrastructure Setup [1 hour]

**File:** `tests/E2E/CategoryWorkflowTest.php`

**Purpose:** E2E tests for category workflows

**Code:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\E2E;

use PHPUnit\Framework\TestCase;

/**
 * Category Workflow E2E Tests
 *
 * @package AffiliateProductShowcase\Tests\E2E
 * @since 1.0.0
 */
class CategoryWorkflowTest extends TestCase {
    
    private static $browser;
    private static $page;
    
    public static function setUpBeforeClass(): void {
        // Initialize browser
        self::$browser = new \Symfony\Component\BrowserKit\HttpBrowser();
        self::$page = admin_url( 'edit-tags.php?taxonomy=aps_category&post_type=aps_product' );
    }
    
    public static function tearDownAfterClass(): void {
        // Clean up browser
        self::$browser = null;
    }
    
    protected function setUp(): void {
        parent::setUp();
        // Log in as admin
        wp_set_current_user( 1 );
    }
    
    protected function tearDown(): void {
        // Clean up test data
        $terms = get_terms( [
            'taxonomy'   => 'aps_category',
            'hide_empty' => false,
            'fields'     => 'ids',
        ] );
        
        foreach ( $terms as $term_id ) {
            wp_delete_term( (int) $term_id, 'aps_category' );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test category CRUD workflow
     */
    public function test_category_crud_workflow(): void {
        // 1. Navigate to categories page
        $crawler = self::$browser->request( 'GET', self::$page );
        $this->assertEquals( 200, $crawler->getStatusCode() );
        $this->assertStringContainsString( 'Categories', $crawler->html() );
        
        // 2. Click "Add New Category"
        $add_link = $crawler->selectLink( 'Add New' );
        $this->assertNotNull( $add_link );
        
        // 3. Fill in category form
        $add_page = self::$browser->click( $add_link );
        $form = $add_page->selectButton( 'Add New Category' )->form();
        
        $this->fill_and_submit_category_form( $form, [
            'name'        => 'E2E Test Category',
            'description'  => 'E2E test description',
            'slug'        => 'e2e-test-category',
            'featured'     => true,
            'sort_order'  => 10,
            'status'       => 'published',
            'is_default'  => false,
        ] );
        
        // 4. Verify category was created
        $terms = get_terms( [
            'taxonomy'   => 'aps_category',
            'hide_empty' => false,
        ] );
        
        $test_category = null;
        foreach ( $terms as $term ) {
            if ( $term->name === 'E2E Test Category' ) {
                $test_category = $term;
                break;
            }
        }
        
        $this->assertNotNull( $test_category );
        $this->assertEquals( 'e2e-test-category', $test_category->slug );
        $this->assertEquals( 'E2E test description', $test_category->description );
        $this->assertEquals( 'published', get_term_meta( $test_category->term_id, '_aps_category_status', true ) );
        
        // 5. Edit category
        $edit_link = $crawler->selectLink( 'E2E Test Category' );
        $this->assertNotNull( $edit_link );
        
        $edit_page = self::$browser->click( $edit_link );
        $form = $edit_page->selectButton( 'Update' )->form();
        
        $form['taxonomy[name]'] = 'Updated E2E Category';
        $form['taxonomy[description]'] = 'Updated description';
        
        // Submit form
        $edit_page->selectButton( 'Update' )->click();
        
        // Verify update
        $updated_term = get_term_by( 'slug', 'e2e-test-category', 'aps_category' );
        $this->assertEquals( 'Updated E2E Category', $updated_term->name );
        $this->assertEquals( 'Updated description', $updated_term->description );
    }
    
    /**
     * Test default category workflow
     */
    public function test_default_category_workflow(): void {
        // Create multiple categories
        create_test_category( [ 'name' => 'First Category' ] );
        create_test_category( [ 'name' => 'Second Category' ] );
        
        // Navigate to categories page
        $crawler = self::$browser->request( 'GET', self::$page );
        
        // Click "Add New Category"
        $add_link = $crawler->selectLink( 'Add New' );
        $add_page = self::$browser->click( $add_link );
        $form = $add_page->selectButton( 'Add New Category' )->form();
        
        // Set as default
        $this->fill_and_submit_category_form( $form, [
            'name'       => 'Default E2E Category',
            'status'      => 'published',
            'is_default'  => true,
        ] );
        
        // Verify only one default exists
        $default_categories = get_terms( [
            'taxonomy'   => 'aps_category',
            'meta_query' => [
                [
                    'key'     => '_aps_category_is_default',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'hide_empty' => false,
        ] );
        
        $this->assertCount( 1, $default_categories );
        $this->assertEquals( 'Default E2E Category', $default_categories[0]->name );
    }
    
    /**
     * Test status filter workflow
     */
    public function test_status_filter_workflow(): void {
        // Create test categories
        create_test_category( [ 'name' => 'Published Category', 'status' => 'published' ] );
        create_test_category( [ 'name' => 'Draft Category', 'status' => 'draft' ] );
        create_test_category( [ 'name' => 'Trash Category', 'status' => 'trashed' ] );
        
        // Navigate to categories page
        $crawler = self::$browser->request( 'GET', self::$page );
        $this->assertStringContainsString( 'All', $crawler->html() );
        $this->assertStringContainsString( 'Published', $crawler->html() );
        $this->assertStringContainsString( 'Draft', $crawler->html() );
        $this->assertStringContainsString( 'Trash', $crawler->html() );
        
        // Click "Published" filter
        $published_link = $crawler->selectLink( 'Published' );
        $this->assertNotNull( $published_link );
        
        $published_page = self::$browser->click( $published_link );
        $this->assertStringContainsString( 'Published Category', $published_page->html() );
        $this->assertStringNotContainsString( 'Draft Category', $published_page->html() );
        
        // Click "Draft" filter
        $draft_link = $crawler->selectLink( 'Draft' );
        $draft_page = self::$browser->click( $draft_link );
        $this->assertStringContainsString( 'Draft Category', $draft_page->html() );
        $this->assertStringNotContainsString( 'Published Category', $draft_page->html() );
    }
    
    /**
     * Test bulk actions workflow
     */
    public function test_bulk_actions_workflow(): void {
        // Create multiple categories
        $term_id1 = create_test_category( [ 'name' => 'Bulk 1', 'status' => 'published' ] );
        $term_id2 = create_test_category( [ 'name' => 'Bulk 2', 'status' => 'published' ] );
        $term_id3 = create_test_category( [ 'name' => 'Bulk 3', 'status' => 'published' ] );
        
        // Navigate to categories page
        $crawler = self::$browser->request( 'GET', self::$page );
        
        // Select categories
        $form = $crawler->filter( 'form' )->form();
        $form['post'] = $term_id1;
        $form['post'] = $term_id2;
        $form['post'] = $term_id3;
        
        // Select "Move to Draft" bulk action
        $form['action'] = 'move_to_draft';
        
        // Submit
        $crawler->selectButton( 'Apply' )->click();
        
        // Verify categories moved to draft
        $term1 = get_term( $term_id1, 'aps_category' );
        $term2 = get_term( $term_id2, 'aps_category' );
        $term3 = get_term( $term_id3, 'aps_category' );
        
        $this->assertEquals( 'draft', get_term_meta( $term_id1, '_aps_category_status', true ) );
        $this->assertEquals( 'draft', get_term_meta( $term_id2, '_aps_category_status', true ) );
        $this->assertEquals( 'draft', get_term_meta( $term_id3, '_aps_category_status', true ) );
    }
    
    /**
     * Helper method to fill and submit category form
     */
    private function fill_and_submit_category_form( $form, array $data ): void {
        $form['taxonomy[name]'] = $data['name'] ?? '';
        $form['taxonomy[slug]'] = $data['slug'] ?? '';
        $form['taxonomy[description]'] = $data['description'] ?? '';
        $form['taxonomy[featured]'] = isset( $data['featured'] ) ? 1 : null;
        $form['taxonomy[sort_order]'] = $data['sort_order'] ?? '';
        $form['taxonomy[status]'] = $data['status'] ?? 'published';
        $form['taxonomy[is_default]'] = isset( $data['is_default'] ) ? 1 : null;
        
        $crawler = $form->selectButton( 'Add New Category' )->form()->submit();
    }
}
```

---

## TASK 4: Database Query Optimization with Caching (4-6 hours)

### 4.1 Implement Query Caching [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php`

**Changes:** Add caching to expensive queries

**Code:**
```php
/**
 * Get all categories with caching
 *
 * @return array<Category> Array of categories
 */
public function list_all(): array {
    $cache_key = 'aps_category_list_all';
    $cached = wp_cache_get( $cache_key, 'aps_categories' );
    
    if ( $cached !== false ) {
        return $cached;
    }
    
    $terms = get_terms( [
        'taxonomy'   => 'aps_category',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }

    $categories = array_map( fn( $term ) => Category::from_wp_term( $term ), $terms );
    
    // Cache for 1 hour
    wp_cache_set( $cache_key, $categories, 'aps_categories', 3600 );
    
    return $categories;
}

/**
 * Get published categories with caching
 *
 * @return array<Category> Array of published categories
 */
public function list_published(): array {
    $cache_key = 'aps_category_list_published';
    $cached = wp_cache_get( $cache_key, 'aps_categories' );
    
    if ( $cached !== false ) {
        return $cached;
    }
    
    $terms = get_terms( [
        'taxonomy'   => 'aps_category',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return [];
    }

    $categories = [];
    foreach ( $terms as $term ) {
        $status = get_term_meta( $term->term_id, '_aps_category_status', true );
        if ( $status === 'published' ) {
            $categories[] = Category::from_wp_term( $term );
        }
    }
    
    // Cache for 30 minutes (changes less frequent)
    wp_cache_set( $cache_key, $categories, 'aps_categories', 1800 );
    
    return $categories;
}

/**
 * Count categories by status with caching
 *
 * @param string $status Status to count
 * @return int Count
 */
public function count_by_status( string $status ): int {
    $cache_key = 'aps_category_count_' . $status;
    $cached = wp_cache_get( $cache_key, 'aps_categories' );
    
    if ( $cached !== false ) {
        return (int) $cached;
    }
    
    $terms = get_terms( [
        'taxonomy'   => 'aps_category',
        'hide_empty' => false,
        'fields'     => 'ids',
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return 0;
    }

    $count = 0;
    foreach ( $terms as $term_id ) {
        $term_status = get_term_meta( (int) $term_id, '_aps_category_status', true );
        
        if ( $status === 'all' ) {
            if ( $term_status !== 'trashed' ) {
                $count++;
            }
        } elseif ( $term_status === $status ) {
            $count++;
        }
    }
    
    // Cache for 5 minutes
    wp_cache_set( $cache_key, $count, 'aps_categories', 300 );
    
    return $count;
}

/**
 * Find category by ID with caching
 *
 * @param int $category_id Category ID
 * @return Category|null Category instance or null
 */
public function find( int $category_id ): ?Category {
    $cache_key = 'aps_category_' . $category_id;
    $cached = wp_cache_get( $cache_key, 'aps_categories' );
    
    if ( $cached !== false ) {
        return $cached;
    }
    
    if ( $category_id <= 0 ) {
        return null;
    }

    $term = get_term( $category_id, 'aps_category' );

    if ( ! $term || is_wp_error( $term ) ) {
        return null;
    }

    $category = Category::from_wp_term( $term );
    
    // Cache for 1 hour
    wp_cache_set( $cache_key, $category, 'aps_categories', 3600 );
    
    return $category;
}

/**
 * Clear category cache on update/delete
 *
 * @param int $category_id Category ID
 * @return void
 */
private function clear_cache( int $category_id ): void {
    // Clear specific category cache
    wp_cache_delete( 'aps_category_' . $category_id, 'aps_categories' );
    
    // Clear list caches
    wp_cache_delete( 'aps_category_list_all', 'aps_categories' );
    wp_cache_delete( 'aps_category_list_published', 'aps_categories' );
    
    // Clear count caches
    wp_cache_delete( 'aps_category_count_all', 'aps_categories' );
    wp_cache_delete( 'aps_category_count_published', 'aps_categories' );
    wp_cache_delete( 'aps_category_count_draft', 'aps_categories' );
    wp_cache_delete( 'aps_category_count_trashed', 'aps_categories' );
}
```

**Update existing methods to clear cache:**
```php
public function update( Category $category ): bool {
    // ... existing update logic ...
    
    // Clear cache after update
    $this->clear_cache( $category->id );
    
    return $result;
}

public function delete( int $category_id ): bool {
    // ... existing delete logic ...
    
    // Clear cache before deletion
    $this->clear_cache( $category_id );
    
    return $result;
}
```

---

### 4.2 Optimize Status Counting with SQL [2 hours]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyStatusManager.php`

**Changes:** Replace N+1 queries with direct SQL

**Code:**
```php
/**
 * Count terms by status with optimized SQL query
 *
 * @param string $status Status to count
 * @return int Count of terms
 */
protected function count_terms_by_status( string $status ): int {
    global $wpdb;
    
    $cache_key = 'aps_count_' . $this->taxonomy_fields->get_taxonomy() . '_' . $status;
    $cached = wp_cache_get( $cache_key, 'aps_categories' );
    
    if ( $cached !== false ) {
        return (int) $cached;
    }
    
    $prefix = $this->taxonomy_fields->get_meta_prefix() . 'status';
    $taxonomy = $this->taxonomy_fields->get_taxonomy();
    
    if ( $status === 'all' ) {
        // Count all non-trashed
        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT t.term_id)
             FROM {$wpdb->terms} t
             INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
             LEFT JOIN {$wpdb->termmeta} tm ON t.term_id = tm.term_id 
                 AND tm.meta_key = %s
             WHERE tt.taxonomy = %s 
                 AND (tm.meta_value IS NULL OR tm.meta_value != 'trashed')",
            $prefix,
            $taxonomy
        );
    } else {
        // Count by specific status
        $sql = $wpdb->prepare(
            "SELECT COUNT(DISTINCT t.term_id)
             FROM {$wpdb->terms} t
             INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
             LEFT JOIN {$wpdb->termmeta} tm ON t.term_id = tm.term_id 
                 AND tm.meta_key = %s
             WHERE tt.taxonomy = %s 
                 AND (tm.meta_value = %s OR (tm.meta_value IS NULL AND %s = 'published'))",
            $prefix,
            $taxonomy,
            $status,
            $status
        );
    }
    
    $count = (int) $wpdb->get_var( $sql );
    
    // Cache for 5 minutes
    wp_cache_set( $cache_key, $count, 'aps_categories', 300 );
    
    return $count;
}
```

---

## TASK 5: Additional Improvements (Optional 2-4 hours)

### 5.1 Add Keyboard Navigation Support [1 hour]

**File:** `wp-content/plugins/affiliate-product-showcase/assets/js/admin-category.js`

**Add code:**
```javascript
// Add keyboard shortcuts for status dropdowns
$(document).on('keydown', '.aps-term-status-select', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).trigger('change');
    }
});

// Add keyboard shortcuts for row actions
$(document).on('keydown', 'a[class*="aps_"]', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.location = $(this).attr('href');
    }
});
```

---

### 5.2 Improve Error Messages [1 hour]

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Traits/TaxonomyBulkActions.php`

**Update notices:**
```php
// Make error messages more specific
private function bulk_move_to_draft( array $term_ids, string $meta_prefix ): int {
    $count = 0;
    $skipped = 0;
    
    foreach ( $term_ids as $term_id ) {
        if ( ! $this->can_delete_term( (int) $term_id, $meta_prefix . 'is_default' ) ) {
            $skipped++;
            continue;
        }
        
        if ( update_term_meta( (int) $term_id, $meta_prefix . 'status', 'draft' ) !== false ) {
            $count++;
        }
    }
    
    // Store skipped count for notice
    if ( $skipped > 0 ) {
        set_transient( 'aps_bulk_draft_skipped', $skipped, 30 );
    }
    
    return $count;
}

// Update notice display
public function display_bulk_action_notices(): void {
    // ... existing notices ...
    
    // Add skipped notice
    $skipped = get_transient( 'aps_bulk_draft_skipped' );
    if ( $skipped && $skipped > 0 ) {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        printf(
            esc_html__( '%d category(s) were skipped because they are set as default. Default categories cannot be moved to draft or trash.', 'affiliate-product-showcase' ),
            $skipped
        );
        echo '</p></div>';
        delete_transient( 'aps_bulk_draft_skipped' );
    }
}
```

---

## TESTING CHECKLIST

### Unit Tests (Required)
- [ ] Category model tests pass
- [ ] CategoryRepository tests pass
- [ ] CategoryFields tests pass
- [ ] TaxonomyStatusManager tests pass
- [ ] TaxonomyColumnRenderer tests pass
- [ ] TaxonomyBulkActions tests pass
- [ ] TaxonomyAjaxHandler tests pass
- [ ] MetadataHelper tests pass
- [ ] SecurityHeaders tests pass

### Integration Tests (Required)
- [ ] GET /categories works
- [ ] GET /categories/{id} works
- [ ] POST /categories works
- [ ] PUT /categories/{id} works
- [ ] DELETE /categories/{id} works
- [ ] Rate limiting works
- [ ] Authentication works
- [ ] Authorization works

### E2E Tests (Required)
- [ ] Category CRUD workflow works
- [ ] Default category workflow works
- [ ] Status filter workflow works
- [ ] Bulk actions workflow works

### Performance Tests (Required)
- [ ] Query caching works
- [ ] Cache invalidation works
- [ ] SQL optimization improves performance
- [ ] No N+1 query issues

---

## SUMMARY

**Files to Create:** 15+
1. `tests/Unit/Admin/CategoryTestBootstrap.php`
2. `tests/Unit/Admin/includes/functions.php`
3. `tests/Unit/Models/CategoryTest.php`
4. `tests/Unit/Repositories/CategoryRepositoryTest.php`
5. `tests/Unit/Admin/CategoryFieldsTest.php`
6. `tests/Unit/Admin/Traits/TaxonomyStatusManagerTest.php`
7. `tests/Unit/Admin/Traits/TaxonomyColumnRendererTest.php`
8. `tests/Unit/Admin/Traits/TaxonomyBulkActionsTest.php`
9. `tests/Unit/Admin/Traits/TaxonomyAjaxHandlerTest.php`
10. `tests/Unit/Helpers/MetadataHelperTest.php`
11. `tests/Unit/Admin/Security/SecurityHeadersTest.php`
12. `tests/Integration/Rest/CategoriesApiTestBootstrap.php`
13. `tests/Integration/Rest/CategoriesControllerTest.php`
14. `tests/E2E/CategoryWorkflowTest.php`
15. `phpunit.xml.dist` (if not exists)

**Files to Modify:** 5
1. `src/Repositories/CategoryRepository.php`
2. `src/Admin/Traits/TaxonomyStatusManager.php`
3. `assets/js/admin-category.js`
4. `phpunit.xml.dist` (configure)
5. `phpcs.xml.dist` (configure)

**Estimated Time:** 24-40 hours (3-5 days)

**Expected Outcomes:**
- ✅ 90%+ test coverage
- ✅ All unit tests passing
- ✅ All integration tests passing
- ✅ All E2E tests passing
- ✅ Database queries optimized with caching
- ✅ No N+1 query issues
- ✅ Cache invalidation working correctly

---

**Next Steps:** Run tests, verify coverage, and deploy to production