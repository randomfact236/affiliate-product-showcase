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

	public function __construct( private Assets $assets, private ProductService $product_service, private Headers $headers ) {
		$this->settings  = new Settings();
		$this->metaboxes = new MetaBoxes( $this->product_service );
	}

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_head', [ $this, 'addMenuIcons' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'add_meta_boxes', [ $this, 'metaboxes', 'register' ] );
		add_action( 'save_post', [ $this, 'metaboxes', 'save_meta' ], 10, 2 );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', [ $this, 'reorderMenus' ], 999 );
		// Initialize security headers
		$this->headers->init();
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Affiliate Manager', Constants::TEXTDOMAIN ),
			__( 'Affiliate Manager', Constants::TEXTDOMAIN ),
			Constants::MENU_CAP,
			Constants::SLUG,
			[ $this, 'render_settings_page' ],
			'dashicons-admin-generic',
			55.1
		);
	}

	public function render_settings_page(): void {
		$settings = $this->settings->get();
		require Constants::viewPath( 'src/Admin/partials/settings-page.php' );
	}

	public function register_settings(): void {
		$this->settings->register();
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( false !== strpos( $hook, Constants::SLUG ) ) {
			$this->assets->enqueue_admin();
		}
	}

	/**
	 * Add custom menu icons
	 *
	 * @return void
	 */
	public function addMenuIcons(): void {
		?>
		<style>
			#adminmenu .toplevel_page_<?php echo Constants::SLUG; ?> .wp-menu-image img {
				width: 20px;
				height: 20px;
				padding: 5px 0;
			}
		</style>
		<?php
	}

	/**
	 * Reorder menus to position Affiliate Manager right after Affiliate Products
	 *
	 * @param array $menu_order Current menu order
	 * @return array Modified menu order
	 */
	public function reorderMenus( $menu_order ) {
		// Find positions of our menus
		$products_key = array_search( 'edit.php?post_type=aps_product', $menu_order );
		$manager_key = array_search( Constants::SLUG, $menu_order );
		
		// If either menu not found, return unchanged
		if ( $products_key === false || $manager_key === false ) {
			return $menu_order;
		}
		
		// Remove Affiliate Manager from current position
		unset( $menu_order[$manager_key] );
		
		// Insert Affiliate Manager right after Affiliate Products
		array_splice( $menu_order, $products_key + 1, 0, [ Constants::SLUG ] );
		
		return $menu_order;
	}

}
