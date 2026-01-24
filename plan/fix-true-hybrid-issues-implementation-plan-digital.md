# Fix True Hybrid Issues - Implementation Plan (Digital Products Only)

**Created:** January 23, 2026  
**Priority:** CRITICAL - Must fix immediately  
**Plugin Type:** Affiliate Digital Product Showcase (software, courses, ebooks, templates, subscriptions)  
**Estimated Time:** 2-3 weeks

---

## Executive Summary

This plan addresses critical issues preventing the digital products page from following true hybrid approach. The main problems are:

1. **ProductFactory reads wrong meta keys** (missing underscore prefix)
2. **Product model missing 15+ properties** defined in MetaBoxes
3. **Field mapping inconsistencies** causing data corruption
4. **Missing image upload** functionality

**Impact:** All product data is saved but cannot be retrieved. Frontend shows empty/null values.

**Important:** This plugin is for **affiliate digital products only**. Physical product fields (weight, dimensions, shipping) are **NOT APPLICABLE** and should be removed or marked as optional.

---

## Phase 1: Fix ProductFactory Meta Key Prefix (CRITICAL)

**Priority:** 🔴 HIGHEST - Must fix immediately  
**Estimated Time:** 2 hours  
**Files to Modify:** `src/Factories/ProductFactory.php`

### Issue
ProductFactory reads from `aps_*` prefix but MetaBoxes saves to `_aps_*` prefix (missing underscore).

### Changes Required

**File:** `src/Factories/ProductFactory.php`  
**Method:** `from_post(WP_Post $post): Product`

**Current Code (Lines 61-75):**
```php
$featured_meta = $meta['aps_featured'][0] ?? '';
$currency      = $meta['aps_currency'][0] ?? 'USD',
$price         = (float) ($meta['aps_price'][0] ?? 0 ),
$original_price = isset($meta['aps_original_price'][0]) 
    ? (float) $meta['aps_original_price'][0] : null,
$affiliate_url = esc_url_raw($meta['aps_affiliate_url'][0] ?? '' ),
$image_url     = esc_url_raw($meta['aps_image_url'][0] ?? '' ) ?: null,
$rating        = isset($meta['aps_rating'][0]) 
    ? (float) $meta['aps_rating'][0] : null,
$badge         = sanitize_text_field($meta['aps_badge'][0] ?? '' ) ?: null,
```

**Fixed Code:**
```php
$featured_meta = $meta['_aps_featured'][0] ?? '';
$currency      = $meta['_aps_currency'][0] ?? 'USD',
$regular_price = (float) ($meta['_aps_regular_price'][0] ?? 0 ),
$sale_price     = isset($meta['_aps_sale_price'][0]) 
    ? (float) $meta['_aps_sale_price'][0] : null,
$affiliate_url = esc_url_raw($meta['_aps_affiliate_url'][0] ?? '' ),
$image_url     = esc_url_raw($meta['_aps_image_url'][0] ?? '' ) ?: null,
$rating        = isset($meta['_aps_rating'][0]) 
    ? (float) $meta['_aps_rating'][0] : null,
$badge_text    = sanitize_text_field($meta['_aps_badge_text'][0] ?? '' ) ?: null,
```

**Complete Meta Key Mapping Changes:**

| Current (Wrong) | Fixed (Correct) |
|-----------------|----------------|
| `aps_featured` | `_aps_featured` |
| `aps_currency` | `_aps_currency` |
| `aps_price` | `_aps_regular_price` |
| `aps_original_price` | `_aps_sale_price` |
| `aps_affiliate_url` | `_aps_affiliate_url` |
| `aps_image_url` | `_aps_image_url` |
| `aps_rating` | `_aps_rating` |
| `aps_badge` | `_aps_badge_text` |

### Implementation Steps

