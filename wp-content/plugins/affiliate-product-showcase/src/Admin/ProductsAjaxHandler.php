<?php
/**
 * Products AJAX Handler
 *
 * Handles AJAX requests for products listing page.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * ProductsAjaxHandler class
 *
 * Handles AJAX requests for bulk actions and quick edit.
 *
 * @since 1.0.0
 */
final class ProductsAjaxHandler {
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Constructor is empty, initialization is done in init()
    }

    /**
     * Initialize AJAX handlers
     *
     * @since 1.0.0
     * @return void
     */
    public function init(): void {
        // Bulk trash action
        add_action('wp_ajax_aps_bulk_trash_products', [$this, 'handle_bulk_trash']);
        add_action('wp_ajax_nopriv_aps_bulk_trash_products', [$this, 'handle_bulk_trash']);

        // Single trash action
        add_action('wp_ajax_aps_trash_product', [$this, 'handle_trash']);
        add_action('wp_ajax_nopriv_aps_trash_product', [$this, 'handle_trash']);

        // Quick edit action
        add_action('wp_ajax_aps_quick_edit_product', [$this, 'handle_quick_edit']);
        add_action('wp_ajax_nopriv_aps_quick_edit_product', [$this, 'handle_quick_edit']);
    }

    /**
     * Handle bulk trash action
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_bulk_trash(): void {
        // Verify nonce
        if (!check_ajax_referer('aps_products_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => __('Invalid nonce', 'affiliate-product-showcase')]);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'affiliate-product-showcase')]);
        }

        // Get product IDs
        $product_ids = isset($_POST['product_ids']) ? json_decode(stripslashes($_POST['product_ids']), true) : [];

        if (empty($product_ids) || !is_array($product_ids)) {
            wp_send_json_error(['message' => __('No products selected', 'affiliate-product-showcase')]);
        }

        // Move products to trash
        $trashed = 0;
        foreach ($product_ids as $product_id) {
            $product_id = intval($product_id);
            $post = get_post($product_id);
            
            if ($post && $post->post_type === 'aps_product') {
                if (wp_trash_post($product_id)) {
                    $trashed++;
                }
            }
        }

        if ($trashed > 0) {
            wp_send_json_success([
                'message' => sprintf(
                    /* translators: %d: number of products */
                    _n('%d product moved to trash', '%d products moved to trash', $trashed, 'affiliate-product-showcase'),
                    $trashed
                ),
                'count' => $trashed,
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to move products to trash', 'affiliate-product-showcase')]);
        }
    }

    /**
     * Handle single trash action
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_trash(): void {
        // Verify nonce
        if (!check_ajax_referer('aps_products_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => __('Invalid nonce', 'affiliate-product-showcase')]);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'affiliate-product-showcase')]);
        }

        // Get product ID
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($product_id <= 0) {
            wp_send_json_error(['message' => __('Invalid product ID', 'affiliate-product-showcase')]);
        }

        // Verify post type
        $post = get_post($product_id);
        if (!$post || $post->post_type !== 'aps_product') {
            wp_send_json_error(['message' => __('Invalid product', 'affiliate-product-showcase')]);
        }

        // Move to trash
        if (wp_trash_post($product_id)) {
            wp_send_json_success([
                'message' => __('Product moved to trash', 'affiliate-product-showcase'),
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to move product to trash', 'affiliate-product-showcase')]);
        }
    }

    /**
     * Handle quick edit action
     *
     * @since 1.0.0
     * @return void
     */
    public function handle_quick_edit(): void {
        // Verify nonce
        if (!check_ajax_referer('aps_products_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => __('Invalid nonce', 'affiliate-product-showcase')]);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'affiliate-product-showcase')]);
        }

        // Get product ID
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($product_id <= 0) {
            wp_send_json_error(['message' => __('Invalid product ID', 'affiliate-product-showcase')]);
        }

        // Verify post type
        $post = get_post($product_id);
        if (!$post || $post->post_type !== 'aps_product') {
            wp_send_json_error(['message' => __('Invalid product', 'affiliate-product-showcase')]);
        }

        // Get form data
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'published';
        $ribbon = isset($_POST['ribbon']) ? sanitize_text_field($_POST['ribbon']) : '';
        $featured = isset($_POST['featured']) && $_POST['featured'] === '1';

        // Validate
        if (empty($title)) {
            wp_send_json_error(['message' => __('Title is required', 'affiliate-product-showcase')]);
        }

        if ($price < 0) {
            wp_send_json_error(['message' => __('Price must be a positive number', 'affiliate-product-showcase')]);
        }

        // Map status names
        $post_status_map = [
            'published' => 'publish',
            'draft' => 'draft',
            'trash' => 'trash',
        ];
        $post_status = $post_status_map[$status] ?? 'publish';

        // Update post
        $update_data = [
            'ID' => $product_id,
            'post_title' => $title,
            'post_status' => $post_status,
        ];

        $result = wp_update_post($update_data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        // Update price meta
        update_post_meta($product_id, '_aps_price', $price);

        // Update currency meta (keep existing or default to USD)
        $currency = get_post_meta($product_id, '_aps_currency', true) ?: 'USD';
        update_post_meta($product_id, '_aps_currency', $currency);

        // Update ribbon taxonomy
        if (!empty($ribbon)) {
            wp_set_object_terms($product_id, [$ribbon], 'aps_ribbon');
        } else {
            wp_set_object_terms($product_id, [], 'aps_ribbon');
        }

        // Update featured meta
        update_post_meta($product_id, '_aps_featured', $featured ? 1 : 0);

        wp_send_json_success([
            'message' => __('Product updated successfully', 'affiliate-product-showcase'),
        ]);
    }
}