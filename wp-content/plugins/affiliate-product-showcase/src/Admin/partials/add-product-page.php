<?php
/**
 * Add Product Page - WooCommerce-Style Custom Editor
 *
 * @package AffiliateProductShowcase\Admin\Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current tab
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';

// Available tabs
$tabs = [
	'general'  => __( 'General', 'affiliate-product-showcase' ),
	'details'  => __( 'Product Details', 'affiliate-product-showcase' ),
	'advanced' => __( 'Advanced', 'affiliate-product-showcase' ),
];
?>

<div class="wrap affiliate-product-showcase">
	<!-- Header with Badge -->
	<div class="aps-page-header">
		<h1>
			<?php esc_html_e( 'Add New Affiliate Product', 'affiliate-product-showcase' ); ?>
			<span class="aps-header-badge"><?php esc_html_e( 'WooCommerce-Style', 'affiliate-product-showcase' ); ?></span>
		</h1>
	</div>
	
	<!-- Color Legend -->
	<div class="aps-color-legend">
		<div class="legend-item">
			<div class="legend-color legend-required"></div>
			<span><?php esc_html_e( 'Required Field', 'affiliate-product-showcase' ); ?></span>
		</div>
		<div class="legend-item">
			<div class="legend-color legend-optional"></div>
			<span><?php esc_html_e( 'Optional Field', 'affiliate-product-showcase' ); ?></span>
		</div>
	</div>
	
	<!-- WooCommerce-Style Tabs -->
	<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_slug => $tab_label ) : ?>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product&page=add-product&tab=' . $tab_slug ) ); ?>"
			   class="nav-tab <?php echo $current_tab === $tab_slug ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</h2>
	
	<!-- Form Container -->
	<div class="woocommerce-product-form-container">
		<form method="post" id="aps-product-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'aps_save_product', 'aps_product_nonce' ); ?>
			
			<!-- Tab Content -->
			<div class="product-tab-content">
				<?php if ( $current_tab === 'general' ) : ?>
					<?php require \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/tabs/tab-general.php' ); ?>
				<?php elseif ( $current_tab === 'details' ) : ?>
					<?php require \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/tabs/tab-details.php' ); ?>
				<?php elseif ( $current_tab === 'advanced' ) : ?>
					<?php require \AffiliateProductShowcase\Plugin\Constants::viewPath( 'src/Admin/partials/tabs/tab-advanced.php' ); ?>
				<?php endif; ?>
			</div>
			
			<!-- Action Buttons -->
			<div class="product-form-actions">
				<input type="hidden" name="action" value="aps_save_product">
				<input type="hidden" name="current_tab" value="<?php echo esc_attr( $current_tab ); ?>">
				
				<button type="submit" class="button button-primary button-large" name="publish">
					<span class="dashicons dashicons-saved" style="margin-top:4px;"></span>
					<?php esc_html_e( 'Publish Product', 'affiliate-product-showcase' ); ?>
				</button>
				
				<button type="submit" class="button button-large" name="draft">
					<span class="dashicons dashicons-list-view" style="margin-top:4px;"></span>
					<?php esc_html_e( 'Save Draft', 'affiliate-product-showcase' ); ?>
				</button>
				
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'affiliate-product-showcase' ); ?>
				</a>
			</div>
		</form>
	</div>
</div>

	<style>
	/* Page Header */
	.aps-page-header {
		background: #2271b1;
		padding: 20px 30px;
		border-radius: 4px 4px 0 0;
		margin: -20px -20px 30px -20px;
	}
	
	.aps-page-header h1 {
		font-size: 23px;
		font-weight: 400;
		color: #fff;
		margin: 0;
		display: flex;
		align-items: center;
		gap: 12px;
	}
	
	.aps-header-badge {
		background: #135e96;
		padding: 4px 12px;
		border-radius: 20px;
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
	}
	
	/* Color Legend */
	.aps-color-legend {
		display: flex;
		gap: 20px;
		padding: 15px 20px;
		background: #f0f6fc;
		border-left: 4px solid #2271b1;
		margin-bottom: 24px;
		border-radius: 0 4px 4px 0;
	}
	
	.aps-color-legend .legend-item {
		display: flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		color: #1d2327;
	}
	
	.legend-color {
		width: 16px;
		height: 16px;
		border-radius: 3px;
	}
	
	.legend-required {
		background: #d63638;
	}
	
	.legend-optional {
		background: #8c8f94;
	}
	
	/* WooCommerce-Style Tabs */
	.woo-nav-tab-wrapper {
		margin: 20px 0 30px 0;
		padding: 0;
		border-bottom: 1px solid #dcdcde;
	}
	
	.woo-nav-tab-wrapper .nav-tab {
		display: inline-block;
		padding: 10px 16px;
		margin-right: 5px;
		background: #f6f7f7;
		border: 1px solid #dcdcde;
		border-bottom: none;
		border-radius: 4px 4px 0 0;
		font-size: 14px;
		font-weight: 500;
		color: #1d2327;
		text-decoration: none;
		cursor: pointer;
		transition: all .2s;
	}
	
	.woo-nav-tab-wrapper .nav-tab:hover {
		background: #fff;
		border-color: #2271b1;
	}
	
	.woo-nav-tab-wrapper .nav-tab-active {
		background: #fff;
		border-color: #2271b1;
		border-bottom: 1px solid #fff;
		margin-bottom: -1px;
		font-weight: 600;
	}
	
	/* Form Container */
	.woocommerce-product-form-container {
		background: #fff;
		border: 1px solid #dcdcde;
		border-radius: 4px;
		box-shadow: 0 1px 2px rgba(0,0,0,.05);
		max-width: 1200px;
	}
	
	.product-tab-content {
		padding: 30px;
	}
	
	/* WooCommerce-Style Data Panel */
	.woocommerce-data-panel {
		border: 1px solid #dcdcde;
		border-radius: 4px;
		margin-bottom: 20px;
	}
	
	.woocommerce-data-panel .panel-header {
		background: #f6f7f7;
		padding: 12px 20px;
		border-bottom: 1px solid #dcdcde;
		border-radius: 4px 4px 0 0;
		font-weight: 600;
		font-size: 14px;
		color: #1d2327;
	}
	
	.woocommerce-data-panel .panel-body {
		padding: 20px;
	}
	
	/* WooCommerce-Style Inputs */
	.woocommerce-input-wrapper {
		margin-bottom: 20px;
	}
	
	.woocommerce-input-wrapper label {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
		font-size: 13px;
		color: #1d2327;
	}
	
	.woocommerce-input-wrapper .description {
		display: block;
		margin-top: 6px;
		font-size: 12px;
		font-style: italic;
		color: #646970;
	}
	
	.woocommerce-input {
		width: 100%;
		max-width: 400px;
		padding: 10px 12px;
		font-size: 14px;
		border: 1px solid #8c8f94;
		border-radius: 3px;
		box-shadow: 0 1px 2px rgba(0,0,0,.05);
		transition: all .2s;
	}
	
	.woocommerce-input:focus {
		border-color: #2271b1;
		box-shadow: 0 0 0 3px rgba(34, 113, 177, .1);
		outline: none;
	}
	
	.woocommerce-textarea {
		min-height: 120px;
		resize: vertical;
	}
	
	.woocommerce-select {
		width: 100%;
		max-width: 400px;
		padding: 10px 36px 10px 12px;
		font-size: 14px;
		border: 1px solid #8c8f94;
		border-radius: 3px;
		background: #fff;
		box-shadow: 0 1px 2px rgba(0,0,0,.05);
		appearance: none;
		cursor: pointer;
	}
	
	/* Price Input Group */
	.woocommerce-price-wrapper {
		display: flex;
		align-items: center;
		max-width: 400px;
	}
	
	.woocommerce-price-wrapper .woocommerce-input {
		flex: 1;
		border-top-right-radius: 0;
		border-bottom-right-radius: 0;
	}
	
	.woocommerce-price-wrapper .woocommerce-input:focus {
		z-index: 1;
	}
	
	.woocommerce-price-symbol {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 0 12px;
		background: #f6f7f7;
		border: 1px solid #8c8f94;
		border-left: none;
		color: #646970;
		font-weight: 600;
		font-size: 14px;
		border-top-right-radius: 3px;
		border-bottom-right-radius: 3px;
		min-width: 50px;
	}
	
	/* Checkbox */
	.woocommerce-checkbox-wrapper label {
		display: flex;
		align-items: center;
		gap: 10px;
		cursor: pointer;
	}
	
	.woocommerce-checkbox-wrapper input[type="checkbox"] {
		width: 20px;
		height: 20px;
		margin: 0;
		cursor: pointer;
	}
	
	/* Action Buttons */
	.product-form-actions {
		padding: 20px 30px;
		background: #f6f7f7;
		border-top: 1px solid #dcdcde;
		border-radius: 0 0 4px 4px;
		display: flex;
		align-items: center;
		gap: 15px;
	}
	
	.product-form-actions .button {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		padding: 0 24px;
		height: 40px;
		font-size: 15px;
		font-weight: 600;
		border-radius: 3px;
		transition: all .2s;
	}
	
	.product-form-actions .button-primary {
		background: #2271b1;
		border-color: #2271b1;
		color: #fff;
	}
	
	.product-form-actions .button-primary:hover {
		background: #135e96;
		border-color: #135e96;
	}
	
	.product-form-actions .button-large {
		height: 44px;
		font-size: 16px;
	}
	
	/* Section Headings */
	.woocommerce-section-title {
		font-size: 16px;
		font-weight: 600;
		color: #1d2327;
		margin: 0 0 15px 0;
		padding-bottom: 10px;
		border-bottom: 1px solid #dcdcde;
	}
	
	/* Grid for 2-column layout */
	.woocommerce-grid-2 {
		display: grid;
		grid-template-columns: repeat(2, 1fr);
		gap: 20px;
	}
	
	@media (max-width: 782px) {
		.woocommerce-grid-2 {
			grid-template-columns: 1fr;
		}
	}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab switching
	$('.woo-nav-tab-wrapper .nav-tab').on('click', function(e) {
		e.preventDefault();
	});
});
</script>
