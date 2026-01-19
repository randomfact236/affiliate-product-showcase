<?php

namespace AffiliateProductShowcase\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		// Validate category IDs
		if ( isset( $data['category_ids'] ) ) {
			if ( ! is_array( $data['category_ids'] ) ) {
				$errors[] = 'Category IDs must be an array.';
			} else {
				foreach ( $data['category_ids'] as $category_id ) {
					if ( ! is_numeric( $category_id ) || $category_id <= 0 ) {
						$errors[] = 'Category IDs must be positive integers.';
						break;
					}
					
					// Verify category term exists
					$term = get_term( (int) $category_id, \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY );
					if ( ! $term || is_wp_error( $term ) ) {
						$errors[] = sprintf( 'Category ID %d does not exist.', (int) $category_id );
						break;
					}
				}
			}
		}

		if ( ! empty( $errors ) ) {
			throw new PluginException( implode( ' ', $errors ) );
		}

		return $data;
	}
}
