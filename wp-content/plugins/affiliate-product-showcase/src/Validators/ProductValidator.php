<?php

namespace AffiliateProductShowcase\Validators;

use AffiliateProductShowcase\Abstracts\AbstractValidator;
use AffiliateProductShowcase\Exceptions\PluginException;

final class ProductValidator extends AbstractValidator {
	public function validate( array $data ): array {
		$errors = [];

		if ( empty( $data['title'] ) ) {
			$errors[] = 'Title is required.';
		}

		if ( empty( $data['affiliate_url'] ) ) {
			$errors[] = 'Affiliate URL is required.';
		}

		if ( ! empty( $errors ) ) {
			throw new PluginException( implode( ' ', $errors ) );
		}

		return $data;
	}
}
