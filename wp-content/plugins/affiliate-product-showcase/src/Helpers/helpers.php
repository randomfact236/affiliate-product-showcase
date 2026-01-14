<?php



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}use AffiliateProductShowcase\Plugin\Constants;

function aps_array_get( array $array, string $key, $default = null ) {
	return $array[ $key ] ?? $default;
}

function aps_bool( $value ): bool {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}

function aps_view( string $relative, array $data = [] ): string {
	$path = Constants::viewPath( $relative );
	if ( ! file_exists( $path ) ) {
		return '';
	}

	extract( $data, EXTR_SKIP );
	ob_start();
	include $path;

	return (string) ob_get_clean();
}
