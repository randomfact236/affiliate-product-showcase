<?php
/**
 * Add Product Page - Single-Page Form with Quick Navigation
 *
 * Supports both adding new products and editing existing products.
 * Detects edit mode via $_GET['post'] parameter.
 *
 * @package AffiliateProductShowcase\Admin\Partials
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine if we're editing or adding
$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
$is_editing = $post_id > 0;

// Get product data if editing
$product_data = [];
if ( $is_editing ) {
	$post = get_post( $post_id );
	if ( $post && $post->post_type === 'aps_product' ) {
		$product_data = [
			'id' => $post->ID,
			'title' => $post->post_title,
			'status' => $post->post_status,
			'content' => $post->post_content,
			// Meta fields
			'logo' => get_post_meta( $post->ID, 'aps_product_logo', true ),
			'brand_image' => get_post_meta( $post->ID, 'aps_brand_image', true ),
			'affiliate_url' => get_post_meta( $post->ID, 'aps_affiliate_url', true ),
			'button_name' => get_post_meta( $post->ID, 'aps_button_name', true ),
			'short_description' => get_post_meta( $post->ID, 'aps_short_description', true ),
			'regular_price' => get_post_meta( $post->ID, 'aps_regular_price', true ),
			'sale_price' => get_post_meta( $post->ID, 'aps_sale_price', true ),
			'currency' => get_post_meta( $post->ID, 'aps_currency', true ) ?: 'USD',
			'featured' => get_post_meta( $post->ID, 'aps_featured', true ) === '1',
			'rating' => get_post_meta( $post->ID, 'aps_rating', true ),
			'views' => get_post_meta( $post->ID, 'aps_views', true ),
			'user_count' => get_post_meta( $post->ID, 'aps_user_count', true ),
			'reviews' => get_post_meta( $post->ID, 'aps_reviews', true ),
			// Features stored as JSON
			'features' => json_decode( get_post_meta( $post->ID, 'aps_features', true ) ?: '[]', true ),
		];
	}
}

// Ensure WordPress media scripts are loaded
wp_enqueue_media();
// Ensure jQuery is loaded
wp_enqueue_script( 'jquery' );

// Load Font Awesome
wp_enqueue_style( 'aps-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
// Load Google Fonts - Inter
wp_enqueue_style( 'aps-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap', [], null );
?>

<div class="wrap affiliate-product-showcase">
	<!-- Header -->
	<div class="aps-header">
		<h1><?php echo esc_html( $is_editing ? __( 'Edit Product', 'affiliate-product-showcase' ) : __( 'Add Product', 'affiliate-product-showcase' ) ); ?></h1>
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
						<input type="checkbox" id="aps-featured" name="aps_featured" value="1" <?php checked( $product_data['featured'] ?? false ); ?>>
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
						<label><?php esc_html_e( 'Product Image (Featured)', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-image-upload">
							<div class="upload-placeholder">
								<i class="fas fa-camera"></i>
								<p><?php esc_html_e( 'Click to upload or enter URL below', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview" id="aps-image-preview" style="<?php echo !empty( $product_data['logo'] ) ? 'background-image: url(' . esc_url( $product_data['logo'] ) . '); display: block;' : ''; ?>"></div>
							<input type="hidden" name="aps_image_url" id="aps-image-url" value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-image-btn">
								<i class="fas fa-upload"></i> <?php esc_html_e( 'Select from Media Library', 'affiliate-product-showcase' ); ?>
							</button>
						</div>
						<div class="aps-url-input">
							<input type="url" name="aps_image_url_input" class="aps-input"
								   placeholder="https://..." id="aps-image-url-input"
								   value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
						</div>
					</div>
					
					<!-- Brand Image Upload -->
					<div class="aps-upload-group">
						<label><?php esc_html_e( 'Brand Image', 'affiliate-product-showcase' ); ?></label>
						<div class="aps-upload-area" id="aps-brand-upload">
							<div class="upload-placeholder">
								<i class="fas fa-tshirt"></i>
								<p><?php esc_html_e( 'Click to upload or enter URL below', 'affiliate-product-showcase' ); ?></p>
							</div>
							<div class="image-preview" id="aps-brand-preview" style="<?php echo !empty( $product_data['brand_image'] ) ? 'background-image: url(' . esc_url( $product_data['brand_image'] ) . '); display: block;' : ''; ?>"></div>
							<input type="hidden" name="aps_brand_image_url" id="aps-brand-image-url" value="<?php echo esc_attr( $product_data['brand_image'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-brand-btn">
								<i class="fas fa-upload"></i> <?php esc_html_e( 'Select from Media Library', 'affiliate-product-showcase' ); ?>
							</button>
						</div>
						<div class="aps-url-input">
							<input type="url" name="aps_brand_url_input" class="aps-input"
								   placeholder="https://..." id="aps-brand-url-input"
								   value="<?php echo esc_attr( $product_data['brand_image'] ?? '' ); ?>">
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
							   placeholder="https://example.com/..."
							   value="<?php echo esc_url( $product_data['affiliate_url'] ?? '' ); ?>">
					</div>
					
					<div class="aps-field-group">
						<label for="aps-button-name"><?php esc_html_e( 'Button Name', 'affiliate-product-showcase' ); ?></label>
						<input type="text" id="aps-button-name" name="aps_button_name" class="aps-input"
							   placeholder="Buy Now"
							   value="<?php echo esc_attr( $product_data['button_name'] ?? 'Buy Now' ); ?>">
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
					<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea aps-full-page"
							  rows="6" maxlength="200"
							  placeholder="<?php esc_attr_e( 'Enter short description (max 40 words)...', 'affiliate-product-showcase' ); ?>" required
							  data-initial="<?php echo esc_attr( $product_data['short_description'] ?? '' ); ?>"><?php echo esc_textarea( $product_data['short_description'] ?? '' ); ?></textarea>
					<div class="word-counter">
						<span id="aps-word-count">0</span>/40 <?php esc_html_e( 'Words', 'affiliate-product-showcase' ); ?>
					</div>
				</div>
			</section>
			
			<!-- 5. Feature List -->
			<section id="features" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'FEATURE LIST', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-feature-list-input-group">
					<input type="text" id="aps-new-feature" class="aps-input"
						   placeholder="<?php esc_attr_e( 'Add new feature...', 'affiliate-product-showcase' ); ?>">
					<button type="button" class="aps-btn aps-btn-primary" id="aps-add-feature">
						<i class="fas fa-plus"></i> <?php esc_html_e( 'Add', 'affiliate-product-showcase' ); ?>
					</button>
				</div>
				
				<div class="aps-features-list" id="aps-features-list">
					<!-- Sample feature (for demo purposes) -->
					<div class="aps-feature-item" id="feat-1">
						<span class="feature-text">erfgergh rgntht</span>
						<div class="feature-actions">
							<button type="button" class="feature-btn active-highlight" onclick="toggleHighlight(this)" title="Highlight">
								<i class="fas fa-bold"></i>
							</button>
							<button type="button" class="feature-btn" onclick="moveFeature(this, -1)" title="Move Up">
								<i class="fas fa-arrow-up"></i>
							</button>
							<button type="button" class="feature-btn" onclick="moveFeature(this, 1)" title="Move Down">
								<i class="fas fa-arrow-down"></i>
							</button>
							<button type="button" class="feature-btn delete" onclick="deleteFeature(this)" title="Delete">
								<i class="fas fa-trash"></i>
							</button>
						</div>
					</div>
					<div class="aps-feature-item" id="feat-2">
						<span class="feature-text">htrgre rtgrtgrt</span>
						<div class="feature-actions">
							<button type="button" class="feature-btn" onclick="toggleHighlight(this)" title="Highlight">
								<i class="fas fa-bold"></i>
							</button>
							<button type="button" class="feature-btn" onclick="moveFeature(this, -1)" title="Move Up">
								<i class="fas fa-arrow-up"></i>
							</button>
							<button type="button" class="feature-btn" onclick="moveFeature(this, 1)" title="Move Down">
								<i class="fas fa-arrow-down"></i>
							</button>
							<button type="button" class="feature-btn delete" onclick="deleteFeature(this)" title="Delete">
								<i class="fas fa-trash"></i>
							</button>
						</div>
					</div>
				</div>
				<input type="hidden" name="aps_features" id="aps-features-input">
			</section>
			
			<!-- 6. Pricing -->
			<section id="pricing" class="aps-section">
				<h2 class="section-title"><?php esc_html_e( 'PRICING', 'affiliate-product-showcase' ); ?></h2>
				
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-regular-price">
							<?php esc_html_e( 'Regular Price', 'affiliate-product-showcase' ); ?>
							<span class="required">*</span>
						</label>
						<input type="number" id="aps-regular-price" name="aps_regular_price" class="aps-input"
							   step="0.01" min="0" placeholder="30.00" required
							   value="<?php echo esc_attr( $product_data['regular_price'] ?? '' ); ?>">
					</div>
					
					<div class="aps-field-group">
						<label for="aps-sale-price"><?php esc_html_e( 'Sale Price', 'affiliate-product-showcase' ); ?></label>
						<input type="number" id="aps-sale-price" name="aps_sale_price" class="aps-input"
							   step="0.01" min="0" placeholder="60.00"
							   value="<?php echo esc_attr( $product_data['sale_price'] ?? '' ); ?>">
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
								<span class="multi-select-placeholder">Select categories...</span>
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
								<span class="multi-select-placeholder">Select ribbons...</span>
							</div>
							<input type="text" class="aps-multiselect-input"
								   placeholder="<?php esc_attr_e( 'Select ribbons...', 'affiliate-product-showcase' ); ?>">
							<div class="aps-dropdown" id="aps-ribbons-dropdown" style="display:none;">
								<?php
								$ribbons = get_terms( [
									'taxonomy'   => 'aps_ribbon',
									'hide_empty' => false,
								] );
								foreach ( $ribbons as $ribbon ) :
								?>
									<div class="dropdown-item" data-value="<?php echo esc_attr( $ribbon->slug ); ?>">
										<?php echo esc_html( $ribbon->name ); ?>
									</div>
								<?php endforeach; ?>
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
					<?php
					$tags = get_terms( [
						'taxonomy'   => 'aps_tag',
						'hide_empty' => false,
					] );
					foreach ( $tags as $tag ) :
					?>
						<label class="aps-checkbox-label">
							<input type="checkbox" name="aps_tags[]" value="<?php echo esc_attr( $tag->slug ); ?>">
							<span><?php echo esc_html( $tag->name ); ?></span>
						</label>
					<?php endforeach; ?>
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
	min-height: 200px;
	resize: vertical;
	width: 100%;
}

/* Full Page Textarea - Responsive */
.aps-textarea.aps-full-page {
	width: 100%;
	min-height: 200px;
	resize: vertical;
	font-family: inherit;
	font-size: 14px;
	line-height: 1.6;
	padding: 12px;
}

