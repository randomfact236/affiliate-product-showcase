<?php
/**
 * Plugin Load Test
 * 
 * This file tests if the plugin classes load correctly without errors.
 */

// Bootstrap WordPress
require_once __DIR__ . '/../../../wp-load.php';

echo "Testing Affiliate Product Showcase Plugin...\n\n";

// Test 1: Check if constants are defined
echo "1. Testing constants...\n";
if (defined('AFFILIATE_PRODUCT_SHOWCASE_VERSION')) {
    echo "   ✓ AFFILIATE_PRODUCT_SHOWCASE_VERSION defined\n";
} else {
    echo "   ✗ AFFILIATE_PRODUCT_SHOWCASE_VERSION NOT defined\n";
}

if (defined('AFFILIATE_PRODUCT_SHOWCASE_PATH')) {
    echo "   ✓ AFFILIATE_PRODUCT_SHOWCASE_PATH defined\n";
} else {
    echo "   ✗ AFFILIATE_PRODUCT_SHOWCASE_PATH NOT defined\n";
}

// Test 2: Check if autoloader exists
echo "\n2. Testing autoloader...\n";
$autoload = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'vendor/autoload.php';
if (file_exists($autoload)) {
    echo "   ✓ Autoloader file exists\n";
} else {
    echo "   ✗ Autoloader file NOT found\n";
}

// Test 3: Check if key classes exist
echo "\n3. Testing key classes...\n";
$classes = [
    'AffiliateProductShowcase\Plugin\Plugin',
    'AffiliateProductShowcase\Plugin\Container',
    'AffiliateProductShowcase\Services\ProductService',
    'AffiliateProductShowcase\Repositories\ProductRepository',
    'AffiliateProductShowcase\Models\Product',
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "   ✓ $class\n";
    } else {
        echo "   ✗ $class NOT found\n";
    }
}

// Test 4: Check if shortcodes are registered
echo "\n4. Testing shortcodes...\n";
$shortcodes = ['aps_product', 'aps_products', 'aps_showcase'];
foreach ($shortcodes as $shortcode) {
    if (shortcode_exists($shortcode)) {
        echo "   ✓ Shortcode [$shortcode] registered\n";
    } else {
        echo "   ✗ Shortcode [$shortcode] NOT registered\n";
    }
}

// Test 5: Check CSS file
echo "\n5. Testing CSS file...\n";
$css_file = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'assets/css/affiliate-product-showcase.css';
if (file_exists($css_file)) {
    $size = filesize($css_file);
    echo "   ✓ CSS file exists ($size bytes)\n";
} else {
    echo "   ✗ CSS file NOT found\n";
}

echo "\n=== Test Complete ===\n";
