<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

/**
 * Shortcodes - Frontend rendering
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
		add_shortcode( 'aps_showcase', [ $this, 'renderShowcase' ] );
	}

	/**
	 * Render product showcase
	 */
	public function renderShowcase( array $atts ): string {
		$template = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/showcase.php';
		
		if ( ! file_exists( $template ) ) {
			return '<p>Error: Template not found at ' . esc_html( $template ) . '</p>';
		}
		
		ob_start();
		include $template;
		$output = ob_get_clean();
		
		if ( empty( $output ) ) {
			return '<p>Error: Template rendered empty output.</p>';
		}
		
		return $output;
	}
}
