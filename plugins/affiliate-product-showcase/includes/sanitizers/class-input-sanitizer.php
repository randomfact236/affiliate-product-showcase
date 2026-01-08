<?php

namespace AffiliateProductShowcase\Sanitizers;

class InputSanitizer {
	public function text( $value ) {
		return sanitize_text_field( (string) $value );
	}
}
