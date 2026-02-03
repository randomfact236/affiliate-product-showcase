<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Assets;

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Assets Manager - Pure SCSS Architecture
 *
 * Handles enqueuing of CSS and JS files for the plugin.
 * Uses direct file loading without Vite/Tailwind dependencies.
 *
 * @package AffiliateProductShowcase\Assets
 * @since 1.0.0
 */
final class Assets {

	/**
	 * Plugin version for cache busting
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->version = Constants::VERSION;
		add_filter( 'script_loader_tag', [ $this, 'add_script_attributes' ], 10, 2 );
	}

	/**
	 * Enqueue admin assets
	 *
	 * @return void
	 */
	public function enqueue_admin(): void {
		// Admin CSS is compiled from SCSS
		wp_enqueue_style(
			'aps-admin',
			Constants::assetUrl( 'assets/css/affiliate-product-showcase.css' ),
			[],
			$this->version
		);
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @return void
	 */
	public function enqueue_frontend(): void {
		// Frontend showcase CSS
		wp_enqueue_style(
			'aps-frontend',
			Constants::assetUrl( 'assets/css/showcase-frontend-isolated.css' ),
			[],
			$this->version
		);
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_editor(): void {
		// Editor uses same CSS as frontend/admin
		wp_enqueue_style(
			'aps-editor',
			Constants::assetUrl( 'assets/css/affiliate-product-showcase.css' ),
			[],
			$this->version
		);
	}

	/**
	 * Add defer/async attributes to plugin scripts.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @return string Modified script tag with attributes.
	 */
	public function add_script_attributes( string $tag, string $handle ): string {
		// Only modify plugin scripts
		if ( ! str_starts_with( $handle, 'aps-' ) ) {
			return $tag;
		}

		// Add defer to frontend scripts
		if ( 'aps-frontend' === $handle ) {
			if ( ! str_contains( $tag, ' defer' ) && ! str_contains( $tag, 'defer=' ) ) {
				return str_replace( ' src=', ' defer src=', $tag );
			}
		}

		// Add async to admin scripts
		if ( 'aps-admin' === $handle ) {
			if ( ! str_contains( $tag, ' async' ) && ! str_contains( $tag, 'async=' ) ) {
				return str_replace( ' src=', ' async src=', $tag );
			}
		}

		return $tag;
	}
}
