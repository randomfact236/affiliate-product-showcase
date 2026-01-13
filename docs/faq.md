# Frequently Asked Questions (FAQ)

## General Questions

### What is the Affiliate Product Showcase plugin?

The Affiliate Product Showcase plugin is a professional WordPress plugin for displaying affiliate products with powerful filtering, sorting, and customization options. It's designed for affiliate marketers, bloggers, and e-commerce sites.

### What are the system requirements?

- **WordPress:** 6.4 or higher
- **PHP:** 7.4, 8.0, 8.1, 8.2, or 8.3
- **MySQL:** 5.7+ or MariaDB 10.3+
- **WP-CLI:** 2.8+ (optional, for CLI commands)

### Is the plugin free?

Yes, the plugin is open source and free to use. It follows enterprise-grade standards and is suitable for production environments.

### Does the plugin collect any data?

**No.** The plugin is privacy-first and does not:
- Collect personal information
- Track user behavior
- Send data to external servers
- Use cookies for tracking
- Perform analytics

All data is stored locally on your WordPress installation.

### Is the plugin GDPR compliant?

Yes. The plugin is fully GDPR compliant because it:
- Does not collect personal data
- Does not track users
- Stores all data locally
- Provides complete data control to administrators
- Includes a privacy policy template

See `docs/privacy-policy-template.md` for details.

### Does the plugin work with any theme?

Yes, the plugin works with all WordPress themes that follow WordPress coding standards. It includes:
- Responsive design
- Clean, minimal CSS
- Customizable classes
- Inline style options

If you encounter issues, see the Troubleshooting guide.

### Can I use it with page builders?

Yes, the plugin works seamlessly with:
- Gutenberg Block Editor
- Elementor
- Beaver Builder
- Divi Builder
- WPBakery Page Builder
- Any builder that supports shortcodes

## Installation & Setup

### How do I install the plugin?

**Method 1: WordPress Admin**
1. Go to Plugins → Add New
2. Search for "Affiliate Product Showcase"
3. Click Install → Activate

**Method 2: Manual Upload**
1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file
4. Activate the plugin

**Method 3: WP-CLI**
```bash
wp plugin install affiliate-product-showcase.zip --activate
```

### After installation, what should I do?

1. **Create your first product:**
   - Go to Affiliate Products → Add New
   - Fill in product details
   - Publish

2. **Display products:**
   - Use shortcode: `[affiliate_products limit="6"]`
   - Or use the Gutenberg block

3. **Configure settings:**
   - Go to Affiliate Products → Settings
   - Adjust cache, display, and performance options

### How do I migrate from another plugin?

We provide migration assistance. Contact support with:
- Current plugin name
- Sample data export
- Specific requirements

### Do I need to configure anything in wp-config.php?

No, the plugin works out of the box. However, for development:
```php
// Optional: Enable debug mode
define( 'WP_DEBUG', true );

// Optional: Custom plugin prefix (advanced)
define( 'AFFILIATE_PRODUCT_SHOWCASE_PREFIX', 'aps_' );
```

## Shortcode Usage

### What shortcodes are available?

See `docs/shortcode-reference.md` for complete documentation.

**Main shortcodes:**
- `[affiliate_products]` - Display products
- `[affiliate_search]` - Search form
- `[affiliate_categories]` - Category list
- `[affiliate_tags]` - Tag cloud
- `[affiliate_brands]` - Brand list
- `[affiliate_compare]` - Comparison table
- `[affiliate_single]` - Single product
- `[affiliate_submit_form]` - Submission form

### How do I display featured products?

```php
[affiliate_products featured="true" limit="6" columns="3"]
```

### Can I filter by category?

```php
[affiliate_products category="electronics,laptops" limit="12"]
```

### How do I customize the CTA button text?

```php
[affiliate_products cta_text="Get Deal Now"]
```

### Can I hide certain elements?

```php
[affiliate_products show_image="false" show_rating="false" show_features="false"]
```

### How do I use multiple shortcodes on one page?

```php
[affiliate_search show_filters="true"]

[affiliate_products filter="true" sort="true" limit="24"]
```

### Can I use shortcodes in theme templates?

```php
<?php
echo do_shortcode( '[affiliate_products category="featured" limit="6"]' );
?>
```

### Do shortcodes work in widgets?

Yes, use the Text widget and paste the shortcode.

## Product Management

### How do I add products?

**Method 1: Admin Interface**
1. Go to Affiliate Products → Add New
2. Fill in:
   - Product name
   - Price
   - Affiliate URL
   - Category
   - Brand
   - Description
   - Features
   - Image
3. Click Publish

**Method 2: WP-CLI**
```bash
wp affiliate product create "Product Name" 99.99 "https://affiliate.com/link" --category=electronics
```

**Method 3: Import**
```bash
wp affiliate product import products.csv
```

