# Shortcode Reference

## Overview

The Affiliate Product Showcase plugin provides powerful shortcodes for displaying affiliate products on your WordPress site. All shortcodes are fully customizable and support extensive attributes for fine-grained control.

## Primary Shortcode: `[affiliate_products]`

The main shortcode for displaying affiliate products with full filtering, sorting, and layout options.

### Basic Usage

```php
[affiliate_products]
```

### Complete Attribute Reference

   - Product Selection

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `ids` | comma-separated IDs | empty | Specific product IDs to display | `ids="123,456,789"` |
| `exclude` | comma-separated IDs | empty | Product IDs to exclude | `exclude="10,20"` |
| `category` | comma-separated slugs/IDs | empty | Filter by categories | `category="electronics,software"` |
| `tag` | comma-separated slugs/IDs | empty | Filter by tags | `tag="featured,onsale"` |
| `ribbon` | comma-separated slugs/IDs | empty | Filter by ribbons | `ribbon="best-seller,new"` |
| `brand` | comma-separated names | empty | Filter by brand names | `brand="Apple,Samsung"` |
| `featured` | boolean | false | Show only featured products | `featured="true"` |
| `trending` | boolean | false | Show only trending products | `trending="true"` |
| `on_sale` | boolean | false | Show only products on sale | `on_sale="true"` |
| `in_stock` | boolean | false | Show only in-stock products | `in_stock="true"` |

   - Display Settings

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `limit` | integer | 12 | Number of products to show | `limit="24"` |
| `columns` | integer (1-6) | 3 | Number of columns in grid | `columns="4"` |
| `layout` | string | grid | Layout type: grid, list, table, slider | `layout="list"` |
| `pagination` | boolean | true | Show pagination | `pagination="false"` |
| `page` | integer | 1 | Page number to show | `page="2"` |

   - Sorting

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `orderby` | string | featured | Sort by: featured, date, title, price, rating, random | `orderby="price"` |
| `order` | string | DESC | Sort order: ASC or DESC | `order="ASC"` |

   - Content Display

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `show_image` | boolean | true | Show product image | `show_image="false"` |
| `show_brand` | boolean | true | Show brand name | `show_brand="false"` |
| `show_rating` | boolean | true | Show star rating | `show_rating="false"` |
| `show_price` | boolean | true | Show price | `show_price="false"` |
| `show_features` | boolean | true | Show features list | `show_features="false"` |
| `show_cta` | boolean | true | Show CTA button | `show_cta="false"` |
| `show_ribbons` | boolean | true | Show ribbons/badges | `show_ribbons="false"` |
| `show_excerpt` | boolean | true | Show excerpt/description | `show_excerpt="false"` |
| `excerpt_length` | integer | 20 | Excerpt length in words | `excerpt_length="30"` |

   - Interactive Elements

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `filter` | boolean | false | Show filter bar | `filter="true"` |
| `search` | boolean | false | Show search bar | `search="true"` |
| `sort` | boolean | false | Show sort dropdown | `sort="true"` |

   - Customization

| Attribute | Type | Default | Description | Example |
|-----------|------|---------|-------------|---------|
| `cta_text` | string | "View Deal" | Custom CTA button text | `cta_text="Get Offer"` |
| `class` | string | empty | Additional CSS class | `class="my-custom-class"` |
| `style` | string | empty | Inline CSS styles (sanitized) | `style="max-width: 800px;"` |

### Examples

   - Basic Product Grid

```php
[affiliate_products limit="12" columns="3"]
```

   - Featured Products Only

```php
[affiliate_products featured="true" limit="6" columns="2" orderby="date" order="DESC"]
```

   - Category-Specific Products

```php
[affiliate_products category="electronics,laptops" limit="8" columns="4" show_rating="true"]
```

   - List Layout with Filters

```php
[affiliate_products layout="list" filter="true" search="true" sort="true" limit="20"]
```

   - Minimal Display (No Images or Features)

```php
[affiliate_products show_image="false" show_features="false" show_rating="false" columns="1" layout="list"]
```

   - Slider Layout

```php
[affiliate_products layout="slider" limit="10" columns="1" show_cta="true" cta_text="Shop Now"]
```

   - Random Products

```php
[affiliate_products orderby="random" limit="6" columns="3" cache="true"]
```

   - Specific Products by ID

```php
[affiliate_products ids="123,456,789" columns="3" show_ribbons="true"]
```

   - Exclude Products

```php
[affiliate_products exclude="10,20,30" limit="12" category="featured"]
```

   - Custom Styling

```php
[affiliate_products class="premium-grid" style="background: #f8f9fa; padding: 20px;" limit="8"]
```

## Additional Shortcodes

### `[affiliate_submit_form]`

Displays a frontend submission form for users to submit affiliate products.

**Attributes:**
- `redirect_url` - URL to redirect after successful submission
- `require_approval` - boolean, show approval notice
- `form_title` - Custom form title text

**Example:**
```php
[affiliate_submit_form redirect_url="/thank-you" require_approval="true" form_title="Submit Your Product"]
```

### `[affiliate_categories]`

Displays a list or grid of product categories.

**Attributes:**
- `layout` - grid or list (default: list)
- `show_count` - boolean, show product count
- `show_images` - boolean, show category images
- `columns` - integer, columns for grid layout

**Example:**
```php
[affiliate_categories layout="grid" columns="3" show_count="true" show_images="true"]
```

### `[affiliate_tags]`

Displays a tag cloud or tag list.

