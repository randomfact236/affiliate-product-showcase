<?php
/**
 * Product Validator
 *
 * Validates product data including:
 * - Required fields (title, affiliate_url)
 * - Category IDs validation
 * - Tag IDs validation
 * - Term existence verification
 *
 * @package AffiliateProductShowcase\Validators
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Validators;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Abstracts\AbstractValidator;
use AffiliateProductShowcase\Exceptions\PluginException;

/**
 * Product Validator
 *
 * Validates product data including:
 * - Required fields (title, affiliate_url)
 * - Category IDs validation
 * - Tag IDs validation
 * - Term existence verification
 *
 * @package AffiliateProductShowcase\Validators
 * @since 1.0.0
 * @author Development Team
 */
final class ProductValidator extends AbstractValidator {
	/**
	 * Validate product data
	 *
	 * Validates required fields and taxonomy term IDs.
	 * Throws exception with error messages if validation fails.
	 *
	 * @param array<string, mixed> $data Product data to validate
	 * @return array<string, mixed> Validated product data
	 * @throws PluginException If validation fails
	 * @since 1.0.0
	 */
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

		// Validate tag IDs
		if ( isset( $data['tag_ids'] ) ) {
			if ( ! is_array( $data['tag_ids'] ) ) {
				$errors[] = 'Tag IDs must be an array.';
			} else {
				foreach ( $data['tag_ids'] as $tag_id ) {
					if ( ! is_numeric( $tag_id ) || $tag_id <= 0 ) {
						$errors[] = 'Tag IDs must be positive integers.';
						break;
					}
					
					// Verify tag term exists
					$term = get_term( (int) $tag_id, \AffiliateProductShowcase\Plugin\Constants::TAX_TAG );
					if ( ! $term || is_wp_error( $term ) ) {
						$errors[] = sprintf( 'Tag ID %d does not exist.', (int) $tag_id );
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
