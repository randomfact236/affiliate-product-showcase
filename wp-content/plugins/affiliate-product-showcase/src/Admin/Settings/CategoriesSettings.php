<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Settings;

/**
 * Categories Settings Section
 *
 * Handles category settings including hierarchy, display style, and featured products.
 *
 * @package AffiliateProductShowcase\Admin\Settings
 * @since 1.0.0
 */
final class CategoriesSettings extends AbstractSettingsSection {
	
	const SECTION_ID = 'affiliate_product_showcase_categories';
	const SECTION_TITLE = 'Category Settings';
	
	/**
	 * Get default values for this section
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'default_category' => 0,
			'enable_category_hierarchy' => true,
			'category_display_style' => 'grid',
			'category_products_per_page' => 12,
			'category_default_sort' => 'date',
			'category_default_sort_order' => 'DESC',
			'show_category_description' => true,
			'show_category_image' => true,
			'show_category_count' => true,
			'enable_category_featured_products' => false,
			'category_featured_products_limit' => 4,
			'enable_empty_category_display' => false,
		];
	}
	
	/**
	 * Register section and fields
	 *
	 * @return void
	 */
	public function register_section_and_fields(): void {
		\add_settings_section(
			self::SECTION_ID,
			__('Category Settings', 'affiliate-product-showcase'),
			[$this, 'render_section_description'],
			'affiliate-product-showcase',
			['data-section' => 'categories']
		);
		
		\add_settings_field(
			'default_category',
			__('Default Category', 'affiliate-product-showcase'),
			[$this, 'render_default_category_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'default_category']
		);
		
		\add_settings_field(
			'enable_category_hierarchy',
			__('Enable Category Hierarchy', 'affiliate-product-showcase'),
			[$this, 'render_enable_category_hierarchy_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_category_hierarchy']
		);
		
		\add_settings_field(
			'category_display_style',
			__('Category Display Style', 'affiliate-product-showcase'),
			[$this, 'render_category_display_style_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_display_style']
		);
		
		\add_settings_field(
			'category_products_per_page',
			__('Category Products Per Page', 'affiliate-product-showcase'),
			[$this, 'render_category_products_per_page_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_products_per_page']
		);
		
		\add_settings_field(
			'category_default_sort',
			__('Category Default Sort', 'affiliate-product-showcase'),
			[$this, 'render_category_default_sort_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_default_sort']
		);
		
		\add_settings_field(
			'category_default_sort_order',
			__('Category Default Sort Order', 'affiliate-product-showcase'),
			[$this, 'render_category_default_sort_order_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_default_sort_order']
		);
		
		\add_settings_field(
			'show_category_description',
			__('Show Category Description', 'affiliate-product-showcase'),
			[$this, 'render_show_category_description_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_category_description']
		);
		
		\add_settings_field(
			'show_category_image',
			__('Show Category Image', 'affiliate-product-showcase'),
			[$this, 'render_show_category_image_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_category_image']
		);
		
		\add_settings_field(
			'show_category_count',
			__('Show Category Count', 'affiliate-product-showcase'),
			[$this, 'render_show_category_count_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'show_category_count']
		);
		
		\add_settings_field(
			'enable_category_featured_products',
			__('Enable Category Featured Products', 'affiliate-product-showcase'),
			[$this, 'render_enable_category_featured_products_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_category_featured_products']
		);
		
		\add_settings_field(
			'category_featured_products_limit',
			__('Category Featured Products Limit', 'affiliate-product-showcase'),
			[$this, 'render_category_featured_products_limit_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'category_featured_products_limit']
		);
		
		\add_settings_field(
			'enable_empty_category_display',
			__('Enable Empty Category Display', 'affiliate-product-showcase'),
			[$this, 'render_enable_empty_category_display_field'],
			'affiliate-product-showcase',
			self::SECTION_ID,
			['label_for' => 'enable_empty_category_display']
		);
	}
	
	/**
	 * Sanitize section options
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_options(array $input): array {
		$sanitized = [];
		
		$sanitized['default_category'] = intval($input['default_category'] ?? 0);
		$sanitized['enable_category_hierarchy'] = isset($input['enable_category_hierarchy']);
		$sanitized['category_display_style'] = in_array($input['category_display_style'] ?? 'grid', ['grid', 'list', 'compact']) ? $input['category_display_style'] : 'grid';
		$sanitized['category_products_per_page'] = intval($input['category_products_per_page'] ?? 12);
		$sanitized['category_products_per_page'] = max(6, min(48, $sanitized['category_products_per_page']));
		$sanitized['category_default_sort'] = in_array($input['category_default_sort'] ?? 'date', ['name', 'price', 'date', 'popularity', 'random']) ? $input['category_default_sort'] : 'date';
		$sanitized['category_default_sort_order'] = in_array($input['category_default_sort_order'] ?? 'DESC', ['ASC', 'DESC']) ? $input['category_default_sort_order'] : 'DESC';
		$sanitized['show_category_description'] = isset($input['show_category_description']);
		$sanitized['show_category_image'] = isset($input['show_category_image']);
		$sanitized['show_category_count'] = isset($input['show_category_count']);
		$sanitized['enable_category_featured_products'] = isset($input['enable_category_featured_products']);
		$sanitized['category_featured_products_limit'] = intval($input['category_featured_products_limit'] ?? 4);
		$sanitized['category_featured_products_limit'] = max(1, min(8, $sanitized['category_featured_products_limit']));
		$sanitized['enable_empty_category_display'] = isset($input['enable_empty_category_display']);
		
		return $sanitized;
	}
	
	/**
	 * Render section description
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__('Configure category display settings, hierarchy, and featured products.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render default category field
	 *
	 * @return void
	 */
	public function render_default_category_field(): void {
		$settings = $this->get_settings();
		$categories = get_terms(['taxonomy' => 'aps_category', 'hide_empty' => false]);
		
		echo '<select name="' . esc_attr($this->option_name) . '[default_category]" id="default-category" aria-describedby="default-category-description">';
		echo '<option value="0">' . esc_html__('None', 'affiliate-product-showcase') . '</option>';
		
		foreach ($categories as $category) {
			$selected = selected($settings['default_category'], $category->term_id, false);
			echo '<option value="' . esc_attr($category->term_id) . '" ' . $selected . '>';
			echo esc_html($category->name);
			echo '</option>';
		}
		
		echo '</select>';
		echo '<p class="description" id="default-category-description">' . esc_html__('Default category for unassigned products.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable category hierarchy field
	 *
	 * @return void
	 */
	public function render_enable_category_hierarchy_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_category_hierarchy'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_category_hierarchy]" value="1" ' . $checked . ' aria-describedby="enable-category-hierarchy-description"> ';
		echo esc_html__('Enable category hierarchy (parent/child)', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="enable-category-hierarchy-description">' . esc_html__('Allow categories to have parent-child relationships.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category display style field
	 *
	 * @return void
	 */
	public function render_category_display_style_field(): void {
		$settings = $this->get_settings();
		$styles = [
			'grid' => __('Grid', 'affiliate-product-showcase'),
			'list' => __('List', 'affiliate-product-showcase'),
			'compact' => __('Compact', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[category_display_style]" id="category-display-style" aria-describedby="category-display-style-description">';
		foreach ($styles as $value => $label) {
			$selected = selected($settings['category_display_style'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description" id="category-display-style-description">' . esc_html__('Choose how categories are displayed on the frontend.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category products per page field
	 *
	 * @return void
	 */
	public function render_category_products_per_page_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[category_products_per_page]" id="category-products-per-page" aria-describedby="category-products-per-page-description">';
		foreach ([6, 12, 18, 24, 36, 48] as $value) {
			$selected = selected($settings['category_products_per_page'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description" id="category-products-per-page-description">' . esc_html__('Number of products to display per category page.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category default sort field
	 *
	 * @return void
	 */
	public function render_category_default_sort_field(): void {
		$settings = $this->get_settings();
		$options = [
			'name' => __('Name', 'affiliate-product-showcase'),
			'price' => __('Price', 'affiliate-product-showcase'),
			'date' => __('Date', 'affiliate-product-showcase'),
			'popularity' => __('Popularity', 'affiliate-product-showcase'),
			'random' => __('Random', 'affiliate-product-showcase'),
		];
		
		echo '<select name="' . esc_attr($this->option_name) . '[category_default_sort]" id="category-default-sort" aria-describedby="category-default-sort-description">';
		foreach ($options as $value => $label) {
			$selected = selected($settings['category_default_sort'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
		echo '<p class="description" id="category-default-sort-description">' . esc_html__('Default sorting method for category products.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category default sort order field
	 *
	 * @return void
	 */
	public function render_category_default_sort_order_field(): void {
		$settings = $this->get_settings();
		$options = [
			'ASC' => __('Ascending', 'affiliate-product-showcase'),
			'DESC' => __('Descending', 'affiliate-product-showcase'),
		];
		
		foreach ($options as $value => $label) {
			$checked = checked($settings['category_default_sort_order'], $value, false);
			echo '<label>';
			echo '<input type="radio" name="' . esc_attr($this->option_name) . '[category_default_sort_order]" value="' . esc_attr($value) . '" ' . $checked . ' aria-describedby="category-default-sort-order-description"> ';
			echo esc_html($label);
			echo '</label><br>';
		}
		echo '<p class="description" id="category-default-sort-order-description">' . esc_html__('Sort order for category products (ascending or descending).', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render show category description field
	 *
	 * @return void
	 */
	public function render_show_category_description_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_category_description'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_category_description]" value="1" ' . $checked . ' aria-describedby="show-category-description-description"> ';
		echo esc_html__('Show category description', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="show-category-description-description">' . esc_html__('Display category description on category pages.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render show category image field
	 *
	 * @return void
	 */
	public function render_show_category_image_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_category_image'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_category_image]" value="1" ' . $checked . ' aria-describedby="show-category-image-description"> ';
		echo esc_html__('Show category image', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="show-category-image-description">' . esc_html__('Display category image on category pages.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render show category count field
	 *
	 * @return void
	 */
	public function render_show_category_count_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['show_category_count'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[show_category_count]" value="1" ' . $checked . ' aria-describedby="show-category-count-description"> ';
		echo esc_html__('Show product count per category', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="show-category-count-description">' . esc_html__('Display number of products in each category.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable category featured products field
	 *
	 * @return void
	 */
	public function render_enable_category_featured_products_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_category_featured_products'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_category_featured_products]" value="1" ' . $checked . ' aria-describedby="enable-category-featured-products-description"> ';
		echo esc_html__('Enable featured products per category', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="enable-category-featured-products-description">' . esc_html__('Show featured products at the top of category pages.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render category featured products limit field
	 *
	 * @return void
	 */
	public function render_category_featured_products_limit_field(): void {
		$settings = $this->get_settings();
		echo '<select name="' . esc_attr($this->option_name) . '[category_featured_products_limit]" id="category-featured-products-limit" aria-describedby="category-featured-products-limit-description">';
		foreach ([1, 2, 3, 4, 6, 8] as $value) {
			$selected = selected($settings['category_featured_products_limit'], $value, false);
			echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($value) . '</option>';
		}
		echo '</select>';
		echo '<p class="description" id="category-featured-products-limit-description">' . esc_html__('Number of featured products to show.', 'affiliate-product-showcase') . '</p>';
	}
	
	/**
	 * Render enable empty category display field
	 *
	 * @return void
	 */
	public function render_enable_empty_category_display_field(): void {
		$settings = $this->get_settings();
		$checked = checked($settings['enable_empty_category_display'], true, false);
		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr($this->option_name) . '[enable_empty_category_display]" value="1" ' . $checked . ' aria-describedby="enable-empty-category-display-description"> ';
		echo esc_html__('Display empty categories', 'affiliate-product-showcase');
		echo '</label>';
		echo '<p class="description" id="enable-empty-category-display-description">' . esc_html__('Show categories even if they have no products.', 'affiliate-product-showcase') . '</p>';
	}
}