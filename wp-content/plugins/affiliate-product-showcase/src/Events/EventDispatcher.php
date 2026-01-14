<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;

/**
 * Event Dispatcher implementation
 * 
 * @package AffiliateProductShowcase\Events
 */
class EventDispatcher implements EventDispatcherInterface {
    /** @var array<string, array<int, array{'listener': callable, 'priority': int}>> */
    private array $listeners = [];

    /** @var array<string, bool> */
    private array $sorted = [];

    /**
     * Register an event listener
     *
     * @param string $event Event name
     * @param callable $listener Callback function
     * @param int $priority Priority (higher numbers execute later)
     * @return self
     */
    public function listen(string $event, callable $listener, int $priority = 10): self {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];

        // Mark as unsorted
        unset($this->sorted[$event]);

        return $this;
    }

    /**
     * Dispatch an event to all listeners
     *
     * @param string $event Event name
     * @param mixed $data Event data
     * @return void
     */
    public function dispatch(string $event, $data = null): void {
        if (!isset($this->listeners[$event]) || empty($this->listeners[$event])) {
            return;
        }

        // Sort listeners by priority if not already sorted
        if (!isset($this->sorted[$event])) {
            usort($this->listeners[$event], function($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });
            $this->sorted[$event] = true;
        }

        // Execute all listeners
        foreach ($this->listeners[$event] as $item) {
            $listener = $item['listener'];
            $listener($data, $event);
        }
    }

    /**
     * Remove an event listener
     *
     * @param string $event Event name
     * @param callable $listener Callback function
     * @return self
     */
    public function forget(string $event, callable $listener): self {
        if (!isset($this->listeners[$event])) {
            return $this;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            function($item) use ($listener) {
                return $item['listener'] !== $listener;
            }
        );

        unset($this->sorted[$event]);

        return $this;
    }

    /**
     * Remove all listeners for an event
     *
     * @param string $event Event name
     * @return self
     */
    public function flush(string $event): self {
        unset($this->listeners[$event], $this->sorted[$event]);
        return $this;
    }

    /**
     * Get all listeners for an event
     *
     * @param string $event Event name
     * @return array<int, callable>
     */
    public function getListeners(string $event): array {
        if (!isset($this->listeners[$event])) {
            return [];
        }

        return array_map(function($item) {
            return $item['listener'];
        }, $this->listeners[$event]);
    }

    /**
     * Check if an event has any listeners
     *
     * @param string $event Event name
     * @return bool
     */
    public function hasListeners(string $event): bool {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }

    /**
     * Get count of listeners for an event
     *
     * @param string $event Event name
     * @return int
     */
    public function getListenerCount(string $event): int {
        return isset($this->listeners[$event]) ? count($this->listeners[$event]) : 0;
    }

    /**
     * Clear all listeners for all events
     *
     * @return self
     */
    public function clear(): self {
        $this->listeners = [];
        $this->sorted = [];
        return $this;
    }
}