@media (max-width: 768px) {
	.aps-textarea.aps-full-page {
		min-height: 150px;
		font-size: 13px;
		padding: 10px;
	}
}

@media (max-width: 480px) {
	.aps-textarea.aps-full-page {
		min-height: 120px;
		font-size: 12px;
		padding: 8px;
	}
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
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
	margin-top: 10px;
}

.aps-tags-group .aps-checkbox-label {
	flex: 0 0 auto;
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

/* Upload Button */
.aps-upload-btn {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	padding: 8px 16px;
	background: var(--aps-primary);
	color: #fff;
	border: none;
	border-radius: 4px;
	font-size: 13px;
	font-weight: 500;
	cursor: pointer;
	transition: all .2s;
	margin-top: 10px;
}

.aps-upload-btn:hover {
	background: var(--aps-primary-hover);
}

.aps-upload-btn i {
	font-size: 14px;
}

/* Multi-Select */
.aps-multi-select {
	position: relative;
}

.aps-selected-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 5px;
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	padding: 4px 8px;
	min-height: 38px;
	background: #fff;
	cursor: pointer;
}

.aps-selected-tags:focus-within {
	border-color: var(--aps-primary);
	box-shadow: 0 0 0 1px var(--aps-primary);
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
.aps-feature-list-input-group {
	display: flex;
	gap: 10px;
	margin-bottom: 15px;
}

.aps-feature-list-input-group .aps-input {
	flex-grow: 1;
}

.aps-features-list {
	border: 1px solid var(--aps-border);
	border-radius: 4px;
	background: #fff;
	max-height: 300px;
	overflow-y: auto;
	margin-top: 15px;
}

.aps-feature-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 10px 15px;
	border-bottom: 1px solid #eee;
	background: #fff;
	transition: background .2s;
}

