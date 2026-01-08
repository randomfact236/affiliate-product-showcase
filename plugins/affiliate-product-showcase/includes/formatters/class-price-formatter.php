<?php

namespace AffiliateProductShowcase\Formatters;

class PriceFormatter {
	public function format( $value ) {
		$number = (float) $value;
		return number_format_i18n( $number, 2 );
	}
}
