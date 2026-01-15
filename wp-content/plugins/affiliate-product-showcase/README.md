# Affiliate Product Showcase

A secure, privacy-focused WordPress plugin for displaying affiliate products with built-in analytics, GDPR compliance, and comprehensive security features.

## Features

- **Secure Affiliate Link Management** - Built-in URL validation and sanitization
- **Privacy-First Design** - No phone-home, no telemetry, no external tracking
- **Analytics Dashboard** - Track views and clicks with real-time statistics
- **GDPR Compliant** - Includes data export and erasure hooks
- **REST API** - Full CRUD operations for programmatic management
- **Rate Limiting** - Protects against API abuse
- **Security Headers** - Content Security Policy and OWASP-recommended protections
- **Multi-Site Support** - Full WordPress Multisite compatibility
- **Caching** - Built-in object caching with stampede protection
- **Affiliate Disclosure** - Customizable disclosure notices for compliance

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- WordPress Object Cache (recommended for production)

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

## Getting Started

### Adding Your First Product

1. Navigate to **Products → Add New**
2. Enter the product title and description
3. Fill in the affiliate details:
   - **Affiliate URL**: The link to the product page
   - **Price**: Product price in your currency
   - **Currency**: Currency code (e.g., USD, EUR, GBP)
   - **Image URL**: Link to product image (must be from your media library)
   - **Rating**: Optional star rating (1-5)
   - **Badge**: Optional badge text (e.g., "Best Seller")
   - **Categories**: Comma-separated categories
4. Click **Publish**

### Displaying Products

#### Using Shortcodes

Display all products:
```php
[affiliate_products]
```

Display products from a specific category:
```php
[affiliate_products category="electronics"]
```

Limit the number of products:
```php
[affiliate_products limit="5"]
```

#### Using PHP

```php
// Get a single product
$product = $product_service->get_product( $product_id );

// Get multiple products
$products = $product_service->get_products([
    'limit' => 10,
    'offset' => 0
]);

// Display a product card
echo do_shortcode('[affiliate_products]');
```

#### Using REST API

**Endpoint:** `GET /wp-json/affiliate-product-showcase/v1/products`

Example request:
```bash
curl -X GET https://yoursite.com/wp-json/affiliate-product-showcase/v1/products
```

Response:
```json
{
  "data": [
    {
      "id": 123,
      "title": "Product Name",
      "slug": "product-name",
      "description": "Product description",
      "currency": "USD",
      "price": 99.99,
      "affiliate_url": "https://example.com/product",
      "image_url": "https://yoursite.com/wp-content/uploads/image.jpg",
      "rating": 4.5,
      "badge": "Best Seller",
      "categories": ["electronics", "gadgets"]
    }
  ],
  "meta": {
    "total": 1,
    "pages": 1
  }
}
```

## Configuration

### Plugin Settings

Access settings at **Settings → Affiliate Product Showcase**:

#### General Settings

- **Affiliate ID**: Your affiliate tracking ID (will be appended to URLs)
- **Disclosure Text**: Custom disclosure message
- **Disclosure Position**: Top or Bottom of product cards
- **Enable Disclosure**: Show/hide affiliate disclosure

#### Analytics Settings

- **Enable Analytics**: Track views and clicks
- **Cache Duration**: Analytics cache duration (seconds)

### Customization

#### Styling

The plugin uses Tailwind CSS for styling. Customize the appearance by overriding the CSS classes in your theme:

```css
/* Override product card styles */
.aps-product-card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.aps-product-title {
    font-size: 1.25rem;
    font-weight: 600;
}

.aps-product-price {
    color: #2563eb;
    font-size: 1.5rem;
}
```

#### Templates

Override plugin templates in your theme:

1. Create a folder: `your-theme/affiliate-product-showcase/`
2. Copy template files from `wp-content/plugins/affiliate-product-showcase/src/Public/partials/`
3. Modify the copies as needed

Available templates:
- `product-card.php` - Product card display
- `product-list.php` - Product list display

## REST API Reference

### Products

#### List Products
```
GET /wp-json/affiliate-product-showcase/v1/products
```

Query Parameters:
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 10, max: 100)
- `category` (string): Filter by category
- `search` (string): Search in title and description

#### Get Single Product
```
GET /wp-json/affiliate-product-showcase/v1/products/{id}
```

#### Create Product
```
POST /wp-json/affiliate-product-showcase/v1/products
```

Body:
```json
{
  "title": "Product Name",
  "description": "Product description",
  "affiliate_url": "https://example.com/product",
  "price": 99.99,
  "currency": "USD",
  "image_url": "https://yoursite.com/wp-content/uploads/image.jpg",
  "rating": 4.5,
  "badge": "Best Seller",
  "categories": ["electronics", "gadgets"]
}
```

#### Update Product
```
PUT /wp-json/affiliate-product-showcase/v1/products/{id}
```

Same body as create (all fields optional).

#### Delete Product
```
DELETE /wp-json/affiliate-product-showcase/v1/products/{id}
```

### Analytics

#### Get Analytics Summary
```
GET /wp-json/affiliate-product-showcase/v1/analytics
```

Response:
```json
{
  "123": {
    "views": 150,
    "clicks": 45
  },
  "124": {
    "views": 200,
    "clicks": 60
  }
}
```

### Health Check

```
GET /wp-json/affiliate-product-showcase/v1/health
```

Response:
```json
{
  "status": "healthy",
  "checks": {
    "database": "ok",
    "cache": "ok",
    "plugin": "ok"
  }
}
```

## Security Features

### URL Validation

The plugin automatically validates and sanitizes all URLs to prevent:

