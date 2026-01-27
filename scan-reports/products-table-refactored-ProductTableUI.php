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
	 * UI Configuration
	 *
	 * @var array
	 */
	private const ACTION_BUTTONS = [
		[
			'type'  => 'link',
			'url'   => 'add_product_url',
			'class' => 'aps-btn-primary',
			'icon'  => 'dashicons-plus',
			'label' => 'Add New Product',
			'js'    => null,
		],
		[
			'type'  => 'link',
			'url'   => 'trash_url',
			'class' => 'aps-btn-secondary',
			'icon'  => 'dashicons-trash',
			'label' => 'Trash',
			'js'    => null,
		],
		[
			'type'  => 'button',
			'class' => 'aps-btn-secondary',
			'icon'  => 'dashicons-download',
			'label' => 'Import',
			'js'    => 'apsImportProducts',
		],
		[
			'type'  => 'button',
			'class' => 'aps-btn-secondary',
			'icon'  => 'dashicons-upload',
			'label' => 'Export',
			'js'    => 'apsExportProducts',
		],
		[
			'type'  => 'button',
			'class' => 'aps-btn-secondary',
			'icon'  => 'dashicons-admin-links',
			'label' => 'Check Links',
			'js'    => 'apsCheckProductLinks',
		],
	];

	/**
	 * Status configurations
	 *
	 * @var array
	 */
	private const STATUSES = [
		'all'      => 'All',
		'publish'  => 'Published',
		'draft'    => 'Draft',
		'trash'    => 'Trash',
	];

	/**
	 * Filter configurations
	 *
	 * @var array
	 */
	private const FILTERS = [
		'bulk_action' => [
			'type'    => 'select',
			'name'    => 'aps_bulk_action',
			'id'      => 'aps_bulk_action',
			'label'   => 'Select action',
			'options' => [
				''                 => '',
				'move_to_draft'    => 'Move to Draft',
				'publish'          => 'Publish',
				'move_to_trash'    => 'Move to Trash',
				'restore'          => 'Restore from Trash',
				'delete_permanent' => 'Delete Permanently',
			],
			'has_apply_button' => true,
		],
		'search' => [
			'type'  => 'text',
			'name'  => 'aps_search',
			'id'    => 'aps_search_products',
			'label' => 'Search products',
			'placeholder' => 'Search products...',
		],
		'sort_order' => [
			'type'    => 'select',
			'name'    => 'order',
			'id'      => 'aps_sort_order',
			'label'   => 'Sort',
			'options' => [
				'desc' => 'Latest',
				'asc'  => 'Oldest',
			],
		],
	];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueStyles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueScripts' ] );
	}

	/**
	 * Render product table page
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! $this->isProductsPage() ) {
			return;
		}

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
		$urls = $this->get_urls();
		$counts = $this->get_status_counts();
		$current_status = $this->get_current_status();

		?>
		<div class="aps-products-page" id="aps-products-page">

			<div class="aps-product-table-actions">
				<?php $this->render_header( $urls ); ?>
				<?php $this->render_action_buttons( $urls ); ?>
				<?php $this->render_status_counts( $urls, $counts, $current_status ); ?>
			</div>

			<?php $this->render_filters_form( $urls ); ?>

		</div>
		<?php
	}

	/**
	 * Render header section
	 *
	 * @param array $urls URLs for the page
	 * @return void
	 */
	private function render_header( array $urls ): void {
		?>
		<h1 class="aps-page-title">
			<?php echo esc_html( __( 'Products', 'affiliate-product-showcase' ) ); ?>
		</h1>

		<p class="aps-page-description">
			<?php echo esc_html( __( 'Quick overview of your catalog with actions, filters, and bulk selection.', 'affiliate-product-showcase' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Render action buttons
	 *
	 * @param array $urls URLs for buttons
	 * @return void
	 */
	private function render_action_buttons( array $urls ): void {
		?>
		<div class="aps-action-buttons">
		<?php
		foreach ( self::ACTION_BUTTONS as $button ) {
			$this->render_single_button( $button, $urls );
		}
		?>
		</div>
		<?php
	}

	/**
	 * Render single action button
	 *
	 * @param array $button Button configuration
	 * @param array $urls   URLs for the page
	 * @return void
	 */
	private function render_single_button( array $button, array $urls ): void {
		if ( 'link' === $button['type'] ) {
			printf(
				'<a href="%s" class="aps-btn %s"><span class="dashicons %s"></span>%s</a>',
				esc_url( $urls[ $button['url'] ] ),
				esc_attr( $button['class'] ),
				esc_attr( $button['icon'] ),
				esc_html( __( $button['label'], 'affiliate-product-showcase' ) )
			);
		} elseif ( 'button' === $button['type'] && $button['js'] ) {
			printf(
				'<button type="button" class="aps-btn %s" onclick="if (typeof %s === \'function\') { %s(); }"><span class="dashicons %s"></span>%s</button>',
				esc_attr( $button['class'] ),
				esc_js( $button['js'] ),
				esc_js( $button['js'] ),
				esc_attr( $button['icon'] ),
				esc_html( __( $button['label'], 'affiliate-product-showcase' ) )
			);
		}
	}

	/**
	 * Render status counts
	 *
	 * @param array $urls          URLs for the page
	 * @param array $counts        Status counts
	 * @param string $current_status Current status
	 * @return void
	 */
	private function render_status_counts( array $urls, array $counts, string $current_status ): void {
		?>
		<div class="aps-product-counts">
		<?php
		foreach ( self::STATUSES as $status => $label ) {
			$url = 'all' === $status ? $urls['base_url'] : add_query_arg( 'post_status', $status, $urls['base_url'] );
			$active = $status === $current_status ? 'active' : '';
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="aps-count-item <?php echo esc_attr( $active ); ?>" data-status="<?php echo esc_attr( $status ); ?>">
				<span class="aps-count-number"><?php echo esc_html( (string) $counts[ $status ] ); ?></span>
				<span class="aps-count-label"><?php echo esc_html( __( $label, 'affiliate-product-showcase' ) ); ?></span>
			</a>
			<?php
		}
		?>
		</div>
		<?php
	}

	/**
	 * Render filters form
	 *
	 * @param array $urls URLs for the page
	 * @return void
	 */
	private function render_filters_form( array $urls ): void {
		?>
		<form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="aps-product-filters">
			<input type="hidden" name="post_type" value="aps_product" />

			<?php foreach ( self::FILTERS as $filter ) : ?>
				<?php $this->render_filter( $filter ); ?>
			<?php endforeach; ?>

			<?php $this->render_taxonomy_filters(); ?>
			<?php $this->render_featured_filter(); ?>
			<?php $this->render_filter_actions( $urls ); ?>
		</form>
		<?php
	}

	/**
	 * Render single filter
	 *
	 * @param array $filter Filter configuration
	 * @return void
	 */
	private function render_filter( array $filter ): void {
		?>
		<div class="aps-filter-group">
			<label class="screen-reader-text" for="<?php echo esc_attr( $filter['id'] ); ?>">
				<?php echo esc_html( __( $filter['label'], 'affiliate-product-showcase' ) ); ?>
			</label>
		<?php

		if ( 'select' === $filter['type'] ) {
			$this->render_select_filter( $filter );
		} elseif ( 'text' === $filter['type'] ) {
			$this->render_text_filter( $filter );
		}

		?>
		</div>
		<?php
	}

	/**
	 * Render select filter
	 *
	 * @param array $filter Filter configuration
	 * @return void
	 */
	private function render_select_filter( array $filter ): void {
		$value = isset( $_GET[ $filter['name'] ] ) ? (string) $_GET[ $filter['name'] ] : '';
		?>
		<select name="<?php echo esc_attr( $filter['name'] ); ?>" id="<?php echo esc_attr( $filter['id'] ); ?>" class="aps-filter-select">
		<?php foreach ( $filter['options'] as $option_value => $option_label ) : ?>
			<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, (string) $option_value ); ?>>
				<?php echo esc_html( __( $option_label, 'affiliate-product-showcase' ) ); ?>
			</option>
		<?php endforeach; ?>
		</select>
		<?php

		if ( ! empty( $filter['has_apply_button'] ) ) {
			?>
			<button type="button" id="aps_action_apply" class="aps-btn aps-btn-apply aps-btn-apply-hidden">
				<?php echo esc_html( __( 'Apply', 'affiliate-product-showcase' ) ); ?>
			</button>
			<?php
		}
	}

	/**
	 * Render text filter
	 *
	 * @param array $filter Filter configuration
	 * @return void
	 */
	private function render_text_filter( array $filter ): void {
		$value = isset( $_GET[ $filter['name'] ] ) ? wp_unslash( $_GET[ $filter['name'] ] ) : '';
		?>
		<input type="text" name="<?php echo esc_attr( $filter['name'] ); ?>" id="<?php echo esc_attr( $filter['id'] ); ?>" 
		       class="aps-filter-input" placeholder="<?php echo esc_attr( __( $filter['placeholder'], 'affiliate-product-showcase' ) ); ?>" 
		       value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Render taxonomy filters (category and tag)
	 *
	 * @return void
	 */
	private function render_taxonomy_filters(): void {
		$taxonomies = [
			[
				'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_CATEGORY,
				'name'     => 'aps_category_filter',
				'id'       => 'aps_category_filter',
				'label'    => 'All Categories',
			],
			[
				'taxonomy' => \AffiliateProductShowcase\Plugin\Constants::TAX_TAG,
				'name'     => 'aps_tag_filter',
				'id'       => 'aps_tag_filter',
				'label'    => 'All Tags',
			],
		];

		foreach ( $taxonomies as $tax_config ) {
			$this->render_taxonomy_filter( $tax_config );
		}
	}

	/**
	 * Render single taxonomy filter
	 *
	 * @param array $config Taxonomy configuration
	 * @return void
	 */
	private function render_taxonomy_filter( array $config ): void {
		$terms = get_terms( [
			'taxonomy'   => $config['taxonomy'],
			'hide_empty' => false,
		] );

		$selected = isset( $_GET[ $config['name'] ] ) ? (int) $_GET[ $config['name'] ] : 0;
		?>
		<div class="aps-filter-group">
			<label class="screen-reader-text" for="<?php echo esc_attr( $config['id'] ); ?>">
				<?php echo esc_html( __( $config['label'], 'affiliate-product-showcase' ) ); ?>
			</label>
			<select name="<?php echo esc_attr( $config['name'] ); ?>" id="<?php echo esc_attr( $config['id'] ); ?>" class="aps-filter-select">
				<option value="0"><?php echo esc_html( __( $config['label'], 'affiliate-product-showcase' ) ); ?></option>
				<?php if ( ! is_wp_error( $terms ) ) : ?>
					<?php foreach ( $terms as $term ) : ?>
						<option value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php selected( $selected, (int) $term->term_id ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Render featured filter toggle
	 *
	 * @return void
	 */
	private function render_featured_filter(): void {
		$checked = isset( $_GET['featured_filter'] ) && '1' === (string) $_GET['featured_filter'];
		?>
		<div class="aps-filter-group aps-filter-toggle">
			<label class="aps-toggle-label">
				<input type="checkbox" name="featured_filter" id="aps_show_featured" value="1" <?php checked( $checked ); ?> />
				<span class="aps-toggle-slider"></span>
				<span class="aps-toggle-text"><?php echo esc_html( __( 'Show Featured', 'affiliate-product-showcase' ) ); ?></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Render filter actions (apply and clear)
	 *
	 * @param array $urls URLs for the page
	 * @return void
	 */
	private function render_filter_actions( array $urls ): void {
		?>
		<div class="aps-filter-group">
			<button type="submit" class="aps-btn aps-btn-apply">
				<?php echo esc_html( __( 'Apply', 'affiliate-product-showcase' ) ); ?>
			</button>
		</div>

		<div class="aps-filter-group">
			<a href="<?php echo esc_url( $urls['base_url'] ); ?>" class="aps-btn aps-btn-clear">
				<?php echo esc_html( __( 'Clear filters', 'affiliate-product-showcase' ) ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Render WP_List_Table instance
	 *
	 * Delegates column rendering to ProductsTable which extends WP_List_Table.
	 * ProductsTable is single source of truth for column display.
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

	// ============================================================
	// HELPER METHODS
	// ============================================================

	/**
	 * Get URLs for the page
	 *
	 * @return array URLs
	 */
	private function get_urls(): array {
		return [
			'add_product_url' => admin_url( 'edit.php?post_type=aps_product&page=add-product' ),
			'trash_url'       => admin_url( 'edit.php?post_type=aps_product&post_status=trash' ),
			'base_url'        => admin_url( 'edit.php?post_type=aps_product' ),
		];
	}

	/**
	 * Get status counts
	 *
	 * @return array Status counts
	 */
	private function get_status_counts(): array {
		$counts = wp_count_posts( 'aps_product' );
		
		return [
			'all'     => ( isset( $counts->publish ) ? (int) $counts->publish : 0 ) +
			            ( isset( $counts->draft ) ? (int) $counts->draft : 0 ) +
			            ( isset( $counts->trash ) ? (int) $counts->trash : 0 ),
			'publish' => isset( $counts->publish ) ? (int) $counts->publish : 0,
			'draft'   => isset( $counts->draft ) ? (int) $counts->draft : 0,
			'trash'   => isset( $counts->trash ) ? (int) $counts->trash : 0,
		];
	}

	/**
	 * Get current status from query params
	 *
	 * @return string Current status
	 */
	private function get_current_status(): string {
		$status = isset( $_GET['post_status'] ) ? sanitize_key( (string) $_GET['post_status'] ) : '';
		return '' === $status ? 'all' : $status;
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

		wp_enqueue_style(
			'aps-admin-table',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/admin-table.css',
			[],
			\AffiliateProductShowcase\Plugin\Constants::VERSION
		);

		wp_enqueue_style(
			'aps-product-table-ui',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/product-table-ui.css',
			['aps-admin-table'],
			\AffiliateProductShowcase\Plugin\Constants::VERSION
		);

		wp_enqueue_style(
			'affiliate-product-showcase-products-table-inline-edit',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/css/products-table-inline-edit.css',
			['aps-product-table-ui'],
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

		wp_localize_script( 'aps-product-table-ui', 'apsProductTableUI', $this->get_ui_script_data() );

		wp_enqueue_script(
			'affiliate-product-showcase-products-table-inline-edit',
			\AffiliateProductShowcase\Plugin\Constants::dirUrl() . 'assets/js/products-table-inline-edit.js',
			['jquery'],
			\AffiliateProductShowcase\Plugin\Constants::VERSION,
			true
		);

		wp_localize_script( 'affiliate-product-showcase-products-table-inline-edit', 'apsInlineEditData', $this->get_inline_edit_script_data() );
	}

	/**
	 * Get UI script data
	 *
	 * @return array Script data
	 */
	private function get_ui_script_data(): array {
		return [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'aps_product_table_ui' ),
			'enableAjax' => false,
			'strings' => [
				'confirmBulkUpload' => __( 'Are you sure you want to bulk upload products?', 'affiliate-product-showcase' ),
				'confirmBulk'       => __( 'Are you sure you want to apply this action to selected products?', 'affiliate-product-showcase' ),
				'confirmImport'      => __( 'Open import page to import products?', 'affiliate-product-showcase' ),
				'confirmExport'      => __( 'Export products to CSV?', 'affiliate-product-showcase' ),
				'selectAction'      => __( 'Please select at least one product.', 'affiliate-product-showcase' ),
				'confirmCheckLinks'  => __( 'Are you sure you want to check all product links?', 'affiliate-product-showcase' ),
				'processing'        => __( 'Processing...', 'affiliate-product-showcase' ),
				'done'              => __( 'Done!', 'affiliate-product-showcase' ),
				'noProducts'        => __( 'No products found.', 'affiliate-product-showcase' ),
			],
		];
	}

	/**
	 * Get inline edit script data
	 *
	 * @return array Script data
	 */
	private function get_inline_edit_script_data(): array {
		return [
			'restUrl' => rest_url( 'affiliate-product-showcase/v1/' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'strings' => [
				'saving'         => __( 'Saving...', 'affiliate-product-showcase' ),
				'saved'          => __( 'Saved!', 'affiliate-product-showcase' ),
				'error'          => __( 'Error saving. Please try again.', 'affiliate-product-showcase' ),
				'addNew'         => __( 'Add New', 'affiliate-product-showcase' ),
				'selectCategory' => __( 'Select Category', 'affiliate-product-showcase' ),
				'selectTags'     => __( 'Select Tags', 'affiliate-product-showcase' ),
				'selectRibbon'   => __( 'Select Ribbon', 'affiliate-product-showcase' ),
				'none'           => __( 'None', 'affiliate-product-showcase' ),
				'published'      => __( 'Published', 'affiliate-product-showcase' ),
				'draft'          => __( 'Draft', 'affiliate-product-showcase' ),
			],
		];
	}
}