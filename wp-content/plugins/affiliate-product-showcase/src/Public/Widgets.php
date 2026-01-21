<?php
/**
 * Widgets
 *
 * Registers WordPress widgets for displaying affiliate products,
 * categories, tags, and ribbons in sidebars.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if (!defined('ABSPATH')) {
	exit;
}

use AffiliateProductShowcase\Plugin\Constants;
use WP_Widget;

/**
 * Tags Widget
 *
 * Displays product tags in tag cloud format with caching support.
 * Supports customization of display options and optimized rendering.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class TagsWidget extends WP_Widget {
	/**
	 * Widget base ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const ID = 'affiliate_showcase_tags';

	/**
	 * Cache key prefix
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private const CACHE_KEY_PREFIX = 'aps_tags_widget_';

	/**
	 * Cache expiration time in seconds
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private const CACHE_EXPIRATION = 3600;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			self::ID,
			__('Product Tags', Constants::TEXTDOMAIN),
			[
				'description' => __('Display product tags in tag cloud format with customization options.', Constants::TEXTDOMAIN),
				'customize_selective_refresh' => true,
			]
		);
	}

	/**
	 * Widget form
	 *
	 * Renders widget settings form in WordPress admin.
	 *
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 */
	public function form($instance): void {
		$limit = isset($instance['limit']) ? absint($instance['limit']) : 20;
		$orderby = isset($instance['orderby']) ? esc_attr($instance['orderby']) : 'count';
		$order = isset($instance['order']) ? esc_attr($instance['order']) : 'DESC';
		$smallest = isset($instance['smallest']) ? absint($instance['smallest']) : 8;
		$largest = isset($instance['largest']) ? absint($instance['largest']) : 32;
		$unit = isset($instance['unit']) ? esc_attr($instance['unit']) : 'pt';
		$show_count = isset($instance['show_count']) ? rest_sanitize_boolean($instance['show_count']) : true;
		$empty_message = isset($instance['empty_message']) ? esc_textarea_field($instance['empty_message']) : __('No tags found', Constants::TEXTDOMAIN);
		$taxonomy = isset($instance['taxonomy']) ? esc_attr($instance['taxonomy']) : Constants::TAX_TAG;

		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
				<?php esc_html_e('Title', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title'] ?? ''); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
				<?php esc_html_e('Number of tags', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="50" value="<?php echo esc_attr($limit); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('orderby')); ?>">
				<?php esc_html_e('Order by', Constants::TEXTDOMAIN); ?>:
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('orderby')); ?>" name="<?php echo esc_attr($this->get_field_name('orderby')); ?>">
				<option value="name" <?php selected($orderby, 'name'); ?>><?php esc_html_e('Name', Constants::TEXTDOMAIN); ?></option>
				<option value="count" <?php selected($orderby, 'count'); ?>><?php esc_html_e('Product count', Constants::TEXTDOMAIN); ?></option>
				<option value="id" <?php selected($orderby, 'id'); ?>><?php esc_html_e('ID', Constants::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('order')); ?>">
				<?php esc_html_e('Order', Constants::TEXTDOMAIN); ?>:
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('order')); ?>" name="<?php echo esc_attr($this->get_field_name('order')); ?>">
				<option value="ASC" <?php selected($order, 'ASC'); ?>><?php esc_html_e('Ascending', Constants::TEXTDOMAIN); ?></option>
				<option value="DESC" <?php selected($order, 'DESC'); ?>><?php esc_html_e('Descending', Constants::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('smallest')); ?>">
				<?php esc_html_e('Minimum font size', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="smallfat" id="<?php echo esc_attr($this->get_field_id('smallest')); ?>" name="<?php echo esc_attr($this->get_field_name('smallest')); ?>" type="number" min="6" max="24" value="<?php echo esc_attr($smallest); ?>" /> pt
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('largest')); ?>">
				<?php esc_html_e('Maximum font size', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="smallfat" id="<?php echo esc_attr($this->get_field_id('largest')); ?>" name="<?php echo esc_attr($this->get_field_name('largest')); ?>" type="number" min="6" max="96" value="<?php echo esc_attr($largest); ?>" /> pt
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('unit')); ?>">
				<?php esc_html_e('Unit', Constants::TEXTDOMAIN); ?>:
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('unit')); ?>" name="<?php echo esc_attr($this->get_field_name('unit')); ?>">
				<option value="pt" <?php selected($unit, 'pt'); ?>><?php esc_html_e('Points', Constants::TEXTDOMAIN); ?></option>
				<option value="px" <?php selected($unit, 'px'); ?>><?php esc_html_e('Pixels', Constants::TEXTDOMAIN); ?></option>
				<option value="em" <?php selected($unit, 'em'); ?>><?php esc_html_e('Ems', Constants::TEXTDOMAIN); ?></option>
				<option value="rem" <?php selected($unit, 'rem'); ?>><?php esc_html_e('Rems', Constants::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_count')); ?>" name="<?php echo esc_attr($this->get_field_name('show_count')); ?>" <?php checked($show_count); ?> />
			<label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>">
				<?php esc_html_e('Show tag count', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('empty_message')); ?>">
				<?php esc_html_e('Empty message', Constants::TEXTDOMAIN); ?>:
			</label>
			<textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('empty_message')); ?>" name="<?php echo esc_attr($this->get_field_name('empty_message')); ?>"><?php echo esc_textarea($empty_message); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Update widget instance
	 *
	 * Sanitizes and validates widget settings before saving.
	 *
	 * @param array<string, mixed> $new_instance New widget instance settings
	 * @param array<string, mixed> $old_instance Old widget instance settings
	 * @return array<string, mixed> Updated instance
	 * @since 1.0.0
	 */
	public function update($new_instance, $old_instance): array {
		$instance = [];
		$instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
		$instance['limit'] = absint($new_instance['limit'] ?? 20);
		$instance['limit'] = max(1, min(100, $instance['limit']));
		$instance['orderby'] = in_array($new_instance['orderby'] ?? '', ['name', 'count', 'id'], true) ? $new_instance['orderby'] : 'name';
		$instance['order'] = in_array($new_instance['order'] ?? '', ['ASC', 'DESC'], true) ? $new_instance['order'] : 'DESC';
		$instance['smallest'] = absint($new_instance['smallest'] ?? 8);
		$instance['smallest'] = max(6, min(24, $instance['smallest']));
		$instance['largest'] = absint($new_instance['largest'] ?? 32);
		$instance['largest'] = max(12, min(96, $instance['largest']));
		$instance['unit'] = in_array($new_instance['unit'] ?? '', ['pt', 'px', 'em', 'rem'], true) ? $new_instance['unit'] : 'pt';
		$instance['show_count'] = rest_sanitize_boolean($new_instance['show_count'] ?? true);
		$instance['empty_message'] = sanitize_textarea_field($new_instance['empty_message'] ?? '');
		$instance['taxonomy'] = esc_attr($new_instance['taxonomy'] ?? Constants::TAX_TAG);

		// Clear cache when settings change
		$this->clear_widget_cache();

		return $instance;
	}

	/**
	 * Render widget
	 *
	 * Displays the widget on the frontend with caching support.
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 */
	public function widget($args, $instance): void {
		try {
			$cache_key = $this->get_cache_key($instance);

			// Try to get cached output
			$cached_output = wp_cache_get($cache_key, 'aps_widgets');
			if ($cached_output !== false) {
				echo $cached_output;
				return;
			}

			// Generate widget content
			$widget_content = $this->generate_widget_content($args, $instance);

			// Cache output
			wp_cache_set($cache_key, $widget_content, 'aps_widgets', self::CACHE_EXPIRATION);

			echo $widget_content;
		} catch (\Throwable $e) {
			// Log error but don't break the page
			error_log(sprintf('TagsWidget error: %s', $e->getMessage()));
			echo '<!-- TagsWidget error occurred -->';
		}
	}

	/**
	 * Generate widget content
	 *
	 * Generates HTML content for the widget.
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Widget instance settings
	 * @return string Generated HTML content
	 * @since 1.0.0
	 */
	private function generate_widget_content(array $args, array $instance): string {
		$atts = [
			'limit'         => intval($instance['limit'] ?? 20),
			'orderby'       => $instance['orderby'] ?? 'count',
			'order'         => $instance['order'] ?? 'DESC',
			'smallest'      => absint($instance['smallest'] ?? 8),
			'largest'       => absint($instance['largest'] ?? 32),
			'unit'          => $instance['unit'] ?? 'pt',
			'show_count'    => rest_sanitize_boolean($instance['show_count'] ?? true),
			'empty_message' => $instance['empty_message'] ?? __('No tags found', Constants::TEXTDOMAIN),
			'taxonomy'      => esc_attr($instance['taxonomy'] ?? Constants::TAX_TAG),
		];

		$term_args = [
			'taxonomy'   => $atts['taxonomy'] ?? Constants::TAX_TAG,
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => intval($atts['limit']),
			'hide_empty' => false,
			'fields'      => 'ids', // Only get IDs for better performance
		];

		$tag_ids = get_terms($term_args);
		$tags = [];

		// Hydrate tags with full objects
		if (!empty($tag_ids) && !is_wp_error($tag_ids)) {
			foreach ($tag_ids as $tag_id) {
				$tag = get_term($tag_id, $atts['taxonomy']);
				if ($tag && !is_wp_error($tag)) {
					$tags[] = $tag;
				}
			}
		}

		ob_start();
		echo $args['before_widget'];

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Product Tags', Constants::TEXTDOMAIN) : $instance['title'], $instance, $this->id_base);

		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo '<div class="aps-tags-cloud" role="list" aria-label="' . esc_attr__('Product tags', Constants::TEXTDOMAIN) . '">';

		if (!empty($tags)) {
			$min_count = PHP_INT_MAX;
			$max_count = 0;

			foreach ($tags as $tag) {
				$count = absint($tag->count);
				$min_count = min($min_count, $count);
				$max_count = max($max_count, $count);

				$font_size = $this->calculate_font_size($count, $min_count, $max_count, $atts['smallest'], $atts['largest'], $atts['unit']);
				$font_style = 'font-size: ' . number_format($font_size, 1) . 'px;';
				$color = get_term_meta($tag->term_id, 'tag_color', true);
				$link = get_term_link($tag);

				if (is_wp_error($link)) {
					continue;
				}

				$color_style = $color ? 'color: ' . esc_attr($color) . ';' : '';

				echo '<span class="aps-tag-link" style="' . esc_attr($font_style . ' ' . $color_style) . '" role="listitem">';
				echo '<a href="' . esc_url($link) . '" rel="tag nofollow" aria-label="' . esc_attr(sprintf(__('%s tag with %d products', Constants::TEXTDOMAIN), $tag->name, $count)) . '">';
				echo esc_html($tag->name);
				echo '</a>';

				if ($atts['show_count']) {
					echo '<span class="aps-tag-count" aria-hidden="true">(' . absint($count) . ')</span>';
				}

				echo '</span>';
			}
		} else {
			echo '<p class="aps-widget-empty-message">' . esc_html($atts['empty_message']) . '</p>';
		}

		echo '</div>';
		echo $args['after_widget'];

		return ob_get_clean();
	}

	/**
	 * Calculate font size for tag
	 *
	 * Calculates font size based on tag count using linear interpolation.
	 *
	 * @param int $count Tag count
	 * @param int $min_count Minimum count
	 * @param int $max_count Maximum count
	 * @param int $smallest Minimum font size
	 * @param int $largest Maximum font size
	 * @param string $unit Unit (pt, px, em, rem)
	 * @return float Calculated font size in pixels
	 * @since 1.0.0
	 */
	private function calculate_font_size(int $count, int $min_count, int $max_count, int $smallest, int $largest, string $unit): float {
		if ($max_count === $min_count) {
			$font_size = $smallest;
		} else {
			$font_size = $smallest + (($count - $min_count) / ($max_count - $min_count)) * ($largest - $smallest);
		}

		// Convert to pixels for display
		$font_size_px = match($unit) {
			'pt' => $font_size * 1.33, // pt to px conversion
			'px' => $font_size,
			'em' => $font_size * 16,
			'rem' => $font_size * 16,
			default => $font_size * 16,
		};

		return $font_size_px;
	}

	/**
	 * Get cache key for widget
	 *
	 * Generates a unique cache key based on widget settings.
	 *
	 * @param array<string, mixed> $instance Widget instance settings
	 * @return string Cache key
	 * @since 1.0.0
	 */
	private function get_cache_key(array $instance): string {
		$hash = md5(serialize($instance));
		return self::CACHE_KEY_PREFIX . $hash;
	}

	/**
	 * Clear widget cache
	 *
	 * Clears all cached output for this widget type.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function clear_widget_cache(): void {
		wp_cache_flush_group('aps_widgets');
	}
}

/**
 * Featured Widget
 *
 * Displays featured products in sidebar with caching support.
 * Supports customization of display options and optimized rendering.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class FeaturedWidget extends WP_Widget {
	/**
	 * Widget base ID
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public const ID = 'affiliate_showcase_featured';

	/**
	 * Cache key prefix
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private const CACHE_KEY_PREFIX = 'aps_featured_widget_';

	/**
	 * Cache expiration time in seconds
	 *
	 * @var int
	 * @since 1.0.0
	 */
	private const CACHE_EXPIRATION = 3600;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			self::ID,
			__('Featured Products', Constants::TEXTDOMAIN),
			[
				'description' => __('Display featured products in sidebar with customization options.', Constants::TEXTDOMAIN),
				'customize_selective_refresh' => true,
			]
		);
	}

	/**
	 * Widget form
	 *
	 * Renders widget settings form in WordPress admin.
	 *
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 */
	public function form($instance): void {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$limit = isset($instance['limit']) ? absint($instance['limit']) : 6;
		$columns = isset($instance['columns']) ? absint($instance['columns']) : 3;
		$template = isset($instance['template']) ? esc_attr($instance['template']) : 'grid';
		$show_image = isset($instance['show_image']) ? rest_sanitize_boolean($instance['show_image']) : true;
		$show_price = isset($instance['show_price']) ? rest_sanitize_boolean($instance['show_price']) : true;
		$show_features = isset($instance['show_features']) ? rest_sanitize_boolean($instance['show_features']) : true;
		$show_rating = isset($instance['show_rating']) ? rest_sanitize_boolean($instance['show_rating']) : true;
		$show_cta = isset($instance['show_cta']) ? rest_sanitize_boolean($instance['show_cta']) : true;
		$cta_text = isset($instance['cta_text']) ? sanitize_text_field($instance['cta_text']) : __('View Deal', Constants::TEXTDOMAIN);

		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
				<?php esc_html_e('Title', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
				<?php esc_html_e('Number of products', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" min="1" max="20" value="<?php echo esc_attr($limit); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
				<?php esc_html_e('Columns', Constants::TEXTDOMAIN); ?>:
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('columns')); ?>" name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
				<option value="1" <?php selected($columns, '1'); ?>><?php esc_html_e('1 Column', Constants::TEXTDOMAIN); ?></option>
				<option value="2" <?php selected($columns, '2'); ?>><?php esc_html_e('2 Columns', Constants::TEXTDOMAIN); ?></option>
				<option value="3" <?php selected($columns, '3'); ?>><?php esc_html_e('3 Columns', Constants::TEXTDOMAIN); ?></option>
				<option value="4" <?php selected($columns, '4'); ?>><?php esc_html_e('4 Columns', Constants::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('template')); ?>">
				<?php esc_html_e('Template', Constants::TEXTDOMAIN); ?>:
			</label>
			<select id="<?php echo esc_attr($this->get_field_id('template')); ?>" name="<?php echo esc_attr($this->get_field_name('template')); ?>">
				<option value="grid" <?php selected($template, 'grid'); ?>><?php esc_html_e('Grid', Constants::TEXTDOMAIN); ?></option>
				<option value="list" <?php selected($template, 'list'); ?>><?php esc_html_e('List', Constants::TEXTDOMAIN); ?></option>
				<option value="table" <?php selected($template, 'table'); ?>><?php esc_html_e('Table', Constants::TEXTDOMAIN); ?></option>
			</select>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_image')); ?>" name="<?php echo esc_attr($this->get_field_name('show_image')); ?>" <?php checked($show_image); ?> />
				<?php esc_html_e('Show product image', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_price')); ?>" name="<?php echo esc_attr($this->get_field_name('show_price')); ?>" <?php checked($show_price); ?> />
				<?php esc_html_e('Show price', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_features')); ?>" name="<?php echo esc_attr($this->get_field_name('show_features')); ?>" <?php checked($show_features); ?> />
				<?php esc_html_e('Show product features', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_rating')); ?>" name="<?php echo esc_attr($this->get_field_name('show_rating')); ?>" <?php checked($show_rating); ?> />
				<?php esc_html_e('Show rating', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_cta')); ?>" name="<?php echo esc_attr($this->get_field_name('show_cta')); ?>" <?php checked($show_cta); ?> />
				<?php esc_html_e('Show call-to-action button', Constants::TEXTDOMAIN); ?>
			</label>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('cta_text')); ?>">
				<?php esc_html_e('Call-to-action button text', Constants::TEXTDOMAIN); ?>:
			</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('cta_text')); ?>" name="<?php echo esc_attr($this->get_field_name('cta_text')); ?>" type="text" value="<?php echo esc_attr($cta_text); ?>" />
		</p>
		<?php
	}

	/**
	 * Update widget instance
	 *
	 * Sanitizes and validates widget settings before saving.
	 *
	 * @param array<string, mixed> $new_instance New widget instance settings
	 * @param array<string, mixed> $old_instance Old widget instance settings
	 * @return array<string, mixed> Updated instance
	 * @since 1.0.0
	 */
	public function update($new_instance, $old_instance): array {
		$instance = [];
		$instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
		$instance['limit'] = absint($new_instance['limit'] ?? 6);
		$instance['limit'] = max(1, min(20, $instance['limit']));
		$instance['columns'] = absint($new_instance['columns'] ?? 3);
		$instance['columns'] = max(1, min(4, $instance['columns']));
		$instance['template'] = in_array($new_instance['template'] ?? '', ['grid', 'list', 'table'], true) ? $new_instance['template'] : 'grid';
		$instance['show_image'] = rest_sanitize_boolean($new_instance['show_image'] ?? true);
		$instance['show_price'] = rest_sanitize_boolean($new_instance['show_price'] ?? true);
		$instance['show_features'] = rest_sanitize_boolean($new_instance['show_features'] ?? true);
		$instance['show_rating'] = rest_sanitize_boolean($new_instance['show_rating'] ?? true);
		$instance['show_cta'] = rest_sanitize_boolean($new_instance['show_cta'] ?? true);
		$instance['cta_text'] = sanitize_text_field($new_instance['cta_text'] ?? '');

		// Clear cache when settings change
		$this->clear_widget_cache();

		return $instance;
	}

	/**
	 * Render widget
	 *
	 * Displays the widget on the frontend with caching support.
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Current widget instance settings
	 * @return void
	 * @since 1.0.0
	 */
	public function widget($args, $instance): void {
		try {
			$cache_key = $this->get_cache_key($instance);

			// Try to get cached output
			$cached_output = wp_cache_get($cache_key, 'aps_widgets');
			if ($cached_output !== false) {
				echo $cached_output;
				return;
			}

			// Generate widget content
			$widget_content = $this->generate_widget_content($args, $instance);

			// Cache output
			wp_cache_set($cache_key, $widget_content, 'aps_widgets', self::CACHE_EXPIRATION);

			echo $widget_content;
		} catch (\Throwable $e) {
			// Log error but don't break the page
			error_log(sprintf('FeaturedWidget error: %s', $e->getMessage()));
			echo '<!-- FeaturedWidget error occurred -->';
		}
	}

	/**
	 * Generate widget content
	 *
	 * Generates HTML content for the widget.
	 *
	 * @param array<string, mixed> $args Widget display arguments
	 * @param array<string, mixed> $instance Widget instance settings
	 * @return string Generated HTML content
	 * @since 1.0.0
	 */
	private function generate_widget_content(array $args, array $instance): string {
		$atts = [
			'featured'      => true,
			'limit'         => intval($instance['limit'] ?? 6),
			'columns'       => absint($instance['columns'] ?? 3),
			'template'      => $instance['template'] ?? 'grid',
			'show_image'    => rest_sanitize_boolean($instance['show_image'] ?? true),
			'show_price'    => rest_sanitize_boolean($instance['show_price'] ?? true),
			'show_features' => rest_sanitize_boolean($instance['show_features'] ?? true),
			'show_rating'   => rest_sanitize_boolean($instance['show_rating'] ?? true),
			'show_cta'      => rest_sanitize_boolean($instance['show_cta'] ?? true),
			'cta_text'      => $instance['cta_text'] ?? __('View Deal', Constants::TEXTDOMAIN),
		];

		$products_args = [
			'post_type'      => Constants::CPT_PRODUCT,
			'post_status'    => 'publish',
			'posts_per_page' => $atts['limit'],
			'paged'         => 1,
			'fields'         => 'ids', // Only get IDs for better performance
			'meta_query'    => [
				[
					'key'     => 'product_featured',
					'value'   => '1',
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
			'orderby'              => [
				'date'       => 'DESC',
				'menu_order' => 'DESC',
			],
		];

		$product_ids = get_posts($products_args);
		$products = [];

		// Hydrate products with full objects
		if (!empty($product_ids)) {
			foreach ($product_ids as $product_id) {
				$product = get_post($product_id);
				if ($product) {
					$products[] = $product;
				}
			}
		}

		// Batch load product metadata to prevent N+1 queries
		$product_metadata = [];
		if (!empty($products)) {
			global $wpdb;
			$ids_in = implode(',', array_map('intval', wp_list_pluck($products, 'ID')));
			$metadata = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN (%s)",
					$ids_in
				)
			);

			foreach ($metadata as $meta) {
				$product_metadata[$meta->post_id][$meta->meta_key] = $meta->meta_value;
			}
		}

		ob_start();
		echo $args['before_widget'];

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Featured Products', Constants::TEXTDOMAIN) : $instance['title'], $instance, $this->id_base);

		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$template_class = 'aps-products-template aps-grid-' . $atts['columns'] . '-cols';
		$template_class .= $atts['template'] === 'list' ? ' aps-products-list' : ' aps-products-grid';
		$template_class .= $atts['template'] === 'table' ? ' aps-products-table' : '';

		echo '<div class="' . esc_attr($template_class) . '" role="list" aria-label="' . esc_attr__('Featured products', Constants::TEXTDOMAIN) . '">';

		if (!empty($products)) {
			foreach ($products as $product) {
				$this->render_product($product, $atts, $product_metadata[$product->ID] ?? []);
			}
		} else {
			echo '<p class="aps-widget-empty-message">' . esc_html__('No featured products found', Constants::TEXTDOMAIN) . '</p>';
		}

		echo '</div>';
		echo $args['after_widget'];

		return ob_get_clean();
	}

	/**
	 * Render product card
	 *
	 * Renders a single product card with all specified display options.
	 *
	 * @param \WP_Post $product Post object
	 * @param array<string, mixed> $atts Display attributes
	 * @param array<string, mixed> $metadata Preloaded product metadata
	 * @return void
	 * @since 1.0.0
	 */
	private function render_product(\WP_Post $product, array $atts, array $metadata): void {
		$product_id = $product->ID;
		$title = get_the_title($product);
		$price = $metadata['product_price'] ?? 0;
		$sale_price = $metadata['product_sale_price'] ?? 0;
		$rating = $metadata['product_rating'] ?? 0;
		$features = $metadata['product_features'] ?? '';
		$affiliate_url = $metadata['product_affiliate_url'] ?? '';

		// Generate price HTML
		if ($sale_price && floatval($sale_price) > 0) {
			$price_html = '<span class="aps-price-original">' . number_format(floatval($price), 2) . '</span> ';
			$price_html .= '<span class="aps-price-sale">' . number_format(floatval($sale_price), 2) . '</span>';
		} else {
			$price_html = '<span class="aps-price-current">' . number_format(floatval($price), 2) . '</span>';
		}

		echo '<div class="aps-widget-product-item" role="listitem">';

		if ($atts['show_image'] && has_post_thumbnail($product)) {
			echo '<div class="aps-widget-product-image">';
			echo '<a href="' . esc_url($affiliate_url) . '" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr(sprintf(__('View %s', Constants::TEXTDOMAIN), $title)) . '">';
			the_post_thumbnail($product, 'medium', ['loading' => 'lazy']);
			echo '</a>';
			echo '</div>';
		}

		echo '<div class="aps-widget-product-content">';

		if ($atts['show_features'] && !empty($features)) {
			$features_array = is_array($features) ? $features : [$features];
			echo '<ul class="aps-widget-product-features" role="list">';
			foreach (array_slice($features_array, 0, 5) as $index => $feature) {
				echo '<li class="aps-widget-feature-item" role="listitem">' . esc_html($feature) . '</li>';
			}
			echo '</ul>';
		}

		echo '<h3 class="aps-widget-product-title">';
		echo '<a href="' . esc_url($affiliate_url) . '" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr(sprintf(__('View %s', Constants::TEXTDOMAIN), $title)) . '">';
		echo esc_html($title);
		echo '</a>';
		echo '</h3>';

		if ($atts['show_rating'] && $rating) {
			echo '<div class="aps-widget-product-rating">';
			echo '<span class="aps-stars" style="--rating: ' . esc_attr($rating) . ';" aria-label="' . esc_attr(sprintf(__('%d out of 5 stars', Constants::TEXTDOMAIN), $rating)) . '">★★★★★</span>';
			echo '<span class="aps-rating-value">' . number_format(floatval($rating), 1) . '</span>';
			echo '</div>';
		}

		if ($atts['show_price']) {
			echo '<div class="aps-widget-product-price">' . $price_html . '</div>';
		}

		if ($atts['show_cta'] && $affiliate_url) {
			echo '<div class="aps-widget-product-cta">';
			$cta_text = $atts['cta_text'] ?? __('View Deal', Constants::TEXTDOMAIN);
			echo '<a href="' . esc_url($affiliate_url) . '" class="aps-cta-button" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr($cta_text) . '">' . esc_html($cta_text) . '</a>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Get cache key for widget
	 *
	 * Generates a unique cache key based on widget settings.
	 *
	 * @param array<string, mixed> $instance Widget instance settings
	 * @return string Cache key
	 * @since 1.0.0
	 */
	private function get_cache_key(array $instance): string {
		$hash = md5(serialize($instance));
		return self::CACHE_KEY_PREFIX . $hash;
	}

	/**
	 * Clear widget cache
	 *
	 * Clears all cached output for this widget type.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function clear_widget_cache(): void {
		wp_cache_flush_group('aps_widgets');
	}
}

/**
 * Widgets Register Class
 *
 * Registers all plugin widgets with WordPress.
 * Handles widget initialization, shortcode support, and cache invalidation.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class Widgets {
	/**
	 * Register widgets
	 *
	 * Registers all plugin widgets with WordPress and sets up hooks.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action widgets_init
	 */
	public function register(): void {
		// Register all widgets
		register_widget(CategoriesWidget::class);
		register_widget(TagsWidget::class);
		register_widget(FeaturedWidget::class);

		// Register shortcodes
		add_shortcode('aps_tags_cloud', [$this, 'render_tags_shortcode']);
		add_shortcode('aps_featured_products', [$this, 'render_featured_shortcode']);

		// Clear widget caches when products are updated
		add_action('save_post', [$this, 'clear_product_widgets_cache']);
		add_action('delete_post', [$this, 'clear_product_widgets_cache']);
		add_action('updated_post_meta', [$this, 'clear_product_widgets_cache']);
	}

	/**
	 * Render tags cloud shortcode
	 *
	 * Renders product tags cloud via shortcode.
	 * Supports same attributes as widget settings.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string Rendered HTML
	 * @since 1.0.0
	 *
	 * @shortcode aps_tags_cloud
	 */
	public function render_tags_shortcode(array $atts = []): string {
		$atts = shortcode_atts([
			'title'        => __('Product Tags', Constants::TEXTDOMAIN),
			'limit'        => 20,
			'orderby'      => 'count',
			'order'        => 'DESC',
			'smallest'     => 8,
			'largest'      => 32,
			'unit'         => 'pt',
			'show_count'   => 1,
			'empty_message' => __('No tags found', Constants::TEXTDOMAIN),
			'taxonomy'     => Constants::TAX_TAG,
		], $atts);

		// Sanitize attributes
		$atts['limit'] = absint($atts['limit']);
		$atts['limit'] = max(1, min(100, $atts['limit']));
		$atts['orderby'] = in_array($atts['orderby'], ['name', 'count', 'id'], true) ? $atts['orderby'] : 'count';
		$atts['order'] = in_array($atts['order'], ['ASC', 'DESC'], true) ? $atts['order'] : 'DESC';
		$atts['smallest'] = absint($atts['smallest']);
		$atts['smallest'] = max(6, min(24, $atts['smallest']));
		$atts['largest'] = absint($atts['largest']);
		$atts['largest'] = max(12, min(96, $atts['largest']));
		$atts['unit'] = in_array($atts['unit'], ['pt', 'px', 'em', 'rem'], true) ? $atts['unit'] : 'pt';
		$atts['show_count'] = rest_sanitize_boolean($atts['show_count']);
		$atts['empty_message'] = sanitize_text_field($atts['empty_message']);
		$atts['taxonomy'] = esc_attr($atts['taxonomy']);

		// Generate tag cloud
		$term_args = [
			'taxonomy'   => $atts['taxonomy'],
			'orderby'    => $atts['orderby'],
			'order'      => $atts['order'],
			'number'     => $atts['limit'],
			'hide_empty' => false,
			'fields'      => 'ids',
		];

		$tag_ids = get_terms($term_args);
		$tags = [];

		if (!empty($tag_ids) && !is_wp_error($tag_ids)) {
			foreach ($tag_ids as $tag_id) {
				$tag = get_term($tag_id, $atts['taxonomy']);
				if ($tag && !is_wp_error($tag)) {
					$tags[] = $tag;
				}
			}
		}

		ob_start();

		if (!empty($atts['title'])) {
			echo '<h3 class="aps-shortcode-title">' . esc_html($atts['title']) . '</h3>';
		}

		echo '<div class="aps-tags-cloud aps-tags-cloud-shortcode" role="list" aria-label="' . esc_attr__('Product tags', Constants::TEXTDOMAIN) . '">';

		if (!empty($tags)) {
			$min_count = PHP_INT_MAX;
			$max_count = 0;

			foreach ($tags as $tag) {
				$count = absint($tag->count);
				$min_count = min($min_count, $count);
				$max_count = max($max_count, $count);

				$font_size = $this->calculate_tag_font_size($count, $min_count, $max_count, $atts['smallest'], $atts['largest'], $atts['unit']);
				$font_style = 'font-size: ' . number_format($font_size, 1) . 'px;';
				$color = get_term_meta($tag->term_id, 'tag_color', true);
				$link = get_term_link($tag);

				if (is_wp_error($link)) {
					continue;
				}

				$color_style = $color ? 'color: ' . esc_attr($color) . ';' : '';

				echo '<span class="aps-tag-link" style="' . esc_attr($font_style . ' ' . $color_style) . '" role="listitem">';
				echo '<a href="' . esc_url($link) . '" rel="tag nofollow" aria-label="' . esc_attr(sprintf(__('%s tag with %d products', Constants::TEXTDOMAIN), $tag->name, $count)) . '">';
				echo esc_html($tag->name);
				echo '</a>';

				if ($atts['show_count']) {
					echo '<span class="aps-tag-count" aria-hidden="true">(' . absint($count) . ')</span>';
				}

				echo '</span>';
			}
		} else {
			echo '<p class="aps-widget-empty-message">' . esc_html($atts['empty_message']) . '</p>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render featured products shortcode
	 *
	 * Renders featured products via shortcode.
	 * Supports same attributes as widget settings.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes
	 * @return string Rendered HTML
	 * @since 1.0.0
	 *
	 * @shortcode aps_featured_products
	 */
	public function render_featured_shortcode(array $atts = []): string {
		$atts = shortcode_atts([
			'title'        => __('Featured Products', Constants::TEXTDOMAIN),
			'limit'        => 6,
			'columns'      => 3,
			'template'     => 'grid',
			'show_image'   => 1,
			'show_price'   => 1,
			'show_features' => 1,
			'show_rating'  => 1,
			'show_cta'     => 1,
			'cta_text'      => __('View Deal', Constants::TEXTDOMAIN),
		], $atts);

		// Sanitize attributes
		$atts['limit'] = absint($atts['limit']);
		$atts['limit'] = max(1, min(20, $atts['limit']));
		$atts['columns'] = absint($atts['columns']);
		$atts['columns'] = max(1, min(4, $atts['columns']));
		$atts['template'] = in_array($atts['template'], ['grid', 'list', 'table'], true) ? $atts['template'] : 'grid';
		$atts['show_image'] = rest_sanitize_boolean($atts['show_image']);
		$atts['show_price'] = rest_sanitize_boolean($atts['show_price']);
		$atts['show_features'] = rest_sanitize_boolean($atts['show_features']);
		$atts['show_rating'] = rest_sanitize_boolean($atts['show_rating']);
		$atts['show_cta'] = rest_sanitize_boolean($atts['show_cta']);
		$atts['cta_text'] = sanitize_text_field($atts['cta_text']);

		$products_args = [
			'post_type'      => Constants::CPT_PRODUCT,
			'post_status'    => 'publish',
			'posts_per_page' => $atts['limit'],
			'paged'         => 1,
			'fields'         => 'ids',
			'meta_query'     => [
				[
					'key'     => 'product_featured',
					'value'   => '1',
					'compare' => '=',
					'type'    => 'NUMERIC',
				],
			],
			'orderby' => ['date' => 'DESC', 'menu_order' => 'DESC'],
		];

		$product_ids = get_posts($products_args);
		$products = [];

		if (!empty($product_ids)) {
			foreach ($product_ids as $product_id) {
				$product = get_post($product_id);
				if ($product) {
					$products[] = $product;
				}
			}
		}

		// Batch load metadata
		$product_metadata = [];
		if (!empty($products)) {
			global $wpdb;
			$ids_in = implode(',', array_map('intval', wp_list_pluck($products, 'ID')));
			$metadata = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN (%s)",
					$ids_in
				)
			);

			foreach ($metadata as $meta) {
				$product_metadata[$meta->post_id][$meta->meta_key] = $meta->meta_value;
			}
		}

		ob_start();

		if (!empty($atts['title'])) {
			echo '<h3 class="aps-shortcode-title">' . esc_html($atts['title']) . '</h3>';
		}

		$template_class = 'aps-products-template aps-grid-' . $atts['columns'] . '-cols aps-products-shortcode';
		$template_class .= $atts['template'] === 'list' ? ' aps-products-list' : ' aps-products-grid';
		$template_class .= $atts['template'] === 'table' ? ' aps-products-table' : '';

		echo '<div class="' . esc_attr($template_class) . '" role="list" aria-label="' . esc_attr__('Featured products', Constants::TEXTDOMAIN) . '">';

		if (!empty($products)) {
			foreach ($products as $product) {
				$this->render_shortcode_product($product, $atts, $product_metadata[$product->ID] ?? []);
			}
		} else {
			echo '<p class="aps-widget-empty-message">' . esc_html__('No featured products found', Constants::TEXTDOMAIN) . '</p>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Calculate tag font size for shortcode
	 *
	 * Calculates font size using smoothed distribution.
	 *
	 * @param int $count Tag count
	 * @param int $min_count Minimum count
	 * @param int $max_count Maximum count
	 * @param int $smallest Minimum font size
	 * @param int $largest Maximum font size
	 * @param string $unit Unit (pt, px, em, rem)
	 * @return float Calculated font size in pixels
	 * @since 1.0.0
	 */
	private function calculate_tag_font_size(int $count, int $min_count, int $max_count, int $smallest, int $largest, string $unit): float {
		if ($max_count === $min_count) {
			$font_size = $smallest;
		} else {
			// Apply logarithmic smoothing for better distribution
			$normalized = ($count - $min_count) / ($max_count - $min_count);
			$smoothed = pow($normalized, 0.5); // Square root smoothing
			$font_size = $smallest + $smoothed * ($largest - $smallest);
		}

		// Convert to pixels
		$font_size_px = match($unit) {
			'pt' => $font_size * 1.33,
			'px' => $font_size,
			'em' => $font_size * 16,
			'rem' => $font_size * 16,
			default => $font_size * 16,
		};

		return $font_size_px;
	}

	/**
	 * Render product card for shortcode
	 *
	 * Renders a single product card for shortcode display.
	 *
	 * @param \WP_Post $product Post object
	 * @param array<string, mixed> $atts Display attributes
	 * @param array<string, mixed> $metadata Preloaded product metadata
	 * @return void
	 * @since 1.0.0
	 */
	private function render_shortcode_product(\WP_Post $product, array $atts, array $metadata): void {
		$product_id = $product->ID;
		$title = get_the_title($product);
		$price = $metadata['product_price'] ?? 0;
		$sale_price = $metadata['product_sale_price'] ?? 0;
		$rating = $metadata['product_rating'] ?? 0;
		$features = $metadata['product_features'] ?? '';
		$affiliate_url = $metadata['product_affiliate_url'] ?? '';

		// Generate price HTML
		if ($sale_price && floatval($sale_price) > 0) {
			$price_html = '<span class="aps-price-original">' . number_format(floatval($price), 2) . '</span> ';
			$price_html .= '<span class="aps-price-sale">' . number_format(floatval($sale_price), 2) . '</span>';
		} else {
			$price_html = '<span class="aps-price-current">' . number_format(floatval($price), 2) . '</span>';
		}

		echo '<div class="aps-widget-product-item" role="listitem">';

		if ($atts['show_image'] && has_post_thumbnail($product)) {
			echo '<div class="aps-widget-product-image">';
			echo '<a href="' . esc_url($affiliate_url) . '" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr(sprintf(__('View %s', Constants::TEXTDOMAIN), $title)) . '">';
			the_post_thumbnail($product, 'medium', ['loading' => 'lazy']);
			echo '</a>';
			echo '</div>';
		}

		echo '<div class="aps-widget-product-content">';

		if ($atts['show_features'] && !empty($features)) {
			$features_array = is_array($features) ? $features : [$features];
			echo '<ul class="aps-widget-product-features" role="list">';
			foreach (array_slice($features_array, 0, 5) as $index => $feature) {
				echo '<li class="aps-widget-feature-item" role="listitem">' . esc_html($feature) . '</li>';
			}
			echo '</ul>';
		}

		echo '<h3 class="aps-widget-product-title">';
		echo '<a href="' . esc_url($affiliate_url) . '" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr(sprintf(__('View %s', Constants::TEXTDOMAIN), $title)) . '">';
		echo esc_html($title);
		echo '</a>';
		echo '</h3>';

		if ($atts['show_rating'] && $rating) {
			echo '<div class="aps-widget-product-rating">';
			echo '<span class="aps-stars" style="--rating: ' . esc_attr($rating) . ';" aria-label="' . esc_attr(sprintf(__('%d out of 5 stars', Constants::TEXTDOMAIN), $rating)) . '">★★★★★</span>';
			echo '<span class="aps-rating-value">' . number_format(floatval($rating), 1) . '</span>';
			echo '</div>';
		}

		if ($atts['show_price']) {
			echo '<div class="aps-widget-product-price">' . $price_html . '</div>';
		}

		if ($atts['show_cta'] && $affiliate_url) {
			echo '<div class="aps-widget-product-cta">';
			$cta_text = $atts['cta_text'] ?? __('View Deal', Constants::TEXTDOMAIN);
			echo '<a href="' . esc_url($affiliate_url) . '" class="aps-cta-button" rel="nofollow sponsored" target="_blank" aria-label="' . esc_attr($cta_text) . '">' . esc_html($cta_text) . '</a>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Clear product widgets cache
	 *
	 * Clears all widget caches when products are updated.
	 *
	 * @param int $post_id Post ID
	 * @return void
	 * @since 1.0.0
	 */
	public function clear_product_widgets_cache(int $post_id): void {
		// Only clear for product post types
		if (get_post_type($post_id) === Constants::CPT_PRODUCT) {
			wp_cache_flush_group('aps_widgets');
		}
	}
}