- XSS attacks
- SQL injection
- Data exfiltration via tracking pixels
- Malicious redirects

**Blocked Domains:**
- google-analytics.com
- facebook.com
- doubleclick.net
- All known tracking and ad domains

### Security Headers

The plugin adds comprehensive security headers:

- **Content-Security-Policy**: Restricts resource loading
- **X-Content-Type-Options**: Prevents MIME sniffing
- **X-Frame-Options**: Prevents clickjacking
- **X-XSS-Protection**: XSS filter
- **Referrer-Policy**: Controls referrer information

### Rate Limiting

Public API endpoints are rate-limited to prevent abuse:

- **Products API**: 100 requests per minute per IP
- **Analytics API**: 50 requests per minute per IP

Exceeded limits return HTTP 429 with `Retry-After` header.

## GDPR Compliance

The plugin includes full GDPR support:

### Data Export

Users can export their data via **Tools → Export Personal Data**.

### Data Erasure

Users can request data deletion via **Tools → Erase Personal Data**.

The plugin will:
1. Remove all affiliate product data associated with the user
2. Clear analytics data linked to user IP
3. Provide confirmation of deletion

## Troubleshooting

### Products Not Displaying

**Problem:** Shortcode returns empty or products don't appear.

**Solutions:**
1. Verify products are published (not draft)
2. Check that affiliate URLs are set
3. Clear your cache: delete transients in database
4. Check browser console for JavaScript errors

### Analytics Not Tracking

**Problem:** Views and clicks not updating.

**Solutions:**
1. Verify analytics is enabled in settings
2. Check that object cache is working
3. Clear analytics cache: `wp transient delete analytics_summary`
4. Ensure WordPress cron is running

### REST API 401 Unauthorized

**Problem:** API returns 401 error.

**Solutions:**
1. Verify you're logged into WordPress
2. Check user has `edit_posts` capability
3. Generate new application password in **Users → Profile**
4. Use correct authentication header:
   ```
   Authorization: Basic base64(username:app_password)
   ```

### Images Not Loading

**Problem:** Product images showing broken links.

**Solutions:**
1. Images must be uploaded to your media library (external URLs blocked)
2. Verify image URLs are correct
3. Check file permissions on uploads directory
4. Ensure image files exist on server

### Performance Issues

**Problem:** Site slow after enabling plugin.

**Solutions:**
1. Enable object caching (Redis, Memcached, or database)
2. Reduce products per page limit
3. Increase analytics cache duration
4. Enable WP-CLI for faster operations

## Development

### Project Structure

```
affiliate-product-showcase/
├── src/
│   ├── Admin/           # Admin interface
│   ├── Cache/           # Caching layer
│   ├── Database/        # Database operations
│   ├── Exceptions/      # Custom exceptions
│   ├── Factories/       # Data factories
│   ├── Formatters/      # Data formatters
│   ├── Models/          # Data models
│   ├── Plugin/          # Plugin core
│   ├── Privacy/         # GDPR compliance
│   ├── Public/          # Frontend assets
│   ├── Repositories/    # Data repositories
│   ├── Rest/            # REST API endpoints
│   ├── Security/        # Security features
│   ├── Services/        # Business logic
│   └── Validators/      # Input validation
├── frontend/            # Frontend source files
├── tests/               # PHPUnit tests
├── docs/                # Documentation
└── plan/                # Development plans
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/unit/test-product-service.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Code Quality

```bash
# PHP CodeSniffer
./vendor/bin/phpcs --standard=phpcs.xml.dist

# PHPStan static analysis
./vendor/bin/phpstan analyse src/ --level=5

# Psalm static analysis
./vendor/bin/psalm --show-info=false
```

## Performance

The plugin is optimized for high-traffic sites:

- **Caching**: Object cache with stampede protection
- **Database**: Optimized queries with proper indexing
- **Autoload**: Large data (analytics) not autoloaded
- **Async**: Non-blocking script loading with defer/async
- **Batch Queries**: Reduced N+1 query problem

### Benchmarks

- **Product Load Time**: < 50ms (with cache)
- **API Response Time**: < 100ms (with cache)
- **Analytics Recording**: < 10ms per event
- **Memory Usage**: ~2MB per page load

## Security

The plugin follows WordPress security best practices:

- ✅ ABSPATH protection on all files
- ✅ Input validation and sanitization
- ✅ Output escaping
- ✅ Prepared SQL statements
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS prevention
- ✅ Rate limiting
- ✅ Security headers
- ✅ No phone-home or telemetry
- ✅ GDPR compliant

## Support

### Documentation

- [Developer Guide](docs/developer-guide.md)
- [Code Quality Tools](docs/code-quality-tools.md)
- [CLI Commands](docs/cli-commands.md)
- [Performance Optimization Guide](docs/performance-optimization-guide.md)

### Getting Help

- **Issues**: [GitHub Issues](https://github.com/randomfact236/affiliate-product-showcase/issues)
- **Discussions**: [GitHub Discussions](https://github.com/randomfact236/affiliate-product-showcase/discussions)
- **Documentation**: [GitHub Wiki](https://github.com/randomfact236/affiliate-product-showcase/wiki)

### Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## License

This plugin is licensed under the GPL v2 or later. See [LICENSE](LICENSE) for details.

## Credits

Developed with ❤️ by the Affiliate Product Showcase team.

## Privacy Policy

This plugin:
- Does not collect or transmit any data
- Does not use third-party tracking
- Does not include phone-home features
- All data remains on your server
- Full GDPR compliance built-in

This plugin complies with GDPR and privacy best practices. No data is collected or transmitted externally. All data remains on your server.
