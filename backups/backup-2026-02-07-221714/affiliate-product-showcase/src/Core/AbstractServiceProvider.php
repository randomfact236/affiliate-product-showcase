<?php
/**
 * Abstract Service Provider
 *
 * Base class for service providers.
 * Follows the Template Method pattern and SOLID principles.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core;

use AffiliateProductShowcase\Core\DependencyInjection\Container;
use AffiliateProductShowcase\Core\Interfaces\ServiceProviderInterface;

/**
 * Abstract Class AbstractServiceProvider
 *
 * Provides a base implementation for service providers.
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface {

	/**
	 * Register services with the container.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	abstract public function register( Container $container ): void;

	/**
	 * Bootstrap any application services.
	 *
	 * Default implementation does nothing.
	 * Override in child classes as needed.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	public function boot( Container $container ): void {
		// Default implementation: do nothing
	}

	/**
	 * Get the service provider namespace.
	 *
	 * @return string Service provider namespace.
	 * @since 1.0.0
	 */
	public function getNamespace(): string {
		return static::class;
	}

	/**
	 * Get the service provider priority.
	 *
	 * Lower priority means the provider loads earlier.
	 *
	 * @return int Priority value (default: 10).
	 * @since 1.0.0
	 */
	public function getPriority(): int {
		return 10;
	}
}