1. **Backup current ProductFactory.php**
   ```bash
   cp src/Factories/ProductFactory.php backups/ProductFactory.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update ProductFactory.php**
   - Open `src/Factories/ProductFactory.php`
   - Go to line 61-75
   - Replace all `aps_*` meta keys with `_aps_*` prefix
   - Replace `aps_price` with `_aps_regular_price`
   - Replace `aps_original_price` with `_aps_sale_price`
   - Replace `aps_badge` with `_aps_badge_text`

3. **Update Product constructor call**
   ```php
   // Update Product constructor call to match new property names:
   return new Product(
       $post->ID,
       $post->post_title,
       $post->post_name, // slug
       $post->post_content, // description
       $currency,
       $regular_price,  // Changed from $price
       $sale_price,     // Changed from $original_price
       $affiliate_url,
       $image_url,
       $rating,
       $badge_text,     // Changed from $badge
       $featured_meta,
       'draft',         // status (temporary, will fix in Phase 2)
       [],             // category_ids (temporary)
       []              // tag_ids (temporary)
   );
   ```

4. **Test the fix**
   - Open WordPress admin
   - Edit an existing product
   - Verify all data displays correctly
   - Save product
   - Reload page and verify data persists

### Verification Checklist
- [ ] Backup created
- [ ] All meta keys updated with underscore prefix
- [ ] Field names corrected (price → regular_price, etc.)
- [ ] Product constructor updated with correct parameters
- [ ] Data displays correctly in admin
- [ ] Data persists after save
- [ ] Frontend displays products correctly

---

## Phase 2: Expand Product Model for Digital Products (CRITICAL)

**Priority:** 🔴 HIGH - Must complete for full functionality  
**Estimated Time:** 4 hours  
**Files to Modify:** `src/Models/Product.php`

### Issue
Product model has only 15 properties but MetaBoxes defines 25+ digital product fields.

### New Properties to Add (Digital Products Only)

**Product Identification:**
```php
private ?string $sku = null;              // Product SKU/software version
private ?int $brand = null;               // Software company/publisher
```

**Pricing (replace existing price/original_price):**
```php
private float $regular_price = 0;           // Original price before discount
private ?float $sale_price = null;        // Discounted price
private ?float $discount_percentage = null;   // Calculated discount
// Note: currency already exists
```

**Product Data:**
```php
private string $stock_status = 'instock';    // Availability: instock, outofstock, preorder
private ?string $availability_date = null;   // Launch date for pre-orders
// Note: rating already exists
private int $review_count = 0;             // Number of reviews
```

**Product Media:**
```php
private ?string $video_url = null;         // Demo/tutorial video URL (YouTube, Vimeo)
```

**Affiliate & Links:**
```php
// Note: affiliate_url already exists
private ?string $coupon_url = null;        // Special discount coupon link
```

**Product Ribbons:**
```php
// Note: featured already exists
private ?int $ribbon = null;              // Ribbon selection ID
private ?string $badge_text = null;        // Badge text for promotions
```

**Additional Information:**
```php
private ?string $warranty = null;           // Support period (e.g., "30-day money-back")
```

**Product Scheduling:**
```php
private ?string $release_date = null;        // Product launch date
private ?string $expiration_date = null;     // Offer expiration date
```

**Display Settings:**
```php
private int $display_order = 0;            // Display priority
private bool $hide_from_home = false;     // Hide from homepage
```

**Physical Product Fields (NOT NEEDED - Remove or Mark Optional):**
```php
// ⚠️ These fields are NOT APPLICABLE for digital products:
// - weight (no shipping)
// - length, width, height (no shipping)
```

### Implementation Steps

1. **Backup Product.php**
   ```bash
   cp src/Models/Product.php backups/Product.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Add new properties to Product class**
   - Open `src/Models/Product.php`
   - Add all new properties after existing ones
   - Use proper PHP types and default values

3. **Add getter methods for all new properties**
   ```php
   // Product Identification:
   public function get_sku(): ?string {
       return $this->sku;
   }
   
   public function get_brand(): ?int {
       return $this->brand;
   }
   
   // Pricing:
   public function get_regular_price(): float {
       return $this->regular_price;
   }
   
   public function get_sale_price(): ?float {
       return $this->sale_price;
   }
   
   public function get_discount_percentage(): ?float {
       return $this->discount_percentage;
   }
   
   // Product Data:
   public function get_stock_status(): string {
       return $this->stock_status;
   }
   
   public function get_availability_date(): ?string {
       return $this->availability_date;
   }
   
   public function get_review_count(): int {
       return $this->review_count;
   }
   
   // Product Media:
   public function get_video_url(): ?string {
       return $this->video_url;
   }
   
   // Affiliate & Links:
   public function get_coupon_url(): ?string {
       return $this->coupon_url;
   }
   
   // Product Ribbons:
   public function get_ribbon(): ?int {
       return $this->ribbon;
   }
   
   public function get_badge_text(): ?string {
       return $this->badge_text;
   }
   
   // Additional Information:
   public function get_warranty(): ?string {
       return $this->warranty;
   }
   
   // Product Scheduling:
   public function get_release_date(): ?string {
       return $this->release_date;
   }
   
   public function get_expiration_date(): ?string {
       return $this->expiration_date;
   }
   
   // Display Settings:
   public function get_display_order(): int {
       return $this->display_order;
   }
   
   public function get_hide_from_home(): bool {
       return $this->hide_from_home;
   }
   ```

