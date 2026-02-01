<?php
/**
 * Help Page Template
 *
 * Help and documentation page for Affiliate Product Showcase plugin.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aps-help-wrap">
    <h1><?php esc_html_e('Help & Documentation', 'affiliate-product-showcase'); ?></h1>

    <div class="aps-help-content">
        <!-- Getting Started -->
        <div class="aps-help-section">
            <h2 class="aps-help-section-title">
                <span class="aps-section-icon">🚀</span>
                <?php esc_html_e('Getting Started', 'affiliate-product-showcase'); ?>
            </h2>

            <div class="aps-help-cards">
                <div class="aps-help-card">
                    <h3><?php esc_html_e('Installation', 'affiliate-product-showcase'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Download the plugin ZIP file', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Go to WordPress Admin → Plugins → Add New', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Click "Upload Plugin" and select the ZIP file', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Click "Install Now" and then "Activate"', 'affiliate-product-showcase'); ?></li>
                    </ol>
                </div>

                <div class="aps-help-card">
                    <h3><?php esc_html_e('First Steps', 'affiliate-product-showcase'); ?></h3>
                    <ol>
                        <li><?php esc_html_e('Go to Affiliate Manager → Add Product', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Fill in product details (title, description, price, etc.)', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Add product images and select categories/tags', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Enter your affiliate link and click "Publish"', 'affiliate-product-showcase'); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Common Questions -->
        <div class="aps-help-section">
            <h2 class="aps-help-section-title">
                <span class="aps-section-icon">❓</span>
                <?php esc_html_e('Frequently Asked Questions', 'affiliate-product-showcase'); ?>
            </h2>

            <div class="aps-faq-accordion">
                <div class="aps-faq-item">
                    <button class="aps-faq-question">
                        <span><?php esc_html_e('How do I add products?', 'affiliate-product-showcase'); ?></span>
                        <span class="aps-faq-icon">+</span>
                    </button>
                    <div class="aps-faq-answer">
                        <p><?php esc_html_e('Go to Affiliate Products → Add Product in your WordPress admin. Fill in the product form with your product details, images, and affiliate link, then click "Publish".', 'affiliate-product-showcase'); ?></p>
                    </div>
                </div>

                <div class="aps-faq-item">
                    <button class="aps-faq-question">
                        <span><?php esc_html_e('Where do I get affiliate links?', 'affiliate-product-showcase'); ?></span>
                        <span class="aps-faq-icon">+</span>
                    </button>
                    <div class="aps-faq-answer">
                        <p><?php esc_html_e('Affiliate links are provided by your affiliate programs (Amazon Associates, ShareASale, CJ Affiliate, etc.). Sign up for these programs to get your unique affiliate links.', 'affiliate-product-showcase'); ?></p>
                    </div>
                </div>

                <div class="aps-faq-item">
                    <button class="aps-faq-question">
                        <span><?php esc_html_e('How do I display products on my site?', 'affiliate-product-showcase'); ?></span>
                        <span class="aps-faq-icon">+</span>
                    </button>
                    <div class="aps-faq-answer">
                        <p><?php esc_html_e('Use shortcodes or widgets to display products. See the Shortcodes section below for examples. You can also create custom product grids using our display options.', 'affiliate-product-showcase'); ?></p>
                    </div>
                </div>

                <div class="aps-faq-item">
                    <button class="aps-faq-question">
                        <span><?php esc_html_e('Can I use this plugin with WooCommerce?', 'affiliate-product-showcase'); ?></span>
                        <span class="aps-faq-icon">+</span>
                    </button>
                    <div class="aps-faq-answer">
                        <p><?php esc_html_e('Yes! This plugin works alongside WooCommerce. You can have both affiliate products and regular WooCommerce products on the same site.', 'affiliate-product-showcase'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcodes -->
        <div class="aps-help-section">
            <h2 class="aps-help-section-title">
                <span class="aps-section-icon">📝</span>
                <?php esc_html_e('Shortcodes', 'affiliate-product-showcase'); ?>
            </h2>

            <div class="aps-shortcode-list">
                <div class="aps-shortcode-item">
                    <h3><?php esc_html_e('Display All Products', 'affiliate-product-showcase'); ?></h3>
                    <code>[affiliate_products]</code>
                    <p><?php esc_html_e('Displays all published affiliate products in a grid layout.', 'affiliate-product-showcase'); ?></p>
                </div>

                <div class="aps-shortcode-item">
                    <h3><?php esc_html_e('Products by Category', 'affiliate-product-showcase'); ?></h3>
                    <code>[affiliate_products category="electronics"]</code>
                    <p><?php esc_html_e('Display products from a specific category.', 'affiliate-product-showcase'); ?></p>
                </div>

                <div class="aps-shortcode-item">
                    <h3><?php esc_html_e('Products by Tag', 'affiliate-product-showcase'); ?></h3>
                    <code>[affiliate_products tag="featured"]</code>
                    <p><?php esc_html_e('Display products with a specific tag.', 'affiliate-product-showcase'); ?></p>
                </div>

                <div class="aps-shortcode-item">
                    <h3><?php esc_html_e('Limit Number of Products', 'affiliate-product-showcase'); ?></h3>
                    <code>[affiliate_products limit="6"]</code>
                    <p><?php esc_html_e('Display a specific number of products.', 'affiliate-product-showcase'); ?></p>
                </div>

                <div class="aps-shortcode-item">
                    <h3><?php esc_html_e('Product Carousel', 'affiliate-product-showcase'); ?></h3>
                    <code>[affiliate_products carousel="true"]</code>
                    <p><?php esc_html_e('Display products in a carousel/slider layout.', 'affiliate-product-showcase'); ?></p>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="aps-help-section">
            <h2 class="aps-help-section-title">
                <span class="aps-section-icon">🔧</span>
                <?php esc_html_e('Troubleshooting', 'affiliate-product-showcase'); ?>
            </h2>

            <div class="aps-troubleshoot-list">
                <div class="aps-troubleshoot-item">
                    <h3><?php esc_html_e('Products not displaying', 'affiliate-product-showcase'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Check that products are published (not draft)', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Verify shortcode is correctly entered', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Clear your cache and browser cookies', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Check for plugin conflicts by temporarily deactivating other plugins', 'affiliate-product-showcase'); ?></li>
                    </ul>
                </div>

                <div class="aps-troubleshoot-item">
                    <h3><?php esc_html_e('Images not uploading', 'affiliate-product-showcase'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Check PHP upload limits in server settings', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Verify folder permissions are writable', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Try uploading smaller image files', 'affiliate-product-showcase'); ?></li>
                    </ul>
                </div>

                <div class="aps-troubleshoot-item">
                    <h3><?php esc_html_e('Affiliate links not working', 'affiliate-product-showcase'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Verify URL format is correct (starts with http:// or https://)', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Test the link in a new browser tab', 'affiliate-product-showcase'); ?></li>
                        <li><?php esc_html_e('Check that your affiliate program is active', 'affiliate-product-showcase'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class="aps-help-section">
            <h2 class="aps-help-section-title">
                <span class="aps-section-icon">💬</span>
                <?php esc_html_e('Need More Help?', 'affiliate-product-showcase'); ?>
            </h2>

            <div class="aps-support-options">
                <div class="aps-support-card">
                    <h3><?php esc_html_e('Documentation', 'affiliate-product-showcase'); ?></h3>
                    <p><?php esc_html_e('Browse our comprehensive documentation for detailed guides and tutorials.', 'affiliate-product-showcase'); ?></p>
                    <a href="https://example.com/docs" target="_blank" class="button button-primary">
                        <?php esc_html_e('View Documentation', 'affiliate-product-showcase'); ?>
                    </a>
                </div>

                <div class="aps-support-card">
                    <h3><?php esc_html_e('Submit a Ticket', 'affiliate-product-showcase'); ?></h3>
                    <p><?php esc_html_e('Can\'t find the answer? Submit a support ticket and our team will help you.', 'affiliate-product-showcase'); ?></p>
                    <a href="https://example.com/support" target="_blank" class="button">
                        <?php esc_html_e('Get Support', 'affiliate-product-showcase'); ?>
                    </a>
                </div>

                <div class="aps-support-card">
                    <h3><?php esc_html_e('Community Forum', 'affiliate-product-showcase'); ?></h3>
                    <p><?php esc_html_e('Join our community to ask questions and share tips with other users.', 'affiliate-product-showcase'); ?></p>
                    <a href="https://example.com/forum" target="_blank" class="button">
                        <?php esc_html_e('Join Forum', 'affiliate-product-showcase'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Version Info -->
        <div class="aps-help-footer">
            <p>
                <?php
                printf(
                    /* translators: %s: plugin version */
                    esc_html__('Affiliate Product Showcase Version: %s', 'affiliate-product-showcase'),
                    esc_html(AFFILIATE_PRODUCT_SHOWCASE_VERSION)
                );
                ?>
            </p>
            <p>
                <?php
                printf(
                    /* translators: %s: WordPress version */
                    esc_html__('WordPress Version: %s', 'affiliate-product-showcase'),
                    esc_html(get_bloginfo('version'))
                );
                ?>
            </p>
            <p>
                <?php
                printf(
                    /* translators: %s: PHP version */
                    esc_html__('PHP Version: %s', 'affiliate-product-showcase'),
                    esc_html(PHP_VERSION)
                );
                ?>
            </p>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // FAQ Accordion
    $('.aps-faq-question').on('click', function() {
        const $item = $(this).closest('.aps-faq-item');
        const isActive = $item.hasClass('active');
        
        // Close all other items
        $('.aps-faq-item').removeClass('active');
        
        // Toggle clicked item
        if (!isActive) {
            $item.addClass('active');
        }
    });
});
</script>
