<?php
/**
 * Status Constants
 *
 * Defines constant values for status fields.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

/**
 * Status Constants
 *
 * Defines constant values for status fields across the plugin.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 2.1.0
 */
final class StatusConstants {
	/**
	 * Published status
	 *
	 * @var string
	 */
	public const PUBLISHED = 'published';
	
	/**
	 * Draft status
	 *
	 * @var string
	 */
	public const DRAFT = 'draft';
	
	/**
	 * Trashed status
	 *
	 * @var string
	 */
	public const TRASHED = 'trashed';
	
	/**
	 * All status (for filtering)
	 *
	 * @var string
	 */
	public const ALL = 'all';
	
	/**
	 * All valid status values
	 *
	 * @var array<string>
	 */
	public const VALID_STATUSES = [
		self::PUBLISHED,
		self::DRAFT,
		self::TRASHED,
	];
	
	/**
	 * Get all valid statuses
	 *
	 * @return array<string> Valid status values
	 * @since 2.1.0
	 */
	public static function getAll(): array {
		return self::VALID_STATUSES;
	}
}
