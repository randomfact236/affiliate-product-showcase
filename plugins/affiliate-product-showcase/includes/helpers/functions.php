<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin option with default.
 *
 * @param string $key Option key inside aps_settings.
 * @param mixed  $default Default value.
 * @return mixed
 */
function aps_get_option( $key, $default = null ) {
	$settings = get_option( 'aps_settings', array() );
	if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
		return $settings[ $key ];
	}
	return $default;
}

/**
 * Update plugin option.
 *
 * @param string $key Option key inside aps_settings.
 * @param mixed  $value Value.
 * @return bool
 */
function aps_update_option( $key, $value ) {
	$settings         = get_option( 'aps_settings', array() );
	$settings         = is_array( $settings ) ? $settings : array();
	$settings[ $key ] = $value;
	return update_option( 'aps_settings', $settings );
}
