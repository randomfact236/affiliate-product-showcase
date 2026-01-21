<?php
/**
 * Dependency Injection Container
 *
 * Manages all plugin services with automatic dependency injection.
 * Follows singleton pattern for service instances.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Admin\Menu;
use AffiliateProductShowcase\Admin\Columns;
use AffiliateProductShowcase\Admin\BulkActions;
use AffiliateProductShowcase\Admin\TermMeta;
use AffiliateProductShowcase\Admin\TermUI;
use AffiliateProductShowcase\Admin\Settings;
use AffiliateProductShowcase\Admin\MetaBoxes;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Public\Templates;
use AffiliateProductShowcase\Public\Shortcodes;
use AffiliateProductShowcase\Public\Widgets;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Rest\ProductsController;
use AffiliateProductShowcase\Rest\AffiliatesController;
use AffiliateProductShowcase\Rest\HealthController;
use AffiliateProductShowcase\Rest\TermsController;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\AffiliateRepository;
use AffiliateProductShowcase\Repositories\AnalyticsRepository;
use AffiliateProductShowcase\Repositories\FeaturesRepository;
use AffiliateProductShowcase\Validators\ProductValidator;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Security\Sanitizer;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Privacy\GDPR;
use AffiliateProductShowcase\Cli\ProductsCommand;

/**
 * Dependency Injection Container
 *
 * Manages all plugin services with automatic dependency injection.
 * Implements singleton pattern to ensure only one instance of each service.
 * Automatically resolves dependencies when services are requested.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 * @author Development Team
 */
final class Container {

	/**
	 * Service registry
	 *
	 * Stores all registered services.
	 *
	 * @var array<string, object>
	 * @since 1.0.0
	 */
	private array $services = [];

	/**
	 * Constructor
	 *
	 * Initializes all services and stores them in the registry.
	 * Services are instantiated only when first requested.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->services = [];
	}

	/**
	 * Singleton instance
	 *
	 * @var Container|null
	 * @since 1.0.0
	 */
	private static ?Container $instance = null;

