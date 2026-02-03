<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

/**
 * Shortcodes - Pure HTML Rendering
 * 
 * NOTE: Asset loading is handled entirely by Enqueue.php.
 * This class only renders HTML templates.
 * 
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 */
final class Shortcodes {

	public function __construct( 
		private ProductService $product_service, 
		private SettingsRepository $settings_repository,
		private AffiliateService $affiliate_service 
	) {}

	/**
	 * Register shortcodes
	 */
	public function register(): void {
		add_shortcode( 'aps_product', [ $this, 'renderSingle' ] );
		add_shortcode( 'aps_products', [ $this, 'renderGrid' ] );
		add_shortcode( 'aps_showcase', [ $this, 'renderShowcase' ] );
	}

	/**
	 * Render single product card
	 */
	public function renderSingle( array $atts ): string {
		$atts    = shortcode_atts( [ 'id' => 0 ], $atts );
		$product = $this->product_service->get_product( (int) $atts['id'] );
		
		if ( ! $product ) {
			return '';
		}

		return $this->renderTemplate( 'product-card', [
			'product'           => $product,
			'settings'          => $this->settings_repository->get_settings(),
			'affiliate_service' => $this->affiliate_service,
		] );
	}

	/**
	 * Render product grid
	 */
	public function renderGrid( array $atts ): string {
		$atts     = shortcode_atts( [ 'per_page' => 6 ], $atts );
		$products = $this->product_service->get_products( [ 'per_page' => (int) $atts['per_page'] ] );

		return $this->renderTemplate( 'product-grid', [
			'products'          => $products,
			'settings'          => $this->settings_repository->get_settings(),
			'affiliate_service' => $this->affiliate_service,
		] );
	}

	/**
	 * Render product showcase (full layout with filters)
	 */
	public function renderShowcase( array $atts ): string {
		$products = $this->product_service->get_products( [ 'per_page' => 12 ] );

		return $this->renderTemplate( 'showcase', [
			'products'          => $products,
			'settings'          => $this->settings_repository->get_settings(),
			'affiliate_service' => $this->affiliate_service,
		] );
	}

	/**
	 * Render a template with extracted variables
	 */
	private function renderTemplate( string $template, array $data ): string {
		$template_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . "templates/{$template}.php";
		
		if ( ! file_exists( $template_path ) ) {
			return sprintf(
				'<!-- Template not found: %s -->',
				esc_html( $template )
			);
		}

		extract( $data );
		
		ob_start();
		include $template_path;
		return ob_get_clean();
	}
}
