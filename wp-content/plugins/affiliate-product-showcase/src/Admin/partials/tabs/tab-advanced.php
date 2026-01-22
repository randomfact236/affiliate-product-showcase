<?php
/**
 * Tab: Advanced - Basic Settings
 *
 * @package AffiliateProductShowcase\Admin\Partials\Tabs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Product Status -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">📊</span>
		<span><?php esc_html_e( 'Product Status', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_status"><?php esc_html_e( 'Status', 'affiliate-product-showcase' ); ?></label>
			<select id="aps_status" name="aps_status" class="woocommerce-select">
				<option value="publish"><?php esc_html_e( 'Published', 'affiliate-product-showcase' ); ?></option>
				<option value="draft"><?php esc_html_e( 'Draft', 'affiliate-product-showcase' ); ?></option>
			</select>
			<span class="description"><?php esc_html_e( 'Product visibility status', 'affiliate-product-showcase' ); ?></span>
		</div>
		
		<div class="woocommerce-checkbox-wrapper">
			<label>
				<input type="checkbox" id="aps_featured" name="aps_featured" value="1">
				<?php esc_html_e( 'Featured Product', 'affiliate-product-showcase' ); ?>
			</label>
			<span class="description"><?php esc_html_e( 'Show in featured products section', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Stock Status -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">📦</span>
		<span><?php esc_html_e( 'Stock Status', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_stock_status"><?php esc_html_e( 'Availability', 'affiliate-product-showcase' ); ?></label>
			<select id="aps_stock_status" name="aps_stock_status" class="woocommerce-select">
				<option value="instock"><?php esc_html_e( 'In Stock', 'affiliate-product-showcase' ); ?></option>
				<option value="outofstock"><?php esc_html_e( 'Out of Stock', 'affiliate-product-showcase' ); ?></option>
				<option value="preorder"><?php esc_html_e( 'Pre-order', 'affiliate-product-showcase' ); ?></option>
			</select>
			<span class="description"><?php esc_html_e( 'Product availability status', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Product Categories -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🏷️</span>
		<span><?php esc_html_e( 'Categories & Tags', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_categories"><?php esc_html_e( 'Product Categories', 'affiliate-product-showcase' ); ?></label>
			<input type="text" id="aps_categories" name="aps_categories" class="woocommerce-input" placeholder="<?php esc_attr_e( 'Electronics, Computers, Laptops', 'affiliate-product-showcase' ); ?>">
			<span class="description"><?php esc_html_e( 'Comma-separated category names', 'affiliate-product-showcase' ); ?></span>
		</div>
		
		<div class="woocommerce-input-wrapper">
			<label for="aps_tags"><?php esc_html_e( 'Tags', 'affiliate-product-showcase' ); ?></label>
			<input type="text" id="aps_tags" name="aps_tags" class="woocommerce-input" placeholder="<?php esc_attr_e( 'new, sale, popular', 'affiliate-product-showcase' ); ?>">
			<span class="description"><?php esc_html_e( 'Comma-separated tag names', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- SEO Settings -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🔍</span>
		<span><?php esc_html_e( 'SEO Settings', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_seo_title"><?php esc_html_e( 'SEO Title', 'affiliate-product-showcase' ); ?></label>
			<input type="text" id="aps_seo_title" name="aps_seo_title" class="woocommerce-input" placeholder="<?php esc_attr_e( 'Product Name - Your Site', 'affiliate-product-showcase' ); ?>">
			<span class="description"><?php esc_html_e( 'Custom SEO title (leave empty to use product name)', 'affiliate-product-showcase' ); ?></span>
		</div>
		
	<div class="woocommerce-input-wrapper">
			<label for="aps_seo_description"><?php esc_html_e( 'SEO Description', 'affiliate-product-showcase' ); ?></label>
			<textarea id="aps_seo_description" name="aps_seo_description" class="woocommerce-input woocommerce-textarea woocommerce-full-page-textarea" maxlength="160" placeholder="<?php esc_attr_e( 'Brief description for search engines...', 'affiliate-product-showcase' ); ?>"></textarea>
			<span class="description"><?php esc_html_e( 'Meta description for search engines (max 160 characters)', 'affiliate-product-showcase' ); ?></span>
	</div>
	</div>
</div>

<style>
	/* Panel Icons */
	.panel-icon {
		font-size: 16px;
		margin-right: 8px;
	}

	/* Full Page Textarea - Responsive */
	.woocommerce-full-page-textarea {
		width: 100%;
		min-height: 100px;
		resize: vertical;
		font-family: inherit;
		font-size: 14px;
		line-height: 1.6;
		padding: 12px;
	}

	/* Responsive adjustments for textarea */
	@media (max-width: 768px) {
		.woocommerce-full-page-textarea {
			min-height: 80px;
			font-size: 13px;
			padding: 10px;
		}
	}

	@media (max-width: 480px) {
		.woocommerce-full-page-textarea {
			min-height: 60px;
			font-size: 12px;
			padding: 8px;
		}
	}
</style>
