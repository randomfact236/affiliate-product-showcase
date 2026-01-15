=== Affiliate Product Showcase ===
Contributors: affiliate-product-showcase
Tags: affiliate, products, showcase, marketing, e-commerce
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A secure, privacy-focused WordPress plugin for displaying affiliate products with built-in analytics, GDPR compliance, and comprehensive security features.

== Description ==

Affiliate Product Showcase is a professional WordPress plugin for managing and displaying affiliate products with ease. Built with security, privacy, and performance in mind, it provides everything you need to create beautiful product showcases that convert.

**Key Features:**

* **Secure Affiliate Link Management** - Built-in URL validation and sanitization to prevent XSS, SQL injection, and data exfiltration
* **Privacy-First Design** - No phone-home, no telemetry, no external tracking. All data stays on your server
* **Analytics Dashboard** - Track views and clicks with real-time statistics and detailed reporting
* **GDPR Compliant** - Includes data export and erasure hooks for full privacy compliance
* **REST API** - Full CRUD operations for programmatic management and integration
* **Rate Limiting** - Protects against API abuse with configurable rate limits
* **Security Headers** - Content Security Policy and OWASP-recommended protections
* **Multi-Site Support** - Full WordPress Multisite compatibility
* **Caching** - Built-in object caching with stampede protection for optimal performance
* **Affiliate Disclosure** - Customizable disclosure notices for FTC and international compliance
* **Gutenberg Blocks** - Modern block editor integration with Product Grid and Product Showcase blocks
* **Shortcode Support** - Easy product display with flexible shortcodes
* **Widget Support** - Display products in sidebars and widget areas
* **Template Override** - Fully customizable with template overrides in your theme

**Security & Privacy:**

The plugin follows WordPress security best practices with:
* ABSPATH protection on all PHP files
* Input validation and sanitization
* Output escaping
* Prepared SQL statements
* CSRF protection
* SQL injection prevention
* XSS protection
* No external requests (zero phone-home)
* No data collection or telemetry

**Performance:**

* Object cache with stampede protection
* Optimized database queries
* Efficient asset loading
* Batch queries to eliminate N+1 problems
* Memory-optimized (no autoload of large data)

**Use Cases:**

* Amazon Associates
* ShareASale affiliates
* Commission Junction (CJ)
* Impact Radius
* Rakuten Advertising
* Any affiliate program

Perfect for:
* Niche review sites
* Comparison websites
* Product recommendation blogs
* E-commerce affiliate sites
* Deal and coupon sites

== Installation ==

1. Upload the `affiliate-product-showcase` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'Products → Add New' to add your first affiliate product
4. Use the shortcode `[affiliate_products]` or Gutenberg blocks to display products

