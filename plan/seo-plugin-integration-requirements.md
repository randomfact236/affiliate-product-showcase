# SEO Plugin Integration Requirements

> **Created:** 2026-01-21  
> **Updated:** 2026-01-21  
> **Status:** Ready for Implementation  
> **Priority:** HIGH - All features must be SEO-friendly

---

## 📋 Overview

**Goal:** Make Affiliate Product Showcase plugin fully SEO-friendly and seamlessly integrated with popular SEO plugins (Yoast SEO, Rank Math, All in One SEO, etc.)

**Strategy:** 
1. Use standard WordPress post type features (title, excerpt, content, featured image)
2. Provide integration hooks for SEO plugins to override/add SEO data
3. Generate proper schema.org structured data automatically
4. Support Open Graph and Twitter Card meta tags
5. Ensure all plugin features (products, categories, tags, ribbons) are SEO-friendly

---

## 🎯 SEO Plugin Integration Requirements

### 1. Custom Post Type Registration

**Must Support:**
- ✅ Standard WordPress fields (title, content, excerpt, featured image)
- ✅ Custom taxonomies (categories, tags)
- ✅ Public post type (accessible to SEO plugins)
- ✅ Supports: `title`, `editor`, `thumbnail`, `excerpt`, `author`
- ✅ Has archive (for category/tag archive pages)
- ✅ Publicly queryable (for SEO plugins to analyze)

**Implementation Location:** 
- File: `wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php`
- Method: `register_post_type()`

---

### 2. WordPress Standard SEO Fields

**Leverage Built-in WordPress Features:**

**For Products:**
- Title → `<title>` tag and SEO title
- Content → Schema.org description and SEO content
- Excerpt → Meta description
- Featured Image → Open Graph image
- Categories/Taxonomies → Schema.org category
- Custom Post Type → Schema.org Product type

**For Categories:**
- Name → Schema.org category name
- Description → Meta description
- Custom fields → Additional schema data

**For Tags:**
- Name → Schema.org keywords/tags
- Description → Tag meta data

**For Ribbons:**
- Name → Schema.org offer/offerCategory
- Description → Schema.org offer details

---

### 3. Integration Hooks for SEO Plugins

**Required Hooks:**

#### Product-Level Hooks
```php
// Allow SEO plugins to override meta title
apply_filters('aps_product_meta_title', $title, $product_id);

// Allow SEO plugins to override meta description
apply_filters('aps_product_meta_description', $description, $product_id);

// Allow SEO plugins to override canonical URL
apply_filters('aps_product_canonical_url', $url, $product_id);

// Allow SEO plugins to modify Open Graph data
apply_filters('aps_product_og_title', $og_title, $product_id);
apply_filters('aps_product_og_description', $og_description, $product_id);
apply_filters('aps_product_og_image', $og_image_url, $product_id);

// Allow SEO plugins to modify Twitter Card data
apply_filters('aps_product_twitter_title', $twitter_title, $product_id);
apply_filters('aps_product_twitter_description', $twitter_description, $product_id);
apply_filters('aps_product_twitter_image', $twitter_image_url, $product_id);

// Allow SEO plugins to modify noindex/nofollow
apply_filters('aps_product_noindex', $noindex, $product_id);
apply_filters('aps_product_nofollow', $nofollow, $product_id);
```

#### Category-Level Hooks
```php
apply_filters('aps_category_meta_title', $title, $category_id);
apply_filters('aps_category_meta_description', $description, $category_id);
apply_filters('aps_category_canonical_url', $url, $category_id);
```

#### Tag-Level Hooks
```php
apply_filters('aps_tag_meta_title', $title, $tag_id);
apply_filters('aps_tag_meta_description', $description, $tag_id);
```

---

### 4. Schema.org Structured Data

**Automatic Schema Generation:**

