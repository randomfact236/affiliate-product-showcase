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

## Quick Start

### Installation

1. Download the plugin zip file from the [releases page](https://github.com/randomfact236/affiliate-product-showcase/releases)
2. Go to **WordPress Admin → Plugins → Add New**
3. Click **Upload Plugin**
4. Select the downloaded zip file
5. Click **Install Now**
6. Activate the plugin

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

**Using Shortcodes:**
```php
[affiliate_products]
```

**Using Gutenberg Blocks:**
- Add "Product Grid" block for grid display
- Add "Product Showcase" block for single product display

**Using PHP:**
```php
$product = $product_service->get_product( $product_id );
```

## Documentation

### User Documentation

- 📖 **[User Guide](docs/user-guide.md)** - Complete guide for using the plugin
  - Installation instructions
  - Adding and managing products
  - Displaying products (shortcodes, blocks, PHP)
  - Configuration settings
  - Customization and styling
  - Troubleshooting and FAQ

### Developer Documentation

- 🔧 **[Developer Guide](docs/developer-guide.md)** - For developers extending the plugin
  - Environment configuration
  - Path and URL handling
  - Options management
  - Security best practices
  - Development workflow

- 🔌 **[REST API Reference](docs/rest-api.md)** - Complete API documentation
  - All endpoints documented
  - Authentication methods
  - Request/response examples
  - Error handling
  - SDK examples (JavaScript, PHP, Python)
  - Rate limiting and security

- 🛡️ **[WordPress.org Compliance](docs/wordpress-org-compliance.md)** - Plugin repository requirements
  - Plugin header requirements
  - Code standards
  - Security requirements
  - Submission checklist

### Additional Documentation

- ⚡ **[Performance Optimization Guide](docs/performance-optimization-guide.md)** - Framework for performance analysis
- 🧪 **[Code Quality Tools](docs/code-quality-tools.md)** - Code quality and testing tools
- 💻 **[CLI Commands](docs/cli-commands.md)** - WP-CLI commands reference
- 🪝 **[Automatic Backup Guide](docs/automatic-backup-guide.md)** - Backup and restore procedures

## Security

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

## Support

### Documentation

- 📖 [User Guide](docs/user-guide.md)
- 🔧 [Developer Guide](docs/developer-guide.md)
- 🔌 [REST API Reference](docs/rest-api.md)
- 🛡️ [WordPress.org Compliance](docs/wordpress-org-compliance.md)

### Getting Help

- **Issues**: [GitHub Issues](https://github.com/randomfact236/affiliate-product-showcase/issues)
- **Discussions**: [GitHub Discussions](https://github.com/randomfact236/affiliate-product-showcase/discussions)
- **FAQ**: See [User Guide](docs/user-guide.md#faq)

### Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

## License

This plugin is licensed under the GPL v2 or later. See [LICENSE](LICENSE) for details.

## Privacy Policy

This plugin:
- Does not collect or transmit any data
- Does not use third-party tracking
- Does not include phone-home features
- All data remains on your server
- Full GDPR compliance built-in

This plugin complies with GDPR and privacy best practices. No data is collected or transmitted externally. All data remains on your server.

---

## Project Status

- **Version**: 1.0.0
- **PHP Version**: 7.4+
- **WordPress Version**: 5.8+
- **License**: GPL v2 or later
- **Documentation**: Complete (see links above)