4. **Add setter methods (optional but recommended)**
   ```php
   // Example:
   public function set_sku(?string $sku): self {
       $this->sku = $sku;
       return $this;
   }
   
   public function set_regular_price(float $price): self {
       $this->regular_price = $price;
       return $this;
   }
   
   public function set_sale_price(?float $price): self {
       $this->sale_price = $price;
       return $this;
   }
   
   // ... add setter for each new property
   ```

5. **Update constructor**
   ```php
   public function __construct(
       int $id,
       string $title,
       string $slug,
       string $description,
       string $currency,
       float $regular_price,
       ?float $sale_price = null,
       ?float $discount_percentage = null,
       string $affiliate_url = '',
       ?string $image_url = null,
       ?float $rating = null,
       ?string $badge_text = null,
       ?string $featured = '',
       ?string $sku = null,
       ?int $brand = null,
       string $stock_status = 'instock',
       ?string $availability_date = null,
       int $review_count = 0,
       ?string $video_url = null,
       ?string $coupon_url = null,
       ?int $ribbon = null,
       ?string $warranty = null,
       ?string $release_date = null,
       ?string $expiration_date = null,
       int $display_order = 0,
       bool $hide_from_home = false,
       string $status = 'publish',
       array $category_ids = [],
       array $tag_ids = []
   ) {
       // ... assign all properties
   }
   ```

6. **Add utility methods for digital products**
   ```php
   /**
    * Calculate if product is on sale
    */
   public function is_on_sale(): bool {
       return $this->sale_price !== null && $this->sale_price < $this->regular_price;
   }
   
   /**
    * Calculate discount percentage
    */
   public function get_calculated_discount(): ?float {
       if (!$this->is_on_sale()) {
           return null;
       }
       
       $discount = ($this->regular_price - $this->sale_price) / $this->regular_price * 100;
       return round($discount, 2);
   }
   
   /**
    * Get display price (sale price if available, otherwise regular price)
    */
   public function get_display_price(): float {
       return $this->is_on_sale() ? $this->sale_price : $this->regular_price;
   }
   
   /**
    * Get stock status label
    */
   public function get_stock_status_label(): string {
       return match($this->stock_status) {
           'instock' => 'In Stock',
           'outofstock' => 'Out of Stock',
           'preorder' => 'Pre-Order',
           default => ucfirst($this->stock_status),
       };
   }
   
   /**
    * Check if product is available (stock status and release date)
    */
   public function is_available(): bool {
       if ($this->stock_status === 'outofstock') {
           return false;
       }
       
       if ($this->release_date && strtotime($this->release_date) > time()) {
           return false;
       }
       
       if ($this->expiration_date && strtotime($this->expiration_date) < time()) {
           return false;
       }
       
       return true;
   }
   ```

### Verification Checklist
- [ ] Backup created
- [ ] All 18 new properties added
- [ ] All getter methods implemented
- [ ] All setter methods implemented
- [ ] Constructor updated with all parameters
- [ ] Utility methods added
- [ ] PHPStan analysis passes
- [ ] Psalm analysis passes
- [ ] All properties can be set and retrieved

---

## Phase 3: Update ProductFactory for Digital Products (HIGH)

**Priority:** 🟠 HIGH - Complete data layer  
**Estimated Time:** 3 hours  
**Files to Modify:** `src/Factories/ProductFactory.php`

### Issue
ProductFactory only reads 8 fields but MetaBoxes has 25+ digital product fields.

### Implementation Steps

1. **Backup ProductFactory.php** (if not done in Phase 1)

