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

use AffiliateProductShowcase\Admin\Config\ProductConfig;
use AffiliateProductShowcase\Admin\Helpers\ProductHelpers;
use AffiliateProductShowcase\Admin\Traits\ColumnRenderer;
use WP_List_Table;

/**
 * ProductsTable class
 *
 * Displays products in a WordPress-style list table.
 *
 * @since 1.0.0
 */
class ProductsTable extends WP_List_Table {
    use ColumnRenderer;
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
        $per_page = $this->get_items_per_page('products_per_page', ProductConfig::DEFAULT_PER_PAGE);
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
            'post_type'      => ProductConfig::POST_TYPE,
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
                    'taxonomy' => ProductConfig::getTaxonomy('category'),
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['category']),
                ],
            ];
        }

        // Apply tag filter
        if (isset($_GET['tag']) && !empty($_GET['tag'])) {
            $args['tax_query'] = isset($args['tax_query']) ? $args['tax_query'] : [];
            $args['tax_query'][] = [
                'taxonomy' => ProductConfig::getTaxonomy('tag'),
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
                'price'         => ProductHelpers::getPrice($post_id),
                'currency'      => ProductHelpers::getCurrency($post_id),
                'logo'          => ProductHelpers::getLogoUrl($post_id),
                'affiliate_url' => ProductHelpers::getAffiliateUrl($post_id),
                'ribbon'        => ProductHelpers::getRibbon($post_id),
                'featured'      => ProductHelpers::isFeatured($post_id),
                'status'        => \get_post_status($post_id),
                'categories'    => ProductHelpers::getCategories($post_id),
                'tags'          => ProductHelpers::getTags($post_id),
                'created_at'    => get_the_date('Y-m-d H:i:s', $post_id),
            ];
        }

        wp_reset_postdata();

        return $products;
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
        return $this->render_logo($item['logo']);
    }

    /**
     * Render title column with row actions
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_title($item): string {
        return $this->render_title_with_actions($item['title'], $item['id']);
    }

    /**
     * Render category column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_category($item): string {
        return $this->render_taxonomy_list($item['categories'], 'aps-category-text');
    }

    /**
     * Render tags column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_tags($item): string {
        return $this->render_taxonomy_list($item['tags'], 'aps-tag-text');
    }

    /**
     * Render ribbon column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_ribbon($item): string {
        return $this->render_ribbon($item['ribbon'], $item['id']);
    }

    /**
     * Render featured column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_featured($item): string {
        return $this->render_empty_indicator($item['featured']);
    }

    /**
     * Render price column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_price($item): string {
    	$currency = $item['currency'] ?? ProductConfig::DEFAULT_CURRENCY;
    	$current_price = floatval($item['price'] ?? 0);
    	$original_price = ProductHelpers::getOriginalPrice($item['id']);
    	
    	return $this->render_price($current_price, $currency, $original_price);
    }

    /**
     * Render status column
     *
     * @since 1.0.0
     * @param array $item Product data
     * @return string
     */
    public function column_status($item): string {
        return $this->render_status($item['status']);
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