<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Assets;

final class Assets {
	private Manifest $manifest;

	public function __construct( Manifest $manifest ) {
		$this->manifest = $manifest;
		add_filter( 'script_loader_tag', [ $this, 'add_script_attributes' ], 10, 2 );
	}

	public function enqueue_admin(): void {
		$this->manifest->enqueue_script( 'aps-admin', 'admin.js', [ 'wp-element' ], true );
		$this->manifest->enqueue_style( 'aps-admin-style', 'admin.css' );
	}

	public function enqueue_frontend(): void {
		$this->manifest->enqueue_script( 'aps-frontend', 'frontend.js', [ 'wp-element' ], true );
		$this->manifest->enqueue_style( 'aps-frontend-style', 'frontend.css' );
	}

	public function enqueue_editor(): void {
		$this->manifest->enqueue_script(
			'aps-blocks',
			'blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
			true
		);
		$this->manifest->enqueue_style( 'aps-editor-style', 'editor.css' );
	}

	/**
	 * Add defer/async attributes to plugin scripts.
	 * 
	 * Adds 'defer' attribute to frontend scripts and 'async' 
	 * to admin scripts for better performance.
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
		if ( 'aps-frontend' === $handle || 'aps-blocks' === $handle ) {
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