2. **Update from_post() method to read all digital product fields**
   ```php
   public static function from_post(WP_Post $post): Product {
       $meta = get_post_meta($post->ID);
       
       return new Product(
           $post->ID,
           $post->post_title,
           $post->post_name,
           $post->post_content,
           sanitize_text_field($meta['_aps_currency'][0] ?? 'USD'),
           (float) ($meta['_aps_regular_price'][0] ?? 0),
           isset($meta['_aps_sale_price'][0]) ? (float) $meta['_aps_sale_price'][0] : null,
           isset($meta['_aps_discount_percentage'][0]) ? (float) $meta['_aps_discount_percentage'][0] : null,
           esc_url_raw($meta['_aps_affiliate_url'][0] ?? ''),
           esc_url_raw($meta['_aps_image_url'][0] ?? '') ?: null,
           isset($meta['_aps_rating'][0]) ? (float) $meta['_aps_rating'][0] : null,
           sanitize_text_field($meta['_aps_badge_text'][0] ?? '') ?: null,
           sanitize_text_field($meta['_aps_featured'][0] ?? ''),
           sanitize_text_field($meta['_aps_sku'][0] ?? '') ?: null,
           isset($meta['_aps_brand'][0]) ? (int) $meta['_aps_brand'][0] : null,
           sanitize_text_field($meta['_aps_stock_status'][0] ?? 'instock'),
           sanitize_text_field($meta['_aps_availability_date'][0] ?? '') ?: null,
           isset($meta['_aps_review_count'][0]) ? (int) $meta['_aps_review_count'][0] : 0,
           esc_url_raw($meta['_aps_video_url'][0] ?? '') ?: null,
           esc_url_raw($meta['_aps_coupon_url'][0] ?? '') ?: null,
           isset($meta['_aps_ribbon'][0]) ? (int) $meta['_aps_ribbon'][0] : null,
           sanitize_textarea_field($meta['_aps_warranty'][0] ?? '') ?: null,
           sanitize_text_field($meta['_aps_release_date'][0] ?? '') ?: null,
           sanitize_text_field($meta['_aps_expiration_date'][0] ?? '') ?: null,
           isset($meta['_aps_display_order'][0]) ? (int) $meta['_aps_display_order'][0] : 0,
           isset($meta['_aps_hide_from_home'][0]) ? (bool) $meta['_aps_hide_from_home'][0] : false,
           $post->post_status,
           wp_get_post_categories($post->ID, ['fields' => 'ids']),
           wp_get_post_tags($post->ID, ['fields' => 'ids'])
       );
   }
   ```

3. **Update from_array() method**
   ```php
   public static function from_array(array $data): Product {
       // Map array keys to Product properties
       return new Product(
           $data['id'] ?? 0,
           $data['title'] ?? '',
           $data['slug'] ?? '',
           $data['description'] ?? '',
           $data['currency'] ?? 'USD',
           (float) ($data['regular_price'] ?? 0),
           isset($data['sale_price']) ? (float) $data['sale_price'] : null,
           isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : null,
           $data['affiliate_url'] ?? '',
           $data['image_url'] ?? null,
           isset($data['rating']) ? (float) $data['rating'] : null,
           $data['badge_text'] ?? null,
           $data['featured'] ?? '',
           $data['sku'] ?? null,
           isset($data['brand']) ? (int) $data['brand'] : null,
           $data['stock_status'] ?? 'instock',
           $data['availability_date'] ?? null,
           isset($data['review_count']) ? (int) $data['review_count'] : 0,
           $data['video_url'] ?? null,
           $data['coupon_url'] ?? null,
           isset($data['ribbon']) ? (int) $data['ribbon'] : null,
           $data['warranty'] ?? null,
           $data['release_date'] ?? null,
           $data['expiration_date'] ?? null,
           isset($data['display_order']) ? (int) $data['display_order'] : 0,
           isset($data['hide_from_home']) ? (bool) $data['hide_from_home'] : false,
           $data['status'] ?? 'publish',
           $data['category_ids'] ?? [],
           $data['tag_ids'] ?? []
       );
   }
   ```

### Verification Checklist
- [ ] All 25+ digital product fields mapped in from_post()
- [ ] from_array() updated with all fields
- [ ] Proper type casting for all fields
- [ ] Default values set for nullable fields
- [ ] PHPStan analysis passes
- [ ] Psalm analysis passes
- [ ] All fields can be retrieved correctly

---

## Phase 4: Add Image Upload to MetaBoxes (HIGH)

**Priority:** 🟠 HIGH - Core feature  
**Estimated Time:** 2 hours  
**Files to Modify:** `src/Admin/MetaBoxes.php`

### Issue
MetaBoxes does not include image upload field.

### Implementation Steps

1. **Add image upload field to MetaBoxes template**
   - Find render_meta_box() method
   - Add image upload field in appropriate section (likely after Product Information)

   ```php
   // Add in Group 4: Product Media
   echo '<div class="aps-form-group">';
   echo '<label for="aps_image_url">' . esc_html__('Product Image', 'affiliate-product-showcase') . '</label>';
   echo '<div class="aps-image-upload-wrapper">';
   
   // Display current image if exists
   $current_image = get_post_meta($post->ID, '_aps_image_url', true);
   if ($current_image) {
       echo '<div class="aps-current-image">';
       echo '<img src="' . esc_url($current_image) . '" alt="' . esc_attr($post->post_title) . '" style="max-width: 200px;" />';
       echo '</div>';
   }
   
   echo '<input type="hidden" name="aps_image_url" id="aps_image_url" value="' . esc_attr($current_image) . '" />';
   echo '<button type="button" class="button aps-upload-image-button">' . esc_html__('Upload Image', 'affiliate-product-showcase') . '</button>';
   echo '<button type="button" class="button aps-remove-image-button">' . esc_html__('Remove Image', 'affiliate-product-showcase') . '</button>';
   echo '</div>';
   echo '</div>';
   ```

