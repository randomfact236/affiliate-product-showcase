<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Cache\Cache;

/**
 * Shortcodes - Frontend rendering
 * 
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 */
final class Shortcodes {

	public function __construct( 
		private ProductService $product_service, 
		private SettingsRepository $settings_repository,
		private AffiliateService $affiliate_service,
		private ?Cache $cache = null
	) {
		$this->cache = $cache ?? new Cache();
	}

	/**
	 * Register shortcodes
	 */
	public function register(): void {
		add_shortcode( 'aps_showcase', [ $this, 'renderShowcaseDynamic' ] );
		add_shortcode( 'aps_showcase_static', [ $this, 'renderShowcaseStatic' ] );
	}

	/**
	 * Render dynamic product showcase with full enterprise features
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string HTML output
	 * @since 2.0.0
	 */
	public function renderShowcaseDynamic( array $atts ): string {
		// Parse attributes with strict typing
		$atts = shortcode_atts([
			'per_page'       => 12,
			'category'       => '',
			'tag'            => '',
			'show_filters'   => true,
			'show_sort'      => true,
			'cache_duration' => 3600, // 1 hour default
		], $atts, 'aps_showcase');

		// Sanitize and validate
		$per_page       = $this->validateInt($atts['per_page'], 1, 100);
		$category       = sanitize_text_field($atts['category']);
		$tag            = sanitize_text_field($atts['tag']);
		$show_filters   = filter_var($atts['show_filters'], FILTER_VALIDATE_BOOLEAN);
		$show_sort      = filter_var($atts['show_sort'], FILTER_VALIDATE_BOOLEAN);
		$cache_duration = $this->validateInt($atts['cache_duration'], 0, 86400);

		// Get current page from URL or default to 1
		$current_page = $this->validateInt($_GET['aps_page'] ?? 1, 1);

		// Build query arguments
		$query_args = [
			'per_page'   => $per_page,
			'page'       => $current_page,
			'post_status'=> 'publish',
		];

		if (!empty($category)) {
			$query_args['category'] = $category;
		}

		if (!empty($tag)) {
			$query_args['tag'] = $tag;
		}

		// Generate cache key
		$cache_key = 'aps_showcase_' . md5(serialize($query_args) . get_locale());
		
		// Try cache first
		if ($cache_duration > 0) {
			$cached = get_transient($cache_key);
			if ($cached !== false) {
				return $cached['html'];
			}
		}

		// Fetch products with error handling
		try {
			$products = $this->product_service->get_products($query_args);
			$total_products = $this->getTotalCount($query_args);
		} catch (\Exception $e) {
			error_log('APS: Failed to fetch products - ' . $e->getMessage());
			return sprintf(
				'<p class="aps-error">%s</p>',
				esc_html__('Unable to load products. Please try again later.', 'affiliate-product-showcase')
			);
		}

		// Validate products array
		if (!is_array($products)) {
			error_log('APS: Product service returned non-array');
			return sprintf(
				'<p class="aps-error">%s</p>',
				esc_html__('Invalid product data.', 'affiliate-product-showcase')
			);
		}

		// Pre-fetch all terms to eliminate N+1 queries
		$product_ids = array_map(fn($p) => $p instanceof Product ? $p->id : null, $products);
		$product_ids = array_filter($product_ids);
		
		$product_terms_cache = [];
		$term_meta_cache = [];
		
		if (!empty($product_ids)) {
			// Batch fetch all terms
			$categories = wp_get_object_terms($product_ids, 'aps_category');
			$tags = wp_get_object_terms($product_ids, 'aps_tag');
			
			// Organize by product ID
			foreach ($product_ids as $pid) {
				$product_terms_cache[$pid] = [
					'aps_category' => [],
					'aps_tag'      => []
				];
			}
			
			foreach ($categories as $term) {
				if ($term instanceof \WP_Term) {
					$product_terms_cache[$term->object_id]['aps_category'][] = $term;
					// Pre-fetch meta
					if (!isset($term_meta_cache[$term->term_id])) {
						$term_meta_cache[$term->term_id] = get_term_meta($term->term_id);
					}
				}
			}
			
			foreach ($tags as $term) {
				if ($term instanceof \WP_Term) {
					$product_terms_cache[$term->object_id]['aps_tag'][] = $term;
					if (!isset($term_meta_cache[$term->term_id])) {
						$term_meta_cache[$term->term_id] = get_term_meta($term->term_id);
					}
				}
			}
		}

		// Fetch all categories/tags for filters (with caching)
		$all_categories = get_terms([
			'taxonomy'   => 'aps_category',
			'hide_empty' => false,
		]);
		$all_tags = get_terms([
			'taxonomy'   => 'aps_tag',
			'hide_empty' => false,
		]);

		$all_categories = !is_wp_error($all_categories) ? $all_categories : [];
		$all_tags = !is_wp_error($all_tags) ? $all_tags : [];

		// FIX: Pre-fetch term meta for ALL sidebar tags (N+1 fix)
		$all_tag_ids = wp_list_pluck($all_tags, 'term_id');
		if (!empty($all_tag_ids)) {
			update_meta_cache('term', $all_tag_ids);
			// Populate local cache for template
			foreach ($all_tag_ids as $tag_id) {
				if (!isset($term_meta_cache[$tag_id])) {
					$term_meta_cache[$tag_id] = get_term_meta($tag_id);
				}
			}
		}

		// Calculate pagination
		$total_pages = (int) ceil($total_products / $per_page);

		// Prepare settings for template
		$settings = [
			'per_page'            => $per_page,
			'show_filters'        => $show_filters,
			'show_sort'           => $show_sort,
			'page'                => $current_page,
			'total_pages'         => $total_pages,
			'categories'          => $all_categories,
			'tags'                => $all_tags,
			'selected_tags'       => explode(',', sanitize_text_field($_GET['aps_tags'] ?? '')),
			'product_terms_cache' => $product_terms_cache,
			'term_meta_cache'     => $term_meta_cache,
		];

		// Enqueue assets with version busting
		$core_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'assets/css/core.css';
		$css_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'assets/css/frontend.css';
		$js_path = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'assets/js/showcase-frontend.min.js';
		
		$core_version = file_exists($core_path) ? filemtime($core_path) : '2.0.0';
		$css_version = file_exists($css_path) ? filemtime($css_path) : '2.0.0';
		$js_version = file_exists($js_path) ? filemtime($js_path) : '2.0.0';

		// Core styles first
		wp_enqueue_style(
			'affiliate-product-showcase-core',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/core.css',
			[],
			$core_version
		);
		
		// Frontend-specific styles
		wp_enqueue_style(
			'affiliate-product-showcase',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/frontend.css',
			[ 'affiliate-product-showcase-core' ],
			$css_version
		);

		wp_enqueue_script(
			'aps-showcase-js',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/js/showcase-frontend.min.js',
			[],
			$js_version,
			true
		);
		
		// Localize with full i18n
		wp_localize_script('aps-showcase-js', 'apsData', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('aps_filter_nonce'),
			'i18n'    => [
				'noProducts' => __('No products found.', 'affiliate-product-showcase'),
				'loading'    => __('Loading...', 'affiliate-product-showcase'),
				'error'      => __('Error loading products.', 'affiliate-product-showcase'),
				'retry'      => __('Retry', 'affiliate-product-showcase'),
			]
		]);

