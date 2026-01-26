<?php
/**
 * Test Script: Verify Tag Taxonomy Registration
 *
 * This script tests if the Tag taxonomy is properly registered
 * and accessible via WordPress functions.
 *
 * Usage: wp eval-file test-taxonomy-fix.php
 */

declare(strict_types=1);

// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    require_once(__DIR__ . '/../../../wp-load.php');
}

// Test 1: Check if taxonomy is registered
echo "=== Test 1: Check if 'product_tag' taxonomy is registered ===\n";
$taxonomy_exists = taxonomy_exists('product_tag');
echo "taxonomy_exists('product_tag'): " . ($taxonomy_exists ? '✅ YES' : '❌ NO') . "\n\n";

// Test 2: Get taxonomy object
echo "=== Test 2: Get taxonomy object ===\n";
$taxonomy_obj = get_taxonomy('product_tag');
if ($taxonomy_obj) {
    echo "✅ Taxonomy object retrieved successfully\n";
    echo "   Name: " . $taxonomy_obj->name . "\n";
    echo "   Label: " . $taxonomy_obj->label . "\n";
    echo "   Object Type: " . implode(', ', (array)$taxonomy_obj->object_type) . "\n";
} else {
    echo "❌ Failed to get taxonomy object\n";
}
echo "\n";

// Test 3: Check if taxonomy is registered to product post type
echo "=== Test 3: Check taxonomy registration to product post type ===\n";
$object_type = get_object_taxonomies('product');
if (in_array('product_tag', $object_type, true)) {
    echo "✅ 'product_tag' is registered to 'product' post type\n";
} else {
    echo "❌ 'product_tag' is NOT registered to 'product' post type\n";
    echo "   Registered taxonomies: " . implode(', ', $object_type) . "\n";
}
echo "\n";

// Test 4: Try to create a test tag
echo "=== Test 4: Create a test tag ===\n";
$test_tag = wp_insert_term('Test Tag', 'product_tag');
if (is_wp_error($test_tag)) {
    echo "❌ Failed to create test tag\n";
    echo "   Error: " . $test_tag->get_error_message() . "\n";
} else {
    echo "✅ Test tag created successfully\n";
    echo "   Term ID: " . $test_tag['term_id'] . "\n";
    
    // Clean up: Delete the test tag
    wp_delete_term($test_tag['term_id'], 'product_tag');
    echo "   Test tag cleaned up\n";
}
echo "\n";

// Test 5: Check if terms exist
echo "=== Test 5: Check if any terms exist ===\n";
$terms = get_terms([
    'taxonomy' => 'product_tag',
    'hide_empty' => false,
]);
if (is_wp_error($terms)) {
    echo "❌ Failed to retrieve terms\n";
    echo "   Error: " . $terms->get_error_message() . "\n";
} else {
    echo "✅ Terms retrieved successfully\n";
    echo "   Count: " . count($terms) . "\n";
    if (count($terms) > 0) {
        echo "   First term: " . $terms[0]->name . " (ID: " . $terms[0]->term_id . ")\n";
    }
}
echo "\n";

// Test 6: Check if taxonomy is public
echo "=== Test 6: Check taxonomy properties ===\n";
if ($taxonomy_obj) {
    echo "Public: " . ($taxonomy_obj->public ? '✅ YES' : '❌ NO') . "\n";
    echo "Publicly Queryable: " . ($taxonomy_obj->publicly_queryable ? '✅ YES' : '❌ NO') . "\n";
    echo "Show UI: " . ($taxonomy_obj->show_ui ? '✅ YES' : '❌ NO') . "\n";
    echo "Show in Menu: " . ($taxonomy_obj->show_in_menu ? '✅ YES' : '❌ NO') . "\n";
    echo "Show in REST API: " . ($taxonomy_obj->show_in_rest ? '✅ YES' : '❌ NO') . "\n";
}
echo "\n";

echo "=== Test Complete ===\n";
echo "If all tests pass, the Tag taxonomy is working correctly!\n";