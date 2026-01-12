<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\DependencyInjection;

/**
 * Interface for Dependency Injection Container
 * 
 * @package AffiliateProductShowcase\DependencyInjection
 */
interface ContainerInterface {
    /**
     * Register a service with the container
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function to create the service
     * @param bool $shared Whether the service should be singleton
     * @return self
     */
    public function register(string $id, callable $factory, bool $shared = true): self;

    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws \RuntimeException If service not found
     */
    public function get(string $id);

    /**
     * Check if a service exists in the container
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Register a service provider
     *
     * @param ServiceProviderInterface $provider
     * @return self
     */
    public function registerProvider(ServiceProviderInterface $provider): self;

    /**
     * Remove a service from the container
     *
     * @param string $id Service identifier
     * @return self
     */
    public function remove(string $id): self;

    /**
     * Clear all services from the container
     *
     * @return self
     */
    public function clear(): self;
}