### What product fields are available?

- **Name:** Product title
- **Price:** Numeric value
- **Sale Price:** Optional discounted price
- **Affiliate URL:** External link
- **Category:** Taxonomy
- **Brand:** Text field
- **Description:** Full description
- **Excerpt:** Short description
- **Features:** List of features
- **Image:** URL or uploaded image
- **Ribbons:** Badges (featured, trending, etc.)
- **Stock Status:** in_stock, out_of_stock, on_backorder
- **Featured:** Boolean flag
- **Trending:** Boolean flag
- **On Sale:** Boolean flag

### Can I bulk import products?

Yes, via CSV or JSON:
```bash
# CSV format
name,price,affiliate_url,category,brand
"Product 1",99.99,https://example.com/1,electronics,BrandX
"Product 2",149.99,https://example.com/2,electronics,BrandY

# Import
wp affiliate product import products.csv --format=csv
```

### How do I update multiple products?

**Method 1: Bulk Edit in Admin**
- Use bulk actions in product list

**Method 2: WP-CLI**
```bash
# Update all electronics products
wp affiliate product list --category=electronics --format=json | jq -c '.[]' | while read product; do
  ID=$(echo $product | jq -r '.ID')
  wp affiliate product update $ID --price=$(echo $product | jq -r '.price * 0.9')
done
```

### Can I export products?

Yes:
```bash
# Export all
wp affiliate product export all-products.csv

# Export specific category
wp affiliate product export electronics.csv --category=electronics

# Export as JSON
wp affiliate product export products.json --format=json
```

### How do I delete products?

**Method 1: Admin**
- Go to Affiliate Products
- Select products
- Choose "Move to Trash" or "Delete Permanently"

**Method 2: WP-CLI**
```bash
# Move to trash
wp affiliate product delete 123

# Delete permanently
wp affiliate product delete 123 --force

# Delete multiple
wp affiliate product delete 123 456 789
```

### Can I restore deleted products?

Yes, if moved to trash:
1. Go to Affiliate Products → Trash
2. Select product
3. Click "Restore"

## Display & Customization

### How do I change the layout?

Use the `layout` attribute:
```php
[affiliate_products layout="grid"]    <!-- Default -->
[affiliate_products layout="list"]
[affiliate_products layout="table"]
[affiliate_products layout="slider"]
```

### Can I customize colors and styles?

**Method 1: Custom CSS**
```css
/* Add to theme or Customizer */
.affiliate-product {
    border: 2px solid #0073aa;
    border-radius: 8px;
}

.affiliate-product .cta-button {
    background: #ff6600;
    color: white;
}
```

**Method 2: Inline styles**
```php
[affiliate_products class="my-custom-style" style="border: 2px solid red;"]
```

**Method 3: Filter hooks**
```php
add_filter( 'affiliate_product_showcase_product_html', function( $html ) {
    return '<div class="custom-wrapper">' . $html . '</div>';
} );
```

### How do I make it responsive?

The plugin is responsive by default. For custom breakpoints:
```css
@media (max-width: 768px) {
    .affiliate-products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .affiliate-products-grid {
        grid-template-columns: 1fr;
    }
}
```

### Can I add custom fields to products?

Yes, using WordPress hooks:
```php
// Add custom meta box
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'custom_product_field',
        'Custom Field',
        'custom_field_callback',
        'affiliate_product',
        'normal',
        'high'
    );
} );

// Save custom field
add_action( 'save_post_affiliate_product', function( $post_id ) {
    if ( isset( $_POST['custom_field'] ) ) {
        update_post_meta( $post_id, 'custom_field', sanitize_text_field( $_POST['custom_field'] ) );
    }
} );

// Display in frontend
add_filter( 'affiliate_product_showcase_product_data', function( $data, $product_id ) {
    $data['custom_field'] = get_post_meta( $product_id, 'custom_field', true );
    return $data;
}, 10, 2 );
```

### How do I add custom ribbons/badges?

```php
add_filter( 'affiliate_product_showcase_product_ribbons', function( $ribbons, $product_id ) {
    $price = get_post_meta( $product_id, 'price', true );
    
    if ( $price < 50 ) {
        $ribbons[] = 'Budget Pick';
    }
    
    return $ribbons;
}, 10, 2 );
```

### Can I use custom templates?

Yes, copy template files to your theme:
```bash
cp wp-content/plugins/affiliate-product-showcase/templates/product.php \
   wp-content/themes/your-theme/affiliate-product-showcase/product.php
```

Then customize the template file.

## Performance & Caching

### How do I enable caching?

**Method 1: Settings Page**
- Go to Affiliate Products → Settings
- Enable "Cache Enabled"

**Method 2: WP-CLI**
```bash
wp affiliate settings set cache_enabled true --type=boolean
```

