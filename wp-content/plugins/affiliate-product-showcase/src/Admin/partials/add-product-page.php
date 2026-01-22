<?php
/**
 * Add Product Page - Single-Page Form with Quick Navigation
 *
 * @package AffiliateProductShowcase\Admin\Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Font Awesome
wp_enqueue_style( 'aps-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
// Load Google Fonts - Inter
wp_enqueue_style( 'aps-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap', [], null );
?>

<div class="wrap affiliate-product-showcase">
	<!-- Header -->
	<div class="aps-header">
		<h1><?php esc_html_e( 'Edit Product', 'affiliate-product-showcase' ); ?></h1>
		<button type="button" class="aps-close-btn" onclick="window.location.href='<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>'">
			<i class="fas fa-times"></i>
		</button>
	</div>
	
	<!-- Quick Navigation -->
	<div class="aps-quick-nav">
		<a href="#product-info" class="nav-link">
			<i class="fas fa-edit"></i> <?php esc_html_e( 'Product Info', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#images" class="nav-link">
			<i class="fas fa-images"></i> <?php esc_html_e( 'Images', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#affiliate" class="nav-link">
			<i class="fas fa-link"></i> <?php esc_html_e( 'Affiliate', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#features" class="nav-link">
			<i class="fas fa-list"></i> <?php esc_html_e( 'Features', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#pricing" class="nav-link">
			<i class="fas fa-tag"></i> <?php esc_html_e( 'Pricing', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#taxonomy" class="nav-link">
			<i class="fas fa-folder"></i> <?php esc_html_e( 'Categories & Tags', 'affiliate-product-showcase' ); ?>
		</a>
		<a href="#stats" class="nav-link">
			<i class="fas fa-chart-bar"></i> <?php esc_html_e( 'Stats', 'affiliate-product-showcase' ); ?>
		</a>
	</div>
	
	<!-- Form Container -->
	<div class="aps-form-container">
		<form method="post" id="aps-product-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'aps_save_product', 'aps_product_nonce' ); ?>
			
			<!-- 1. Product Info -->
			<section id="product-info" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRODUCT INFO', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-product-title">
							<?php esc_html_e( 'Product Title', 'affiliate-product-showcase' ); ?>
							<span class="required">*</span>
						</label>
						<input type="text" id="aps-product-title" name="aps_product_title" class="aps-input"
							   placeholder="<?php esc_attr_e( 'Enter title...', 'affiliate-product-showcase' ); ?>" required>
					</div>
					
					<div class="aps-field-group">
						<label for="aps-product-status"><?php esc_html_e( 'Status', 'affiliate-product-showcase' ); ?></label>
						<select id="aps-product-status" name="aps_product_status" class="aps-select">
							<option value="draft"><?php esc_html_e( 'Draft', 'affiliate-product-showcase' ); ?></option>
							<option value="publish"><?php esc_html_e( 'Published', 'affiliate-product-showcase' ); ?></option>
						</select>
					</div>
				</div>
				
				<div class="aps-field-group">
					<label class="aps-checkbox-label">
						<input type="checkbox" id="aps-featured" name="aps_featured" value="1">
						<span><?php esc_html_e( 'Featured Product', 'affiliate-product-showcase' ); ?></span>
					</label>
				</div>
			</section>
			
			<!-- 2. Product Images -->
			<section id="images" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRODUCT IMAGES', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-2">
					<!-- Logo Upload -->
					<div class="aps-upload-group">
						<label><?php esc_html_e( 'Upload Logo', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-logo-upload">
							<div class="upload-placeholder">
								<i class="fas fa-camera"></i>
								<p><?php esc_html_e( 'Drag & Drop', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview" id="aps-logo-preview"></div>
							<input type="file" name="aps_logo_image" id="aps-logo-input" accept="image/*" style="display:none;">
						</div>
						<div class="aps-url-input">
							<input type="url" name="aps_logo_url" class="aps-input"
								   placeholder="https://..." id="aps-logo-url-input">
						</div>
					</div>
					
					<!-- Brand Image Upload -->
					<div class="aps-upload-group">
						<label><?php esc_html_e( 'Brand Image', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-brand-upload">
							<div class="upload-placeholder">
								<i class="fas fa-tshirt"></i>
								<p><?php esc_html_e( 'Drag & Drop', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview" id="aps-brand-preview"></div>
							<input type="file" name="aps_brand_image" id="aps-brand-input" accept="image/*" style="display:none;">
						</div>
						<div class="aps-url-input">
							<input type="url" name="aps_brand_url" class="aps-input"
								   placeholder="https://..." id="aps-brand-url-input">
						</div>
					</div>
				</div>
			</section>
			
			<!-- 3. Affiliate Details -->
			<section id="affiliate" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'AFFILIATE DETAILS', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-affiliate-url"><?php esc_html_e( 'Affiliate URL', 'affiliate-product-showcase' ); ?></label>
						<input type="url" id="aps-affiliate-url" name="aps_affiliate_url" class="aps-input"
							   placeholder="https://example.com/...">
					</div>
					
					<div class="aps-field-group">
						<label for="aps-button-name"><?php esc_html_e( 'Button Name', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-button-name" name="aps_button_name" class="aps-input"
							   placeholder="Buy Now">
					</div>
				</div>
			</section>
			
			<!-- 4. Short Description -->
			<section class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'SHORT DESCRIPTION', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-field-group">
					<label for="aps-short-description">
						<?php esc_html_e( 'Short Description', 'affiliate-product-showcase' ); ?>
						<span class="required">*</span>
					</label>
					<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea"
							  rows="4" maxlength="200"
							  placeholder="<?php esc_attr_e( 'Enter short description (max 40 words)...', 'affiliate-product-showcase' ); ?>" required></textarea>
					<div class="word-counter">
						<span id="aps-word-count">0</span>/40 <?php esc_html_e( 'Words', 'affiliate-product-showcase' ); ?>
					</div>
				</div>
			</section>
			
			<!-- 5. Feature List -->
			<section id="features" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'FEATURE LIST', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-new-feature"><?php esc_html_e( 'Add new feature', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-new-feature" class="aps-input"
							   placeholder="<?php esc_attr_e( 'Enter feature...', 'affiliate-product-showcase' ); ?>">
					</div>
					<div class="aps-field-group" style="display:flex; align-items:flex-end;">
						<button type="button" class="aps-btn aps-btn-secondary" id="aps-add-feature">
							<i class="fas fa-plus"></i> <?php esc_html_e( 'Add', 'affiliate-product-showcase' ); ?>
						</button>
					</div>
				</div>
				
				<div class="aps-features-list" id="aps-features-list">
					<!-- Features will be added dynamically here -->
				</div>
				<input type="hidden" name="aps_features" id="aps-features-input">
			</section>
			
			<!-- 6. Pricing -->
			<section id="pricing" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRICING', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-current-price">
							<?php esc_html_e( 'Current Price', 'affiliate-product-showcase' ); ?>
							<span class="required">*</span>
						</label>
						<input type="number" id="aps-current-price" name="aps_current_price" class="aps-input"
							   step="0.01" min="0" placeholder="30.00" required>
					</div>
					
					<div class="aps-field-group">
						<label for="aps-original-price"><?php esc_html_e( 'Original Price', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-original-price" name="aps_original_price" class="aps-input"
							   step="0.01" min="0" placeholder="60.00">
					</div>
					
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Discount', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-discount" class="aps-input aps-readonly"
							   readonly value="<?php esc_html_e( '0% OFF', 'affiliate-product-showcase' ); ?>">
					</div>
				</div>
			</section>
			
			<!-- 7. Categories & Ribbons -->
			<section id="taxonomy" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'CATEGORIES & RIBBONS', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-2">
					<!-- Categories Multi-Select -->
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Category', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-multi-select" id="aps-categories-select">
							<div class="aps-selected-tags" id="aps-selected-categories">
								<!-- Selected categories as tags -->
							</div>
							<input type="text" class="aps-multiselect-input" 
								   placeholder="<?php esc_attr_e( 'Select categories...', 'affiliate-product-showcase' ); ?>">
							<div class="aps-dropdown" id="aps-categories-dropdown" style="display:none;">
								<?php
								$categories = get_terms( [
									'taxonomy'   => 'aps_category',
									'hide_empty' => false,
								] );
								foreach ( $categories as $category ) :
								?>
									<div class="dropdown-item" data-value="<?php echo esc_attr( $category->slug ); ?>">
										<?php echo esc_html( $category->name ); ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<input type="hidden" name="aps_categories" id="aps-categories-input">
					</div>
					
					<!-- Ribbons Multi-Select -->
					<div class="aps-field-group">
						<label><?php esc_html_e( 'Ribbon Badge', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-multi-select" id="aps-ribbons-select">
							<div class="aps-selected-tags" id="aps-selected-ribbons">
								<!-- Selected ribbons as tags -->
							</div>
							<input type="text" class="aps-multiselect-input"
								   placeholder="<?php esc_attr_e( 'Select ribbons...', 'affiliate-product-showcase' ); ?>">
							<div class="aps-dropdown" id="aps-ribbons-dropdown" style="display:none;">
								<div class="dropdown-item" data-value="hot"><?php esc_html_e( 'HOT', 'affiliate-product-showcase' ); ?></div>
								<div class="dropdown-item" data-value="new"><?php esc_html_e( 'NEW ARRIVAL', 'affiliate-product-showcase' ); ?></div>
								<div class="dropdown-item" data-value="sale"><?php esc_html_e( 'SALE', 'affiliate-product-showcase' ); ?></div>
								<div class="dropdown-item" data-value="limited"><?php esc_html_e( 'LIMITED', 'affiliate-product-showcase' ); ?></div>
								<div class="dropdown-item" data-value="bestseller"><?php esc_html_e( 'BEST SELLER', 'affiliate-product-showcase' ); ?></div>
							</div>
						</div>
						<input type="hidden" name="aps_ribbons" id="aps-ribbons-input">
					</div>
				</div>
			</section>
			
			<!-- 8. Product Tags -->
			<section class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRODUCT TAGS', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-tags-group">
					<label class="aps-checkbox-label">
						<input type="checkbox" name="aps_tags[]" value="new-arrival">
						<span><?php esc_html_e( 'New Arrival', 'affiliate-product-showcase' ); ?></span>
					</label>
					<label class="aps-checkbox-label">
						<input type="checkbox" name="aps_tags[]" value="best-seller">
						<span><?php esc_html_e( 'Best Seller', 'affiliate-product-showcase' ); ?></span>
					</label>
					<label class="aps-checkbox-label">
						<input type="checkbox" name="aps_tags[]" value="on-sale">
						<span><?php esc_html_e( 'On Sale', 'affiliate-product-showcase' ); ?></span>
					</label>
					<label class="aps-checkbox-label">
						<input type="checkbox" name="aps_tags[]" value="limited-edition">
						<span><?php esc_html_e( 'Limited Edition', 'affiliate-product-showcase' ); ?></span>
					</label>
				</div>
			</section>
			
			<!-- 9. Product Statistics -->
			<section id="stats" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRODUCT STATISTICS', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-rating"><?php esc_html_e( 'Rating', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-rating" name="aps_rating" class="aps-input"
							   step="0.1" min="0" max="5" placeholder="4.5">
					</div>
					
					<div class="aps-field-group">
						<label for="aps-views"><?php esc_html_e( 'Views', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-views" name="aps_views" class="aps-input"
							   min="0" placeholder="325">
					</div>
					
					<div class="aps-field-group">
						<label for="aps-user-count"><?php esc_html_e( 'User Count', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-user-count" name="aps_user_count" class="aps-input"
							   placeholder="1.5K">
					</div>
				</div>
				
				<div class="aps-field-group">
					<label for="aps-reviews"><?php esc_html_e( 'No. of Reviews', 'affiliate-product-showcase' ); ?></label>
					<input type="number" id="aps-reviews" name="aps_reviews" class="aps-input"
						   min="0" placeholder="12">
				</div>
			</section>
			
			<!-- Footer Actions -->
			<div class="aps-form-actions">
				<button type="submit" class="aps-btn aps-btn-primary" name="publish">
					<i class="fas fa-save"></i> <?php esc_html_e( 'Update Product', 'affiliate-product-showcase' ); ?>
				</button>
				<button type="submit" class="aps-btn aps-btn-secondary" name="draft">
					<i class="fas fa-file-alt"></i> <?php esc_html_e( 'Save Draft', 'affiliate-product-showcase' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-btn aps-btn-cancel">
					<?php esc_html_e( 'Cancel', 'affiliate-product-showcase' ); ?>
				</a>
			</div>
			
			<input type="hidden" name="action" value="aps_save_product">
		</form>
	</div>
</div>

<style>
/* Google Fonts */
.affiliate-product-showcase {
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Colors */
:root {
	--aps-primary: #2271b1;
	--aps-primary-hover: #135e96;
	--aps-bg: #f0f0f1;
	--aps-card: #ffffff;
	--aps-text: #1d2327;
	--aps-muted: #646970;
	--aps-border: #c3c4c7;
	--aps-danger: #d63638;
	--aps-success: #00a32a;
	--aps-tag-bg: #e6f0f5;
	--aps-tag-color: #2271b1;
}

/* Header */
.aps-header {
	background: #fff;
	padding: 20px 30px;
	border-bottom: 1px solid var(--aps-border);
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin: -20px -20px 30px -20px;
}

.aps-header h1 {
	font-size: 20px;
	font-weight: 600;
	color: var(--aps-text);
	margin: 0;
}

.aps-close-btn {
	background: none;
	border: none;
	font-size: 20px;
	color: var(--aps-muted);
	cursor: pointer;
	padding: 8px;
	transition: color .2s;
}

.aps-close-btn:hover {
	color: var(--aps-danger);
}

/* Quick Navigation */
.aps-quick-nav {
	display: flex;
	gap: 15px;
	padding: 15px 20px;
	background: var(--aps-bg);
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	margin-bottom: 30px;
	flex-wrap: wrap;
}

.aps-quick-nav .nav-link {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 10px 15px;
	background: #fff;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	color: var(--aps-text);
	text-decoration: none;
	font-size: 14px;
	font-weight: 500;
	transition: all .2s;
}

.aps-quick-nav .nav-link:hover {
	background: var(--aps-primary);
	border-color: var(--aps-primary);
	color: #fff;
}

.aps-quick-nav .nav-link i {
	font-size: 16px;
}

/* Form Container */
.aps-form-container {
	background: var(--aps-card);
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	padding: 30px;
	max-width: 1200px;
	margin-bottom: 30px;
}

/* Sections */
.aps-section {
	margin-bottom: 40px;
	padding-bottom: 40px;
	border-bottom: 1px solid var(--aps-border);
}

.aps-section:last-of-type {
	border-bottom: none;
	margin-bottom: 20px;
	padding-bottom: 20px;
}

.section-title {
	font-size: 14px;
	font-weight: 600;
	color: var(--aps-text);
	margin: 0 0 20px 0;
	padding-bottom: 10px;
	border-bottom: 2px solid var(--aps-primary);
}

/* Field Groups */
.aps-field-group {
	margin-bottom: 20px;
}

.aps-field-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: 500;
	font-size: 13px;
	color: var(--aps-text);
}

