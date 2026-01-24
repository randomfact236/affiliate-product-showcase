<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Plugin\Constants;
use AffiliateProductShowcase\Security\Headers;
use AffiliateProductShowcase\Services\ProductService;

final class Admin {
	private Settings $settings;
	private MetaBoxes $metaboxes;
	private ProductFormHandler $form_handler;
	private Menu $menu;
	private ProductTableUI $product_table_ui;
	private CategoryFields $category_fields;
	private TagFields $tag_fields;

	public function __construct(
		private Assets $assets,
		private ProductService $product_service,
		private Headers $headers,
		Menu $menu,
		ProductFormHandler $form_handler
	) {
		$this->settings = new Settings();
		$this->metaboxes = new MetaBoxes( $this->product_service );
		$this->form_handler = $form_handler;
		$this->menu = $menu;
		$this->product_table_ui = new ProductTableUI();
		$this->category_fields = new CategoryFields();
		$this->tag_fields = new TagFields();
	}

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'add_meta_boxes', [ $this->metaboxes, 'register' ] );
		add_action( 'save_post', [ $this->metaboxes, 'save_meta' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'render_product_table_on_products_page' ], 10 );
		
		// Initialize category components (WordPress native + custom enhancements)
		$this->category_fields->init();
		
		// Initialize tag components (WordPress native + custom enhancements)
		$this->tag_fields->init();
		
		$this->headers->init();
	}

	/**
	 * Render product table only on products page
	 *
	 * This prevents the product table from appearing on other admin pages
	 * like categories, tags, etc.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function render_product_table_on_products_page(): void {
		$screen = get_current_screen();
		
		// Only render on our custom products page
		if ( $screen && $screen->id === 'affiliate-product-showcase_page_aps-products' ) {
			$this->product_table_ui->render();
		}
	}

	public function register_settings(): void {
		$this->settings->register();
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( false !== strpos( $hook, Constants::SLUG ) ) {
			$this->assets->enqueue_admin();
		}
	}
}
