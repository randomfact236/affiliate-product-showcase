<?php
/**
 * Products Page Template
 *
 * Template for products listing page.
 *
 * @package Affiliate_Product_Showcase\Admin\Partials
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get status counts for tabs
$status_counts = $this->get_status_counts();
?>

<div class="wrap aps-products-page">
<!-- Page Header -->
<h1>
    <?php esc_html_e('Products', 'affiliate-product-showcase'); ?>
</h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=aps-add-product')); ?>" 
       class="page-title-action">
        <?php esc_html_e('Add New Product', 'affiliate-product-showcase'); ?>
    </a>
    
    <hr class="wp-header-end">

    <!-- Navigation Tabs (Status Filters) -->
    <h2 class="nav-tab-wrapper aps-nav-tabs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=aps-products&status=all')); ?>" 
           class="nav-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'all' ? 'nav-tab-active' : ''; ?>">
            <?php 
            printf(
                /* translators: %d: product count */
                esc_html__('All (%d)', 'affiliate-product-showcase'),
                intval($status_counts['all'])
            ); 
            ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=aps-products&status=published')); ?>" 
           class="nav-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'published' ? 'nav-tab-active' : ''; ?>">
            <?php 
            printf(
                /* translators: %d: product count */
                esc_html__('Published (%d)', 'affiliate-product-showcase'),
                intval($status_counts['published'])
            ); 
            ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=aps-products&status=draft')); ?>" 
           class="nav-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'draft' ? 'nav-tab-active' : ''; ?>">
            <?php 
            printf(
                /* translators: %d: product count */
                esc_html__('Drafts (%d)', 'affiliate-product-showcase'),
                intval($status_counts['draft'])
            ); 
            ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=aps-products&status=trash')); ?>" 
           class="nav-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'trash' ? 'nav-tab-active' : ''; ?>">
            <?php 
            printf(
                /* translators: %d: product count */
                esc_html__('Trash (%d)', 'affiliate-product-showcase'),
                intval($status_counts['trash'])
            ); 
            ?>
        </a>
    </h2>

    <!-- Top Toolbar -->
    <div class="tablenav top">
        <!-- Left Side: Bulk Actions + Filters -->
        <div class="alignleft actions bulkactions">
            <!-- Bulk Actions -->
            <label for="bulk-action-selector-top" class="screen-reader-text">
                <?php esc_html_e('Select bulk action', 'affiliate-product-showcase'); ?>
            </label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1"><?php esc_html_e('Bulk actions', 'affiliate-product-showcase'); ?></option>
                <option value="trash"><?php esc_html_e('Move to Trash', 'affiliate-product-showcase'); ?></option>
            </select>
            <input type="submit" id="doaction" class="button action" value="<?php esc_attr_e('Apply', 'affiliate-product-showcase'); ?>">

            <!-- Category Filter -->
            <label for="category-filter-top" class="screen-reader-text">
                <?php esc_html_e('Filter by category', 'affiliate-product-showcase'); ?>
            </label>
            <select name="category" id="category-filter-top">
                <option value="all"><?php esc_html_e('All Categories', 'affiliate-product-showcase'); ?></option>
                <?php 
                $categories = get_terms(['taxonomy' => 'aps_category', 'hide_empty' => false]);
                foreach ($categories as $category) {
                    $selected = isset($_GET['category']) && $_GET['category'] === $category->slug ? 'selected' : '';
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($category->slug),
                        esc_attr($selected),
                        esc_html($category->name)
                    );
                }
                ?>
            </select>

            <!-- Status Filter -->
            <label for="status-filter-top" class="screen-reader-text">
                <?php esc_html_e('Filter by status', 'affiliate-product-showcase'); ?>
            </label>
            <select name="status_filter" id="status-filter-top">
                <option value="all"><?php esc_html_e('All Statuses', 'affiliate-product-showcase'); ?></option>
                <option value="published" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'published' ? 'selected' : ''; ?>>
                    <?php esc_html_e('Published', 'affiliate-product-showcase'); ?>
                </option>
                <option value="draft" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'draft' ? 'selected' : ''; ?>>
                    <?php esc_html_e('Draft', 'affiliate-product-showcase'); ?>
                </option>
                <option value="trash" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'trash' ? 'selected' : ''; ?>>
                    <?php esc_html_e('Trash', 'affiliate-product-showcase'); ?>
                </option>
            </select>

            <input type="submit" id="filter-submit" class="button" value="<?php esc_attr_e('Filter', 'affiliate-product-showcase'); ?>">
        </div>

        <!-- Right Side: Search + Pagination -->
        <div class="alignright">
            <!-- Search Input -->
            <div class="search-form">
                <input type="hidden" name="page" value="aps-products">
                <?php if (isset($_GET['status'])): ?>
                    <input type="hidden" name="status" value="<?php echo esc_attr(sanitize_text_field($_GET['status'])); ?>">
                <?php endif; ?>
                <label for="post-search-input" class="screen-reader-text">
                    <?php esc_html_e('Search Products:', 'affiliate-product-showcase'); ?>
                </label>
                <input type="search" id="post-search-input" name="s" 
                       value="<?php echo isset($_GET['s']) ? esc_attr(sanitize_text_field($_GET['s'])) : ''; ?>"
                       placeholder="<?php esc_attr_e('Search products...', 'affiliate-product-showcase'); ?>">
               <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Products', 'affiliate-product-showcase'); ?>">
           </div>
       </div>

        <div class="clear"></div>
    </div>

    <!-- Products Table -->
    <form method="post" id="aps-products-form">
        <?php 
        $this->products_table->display(); 
        ?>
    </form>

    <!-- Footer Toolbar -->
    <div class="tablenav bottom">
        <!-- Left Side: Bulk Actions -->
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-bottom" class="screen-reader-text">
                <?php esc_html_e('Select bulk action', 'affiliate-product-showcase'); ?>
            </label>
            <select name="action2" id="bulk-action-selector-bottom">
                <option value="-1"><?php esc_html_e('Bulk actions', 'affiliate-product-showcase'); ?></option>
                <option value="trash"><?php esc_html_e('Move to Trash', 'affiliate-product-showcase'); ?></option>
            </select>
            <input type="submit" id="doaction2" class="button action" value="<?php esc_attr_e('Apply', 'affiliate-product-showcase'); ?>">
        </div>

        <!-- Right Side: Pagination -->
        <div class="alignright actions">
            <?php 
            $this->products_table->pagination('bottom'); 
            ?>
        </div>

        <div class="clear"></div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="aps-toast-container"></div>

