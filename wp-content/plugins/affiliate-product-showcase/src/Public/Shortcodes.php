<?php

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Shortcodes {
	public function __construct( private ProductService $product_service, private SettingsRepository $settings_repository ) {}

	public function register(): void {
		add_shortcode( 'aps_product', [ $this, 'render_single' ] );
		add_shortcode( 'aps_products', [ $this, 'render_grid' ] );
	}

	public function render_single( array $atts ): string {
		$atts    = shortcode_atts( [ 'id' => 0 ], $atts );
		$product = $this->product_service->get_product( (int) $atts['id'] );
		if ( ! $product ) {
			return '';
		}

		return aps_view( 'src/Public/partials/product-card.php', [
			'product'  => $product,
			'settings' => $this->settings_repository->get_settings(),
		] );
	}

	public function render_grid( array $atts ): string {
		$atts     = shortcode_atts( [ 'per_page' => 6 ], $atts );
		$products = $this->product_service->get_products( [ 'per_page' => (int) $atts['per_page'] ] );

		return aps_view( 'src/Public/partials/product-grid.php', [
			'products' => $products,
			'settings' => $this->settings_repository->get_settings(),
		] );
	}
}
