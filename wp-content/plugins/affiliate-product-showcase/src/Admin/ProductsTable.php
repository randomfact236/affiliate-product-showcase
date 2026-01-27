<?php
/**
 * Products Table
 *
 * Extends WP_List_Table for products listing.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use WP_List_Table;

/**
 * ProductsTable class
 *
 * Displays products in a WordPress-style list table.
 *
 * @since 1.0.0
 */
class ProductsTable extends WP_List_Table {
    /**
     * Products data
     *
     * @since 1.0.0
     * @var array
     */
    private array $products = [];

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct([
            'singular' => __('product', 'affiliate-product-showcase'),
            'plural'   => __('products', 'affiliate-product-showcase'),
            'ajax'     => false,
        ]);
    }

    /**
     * Prepare items for display
     *
     * @since 1.0.0
     * @return void
     */
    public function prepare_items(): void {
        // Get products from database
        $this->products = $this->get_products_data();

        // Set pagination
        $per_page = $this->get_items_per_page('products_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = count($this->products);

        // Slice products for current page
        $this->items = array_slice(
            $this->products,
            ($current_page - 1) * $per_page,
            $per_page
        );

        // Set pagination args
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Get products data from database
     *
     * @since 1.0.0
     * @return array
     */
    private function get_products_data(): array {
        $args = [
            'post_type'      => 'aps_product',
            'posts_per_page' => -1,
            'post_status'     => ['publish', 'draft', 'trash'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        // Apply status filter from URL
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            if ($_GET['status'] === 'trash') {
                $args['post_status'] = 'trash';
            } elseif ($_GET['status'] === 'draft') {
                $args['post_status'] = 'draft';
            } elseif ($_GET['status'] === 'published') {
                $args['post_status'] = 'publish';
            }
        }

        // Apply category filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'aps_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['category']),
                ],
            ];
        }

        // Apply tag filter
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $args['tax_query'] = isset($args['tax_query']) ? $args['tax_query'] : [];
            $args['tax_query'][] = [
                'taxonomy' => 'aps_tag',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['tag']),
            ];
        }

        // Apply search filter
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $args['s'] = sanitize_text_field($_GET['s']);
        }

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            return [];
        }

        $products = [];
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $products[] = [
                'id'            => $post_id,
                'title'         => get_the_title(),
                'slug'          => \get_post_field('post_name', $post_id),
                'description'   => get_the_content(),
                'price'         => \get_post_meta($post_id, '_aps_price', true),
                'currency'      => \get_post_meta($post_id, '_aps_currency', true) ?: 'USD',
                'logo'          => \get_the_post_thumbnail_url($post_id, 'thumbnail'),
                'affiliate_url' => \get_post_meta($post_id, '_aps_affiliate_url', true),
                'ribbon'        => $this->get_product_ribbon($post_id),
                'featured'       => (bool) \get_post_meta($post_id, '_aps_featured', true),
                'status'        => \get_post_status($post_id),
                'categories'    => $this->get_product_categories($post_id),
                'tags'          => $this->get_product_tags($post_id),
                'created_at'     => get_the_date('Y-m-d H:i:s', $post_id),
            ];
        }

        wp_reset_postdata();

        return $products;
    }

    /**
     * Get product ribbon
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return string Ribbon name
     */
    private function get_product_ribbon(int $product_id): string {
        $terms = \wp_get_object_terms($product_id, 'aps_ribbon', ['fields' => 'names']);
        return is_array($terms) && !empty($terms) ? $terms[0] : '';
    }

    /**
     * Get product categories
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return array Category names
     */
    private function get_product_categories(int $product_id): array {
        $terms = \wp_get_object_terms($product_id, 'aps_category', ['fields' => 'names']);
        return is_array($terms) ? $terms : [];
    }

    /**
     * Get product tags
     *
     * @since 1.0.0
     * @param int $product_id Product ID
     * @return array Tag names
     */
    private function get_product_tags(int $product_id): array {
        $terms = \wp_get_object_terms($product_id, 'aps_tag', ['fields' => 'names']);
        return is_array($terms) ? $terms : [];
    }

    /**
     * Define table columns
     *
     * @since 1.0.0
     * @return array
     */
    public function get_columns(): array {
        return [
            'cb'        => '<input type="checkbox" />',
            'id'        => __('ID', 'affiliate-product-showcase'),
            'logo'      => __('Logo', 'affiliate-product-showcase'),
            'title'     => __('Title', 'affiliate-product-showcase'),
            'category'  => __('Category', 'affiliate-product-showcase'),
            'tags'      => __('Tags', 'affiliate-product-showcase'),
            // Ribbon column registered by Menu.php for WordPress native table
            'featured'  => __('Featured', 'affiliate-product-showcase'),
            'price'     => __('Price', 'affiliate-product-showcase'),
            'status'    => __('Status', 'affiliate-product-showcase'),
        ];
    }

    /**
     * Define sortable columns
     *
     * @since 1.0.0
     * @return array
     */
    public function get_sortable_columns(): array {
        return [
            'id'     => ['id', false],
            'title'  => ['title', false],
            'price'  => ['price', false],
            'status' => ['status', false],
        ];
    }

    /**
     * Define bulk actions
     *
     * @since 1.0.0
     * @return array
     */
    public function get_bulk_actions(): array {
        return [
            'trash' => __('Move to Trash', 'affiliate-product-showcase'),
        ];
    }

    /**
     * Render checkbox column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_cb($item): string {
        return sprintf(
            '<input type="checkbox" name="product[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Render ID column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_id($item): string {
        return (string) $item['id'];
    }

    /**
     * Render logo column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_logo($item): string {
        if (empty($item['logo'])) {
            return '<span class="aps-no-logo">—</span>';
        }

        $logo_url = esc_url($item['logo']);
        return sprintf(
            '<img src="%s" alt="" class="aps-product-logo" width="48" height="48" />',
            $logo_url
        );
    }

    /**
     * Render title column with row actions
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_title($item): string {
        $edit_url = \admin_url(sprintf(
            'admin.php?page=aps-edit-product&id=%d',
            $item['id']
        ));
        $view_url = \get_permalink($item['id']);

        $actions = [
            'edit'       => sprintf(
                '<a href="%s">%s</a>',
                esc_url($edit_url),
                __('Edit', 'affiliate-product-showcase')
            ),
            'inline'     => sprintf(
                '<a href="#" class="aps-inline-edit" data-id="%d">%s</a>',
                $item['id'],
                __('Quick Edit', 'affiliate-product-showcase')
            ),
            'trash'      => sprintf(
                '<a href="#" class="aps-trash-product" data-id="%d">%s</a>',
                $item['id'],
                __('Trash', 'affiliate-product-showcase')
            ),
            'view'       => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url($view_url),
                __('View', 'affiliate-product-showcase')
            ),
        ];

        return sprintf(
            '<strong><a href="%s" class="row-title">%s</a></strong>%s',
            esc_url($edit_url),
            esc_html($item['title']),
            $this->row_actions($actions)
        );
    }

    /**
     * Render category column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_category($item): string {
        if (empty($item['categories'])) {
            return '<span class="aps-category-text">—</span>';
        }

        return sprintf(
            '<span class="aps-category-text">%s</span>',
            esc_html(implode(', ', $item['categories']))
        );
    }

    /**
     * Render tags column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_tags($item): string {
        if (empty($item['tags'])) {
            return '<span class="aps-tag-text">—</span>';
        }

        return sprintf(
            '<span class="aps-tag-text">%s</span>',
            esc_html(implode(', ', $item['tags']))
        );
    }

    /**
     * Render ribbon column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_ribbon($item): string {
        if (empty($item['ribbon'])) {
            return '<span class="aps-ribbon-empty">—</span>';
        }

        return sprintf(
            '<span class="aps-ribbon-badge">%s</span>',
            esc_html($item['ribbon'])
        );
    }

    /**
     * Render featured column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_featured($item): string {
        return $item['featured'] ? '<span class="aps-featured-star">★</span>' : '';
    }

    /**
     * Render price column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_price($item): string {
        $currency = $item['currency'] ?? 'USD';
        $currency_symbol = $this->get_currency_symbol($currency);
        $price = floatval($item['price'] ?? 0);
        
        return sprintf(
            '<span class="aps-price">%s%s</span>',
            esc_html($currency_symbol),
            esc_html(number_format($price, 2))
        );
    }

    /**
     * Render status column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_status($item): string {
        $status_class = 'aps-product-status-' . $item['status'];
        $status_label = $this->get_status_label($item['status']);

        return sprintf(
            '<span class="aps-product-status %s">%s</span>',
            esc_attr($status_class),
            esc_html($status_label)
        );
    }

    /**
     * Render default column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @param string $column_name Column name
     * @return string
     */
    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? \esc_html((string) $item[$column_name]) : '';
    }

    /**
     * Get currency symbol
     *
     * @since 1.0.0
     * @param string $currency Currency code
     * @return string
     */
    private function get_currency_symbol(string $currency): string {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
        ];

        return $symbols[$currency] ?? '$';
    }

    /**
     * Get status label
     *
     * @since 1.0.0
     * @param string $status Status value
     * @return string
     */
    private function get_status_label(string $status): string {
        $labels = [
            'published' => __('Published', 'affiliate-product-showcase'),
            'draft'     => __('Draft', 'affiliate-product-showcase'),
            'trash'     => __('Trash', 'affiliate-product-showcase'),
            'pending'   => __('Pending', 'affiliate-product-showcase'),
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get hidden columns
     *
     * @since 1.0.0
     * @return array
     */
    public function get_hidden_columns(): array {
        return [];
    }

    /**
     * No items found message
     *
     * @since 1.0.0
     * @return void
     */
    public function no_items(): void {
        echo __('No products found.', 'affiliate-product-showcase');
    }
}