#### Product Schema
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Product Title",
  "description": "Product description",
  "image": "https://example.com/product-image.jpg",
  "url": "https://example.com/product-url",
  "offers": {
    "@type": "Offer",
    "price": "29.99",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock",
    "url": "https://affiliate-link.com",
    "priceValidUntil": "2026-12-31"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.5",
    "reviewCount": "6536"
  },
  "brand": {
    "@type": "Brand",
    "name": "Brand Name"
  },
  "category": "Product Category"
}
```

#### Collection Schema (for Product Lists)
```json
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "numberOfItems": 20,
  "itemListElement": [...]
}
```

#### BreadcrumbList Schema
```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Category",
      "item": "https://example.com/category"
    }
  ]
}
```

---

### 5. Open Graph Meta Tags

**Automatic Open Graph Generation:**

```html
<!-- Open Graph -->
<meta property="og:title" content="Product Title">
<meta property="og:description" content="Product description">
<meta property="og:image" content="https://example.com/product-image.jpg">
<meta property="og:url" content="https://example.com/product-url">
<meta property="og:type" content="product">
<meta property="og:price:amount" content="29.99">
<meta property="og:price:currency" content="USD">
<meta property="og:availability" content="in stock">
<meta property="og:brand" content="Brand Name">
```

---

### 6. Twitter Card Meta Tags

**Automatic Twitter Card Generation:**

```html
<!-- Twitter Card -->
<meta name="twitter:card" content="product">
<meta name="twitter:title" content="Product Title">
<meta name="twitter:description" content="Product description">
<meta name="twitter:image" content="https://example.com/product-image.jpg">
<meta name="twitter:label1" content="Price">
<meta name="twitter:data1" content="$29.99">
<meta name="twitter:label2" content="Availability">
<meta name="twitter:data2" content="In Stock">
```

---

### 7. Supported SEO Plugins

**Automatically Detected and Integrated:**

✅ **Yoast SEO**
- Detects `aps_product` post type
- Adds SEO meta box automatically
- Integration via `wpseo_` hooks

✅ **Rank Math SEO**
- Detects `aps_product` post type
- Adds SEO meta box automatically
- Integration via `rank_math_` hooks

✅ **All in One SEO Pack (AIOSEO)**
- Detects `aps_product` post type
- Adds SEO meta box automatically
- Integration via `aioseo_` hooks

✅ **SEOPress**
- Detects `aps_product` post type
- Adds SEO meta box automatically
- Integration via `seopress_` hooks

✅ **The SEO Framework (TSF)**
- Detects `aps_product` post type
- Adds SEO meta box automatically
- Integration via `tsf_` hooks

✅ **Any WordPress-compliant SEO plugin**
- Uses standard WordPress post type registration
- Provides hooks for extensibility
- SEO plugins can integrate without modifications

---

## 📁 Implementation Files

### Files to Create

1. **`wp-content/plugins/affiliate-product-showcase/src/Seo/SeoIntegration.php`** (NEW)
   - SEO integration class
   - Register integration hooks
   - Generate schema.org data
   - Generate Open Graph tags
   - Generate Twitter Card tags

2. **`wp-content/plugins/affiliate-product-showcase/src/Seo/SchemaGenerator.php`** (NEW)
   - Schema.org data generator
   - Product schema
   - Collection schema
   - Breadcrumb schema
   - Validation

3. **`wp-content/plugins/affiliate-product-showcase/src/Seo/OpenGraphGenerator.php`** (NEW)
   - Open Graph meta tag generator
   - Twitter Card generator
   - Image URL processing
   - Fallback handling

### Files to Modify

4. **`wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php`** (MODIFY)
   - Update `register_post_type()` to support SEO plugins
   - Add SEO integration hooks
   - Ensure proper supports array

5. **`wp-content/plugins/affiliate-product-showcase/src/Public/Public_.php`** (MODIFY)
   - Add schema.org output to head
   - Add Open Graph output to head
   - Add Twitter Card output to head
   - Hook integration with SEO plugins

6. **`wp-content/plugins/affiliate-product-showcase/src/Public/SingleProduct.php`** (MODIFY)
   - Ensure proper semantic HTML
   - Add structured data attributes
   - Support SEO plugin overrides

---

## 🎨 Template Requirements

### Semantic HTML Structure

```html
<article itemscope itemtype="https://schema.org/Product" class="aps-single-product">
  <header>
    <h1 itemprop="name">Product Title</h1>
    <div itemprop="description">Product description</div>
  </header>
  
  <div class="aps-product-image">
    <img src="product-image.jpg" alt="Product Name" itemprop="image">
  </div>
  
  <div class="aps-product-price" itemscope itemtype="https://schema.org/Offer" itemprop="offers">
    <span itemprop="price" content="29.99">$29.99</span>
    <span itemprop="priceCurrency" content="USD">USD</span>
    <link itemprop="url" href="https://affiliate-link.com">Buy Now</link>
  </div>
  
  <div class="aps-product-rating" itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
    <span itemprop="ratingValue">4.5</span>
    <span itemprop="reviewCount">6536 reviews</span>
  </div>
