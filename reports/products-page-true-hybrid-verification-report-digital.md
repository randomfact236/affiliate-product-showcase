# Products Page True Hybrid Verification Report - Digital Products Only

**Generated:** January 23, 2026  
**Plugin Type:** Affiliate Digital Product Showcase  
**Status:** ❌ NOT COMPLIANT - Critical Issues Found

---

## Executive Summary

The current products page implementation does **NOT** follow the true hybrid approach defined in the section1 implementation strategy. Critical misalignments exist between:

1. **Backend MetaBoxes** (25+ fields with `_aps_*` prefix in database)
2. **Product Model** (15 properties only, incomplete)
3. **ProductFactory** (reads from `aps_*` prefix, should read from `_aps_*`)

**Result:** Data is being saved but cannot be properly retrieved or used due to meta key naming mismatches and missing properties.

**Important:** This plugin is for **affiliate digital products only** (software, courses, ebooks, templates, subscriptions). Physical product fields (weight, dimensions, shipping) are not applicable.

---

## 1. Product Model Analysis

### Current State
**File:** `wp-content/plugins/affiliate-product-showcase/src/Models/Product.php`

**Properties (15 total):**
```php
- id
- title
- slug
- description
- currency
- price
- original_price
- affiliate_url
- image_url
- rating
- badge
- featured
- status
- category_ids
- tag_ids
```

### Digital Product Properties (Required for Affiliate Digital Products)

**Essential Digital Product Fields:**
```php
- ✅ id (exists)
- ✅ title (exists)
- ✅ slug (exists)
- ✅ description (exists)
- ✅ currency (exists)
- ✅ price (exists) - should be regular_price
- ✅ original_price (exists) - should be sale_price
- ✅ affiliate_url (exists)
- ✅ image_url (exists)
- ✅ rating (exists)
- ✅ badge (exists) - should be badge_text
- ✅ featured (exists)
- ✅ status (exists)
- ✅ category_ids (exists)
- ✅ tag_ids (exists)
```

**Missing Digital Product Properties:**
```php
- ❌ sku - Product identifier/SKU for software versions
- ❌ brand - Software company/publisher name
- ❌ regular_price - Original price (before discount)
- ❌ sale_price - Discounted price
- ❌ discount_percentage - Calculated discount
- ❌ stock_status - For licensing/seats availability (instock, outofstock, preorder)
- ❌ availability_date - Launch date for pre-orders
- ❌ review_count - Number of reviews
- ❌ video_url - Demo/tutorial video (YouTube, Vimeo)
- ❌ coupon_url - Special discount coupon link
- ❌ ribbon - Ribbon selection ID
- ❌ badge_text - Badge text for promotions
- ❌ warranty - Support period (e.g., "30-day money-back")
- ❌ release_date - Product launch date
- ❌ expiration_date - Offer expiration date
- ❌ display_order - Display priority
- ❌ hide_from_home - Hide from homepage
```

**Physical Product Fields (NOT NEEDED - Digital Products Only):**
```
- ⚠️ weight - NOT APPLICABLE (no shipping)
- ⚠️ length - NOT APPLICABLE (no shipping)
- ⚠️ width - NOT APPLICABLE (no shipping)
- ⚠️ height - NOT APPLICABLE (no shipping)
```

### Gap Analysis

| Digital Product Field | Product Model | Status |
|-------------------|----------------|--------|
| `sku` | ❌ Missing | NOT IMPLEMENTED |
| `brand` | ❌ Missing | NOT IMPLEMENTED |
| `regular_price` | ⚠️ Mapped to `price` | INCORRECT MAPPING |
| `sale_price` | ⚠️ Mapped to `original_price` | INCORRECT MAPPING |
| `discount_percentage` | ❌ Missing | NOT IMPLEMENTED |
| `stock_status` | ❌ Missing | NOT IMPLEMENTED |
| `availability_date` | ❌ Missing | NOT IMPLEMENTED |
| `review_count` | ❌ Missing | NOT IMPLEMENTED |
| `video_url` | ❌ Missing | NOT IMPLEMENTED |
| `coupon_url` | ❌ Missing | NOT IMPLEMENTED |
| `ribbon` | ❌ Missing | NOT IMPLEMENTED |
| `badge_text` | ⚠️ Mapped to `badge` | INCORRECT MAPPING |
| `warranty` | ❌ Missing | NOT IMPLEMENTED |
| `release_date` | ❌ Missing | NOT IMPLEMENTED |
| `expiration_date` | ❌ Missing | NOT IMPLEMENTED |
| `display_order` | ❌ Missing | NOT IMPLEMENTED |
| `hide_from_home` | ❌ Missing | NOT IMPLEMENTED |

