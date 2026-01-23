<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\ProductRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * AJAX Handler for Product Table
 *
 * Handles AJAX requests for:
 * - Filtering products
 * - Sorting products
 * - Bulk actions
 * - Status updates
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class AjaxHandler {

    /**
     * @var ProductService
     */
    private ProductService $productService;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * Constructor
     */
    public function __construct(
        ProductService $productService,
        ProductRepository $productRepository
    ) {
        $this->productService = $productService;
        $this->productRepository = $productRepository;

        $this->registerAjaxHandlers();
    }

    /**
     * Register AJAX handlers
     */
    private function registerAjaxHandlers(): void {
        // Filter products
        add_action('wp_ajax_aps_filter_products', [$this, 'handleFilterProducts']);
        add_action('wp_ajax_nopriv_aps_filter_products', [$this, 'handleFilterProducts']);

        // Bulk actions
        add_action('wp_ajax_aps_bulk_action', [$this, 'handleBulkAction']);
        add_action('wp_ajax_nopriv_aps_bulk_action', [$this, 'handleBulkAction']);

        // Status update
        add_action('wp_ajax_aps_update_status', [$this, 'handleStatusUpdate']);
        add_action('wp_ajax_nopriv_aps_update_status', [$this, 'handleStatusUpdate']);

        // Check links
        add_action('wp_ajax_aps_check_links', [$this, 'handleCheckLinks']);
        add_action('wp_ajax_nopriv_aps_check_links', [$this, 'handleCheckLinks']);
    }

    /**
     * Handle filter products request
     *
     * @return void
     */
    public function handleFilterProducts(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aps_product_table_ui')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        // Get filter parameters
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $featured = isset($_POST['featured']) ? filter_var($_POST['featured'], FILTER_VALIDATE_BOOLEAN) : false;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

        // Build query args
        $args = [
            'post_type' => 'aps_product',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'post_status' => $status === 'all' ? ['publish', 'draft', 'pending'] : $status,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        // Add search
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Add category filter
        if ($category > 0) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'aps_category',
                    'field' => 'term_id',
                    'terms' => $category,
                ]
            ];
        }

        // Add featured filter
        if ($featured) {
            $args['meta_query'] = [
                [
                    'key' => 'aps_featured',
                    'value' => '1',
                    'compare' => '=',
                ]
            ];
        }

        // Get products
        $query = new \WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $products[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'logo' => get_post_meta($post_id, 'aps_product_logo', true),
                    'price' => get_post_meta($post_id, 'aps_product_price', true),
                    'original_price' => get_post_meta($post_id, 'aps_product_original_price', true),
                    'discount_percentage' => $this->calculateDiscount($post_id),
                    'status' => get_post_status($post_id),
                    'featured' => get_post_meta($post_id, 'aps_featured', true) === '1',
                    'ribbon' => get_post_meta($post_id, 'aps_product_ribbon', true),
                    'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
                    'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
                    'affiliate_url' => get_post_meta($post_id, 'aps_product_affiliate_url', true),
                ];
            }
        }

        wp_reset_postdata();

        // Send response
        wp_send_json_success([
            'products' => $products,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
        ]);
    }

    /**
     * Handle bulk action
     *
     * @return void
     */
    public function handleBulkAction(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aps_product_table_ui')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];

        if (empty($action) || empty($product_ids)) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }

        $success_count = 0;
        $error_count = 0;

        foreach ($product_ids as $product_id) {
            $result = $this->processBulkAction($action, $product_id);
            
            if ($result) {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        wp_send_json_success([
            'message' => sprintf(
                '%d products processed successfully. %d failed.',
                $success_count,
                $error_count
            ),
            'success_count' => $success_count,
            'error_count' => $error_count,
        ]);
    }

    /**
     * Handle status update
     *
     * @return void
     */
    public function handleStatusUpdate(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aps_product_table_ui')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if ($product_id === 0 || !in_array($new_status, ['publish', 'draft', 'pending', 'trash'])) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }

        // Update status
        $result = wp_update_post([
            'ID' => $product_id,
            'post_status' => $new_status,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'Failed to update status']);
            return;
        }

        wp_send_json_success([
            'message' => 'Status updated successfully',
            'new_status' => $new_status,
        ]);
    }

    /**
     * Handle check links
     *
     * @return void
     */
    public function handleCheckLinks(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aps_product_table_ui')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        // Get all products with affiliate URLs
        $args = [
            'post_type' => 'aps_product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'aps_product_affiliate_url',
                    'compare' => 'EXISTS',
                ]
            ]
        ];

        $query = new \WP_Query($args);
        $results = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $affiliate_url = get_post_meta($product_id, 'aps_product_affiliate_url', true);

                // Check link (simulated for now)
                $is_valid = $this->checkLink($affiliate_url);

                $results[] = [
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'url' => $affiliate_url,
                    'valid' => $is_valid,
                ];
            }
        }

        wp_reset_postdata();

        $valid_count = count(array_filter($results, fn($r) => $r['valid']));
        $invalid_count = count($results) - $valid_count;

        wp_send_json_success([
            'message' => sprintf(
                'Checked %d links. %d valid, %d invalid.',
                count($results),
                $valid_count,
                $invalid_count
            ),
            'results' => $results,
            'valid_count' => $valid_count,
            'invalid_count' => $invalid_count,
        ]);
    }

    /**
     * Process bulk action
     *
     * @param string $action
     * @param int $product_id
     * @return bool
     */
    private function processBulkAction(string $action, int $product_id): bool {
        switch ($action) {
            case 'set_in_stock':
                return update_post_meta($product_id, 'aps_stock_status', 'in_stock');
            
            case 'set_out_of_stock':
                return update_post_meta($product_id, 'aps_stock_status', 'out_of_stock');
            
            case 'set_featured':
                return update_post_meta($product_id, 'aps_featured', '1');
            
            case 'unset_featured':
                return update_post_meta($product_id, 'aps_featured', '0');
            
            case 'reset_clicks':
                return update_post_meta($product_id, 'aps_clicks', 0);
            
            case 'delete':
                return wp_delete_post($product_id, true) !== false;
            
            default:
                return false;
        }
    }

    /**
     * Calculate discount percentage
     *
     * @param int $product_id
     * @return int
     */
    private function calculateDiscount(int $product_id): int {
        $price = (float) get_post_meta($product_id, 'aps_product_price', true);
        $original_price = (float) get_post_meta($product_id, 'aps_product_original_price', true);

        if ($original_price > 0 && $original_price > $price) {
            return (int) round((($original_price - $price) / $original_price) * 100);
        }

        return 0;
    }

    /**
     * Check link validity
     *
     * @param string $url
     * @return bool
     */
    private function checkLink(string $url): bool {
        // Simulate link check (in production, use wp_remote_get)
        return !empty($url) && filter_var($url, FILTER_VALIDATE_URL);
    }
}
