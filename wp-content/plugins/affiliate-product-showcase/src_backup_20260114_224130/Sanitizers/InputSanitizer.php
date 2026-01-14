<?php

namespace AffiliateProductShowcase\Sanitizers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class InputSanitizer {
	public function bool( $value ): bool {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	public function text( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	public function float( $value ): float {
		return (float) $value;
	}

	public function url( $value ): string {
		return esc_url_raw( (string) $value );
	}
}
