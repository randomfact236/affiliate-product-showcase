<?php
/**
 * Admin Bootstrapper
 *
 * Main admin class for the Affiliate Product Showcase plugin.
 * Initializes admin menu, settings, meta boxes, and assets.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Admin\Menu;
use AffiliateProductShowcase\Admin\Columns;
use AffiliateProductShowcase\Admin\BulkActions;
use AffiliateProductShowcase\Admin\TermMeta;
use AffiliateProductShowcase\Admin\TermUI;
use AffiliateProductShowcase\Admin\Settings;
use AffiliateProductShowcase\Admin\MetaBoxes;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Security\Headers;

/**
 * Admin Bootstrapper
 *
 * Main admin class that initializes all admin functionality.
 * Coordinates admin menu, settings, meta boxes, and asset loading.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 * @author Development Team
 */
final class Admin {
	/**
	 * Settings service instance
	 *
	 * @var Settings
	 * @since 1.0.0
	 */
	private Settings $settings;

	/**
	 * MetaBoxes service instance
	 *
	 * @var MetaBoxes
	 * @since 1.0.0
	 */
	private MetaBoxes $metaboxes;

	/**
	 * Assets service instance
	 *
	 * @var Assets
	 * @since 1.0.0
	 */
	private Assets $assets;

	/**
	 * Headers service instance
	 *
	 * @var Headers
	 * @since 1.0.0
	 */
	private Headers $headers;

	/**
	 * Product service instance
	 *
	 * @var ProductService
	 * @since 1.0.0
	 */
	private ProductService $product_service;

	/**
	 * Settings repository instance
	 *
	 * @var SettingsRepository
	 * @since 1.0.0
	 */
	private SettingsRepository $settings_repository;

	/**
	 * Affiliate service instance
	 *
	 * @var AffiliateService
	 * @since 1.0.0
	 */
	private AffiliateService $affiliate_service;

	/**
	 * Constructor
	 *
	 * Initializes all admin services with their dependencies.
	 *
	 * @param Settings $settings Settings service instance
	 * @param MetaBoxes $metaboxes Meta boxes service instance
	 * @param Assets $assets Assets service instance
	 * @param Headers $headers Security headers service instance
	 * @param ProductService $product_service Product service instance
	 * @param SettingsRepository $settings_repository Settings repository instance
	 * @param AffiliateService $affiliate_service Affiliate service instance
	 * @since 1.0.0
	 */
	public function __construct(
		Settings $settings,
		MetaBoxes $metaboxes,
		Assets $assets,
		Headers $headers,
		ProductService $product_service,
		SettingsRepository $settings_repository,
		AffiliateService $affiliate_service
	) {
		$this->settings          = $settings;
		$this->metaboxes         = $metaboxes;
		$this->assets             = $assets;
		$this->headers             = $headers;
		$this->product_service     = $product_service;
		$this->settings_repository = $settings_repository;
		$this->affiliate_service   = $affiliate_service;
	}

	/**
	 * Initialize admin
	 *
	 * Registers all admin hooks and menus.
	 * Calls initialization methods on all admin services.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action admin_menu
	 * @action admin_init
	 * @action admin_enqueue_scripts
	 */
	public function init(): void {
		$this->settings->init();
		$this->metaboxes->init();
		$this->assets->init();
		$this->headers->init();
	}

	/**
	 * Get settings service
	 *
	 * Returns the settings service instance.
	 * Used by other admin components to access settings.
	 *
	 * @return Settings Settings service instance
	 * @since 1.0.0
	 */
	public function settings(): Settings {
		return $this->settings;
	}

	/**
	 * Get meta boxes service
	 *
	 * Returns the meta boxes service instance.
	 * Used by other admin components to access meta box functionality.
	 *
	 * @return MetaBoxes Meta boxes service instance
	 * @since 1.0.0
	 */
	public function metaboxes(): MetaBoxes {
		return $this->metaboxes;
	}

	/**
	 * Get assets service
	 *
	 * Returns the assets service instance.
	 * Used by other admin components to access asset functionality.
	 *
	 * @return Assets Assets service instance
	 * @since 1.0.0
	 */
	public function assets(): Assets {
		return $this->assets;
	}

	/**
	 * Get headers service
	 *
	 * Returns the security headers service instance.
	 * Used by other admin components to access security header functionality.
	 *
	 * @return Headers Security headers service instance
	 * @since 1.0.0
	 */
	public function headers(): Headers {
		return $this->headers;
	}
}
