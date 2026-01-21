<?php
/**
 * Product Meta Box Template
 *
 * Displays meta fields for add/edit product screens.
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
<div class="aps-meta-box">
    <div class="aps-field">
        <label for="aps_price"><?php esc_html_e( 'Price', 'affiliate-product-showcase' ); ?></label><br />
        <input type="number" 
               name="aps_price" 
               id="aps_price" 
               step="0.01" 
               value="<?php echo esc_attr( $meta['price'] ?? '' ); ?>" 
               class="regular-text" 
               placeholder="<?php esc_attr_e( 'Enter price', 'affiliate-product-showcase' ); ?>" />
    </div>
    
    <div class="aps-field">
        <label for="aps_currency"><?php esc_html_e( 'Currency', 'affiliate-product-showcase' ); ?></label><br />
        <input type="text" 
               name="aps_currency" 
               id="aps_currency" 
               value="<?php echo esc_attr( $meta['currency'] ?? 'USD' ); ?>" 
               class="regular-text" 
               placeholder="<?php esc_attr_e( 'e.g., USD, EUR, GBP', 'affiliate-product-showcase' ); ?>" />
    </div>
    
    <div class="aps-field">
        <label for="aps_affiliate_url"><?php esc_html_e( 'Affiliate URL', 'affiliate-product-showcase' ); ?></label><br />
        <input type="url" 
               name="aps_affiliate_url" 
               id="aps_affiliate_url" 
               value="<?php echo esc_url( $meta['affiliate_url'] ?? '' ); ?>" 
               class="large-text" 
               placeholder="<?php esc_attr_e( 'https://example.com/product', 'affiliate-product-showcase' ); ?>" />
    </div>
    
    <div class="aps-field">
        <label for="aps_image_url"><?php esc_html_e( 'Image URL', 'affiliate-product-showcase' ); ?></label><br />
        <input type="url" 
               name="aps_image_url" 
               id="aps_image_url" 
               value="<?php echo esc_url( $meta['image_url'] ?? '' ); ?>" 
               class="large-text" 
               placeholder="<?php esc_attr_e( 'https://example.com/image.jpg', 'affiliate-product-showcase' ); ?>" />
    </div>
    
    <div class="aps-field">
        <label for="aps_rating"><?php esc_html_e( 'Rating (0-5)', 'affiliate-product-showcase' ); ?></label><br />
        <input type="number" 
               name="aps_rating" 
               id="aps_rating" 
               min="0" 
               max="5" 
               step="0.1" 
               value="<?php echo esc_attr( $meta['rating'] ?? '' ); ?>" 
               class="regular-text" 
               placeholder="<?php esc_attr_e( 'e.g., 4.5', 'affiliate-product-showcase' ); ?>" />
    </div>
    
    <div class="aps-field">
        <label for="aps_badge"><?php esc_html_e( 'Badge (e.g., "Best Seller")', 'affiliate-product-showcase' ); ?></label><br />
        <input type="text" 
               name="aps_badge" 
               id="aps_badge" 
               value="<?php echo esc_attr( $meta['badge'] ?? '' ); ?>" 
               class="regular-text" 
               placeholder="<?php esc_attr_e( 'e.g., Sale, New, Best Seller', 'affiliate-product-showcase' ); ?>" />
    </div>
</div>