</article>
```

---

## 🧪 Testing Requirements

### SEO Plugin Integration Testing

1. **Test with Each SEO Plugin:**
   - Yoast SEO (latest version)
   - Rank Math SEO (latest version)
   - All in One SEO Pack (latest version)
   - SEOPress (latest version)
   - The SEO Framework (latest version)

2. **Verify Integration:**
   - SEO meta box appears on product edit page
   - SEO plugins can analyze product content
   - SEO plugin suggestions work correctly
   - SEO plugin preview works

3. **Verify Schema.org:**
   - Google Structured Data Testing Tool: https://search.google.com/structured-data/testing-tool/
   - Schema.org Validator: https://validator.schema.org/
   - Rich Results Test: https://search.google.com/test/rich-results

4. **Verify Open Graph:**
   - Facebook Sharing Debugger: https://developers.facebook.com/tools/debug/
   - LinkedIn Post Inspector: https://www.linkedin.com/post-inspector/

5. **Verify Twitter Cards:**
   - Twitter Card Validator: https://cards-dev.twitter.com/validator

---

## 📊 Success Criteria

### Must-Have (Blocking)
- ✅ All post types (product, category, tag, ribbon) SEO-friendly
- ✅ SEO plugins auto-detect custom post types
- ✅ Schema.org structured data generates automatically
- ✅ Open Graph meta tags generate automatically
- ✅ Twitter Card meta tags generate automatically
- ✅ Integration hooks for SEO plugins work correctly
- ✅ Semantic HTML structure on all templates
- ✅ No conflicts with popular SEO plugins

### Nice-to-Have
- ✅ SEO plugin compatibility documented
- ✅ Rich snippets appear in Google search
- ✅ Social sharing previews work correctly
- ✅ Performance optimized (no blocking scripts)

---

## 📝 Implementation Checklist

- [ ] Register custom post types with SEO-friendly supports
- [ ] Create SeoIntegration class with hooks
- [ ] Create SchemaGenerator for structured data
- [ ] Create OpenGraphGenerator for meta tags
- [ ] Add schema output to product templates
- [ ] Add Open Graph output to head
- [ ] Add Twitter Card output to head
- [ ] Test integration with Yoast SEO
- [ ] Test integration with Rank Math SEO
- [ ] Test integration with All in One SEO
- [ ] Verify schema.org with Google testing tool
- [ ] Verify Open Graph with Facebook debugger
- [ ] Verify Twitter Cards with validator
- [ ] Document SEO plugin integration
- [ ] Update user documentation with SEO features

---

## 🔌 SEO Best Practices

### For Implementation
- Use semantic HTML5 elements
- Include proper heading hierarchy (h1, h2, h3)
- Add alt text to all images
- Use descriptive URLs (slugs)
- Include meta descriptions (150-160 characters)
- Use canonical URLs
- Implement proper 404 handling
- Use HTTPS (when available)
- Ensure mobile-friendly design
- Optimize page load speed

### For Content
- Use keyword-rich titles
- Write descriptive meta descriptions
- Use heading tags properly
- Include relevant keywords naturally
- Optimize images (WebP, lazy loading)
- Use internal linking
- Add breadcrumb navigation
- Include product reviews/ratings

---

## 📚 References

- Schema.org: https://schema.org/Product
- Google Structured Data: https://developers.google.com/search/docs/guides/intro-structured-data
- Open Graph Protocol: https://ogp.me/
- Twitter Cards: https://developer.twitter.com/en/docs/twitter-for-websites/cards
- Yoast SEO API: https://developer.yoast.com/features/seo-api/
- Rank Math API: https://rankmath.com/kb/developer-api/

---

**Last Updated:** 2026-01-21  
**Version:** 1.0.0  
**Maintainer:** Development Team  
**Status:** Ready for Implementation