**Method 3: Shortcode**
```php
[affiliate_products cache="true" cache_duration="3600"]
```

### How long does cache last?

Default: 1 hour (3600 seconds)

Customize:
```php
// In functions.php
add_filter( 'affiliate_product_showcase_cache_duration', function( $duration ) {
    return HOUR_IN_SECONDS * 24; // 24 hours
} );
```

### How do I clear cache?

**Method 1: WP-CLI**
```bash
wp affiliate cache clear
```

**Method 2: Settings Page**
- Go to Affiliate Products → Settings
- Click "Clear Cache"

**Method 3: Programmatically**
```php
do_action( 'affiliate_product_showcase_cache_cleared', 'all' );
```

### Can I pre-warm cache?

Yes:
```bash
# Warm all products
wp affiliate cache warm

# Warm specific category
wp affiliate cache warm --category=electronics

# Warm top 50 products
wp affiliate cache warm --limit=50
```

### How do I optimize for high traffic?

1. **Enable caching** (see above)
2. **Use pagination**:
   ```php
   [affiliate_products limit="12" pagination="true"]
   ```

3. **Reduce product limit**:
   ```php
   [affiliate_products limit="6"]
   ```

4. **Use CDN for images**:
   ```php
   add_filter( 'affiliate_product_showcase_product_image', function( $url ) {
       return str_replace( 'example.com', 'cdn.example.com', $url );
   } );
   ```

5. **Enable Redis** (if available):
   ```bash
   wp affiliate settings set redis_enabled true --type=boolean
   ```

### What if my server is slow?

1. **Check cache hit rate**:
   ```bash
   wp affiliate maintenance stats
   ```

2. **Reduce database queries**:
   ```php
   [affiliate_products cache="true" limit="6"]
   ```

3. **Use lazy loading**:
   ```php
   add_filter( 'affiliate_product_showcase_query_args', function( $args ) {
       $args['no_found_rows'] = true;
       return $args;
   } );
   ```

4. **Optimize images**:
   - Use smaller images
   - Enable compression
   - Use WebP format

## WP-CLI Commands

### What WP-CLI commands are available?

See `docs/cli-commands.md` for complete reference.

**Common commands:**
```bash
# List products
wp affiliate product list

# Create product
wp affiliate product create "Name" 99.99 "URL"

# Update product
wp affiliate product update 123 --price=89.99

# Delete product
wp affiliate product delete 123

# Import products
wp affiliate product import file.csv

# Export products
wp affiliate product export file.csv

# Clear cache
wp affiliate cache clear

# Generate test data
wp affiliate tools generate-test-data 10
```

### Can I automate with WP-CLI?

Yes, use cron jobs:
```bash
# Daily backup and cache clear
0 2 * * * cd /var/www/html && wp affiliate product export backup.csv && wp affiliate cache clear
```

### How do I schedule regular imports?

```bash
# Cron job every hour
0 * * * * cd /var/www/html && wp affiliate product import /path/to/products.csv --update-existing
```

## Hooks & Customization

### What hooks are available?

See `docs/hooks-filters.md` for complete reference.

**Common actions:**
- `affiliate_product_showcase_loaded`
- `affiliate_product_showcase_product_created`
- `affiliate_product_track_click`

**Common filters:**
- `affiliate_product_showcase_product_price`
- `affiliate_product_showcase_product_url`
- `affiliate_product_showcase_query_args`

### How do I add custom tracking?

```php
add_action( 'affiliate_product_track_click', function( $product_id, $data ) {
    // Send to Google Analytics
    // Send to custom analytics
    // Log to database
}, 10, 2 );
```

### Can I modify product data?

```php
add_filter( 'affiliate_product_showcase_product_data', function( $data, $product_id ) {
    // Add custom field
    $data['custom'] = get_post_meta( $product_id, 'custom_field', true );
    
    // Modify price
    $data['price'] = $data['price'] * 1.1; // Add 10% margin
    
    return $data;
}, 10, 2 );
```

### How do I add custom fields?

```php
// Add to admin
add_action( 'affiliate_product_showcase_admin_init', function() {
    register_setting( 'affiliate_product_showcase_settings', 'custom_setting' );
    add_settings_field( 'custom_setting', 'Custom Setting', 'custom_setting_callback', 'affiliate-product-showcase', 'basic' );
} );

// Use in frontend
add_filter( 'affiliate_product_showcase_product_data', function( $data, $product_id ) {
    $data['custom_setting'] = get_option( 'custom_setting' );
    return $data;
}, 10, 2 );
```

## Security & Privacy

### Is the plugin secure?

Yes, the plugin follows WordPress security best practices:
- Input sanitization
- Output escaping
- Nonce verification
- Capability checks
- Prepared SQL statements
- CSRF protection

### How do I report a security issue?

See `SECURITY.md` for responsible disclosure process.

