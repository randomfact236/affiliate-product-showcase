<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

/**
 * Public Enqueue - Single Asset Source
 *
 * Handles enqueuing of a single CSS and JS file for all public-facing functionality.
 * Uses manifest.json for cache-busting hashed filenames.
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
	 * Assets already loaded flag
	 *
	 * @var bool
	 */
	private static bool $assets_loaded = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Enqueue public assets (single CSS and JS file)
	 *
	 * Assets are conditionally loaded only when shortcode or block is present.
	 *
	 * @return void
	 */
	public function enqueueAssets(): void {
		// Prevent duplicate loading
		if ( self::$assets_loaded ) {
			return;
		}

		// Only load on pages with our shortcodes or blocks
		if ( ! $this->shouldLoadOnCurrentPage() ) {
			return;
		}

		// Enqueue Google Fonts (Inter)
		wp_enqueue_style(
			'affiliate-product-showcase-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
			[],
			null
		);

		// Get main CSS and JS from manifest
		$main_css = $this->getAssetUrl( 'main.css' );
		$main_js  = $this->getAssetUrl( 'main.js' );

		// Enqueue main CSS
		if ( $main_css ) {
			wp_enqueue_style(
				'affiliate-product-showcase',
				$main_css,
				[],
				null // Version is in filename hash
			);
		}

		// Enqueue main JS
		if ( $main_js ) {
			wp_enqueue_script(
				'affiliate-product-showcase',
				$main_js,
				[],
				null, // Version is in filename hash
				true  // Load in footer
			);

			// Localize script with WordPress data
			wp_localize_script(
				'affiliate-product-showcase',
				'affiliateProductShowcase',
				$this->getScriptData()
			);
		}

		self::$assets_loaded = true;
	}

	/**
	 * Get asset URL from manifest.json
	 *
	 * @param string $asset Asset filename (e.g., 'main.css', 'main.js')
	 * @return string|null Asset URL or null if not found
	 */
	private function getAssetUrl( string $asset ): ?string {
		$manifest = $this->getManifest();
		
		// Find the asset in manifest
		foreach ( $manifest as $entry ) {
			if ( isset( $entry['file'] ) ) {
				// Check if this is the JS file
				if ( $asset === 'main.js' && str_ends_with( $entry['file'], '.js' ) ) {
					return AFFILIATE_PRODUCT_SHOWCASE_URL . 'dist/' . $entry['file'];
				}
				
				// Check for CSS files
				if ( isset( $entry['css'] ) && is_array( $entry['css'] ) ) {
					foreach ( $entry['css'] as $css_file ) {
						if ( $asset === 'main.css' && str_ends_with( $css_file, '.css' ) ) {
							return AFFILIATE_PRODUCT_SHOWCASE_URL . 'dist/' . $css_file;
						}
					}
				}
			}
		}
		
		return null;
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
		
		$manifest_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'dist/manifest.json';
		
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
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'affiliate-product-showcase' ),
			'restUrl' => rest_url( 'affiliate-product-showcase/v1/' ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'strings' => [
				'loading'   => __( 'Loading...', 'affiliate-product-showcase' ),
				'loadMore'  => __( 'Load More', 'affiliate-product-showcase' ),
				'noResults' => __( 'No products found.', 'affiliate-product-showcase' ),
				'viewDetails' => __( 'View Details', 'affiliate-product-showcase' ),
				'affiliateLink' => __( 'Buy Now', 'affiliate-product-showcase' ),
			],
		];
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

		$post = get_post();
		
		// Check for all plugin shortcodes
		$has_shortcode = $post ? (
			has_shortcode( $post->post_content, 'aps_product' ) ||
			has_shortcode( $post->post_content, 'aps_products' ) ||
			has_shortcode( $post->post_content, 'aps_showcase' )
		) : false;

		// Check for blocks
		$has_blocks = $this->hasAffiliateBlocks();

		return $has_shortcode || $has_blocks;
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
}