For detailed installation and usage instructions, see the [User Guide](https://github.com/randomfact236/affiliate-product-showcase/blob/main/docs/user-guide.md).

== Frequently Asked Questions ==

= Does this plugin collect my data? =

No. The plugin does not collect, transmit, or store any personal data. All data remains on your server. The plugin is fully GDPR compliant and includes data export and erasure hooks.

= Do I need an external account or API key? =

No. The plugin works completely standalone without any external services, APIs, or third-party accounts. Just install, activate, and start adding products.

= Can I use this with any affiliate program? =

Yes! You can use affiliate links from any program including Amazon Associates, ShareASale, Commission Junction, Impact Radius, Rakuten, and many others.

= Is this plugin GDPR compliant? =

Yes. The plugin includes full GDPR support with data export and erasure hooks. You can export and delete user data through WordPress Tools → Export Personal Data and Erase Personal Data.

= Why are external URLs blocked by default? =

For security reasons, external URLs are blocked in standalone mode to prevent data exfiltration via tracking pixels and malicious redirects. You can disable this in settings, but we recommend keeping it enabled and uploading images to your WordPress media library.

= Do I need coding knowledge to use this plugin? =

No. The plugin works with shortcodes and Gutenberg blocks. No coding required for basic use. However, developers can extend functionality using the REST API or PHP hooks.

= Does this plugin work with page builders? =

Yes. The plugin is compatible with Elementor, Divi, Beaver Builder, and other popular page builders through shortcodes and widget support.

= Can I customize the product display? =

Yes. You can fully customize the appearance by:
1. Using the Gutenberg Block settings
2. Overriding CSS classes in your theme
3. Creating template overrides in your theme

= Does this plugin slow down my site? =

No. The plugin is optimized for performance with object caching, efficient database queries, and minimal overhead. Benchmarks show <50ms product load time with cache enabled.

= Is there analytics included? =

Yes. The plugin includes built-in analytics to track product views and clicks. Analytics are stored locally on your server and can be accessed via the WordPress admin dashboard.

= Do I need to add affiliate disclosure text? =

Yes. Most jurisdictions require affiliate disclosure. The plugin includes customizable disclosure text that can be placed at the top or bottom of product cards. Default disclosure: "Affiliate Disclosure: We may earn a commission when you purchase through our links."

= Can I use this in a multisite network? =

Yes. The plugin fully supports WordPress Multisite with proper network isolation and site-specific settings.

= Does this work with caching plugins? =

Yes. The plugin is compatible with WP Rocket, W3 Total Cache, Super Cache, WP Super Cache, and other caching plugins.

= Is this plugin free? =

Yes. This plugin is completely free and open-source (GPL v2 or later). No premium version, no hidden fees, no upsells.

== Screenshots ==

1. Product Grid Block - Modern grid layout with customizable columns and styling
2. Admin Product Editor - Easy-to-use interface for adding and managing products
3. Analytics Dashboard - Real-time statistics for views and clicks
4. Plugin Settings - Comprehensive configuration options
5. Product Card Display - Beautiful, responsive product cards

== Upgrade Notice ==

= 1.0.0 =

Initial release with full feature set including:
* Product management with custom post type
* REST API for programmatic access
* Gutenberg block editor support (Product Grid and Product Showcase)
* Built-in analytics tracking
* Shortcode and widget support
* Comprehensive security features
* GDPR compliance
* Multi-site support

== Changelog ==

= 1.0.0 =
* Initial release
* Product management with custom post type (aps_product)
* REST API for product CRUD operations
* Shortcode support ([affiliate_products])
* Block editor support for product showcase
* Analytics tracking for views and clicks
* Admin settings page with options
* Affiliate link service with security attributes
* Widget support for displaying products
* Product grid and single product templates
* Asset management with Vite manifest
* URL validation and sanitization
* GDPR data export and erasure hooks
* Rate limiting on REST API endpoints
* Security headers (CSP, X-Content-Type-Options, X-Frame-Options)
* Multi-site compatibility
* Object caching with stampede protection
* Batch query optimization
* Non-blocking script loading

== Other Notes ==

**Performance Recommendations:**

* Enable object cache (Redis, Memcached, or database) for best performance
* Use pagination for large product catalogs
* Enable caching plugin (WP Rocket, W3 Total Cache, etc.)
* Optimize images before uploading

**Security Notes:**

* Always keep WordPress and plugins updated
* Use strong passwords and application passwords for API access
* Enable security headers for optimal protection
* Review blocked domains list in settings

**Developer Resources:**

* [Developer Guide](https://github.com/randomfact236/affiliate-product-showcase/blob/main/docs/developer-guide.md)
* [REST API Documentation](https://github.com/randomfact236/affiliate-product-showcase/blob/main/docs/rest-api.md)
* [WordPress.org Compliance Guide](https://github.com/randomfact236/affiliate-product-showcase/blob/main/docs/wordpress-org-compliance.md)
* [GitHub Repository](https://github.com/randomfact236/affiliate-product-showcase)

**Support:**

* GitHub Issues: https://github.com/randomfact236/affiliate-product-showcase/issues
* GitHub Discussions: https://github.com/randomfact236/affiliate-product-showcase/discussions
* WordPress.org Support Forums: https://wordpress.org/support/plugin/affiliate-product-showcase/

**Contributing:**

We welcome contributions! Please see [CONTRIBUTING.md](https://github.com/randomfact236/affiliate-product-showcase/blob/main/CONTRIBUTING.md) for guidelines.

**License:**

This plugin is licensed under the GPL v2 or later. See [LICENSE](https://github.com/randomfact236/affiliate-product-showcase/blob/main/LICENSE) for details.
