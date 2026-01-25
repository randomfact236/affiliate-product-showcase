<?php
/**
 * Dashboard Page Template
 *
 * Main dashboard for Affiliate Product Showcase plugin.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get product statistics
$total_products = wp_count_posts('aps_product');
$published_products = $total_products->publish ?? 0;
$draft_products = $total_products->draft ?? 0;

// Get category count
$category_count = wp_count_terms('aps_category', ['hide_empty' => false]);

// Get tag count
$tag_count = wp_count_terms('aps_tag', ['hide_empty' => false]);
?>

<div class="wrap aps-dashboard-wrap">
    <h1><?php esc_html_e('Affiliate Product Showcase Dashboard', 'affiliate-product-showcase'); ?></h1>

    <!-- Welcome Section -->
    <div class="aps-welcome-banner">
        <div class="aps-welcome-content">
            <h2><?php esc_html_e('Welcome to Affiliate Product Showcase', 'affiliate-product-showcase'); ?></h2>
            <p><?php esc_html_e('Manage your affiliate products, categories, and settings all in one place.', 'affiliate-product-showcase'); ?></p>
        </div>
        <div class="aps-welcome-actions">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary button-large">
                <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings')); ?>" class="button button-large">
                <?php esc_html_e('Settings', 'affiliate-product-showcase'); ?>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="aps-stats-grid">
        <div class="aps-stat-card">
            <div class="aps-stat-icon">📦</div>
            <div class="aps-stat-content">
                <h3><?php echo esc_html($published_products); ?></h3>
                <p><?php esc_html_e('Published Products', 'affiliate-product-showcase'); ?></p>
            </div>
        </div>

        <div class="aps-stat-card">
            <div class="aps-stat-icon">📝</div>
            <div class="aps-stat-content">
                <h3><?php echo esc_html($draft_products); ?></h3>
                <p><?php esc_html_e('Draft Products', 'affiliate-product-showcase'); ?></p>
            </div>
        </div>

        <div class="aps-stat-card">
            <div class="aps-stat-icon">📁</div>
            <div class="aps-stat-content">
                <h3><?php echo esc_html(is_wp_error($category_count) ? 0 : $category_count); ?></h3>
                <p><?php esc_html_e('Categories', 'affiliate-product-showcase'); ?></p>
            </div>
        </div>

        <div class="aps-stat-card">
            <div class="aps-stat-icon">🏷️</div>
            <div class="aps-stat-content">
                <h3><?php echo esc_html(is_wp_error($tag_count) ? 0 : $tag_count); ?></h3>
                <p><?php esc_html_e('Tags', 'affiliate-product-showcase'); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="aps-quick-actions">
        <h2><?php esc_html_e('Quick Actions', 'affiliate-product-showcase'); ?></h2>
        
        <div class="aps-quick-actions-grid">
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">📋</span>
                <span class="aps-action-text"><?php esc_html_e('View All Products', 'affiliate-product-showcase'); ?></span>
            </a>

            <a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">➕</span>
                <span class="aps-action-text"><?php esc_html_e('Add Product', 'affiliate-product-showcase'); ?></span>
            </a>

            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_category&post_type=aps_product')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">📂</span>
                <span class="aps-action-text"><?php esc_html_e('Manage Categories', 'affiliate-product-showcase'); ?></span>
            </a>

            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_tag&post_type=aps_product')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">🏷️</span>
                <span class="aps-action-text"><?php esc_html_e('Manage Tags', 'affiliate-product-showcase'); ?></span>
            </a>

            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_ribbon&post_type=aps_product')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">🎀</span>
                <span class="aps-action-text"><?php esc_html_e('Manage Ribbons', 'affiliate-product-showcase'); ?></span>
            </a>

            <a href="<?php echo esc_url(admin_url('admin.php?page=affiliate-manager-settings')); ?>" class="aps-quick-action">
                <span class="aps-action-icon">⚙️</span>
                <span class="aps-action-text"><?php esc_html_e('Settings', 'affiliate-product-showcase'); ?></span>
            </a>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="aps-recent-products">
        <h2><?php esc_html_e('Recent Products', 'affiliate-product-showcase'); ?></h2>
        
        <?php
        $recent_products = new WP_Query([
            'post_type' => 'aps_product',
            'posts_per_page' => 5,
            'post_status' => 'publish',
        ]);

        if ($recent_products->have_posts()) :
        ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Product', 'affiliate-product-showcase'); ?></th>
                        <th><?php esc_html_e('Date', 'affiliate-product-showcase'); ?></th>
                        <th><?php esc_html_e('Status', 'affiliate-product-showcase'); ?></th>
                        <th><?php esc_html_e('Actions', 'affiliate-product-showcase'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recent_products->have_posts()) : $recent_products->the_post(); ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php the_permalink(); ?>" target="_blank">
                                        <?php the_title(); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo get_the_date(); ?></td>
                            <td>
                                <span class="aps-status-badge aps-status-publish">
                                    <?php esc_html_e('Published', 'affiliate-product-showcase'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link(); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'affiliate-product-showcase'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="aps-empty-state">
                <p><?php esc_html_e('No products found. Add your first product to get started!', 'affiliate-product-showcase'); ?></p>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=aps_product&page=add-product')); ?>" class="button button-primary">
                    <?php esc_html_e('Add First Product', 'affiliate-product-showcase'); ?>
                </a>
            </div>
        <?php endif; ?>
        <?php wp_reset_postdata(); ?>
    </div>

    <!-- Help & Resources -->
    <div class="aps-help-resources">
        <h2><?php esc_html_e('Help & Resources', 'affiliate-product-showcase'); ?></h2>
        
        <div class="aps-help-grid">
            <div class="aps-help-card">
                <h3><?php esc_html_e('Documentation', 'affiliate-product-showcase'); ?></h3>
                <p><?php esc_html_e('Learn how to use all features of Affiliate Product Showcase.', 'affiliate-product-showcase'); ?></p>
                <a href="https://example.com/docs" target="_blank" class="button">
                    <?php esc_html_e('View Docs', 'affiliate-product-showcase'); ?>
                </a>
            </div>

            <div class="aps-help-card">
                <h3><?php esc_html_e('Support', 'affiliate-product-showcase'); ?></h3>
                <p><?php esc_html_e('Get help from our support team.', 'affiliate-product-showcase'); ?></p>
                <a href="https://example.com/support" target="_blank" class="button">
                    <?php esc_html_e('Get Support', 'affiliate-product-showcase'); ?>
                </a>
            </div>

            <div class="aps-help-card">
                <h3><?php esc_html_e('Changelog', 'affiliate-product-showcase'); ?></h3>
                <p><?php esc_html_e('See what\'s new in the latest version.', 'affiliate-product-showcase'); ?></p>
                <a href="https://example.com/changelog" target="_blank" class="button">
                    <?php esc_html_e('View Changelog', 'affiliate-product-showcase'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.aps-dashboard-wrap {
    max-width: 1200px;
    margin: 20px auto;
}

.aps-welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 40px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.aps-welcome-content h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 700;
}

.aps-welcome-content p {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.aps-welcome-actions {
    display: flex;
    gap: 12px;
}

.aps-welcome-actions .button {
    background: #ffffff;
    color: #667eea;
    border: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.aps-welcome-actions .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.aps-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.aps-stat-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.aps-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.aps-stat-icon {
    font-size: 48px;
    line-height: 1;
}

.aps-stat-content h3 {
    margin: 0 0 4px 0;
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
}

.aps-stat-content p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.aps-quick-actions {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.aps-quick-actions h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.aps-quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.aps-quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s ease;
}

.aps-quick-action:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
    transform: translateY(-2px);
}

.aps-action-icon {
    font-size: 24px;
}

.aps-action-text {
    font-weight: 500;
}

.aps-recent-products {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.aps-recent-products h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.aps-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.aps-status-publish {
    background: #dcfce7;
    color: #166534;
}

.aps-empty-state {
    text-align: center;
    padding: 40px;
    background: #f9fafb;
    border-radius: 8px;
}

.aps-help-resources {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
}

.aps-help-resources h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.aps-help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.aps-help-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
}

.aps-help-card h3 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.aps-help-card p {
    margin: 0 0 16px 0;
    font-size: 14px;
    color: #6b7280;
    line-height: 1.6;
}

.aps-help-card .button {
    width: 100%;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .aps-welcome-banner {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }

    .aps-stats-grid {
        grid-template-columns: 1fr;
    }

    .aps-quick-actions-grid {
        grid-template-columns: 1fr;
    }

    .aps-help-grid {
        grid-template-columns: 1fr;
    }
}
</style>