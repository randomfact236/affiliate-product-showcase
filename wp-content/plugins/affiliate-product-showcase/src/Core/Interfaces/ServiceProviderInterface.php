<?php
/**
 * Service Provider Interface
 *
 * Interface for service providers in the dependency injection container.
 * Follows the Dependency Inversion Principle (DIP) from SOLID.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core\Interfaces;

use AffiliateProductShowcase\Core\DependencyInjection\Container;

/**
 * Interface ServiceProviderInterface
 *
 * Defines the contract for service providers.
 */
interface ServiceProviderInterface {

	/**
	 * Register services with the container.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	public function register( Container $container ): void;

	/**
	 * Bootstrap any application services.
	 *
	 * Called after all service providers have been registered.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	public function boot( Container $container ): void;
}
