<?php

namespace AffiliateProductShowcase\Formatters;

final class PriceFormatter {
	public function format( float $amount, string $currency = 'USD' ): string {
		$symbol = $this->symbol_for_currency( $currency );
		return sprintf( '%s%s', $symbol, number_format_i18n( $amount, 2 ) );
	}

	private function symbol_for_currency( string $currency ): string {
		return match ( strtoupper( $currency ) ) {
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			default => strtoupper( $currency ) . ' ',
		};
	}
}
