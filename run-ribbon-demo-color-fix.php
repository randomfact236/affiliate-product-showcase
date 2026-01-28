<?php
/**
 * Run Ribbon Demo Color Fix Migration
 * 
 * This script runs the RibbonDemoColorFix migration to set
 * background color for "Ribbon demo" ribbon.
 * 
 * Usage: php run-ribbon-demo-color-fix.php
 */

// Define paths
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');

// Load WordPress minimal core
require_once(ABSPATH . 'wp-load.php');

// Load migration class
require_once __DIR__ . '/wp-content/plugins/affiliate-product-showcase/src/Migrations/RibbonDemoColorFix.php';

use Affiliate\ProductShowcase\Migrations\RibbonDemoColorFix;

echo "========================================\n";
echo "Ribbon Demo Color Fix Migration\n";
echo "========================================\n\n";

// Initialize migration
$migration = new RibbonDemoColorFix();

// Check if already run
if ($migration->has_run()) {
    echo "⚠️  Migration already run. Skipping.\n";
    echo "✅ 'Ribbon demo' ribbon already has background color set.\n\n";
    exit(0);
}

// Run migration
echo "🚀 Running migration...\n";
$migration->run();

// Verify results
$term = get_term_by('slug', 'ribbon-demo', 'aps_ribbon');
if ($term && !is_wp_error($term)) {
    $bg_color = get_term_meta($term->term_id, '_aps_ribbon_bg_color', true);
    $text_color = get_term_meta($term->term_id, '_aps_ribbon_color', true);
    $icon = get_term_meta($term->term_id, '_aps_ribbon_icon', true);
    
    echo "\n✅ Migration completed successfully!\n";
    echo "----------------------------------------\n";
    echo "Term: {$term->name} (ID: {$term->term_id})\n";
    echo "Background Color: {$bg_color}\n";
    echo "Text Color: {$text_color}\n";
    echo "Icon: {$icon}\n";
    echo "----------------------------------------\n\n";
    echo "📋 Refresh the ribbon list page to see the yellow background swatch.\n";
} else {
    echo "\n❌ Error: Could not verify migration.\n";
    exit(1);
}