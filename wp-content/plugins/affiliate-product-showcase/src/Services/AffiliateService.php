<?php

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Models\AffiliateLink;
use AffiliateProductShowcase\Repositories\SettingsRepository;

final class AffiliateService {
	private SettingsRepository $settings_repository;

	public function __construct() {
		$this->settings_repository = new SettingsRepository();
	}

	public function build_link( string $url ): AffiliateLink {
		$settings   = $this->settings_repository->get_settings();
		$trackingId = $settings['affiliate_id'] ?? '';

		$finalUrl = $url;
		if ( $trackingId && false === strpos( $url, $trackingId ) ) {
			$separator = false !== strpos( $url, '?' ) ? '&' : '?';
			$finalUrl  = $url . $separator . 'aff_id=' . rawurlencode( $trackingId );
		}

		return new AffiliateLink( $finalUrl, null, $trackingId ?: null );
	}
}
