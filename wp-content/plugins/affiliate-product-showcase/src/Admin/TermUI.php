<?php
/**
 * Advanced Term UI
 *
 * Provides advanced admin UI functionality for terms including:
 * - Quick edit support
 * - Bulk edit support
 * - Autocomplete functionality
 * - Enhanced filtering and search
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

use AffiliateProductShowcase\Plugin\Constants;

/**
 * Advanced Term UI
 *
 * Handles advanced term admin UI features.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class TermUI {
	/**
	 * Boot service
	 *
	 * Registers all hooks and scripts for advanced term UI.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		// Add quick edit support
		add_filter( 'get_terms_args', [ $this, 'enable_quick_edit' ], 10, 2 );
		add_filter( 'post_row_actions', [ $this, 'add_quick_edit_link' ], 10, 2 );

		// Add bulk edit support
		add_action( 'admin_head', [ $this, 'enqueue_admin_styles' ] );
		add_action( 'admin_footer', [ $this, 'enqueue_admin_scripts' ] );
		add_filter( 'bulk_actions-' . Constants::TAX_CATEGORY, [ $this, 'add_category_bulk_actions' ], 10, 2 );
		add_filter( 'bulk_actions-' . Constants::TAX_TAG, [ $this, 'add_tag_bulk_actions' ], 10, 2 );
		add_filter( 'bulk_actions-' . Constants::TAX_RIBBON, [ $this, 'add_ribbon_bulk_actions' ], 10, 2 );

		// Add autocomplete support
		add_action( 'wp_ajax_' . Constants::TAX_CATEGORY . '_autocomplete', [ $this, 'category_autocomplete' ] );
		add_action( 'wp_ajax_' . Constants::TAX_TAG . '_autocomplete', [ $this, 'tag_autocomplete' ] );

		// Add search/filter functionality
		add_action( 'restrict_manage_posts', [ $this, 'enhance_term_list_screen' ], 10, 1 );
		add_filter( 'manage_' . Constants::TAX_CATEGORY . '_columns', [ $this, 'add_category_list_columns' ] );
		add_filter( 'manage_' . Constants::TAX_TAG . '_columns', [ $this, 'add_tag_list_columns' ] );
	}

	/**
	 * Enable quick edit for terms
	 *
	 * @param array<string, mixed> $args Query arguments
	 * @param array<string, mixed> $taxonomies Taxonomies
	 * @return array<string, mixed> Modified arguments
	 * @since 1.0.0
	 *
	 * @filter get_terms_args
	 */
	public function enable_quick_edit( array $args, array $taxonomies ): array {
		// Enable quick edit by adding custom fields
		$args['fields'] = $this->get_quick_edit_fields();
		
		return $args;
	}

	/**
	 * Add quick edit link to term list
	 *
	 * @param array<string, mixed> $actions Row actions
	 * @param \WP_Term $term Term object
	 * @return array<string, mixed> Modified actions
	 * @since 1.0.0
	 *
	 * @filter post_row_actions
	 */
	public function add_quick_edit_link( array $actions, \WP_Term $term ): array {
		if ( ! current_user_can( 'edit_term', $term->taxonomy ) ) {
			return $actions;
		}

		$edit_link = $this->get_quick_edit_link( $term );
		$actions['quickedit'] = sprintf(
			'<a href="%s" class="button button-small" aria-label="%s">%s</a>',
			esc_url( $edit_link ),
			esc_attr__( 'Quick Edit', Constants::TEXTDOMAIN ),
			esc_attr__( 'Quick edit "%s"', Constants::TEXTDOMAIN ),
			esc_html__( 'Quick Edit', Constants::TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * Get quick edit link for term
	 *
	 * @param \WP_Term $term Term object
	 * @return string Admin URL
	 * @since 1.0.0
	 */
	private function get_quick_edit_link( \WP_Term $term ): string {
		return add_query_arg(
			'taxonomy',
			$term->taxonomy,
			'term_id',
			$term->term_id,
			'action',
			'quickedit'
		);
	}

	/**
	 * Get quick edit form fields
	 *
	 * @return array<string, mixed> Form field definitions
	 * @since 1.0.0
	 */
	private function get_quick_edit_fields(): array {
		return [
			'slug' => [
				'label' => esc_html__( 'Slug', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'The URL-friendly slug', Constants::TEXTDOMAIN ),
			],
			'name' => [
				'label' => esc_html__( 'Name', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'The term name', Constants::TEXTDOMAIN ),
			],
			'description' => [
				'label' => esc_html__( 'Description', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'The term description', Constants::TEXTDOMAIN ),
			],
			'parent' => [
				'label' => esc_html__( 'Parent', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'The parent term', Constants::TEXTDOMAIN ),
			],
			'category_order' => [
				'label' => esc_html__( 'Display Order', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Order in category lists (lower numbers appear first)', Constants::TEXTDOMAIN ),
			],
			'category_featured' => [
				'label' => esc_html__( 'Featured', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Show in featured product sections and widgets', Constants::TEXTDOMAIN ),
			],
			'category_hide_from_menu' => [
				'label' => esc_html__( 'Hide from Menu', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Hide this category from navigation menus', Constants::TEXTDOMAIN ),
			],
			'category_icon' => [
				'label' => esc_html__( 'Icon', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'SVG icon or emoji character', Constants::TEXTDOMAIN ),
			],
			'category_image' => [
				'label' => esc_html__( 'Image', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Category thumbnail image', Constants::TEXTDOMAIN ),
			],
			'category_color' => [
				'label' => esc_html__( 'Color', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Hex color code for visual identification', Constants::TEXTDOMAIN ),
			],
			'tag_color' => [
				'label' => esc_html__( 'Color', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Hex color code for visual identification', Constants::TEXTDOMAIN ),
			],
			'tag_icon' => [
				'label' => esc_html__( 'Icon', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'SVG icon or emoji character', Constants::TEXTDOMAIN ),
			],
			'tag_featured' => [
				'label' => esc_html__( 'Featured', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Show in featured product sections and widgets', Constants::TEXTDOMAIN ),
			],
			'ribbon_text' => [
				'label' => esc_html__( 'Text', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Display text (e.g., Best Seller, New, Sale)', Constants::TEXTDOMAIN ),
			],
			'ribbon_bg_color' => [
				'label' => esc_html__( 'Background Color', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Background color hex code for ribbon badge', Constants::TEXTDOMAIN ),
			],
			'ribbon_text_color' => [
				'label' => esc_html__( 'Text Color', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Text color hex code for ribbon', Constants::TEXTDOMAIN ),
			],
			'ribbon_position' => [
				'label' => esc_html__( 'Position', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Position on product image', Constants::TEXTDOMAIN ),
			],
			'ribbon_style' => [
				'label' => esc_html__( 'Style', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Visual style of ribbon (badge, corner, banner, diagonal)', Constants::TEXTDOMAIN ),
			],
			'ribbon_icon' => [
				'label' => esc_html__( 'Icon', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'SVG icon path or Heroicon name', Constants::TEXTDOMAIN ),
			],
			'ribbon_priority' => [
				'label' => esc_html__( 'Priority', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Display priority (higher numbers appear first)', Constants::TEXTDOMAIN ),
			],
			'ribbon_start_date' => [
				'label' => esc_html__( 'Start Date', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Scheduled start date for ribbon display', Constants::TEXTDOMAIN ),
			],
			'ribbon_expiration_date' => [
				'label' => esc_html__( 'Expiration Date', Constants::TEXTDOMAIN ),
				'description' => esc_html__( 'Expiration date for scheduled ribbons', Constants::TEXTDOMAIN ),
			],
		];
	}

	/**
	 * Add bulk edit actions for categories
	 *
	 * @param array<string, mixed> $actions Bulk actions
	 * @param \WP_Term $term Term object
	 * @return array<string, mixed> Modified actions
	 * @since 1.0.0
	 *
	 * @filter bulk_actions-product_category
	 */
	public function add_category_bulk_actions( array $actions, \WP_Term $term ): array {
		if ( ! current_user_can( 'manage_categories', $term->taxonomy ) ) {
			return $actions;
		}

		$actions['set_featured'] = sprintf(
			'<a href="#" class="aps-bulk-action aps-bulk-set-featured" data-term-id="%d">%s</a>',
			$term->term_id,
			esc_html__( 'Set Featured', Constants::TEXTDOMAIN )
		);

		$actions['set_order'] = sprintf(
			'<a href="#" class="aps-bulk-action aps-bulk-set-order" data-term-id="%d">%s</a>',
			$term->term_id,
			esc_html__( 'Set Order', Constants::TEXTDOMAIN )
		);

		$actions['hide_menu'] = sprintf(
			'<a href="#" class="aps-bulk-action aps-bulk-toggle-menu" data-term-id="%d">%s</a>',
			$term->term_id,
			get_term_meta( $term->term_id, 'category_hide_from_menu', true ) ? esc_html__( 'Show in Menu', Constants::TEXTDOMAIN ) : esc_html__( 'Hide from Menu', Constants::TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * Add bulk edit actions for tags
	 *
	 * @param array<string, mixed> $actions Bulk actions
	 * @param \WP_Term $term Term object
	 * @return array<string, mixed> Modified actions
	 * @since 1.0.0
	 *
	 * @filter bulk_actions-product_tag
	 */
	public function add_tag_bulk_actions( array $actions, \WP_Term $term ): array {
		if ( ! current_user_can( 'manage_post_tags', $term->taxonomy ) ) {
			return $actions;
		}

		$actions['set_featured'] = sprintf(
			'<a href="#" class="aps-bulk-action aps-bulk-set-featured" data-term-id="%d">%s</a>',
			$term->term_id,
			esc_html__( 'Set Featured', Constants::TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * Add bulk edit actions for ribbons
	 *
	 * @param array<string, mixed> $actions Bulk actions
	 * @param \WP_Term $term Term object
	 * @return array<string, mixed> Modified actions
	 * @since 1.0.0
	 *
	 * @filter bulk_actions-product_ribbon
	 */
	public function add_ribbon_bulk_actions( array $actions, \WP_Term $term ): array {
		if ( ! current_user_can( 'manage_' . Constants::TAX_RIBBON, $term->taxonomy ) ) {
			return $actions;
		}

		$actions['set_priority'] = sprintf(
			'<a href="#" class="aps-bulk-action aps-bulk-set-priority" data-term-id="%d">%s</a>',
			$term->term_id,
			esc_html__( 'Set Priority', Constants::TEXTDOMAIN )
		);

		return $actions;
	}

	/**
	 * Category autocomplete handler
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action wp_ajax_product_category_autocomplete
	 */
	public function category_autocomplete(): void {
		check_ajax_referer();
		check_ajax_nonce( 'aps_category_autocomplete_nonce', 'nonce' );

		$search = sanitize_text_field( $_POST['search'] ?? '' );
		if ( empty( $search ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Search term is required', Constants::TEXTDOMAIN ) ] );
			return;
		}

		$args = [
			'taxonomy'   => Constants::TAX_CATEGORY,
			'name__like' => $search . '*',
			'hide_empty' => false,
			'number'     => 10,
			'fields'     => 'name,slug',
		];

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			wp_send_json_error( [ 'message' => $terms->get_error_message() ] );
			return;
		}

		$results = [];
		foreach ( $terms as $term ) {
			$results[] = [
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			];
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Tag autocomplete handler
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action wp_ajax_product_tag_autocomplete
	 */
	public function tag_autocomplete(): void {
		check_ajax_referer();
		check_ajax_nonce( 'aps_tag_autocomplete_nonce', 'nonce' );

		$search = sanitize_text_field( $_POST['search'] ?? '' );
		if ( empty( $search ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Search term is required', Constants::TEXTDOMAIN ) ] );
			return;
		}

		$args = [
			'taxonomy' => Constants::TAX_TAG,
			'name__like' => $search . '*',
			'hide_empty' => false,
			'number'     => 10,
			'fields'     => 'name,slug',
		];

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			wp_send_json_error( [ 'message' => $terms->get_error_message() ] );
			return;
		}

		$results = [];
		foreach ( $terms as $term ) {
			// Get tag color for icon
			$color = get_term_meta( $term->term_id, 'tag_color', true );
			$icon = get_term_meta( $term->term_id, 'tag_icon', true );

			$results[] = [
				'id'    => $term->term_id,
				'name'  => $term->name,
				'slug'  => $term->slug,
				'color' => $color,
				'icon'  => $icon,
			];
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Add custom columns to category list
	 *
	 * @param array<string, mixed> $columns List columns
	 * @param string $screen Screen ID
	 * @return array<string, mixed> Modified columns
	 * @since 1.0.0
	 *
	 * @filter manage_product_category_columns
	 */
	public function add_category_list_columns( array $columns, string $screen ): array {
		if ( 'edit-' . Constants::TAX_CATEGORY !== $screen ) {
			return $columns;
		}

		$columns['category_order'] = [
			'title'     => esc_html__( 'Order', Constants::TEXTDOMAIN ),
			'sortable'   => true,
			'width'      => '50',
		];

		$columns['category_featured'] = [
			'title'     => esc_html__( 'Featured', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		$columns['category_image'] = [
			'title'     => esc_html__( 'Image', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		$columns['category_color'] = [
			'title'     => esc_html__( 'Color', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		return $columns;
	}

	/**
	 * Add custom columns to tag list
	 *
	 * @param array<string, mixed> $columns List columns
	 * @param string $screen Screen ID
	 * @return array<string, mixed> Modified columns
	 * @since 1.0.0
	 *
	 * @filter manage_product_tag_columns
	 */
	public function add_tag_list_columns( array $columns, string $screen ): array {
		if ( 'edit-' . Constants::TAX_TAG !== $screen ) {
			return $columns;
		}

		$columns['tag_color'] = [
			'title'     => esc_html__( 'Color', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		$columns['tag_icon'] = [
			'title'     => esc_html__( 'Icon', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		$columns['tag_featured'] = [
			'title'     => esc_html__( 'Featured', Constants::TEXTDOMAIN ),
			'sortable'   => false,
			'width'      => '50',
		];

		return $columns;
	}

	/**
	 * Render custom column value
	 *
	 * @param string $column_name Column name
	 * @param int $term_id Term ID
	 * @return string|null Column value
	 * @since 1.0.0
	 *
	 * @action manage_product_category_custom_column
	 * @action manage_product_tag_custom_column
	 */
	public function render_custom_column( string $column_name, int $term_id ): ?string {
		if ( 'category_order' === $column_name ) {
			$order = get_term_meta( $term_id, 'category_order', true );
			return $order ? esc_html( number_format_i18n( $order ) ) : '-';
		}

		if ( 'category_featured' === $column_name ) {
			$featured = (bool) get_term_meta( $term_id, 'category_featured', true );
			return $featured ? esc_html__( 'Yes', Constants::TEXTDOMAIN ) : esc_html__( 'No', Constants::TEXTDOMAIN );
		}

		if ( 'category_image' === $column_name ) {
			$image_id = get_term_meta( $term_id, 'category_image', true );
			if ( $image_id ) {
				$image = wp_get_attachment_image( $image_id, 'thumbnail', [ 'class' => 'aps-term-image-thumbnail' ] );
				return $image;
			}
			return '-';
		}

		if ( 'category_color' === $column_name ) {
			$color = get_term_meta( $term_id, 'category_color', true );
			if ( $color ) {
				$swatch = sprintf( '<span class="aps-color-swatch" style="background-color:%s;" title="%s"></span>',
					esc_attr( $color ),
					esc_attr( $color )
				);
				return $swatch;
			}
			return '-';
		}

		if ( 'tag_color' === $column_name ) {
			$color = get_term_meta( $term_id, 'tag_color', true );
			if ( $color ) {
				$swatch = sprintf( '<span class="aps-color-swatch-circle" style="background-color:%s;" title="%s"></span>',
					esc_attr( $color ),
					esc_attr( $color )
				);
				return $swatch;
			}
			return '-';
		}

		if ( 'tag_icon' === $column_name ) {
			$icon = get_term_meta( $term_id, 'tag_icon', true );
			if ( $icon ) {
				// Check if it's an SVG icon
				if ( strpos( $icon, '<svg' ) !== false || strpos( $icon, '<path' ) !== false ) {
					return sprintf( '<svg class="aps-icon-preview" fill="none" viewBox="0 0 24 24" stroke="currentColor">%s</svg>',
						$icon
					);
				}
				return esc_html( $icon );
			}
			return '-';
		}

		if ( 'tag_featured' === $column_name ) {
			$featured = (bool) get_term_meta( $term_id, 'tag_featured', true );
			return $featured ? esc_html__( 'Yes', Constants::TEXTDOMAIN ) : esc_html__( 'No', Constants::TEXTDOMAIN );
		}

		return null;
	}

	/**
	 * Enhance term list screen
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action restrict_manage_posts
	 */
	public function enhance_term_list_screen(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY, 'edit-' . Constants::TAX_TAG, 'edit-' . Constants::TAX_RIBBON ] ) ) {
			return;
		}

		// Add search box
		add_action( 'restrict_manage_posts', [ $this, 'add_search_box' ], 10, 1 );
		add_action( 'restrict_manage_posts', [ $this, 'add_filter_dropdown' ], 10, 1 );
		add_action( 'restrict_manage_posts', [ $this, 'add_view_options' ], 10, 1 );
	}

	/**
	 * Add search box to term list
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action restrict_manage_posts
	 */
	public function add_search_box(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY, 'edit-' . Constants::TAX_TAG ] ) ) {
			return;
		}

		?>
		<div class="aps-quick-search">
			<label for="aps-term-search"><?php esc_html_e( 'Search terms:', Constants::TEXTDOMAIN ); ?></label>
			<input type="search" 
				   id="aps-term-search" 
				   name="s" 
				   class="regular-text" 
				   value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>">
			<span class="description"><?php esc_html_e( 'Search by name or slug', Constants::TEXTDOMAIN ); ?></span>
		</div>
		<?php
	}

	/**
	 * Add filter dropdown to term list
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action restrict_manage_posts
	 */
	public function add_filter_dropdown(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY ] ) ) {
			return;
		}

		$current_filter = isset( $_GET['aps_filter'] ) ? sanitize_text_field( $_GET['aps_filter'] ) : '';

		?>
		<div class="aps-filter-dropdown">
			<label for="aps-filter"><?php esc_html_e( 'Filter:', Constants::TEXTDOMAIN ); ?></label>
			<select name="aps_filter" id="aps-filter" class="postform">
				<option value=""><?php esc_html_e( 'All terms', Constants::TEXTDOMAIN ); ?></option>
				<option value="featured" <?php selected( 'featured', $current_filter ); ?>><?php esc_html_e( 'Featured only', Constants::TEXTDOMAIN ); ?></option>
				<option value="empty" <?php selected( 'empty', $current_filter ); ?>><?php esc_html_e( 'Empty terms', Constants::TEXTDOMAIN ); ?></option>
				<option value="hide_menu" <?php selected( 'hide_menu', $current_filter ); ?>><?php esc_html_e( 'Hidden from menu', Constants::TEXTDOMAIN ); ?></option>
			</select>
		</div>
		<?php
	}

	/**
	 * Add view options
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action restrict_manage_posts
	 */
	public function add_view_options(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY ] ) ) {
			return;
		}

		?>
		<div class="aps-view-options">
			<input type="checkbox" id="aps-show-images" name="aps_show_images" value="1" <?php checked( 'aps_show_images' ); ?>>
			<label for="aps-show-images"><?php esc_html_e( 'Show images', Constants::TEXTDOMAIN ); ?></label>
			
			<input type="checkbox" id="aps-show-descriptions" name="aps_show_descriptions" value="1" <?php checked( 'aps_show_descriptions' ); ?>>
			<label for="aps-show-descriptions"><?php esc_html_e( 'Show descriptions', Constants::TEXTDOMAIN ); ?></label>
		</div>
		<?php
	}

	/**
	 * Enqueue admin styles
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action admin_head
	 */
	public function enqueue_admin_styles(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY, 'edit-' . Constants::TAX_TAG, 'edit-' . Constants::TAX_RIBBON ] ) ) {
			return;
		}

		wp_enqueue_style(
			'aps-term-ui-admin',
			plugins_url( 'affiliate-product-showcase/assets/css/admin-term-ui.css' ),
			[],
			'1.0.0'
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action admin_footer
	 */
	public function enqueue_admin_scripts(): void {
		$screen = get_current_screen();

		if ( ! in_array( $screen, [ 'edit-' . Constants::TAX_CATEGORY, 'edit-' . Constants::TAX_TAG, 'edit-' . Constants::TAX_RIBBON ] ) ) {
			return;
		}

		// Localize script with AJAX URLs
		wp_enqueue_script(
			'aps-term-ui-admin',
			plugins_url( 'affiliate-product-showcase/assets/js/admin-term-ui.js' ),
			[],
			'1.0.0',
			true
		);

		wp_localize_script( 'aps-term-ui-admin', 'apsTermUI', [
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'aps_term_ui_nonce' ),
			'categorySlug' => Constants::TAX_CATEGORY,
			'tagSlug'       => Constants::TAX_TAG,
			'ribbonSlug'   => Constants::TAX_RIBBON,
		] );
	}
}
