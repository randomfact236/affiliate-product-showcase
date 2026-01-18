# User Guide

Complete guide for using the Affiliate Product Showcase plugin.

## Table of Contents

- [Getting Started](#getting-started)
- [Installation](#installation)
- [Adding Products](#adding-products)
- [Displaying Products](#displaying-products)
- [Configuration](#configuration)
- [Shortcodes](#shortcodes)
- [Gutenberg Blocks](#gutenberg-blocks)
- [Analytics](#analytics)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)

---

## Getting Started

Affiliate Product Showcase is a secure, privacy-focused WordPress plugin for displaying affiliate products with built-in analytics and GDPR compliance.

### Key Features

- **Secure Affiliate Link Management** - Built-in URL validation and sanitization
- **Privacy-First Design** - No phone-home, no telemetry, no external tracking
- **Analytics Dashboard** - Track views and clicks with real-time statistics
- **GDPR Compliant** - Includes data export and erasure hooks
- **REST API** - Full CRUD operations for programmatic management
- **Rate Limiting** - Protects against API abuse
- **Multi-Site Support** - Full WordPress Multisite compatibility
- **Caching** - Built-in object caching with stampede protection
- **Affiliate Disclosure** - Customizable disclosure notices for compliance

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- WordPress Object Cache (recommended for production)

---

## Installation

### Manual Installation

1. Download the plugin zip file from the [releases page](https://github.com/randomfact236/affiliate-product-showcase/releases)
2. Go to **WordPress Admin → Plugins → Add New**
3. Click **Upload Plugin**
4. Select the downloaded zip file
5. Click **Install Now**
6. Activate the plugin

### Installation via FTP

1. Extract the plugin zip file
2. Upload the `affiliate-product-showcase` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin → Plugins**
4. Find "Affiliate Product Showcase" and activate it

### Installation via WordPress Plugin Directory

1. Go to **WordPress Admin → Plugins → Add New**
2. Search for "Affiliate Product Showcase"
3. Click **Install Now**
4. Click **Activate**

---

## Adding Products

### Using WordPress Admin

1. Navigate to **Products → Add New**
2. Enter the product details:

   - Product Information

**Title:** (Required)
- Enter a descriptive product name
- Example: "Premium Wireless Headphones"

**Description:** (Optional)
- Detailed product description
- Supports HTML formatting
- Example: "High-quality wireless headphones with active noise cancellation and 24-hour battery life."

**Affiliate URL:** (Required)
- The link to the product page on your affiliate partner's site
- Must be a valid URL
- For security, only local URLs are allowed in standalone mode
- Example: `https://example.com/affiliate/product-123`

**Price:** (Required)
- Product price in your currency
- Use decimal format (e.g., 99.99)
- Example: 199.99

**Currency:** (Optional)
- Currency code (e.g., USD, EUR, GBP)
- Default: USD
- Example: USD

**Image URL:** (Optional)
- Link to product image
- Must be from your media library (external URLs blocked for security)
- Recommended size: 800x800px
- Example: `https://yoursite.com/wp-content/uploads/2026/01/product.jpg`

**Rating:** (Optional)
- Star rating from 1 to 5
- Uses half-star support (e.g., 4.5)
- Example: 4.5

**Badge:** (Optional)
- Badge text to display on product card
- Examples: "Best Seller", "New", "Sale", "Limited Time"

**Categories:** (Optional)
- Comma-separated category names
- Used for filtering products
- Example: "electronics, audio, headphones"

**Original Price:** (Optional)
- Original price before discount
- Displays crossed-out price
- Example: 249.99

3. Click **Publish** to make the product live
4. Or click **Preview** to see how it will look

### Using REST API

See [REST API Documentation](rest-api.md) for programmatic product creation.

---

## Displaying Products

### Using Shortcodes

   - Display All Products

Show all published products:

```php
[affiliate_products]
```

   - Filter by Category

Display products from a specific category:

```php
[affiliate_products category="electronics"]
```

You can filter by multiple categories:

```php
[affiliate_products category="electronics, audio"]
```

   - Limit Number of Products

Show a specific number of products:

```php
[affiliate_products limit="5"]
```

   - Combine Parameters

You can combine multiple parameters:

```php
[affiliate_products category="electronics" limit="10"]
```

   - All Shortcode Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `limit` | integer | `10` | Number of products to display (1-100) |
| `category` | string | `null` | Filter by category slug |
| `orderby` | string | `date` | Sort by: `date`, `title`, `price` |
| `order` | string | `DESC` | Sort direction: `ASC`, `DESC` |

### Using PHP

   - Get a Single Product

```php
// Get product by ID
$product = $product_service->get_product( $product_id );

// Access product data
echo $product->title;
echo $product->price;
echo $product->affiliate_url;
```

   - Get Multiple Products

```php
// Get products with parameters
$products = $product_service->get_products([
    'limit' => 10,
    'offset' => 0,
    'category' => 'electronics',
    'orderby' => 'price',
    'order' => 'ASC'
]);

// Loop through products
foreach ( $products as $product ) {
    echo '<div class="product">';
    echo '<h3>' . esc_html( $product->title ) . '</h3>';
    echo '<p>Price: $' . esc_html( $product->price ) . '</p>';
    echo '</div>';
}
```

   - Display Product Card

Use the built-in product card template:

```php
// Display a product card
echo do_shortcode( '[affiliate_products id="123"]' );
```

### Using REST API

See [REST API Documentation](rest-api.md) for fetching products programmatically.

---

## Configuration

### Plugin Settings

Access settings at **Settings → Affiliate Product Showcase**

   - General Settings

**Affiliate ID**
- Your affiliate tracking ID
- Will be automatically appended to all product URLs
- Optional - leave blank if not needed
- Example: `your_affiliate_id`

**Disclosure Text**
- Custom disclosure message for compliance
- Displayed on product cards
- Default: "Affiliate Disclosure: We may earn a commission when you purchase through our links."
- Example: "As an Amazon Associate I earn from qualifying purchases."

**Disclosure Position**
- Where to display the disclosure on product cards
- Options: "Top", "Bottom", "Hidden"
- Default: "Top"

**Enable Disclosure**
- Show or hide affiliate disclosure
- Default: "Enabled"
- **Note:** Disable at your own risk - many jurisdictions require disclosure

   - Analytics Settings

**Enable Analytics**
- Track product views and clicks
- Default: "Enabled"
- Requires object cache for best performance

**Cache Duration**
- How long to cache analytics data (in seconds)
- Default: 3600 (1 hour)
- Lower values = more accurate, more database queries
- Higher values = better performance, slightly delayed data

   - Performance Settings

**Enable Object Cache**
- Use WordPress object cache for analytics
- Recommended: "Enabled"
- Requires object cache plugin (Redis, Memcached, etc.)
- If not available, falls back to database

**Cache Stampede Protection**
- Prevents multiple simultaneous cache rebuilds
- Default: "Enabled"
- Recommended: Always keep enabled

   - Security Settings

**Standalone Mode**
- Only allow local URLs (from your media library)
- Default: "Enabled"
- Disable to allow external URLs from specific domains
- **Security Note:** Standalone mode is more secure

**Blocked Domains**
- List of domains to block (comma-separated)
- Default includes tracking and ad domains
- Example: `google-analytics.com, facebook.com, doubleclick.net`

### Customization

   - Styling

The plugin uses Tailwind CSS for styling. Customize the appearance by overriding CSS classes in your theme:

**Product Card Styling**

```css
/* Override product card styles */
.aps-product-card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    transition: transform 0.3s ease;
}

.aps-product-card:hover {
    transform: translateY(-5px);
}

/* Product title */
.aps-product-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 10px;
}

/* Product price */
.aps-product-price {
    color: #2563eb;
    font-size: 1.5rem;
    font-weight: 700;
}

.aps-original-price {
    color: #9ca3af;
    text-decoration: line-through;
    margin-left: 10px;
}

/* Product badge */
.aps-product-badge {
    background: #10b981;
    color: #ffffff;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Star ratings */
.aps-star {
    color: #fbbf24;
}

.aps-star.empty {
    color: #d1d5db;
}

/* Affiliate disclosure */
.aps-disclosure {
    background: #f3f4f6;
    padding: 10px;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 10px;
}
```

   - Templates

Override plugin templates in your theme:

1. Create a folder: `your-theme/affiliate-product-showcase/`
2. Copy template files from `wp-content/plugins/affiliate-product-showcase/src/Frontend/partials/`
3. Modify the copies as needed

**Available Templates:**

- `product-card.php` - Product card display
- `product-list.php` - Product list display
- `single-product.php` - Single product page
- `grid-layout.php` - Grid layout wrapper

**Example Template Override:**

```php
/* wp-content/themes/your-theme/affiliate-product-showcase/product-card.php */

<div class="custom-product-card">
    <h2 class="custom-title"><?php echo esc_html( $product->title ); ?></h2>
    
    <?php if ( $product->image_url ) : ?>
        <img src="<?php echo esc_url( $product->image_url ); ?>" 
             alt="<?php echo esc_attr( $product->title ); ?>"
             class="custom-image">
    <?php endif; ?>
    
    <div class="custom-price">
        $<?php echo esc_html( $product->price ); ?>
    </div>
    
    <a href="<?php echo esc_url( $product->affiliate_url ); ?>" 
       class="custom-button"
       rel="nofollow sponsored"
       target="_blank">
        View Deal
    </a>
</div>
```

---

## Shortcodes

### [affiliate_products]

Display products with various options.

   - Basic Usage

```php
[affiliate_products]
```

   - Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `id` | integer | `null` | Display specific product by ID |
| `limit` | integer | `10` | Number of products to display (1-100) |
| `category` | string | `null` | Filter by category slug |
| `orderby` | string | `date` | Sort by: `date`, `title`, `price` |
| `order` | string | `DESC` | Sort direction: `ASC`, `DESC` |

   - Examples

**Display specific product:**
```php
[affiliate_products id="123"]
```

**Display 5 products:**
```php
[affiliate_products limit="5"]
```

**Display electronics category:**
```php
[affiliate_products category="electronics"]
```

**Sort by price ascending:**
```php
[affiliate_products orderby="price" order="ASC"]
```

**Combine multiple parameters:**
```php
[affiliate_products category="electronics" limit="10" orderby="price" order="ASC"]
```

---

## Gutenberg Blocks

### Product Grid Block

The Product Grid block provides a visual interface for displaying products in a grid layout.

   - Adding the Block

1. Edit a page or post in WordPress
2. Click the **+** (Add Block) button
3. Search for "Product Grid"
4. Click to add the block

   - Block Settings

**Grid Settings**

- **Products per page:** Number of products to display (2-12)
- **Columns:** Number of grid columns (1-6)
- **Gap:** Spacing between items in pixels (0-48)

**Display Options**

- **Show Price:** Display product prices
- **Show Rating:** Display star ratings
- **Show Badge:** Display product badges

**Hover Effect**

- **None:** No hover animation
- **Lift Up:** Cards lift up on hover
- **Scale:** Cards scale up on hover
- **Shadow:** Cards gain shadow on hover

**Style Presets**

Quick styles for common layouts:

- **Default:** 3 columns, 16px gap, all features enabled
- **Compact:** 4 columns, 12px gap, price only
- **Featured:** 2 columns, 24px gap, shadow hover effect
- **Minimal:** 6 columns, 8px gap, no features

### Product Showcase Block

The Product Showcase block provides a single product display with detailed information.

   - Adding the Block

1. Edit a page or post
2. Click the **+** (Add Block) button
3. Search for "Product Showcase"
4. Click to add the block

   - Block Settings

- Select a product from the dropdown
- Customize display options
- Adjust layout and styling

---

## Analytics

### Viewing Analytics

1. Navigate to **Products → Analytics**
2. View statistics for all products
3. Filter by date range
4. View click rates and trends

### Analytics Data

The plugin tracks:

- **Views:** Number of times product was viewed
- **Clicks:** Number of times affiliate link was clicked
- **Click Rate:** Percentage of views that resulted in clicks
- **Last Viewed:** Date/time product was last viewed
- **Last Clicked:** Date/time link was last clicked

### Tracking with REST API

Track product views and clicks programmatically:

```javascript
// Track a view
fetch('/wp-json/affiliate-product-showcase/v1/analytics/view', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    product_id: 123
  })
});

// Track a click
fetch('/wp-json/affiliate-product-showcase/v1/analytics/click', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    product_id: 123
  })
});
```

See [REST API Documentation](rest-api.md) for more details.

---

## Troubleshooting

### Products Not Displaying

**Problem:** Shortcode returns empty or products don't appear.

**Possible Causes & Solutions:**

1. **Products Not Published**
   - Check that products are published (not draft)
   - Go to **Products → All Products** and verify status

2. **No Products Created**
   - Ensure you have created at least one product
   - Go to **Products → Add New**

3. **Affiliate URLs Not Set**
   - Check that all products have affiliate URLs
   - Affiliate URL is required field

4. **Cache Issues**
   - Clear your cache
   - Delete transients in database
   - Flush object cache if using Redis/Memcached

5. **Shortcode Syntax Error**
   - Verify shortcode syntax is correct
   - Check for typos in parameter names
   - Use proper shortcode format: `[affiliate_products]`

6. **JavaScript Errors**
   - Check browser console for errors
   - F12 → Console tab
   - Fix any JavaScript errors

### Analytics Not Tracking

**Problem:** Views and clicks not updating.

**Possible Causes & Solutions:**

1. **Analytics Not Enabled**
   - Go to **Settings → Affiliate Product Showcase**
   - Check "Enable Analytics" is checked
   - Save settings

2. **Object Cache Not Working**
   - Verify object cache is active
   - Install Redis or Memcached plugin
   - Test cache connection

3. **Cache Not Cleared**
   - Clear analytics cache
   - Run: `wp transient delete analytics_summary`
   - Or delete in database manually

4. **WordPress Cron Not Running**
   - Check if WordPress cron is running
   - Use a real cron job instead
   - Test: `wp cron event list`

5. **REST API Not Accessible**
   - Check REST API is enabled
   - Verify permalinks are set
   - Save permalinks settings

### REST API 401 Unauthorized

**Problem:** API returns 401 error.

**Possible Causes & Solutions:**

1. **Not Logged In**
   - Verify you're logged into WordPress
   - Test in incognito window

2. **Wrong Credentials**
   - Check username is correct
   - Generate new application password
   - Use application password, not regular password

3. **Insufficient Permissions**
   - User must have `edit_posts` capability
   - Check user role in **Users → All Users**
   - Use Administrator account for testing

4. **Authentication Header Format**
   - Correct format:
     ```
     Authorization: Basic base64(username:app_password)
     ```
   - Generate base64 string properly

### Images Not Loading

**Problem:** Product images showing broken links.

**Possible Causes & Solutions:**

1. **External URLs Blocked**
   - Images must be uploaded to your media library
   - External URLs are blocked for security
   - Upload images to **Media → Add New**

2. **Image URLs Incorrect**
   - Verify image URLs are correct
   - Check for typos
   - Test URL in browser

3. **File Permissions**
   - Check file permissions on uploads directory
   - Should be 755 for directories, 644 for files
   - Contact hosting provider if needed

4. **Files Don't Exist**
   - Ensure image files exist on server
   - Check file paths
   - Re-upload images if needed

### Performance Issues

**Problem:** Site slow after enabling plugin.

**Possible Causes & Solutions:**

1. **Object Cache Not Enabled**
   - Enable object cache (Redis, Memcached)
   - Database fallback is slower
   - Install object cache plugin

2. **Too Many Products**
   - Reduce products per page limit
   - Use pagination
   - Default: 10 products per page

3. **Cache Duration Too Low**
   - Increase analytics cache duration
   - Default: 3600 seconds (1 hour)
   - Higher values = better performance

4. **No WP-CLI Available**
   - Use WP-CLI for faster operations
   - Avoid WordPress admin for bulk operations
   - Use REST API instead

5. **Server Resources**
   - Check server resources (CPU, RAM)
   - Upgrade hosting if needed
   - Use caching plugin (WP Rocket, W3 Total Cache)

### Plugin Activation Issues

**Problem:** Plugin won't activate.

**Possible Causes & Solutions:**

1. **PHP Version Too Low**
   - Requires PHP 7.4 or higher
   - Check PHP version: `php -v`
   - Upgrade PHP or contact hosting provider

2. **WordPress Version Too Low**
   - Requires WordPress 5.8 or higher
   - Check WordPress version in admin
   - Update WordPress to latest version

3. **Missing Dependencies**
   - Ensure all files are uploaded
   - Check file permissions
   - Re-upload plugin files

4. **Plugin Conflict**
   - Deactivate other plugins temporarily
   - Reactivate one by one to find conflict
   - Check for plugin name collisions

5. **Memory Limit**
   - Increase PHP memory limit
   - Add to wp-config.php:
     ```php
     define( 'WP_MEMORY_LIMIT', '256M' );
     ```
   - Contact hosting provider

---

## FAQ

### General Questions

**Q: Is this plugin free?**  
A: Yes, this plugin is free and open-source (GPL v2 or later).

**Q: Does this plugin collect my data?**  
A: No. The plugin does not collect, transmit, or store any personal data. All data remains on your server.

**Q: Do I need an external account?**  
A: No. The plugin works standalone without any external services or APIs.

**Q: Can I use this with any affiliate program?**  
A: Yes, you can use affiliate links from any program (Amazon, ShareASale, CJ, etc.).

**Q: Is this GDPR compliant?**  
A: Yes, the plugin includes GDPR compliance features including data export and erasure hooks.

### Security Questions

**Q: Why are external URLs blocked?**  
A: For security reasons, external URLs are blocked to prevent data exfiltration via tracking pixels and malicious redirects.

**Q: Can I allow external URLs?**  
A: Yes, you can disable "Standalone Mode" in settings, but this reduces security. Only allow trusted domains.

**Q: What domains are blocked?**  
A: Tracking and ad domains are blocked by default (google-analytics.com, facebook.com, doubleclick.net, etc.).

**Q: Is my affiliate data secure?**  
A: Yes, all data is stored in your WordPress database. No data is transmitted externally.

### Technical Questions

**Q: Do I need coding knowledge?**  
A: No, the plugin works with shortcodes and Gutenberg blocks. No coding required for basic use.

**Q: Can I customize the design?**  
A: Yes, you can override templates and CSS to fully customize the appearance.

**Q: Does this work with page builders?**  
A: Yes, the plugin works with Elementor, Divi, Beaver Builder, and other page builders.

**Q: Can I use this in a multisite network?**  
A: Yes, the plugin fully supports WordPress Multisite.

**Q: Does this plugin work with caching plugins?**  
A: Yes, the plugin is compatible with WP Rocket, W3 Total Cache, Super Cache, and other caching plugins.

### Analytics Questions

**Q: How accurate are the analytics?**  
A: Analytics are highly accurate, with real-time tracking and cache synchronization.

**Q: Can I export analytics data?**  
A: Yes, you can export analytics via REST API or integrate with third-party analytics tools.

**Q: Does analytics slow down my site?**  
A: No, analytics are optimized for performance with object caching and efficient database queries.

**Q: Can I disable analytics?**  
A: Yes, you can disable analytics in settings if you don't need tracking.

### Affiliate Questions

**Q: Do I need to add disclosure text?**  
A: Yes, most jurisdictions require affiliate disclosure. The plugin includes customizable disclosure text.

**Q: Where should I place the disclosure?**  
A: The disclosure can be placed at the top or bottom of product cards. Top is recommended for compliance.

**Q: Can I customize the disclosure text?**  
A: Yes, you can fully customize the disclosure text in plugin settings.

**Q: Do affiliate links open in a new tab?**  
A: Yes, all affiliate links open in a new tab with `rel="nofollow sponsored"` attributes for SEO.

### Performance Questions

**Q: Will this plugin slow down my site?**  
A: No, the plugin is optimized for performance with caching, efficient queries, and minimal overhead.

**Q: Do I need object cache?**  
A: Object cache is recommended for best performance but not required. The plugin falls back to database caching.

**Q: How can I improve performance?**  
A: Enable object cache, reduce products per page, increase cache duration, and use a caching plugin.

---

## Additional Resources

### Documentation

- [Developer Guide](developer-guide.md) - For developers extending the plugin
- [REST API Reference](rest-api.md) - For programmatic access
- [WordPress.org Compliance](wordpress-org-compliance.md) - For submission requirements

### Support

- **GitHub Issues:** [Report bugs](https://github.com/randomfact236/affiliate-product-showcase/issues)
- **GitHub Discussions:** [Ask questions](https://github.com/randomfact236/affiliate-product-showcase/discussions)
- **WordPress.org Forums:** [Community support](https://wordpress.org/support/plugin/affiliate-product-showcase/)

### Contributing

We welcome contributions! See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines.

---

**Version:** 1.0.0  
**Last Updated:** January 2026  
**Plugin Version:** 1.0.0
