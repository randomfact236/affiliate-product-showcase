<?php
/**
 * Plugin Service Provider
 *
 * Registers core plugin services with the dependency injection container.
 * Follows the Dependency Inversion Principle (DIP) from SOLID.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core\Providers;

use AffiliateProductShowcase\Core\AbstractServiceProvider;
use AffiliateProductShowcase\Core\DependencyInjection\Container;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Factories\CategoryFactory;
use AffiliateProductShowcase\Validators\UrlValidator;
use AffiliateProductShowcase\Security\CSRFProtection;
use AffiliateProductShowcase\Security\RateLimiter;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Helpers\CategoryMetaKeys;
use AffiliateProductShowcase\Rest\ProductsController;

/**
 * Class PluginServiceProvider
 *
 * Registers and bootstraps plugin services.
 */
class PluginServiceProvider extends AbstractServiceProvider {

	/**
	 * Register services with the container.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	public function register( Container $container ): void {
		// Register factories
		$container->singleton( ProductFactory::class, ProductFactory::class );
		$container->singleton( CategoryFactory::class, CategoryFactory::class );

		// Register repositories
		$container->singleton( ProductRepository::class, function( $c ) {
			return new ProductRepository( $c->get( ProductFactory::class ) );
		} );
		$container->singleton( CategoryRepository::class, function( $c ) {
			return new CategoryRepository( $c->get( CategoryFactory::class ) );
		} );

		// Register validators
		$container->singleton( UrlValidator::class, UrlValidator::class );

		// Register security services
		$container->singleton( CSRFProtection::class, CSRFProtection::class );
		$container->singleton( RateLimiter::class, RateLimiter::class );
		$container->singleton( Headers::class, Headers::class );

		// Register helpers
		$container->singleton( CategoryMetaKeys::class, CategoryMetaKeys::class );

		// Register REST controllers
		$container->factory( ProductsController::class, function( $c ) {
			return new ProductsController(
				$c->get( ProductRepository::class ),
				$c->get( CSRFProtection::class ),
				$c->get( RateLimiter::class )
			);
		} );

		// Register aliases for convenience
		$container->alias( 'product.repository', ProductRepository::class );
		$container->alias( 'category.repository', CategoryRepository::class );
		$container->alias( 'product.factory', ProductFactory::class );
		$container->alias( 'category.factory', CategoryFactory::class );
		$container->alias( 'url.validator', UrlValidator::class );
		$container->alias( 'csrf.protection', CSRFProtection::class );
		$container->alias( 'rate.limiter', RateLimiter::class );
		$container->alias( 'security.headers', Headers::class );
		$container->alias( 'category.meta.keys', CategoryMetaKeys::class );
	}

	/**
	 * Bootstrap application services.
	 *
	 * @param Container $container Dependency injection container.
	 * @return void
	 * @since 1.0.0
	 */
	public function boot( Container $container ): void {
		// Initialize security headers
		$headers = $container->get( Headers::class );
		$headers->init();

		// Initialize CSRF protection
		$csrf = $container->get( CSRFProtection::class );
		$csrf->init();

		// Initialize rate limiter
		$rateLimiter = $container->get( RateLimiter::class );
		$rateLimiter->init();
	}

	/**
	 * Get the service provider priority.
	 *
	 * Core services should load early.
	 *
	 * @return int Priority value.
	 * @since 1.0.0
	 */
	public function getPriority(): int {
		return 1; // Load first
	}
}
