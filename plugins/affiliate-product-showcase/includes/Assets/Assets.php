<?php

namespace AffiliateProductShowcase\Assets;

class Assets {
	/**
	 * Enqueue a built asset from Vite manifest.
	 *
	 * @param string $entry Entry key (e.g. src/js/frontend.js).
	 * @param string $handle Script/style handle.
	 */
	public function enqueue_entry( $entry, $handle ) {
		$manifest_path = APS_PLUGIN_DIR . 'assets/dist/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			return;
		}

		$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
		if ( ! is_array( $manifest ) || ! isset( $manifest[ $entry ]['file'] ) ) {
			return;
		}

		$file = (string) $manifest[ $entry ]['file'];
		$url  = APS_PLUGIN_URL . 'assets/dist/' . ltrim( $file, '/' );

		if ( str_ends_with( $file, '.js' ) ) {
			wp_enqueue_script( $handle, $url, array(), APS_VERSION, true );
		}
		if ( str_ends_with( $file, '.css' ) ) {
			wp_enqueue_style( $handle, $url, array(), APS_VERSION );
		}
	}
}
