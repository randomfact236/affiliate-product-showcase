<?php
/**
 * Public Bootstrapper
 *
 * Registers all public-facing services including
 * templates, shortcodes, and widgets.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Public;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Public\Templates;
use AffiliateProductShowcase\Public\Shortcodes;
use AffiliateProductShowcase\Public\Widgets;
use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Plugin\Constants;

/**
 * Public Bootstrapper
 *
 * Initializes and registers all public-facing services
 * for templates, shortcodes, and widgets.
 *
 * @package AffiliateProductShowcase\Public
 * @since 1.0.0
 * @author Development Team
 */
final class Public_ {
	private bool $initialized = false;
	/**
	 * Templates instance
	 *
	 * @var Templates
	 * @since 1.0.0
	 */
	private Templates $templates;

	/**
	 * Shortcodes instance
	 *
	 * @var Shortcodes
	 * @since 1.0.0
	 */
	private Shortcodes $shortcodes;

	/**
	 * Widgets instance
	 *
	 * @var Widgets
	 * @since 1.0.0
	 */
	private Widgets $widgets;

	/**
	 * Product service
	 *
	 * @var ProductService
	 * @since 1.0.0
	 */
	private ProductService $product_service;

	/**
	 * Settings repository
	 *
	 * @var SettingsRepository
	 * @since 1.0.0
	 */
	private SettingsRepository $settings_repository;

	/**
	 * Affiliate service
	 *
	 * @var AffiliateService
	 * @since 1.0.0
	 */
	private AffiliateService $affiliate_service;

	/**
	 * Constructor
	 *
	 * Initializes all public services with their dependencies.
	 *
	 * @param Templates $templates Templates service instance
	 * @param Shortcodes $shortcodes Shortcodes service instance
	 * @param Widgets $widgets Widgets service instance
	 * @param ProductService $product_service Product service instance
	 * @param SettingsRepository $settings_repository Settings repository instance
	 * @param AffiliateService $affiliate_service Affiliate service instance
	 * @since 1.0.0
	 */
	public function __construct(
		Templates $templates,
		Shortcodes $shortcodes,
		Widgets $widgets,
		ProductService $product_service,
		SettingsRepository $settings_repository,
		AffiliateService $affiliate_service
	) {
		$this->templates           = $templates;
		$this->shortcodes          = $shortcodes;
		$this->widgets             = $widgets;
		$this->product_service     = $product_service;
		$this->settings_repository = $settings_repository;
		$this->affiliate_service   = $affiliate_service;
	}

	public function init(): void {
		if ( $this->initialized ) {
			return;
		}
		$this->initialized = true;
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register services
	 *
	 * Registers all public services with WordPress.
	 * Boots templates, registers shortcodes, and registers widgets.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @action init
	 */
	public function register(): void {
		$this->templates->boot();
		$this->shortcodes->register();
		$this->widgets->register();
	}
}
