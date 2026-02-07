<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Migrations;

/**
 * Ribbon Demo Color Fix Migration
 *
 * This migration fixes the missing background color for "Ribbon demo" ribbon
 * by setting yellow background and black text for proper contrast.
 *
 * @package Affiliate_Product_Showcase
 * @since 1.0.0
 */
final class RibbonDemoColorFix {
    /**
     * Run the migration
     *
     * @return void
     */
    public function run(): void {
        // Find "Ribbon demo" term by slug
        $term = get_term_by('slug', 'ribbon-demo', 'aps_ribbon');
        
        if (!$term || is_wp_error($term)) {
            error_log('[APS] Ribbon Demo Color Fix: "Ribbon demo" term not found');
            return;
        }
        
        $term_id = $term->term_id;
        
        // Get current values for logging
        $current_bg = get_term_meta($term_id, '_aps_ribbon_bg_color', true);
        $current_text = get_term_meta($term_id, '_aps_ribbon_color', true);
        
        error_log('[APS] Ribbon Demo Color Fix: Found term ID ' . $term_id);
        error_log('[APS] Ribbon Demo Color Fix: Current BG = ' . ($current_bg ?: 'none'));
        error_log('[APS] Ribbon Demo Color Fix: Current Text = ' . ($current_text ?: 'none'));
        
        // Only update if background color is missing
        if (empty($current_bg)) {
            // Set background color to yellow (#ffff00)
            update_term_meta($term_id, '_aps_ribbon_bg_color', '#ffff00');
            error_log('[APS] Ribbon Demo Color Fix: Set background color to #ffff00');
        }
        
        // Only update if text color is missing
        if (empty($current_text)) {
            // Set text color to black (#000000) for contrast
            update_term_meta($term_id, '_aps_ribbon_color', '#000000');
            error_log('[APS] Ribbon Demo Color Fix: Set text color to #000000');
        }
        
        // Set icon if not present
        $current_icon = get_term_meta($term_id, '_aps_ribbon_icon', true);
        if (empty($current_icon)) {
            update_term_meta($term_id, '_aps_ribbon_icon', 'star');
            error_log('[APS] Ribbon Demo Color Fix: Set icon to star');
        }
        
        error_log('[APS] Ribbon Demo Color Fix: Migration completed successfully');
    }
    
    /**
     * Check if migration has been run
     *
     * @return bool
     */
    public function has_run(): bool {
        $term = get_term_by('slug', 'ribbon-demo', 'aps_ribbon');
        
        if (!$term || is_wp_error($term)) {
            return false;
        }
        
        $bg_color = get_term_meta($term->term_id, '_aps_ribbon_bg_color', true);
        
        // Migration considered run if background color is set
        return !empty($bg_color);
    }
}