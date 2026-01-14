<?php

namespace AffiliateProductShowcase\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Repositories\SettingsRepository;

/**
 * GDPR Compliance Handler
 *
 * Implements WordPress personal data export and erasure hooks for GDPR compliance.
 * Handles user data related to affiliate product tracking and analytics.
 *
 * @package AffiliateProductShowcase
 * @subpackage Privacy
 * @since 1.0.0
 */
final class GDPR {
	private SettingsRepository $settings_repository;

	public function __construct() {
		$this->settings_repository = new SettingsRepository();
	}

	/**
	 * Register GDPR hooks.
	 */
	public function register(): void {
		// Register personal data exporter
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );
		
		// Register personal data eraser
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_eraser' ] );
		
		// Exporter callback
		add_action( 'wp_privacy_personal_data_export_page', [ $this, 'export_user_data' ], 10, 3 );
		
		// Eraser callback
		add_action( 'wp_privacy_personal_data_erase_page', [ $this, 'erase_user_data' ], 10, 3 );
	}

	/**
	 * Register personal data exporter.
	 *
	 * @param array $exporters Array of registered exporters
	 * @return array Updated array with our exporter
	 */
	public function register_exporter( array $exporters ): array {
		$exporters['affiliate-product-showcase-user-data'] = [
			'exporter_friendly_name' => __( 'Affiliate Product Showcase', 'affiliate-product-showcase' ),
			'callback'               => [ $this, 'export_user_data' ],
		];

		return $exporters;
	}

	/**
	 * Register personal data eraser.
	 *
	 * @param array $erasers Array of registered erasers
	 * @return array Updated array with our eraser
	 */
	public function register_eraser( array $erasers ): array {
		$erasers['affiliate-product-showcase-user-data'] = [
			'eraser_friendly_name' => __( 'Affiliate Product Showcase', 'affiliate-product-showcase' ),
			'callback'             => [ $this, 'erase_user_data' ],
		];

		return $erasers;
	}

	/**
	 * Export user's personal data.
	 *
	 * Exports analytics data related to user interactions.
	 *
	 * @param string $email_address User email address
	 * @param int $page Page number
	 * @return array Export data
	 */
	public function export_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return [
				'done' => true,
				'data' => [],
				'message' => __( 'User not found', 'affiliate-product-showcase' ),
			];
		}

		$export_data = [];
		$settings = $this->settings_repository->get_settings();

		// Export affiliate ID if user has one configured
		if ( ! empty( $settings['affiliate_id'] ) ) {
			$export_data[] = [
				'group_id'    => 'affiliate-product-showcase',
				'group_label'  => __( 'Affiliate Product Showcase', 'affiliate-product-showcase' ),
				'item_id'     => 'affiliate-id',
				'data'        => [
					[
						'name'  => __( 'Affiliate ID', 'affiliate-product-showcase' ),
						'value' => $settings['affiliate_id'],
					],
				],
			];
		}

		// Export analytics data (views/clicks)
		$analytics = get_option( 'aps_analytics', [] );
		$user_analytics = [];

		// Note: WordPress doesn't track individual user interactions by default
		// This is a placeholder for future user-specific analytics
		if ( ! empty( $analytics ) ) {
			$user_analytics = [
				[
					'name'  => __( 'Analytics Summary', 'affiliate-product-showcase' ),
					'value' => __( 'Aggregated analytics data available', 'affiliate-product-showcase' ),
				],
			];
		}

		if ( ! empty( $user_analytics ) ) {
			$export_data[] = [
				'group_id'    => 'affiliate-product-showcase-analytics',
				'group_label'  => __( 'Analytics Data', 'affiliate-product-showcase' ),
				'item_id'     => 'analytics',
				'data'        => $user_analytics,
			];
		}

		return [
			'done'    => true,
			'data'     => $export_data,
			'message'  => '',
		];
	}

	/**
	 * Erase user's personal data.
	 *
	 * Erases analytics data related to user interactions.
	 *
	 * @param string $email_address User email address
	 * @param int $page Page number
	 * @return array Erasure result
	 */
	public function erase_user_data( string $email_address, int $page = 1 ): array {
		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {
			return [
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => [ __( 'User not found', 'affiliate-product-showcase' ) ],
				'done'          => true,
			];
		}

		$messages = [];
		$items_removed = false;

		// Note: WordPress doesn't track individual user interactions by default
		// This is a placeholder for future user-specific analytics
		// Analytics data is currently aggregated by product, not by user

		$messages[] = __(
			'Affiliate Product Showcase does not store user-specific data. '
			. 'Analytics data is aggregated by product only.',
			'affiliate-product-showcase'
		);

		return [
			'items_removed'  => $items_removed,
			'items_retained' => false,
			'messages'       => $messages,
			'done'          => true,
		];
	}
}