---

## 2. ProductFactory Analysis

### Current State
**File:** `wp-content/plugins/affiliate-product-showcase/src/Factories/ProductFactory.php`

### Critical Issue: Meta Key Prefix Mismatch

**ProductFactory reads from `aps_*` prefix:**
```php
// Line 61-75 in ProductFactory.php
$featured_meta = $meta['aps_featured'][0] ?? '';           // ❌ WRONG
$currency      = $meta['aps_currency'][0] ?? 'USD',        // ❌ WRONG
$price         = (float) ($meta['aps_price'][0] ?? 0 ),    // ❌ WRONG
$original_price = isset($meta['aps_original_price'][0])      // ❌ WRONG
    ? (float) $meta['aps_original_price'][0] : null,
$affiliate_url = esc_url_raw($meta['aps_affiliate_url'][0] ?? '' ), // ❌ WRONG
$image_url     = esc_url_raw($meta['aps_image_url'][0] ?? '' ) ?: null, // ❌ WRONG
$rating        = isset($meta['aps_rating'][0])                // ❌ WRONG
    ? (float) $meta['aps_rating'][0] : null,
$badge         = sanitize_text_field($meta['aps_badge'][0] ?? '' ) ?: null, // ❌ WRONG
```

**MetaBoxes saves with `_aps_*` prefix:**
```php
// Lines 147-171 in MetaBoxes.php
update_post_meta( $post_id, '_aps_sku', $sku );
update_post_meta( $post_id, '_aps_brand', $brand );
update_post_meta( $post_id, '_aps_regular_price', $regular_price );
update_post_meta( $post_id, '_aps_sale_price', $sale_price );
update_post_meta( $post_id, '_aps_discount_percentage', $discount_percentage );
update_post_meta( $post_id, '_aps_currency', $currency );
update_post_meta( $post_id, '_aps_stock_status', $stock_status );
update_post_meta( $post_id, '_aps_availability_date', $availability_date );
update_post_meta( $post_id, '_aps_rating', $rating );
update_post_meta( $post_id, '_aps_review_count', $review_count );
update_post_meta( $post_id, '_aps_video_url', $video_url );
update_post_meta( $post_id, '_aps_coupon_url', $coupon_url );
update_post_meta( $post_id, '_aps_featured', $featured );
update_post_meta( $post_id, '_aps_ribbon', $ribbon );
update_post_meta( $post_id, '_aps_badge_text', $badge_text );
update_post_meta( $post_id, '_aps_warranty', $warranty );
update_post_meta( $post_id, '_aps_release_date', $release_date );
update_post_meta( $post_id, '_aps_expiration_date', $expiration_date );
update_post_meta( $post_id, '_aps_display_order', $display_order );
update_post_meta( $post_id, '_aps_hide_from_home', $hide_from_home );
```

**Result:** Data saved by MetaBoxes with `_aps_*` prefix cannot be retrieved by ProductFactory because it reads from `aps_*` prefix (missing underscore).

### Field Mapping Issues

