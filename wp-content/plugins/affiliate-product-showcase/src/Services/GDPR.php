<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR - Handles GDPR compliance for personal data.
 * 
 * Provides export and erasure hooks for personal data
 * stored by the plugin, including analytics data.
 * 
 * @package AffiliateProductShowcase\Services
 * @since 1.0.0
 */
final class GDPR {
	private const EXPORTER_ID = 'affiliate-showcase-exporter';
	private const EXPORTER_NAME = 'Affiliate Product Showcase Plugin';
	private const ERASER_ID = 'affiliate-showcase-eraser';
	private const ERASER_NAME = 'Affiliate Product Showcase Plugin';

	public function init(): void {
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_eraser' ] );
	}

	/**
	 * Register personal data exporter.
	 *
	 * @param array<int, array<string, mixed>> $exporters Existing exporters.
	 * @return array<int, array<string, mixed>> Updated exporters.
	 */
	public function register_exporter( array $exporters ): array {
		$exporters[ self::EXPORTER_ID ] = [
			'exporter_friendly_name' => self::EXPORTER_NAME,
			'callback'              => [ $this, 'export_data' ],
		];

		return $exporters;
	}

	/**
	 * Register personal data eraser.
	 *
	 * @param array<int, array<string, mixed>> $erasers Existing erasers.
	 * @return array<int, array<string, mixed>> Updated erasers.
	 */
	public function register_eraser( array $erasers ): array {
		$erasers[ self::ERASER_ID ] = [
			'eraser_friendly_name' => self::ERASER_NAME,
			'callback'             => [ $this, 'erase_data' ],
		];

		return $erasers;
	}

	/**
	 * Export personal data for a user.
	 *
	 * @param string $email_address User email address.
	 * @param int    $page Page number for pagination.
	 * @return array<string, mixed> Export data.
	 */
	public function export_data( string $email_address, int $page ): array {
		$user = get_user_by( 'email', $email_address );
		
		if ( ! $user ) {
			return [
				'done' => true,
				'data' => [],
			];
		}

		$data_to_export = [];

		// Export analytics data if stored
		$analytics = $this->get_user_analytics( $user->ID );
		if ( ! empty( $analytics ) ) {
			$data_to_export[] = [
				'group_id'    => 'affiliate-showcase-analytics',
				'group_label' => __( 'Affiliate Analytics', 'affiliate-product-showcase' ),
				'item_id'     => 'analytics-' . $user->ID,
				'data'        => $analytics,
			];
		}

		return [
			'done' => true,
			'data' => $data_to_export,
		];
	}

	/**
	 * Erase personal data for a user.
	 *
	 * @param string $email_address User email address.
	 * @param int    $page Page number for pagination.
	 * @return array<string, mixed> Erasure result.
	 */
	public function erase_data( string $email_address, int $page ): array {
		$user = get_user_by( 'email', $email_address );
		
		if ( ! $user ) {
			return [
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => [],
				'done'          => true,
			];
		}

		$items_removed = false;
		$messages = [];

		// Erase analytics data
		$erased = $this->erase_user_analytics( $user->ID );
		if ( $erased ) {
			$items_removed = true;
			$messages[] = __( 'Affiliate analytics data removed.', 'affiliate-product-showcase' );
		}

		return [
			'items_removed'  => $items_removed,
			'items_retained' => ! $items_removed,
			'messages'       => $messages,
			'done'          => true,
		];
	}

	/**
	 * Get analytics data for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array<int, array<string, string>> Analytics data.
	 */
	private function get_user_analytics( int $user_id ): array {
		$analytics_data = [];

		// Get analytics from post meta if any user-specific tracking exists
		$args = [
			'post_type'      => 'aps_analytics',
			'posts_per_page' => -1,
			'author'         => $user_id,
			'post_status'    => 'any',
		];

		$query = new \WP_Query( $args );
		
		foreach ( $query->posts as $post ) {
			$analytics_data[] = [
				'name'  => __( 'Analytics Record', 'affiliate-product-showcase' ),
				'value' => $post->post_title,
			];
		}

		return $analytics_data;
	}

	/**
	 * Erase analytics data for a user.
	 *
	 * @param int $user_id User ID.
	 * @return bool Whether data was erased.
	 */
	private function erase_user_analytics( int $user_id ): bool {
		$args = [
			'post_type'      => 'aps_analytics',
			'posts_per_page' => -1,
			'author'         => $user_id,
			'post_status'    => 'any',
		];

		$query = new \WP_Query( $args );
		$erased = false;

		foreach ( $query->posts as $post ) {
			$deleted = wp_delete_post( $post->ID, true );
			if ( $deleted ) {
				$erased = true;
			}
		}

		return $erased;
	}
}
