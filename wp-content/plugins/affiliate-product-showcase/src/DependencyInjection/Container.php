<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\DependencyInjection;

use RuntimeException;

/**
 * Simple Dependency Injection Container
 * 
 * @package AffiliateProductShowcase\DependencyInjection
 */
class Container implements ContainerInterface {
    /** @var array<string, callable> */
    private array $factories = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, bool> */
    private array $shared = [];

    /**
     * Register a service with the container
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function to create the service
     * @param bool $shared Whether the service should be a singleton
     * @return self
     */
    public function register(string $id, callable $factory, bool $shared = true): self {
        $this->factories[$id] = $factory;
        $this->shared[$id] = $shared;

        return $this;
    }

    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return mixed
     * @throws RuntimeException If service not found
     */
    public function get(string $id) {
        if (isset($this->instances[$id]) && $this->shared[$id]) {
            return $this->instances[$id];
        }

        if (!isset($this->factories[$id])) {
            throw new RuntimeException("Service '{$id}' is not registered in the container.");
        }

        $instance = $this->factories[$id]($this);

        if ($this->shared[$id]) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    /**
     * Check if a service exists in the container
     *
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool {
        return isset($this->factories[$id]);
    }

    /**
     * Register a service provider
     *
     * @param ServiceProviderInterface $provider
     * @return self
     */
    public function registerProvider(ServiceProviderInterface $provider): self {
        $provider->register($this);
        return $this;
    }

    /**
     * Remove a service from the container
     *
     * @param string $id Service identifier
     * @return self
     */
    public function remove(string $id): self {
        unset($this->factories[$id], $this->instances[$id], $this->shared[$id]);
        return $this;
    }

    /**
     * Clear all services from the container
     *
     * @return self
     */
    public function clear(): self {
        $this->factories = [];
        $this->instances = [];
        $this->shared = [];
        return $this;
    }

    /**
     * Call a callable with dependency injection
     *
     * @param callable $callable
     * @param array $parameters Additional parameters
     * @return mixed
     */
    public function call(callable $callable, array $parameters = []) {
        $reflection = $this->getReflectionForCallable($callable);
        $args = [];

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            // Use provided parameter if available
            if (isset($parameters[$name])) {
                $args[] = $parameters[$name];
                continue;
            }

            // Try to resolve from container
            $type = $parameter->getType();
            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if ($this->has($typeName)) {
                    $args[] = $this->get($typeName);
                    continue;
                }
            }

            // Use default value if available
            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }

            // Throw exception if cannot resolve
            throw new RuntimeException(
                "Cannot resolve parameter '{$name}' in callable."
            );
        }

        return $callable(...$args);
    }

    /**
     * Get reflection for a callable
     *
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    private function getReflectionForCallable(callable $callable): \ReflectionFunctionAbstract {
        if (is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        if (is_object($callable) && !$callable instanceof \Closure) {
            return new \ReflectionMethod($callable, '__invoke');
        }

        return new \ReflectionFunction($callable);
    }
}
