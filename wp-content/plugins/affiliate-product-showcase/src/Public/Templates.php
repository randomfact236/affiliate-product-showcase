<?php
/**
 * Template Loader
 *
 * Handles template loading for product archives, single products,
 * and taxonomy pages with WordPress hierarchy overrides.
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

/**
 * Template Loader
 *
 * Provides template loading functions for product display.
 * Uses WordPress template hierarchy with plugin-specific overrides.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class Templates {
	/**
	 * Boot service
	 *
	 * Registers template filters with WordPress.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function boot(): void {
		add_filter( 'template_include', [ $this, 'load_product_template' ], 10, 1 );
		add_filter( 'single_template', [ $this, 'load_single_product_template' ] );
		add_filter( 'archive_template', [ $this, 'load_archive_product_template' ] );
		add_filter( 'taxonomy_template', [ $this, 'load_taxonomy_template' ], 10, 1 );
	}

	/**
	 * Load product template
	 *
	 * Checks if current request is for a product and loads
	 * appropriate template from plugin or theme.
	 *
	 * @param string $template Current template path
	 * @return string Template path to use
	 * @since 1.0.0
	 *
	 * @filter template_include
	 */
	public function load_product_template( string $template ): string {
		// Only modify for our custom post type
		if ( is_singular( Constants::CPT_PRODUCT ) ) {
			$plugin_template = $this->locate_template( 'single-' . Constants::CPT_PRODUCT . '.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		// Check for taxonomy archives
		if ( is_tax( Constants::TAX_CATEGORY ) || is_tax( Constants::TAX_TAG ) ) {
			$plugin_template = $this->locate_template( 'taxonomy-' . Constants::TAX_CATEGORY . '.php' );
			if ( ! $plugin_template ) {
				$plugin_template = $this->locate_template( 'archive-' . Constants::CPT_PRODUCT . '.php' );
			}
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Load single product template
	 *
	 * @param string $template Current template path
	 * @return string Template path to use
	 * @since 1.0.0
	 *
	 * @filter single_template
	 */
	public function load_single_product_template( string $template ): string {
		if ( is_singular( Constants::CPT_PRODUCT ) ) {
			$plugin_template = $this->locate_template( 'single-' . Constants::CPT_PRODUCT . '.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Load archive product template
	 *
	 * @param string $template Current template path
	 * @return string Template path to use
	 * @since 1.0.0
	 *
	 * @filter archive_template
	 */
	public function load_archive_product_template( string $template ): string {
		if ( is_post_type_archive( Constants::CPT_PRODUCT ) ) {
			$plugin_template = $this->locate_template( 'archive-' . Constants::CPT_PRODUCT . '.php' );
			if ( $plugin_template ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Load taxonomy template
	 *
	 * @param string $template Current template path
	 * @return string Template path to use
	 * @since 1.0.0
	 *
	 * @filter taxonomy_template
	 */
	public function load_taxonomy_template( string $template ): string {
		$term = get_queried_object();

		if ( $term && isset( $term->taxonomy ) ) {
			// Category archive template
			if ( $term->taxonomy === Constants::TAX_CATEGORY ) {
				$plugin_template = $this->locate_template( 'taxonomy-' . Constants::TAX_CATEGORY . '.php' );
				if ( ! $plugin_template ) {
					$plugin_template = $this->locate_template( 'taxonomy-' . Constants::CPT_PRODUCT . '.php' );
				}
				if ( $plugin_template ) {
					return $plugin_template;
				}
			}

			// Tag archive template
			if ( $term->taxonomy === Constants::TAX_TAG ) {
				$plugin_template = $this->locate_template( 'taxonomy-' . Constants::TAX_TAG . '.php' );
				if ( ! $plugin_template ) {
					$plugin_template = $this->locate_template( 'taxonomy-' . Constants::CPT_PRODUCT . '.php' );
				}
				if ( $plugin_template ) {
					return $plugin_template;
				}
			}
		}

		return $template;
	}

	/**
	 * Locate template file
	 *
	 * Searches for template in this order:
	 * 1. Child theme
	 * 2. Parent theme
	 * 3. Plugin templates folder
	 * 4. WordPress default
	 *
	 * @param string $template_name Template file name
	 * @return string|false Template path if found, false otherwise
	 * @since 1.0.0
	 */
	private function locate_template( string $template_name ) {
		// Search in child theme
		$template = locate_template( 'affiliate-product-showcase/' . $template_name );
		if ( $template ) {
			return $template;
		}

		// Search in plugin's templates directory
		$plugin_dir = plugin_dir_path( dirname( dirname( __DIR__ ) ) );
		$template_path = $plugin_dir . 'templates/' . $template_name;

		if ( file_exists( $template_path ) ) {
			return $template_path;
		}

		return false;
	}

	/**
	 * Get breadcrumb items for product pages
	 *
	 * @return array<int, array<string, string>> Breadcrumb items with 'url' and 'title'
	 * @since 1.0.0
	 */
	public function get_breadcrumbs(): array {
		$breadcrumbs = [];

		// Home
		$breadcrumbs[] = [
			'url'   => home_url(),
			'title' => __( 'Home', Constants::TEXTDOMAIN ),
		];

		// Shop/Archive page
		if ( is_post_type_archive( Constants::CPT_PRODUCT ) || is_tax( Constants::TAX_CATEGORY ) || is_tax( Constants::TAX_TAG ) ) {
			$breadcrumbs[] = [
				'url'   => get_post_type_archive_link( Constants::CPT_PRODUCT ),
				'title' => __( 'Products', Constants::TEXTDOMAIN ),
			];
		}

		// Category archive
		if ( is_tax( Constants::TAX_CATEGORY ) ) {
			$term = get_queried_object();
			if ( $term && ! is_wp_error( $term ) ) {
				$ancestors = get_ancestors( $term );
				// Add ancestors in reverse order (top to bottom)
				foreach ( array_reverse( $ancestors ) as $ancestor ) {
					$breadcrumbs[] = [
						'url'   => get_term_link( $ancestor ),
						'title' => $ancestor->name,
					];
				}
				// Current term
				$breadcrumbs[] = [
					'url'   => get_term_link( $term ),
					'title' => $term->name,
				];
			}
		}

		// Tag archive
		if ( is_tax( Constants::TAX_TAG ) ) {
			$term = get_queried_object();
			if ( $term && ! is_wp_error( $term ) ) {
				$breadcrumbs[] = [
					'url'   => get_term_link( $term ),
					'title' => sprintf( __( 'Tag: %s', Constants::TEXTDOMAIN ), $term->name ),
				];
			}
		}

		// Single product
		if ( is_singular( Constants::CPT_PRODUCT ) ) {
			$product_id = get_the_ID();
			$terms = get_the_terms( $product_id, Constants::TAX_CATEGORY );

			if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$ancestors = get_ancestors( $term );
					// Add category and its ancestors
					foreach ( array_reverse( $ancestors ) as $ancestor ) {
						$breadcrumbs[] = [
							'url'   => get_term_link( $ancestor ),
							'title' => $ancestor->name,
						];
					}
					$breadcrumbs[] = [
						'url'   => get_term_link( $term ),
						'title' => $term->name,
					];
					break; // Only show first category
				}
			}

			// Current product
			$breadcrumbs[] = [
				'url'   => get_permalink(),
				'title' => get_the_title(),
			];
		}

		/**
		 * Filter breadcrumbs
		 *
		 * @param array<int, array<string, string>> $crumbs Breadcrumb items
		 * @return array<int, array<string, string>> Modified breadcrumb items
		 * @since 1.0.0
		 *
		 * @filter affiliate_showcase_breadcrumbs
		 */
		return apply_filters( 'affiliate_showcase_breadcrumbs', $breadcrumbs );
	}

	/**
	 * Render breadcrumb HTML
	 *
	 * @return string Breadcrumb HTML
	 * @since 1.0.0
	 */
	public function render_breadcrumbs(): string {
		$breadcrumbs = $this->get_breadcrumbs();

		if ( empty( $breadcrumbs ) ) {
			return '';
		}

		$html = '<nav class="aps-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', Constants::TEXTDOMAIN ) . '">';

		foreach ( $breadcrumbs as $index => $crumb ) {
			$is_last = $index === count( $breadcrumbs ) - 1;

			$html .= '<span class="aps-breadcrumb-item">';

			if ( ! empty( $crumb['url'] ) ) {
				$html .= '<a href="' . esc_url( $crumb['url'] ) . '" rel="' . ( $is_last ? 'bookmark' : 'nofollow' ) . '">';
			}

			$html .= '<span class="aps-breadcrumb-title">' . esc_html( $crumb['title'] ) . '</span>';

			if ( ! empty( $crumb['url'] ) ) {
				$html .= '</a>';
			}

			$html .= '</span>';

			if ( ! $is_last ) {
				$html .= '<span class="aps-breadcrumb-separator"> / </span>';
			}
		}

		$html .= '</nav>';

		return $html;
	}
}
