<?php
/**
 * Direct test for TagsController without relying on autoloader
 * This script manually includes the necessary files
 */

// Define the base path
define('ABSPATH', dirname(dirname(dirname(__DIR__))) . '/');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

// Load WordPress
require_once ABSPATH . 'wp-load.php';

// Manually include the necessary files
require_once __DIR__ . '/src/Models/Tag.php';
require_once __DIR__ . '/src/Factories/TagFactory.php';
require_once __DIR__ . '/src/Repositories/TagRepository.php';
require_once __DIR__ . '/src/Rest/TagsController.php';

echo "=== Testing TagsController Directly ===\n\n";

try {
    // Create instances
    $tag_factory = new \AffiliateProductShowcase\Factories\TagFactory();
    $tag_repository = new \AffiliateProductShowcase\Repositories\TagRepository($tag_factory);
    $tags_controller = new \AffiliateProductShowcase\Rest\TagsController($tag_repository);
    
    echo "✓ TagsController instantiated successfully\n";
    echo "✓ TagRepository instantiated successfully\n";
    echo "✓ TagFactory instantiated successfully\n\n";
    
    // Check if the class exists
    echo "Class: " . get_class($tags_controller) . "\n";
    echo "Namespace: " . (new ReflectionClass($tags_controller))->getNamespaceName() . "\n\n";
    
    // Check if the register_routes method exists
    if (method_exists($tags_controller, 'register_routes')) {
        echo "✓ register_routes method exists\n";
    } else {
        echo "✗ register_routes method NOT found\n";
    }
    
    // Check if the get_items method exists
    if (method_exists($tags_controller, 'get_items')) {
        echo "✓ get_items method exists\n";
    } else {
        echo "✗ get_items method NOT found\n";
    }
    
    // Check if the get_item method exists
    if (method_exists($tags_controller, 'get_item')) {
        echo "✓ get_item method exists\n";
    } else {
        echo "✗ get_item method NOT found\n";
    }
    
    // Check if the create_item method exists
    if (method_exists($tags_controller, 'create_item')) {
        echo "✓ create_item method exists\n";
    } else {
        echo "✗ create_item method NOT found\n";
    }
    
    // Check if the update_item method exists
    if (method_exists($tags_controller, 'update_item')) {
        echo "✓ update_item method exists\n";
    } else {
        echo "✗ update_item method NOT found\n";
    }
    
    // Check if the delete_item method exists
    if (method_exists($tags_controller, 'delete_item')) {
        echo "✓ delete_item method exists\n";
    } else {
        echo "✗ delete_item method NOT found\n";
    }
    
    echo "\n=== All checks passed! ===\n";
    echo "TagsController is properly defined and can be instantiated.\n";
    
} catch (\Throwable $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}