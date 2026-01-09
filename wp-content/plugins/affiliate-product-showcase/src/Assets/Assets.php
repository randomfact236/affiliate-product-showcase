<?php

namespace AffiliateProductShowcase\Assets;

use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Plugin\Constants;

final class Assets {
	private Cache $cache;
	private string $manifest_path;
	private string $dist_url;

	public function __construct( Cache $cache ) {
		$this->cache         = $cache;
		$this->manifest_path = Constants::viewPath( 'assets/dist/manifest.json' );
		$this->dist_url      = Constants::assetUrl( 'assets/dist/' );
	}

	public function enqueue_admin(): void {
		$this->enqueue_entry( 'aps-admin', 'admin.js', [ 'wp-element' ] );
		$this->enqueue_style_entry( 'aps-admin-style', 'admin.css' );
	}

	public function enqueue_frontend(): void {
		$this->enqueue_entry( 'aps-frontend', 'frontend.js', [ 'wp-element' ] );
		$this->enqueue_style_entry( 'aps-frontend-style', 'frontend.css' );
	}

	public function enqueue_editor(): void {
		$this->enqueue_entry( 'aps-blocks', 'blocks.js', [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ] );
		$this->enqueue_style_entry( 'aps-editor-style', 'editor.css' );
	}

	private function enqueue_entry( string $handle, string $entry, array $deps = [] ): void {
		$asset = $this->manifest_lookup( $entry );
		if ( ! $asset ) {
			return;
		}

		wp_register_script(
			$handle,
			$this->dist_url . $asset,
			$deps,
			Constants::VERSION,
			true
		);
		wp_enqueue_script( $handle );
	}

	private function enqueue_style_entry( string $handle, string $entry ): void {
		$asset = $this->manifest_lookup( $entry );
		if ( ! $asset ) {
			return;
		}

		wp_register_style(
			$handle,
			$this->dist_url . $asset,
			[],
			Constants::VERSION
		);
		wp_enqueue_style( $handle );
	}

	private function manifest_lookup( string $entry ): ?string {
		$manifest = $this->cache->remember( 'aps_manifest', function () {
			if ( ! file_exists( $this->manifest_path ) ) {
				return [];
			}

			$contents = file_get_contents( $this->manifest_path );
			$data     = json_decode( (string) $contents, true );

			return is_array( $data ) ? $data : [];
		}, 60 );

		if ( isset( $manifest[ $entry ]['file'] ) ) {
			return $manifest[ $entry ]['file'];
		}

		return null;
	}
}