2. **Add JavaScript for image upload**
   ```php
   // Add JavaScript to handle image upload
   add_action('admin_footer', function() {
       global $post;
       if (!$post || get_post_type($post) !== 'aps_product') {
           return;
       }
       
       ?>
       <script>
       jQuery(document).ready(function($) {
           $('.aps-upload-image-button').on('click', function(e) {
               e.preventDefault();
               
               var imageUploader = wp.media({
                   title: 'Select Product Image',
                   button: { text: 'Use This Image' },
                   multiple: false
               });
               
               imageUploader.on('select', function() {
                   var attachment = imageUploader.state().get('selection').first().toJSON();
                   $('#aps_image_url').val(attachment.url);
                   $('.aps-current-image img').attr('src', attachment.url);
               });
               
               imageUploader.open();
           });
           
           $('.aps-remove-image-button').on('click', function(e) {
               e.preventDefault();
               $('#aps_image_url').val('');
               $('.aps-current-image').remove();
           });
       });
       </script>
       <?php
   });
   ```

3. **Handle image upload in save_meta()**
   - Add image URL saving logic:
   ```php
   // In save_meta() method
   $image_url = esc_url_raw(wp_unslash($_POST['aps_image_url'] ?? ''));
   update_post_meta($post_id, '_aps_image_url', $image_url);
   ```

4. **Add nonce verification for image upload**
   ```php
   // Ensure nonce is verified before processing
   if (!isset($_POST['aps_meta_box_nonce']) || 
       !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aps_meta_box_nonce'])), 'aps_meta_box')) {
       return;
   }
   ```

### Verification Checklist
- [ ] Image upload field added to MetaBoxes
- [ ] JavaScript for image upload added
- [ ] Image URL saved correctly in save_meta()
- [ ] Image can be uploaded via admin
- [ ] Image displays correctly in admin
- [ ] Image can be removed
- [ ] Image displays in frontend template

---

## Phase 5: Update Templates for Digital Products (MEDIUM)

**Priority:** 🟡 MEDIUM - Display enhancements  
**Estimated Time:** 3 hours  
**Files to Modify:** 
- `src/Blocks/templates/product-grid-item.php`
- `src/Blocks/templates/product-showcase-item.php`

### Issue
Templates only display 7 basic fields, cannot show new digital product fields.

### Implementation Steps

