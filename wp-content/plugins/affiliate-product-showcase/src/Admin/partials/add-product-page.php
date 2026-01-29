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
}

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
	
	<nav class="aps-quick-nav" aria-label="Form sections quick navigation">
		<a href="#product-info" class="nav-link"><i class="fas fa-edit" aria-hidden="true"></i> Product Info</a>
		<a href="#images" class="nav-link"><i class="fas fa-images" aria-hidden="true"></i> Images</a>
		<a href="#affiliate" class="nav-link"><i class="fas fa-link" aria-hidden="true"></i> Affiliate</a>
		<a href="#features" class="nav-link"><i class="fas fa-list" aria-hidden="true"></i> Features</a>
		<a href="#pricing" class="nav-link"><i class="fas fa-tag" aria-hidden="true"></i> Pricing</a>
		<a href="#taxonomy" class="nav-link"><i class="fas fa-folder" aria-hidden="true"></i> Categories & Tags</a>
		<a href="#stats" class="nav-link"><i class="fas fa-chart-bar" aria-hidden="true"></i> Statistics</a>
	</nav>
	
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
			
			<section id="product-info" class="aps-section" aria-labelledby="product-info-title">
				<h2 id="product-info-title" class="section-title">PRODUCT INFO</h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label for="aps-product-title">Product Title <span class="required">*</span></label>
						<input type="text" id="aps-product-title" name="aps_title" class="aps-input"
							   placeholder="Enter title..." required aria-required="true"
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
			
			<section id="images" class="aps-section" aria-labelledby="images-title">
				<h2 id="images-title" class="section-title">PRODUCT IMAGES</h2>
				<div class="aps-grid-2">
					<div class="aps-upload-group">
						<label>Product Image (Featured)</label>
						<div class="aps-upload-area" id="aps-image-upload">
							<div class="upload-placeholder">
								<i class="fas fa-camera"></i>
								<p>Click to upload or enter URL below</p>
							</div>
							<div class="image-preview aps-image-preview <?php echo !empty( $product_data['logo'] ) ? 'has-image' : 'no-image'; ?>" id="aps-image-preview" data-image-url="<?php echo esc_url( $product_data['logo'] ?? '' ); ?>"></div>
							<input type="hidden" name="aps_image_url" id="aps-image-url" value="<?php echo esc_attr( $product_data['logo'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-image-btn">
								<i class="fas fa-upload"></i> Select from Media Library
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel aps-hidden" id="aps-remove-image-btn">
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
							<div class="image-preview aps-brand-preview <?php echo !empty( $product_data['brand_image'] ) ? 'has-image' : 'no-image'; ?>" id="aps-brand-preview" data-image-url="<?php echo esc_url( $product_data['brand_image'] ?? '' ); ?>"></div>
							<input type="hidden" name="aps_brand_image_url" id="aps-brand-image-url" value="<?php echo esc_attr( $product_data['brand_image'] ?? '' ); ?>">
							<button type="button" class="aps-upload-btn" id="aps-upload-brand-btn">
								<i class="fas fa-upload"></i> Select from Media Library
							</button>
							<button type="button" class="aps-upload-btn aps-btn-cancel aps-hidden" id="aps-remove-brand-btn">
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
			
			<section id="affiliate" class="aps-section" aria-labelledby="affiliate-title">
				<h2 id="affiliate-title" class="section-title">AFFILIATE DETAILS</h2>
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
			
			<section id="short-description" class="aps-section" aria-labelledby="short-description-title">
				<h2 id="short-description-title" class="section-title">SHORT DESCRIPTION</h2>
				<div class="aps-field-group">
					<label for="aps-short-description">Short Description <span class="required">*</span></label>
					<textarea id="aps-short-description" name="aps_short_description" class="aps-textarea aps-full-page"
							  rows="6" maxlength="200"
							  placeholder="Enter short description (max 40 words)..." required aria-required="true"
							  aria-describedby="aps-word-counter"
							  data-initial="<?php echo esc_attr( $product_data['short_description'] ?? '' ); ?>"><?php echo esc_textarea( $product_data['short_description'] ?? '' ); ?></textarea>
					<div class="word-counter" id="aps-word-counter" aria-live="polite">
						<span id="aps-word-count">0</span>/40 Words
					</div>
				</div>
			</section>
			
			<section id="features" class="aps-section" aria-labelledby="features-title">
				<h2 id="features-title" class="section-title">FEATURE LIST</h2>
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
			
			<section id="pricing" class="aps-section" aria-labelledby="pricing-title">
				<h2 id="pricing-title" class="section-title">PRICING</h2>
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
			
			<section id="taxonomy" class="aps-section" aria-labelledby="taxonomy-title">
				<h2 id="taxonomy-title" class="section-title">CATEGORIES & RIBBONS</h2>
				<div class="aps-grid-2">
					<div class="aps-field-group">
						<label id="aps-categories-label">Category</label>
						<div class="aps-multi-select" id="aps-categories-select" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-labelledby="aps-categories-label">
							<div class="aps-selected-tags" id="aps-selected-categories" role="listbox" aria-live="polite">
								<span class="multi-select-placeholder">Select categories...</span>
							</div>
							<input type="text" class="aps-multiselect-input" placeholder="Select categories..." aria-autocomplete="list" aria-controls="aps-categories-dropdown">
							<div class="aps-dropdown aps-hidden" id="aps-categories-dropdown" role="listbox">
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
						<label id="aps-ribbons-label">Ribbon Badge</label>
						<div class="aps-multi-select" id="aps-ribbons-select" role="combobox" aria-expanded="false" aria-haspopup="listbox" aria-labelledby="aps-ribbons-label">
							<div class="aps-selected-tags" id="aps-selected-ribbons" role="listbox" aria-live="polite">
								<span class="multi-select-placeholder">Select ribbons...</span>
							</div>
							<input type="text" class="aps-multiselect-input" placeholder="Select ribbons..." aria-autocomplete="list" aria-controls="aps-ribbons-dropdown">
							<div class="aps-dropdown aps-hidden" id="aps-ribbons-dropdown" role="listbox">
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
			
			<section id="stats" class="aps-section" aria-labelledby="stats-title">
				<h2 id="stats-title" class="section-title">PRODUCT STATISTICS</h2>
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
			
			<div class="aps-form-actions" role="group" aria-label="Form actions">
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
