<?php
/**
 * Debug script to check user capabilities
 *
 * Run: php test-debug-capabilities.php
 */

// Load WordPress
$wordpress_root = dirname(dirname(dirname(__DIR__)));
require_once $wordpress_root . '/wp-load.php';

echo "WordPress Root: " . $wordpress_root . "\n\n";

echo "=== CAPABILITIES DEBUG ===\n\n";

// Get current user
$current_user = wp_get_current_user();
echo "Current User ID: " . $current_user->ID . "\n";
echo "Current User Login: " . $current_user->user_login . "\n";
echo "User Roles: " . implode(', ', $current_user->roles) . "\n\n";

// Check key capabilities
$capabilities_to_check = [
    'manage_options' => 'Can manage options',
    'edit_posts' => 'Can edit posts',
    'publish_posts' => 'Can publish posts',
    'create_posts' => 'Can create posts',
    'edit_aps_products' => 'Can edit aps_products',
    'create_aps_products' => 'Can create aps_products',
];

foreach ($capabilities_to_check as $cap => $description) {
    $has_cap = current_user_can($cap) ? 'YES' : 'NO';
    echo sprintf("%-25s %s\n", $description . ':', $has_cap);
}

echo "\n=== CPT CAPABILITIES ===\n";

// Check CPT-specific capabilities
$cpt_object = get_post_type_object('aps_product');
if ($cpt_object) {
    echo "CPT Found: YES\n";
    echo "Capability Type: " . $cpt_object->capability_type . "\n";
    echo "Map Meta Cap: " . ($cpt_object->map_meta_cap ? 'YES' : 'NO') . "\n\n";
    
    if ($cpt_object->capabilities) {
        echo "Custom Capabilities:\n";
        foreach ($cpt_object->capabilities as $key => $cap) {
            $has_cap = current_user_can($cap) ? 'YES' : 'NO';
            echo sprintf("  %-20s -> %-20s : %s\n", $key, $cap, $has_cap);
        }
    }
} else {
    echo "CPT Found: NO\n";
}

echo "\n=== MENU PAGE ACCESS ===\n";

// Check if can access custom menu page
$menu_slug = 'edit.php?post_type=aps_product';
$submenu_slug = 'add-product';

// Check if menu exists
global $submenu;
if (isset($submenu[$menu_slug])) {
    echo "Menu Found: YES\n";
    foreach ($submenu[$menu_slug] as $index => $item) {
        echo sprintf("  [%d] %s (cap: %s)\n", $index, $item[0], $item[1]);
    }
} else {
    echo "Menu Found: NO\n";
}

echo "\n=== COMPLETE ===\n";