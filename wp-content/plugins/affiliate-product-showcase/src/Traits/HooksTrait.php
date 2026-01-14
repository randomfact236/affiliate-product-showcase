<?php

namespace AffiliateProductShowcase\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait HooksTrait {
	protected function actions(): array {
		return [];
	}

	protected function filters(): array {
		return [];
	}

	public function register_hooks(): void {
		foreach ( $this->actions() as $definition ) {
			$this->register_hook( 'add_action', $definition );
		}

		foreach ( $this->filters() as $definition ) {
			$this->register_hook( 'add_filter', $definition );
		}
	}

	private function register_hook( string $registrar, array $definition ): void {
		$hook         = $definition[0] ?? null;
		$method       = $definition[1] ?? null;
		$priority     = isset( $definition[2] ) ? (int) $definition[2] : 10;
		$acceptedArgs = isset( $definition[3] ) ? (int) $definition[3] : 1;

		if ( ! is_string( $hook ) || '' === $hook || ! is_string( $method ) || '' === $method ) {
			return;
		}

		$callable = [ $this, $method ];
		if ( ! is_callable( $callable ) ) {
			return;
		}

		if ( 'add_action' === $registrar ) {
			add_action( $hook, $callable, $priority, $acceptedArgs );
			return;
		}

		if ( 'add_filter' === $registrar ) {
			add_filter( $hook, $callable, $priority, $acceptedArgs );
		}
	}
}
