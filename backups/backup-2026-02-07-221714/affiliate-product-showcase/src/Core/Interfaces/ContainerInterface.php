<?php
/**
 * Container Interface
 *
 * Interface for dependency injection container.
 * Follows the Dependency Inversion Principle (DIP) from SOLID.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core\Interfaces;

/**
 * Interface ContainerInterface
 *
 * Defines the contract for dependency injection containers.
 */
interface ContainerInterface {

	/**
	 * Register a service with the container.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation (class name, closure, or object).
	 * @param bool $singleton Whether to register as singleton.
	 * @return self
	 * @since 1.0.0
	 */
	public function register( string $id, $concrete, bool $singleton = false ): self;

	/**
	 * Register a singleton service.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation.
	 * @return self
	 * @since 1.0.0
	 */
	public function singleton( string $id, $concrete ): self;

	/**
	 * Register a factory service.
	 *
	 * @param string $id Service identifier.
	 * @param mixed $concrete Service implementation.
	 * @return self
	 * @since 1.0.0
	 */
	public function factory( string $id, $concrete ): self;

	/**
	 * Register an alias for a service.
	 *
	 * @param string $alias Alias name.
	 * @param string $id Service identifier.
	 * @return self
	 * @since 1.0.0
	 */
	public function alias( string $alias, string $id ): self;

	/**
	 * Check if a service is registered.
	 *
	 * @param string $id Service identifier.
	 * @return bool
	 * @since 1.0.0
	 */
	public function has( string $id ): bool;

	/**
	 * Get a service from the container.
	 *
	 * @param string $id Service identifier.
	 * @return mixed Service instance.
	 * @since 1.0.0
	 */
	public function get( string $id );

	/**
	 * Clear all registered services.
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function clear(): self;

	/**
	 * Get all registered service IDs.
	 *
	 * @return array<string> Service identifiers.
	 * @since 1.0.0
	 */
	public function getRegisteredServices(): array;
}
