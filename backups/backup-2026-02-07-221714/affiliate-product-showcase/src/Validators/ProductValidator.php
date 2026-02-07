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
	 * Validate taxonomy term IDs
	 *
	 * Validates that term IDs are positive integers and that the terms exist.
	 *
	 * @param array<int, mixed> $term_ids Array of term IDs to validate
	 * @param string $taxonomy Taxonomy name
	 * @param string $label Human-readable label for error messages
	 * @return array<string> Array of error messages (empty if valid)
	 * @since 1.0.0
	 */
	private function validate_taxonomy_ids( array $term_ids, string $taxonomy, string $label ): array {
		$errors = [];

		if ( ! is_array( $term_ids ) ) {
			$errors[] = sprintf( '%s IDs must be an array.', $label );
			return $errors;
		}

		foreach ( $term_ids as $term_id ) {
			if ( ! is_numeric( $term_id ) || $term_id <= 0 ) {
				$errors[] = sprintf( '%s IDs must be positive integers.', $label );
				break;
			}

			$term = get_term( (int) $term_id, $taxonomy );
			if ( ! $term || is_wp_error( $term ) ) {
				$errors[] = sprintf( '%s ID %d does not exist.', $label, (int) $term_id );
				break;
			}
		}

		return $errors;
	}

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

		// Validate category IDs using helper method
		if ( isset( $data['category_ids'] ) ) {
			$category_errors = $this->validate_taxonomy_ids(
				$data['category_ids'],
				\AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY,
				'Category'
			);
			$errors = array_merge( $errors, $category_errors );
		}

		// Validate tag IDs using helper method
		if ( isset( $data['tag_ids'] ) ) {
			$tag_errors = $this->validate_taxonomy_ids(
				$data['tag_ids'],
				\AffiliateProductShowcase\Plugin\Constants::TAX_TAG,
				'Tag'
			);
			$errors = array_merge( $errors, $tag_errors );
		}

		if ( ! empty( $errors ) ) {
			throw new PluginException( implode( ' ', $errors ) );
		}

		return $data;
	}
}
