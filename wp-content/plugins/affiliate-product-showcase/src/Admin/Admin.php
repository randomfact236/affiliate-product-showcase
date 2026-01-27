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
use AffiliateProductShowcase\Admin\ProductFilters;
use AffiliateProductShowcase\Admin\Settings;

final class Admin {
	private Settings $settings;
	private ProductFormHandler $form_handler;
	private Menu $menu;
	private CategoryFields $category_fields;
	private TagFields $tag_fields;
	private RibbonFields $ribbon_fields;
	private ProductFilters $product_filters;

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
		$this->category_fields = new CategoryFields();
		$this->tag_fields = new TagFields();
		$this->ribbon_fields = $ribbon_fields;
		$this->product_filters = new ProductFilters();
	}

	public function init(): void {
		// Initialize settings
		$this->settings->init();
		
		// Initialize category components (WordPress native + custom enhancements)
		$this->category_fields->init();
		
		// Initialize tag components (WordPress native + custom enhancements)
		$this->tag_fields->init();
		
		// Initialize ribbon components (WordPress native + custom enhancements)
		$this->ribbon_fields->init();
		
		// Initialize product filters (WordPress default table extensions)
		$this->product_filters->init();
		
		$this->headers->init();
	}

	public function enqueue_admin_assets(string $hook): void {
		if (false !== strpos($hook, Constants::SLUG)) {
			$this->assets->enqueue_admin();
		}
	}
}