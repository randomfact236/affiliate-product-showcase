<?php

namespace AffiliateProductShowcase\Traits;

trait HooksTrait
{
	/**
	 * Return action hook definitions.
	 *
	 * Each item can be:
	 * - [hook, method]
	 * - [hook, method, priority]
	 * - [hook, method, priority, acceptedArgs]
	 *
	 * Where `method` is a method name on `$this`.
	 */
	protected function actions(): array
	{
		return [];
	}

	/**
	 * Return filter hook definitions.
	 *
	 * Each item can be:
	 * - [hook, method]
	 * - [hook, method, priority]
	 * - [hook, method, priority, acceptedArgs]
	 *
	 * Where `method` is a method name on `$this`.
	 */
	protected function filters(): array
	{
		return [];
	}

	public function register_hooks(): void
	{
		foreach ($this->actions() as $definition) {
			$this->register_hook('add_action', $definition);
		}

		foreach ($this->filters() as $definition) {
			$this->register_hook('add_filter', $definition);
		}
	}

	private function register_hook(string $registrar, array $definition): void
	{
		$hook = $definition[0] ?? null;
		$method = $definition[1] ?? null;
		$priority = isset($definition[2]) ? (int) $definition[2] : 10;
		$acceptedArgs = isset($definition[3]) ? (int) $definition[3] : 1;

		if (!is_string($hook) || $hook === '' || !is_string($method) || $method === '') {
			return;
		}

		$callable = [$this, $method];
		if (!is_callable($callable)) {
			return;
		}

		if ($registrar === 'add_action') {
			add_action($hook, $callable, $priority, $acceptedArgs);
			return;
		}

		if ($registrar === 'add_filter') {
			add_filter($hook, $callable, $priority, $acceptedArgs);
			return;
		}
	}
}