**Attributes:**
- `format` - cloud or list (default: cloud)
- `smallest` - integer, smallest font size (for cloud)
- `largest` - integer, largest font size (for cloud)
- `unit` - px or em (default: px)
- `show_count` - boolean, show tag count

**Example:**
```php
[affiliate_tags format="cloud" smallest="12" largest="24" unit="px"]
```

### `[affiliate_brands]`

Displays a list of brands.

**Attributes:**
- `layout` - grid or list (default: list)
- `columns` - integer, columns for grid layout
- `show_count` - boolean, show product count per brand

**Example:**
```php
[affiliate_brands layout="grid" columns="4" show_count="true"]
```

### `[affiliate_search]`

Displays a standalone search form.

**Attributes:**
- `placeholder` - Custom placeholder text
- `button_text` - Custom button text
- `show_filters` - boolean, show filter options

**Example:**
```php
[affiliate_search placeholder="Search products..." button_text="Search" show_filters="true"]
```

### `[affiliate_compare]`

Displays a product comparison table.

**Attributes:**
- `ids` - comma-separated product IDs to compare
- `show_features` - boolean, show feature comparison
- `show_prices` - boolean, show price comparison

**Example:**
```php
[affiliate_compare ids="123,456,789" show_features="true" show_prices="true"]
```

### `[affiliate_single]`

Displays a single product in full detail.

**Attributes:**
- `id` - required, product ID
- `layout` - full or compact (default: full)
- `show_related` - boolean, show related products

**Example:**
```php
[affiliate_single id="123" layout="full" show_related="true"]
```

## Advanced Usage

### Combining Multiple Shortcodes

```php
[affiliate_search placeholder="Search our products..." show_filters="true"]

[affiliate_products search="true" filter="true" sort="true" limit="24" columns="3"]
```

### Using with Page Builders

All shortcodes work seamlessly with:
- Gutenberg Block Editor
- Elementor
- Beaver Builder
- Divi Builder
- WPBakery Page Builder

### Conditional Display

Use with WordPress conditional tags:

```php
<?php if ( is_page( 'shop' ) ) : ?>
    [affiliate_products limit="12" columns="3"]
<?php endif; ?>
```

### In Theme Templates

```php
<?php
echo do_shortcode( '[affiliate_products category="featured" limit="6" columns="3"]' );
?>
```

## Caching

All shortcodes support built-in caching for optimal performance:

```php
[affiliate_products cache="true" cache_duration="3600" limit="12"]
```

**Cache Attributes:**
- `cache` - boolean, enable caching (default: true)
- `cache_duration` - integer, cache lifetime in seconds (default: 3600)

## Accessibility

All shortcodes are WCAG 2.1 AA compliant:
- Semantic HTML markup
- ARIA labels where appropriate
- Keyboard navigation support
- Screen reader friendly

## Performance Tips

1. **Use pagination** for large product sets
2. **Enable caching** for frequently accessed content
3. **Limit columns** on mobile devices (responsive design handles this automatically)
4. **Disable unused features** (images, ratings, etc.) to reduce DOM size
5. **Use specific filters** rather than loading all products

## Troubleshooting

### Shortcode Not Displaying

1. **Check plugin activation** - Ensure plugin is active
2. **Verify syntax** - Check for typos in shortcode name
3. **Check for conflicts** - Other plugins may interfere
4. **Clear cache** - If caching is enabled

### No Products Showing

1. **Verify products exist** - Check admin panel
2. **Check filters** - Attributes may be too restrictive
3. **Review categories/tags** - Products may not be assigned
4. **Check publish status** - Products must be published

### Layout Issues

1. **Theme conflicts** - Try with default theme
2. **CSS conflicts** - Check browser console for errors
3. **Column count** - Adjust columns attribute
4. **Custom CSS** - Use class/style attributes for overrides

## Developer Hooks

### Filter Hooks

```php
// Modify shortcode attributes
add_filter( 'affiliate_products_shortcode_attributes', function( $atts ) {
    $atts['limit'] = 20; // Change default limit
    return $atts;
} );

// Modify product query
add_filter( 'affiliate_products_query_args', function( $args ) {
    $args['meta_query'][] = array(
        'key' => 'custom_field',
        'value' => 'some_value'
    );
    return $args;
} );

// Modify product HTML output
add_filter( 'affiliate_products_product_html', function( $html, $product ) {
    // Add custom markup
    return $html;
} );
```

### Action Hooks

```php
// Before shortcode output
add_action( 'affiliate_products_before_shortcode', function( $atts ) {
    // Custom setup
} );

// After shortcode output
add_action( 'affiliate_products_after_shortcode', function( $atts ) {
    // Custom cleanup or tracking
} );
```

## REST API Integration

Shortcodes can fetch data via REST API for AJAX-powered displays:

```javascript
// Example: Custom AJAX loading
fetch( '/wp-json/affiliate-showcase/v1/products?category=electronics&limit=12' )
    .then( response => response.json() )
    .then( products => {
        // Render products dynamically
    } );
```

## Best Practices

1. **Always specify limits** - Don't load all products at once
2. **Use specific filters** - Narrow down results for better performance
3. **Enable caching** - Especially for high-traffic pages
4. **Test on mobile** - Ensure responsive design works
5. **Monitor performance** - Use Query Monitor plugin in development
6. **Keep it simple** - Don't overload pages with too many shortcodes

## Version Compatibility

- WordPress 6.4+
- PHP 7.4+
- All shortcodes are backward compatible within major versions

---

*Last updated: January 2026*
*Plugin Version: 1.0.0*
