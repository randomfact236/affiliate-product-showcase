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
			'short_description' => $post->post_excerpt,
			// Meta fields
			'logo' => get_post_meta( $post->ID, '_aps_logo', true ),
			'brand_image' => get_post_meta( $post->ID, '_aps_brand_image', true ),
			'affiliate_url' => get_post_meta( $post->ID, '_aps_affiliate_url', true ),
			'button_name' => get_post_meta( $post->ID, '_aps_button_name', true ),
			'regular_price' => get_post_meta( $post->ID, '_aps_price', true ),
			'original_price' => get_post_meta( $post->ID, '_aps_original_price', true ),
			'currency' => get_post_meta( $post->ID, '_aps_currency', true ) ?: 'USD',
			'featured' => get_post_meta( $post->ID, '_aps_featured', true ) === '1',
			'rating' => get_post_meta( $post->ID, '_aps_rating', true ),
			'views' => get_post_meta( $post->ID, '_aps_views', true ),
			'user_count' => get_post_meta( $post->ID, '_aps_user_count', true ),
			'reviews' => get_post_meta( $post->ID, '_aps_reviews', true ),
			'features' => json_decode( get_post_meta( $post->ID, '_aps_features', true ) ?: '[]', true ),
			'video_url' => get_post_meta( $post->ID, '_aps_video_url', true ),
			'platform_requirements' => get_post_meta( $post->ID, '_aps_platform_requirements', true ),
			'version_number' => get_post_meta( $post->ID, '_aps_version_number', true ),
			'stock_status' => get_post_meta( $post->ID, '_aps_stock_status', true ) ?: 'instock',
			'seo_title' => get_post_meta( $post->ID, '_aps_seo_title', true ),
			'seo_description' => get_post_meta( $post->ID, '_aps_seo_description', true ),
		];
		
		// Get product categories, tags, and ribbons
		$product_data['categories'] = wp_get_object_terms( $post->ID, 'aps_category', [ 'fields' => 'slugs' ] );
		$product_data['tags'] = wp_get_object_terms( $post->ID, 'aps_tag', [ 'fields' => 'slugs' ] );
		$product_data['ribbons'] = wp_get_object_terms( $post->ID, 'aps_ribbon', [ 'fields' => 'slugs' ] );
	} else {
	// Post doesn't exist or wrong post type - show error
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

// Enqueue scripts
wp_enqueue_media();
wp_enqueue_script( 'jquery' ); 

// Enqueue styles
wp_enqueue_style( 'aps-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
wp_enqueue_style( 'aps-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap', [], null );
?>
 
<div class="wrap affiliate-product-showcase">
	<div class="aps-header">
		<h1><?php echo esc_html( $is_editing ? __( 'Edit Product', 'affiliate-product-showcase' ) : __( 'Add Product', 'affiliate-product-showcase' ) ); ?></h1>
		<button type="button" class="aps-close-btn" onclick="window.location.href='<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>'">
			<i class="fas fa-times"></i>
		</button>
	</div>
	
	<div class="aps-quick-nav">
		<a href="#product-info" class="nav-link"><i class="fas fa-edit"></i> Product Info</a>
		<a href="#images" class="nav-link"><i class="fas fa-images"></i> Images</a>
		<a href="#affiliate" class="nav-link"><i class="fas fa-link"></i> Affiliate</a>
		<a href="#features" class="nav-link"><i class="fas fa-list"></i> Features</a>
		<a href="#pricing" class="nav-link"><i class="fas fa-tag"></i> Pricing</a>
		<a href="#taxonomy" class="nav-link"><i class="fas fa-folder"></i> Categories & Tags</a>
		<a href="#stats" class="nav-link"><i class="fas fa-chart-bar"></i> Statistics</a>
	</div>
	
	<div class="aps-form-container">
		<form method="post" id="aps-product-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php 
			$form_action = $is_editing ? 'aps_update_product' : 'aps_save_product';
			$nonce_action = $is_editing ? 'aps_update_product' : 'aps_save_product';
			?>
			
			<?php wp_nonce_field( $nonce_action, 'aps_product_nonce' ); ?>
			
			<?php if ( $is_editing ) : ?>
				<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<?php endif; ?>
			
			<section id="product-info" class="aps-section">
				<h2 class="section-title">PRODUCT INFO</h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-product-title">Product Title <span class="required">*</span></label>
						<input type="text" id="aps-product-title" name="aps_title" class="aps-input"
							   placeholder="Enter title..." required
							   value="<?php echo esc_attr( $product_data['title'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-product-status">Status</label>
						<select id="aps-product-status" name="aps_status" class="aps-select">
							<option value="draft" <?php selected( $product_data['status'] ?? '', 'draft' ); ?>>Draft</option>
							<option value="publish" <?php selected( $product_data['status'] ?? '', 'publish' ); ?>>Published</option>
						</select>
					</div>
				</div>
				<div class="aps-field-group">
					<label class="aps-checkbox-label">
						<input type="checkbox" id="aps-featured" name="aps_featured" value="1" <?php checked( $product_data['featured'] ?? false ); ?>>
						<span>Featured Product</span>
					</label>
				</div>
			</section>
			
			<section id="images" class="aps-section">
				<h2 class="section-title">PRODUCT IMAGES</h2>
				<div class="aps-grid-2">
					<div class="aps-upload-group">
						<label>Product Image (Featured)</label>
						<div class="aps-upload-area" id="aps-image-upload">
							<div class="upload-placeholder">
								<i class="fas fa-camera"></i>
								<p>Click to upload or enter URL below</p>
							</div>
							<div class="image-preview" id="aps-image-preview" style="<?php echo !empty( $product_data['logo'] ) ? 'background-image: url(' . esc_url( $product_data['logo'] ) . '); display: block;' : ''; ?>"></div>
							<input type="hidden" name="aps_image_url" id="aps-image-url" value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-image-btn">
								<i class="fas fa-upload"></i> Select from Media Library
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel" id="aps-remove-image-btn" style="display:none;">
								<i class="fas fa-times"></i> Remove
							</button>
						</div>
						<div class="aps-url-input">
							<input type="url" name="aps_image_url_input" class="aps-input"
								   placeholder="https://..." id="aps-image-url-input"
								   value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
						</div>
					</div>
					<div class="aps-upload-group">
						<label>Logo</label>
						<div class="aps-upload-area" id="aps-brand-upload">
							<div class="upload-placeholder">
								<i class="fas fa-tshirt"></i>
								<p>Click to upload or enter URL below</p>
							</div>
							<div class="image-preview" id="aps-brand-preview" style="<?php echo !empty( $product_data['brand_image'] ) ? 'background-image: url(' . esc_url( $product_data['brand_image'] ) . '); display: block;' : ''; ?>"></div>
							<input type="hidden" name="aps_brand_image_url" id="aps-brand-image-url" value="<?php echo esc_attr( $product_data['brand_image'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-brand-btn">
								<i class="fas fa-upload"></i> Select from Media Library
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel" id="aps-remove-brand-btn" style="display:none;">
								<i class="fas fa-times"></i> Remove
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
			
			<section id="affiliate" class="aps-section">
				<h2 class="section-title">AFFILIATE DETAILS</h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-affiliate-url">Affiliate URL</label>
						<input type="url" id="aps-affiliate-url" name="aps_affiliate_url" class="aps-input"
							   placeholder="https://example.com/..."
							   value="<?php echo esc_url( $product_data['affiliate_url'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-button-name">Button Name</label>
						<input type="text" id="aps-button-name" name="aps_button_name" class="aps-input"
							   placeholder=""
							   value="<?php echo esc_attr( $product_data['button_name'] ?? '' ); ?>">
					</div>
				</div>
			</section>
			
			<section class="aps-section">
				<h2 class="section-title">SHORT DESCRIPTION</h2>
				<div class="aps-field-group">
					<label for="aps-short-description">Short Description <span class="required">*</span></label>
					<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea aps-full-page"
							  rows="6" maxlength="200"
							  placeholder="Enter short description (max 40 words)..." required
							  data-initial="<?php echo esc_attr( $product_data['short_description'] ?? '' ); ?>"><?php echo esc_textarea( $product_data['short_description'] ?? '' ); ?></textarea>
					<div class="word-counter">
						<span id="aps-word-count">0</span>/40 Words
					</div>
				</div>
			</section>
			
			<section id="features" class="aps-section">
				<h2 class="section-title">FEATURE LIST</h2>
				<div class="aps-feature-list-input-group">
					<input type="text" id="aps-new-feature" class="aps-input"
						   placeholder="Add new feature...">
					<button type="button" class="aps-btn aps-btn-primary" id="aps-add-feature">
						<i class="fas fa-plus"></i> Add
					</button>
				</div>
				<div class="aps-features-list" id="aps-features-list"></div>
				<input type="hidden" name="aps_features" id="aps-features-input">
			</section>
			
			<section id="pricing" class="aps-section">
				<h2 class="section-title">PRICING</h2>
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-current-price">Current Price <span class="required">*</span></label>
						<input type="number" id="aps-current-price" name="aps_current_price" class="aps-input"
							   step="0.01" min="0" placeholder="30.00" required
							   value="<?php echo esc_attr( $product_data['regular_price'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-original-price">Original Price</label>
						<input type="number" id="aps-original-price" name="aps_original_price" class="aps-input"
							   step="0.01" min="0" placeholder="60.00"
							   value="<?php echo esc_attr( $product_data['original_price'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label>Discount</label>
						<input type="text" id="aps-discount" class="aps-input aps-readonly"
							   readonly value="0% OFF">
					</div>
				</div>
			</section>
			
			<section id="taxonomy" class="aps-section">
				<h2 class="section-title">CATEGORIES & RIBBONS</h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label>Category</label>
						<div class="aps-multi-select" id="aps-categories-select">
							<div class="aps-selected-tags" id="aps-selected-categories">
								<span class="multi-select-placeholder">Select categories...</span>
							</div>
							<input type="text" class="aps-multiselect-input" placeholder="Select categories...">
							<div class="aps-dropdown" id="aps-categories-dropdown" style="display:none;">
								<?php
								$categories = get_terms( [ 'taxonomy' => 'aps_category', 'hide_empty' => false ] );
								foreach ( $categories as $category ) :
									$category_image = get_term_meta( $category->term_id, '_aps_category_image', true );
									$category_featured = get_term_meta( $category->term_id, '_aps_category_featured', true ) === '1';
								?>
									<div class="dropdown-item aps-taxonomy-item" data-value="<?php echo esc_attr( $category->slug ); ?>">
										<?php if ( $category_image ) : ?>
											<span class="taxonomy-image" style="background-image: url('<?php echo esc_url( $category_image ); ?>');"></span>
										<?php endif; ?>
										<span class="taxonomy-name"><?php echo esc_html( $category->name ); ?></span>
										<?php if ( $category_featured ) : ?>
											<span class="taxonomy-badge featured">★</span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<input type="hidden" name="aps_categories" id="aps-categories-input">
					</div>
					<div class="aps-field-group">
						<label>Ribbon Badge</label>
						<div class="aps-multi-select" id="aps-ribbons-select">
							<div class="aps-selected-tags" id="aps-selected-ribbons">
								<span class="multi-select-placeholder">Select ribbons...</span>
							</div>
							<input type="text" class="aps-multiselect-input" placeholder="Select ribbons...">
							<div class="aps-dropdown" id="aps-ribbons-dropdown" style="display:none;">
								<?php
								$ribbons = get_terms( [ 'taxonomy' => 'aps_ribbon', 'hide_empty' => false ] );
								foreach ( $ribbons as $ribbon ) :
									$ribbon_color = get_term_meta( $ribbon->term_id, '_aps_ribbon_color', true ) ?: '#ff6b6b';
									$ribbon_bg = get_term_meta( $ribbon->term_id, '_aps_ribbon_bg_color', true ) ?: '#ff0000';
									$ribbon_icon = get_term_meta( $ribbon->term_id, '_aps_ribbon_icon', true ) ?: '';
								?>
									<div class="dropdown-item aps-ribbon-item" data-value="<?php echo esc_attr( $ribbon->slug ); ?>">
										<span class="ribbon-badge-preview" style="color: <?php echo esc_attr( $ribbon_color ); ?>; background-color: <?php echo esc_attr( $ribbon_bg ); ?>;">
											<?php if ( $ribbon_icon ) : ?>
												<span class="ribbon-icon"><?php echo esc_html( $ribbon_icon ); ?></span>
											<?php endif; ?>
											<span class="ribbon-name"><?php echo esc_html( $ribbon->name ); ?></span>
										</span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<input type="hidden" name="aps_ribbons" id="aps-ribbons-input">
					</div>
				</div>
			</section>
			
			<section id="stats" class="aps-section">
				<h2 class="section-title">PRODUCT STATISTICS</h2>
				<div class="aps-grid-3">
					<div class="aps-field-group">
						<label for="aps-rating">Rating</label>
						<input type="number" id="aps-rating" name="aps_rating" class="aps-input"
							   step="0.1" min="0" max="5" placeholder="4.5"
							   value="<?php echo esc_attr( $product_data['rating'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-views">Views</label>
						<input type="number" id="aps-views" name="aps_views" class="aps-input"
							   min="0" placeholder="325"
							   value="<?php echo esc_attr( $product_data['views'] ?? '' ); ?>">
					</div>
					<div class="aps-field-group">
						<label for="aps-user-count">User Count</label>
						<input type="text" id="aps-user-count" name="aps_user_count" class="aps-input"
							   placeholder="1.5K"
							   value="<?php echo esc_attr( $product_data['user_count'] ?? '' ); ?>">
					</div>
				</div>
				<div class="aps-field-group">
					<label for="aps-reviews">No. of Reviews</label>
						<input type="number" id="aps-reviews" name="aps_reviews" class="aps-input"
						   min="0" placeholder="12"
						   value="<?php echo esc_attr( $product_data['reviews'] ?? '' ); ?>">
				</div>
			</section>
			
			<div class="aps-form-actions">
				<?php if ( $is_editing ) : ?>
					<button type="submit" class="aps-btn aps-btn-primary" name="publish">
						<i class="fas fa-sync-alt"></i> Update Product
					</button>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-btn aps-btn-cancel">
						Cancel
					</a>
				<?php else : ?>
					<button type="submit" class="aps-btn aps-btn-secondary" name="draft">
						<i class="fas fa-file-alt"></i> Save Draft
					</button>
					<button type="submit" class="aps-btn aps-btn-primary" name="publish">
						<i class="fas fa-save"></i> Publish Product
					</button>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aps_product' ) ); ?>" class="aps-btn aps-btn-cancel">
						Cancel
					</a>
				<?php endif; ?>
			</div>
			
			<input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
		</form>
	</div>
</div>

<style>
.affiliate-product-showcase { font-family: 'Inter', sans-serif; }
:root {
	--aps-primary: #2271b1;
	--aps-primary-hover: #135e96;
	--aps-bg: #f0f0f1;
	--aps-card: #ffffff;
	--aps-text: #1d2327;
	--aps-muted: #646970;
	--aps-border: #c3c4c7;
	--aps-danger: #d63638;
}
.aps-header { background: #fff; padding: 20px 30px; border-bottom: 1px solid var(--aps-border); display: flex; justify-content: space-between; align-items: center; margin: -20px -20px 30px -20px; }
.aps-header h1 { font-size: 20px; font-weight: 600; margin: 0; }
.aps-close-btn { background: none; border: none; font-size: 20px; color: var(--aps-muted); cursor: pointer; padding: 8px; }
.aps-close-btn:hover { color: var(--aps-danger); }
.aps-quick-nav { display: flex; gap: 15px; padding: 15px 20px; background: var(--aps-bg); border: 1px solid var(--aps-border); border-radius: 4px; margin-bottom: 30px; flex-wrap: wrap; }
.aps-quick-nav .nav-link { display: flex; align-items: center; gap: 8px; padding: 10px 15px; background: #fff; border: 1px solid var(--aps-border); border-radius: 4px; color: var(--aps-text); text-decoration: none; font-size: 14px; font-weight: 500; }
.aps-quick-nav .nav-link:hover { background: var(--aps-primary); color: #fff; }
.aps-quick-nav .nav-link i { font-size: 16px; }
.aps-form-container { background: var(--aps-card); border: 1px solid var(--aps-border); border-radius: 4px; padding: 30px; max-width: 1200px; margin-bottom: 30px; }
.aps-section { margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid var(--aps-border); }
.section-title { font-size: 14px; font-weight: 600; margin: 0 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid var(--aps-primary); }
.aps-field-group { margin-bottom: 20px; }
.aps-field-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px; }
.aps-field-group .required { color: var(--aps-danger); }
.aps-field-group .description { display: block; margin-top: 6px; font-size: 12px; font-style: italic; color: var(--aps-muted); }
.aps-input { width: 100%; padding: 10px 12px; font-size: 14px; border: 1px solid var(--aps-border); border-radius: 4px; }
.aps-input:focus { border-color: var(--aps-primary); outline: none; }
.aps-textarea { min-height: 200px; width: 100%; resize: vertical; }
.aps-textarea.aps-full-page { min-height: 200px; width: 100%; font-family: inherit; line-height: 1.6; padding: 12px; }
.aps-select { width: 100%; padding: 10px 36px 10px 12px; font-size: 14px; border: 1px solid var(--aps-border); border-radius: 4px; background: #fff; cursor: pointer; }
.aps-readonly { background: #f6f7f7; }
.aps-checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
.aps-checkbox-label input[type="checkbox"] { width: 20px; height: 20px; }
.aps-tags-group { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
.aps-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.aps-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.aps-upload-area { border: 2px dashed var(--aps-border); border-radius: 4px; padding: 20px; text-align: center; background: #f9f9f9; cursor: pointer; position: relative; min-height: 150px; }
.aps-upload-area:hover { border-color: var(--aps-primary); }
.aps-upload-area .upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--aps-muted); }
.aps-upload-area .upload-placeholder i { font-size: 40px; }
.aps-upload-area .image-preview { display: none; position: absolute; width: 100%; height: 150px; background-size: contain; background-repeat: no-repeat; background-position: center; top: 0; left: 0; }
.aps-url-input { margin-top: 15px; }
.aps-upload-btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: var(--aps-primary); color: #fff; border-radius: 4px; font-size: 13px; cursor: pointer; margin-top: 10px; }
.aps-multi-select { position: relative; }
.aps-selected-tags { display: flex; flex-wrap: wrap; gap: 5px; border: 1px solid var(--aps-border); border-radius: 4px; padding: 4px 8px; min-height: 38px; cursor: pointer; }
.aps-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #e6f0f5; color: #2271b1; border-radius: 3px; font-size: 13px; }
.aps-tag .remove-tag { cursor: pointer; font-size: 12px; }
.aps-multiselect-input { border: none; outline: none; font-size: 14px; min-width: 150px; flex: 1; }
.aps-dropdown { position: absolute; background: #fff; border: 1px solid var(--aps-border); border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 100; display: none; }
.aps-dropdown .dropdown-item { padding: 10px 15px; cursor: pointer; }
.aps-dropdown .dropdown-item:hover { background: #f6f7f7; }
.aps-features-list { border: 1px solid var(--aps-border); border-radius: 4px; max-height: 300px; overflow-y: auto; margin-top: 15px; }
.aps-feature-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid #eee; }
.aps-feature-item:hover { background: #f8f9f9; }
.aps-feature-item.highlighted { background: #e6f0f5; }
.aps-feature-item .feature-text { flex-grow: 1; }
.aps-feature-item .feature-actions { display: flex; gap: 5px; }
.aps-feature-item .feature-actions button { background: none; border: 1px solid #ddd; border-radius: 3px; width: 28px; height: 28px; cursor: pointer; color: var(--aps-muted); }
.word-counter { text-align: right; font-size: 12px; color: var(--aps-muted); margin-top: 5px; }
.aps-form-actions { display: flex; align-items: center; gap: 15px; padding-top: 20px; border-top: 1px solid var(--aps-border); }
.aps-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600; border: none; }
.aps-btn-primary { background: var(--aps-primary); color: #fff; }
.aps-btn-primary:hover { background: var(--aps-primary-hover); }
.aps-btn-secondary { background: #646970; color: #fff; }
.aps-btn-cancel { background: transparent; color: var(--aps-muted); }
.aps-tag-checkbox { display: flex; align-items: center; gap: 8px; padding: 6px 12px; background: #f9f9f9; border: 1px solid #eee; border-radius: 4px; cursor: pointer; }
.aps-tag-checkbox .tag-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #e6f0f5; color: #2271b1; border-radius: 4px; font-weight: 600; font-size: 12px; }
.aps-taxonomy-item { display: flex; align-items: center; gap: 10px; }
.aps-taxonomy-item .taxonomy-image { width: 24px; height: 24px; background-size: cover; border-radius: 4px; }
.aps-taxonomy-item .taxonomy-badge { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: #ffd700; color: #fff; font-size: 12px; }
.aps-ribbon-item .ribbon-badge-preview { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; }
@media (max-width: 768px) { .aps-grid-2, .aps-grid-3 { grid-template-columns: 1fr; } .aps-quick-nav { flex-direction: column; } }
</style>

<script>
	const apsProductData = <?php echo wp_json_encode( $product_data ); ?>;
	const apsIsEditing = <?php echo $is_editing ? 'true' : 'false'; ?>;
	
	jQuery(document).ready(function($) {
		console.log('Product Data:', apsProductData);
	
		$('.aps-quick-nav .nav-link').on('click', function(e) {
			e.preventDefault();
			const target = $(this).attr('href');
			$('html, body').animate({ scrollTop: $(target).offset().top - 50 }, 300);
		});
	
		$('#aps-short-description').on('input', function() {
			const text = $(this).val().trim();
			const words = text === '' ? 0 : text.split(/\s+/).length;
			$('#aps-word-count').text(Math.min(words, 40));
		});
	
		$('#aps-current-price, #aps-original-price').on('input', function() {
			const current = parseFloat($('#aps-current-price').val()) || 0;
			const original = parseFloat($('#aps-original-price').val()) || 0;
			// Calculate discount: (original - current) / original * 100
			// Only show discount if original price is greater than current price
			if (current > 0 && original > 0 && original > current) {
				const discount = ((original - current) / original * 100).toFixed(0);
				$('#aps-discount').val(discount + '% OFF');
			} else {
				$('#aps-discount').val('0% OFF');
			}
		});
	
		let features = [];
		if (apsIsEditing && apsProductData.features && Array.isArray(apsProductData.features)) {
			features = apsProductData.features;
			renderFeatures();
		}
	
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
			if (e.which === 13) { e.preventDefault(); $('#aps-add-feature').click(); }
		});
	
		function renderFeatures() {
			const container = $('#aps-features-list');
			container.empty();
			features.forEach((feature, index) => {
				const html = `<div class="aps-feature-item ${feature.highlighted ? 'highlighted' : ''}" data-index="${index}">
					<span class="feature-text">${feature.text.replace(/</g, '<')}</span>
					<div class="feature-actions">
						<button type="button" class="highlight-btn" title="Highlight"><i class="fas fa-bold"></i></button>
						<button type="button" class="move-up" title="Move Up"><i class="fas fa-arrow-up"></i></button>
						<button type="button" class="move-down" title="Move Down"><i class="fas fa-arrow-down"></i></button>
						<button type="button" class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
					</div>`;
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
	
		const selectedCategories = [];
		if (apsIsEditing && apsProductData.categories && Array.isArray(apsProductData.categories)) {
			apsProductData.categories.forEach(catSlug => {
				if (!selectedCategories.includes(catSlug)) selectedCategories.push(catSlug);
			});
			renderCategories();
			$('#aps-categories-input').val(selectedCategories.join(','));
		}
	
		$('.aps-multi-select .aps-multiselect-input').on('focus click', function() {
			$(this).siblings('.aps-dropdown').slideDown(200);
		});
	
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.aps-multi-select').length) {
				$('.aps-dropdown').slideUp(200);
			}
		});
	
		$('#aps-categories-dropdown .dropdown-item').on('click', function() {
			const value = $(this).data('value');
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
				container.append(`<span class="aps-tag">${text}<span class="remove-tag" data-index="${index}">&times;</span></span>`);
			});
		}
	
		$(document).on('click', '#aps-selected-categories .remove-tag', function() {
			const index = $(this).data('index');
			selectedCategories.splice(index, 1);
			renderCategories();
			$('#aps-categories-input').val(selectedCategories.join(','));
		});
	
		const selectedRibbons = [];
		const ribbonData = {}; // Store ribbon styling data
	
		if (apsIsEditing && apsProductData.ribbons && Array.isArray(apsProductData.ribbons)) {
			apsProductData.ribbons.forEach(ribbonSlug => {
				if (!selectedRibbons.includes(ribbonSlug)) selectedRibbons.push(ribbonSlug);
			});
			renderRibbons();
			$('#aps-ribbons-input').val(selectedRibbons.join(','));
		}
	
		$('#aps-ribbons-dropdown .dropdown-item').on('click', function() {
			const value = $(this).data('value');
			if (!selectedRibbons.includes(value)) {
				selectedRibbons.push(value);
				// Store ribbon styling data
				const preview = $(this).find('.ribbon-badge-preview');
				ribbonData[value] = {
					color: preview.css('color'),
					backgroundColor: preview.css('background-color')
				};
				renderRibbons();
				$('#aps-ribbons-input').val(selectedRibbons.join(','));
			}
		});
	
		function renderRibbons() {
			const container = $('#aps-selected-ribbons');
			container.empty();
			selectedRibbons.forEach((ribbon, index) => {
				const dropdownItem = $('#aps-ribbons-dropdown .dropdown-item[data-value="' + ribbon + '"]');
				const preview = dropdownItem.find('.ribbon-badge-preview');
				const color = preview.css('color');
				const bgColor = preview.css('background-color');
				const text = dropdownItem.find('.ribbon-name').text();
				const icon = dropdownItem.find('.ribbon-icon').text();
				const iconHtml = icon ? `<span class="ribbon-icon">${icon}</span>` : '';
				container.append(`<span class="aps-tag" style="color: ${color}; background-color: ${bgColor};">${iconHtml}${text}<span class="remove-tag" data-index="${index}">&times;</span></span>`);
			});
		}
	
		$(document).on('click', '#aps-selected-ribbons .remove-tag', function() {
			const index = $(this).data('index');
			selectedRibbons.splice(index, 1);
			renderRibbons();
			$('#aps-ribbons-input').val(selectedRibbons.join(','));
		});
	
		$('#aps-upload-image-btn').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				alert('WordPress media library is not loaded. Please refresh page.');
				return;
			}
			const mediaUploader = wp.media({ title: 'Select Image', button: { text: 'Use This Image' }, multiple: false });
			mediaUploader.on('select', function() {
				const attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#aps-image-url').val(attachment.url);
				$('#aps-image-url-input').val(attachment.url);
				$('#aps-image-preview').css('background-image', 'url(' + attachment.url + ')').show();
				$('#aps-image-upload .upload-placeholder').hide();
				$('#aps-remove-image-btn').show();
			});
			mediaUploader.open();
		});
	
		$('#aps-upload-brand-btn').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
				alert('WordPress media library is not loaded. Please refresh page.');
				return;
			}
			const mediaUploader = wp.media({ title: 'Select Brand Image', button: { text: 'Use This Image' }, multiple: false });
			mediaUploader.on('select', function() {
				const attachment = mediaUploader.state().get('selection').first().toJSON();
				$('#aps-brand-image-url').val(attachment.url);
				$('#aps-brand-url-input').val(attachment.url);
				$('#aps-brand-preview').css('background-image', 'url(' + attachment.url + ')').show();
				$('#aps-brand-upload .upload-placeholder').hide();
				$('#aps-remove-brand-btn').show();
			});
			mediaUploader.open();
		});
	
		$('#aps-image-url-input').on('blur', function() {
			const url = $(this).val();
			if (url) {
				$('#aps-image-url').val(url);
				$('#aps-image-preview').css('background-image', 'url(' + url + ')').show();
				$('#aps-image-upload .upload-placeholder').hide();
				$('#aps-remove-image-btn').show();
			} else {
				$('#aps-image-url').val('');
				$('#aps-image-preview').css('background-image', 'none').hide();
				$('#aps-image-upload .upload-placeholder').show();
				$('#aps-remove-image-btn').hide();
			}
		});
	
		$('#aps-brand-url-input').on('blur', function() {
			const url = $(this).val();
			if (url) {
				$('#aps-brand-image-url').val(url);
				$('#aps-brand-preview').css('background-image', 'url(' + url + ')').show();
				$('#aps-brand-upload .upload-placeholder').hide();
				$('#aps-remove-brand-btn').show();
			} else {
				$('#aps-brand-image-url').val('');
				$('#aps-brand-preview').css('background-image', 'none').hide();
				$('#aps-brand-upload .upload-placeholder').show();
				$('#aps-remove-brand-btn').hide();
			}
		});
	
		// Remove image functionality
		$('#aps-remove-image-btn').on('click', function() {
			$('#aps-image-url').val('');
			$('#aps-image-url-input').val('');
			$('#aps-image-preview').css('background-image', 'none').hide();
			$('#aps-image-upload .upload-placeholder').show();
			$(this).hide();
		});
	
		$('#aps-remove-brand-btn').on('click', function() {
			$('#aps-brand-image-url').val('');
			$('#aps-brand-url-input').val('');
			$('#aps-brand-preview').css('background-image', 'none').hide();
			$('#aps-brand-upload .upload-placeholder').show();
			$(this).hide();
		});
	
		if (apsIsEditing) {
			if (apsProductData.rating) $('#aps-rating').val(apsProductData.rating);
			if (apsProductData.views) $('#aps-views').val(apsProductData.views);
			if (apsProductData.reviews) $('#aps-reviews').val(apsProductData.reviews);
			if (apsProductData.short_description) $('#aps-short-description').val(apsProductData.short_description);
			if (apsProductData.regular_price) $('#aps-current-price').val(apsProductData.regular_price);
			if (apsProductData.original_price) $('#aps-original-price').val(apsProductData.original_price);
		}
	
	});
</script>