.aps-field-group .required {
	color: var(--aps-danger);
}

.aps-field-group .description {
	display: block;
	margin-top: 6px;
	font-size: 12px;
	font-style: italic;
	color: var(--aps-muted);
}

/* Inputs */
.aps-input {
	width: 100%;
	padding: 10px 12px;
	font-size: 14px;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	transition: all .2s;
}

.aps-input:focus {
	border-color: var(--aps-primary);
	box-shadow: 0 0 0 3px rgba(34, 113, 177, .1);
	outline: none;
}

.aps-textarea {
	min-height: 120px;
	resize: vertical;
}

.aps-select {
	width: 100%;
	padding: 10px 36px 10px 12px;
	font-size: 14px;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	background: #fff;
	appearance: none;
	cursor: pointer;
}

.aps-readonly {
	background: #f6f7f7;
	cursor: not-allowed;
}

/* Checkbox */
.aps-checkbox-label {
	display: flex;
	align-items: center;
	gap: 10px;
	cursor: pointer;
	margin-bottom: 10px;
}

.aps-checkbox-label input[type="checkbox"] {
	width: 20px;
	height: 20px;
	margin: 0;
	cursor: pointer;
}

.aps-tags-group {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
	gap: 10px;
}

/* Grid Layouts */
.aps-grid-2 {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 20px;
}

