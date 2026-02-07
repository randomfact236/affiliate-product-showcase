<?php
/**
 * Add Product Page - Modern Design
 *
 * Supports both adding new products and editing existing products.
 *
 * @package AffiliateProductShowcase\Admin\Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine if we're editing or adding (these are already set by Menu.php, but we ensure they're available)
if ( ! isset( $post_id ) ) {
    $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
}
if ( ! isset( $is_editing ) ) {
    $is_editing = $post_id > 0;
}

// Get product data if editing (if not already populated by Menu.php)
if ( ! isset( $product_data ) || empty( $product_data ) ) {
    $product_data = [];
    if ( $is_editing ) {
        $post = get_post( $post_id );
        
        if ( $post && $post->post_type === 'aps_product' ) {
            $product_data = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'status' => $post->post_status,
                'content' => $post->post_content,
                'short_description' => $post->post_excerpt,
                'logo' => get_post_meta( $post->ID, '_aps_logo', true ),
                'brand_image' => get_post_meta( $post->ID, '_aps_brand_image', true ),
                'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
                'button_name' => get_post_meta( $post->ID, '_aps_button_name', true ),
                'regular_price' => get_post_meta( $post->ID, '_aps_price', true ),
                'original_price' => get_post_meta( $post->ID, '_aps_original_price', true ),
                'featured' => get_post_meta( $post->ID, '_aps_featured', true ) === '1',
                'rating' => get_post_meta( $post->ID, '_aps_rating', true ),
                'views' => get_post_meta( $post->ID, '_aps_views', true ),
                'user_count' => get_post_meta( $post->ID, '_aps_user_count', true ),
                'reviews' => get_post_meta( $post->ID, '_aps_reviews', true ),
                'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
            ];
            
            $product_data['categories'] = wp_get_object_terms( $post->ID, 'aps_category', [ 'fields' => 'slugs' ] );
            $product_data['tags'] = wp_get_object_terms( $post->ID, 'aps_tag', [ 'fields' => 'slugs' ] );
            $product_data['ribbons'] = wp_get_object_terms( $post->ID, 'aps_ribbon', [ 'fields' => 'slugs' ] );
        } else {
            wp_die(
                sprintf(
                    '<h1>%s</h1><p>%s</p>',
                    esc_html__( 'Invalid Product', 'affiliate-product-showcase' ),
                    esc_html__( 'The product you are trying to edit does not exist or is not the correct type.', 'affiliate-product-showcase' )
                ),
                esc_html__( 'Product Not Found', 'affiliate-product-showcase' ),
                403
            );
        }
    }
}

$form_action = $is_editing ? 'aps_update_product' : 'aps_save_product';
$nonce_action = $is_editing ? 'aps_update_product' : 'aps_save_product';

// Enqueue WordPress media
wp_enqueue_media();
?>

<style>
* {
	margin: 0;
	padding: 0;
	box-sizing: border-box;
}

.aps-modern-form {
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
	max-width: 1200px;
	margin: 20px auto;
}

/* Header */
.aps-form-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20px;
	padding: 20px;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.aps-form-header h1 {
	font-size: 24px;
	font-weight: 600;
	color: #1d2327;
	margin: 0;
}

.aps-close-btn {
	background: #dc3232;
	color: #fff;
	border: none;
	border-radius: 4px;
	width: 36px;
	height: 36px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: background 0.2s;
	font-size: 18px;
}

.aps-close-btn:hover {
	background: #b32d2e;
}

