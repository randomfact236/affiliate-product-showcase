<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for Event Dispatcher
 * 
 * @package AffiliateProductShowcase\Events
 */
interface EventDispatcherInterface {
    /**
     * Register an event listener
     *
     * @param string $event Event name
     * @param callable $listener Callback function
     * @param int $priority Priority (higher numbers execute later)
     * @return self
     */
    public function listen(string $event, callable $listener, int $priority = 10): self;

    /**
     * Dispatch an event to all listeners
     *
     * @param string $event Event name
     * @param mixed $data Event data
     * @return void
     */
    public function dispatch(string $event, $data = null): void;

    /**
     * Remove an event listener
     *
     * @param string $event Event name
     * @param callable $listener Callback function
     * @return self
     */
    public function forget(string $event, callable $listener): self;

    /**
     * Remove all listeners for an event
     *
     * @param string $event Event name
     * @return self
     */
    public function flush(string $event): self;

    /**
     * Get all listeners for an event
     *
     * @param string $event Event name
     * @return array<int, callable>
     */
    public function getListeners(string $event): array;
}