.aps-grid-3 {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 20px;
}

@media (max-width: 768px) {
	.aps-grid-2,
	.aps-grid-3 {
		grid-template-columns: 1fr;
	}
	
	.aps-quick-nav {
		flex-direction: column;
	}
	
	.aps-quick-nav .nav-link {
		width: 100%;
		justify-content: center;
	}
}

/* Upload Areas */
.aps-upload-area {
	border: 2px dashed var(--aps-border);
	border-radius: 4px;
	padding: 20px;
	text-align: center;
	background: #f9f9f9;
	cursor: pointer;
	transition: all .2s;
	position: relative;
	min-height: 150px;
}

.aps-upload-area:hover {
	border-color: var(--aps-primary);
	background: #f6f7f7;
}

.aps-upload-area .upload-placeholder {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 10px;
	color: var(--aps-muted);
}

.aps-upload-area .upload-placeholder i {
	font-size: 40px;
}

.aps-upload-area .upload-placeholder p {
	margin: 0;
	font-size: 14px;
}

.aps-upload-area .image-preview {
	display: none;
	width: 100%;
	height: 150px;
	background-size: contain;
	background-repeat: no-repeat;
	background-position: center;
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
}

.aps-url-input {
	margin-top: 15px;
}

/* Multi-Select */
.aps-multi-select {
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	padding: 8px;
	min-height: 42px;
	cursor: text;
}