1. **Update product-grid-item.php for digital products**
   ```php
   <?php
   /**
    * Product Grid Item Template (Digital Products)
    */
   use AffiliateProductShowcase\Helpers\FormatHelper;
   
   // Get product data
   $id          = $product->get_id();
   $title        = $product->get_title();
   $description  = $product->get_description();
   $image_url    = $product->get_image_url('medium');
   $affiliate_url= $product->get_affiliate_link();
   $regular_price= $product->get_regular_price();
   $sale_price   = $product->get_sale_price();
   $rating       = $product->get_rating();
   $badge_text   = $product->get_badge_text();
   $sku          = $product->get_sku();
   $brand        = $product->get_brand();
   $stock_status = $product->get_stock_status();
   $review_count = $product->get_review_count();
   $video_url    = $product->get_video_url();
   $coupon_url   = $product->get_coupon_url();
   $warranty     = $product->get_warranty();
   $release_date = $product->get_release_date();
   
   // Calculate display values
   $display_price = $sale_price ? $sale_price : $regular_price;
   $is_on_sale = $product->is_on_sale();
   $discount_percentage = $product->get_calculated_discount();
   $is_available = $product->is_available();
   ?>
   
   <article class="aps-grid-item" id="aps-product-<?php echo esc_attr($id); ?>">
       <?php if ($image_url): ?>
           <img src="<?php echo esc_url($image_url); ?>" 
                alt="<?php echo esc_attr($title); ?>" 
                class="aps-product-image"
                loading="lazy" />
       <?php endif; ?>
       
       <?php if ($badge_text): ?>
           <span class="aps-product-badge"><?php echo esc_html($badge_text); ?></span>
       <?php endif; ?>
       
       <div class="aps-product-content">
           <h3 class="aps-product-title">
               <a href="<?php echo esc_url($affiliate_url); ?>" target="_blank" rel="nofollow sponsored">
                   <?php echo esc_html($title); ?>
               </a>
           </h3>
           
           <?php if ($sku): ?>
               <div class="aps-product-sku">SKU: <?php echo esc_html($sku); ?></div>
           <?php endif; ?>
           
           <?php if ($brand): ?>
               <div class="aps-product-brand">
                   <?php esc_html_e('Brand:', 'affiliate-product-showcase'); ?>
                   <span><?php echo esc_html($brand); ?></span>
               </div>
           <?php endif; ?>
           
           <?php if ($rating): ?>
               <div class="aps-product-rating">
                   <?php echo FormatHelper::format_rating($rating); ?>
               </div>
           <?php endif; ?>
           
           <?php if ($review_count): ?>
               <div class="aps-product-reviews">
                   <?php echo esc_html($review_count); ?> <?php esc_html_e('reviews', 'affiliate-product-showcase'); ?>
               </div>
           <?php endif; ?>
           
           <?php if ($regular_price): ?>
               <div class="aps-product-price">
                   <span class="aps-current-price">
                       <?php echo FormatHelper::format_price($display_price); ?>
                   </span>
                   
                   <?php if ($is_on_sale): ?>
                       <span class="aps-original-price">
                           <?php echo FormatHelper::format_price($regular_price); ?>
                       </span>
                       <span class="aps-discount">
                           -<?php echo esc_html($discount_percentage); ?>%
                       </span>
                   <?php endif; ?>
               </div>
           <?php endif; ?>
           
           <?php if ($stock_status): ?>
               <div class="aps-stock-status <?php echo esc_attr($stock_status); ?>">
                   <?php echo esc_html($product->get_stock_status_label()); ?>
               </div>
           <?php endif; ?>
           
           <?php if ($video_url): ?>
               <div class="aps-product-video">
                   <a href="<?php echo esc_url($video_url); ?>" target="_blank" rel="nofollow sponsored" class="aps-video-link">
                       <span class="dashicons dashicons-video-alt3"></span>
                       <?php esc_html_e('Watch Demo', 'affiliate-product-showcase'); ?>
                   </a>
               </div>
           <?php endif; ?>
           
           <?php if ($coupon_url): ?>
               <div class="aps-product-coupon">
                   <a href="<?php echo esc_url($coupon_url); ?>" target="_blank" rel="nofollow sponsored" class="aps-coupon-link">
                       <span class="dashicons dashicons-ticket-alt"></span>
                       <?php esc_html_e('Get Coupon', 'affiliate-product-showcase'); ?>
                   </a>
               </div>
           <?php endif; ?>
           
           <?php if ($warranty): ?>
               <div class="aps-product-warranty">
                   <span class="dashicons dashicons-shield-alt"></span>
                   <?php echo esc_html($warranty); ?>
               </div>
           <?php endif; ?>
           
           <?php if ($release_date): ?>
               <div class="aps-product-release-date">
                   <span class="dashicons dashicons-calendar-alt"></span>
                   <?php esc_html_e('Available:', 'affiliate-product-showcase'); ?>
                   <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($release_date))); ?>
               </div>
           <?php endif; ?>
           
           <p class="aps-product-description">
               <?php echo wp_kses_post(wp_trim_words($description, 20, '...')); ?>
           </p>
           
           <a href="<?php echo esc_url($affiliate_url); ?>" 
              target="_blank" 
              rel="nofollow sponsored"
              class="aps-product-button">
               <?php esc_html_e('View Deal', 'affiliate-product-showcase'); ?>
           </a>
       </div>
   </article>
   ```

2. **Update product-showcase-item.php** (similar changes)

3. **Add CSS for digital product elements**
   ```css
   .aps-product-sku {
       font-size: 0.85em;
       color: #666;
       margin: 5px 0;
   }
   
   .aps-product-brand {
       font-size: 0.85em;
       color: #666;
       margin: 5px 0;
   }
   
   .aps-product-brand span {
       font-weight: 600;
       color: #333;
   }
   
   .aps-product-reviews {
       font-size: 0.85em;
       color: #666;
       margin: 5px 0;
   }
   
   .aps-stock-status {
       display: inline-block;
       padding: 2px 8px;
       border-radius: 3px;
       font-size: 0.85em;
       font-weight: 600;
       margin: 5px 0;
   }
   
   .aps-stock-status.instock {
       background: #e7f7e7;
       color: #1e7e1e;
   }
   
   .aps-stock-status.outofstock {
       background: #f7e7e7;
       color: #7e1e1e;
   }
   
   .aps-stock-status.preorder {
       background: #fff3cd;
       color: #0d47a1;
   }
   
   .aps-product-video {
       margin: 8px 0;
   }
   
   .aps-video-link {
       display: inline-flex;
       align-items: center;
       gap: 5px;
       color: #0073aa;
       text-decoration: none;
       font-weight: 500;
   }
   
   .aps-video-link:hover {
       color: #005177;
       text-decoration: underline;
   }
   
   .aps-product-coupon {
       margin: 8px 0;
   }
   
   .aps-coupon-link {
       display: inline-flex;
       align-items: center;
       gap: 5px;
       color: #d63639;
       text-decoration: none;
       font-weight: 500;
   }
   
   .aps-coupon-link:hover {
       color: #b03e2f;
       text-decoration: underline;
   }
   
   .aps-product-warranty {
       font-size: 0.85em;
       color: #666;
       margin: 8px 0;
       display: flex;
       align-items: center;
       gap: 5px;
   }
   
   .aps-product-release-date {
       font-size: 0.85em;
       color: #666;
       margin: 5px 0;
       display: flex;
       align-items: center;
       gap: 5px;
   }
   ```

