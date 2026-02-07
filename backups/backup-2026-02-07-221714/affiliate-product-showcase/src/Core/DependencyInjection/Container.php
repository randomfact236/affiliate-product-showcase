<?php
/**
 * Dependency Injection Container
 *
 * Implements a simple dependency injection container for managing service dependencies.
 * Follows the Dependency Inversion Principle (DIP) from SOLID.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core\DependencyInjection;

use AffiliateProductShowcase\Core\Exceptions\ContainerException;
use AffiliateProductShowcase\Core\Interfaces\ContainerInterface;

/**
 * Class Container
 *
 * Dependency injection container with singleton and factory support.
 */
class Container implements ContainerInterface {

	/**
	 * Registered services.
	 *
	 * @var array<string, mixed>
	 */
	private array $services = [];

	/**
	 * Singleton instances.
	 *
	 * @var array<string, object>
	 */
	private array $singletons = [];

	/**
	 * Aliases for service resolution.
	 *
	 * @var array<string, string>
	 */
	private array $aliases = [];

	/**
	 * Register a service with the container.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation (class name, closure, or object).
	 * @param bool $singleton Whether the service should be a singleton.
	 * @return self
	 * @since 1.0.0
	 */
	public function register( string $id, $concrete, bool $singleton = false ): self {
		$this->services[ $id ] = [
			'concrete' => $concrete,
			'singleton' => $singleton,
		];
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
		return $this->register( $id, $concrete, true );
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
		return $this->register( $id, $concrete, false );
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
		$this->aliases[ $alias ] = $id;
		return $this;
	}

	/**
	 * Check if a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool
	 * @since 1.0.0
	 */
	public function has( string $id ): bool {
		return isset( $this->services[ $id ] ) || isset( $this->aliases[ $id ] );
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
		// Resolve alias
		if ( isset( $this->aliases[ $id ] ) ) {
			$id = $this->aliases[ $id ];
		}

		// Check if service is registered
		if ( ! isset( $this->services[ $id ] ) ) {
			throw ContainerException::notFound( $id );
		}

		$service = $this->services[ $id ];

		// Return singleton instance if already created
		if ( $service['singleton'] && isset( $this->singletons[ $id ] ) ) {
			return $this->singletons[ $id ];
		}

		// Resolve the service
		$instance = $this->resolve( $service['concrete'] );

		// Store singleton instance
		if ( $service['singleton'] ) {
			$this->singletons[ $id ] = $instance;
		}

		return $instance;
	}

	/**
	 * Resolve a service implementation.
	 *
	 * @param mixed $concrete Service implementation.
	 * @return mixed Resolved service instance.
	 * @throws ContainerException If resolution fails.
	 * @since 1.0.0
	 */
	private function resolve( $concrete ) {
		// If concrete is already an object, return it
		if ( is_object( $concrete ) ) {
			return $concrete;
		}

		// If concrete is a closure, execute it
		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		}

		// If concrete is a class name, instantiate it
		if ( is_string( $concrete ) && class_exists( $concrete ) ) {
			return $this->instantiate( $concrete );
		}

		throw ContainerException::cannotResolve( $concrete );
	}

	/**
	 * Instantiate a class with dependency injection.
	 *
	 * @param string $class Class name.
	 * @return object Class instance.
	 * @throws ContainerException If instantiation fails.
	 * @since 1.0.0
	 */
	private function instantiate( string $class ): object {
		try {
			$reflection = new \ReflectionClass( $class );

			// Check if class is instantiable
			if ( ! $reflection->isInstantiable() ) {
				throw ContainerException::notInstantiable( $class );
			}

			$constructor = $reflection->getConstructor();

			// If no constructor, instantiate directly
			if ( null === $constructor ) {
				return new $class();
			}

			// Resolve constructor dependencies
			$dependencies = $this->resolveDependencies( $constructor->getParameters() );

			// Instantiate with resolved dependencies
			return $reflection->newInstanceArgs( $dependencies );
		} catch ( \ReflectionException $e ) {
			throw ContainerException::reflectionError( $class, $e->getMessage(), 0, $e );
		}
	}

	/**
	 * Resolve constructor dependencies.
	 *
	 * @param array<\ReflectionParameter> $parameters Constructor parameters.
	 * @return array<mixed> Resolved dependencies.
	 * @throws ContainerException If dependency cannot be resolved.
	 * @since 1.0.0
	 */
	private function resolveDependencies( array $parameters ): array {
		$dependencies = [];

		foreach ( $parameters as $parameter ) {
			$type = $parameter->getType();

			// If parameter has no type, use default value or null
			if ( null === $type ) {
				$dependencies[] = $parameter->isDefaultValueAvailable()
					? $parameter->getDefaultValue()
					: null;
				continue;
			}

			// Get class name from type
			if ( $type instanceof \ReflectionNamedType ) {
				$className = $type->getName();

				// Try to resolve from container
				if ( $this->has( $className ) ) {
					$dependencies[] = $this->get( $className );
					continue;
				}

				// Try to instantiate the class
				if ( class_exists( $className ) ) {
					$dependencies[] = $this->instantiate( $className );
					continue;
				}
			}

			// Use default value if available
			if ( $parameter->isDefaultValueAvailable() ) {
				$dependencies[] = $parameter->getDefaultValue();
				continue;
			}

			throw ContainerException::cannotResolveParameter( $parameter->getName() );
		}

		return $dependencies;
	}

	/**
	 * Clear all registered services.
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function clear(): self {
		$this->services = [];
		$this->singletons = [];
		$this->aliases = [];
		return $this;
	}

	/**
	 * Get all registered service IDs.
	 *
	 * @return array<string> Service identifiers.
	 * @since 1.0.0
	 */
	public function getRegisteredServices(): array {
		return array_keys( $this->services );
	}
}