.aps-selected-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 5px;
}

.aps-tag {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 4px 10px;
	background: var(--aps-tag-bg);
	color: var(--aps-tag-color);
	border-radius: 3px;
	font-size: 13px;
	font-weight: 500;
}

.aps-tag .remove-tag {
	cursor: pointer;
	font-size: 12px;
	transition: color .2s;
}

.aps-tag .remove-tag:hover {
	color: var(--aps-danger);
}

.aps-multiselect-input {
	border: none;
	outline: none;
	font-size: 14px;
	min-width: 150px;
	flex: 1;
}

.aps-dropdown {
	position: absolute;
	background: #fff;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	max-height: 200px;
	overflow-y: auto;
	z-index: 100;
	margin-top: 5px;
	box-shadow: 0 2px 8px rgba(0,0,0,.1);
}

.aps-dropdown .dropdown-item {
	padding: 10px 15px;
	cursor: pointer;
	transition: background .2s;
}

.aps-dropdown .dropdown-item:hover {
	background: #f6f7f7;
}

/* Features List */
.aps-features-list {
	margin-top: 15px;
}

.aps-feature-item {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 12px 15px;
	background: #f9f9f9;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	margin-bottom: 10px;
	transition: all .2s;
}

.aps-feature-item:hover {
	background: #f6f7f7;
}

