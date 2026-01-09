<?php

namespace AffiliateProductShowcase\Plugin;

final class Constants {
	public const VERSION       = '1.0.0';
	public const TEXTDOMAIN    = 'affiliate-product-showcase';
	public const PREFIX        = 'aps_';
	public const SLUG          = 'affiliate-product-showcase';
	public const CPT_PRODUCT   = 'aps_product';
	public const OPTION_PREFIX = 'aps_';
	public const REST_NAMESPACE = 'affiliate/v1';
	public const FILE          = __DIR__ . '/../../affiliate-product-showcase.php';
	public const MENU_CAP      = 'manage_options';
	public const NONCE_ACTION  = 'aps_action';

	public static function basename(): string {
		return plugin_basename( self::FILE );
	}

	public static function dirPath(): string {
		return plugin_dir_path( self::FILE );
	}

	public static function dirUrl(): string {
		return plugin_dir_url( self::FILE );
	}

	public static function languagesPath(): string {
		return dirname( self::basename() ) . '/languages';
	}

	public static function assetUrl( string $relative ): string {
		return self::dirUrl() . ltrim( $relative, '/\\' );
	}

	public static function viewPath( string $relative ): string {
		return self::dirPath() . ltrim( $relative, '/\\' );
	}
}