| Field Name in MetaBoxes | Meta Key Saved | ProductFactory Reads | Status |
|------------------------|----------------|----------------------|--------|
| Regular Price | `_aps_regular_price` | `aps_price` | ❌ WRONG KEY + MISSING UNDERSCORE |
| Sale Price | `_aps_sale_price` | `aps_original_price` | ❌ WRONG KEY + MISSING UNDERSCORE |
| Featured | `_aps_featured` | `aps_featured` | ❌ MISSING UNDERSCORE |
| Rating | `_aps_rating` | `aps_rating` | ❌ MISSING UNDERSCORE |
| Badge Text | `_aps_badge_text` | `aps_badge` | ❌ WRONG KEY + MISSING UNDERSCORE |
| Currency | `_aps_currency` | `aps_currency` | ❌ MISSING UNDERSCORE |
| Affiliate URL | `_aps_affiliate_url` | `aps_affiliate_url` | ❌ MISSING UNDERSCORE |
| Image URL | `_aps_image_url` (if saved) | `aps_image_url` | ❌ MISSING UNDERSCORE |

---

## 3. MetaBoxes Analysis

### Current State
**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/MetaBoxes.php`

### Digital Product Field Groups (10 Groups, 25+ Fields)

**Group 1: Product Information**
- `sku` → `_aps_sku` - Product SKU/software version
- `brand` → `_aps_brand` - Software company/publisher

**Group 2: Pricing**
- `regular_price` → `_aps_regular_price` - Original price
- `sale_price` → `_aps_sale_price` - Discounted price
- `discount_percentage` → `_aps_discount_percentage` - Calculated discount
- `currency` → `_aps_currency` - Currency (USD, EUR, GBP, etc.)

**Group 3: Product Data**
- `stock_status` → `_aps_stock_status` - Availability (instock, outofstock, preorder)
- `availability_date` → `_aps_availability_date` - Launch date for pre-orders
- `rating` → `_aps_rating` - Product rating (0-5 stars)
- `review_count` → `_aps_review_count` - Number of reviews

**Group 4: Product Media**
- `video_url` → `_aps_video_url` - Demo/tutorial video URL

**Group 5: Affiliate & Links**
- `affiliate_url` → `_aps_affiliate_url` - Primary affiliate link
- `coupon_url` → `_aps_coupon_url` - Special discount coupon link

**Group 6: Product Ribbons**
- `featured` → `_aps_featured` - Featured product flag
- `ribbon` → `_aps_ribbon` - Ribbon selection ID
- `badge_text` → `_aps_badge_text` - Badge text for promotions

**Group 7: Additional Information**
- `warranty` → `_aps_warranty` - Support period (e.g., "30-day money-back")

**Group 8: Product Scheduling**
- `release_date` → `_aps_release_date` - Product launch date
- `expiration_date` → `_aps_expiration_date` - Offer expiration date

**Group 9: Display Settings**
- `display_order` → `_aps_display_order` - Display priority
- `hide_from_home` → `_aps_hide_from_home` - Hide from homepage

### Physical Product Fields (NOT NEEDED)

**Group 5: Shipping & Dimensions** - NOT APPLICABLE for Digital Products
- `weight` → `_aps_weight` - Physical shipping weight
- `length` → `_aps_length` - Physical dimensions
- `width` → `_aps_width` - Physical dimensions
- `height` → `_aps_height` - Physical dimensions

**Recommendation:** These fields should be removed or marked as optional for physical products only.

### MetaBoxes Data Flow (CORRECT)

```php
// 1. Read from database with '_aps_*' prefix (lines 39-81)
$sku = get_post_meta($post->ID, '_aps_sku', true);
$brand = get_post_meta($post->ID, '_aps_brand', true);
$regular_price = get_post_meta($post->ID, '_aps_regular_price', true);
// ... etc

// 2. Read from form with 'aps_*' prefix (lines 102-144)
$sku = sanitize_text_field(wp_unslash($_POST['aps_sku'] ?? ''));
$regular_price = isset($_POST['aps_regular_price']) 
    ? (float) wp_unslash($_POST['aps_regular_price']) : 0;
// ... etc