.aps-feature-item.highlighted {
	background: #e6f0f5;
	border-color: var(--aps-primary);
	font-weight: 600;
}

.aps-feature-item .feature-text {
	flex: 1;
}

.aps-feature-item .feature-actions {
	display: flex;
	gap: 5px;
}

.aps-feature-item .feature-actions button {
	background: none;
	border: none;
	cursor: pointer;
	padding: 5px;
	color: var(--aps-muted);
	transition: color .2s;
}

.aps-feature-item .feature-actions button:hover {
	color: var(--aps-primary);
}

.aps-feature-item .feature-actions button.highlight-btn:hover {
	color: var(--aps-primary);
}

.aps-feature-item .feature-actions button.delete-btn:hover {
	color: var(--aps-danger);
}

/* Word Counter */
.word-counter {
	display: block;
	text-align: right;
	font-size: 12px;
	color: var(--aps-muted);
	margin-top: 5px;
}

/* Form Actions */
.aps-form-actions {
	display: flex;
	align-items: center;
	gap: 15px;
	padding-top: 20px;
	border-top: 1px solid var(--aps-border);
}

.aps-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 10px 24px;
	font-size: 14px;
	font-weight: 600;
	border-radius: 4px;
	cursor: pointer;
	transition: all .2s;
	text-decoration: none;
	border: none;
}

.aps-btn-primary {
	background: var(--aps-primary);
	color: #fff;
}

.aps-btn-primary:hover {
	background: var(--aps-primary-hover);
}

.aps-btn-secondary {
	background: #646970;
	color: #fff;
}

.aps-btn-secondary:hover {
	background: #50565e;
}

.aps-btn-cancel {
	background: transparent;
	color: var(--aps-muted);
}

.aps-btn-cancel:hover {
	color: var(--aps-text);
}

