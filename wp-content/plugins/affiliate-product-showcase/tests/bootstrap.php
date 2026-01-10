<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

// Provide minimal plugin helper functions required during plugin bootstrap.
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( string $file ): string {
		return rtrim( sys_get_temp_dir(), '/\\' ) . '/aps/';
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( string $file ): string {
		return 'https://example.com/wp-content/plugins/affiliate-product-showcase/';
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( string $file ): string {
		return basename( $file );
	}
}

// Minimal translation helper used by the plugin. Tests expect a global __() function
// to be available; provide a simple passthrough for test runs.
if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = '' ): string {
		return $text;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private string $code;
		private string $message;
		private $data;

		public function __construct( string $code = '', string $message = '', $data = null ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ): bool {
		return $thing instanceof WP_Error;
	}
}

// Defer defining WordPress helper functions to Brain Monkey so Patchwork can redefine them during tests.

// Compatibility shim: some tests call Brain\Monkey\Functions::when() etc.
// Brain Monkey exposes namespaced functions under Brain\Monkey\Functions\when(),
// so provide a small proxy class to keep test code compatible.
if ( ! class_exists( '\\Brain\\Monkey\\Functions' ) ) {
	eval('namespace Brain\\Monkey { class Functions { public static function __callStatic($name, $arguments) { return \\call_user_func_array("\\\\Brain\\\\Monkey\\\\Functions\\\\" . $name, $arguments); } } }');
}
