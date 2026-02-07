<?php
/**
 * Test script to verify taxonomy registration fix
 * This script properly loads WordPress and the plugin
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

echo "=== Taxonomy Registration Fix Verification (v2) ===\n\n";

// Test 1: Check if plugin is loaded
echo "Test 1: Checking if plugin is loaded...\n";
$plugin_loaded = class_exists('AffiliateProductShowcase\\Plugin\\Plugin');
echo $plugin_loaded ? "✅ PASS: Plugin class exists\n" : "❌ FAIL: Plugin class not found\n\n";

// Test 2: Check if TagActivator exists
echo "Test 2: Checking if TagActivator exists...\n";
$activator_exists = class_exists('AffiliateProductShowcase\\TagActivator');
echo $activator_exists ? "✅ PASS: TagActivator class exists\n" : "❌ FAIL: TagActivator class not found\n\n";

// Test 3: Manually trigger taxonomy registration
echo "Test 3: Manually triggering taxonomy registration...\n";
if ($activator_exists) {
    try {
        AffiliateProductShowcase\TagActivator::activate();
        echo "✅ PASS: TagActivator::activate() executed\n";
    } catch (Throwable $e) {
        echo "❌ FAIL: TagActivator::activate() failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ FAIL: Cannot trigger activation - TagActivator not found\n";
}
echo "\n";

// Test 4: Check if 'aps_tag' taxonomy is registered
echo "Test 4: Checking if 'aps_tag' taxonomy is registered...\n";
$taxonomy_exists = taxonomy_exists('aps_tag');
echo $taxonomy_exists ? "✅ PASS: 'aps_tag' taxonomy is registered\n" : "❌ FAIL: 'aps_tag' taxonomy is NOT registered\n\n";

// Test 5: Check if 'aps_tag' taxonomy is registered for 'aps_product' post type
echo "Test 5: Checking if 'aps_tag' taxonomy is registered for 'aps_product' post type...\n";
$post_type_exists = post_type_exists('aps_product');
$taxonomy_for_post_type = is_object_in_taxonomy('aps_product', 'aps_tag');
echo $post_type_exists && $taxonomy_for_post_type ? "✅ PASS: 'aps_tag' taxonomy is registered for 'aps_product' post type\n" : "❌ FAIL: Either 'aps_product' post type or 'aps_tag' taxonomy is not registered\n\n";

// Test 6: Check if REST API route is registered
echo "Test 6: Checking if REST API route is registered...\n";
$rest_server = WP_REST_Server::get_instance();
$routes = $rest_server->get_routes();
$tags_route_exists = isset($routes['/affiliate-product-showcase/v1/tags']);
echo $tags_route_exists ? "✅ PASS: REST API route '/affiliate-product-showcase/v1/tags' is registered\n" : "❌ FAIL: REST API route '/affiliate-product-showcase/v1/tags' is NOT registered\n\n";

// Test 7: Test REST API endpoint access
echo "Test 7: Testing REST API endpoint access...\n";
if ($tags_route_exists) {
    $request = new WP_REST_Request('GET', '/affiliate-product-showcase/v1/tags');
    $response = rest_do_request($request);
    $status = $response->get_status();
    
    if ($status === 200) {
        echo "✅ PASS: REST API endpoint returned 200 OK\n";
        echo "   Response data: " . wp_json_encode($response->get_data()) . "\n";
    } else {
        echo "❌ FAIL: REST API endpoint returned status $status\n";
    }
} else {
    echo "❌ FAIL: Cannot test endpoint - route not registered\n";
}
echo "\n";

// Test 8: Test taxonomy term retrieval
echo "Test 8: Testing taxonomy term retrieval...\n";
if ($taxonomy_exists) {
    // Create a test term
    $term = wp_insert_term('Test Tag', 'aps_tag');
    if (!is_wp_error($term)) {
        echo "✅ PASS: Created test term with ID: " . $term['term_id'] . "\n";
        
        // Retrieve terms
        $terms = get_terms([
            'taxonomy' => 'aps_tag',
            'hide_empty' => false,
        ]);
        
        if (!is_wp_error($terms) && is_array($terms)) {
            echo "✅ PASS: Retrieved " . count($terms) . " term(s) from 'aps_tag' taxonomy\n";
            
            // Clean up test term
            wp_delete_term($term['term_id'], 'aps_tag');
            echo "✅ PASS: Cleaned up test term\n";
        } else {
            echo "❌ FAIL: Could not retrieve terms from 'aps_tag' taxonomy\n";
        }
    } else {
        echo "❌ FAIL: Could not create test term: " . $term->get_error_message() . "\n";
    }
} else {
    echo "❌ FAIL: Cannot test term retrieval - taxonomy not registered\n";
}
echo "\n";

// Test 9: Check if TagRepository exists and can be instantiated
echo "Test 9: Checking if TagRepository exists...\n";
$repository_exists = class_exists('AffiliateProductShowcase\\Repositories\\TagRepository');
echo $repository_exists ? "✅ PASS: TagRepository class exists\n" : "❌ FAIL: TagRepository class not found\n\n";

// Test 10: Check if TagsController exists and can be instantiated
echo "Test 10: Checking if TagsController exists...\n";
$controller_exists = class_exists('AffiliateProductShowcase\\Rest\\TagsController');
echo $controller_exists ? "✅ PASS: TagsController class exists\n" : "❌ FAIL: TagsController class not found\n\n";

// Summary
echo "=== Verification Complete ===\n\n";
echo "Summary:\n";
echo "- If all tests show ✅ PASS, the fix is working correctly\n";
echo "- If any test shows ❌ FAIL, there may be remaining issues\n";
echo "- If you see ❌ FAIL on taxonomy registration, the taxonomy is not being registered\n";
echo "- If you see ❌ FAIL on REST API route, the controller is not registered\n";
echo "- If you see ❌ FAIL on endpoint access, there may be a priority issue\n";