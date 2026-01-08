<?php

namespace AffiliateProductShowcase\Blocks;

class Blocks {
	public function register_blocks() {
		$base = APS_PLUGIN_DIR . 'blocks/';
		if ( ! is_dir( $base ) ) {
			return;
		}

		foreach ( glob( $base . '*/block.json' ) as $block_json ) {
			register_block_type( dirname( $block_json ) );
		}
	}
}