// 3. Save to database with '_aps_*' prefix (lines 147-171)
update_post_meta($post_id, '_aps_sku', $sku);
update_post_meta($post_id, '_aps_regular_price', $regular_price);
// ... etc
```

**MetaBoxes is CORRECT** - follows proper WordPress meta conventions:
- Form fields use `aps_*` prefix (public naming)
- Database storage uses `_aps_*` prefix (hidden meta)

**Issues:**
- ❌ ProductFactory reads from `aps_*` prefix instead of `_aps_*`
- ❌ Image URL is NOT saved in MetaBoxes at all
- ❌ No image upload field defined in MetaBoxes

---

## 4. Template Analysis

### Current State
**File:** `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-grid-item.php`

**Displayed Fields:**
```php
- id
- title
- description (truncated)
- image_url (calls $product->get_image_url('medium'))
- affiliate_link (calls $product->get_affiliate_link())
- price (calls $product->get_price())
- original_price (calls $product->get_original_price())
- rating (calls $product->get_rating())
- badge (calls $product->get_badge())
```

### Digital Product Display Fields Needed

**Essential for Digital Products:**
```php
- ✅ id (displayed)
- ✅ title (displayed)
- ✅ description (displayed, truncated)
- ✅ image_url (displayed)
- ✅ affiliate_link (displayed)
- ✅ price (displayed) - should use regular_price/sale_price logic
- ✅ original_price (displayed) - should use sale_price
- ✅ rating (displayed)
- ✅ badge (displayed) - should use badge_text
```

**Missing Display Fields:**
```php
- ❌ sku - Display software version/SKU
- ❌ brand - Display software company/publisher
- ❌ stock_status - Show availability (for licensing/seats)
- ❌ review_count - Show number of reviews
- ❌ video_url - Display demo/tutorial video
- ❌ coupon_url - Display special discount link
- ❌ warranty - Display support period
- ❌ discount_percentage - Show calculated discount
- ❌ release_date - Show launch date
- ❌ expiration_date - Show offer expiration
```

**Issues:**
- ⚠️ Template depends on Product model methods that return null/empty due to prefix mismatch
- ⚠️ Template cannot display new digital product fields
- ⚠️ Discount percentage is calculated in template, not retrieved from meta
- ⚠️ No demo video display functionality

---

## 5. True Hybrid Compliance Score

### Compliance Matrix

| Component | True Hybrid Standard | Current State | Score |
|-----------|---------------------|---------------|-------|
| **Product Model** | 25+ properties for digital products | 15 properties only | 5/10 |
| **Meta Key Prefix** | Consistent across all layers (`_aps_*` for database) | MetaBoxes: ✓ `_aps_*`, ProductFactory: ✗ `aps_*` | 2/10 |
| **Field Mapping** | Direct 1:1 mapping with correct keys | Multiple wrong keys + prefix mismatch | 3/10 |
| **Data Retrieval** | Factory reads same keys as MetaBoxes save | Wrong prefix, returns null/empty | 0/10 |
| **Template Display** | Display all digital product fields | Only 7 basic fields | 4/10 |
| **Type Safety** | All fields typed with proper types | Some fields missing types | 6/10 |
| **N+1 Prevention** | Meta cache for batch operations | ✅ Implemented | 10/10 |

### Overall Score: **4.3/10 (Poor)**

**Verdict:** ❌ **NOT COMPLIANT** with true hybrid approach

---

## 6. Critical Issues Summary

### Issue 1: ProductFactory Meta Key Prefix Mismatch (CRITICAL)
**Severity:** 🔴 CRITICAL - Data cannot be retrieved

**Problem:**
- MetaBoxes saves to database with `_aps_*` prefix (CORRECT)
- ProductFactory reads from `aps_*` prefix (WRONG - missing underscore)
- Result: All product data returns null/empty when retrieved

**Example:**
```php
// MetaBoxes saves:
update_post_meta($post_id, '_aps_regular_price', 29.99);

// ProductFactory reads:
$price = (float) ($meta['aps_price'][0] ?? 0);
// Returns: 0 (not found in database)
```

**Impact:**
- All product data is lost when retrieved
- Templates show empty/null values
- Admin forms save data but it's inaccessible
- Feature completely broken

**Fix Required:**
Update ProductFactory.from_post() to read from `_aps_*` prefix:
```php
// Change from:
$price = (float) ($meta['aps_price'][0] ?? 0);