**Do NOT** create public issues for security vulnerabilities.

### Does the plugin work with security plugins?

Yes, compatible with:
- Wordfence
- iThemes Security
- Sucuri Security
- All In One WP Security & Firewall

### Can I restrict access?

Yes, using capabilities:
```php
// Allow editors to manage products
add_filter( 'affiliate_product_showcase_capability', function( $capability, $action ) {
    if ( $action === 'manage_products' ) {
        return 'edit_posts';
    }
    return $capability;
}, 10, 2 );
```

### How do I backup plugin data?

**Method 1: WP-CLI**
```bash
wp affiliate product export backup.csv
wp db export affiliate-backup.sql
```

**Method 2: WordPress Export**
- Tools → Export → Affiliate Products

**Method 3: Database**
```bash
mysqldump -u user -p database wp_posts wp_postmeta wp_terms > affiliate-data.sql
```

## Troubleshooting

### Where do I find help?

1. **Documentation:** See all files in `docs/` folder
2. **GitHub Issues:** Report bugs
3. **Support Forum:** WordPress.org plugin page
4. **Email:** Contact maintainer

### What if I get a white screen?

1. Enable debug mode:
   ```php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   ```

2. Check error log:
   ```bash
   tail -f wp-content/debug.log
   ```

3. Deactivate other plugins
4. Switch to default theme

### Products not showing?

See `docs/troubleshooting.md` section 2.2

### Shortcode not working?

See `docs/troubleshooting.md` section 2.1

### Performance issues?

See `docs/troubleshooting.md` section 4

## Compatibility

### WordPress Versions

- **Tested with:** 6.4, 6.5, 6.6, 6.7
- **Requires:** 6.4+
- **Supports:** Latest 3 versions

### PHP Versions

- **Supported:** 7.4, 8.0, 8.1, 8.2, 8.3
- **Recommended:** 8.2+
- **Not supported:** Below 7.4

### Themes

- **Compatible:** All WordPress themes
- **Optimized for:** Block themes, Twenty* themes
- **Tested with:** Astra, GeneratePress, OceanWP, Divi, Avada

### Plugins

**Works with:**
- WooCommerce (for display only)
- Yoast SEO
- WP Rocket
- All caching plugins
- Page builders

**Potential conflicts:**
- Other affiliate plugins (may need to disable)
- Plugins that modify product post types

### Multisite

- **Supported:** Yes
- **Network activation:** Yes
- **Per-site settings:** Yes

### Languages

- **Translation ready:** Yes
- **WPML compatible:** Yes
- **Polylang compatible:** Yes
- **Current translations:** English (more coming soon)

## Advanced Topics

### Can I use it as a headless CMS?

Yes, via REST API:
```javascript
fetch( '/wp-json/affiliate-showcase/v1/products?category=electronics' )
    .then( r => r.json() )
    .then( products => console.log( products ) );
```

### Can I integrate with external APIs?

Yes, using hooks:
```php
add_action( 'affiliate_product_showcase_product_created', function( $product_id, $data ) {
    // Sync to CRM
    my_crm_api->createProduct( $data );
    
    // Sync to email service
    my_email_api->addToList( $data );
}, 10, 2 );
```

### Can I create custom blocks?

Yes, the plugin provides:
- REST API endpoints
- JavaScript utilities
- CSS classes

Example:
```javascript
// Custom React block
import { useSelect } from '@wordpress/data';

function MyAffiliateBlock() {
    const products = useSelect( ( select ) => {
        return select( 'affiliate-showcase' ).getProducts( { limit: 6 } );
    } );
    
    // Render products
}
```

### How do I contribute?

1. Fork the repository
2. Create feature branch
3. Follow coding standards
4. Submit pull request

See `CONTRIBUTING.md` for details.

## Pricing & Licensing

### Is it really free?

Yes, 100% free and open source under GPL v2+ license.

### Can I use it commercially?

Yes, unlimited sites, unlimited products, no restrictions.

### Do you offer premium support?

Contact the maintainer for:
- Custom development
- Priority support
- Custom integrations

### Are updates free?

Yes, all updates are free and automatic via WordPress.

## Getting More Help

### Documentation

- **Main README:** `README.md`
- **Shortcodes:** `docs/shortcode-reference.md`
- **CLI Commands:** `docs/cli-commands.md`
- **Hooks:** `docs/hooks-filters.md`
- **Troubleshooting:** `docs/troubleshooting.md`
- **Privacy:** `docs/privacy-policy-template.md`

### Community

- **GitHub Discussions:** Questions & Answers
- **WordPress.org Forum:** Community support
- **Slack/Discord:** Coming soon

### Professional Support

For enterprise needs:
- Email: [Contact maintainer]
- Priority response: 24-48 hours
- Custom development available

---

*Last updated: January 2026*
*Plugin Version: 1.0.0*
