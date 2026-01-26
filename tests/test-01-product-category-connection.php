<?php
/**
 * Test #1: Products ↔ Categories - Assign Single Category to Product
 *
 * This test verifies that a single category can be assigned to a product
 * and that relationship is saved correctly in the database.
 *
 * @package AffiliateProductShowcase\Tests
 * @since 1.0.0
 */

use AffiliateProductShowcase\Models\Product;

// Load WordPress
define('WP_USE_THEMES', false);
require_once dirname(__DIR__) . '/wp-load.php';

// Ensure plugin is active
if (!is_plugin_active('affiliate-product-showcase/affiliate-product-showcase.php')) {
    die('ERROR: Affiliate Product Showcase plugin is not active.' . PHP_EOL);
}

echo "=== Test #1: Assign Single Category to Product ===" . PHP_EOL . PHP_EOL;

$test_passed = true;
$errors = [];

// Step 1: Create a test category
echo "Step 1: Creating test category..." . PHP_EOL;
$term_data = wp_insert_term(
    'Test Electronics',
    'product_category',
    [
        'description' => 'Test category for connection testing',
        'slug' => 'test-electronics-' . time()
    ]
);

if (is_wp_error($term_data)) {
    $errors[] = 'Failed to create test category: ' . $term_data->get_error_message();
    $test_passed = false;
    $category_id = null;
} else {
    $category_id = $term_data['term_id'];
    echo "✓ Test category created with ID: {$category_id}" . PHP_EOL;
}

// Step 2: Create a test product
echo "\nStep 2: Creating test product..." . PHP_EOL;
$product_id = wp_insert_post([
    'post_title'    => 'Test Product 1',
    'post_content'  => 'Test product for connection testing',
    'post_excerpt'  => 'Test excerpt',
    'post_status'   => 'publish',
    'post_type'     => 'product',
]);

if (is_wp_error($product_id)) {
    $errors[] = 'Failed to create test product: ' . $product_id->get_error_message();
    $test_passed = false;
    $product_id = null;
} else {
    echo "✓ Test product created with ID: {$product_id}" . PHP_EOL;
}

// Step 3: Assign category to product
if ($product_id && $category_id) {
    echo "\nStep 3: Assigning category to product..." . PHP_EOL;
    $result = wp_set_object_terms($product_id, [$category_id], 'product_category');
    
    if (is_wp_error($result)) {
        $errors[] = 'Failed to assign category to product: ' . $result->get_error_message();
        $test_passed = false;
    } else {
        echo "✓ Category assigned successfully" . PHP_EOL;
    }
}

// Step 4: Verify assignment
if ($product_id) {
    echo "\nStep 4: Verifying category assignment..." . PHP_EOL;
    $assigned_categories = wp_get_post_terms($product_id, 'product_category');
    
    if (empty($assigned_categories)) {
        $errors[] = 'No categories assigned to product';
        $test_passed = false;
    } else {
        echo "✓ Categories assigned: " . count($assigned_categories) . PHP_EOL;
        foreach ($assigned_categories as $cat) {
            echo "  - {$cat->name} (ID: {$cat->term_id})" . PHP_EOL;
        }
        
        // Verify correct category
        $correct_category = false;
        foreach ($assigned_categories as $cat) {
            if ($cat->term_id == $category_id) {
                $correct_category = true;
                break;
            }
        }
        
        if (!$correct_category) {
            $errors[] = 'Expected category ID {$category_id} not found in assigned categories';
            $test_passed = false;
        } else {
            echo "✓ Correct category verified" . PHP_EOL;
        }
    }
}

// Step 5: Check database integrity
echo "\nStep 5: Checking database integrity..." . PHP_EOL;
global $wpdb;

// Check term_relationships table
$term_rel_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->term_relationships} 
     WHERE object_id = %d AND term_taxonomy_id IN (
         SELECT tt.term_taxonomy_id FROM {$wpdb->term_taxonomy} tt 
         INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id 
         WHERE t.term_id = %d AND tt.taxonomy = 'product_category'
     )",
    $product_id,
    $category_id
));

if ($term_rel_count == 1) {
    echo "✓ Database term_relationships: 1 entry found (correct)" . PHP_EOL;
} else {
    $errors[] = "Expected 1 term_relationships entry, found: {$term_rel_count}";
    $test_passed = false;
    echo "✗ Database term_relationships: {$term_rel_count} entries found (expected 1)" . PHP_EOL;
}

// Step 6: Verify Product model
echo "\nStep 6: Verifying Product model..." . PHP_EOL;
$plugin_path = WP_PLUGIN_DIR . '/affiliate-product-showcase/vendor/autoload.php';
if (file_exists($plugin_path)) {
    require_once $plugin_path;
    
    // Note: We would typically use ProductFactory here, but we'll verify structure
    echo "✓ Product model loaded successfully" . PHP_EOL;
    echo "  Product has category_ids property: " . (property_exists(new Product(1, '', '', '', '', '', 0.0, ''), 'category_ids') ? 'Yes' : 'No') . PHP_EOL;
}

// Cleanup
echo "\nCleanup: Removing test data..." . PHP_EOL;
if ($product_id) {
    wp_delete_post($product_id, true);
    echo "✓ Test product deleted" . PHP_EOL;
}
if ($category_id) {
    wp_delete_term($category_id, 'product_category');
    echo "✓ Test category deleted" . PHP_EOL;
}

// Test Summary
echo "\n" . str_repeat("=", 60) . PHP_EOL;
echo "TEST SUMMARY" . PHP_EOL;
echo str_repeat("=", 60) . PHP_EOL;

if ($test_passed) {
    echo "Status: ✓ PASSED" . PHP_EOL;
    echo "Test: Assign single category to product" . PHP_EOL;
    echo "Result: Category assignment and database integrity verified" . PHP_EOL;
} else {
    echo "Status: ✗ FAILED" . PHP_EOL;
    echo "Errors found:" . PHP_EOL;
    foreach ($errors as $error) {
        echo "  - {$error}" . PHP_EOL;
    }
}

echo "\n";

exit($test_passed ? 0 : 1);