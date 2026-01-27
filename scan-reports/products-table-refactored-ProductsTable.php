<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Plugin\Constants;

/**
 * Products List Table
 *
 * Extends WordPress WP_List_Table to display products with custom columns.
 * Provides native pagination, sorting, and bulk actions.
 *
 * This is SINGLE source of truth for column rendering in true hybrid approach.
 * Custom UI is rendered by ProductTableUI, column rendering is handled here.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
class ProductsTable extends \WP_List_Table {

	/**
	 * Product repository
	 *
	 * @var ProductRepository
	 */
	private ProductRepository $repository;

	/**
	 * Currency symbols configuration
	 *
	 * @var array
	 */
	private const CURRENCY_SYMBOLS = [
		'USD' => '$',
		'EUR' => '€',
		'GBP' => '£',
		'JPY' => '¥',
	];

	/**
	 * Constructor
	 *
	 * @param ProductRepository $repository Product repository instance
	 */
	public function __construct( ProductRepository $repository ) {
		$this->repository = $repository;

		// Set screen options
		parent::__construct( [
			'singular' => 'product',
			'plural'   => 'products',
			'ajax'     => true,
		] );
	}

	/**
	 * Get table columns
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return [
			'cb'        => '<input type="checkbox" />',
			'id'        => __( '#', 'affiliate-product-showcase' ),
			'logo'      => __( 'Logo', 'affiliate-product-showcase' ),
			'title'     => __( 'Product', 'affiliate-product-showcase' ),
			'category'  => __( 'Category', 'affiliate-product-showcase' ),
			'tags'      => __( 'Tags', 'affiliate-product-showcase' ),
			'ribbon'    => __( 'Ribbon', 'affiliate-product-showcase' ),
			'featured'  => __( 'Featured', 'affiliate-product-showcase' ),
			'price'     => __( 'Price', 'affiliate-product-showcase' ),
			'status'    => __( 'Status', 'affiliate-product-showcase' ),
		];
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array {
		return [
			'title'    => [ 'title', true ],
			'price'    => [ 'price', true ],
			'status'   => [ 'status', true ],
			'featured' => [ 'featured', true ],
		];
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions(): array {
		return [
			'publish'           => __( 'Publish', 'affiliate-product-showcase' ),
			'move_to_draft'     => __( 'Move to Draft', 'affiliate-product-showcase' ),
			'set_in_stock'      => __( 'Set In Stock', 'affiliate-product-showcase' ),
			'set_out_of_stock'  => __( 'Set Out of Stock', 'affiliate-product-showcase' ),
			'set_featured'      => __( 'Set Featured', 'affiliate-product-showcase' ),
			'unset_featured'    => __( 'Unset Featured', 'affiliate-product-showcase' ),
			'reset_clicks'      => __( 'Reset Clicks', 'affiliate-product-showcase' ),
			'export_csv'        => __( 'Export to CSV', 'affiliate-product-showcase' ),
		];
	}

	/**
	 * Column: Checkbox
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="post[]" value="%d" />',
			(int) $item->ID
		);
	}

	/**
	 * Column: ID
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_id( $item ): string {
		return (string) (int) $item->ID;
	}

	/**
	 * Column: Logo
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_logo( $item ): string {
		$logo_url = (string) get_post_meta( $item->ID, 'aps_product_logo', true );

		if ( empty( $logo_url ) ) {
			$placeholder = strtoupper( mb_substr( (string) $item->post_title, 0, 1 ) );
			return sprintf( '<span class="aps-product-logo-placeholder">%s</span>', esc_html( $placeholder ?: '?' ) );
		}

		return sprintf(
			'<img class="aps-product-logo" src="%s" alt="%s" loading="lazy" />',
			esc_url( $logo_url ),
			esc_attr( (string) $item->post_title )
		);
	}

	/**
	 * Column: Title
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_title( $item ): string {
		$edit_url = admin_url( 'admin.php?page=affiliate-manager-add-product&post=' . $item->ID );
		$title = (string) $item->post_title;
		$post_type = get_post_type_object( 'aps_product' );
		$can_edit_post = $post_type ? current_user_can( $post_type->cap->edit_post, $item->ID ) : false;
		$can_delete_post = $post_type ? current_user_can( $post_type->cap->delete_post, $item->ID ) : false;

		$actions = [];

		if ( $can_edit_post ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $edit_url ),
				__( 'Edit', 'affiliate-product-showcase' )
			);
		}

		$post_status = get_post_status( $item->ID );
		$actions = array_merge( $actions, $this->get_post_actions( $item->ID, $title, $post_status, $can_delete_post ) );

		return sprintf(
			'<div class="aps-product-cell"><strong><a href="%s">%s</a></strong><div class="aps-product-sub">%s</div>%s</div>',
			esc_url( $edit_url ),
			esc_html( $title ),
			esc_html( sprintf( __( 'ID #%d', 'affiliate-product-showcase' ), (int) $item->ID ) ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Column: Category
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_category( $item ): string {
		return $this->render_taxonomy_column( $item->ID, Constants::TAX_CATEGORY, 'aps-product-category', 'category-id', 'category' );
	}

	/**
	 * Column: Tags
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_tags( $item ): string {
		return $this->render_taxonomy_column( $item->ID, Constants::TAX_TAG, 'aps-product-tag', 'tag-id', 'tags' );
	}

	/**
	 * Column: Ribbon
	 *
	 * TRUE HYBRID: Retrieves ribbon from taxonomy relationship only.
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_ribbon( $item ): string {
		$terms = get_the_terms( $item->ID, Constants::TAX_RIBBON );
		
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return sprintf( '<span data-field="ribbon" data-product-id="%d">—</span>', (int) $item->ID );
		}

		$badges = array_map( static function( $term ) {
			return sprintf(
				'<span class="aps-product-badge" data-ribbon-id="%s">%s</span>',
				esc_attr( (string) $term->term_id ),
				esc_html( $term->name )
			);
		}, $terms );

		return sprintf( '<div data-field="ribbon" data-product-id="%d">%s</div>', (int) $item->ID, implode( ' ', $badges ) );
	}

	/**
	 * Column: Featured
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_featured( $item ): string {
		$is_featured = (bool) $this->get_meta_with_fallback( $item->ID, 'aps_featured' );

		if ( $is_featured ) {
			return '<span class="aps-product-featured dashicons dashicons-star-filled" aria-label="' . esc_attr__( 'Featured', 'affiliate-product-showcase' ) . '"></span>';
		}

		return '—';
	}

	/**
	 * Column: Price
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_price( $item ): string {
		$price = $this->get_meta_with_fallback( $item->ID, 'aps_price' );
		$currency = $this->get_meta_with_fallback( $item->ID, 'aps_currency' );
		$original_price = $this->get_meta_with_fallback( $item->ID, 'aps_original_price' );
		$symbol = self::CURRENCY_SYMBOLS[ $currency ] ?? $currency;

		if ( empty( $price ) ) {
			return sprintf( '<span data-field="price" data-product-id="%d">—</span>', (int) $item->ID );
		}

		$output = sprintf(
			'<div data-field="price" data-product-id="%d" data-currency="%s" data-original-price="%s" data-price="%s">',
			(int) $item->ID,
			esc_attr( $currency ),
			esc_attr( (string) $original_price ),
			esc_attr( (string) $price )
		);

		$output .= $this->render_price_with_discount( (float) $price, (float) $original_price, $symbol );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Column: Status
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_status( $item ): string {
		$status = (string) get_post_status( $item->ID );
		$label = strtoupper( $status );
		$class = 'aps-product-status';

		switch ( $status ) {
			case 'publish':
				$class .= ' aps-product-status-published';
				$label = 'PUBLISHED';
				break;
			case 'draft':
				$class .= ' aps-product-status-draft';
				$label = 'DRAFT';
				break;
			case 'trash':
				$class .= ' aps-product-status-trash';
				$label = 'TRASH';
				break;
			case 'pending':
			default:
				$class .= ' aps-product-status-pending';
				$label = strtoupper( $status );
				break;
		}

		return sprintf(
			'<div data-field="status" data-product-id="%d" data-status="%s"><span class="%s">%s</span></div>',
			(int) $item->ID,
			esc_attr( $status ),
			esc_attr( $class ),
			esc_html( $label )
		);
	}

	/**
	 * Default column handler
	 *
	 * @param array  $item
	 * @param string $column_name
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return apply_filters( "manage_aps_product_posts_custom_column_{$column_name}", '', $item->ID );
	}

	/**
	 * No items text
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No products found.', 'affiliate-product-showcase' );
	}

	/**
	 * Override views to prevent WP status links from appearing
	 *
	 * This removes the default WordPress status views (All, Published, Drafts, etc.)
	 * from appearing at the top and bottom of the table.
	 * Custom status counts are rendered in ProductTableUI instead.
	 *
	 * @return array|false Return false to disable views
	 */
	public function views(): array|false {
		return false;
	}

	/**
	 * Hidden columns
	 *
	 * @return array
	 */
	public function get_hidden_columns(): array {
		return [];
	}

	/**
	 * Prepare items for display
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$this->_column_headers = [ $this->get_columns(), $this->get_hidden_columns(), $this->get_sortable_columns() ];

		// Get pagination settings
		$per_page = $this->get_items_per_page( 'products_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		// Build query args
		$args = $this->build_query_args( $per_page, $offset );

		// Get products
		$query = new \WP_Query( $args );
		$this->items = $query->posts;

		// Set pagination
		$total_items = $query->found_posts;
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}

	// ============================================================
	// HELPER METHODS
	// ============================================================

	/**
	 * Get post meta with fallback to prefixed version
	 *
	 * @param int    $post_id Post ID
	 * @param string $meta_key Meta key (without prefix)
	 * @return mixed Meta value
	 */
	private function get_meta_with_fallback( int $post_id, string $meta_key ) {
		$value = get_post_meta( $post_id, $meta_key, true );
		
		if ( '' === (string) $value ) {
			$value = get_post_meta( $post_id, '_' . $meta_key, true );
		}
		
		return $value;
	}

	/**
	 * Render taxonomy column with badges
	 *
	 * @param int    $post_id      Post ID
	 * @param string $taxonomy     Taxonomy name
	 * @param string $badge_class   Badge CSS class
	 * @param string $data_attr    Data attribute name
	 * @param string $field_name    Field name for data attribute
	 * @return string Rendered badges HTML
	 */
	private function render_taxonomy_column( int $post_id, string $taxonomy, string $badge_class, string $data_attr, string $field_name ): string {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return sprintf( '<span data-field="%s" data-product-id="%d">—</span>', $field_name, $post_id );
		}

		$badges = array_map( static function( $term ) use ( $badge_class, $data_attr ) {
			return sprintf(
				'<span class="%s" data-%s="%s">%s <span aria-hidden="true">×</span></span>',
				esc_attr( $badge_class ),
				esc_attr( $data_attr ),
				esc_attr( (string) $term->term_id ),
				esc_html( $term->name )
			);
		}, $terms );

		return sprintf( '<div data-field="%s" data-product-id="%d">%s</div>', $field_name, $post_id, implode( ' ', $badges ) );
	}

	/**
	 * Render price with optional discount
	 *
	 * @param float  $price          Current price
	 * @param float  $original_price Original price
	 * @param string $symbol         Currency symbol
	 * @return string Rendered price HTML
	 */
	private function render_price_with_discount( float $price, float $original_price, string $symbol ): string {
		$output = sprintf(
			'<span class="aps-product-price">%s%s</span>',
			esc_html( $symbol ),
			esc_html( number_format_i18n( $price, 2 ) )
		);

		if ( ! empty( $original_price ) && $original_price > $price ) {
			$discount = (int) round( ( ( $original_price - $price ) / $original_price ) * 100 );
			$output .= sprintf(
				'<span class="aps-product-price-original">%s%s</span><span class="aps-product-price-discount">%d%% OFF</span>',
				esc_html( $symbol ),
				esc_html( number_format_i18n( $original_price, 2 ) ),
				esc_html( $discount )
			);
		}

		return $output;
	}

	/**
	 * Get post actions based on post status
	 *
	 * @param int    $post_id        Post ID
	 * @param string $title          Post title
	 * @param string $post_status    Post status
	 * @param bool   $can_delete_post Whether user can delete post
	 * @return array Post actions
	 */
	private function get_post_actions( int $post_id, string $title, string $post_status, bool $can_delete_post ): array {
		$actions = [];

		if ( 'trash' === $post_status && $can_delete_post ) {
			$actions['untrash'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( sprintf( 'post.php?post=%d&action=untrash', $post_id ) ), 'untrash-post_' . $post_id ) ),
				esc_attr( sprintf( __( 'Restore "%s" from trash', 'affiliate-product-showcase' ), $title ) ),
				__( 'Restore', 'affiliate-product-showcase' )
			);
			
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( admin_url( sprintf( 'post.php?post=%d&action=delete', $post_id ) ), 'delete-post_' . $post_id ) ),
				esc_attr( sprintf( __( 'Delete "%s" permanently', 'affiliate-product-showcase' ), $title ) ),
				__( 'Delete Permanently', 'affiliate-product-showcase' )
			);
		} elseif ( $can_delete_post ) {
			$actions['trash'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( get_delete_post_link( $post_id ) ),
				esc_attr( sprintf( __( 'Move "%s" to trash', 'affiliate-product-showcase' ), $title ) ),
				__( 'Trash', 'affiliate-product-showcase' )
			);
		}

		return $actions;
	}

	/**
	 * Build query arguments for WP_Query
	 *
	 * @param int $per_page Items per page
	 * @param int $offset   Offset for pagination
	 * @return array Query arguments
	 */
	private function build_query_args( int $per_page, int $offset ): array {
		$filters = $this->get_filter_values();
		
		$args = [
			'post_type'      => 'aps_product',
			'post_status'    => $filters['post_status'],
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'orderby'        => $filters['orderby'],
			'order'          => $filters['order'],
		];

		// Add search
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		// Add tax query
		$tax_query = $this->build_tax_query( $filters );
		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		// Add meta query for featured filter
		if ( $filters['featured'] ) {
			$args['meta_query'] = [
				[
					'key'     => 'aps_featured',
					'value'   => '1',
					'compare' => '=',
				],
			];
		}

		return $args;
	}

	/**
	 * Get filter values from $_GET
	 *
	 * @return array Filter values
	 */
	private function get_filter_values(): array {
		$post_status = isset( $_GET['post_status'] ) ? sanitize_key( (string) $_GET['post_status'] ) : '';
		$statuses_default = 'trash' === $post_status ? [ 'trash' ] : [ 'publish', 'draft' ];

		return [
			'search'      => isset( $_GET['aps_search'] ) ? sanitize_text_field( wp_unslash( $_GET['aps_search'] ) ) : '',
			'category'    => isset( $_GET['aps_category_filter'] ) ? (int) $_GET['aps_category_filter'] : 0,
			'tag'         => isset( $_GET['aps_tag_filter'] ) ? (int) $_GET['aps_tag_filter'] : 0,
			'featured'    => isset( $_GET['featured_filter'] ) ? ( '1' === (string) $_GET['featured_filter'] ) : false,
			'order'       => isset( $_GET['order'] ) ? sanitize_key( (string) $_GET['order'] ) : 'desc',
			'orderby'     => isset( $_GET['orderby'] ) ? sanitize_key( (string) $_GET['orderby'] ) : 'date',
			'post_status' => $post_status ? $post_status : $statuses_default,
		];
	}

	/**
	 * Build taxonomy query from filters
	 *
	 * @param array $filters Filter values
	 * @return array Tax query
	 */
	private function build_tax_query( array $filters ): array {
		$tax_query = [];

		if ( $filters['category'] > 0 ) {
			$tax_query[] = [
				'taxonomy' => Constants::TAX_CATEGORY,
				'terms'    => $filters['category'],
			];
		}

		if ( $filters['tag'] > 0 ) {
			$tax_query[] = [
				'taxonomy' => Constants::TAX_TAG,
				'terms'    => $filters['tag'],
			];
		}

		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
		}

		return $tax_query;
	}
}