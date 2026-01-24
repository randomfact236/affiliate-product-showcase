# Fix True Hybrid Issues - Implementation Plan

**Created:** January 23, 2026  
**Priority:** CRITICAL - Must fix immediately  
**Estimated Time:** 2-3 weeks

---

## Executive Summary

This plan addresses critical issues preventing the products page from following true hybrid approach. The main problems are:

1. **ProductFactory reads wrong meta keys** (missing underscore prefix)
2. **Product model missing 15+ properties** defined in MetaBoxes
3. **Field mapping inconsistencies** causing data corruption
4. **Missing image upload** functionality

**Impact:** All product data is saved but cannot be retrieved. Frontend shows empty/null values.

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
   // Update the Product constructor call to match new property names:
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

## Phase 2: Expand Product Model (CRITICAL)

**Priority:** 🔴 HIGH - Must complete for full functionality  
**Estimated Time:** 4 hours  
**Files to Modify:** `src/Models/Product.php`

### Issue
Product model has only 15 properties but MetaBoxes defines 30+ fields.

### New Properties to Add

```php
// Group 1: Product Information
private ?string $sku = null;
private ?int $brand = null;

// Group 2: Pricing (replace existing price/original_price)
private float $regular_price = 0;
private ?float $sale_price = null;
private ?float $discount_percentage = null;
// Note: currency already exists

// Group 3: Product Data
private string $stock_status = 'instock';
private ?string $availability_date = null;
// Note: rating already exists
private int $review_count = 0;

// Group 4: Product Media
private ?string $video_url = null;

// Group 5: Shipping & Dimensions
private ?float $weight = null;
private ?float $length = null;
private ?float $width = null;
private ?float $height = null;

// Group 6: Affiliate & Links
// Note: affiliate_url already exists
private ?string $coupon_url = null;

// Group 7: Product Ribbons
// Note: featured already exists
private ?int $ribbon = null;
private ?string $badge_text = null;

// Group 8: Additional Information
private ?string $warranty = null;

// Group 9: Product Scheduling
private ?string $release_date = null;
private ?string $expiration_date = null;

// Group 10: Display Settings
private int $display_order = 0;
private bool $hide_from_home = false;
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
   // Example:
   public function get_sku(): ?string {
       return $this->sku;
   }
   
   public function get_regular_price(): float {
       return $this->regular_price;
   }
   
   public function get_sale_price(): ?float {
       return $this->sale_price;
   }
   
   public function get_discount_percentage(): ?float {
       return $this->discount_percentage;
   }
   
   public function get_stock_status(): string {
       return $this->stock_status;
   }
   
   // ... add getter for each new property
   ```