.aps-feature-item:last-child {
	border-bottom: none;
}

.aps-feature-item:hover {
	background: #f8f9f9;
}

.aps-feature-item.highlighted {
	background: #e6f0f5;
}

.aps-feature-item.highlighted .feature-text {
	font-weight: bold;
	color: var(--aps-primary);
	background: #e6f0f5;
	padding: 2px 6px;
	border-radius: 3px;
}

.aps-feature-item .feature-text {
	flex-grow: 1;
	margin-right: 15px;
	font-size: 14px;
}

.aps-feature-item .feature-actions {
	display: flex;
	gap: 5px;
}

.aps-feature-item .feature-actions button {
	background: none;
	border: 1px solid #ddd;
	border-radius: 3px;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	color: var(--aps-muted);
	transition: all .2s;
}

.aps-feature-item .feature-actions button:hover {
	background: #e0e0e0;
	color: var(--aps-text);
}

.aps-feature-item .feature-actions button.highlight-btn:hover {
	background: var(--aps-primary);
	color: #fff;
	border-color: var(--aps-primary);
}

.aps-feature-item .feature-actions button.delete-btn:hover {
	background: #fee;
	color: var(--aps-danger);
	border-color: #fcc;
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
	// Debug: Check if required libraries are loaded
	console.log('jQuery loaded:', typeof jQuery !== 'undefined');
	console.log('wp object loaded:', typeof wp !== 'undefined');
	console.log('wp.media loaded:', typeof wp !== 'undefined' && typeof wp.media !== 'undefined');
	
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
	$('#aps-regular-price, #aps-sale-price').on('input', function() {
		const regular = parseFloat($('#aps-regular-price').val()) || 0;
		const sale = parseFloat($('#aps-sale-price').val()) || 0;
		
		if (regular > 0 && sale > 0 && sale < regular) {
			const discount = ((regular - sale) / regular * 100).toFixed(0);
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
		
		// Toggle placeholder visibility
		const placeholder = container.find('.multi-select-placeholder');
		if (placeholder.length) {
			placeholder.toggle(selectedCategories.length === 0);
		}
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
		
		// Toggle placeholder visibility
		const placeholder = container.find('.multi-select-placeholder');
		if (placeholder.length) {
			placeholder.toggle(selectedRibbons.length === 0);
		}
	}
	
	$(document).on('click', '#aps-selected-ribbons .remove-tag', function() {
		const index = $(this).data('index');
		selectedRibbons.splice(index, 1);
		renderRibbons();
		$('#aps-ribbons-input').val(selectedRibbons.join(','));
	});
	
	// WordPress Media Library Upload
	$('#aps-upload-image-btn').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		console.log('Product Image button clicked');
		
		// Check if wp.media is available
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			console.error('WordPress media library is not loaded');
			alert('WordPress media library is not loaded. Please refresh the page.');
			return;
		}
		
		const mediaUploader = wp.media({
			title: 'Select Image',
			button: { text: 'Use This Image' },
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			const attachment = mediaUploader.state().get('selection').first().toJSON();
			console.log('Image selected:', attachment);
			$('#aps-image-url').val(attachment.url);
			$('#aps-image-url-input').val(attachment.url);
			$('#aps-image-preview')
				.css('background-image', 'url(' + attachment.url + ')')
				.show();
			$('#aps-image-upload .upload-placeholder').hide();
		});
		
		mediaUploader.open();
	});
	
	$('#aps-upload-brand-btn').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		console.log('Brand Image button clicked');
		
		// Check if wp.media is available
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			console.error('WordPress media library is not loaded');
			alert('WordPress media library is not loaded. Please refresh the page.');
			return;
		}
		
		const mediaUploader = wp.media({
			title: 'Select Brand Image',
			button: { text: 'Use This Image' },
			multiple: false
		});
		
		mediaUploader.on('select', function() {
			const attachment = mediaUploader.state().get('selection').first().toJSON();
			console.log('Brand image selected:', attachment);
			$('#aps-brand-image-url').val(attachment.url);
			$('#aps-brand-url-input').val(attachment.url);
			$('#aps-brand-preview')
				.css('background-image', 'url(' + attachment.url + ')')
				.show();
			$('#aps-brand-upload .upload-placeholder').hide();
		});
		
		mediaUploader.open();
	});
	
	// URL input for images
	$('#aps-image-url-input').on('blur', function() {
		const url = $(this).val();
		if (url) {
			$('#aps-image-url').val(url);
			$('#aps-image-preview')
				.css('background-image', 'url(' + url + ')')
				.show();
			$('#aps-image-upload .upload-placeholder').hide();
		}
	});
	
	$('#aps-brand-url-input').on('blur', function() {
		const url = $(this).val();
		if (url) {
			$('#aps-brand-image-url').val(url);
			$('#aps-brand-preview')
				.css('background-image', 'url(' + url + ')')
				.show();
			$('#aps-brand-upload .upload-placeholder').hide();
		}
	});
});
</script>
