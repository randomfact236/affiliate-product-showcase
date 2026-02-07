<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

use AffiliateProductShowcase\Models\Product;
use AffiliateProductShowcase\Services\ProductService;

/**
 * AJAX Handler - Enterprise Grade v2.0
 *
 * @package AffiliateProductShowcase\Public
 * @since   2.0.0
 */
final class AjaxHandler {

	/**
	 * @var ProductService
	 */
	private ProductService $product_service;

	/**
	 * Constructor
	 *
	 * @param ProductService $product_service
	 */
	public function __construct(ProductService $product_service) {
		$this->product_service = $product_service;
	}

	/**
	 * Register AJAX hooks
	 */
	public function register(): void {
		add_action('wp_ajax_aps_filter_products', [$this, 'handleFilterProducts']);
		add_action('wp_ajax_nopriv_aps_filter_products', [$this, 'handleFilterProducts']);
	}

	/**
	 * Handle AJAX product filtering with full validation
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function handleFilterProducts(): void {
		// Verify nonce
		if (!wp_verify_nonce($_POST['nonce'] ?? '', 'aps_filter_nonce')) {
			wp_send_json_error([
				'message' => __('Security check failed.', 'affiliate-product-showcase')
			], 403);
			return;
		}

		// Rate limiting (simple implementation)
		$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
		$rate_key = 'aps_rate_' . md5($ip);
		$requests = get_transient($rate_key) ?: 0;
		
		if ($requests > 30) { // Max 30 requests per minute
			wp_send_json_error([
				'message' => __('Too many requests. Please try again later.', 'affiliate-product-showcase')
			], 429);
			return;
		}
		
		set_transient($rate_key, $requests + 1, 60);

		// Sanitize and validate inputs
		$category = sanitize_text_field($_POST['category'] ?? 'all');
		$tags_json = sanitize_text_field($_POST['tags'] ?? '[]');
		$sort = sanitize_text_field($_POST['sort'] ?? 'featured');
		$search = sanitize_text_field($_POST['search'] ?? '');
		$page = filter_var($_POST['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1;
		$per_page = filter_var($_POST['per_page'] ?? 12, FILTER_VALIDATE_INT) ?: 12;

		// Clamp per_page
		$per_page = max(1, min(100, $per_page));

		// Parse and validate tags
		$tags = json_decode($tags_json, true);
		if (!is_array($tags)) {
			$tags = [];
		}
		$tags = array_filter(array_map('sanitize_text_field', $tags));

		// Validate sort option
		$allowed_sorts = ['featured', 'latest', 'oldest', 'rating', 'popularity', 'random', 'price_low', 'price_high'];
		if (!in_array($sort, $allowed_sorts, true)) {
			$sort = 'featured';
		}

		// Build query args
		$query_args = [
			'per_page'   => $per_page,
			'page'       => $page,
			'post_status'=> 'publish',
		];

		if ($category !== 'all') {
			$query_args['category'] = $category;
		}

		if (!empty($tags)) {
			$query_args['tag'] = $tags;
		}

		if (!empty($search)) {
			$query_args['search'] = $search;
		}

		// Apply sorting with validation
		switch ($sort) {
			case 'latest':
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'DESC';
				break;
			case 'oldest':
				$query_args['orderby'] = 'date';
				$query_args['order'] = 'ASC';
				break;
			case 'rating':
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = '_aps_rating';
				$query_args['order'] = 'DESC';
				break;
			case 'popularity':
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = '_aps_view_count';
				$query_args['order'] = 'DESC';
				break;
			case 'price_low':
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = '_aps_price';
				$query_args['order'] = 'ASC';
				break;
			case 'price_high':
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = '_aps_price';
				$query_args['order'] = 'DESC';
				break;
			case 'random':
				$query_args['orderby'] = 'rand';
				break;
			default: // featured
				$query_args['orderby'] = 'meta_value_num';
				$query_args['meta_key'] = '_aps_featured';
				$query_args['order'] = 'DESC';
				break;
		}

		// Fetch products with try-catch
		try {
			$products = $this->product_service->get_products($query_args);
			$total_products = $this->getTotalCount($query_args);
		} catch (\Exception $e) {
			error_log('APS AJAX Error: ' . $e->getMessage());
			wp_send_json_error([
				'message' => __('Failed to load products.', 'affiliate-product-showcase')
			], 500);
			return;
		}

		// Validate response
		if (!is_array($products)) {
			wp_send_json_error([
				'message' => __('Invalid product data.', 'affiliate-product-showcase')
			], 500);
			return;
		}

		// Pre-fetch terms for all products (N+1 prevention)
		$product_ids = array_map(fn($p) => $p instanceof Product ? $p->id : null, $products);
		$product_ids = array_filter($product_ids);
		
		$product_terms_cache = [];
		if (!empty($product_ids)) {
			$categories = wp_get_object_terms($product_ids, 'aps_category');
			$tags_terms = wp_get_object_terms($product_ids, 'aps_tag');
			
			foreach ($product_ids as $pid) {
				$product_terms_cache[$pid] = ['aps_category' => [], 'aps_tag' => []];
			}
			
			foreach ($categories as $term) {
				if ($term instanceof \WP_Term) {
					$product_terms_cache[$term->object_id]['aps_category'][] = $term;
				}
			}
			foreach ($tags_terms as $term) {
				if ($term instanceof \WP_Term) {
					$product_terms_cache[$term->object_id]['aps_tag'][] = $term;
				}
			}
		}

		// Render products HTML
		$html = $this->renderProductCards($products, $product_terms_cache);
		
		// Render pagination
		$total_pages = (int) ceil($total_products / $per_page);
		$pagination_html = $this->renderPagination($page, $total_pages, $total_products);

		wp_send_json_success([
			'products'    => $html,
			'count'       => count($products),
			'total'       => $total_products,
			'total_pages' => $total_pages,
			'pagination'  => $pagination_html,
		]);
	}

	/**
	 * Get total product count for pagination
	 *
	 * @param array $query_args Query arguments
	 * @return int Total count
	 */
	private function getTotalCount(array $query_args): int {
		$wp_args = [
			'post_type'      => 'aps_product',
			'posts_per_page' => -1,
			'post_status'    => $query_args['post_status'] ?? 'publish',
			'fields'         => 'ids',
		];
		
		if (!empty($query_args['category'])) {
			$wp_args['tax_query'][] = [
				'taxonomy' => 'aps_category',
				'field'    => 'slug',
				'terms'    => $query_args['category'],
			];
		}
		
		if (!empty($query_args['tag'])) {
			$wp_args['tax_query'][] = [
				'taxonomy' => 'aps_tag',
				'field'    => 'slug',
				'terms'    => $query_args['tag'],
			];
		}
		
		if (!empty($query_args['search'])) {
			$wp_args['s'] = $query_args['search'];
		}
		
		$query = new \WP_Query($wp_args);
		return (int) $query->found_posts;
	}