/* Quick Navigation */
.aps-quick-nav {
	display: flex;
	gap: 8px;
	margin-bottom: 20px;
	flex-wrap: wrap;
	background: #fff;
	padding: 15px;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.aps-quick-nav .nav-link {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	background: #f0f0f1;
	color: #1d2327;
	text-decoration: none;
	border-radius: 4px;
	font-size: 14px;
	font-weight: 500;
	transition: all 0.2s;
}

.aps-quick-nav .nav-link:hover {
	background: #2271b1;
	color: #fff;
}

.aps-quick-nav .nav-link .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

/* Form Container */
.aps-form-container {
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	overflow: hidden;
}

/* Sections */
.aps-section {
	padding: 25px;
	border-bottom: 1px solid #dcdcde;
}

.aps-section:last-child {
	border-bottom: none;
}

.section-title {
	font-size: 14px;
	font-weight: 600;
	margin-bottom: 20px;
	color: #1d2327;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

/* Grid Layouts */
.aps-grid-2 {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
}

.aps-grid-3 {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
}

/* Form Fields */
.aps-field-group {
	margin-bottom: 20px;
}

.aps-field-group:last-child {
	margin-bottom: 0;
}

.aps-field-group label {
	display: block;
	font-weight: 500;
	margin-bottom: 6px;
	color: #1d2327;
	font-size: 13px;
}

.required {
	color: #d63638;
}

.aps-input,
.aps-select,
.aps-textarea {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid #8c8f94;
	border-radius: 4px;
	font-size: 14px;
	background: #fff;
	transition: border-color 0.2s, box-shadow 0.2s;
}

.aps-input:focus,
.aps-select:focus,
.aps-textarea:focus {
	outline: none;
	border-color: #2271b1;
	box-shadow: 0 0 0 1px #2271b1;
}

.aps-textarea {
	resize: vertical;
	min-height: 100px;
}

.aps-textarea.aps-full-page {
	min-height: 120px;
}

.aps-readonly {
	background: #f6f7f7 !important;
	color: #2271b1 !important;
	font-weight: 600 !important;
	text-align: center;
}

/* Checkbox */
.aps-checkbox-label {
	display: flex;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	font-weight: 500;
	margin-top: 24px;
}

.aps-checkbox-label input[type="checkbox"] {
	width: 18px;
	height: 18px;
	cursor: pointer;
}

/* Upload Areas */
.aps-upload-group {
	margin-bottom: 10px;
}

.aps-upload-group > label {
	font-size: 13px;
	font-weight: 500;
	margin-bottom: 8px;
	display: block;
}

.aps-upload-area {
	border: 2px dashed #c3c4c7;
	border-radius: 8px;
	padding: 30px 20px;
	text-align: center;
	background: #f6f7f7;
	position: relative;
	transition: all 0.2s;
}

.aps-upload-area:hover {
	border-color: #2271b1;
	background: #f0f6fc;
}

.upload-placeholder {
	color: #646970;
}

.upload-placeholder .dashicons {
	font-size: 40px;
	width: 40px;
	height: 40px;
	margin-bottom: 10px;
	color: #8c8f94;
	display: block;
	margin-left: auto;
	margin-right: auto;
}

.upload-placeholder p {
	font-size: 14px;
	margin: 0;
}

.image-preview {
	margin: 0 0 15px 0;
	border-radius: 4px;
	overflow: hidden;
	display: none;
	max-height: 150px;
}

.image-preview.has-image {
	display: block;
}

.image-preview img {
	max-width: 100%;
	max-height: 150px;
	height: auto;
	display: block;
	margin: 0 auto;
}

.aps-upload-btn {
	background: #2271b1;
	color: #fff;
	border: none;
	padding: 8px 16px;
	border-radius: 4px;
	cursor: pointer;
	font-size: 13px;
	margin: 5px;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: background 0.2s;
}

.aps-upload-btn:hover {
	background: #135e96;
}

.aps-btn-cancel {
	background: #d63638 !important;
}

.aps-btn-cancel:hover {
	background: #b32d2e !important;
}

.aps-hidden {
	display: none !important;
}

.aps-url-input {
	margin-top: 10px;
}

/* Word Counter */
.word-counter {
	text-align: right;
	font-size: 12px;
	color: #646970;
	margin-top: 5px;
}

/* Features */
.aps-feature-list-input-group {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
}

.aps-feature-list-input-group .aps-input {
	flex: 1;
}

.aps-btn {
	padding: 8px 16px;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	font-size: 13px;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: all 0.2s;
	text-decoration: none;
	line-height: 1.5;
}

.aps-btn-primary {
	background: #2271b1;
	color: #fff;
}

.aps-btn-primary:hover {
	background: #135e96;
}

.aps-btn-secondary {
	background: #f0f0f1;
	color: #1d2327;
	border: 1px solid #c3c4c7;
}

.aps-btn-secondary:hover {
	background: #dcdcde;
}

.aps-features-list {
	margin-top: 15px;
}

/* Feature Items */
.feature-item {
	background: #f6f7f7;
	padding: 10px 12px;
	border-radius: 4px;
	margin-bottom: 8px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 14px;
	cursor: grab;
	transition: all 0.2s;
}

.feature-item:hover {
	background: #f0f0f1;
}

.feature-item.dragging {
	opacity: 0.5;
	cursor: grabbing;
}

.feature-item-content {
	display: flex;
	align-items: center;
	gap: 10px;
	flex: 1;
}

.feature-item.drag-handle {
	cursor: grab;
	color: #8c8f94;
	font-size: 16px;
}

.feature-item.drag-handle:active {
	cursor: grabbing;
}

.feature-text {
	flex: 1;
	cursor: text;
	padding: 4px 8px;
	border-radius: 3px;
	transition: background 0.2s;
}

.feature-text:hover {
	background: #f0f6fc;
}

.feature-text.is-bold {
	font-weight: 700;
}

.feature-actions {
	display: flex;
	gap: 6px;
	align-items: center;
}

.feature-btn {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 3px;
	width: 28px;
	height: 28px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 14px;
	color: #646970;
	transition: all 0.2s;
}

.feature-btn:hover {
	background: #f0f0f1;
	color: #1d2327;
}

.feature-btn.active {
	background: #2271b1;
	color: #fff;
	border-color: #2271b1;
}

.feature-btn.move-btn {
	cursor: pointer;
}

.feature-btn.move-btn:disabled {
	opacity: 0.3;
	cursor: not-allowed;
}

.remove-feature {
	background: #d63638;
	color: #fff;
	border: none;
	border-radius: 3px;
	width: 28px;
	height: 28px;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 16px;
	line-height: 1;
}

.remove-feature:hover {
	background: #b32d2e;
}

/* Taxonomy Multi-Select */
.aps-taxonomy-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
}

.aps-multi-select {
	position: relative;
	border: 1px solid #8c8f94;
	border-radius: 4px;
	background: #fff;
	cursor: pointer;
}

.aps-multi-select:hover {
	border-color: #2271b1;
}

.aps-selected-tags {
	padding: 8px 12px;
	min-height: 38px;
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	align-items: center;
	max-width: 100%;
}

.multi-select-placeholder {
	color: #646970;
	font-size: 14px;
}

.aps-dropdown-trigger {
	position: absolute;
	right: 8px;
	top: 50%;
	transform: translateY(-50%);
	color: #646970;
	pointer-events: none;
}

.aps-dropdown {
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	background: #fff;
	border: 1px solid #8c8f94;
	border-top: none;
	border-radius: 0 0 4px 4px;
	max-height: 200px;
	overflow-y: auto;
	z-index: 100;
	box-shadow: 0 2px 6px rgba(0,0,0,0.1);
	display: none;
}

.aps-dropdown.open {
	display: block;
}

.dropdown-item {
	padding: 10px 12px;
	cursor: pointer;
	display: flex;
	align-items: center;
	gap: 8px;
	border-bottom: 1px solid #f0f0f1;
	font-size: 14px;
	transition: background 0.2s;
}

.dropdown-item:last-child {
	border-bottom: none;
}

.dropdown-item:hover {
	background: #f0f6fc;
}

.dropdown-item.selected {
	background: #f0f6fc;
	color: #2271b1;
}

.ribbon-badge-preview {
	padding: 4px 10px;
	border-radius: 4px;
	font-size: 13px;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 4px;
}

.selected-tag {
	background: #2271b1;
	color: #fff;
	padding: 6px 12px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: all 0.2s;
	white-space: nowrap;
	margin: 2px;
}

.selected-tag .tag-icon {
	font-size: 14px;
	line-height: 1;
}

.selected-tag .tag-text {
	line-height: 1;
}

.selected-tag .remove {
	cursor: pointer;
	font-size: 16px;
	line-height: 1;
	margin-left: 4px;
	font-weight: bold;
}

.selected-tag .remove:hover {
	opacity: 0.8;
}

/* JS-generated tag styles (aps-tag, remove-tag) */
.aps-tag {
	background: #2271b1;
	color: #fff;
	padding: 6px 12px;
	border-radius: 20px;
	font-size: 13px;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	transition: all 0.2s;
	white-space: nowrap;
	margin: 2px;
}

.aps-tag .remove-tag {
	cursor: pointer;
	font-size: 16px;
	line-height: 1;
	margin-left: 4px;
	font-weight: bold;
	color: inherit;
}

.aps-tag .remove-tag:hover {
	opacity: 0.8;
}

.aps-tag .remove-tag:focus-visible {
	outline: 2px solid currentColor;
	outline-offset: 2px;
	border-radius: 2px;
}

/* Ribbon tag preview - colors applied via inline styles from JS */
.ribbon-tag-preview {
	padding: 4px 10px;
	border-radius: 4px;
	font-size: 13px;
	font-weight: 500;
	display: inline-flex;
	align-items: center;
	gap: 4px;
}

/* Tags Grid */
.aps-tags-section {
	margin-top: 20px;
}

.aps-tags-section label {
	font-size: 13px;
	font-weight: 500;
	margin-bottom: 10px;
	display: block;
}

.aps-tags-grid {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}

.aps-tag-checkbox {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px 14px;
	background: #f6f7f7;
	border-radius: 20px;
	cursor: pointer;
	font-size: 13px;
	transition: all 0.2s;
	white-space: nowrap;
}

.aps-tag-checkbox:hover {
	background: #f0f6fc;
	transform: translateY(-1px);
}

.aps-tag-checkbox input[type="checkbox"] {
	margin: 0;
	cursor: pointer;
}

.aps-tag-checkbox .tag-icon {
	font-size: 14px;
}

/* Form Actions */
.aps-form-actions {
	padding: 20px 25px;
	background: #f6f7f7;
	border-top: 1px solid #dcdcde;
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 782px) {
	.aps-grid-2,
	.aps-grid-3,
	.aps-taxonomy-grid {
		grid-template-columns: 1fr;
	}

	.aps-quick-nav {
		flex-direction: column;
	}

	.aps-form-actions {
		flex-direction: column;
	}

	.aps-btn {
		width: 100%;
		justify-content: center;
	}
}
</style>

<div class="wrap aps-modern-form">
	<!-- Header -->
	<div class="aps-form-header">
		<h1><?php echo $is_editing ? esc_html__( 'Edit Product', 'affiliate-product-showcase' ) : esc_html__( 'Add Product', 'affiliate-product-showcase' ); ?></h1>
		<button type="button" class="aps-close-btn" onclick="window.location.href='<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>'">
			<span class="dashicons dashicons-no-alt"></span>
		</button>
	</div>

	<!-- Quick Navigation -->
	<nav class="aps-quick-nav" aria-label="Form sections quick navigation">
		<a href="#product-info" class="nav-link"><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Product Info', 'affiliate-product-showcase' ); ?></a>
		<a href="#images" class="nav-link"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Images', 'affiliate-product-showcase' ); ?></a>
		<a href="#affiliate" class="nav-link"><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'Affiliate', 'affiliate-product-showcase' ); ?></a>
		<a href="#features" class="nav-link"><span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Features', 'affiliate-product-showcase' ); ?></a>
		<a href="#pricing" class="nav-link"><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Pricing', 'affiliate-product-showcase' ); ?></a>
		<a href="#taxonomy" class="nav-link"><span class="dashicons dashicons-category"></span> <?php esc_html_e( 'Categories', 'affiliate-product-showcase' ); ?></a>
		<a href="#stats" class="nav-link"><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e( 'Statistics', 'affiliate-product-showcase' ); ?></a>
	</nav>

	<div class="aps-form-container">
		<form method="post" id="aps-product-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( $nonce_action, 'aps_product_nonce' ); ?>
			<?php if ( $is_editing ) : ?>
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<?php endif; ?>

			<!-- Product Info -->
			<section id="product-info" class="aps-section" aria-labelledby="product-info-title">
				<h2 id="product-info-title" class="section-title"><?php esc_html_e( 'Product Info', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-product-title"><?php esc_html_e( 'Product Title', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
						<input type="text" id="aps-product-title" name="aps_title" class="aps-input"
							   placeholder="<?php esc_attr_e( 'Enter title...', 'affiliate-product-showcase' ); ?>" required
							   value="<?php echo esc_attr( $product_data['title'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-product-status"><?php esc_html_e( 'Status', 'affiliate-product-showcase' ); ?></label>
						<select id="aps-product-status" name="aps_status" class="aps-select">
							<option value="draft" <?php selected( $product_data['status'] ?? '', 'draft' ); ?>><?php esc_html_e( 'Draft', 'affiliate-product-showcase' ); ?></option>
							<option value="publish" <?php selected( $product_data['status'] ?? '', 'publish' ); ?>><?php esc_html_e( 'Published', 'affiliate-product-showcase' ); ?></option>
						</select>
					</div>
				</div>
				<div class="aps-field-group">
					<label class="aps-checkbox-label">
						<input type="checkbox" name="aps_featured" value="1" <?php checked( $product_data['featured'] ?? false ); ?>>
						<span><?php esc_html_e( 'Featured Product', 'affiliate-product-showcase' ); ?></span>
					</label>
				</div>
			</section>

			<!-- Images -->
			<section id="images" class="aps-section" aria-labelledby="images-title">
				<h2 id="images-title" class="section-title"><?php esc_html_e( 'Product Images', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-grid-2">
					<div class="aps-upload-group">
						<label><?php esc_html_e( 'Product Image (Featured)', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-image-upload">
							<div class="upload-placeholder" id="aps-image-placeholder" <?php echo ! empty( $product_data['logo'] ) ? 'style="display:none;"' : ''; ?>>
								<span class="dashicons dashicons-camera"></span>
								<p><?php esc_html_e( 'Click to upload or enter URL below', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview <?php echo ! empty( $product_data['logo'] ) ? 'has-image' : ''; ?>" id="aps-image-preview">
								<?php if ( ! empty( $product_data['logo'] ) ) : ?>
									<img src="<?php echo esc_url( $product_data['logo'] ); ?>" alt="">
								<?php endif; ?>
							</div>
							<input type="hidden" name="aps_image_url" id="aps-image-url" value="<?php echo esc_url( $product_data['logo'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-image-btn">
								<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Select from Media Library', 'affiliate-product-showcase' ); ?>
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel <?php echo empty( $product_data['logo'] ) ? 'aps-hidden' : ''; ?>" id="aps-remove-image-btn">
								<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Remove', 'affiliate-product-showcase' ); ?>
							</button>
						</div>
						<div class="aps-url-input">
							<input type="url" id="aps-image-url-input" class="aps-input"
								   placeholder="https://..." value="<?php echo esc_url( $product_data['logo'] ?? '' ); ?>">
						</div>
					</div>
					<div class="aps-upload-group">
						<label><?php esc_html_e( 'Brand Logo', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-brand-upload">
							<div class="upload-placeholder" id="aps-brand-placeholder" <?php echo ! empty( $product_data['brand_image'] ) ? 'style="display:none;"' : ''; ?>>
								<span class="dashicons dashicons-format-image"></span>
								<p><?php esc_html_e( 'Click to upload or enter URL below', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview <?php echo ! empty( $product_data['brand_image'] ) ? 'has-image' : ''; ?>" id="aps-brand-preview">
								<?php if ( ! empty( $product_data['brand_image'] ) ) : ?>
									<img src="<?php echo esc_url( $product_data['brand_image'] ); ?>" alt="">
								<?php endif; ?>
							</div>
							<input type="hidden" name="aps_brand_image_url" id="aps-brand-image-url" value="<?php echo esc_url( $product_data['brand_image'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-brand-btn">
								<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Select from Media Library', 'affiliate-product-showcase' ); ?>
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel <?php echo empty( $product_data['brand_image'] ) ? 'aps-hidden' : ''; ?>" id="aps-remove-brand-btn">
								<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Remove', 'affiliate-product-showcase' ); ?>
							</button>
						</div>
						<div class="aps-url-input">
							<input type="url" id="aps-brand-url-input" class="aps-input"
								   placeholder="https://..." value="<?php echo esc_url( $product_data['brand_image'] ?? '' ); ?>">
						</div>
					</div>
				</div>
			</section>

			<!-- Affiliate -->
			<section id="affiliate" class="aps-section" aria-labelledby="affiliate-title">
				<h2 id="affiliate-title" class="section-title"><?php esc_html_e( 'Affiliate Details', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-affiliate-url"><?php esc_html_e( 'Affiliate URL', 'affiliate-product-showcase' ); ?></label>
						<input type="url" id="aps-affiliate-url" name="aps_affiliate_url" class="aps-input"
							   placeholder="https://example.com/..."
							   value="<?php echo esc_url( $product_data['affiliate_url'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-button-name"><?php esc_html_e( 'Button Name', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-button-name" name="aps_button_name" class="aps-input"
							   placeholder="<?php esc_attr_e( 'Get Deal', 'affiliate-product-showcase' ); ?>"
							   value="<?php echo esc_attr( $product_data['button_name'] ?? '' ); ?>">
					</div>
				</div>
			</section>

			<!-- Short Description -->
			<section id="short-description" class="aps-section" aria-labelledby="short-description-title">
				<h2 id="short-description-title" class="section-title"><?php esc_html_e( 'Short Description', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-field-group">
					<label for="aps-short-description"><?php esc_html_e( 'Short Description', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
					<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea aps-full-page"
							  placeholder="<?php esc_attr_e( 'Enter short description (max 40 words)...', 'affiliate-product-showcase' ); ?>" required
							  data-initial="<?php echo esc_attr( $product_data['short_description'] ?? '' ); ?>"><?php echo esc_textarea( $product_data['short_description'] ?? '' ); ?></textarea>
					<div class="word-counter"><span id="aps-word-count" aria-live="polite" aria-atomic="true">0</span>/40 <?php esc_html_e( 'words', 'affiliate-product-showcase' ); ?></div>
				</div>
			</section>

			<!-- Features -->
			<section id="features" class="aps-section" aria-labelledby="features-title">
				<h2 id="features-title" class="section-title"><?php esc_html_e( 'Feature List', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-feature-list-input-group">
					<input type="text" id="aps-new-feature" class="aps-input" placeholder="<?php esc_attr_e( 'Add new feature...', 'affiliate-product-showcase' ); ?>">
					<button type="button" class="aps-btn aps-btn-primary" id="aps-add-feature">
						<span class="dashicons dashicons-plus-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php esc_html_e( 'Add', 'affiliate-product-showcase' ); ?>
					</button>
				</div>
				<div class="aps-features-list" id="aps-features-list">
					<?php 
					$features_data = $product_data['features'] ?? [];
					// Convert simple array to structured format if needed
					$structured_features = [];
					foreach ( $features_data as $feature ) {
						if ( is_array( $feature ) ) {
							$structured_features[] = $feature;
						} else {
							$structured_features[] = [ 'text' => $feature, 'bold' => false ];
						}
					}
					foreach ( $structured_features as $index => $feature ) : 
						$is_bold = $feature['bold'] ?? false;
						$text = $feature['text'] ?? $feature;
					?>
						<div class="feature-item" data-index="<?php echo esc_attr( $index ); ?>" data-bold="<?php echo $is_bold ? '1' : '0'; ?>">
							<div class="feature-item-content">
								<span class="dashicons dashicons-menu drag-handle"></span>
								<span class="feature-text <?php echo $is_bold ? 'is-bold' : ''; ?>"><?php echo esc_html( $text ); ?></span>
							</div>
							<div class="feature-actions">
								<button type="button" class="feature-btn move-btn move-up" title="<?php esc_attr_e( 'Move up', 'affiliate-product-showcase' ); ?>" <?php echo $index === 0 ? 'disabled' : ''; ?>>
									<span class="dashicons dashicons-arrow-up-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span>
								</button>
								<button type="button" class="feature-btn move-btn move-down" title="<?php esc_attr_e( 'Move down', 'affiliate-product-showcase' ); ?>" <?php echo $index === count( $structured_features ) - 1 ? 'disabled' : ''; ?>>
									<span class="dashicons dashicons-arrow-down-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span>
								</button>
								<button type="button" class="feature-btn bold-btn <?php echo $is_bold ? 'active' : ''; ?>" title="<?php esc_attr_e( 'Bold', 'affiliate-product-showcase' ); ?>">
									<span class="dashicons dashicons-editor-bold" style="font-size: 12px; width: 12px; height: 12px;"></span>
								</button>
								<button type="button" class="remove-feature">&times;</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<input type="hidden" name="aps_features" id="aps-features-input" value="<?php echo esc_attr( json_encode( $structured_features ) ); ?>">
			</section>

			<!-- Pricing -->
			<section id="pricing" class="aps-section" aria-labelledby="pricing-title">
				<h2 id="pricing-title" class="section-title"><?php esc_html_e( 'Pricing', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-current-price"><?php esc_html_e( 'Current Price', 'affiliate-product-showcase' ); ?> <span class="required">*</span></label>
						<input type="number" id="aps-current-price" name="aps_current_price" class="aps-input"
							   step="0.01" min="0" placeholder="30.00" required
							   value="<?php echo esc_attr( $product_data['regular_price'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-original-price"><?php esc_html_e( 'Original Price', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-original-price" name="aps_original_price" class="aps-input"
							   step="0.01" min="0" placeholder="60.00"
							   value="<?php echo esc_attr( $product_data['original_price'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Discount', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-discount" class="aps-input aps-readonly" readonly value="0% OFF">
					</div>
				</div>
			</section>

			<!-- Taxonomy -->
			<section id="taxonomy" class="aps-section" aria-labelledby="taxonomy-title">
				<h2 id="taxonomy-title" class="section-title"><?php esc_html_e( 'Categories & Ribbons', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-taxonomy-grid">
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Category', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-multi-select" id="aps-categories-select">
							<div class="aps-selected-tags" id="aps-selected-categories">
								<span class="multi-select-placeholder"><?php esc_html_e( 'Select categories...', 'affiliate-product-showcase' ); ?></span>
							</div>
							<span class="aps-dropdown-trigger dashicons dashicons-arrow-down-alt2"></span>
							<div class="aps-dropdown" id="aps-categories-dropdown">
								<?php
								$categories = get_terms( [ 'taxonomy' => 'aps_category', 'hide_empty' => false ] );
								$selected_cats = $product_data['categories'] ?? [];
								foreach ( $categories as $category ) :
									$is_selected = in_array( $category->slug, $selected_cats, true );
									$cat_icon = get_term_meta( $category->term_id, '_aps_category_icon', true ) ?: '📁';
									$cat_color = get_term_meta( $category->term_id, '_aps_category_color', true ) ?: '#2271b1';
									?>
									<div class="dropdown-item <?php echo $is_selected ? 'selected' : ''; ?>" 
										 data-value="<?php echo esc_attr( $category->slug ); ?>"
										 data-name="<?php echo esc_attr( $category->name ); ?>"
										 data-icon="<?php echo esc_attr( $cat_icon ); ?>"
										 data-color="<?php echo esc_attr( $cat_color ); ?>">
										<span style="margin-right: 8px;"><?php echo esc_html( $cat_icon ); ?></span>
										<span class="taxonomy-name"><?php echo esc_html( $category->name ); ?></span>
									</div>
									<?php
								endforeach;
								?>
							</div>
						</div>
						<input type="hidden" name="aps_categories" id="aps-categories-input" value="<?php echo esc_attr( implode( ',', $selected_cats ) ); ?>">
					</div>
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Ribbon Badge', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-multi-select" id="aps-ribbons-select">
							<div class="aps-selected-tags" id="aps-selected-ribbons">
								<span class="multi-select-placeholder"><?php esc_html_e( 'Select ribbons...', 'affiliate-product-showcase' ); ?></span>
							</div>
							<span class="aps-dropdown-trigger dashicons dashicons-arrow-down-alt2"></span>
							<div class="aps-dropdown" id="aps-ribbons-dropdown">
								<div class="dropdown-item <?php echo empty( $product_data['ribbons'] ) ? 'selected' : ''; ?>" data-value="" data-name="<?php echo esc_attr( __( 'None', 'affiliate-product-showcase' ) ); ?>" data-icon="○" data-color="#646970">
									<span class="ribbon-icon" style="margin-right: 8px;">○</span> <span class="ribbon-name"><?php esc_html_e( 'None', 'affiliate-product-showcase' ); ?></span>
								</div>
								<?php
								$ribbons = get_terms( [ 'taxonomy' => 'aps_ribbon', 'hide_empty' => false ] );
								$selected_ribbons = $product_data['ribbons'] ?? [];
								foreach ( $ribbons as $ribbon ) :
									$is_selected = in_array( $ribbon->slug, $selected_ribbons, true );
									$bg_color = get_term_meta( $ribbon->term_id, '_aps_ribbon_bg_color', true ) ?: '#2271b1';
									$color = get_term_meta( $ribbon->term_id, '_aps_ribbon_color', true ) ?: '#fff';
									$ribbon_icon = get_term_meta( $ribbon->term_id, '_aps_ribbon_icon', true ) ?: '🏷️';
									?>
									<div class="dropdown-item <?php echo $is_selected ? 'selected' : ''; ?>" 
										 data-value="<?php echo esc_attr( $ribbon->slug ); ?>"
										 data-name="<?php echo esc_attr( $ribbon->name ); ?>"
										 data-icon="<?php echo esc_attr( $ribbon_icon ); ?>"
										 data-color="<?php echo esc_attr( $bg_color ); ?>">
										<span class="ribbon-badge-preview" style="background: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $color ); ?>">
											<span class="ribbon-icon" style="margin-right: 4px;"><?php echo esc_html( $ribbon_icon ); ?></span>
											<span class="ribbon-name"><?php echo esc_html( $ribbon->name ); ?></span>
										</span>
									</div>
									<?php
								endforeach;
								?>
							</div>
						</div>
						<input type="hidden" name="aps_ribbons" id="aps-ribbons-input" value="<?php echo esc_attr( implode( ',', $selected_ribbons ) ); ?>">
					</div>
				</div>

				<!-- Tags -->
				<div class="aps-tags-section">
					<label><?php esc_html_e( 'Tags', 'affiliate-product-showcase' ); ?></label>
					<div class="aps-tags-grid">
						<?php
						$all_tags = get_terms( [ 'taxonomy' => 'aps_tag', 'hide_empty' => false ] );
						$selected_tags = $product_data['tags'] ?? [];
						foreach ( $all_tags as $tag ) :
							$is_checked = in_array( $tag->slug, $selected_tags, true );
							$icon = get_term_meta( $tag->term_id, '_aps_tag_icon', true ) ?: '🏷️';
							?>
							<label class="aps-tag-checkbox">
								<input type="checkbox" name="aps_tags[]" value="<?php echo esc_attr( $tag->slug ); ?>" <?php checked( $is_checked ); ?>>
								<span class="tag-icon"><?php echo esc_html( $icon ); ?></span>
								<span><?php echo esc_html( $tag->name ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<!-- Statistics -->
			<section id="stats" class="aps-section" aria-labelledby="stats-title">
				<h2 id="stats-title" class="section-title"><?php esc_html_e( 'Product Statistics', 'affiliate-product-showcase' ); ?></h2>
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-rating"><?php esc_html_e( 'Rating', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-rating" name="aps_rating" class="aps-input"
							   step="0.1" min="0" max="5" placeholder="4.5"
							   value="<?php echo esc_attr( $product_data['rating'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-views"><?php esc_html_e( 'Views', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-views" name="aps_views" class="aps-input"
							   min="0" placeholder="325"
							   value="<?php echo esc_attr( $product_data['views'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-user-count"><?php esc_html_e( 'User Count', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-user-count" name="aps_user_count" class="aps-input"
							   placeholder="1.5K"
							   value="<?php echo esc_attr( $product_data['user_count'] ?? '' ); ?>">
					</div>
				</div>
				<div class="aps-field-group">
					<label for="aps-reviews"><?php esc_html_e( 'No. of Reviews', 'affiliate-product-showcase' ); ?></label>
					<input type="number" id="aps-reviews" name="aps_reviews" class="aps-input"
						   min="0" placeholder="12" style="max-width: 200px;"
						   value="<?php echo esc_attr( $product_data['reviews'] ?? '' ); ?>">
				</div>
			</section>

			<!-- Form Actions -->
			<div class="aps-form-actions">
				<?php if ( ! $is_editing ) : ?>
					<button type="submit" name="action" value="aps_save_product_draft" class="aps-btn aps-btn-secondary">
						<span class="dashicons dashicons-media-document" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php esc_html_e( 'Save Draft', 'affiliate-product-showcase' ); ?>
					</button>
				<?php endif; ?>
				<button type="submit" name="action" value="<?php echo esc_attr( $form_action ); ?>" class="aps-btn aps-btn-primary">
					<span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php echo $is_editing ? esc_html__( 'Update Product', 'affiliate-product-showcase' ) : esc_html__( 'Publish Product', 'affiliate-product-showcase' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-btn aps-btn-cancel">
					<span class="dashicons dashicons-no-alt" style="font-size: 14px; width: 14px; height: 14px;"></span> <?php esc_html_e( 'Cancel', 'affiliate-product-showcase' ); ?>
				</a>
			</div>
		</form>
	</div>
</div>
