<?php
/**
 * Test script to verify taxonomy registration before REST API
 * 
 * This script tests the fix for the "Invalid taxonomy" error
 * by checking if taxonomies are registered before REST API routes.
 */

// Bootstrap WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Taxonomy Registration Fix Verification ===\n\n";

// Test 1: Check if taxonomy is registered
echo "Test 1: Checking if 'aps_tag' taxonomy is registered...\n";
if (taxonomy_exists('aps_tag')) {
    echo "✅ SUCCESS: 'aps_tag' taxonomy is registered\n";
} else {
    echo "❌ FAIL: 'aps_tag' taxonomy is NOT registered\n";
}
echo "\n";

// Test 2: Check if taxonomy is registered with correct post type
echo "Test 2: Checking if 'aps_tag' taxonomy is registered for 'aps_product' post type...\n";
if (post_type_exists('aps_product') && taxonomy_exists('aps_tag')) {
    $taxonomies = get_object_taxonomies('aps_product');
    if (in_array('aps_tag', $taxonomies)) {
        echo "✅ SUCCESS: 'aps_tag' taxonomy is registered for 'aps_product' post type\n";
    } else {
        echo "❌ FAIL: 'aps_tag' taxonomy is NOT registered for 'aps_product' post type\n";
        echo "   Registered taxonomies for 'aps_product': " . implode(', ', $taxonomies) . "\n";
    }
} else {
    echo "❌ FAIL: Either 'aps_product' post type or 'aps_tag' taxonomy is not registered\n";
}
echo "\n";

// Test 3: Check if REST API route is registered
echo "Test 3: Checking if REST API route is registered...\n";
if (function_exists('rest_get_server')) {
    $server = rest_get_server();
    $routes = $server->get_routes();
    
    $tags_route = '/affiliate-product-showcase/v1/tags';
    if (isset($routes[$tags_route])) {
        echo "✅ SUCCESS: REST API route '$tags_route' is registered\n";
        echo "   Available methods: " . implode(', ', array_keys($routes[$tags_route])) . "\n";
    } else {
        echo "❌ FAIL: REST API route '$tags_route' is NOT registered\n";
        echo "   Available routes:\n";
        foreach (array_keys($routes) as $route) {
            if (strpos($route, 'affiliate-product-showcase') !== false) {
                echo "   - $route\n";
            }
        }
    }
} else {
    echo "⚠️  WARNING: Cannot check REST API routes (rest_get_server not available)\n";
}
echo "\n";

// Test 4: Try to access the REST API endpoint
echo "Test 4: Testing REST API endpoint access...\n";
if (function_exists('rest_get_server')) {
    $server = rest_get_server();
    $request = new WP_REST_Request('GET', '/affiliate-product-showcase/v1/tags');
    $request->set_param('per_page', 5);
    
    try {
        $response = $server->dispatch($request);
        $status = $response->get_status();
        
        if ($status === 200) {
            echo "✅ SUCCESS: REST API endpoint returned 200 OK\n";
            $data = $response->get_data();
            echo "   Response data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        } elseif ($status === 404) {
            echo "❌ FAIL: REST API endpoint returned 404 Not Found\n";
            echo "   This usually means the route is not registered\n";
        } else {
            echo "⚠️  WARNING: REST API endpoint returned status $status\n";
            $data = $response->get_data();
            echo "   Response data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR: Exception occurred while testing REST API endpoint\n";
        echo "   Message: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
} else {
    echo "⚠️  WARNING: Cannot test REST API endpoint (rest_get_server not available)\n";
}
echo "\n";

// Test 5: Check if taxonomy terms can be retrieved
echo "Test 5: Testing taxonomy term retrieval...\n";
if (taxonomy_exists('aps_tag')) {
    $terms = get_terms([
        'taxonomy' => 'aps_tag',
        'hide_empty' => false,
        'number' => 5,
    ]);
    
    if (!is_wp_error($terms)) {
        echo "✅ SUCCESS: Terms retrieved successfully\n";
        echo "   Found " . count($terms) . " terms\n";
        if (count($terms) > 0) {
            echo "   Sample terms:\n";
            foreach (array_slice($terms, 0, 3) as $term) {
                echo "   - {$term->name} (ID: {$term->term_id})\n";
            }
        }
    } else {
        echo "❌ FAIL: Error retrieving terms\n";
        echo "   Error: " . $terms->get_error_message() . "\n";
    }
} else {
    echo "❌ FAIL: Cannot test term retrieval - taxonomy not registered\n";
}
echo "\n";

echo "=== Verification Complete ===\n";
echo "\n";
echo "Summary:\n";
echo "- If all tests show ✅ SUCCESS, the fix is working correctly\n";
echo "- If any test shows ❌ FAIL, there may be remaining issues\n";
echo "- If you see ❌ FAIL on taxonomy registration, the taxonomy is not being registered\n";
echo "- If you see ❌ FAIL on REST API route, the controller is not registered\n";
echo "- If you see ❌ FAIL on endpoint access, there may be a priority issue\n";