	/**
	 * Render product cards HTML
	 *
	 * @param array<Product> $products
	 * @param array $product_terms_cache
	 * @return string
	 */
	private function renderProductCards(array $products, array $product_terms_cache): string {
		$partial = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/partials/product-card.php';
		
		// Fallback to inline rendering if partial missing
		if (!file_exists($partial)) {
			error_log('APS: Product card partial not found, using inline rendering');
			return $this->renderInlineCards($products, $product_terms_cache);
		}
		
		ob_start();
		
		// Make variables available to partial
		$affiliate_service = null; // AJAX doesn't have affiliate service context
		
		foreach ($products as $product) {
			if (!$product instanceof Product) continue;
			
			// Set up term cache for this product
			$product_categories = $product_terms_cache[$product->id]['aps_category'] ?? [];
			$product_tags = $product_terms_cache[$product->id]['aps_tag'] ?? [];
			
			include $partial;
		}
		
		return ob_get_clean();
	}

	/**
	 * Inline card rendering fallback
	 */
	private function renderInlineCards(array $products, array $product_terms_cache): string {
		$html = '';
		
		foreach ($products as $product) {
			if (!$product instanceof Product) continue;
			
			$tags = $product_terms_cache[$product->id]['aps_tag'] ?? [];
			$tag_html = '';
			
			foreach (array_slice($tags, 0, 3) as $tag) {
				$tag_html .= '<span class="aps-tag-badge">' . esc_html($tag->name) . '</span>';
			}
			
			$html .= sprintf(
				'<article class="aps-tool-card" data-id="%d">
					<h3 class="aps-tool-name">%s</h3>
					<div class="aps-price-block">
						<div class="aps-current-price">%s</div>
					</div>
					<div class="aps-tags-row">%s</div>
				</article>',
				esc_attr($product->id),
				esc_html($product->title),
				esc_html(number_format_i18n((float) $product->price, 2)),
				$tag_html
			);
		}
		
		return $html;
	}

	/**
	 * Render pagination HTML
	 */
	private function renderPagination(int $current_page, int $total_pages, int $total_items): string {
		if ($total_pages <= 1) {
			return '';
		}

		ob_start();
		?>
		<nav class="aps-pagination" aria-label="<?php esc_attr_e('Product pagination', 'affiliate-product-showcase'); ?>">
			<div class="aps-pagination-info">
				<?php 
				printf(
					esc_html__('Page %1$s of %2$s (%3$s items)', 'affiliate-product-showcase'),
					'<span class="aps-current-page">' . esc_html((string) $current_page) . '</span>',
					'<span class="aps-total-pages">' . esc_html((string) $total_pages) . '</span>',
					'<span class="aps-total-items">' . esc_html(number_format_i18n($total_items)) . '</span>'
				); 
				?>
			</div>
			<div class="aps-pagination-controls">
				<?php if ($current_page > 1) : ?>
					<button type="button" class="aps-pagination-prev" data-page="<?php echo esc_attr((string) ($current_page - 1)); ?>">
						← <?php esc_html_e('Previous', 'affiliate-product-showcase'); ?>
					</button>
				<?php endif; ?>
				
				<div class="aps-pagination-numbers">
					<?php for ($i = 1; $i <= $total_pages; $i++) : ?>
						<button type="button" 
								class="aps-pagination-number <?php echo $i === $current_page ? 'active' : ''; ?>" 
								data-page="<?php echo esc_attr((string) $i); ?>"
								<?php echo $i === $current_page ? 'aria-current="page"' : ''; ?>>
							<?php echo esc_html((string) $i); ?>
						</button>
					<?php endfor; ?>
				</div>
				
				<?php if ($current_page < $total_pages) : ?>
					<button type="button" class="aps-pagination-next" data-page="<?php echo esc_attr((string) ($current_page + 1)); ?>">
						<?php esc_html_e('Next', 'affiliate-product-showcase'); ?> →
					</button>
				<?php endif; ?>
			</div>
		</nav>
		<?php
		return ob_get_clean();
	}
}
