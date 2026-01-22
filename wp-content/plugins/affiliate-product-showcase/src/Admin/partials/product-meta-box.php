<?php
/**
 * Product Meta Box Template
 *
 * WooCommerce-style single-page form with 10 field groups.
 *
 * @var array $meta Current product meta values
 * @var WP_Post $post Post object
 */

if ( ! isset( $meta ) ) {
    $meta = array(); // Fallback for new products
}

// Use nonce field for security
wp_nonce_field( 'aps_meta_box', 'aps_meta_box_nonce' );
?>

<div class="aps-product-form">
    <!-- Group 1: Product Information (Basic) -->
    <div class="aps-form-section aps-section-product-info">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e( 'Product Information', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-text">
                <label for="aps_sku">
                    <?php esc_html_e( 'SKU / Product ID', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Unique identifier for this product', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="text" 
                       name="aps_sku" 
                       id="aps_sku" 
                       value="<?php echo esc_attr( $meta['sku'] ?? '' ); ?>" 
                       class="regular-text aps-input"
                       placeholder="<?php esc_attr_e( 'e.g., PROD-001', 'affiliate-product-showcase' ); ?>" />
            </div>
            
            <div class="aps-field aps-field-select">
                <label for="aps_brand">
                    <?php esc_html_e( 'Brand / Manufacturer', 'affiliate-product-showcase' ); ?>
                </label>
                <select name="aps_brand" id="aps_brand" class="aps-select">
                    <option value=""><?php esc_html_e( 'Select brand...', 'affiliate-product-showcase' ); ?></option>
                    <?php
                    $brands = get_terms( array(
                        'taxonomy' => 'aps_brand',
                        'hide_empty' => false,
                    ) );
                    foreach ( $brands as $brand ) :
                    ?>
                    <option value="<?php echo esc_attr( $brand->term_id ); ?>" <?php selected( $meta['brand'] ?? '', $brand->term_id ); ?>>
                        <?php echo esc_html( $brand->name ); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Group 2: Pricing -->
    <div class="aps-form-section aps-section-pricing">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-cart"></span>
            <?php esc_html_e( 'Pricing', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-number">
                <label for="aps_regular_price">
                    <?php esc_html_e( 'Regular Price', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Original price before discount', 'affiliate-product-showcase' ); ?></span>
                </label>
                <div class="aps-input-group">
                    <span class="aps-input-prefix"><?php echo esc_html( $meta['currency'] ?? 'USD' ); ?></span>
                    <input type="number" 
                           name="aps_regular_price" 
                           id="aps_regular_price" 
                           step="0.01" 
                           min="0" 
                           value="<?php echo esc_attr( $meta['regular_price'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-money"
                           placeholder="0.00" />
                </div>
            </div>
            
            <div class="aps-field aps-field-number">
                <label for="aps_sale_price">
                    <?php esc_html_e( 'Sale Price', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Discounted price (optional)', 'affiliate-product-showcase' ); ?></span>
                </label>
                <div class="aps-input-group">
                    <span class="aps-input-prefix"><?php echo esc_html( $meta['currency'] ?? 'USD' ); ?></span>
                    <input type="number" 
                           name="aps_sale_price" 
                           id="aps_sale_price" 
                           step="0.01" 
                           min="0" 
                           value="<?php echo esc_attr( $meta['sale_price'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-money"
                           placeholder="0.00" />
                </div>
            </div>
            
            <div class="aps-field aps-field-number">
                <label for="aps_discount_percentage">
                    <?php esc_html_e( 'Discount Percentage', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Auto-calculated or manual override', 'affiliate-product-showcase' ); ?></span>
                </label>
                <div class="aps-input-group">
                    <input type="number" 
                           name="aps_discount_percentage" 
                           id="aps_discount_percentage" 
                           step="0.1" 
                           min="0" 
                           max="100" 
                           value="<?php echo esc_attr( $meta['discount_percentage'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-percent"
                           placeholder="0.0" />
                    <span class="aps-input-suffix">%</span>
                </div>
            </div>
            
            <div class="aps-field aps-field-select">
                <label for="aps_currency">
                    <?php esc_html_e( 'Currency', 'affiliate-product-showcase' ); ?>
                </label>
                <select name="aps_currency" id="aps_currency" class="aps-select">
                    <option value="USD" <?php selected( $meta['currency'] ?? '', 'USD' ); ?>><?php esc_html_e( 'US Dollar ($)', 'affiliate-product-showcase' ); ?></option>
                    <option value="EUR" <?php selected( $meta['currency'] ?? '', 'EUR' ); ?>><?php esc_html_e( 'Euro (€)', 'affiliate-product-showcase' ); ?></option>
                    <option value="GBP" <?php selected( $meta['currency'] ?? '', 'GBP' ); ?>><?php esc_html_e( 'British Pound (£)', 'affiliate-product-showcase' ); ?></option>
                    <option value="JPY" <?php selected( $meta['currency'] ?? '', 'JPY' ); ?>><?php esc_html_e( 'Japanese Yen (¥)', 'affiliate-product-showcase' ); ?></option>
                    <option value="AUD" <?php selected( $meta['currency'] ?? '', 'AUD' ); ?>><?php esc_html_e( 'Australian Dollar (A$)', 'affiliate-product-showcase' ); ?></option>
                    <option value="CAD" <?php selected( $meta['currency'] ?? '', 'CAD' ); ?>><?php esc_html_e( 'Canadian Dollar (C$)', 'affiliate-product-showcase' ); ?></option>
                    <option value="INR" <?php selected( $meta['currency'] ?? '', 'INR' ); ?>><?php esc_html_e( 'Indian Rupee (₹)', 'affiliate-product-showcase' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Group 3: Product Data -->
    <div class="aps-form-section aps-section-product-data">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-database"></span>
            <?php esc_html_e( 'Product Data', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content aps-grid-2">
            <div class="aps-field aps-field-select">
                <label for="aps_stock_status">
                    <?php esc_html_e( 'Stock Status', 'affiliate-product-showcase' ); ?>
                </label>
                <select name="aps_stock_status" id="aps_stock_status" class="aps-select">
                    <option value="instock" <?php selected( $meta['stock_status'] ?? '', 'instock' ); ?>>
                        <?php esc_html_e( 'In Stock', 'affiliate-product-showcase' ); ?>
                    </option>
                    <option value="outofstock" <?php selected( $meta['stock_status'] ?? '', 'outofstock' ); ?>>
                        <?php esc_html_e( 'Out of Stock', 'affiliate-product-showcase' ); ?>
                    </option>
                    <option value="preorder" <?php selected( $meta['stock_status'] ?? '', 'preorder' ); ?>>
                        <?php esc_html_e( 'Pre-order', 'affiliate-product-showcase' ); ?>
                    </option>
                </select>
            </div>
            
            <div class="aps-field aps-field-date">
                <label for="aps_availability_date">
                    <?php esc_html_e( 'Availability Date', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'For pre-orders', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="date" 
                       name="aps_availability_date" 
                       id="aps_availability_date" 
                       value="<?php echo esc_attr( $meta['availability_date'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-date" />
            </div>
            
            <div class="aps-field aps-field-number">
                <label for="aps_rating">
                    <?php esc_html_e( 'Rating', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( '0-5 stars', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="number" 
                       name="aps_rating" 
                       id="aps_rating" 
                       min="0" 
                       max="5" 
                       step="0.1" 
                       value="<?php echo esc_attr( $meta['rating'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-number"
                       placeholder="4.5" />
            </div>
            
            <div class="aps-field aps-field-number">
                <label for="aps_review_count">
                    <?php esc_html_e( 'Review Count', 'affiliate-product-showcase' ); ?>
                </label>
                <input type="number" 
                       name="aps_review_count" 
                       id="aps_review_count" 
                       min="0" 
                       value="<?php echo esc_attr( $meta['review_count'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-number"
                       placeholder="0" />
            </div>
        </div>
    </div>

    <!-- Group 4: Product Media -->
    <div class="aps-form-section aps-section-media">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-format-image"></span>
            <?php esc_html_e( 'Product Media', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-url">
                <label for="aps_video_url">
                    <?php esc_html_e( 'Product Video URL', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'YouTube or Vimeo URL', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="url" 
                       name="aps_video_url" 
                       id="aps_video_url" 
                       value="<?php echo esc_url( $meta['video_url'] ?? '' ); ?>" 
                       class="large-text aps-input aps-input-url"
                       placeholder="<?php esc_attr_e( 'https://www.youtube.com/watch?v=...', 'affiliate-product-showcase' ); ?>" />
            </div>
        </div>
    </div>

    <!-- Group 5: Shipping & Dimensions -->
    <div class="aps-form-section aps-section-shipping">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-portfolio"></span>
            <?php esc_html_e( 'Shipping & Dimensions', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-number">
                <label for="aps_weight">
                    <?php esc_html_e( 'Weight (kg)', 'affiliate-product-showcase' ); ?>
                </label>
                <input type="number" 
                       name="aps_weight" 
                       id="aps_weight" 
                       step="0.01" 
                       min="0" 
                       value="<?php echo esc_attr( $meta['weight'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-number"
                       placeholder="0.00" />
            </div>
            
            <div class="aps-dimensions">
                <div class="aps-field aps-field-number">
                    <label for="aps_length">
                        <?php esc_html_e( 'Length (cm)', 'affiliate-product-showcase' ); ?>
                    </label>
                    <input type="number" 
                           name="aps_length" 
                           id="aps_length" 
                           step="0.1" 
                           min="0" 
                           value="<?php echo esc_attr( $meta['length'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-number"
                           placeholder="0.0" />
                </div>
                
                <div class="aps-field aps-field-number">
                    <label for="aps_width">
                        <?php esc_html_e( 'Width (cm)', 'affiliate-product-showcase' ); ?>
                    </label>
                    <input type="number" 
                           name="aps_width" 
                           id="aps_width" 
                           step="0.1" 
                           min="0" 
                           value="<?php echo esc_attr( $meta['width'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-number"
                           placeholder="0.0" />
                </div>
                
                <div class="aps-field aps-field-number">
                    <label for="aps_height">
                        <?php esc_html_e( 'Height (cm)', 'affiliate-product-showcase' ); ?>
                    </label>
                    <input type="number" 
                           name="aps_height" 
                           id="aps_height" 
                           step="0.1" 
                           min="0" 
                           value="<?php echo esc_attr( $meta['height'] ?? '' ); ?>" 
                           class="regular-text aps-input aps-input-number"
                           placeholder="0.0" />
                </div>
            </div>
        </div>
    </div>

    <!-- Group 6: Affiliate & Links -->
    <div class="aps-form-section aps-section-links">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-admin-links"></span>
            <?php esc_html_e( 'Affiliate & Links', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-url">
                <label for="aps_affiliate_url">
                    <?php esc_html_e( 'Affiliate URL', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-required">*</span>
                    <span class="aps-field-tip"><?php esc_html_e( 'Where users go when clicking "Buy Now"', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="url" 
                       name="aps_affiliate_url" 
                       id="aps_affiliate_url" 
                       value="<?php echo esc_url( $meta['affiliate_url'] ?? '' ); ?>" 
                       class="large-text aps-input aps-input-url aps-input-required"
                       placeholder="<?php esc_attr_e( 'https://example.com/product', 'affiliate-product-showcase' ); ?>"
                       required />
            </div>
            
            <div class="aps-field aps-field-url">
                <label for="aps_coupon_url">
                    <?php esc_html_e( 'Coupon/Discount URL', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Optional: Direct link to discount', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="url" 
                       name="aps_coupon_url" 
                       id="aps_coupon_url" 
                       value="<?php echo esc_url( $meta['coupon_url'] ?? '' ); ?>" 
                       class="large-text aps-input aps-input-url"
                       placeholder="<?php esc_attr_e( 'https://example.com/product?coupon=...', 'affiliate-product-showcase' ); ?>" />
            </div>
        </div>
    </div>

    <!-- Group 7: Product Ribbons -->
    <div class="aps-form-section aps-section-ribbons">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-awards"></span>
            <?php esc_html_e( 'Product Ribbons', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-checkbox">
                <label>
                    <input type="checkbox" 
                           name="aps_featured" 
                           id="aps_featured" 
                           value="1" 
                           <?php checked( $meta['featured'] ?? false ); ?> />
                    <?php esc_html_e( 'Featured Product', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Show in featured products section', 'affiliate-product-showcase' ); ?></span>
                </label>
            </div>
            
            <div class="aps-field aps-field-select">
                <label for="aps_ribbon">
                    <?php esc_html_e( 'Ribbon', 'affiliate-product-showcase' ); ?>
                </label>
                <select name="aps_ribbon" id="aps_ribbon" class="aps-select">
                    <option value=""><?php esc_html_e( 'Select ribbon...', 'affiliate-product-showcase' ); ?></option>
                    <?php
                    $ribbons = get_terms( array(
                        'taxonomy' => 'aps_ribbon',
                        'hide_empty' => false,
                    ) );
                    foreach ( $ribbons as $ribbon ) :
                    ?>
                    <option value="<?php echo esc_attr( $ribbon->term_id ); ?>" <?php selected( $meta['ribbon'] ?? '', $ribbon->term_id ); ?>>
                        <?php echo esc_html( $ribbon->name ); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="aps-field aps-field-text">
                <label for="aps_badge_text">
                    <?php esc_html_e( 'Custom Badge Text', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Override ribbon text if needed', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="text" 
                       name="aps_badge_text" 
                       id="aps_badge_text" 
                       value="<?php echo esc_attr( $meta['badge_text'] ?? '' ); ?>" 
                       class="regular-text aps-input"
                       placeholder="<?php esc_attr_e( 'e.g., Best Seller', 'affiliate-product-showcase' ); ?>" />
            </div>
        </div>
    </div>

    <!-- Group 8: Additional Information -->
    <div class="aps-form-section aps-section-additional">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-editor-alignleft"></span>
            <?php esc_html_e( 'Additional Information', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content">
            <div class="aps-field aps-field-textarea">
                <label for="aps_warranty">
                    <?php esc_html_e( 'Warranty Information', 'affiliate-product-showcase' ); ?>
                </label>
                <textarea name="aps_warranty" 
                          id="aps_warranty" 
                          rows="3" 
                          class="large-text aps-input aps-input-textarea"
                          placeholder="<?php esc_attr_e( 'e.g., 2-year manufacturer warranty', 'affiliate-product-showcase' ); ?>"><?php echo esc_textarea( $meta['warranty'] ?? '' ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Group 9: Product Scheduling -->
    <div class="aps-form-section aps-section-scheduling">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-clock"></span>
            <?php esc_html_e( 'Product Scheduling', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content aps-grid-2">
            <div class="aps-field aps-field-date">
                <label for="aps_release_date">
                    <?php esc_html_e( 'Release Date', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'When product becomes available', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="date" 
                       name="aps_release_date" 
                       id="aps_release_date" 
                       value="<?php echo esc_attr( $meta['release_date'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-date" />
            </div>
            
            <div class="aps-field aps-field-date">
                <label for="aps_expiration_date">
                    <?php esc_html_e( 'Expiration Date', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'When offer ends (optional)', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="date" 
                       name="aps_expiration_date" 
                       id="aps_expiration_date" 
                       value="<?php echo esc_attr( $meta['expiration_date'] ?? '' ); ?>" 
                       class="regular-text aps-input aps-input-date" />
            </div>
        </div>
    </div>

    <!-- Group 10: Display Settings -->
    <div class="aps-form-section aps-section-display">
        <h2 class="aps-section-title">
            <span class="dashicons dashicons-admin-appearance"></span>
            <?php esc_html_e( 'Display Settings', 'affiliate-product-showcase' ); ?>
        </h2>
        
        <div class="aps-section-content aps-grid-2">
            <div class="aps-field aps-field-number">
                <label for="aps_display_order">
                    <?php esc_html_e( 'Display Order', 'affiliate-product-showcase' ); ?>
                    <span class="aps-field-tip"><?php esc_html_e( 'Lower numbers appear first', 'affiliate-product-showcase' ); ?></span>
                </label>
                <input type="number" 
                       name="aps_display_order" 
                       id="aps_display_order" 
                       min="0" 
                       value="<?php echo esc_attr( $meta['display_order'] ?? '0' ); ?>" 
                       class="regular-text aps-input aps-input-number"
                       placeholder="0" />
            </div>
            
            <div class="aps-field aps-field-checkbox">
                <label>
                    <input type="checkbox" 
                           name="aps_hide_from_home" 
                           id="aps_hide_from_home" 
                           value="1" 
                           <?php checked( $meta['hide_from_home'] ?? false ); ?> />
                    <?php esc_html_e( 'Hide from Homepage', 'affiliate-product-showcase' ); ?>
                </label>
            </div>
        </div>
    </div>
</div>
