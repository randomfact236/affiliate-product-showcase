<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Blocks;

use AffiliateProductShowcase\Services\ProductService;

/**
 * Blocks - Frontend rendering disabled
 *
 * User will provide custom frontend design code.
 *
 * @package AffiliateProductShowcase\Blocks
 * @since 1.0.0
 */
final class Blocks {

	public function __construct( private ProductService $product_service ) {}

	/**
	 * Register blocks
	 *
	 * @return void
	 */
	public function register(): void {
		// Blocks registered but frontend rendering disabled
		// User will provide custom design code
	}

	/**
	 * Render product showcase block - disabled
	 *
	 * @param array<string, mixed> $attributes Block attributes
	 * @return string Rendered HTML
	 */
	public function render_showcase_block( array $attributes ): string {
		return '<!-- Product Showcase block - Custom frontend design will be inserted here -->';
	}

	/**
	 * Render product grid block - disabled
	 *
	 * @param array<string, mixed> $attributes Block attributes
	 * @return string Rendered HTML
	 */
	public function render_grid_block( array $attributes ): string {
		return '<!-- Product Grid block - Custom frontend design will be inserted here -->';
	}
}
