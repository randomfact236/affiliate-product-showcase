<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

/**
 * Health Check Controller
 *
 * Provides a health check endpoint for monitoring plugin status.
 * Can be integrated with uptime monitoring services.
 *
 * @package AffiliateProductShowcase
 * @subpackage Rest
 * @since 1.0.0
 */
final class HealthController {
	private string $namespace = 'affiliate-product-showcase/v1';

	/**
	 * Register health check routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/health',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'health_check' ],
				'permission_callback' => '__return_true',
				'schema'              => [ $this, 'get_health_schema' ],
			]
		);
	}

	/**
	 * Health check endpoint handler.
	 *
	 * Returns plugin health status including:
	 * - Overall status (healthy/unhealthy)
	 * - Database connectivity
	 * - Cache status
	 * - Plugin version
	 *
	 * @return WP_REST_Response|WP_Error Health check response
	 */
	public function health_check(): WP_REST_Response {
		$checks = [
			'database' => $this->check_database(),
			'cache'    => $this->check_cache(),
			'plugin'   => $this->check_plugin_status(),
		];

		$all_healthy = true;
		foreach ( $checks as $check ) {
			if ( ! $check['status'] ) {
				$all_healthy = false;
				break;
			}
		}

		return new WP_REST_Response(
			[
				'status'   => $all_healthy ? 'healthy' : 'unhealthy',
				'timestamp' => current_time( 'mysql' ),
				'checks'   => $checks,
				'version'  => defined( 'APS_VERSION' ) ? APS_VERSION : 'unknown',
			],
			$all_healthy ? 200 : 503
		);
	}

	/**
	 * Check database connectivity.
	 *
	 * @return array<string, mixed> Check result with status and message
	 */
	private function check_database(): array {
		global $wpdb;

		// Simple connectivity check
		$result = $wpdb->get_var( 'SELECT 1' );

		if ( '1' === $result ) {
			return [
				'status'  => true,
				'message' => 'Database connection is healthy',
			];
		}

		return [
			'status'  => false,
			'message' => 'Database connection failed',
		];
	}

	/**
	 * Check cache functionality.
	 *
	 * @return array<string, mixed> Check result with status and message
	 */
	private function check_cache(): array {
		// Test write and read
		$test_key   = 'health_check_test_' . time();
		$test_value = 'test_value';

		set_transient( $test_key, $test_value, 10 );
		$retrieved = get_transient( $test_key );
		delete_transient( $test_key );

		if ( $test_value === $retrieved ) {
			return [
				'status'  => true,
				'message' => 'Cache is working properly',
			];
		}

		return [
			'status'  => false,
			'message' => 'Cache is not functioning correctly',
		];
	}

	/**
	 * Check plugin status.
	 *
	 * @return array<string, mixed> Check result with status and message
	 */
	private function check_plugin_status(): array {
		// Check if plugin is active
		if ( ! defined( 'APS_VERSION' ) ) {
			return [
				'status'  => false,
				'message' => 'Plugin version constant not defined',
			];
		}

		// Check if critical services exist
		$required_classes = [
			'AffiliateProductShowcase\Services\ProductService',
			'AffiliateProductShowcase\Services\AffiliateService',
			'AffiliateProductShowcase\Services\AnalyticsService',
		];

		$missing = [];
		foreach ( $required_classes as $class ) {
			if ( ! class_exists( $class ) ) {
				$missing[] = $class;
			}
		}

		if ( empty( $missing ) ) {
			return [
				'status'  => true,
				'message' => 'All critical services are available',
			];
		}

		return [
			'status'  => false,
			'message' => 'Missing classes: ' . implode( ', ', $missing ),
		];
	}

	/**
	 * Get health check schema.
	 *
	 * @return array<string, mixed> Schema for health check response
	 */
	public function get_health_schema(): array {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'health',
			'type'       => 'object',
			'properties' => [
				'status'    => [
					'description' => 'Overall health status',
					'type'        => 'string',
					'enum'        => [ 'healthy', 'unhealthy' ],
				],
				'timestamp' => [
					'description' => 'Check timestamp',
					'type'        => 'string',
					'format'      => 'date-time',
				],
				'checks'    => [
					'type'     => 'object',
					'properties' => [
						'database' => [
							'type'       => 'object',
							'properties' => [
								'status'  => [ 'type' => 'boolean' ],
								'message' => [ 'type' => 'string' ],
							],
						],
						'cache'    => [
							'type'       => 'object',
							'properties' => [
								'status'  => [ 'type' => 'boolean' ],
								'message' => [ 'type' => 'string' ],
							],
						],
						'plugin'   => [
							'type'       => 'object',
							'properties' => [
								'status'  => [ 'type' => 'boolean' ],
								'message' => [ 'type' => 'string' ],
							],
						],
					],
				],
				'version'   => [
					'description' => 'Plugin version',
					'type'        => 'string',
				],
			],
		];
	}
}
