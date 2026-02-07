<?php
/**
 * Status Validator
 *
 * Validates and normalizes status values for taxonomies.
 *
 * @package AffiliateProductShowcase\Validators
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Validators;

/**
 * Status Validator
 *
 * Validates and normalizes status values for taxonomies.
 *
 * @package AffiliateProductShowcase\Validators
 * @since 2.1.0
 */
final class StatusValidator {
	public const PUBLISHED = 'published';
	public const DRAFT = 'draft';
	public const TRASHED = 'trashed';
	public const ALL = 'all';
	
	private const VALID_STATUSES = [self::PUBLISHED, self::DRAFT, self::TRASHED];
	
	/**
	 * Validate status value
	 *
	 * @param string $status Status to validate
	 * @return string Validated status (defaults to 'published')
	 * @since 2.1.0
	 */
	public static function validate(string $status): string {
		return in_array($status, self::VALID_STATUSES, true) ? $status : self::PUBLISHED;
	}
	
	/**
	 * Check if status is valid
	 *
	 * @param string $status Status to check
	 * @return bool True if valid
	 * @since 2.1.0
	 */
	public static function isValid(string $status): bool {
		return in_array($status, self::VALID_STATUSES, true);
	}
	
	/**
	 * Get all valid statuses
	 *
	 * @return array<string> Valid status values
	 * @since 2.1.0
	 */
	public static function getValidStatuses(): array {
		return self::VALID_STATUSES;
	}
}
