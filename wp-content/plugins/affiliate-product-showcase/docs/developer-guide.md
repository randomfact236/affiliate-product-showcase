# Affiliate Product Showcase - Developer Guide

Welcome to the Affiliate Product Showcase plugin developer guide. This guide provides essential information for developers working with this plugin.

## Table of Contents

- [Environment Configuration](#environment-configuration)
- [Path and URL Handling](#path-and-url-handling)
- [Options Management](#options-management)
- [Security Best Practices](#security-best-practices)
- [Development Workflow](#development-workflow)
- [Testing Guidelines](#testing-guidelines)
- [Contributing](#contributing)

---

## Environment Configuration

### .env Files Are Development-Only

**IMPORTANT:** `.env` files are **NOT** used in production. They are strictly for local development and CI environments.

#### Why .env is Dev-Only

1. **Security**: Environment files can accidentally be committed to version control
2. **Portability**: Production servers may not support .env files or may use different configuration mechanisms
3. **Performance**: WordPress Options API is cached and optimized for production use
4. **Standardization**: WordPress plugins should use the WordPress Options API for configuration storage

#### Development Workflow

**For Local Development:**

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your local development settings:
   ```bash
   PLUGIN_DEV_MODE=true
   PLUGIN_DEBUG=true
   # ... other settings
   ```

3. Environment variables are automatically loaded by PHP's `getenv()` function

**For Production:**

1. Configure all settings via the WordPress admin settings page
2. Settings are stored in the WordPress `wp_options` table
3. No `.env` file is present on production servers

### Available Environment Variables

#### Plugin Settings

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_DEV_MODE` | boolean | `false` | Enable development features, disable caching |
| `PLUGIN_DEBUG` | boolean | `false` | Enable detailed logging and error messages |

#### Database Configuration (Optional)

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_DB_HOST` | string | WordPress DB | Custom database host |
| `PLUGIN_DB_NAME` | string | WordPress DB | Custom database name |
| `PLUGIN_DB_USER` | string | WordPress DB | Custom database user |
| `PLUGIN_DB_PASSWORD` | string | WordPress DB | Custom database password |
| `PLUGIN_DB_PREFIX` | string | WordPress prefix | Custom table prefix |

**Note:** If not set, defaults to WordPress database credentials.

#### Redis Configuration (Optional)

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_REDIS_HOST` | string | `127.0.0.1` | Redis server host |
| `PLUGIN_REDIS_PORT` | integer | `6379` | Redis server port |
| `PLUGIN_REDIS_PASSWORD` | string | empty | Redis authentication password |
| `PLUGIN_REDIS_DATABASE` | integer | `0` | Redis database number |
| `PLUGIN_REDIS_TTL` | integer | `3600` | Default TTL (seconds) |

#### Developer Options

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_TEST_MODE` | boolean | `false` | Enable test mode features |
| `PLUGIN_MOCK_API` | boolean | `false` | Use mock API responses |
| `PLUGIN_ENABLE_PROFILING` | boolean | `false` | Enable code profiling |

**WARNING:** Never set these options in production.

---

## Path and URL Handling

The plugin provides centralized helpers for handling paths and URLs to ensure compatibility with multisite, subdirectory installations, and custom wp-content directories.

### Why Use Path Helpers?

Using WordPress native functions through the `Paths` helper provides several benefits:

1. **Multisite Compatibility**: Works correctly in WordPress multisite networks
2. **Subdirectory Support**: Compatible with sites in subdirectories (e.g., `/blog/`)
3. **Custom wp-content**: Supports custom `wp-content` directory locations
4. **No Hardcoded URLs**: Prevents hardcoded domain names that break on deployment
5. **Consistent API**: Single source of truth for all path/URL operations

### Using the Paths Helper

The `Paths` class provides static methods for common path and URL operations.

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get plugin base path (filesystem)
$path = Paths::plugin_path();
// Returns: /var/www/html/wp-content/plugins/affiliate-product-showcase/

// Get plugin base URL
$url = Paths::plugin_url();
// Returns: https://example.com/wp-content/plugins/affiliate-product-showcase/

// Get specific file path
$template_path = Paths::plugin_file_path( 'templates/product-card.php' );
// Returns: /var/www/html/wp-content/plugins/affiliate-product-showcase/templates/product-card.php

// Get specific file URL
$asset_url = Paths::plugin_file_url( 'assets/css/style.css' );
// Returns: https://example.com/wp-content/plugins/affiliate-product-showcase/assets/css/style.css
```

### Asset URL Management

The plugin separates development assets (uncompiled) from production assets (compiled in `dist/`).

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get compiled/dist assets URL (for production)
$dist_url = Paths::dist_url();
// Returns: https://example.com/wp-content/plugins/affiliate-product-showcase/assets/dist/

// Get a specific compiled asset
$css_url = Paths::dist_file_url( 'frontend.abc123.css' );
$js_url  = Paths::dist_file_url( 'admin.def456.js' );

// Get uncompiled assets URL (for development)
$images_url = Paths::images_url();
// Returns: https://example.com/wp-content/plugins/affiliate-product-showcase/assets/images/
```

### Template/View File Paths

When including PHP template files, use the view path helpers:

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get templates directory path
$templates_path = Paths::views_path();
// Returns: /var/www/html/wp-content/plugins/affiliate-product-showcase/templates/

// Include a specific template
$template = Paths::view_file_path( 'product-card.php' );
if ( file_exists( $template ) ) {
    include $template;
}
```

### REST API URLs

Get REST API endpoint URLs for plugin's custom endpoints:

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get REST API base URL
$base_url = Paths::rest_url();
// Returns: https://example.com/wp-json/affiliate/v1/

// Get specific endpoint
$products_url = Paths::rest_endpoint_url( 'products' );
// Returns: https://example.com/wp-json/affiliate/v1/products
```

### Admin URLs

Generate URLs for plugin admin pages:

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get admin URL for a specific plugin page
$settings_url = Paths::admin_url( 'settings' );
// Returns: https://example.com/wp-admin/admin.php?page=affiliate-product-showcase-settings

// With query arguments
$edit_url = Paths::admin_url( 'products', [ 'action' => 'edit', 'id' => 123 ] );
// Returns: https://example.com/wp-admin/admin.php?page=affiliate-product-showcase-products&action=edit&id=123
```

### Uploads Directory

Get uploads directory information compatible with custom upload directories:

```php
use AffiliateProductShowcase\Helpers\Paths;

// Get uploads directory info
$uploads = Paths::uploads_dir();

// Access paths and URLs
$path = $uploads['path'];   // Filesystem path
$url  = $uploads['url'];    // URL to uploads directory

// Get subdirectory within uploads
$custom_uploads = Paths::uploads_dir( 'affiliate-showcase' );
$path = $custom_uploads['path'];  // .../wp-content/uploads/2026/01/affiliate-showcase
$url  = $custom_uploads['url'];   // https://example.com/wp-content/uploads/2026/01/affiliate-showcase
```

### Versioned URLs

Add plugin version to URLs for cache busting (useful when not using file hash versioning):

```php
use AffiliateProductShowcase\Helpers\Paths;

$url = Paths::versioned_url( 'https://example.com/wp-content/plugins/affiliate-product-showcase/assets/style.css' );
// Returns: https://example.com/.../style.css?ver=1.0.0
```

### Verifying Local URLs

Check if a URL belongs to the current site (important for standalone mode validation):

```php
use AffiliateProductShowcase\Helpers\Paths;

$external_url = 'https://cdn.example.com/style.css';
$local_url    = 'https://example.com/wp-content/plugins/.../style.css';

if ( Paths::is_local_url( $local_url ) ) {
    // URL is local to this site - allowed
}

if ( ! Paths::is_local_url( $external_url ) ) {
    // URL is external - reject for standalone mode
    wp_die( 'External URLs are not allowed in standalone mode' );
}
```

### Enqueuing Assets

When enqueuing CSS and JavaScript files, use path helpers:

```php
function enqueue_assets() {
    // Enqueue compiled CSS
    $css_url = Paths::dist_file_url( 'frontend.abc123.css' );
    wp_enqueue_style(
        'affiliate-showcase-frontend',
        $css_url,
        [],
        null // Use file hash for versioning (already in filename)
    );

    // Enqueue compiled JS
    $js_url = Paths::dist_file_url( 'frontend.def456.js' );
    wp_enqueue_script(
        'affiliate-showcase-frontend',
        $js_url,
        [ 'react', 'react-dom' ],
        null,
        true // Load in footer
    );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
```

### Using Constants

The `Constants` class provides direct access to common paths and URLs:

```php
use AffiliateProductShowcase\Plugin\Constants;

// Get plugin basename
$basename = Constants::basename();
// Returns: affiliate-product-showcase/affiliate-product-showcase.php

// Get plugin directory path
$path = Constants::dirPath();

// Get plugin directory URL
$url = Constants::dirUrl();

// Get asset URL
$asset_url = Constants::assetUrl( 'assets/images/icon.png' );

// Get view path
$template = Constants::viewPath( 'templates/product-card.php' );
```

### Best Practices

1. **Always use path helpers** - Never hardcode paths or URLs
2. **Check file existence** - Verify files exist before including them
3. **Use dist URLs in production** - Reference compiled assets, not source files
4. **Validate external URLs** - Use `is_local_url()` when enforcing standalone mode
5. **Version your assets** - Use file hashes or query parameters for cache busting

---

## Options Management

### Using the Options Helper

The plugin provides a centralized `Options` helper class for retrieving and updating options.

#### Getting Options

```php
use AffiliateProductShowcase\Helpers\Options;

// Get option with default fallback
$dev_mode = Options::get_plugin_option( 'dev_mode', false );

// Get multiple options at once
$options = Options::get_plugin_options(
    [ 'dev_mode', 'debug_mode', 'cache_enabled' ],
    [
        'dev_mode'      => false,
        'debug_mode'    => false,
        'cache_enabled' => true,
    ]
);

// Check if option exists
if ( Options::has_plugin_option( 'custom_setting' ) ) {
    $value = Options::get_plugin_option( 'custom_setting' );
}
```

#### Updating Options

```php
use AffiliateProductShowcase\Helpers\Options;

// Update option
$result = Options::update_plugin_option( 'cache_enabled', true );

// Delete option
$result = Options::delete_plugin_option( 'temp_setting' );
```

#### Convenience Methods

```php
use AffiliateProductShowcase\Helpers\Options;

// Check development mode
if ( Options::is_dev_mode() ) {
    // Development-specific code
}

// Check debug mode
if ( Options::is_debug_mode() ) {
    error_log( 'Debug info: ' . print_r( $data, true ) );
}
```

### Option Priority

The plugin follows this priority order for retrieving option values:

1. **Environment Variable** (if set) - Used in development
2. **WordPress Options Table** - Used in production
3. **Default Value** - Fallback if neither is set

This allows developers to override settings locally via `.env` without affecting production.

### Option Naming Convention

All options are automatically prefixed with the plugin's option prefix to avoid conflicts.

```php
// Internally stored as: affiliate_product_showcase_dev_mode
Options::get_plugin_option( 'dev_mode', false );
```

---

## Security Best Practices

### Never Commit .env Files

The `.env` file is included in `.gitignore` to prevent accidental commits.

**Always verify:**
- `.env` is in `.gitignore`
- No sensitive data in `.env.example`
- No real API keys or credentials in `.env.example`

### API Keys and Credentials

**DO NOT store API keys in:**
- `.env` files
- WordPress Options table (unless encrypted)
- Source code
- Version control

**RECOMMENDED approaches:**

1. **Use WordPress Settings API** for user-configurable keys
   - Store in `wp_options` table
   - User enters via admin interface
   - Sanitize and validate on save

2. **Use constants in wp-config.php** for server-wide keys
   ```php
   // In wp-config.php
   define( 'AFFILIATE_PRODUCT_SHOWCASE_API_KEY', 'your-key-here' );
   ```

3. **Use WordPress transient API** for temporary tokens
   ```php
   set_transient( 'affiliate_product_showcase_token', $token, HOUR_IN_SECONDS );
   $token = get_transient( 'affiliate_product_showcase_token' );
   ```

### Environment Variable Security

When using environment variables in development:

1. **Never commit .env** - Always keep it in `.gitignore`
2. **Use .env.example** - Provide a template with safe defaults
3. **Document variables** - Explain what each variable does
4. **Use type-safe helpers** - Always use `Env::get_bool()`, `Env::get_int()`, etc.
5. **Validate inputs** - Never trust environment variable values implicitly

### Using the Env Helper Safely

```php
use AffiliateProductShowcase\Helpers\Env;

// ✅ GOOD: Use type-safe methods
$debug = Env::get_bool( 'DEBUG_MODE', false );
$port  = Env::get_int( 'REDIS_PORT', 6379 );
$timeout = Env::get_float( 'timeout', 1.5 );

// ✅ GOOD: Validate with callback
$email = Env::get_validated( 'email', 'is_email', 'default@example.com' );

// ✅ GOOD: Check if set before using
if ( Env::has( 'CUSTOM_API_KEY' ) ) {
    $key = Env::get_string( 'CUSTOM_API_KEY' );
}

// ❌ BAD: Direct access without validation
$value = $_ENV['SOME_VAR']; // phpcs:ignore
```

### Input Sanitization and Escaping

Always sanitize user inputs and escape outputs:

```php
// Sanitize input
$email    = sanitize_email( $_POST['email'] );
$username = sanitize_user( $_POST['username'] );
$content  = wp_kses_post( $_POST['content'] );

// Escape output
echo esc_html( $title );
echo esc_url( $url );
echo esc_attr( $attribute_value );
```

---

## Development Workflow

### Setting Up Local Development

1. **Clone the repository:**
   ```bash
   git clone https://github.com/randomfact236/affiliate-product-showcase.git
   cd affiliate-product-showcase
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Set up environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your local settings
   ```

4. **Start Docker containers:**
   ```bash
   cd docker
   docker-compose up -d
   ```

5. **Run development server:**
   ```bash
   npm run dev
   ```

### Code Standards

The plugin follows WordPress Coding Standards:

- **PHP:** PSR-12 + WordPress Coding Standards
- **JavaScript:** ESLint with WordPress preset
- **CSS:** Stylelint with standard rules

Run linters before committing:

```bash
# PHP linting
composer lint

# JavaScript linting
npm run lint

# CSS linting
npm run stylelint
```

### Type Checking

The plugin uses strict types and requires PHP 7.4+:

```php
declare(strict_types=1);

// Always use type hints
function example_function( string $name, int $count ): string {
    return "{$name} ({$count})";
}
```

Run static analysis:

```bash
# PHPStan
composer analyze

# Psalm
composer psalm
```

### Testing

Run the test suite:

```bash
# PHPUnit tests
composer test

# JavaScript tests
npm test
```

---

## Testing Guidelines

### Unit Testing

Write unit tests for all helper functions and utility classes:

```php
class OptionsTest extends \PHPUnit\Framework\TestCase {
    public function test_get_plugin_option_returns_default() {
        $result = Options::get_plugin_option( 'nonexistent', 'default' );
        $this->assertEquals( 'default', $result );
    }
}
```

### Integration Testing

Test integration with WordPress core functions:

```php
class OptionsIntegrationTest extends \WP_UnitTestCase {
    public function test_update_plugin_option_persists() {
        Options::update_plugin_option( 'test_key', 'test_value' );
        $stored = get_option( 'affiliate_product_showcase_test_key' );
        $this->assertEquals( 'test_value', $stored );
    }
}
```

### Testing Environment Variables

When testing environment variable behavior:

1. Use `putenv()` to set test values
2. Clean up after tests
3. Test both environment and options table paths

```php
public function test_env_precedence() {
    putenv( 'PLUGIN_TEST_MODE=true' );
    $result = Options::get_plugin_option( 'test_mode' );
    $this->assertTrue( $result );
    putenv( 'PLUGIN_TEST_MODE' ); // Clean up
}
```

---

## Contributing

### Getting Started

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Make your changes
4. Run tests and linters
5. Commit with conventional commits: `git commit -m "feat: add new feature"`
6. Push to your fork: `git push origin feature/your-feature`
7. Create a pull request

### Commit Message Format

Follow conventional commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

Example:
```
feat(options): add caching support

- Add cache_enabled option
- Implement Redis backend
- Add cache invalidation hooks
```

### Pull Request Guidelines

1. **Clear description** - Explain what and why
2. **Related issues** - Link to related GitHub issues
3. **Tests included** - Add tests for new features
4. **Documentation updated** - Update relevant docs
5. **All checks pass** - Ensure CI checks pass

---

## Additional Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PHP The Right Way](https://phptherightway.com/)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)

---

## Support

For questions or issues:

- **GitHub Issues:** [Report bugs](https://github.com/randomfact236/affiliate-product-showcase/issues)
- **Documentation:** [Main README](../README.md)
- **Changelog:** [CHANGELOG.md](../CHANGELOG.md)

---

*Last updated: January 2026*
*Version: 1.0.0*
