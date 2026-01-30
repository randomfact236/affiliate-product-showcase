<?php
/**
 * Category Form Handler
 *
 * Handles category form submissions including:
 * - Creating new categories
 * - Updating existing categories
 * - Form validation and sanitization
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Factories\CategoryFactory;
use AffiliateProductShowcase\Models\Category;
use AffiliateProductShowcase\Plugin\Constants;

/**
 * Category Form Handler
 *
 * Handles category form submissions including:
 * - Creating new categories
 * - Updating existing categories
 * - Form validation and sanitization
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class CategoryFormHandler {
	/**
	 * Category repository
	 *
	 * @var CategoryRepository
	 * @since 1.0.0
	 */
	private CategoryRepository $repository;

	/**
	 * Constructor
	 *
	 * @param CategoryRepository $repository Category repository
	 * @since 1.0.0
	 */
	public function __construct( CategoryRepository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Initialize category form hooks
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'handle_form_submission' ] );
		add_action( 'admin_notices', [ $this, 'display_admin_notices' ] );
	}

	/**
	 * Handle category form submission
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function handle_form_submission(): void {
		// Check if this is a category form submission
		if ( ! isset( $_POST['aps_category_form_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['aps_category_form_nonce'], 'aps_category_form' ) ) {
			$this->add_admin_notice(
				__( 'Security check failed. Please try again.', 'affiliate-product-showcase' ),
				'error'
			);
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_categories' ) ) {
			$this->add_admin_notice(
				__( 'You do not have permission to manage categories.', 'affiliate-product-showcase' ),
				'error'
			);
			return;
		}

		// Get form data
		$cat_id      = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$slug        = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$parent_id   = isset( $_POST['parent_id'] ) ? absint( wp_unslash( $_POST['parent_id'] ) ) : 0;
		$featured     = isset( $_POST['featured'] ) && '1' === $_POST['featured'];
		$image_url   = isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '';
		$sort_order  = isset( $_POST['sort_order'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_order'] ) ) : 'date';

		// Validate required fields
		if ( empty( $name ) ) {
			$this->add_admin_notice(
				__( 'Category name is required.', 'affiliate-product-showcase' ),
				'error'
			);
			return;
		}

		try {
			if ( $cat_id > 0 ) {
				// Update existing category
				$category = new Category(
					$cat_id,
					$name,
					$slug,
					$description,
					$parent_id,
					0, // count - will be updated by WordPress
					$featured,
					$image_url,
					$sort_order
				);

				$updated = $this->repository->update( $category );

				$this->add_admin_notice(
					sprintf(
						__( 'Category "%s" updated successfully.', 'affiliate-product-showcase' ),
						esc_html( $name )
					),
					'success'
				);
			} else {
				// Create new category
				$category = new Category(
					0,
					$name,
					$slug,
					$description,
					$parent_id,
					0, // count - will be updated by WordPress
					$featured,
					$image_url,
					$sort_order
				);

				$created = $this->repository->create( $category );

				$this->add_admin_notice(
					sprintf(
						__( 'Category "%s" created successfully.', 'affiliate-product-showcase' ),
						esc_html( $name )
					),
					'success'
				);
			}
		} catch ( \AffiliateProductShowcase\Exceptions\PluginException $e ) {
			$this->add_admin_notice(
				sprintf(
					__( 'Error: %s', 'affiliate-product-showcase' ),
					esc_html( $e->getMessage() )
				),
				'error'
			);
		}
	}

	/**
	 * Add admin notice
	 *
	 * @param string $message Notice message
	 * @param string $type Notice type (success, error, warning, info)
	 * @return void
	 * @since 1.0.0
	 */
	private function add_admin_notice( string $message, string $type = 'info' ): void {
		add_action( 'admin_notices', function () use ( $message, $type ) {
			echo sprintf(
				'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
				esc_attr( $type ),
				wp_kses_post( $message )
			);
		} );
	}

	/**
	 * Display admin notices
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action admin_notices
	 */
	public function display_admin_notices(): void {
		// Notices are added via add_admin_notice() method
	}
}