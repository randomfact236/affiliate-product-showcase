<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use League\Container\Container as BaseContainer;
use League\Container\ReflectionContainer;

/**
 * Dependency Injection Container
 *
 * Extends League\Container to provide a singleton instance for the plugin.
 * Uses reflection container for automatic dependency resolution.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 */
final class Container extends BaseContainer {
	private static ?Container $instance = null;

	/**
	 * Get singleton instance of the container
	 *
	 * @return Container Container instance
	 */
	public static function get_instance(): Container {
		if ( self::$instance === null ) {
			self::$instance = new self();
			self::$instance->addServiceProvider( new ServiceProvider() );
			self::$instance->delegate( new ReflectionContainer() );
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning of the instance
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing of the instance
	 *
	 * @return void
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton' );
	}
}
