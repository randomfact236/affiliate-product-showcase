<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Core;

/**
 * Main plugin bootstrap class.
 *
 * Responsible for registering hooks and providing a single entry point
 * for initialization of the plugin in admin and frontend contexts.
 */
final class Plugin
{
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Get the plugin singleton instance.
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor is private to enforce singleton usage.
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize plugin: register the high-level init hook.
     */
    private function init(): void
    {
        add_action('init', [$this, 'register_hooks']);
    }

    /**
     * Register admin and frontend hooks depending on context.
     */
    public function register_hooks(): void
    {
        if (is_admin()) {
            add_action('admin_init', [$this, 'admin_init']);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'frontend_init']);
        }
    }

    /**
     * Admin initialization logic.
     */
    public function admin_init(): void
    {
        // Register admin menus, settings, metaboxes, etc.
    }

    /**
     * Frontend initialization logic.
     */
    public function frontend_init(): void
    {
        // Register scripts/styles, shortcodes, rest routes, etc.
    }

    /**
     * Activation callback. Performs one-time setup tasks.
     */
    public static function activate(): void
    {
        // e.g. flush rewrite rules, create DB tables, set default options
    }

    /**
     * Deactivation callback. Performs teardown tasks.
     */
    public static function deactivate(): void
    {
        // e.g. flush rewrite rules
    }
}
