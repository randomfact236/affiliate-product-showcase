<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Admin\Settings;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Blocks\Blocks;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Cli\ProductsCommand;
use AffiliateProductShowcase\Formatters\PriceFormatter;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Privacy\GDPR;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Validators\ProductValidator;

/**
 * Service Provider for Dependency Injection Container
 *
 * Registers all services with their dependencies in the container.
 * Uses shared instances where appropriate for performance.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 */
final class ServiceProvider implements \League\Container\ServiceProvider\ServiceProviderInterface {
	/**
	 * List of services provided by this provider
	 *
	 * @return array<string> Service class names
	 */
	public function provides( string $id ): bool {
		$services = [
			// Cache
			Cache::class,

			// Repositories
			ProductRepository::class,
			SettingsRepository::class,

			// Validators
			ProductValidator::class,

			// Factories
			ProductFactory::class,

			// Formatters
			PriceFormatter::class,

			// Services
			ProductService::class,
			AffiliateService::class,
			AnalyticsService::class,

			// Assets
			Manifest::class,
			SRI::class,
			Assets::class,

			// Security
			Headers::class,

			// Admin
			Settings::class,
			Admin::class,

			// Public
			Public_::class,

			// Blocks
			Blocks::class,

			// REST Controllers
			ProductsController::class,
			AnalyticsController::class,
			HealthController::class,

			// CLI
			ProductsCommand::class,

			// Privacy
			GDPR::class,
		];

		return in_array( $id, $services );
	}

	/**
	 * Register services with the container
	 *
	 * @return void
	 */
	public function register(): void {
		// ============================================================================
		// Cache Layer (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( Cache::class );

		// ============================================================================
		// Repositories (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( ProductRepository::class );
		$this->getContainer()->addShared( SettingsRepository::class );

		// ============================================================================
		// Validators (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( ProductValidator::class );

		// ============================================================================
		// Factories (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( ProductFactory::class );

		// ============================================================================
		// Formatters (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( PriceFormatter::class );

		// ============================================================================
		// Services (Shared - Business Logic Layer)
		// ============================================================================
		$this->getContainer()->addShared( ProductService::class )
			->addArgument( ProductRepository::class )
			->addArgument( ProductValidator::class )
			->addArgument( ProductFactory::class )
			->addArgument( PriceFormatter::class )
			->addArgument( Cache::class );

		$this->getContainer()->addShared( AffiliateService::class )
			->addArgument( SettingsRepository::class );

		$this->getContainer()->addShared( AnalyticsService::class )
			->addArgument( Cache::class );

		// ============================================================================
		// Assets (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( Manifest::class );
		$this->getContainer()->addShared( SRI::class )
			->addArgument( Manifest::class );
		$this->getContainer()->addShared( Assets::class )
			->addArgument( Manifest::class );

		// ============================================================================
		// Security (Shared - Performance Critical)
		// ============================================================================
		$this->getContainer()->addShared( Headers::class );

		// ============================================================================
		// Admin (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( Settings::class );
		$this->getContainer()->addShared( Admin::class )
			->addArgument( Assets::class )
			->addArgument( ProductService::class )
			->addArgument( Headers::class );

		// ============================================================================
		// Public (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( Public_::class )
			->addArgument( Assets::class )
			->addArgument( ProductService::class );

		// ============================================================================
		// Blocks (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( Blocks::class )
			->addArgument( ProductService::class );

		// ============================================================================
		// REST Controllers (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( ProductsController::class )
			->addArgument( ProductService::class );

		$this->getContainer()->addShared( AnalyticsController::class )
			->addArgument( AnalyticsService::class );

		$this->getContainer()->addShared( HealthController::class );

		// ============================================================================
		// CLI (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( ProductsCommand::class )
			->addArgument( ProductService::class );

		// ============================================================================
		// Privacy (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( GDPR::class );
	}
}
