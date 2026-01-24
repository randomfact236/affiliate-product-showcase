<?php
/**
 * Category Table
 *
 * Displays categories in a WordPress-like table with:
 * - Bulk actions
 * - Search and filtering
 * - Pagination
 * - Row actions (edit, delete, view)
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

/**
 * Category Table
 *
 * Displays categories in a WordPress-like table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class CategoryTable {
	/**
	 * Category repository
	 *
	 * @var CategoryRepository
	 * @since 1.0.0
	 */
	private CategoryRepository $repository;

	/**
	 * Category factory
	 *
	 * @var CategoryFactory
	 * @since 1.0.0
	 */
	private CategoryFactory $factory;

	/**
	 * Current page
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private int $page = 1;

	/**
	 * Items per page
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private int $per_page = 20;

	/**
	 * Search term
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private string $search = '';

	/**
	 * Sort order
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private string $orderby = 'name';

	/**
	 * Sort direction
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private string $order = 'ASC';

	/**
	 * Constructor
	 *
	 * @param CategoryRepository $repository Category repository
	 * @param CategoryFactory $factory Category factory
	 * @since 1.0.0
	 */
	public function __construct( CategoryRepository $repository, CategoryFactory $factory ) {
		$this->repository = $repository;
		$this->factory = $factory;
	}

	/**
	 * Initialize category table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init(): void {
		// Handle bulk actions
		$this->handle_bulk_actions();
	}

	/**
	 * Handle bulk actions
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function handle_bulk_actions(): void {
		if ( ! isset( $_POST['aps_category_bulk_action'] ) || 
		     ! isset( $_POST['aps_category_bulk_nonce'] ) ) {
			return;
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['aps_category_bulk_nonce'], 'aps_category_bulk' ) ) {
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

		$action = sanitize_text_field( $_POST['aps_category_bulk_action'] );
		$categories = isset( $_POST['category_ids'] ) 
			? array_map( 'intval', $_POST['category_ids'] ) 
			: [];

		if ( empty( $categories ) ) {
			$this->add_admin_notice(
				__( 'No categories selected.', 'affiliate-product-showcase' ),
				'warning'
			);
			return;
		}

		try {
			$processed = 0;

			foreach ( $categories as $category_id ) {
				switch ( $action ) {
					case 'delete':
						$this->repository->delete( $category_id );
						$processed++;
						break;
					case 'delete_permanently':
						$this->repository->delete_permanently( $category_id );
						$processed++;
						break;
					case 'toggle_featured':
						$category = $this->repository->find( $category_id );
						if ( $category ) {
							$updated = new Category(
								$category->id,
								$category->name,
								$category->slug,
								$category->description,
								$category->parent_id,
								! $category->featured,
								$category->image_url,
								$category->sort_order
							);
							$this->repository->update( $updated );
							$processed++;
						}
						break;
				}
			}

			$this->add_admin_notice(
				sprintf(
					__( '%d categories processed successfully.', 'affiliate-product-showcase' ),
					$processed
				),
				'success'
			);
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
	 * Get page parameters
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function get_page_params(): void {
		$this->page      = isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1;
		$this->search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$this->orderby   = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'name';
		$this->order     = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'ASC';
	}

	/**
	 * Render category table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render(): void {
		$this->get_page_params();

		$categories = $this->repository->all();
		$total      = $this->repository->count();

		// Apply search filter
		if ( ! empty( $this->search ) ) {
			$categories = $this->repository->search( $this->search );
			$total = count( $categories );
		}

		// Pagination
		$offset     = ( $this->page - 1 ) * $this->per_page;
		$categories = array_slice( $categories, $offset, $this->per_page );

		include AFFILIATE_PRODUCT_SHOWCASE_DIR . 'templates/admin/categories-table.php';
	}

	/**
	 * Get category count for pagination
	 *
	 * @return int Total number of categories
	 * @since 1.0.0
	 */
	public function get_total_items(): int {
		$total = $this->repository->count();

		if ( ! empty( $this->search ) ) {
			$total = count( $this->repository->search( $this->search ) );
		}

		return $total;
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
}