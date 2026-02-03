<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Shortcodes {
	private AffiliateService $affiliate_service;
	private static bool $vite_assets_loaded = false;

	public function __construct( 
		private ProductService $product_service, 
		private SettingsRepository $settings_repository,
		AffiliateService $affiliate_service 
	) {
		$this->affiliate_service = $affiliate_service;
	}

	public function register(): void {
		add_shortcode( 'aps_product', [ $this, 'render_single' ] );
		add_shortcode( 'aps_products', [ $this, 'render_grid' ] );
		add_shortcode( 'aps_showcase', [ $this, 'render_showcase' ] );
	}

	/**
	 * Load Vite frontend assets for the showcase template.
	 * 
	 * NOTE: This is separate from Enqueue.php because the showcase uses
	 * a different build system (Vite in frontend/ directory) than the
	 * regular shortcodes (SCSS in assets/ directory).
	 */
	private function loadViteAssets(): void {
		if ( self::$vite_assets_loaded ) {
			return;
		}

		$dist_path = AFFILIATE_PRODUCT_SHOWCASE_DIR . 'frontend/dist/';
		$dist_url  = plugin_dir_url( AFFILIATE_PRODUCT_SHOWCASE_FILE ) . 'frontend/dist/';
		$manifest_path = $dist_path . '.vite/manifest.json';

		// Enqueue Google Fonts (Inter)
		wp_enqueue_style(
			'aps-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
			[],
			null
		);

		// Load from Vite manifest if available
		if ( file_exists( $manifest_path ) ) {
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
			
			if ( is_array( $manifest ) && isset( $manifest['index.html'] ) ) {
				$entry = $manifest['index.html'];

				// Enqueue CSS files
				if ( isset( $entry['css'] ) && is_array( $entry['css'] ) ) {
					foreach ( $entry['css'] as $index => $css_file ) {
						wp_enqueue_style(
							"aps-showcase-css-{$index}",
							$dist_url . $css_file,
							[],
							AFFILIATE_PRODUCT_SHOWCASE_VERSION
						);
					}
				}

				// Enqueue JS file
				if ( isset( $entry['file'] ) ) {
					wp_enqueue_script(
						'aps-showcase-js',
						$dist_url . $entry['file'],
						[],
						AFFILIATE_PRODUCT_SHOWCASE_VERSION,
						true
					);
				}
			}
		}

		self::$vite_assets_loaded = true;
	}

	public function render_showcase( array $atts ): string {
		// Load Vite frontend assets for showcase template
		$this->loadViteAssets();
		
		ob_start();
		include AFFILIATE_PRODUCT_SHOWCASE_DIR . '/frontend/templates/products-showcase.php';
		return ob_get_clean();
	}

	public function render_single( array $atts ): string {
		$atts    = shortcode_atts( [ 'id' => 0 ], $atts );
		$product = $this->product_service->get_product( (int) $atts['id'] );
		if ( ! $product ) {
			return '';
		}

		return aps_view( 'src/Public/partials/product-card.php', [
			'product'           => $product,
			'settings'          => $this->settings_repository->get_settings(),
			'affiliate_service' => $this->affiliate_service,
		] );
	}

	public function render_grid( array $atts ): string {
		$atts     = shortcode_atts( [ 'per_page' => 6 ], $atts );
		$products = $this->product_service->get_products( [ 'per_page' => (int) $atts['per_page'] ] );

		return aps_view( 'src/Public/partials/product-grid.php', [
			'products'          => $products,
			'settings'          => $this->settings_repository->get_settings(),
			'affiliate_service' => $this->affiliate_service,
		] );
	}
}
