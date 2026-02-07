<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Product Filters
 *
 * Adds custom filters to WordPress default products list table.
 * Uses hooks to extend WordPress native table interface.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class ProductFilters {
    
    /**
     * Initialize filters
     *
     * @return void
     */
    public function init(): void {
        // Add filters to top of table
        add_action('restrict_manage_posts', [$this, 'add_category_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_tag_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_featured_filter'], 10, 2);
        add_action('restrict_manage_posts', [$this, 'add_search_filter'], 10, 2);
        
        // Handle filter queries
        add_action('pre_get_posts', [$this, 'handle_filters']);
    }
    
    /**
     * Add category filter dropdown
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_category_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $categories = get_terms([
            'taxonomy' => Constants::TAX_CATEGORY,
            'hide_empty' => false
        ]);
        
        // Always render dropdown, even if empty
        if (is_wp_error($categories)) {
            $categories = [];
        }
        
        $selected = isset($_GET['aps_category_filter']) ? (int) $_GET['aps_category_filter'] : 0;
        
        echo '<select name="aps_category_filter" id="aps_category_filter">';
        echo '<option value="0">' . esc_html__('All Categories', 'affiliate-product-showcase') . '</option>';
        
        foreach ($categories as $category) {
            $selected_attr = selected($selected, $category->term_id, false);
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $category->term_id,
                $selected_attr,
                esc_html($category->name)
            );
        }
        
        echo '</select>';
    }
    
    /**
     * Add tag filter dropdown
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_tag_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $tags = get_terms([
            'taxonomy' => Constants::TAX_TAG,
            'hide_empty' => false
        ]);
        
        // Always render dropdown, even if empty
        if (is_wp_error($tags)) {
            $tags = [];
        }
        
        $selected = isset($_GET['aps_tag_filter']) ? (int) $_GET['aps_tag_filter'] : 0;
        
        echo '<select name="aps_tag_filter" id="aps_tag_filter">';
        echo '<option value="0">' . esc_html__('All Tags', 'affiliate-product-showcase') . '</option>';
        
        foreach ($tags as $tag) {
            $selected_attr = selected($selected, $tag->term_id, false);
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $tag->term_id,
                $selected_attr,
                esc_html($tag->name)
            );
        }
        
        echo '</select>';
    }
    
    /**
     * Add featured filter checkbox
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_featured_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $is_checked = isset($_GET['featured_filter']) ? checked('1', $_GET['featured_filter'], false) : '';
        
        echo '<label class="aps-featured-filter-label">';
        echo '<input type="checkbox" name="featured_filter" value="1" ' . $is_checked . ' />';
        echo esc_html__('Featured Only', 'affiliate-product-showcase');
        echo '</label>';
    }
    
    /**
     * Add custom search input
     *
     * @param string $post_type Post type
     * @param string $which     Which tablenav (top/bottom)
     * @return void
     */
    public function add_search_filter(string $post_type, string $which): void {
        if ('aps_product' !== $post_type || 'top' !== $which) {
            return;
        }
        
        $search_value = isset($_GET['aps_search']) ? esc_attr($_GET['aps_search']) : '';
        
        echo '<input type="text" name="aps_search" id="aps_search" ';
        echo 'placeholder="' . esc_attr__('Search products...', 'affiliate-product-showcase') . '" ';
        echo 'value="' . $search_value . '" />';
    }
    
    /**
     * Handle custom filters in query
     *
     * @param \WP_Query $query WordPress query object
     * @return void
     */
    public function handle_filters(\WP_Query $query): void {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || 'edit-aps_product' !== $screen->id) {
            return;
        }
        
        // Category filter
        if (isset($_GET['aps_category_filter']) && !empty($_GET['aps_category_filter'])) {
            $category_id = (int) $_GET['aps_category_filter'];
            if ($category_id > 0) {
                $tax_query = $query->get('tax_query');
                if (empty($tax_query)) {
                    $tax_query = [];
                }
                
                $tax_query[] = [
                    'taxonomy' => Constants::TAX_CATEGORY,
                    'terms' => $category_id,
                ];
                
                $tax_query['relation'] = 'AND';
                $query->set('tax_query', $tax_query);
            }
        }
        
        // Tag filter
        if (isset($_GET['aps_tag_filter']) && !empty($_GET['aps_tag_filter'])) {
            $tag_id = (int) $_GET['aps_tag_filter'];
            if ($tag_id > 0) {
                $tax_query = $query->get('tax_query');
                if (empty($tax_query)) {
                    $tax_query = [];
                }
                
                $tax_query[] = [
                    'taxonomy' => Constants::TAX_TAG,
                    'terms' => $tag_id,
                ];
                
                $tax_query['relation'] = 'AND';
                $query->set('tax_query', $tax_query);
            }
        }
        
        // Featured filter
        if (isset($_GET['featured_filter']) && '1' === $_GET['featured_filter']) {
            $meta_query = $query->get('meta_query');
            if (empty($meta_query)) {
                $meta_query = [];
            }
            
            $meta_query[] = [
                'key' => 'aps_featured',
                'value' => '1',
                'compare' => '=',
            ];
            
            $query->set('meta_query', $meta_query);
        }
        
        // Status filter
        if (isset($_GET['aps_status_filter']) && !empty($_GET['aps_status_filter'])) {
            $status = sanitize_text_field($_GET['aps_status_filter']);
            $query->set('post_status', $status);
        }
        
        // Custom search
        if (isset($_GET['aps_search']) && !empty($_GET['aps_search'])) {
            $search_term = sanitize_text_field($_GET['aps_search']);
            $query->set('s', $search_term);
        }
    }
}