// To:
$price = (float) ($meta['_aps_regular_price'][0] ?? 0);
```

### Issue 2: Missing Digital Product Properties (CRITICAL)
**Severity:** 🔴 CRITICAL - Data model incomplete

**Problem:**
- Product model has 15 properties, MetaBoxes has 25+ digital product fields
- 10+ digital product fields are saved but cannot be used

**Missing Digital Product Properties:**
- sku, brand (product identification)
- regular_price, sale_price, discount_percentage (pricing)
- stock_status, availability_date, review_count (product data)
- video_url, coupon_url (media & links)
- ribbon, badge_text (promotions)
- warranty, release_date, expiration_date (support & scheduling)
- display_order, hide_from_home (display settings)

**Impact:**
- Advanced digital product features cannot be used
- Frontend cannot display full product information
- Business logic cannot access saved data
- Demo videos, coupons, warranties cannot be displayed

### Issue 3: Field Mapping Inconsistencies (MAJOR)
**Severity:** 🟠 MAJOR - Incorrect data mapping

**Problem:**
- `regular_price`/`sale_price` structure not reflected in model
- Model uses `price`/`original_price` instead
- `badge_text` vs `badge` naming conflict
- Wrong field names in ProductFactory

**Impact:**
- Business logic unclear
- Developer confusion
- Potential data corruption
- Discount calculation logic incorrect

### Issue 4: Missing Image Upload (MAJOR)
**Severity:** 🟠 MAJOR - Core feature broken

**Problem:**
- MetaBoxes does NOT include image upload field
- ProductFactory expects `aps_image_url` meta key
- No way to set product image via admin

**Impact:**
- Products cannot have images
- Frontend shows broken image links
- Core feature non-functional

---

## 7. Implementation Strategy

### Phase 1: Fix ProductFactory Meta Key Prefix (CRITICAL)
**Priority:** 🔴 HIGHEST - Must fix immediately

**Steps:**
1. Update ProductFactory.from_post() to read from `_aps_*` prefix:
   ```php
   // Line 61-75 in ProductFactory.php
   // Change ALL meta keys to include underscore:
   
   // WRONG (current):
   $featured_meta = $meta['aps_featured'][0] ?? '';
   $currency = $meta['aps_currency'][0] ?? 'USD';
   $price = (float) ($meta['aps_price'][0] ?? 0);
   
   // CORRECT:
   $featured_meta = $meta['_aps_featured'][0] ?? '';
   $currency = $meta['_aps_currency'][0] ?? 'USD';
   $regular_price = (float) ($meta['_aps_regular_price'][0] ?? 0);
   $sale_price = isset($meta['_aps_sale_price'][0]) 
       ? (float) $meta['_aps_sale_price'][0] : null;
   ```

2. Update all meta keys in ProductFactory:
   - `aps_featured` → `_aps_featured`
   - `aps_currency` → `_aps_currency`
   - `aps_price` → `_aps_regular_price`
   - `aps_original_price` → `_aps_sale_price`
   - `aps_affiliate_url` → `_aps_affiliate_url`
   - `aps_image_url` → `_aps_image_url` (if exists)
   - `aps_rating` → `_aps_rating`
   - `aps_badge` → `_aps_badge_text`

3. **TEST:** Verify data can be saved and retrieved

### Phase 2: Expand Product Model for Digital Products (CRITICAL)
**Priority:** 🔴 HIGH - Must complete for full functionality

**Steps:**
1. Add missing digital product properties to Product model:
   ```php
   // Add to Product class:
   private ?string $sku = null;
   private ?int $brand = null;
   private float $regular_price = 0;
   private ?float $sale_price = null;
   private ?float $discount_percentage = null;
   private string $stock_status = 'instock';
   private ?string $availability_date = null;
   private int $review_count = 0;
   private ?string $video_url = null;
   private ?string $coupon_url = null;
   private ?int $ribbon = null;
   private ?string $badge_text = null;
   private ?string $warranty = null;
   private ?string $release_date = null;
   private ?string $expiration_date = null;
   private int $display_order = 0;
   private bool $hide_from_home = false;
   ```

2. Add getter methods for all new properties
3. Update constructor to accept all properties
4. **TEST:** Verify all properties can be set and retrieved

### Phase 3: Update ProductFactory for Digital Products (HIGH)
**Priority:** 🟠 HIGH - Complete data layer

**Steps:**
1. Update from_post() to map all digital product fields:
   ```php
   return new Product(
       $post->ID,
       $post->post_title,
       // ... existing fields ...
       $meta['_aps_sku'][0] ?? null,
       $meta['_aps_brand'][0] ?? null,
       (float) ($meta['_aps_regular_price'][0] ?? 0),
       isset($meta['_aps_sale_price'][0]) ? (float) $meta['_aps_sale_price'][0] : null,
       // ... all digital product fields ...
   );
   ```

2. Update from_array() to support all fields
3. **TEST:** Verify all data flows correctly

### Phase 4: Add Image Upload to MetaBoxes (HIGH)
**Priority:** 🟠 HIGH - Core feature

**Steps:**
1. Add image upload field to MetaBoxes template
2. Handle image upload in save_meta()
3. Store image URL as `_aps_image_url`
4. **TEST:** Verify images can be uploaded and displayed

### Phase 5: Update Templates for Digital Products (MEDIUM)
**Priority:** 🟡 MEDIUM - Display enhancements

**Steps:**
1. Update product-grid-item.php to display digital product fields:
   - SKU/software version
   - Brand/publisher
   - Stock status (for licensing/seats)
   - Review count
   - Demo video player
   - Coupon link
   - Warranty information
   - Release/expiration dates

2. Add video player for demo/tutorial videos
3. Add conditional display logic for optional fields
4. **TEST:** Verify frontend displays correctly

### Phase 6: Testing & Verification (REQUIRED)
**Priority:** 🟡 REQUIRED - Quality assurance

**Steps:**
1. Write unit tests for Product model
2. Write integration tests for ProductFactory
3. Write E2E tests for MetaBoxes
4. Run static analysis (PHPStan, Psalm, PHPCS)
5. Manual testing of all features
6. **VERIFY:** All tests passing, no regressions

---

## 8. Recommendations

### Immediate Actions (Today)
1. ⚠️ **STOP** - Do not add new features until data layer is fixed
2. Fix ProductFactory meta key prefix mismatch (Phase 1)
3. Verify data can be saved and retrieved
4. Test all existing product displays

### Short Term (This Week)
1. Complete Product model expansion for digital products (Phase 2)
2. Update ProductFactory mapping (Phase 3)
3. Add image upload functionality (Phase 4)

### Medium Term (Next 2 Weeks)
1. Update all templates to display digital product fields
2. Write comprehensive tests
3. Run static analysis and fix issues
4. Manual testing of all features

### Long Term (Next Month)
1. Document data model and field mappings
2. Create migration guide for existing data
3. Optimize performance with object caching
4. Implement batch operations for bulk edits

---

## 9. Conclusion

The current products page implementation does **NOT** follow true hybrid approach due to:

1. **Critical data layer issue** - ProductFactory reads wrong meta keys (missing underscore)
2. **Incomplete data model** - Missing 10+ digital product properties
3. **Inconsistent field mapping** - Wrong field names in ProductFactory
4. **Missing core features** - Image upload, demo video display

**Root Cause:** ProductFactory was not updated when MetaBoxes was expanded with digital product fields and proper WordPress meta conventions.

**Quality Score:** 4.3/10 (Poor)  
**Production Ready:** ❌ NO - Critical issues must be fixed  
**Estimated Fix Time:** 2-3 weeks for full compliance

**Note:** Physical product fields (weight, dimensions) should be removed or marked as optional for future physical product support.

---

## Appendix: Digital Product Meta Key Mapping

### MetaBoxes → ProductFactory Mapping (Current vs Correct)

| MetaBoxes Field | Meta Key Saved | ProductFactory Reads (WRONG) | Should Read (CORRECT) | Status |
|-----------------|----------------|----------------------|----------------|--------|
| **Group 1: Product Information** | | | | |
| SKU | `_aps_sku` | - | `_aps_sku` | ❌ MISSING |
| Brand | `_aps_brand` | - | `_aps_brand` | ❌ MISSING |
| **Group 2: Pricing** | | | | |
| Regular Price | `_aps_regular_price` | `aps_price` | `_aps_regular_price` | ❌ WRONG KEY |
| Sale Price | `_aps_sale_price` | `aps_original_price` | `_aps_sale_price` | ❌ WRONG KEY |
| Discount Percentage | `_aps_discount_percentage` | - | `_aps_discount_percentage` | ❌ MISSING |
| Currency | `_aps_currency` | `aps_currency` | `_aps_currency` | ❌ MISSING _ |
| **Group 3: Product Data** | | | | |
| Stock Status | `_aps_stock_status` | - | `_aps_stock_status` | ❌ MISSING |
| Availability Date | `_aps_availability_date` | - | `_aps_availability_date` | ❌ MISSING |
| Rating | `_aps_rating` | `aps_rating` | `_aps_rating` | ❌ MISSING _ |
| Review Count | `_aps_review_count` | - | `_aps_review_count` | ❌ MISSING |
| **Group 4: Product Media** | | | | |
| Video URL | `_aps_video_url` | - | `_aps_video_url` | ❌ MISSING |
| Image URL | - | `aps_image_url` | `_aps_image_url` | ❌ NOT SAVED |
| **Group 5: Affiliate & Links** | | | | |
| Affiliate URL | `_aps_affiliate_url` | `aps_affiliate_url` | `_aps_affiliate_url` | ❌ MISSING _ |
| Coupon URL | `_aps_coupon_url` | - | `_aps_coupon_url` | ❌ MISSING |
| **Group 6: Product Ribbons** | | | | |
| Featured | `_aps_featured` | `aps_featured` | `_aps_featured` | ❌ MISSING _ |
| Ribbon | `_aps_ribbon` | - | `_aps_ribbon` | ❌ MISSING |
| Badge Text | `_aps_badge_text` | `aps_badge` | `_aps_badge_text` | ❌ WRONG KEY |
| **Group 7: Additional Information** | | | | |
| Warranty | `_aps_warranty` | - | `_aps_warranty` | ❌ MISSING |
| **Group 8: Product Scheduling** | | | | |
| Release Date | `_aps_release_date` | - | `_aps_release_date` | ❌ MISSING |
| Expiration Date | `_aps_expiration_date` | - | `_aps_expiration_date` | ❌ MISSING |
| **Group 9: Display Settings** | | | | |
| Display Order | `_aps_display_order` | - | `_aps_display_order` | ❌ MISSING |
| Hide From Home | `_aps_hide_from_home` | - | `_aps_hide_from_home` | ❌ MISSING |

### Physical Product Fields (NOT NEEDED - Digital Products Only)

| MetaBoxes Field | Meta Key Saved | Status |
|-----------------|----------------|--------|
| Weight | `_aps_weight` | ⚠️ NOT APPLICABLE |
| Length | `_aps_length` | ⚠️ NOT APPLICABLE |
| Width | `_aps_width` | ⚠️ NOT APPLICABLE |
| Height | `_aps_height` | ⚠️ NOT APPLICABLE |

### Legend
- **❌ MISSING** - Field not read by ProductFactory at all
- **❌ WRONG KEY** - ProductFactory reads wrong field name
- **❌ MISSING _** - ProductFactory missing underscore prefix
- **❌ NOT SAVED** - Field not saved by MetaBoxes
- **⚠️ NOT APPLICABLE** - Physical product field, not needed for digital products

---

**Report Generated By:** Automated Verification System  
**Report Version:** 1.2.0 (Digital Products Only)  
**Last Updated:** January 23, 2026