		// Render template with output buffering
		$template = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/showcase-dynamic.php';
		
		if (!file_exists($template)) {
			error_log('APS: Template not found: ' . $template);
			return sprintf(
				'<p class="aps-error">%s</p>',
				esc_html__('Error: Template not found.', 'affiliate-product-showcase')
			);
		}

		ob_start();
		
		// Make variables available to template
		$affiliate_service = $this->affiliate_service;
		
		include $template;
		
		$output = ob_get_clean();

		// Cache the output if enabled
		if ($cache_duration > 0 && !empty($output)) {
			set_transient($cache_key, ['html' => $output], $cache_duration);
		}

		return $output;
	}

	/**
	 * Get total product count for pagination
	 *
	 * @param array $query_args Query arguments
	 * @return int Total count
	 */
	private function getTotalCount(array $query_args): int {
		// Remove pagination to get total
		$count_args = $query_args;
		$count_args['per_page'] = -1;
		$count_args['fields'] = 'ids';
		
		$wp_args = [
			'post_type'      => 'aps_product',
			'posts_per_page' => -1,
			'post_status'    => $count_args['post_status'] ?? 'publish',
			'fields'         => 'ids',
		];
		
		if (!empty($count_args['category'])) {
			$wp_args['tax_query'][] = [
				'taxonomy' => 'aps_category',
				'field'    => 'slug',
				'terms'    => $count_args['category'],
			];
		}
		
		if (!empty($count_args['tag'])) {
			$wp_args['tax_query'][] = [
				'taxonomy' => 'aps_tag',
				'field'    => 'slug',
				'terms'    => $count_args['tag'],
			];
		}
		
		$query = new \WP_Query($wp_args);
		return (int) $query->found_posts;
	}

	/**
	 * Render product showcase (STATIC - no database)
	 * Exact copy of design from plan/frontend-design.md
	 */
	public function renderShowcaseStatic( array $atts ): string {
		// Enqueue core + frontend CSS
		wp_enqueue_style(
			'affiliate-product-showcase-core',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/core.css',
			[],
			'2.0.0'
		);
		wp_enqueue_style(
			'affiliate-product-showcase-static',
			AFFILIATE_PRODUCT_SHOWCASE_URL . 'assets/css/frontend.css',
			[ 'affiliate-product-showcase-core' ],
			'2.0.0'
		);
		
		$template = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/showcase-static.php';
		
		if ( ! file_exists( $template ) ) {
			return '<p>Error: Static template not found at ' . esc_html( $template ) . '</p>';
		}
		
		ob_start();
		include $template;
		$output = ob_get_clean();
		
		if ( empty( $output ) ) {
			return '<p>Error: Template rendered empty output.</p>';
		}
		
		return $output;
	}

	/**
	 * Validate integer within range
	 *
	 * @param mixed $value
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	private function validateInt($value, int $min = 0, int $max = PHP_INT_MAX): int {
		$int = filter_var($value, FILTER_VALIDATE_INT);
		if ($int === false) {
			return $min;
		}
		return max($min, min($max, $int));
	}
}
