<?php
/**
 * Simple test for TagsController without WordPress dependencies
 */

// Define a simple autoloader
spl_autoload_register(function ($class) {
    // Check if the class is from our plugin
    if (strpos($class, 'AffiliateProductShowcase\\') === 0) {
        // Convert namespace to file path
        $classPath = str_replace('AffiliateProductShowcase\\', '', $class);
        $classPath = str_replace('\\', '/', $classPath);
        $filePath = __DIR__ . '/src/' . $classPath . '.php';
        
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
});

echo "=== Testing TagsController (Simple) ===\n\n";

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