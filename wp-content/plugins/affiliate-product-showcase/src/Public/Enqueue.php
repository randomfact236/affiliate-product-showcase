<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

/**
 * Public Enqueue
 *
 * Handles enqueuing of scripts and styles for the public-facing side.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 */
class Enqueue {

    /**
     * Plugin version
     *
     * @var string
     */
    private const VERSION = '1.0.0';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueStyles' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
        add_action( 'wp_footer', [ $this, 'printInlineScripts' ] );
    }

    /**
     * Enqueue public styles
     *
     * @return void
     */
    public function enqueueStyles(): void {
        // Main public CSS
        wp_enqueue_style(
            'affiliate-product-showcase-public',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/public.css',
            [],
            self::VERSION
        );

        // Product card styles
        wp_enqueue_style(
            'affiliate-product-showcase-card',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/product-card.css',
            [ 'affiliate-product-showcase-public' ],
            self::VERSION
        );

        // Grid layout styles
        wp_enqueue_style(
            'affiliate-product-showcase-grid',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/grid.css',
            [ 'affiliate-product-showcase-public' ],
            self::VERSION
        );

        // Responsive styles
        wp_enqueue_style(
            'affiliate-product-showcase-responsive',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/responsive.css',
            [ 'affiliate-product-showcase-public' ],
            self::VERSION
        );

        // Custom CSS from settings
        $custom_css = get_option( 'affiliate_product_showcase_custom_css', '' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style(
                'affiliate-product-showcase-public',
                $custom_css
            );
        }
    }

    /**
     * Enqueue public scripts
     *
     * @return void
     */
    public function enqueueScripts(): void {
        // Main public JS
        wp_enqueue_script(
            'affiliate-product-showcase-public',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/public.js',
            [ 'jquery' ],
            self::VERSION,
            true
        );

        // Product card JS
        wp_enqueue_script(
            'affiliate-product-showcase-card',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/product-card.js',
            [ 'jquery', 'affiliate-product-showcase-public' ],
            self::VERSION,
            true
        );

        // Localize script
        wp_localize_script(
            'affiliate-product-showcase-public',
            'affiliateProductShowcase',
            $this->getScriptData()
        );

        // Only load tracking if enabled
        if ( $this->isTrackingEnabled() ) {
            wp_register_script(
                'affiliate-product-showcase-tracking',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/tracking.js',
                [],
                self::VERSION,
                true
            );
            
            // Add defer attribute for non-critical script (WordPress 6.3+)
            wp_script_add_data( 'affiliate-product-showcase-tracking', 'defer', true );
            
            wp_enqueue_script( 'affiliate-product-showcase-tracking' );
        }

        // Lazy load images if enabled
        if ( $this->isLazyLoadEnabled() ) {
            wp_register_script(
                'affiliate-product-showcase-lazyload',
                AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/lazyload.js',
                [],
                self::VERSION,
                true
            );
            
            // Add defer attribute for non-critical script (WordPress 6.3+)
            wp_script_add_data( 'affiliate-product-showcase-lazyload', 'defer', true );
            
            wp_enqueue_script( 'affiliate-product-showcase-lazyload' );
        }
    }

    /**
     * Print inline scripts
     *
     * @return void
     */
    public function printInlineScripts(): void {
        if ( ! $this->shouldLoadOnCurrentPage() ) {
            return;
        }

        ?>
        <script type="text/javascript">
            // Affiliate Product Showcase Configuration
            if (typeof affiliateProductShowcase !== 'undefined') {
                affiliateProductShowcase.config = {
                    trackingEnabled: <?php echo $this->isTrackingEnabled() ? 'true' : 'false'; ?>,
                    lazyLoad: <?php echo $this->isLazyLoadEnabled() ? 'true' : 'false'; ?>,
                    displayMode: '<?php echo esc_js( get_option( 'affiliate_product_showcase_display_mode', 'grid' ) ); ?>',
                };
            }
        </script>
        <?php
    }

    /**
     * Get script data for localization
     *
     * @return array
     */
    private function getScriptData(): array {
        return [
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( 'affiliate-product-showcase-public' ),
            'restUrl'    => rest_url( 'affiliate-product-showcase/v1/' ),
            'restNonce'  => wp_create_nonce( 'wp_rest' ),
            'strings'    => [
                'loading'      => __( 'Loading...', 'affiliate-product-showcase' ),
                'loadMore'     => __( 'Load More', 'affiliate-product-showcase' ),
                'noResults'    => __( 'No products found.', 'affiliate-product-showcase' ),
                'addToCart'    => __( 'Add to Cart', 'affiliate-product-showcase' ),
                'viewDetails'  => __( 'View Details', 'affiliate-product-showcase' ),
                'affiliateLink' => __( 'Buy Now', 'affiliate-product-showcase' ),
                'outOfStock'   => __( 'Out of Stock', 'affiliate-product-showcase' ),
            ],
            'settings'   => [
                'displayMode'  => get_option( 'affiliate_product_showcase_display_mode', 'grid' ),
                'itemsPerPage' => intval( get_option( 'affiliate_product_showcase_items_per_page', 12 ) ),
                'cacheDuration' => intval( get_option( 'affiliate_product_showcase_cache_duration', 3600 ) ),
            ],
        ];
    }

    /**
     * Check if tracking is enabled
     *
     * @return bool
     */
    private function isTrackingEnabled(): bool {
        return (bool) get_option( 'affiliate_product_showcase_tracking_enabled', true );
    }

    /**
     * Check if lazy loading is enabled
     *
     * @return bool
     */
    private function isLazyLoadEnabled(): bool {
        return (bool) get_option( 'affiliate_product_showcase_lazy_load', false );
    }

    /**
     * Check if plugin should load on current page
     *
     * @return bool
     */
    private function shouldLoadOnCurrentPage(): bool {
        // Don't load on admin pages
        if ( is_admin() ) {
            return false;
        }

        // Check if current page has affiliate products
        $has_products = has_shortcode( 'affiliate_products' );
        $has_blocks = $this->hasAffiliateBlocks();

        return $has_products || $has_blocks;
    }

    /**
     * Check if page has affiliate blocks
     *
     * @return bool
     */
    private function hasAffiliateBlocks(): bool {
        if ( ! function_exists( 'has_block' ) ) {
            return false;
        }

        $post = get_post();
        if ( ! $post ) {
            return false;
        }

        return has_block( 'affiliate-product-showcase/products', $post );
    }

    /**
     * Get plugin settings for JS
     *
     * @return array
     */
    public static function getSettings(): array {
        return [
            'displayMode'     => get_option( 'affiliate_product_showcase_display_mode', 'grid' ),
            'itemsPerPage'    => intval( get_option( 'affiliate_product_showcase_items_per_page', 12 ) ),
            'cacheDuration'   => intval( get_option( 'affiliate_product_showcase_cache_duration', 3600 ) ),
            'trackingEnabled'  => (bool) get_option( 'affiliate_product_showcase_tracking_enabled', true ),
            'lazyLoad'        => (bool) get_option( 'affiliate_product_showcase_lazy_load', false ),
            'enableAnalytics' => (bool) get_option( 'affiliate_product_showcase_enable_analytics', true ),
        ];
    }
}
