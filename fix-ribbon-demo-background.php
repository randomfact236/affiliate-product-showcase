<?php
/**
 * Fix Ribbon Demo Background Color
 * 
 * This script sets the background color for "Ribbon demo" ribbon
 * Run this file directly: php fix-ribbon-demo-background.php
 */

// Load WordPress
require_once __DIR__ . '/wp-load.php';

// Find "Ribbon demo" term
$term = get_term_by('slug', 'ribbon-demo', 'aps_ribbon');

if (!$term || is_wp_error($term)) {
    echo "❌ Error: 'Ribbon demo' ribbon not found.\n";
    echo "Creating 'Ribbon demo' ribbon instead...\n";
    
    // Create the term
    $result = wp_insert_term('Ribbon demo', 'aps_ribbon');
    
    if (is_wp_error($result)) {
        echo "❌ Error creating term: " . $result->get_error_message() . "\n";
        exit(1);
    }
    
    $term_id = $result['term_id'];
} else {
    $term_id = $term->term_id;
}

echo "✅ Found ribbon 'Ribbon demo' (ID: $term_id)\n";

// Check current background color
$current_bg = get_term_meta($term_id, '_aps_ribbon_bg_color', true);
echo "📋 Current background color: " . ($current_bg ? $current_bg : 'not set') . "\n";

// Set background color to yellow
update_term_meta($term_id, '_aps_ribbon_bg_color', '#ffff00');

// Verify update
$new_bg = get_term_meta($term_id, '_aps_ribbon_bg_color', true);
echo "✅ Updated background color to: " . $new_bg . "\n";

// Also set text color for better contrast
update_term_meta($term_id, '_aps_ribbon_color', '#000000');

// Verify text color
$text_color = get_term_meta($term_id, '_aps_ribbon_color', true);
echo "✅ Updated text color to: " . $text_color . "\n";

// Set icon (optional)
update_term_meta($term_id, '_aps_ribbon_icon', 'star');
echo "✅ Set icon to: star\n";

echo "\n✅ 'Ribbon demo' ribbon background color fixed!\n";
echo "📋 Refresh the ribbon list page to see the yellow background swatch.\n";