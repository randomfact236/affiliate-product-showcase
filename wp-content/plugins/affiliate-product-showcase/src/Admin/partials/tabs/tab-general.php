<?php
/**
 * Tab: General - Basic Product Information
 *
 * @package AffiliateProductShowcase\Admin\Partials\Tabs
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Product Information Panel -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">📝</span>
		<span><?php esc_html_e( 'Product Information', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_title"><?php esc_html_e( 'Product Name', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
			<input type="text" id="aps_title" name="aps_title" class="woocommerce-input" placeholder="<?php esc_attr_e( 'e.g., Apple MacBook Pro 14-inch', 'affiliate-product-showcase' ); ?>" required>
			<span class="description"><?php esc_html_e( 'The name of your affiliate product', 'affiliate-product-showcase' ); ?></span>
		</div>

		<div class="woocommerce-input-wrapper">
			<label for="aps_description"><?php esc_html_e( 'Product Description', 'affiliate-product-showcase' ); ?></label>
			<textarea id="aps_description" name="aps_description" class="woocommerce-input woocommerce-textarea" rows="5" placeholder="<?php esc_attr_e( 'Describe your product features and benefits...', 'affiliate-product-showcase' ); ?>"></textarea>
			<span class="description"><?php esc_html_e( 'Detailed description for product page and SEO', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Pricing Panel -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">💰</span>
		<span><?php esc_html_e( 'Pricing', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-grid-2">
			<div class="woocommerce-input-wrapper">
				<label for="aps_regular_price"><?php esc_html_e( 'Regular Price', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
				<div class="woocommerce-price-wrapper">
					<input type="number" id="aps_regular_price" name="aps_regular_price" class="woocommerce-input" step="0.01" min="0" placeholder="0.00" required>
					<div class="woocommerce-price-currency">USD</div>
				</div>
				<span class="description"><?php esc_html_e( 'Standard price for this product', 'affiliate-product-showcase' ); ?></span>
			</div>
			
			<div class="woocommerce-input-wrapper">
				<label for="aps_sale_price"><?php esc_html_e( 'Sale Price', 'affiliate-product-showcase' ); ?></label>
				<div class="woocommerce-price-wrapper">
					<input type="number" id="aps_sale_price" name="aps_sale_price" class="woocommerce-input" step="0.01" min="0" placeholder="0.00">
					<div class="woocommerce-price-currency">USD</div>
				</div>
				<span class="description"><?php esc_html_e( 'Discounted price (optional)', 'affiliate-product-showcase' ); ?></span>
			</div>
		</div>

		<div class="woocommerce-input-wrapper">
			<label for="aps_currency"><?php esc_html_e( 'Currency', 'affiliate-product-showcase' ); ?></label>
			<select id="aps_currency" name="aps_currency" class="woocommerce-select">
				<option value="USD">USD ($)</option>
				<option value="EUR">EUR (€)</option>
				<option value="GBP">GBP (£)</option>
				<option value="JPY">JPY (¥)</option>
				<option value="AUD">AUD (A$)</option>
				<option value="CAD">CAD (C$)</option>
				<option value="INR">INR (₹)</option>
			</select>
			<span class="description"><?php esc_html_e( 'Select product currency', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<!-- Affiliate Link Panel -->
<div class="woocommerce-data-panel">
	<div class="panel-header">
		<span class="panel-icon">🔗</span>
		<span><?php esc_html_e( 'Affiliate Link', 'affiliate-product-showcase' ); ?></span>
	</div>
	<div class="panel-body">
		<div class="woocommerce-input-wrapper">
			<label for="aps_affiliate_url"><?php esc_html_e( 'Affiliate URL', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
			<input type="url" id="aps_affiliate_url" name="aps_affiliate_url" class="woocommerce-input" placeholder="<?php esc_attr_e( 'https://amazon.com/product', 'affiliate-product-showcase' ); ?>" required>
			<span class="description"><?php esc_html_e( 'Your affiliate tracking link for this product', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
</div>

<style>
	/* Panel Icons */
	.panel-icon {
		font-size: 16px;
		margin-right: 8px;
	}
	
	/* Required Asterisk */
	.required {
		color: #d63638;
		margin-left: 2px;
		font-weight: bold;
	}
	
	/* Price Currency Wrapper - Updated to match mockup */
	.woocommerce-price-currency {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 0 16px;
		background: #f6f7f7;
		border: 1px solid #8c8f94;
		border-left: none;
		color: #646970;
		font-weight: 600;
		font-size: 14px;
		border-top-right-radius: 3px;
		border-bottom-right-radius: 3px;
		min-width: 60px;
		height: 42px;
	}
</style>
