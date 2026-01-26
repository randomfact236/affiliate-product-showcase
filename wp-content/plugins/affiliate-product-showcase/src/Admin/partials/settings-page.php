<?php
/**
 * Settings Page Template with Pill Tabs
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap aps-settings-wrap">
    <h1><?php esc_html_e('Affiliate Product Showcase Settings', 'affiliate-product-showcase'); ?></h1>

    <!-- Tab Navigation -->
    <div class="aps-settings-tabs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=general')); ?>" 
           class="aps-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">⚙️</span>
            <span class="aps-tab-text"><?php esc_html_e('General', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=display')); ?>" 
           class="aps-tab <?php echo $active_tab === 'display' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">🎨</span>
            <span class="aps-tab-text"><?php esc_html_e('Display', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=products')); ?>" 
           class="aps-tab <?php echo $active_tab === 'products' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">📦</span>
            <span class="aps-tab-text"><?php esc_html_e('Products', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=categories')); ?>" 
           class="aps-tab <?php echo $active_tab === 'categories' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">📁</span>
            <span class="aps-tab-text"><?php esc_html_e('Categories', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=tags')); ?>" 
           class="aps-tab <?php echo $active_tab === 'tags' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">🏷️</span>
            <span class="aps-tab-text"><?php esc_html_e('Tags', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=ribbons')); ?>" 
           class="aps-tab <?php echo $active_tab === 'ribbons' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">🎀</span>
            <span class="aps-tab-text"><?php esc_html_e('Ribbons', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=performance')); ?>" 
           class="aps-tab <?php echo $active_tab === 'performance' ? 'active' : ''; ?> disabled">
            <span class="aps-tab-icon">⚡</span>
            <span class="aps-tab-text"><?php esc_html_e('Performance', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings&tab=security')); ?>" 
           class="aps-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?> disabled">
            <span class="aps-tab-icon">🔒</span>
            <span class="aps-tab-text"><?php esc_html_e('Security', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
    </div>

    <!-- Settings Form -->
    <div class="aps-settings-content">
        <?php if (in_array($active_tab, ['general', 'products', 'categories', 'tags', 'ribbons', 'display'])): ?>
            <form action="options.php" method="post">
                <?php
                settings_fields(\AffiliateProductShowcase\Admin\Settings::OPTION_GROUP);
                
                // Render only the active section based on tab
                if ($active_tab === 'general') {
                    // Render general section manually with heading
                    echo '<h2>' . esc_html__('General Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Configure general plugin settings and preferences.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render general settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_GENERAL);
                    echo '</table>';
                } elseif ($active_tab === 'display') {
                    // Render display section manually with heading
                    echo '<h2>' . esc_html__('Display Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Control how products are displayed on frontend.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render display settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_DISPLAY);
                    echo '</table>';
                } elseif ($active_tab === 'products') {
                    // Render products section manually with heading
                    echo '<h2>' . esc_html__('Product Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Configure product-specific settings including slugs, tracking, and display options.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render products settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_PRODUCTS);
                    echo '</table>';
                } elseif ($active_tab === 'categories') {
                    // Render categories section manually with heading
                    echo '<h2>' . esc_html__('Category Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Configure category display settings, hierarchy, and featured products.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render category settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_CATEGORIES);
                    echo '</table>';
                } elseif ($active_tab === 'tags') {
                    // Render tags section manually with heading
                    echo '<h2>' . esc_html__('Tag Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Configure tag display styles, colors, icons, and filtering options.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render tag settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_TAGS);
                    echo '</table>';
                } elseif ($active_tab === 'ribbons') {
                    // Render ribbons section manually with heading
                    echo '<h2>' . esc_html__('Ribbon Settings', 'affiliate-product-showcase') . '</h2>';
                    echo '<p>' . esc_html__('Configure ribbon/badge settings including position, colors, and animations.', 'affiliate-product-showcase') . '</p>';
                    
                    // Render ribbon settings fields table
                    echo '<table class="form-table" role="presentation">';
                    do_settings_fields('affiliate-product-showcase', \AffiliateProductShowcase\Admin\Settings::SECTION_RIBBONS);
                    echo '</table>';
                }
                
                submit_button();
                ?>
            </form>
        <?php else: ?>
            <div class="aps-coming-soon">
                <div class="aps-coming-soon-icon">🚧</div>
                <h2><?php esc_html_e('Coming Soon', 'affiliate-product-showcase'); ?></h2>
                <p><?php esc_html_e('These settings will be available in a future update.', 'affiliate-product-showcase'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>