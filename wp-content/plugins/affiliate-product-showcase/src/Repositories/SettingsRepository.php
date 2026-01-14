<?php

namespace AffiliateProductShowcase\Repositories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Abstracts\AbstractRepository;
use AffiliateProductShowcase\Plugin\Constants;

final class SettingsRepository extends AbstractRepository {
	private const OPTION_KEY = 'aps_settings';

	public function find( int $id ): ?array {
		return null;
	}

	public function list( array $args = [] ): array {
		return $this->get_settings();
	}

	public function save( object $model ): int {
		return 0;
	}

	public function delete( int $id ): bool {
		return false;
	}

	public function get_settings(): array {
		$defaults = [
			'currency'       => 'USD',
			'affiliate_id'   => '',
			'enable_ratings' => true,
			'enable_cache'   => true,
			'cta_label'      => __( 'View Deal', Constants::TEXTDOMAIN ),
		];

		$settings = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $settings ) ) {
			$settings = [];
		}

		return wp_parse_args( $settings, $defaults );
	}

	public function update_settings( array $settings ): void {
		$sanitized = [
			'currency'       => sanitize_text_field( $settings['currency'] ?? 'USD' ),
			'affiliate_id'   => sanitize_text_field( $settings['affiliate_id'] ?? '' ),
			'enable_ratings' => ! empty( $settings['enable_ratings'] ),
			'enable_cache'   => ! empty( $settings['enable_cache'] ),
			'cta_label'      => sanitize_text_field( $settings['cta_label'] ?? __( 'View Deal', Constants::TEXTDOMAIN ) ),
		];

		update_option( self::OPTION_KEY, $sanitized );
	}
}
