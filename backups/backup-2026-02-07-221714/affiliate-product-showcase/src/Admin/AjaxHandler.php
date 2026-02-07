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

        // Product table actions (no nopriv - only logged-in admins)
        add_action('wp_ajax_aps_bulk_trash_products', [$this, 'handleBulkTrashProducts']);
        add_action('wp_ajax_aps_trash_product', [$this, 'handleTrashProduct']);
        add_action('wp_ajax_aps_quick_edit_product', [$this, 'handleQuickEditProduct']);
    }

    /**
     * Verify nonce for AJAX requests
     *
     * @param string $action The nonce action to verify
     * @return bool True if nonce is valid, false otherwise
     */
    private function verifyNonce(string $action): bool {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $action)) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return false;
        }
        return true;
    }

    /**
     * Verify manage_options capability for AJAX requests
     *
     * @return bool True if user has capability, false otherwise
     */
    private function verifyManageOptionsCapability(): bool {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return false;
        }
        return true;
    }

    /**
     * Update product title
     *
     * @param int $product_id The product ID
     * @param string $title The title to set
     * @return bool True if update successful, false otherwise
     */
    private function updateProductTitle(int $product_id, string $title): bool {
        if (empty($title)) {
            return false;
        }
        $updated = wp_update_post([
            'ID' => $product_id,
            'post_title' => $title,
        ]);
        return !is_wp_error($updated);
    }

    /**
     * Update product status
     *
     * @param int $product_id The product ID
     * @param string $status The status to set
     * @return bool True if update successful, false otherwise
     */
    private function updateProductStatus(int $product_id, string $status): bool {
        $valid_statuses = ['publish', 'draft', 'pending'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        $updated = wp_update_post([
            'ID' => $product_id,
            'post_status' => $status,
        ]);
        return !is_wp_error($updated);
    }

    /**
     * Update product price
     *
     * @param int $product_id The product ID
     * @param float $price The price to set
     * @return bool True if update successful, false otherwise
     */
    private function updateProductPrice(int $product_id, float $price): bool {
        if ($price < 0) {
            return false;
        }
        update_post_meta($product_id, '_aps_price', $price);
        return true;
    }

    /**
     * Update product original price
     *
     * @param int $product_id The product ID
     * @param float $original_price The original price to set
     * @return bool True if update successful, false otherwise
     */
    private function updateProductOriginalPrice(int $product_id, float $original_price): bool {
        if ($original_price < 0) {
            return false;
        }
        update_post_meta($product_id, '_aps_original_price', $original_price);
        return true;
    }

    /**
     * Update product featured status
     *
     * @param int $product_id The product ID
     * @param bool $featured Whether to feature the product
     * @return bool Always true
     */
    private function updateProductFeatured(int $product_id, bool $featured): bool {
        update_post_meta($product_id, '_aps_featured', $featured ? '1' : '0');
        return true;
    }

    /**
     * Update product ribbon
     *
     * @param int $product_id The product ID
     * @param string $ribbon The ribbon term slug
     * @return bool True if ribbon is not empty, false otherwise
     */
    private function updateProductRibbon(int $product_id, string $ribbon): bool {
        if (!empty($ribbon)) {
            wp_set_object_terms($product_id, [$ribbon], 'aps_ribbon', false);
        }
        return true;
    }

    /**
     * Handle filter products request
     *
     * @return void
     */
    public function handleFilterProducts(): void {
        // Verify nonce
        if (!$this->verifyNonce('aps_product_table_ui')) {
            return;
        }

        // Check permissions
        if (!$this->verifyManageOptionsCapability()) {
            return;
        }

        // Get filter parameters
        $params = $this->getFilterParameters();

        // Build query args
        $args = $this->buildFilterQuery($params);

        // Get products
        $query = new \WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            // Prime caches to prevent N+1 queries
            $post_ids = wp_list_pluck($query->posts, 'ID');
            
            // Prime postmeta cache
            update_postmeta_cache($post_ids);
            
            // Prime taxonomy cache
            update_object_term_cache($post_ids, ['aps_product', 'aps_category', 'aps_tag', 'aps_ribbon']);
            
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $products[] = $this->buildProductData($post_id);
            }
        }

        wp_reset_postdata();

        // Send response
        wp_send_json_success([
            'products' => $products,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $params['page'],
        ]);
    }

    /**
     * Get and sanitize filter parameters from POST request
     *
     * @return array Filter parameters
     */
    private function getFilterParameters(): array
    {
        return [
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'category' => isset($_POST['category']) ? intval($_POST['category']) : 0,
            'featured' => isset($_POST['featured']) ? filter_var($_POST['featured'], FILTER_VALIDATE_BOOLEAN) : false,
            'status' => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all',
            'per_page' => isset($_POST['per_page']) ? intval($_POST['per_page']) : 20,
            'page' => isset($_POST['page']) ? intval($_POST['page']) : 1,
        ];
    }

    /**
     * Build WP_Query arguments based on filter parameters
     *
     * @param array $params Filter parameters
     * @return array Query arguments
     */
    private function buildFilterQuery(array $params): array
    {
        $args = [
            'post_type' => 'aps_product',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => $params['status'] === 'all' ? ['publish', 'draft', 'pending'] : $params['status'],
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        // Add search
        if (!empty($params['search'])) {
            $args['s'] = $params['search'];
        }

        // Add category filter
        if ($params['category'] > 0) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'aps_category',
                    'field' => 'term_id',
                    'terms' => $params['category'],
                ]
            ];
        }

        // Add featured filter
        if ($params['featured']) {
            $args['meta_query'] = [
                [
                    'key' => '_aps_featured',
                    'value' => '1',
                    'compare' => '=',
                ]
            ];
        }

        return $args;
    }

    /**
     * Build product data array for JSON response
     *
     * @param int $post_id Product post ID
     * @return array Product data
     */
    private function buildProductData(int $post_id): array
    {
        // Get ribbon from taxonomy
        $ribbon_terms = wp_get_post_terms($post_id, 'aps_ribbon', ['fields' => 'names']);

        return [
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'logo' => get_post_meta($post_id, '_aps_logo', true),
            'price' => get_post_meta($post_id, '_aps_price', true),
            'original_price' => get_post_meta($post_id, '_aps_original_price', true),
            'discount_percentage' => $this->calculateDiscount($post_id),
            'status' => get_post_status($post_id),
            'featured' => get_post_meta($post_id, '_aps_featured', true) === '1',
            'ribbon' => !empty($ribbon_terms) ? $ribbon_terms[0] : '',
            'categories' => wp_get_post_terms($post_id, 'aps_category', ['fields' => 'names']),
            'tags' => wp_get_post_terms($post_id, 'aps_tag', ['fields' => 'names']),
            'affiliate_url' => get_post_meta($post_id, '_aps_affiliate_url', true),
        ];
    }

    /**
     * Handle bulk action
     *
     * @return void
     */
    public function handleBulkAction(): void {
        // Verify nonce
        if (!$this->verifyNonce('aps_product_table_ui')) {
            return;
        }

        // Check permissions
        if (!$this->verifyManageOptionsCapability()) {
            return;
        }

        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];

        if (empty($action) || empty($product_ids)) {
            wp_send_json_error(['message' => 'Invalid request']);
            return;
        }

        $result = $this->processBulkActions($action, $product_ids);

        wp_send_json_success([
            'message' => sprintf(
                '%d products processed successfully. %d failed.',
                $result['success_count'],
                $result['error_count']
            ),
            'success_count' => $result['success_count'],
            'error_count' => $result['error_count'],
        ]);
    }

    /**
     * Process bulk action for multiple products
     *
     * @param string $action Action to perform
     * @param array $product_ids Product IDs
     * @return array Result with success and error counts
     */
    private function processBulkActions(string $action, array $product_ids): array
    {
        $success_count = 0;
        $error_count = 0;

        foreach ($product_ids as $product_id) {
            if ($this->processBulkAction($action, $product_id)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }

        return [
            'success_count' => $success_count,
            'error_count' => $error_count,
        ];
    }

    /**
     * Handle status update
     *
     * @return void
     */
    public function handleStatusUpdate(): void {
        // Verify nonce
        if (!$this->verifyNonce('aps_product_table_ui')) {
            return;
        }

        // Check permissions
        if (!$this->verifyManageOptionsCapability()) {
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
        $query = new \WP_Query($this->buildLinkCheckQuery());
        $results = [];

        if ($query->have_posts()) {
            // Prime postmeta cache to prevent N+1 queries
            $post_ids = wp_list_pluck($query->posts, 'ID');
            update_postmeta_cache($post_ids);
            
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = $this->buildLinkCheckResult(get_the_ID());
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
     * Build query arguments for checking links
     *
     * @return array Query arguments
     */
    private function buildLinkCheckQuery(): array
    {
        return [
            'post_type' => 'aps_product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_aps_affiliate_url',
                    'compare' => 'EXISTS',
                ]
            ]
        ];
    }

    /**
     * Build link check result for a product
     *
     * @param int $product_id Product ID
     * @return array Link check result
     */
    private function buildLinkCheckResult(int $product_id): array
    {
        $affiliate_url = get_post_meta($product_id, '_aps_affiliate_url', true);

        return [
            'id' => $product_id,
            'title' => get_the_title($product_id),
            'url' => $affiliate_url,
            'valid' => $this->checkLink($affiliate_url),
        ];
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
                return update_post_meta($product_id, '_aps_stock_status', 'in_stock');
            
            case 'set_out_of_stock':
                return update_post_meta($product_id, '_aps_stock_status', 'out_of_stock');
            
            case 'set_featured':
                return update_post_meta($product_id, '_aps_featured', '1');
            
            case 'unset_featured':
                return update_post_meta($product_id, '_aps_featured', '0');
            
            case 'reset_clicks':
                return update_post_meta($product_id, '_aps_clicks', 0);
            
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
        $price = (float) get_post_meta($product_id, '_aps_price', true);
        $original_price = (float) get_post_meta($product_id, '_aps_original_price', true);

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

    /**
     * Handle bulk trash products action
     *
     * @return void
     */
    public function handleBulkTrashProducts(): void {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aps_products_nonce')) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        // Get product IDs
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];

        if (empty($product_ids)) {
            wp_send_json_error(['message' => 'No products selected']);
            return;
        }

        $success_count = 0;
        $error_count = 0;
        $trashed_ids = [];

        foreach ($product_ids as $product_id) {
            if ($this->trashProduct($product_id)) {
                $success_count++;
                $trashed_ids[] = $product_id;
            } else {
                $error_count++;
            }
        }

        // Clear product cache
        $this->clearProductCache($trashed_ids);

        wp_send_json_success([
            'message' => sprintf(
                '%d product%s moved to trash.',
                $success_count,
                $success_count === 1 ? '' : 's'
            ),
            'count' => $success_count,
            'trashed_ids' => $trashed_ids,
            'errors' => $error_count,
        ]);
    }

    /**
     * Validate and trash a single product
     *
     * @param int $product_id Product ID
     * @return bool True if trashed successfully, false otherwise
     */
    private function trashProduct(int $product_id): bool
    {
        // Validate product exists and is correct type
        $post = get_post($product_id);
        
        if (!$post || $post->post_type !== 'aps_product') {
            return false;
        }

        // Trash the product
        return (bool) wp_trash_post($product_id);
    }

    /**
     * Clear cache for multiple products
     *
     * @param array $product_ids Product IDs
     * @return void
     */
    private function clearProductCache(array $product_ids): void
    {
        foreach ($product_ids as $product_id) {
            wp_cache_delete("product_{$product_id}", 'products');
        }
    }

    /**
     * Handle trash product action
     *
     * @return void
     */
    public function handleTrashProduct(): void {
        // Verify nonce
        if (!$this->verifyNonce('aps_products_nonce')) {
            return;
        }

        // Check permissions
        if (!$this->verifyManageOptionsCapability()) {
            return;
        }

        // Get product ID
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($product_id === 0) {
            wp_send_json_error(['message' => 'Invalid product ID']);
            return;
        }

        // Validate product exists and is correct type
        $post = get_post($product_id);
        
        if (!$post) {
            wp_send_json_error(['message' => 'Product not found']);
            return;
        }

        if ($post->post_type !== 'aps_product') {
            wp_send_json_error(['message' => 'Invalid product type']);
            return;
        }

        // Trash the product
        $result = wp_trash_post($product_id);

        if ($result) {
            // Clear product cache
            wp_cache_delete("product_{$product_id}", 'products');

            wp_send_json_success([
                'message' => 'Product moved to trash.',
                'product_id' => $product_id,
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to move product to trash']);
        }
    }

    /**
     * Handle quick edit product action
     *
     * @return void
     */
    public function handleQuickEditProduct(): void {
        // Security and input validation
        $product_id = $this->validateQuickEditRequest();
        if (!$product_id) {
            return;
        }

        $product_data = $this->getQuickEditData();
        if (empty($product_data)) {
            wp_send_json_error(['message' => 'No data provided']);
            return;
        }

        // Validate fields
        $errors = $this->validateQuickEditData($product_data);
        if (!empty($errors)) {
            wp_send_json_error(['message' => 'Validation failed', 'errors' => $errors]);
            return;
        }

        // Process updates
        $updated_fields = $this->processFieldUpdates($product_id, $product_data);

        // Clear cache and return success
        wp_cache_delete("product_{$product_id}", 'products');
        
        wp_send_json_success([
            'message' => 'Product updated successfully.',
            'product_id' => $product_id,
            'updated_fields' => $updated_fields,
        ]);
    }

    /**
     * Validate quick edit request (security + product validation)
     *
     * @return int Product ID if valid, 0 otherwise
     */
    private function validateQuickEditRequest(): int {
        if (!$this->verifyNonce('aps_products_nonce')) {
            return 0;
        }

        if (!$this->verifyManageOptionsCapability()) {
            return 0;
        }

        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        if ($product_id === 0) {
            wp_send_json_error(['message' => 'Invalid product ID']);
            return 0;
        }

        $post = get_post($product_id);
        
        if (!$post) {
            wp_send_json_error(['message' => 'Product not found']);
            return 0;
        }

        if ($post->post_type !== 'aps_product') {
            wp_send_json_error(['message' => 'Invalid product type']);
            return 0;
        }

        return $product_id;
    }

    /**
     * Get quick edit data from request
     *
     * @return array Product data
     */
    private function getQuickEditData(): array {
        return isset($_POST['data']) && is_array($_POST['data']) ? $_POST['data'] : [];
    }

    /**
     * Process field updates for quick edit
     *
     * @param int $product_id Product ID
     * @param array $product_data Product data from request
     * @return array Updated fields
     */
    private function processFieldUpdates(int $product_id, array $product_data): array {
        $updated_fields = [];
        
        // Define field update configuration
        $field_config = [
            'title' => [
                'sanitize' => 'sanitize_text_field',
                'update_method' => 'updateProductTitle',
                'validate' => true,
            ],
            'status' => [
                'sanitize' => 'sanitize_text_field',
                'update_method' => 'updateProductStatus',
                'validate' => true,
            ],
            'price' => [
                'sanitize' => 'floatval',
                'update_method' => 'updateProductPrice',
                'validate' => true,
                'min_value' => 0,
            ],
            'original_price' => [
                'sanitize' => 'floatval',
                'update_method' => 'updateProductOriginalPrice',
                'validate' => true,
                'min_value' => 0,
            ],
            'featured' => [
                'sanitize' => null,
                'update_method' => 'updateProductFeatured',
                'validate' => false,
                'allowed_values' => ['1', true],
            ],
            'ribbon' => [
                'sanitize' => 'sanitize_text_field',
                'update_method' => 'updateProductRibbon',
                'validate' => true,
            ],
        ];

        // Process each field
        foreach ($field_config as $field => $config) {
            if (!isset($product_data[$field])) {
                continue;
            }

            $value = $product_data[$field];
            
            // Apply sanitization if configured
            if ($config['sanitize']) {
                $sanitized_value = $config['sanitize']($value);
            } else {
                $sanitized_value = $value;
            }

            // Apply validation if configured
            if (isset($config['validate']) && $config['validate']) {
                // Check min value
                if (isset($config['min_value']) && $sanitized_value < $config['min_value']) {
                    continue;
                }
                
                // Check allowed values
                if (isset($config['allowed_values']) && !in_array($sanitized_value, $config['allowed_values'], true)) {
                    continue;
                }
            }

            // Call update method
            $update_method = $config['update_method'];
            if ($this->$update_method($product_id, $sanitized_value)) {
                $updated_fields[$field] = $sanitized_value;
            }
        }

        return $updated_fields;
    }

    /**
     * Validate quick edit data
     *
     * @param array $data
     * @return array
     */
    private function validateQuickEditData(array $data): array {
        $errors = [];

        // Validate title
        if (isset($data['title'])) {
            $error = $this->validateTitle($data['title']);
            if ($error !== null) {
                $errors['title'] = $error;
            }
        }

        // Validate price
        if (isset($data['price'])) {
            $error = $this->validatePrice($data['price']);
            if ($error !== null) {
                $errors['price'] = $error;
            }
        }

        // Validate original price
        if (isset($data['original_price'])) {
            $price = isset($data['price']) ? $data['price'] : null;
            $error = $this->validateOriginalPrice($data['original_price'], $price);
            if ($error !== null) {
                $errors['original_price'] = $error;
            }
        }

        // Validate status
        if (isset($data['status'])) {
            $error = $this->validateStatus($data['status']);
            if ($error !== null) {
                $errors['status'] = $error;
            }
        }

        return $errors;
    }

    /**
     * Validate title field
     *
     * @param string|null $title Title value
     * @return string|null Error message or null if valid
     */
    private function validateTitle(?string $title): ?string
    {
        if (empty($title)) {
            return 'Title is required';
        }
        if (strlen($title) > 200) {
            return 'Title must be less than 200 characters';
        }
        return null;
    }

    /**
     * Validate price field
     *
     * @param mixed $price Price value
     * @return string|null Error message or null if valid
     */
    private function validatePrice($price): ?string
    {
        $price = floatval($price);
        if ($price < 0) {
            return 'Price must be a positive number';
        }
        return null;
    }

    /**
     * Validate original price field
     *
     * @param mixed $original_price Original price value
     * @param mixed $price Current price value
     * @return string|null Error message or null if valid
     */
    private function validateOriginalPrice($original_price, $price): ?string
    {
        $original_price = floatval($original_price);
        if ($original_price < 0) {
            return 'Original price must be a positive number';
        }
        if ($original_price > 0 && $price !== null && $original_price < floatval($price)) {
            return 'Original price cannot be less than price';
        }
        return null;
    }

    /**
     * Validate status field
     *
     * @param string|null $status Status value
     * @return string|null Error message or null if valid
     */
    private function validateStatus(?string $status): ?string
    {
        $valid_statuses = ['publish', 'draft', 'pending'];
        if (!in_array($status, $valid_statuses)) {
            return 'Invalid status value';
        }
        return null;
    }

}
