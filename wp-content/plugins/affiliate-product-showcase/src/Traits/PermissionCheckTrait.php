<?php
/**
 * Permission Check Trait
 *
 * Provides reusable permission check methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Traits;

/**
 * Permission Check Trait
 *
 * Provides reusable permission check methods.
 *
 * @package AffiliateProductShowcase\Traits
 * @since 2.1.0
 */
trait PermissionCheckTrait {
	/**
	 * Check if user can manage categories
	 *
	 * @return bool True if has permission
	 * @since 2.1.0
	 */
	protected function can_manage_categories(): bool {
		return current_user_can('manage_categories');
	}
	
	/**
	 * Require manage categories permission
	 *
	 * Dies with error if user lacks permission.
	 *
	 * @return void
	 * @since 2.1.0
	 */
	protected function require_manage_categories(): void {
		if (!$this->can_manage_categories()) {
			wp_die(esc_html__('You do not have permission to perform this action.', 'affiliate-product-showcase'));
		}
	}
	
	/**
	 * Check if user can edit categories
	 *
	 * @return bool True if has permission
	 * @since 2.1.0
	 */
	protected function can_edit_categories(): bool {
		return current_user_can('edit_categories');
	}
}
