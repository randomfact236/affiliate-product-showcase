<?php

namespace AffiliateProductShowcase\Plugin;

class Loader {
	/** @var array<int, array{type:string, hook:string, callback:callable, priority:int, accepted_args:int}> */
	private $hooks = array();

	public function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->hooks[] = array(
			'type'          => 'action',
			'hook'          => (string) $hook,
			'callback'      => $callback,
			'priority'      => (int) $priority,
			'accepted_args' => (int) $accepted_args,
		);
	}

	public function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->hooks[] = array(
			'type'          => 'filter',
			'hook'          => (string) $hook,
			'callback'      => $callback,
			'priority'      => (int) $priority,
			'accepted_args' => (int) $accepted_args,
		);
	}

	public function run() {
		foreach ( $this->hooks as $hook ) {
			if ( 'action' === $hook['type'] ) {
				add_action( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
				continue;
			}

			add_filter( $hook['hook'], $hook['callback'], $hook['priority'], $hook['accepted_args'] );
		}
	}
}