### Verification Checklist
- [ ] SKU displays correctly
- [ ] Brand displays correctly
- [ ] Stock status displays correctly
- [ ] Review count displays correctly
- [ ] Demo video link displays
- [ ] Coupon link displays
- [ ] Warranty information displays
- [ ] Release date displays
- [ ] Sale price logic works correctly
- [ ] Discount percentage calculates correctly
- [ ] CSS styles applied correctly
- [ ] Responsive design tested

---

## Phase 6: Testing & Verification (REQUIRED)

**Priority:** 🟡 REQUIRED - Quality assurance  
**Estimated Time:** 4 hours  
**Files to Create:** Test files

### Unit Tests

**File:** `tests/Unit/Models/ProductTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Models\Product;

final class ProductTest extends TestCase {
    public function test_product_creation(): void {
        $product = new Product(
            1,
            'Test Software',
            'test-software',
            'Test description',
            'USD',
            99.99,
            79.99,
            20.0,
            'https://example.com/affiliate',
            'https://example.com/image.jpg',
            4.5,
            'Best Deal',
            '',
            'TEST-123',
            null,
            'instock',
            null,
            10,
            'https://youtube.com/demo',
            'https://example.com/coupon',
            null,
            '30-day money-back',
            null,
            null,
            0,
            false,
            'publish',
            [],
            []
        );
        
        $this->assertEquals(1, $product->get_id());
        $this->assertEquals('Test Software', $product->get_title());
        $this->assertEquals(99.99, $product->get_regular_price());
        $this->assertEquals(79.99, $product->get_sale_price());
        $this->assertEquals(20.0, $product->get_discount_percentage());
    }
    
    public function test_is_on_sale(): void {
        $on_sale_product = new Product(1, 'Test', 'test', 'desc', 'USD', 99.99, 79.99);
        $not_on_sale_product = new Product(2, 'Test', 'test', 'desc', 'USD', 99.99, null);
        
        $this->assertTrue($on_sale_product->is_on_sale());
        $this->assertFalse($not_on_sale_product->is_on_sale());
    }
    
    public function test_calculated_discount(): void {
        $product = new Product(1, 'Test', 'test', 'desc', 'USD', 100, 75);
        
        $this->assertEquals(25.0, $product->get_calculated_discount());
    }
    
    public function test_is_available(): void {
        $available_product = new Product(1, 'Test', 'test', 'desc', 'USD', 99, null, null, '', null, null, null, null, 'instock');
        $unavailable_product = new Product(2, 'Test', 'test', 'desc', 'USD', 99, null, null, '', null, null, null, 'outofstock');
        
        $this->assertTrue($available_product->is_available());
        $this->assertFalse($unavailable_product->is_available());
    }
}
```

### Integration Tests

**File:** `tests/Integration/Factories/ProductFactoryTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Factories;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Factories\ProductFactory;
use AffiliateProductShowcase\Models\Product;

final class ProductFactoryTest extends TestCase {
    public function test_from_post_retrieves_all_digital_product_fields(): void {
        // Create test post with meta
        $post_id = wp_insert_post([
            'post_title' => 'Test Software',
            'post_type' => 'aps_product',
        ]);
        
        update_post_meta($post_id, '_aps_regular_price', 99.99);
        update_post_meta($post_id, '_aps_sale_price', 79.99);
        update_post_meta($post_id, '_aps_discount_percentage', 20.0);
        update_post_meta($post_id, '_aps_sku', 'TEST-123');
        update_post_meta($post_id, '_aps_stock_status', 'instock');
        update_post_meta($post_id, '_aps_review_count', 10);
        update_post_meta($post_id, '_aps_video_url', 'https://youtube.com/demo');
        update_post_meta($post_id, '_aps_coupon_url', 'https://example.com/coupon');
        update_post_meta($post_id, '_aps_warranty', '30-day money-back');
        update_post_meta($post_id, '_aps_badge_text', 'Best Deal');
        
        $post = get_post($post_id);
        $product = ProductFactory::from_post($post);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(99.99, $product->get_regular_price());
        $this->assertEquals(79.99, $product->get_sale_price());
        $this->assertEquals(20.0, $product->get_discount_percentage());
        $this->assertEquals('TEST-123', $product->get_sku());
        $this->assertEquals('instock', $product->get_stock_status());
        $this->assertEquals(10, $product->get_review_count());
        $this->assertEquals('https://youtube.com/demo', $product->get_video_url());
        $this->assertEquals('https://example.com/coupon', $product->get_coupon_url());
        $this->assertEquals('30-day money-back', $product->get_warranty());
        $this->assertEquals('Best Deal', $product->get_badge_text());
        
        // Cleanup
        wp_delete_post($post_id, true);
    }
}
```

