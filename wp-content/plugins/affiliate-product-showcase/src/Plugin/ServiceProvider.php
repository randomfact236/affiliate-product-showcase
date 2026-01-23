<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Admin\AjaxHandler;
use AffiliateProductShowcase\Admin\Menu;
use AffiliateProductShowcase\Admin\ProductFormHandler;
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
use AffiliateProductShowcase\Rest\AffiliatesController;
use AffiliateProductShowcase\Rest\AnalyticsController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Validators\ProductValidator;
use League\Container\ContainerAwareTrait;
use League\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Service Provider for Dependency Injection Container
 *
 * Registers all services with their dependencies in the container.
 * Uses shared instances where appropriate for performance.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 */
final class ServiceProvider implements ServiceProviderInterface {
	use ContainerAwareTrait;

	/**
	 * @var string
	 */
	protected $identifier;

	/**
	 * Get the identifier for this service provider
	 *
	 * @return string
	 */
	public function getIdentifier(): string {
		return $this->identifier ?? get_class( $this );
	}

	/**
	 * Set the identifier for this service provider
	 *
	 * @param string $id The identifier to set
	 * @return ServiceProviderInterface
	 */
	public function setIdentifier( string $id ): ServiceProviderInterface {
		$this->identifier = $id;
		return $this;
	}

	/**
	 * Check if this provider provides a specific service
	 *
	 * @param string $id The service identifier to check
	 * @return bool
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
			Menu::class,
			ProductFormHandler::class,
			Admin::class,
			AjaxHandler::class,

			// Public
			Public_::class,

			// Blocks
			Blocks::class,

			// REST Controllers
			ProductsController::class,
			AnalyticsController::class,
			HealthController::class,
			AffiliatesController::class,

			// CLI
			ProductsCommand::class,

			// Privacy
			GDPR::class,
		];

		return in_array( $id, $services );
	}

	/**
	 * Boot services
	 *
	 * @return void
	 */
	public function boot(): void {
		// Initialize AjaxHandler which registers its own hooks
		if ( $this->getContainer()->has( AjaxHandler::class ) ) {
			$this->getContainer()->get( AjaxHandler::class );
		}
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
		$this->getContainer()->addShared( Menu::class );
		$this->getContainer()->addShared( ProductFormHandler::class )
			->addArgument( ProductRepository::class )
			->addArgument( ProductFactory::class );
		$this->getContainer()->addShared( Admin::class )
			->addArgument( Assets::class )
			->addArgument( ProductService::class )
			->addArgument( Headers::class )
			->addArgument( Menu::class )
			->addArgument( ProductFormHandler::class );
		$this->getContainer()->addShared( AjaxHandler::class )
			->addArgument( ProductService::class )
			->addArgument( ProductRepository::class );

		// ============================================================================
		// Public (Shared - Request Scope)
		// ============================================================================
		$this->getContainer()->addShared( Public_::class )
			->addArgument( Assets::class )
			->addArgument( ProductService::class )
			->addArgument( SettingsRepository::class )
			->addArgument( AffiliateService::class );

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

		$this->getContainer()->addShared( AffiliatesController::class )
			->addArgument( AffiliateService::class );

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