	/**
	 * Get container instance
	 *
	 * Returns singleton instance of container.
	 *
	 * @return Container Container instance
	 * @since 1.0.0
	 */
	public static function get_instance(): Container {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get service
	 *
	 * Retrieves a service instance from the container.
	 * If the service doesn't exist, it will be instantiated
	 * with all its dependencies automatically resolved.
	 *
	 * @template T of service class
	 * @param array<mixed> ...$constructor_args Arguments to pass to service constructor
	 * @return T Service instance
	 * @since 1.0.0
	 * @throws InvalidArgumentException If service class doesn't exist
	 */
	public function get( string $service_class, ...$constructor_args ): object {
		// Check if service is already instantiated
		if ( isset( $this->services[ $service_class ] ) ) {
			// If it's already a string (not instantiated), instantiate it now
			if ( is_string( $this->services[ $service_class ] ) ) {
				$dependencies = $this->resolve_dependencies( $service_class );
				$service = new $service_class( ...$dependencies, ...$constructor_args );
				$this->services[ $service_class ] = $service;
				return $service;
			}
			
			return $this->services[ $service_class ];
		}

		// Check if service class exists
		if ( ! class_exists( $service_class ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Service class "%s" does not exist', $service_class )
			);
		}

		// Resolve dependencies automatically
		$dependencies = $this->resolve_dependencies( $service_class );
		
		// Instantiate service with resolved dependencies and additional arguments
		$service = new $service_class( ...$dependencies, ...$constructor_args );

		// Store service in registry
		$this->services[ $service_class ] = $service;

		return $service;
	}

	/**
	 * Register admin services
	 *
	 * Registers all admin-related services with their dependencies.
	 * Services are registered but not instantiated until requested.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @see AffiliateProductShowcase\Admin\Admin
	 * @see AffiliateProductShowcase\Admin\Menu
	 * @see AffiliateProductShowcase\Admin\Columns
	 * @see AffiliateProductShowcase\Admin\BulkActions
	 * @see AffiliateProductShowcase\Admin\TermMeta
	 * @see AffiliateProductShowcase\Admin\TermUI
	 * @see AffiliateProductShowcase\Admin\Settings
	 * @see AffiliateProductShowcase\Admin\MetaBoxes
	 */
	private function register_admin_services(): void {
		$this->register( AffiliateProductShowcase\Admin\Admin::class );
		$this->register( AffiliateProductShowcase\Admin\Menu::class );
		$this->register( AffiliateProductShowcase\Admin\Columns::class );
		$this->register( AffiliateProductShowcase\Admin\BulkActions::class );
		$this->register( AffiliateProductShowcase\Admin\TermMeta::class );
		$this->register( AffiliateProductShowcase\Admin\TermUI::class );
		$this->register( AffiliateProductShowcase\Admin\Settings::class );
		$this->register( AffiliateProductShowcase\Admin\MetaBoxes::class );
	}

	/**
	 * Register public services
	 *
	 * Registers all public-facing services with their dependencies.
	 * Services are registered but not instantiated until requested.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @see AffiliateProductShowcase\Public\Templates
	 * @see AffiliateProductShowcase\Public\Shortcodes
	 * @see AffiliateProductShowcase\Public\Widgets
	 * @see AffiliateProductShowcase\Public\ProductCardRenderer
	 * @see AffiliateProductShowcase\Public\CategoriesWidget
	 * @see AffiliateProductShowcase\Public\Public_
	 */
	private function register_public_services(): void {
		$this->register( AffiliateProductShowcase\Public\Templates::class );
		$this->register( AffiliateProductShowcase\Public\Shortcodes::class );
		$this->register( AffiliateProductShowcase\Public\Widgets::class );
		$this->register( AffiliateProductShowcase\Public\ProductCardRenderer::class );
		$this->register( AffiliateProductShowcase\Public\CategoriesWidget::class );
		$this->register( AffiliateProductShowcase\Public\Public_::class );
	}

	/**
	 * Register REST API services
	 *
	 * Registers all REST API controllers with their dependencies.
	 * Services are registered but not instantiated until requested.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @see AffiliateProductShowcase\Rest\ProductsController
	 * @see AffiliateProductShowcase\Rest\AffiliatesController
	 * @see AffiliateProductShowcase\Rest\HealthController
	 * @see AffiliateProductShowcase\Rest\TermsController
	 */
	private function register_rest_services(): void {
		$this->register( AffiliateProductShowcase\Rest\ProductsController::class );
		$this->register( AffiliateProductShowcase\Rest\AffiliatesController::class );
		$this->register( AffiliateProductShowcase\Rest\HealthController::class );
		$this->register( AffiliateProductShowcase\Rest\TermsController::class );
	}

	/**
	 * Register services
	 *
	 * Registers all plugin services (admin, public, REST API).
	 * Called during plugin initialization.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action plugins_loaded
	 */
	public function register_services(): void {
		$this->register_admin_services();
		$this->register_public_services();
		$this->register_rest_services();
	}

	/**
	 * Register service class
	 *
	 * Registers a service class without instantiation.
	 * Service will be instantiated when first requested via get().
	 *
	 * @param string $service_class Full class name of service
	 * @return void
	 * @since 1.0.0
	 */
	private function register( string $service_class ): void {
		// Store service class for later instantiation
		// Dependencies will be resolved automatically when get() is called
		$this->services[ $service_class ] = $service_class;
	}

	/**
	 * Resolve dependencies for service
	 *
	 * Automatically resolves dependencies for a given service class.
	 * Uses reflection to analyze constructor parameters.
	 * Only resolves class dependencies; scalar types (int, string, bool, etc.)
	 * are skipped and will use their default values if provided.
	 *
	 * @param string $service_class Service class name
	 * @return array<mixed> Array of resolved dependencies
	 * @since 1.0.0
	 */
	private function resolve_dependencies( string $service_class ): array {
		$dependencies = [];
		
		// Use reflection to analyze service constructor
		if ( class_exists( $service_class ) ) {
			$reflection = new \ReflectionClass( $service_class );
			$constructor = $reflection->getConstructor();
			
			if ( $constructor ) {
				foreach ( $constructor->getParameters() as $param ) {
					$type = $param->getType();
					
					// Only resolve class dependencies, skip scalar types
					if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
						$dependencies[ $param->getName() ] = $this->get( $type->getName() );
					}
				}
			}
		}
		
		return $dependencies;
	}

	/**
	 * Get dependencies by type
	 *
	 * Returns all registered services grouped by type.
	 * Useful for debugging and dependency analysis.
	 *
	 * @return array<string, array<string>> Services grouped by type
	 * @since 1.0.0
	 */
	public function get_dependencies_by_type(): array {
		$by_type = [
			'admin' => [],
			'public' => [],
			'rest' => [],
			'services' => [],
			'repositories' => [],
			'validators' => [],
			'factories' => [],
		];

		foreach ( $this->services as $service_class => $service ) {
			$deps = $this->resolve_dependencies( $service_class );
			
			// Categorize service by its main namespace
			if ( strpos( $service_class, 'AffiliateProductShowcase\\Admin\\' ) === 0 ) {
				$by_type['admin'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Public\\' ) === 0 ) {
				$by_type['public'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Rest\\' ) === 0 ) {
				$by_type['rest'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Services\\' ) === 0 ) {
				$by_type['services'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Repositories\\' ) === 0 ) {
				$by_type['repositories'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Validators\\' ) === 0 ) {
				$by_type['validators'][] = $service_class;
			} elseif ( strpos( $service_class, 'AffiliateProductShowcase\\Factories\\' ) === 0 ) {
				$by_type['factories'][] = $service_class;
			}
		}

		return $by_type;
	}

	/**
	 * Reset container
	 *
	 * Clears all registered services.
	 * Used primarily in testing environments.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function reset(): void {
		$this->services = [];
	}
}
