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
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=general')); ?>" 
           class="aps-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">⚙️</span>
            <span class="aps-tab-text"><?php esc_html_e('General', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=display')); ?>" 
           class="aps-tab <?php echo $active_tab === 'display' ? 'active' : ''; ?>">
            <span class="aps-tab-icon">🎨</span>
            <span class="aps-tab-text"><?php esc_html_e('Display', 'affiliate-product-showcase'); ?></span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=products')); ?>" 
           class="aps-tab <?php echo $active_tab === 'products' ? 'active' : ''; ?>" style="opacity: 0.5; cursor: not-allowed;">
            <span class="aps-tab-icon">📦</span>
            <span class="aps-tab-text"><?php esc_html_e('Products', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=categories')); ?>" 
           class="aps-tab <?php echo $active_tab === 'categories' ? 'active' : ''; ?>" style="opacity: 0.5; cursor: not-allowed;">
            <span class="aps-tab-icon">📁</span>
            <span class="aps-tab-text"><?php esc_html_e('Categories', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=performance')); ?>" 
           class="aps-tab <?php echo $active_tab === 'performance' ? 'active' : ''; ?>" style="opacity: 0.5; cursor: not-allowed;">
            <span class="aps-tab-icon">⚡</span>
            <span class="aps-tab-text"><?php esc_html_e('Performance', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-product-showcase-settings&tab=security')); ?>" 
           class="aps-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>" style="opacity: 0.5; cursor: not-allowed;">
            <span class="aps-tab-icon">🔒</span>
            <span class="aps-tab-text"><?php esc_html_e('Security', 'affiliate-product-showcase'); ?></span>
            <span class="aps-tab-badge">Coming Soon</span>
        </a>
    </div>

    <!-- Settings Form -->
    <div class="aps-settings-content">
        <?php if ($active_tab === 'general' || $active_tab === 'display'): ?>
            <form action="options.php" method="post">
                <?php
                settings_fields(\AffiliateProductShowcase\Admin\Settings::OPTION_GROUP);
                do_settings_sections('affiliate-product-showcase');
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

<style>
.aps-settings-wrap {
    max-width: 1200px;
    margin: 20px auto;
}

.aps-settings-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 25px 0 30px 0;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.aps-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f3f4f6;
    border-radius: 25px;
    color: #6b7280;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    border: 2px solid transparent;
}

.aps-tab:hover {
    background: #e5e7eb;
    color: #1f2937;
    transform: translateY(-2px);
}

.aps-tab.active {
    background: #3b82f6;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    border-color: #3b82f6;
}

.aps-tab.active:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.aps-tab-icon {
    font-size: 18px;
    line-height: 1;
}

.aps-tab-text {
    font-size: 14px;
    line-height: 1.4;
}

.aps-tab-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #f59e0b;
    color: #ffffff;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.aps-settings-content {
    background: #ffffff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.aps-coming-soon {
    text-align: center;
    padding: 60px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: #ffffff;
}

.aps-coming-soon-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.aps-coming-soon h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 700;
}

.aps-coming-soon p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

/* Form Styles */
.aps-settings-content form input[type="text"],
.aps-settings-content form input[type="number"],
.aps-settings-content form select {
    padding: 10px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.aps-settings-content form input[type="text"]:focus,
.aps-settings-content form input[type="number"]:focus,
.aps-settings-content form select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.aps-settings-content form label {
    display: block;
    margin: 0 0 8px 0;
    font-weight: 500;
    color: #374151;
}

.aps-settings-content form .description {
    margin-top: 8px;
    color: #6b7280;
    font-size: 13px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .aps-settings-tabs {
        flex-direction: column;
        gap: 8px;
    }
    
    .aps-tab {
        width: 100%;
        justify-content: center;
    }
    
    .aps-settings-content {
        padding: 20px;
    }
}
</style>