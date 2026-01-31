<?php
/**
 * Application
 *
 * Main application class that manages the dependency injection container
 * and service providers. Follows the Dependency Inversion Principle (DIP) from SOLID.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core;

use AffiliateProductShowcase\Core\DependencyInjection\Container;
use AffiliateProductShowcase\Core\Interfaces\ServiceProviderInterface;
use AffiliateProductShowcase\Core\Exceptions\ContainerException;

/**
 * Class Application
 *
 * Main application bootstrap and service container manager.
 */
class Application {

	/**
	 * The dependency injection container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Registered service providers.
	 *
	 * @var array<ServiceProviderInterface>
	 */
	private array $providers = [];

	/**
	 * Whether the application has been booted.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Create a new application instance.
	 *
	 * @param Container|null $container Optional container instance.
	 * @since 1.0.0
	 */
	public function __construct( ?Container $container = null ) {
		$this->container = $container ?? new Container();
	}

	/**
	 * Register a service provider.
	 *
	 * @param ServiceProviderInterface $provider Service provider to register.
	 * @return self
	 * @since 1.0.0
	 */
	public function registerProvider( ServiceProviderInterface $provider ): self {
		$this->providers[] = $provider;
		return $this;
	}

	/**
	 * Register multiple service providers.
	 *
	 * @param array<ServiceProviderInterface> $providers Array of service providers.
	 * @return self
	 * @since 1.0.0
	 */
	public function registerProviders( array $providers ): self {
		foreach ( $providers as $provider ) {
			$this->registerProvider( $provider );
		}
		return $this;
	}

	/**
	 * Get the dependency injection container.
	 *
	 * @return Container The application container.
	 * @since 1.0.0
	 */
	public function getContainer(): Container {
		return $this->container;
	}

	/**
	 * Check if the application has been booted.
	 *
	 * @return bool True if booted, false otherwise.
	 * @since 1.0.0
	 */
	public function isBooted(): bool {
		return $this->booted;
	}

	/**
	 * Boot the application.
	 *
	 * Registers all service providers and boots them.
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function boot(): self {
		if ( $this->booted ) {
			return $this;
		}

		// Sort providers by priority
		usort( $this->providers, function( ServiceProviderInterface $a, ServiceProviderInterface $b ) {
			return $a->getPriority() <=> $b->getPriority();
		} );

		// Register all providers
		foreach ( $this->providers as $provider ) {
			$provider->register( $this->container );
		}

		// Boot all providers
		foreach ( $this->providers as $provider ) {
			$provider->boot( $this->container );
		}

		$this->booted = true;

		return $this;
	}

	/**
	 * Get a service from the container.
	 *
	 * @param string $id Service identifier.
	 * @return mixed Service instance.
	 * @throws ContainerException If service not found.
	 * @since 1.0.0
	 */
	public function get( string $id ) {
		return $this->container->get( $id );
	}

	/**
	 * Check if a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool True if registered, false otherwise.
	 * @since 1.0.0
	 */
	public function has( string $id ): bool {
		return $this->container->has( $id );
	}

	/**
	 * Register a service with the container.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation.
	 * @param bool $singleton Whether to register as singleton.
	 * @return self
	 * @since 1.0.0
	 */
	public function register( string $id, $concrete, bool $singleton = false ): self {
		$this->container->register( $id, $concrete, $singleton );
		return $this;
	}

	/**
	 * Register a singleton service.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation.
	 * @return self
	 * @since 1.0.0
	 */
	public function singleton( string $id, $concrete ): self {
		$this->container->singleton( $id, $concrete );
		return $this;
	}

	/**
	 * Register a factory service.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation.
	 * @return self
	 * @since 1.0.0
	 */
	public function factory( string $id, $concrete ): self {
		$this->container->factory( $id, $concrete );
		return $this;
	}

	/**
	 * Register an alias for a service.
	 *
	 * @param string $alias Alias name.
	 * @param string $id Service identifier.
	 * @return self
	 * @since 1.0.0
	 */
	public function alias( string $alias, string $id ): self {
		$this->container->alias( $alias, $id );
		return $this;
	}

	/**
	 * Get the global application instance.
	 *
	 * @return Application The application instance.
	 * @since 1.0.0
	 */
	public static function getInstance(): self {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}
