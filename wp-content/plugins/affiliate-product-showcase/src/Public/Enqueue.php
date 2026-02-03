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
     * Manifest cache
     *
     * @var array<string, mixed>|null
     */
    private static ?array $manifest = null;

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
     * FIXED: Added conditional loading check to prevent loading on every page.
     * Assets now only load when the shortcode or block is present on the page.
     * FIXED: Uses manifest.json to get hashed filenames for cache busting.
     *
     * @return void
     */
    public function enqueueStyles(): void {
        // FIX: Only load assets if shortcode/block is present on current page
        if ( ! $this->shouldLoadOnCurrentPage() ) {
            return;
        }

        // Enqueue Google Fonts (Inter) - used by product showcase
        wp_enqueue_style(
            'affiliate-product-showcase-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
            [],
            null
        );

        // Get frontend CSS from manifest (hashed filename)
        $frontend_css = $this->getAssetUrl( 'styles/frontend.scss' );
        
        if ( $frontend_css ) {
            wp_enqueue_style(
                'affiliate-product-showcase-public',
                $frontend_css,
                [],
                null // Version is in filename hash
            );
        } else {
            // Fallback to static file if manifest not available
            wp_enqueue_style(
                'affiliate-product-showcase-public',
                AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/affiliate-product-showcase.css',
                [],
                self::VERSION
            );
        }

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
     * FIXED: Added conditional loading check to prevent loading on every page.
     * Assets now only load when the shortcode or block is present on the page.
     * FIXED: Uses manifest.json to get hashed filenames for cache busting.
     *
     * @return void
     */
    public function enqueueScripts(): void {
        // FIX: Only load assets if shortcode/block is present on current page
        if ( ! $this->shouldLoadOnCurrentPage() ) {
            return;
        }

        // Get frontend JS from manifest (hashed filename)
        $frontend_js = $this->getAssetUrl( 'js/frontend.ts' );
        
        if ( $frontend_js ) {
            wp_enqueue_script(
                'affiliate-product-showcase-public',
                $frontend_js,
                [],
                null, // Version is in filename hash
                true
            );
        } else {
            // Fallback to static file if manifest not available
            wp_enqueue_script(
                'affiliate-product-showcase-public',
                AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/js/public.js',
                [ 'jquery' ],
                self::VERSION,
                true
            );
        }

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
                AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/js/tracking.js',
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
                AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/js/lazyload.js',
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
     * Get asset URL from manifest.json
     *
     * Reads the Vite manifest to get the hashed filename for cache busting.
     *
     * @param string $entry Entry point (e.g., 'js/frontend.ts', 'styles/frontend.scss')
     * @return string|null Asset URL or null if not found
     */
    private function getAssetUrl( string $entry ): ?string {
        $manifest = $this->getManifest();
        
        if ( ! isset( $manifest[ $entry ] ) ) {
            return null;
        }
        
        $file = $manifest[ $entry ]['file'] ?? null;
        
        if ( ! $file ) {
            return null;
        }
        
        return AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/dist/' . $file;
    }

    /**
     * Load and cache manifest.json
     *
     * @return array<string, mixed> Manifest data
     */
    private function getManifest(): array {
        if ( self::$manifest !== null ) {
            return self::$manifest;
        }
        
        $manifest_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'assets/dist/manifest.json';
        
        if ( ! file_exists( $manifest_path ) ) {
            self::$manifest = [];
            return self::$manifest;
        }
        
        $content = file_get_contents( $manifest_path );
        
        if ( false === $content ) {
            self::$manifest = [];
            return self::$manifest;
        }
        
        $manifest = json_decode( $content, true );
        
        if ( ! is_array( $manifest ) ) {
            self::$manifest = [];
            return self::$manifest;
        }
        
        self::$manifest = $manifest;
        return self::$manifest;
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
     * FIXED: Corrected has_shortcode() usage to include post content parameter.
     *
     * @return bool
     */
    private function shouldLoadOnCurrentPage(): bool {
        // Don't load on admin pages
        if ( is_admin() ) {
            return false;
        }

        // Check if current page has affiliate products shortcode
        // FIX: has_shortcode() requires post_content as first parameter
        $post = get_post();
        // Check for all plugin shortcodes
		$has_products = $post ? (
			has_shortcode( $post->post_content, 'aps_product' ) ||
			has_shortcode( $post->post_content, 'aps_products' ) ||
			has_shortcode( $post->post_content, 'aps_showcase' )
		) : false;
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