<!-- Quick Edit Modal -->
<div id="aps-quick-edit-modal" class="aps-modal" style="display: none;">
    <div class="aps-modal-overlay"></div>
    <div class="aps-modal-content">
        <div class="aps-modal-header">
            <h2><?php esc_html_e('Quick Edit Product', 'affiliate-product-showcase'); ?></h2>
            <button class="aps-modal-close" type="button">
                <span class="screen-reader-text"><?php esc_html_e('Close modal', 'affiliate-product-showcase'); ?></span>
                ×
            </button>
        </div>
        <div class="aps-modal-body">
            <form id="aps-quick-edit-form">
                <input type="hidden" id="quick-edit-product-id" name="product_id">
                
                <div class="aps-form-field">
                    <label for="quick-edit-title"><?php esc_html_e('Title', 'affiliate-product-showcase'); ?></label>
                    <input type="text" id="quick-edit-title" name="title" required>
                </div>

                <div class="aps-form-field">
                    <label for="quick-edit-price"><?php esc_html_e('Price', 'affiliate-product-showcase'); ?></label>
                    <input type="number" id="quick-edit-price" name="price" step="0.01" required>
                </div>

                <div class="aps-form-field">
                    <label for="quick-edit-status"><?php esc_html_e('Status', 'affiliate-product-showcase'); ?></label>
                    <select id="quick-edit-status" name="status">
                        <option value="published"><?php esc_html_e('Published', 'affiliate-product-showcase'); ?></option>
                        <option value="draft"><?php esc_html_e('Draft', 'affiliate-product-showcase'); ?></option>
                        <option value="trash"><?php esc_html_e('Trash', 'affiliate-product-showcase'); ?></option>
                    </select>
                </div>

                <div class="aps-form-field">
                    <label for="quick-edit-ribbon"><?php esc_html_e('Ribbon', 'affiliate-product-showcase'); ?></label>
                    <input type="text" id="quick-edit-ribbon" name="ribbon" placeholder="<?php esc_attr_e('e.g., Best Seller', 'affiliate-product-showcase'); ?>">
                </div>

                <div class="aps-form-field">
                    <label>
                        <input type="checkbox" id="quick-edit-featured" name="featured">
                        <?php esc_html_e('Featured Product', 'affiliate-product-showcase'); ?>
                    </label>
                </div>
            </form>
        </div>
        <div class="aps-modal-footer">
            <button type="button" class="button button-secondary aps-modal-cancel">
                <?php esc_html_e('Cancel', 'affiliate-product-showcase'); ?>
            </button>
            <button type="button" class="button button-primary aps-modal-save">
                <?php esc_html_e('Save Changes', 'affiliate-product-showcase'); ?>
            </button>
        </div>
    </div>
</div>