### Static Analysis

```bash
# Run PHPStan
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan

# Run Psalm
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm

# Run PHPCS
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs
```

### Manual Testing Checklist

**Admin Interface:**
- [ ] Create new digital product with all fields filled
- [ ] Verify all fields save correctly
- [ ] Upload product image
- [ ] Add demo video URL
- [ ] Add coupon URL
- [ ] Set warranty information
- [ ] Set release date
- [ ] Edit existing product
- [ ] Verify all fields display correctly
- [ ] Update fields and save
- [ ] Verify updates persist
- [ ] Delete product

**Frontend Display:**
- [ ] View product grid
- [ ] Verify all digital products display
- [ ] Verify product images display
- [ ] Verify prices display correctly
- [ ] Verify sale prices show correctly
- [ ] Verify discount percentages calculate correctly
- [ ] Verify SKU displays (if enabled)
- [ ] Verify brand displays (if enabled)
- [ ] Verify stock status displays
- [ ] Verify rating displays
- [ ] Verify review count displays
- [ ] Verify demo video link displays and works
- [ ] Verify coupon link displays and works
- [ ] Verify warranty information displays
- [ ] Verify release date displays
- [ ] Click affiliate link and verify opens correctly
- [ ] Test responsive design (mobile, tablet, desktop)

---

## Summary

### Phase Overview

| Phase | Priority | Time | Files Modified | Focus |
|--------|----------|------|---------------|--------|
| Phase 1 | 🔴 CRITICAL | 2 hrs | ProductFactory.php | Fix meta key prefix |
| Phase 2 | 🔴 HIGH | 4 hrs | Product.php | Add digital product properties |
| Phase 3 | 🟠 HIGH | 3 hrs | ProductFactory.php | Map all fields |
| Phase 4 | 🟠 HIGH | 2 hrs | MetaBoxes.php | Image upload |
| Phase 5 | 🟡 MEDIUM | 3 hrs | Templates | Digital product display |
| Phase 6 | 🟡 REQUIRED | 4 hrs | Test files | Testing & verification |

**Total Estimated Time:** 18 hours (2-3 days)

### Dependencies

- Phase 1 must be completed before Phase 2
- Phase 2 must be completed before Phase 3
- Phase 3 must be completed before Phase 5
- Phase 4 can be done in parallel with Phase 2-3
- Phase 6 depends on all previous phases

### Risk Mitigation

**Backup Strategy:**
- Create backups before each phase
- Keep backups for at least 1 week
- Test on staging environment first

**Rollback Plan:**
```bash
# If issues occur, restore from backup
cp backups/ProductFactory.php.backup-YYYYMMDD-HHMMSS src/Factories/ProductFactory.php
cp backups/Product.php.backup-YYYYMMDD-HHMMSS src/Models/Product.php
```

**Testing Strategy:**
- Run unit tests after each phase
- Run integration tests after each phase
- Manual testing after each phase
- Static analysis before committing

---

## Next Steps

1. **Start with Phase 1** (most critical)
2. Complete phases in order
3. Test thoroughly after each phase
4. Commit changes with proper messages
5. Deploy to staging for final verification
6. Deploy to production after all tests pass

**Note:** Do not proceed to Phase 2 until Phase 1 is complete and verified.

---

## Important Notes

### Digital Product Fields vs Physical Product Fields

**Digital Product Fields (25+ total):**
- Product identification: sku, brand
- Pricing: regular_price, sale_price, discount_percentage, currency
- Product data: stock_status, availability_date, rating, review_count
- Media: video_url, image_url
- Links: affiliate_url, coupon_url
- Promotions: featured, ribbon, badge_text
- Support: warranty
- Scheduling: release_date, expiration_date
- Display: display_order, hide_from_home

**Physical Product Fields (NOT NEEDED):**
- weight, length, width, height (shipping dimensions)
- These should be removed from MetaBoxes or marked as optional

### Recommendation

Consider removing physical product fields (weight, length, width, height) from MetaBoxes to simplify the interface for digital products only. If physical product support is needed in the future, these can be added back with a conditional display based on product type.