4. **Add setter methods (optional but recommended)**
   ```php
   public function set_sku(?string $sku): self {
       $this->sku = $sku;
       return $this;
   }
   
   public function set_regular_price(float $price): self {
       $this->regular_price = $price;
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
       ?float $weight = null,
       ?float $length = null,
       ?float $width = null,
       ?float $height = null,
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

6. **Add utility methods**
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

## Phase 3: Update ProductFactory to Map All Fields (HIGH)

**Priority:** 🟠 HIGH - Complete data layer  
**Estimated Time:** 3 hours  
**Files to Modify:** `src/Factories/ProductFactory.php`

### Issue
ProductFactory only reads 8 fields but MetaBoxes has 30+ fields.

### Implementation Steps

1. **Backup ProductFactory.php** (if not done in Phase 1)

2. **Update from_post() method to read all MetaBoxes fields**
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
           isset($meta['_aps_weight'][0]) ? (float) $meta['_aps_weight'][0] : null,
           isset($meta['_aps_length'][0]) ? (float) $meta['_aps_length'][0] : null,
           isset($meta['_aps_width'][0]) ? (float) $meta['_aps_width'][0] : null,
           isset($meta['_aps_height'][0]) ? (float) $meta['_aps_height'][0] : null,
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
           isset($data['weight']) ? (float) $data['weight'] : null,
           isset($data['length']) ? (float) $data['length'] : null,
           isset($data['width']) ? (float) $data['width'] : null,
           isset($data['height']) ? (float) $data['height'] : null,
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
- [ ] All 30+ MetaBoxes fields mapped in from_post()
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
   - Find the render_meta_box() method
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

## Phase 5: Update Templates (MEDIUM)

**Priority:** 🟡 MEDIUM - Display enhancements  
**Estimated Time:** 3 hours  
**Files to Modify:** 
- `src/Blocks/templates/product-grid-item.php`
- `src/Blocks/templates/product-showcase-item.php`

### Issue
Templates only display 7 basic fields, cannot show new fields.

### Implementation Steps

1. **Update product-grid-item.php**
   ```php
   <?php
   /**
    * Product Grid Item Template
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
   
   // Calculate display values
   $display_price = $sale_price ? $sale_price : $regular_price;
   $is_on_sale = $product->is_on_sale();
   $discount_percentage = $product->get_calculated_discount();
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
           
           <?php if ($rating): ?>
               <div class="aps-product-rating">
                   <?php echo FormatHelper::format_rating($rating); ?>
               </div>
           <?php endif; ?>
           
           <?php if ($review_count): ?>
               <div class="aps-product-reviews">
                   <?php echo esc_html($review_count); ?> reviews
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
                   <?php echo esc_html(ucfirst($stock_status)); ?>
               </div>
           <?php endif; ?>
           
           <p class="aps-product-description">
               <?php echo wp_kses_post(wp_trim_words($description, 20, '...')); ?>
           </p>
           
           <a href="<?php echo esc_url($affiliate_url); ?>" 
              target="_blank" 
              rel="nofollow sponsored"
              class="aps-product-button">
               View Deal
           </a>
       </div>
   </article>
   ```

2. **Update product-showcase-item.php** (similar changes)

3. **Add CSS for new elements**
   ```css
   .aps-product-sku {
       font-size: 0.85em;
       color: #666;
       margin: 5px 0;
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
   ```

### Verification Checklist
- [ ] SKU displays correctly
- [ ] Brand displays correctly
- [ ] Stock status displays correctly
- [ ] Review count displays correctly
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
            'Test Product',
            'test-product',
            'Test description',
            'USD',
            29.99,
            19.99
        );
        
        $this->assertEquals(1, $product->get_id());
        $this->assertEquals('Test Product', $product->get_title());
        $this->assertEquals(29.99, $product->get_regular_price());
        $this->assertEquals(19.99, $product->get_sale_price());
    }
    
    public function test_is_on_sale(): void {
        $on_sale_product = new Product(1, 'Test', 'test', 'desc', 'USD', 29.99, 19.99);
        $not_on_sale_product = new Product(2, 'Test', 'test', 'desc', 'USD', 29.99, null);
        
        $this->assertTrue($on_sale_product->is_on_sale());
        $this->assertFalse($not_on_sale_product->is_on_sale());
    }
    
    public function test_calculated_discount(): void {
        $product = new Product(1, 'Test', 'test', 'desc', 'USD', 100, 75);
        
        $this->assertEquals(25.0, $product->get_calculated_discount());
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
    public function test_from_post_retrieves_all_fields(): void {
        // Create test post with meta
        $post_id = wp_insert_post([
            'post_title' => 'Test Product',
            'post_type' => 'aps_product',
        ]);
        
        update_post_meta($post_id, '_aps_regular_price', 29.99);
        update_post_meta($post_id, '_aps_sale_price', 19.99);
        update_post_meta($post_id, '_aps_sku', 'TEST-123');
        update_post_meta($post_id, '_aps_stock_status', 'instock');
        
        $post = get_post($post_id);
        $product = ProductFactory::from_post($post);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals(29.99, $product->get_regular_price());
        $this->assertEquals(19.99, $product->get_sale_price());
        $this->assertEquals('TEST-123', $product->get_sku());
        $this->assertEquals('instock', $product->get_stock_status());
        
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
- [ ] Create new product with all fields filled
- [ ] Verify all fields save correctly
- [ ] Upload product image
- [ ] Edit existing product
- [ ] Verify all fields display correctly
- [ ] Update fields and save
- [ ] Verify updates persist
- [ ] Delete product

**Frontend Display:**
- [ ] View product grid
- [ ] Verify all products display
- [ ] Verify product images display
- [ ] Verify prices display correctly
- [ ] Verify sale prices show correctly
- [ ] Verify discount percentages calculate correctly
- [ ] Verify SKU displays (if enabled)
- [ ] Verify brand displays (if enabled)
- [ ] Verify stock status displays
- [ ] Verify rating displays
- [ ] Verify review count displays
- [ ] Click affiliate link and verify opens correctly
- [ ] Test responsive design (mobile, tablet, desktop)

---

## Summary

### Phase Overview

| Phase | Priority | Time | Files Modified |
|--------|----------|------|---------------|
| Phase 1: Fix ProductFactory Prefix | 🔴 CRITICAL | 2 hours | ProductFactory.php |
| Phase 2: Expand Product Model | 🔴 HIGH | 4 hours | Product.php |
| Phase 3: Update ProductFactory Mapping | 🟠 HIGH | 3 hours | ProductFactory.php |
| Phase 4: Add Image Upload | 🟠 HIGH | 2 hours | MetaBoxes.php |
| Phase 5: Update Templates | 🟡 MEDIUM | 3 hours | Templates |
| Phase 6: Testing & Verification | 🟡 REQUIRED | 4 hours | Test files |

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