/* Toast Notification */
.aps-toast {
	position: fixed;
	bottom: 20px;
	right: 20px;
	padding: 15px 20px;
	background: #fff;
	border-left: 4px solid var(--aps-success);
	border-radius: 4px;
	box-shadow: 0 2px 8px rgba(0,0,0,.2);
	z-index: 10000;
	animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
	from {
		transform: translateX(100%);
		opacity: 0;
	}
	to {
		transform: translateX(0);
		opacity: 1;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Quick Navigation - Smooth scroll
	$('.aps-quick-nav .nav-link').on('click', function(e) {
		e.preventDefault();
		const target = $(this).attr('href');
		$('html, body').animate({
			scrollTop: $(target).offset().top - 50
		}, 300);
	});
	
	// Word Counter
	$('#aps-short-description').on('input', function() {
		const text = $(this).val().trim();
		const words = text === '' ? 0 : text.split(/\s+/).length;
		$('#aps-word-count').text(Math.min(words, 40));
	});
	
	// Discount Calculator
	$('#aps-current-price, #aps-original-price').on('input', function() {
		const current = parseFloat($('#aps-current-price').val()) || 0;
		const original = parseFloat($('#aps-original-price').val()) || 0;
		
		if (original > 0 && current < original) {
			const discount = ((original - current) / original * 100).toFixed(0);
			$('#aps-discount').val(discount + '% OFF');
		} else {
			$('#aps-discount').val('0% OFF');
		}
	});
	
	// Feature List
	let features = [];
	
	$('#aps-add-feature').on('click', function() {
		const input = $('#aps-new-feature');
		const featureText = input.val().trim();
		
		if (featureText) {
			features.push({ text: featureText, highlighted: false });
			input.val('');
			renderFeatures();
		}
	});
	
	$('#aps-new-feature').on('keypress', function(e) {
		if (e.which === 13) {
			e.preventDefault();
			$('#aps-add-feature').click();
		}
	});
	
	function renderFeatures() {
		const container = $('#aps-features-list');
		container.empty();
		
		features.forEach((feature, index) => {
			const html = `
				<div class="aps-feature-item ${feature.highlighted ? 'highlighted' : ''}" data-index="${index}">
					<span class="feature-text">${escapeHtml(feature.text)}</span>
					<div class="feature-actions">
						<button type="button" class="highlight-btn" title="Highlight">
							<i class="fas fa-bold"></i>
						</button>
						<button type="button" class="move-up" title="Move Up">
							<i class="fas fa-arrow-up"></i>
						</button>
						<button type="button" class="move-down" title="Move Down">
							<i class="fas fa-arrow-down"></i>
						</button>
						<button type="button" class="delete-btn" title="Delete">
							<i class="fas fa-trash"></i>
						</button>
					</div>
				</div>
			`;
			container.append(html);
		});
		
		$('#aps-features-input').val(JSON.stringify(features));
	}
	
	$(document).on('click', '.aps-feature-item .highlight-btn', function() {
		const index = $(this).closest('.aps-feature-item').data('index');
		features[index].highlighted = !features[index].highlighted;
		renderFeatures();
	});
	
	$(document).on('click', '.aps-feature-item .move-up', function() {
		const index = $(this).closest('.aps-feature-item').data('index');
		if (index > 0) {
			[features[index], features[index - 1]] = [features[index - 1], features[index]];
			renderFeatures();
		}
	});
	
	$(document).on('click', '.aps-feature-item .move-down', function() {
		const index = $(this).closest('.aps-feature-item').data('index');
		if (index < features.length - 1) {
			[features[index], features[index + 1]] = [features[index + 1], features[index]];
			renderFeatures();
		}
	});
	
	$(document).on('click', '.aps-feature-item .delete-btn', function() {
		const index = $(this).closest('.aps-feature-item').data('index');
		features.splice(index, 1);
		renderFeatures();
	});
	
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
	
	// Multi-Select for Categories
	const selectedCategories = [];
	
	$('.aps-multi-select .aps-multiselect-input').on('focus click', function() {
		const dropdown = $(this).siblings('.aps-dropdown');
		dropdown.slideDown(200);
	});
	
	$(document).on('click', function(e) {
		if (!$(e.target).closest('.aps-multi-select').length) {
			$('.aps-dropdown').slideUp(200);
		}
	});
	
	$('#aps-categories-dropdown .dropdown-item').on('click', function() {
		const value = $(this).data('value');
		const text = $(this).text();
		
		if (!selectedCategories.includes(value)) {
			selectedCategories.push(value);
			renderCategories();
			$('#aps-categories-input').val(selectedCategories.join(','));
		}
	});
	
	function renderCategories() {
		const container = $('#aps-selected-categories');
		container.empty();
		
		selectedCategories.forEach((cat, index) => {
			const text = $('#aps-categories-dropdown .dropdown-item[data-value="' + cat + '"]').text();
			container.append(`
				<span class="aps-tag">
					${escapeHtml(text)}
					<span class="remove-tag" data-index="${index}">&times;</span>
				</span>
			`);
		});
	}
	
	$(document).on('click', '#aps-selected-categories .remove-tag', function() {
		const index = $(this).data('index');
		selectedCategories.splice(index, 1);
		renderCategories();
		$('#aps-categories-input').val(selectedCategories.join(','));
	});
	
	// Multi-Select for Ribbons
	const selectedRibbons = [];
	
	$('#aps-ribbons-dropdown .dropdown-item').on('click', function() {
		const value = $(this).data('value');
		const text = $(this).text();
		
		if (!selectedRibbons.includes(value)) {
			selectedRibbons.push(value);
			renderRibbons();
			$('#aps-ribbons-input').val(selectedRibbons.join(','));
		}
	});
	
	function renderRibbons() {
		const container = $('#aps-selected-ribbons');
		container.empty();
		
		selectedRibbons.forEach((ribbon, index) => {
			const text = $('#aps-ribbons-dropdown .dropdown-item[data-value="' + ribbon + '"]').text();
			container.append(`
				<span class="aps-tag">
					${escapeHtml(text)}
					<span class="remove-tag" data-index="${index}">&times;</span>
				</span>
			`);
		});
	}
	
	$(document).on('click', '#aps-selected-ribbons .remove-tag', function() {
		const index = $(this).data('index');
		selectedRibbons.splice(index, 1);
		renderRibbons();
		$('#aps-ribbons-input').val(selectedRibbons.join(','));
	});
	
	// Image Upload Preview
	$('#aps-logo-upload, #aps-brand-upload').on('click', function() {
		$(this).find('input[type="file"]').click();
	});
	
	$('#aps-logo-input').on('change', function(e) {
		const file = e.target.files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = function(e) {
				$('#aps-logo-preview')
					.css('background-image', 'url(' + e.target.result + ')')
					.show();
				$('#aps-logo-upload .upload-placeholder').hide();
			};
			reader.readAsDataURL(file);
		}
	});
	
	$('#aps-brand-input').on('change', function(e) {
		const file = e.target.files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = function(e) {
				$('#aps-brand-preview')
					.css('background-image', 'url(' + e.target.result + ')')
					.show();
				$('#aps-brand-upload .upload-placeholder').hide();
			};
			reader.readAsDataURL(file);
		}
	});
	
	// URL input for images
	$('#aps-logo-url-input').on('blur', function() {
		const url = $(this).val();
		if (url) {
			$('#aps-logo-preview')
				.css('background-image', 'url(' + url + ')')
				.show();
			$('#aps-logo-upload .upload-placeholder').hide();
		}
	});
	
	$('#aps-brand-url-input').on('blur', function() {
		const url = $(this).val();
		if (url) {
			$('#aps-brand-preview')
				.css('background-image', 'url(' + url + ')')
				.show();
			$('#aps-brand-upload .upload-placeholder').hide();
		}
	});
	
	// Drag & Drop
	$('.aps-upload-area').on('dragover', function(e) {
		e.preventDefault();
		$(this).css('border-color', '#2271b1');
	}).on('dragleave', function(e) {
		e.preventDefault();
		$(this).css('border-color', '#c3c4c7');
	}).on('drop', function(e) {
		e.preventDefault();
		$(this).css('border-color', '#c3c4c7');
		
		const file = e.originalEvent.dataTransfer.files[0];
		const input = $(this).find('input[type="file"]');
		
		if (file) {
			const dataTransfer = new DataTransfer();
			dataTransfer.items.add(file);
			input[0].files = dataTransfer.files;
			input.trigger('change');
		}
	});
});
</script>
