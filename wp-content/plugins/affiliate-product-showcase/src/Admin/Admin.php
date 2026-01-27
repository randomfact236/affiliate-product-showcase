<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Admin\CategoryFields;
use AffiliateProductShowcase\Admin\TagFields;
use AffiliateProductShowcase\Admin\RibbonFields;
use AffiliateProductShowcase\Admin\Settings;

final class Admin {
	private Settings $settings;
	private ProductFormHandler $form_handler;
	private Menu $menu;
	private CategoryFields $category_fields;
	private TagFields $tag_fields;
	private RibbonFields $ribbon_fields;

	public function __construct(
		private Assets $assets,
		private ProductService $product_service,
		private Headers $headers,
		Menu $menu,
		ProductFormHandler $form_handler,
		RibbonFields $ribbon_fields,
		Settings $settings
	) {
		$this->settings = $settings;
		$this->form_handler = $form_handler;
		$this->menu = $menu;
		// REMOVED: ProductTableUI - using native WordPress UI only
		// $this->product_table_ui = new ProductTableUI();
		$this->category_fields = new CategoryFields();
		$this->tag_fields = new TagFields();
		$this->ribbon_fields = $ribbon_fields;
	}

	public function init(): void {
		// Initialize settings
		$this->settings->init();
		
		// ProductTableUI removed - using native WordPress UI only
		// add_action('admin_notices', [$this, 'render_product_table_on_products_page'], 10);
		
		// Initialize category components (WordPress native + custom enhancements)
		$this->category_fields->init();
		
		// Initialize tag components (WordPress native + custom enhancements)
		$this->tag_fields->init();
		
		// Initialize ribbon components (WordPress native + custom enhancements)
		$this->ribbon_fields->init();
		
		$this->headers->init();
	}

	/**
	 * Render product table only on products page
	 *
	 * This prevents product table from appearing on other admin pages
	 * like categories, tags, etc.
	 *
	 * NOTE: ProductTableUI has been removed.
	 * Products now use native WordPress UI only via ProductsTable (WP_List_Table).
	 *
	 * @return void
	 * @since 1.0.0
	 * @deprecated 1.0.0 Use native WordPress UI instead
	 */
	public function render_product_table_on_products_page(): void {
		// REMOVED: ProductTableUI custom UI no longer used
		// Products page now uses native WordPress interface via ProductsTable
		return;
		
		// $screen = get_current_screen();
		// Only render on products listing page (edit.php?post_type=aps_product)
		// if ($screen && $screen->id === 'edit-aps_product') {
		//	$this->product_table_ui->render();
		// }
	}

	public function enqueue_admin_assets(string $hook): void {
		if (false !== strpos($hook, Constants::SLUG)) {
			$this->assets->enqueue_admin();
		}
	}
}