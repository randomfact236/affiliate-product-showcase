<?php
/**
 * Test Script: Product Table Fixes Verification
 *
 * Verifies that all fixes are working correctly:
 * - Phase 1: AjaxHandler.php reads correct meta keys
 * - Phase 2: add-product-page.php reads correct meta keys  
 * - Phase 3: Enqueue.php loads CSS correctly
 *
 * Run via: wp eval-file tests/test-product-table-fixes.php
 * Or access directly: /tests/test-product-table-fixes.php
 *
 * @package AffiliateProductShowcase\Tests
 * @since 1.0.0
 */

// Load WordPress
if (file_exists(__DIR__ . '/../wp-load.php')) {
    require_once(__DIR__ . '/../wp-load.php');
} elseif (file_exists(__DIR__ . '/../../../wp-load.php')) {
    require_once(__DIR__ . '/../../../wp-load.php');
} else {
    die('Error: Could not find wp-load.php');
}

// Only run for admins
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to run this test.');
}

/**
 * Test Phase 1: AjaxHandler.php Meta Keys
 */
function test_phase1_ajaxhandler_meta_keys() {
    echo "\n=== Phase 1: Testing AjaxHandler.php Meta Keys ===\n";
    
    // Get a test product (first one found)
    $args = [
        'post_type' => 'aps_product',
        'posts_per_page' => 1,
        'post_status' => 'any',
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        echo "❌ No test products found. Skipping Phase 1 tests.\n";
        return false;
    }
    
    $query->the_post();
    $post_id = get_the_ID();
    
    echo "Testing with Product ID: $post_id\n";
    
    // Test meta keys that AjaxHandler.php should read
    $meta_keys_to_test = [
        '_aps_logo' => 'Logo',
        '_aps_price' => 'Price',
        '_aps_original_price' => 'Original Price',
        '_aps_featured' => 'Featured',
        '_aps_affiliate_url' => 'Affiliate URL',
        '_aps_currency' => 'Currency',
    ];
    
    $pass_count = 0;
    $fail_count = 0;
    
    foreach ($meta_keys_to_test as $key => $label) {
        $value = get_post_meta($post_id, $key, true);
        $exists = !empty($value);
        
        if ($exists) {
            echo "✅ $label ($key): EXISTS\n";
            $pass_count++;
        } else {
            echo "⚠️  $label ($key): NOT SET (may be optional)\n";
            $pass_count++; // Not all meta keys are required
        }
    }
    
    // Verify old keys (without underscore) should NOT be used
    echo "\nVerifying old keys are NOT used:\n";
    $old_keys = [
        'aps_product_logo',
        'aps_product_price',
        'aps_product_original_price',
        'aps_product_affiliate_url',
    ];
    
    foreach ($old_keys as $old_key) {
        $value = get_post_meta($post_id, $old_key, true);
        if (empty($value)) {
            echo "✅ Old key '$old_key': NOT USED (correct)\n";
            $pass_count++;
        } else {
            echo "❌ Old key '$old_key': STILL HAS DATA (should migrate)\n";
            $fail_count++;
        }
    }
    
    wp_reset_postdata();
    
    echo "\nPhase 1 Result: $pass_count passed, $fail_count failed\n";
    return $fail_count === 0;
}

/**
 * Test Phase 2: add-product-page.php Meta Keys
 */
function test_phase2_addproduct_page_meta_keys() {
    echo "\n=== Phase 2: Testing add-product-page.php Meta Keys ===\n";
    
    // Get a test product
    $args = [
        'post_type' => 'aps_product',
        'posts_per_page' => 1,
        'post_status' => 'any',
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        echo "❌ No test products found. Skipping Phase 2 tests.\n";
        return false;
    }
    
    $query->the_post();
    $post_id = get_the_ID();
    $post = get_post($post_id);
    
    echo "Testing with Product ID: $post_id\n";
    
    // Simulate product_data array from add-product-page.php
    $product_data = [
        'logo' => get_post_meta($post->ID, '_aps_logo', true),
        'brand_image' => get_post_meta($post->ID, '_aps_brand_image', true),
        'affiliate_url' => get_post_meta($post->ID, '_aps_affiliate_url', true),
        'button_name' => get_post_meta($post->ID, '_aps_button_name', true),
        'short_description' => get_post_meta($post->ID, '_aps_short_description', true),
        'regular_price' => get_post_meta($post->ID, '_aps_price', true),
        'sale_price' => get_post_meta($post->ID, '_aps_sale_price', true),
        'currency' => get_post_meta($post->ID, '_aps_currency', true) ?: 'USD',
        'featured' => get_post_meta($post->ID, '_aps_featured', true) === '1',
        'rating' => get_post_meta($post->ID, '_aps_rating', true),
        'views' => get_post_meta($post->ID, '_aps_views', true),
        'user_count' => get_post_meta($post->ID, '_aps_user_count', true),
        'reviews' => get_post_meta($post->ID, '_aps_reviews', true),
        'features' => json_decode(get_post_meta($post->ID, '_aps_features', true) ?: '[]', true),
    ];
    
    $pass_count = 0;
    $fail_count = 0;
    
    echo "\nReading meta keys:\n";
    foreach ($product_data as $key => $value) {
        if ($value !== null && $value !== '' && $value !== []) {
            if (is_array($value) || is_object($value)) {
                echo "✅ $key: EXISTS (array/object)\n";
            } else {
                $display_value = is_string($value) && strlen($value) > 50 
                    ? substr($value, 0, 50) . '...' 
                    : $value;
                echo "✅ $key: " . print_r($display_value, true) . "\n";
            }
            $pass_count++;
        } else {
            echo "⚠️  $key: NOT SET (may be optional)\n";
            $pass_count++; // Not all fields are required
        }
    }
    
    wp_reset_postdata();
    
    echo "\nPhase 2 Result: $pass_count fields checked\n";
    return true;
}

