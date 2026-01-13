<?php

namespace AffiliateProductShowcase\Assets;

final class Assets {
	private Manifest $manifest;

	public function __construct( Manifest $manifest ) {
		$this->manifest = $manifest;
	}

	public function enqueue_admin(): void {
		$this->manifest->enqueue_script( 'aps-admin', 'admin.js', [ 'wp-element' ], true );
		$this->manifest->enqueue_style( 'aps-admin-style', 'admin.css' );
	}

	public function enqueue_frontend(): void {
		$this->manifest->enqueue_script( 'aps-frontend', 'frontend.js', [ 'wp-element' ], true );
		$this->manifest->enqueue_style( 'aps-frontend-style', 'frontend.css' );
	}

	public function enqueue_block_assets(): void {
		// Provides the front-end style handle used by block.json: "style": "aps-blocks".
		$this->manifest->enqueue_style( 'aps-blocks', 'frontend.css' );

		// Provides the viewScript handle used by block.json: "viewScript": "aps-blocks-frontend".
		$this->manifest->enqueue_script( 'aps-blocks-frontend', 'frontend.js', [ 'wp-element' ], true );
	}

	public function enqueue_editor(): void {
		// Provides the editorScript/editorStyle handles used by block.json.
		$this->manifest->enqueue_script(
			'aps-blocks-editor',
			'blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
			true
		);
		$this->manifest->enqueue_style( 'aps-blocks-editor', 'editor.css' );

		// Keep existing handles for backwards compatibility (if other code uses them).
		$this->manifest->enqueue_script(
			'aps-blocks',
			'blocks.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ],
			true
		);
		$this->manifest->enqueue_style( 'aps-editor-style', 'editor.css' );
	}
}
