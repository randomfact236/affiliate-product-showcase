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
