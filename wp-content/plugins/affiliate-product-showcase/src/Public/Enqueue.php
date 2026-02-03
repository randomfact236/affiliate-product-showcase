<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

/**
 * Public Enqueue - Frontend assets
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 */
class Enqueue {

	/**
	 * Plugin version
	 */
	private const VERSION = '1.0.0';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Enqueue public assets
	 */
	public function enqueueAssets(): void {
		if ( ! $this->shouldLoadOnCurrentPage() ) {
			return;
		}

		// Isolated showcase frontend CSS
		wp_enqueue_style(
			'affiliate-product-showcase',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/showcase-frontend-isolated.css',
			[],
			self::VERSION
		);
	}

	/**
	 * Check if plugin should load on current page
	 */
	private function shouldLoadOnCurrentPage(): bool {
		if ( is_admin() ) {
			return false;
		}

		$post = get_post();
		
		if ( ! $post ) {
			return false;
		}

		return has_shortcode( $post->post_content, 'aps_showcase' );
	}
}