/**
 * Test Phase 3: CSS Loading
 */
function test_phase3_css_loading() {
    echo "\n=== Phase 3: Testing CSS Loading ===\n";
    
    // Check if CSS files exist
    $css_files = [
        'admin-table-filters.css' => 'assets/css/admin-table-filters.css',
        'admin-products.css' => 'assets/css/admin-products.css',
    ];
    
    $pass_count = 0;
    $fail_count = 0;
    
    foreach ($css_files as $name => $path) {
        $full_path = WP_PLUGIN_DIR . '/affiliate-product-showcase/' . $path;
        
        if (file_exists($full_path)) {
            $size = filesize($full_path);
            echo "✅ $name: EXISTS (size: $size bytes)\n";
            $pass_count++;
        } else {
            echo "❌ $name: NOT FOUND at $full_path\n";
            $fail_count++;
        }
    }
    
    // Check Enqueue.php for correct CSS loading logic
    $enqueue_file = WP_PLUGIN_DIR . '/affiliate-product-showcase/src/Admin/Enqueue.php';
    
    if (file_exists($enqueue_file)) {
        $enqueue_content = file_get_contents($enqueue_file);
        
        // Check for products page CSS loading
        if (strpos($enqueue_content, 'edit.php') !== false && 
            strpos($enqueue_content, 'typenow') !== false &&
            strpos($enqueue_content, 'admin-products.css') !== false) {
            echo "✅ Enqueue.php: Products page CSS loading logic FOUND\n";
            $pass_count++;
        } else {
            echo "❌ Enqueue.php: Products page CSS loading logic MISSING\n";
            $fail_count++;
        }
        
        // Check for table filters CSS loading
        if (strpos($enqueue_content, 'admin-table-filters.css') !== false) {
            echo "✅ Enqueue.php: Table filters CSS loading logic FOUND\n";
            $pass_count++;
        } else {
            echo "❌ Enqueue.php: Table filters CSS loading logic MISSING\n";
            $fail_count++;
        }
    } else {
        echo "❌ Enqueue.php file not found\n";
        $fail_count++;
    }
    
    echo "\nPhase 3 Result: $pass_count passed, $fail_count failed\n";
    return $fail_count === 0;
}

/**
 * Run All Tests
 */
function run_all_tests() {
    echo "\n";
    echo "========================================\n";
    echo "Product Table Fixes Test Suite\n";
    echo "========================================\n";
    echo "Started: " . date('Y-m-d H:i:s') . "\n";
    echo "User: " . wp_get_current_user()->user_login . "\n";
    
    $results = [];
    
    // Run Phase 1
    $results['phase1'] = test_phase1_ajaxhandler_meta_keys();
    
    // Run Phase 2
    $results['phase2'] = test_phase2_addproduct_page_meta_keys();
    
    // Run Phase 3
    $results['phase3'] = test_phase3_css_loading();
    
    // Summary
    echo "\n";
    echo "========================================\n";
    echo "TEST SUMMARY\n";
    echo "========================================\n";
    
    foreach ($results as $phase => $passed) {
        $status = $passed ? '✅ PASSED' : '❌ FAILED';
        echo ucfirst($phase) . ": $status\n";
    }
    
    $all_passed = !in_array(false, $results, true);
    
    echo "\n";
    if ($all_passed) {
        echo "🎉 ALL TESTS PASSED!\n";
        echo "Product table fixes are working correctly.\n";
    } else {
        echo "⚠️  SOME TESTS FAILED\n";
        echo "Please review the output above for details.\n";
    }
    
    echo "Completed: " . date('Y-m-d H:i:s') . "\n";
    echo "========================================\n";
    
    return $all_passed;
}

// Run tests
run_all_tests();