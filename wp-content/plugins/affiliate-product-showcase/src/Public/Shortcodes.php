<?php
/**
 * Shortcode Handler
 *
 * Registers and renders shortcodes for displaying products,
 * categories, tags, and ribbons.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;

/**
 * Shortcode Handler
 *
 * Manages all shortcodes for plugin.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class Shortcodes {
	/**
	 * Product service
	 *
	 * @var ProductService
	 * @since 1.0.0
	 */
	private ProductService $product_service;

	/**
	 * Settings repository
	 *
	 * @var SettingsRepository
	 * @since 1.0.0
	 */
	private SettingsRepository $settings_repository;

	/**
	 * Affiliate service
	 *
	 * @var AffiliateService
	 * @since 1.0.0
	 */
	private AffiliateService $affiliate_service;

	/**
	 * Product card renderer
	 *
	 * @var ProductCardRenderer
	 * @since 1.0.0
	 */
	private ProductCardRenderer $card_renderer;

	/**
	 * Constructor
	 *
	 * @param ProductService $product_service Product service instance
	 * @param SettingsRepository $settings_repository Settings repository instance
	 * @param AffiliateService $affiliate_service Affiliate service instance
	 * @param ProductCardRenderer $card_renderer Product card renderer instance
	 * @since 1.0.0
	 */
	public function __construct(
		ProductService $product_service,
		SettingsRepository $settings_repository,
		AffiliateService $affiliate_service,
		ProductCardRenderer $card_renderer
	) {
		$this->product_service = $product_service;
		$this->settings_repository = $settings_repository;
		$this->affiliate_service = $affiliate_service;
		$this->card_renderer = $card_renderer;
	}

	/**
	 * Register shortcodes
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function register(): void {
		add_shortcode( 'affiliate_products', [ $this, 'render_products' ] );
		add_shortcode( 'affiliate_categories', [ $this, 'render_categories' ] );
		add_shortcode( 'affiliate_tags', [ $this, 'render_tags' ] );
		add_shortcode( 'affiliate_ribbons', [ $this, 'render_ribbons' ] );
		add_shortcode( 'affiliate_category', [ $this, 'render_category' ] );
		add_shortcode( 'affiliate_tag', [ $this, 'render_tag' ] );
		add_shortcode( 'affiliate_ribbon', [ $this, 'render_ribbon' ] );
		
		// Alias shortcodes
		add_shortcode( 'affiliate_featured', [ $this, 'render_featured_products' ] );
		add_shortcode( 'affiliate_trending', [ $this, 'render_trending_products' ] );
		add_shortcode( 'affiliate_on_sale', [ $this, 'render_on_sale_products' ] );
	}

	/**
	 * Render products grid
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_products
	 */
	public function render_products( array $atts ): string {
		$atts = shortcode_atts(
			[
				'category'        => '',
				'tag'            => '',
				'ribbon'         => '',
				'search'          => '',
				'sort'           => 'date',
				'order'          => 'DESC',
				'limit'          => 10,
				'columns'        => 3,
				'template'        => 'grid',
				'show_image'     => true,
				'show_price'     => true,
				'show_rating'    => true,
				'show_features'  => true,
				'show_cta'       => true,
				'cta_text'       => __( 'View Deal', Constants::TEXTDOMAIN ),
				'pagination'     => false,
				'exclude'       => '',
				'include'       => '',
			],
			$atts
		);

		return $this->render_products_html( $atts );
	}

	/**
	 * Render categories list
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_categories
	 */
	public function render_categories( array $atts ): string {
		$atts = shortcode_atts(
			[
				'limit'          => 10,
				'orderby'        => 'name',
				'order'          => 'ASC',
				'show_count'     => true,
				'show_image'     => false,
				'show_icon'      => true,
				'empty_message'  => __( 'No categories found', Constants::TEXTDOMAIN ),
				'hierarchical'   => false,
				'parent'         => 0,
				'taxonomy'       => Constants::TAX_CATEGORY,
			],
			$atts
		);

		return $this->render_categories_html( $atts );
	}

	/**
	 * Render tags cloud
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_tags
	 */
	public function render_tags( array $atts ): string {
		$atts = shortcode_atts(
			[
				'limit'          => 20,
				'orderby'        => 'count',
				'order'          => 'DESC',
				'show_count'     => true,
				'smallest'       => 8,
				'largest'        => 32,
				'unit'           => 'pt',
				'taxonomy'       => Constants::TAX_TAG,
				'empty_message'  => __( 'No tags found', Constants::TEXTDOMAIN ),
			],
			$atts
		);

		return $this->render_tags_html( $atts );
	}

	/**
	 * Render ribbons list
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_ribbons
	 */
	public function render_ribbons( array $atts ): string {
		$atts = shortcode_atts(
			[
				'limit'          => 50,
				'orderby'        => 'name',
				'order'          => 'ASC',
				'show_count'     => false,
				'taxonomy'       => Constants::TAX_RIBBON,
				'empty_message'  => __( 'No ribbons found', Constants::TEXTDOMAIN ),
			],
			$atts
		);

		return $this->render_ribbons_html( $atts );
	}

	/**
	 * Render single category
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_category
	 */
	public function render_category( array $atts ): string {
		$atts = shortcode_atts(
			[
				'slug'           => '',
				'show_count'     => false,
				'show_image'     => true,
				'show_description' => true,
				'show_products'  => false,
				'limit'          => 6,
			],
			$atts
		);

		$category = get_term_by( 'slug', $atts['slug'], Constants::TAX_CATEGORY );

		if ( ! $category || is_wp_error( $category ) ) {
			return sprintf( '<p class="aps-error">%s</p>', esc_html( $atts['empty_message'] ?? __( 'Category not found', Constants::TEXTDOMAIN ) ) );
		}

		return $this->render_single_category_html( $category, $atts );
	}

	/**
	 * Render single tag
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_tag
	 */
	public function render_tag( array $atts ): string {
		$atts = shortcode_atts(
			[
				'slug'           => '',
				'show_count'     => false,
				'show_products'  => false,
			],
			$atts
		);

		$tag = get_term_by( 'slug', $atts['slug'], Constants::TAX_TAG );

		if ( ! $tag || is_wp_error( $tag ) ) {
			return sprintf( '<p class="aps-error">%s</p>', esc_html( $atts['empty_message'] ?? __( 'Tag not found', Constants::TEXTDOMAIN ) ) );
		}

		return $this->render_single_tag_html( $tag, $atts );
	}

	/**
	 * Render single ribbon
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_ribbon
	 */
	public function render_ribbon( array $atts ): string {
		$atts = shortcode_atts(
			[
				'slug'           => '',
				'show_preview'   => true,
			],
			$atts
		);

		$ribbon = get_term_by( 'slug', $atts['slug'], Constants::TAX_RIBBON );

		if ( ! $ribbon || is_wp_error( $ribbon ) ) {
			return sprintf( '<p class="aps-error">%s</p>', esc_html( $atts['empty_message'] ?? __( 'Ribbon not found', Constants::TEXTDOMAIN ) ) );
		}

		return $this->render_single_ribbon_html( $ribbon, $atts );
	}

	/**
	 * Render featured products
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_featured
	 */
	public function render_featured_products( array $atts ): string {
		$atts = array_merge( $atts, [ 'featured' => true ] );
		return $this->render_products_html( $atts );
	}

	/**
	 * Render trending products
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_trending
	 */
	public function render_trending_products( array $atts ): string {
		$atts = array_merge( $atts, [ 'sort' => 'count', 'order' => 'DESC' ] );
		return $this->render_products_html( $atts );
	}

	/**
	 * Render products on sale
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 *
	 * @shortcode affiliate_on_sale
	 */
	public function render_on_sale_products( array $atts ): string {
		$atts = array_merge( $atts, [ 'on_sale' => true ] );
		return $this->render_products_html( $atts );
	}

	/**
	 * Render products HTML
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_products_html( array $atts ): string {
		$args = [
			'post_type'      => Constants::CPT_PRODUCT,
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['limit'] ),
			'paged'           => get_query_var( 'paged' ) ?? 1,
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
			's'              => $this->product_service->get_tax_query( $atts ),
		];

		// Add filtering
		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => Constants::TAX_CATEGORY,
				'field'    => 'slug',
				'terms'    => explode( ',', $atts['category'] ),
			];
		}

		if ( ! empty( $atts['tag'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => Constants::TAX_TAG,
				'field'    => 'slug',
				'terms'    => explode( ',', $atts['tag'] ),
			];
		}

		if ( ! empty( $atts['ribbon'] ) ) {
			$args['tax_query'][] = [
				'taxonomy' => Constants::TAX_RIBBON,
				'field'    => 'slug',
				'terms'    => explode( ',', $atts['ribbon'] ),
			];
		}

		if ( ! empty( $atts['exclude'] ) ) {
			$args['post__not_in'] = explode( ',', $atts['exclude'] );
		}

		if ( ! empty( $atts['include'] ) ) {
			$args['post__in'] = explode( ',', $atts['include'] );
		}

		// Featured filter
		if ( isset( $atts['featured'] ) && $atts['featured'] ) {
			$args['meta_query'][] = [
				'key'     => 'product_featured',
				'value'   => '1',
				'compare' => '=',
				'type'    => 'NUMERIC',
			];
		}

		// On sale filter
		if ( isset( $atts['on_sale'] ) && $atts['on_sale'] ) {
			$args['meta_query'][] = [
				'relation' => 'OR',
				[
					'key'     => 'product_sale_price',
					'value'   => '',
					'compare' => '!=',
					'type'    => 'NUMERIC',
				],
				[
					'key'     => 'product_sale_price',
					'compare' => '>',
					'value'   => 0,
					'type'    => 'NUMERIC',
				],
			];
		}

		$products = get_posts( $args );

		ob_start();
		
		$template_class = 'aps-products-template aps-grid-' . $atts['columns'] . '-cols';
		$template_class .= $atts['template'] === 'list' ? 'aps-products-list' : 'aps-products-grid';
		$template_class .= $atts['template'] === 'table' ? 'aps-products-table' : '';

		echo '<div class="' . esc_attr( $template_class ) . '">';

		foreach ( $products as $product ) {
			echo $this->render_product_card( $product, $atts );
		}

		echo '</div>';

		if ( $atts['pagination'] && $products ) {
			echo $this->render_pagination( $products, $args );
		}

		return ob_get_clean();
	}

	/**
	 * Render product card
	 *
	 * Delegates to shared ProductCardRenderer to avoid code duplication.
	 *
	 * @param \WP_Post $product Post object
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_product_card( \WP_Post $product, array $atts ): string {
		$sanitized_atts = $this->card_renderer->sanitize_attributes( $atts );
		return $this->card_renderer->render( $product, $sanitized_atts );
	}

	/**
	 * Render categories HTML
	 *
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_categories_html( array $atts ): string {
		$args = [
			'taxonomy'   => $atts['taxonomy'] ?? Constants::TAX_CATEGORY,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => false,
			'parent'     => intval( $atts['parent'] ),
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'category_hide_from_menu',
					'value'   => '1',
					'compare' => '!=',
					'type'    => 'NUMERIC',
				],
			],
		];

		$categories = get_terms( $args );

		ob_start();
		echo '<ul class="aps-categories-list">';

		foreach ( $categories as $category ) {
			$icon = get_term_meta( $category->term_id, 'category_icon', true );
			$image = get_term_meta( $category->term_id, 'category_image', true );
			$color = get_term_meta( $category->term_id, 'category_color', true );
			$count = absint( $category->count );
			$link = get_term_link( $category );

			echo '<li class="aps-category-item">';

			if ( $atts['show_image'] && $image ) {
				echo '<div class="aps-category-image">';
				echo '<a href="' . esc_url( $link ) . '" rel="nofollow">';
				echo wp_get_attachment_image( $image, 'thumbnail' );
				echo '</a>';
				echo '</div>';
			}

			echo '<div class="aps-category-info">';

			if ( $atts['show_icon'] && $icon ) {
				$is_svg = strpos( $icon, '<svg' ) !== false || strpos( $icon, '<path' ) !== false;
				
				if ( $is_svg ) {
					echo '<span class="aps-category-icon">' . $icon . '</span>';
				} else {
					echo '<span class="aps-category-icon-emoji">' . esc_html( $icon ) . '</span>';
				}
			}

			echo '<a href="' . esc_url( $link ) . '" class="aps-category-link" rel="nofollow">';
			echo esc_html( $category->name );
			echo '</a>';

			if ( $atts['show_count'] ) {
				echo '<span class="aps-category-count">(' . $count . ')</span>';
			}

			echo '</div>';

			if ( $atts['show_count'] ) {
				echo '<div class="aps-category-count-bar" style="width:' . min( 100, ( $count / 20 ) * 100 ) . '%;"></div>';
			}

			echo '</li>';
		}

		echo '</ul>';

		if ( empty( $categories ) ) {
			echo '<p class="aps-empty-message">' . esc_html( $atts['empty_message'] ) . '</p>';
		}

		return ob_get_clean();
	}

	/**
	 * Render tags HTML
	 *
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_tags_html( array $atts ): string {
		$args = [
			'taxonomy'   => $atts['taxonomy'] ?? Constants::TAX_TAG,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => false,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'tag_featured',
					'value'   => '1',
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
		];

		$tags = get_terms( $args );

		ob_start();
		echo '<div class="aps-tags-cloud">';

		$min_count = PHP_INT_MAX;
		$max_count = 0;

		foreach ( $tags as $tag ) {
			$count = absint( $tag->count );
			$min_count = min( $min_count, $count );
			$max_count = max( $max_count, $count );

			$font_size = 0.8 + ( ( $count - $min_count ) / ( $max_count - $min_count ) ) * 1.4;
			$font_size = min( 2.5, max( $font_size ) );

			$color = get_term_meta( $tag->term_id, 'tag_color', true );
			$link = get_term_link( $tag );

			$color_style = $color ? 'color: ' . esc_attr( $color ) . ';' : '';

			echo '<span class="aps-tag-link" style="font-size:' . number_format( $font_size, 2 ) . 'em;' . $color_style . '" rel="nofollow">';
			echo esc_html( $tag->name );
			echo '</span>';

			if ( $atts['show_count'] ) {
				echo '<span class="aps-tag-count">(' . $count . ')</span>';
			}
		}

		echo '</div>';

		if ( empty( $tags ) ) {
			echo '<p class="aps-empty-message">' . esc_html( $atts['empty_message'] ) . '</p>';
		}

		return ob_get_clean();
	}

	/**
	 * Render ribbons HTML
	 *
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_ribbons_html( array $atts ): string {
		$args = [
			'taxonomy'   => $atts['taxonomy'] ?? Constants::TAX_RIBBON,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval( $atts['limit'] ),
			'hide_empty' => false,
			'meta_query' => [
				'relation' => 'OR',
				[
					'key'     => 'ribbon_start_date',
					'value'   => current_time( 'Y-m-d H:i:s' ),
					'compare' => '<=',
					'type'    => 'DATETIME',
				],
				[
					'key'     => 'ribbon_expiration_date',
					'value'   => current_time( 'Y-m-d H:i:s' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				],
			],
		];

		$ribbons = get_terms( $args );

		ob_start();
		echo '<div class="aps-ribbons-list">';

		foreach ( $ribbons as $ribbon ) {
			$text = get_term_meta( $ribbon->term_id, 'ribbon_text', true );
			$bg_color = get_term_meta( $ribbon->term_id, 'ribbon_bg_color', true );
			$text_color = get_term_meta( $ribbon->term_id, 'ribbon_text_color', true );
			$position = get_term_meta( $ribbon->term_id, 'ribbon_position', true ) ?: 'top-left';
			$style = get_term_meta( $ribbon->term_id, 'ribbon_style', true ) ?: 'badge';
			$priority = get_term_meta( $ribbon->term_id, 'ribbon_priority', true ) ?: 0;
			$link = get_term_link( $ribbon );

			echo '<div class="aps-ribbon-item" style="order:' . intval( $priority ) . ';">';

			if ( $bg_color || $text_color ) {
				echo '<div class="aps-ribbon-preview" style="background-color:' . esc_attr( $bg_color ) . ';color:' . esc_attr( $text_color ) . ';">';
				echo esc_html( $text ?: $ribbon->name );
				echo '</div>';
			}

			echo '<span class="aps-ribbon-text">' . esc_html( $text ?: $ribbon->name ) . '</span>';
			echo '<span class="aps-ribbon-meta">' . ucfirst( esc_html( $style ) ) . '</span>';

			echo '</div>';
		}

		echo '</div>';

		if ( empty( $ribbons ) ) {
			echo '<p class="aps-empty-message">' . esc_html( $atts['empty_message'] ) . '</p>';
		}

		return ob_get_clean();
	}

	/**
	 * Render single category HTML
	 *
	 * @param \WP_Term $category Category term object
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_single_category_html( \WP_Term $category, array $atts ): string {
		$image = get_term_meta( $category->term_id, 'category_image', true );
		$color = get_term_meta( $category->term_id, 'category_color', true );
		$icon = get_term_meta( $category->term_id, 'category_icon', true );
		$description = $category->description;
		$link = get_term_link( $category );

		$args = [
			'post_type'   => Constants::CPT_PRODUCT,
			'posts_per_page' => intval( $atts['limit'] ),
			'paged'        => 1,
			'tax_query'   => [
				[
					'taxonomy' => Constants::TAX_CATEGORY,
					'field'    => 'slug',
					'terms'    => [ $category->slug ],
				],
			],
		];

		$products = new \WP_Query( $args );

		ob_start();

		echo '<div class="aps-single-category">';
		
		if ( $atts['show_image'] && $image ) {
			echo '<div class="aps-category-banner-image">';
			echo wp_get_attachment_image( $image, 'large' );
			echo '</div>';
		}

		echo '<div class="aps-single-category-info">';

		if ( $atts['show_image'] && $icon ) {
			$is_svg = strpos( $icon, '<svg' ) !== false || strpos( $icon, '<path' ) !== false;
			echo '<div class="aps-category-icon-preview">';
			if ( $is_svg ) {
				echo '<svg class="aps-icon-preview-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">' . $icon . '</svg>';
			} else {
				echo '<span class="aps-icon-preview-emoji">' . esc_html( $icon ) . '</span>';
			}
			echo '</div>';
		}

		echo '<h1 class="aps-single-category-title">' . esc_html( $category->name ) . '</h1>';

		if ( $atts['show_description'] && $description ) {
			echo '<p class="aps-single-category-description">' . wp_kses_post( $description ) . '</p>';
		}

		echo '<div class="aps-single-category-meta">';
		
		if ( $color ) {
			echo '<div class="aps-category-color-swatch" style="background-color:' . esc_attr( $color ) . ';"></div>';
		}

		if ( $atts['show_count'] ) {
			echo '<span class="aps-category-count">' . absint( $category->count ) . ' ' . __( 'products', Constants::TEXTDOMAIN ) . '</span>';
		}

		echo '</div>';

		if ( $atts['show_products'] && $products->have_posts() ) {
			echo '<h2 class="aps-category-products-title">' . esc_html__( 'Products', Constants::TEXTDOMAIN ) . '</h2>';
			echo '<div class="aps-category-products-grid">';
			while ( $products->have_posts() ) {
				$products->the_post();
				$product = $products->post;
				echo $this->render_product_card( $product, [
					'show_image' => $atts['show_image'],
					'show_price' => true,
					'show_features' => false,
					'show_rating' => false,
					'show_cta' => false,
				] );
			}
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render single tag HTML
	 *
	 * @param \WP_Term $tag Tag term object
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_single_tag_html( \WP_Term $tag, array $atts ): string {
		$color = get_term_meta( $tag->term_id, 'tag_color', true );
		$icon = get_term_meta( $tag->term_id, 'tag_icon', true );
		$link = get_term_link( $tag );

		$args = [
			'post_type'   => Constants::CPT_PRODUCT,
			'posts_per_page' => intval( $atts['limit'] ),
			'paged'        => 1,
			'tax_query'   => [
				[
					'taxonomy' => Constants::TAX_TAG,
					'field'    => 'slug',
					'terms'    => [ $tag->slug ],
				],
			],
		];

		$products = new \WP_Query( $args );

		ob_start();

		echo '<div class="aps-single-tag">';

		if ( $atts['show_image'] && $icon ) {
			$is_svg = strpos( $icon, '<svg' ) !== false || strpos( $icon, '<path' ) !== false;
			echo '<div class="aps-tag-icon-preview">';
			if ( $is_svg ) {
				echo '<svg class="aps-icon-preview-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">' . $icon . '</svg>';
			} else {
				echo '<span class="aps-icon-preview-emoji">' . esc_html( $icon ) . '</span>';
			}
			echo '</div>';
		}

		echo '<h1 class="aps-single-tag-title">' . sprintf( __( 'Tag: %s', Constants::TEXTDOMAIN ), esc_html( $tag->name ) ) . '</h1>';

		if ( $color ) {
			echo '<div class="aps-single-tag-color-swatch" style="background-color:' . esc_attr( $color ) . '"></div>';
		}

		if ( $atts['show_count'] ) {
			echo '<span class="aps-single-tag-count">' . absint( $tag->count ) . ' ' . __( 'products', Constants::TEXTDOMAIN ) . '</span>';
		}

		if ( $atts['show_products'] && $products->have_posts() ) {
			echo '<h2 class="aps-tag-products-title">' . esc_html__( 'Products', Constants::TEXTDOMAIN ) . '</h2>';
			echo '<div class="aps-tag-products-grid">';
			while ( $products->have_posts() ) {
				$products->the_post();
				$product = $products->post;
				echo $this->render_product_card( $product, [
					'show_image' => $atts['show_image'],
					'show_price' => true,
					'show_features' => false,
					'show_rating' => false,
					'show_cta' => false,
				] );
			}
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render single ribbon HTML
	 *
	 * @param \WP_Term $ribbon Ribbon term object
	 * @param array<string, mixed> $atts Display attributes
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_single_ribbon_html( \WP_Term $ribbon, array $atts ): string {
		$text = get_term_meta( $ribbon->term_id, 'ribbon_text', true );
		$bg_color = get_term_meta( $ribbon->term_id, 'ribbon_bg_color', true );
		$text_color = get_term_meta( $ribbon->term_id, 'ribbon_text_color', true );
		$position = get_term_meta( $ribbon->term_id, 'ribbon_position', true ) ?: 'top-left';
		$style = get_term_meta( $ribbon->term_id, 'ribbon_style', true ) ?: 'badge';

		if ( ! $atts['show_preview'] ) {
			return '';
		}

		ob_start();
		echo '<div class="aps-single-ribbon-preview">';

		if ( $bg_color || $text_color ) {
			echo '<div class="aps-ribbon-box" style="background-color:' . esc_attr( $bg_color ) . ';color:' . esc_attr( $text_color ) . ';position:' . esc_attr( $position ) . ';';
			if ( 'corner' === $style ) {
				echo 'clip-path: polygon(0 0, 100% 20%, 0); clip-path: polygon(100% 20%, 0 0, 20%, 100%); clip-path: polygon(0 100%, 100%, 80%, 80%);';
			} elseif ( 'banner' === $style ) {
				echo 'clip-path: polygon(0 0, 100% 30%, 0); clip-path: polygon(100% 70%, 0 30%, 0 0);';
			}
			echo '">';
			echo esc_html( $text ?: $ribbon->name );
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render pagination
	 *
	 * @param \WP_Post[] $products Posts array
	 * @param array<string, mixed> $query_args Query arguments
	 * @return string HTML output
	 * @since 1.0.0
	 */
	private function render_pagination( array $products, array $query_args ): string {
		$total = count( $products );
		$current_page = intval( $query_args['paged'] ?? 1 );
		$per_page = intval( $query_args['posts_per_page'] );
		$total_pages = ceil( $total / $per_page );

		if ( $total_pages <= 1 ) {
			return '';
		}

		ob_start();
		echo '<nav class="aps-pagination" aria-label="' . esc_attr__( 'Products pagination', Constants::TEXTDOMAIN ) . '">';

		// Previous page
		if ( $current_page > 1 ) {
			echo '<a href="' . esc_url( add_query_arg( 'paged', $current_page - 1 ) ) . '" class="aps-pagination-link aps-prev" aria-label="' . esc_attr__( 'Previous page', Constants::TEXTDOMAIN ) . '">';
			echo '<span class="aps-pagination-icon">&laquo;</span>';
			echo '</a>';
		}

		echo '<span class="aps-pagination-info">';
		printf( esc_html__( 'Page %d of %d', Constants::TEXTDOMAIN ), $current_page, $total_pages );
		echo '</span>';

		// Next page
		if ( $current_page < $total_pages ) {
			echo '<a href="' . esc_url( add_query_arg( 'paged', $current_page + 1 ) ) . '" class="aps-pagination-link aps-next" aria-label="' . esc_attr__( 'Next page', Constants::TEXTDOMAIN ) . '">';
			echo '<span class="aps-pagination-icon">&raquo;</span>';
			echo '</a>';
		}

		echo '</nav>';

		return ob_get_clean();
	}
}
