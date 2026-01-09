<?php

namespace AffiliateProductShowcase\Blocks;

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class Blocks {
	private SettingsRepository $settings_repository;

	public function __construct( private ProductService $product_service ) {
		$this->settings_repository = new SettingsRepository();
	}

	public function register(): void {
		$blocks = [
			'blocks/product-showcase',
			'blocks/product-grid',
		];

		foreach ( $blocks as $block ) {
			register_block_type(
				Constants::viewPath( $block ),
				[
					'render_callback' => [ $this, 'render_block' ],
				]
			);
		}
	}

	public function render_block( array $attributes, string $content, \WP_Block $block ): string {
		$type     = $block->name;
		$products = $this->product_service->get_products( [ 'per_page' => $attributes['perPage'] ?? 6 ] );

		$settings = $this->settings_repository->get_settings();

		if ( false !== strpos( $type, 'product-grid' ) ) {
			return aps_view( 'src/Public/partials/product-grid.php', [ 'products' => $products, 'attributes' => $attributes, 'settings' => $settings ] );
		}

		$product = $products[0] ?? null;
		return $product ? aps_view( 'src/Public/partials/product-card.php', [ 'product' => $product, 'settings' => $settings ] ) : '';
	}
}
