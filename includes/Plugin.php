<?php
/**
 * Plugin Name: Affiliate Product Showcase
 * Plugin URI: https://github.com/randomfact236/affiliate-product-showcase
 * Description: A WordPress plugin for showcasing affiliate products
 * Version: 1.0.0
 * Author: randomfact236
 * Author URI: https://github.com/randomfact236
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: affiliate-product-showcase
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AFFILIATE_PRODUCT_SHOWCASE_VERSION', '1.0.0');
define('AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class Affiliate_Product_Showcase {
    
    /**
     * Initialize the plugin
     */
    public static function init() {
        add_action('init', [__CLASS__, 'load_textdomain']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }
    
    /**
     * Load plugin text domain
     */
    public static function load_textdomain() {
        load_plugin_textdomain('affiliate-product-showcase', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue frontend assets
     */
    public static function enqueue_assets() {
        wp_enqueue_style(
            'affiliate-product-showcase-style',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/css/style.css',
            [],
            AFFILIATE_PRODUCT_SHOWCASE_VERSION
        );
        
        wp_enqueue_script(
            'affiliate-product-showcase-script',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/script.js',
            ['jquery'],
            AFFILIATE_PRODUCT_SHOWCASE_VERSION,
            true
        );
    }
}

// Initialize the plugin
Affiliate_Product_Showcase::init();
