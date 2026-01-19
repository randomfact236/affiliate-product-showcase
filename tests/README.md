# Test Suite for Affiliate Product Showcase

This directory contains the PHPUnit test suite for the Affiliate Product Showcase plugin.

## Test Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   └── ProductTest.php          (10 tests)
│   ├── Services/
│   │   └── ProductValidatorTest.php (26 tests)
│   ├── Security/
│   │   └── CSRFProtectionTest.php   (35 tests)
│   ├── Formatters/
│   │   └── PriceFormatterTest.php   (16 tests)
│   └── Helpers/
│       └── FormatHelperTest.php      (35 tests)
├── bootstrap.php
├── db-seed.php
└── SampleTest.php                   (3 tests - placeholder)
```

## Running Tests

### Run All Tests

```bash
cd wp-content/plugins/affiliate-product-showcase
vendor/bin/phpunit
```

### Run Specific Test File

```bash
vendor/bin/phpunit tests/Unit/Models/ProductTest.php
```

### Run Tests with Coverage Report (Text)

```bash
vendor/bin/phpunit --coverage-text
```

### Run Tests with HTML Coverage Report

```bash
vendor/bin/phpunit --coverage-html coverage-report
```

This will generate a detailed HTML coverage report in the `coverage-report` directory.

### Run Tests with Filter

```bash
vendor/bin/phpunit --filter testMethodName
vendor/bin/phpunit --filter "ProductTest"
vendor/bin/phpunit --filter "testFormatUSD"
```

## Test Coverage Goals

**Current Status:** 122 tests covering major components
**Target:** 90%+ code coverage (required by assistant rules)

### Covered Components

✅ **Models**
- Product model with comprehensive tests for all properties and methods

✅ **Services**
- ProductValidator with extensive validation scenario testing

✅ **Security**
- CSRFProtection with thorough nonce generation and verification tests

✅ **Formatters**
- PriceFormatter with complete currency and price format testing

✅ **Helpers**
- FormatHelper with wide-ranging utility function testing

### Components Needing Coverage

🔄 **Additional Services**
- ProductService
- AffiliateService
- AnalyticsService
- SettingsValidator
- NotificationService

🔄 **Repositories**
- ProductRepository
- AnalyticsRepository
- SettingsRepository

🔄 **Security Components**
- RateLimiter
- PermissionManager
- Validator
- Sanitizer
- AuditLogger

🔄 **Formatters**
- DateFormatter

🔄 **Helpers**
- Options
- Paths
- Logger
- Env

🔄 **REST Controllers**
- ProductsController
- AnalyticsController
- SettingsController
- AffiliatesController
- HealthController

🔄 **Admin Components**
- Menu
- Settings
- MetaBoxes
- Columns
- BulkActions

🔄 **Public Components**
- Shortcodes
- Widgets
- Enqueue

🔄 **Other Components**
- Blocks
- Cache
- Database/Migrations
- Factories
- Privacy/GDPR
- CLI Commands

## Test Writing Guidelines

### Naming Conventions

- **Test File Name:** `{ClassName}Test.php`
- **Test Method Name:** `test{ScenarioName}` in camelCase
- **Namespace:** `AffiliateProductShowcase\Tests\Unit\{Component}`

### Test Structure

```php
<?php
namespace AffiliateProductShowcase\Tests\Unit\Component;

use AffiliateProductShowcase\Component\ClassName;
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase {
    
    private ClassName $instance;

    protected function setUp(): void {
        $this->instance = new ClassName();
    }

    public function testSomeFunctionality() {
        // Arrange
        $input = 'test';
        
        // Act
        $result = $this->instance->method($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Best Practices

1. **One Assertion Per Test** - Keep tests focused and simple
2. **Arrange-Act-Assert Pattern** - Clear test structure
3. **Descriptive Test Names** - Make test names self-explanatory
4. **Test Edge Cases** - Include boundary conditions and error cases
5. **Clean Up** - Reset state in setUp() and tearDown() if needed
6. **Use Type Hints** - Maintain PHP 8.1+ type safety

### Common Assertions

```php
// Equality
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);

// Boolean
$this->assertTrue($condition);
$this->assertFalse($condition);

// Type
$this->assertIsArray($variable);
$this->assertIsString($variable);
$this->assertIsInt($variable);

// String
$this->assertStringContainsString($needle, $haystack);
$this->assertStringStartsWith($prefix, $string);
$this->assertStringEndsWith($suffix, $string);

// Array
$this->assertArrayHasKey($key, $array);
$this->assertContains($needle, $haystack);
$this->assertCount($count, $array);

// Exception
$this->expectException(Exception::class);
```

## Continuous Integration

Tests run automatically on:
- Pull requests (via GitHub Actions)
- Push to main/develop branches

See `.github/workflows/phpunit.yml` for CI configuration.

## Coverage Requirements

Per assistant rules, the plugin must maintain **90%+ code coverage**.

To check current coverage:

```bash
vendor/bin/phpunit --coverage-text
```

To generate detailed coverage report:

```bash
vendor/bin/phpunit --coverage-html coverage-report
open coverage-report/index.html
```

## Troubleshooting

### Tests Not Found

Make sure you're in the correct directory:
```bash
cd wp-content/plugins/affiliate-product-showcase
```

### Missing Dependencies

Install Composer dependencies:
```bash
composer install
```

### WordPress Bootstrap Issues

Ensure `tests/bootstrap.php` is correctly configured to load WordPress environment.

### Database Errors

Run database seed:
```bash
php tests/db-seed.php
```

## Contributing

When adding new features:

1. Write tests first (TDD approach)
2. Ensure all tests pass
3. Maintain or increase coverage percentage
4. Follow naming conventions
5. Add comments for complex test scenarios
6. Update this README if adding new test categories

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Plugin Testing](https://make.wordpress.org/cli/handbook/misc/plugin-unit-testing/)
- [Assistant Instructions](../../docs/assistant-instructions.md)
- [Quality Standards](../../docs/assistant-quality-standards.md)
