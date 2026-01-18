# Affiliate Product Showcase - Developer Guide

Welcome to the Affiliate Product Showcase plugin developer guide. This guide provides essential information for developers working with this plugin.

## Table of Contents

- [Environment Configuration](#environment-configuration)
- [Options Management](#options-management)
- [Security Best Practices](#security-best-practices)
- [Development Workflow](#development-workflow)
- [Testing Guidelines](#testing-guidelines)
- [Contributing](#contributing)

---

## Environment Configuration

### .env Files Are Development-Only

**IMPORTANT:** `.env` files are **NOT** used in production. They are strictly for local development and CI environments.

   - Why .env is Dev-Only

1. **Security**: Environment files can accidentally be committed to version control
2. **Portability**: Production servers may not support .env files or may use different configuration mechanisms
3. **Performance**: WordPress Options API is cached and optimized for production use
4. **Standardization**: WordPress plugins should use the WordPress Options API for configuration storage

   - Development Workflow

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

   - Plugin Settings

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_DEV_MODE` | boolean | `false` | Enable development features, disable caching |
| `PLUGIN_DEBUG` | boolean | `false` | Enable detailed logging and error messages |

   - Database Configuration (Optional)

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_DB_HOST` | string | WordPress DB | Custom database host |
| `PLUGIN_DB_NAME` | string | WordPress DB | Custom database name |
| `PLUGIN_DB_USER` | string | WordPress DB | Custom database user |
| `PLUGIN_DB_PASSWORD` | string | WordPress DB | Custom database password |
| `PLUGIN_DB_PREFIX` | string | WordPress prefix | Custom table prefix |

**Note:** If not set, defaults to WordPress database credentials.

   - Redis Configuration (Optional)

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_REDIS_HOST` | string | `127.0.0.1` | Redis server host |
| `PLUGIN_REDIS_PORT` | integer | `6379` | Redis server port |
| `PLUGIN_REDIS_PASSWORD` | string | empty | Redis authentication password |
| `PLUGIN_REDIS_DATABASE` | integer | `0` | Redis database number |
| `PLUGIN_REDIS_TTL` | integer | `3600` | Default TTL (seconds) |

   - Developer Options

| Variable | Type | Default | Description |
|----------|------|---------|-------------|
| `PLUGIN_TEST_MODE` | boolean | `false` | Enable test mode features |
| `PLUGIN_MOCK_API` | boolean | `false` | Use mock API responses |
| `PLUGIN_ENABLE_PROFILING` | boolean | `false` | Enable code profiling |

**WARNING:** Never set these options in production.

---

## Options Management

### Using the Options Helper

The plugin provides a centralized `Options` helper class for retrieving and updating options.

   - Getting Options

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

   - Updating Options

```php
use AffiliateProductShowcase\Helpers\Options;

// Update option
$result = Options::update_plugin_option( 'cache_enabled', true );

// Delete option
$result = Options::delete_plugin_option( 'temp_setting' );
```

   - Convenience Methods

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
