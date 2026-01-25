<?php
/**
 * Add Product Page Template
 *
 * WooCommerce-style product editor for adding affiliate products.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current user capability
if (!current_user_can('edit_posts')) {
    wp_die(__('You do not have permission to add products.', 'affiliate-product-showcase'));
}
?>

<div class="wrap aps-add-product-wrap">
    <h1><?php esc_html_e('Add New Affiliate Product', 'affiliate-product-showcase'); ?></h1>

    <form id="aps-product-form" method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('aps_add_product', 'aps_product_nonce'); ?>
        
        <div class="aps-product-editor">
            <!-- Left Column: Product Details -->
            <div class="aps-product-main">
                <!-- Basic Information -->
                <div class="aps-section">
                    <h2><?php esc_html_e('Basic Information', 'affiliate-product-showcase'); ?></h2>
                    
                    <div class="aps-field-group">
                        <label for="product_title"><?php esc_html_e('Product Title', 'affiliate-product-showcase'); ?> <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="product_title" 
                            name="product_title" 
                            class="regular-text" 
                            required 
                            placeholder="<?php esc_attr_e('Enter product title', 'affiliate-product-showcase'); ?>"
                        >
                    </div>

                    <div class="aps-field-group">
                        <label for="product_description"><?php esc_html_e('Description', 'affiliate-product-showcase'); ?> <span class="required">*</span></label>
                        <?php
                        wp_editor(
                            '',
                            'product_description',
                            [
                                'textarea_name' => 'product_description',
                                'media_buttons' => true,
                                'textarea_rows' => 10,
                                'teeny' => false,
                            ]
                        );
                        ?>
                    </div>

                    <div class="aps-field-group">
                        <label for="product_excerpt"><?php esc_html_e('Short Description', 'affiliate-product-showcase'); ?></label>
                        <textarea 
                            id="product_excerpt" 
                            name="product_excerpt" 
                            rows="3" 
                            placeholder="<?php esc_attr_e('Short product description (optional)', 'affiliate-product-showcase'); ?>"
                        ></textarea>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="aps-section">
                    <h2><?php esc_html_e('Product Images', 'affiliate-product-showcase'); ?></h2>
                    
                    <div class="aps-field-group">
                        <label><?php esc_html_e('Product Image', 'affiliate-product-showcase'); ?></label>
                        <div class="aps-image-uploader">
                            <div class="aps-image-preview" id="main_image_preview">
                                <span class="aps-placeholder"><?php esc_html_e('No image selected', 'affiliate-product-showcase'); ?></span>
                            </div>
                            <button type="button" class="button" id="upload_main_image">
                                <?php esc_html_e('Upload Image', 'affiliate-product-showcase'); ?>
                            </button>
                            <button type="button" class="button" id="remove_main_image" style="display: none;">
                                <?php esc_html_e('Remove Image', 'affiliate-product-showcase'); ?>
                            </button>
                            <input type="hidden" id="main_image_id" name="main_image_id">
                        </div>
                    </div>
                </div>

                <!-- Categories & Tags -->
                <div class="aps-section">
                    <h2><?php esc_html_e('Categories & Tags', 'affiliate-product-showcase'); ?></h2>
                    
                    <div class="aps-field-group">
                        <label for="product_categories"><?php esc_html_e('Categories', 'affiliate-product-showcase'); ?></label>
                        <?php
                        wp_dropdown_categories([
                            'taxonomy' => 'aps_category',
                            'name' => 'product_categories[]',
                            'id' => 'product_categories',
                            'show_option_none' => __('Select categories', 'affiliate-product-showcase'),
                            'multiple' => true,
                            'class' => 'aps-select2',
                        ]);
                        ?>
                    </div>

                    <div class="aps-field-group">
                        <label for="product_tags"><?php esc_html_e('Tags', 'affiliate-product-showcase'); ?></label>
                        <input 
                            type="text" 
                            id="product_tags" 
                            name="product_tags" 
                            class="aps-tags-input"
                            placeholder="<?php esc_attr_e('Enter tags separated by commas', 'affiliate-product-showcase'); ?>"
                        >
                        <p class="description"><?php esc_html_e('Separate tags with commas', 'affiliate-product-showcase'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Data -->
            <div class="aps-product-sidebar">
                <!-- Publish Box -->
                <div class="aps-publish-box">
                    <h3><?php esc_html_e('Publish', 'affiliate-product-showcase'); ?></h3>
                    
                    <div class="aps-field-group">
                        <label for="product_status"><?php esc_html_e('Status', 'affiliate-product-showcase'); ?></label>
                        <select id="product_status" name="product_status">
                            <option value="draft"><?php esc_html_e('Draft', 'affiliate-product-showcase'); ?></option>
                            <option value="publish"><?php esc_html_e('Published', 'affiliate-product-showcase'); ?></option>
                        </select>
                    </div>

                    <div class="aps-publish-actions">
                        <button type="submit" class="button button-primary button-large" name="aps_publish_product">
                            <?php esc_html_e('Publish Product', 'affiliate-product-showcase'); ?>
                        </button>
                        <button type="submit" class="button button-large" name="aps_save_draft">
                            <?php esc_html_e('Save Draft', 'affiliate-product-showcase'); ?>
                        </button>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="aps-pricing-box">
                    <h3><?php esc_html_e('Pricing', 'affiliate-product-showcase'); ?></h3>
                    
                    <div class="aps-field-group">
                        <label for="product_price"><?php esc_html_e('Price', 'affiliate-product-showcase'); ?> <span class="required">*</span></label>
                        <div class="aps-input-with-currency">
                            <span class="aps-currency-symbol">$</span>
                            <input 
                                type="number" 
                                id="product_price" 
                                name="product_price" 
                                step="0.01" 
                                min="0" 
                                required 
                                placeholder="0.00"
                            >
                        </div>
                    </div>

                    <div class="aps-field-group">
                        <label for="original_price"><?php esc_html_e('Original Price', 'affiliate-product-showcase'); ?></label>
                        <div class="aps-input-with-currency">
                            <span class="aps-currency-symbol">$</span>
                            <input 
                                type="number" 
                                id="original_price" 
                                name="original_price" 
                                step="0.01" 
                                min="0" 
                                placeholder="0.00"
                            >
                        </div>
                        <p class="description"><?php esc_html_e('Show as sale price', 'affiliate-product-showcase'); ?></p>
                    </div>

                    <div class="aps-field-group">
                        <label for="currency"><?php esc_html_e('Currency', 'affiliate-product-showcase'); ?></label>
                        <select id="currency" name="currency">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                </div>

                <!-- Affiliate Link -->
                <div class="aps-affiliate-box">
                    <h3><?php esc_html_e('Affiliate Link', 'affiliate-product-showcase'); ?></h3>
                    
                    <div class="aps-field-group">
                        <label for="affiliate_url"><?php esc_html_e('Product URL', 'affiliate-product-showcase'); ?> <span class="required">*</span></label>
                        <input 
                            type="url" 
                            id="affiliate_url" 
                            name="affiliate_url" 
                            class="large-text" 
                            required 
                            placeholder="https://example.com/product"
                        >
                        <p class="description"><?php esc_html_e('Your affiliate link to the product', 'affiliate-product-showcase'); ?></p>
                    </div>

                    <div class="aps-field-group">
                        <label for="button_text"><?php esc_html_e('Button Text', 'affiliate-product-showcase'); ?></label>
                        <input 
                            type="text" 
                            id="button_text" 
                            name="button_text" 
                            value="<?php esc_attr_e('Buy Now', 'affiliate-product-showcase'); ?>"
                            placeholder="<?php esc_attr_e('Buy Now', 'affiliate-product-showcase'); ?>"
                        >
                    </div>
                </div>

                <!-- Ribbon -->
                <div class="aps-ribbon-box">
                    <h3><?php esc_html_e('Ribbon', 'affiliate-product-showcase'); ?></h3>
                    
                    <div class="aps-field-group">
                        <label for="ribbon_select"><?php esc_html_e('Select Ribbon', 'affiliate-product-showcase'); ?></label>
                        <select id="ribbon_select" name="ribbon_id">
                            <option value=""><?php esc_html_e('No ribbon', 'affiliate-product-showcase'); ?></option>
                            <?php
                            $ribbons = get_terms([
                                'taxonomy' => 'aps_ribbon',
                                'hide_empty' => false,
                            ]);
                            foreach ($ribbons as $ribbon) {
                                echo '<option value="' . esc_attr($ribbon->term_id) . '">' . esc_html($ribbon->name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.aps-add-product-wrap {
    max-width: 1400px;
    margin: 20px auto;
}

.aps-product-editor {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.aps-product-main {
    flex: 1;
}

.aps-product-sidebar {
    width: 300px;
    flex-shrink: 0;
}

.aps-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
}

.aps-section h2 {
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 18px;
    font-weight: 600;
}

.aps-field-group {
    margin-bottom: 20px;
}

.aps-field-group:last-child {
    margin-bottom: 0;
}

.aps-field-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
}

.aps-field-group .required {
    color: #ef4444;
}

.aps-field-group input[type="text"],
.aps-field-group input[type="url"],
.aps-field-group input[type="number"],
.aps-field-group textarea,
.aps-field-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.aps-field-group input:focus,
.aps-field-group textarea:focus,
.aps-field-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.aps-field-group .description {
    margin-top: 6px;
    font-size: 13px;
    color: #6b7280;
}

.aps-publish-box,
.aps-pricing-box,
.aps-affiliate-box,
.aps-ribbon-box {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.aps-publish-box h3,
.aps-pricing-box h3,
.aps-affiliate-box h3,
.aps-ribbon-box h3 {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
}

.aps-publish-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.aps-input-with-currency {
    position: relative;
}

.aps-currency-symbol {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-weight: 500;
}

.aps-input-with-currency input {
    padding-left: 30px !important;
}

.aps-image-uploader {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.aps-image-preview {
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 12px;
    overflow: hidden;
}

.aps-image-preview img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
}

.aps-placeholder {
    color: #9ca3af;
}

.aps-image-uploader button {
    margin: 0 6px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .aps-product-editor {
        flex-direction: column;
    }

    .aps-product-sidebar {
        width: 100%;
    }
}

/* WordPress Editor Overrides */
.wp-editor-container {
    border: 1px solid #d1d5db;
    border-radius: 6px;
}

.wp-editor-tabs {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    padding: 8px 12px;
}
</style>