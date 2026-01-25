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
			'ajax'     => false,
		] );
	}

	/**
	 * Get table columns
	 *
	 * @return array
	 */
	public function get_columns(): array {
		$columns = [
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

		return $columns;
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array {
		$sortable = [
			'title'   => [ 'title', true ],
			'price'   => [ 'price', true ],
			'status'  => [ 'status', true ],
			'featured' => [ 'featured', true ],
		];

		return $sortable;
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions(): array {
		$actions = [
			'set_in_stock'    => __( 'Set In Stock', 'affiliate-product-showcase' ),
			'set_out_of_stock' => __( 'Set Out of Stock', 'affiliate-product-showcase' ),
			'set_featured'     => __( 'Set Featured', 'affiliate-product-showcase' ),
			'unset_featured'   => __( 'Unset Featured', 'affiliate-product-showcase' ),
			'reset_clicks'     => __( 'Reset Clicks', 'affiliate-product-showcase' ),
			'export_csv'       => __( 'Export to CSV', 'affiliate-product-showcase' ),
		];

		return $actions;
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
		$edit_url = get_edit_post_link( $item->ID );
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

		// Add Restore or Trash/Delete Permanently action based on post status
		$post_status = get_post_status( $item->ID );
		
		if ( 'trash' === $post_status ) {
			// Show Restore and Delete Permanently for trashed items
			if ( $can_delete_post ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url( wp_nonce_url( admin_url( sprintf( 'post.php?post=%d&action=untrash', $item->ID ) ), 'untrash-post_' . $item->ID ) ),
					esc_attr( sprintf( __( 'Restore "%s" from trash', 'affiliate-product-showcase' ), $title ) ),
					__( 'Restore', 'affiliate-product-showcase' )
				);
				
				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
					esc_url( wp_nonce_url( admin_url( sprintf( 'post.php?post=%d&action=delete', $item->ID ) ), 'delete-post_' . $item->ID ) ),
					esc_attr( sprintf( __( 'Delete "%s" permanently', 'affiliate-product-showcase' ), $title ) ),
					__( 'Delete Permanently', 'affiliate-product-showcase' )
				);
			}
		} else {
			// Show Trash for non-trashed items
			if ( $can_delete_post ) {
				$actions['trash'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url( get_delete_post_link( $item->ID ) ),
					esc_attr( sprintf( __( 'Move "%s" to trash', 'affiliate-product-showcase' ), $title ) ),
					__( 'Trash', 'affiliate-product-showcase' )
				);
			}
		}

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
		$categories = get_the_terms( $item->ID, Constants::TAX_CATEGORY );

		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			return '—';
		}

		$badges = array_map( static function( $category ) {
			return sprintf(
				'<span class="aps-product-category">%s <span aria-hidden="true">×</span></span>',
				esc_html( $category->name )
			);
		}, $categories );

		return implode( ' ', $badges );
	}

	/**
	 * Column: Tags
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_tags( $item ): string {
		$tags = get_the_terms( $item->ID, Constants::TAX_TAG );

		if ( empty( $tags ) || is_wp_error( $tags ) ) {
			return '—';
		}

		$badges = array_map( static function( $tag ) {
			return sprintf(
				'<span class="aps-product-tag">%s <span aria-hidden="true">×</span></span>',
				esc_html( $tag->name )
			);
		}, $tags );

		return implode( ' ', $badges );
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
		$ribbons = get_the_terms( $item->ID, Constants::TAX_RIBBON );
		
		if ( empty( $ribbons ) || is_wp_error( $ribbons ) ) {
			return '—';
		}

		$badges = array_map( static function( $ribbon ) {
			return sprintf( '<span class="aps-product-badge">%s</span>', esc_html( $ribbon->name ) );
		}, $ribbons );

		return implode( ' ', $badges );
	}

	/**
	 * Column: Featured
	 *
	 * @param \WP_Post $item
	 * @return string
	 */
	public function column_featured( $item ): string {
		$is_featured = (bool) get_post_meta( $item->ID, 'aps_featured', true );
		if ( ! $is_featured ) {
			$is_featured = (bool) get_post_meta( $item->ID, '_aps_featured', true );
		}

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
		$price = get_post_meta( $item->ID, 'aps_price', true );
		if ( '' === (string) $price ) {
			$price = get_post_meta( $item->ID, '_aps_price', true );
		}
		$currency = get_post_meta( $item->ID, 'aps_currency', true );
		if ( '' === (string) $currency ) {
			$currency = get_post_meta( $item->ID, '_aps_currency', true );
		}
		$original_price = get_post_meta( $item->ID, 'aps_original_price', true );
		if ( '' === (string) $original_price ) {
			$original_price = get_post_meta( $item->ID, '_aps_original_price', true );
		}
		$currency_symbols = [
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
		];
		$symbol = $currency_symbols[ $currency ] ?? $currency;

		if ( empty( $price ) ) {
			return '—';
		}

		$output = sprintf(
			'<span class="aps-product-price">%s%s</span>',
			esc_html( $symbol ),
			esc_html( number_format_i18n( (float) $price, 2 ) )
		);

		if ( ! empty( $original_price ) && (float) $original_price > (float) $price ) {
			$discount = (int) round( ( ( (float) $original_price - (float) $price ) / (float) $original_price ) * 100 );
			$output .= sprintf(
				'<span class="aps-product-price-original">%s%s</span><span class="aps-product-price-discount">%d%% OFF</span>',
				esc_html( $symbol ),
				esc_html( number_format_i18n( (float) $original_price, 2 ) ),
				esc_html( $discount )
			);
		}

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

		return sprintf( '<span class="%s">%s</span>', esc_attr( $class ), esc_html( $label ) );
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

		// Get filter values
		$search = isset( $_GET['aps_search'] ) ? sanitize_text_field( wp_unslash( $_GET['aps_search'] ) ) : '';
		$category = isset( $_GET['aps_category_filter'] ) ? intval( $_GET['aps_category_filter'] ) : 0;
		$tag = isset( $_GET['aps_tag_filter'] ) ? intval( $_GET['aps_tag_filter'] ) : 0;
		$featured = isset( $_GET['featured_filter'] ) ? ( '1' === (string) $_GET['featured_filter'] ) : false;
		$order = isset( $_GET['order'] ) ? sanitize_key( (string) $_GET['order'] ) : 'desc';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( (string) $_GET['orderby'] ) : 'date';
		$post_status = isset( $_GET['post_status'] ) ? sanitize_key( (string) $_GET['post_status'] ) : '';
		$statuses_default = [ 'publish', 'draft' ];
		if ( 'trash' === $post_status ) {
			$statuses_default = [ 'trash' ];
		}

		// Build query args
		$args = [
			'post_type'      => 'aps_product',
			'post_status'    => $post_status ? $post_status : $statuses_default,
			'posts_per_page' => $per_page,
			'offset'         => $offset,
			'orderby'        => $orderby,
			'order'          => $order,
		];

		// Add search
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		// Build tax_query for category and tag filters
		$tax_query = [];

		// Add category filter
		if ( $category > 0 ) {
			$tax_query[] = [
				'taxonomy' => Constants::TAX_CATEGORY,
				'terms'    => $category,
			];
		}

		// Add tag filter
		if ( $tag > 0 ) {
			$tax_query[] = [
				'taxonomy' => Constants::TAX_TAG,
				'terms'    => $tag,
			];
		}

		// Apply tax_query if we have filters
		if ( ! empty( $tax_query ) ) {
			// Set relation to AND so products must match both category and tag if both are selected
			$tax_query['relation'] = 'AND';
			$args['tax_query'] = $tax_query;
		}

		// Add featured filter
		if ( $featured ) {
			$args['meta_query'] = [
				[
					'key'     => 'aps_featured',
					'value'   => '1',
					'compare' => '=',
				],
			];
		}

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

	// Intentionally not rendering WP-style views here.
	// Status counts are rendered in ProductTableUI to match custom design.
}