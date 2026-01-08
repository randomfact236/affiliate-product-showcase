<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Keep uninstall safe and minimal: remove only plugin-owned options/transients.
delete_option( 'aps_settings' );
delete_transient( 'aps_products_cache' );
