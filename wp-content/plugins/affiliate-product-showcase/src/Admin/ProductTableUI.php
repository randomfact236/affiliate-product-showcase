<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Product Table UI
 *
 * Manages UI elements above to products list table.
 * Renders action buttons, custom filters, and displays ProductsTable.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductTableUI {

	/**
	 * Product table instance
	 *
	 * @var ProductsTable
	 */
	private ProductsTable $product_table;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Enqueue styles and scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueStyles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
	}

	/**
	 * Render product table page
	 *
	 * @return void
	 */
	public function render(): void {
		// Only show on products list page
		if ( ! $this->isProductsPage() ) {
			return;
		}

		// Initialize products table
		$this->product_table = new ProductsTable(
			new \AffiliateProductShowcase\Repositories\ProductRepository()
		);

		$this->product_table->prepare_items();
		$this->renderCustomUI();
		$this->renderTable();
	}

	/**
	 * Check if current page is products list
	 *
	 * @return bool
	 */
	private function isProductsPage(): bool {
		return isset( $_GET['post_type'] ) &&
		       $_GET['post_type'] === 'aps_product' &&
		       ! isset( $_GET['page'] );
	}

	/**
	 * Render custom UI above table
	 *
	 * @return void
	 */
	private function renderCustomUI(): void {
		$add_product_url = admin_url( 'edit.php?post_type=aps_product&page=add-product' );
		$trash_url = admin_url( 'edit.php?post_type=aps_product&post_status=trash' );
		$base_url = admin_url( 'edit.php?post_type=aps_product' );

		$counts = wp_count_posts( 'aps_product' );
		$publish_count = isset( $counts->publish ) ? (int) $counts->publish : 0;
		$draft_count = isset( $counts->draft ) ? (int) $counts->draft : 0;
		$trash_count = isset( $counts->trash ) ? (int) $counts->trash : 0;
		$all_count = $publish_count + $draft_count + $trash_count;

		$current_status = isset( $_GET['post_status'] ) ? sanitize_key( (string) $_GET['post_status'] ) : 'all';
		if ( '' === $current_status ) {
			$current_status = 'all';
		}

		?>
		<div class="aps-products-page" id="aps-products-page">

			<div class="aps-product-table-actions">
				<h1 class="aps-page-title">
					<?php echo esc_html( __( 'Products', 'affiliate-product-showcase' ) ); ?>
				</h1>

				<p class="aps-page-description">
					<?php echo esc_html( __( 'Quick overview of your catalog with actions, filters, and bulk selection.', 'affiliate-product-showcase' ) ); ?>
				</p>

				<div class="aps-action-buttons">
					<a href="<?php echo esc_url( $add_product_url ); ?>" class="aps-btn aps-btn-primary">
						<span class="dashicons dashicons-plus"></span>
						<?php echo esc_html( __( 'Add New Product', 'affiliate-product-showcase' ) ); ?>
					</a>

					<a href="<?php echo esc_url( $trash_url ); ?>" class="aps-btn aps-btn-secondary">
						<span class="dashicons dashicons-trash"></span>
						<?php echo esc_html( __( 'Trash', 'affiliate-product-showcase' ) ); ?>
					</a>

					<button type="button" class="aps-btn aps-btn-secondary" onclick="if (typeof apsImportProducts === 'function') { apsImportProducts(); }">
						<span class="dashicons dashicons-download"></span>
						<?php echo esc_html( __( 'Import', 'affiliate-product-showcase' ) ); ?>
					</button>

					<button type="button" class="aps-btn aps-btn-secondary" onclick="if (typeof apsExportProducts === 'function') { apsExportProducts(); }">
						<span class="dashicons dashicons-upload"></span>
						<?php echo esc_html( __( 'Export', 'affiliate-product-showcase' ) ); ?>
					</button>

					<button type="button" class="aps-btn aps-btn-secondary" onclick="if (typeof apsCheckProductLinks === 'function') { apsCheckProductLinks(); }">
						<span class="dashicons dashicons-admin-links"></span>
						<?php echo esc_html( __( 'Check Links', 'affiliate-product-showcase' ) ); ?>
					</button>
				</div>

				<div class="aps-product-counts">
					<a href="<?php echo esc_url( $base_url ); ?>" class="aps-count-item <?php echo ( 'all' === $current_status ) ? 'active' : ''; ?>" data-status="all">
						<span class="aps-count-number"><?php echo esc_html( (string) $all_count ); ?></span>
						<span class="aps-count-label"><?php echo esc_html( __( 'All', 'affiliate-product-showcase' ) ); ?></span>
					</a>
					<a href="<?php echo esc_url( add_query_arg( 'post_status', 'publish', $base_url ) ); ?>" class="aps-count-item <?php echo ( 'publish' === $current_status ) ? 'active' : ''; ?>" data-status="publish">
						<span class="aps-count-number"><?php echo esc_html( (string) $publish_count ); ?></span>
						<span class="aps-count-label"><?php echo esc_html( __( 'Published', 'affiliate-product-showcase' ) ); ?></span>
					</a>
					<a href="<?php echo esc_url( add_query_arg( 'post_status', 'draft', $base_url ) ); ?>" class="aps-count-item <?php echo ( 'draft' === $current_status ) ? 'active' : ''; ?>" data-status="draft">
						<span class="aps-count-number"><?php echo esc_html( (string) $draft_count ); ?></span>
						<span class="aps-count-label"><?php echo esc_html( __( 'Draft', 'affiliate-product-showcase' ) ); ?></span>
					</a>
					<a href="<?php echo esc_url( add_query_arg( 'post_status', 'trash', $base_url ) ); ?>" class="aps-count-item <?php echo ( 'trash' === $current_status ) ? 'active' : ''; ?>" data-status="trash">
						<span class="aps-count-number"><?php echo esc_html( (string) $trash_count ); ?></span>
						<span class="aps-count-label"><?php echo esc_html( __( 'Trash', 'affiliate-product-showcase' ) ); ?></span>
					</a>
				</div>
			</div>

			<form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="aps-product-filters">
				<input type="hidden" name="post_type" value="aps_product" />

				<div class="aps-filter-group">
					<label class="screen-reader-text" for="aps_bulk_action"><?php echo esc_html( __( 'Select action', 'affiliate-product-showcase' ) ); ?></label>
					<select name="aps_bulk_action" id="aps_bulk_action" class="aps-filter-select">
						<option value=""><?php echo esc_html( __( 'Select action', 'affiliate-product-showcase' ) ); ?></option>
						<option value="move_to_draft"><?php echo esc_html( __( 'Move to Draft', 'affiliate-product-showcase' ) ); ?></option>
						<option value="publish"><?php echo esc_html( __( 'Publish', 'affiliate-product-showcase' ) ); ?></option>
						<option value="move_to_trash"><?php echo esc_html( __( 'Move to Trash', 'affiliate-product-showcase' ) ); ?></option>
						<option value="restore"><?php echo esc_html( __( 'Restore from Trash', 'affiliate-product-showcase' ) ); ?></option>
						<option value="delete_permanent"><?php echo esc_html( __( 'Delete Permanently', 'affiliate-product-showcase' ) ); ?></option>
					</select>
					<button type="button" id="aps_action_apply" class="aps-btn aps-btn-apply" style="display:none; margin-left:8px;"><?php echo esc_html( __( 'Apply', 'affiliate-product-showcase' ) ); ?></button>
				</div>

				<div class="aps-filter-group aps-filter-search">
					<label class="screen-reader-text" for="aps_search_products"><?php echo esc_html( __( 'Search products', 'affiliate-product-showcase' ) ); ?></label>
					<input type="text" name="aps_search" id="aps_search_products" class="aps-filter-input" placeholder="<?php echo esc_attr( __( 'Search products...', 'affiliate-product-showcase' ) ); ?>" value="<?php echo isset( $_GET['aps_search'] ) ? esc_attr( wp_unslash( $_GET['aps_search'] ) ) : ''; ?>" />
				</div>

				<div class="aps-filter-group">
					<label class="screen-reader-text" for="aps_category_filter"><?php echo esc_html( __( 'All Categories', 'affiliate-product-showcase' ) ); ?></label>
					<select name="aps_category_filter" id="aps_category_filter" class="aps-filter-select">
						<option value="0"><?php echo esc_html( __( 'All Categories', 'affiliate-product-showcase' ) ); ?></option>
						<?php
						$categories = get_terms( [
							'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY,
							'hide_empty' => false,
						] );
						if ( ! is_wp_error( $categories ) ) :
							foreach ( $categories as $category ) :
								$selected = isset( $_GET['aps_category_filter'] ) ? (int) $_GET['aps_category_filter'] : 0;
								?>
								<option value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php selected( $selected, (int) $category->term_id ); ?>>
									<?php echo esc_html( $category->name ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="aps-filter-group">
					<label class="screen-reader-text" for="aps_sort_order"><?php echo esc_html( __( 'Sort', 'affiliate-product-showcase' ) ); ?></label>
					<select name="order" id="aps_sort_order" class="aps-filter-select">
						<option value="desc" <?php selected( isset( $_GET['order'] ) ? (string) $_GET['order'] : 'desc', 'desc' ); ?>>
							<?php echo esc_html( __( 'Latest', 'affiliate-product-showcase' ) ); ?>
						</option>
						<option value="asc" <?php selected( isset( $_GET['order'] ) ? (string) $_GET['order'] : 'desc', 'asc' ); ?>>
							<?php echo esc_html( __( 'Oldest', 'affiliate-product-showcase' ) ); ?>
						</option>
					</select>
				</div>

				<div class="aps-filter-group aps-filter-toggle">
					<label class="aps-toggle-label">
						<input type="checkbox" name="featured_filter" id="aps_show_featured" value="1" <?php checked( isset( $_GET['featured_filter'] ) ? (string) $_GET['featured_filter'] : '', '1' ); ?> />
						<span class="aps-toggle-slider"></span>
						<span class="aps-toggle-text"><?php echo esc_html( __( 'Show Featured', 'affiliate-product-showcase' ) ); ?></span>
					</label>
				</div>

				<div class="aps-filter-group">
					<button type="submit" class="aps-btn aps-btn-apply"><?php echo esc_html( __( 'Apply', 'affiliate-product-showcase' ) ); ?></button>
				</div>

				<div class="aps-filter-group">
					<a href="<?php echo esc_url( $base_url ); ?>" class="aps-btn aps-btn-clear"><?php echo esc_html( __( 'Clear filters', 'affiliate-product-showcase' ) ); ?></a>
				</div>
			</form>

		</div>
		<?php
	}

	/**
	 * Render the WP_List_Table instance
	 *
	 * Delegates column rendering to ProductsTable which extends WP_List_Table.
	 * ProductsTable is the single source of truth for column display.
	 *
	 * @return void
	 */
	private function renderTable(): void {
		?>
		<form method="post" class="aps-products-table-form">
			<?php $this->product_table->display(); ?>
		</form>
		<?php
	}

	/**
	 * Enqueue styles
	 *
	 * @param string $hook Current admin hook
	 * @return void
	 */
	public function enqueueStyles( string $hook ): void {
		if ( ! $this->isProductsPage() ) {
			return;
		}

		// Enqueue admin table CSS
		wp_enqueue_style(
			'aps-admin-table',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/admin-table.css',
			[],
			\AffiliateProductShowcase\Plugin\Constants::VERSION
		);

		// Enqueue product table UI CSS
		wp_enqueue_style(
			'aps-product-table-ui',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/product-table-ui.css',
			['aps-admin-table'],
			\AffiliateProductShowcase\Plugin\Constants::VERSION
		);
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook Current admin hook
	 * @return void
	 */
	public function enqueueScripts( string $hook ): void {
		if ( ! $this->isProductsPage() ) {
			return;
		}

		wp_enqueue_script(
			'aps-product-table-ui',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/js/product-table-ui.js',
			['jquery'],
			\AffiliateProductShowcase\Plugin\Constants::VERSION,
			true
		);

		wp_localize_script( 'aps-product-table-ui', 'apsProductTableUI', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'aps_product_table_ui' ),
			'enableAjax' => false,
			'strings' => [
				'confirmBulkUpload' => __( 'Are you sure you want to bulk upload products?', 'affiliate-product-showcase' ),
				'confirmBulk' => __( 'Are you sure you want to apply this action to the selected products?', 'affiliate-product-showcase' ),
				'confirmImport' => __( 'Open import page to import products?', 'affiliate-product-showcase' ),
				'confirmExport' => __( 'Export products to CSV?', 'affiliate-product-showcase' ),
				'selectAction' => __( 'Please select at least one product.', 'affiliate-product-showcase' ),
				'confirmCheckLinks' => __( 'Are you sure you want to check all product links?', 'affiliate-product-showcase' ),
				'processing' => __( 'Processing...', 'affiliate-product-showcase' ),
				'done' => __( 'Done!', 'affiliate-product-showcase' ),
				'noProducts' => __( 'No products found.', 'affiliate-product-showcase' ),
			],
		]);
	}
}
