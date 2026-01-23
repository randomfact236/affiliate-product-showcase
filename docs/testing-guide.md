# Testing Guide

## 📋 Purpose

This guide defines **testing standards and practices** for the project.

**Standard:** Enterprise-grade testing (90%+ coverage)
**Philosophy:** Test everything that matters, test thoroughly

---

## 📊 Testing Standards Overview

### Coverage Requirements

| Test Type | Minimum Coverage | Priority |
|------------|-----------------|----------|
| **Unit Tests** | 90%+ | MANDATORY |
| **Integration Tests** | 80%+ | HIGH |
| **E2E Tests** | Critical paths | MANDATORY |
| **Overall Coverage** | 90%+ | MANDATORY |

**Requirements:**
- ✅ All public methods tested
- ✅ All complex logic tested
- ✅ Edge cases covered
- ✅ Error paths tested
- ✅ Success paths tested

---

## 1️⃣ Unit Tests

### PHP Unit Tests

```php
<?php
declare(strict_types=1);

namespace Your\Tests\Services;

use PHPUnit\Framework\TestCase;
use Your\Services\ProductService;
use Your\Repositories\ProductRepository;
use Your\Models\Product;

final class ProductServiceTest extends TestCase {
    private ProductService $service;
    private ProductRepository $repository;

    protected function setUp(): void {
        parent::setUp();
        $this->repository = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repository);
    }

    /** @test */
    public function it_gets_product_successfully(): void {
        // Arrange
        $expected = new Product(1, 'Test', 'test', 'Description', 'USD', 29.99);
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($expected);

        // Act
        $result = $this->service->get_product(1);

        // Assert
        $this->assertSame($expected, $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_id(): void {
        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->service->get_product(0);
    }
}
```

### Unit Test Requirements

- ✅ Test coverage minimum 90% (enterprise-grade requirement)
- ✅ All public methods tested
- ✅ Test name describes behavior
- ✅ Arrange-Act-Assert pattern
- ✅ Mock external dependencies
- ✅ Test success and failure cases

### JavaScript/React Unit Tests

```typescript
import { render, screen } from '@testing-library/react';
import ProductCard from './ProductCard';

describe('ProductCard', () => {
  it('renders product information correctly', () => {
    const product = {
      id: 1,
      title: 'Test Product',
      price: 29.99,
      onAddToCart: jest.fn()
    };

    render(<ProductCard {...product} />);

    expect(screen.getByText('Test Product')).toBeInTheDocument();
    expect(screen.getByText('$29.99')).toBeInTheDocument();
  });

  it('calls onAddToCart when button is clicked', () => {
    const onAddToCart = jest.fn();
    const product = {
      id: 1,
      title: 'Test Product',
      price: 29.99,
      onAddToCart
    };

    render(<ProductCard {...product} />);
    screen.getByRole('button', { name: /add to cart/i }).click();

    expect(onAddToCart).toHaveBeenCalledWith(1);
  });
});
```

---

## 2️⃣ Integration Tests

### PHP Integration Tests

```php
/** @test */
public function it_creates_product_via_api(): void {
    // Arrange
    wp_set_current_user(1); // Admin
    $data = [
        'title' => 'Test Product',
        'price' => 29.99,
        'affiliate_url' => 'https://example.com',
    ];

    // Act
    $response = $this->client->post('/wp-json/your-plugin/v1/products', [
        'json' => $data
    ]);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());
    $body = json_decode($response->getBody(), true);
    $this->assertEquals('Test Product', $body['title']);
    $this->assertArrayHasKey('id', $body);
}
```

### Integration Test Requirements

- ✅ Test API endpoints
- ✅ Test database interactions
- ✅ Test external service integrations
- ✅ Test authentication/authorization
- ✅ Test error handling
- ✅ Clean up test data

---

## 3️⃣ E2E Tests

### E2E Test Examples

```typescript
test('user can add product to cart', async ({ page }) => {
  await page.goto('/products');
  
  await page.click('text=Add to Cart');
  
  await expect(page.locator('.cart-count')).toHaveText('1');
  await expect(page.locator('.notification')).toContainText('Product added');
});

test('user can search for products', async ({ page }) => {
  await page.goto('/products');
  
  await page.fill('input[name="search"]', 'widget');
  await page.press('Enter');
  
  await expect(page.locator('.product-card')).toHaveCount(3);
});
```

### E2E Test Requirements

- ✅ Test critical user journeys
- ✅ Test across browsers (Chrome, Firefox, Safari)
- ✅ Test on mobile devices
- ✅ Test with screen readers
- ✅ Test load handling
- ✅ Use real data (not mocks)

---

## 4️⃣ Visual Regression Testing

### Playwright Visual Tests

```javascript
// Playwright visual regression test
test('product card visual regression', async ({ page }) => {
  await page.goto('/products');
  
  // Take screenshot and compare with baseline
  await expect(page).toHaveScreenshot('product-card.png', {
    maxDiffPixels: 100,
    threshold: 0.2
  });
});
```

### Percy.io Integration

```javascript
// Percy.io integration
test('product page visual regression', async ({ page }) => {
  await page.goto('/products/1');
  
  // Percy takes screenshots across multiple viewports
  await percySnapshot(page, 'Product Page', {
    widths: [375, 768, 1280]
  });
});
```

### Visual Regression Requirements

- ✅ Visual tests for all major UI components
- ✅ Test across multiple viewports (mobile, tablet, desktop)
- ✅ Test across multiple browsers (Chrome, Firefox, Safari)
- ✅ Configure acceptable pixel difference threshold
- ✅ Automated visual regression in CI
- ✅ Baseline images stored in version control
- ✅ Review and approve visual changes before merge

### Tools

- ✅ Playwright screenshot comparison
- ✅ Percy.io or Chromatic for cloud-based visual testing
- ✅ BackstopJS for visual regression testing
- ✅ Applitools for AI-powered visual testing

---

## 5️⃣ Test Data Management

### PHP Test Fixtures

```php
<?php
declare(strict_types=1);

namespace Your\Tests\Fixtures;

use Your\Models\Product;

class ProductFixtures {
    /**
     * Create a test product with predictable data
     */
    public static function create_product(array $overrides = []): Product {
        return new Product(
            $overrides['id'] ?? 1,
            $overrides['title'] ?? 'Test Product',
            $overrides['slug'] ?? 'test-product',
            $overrides['description'] ?? 'Test description',
            $overrides['currency'] ?? 'USD',
            $overrides['price'] ?? 29.99
        );
    }

    /**
     * Create multiple test products
     */
    public static function create_products(int $count = 10): array {
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = self::create_product([
                'id' => $i,
                'title' => "Test Product {$i}",
                'slug' => "test-product-{$i}",
            ]);
        }
        return $products;
    }

    /**
     * Clean up test data after tests
     */
    public static function cleanup(): void {
        // Delete all test products
        $ids = get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'any',
        ]);

        foreach ($ids as $id) {
            wp_delete_post($id, true);
        }

        // Clear caches
        wp_cache_flush();
    }
}
```

### JavaScript Test Fixtures

```javascript
// JavaScript test fixtures for frontend tests
export const productFixtures = {
  createProduct(overrides = {}) {
    return {
      id: overrides.id || 1,
      title: overrides.title || 'Test Product',
      price: overrides.price || 29.99,
      currency: overrides.currency || 'USD',
      description: overrides.description || 'Test description',
      affiliateUrl: overrides.affiliateUrl || 'https://example.com',
      ...overrides
    };
  },

  createProducts(count = 10) {
    return Array.from({ length: count }, (_, i) => 
      this.createProduct({
        id: i + 1,
        title: `Test Product ${i + 1}`
      })
    );
  },

  createProductWithOriginalPrice() {
    return {
      ...this.createProduct(),
      originalPrice: 49.99,
      price: 29.99
    };
  }
};
```

### Test Data Requirements

- ✅ Separate test database from production/development
- ✅ Predictable test data (no random values in tests)
- ✅ Test fixtures for common data structures
- ✅ Clean up test data after each test
- ✅ Isolate tests (no shared state between tests)
- ✅ Use transactions for database tests (rollback after each test)
- ✅ Mock external dependencies (APIs, services)
- ✅ Seed test database with consistent data
- ✅ Version control test fixtures
- ✅ Document fixture data structure

### Best Practices

```php
// ✅ CORRECT: Use transactions and rollback
public function test_create_product() {
    $this->wpdb->query('START TRANSACTION');
    
    try {
        $product = $this->service->create($data);
        $this->assertNotNull($product);
    } finally {
        $this->wpdb->query('ROLLBACK');
    }
}

// ❌ WRONG: No cleanup, pollutes test database
public function test_create_product() {
    $product = $this->service->create($data);
    $this->assertNotNull($product);
    // Product remains in database!
}
```

---

## 6️⃣ Testing Commands

### PHP Testing

```bash
# Run all tests
composer --working-dir=wp-content/plugins/affiliate-product-showcase test

# Run specific test
vendor/bin/phpunit tests/Services/ProductServiceTest

# Run with coverage
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### JavaScript Testing

```bash
# Run all tests
npm --prefix wp-content/plugins/affiliate-product-showcase test

# Run specific test
npm test -- ProductCard.test.js

# Run tests in watch mode
npm test -- --watch

# Run tests with coverage
npm test -- --coverage
```

### Linting

```bash
# PHP linting
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs

# PHP static analysis
vendor/bin/psalm --level=4

# JavaScript linting
npm --prefix wp-content/plugins/affiliate-product-showcase run lint:js
```

---

## 7️⃣ Test Organization

### Directory Structure

```
tests/
├── Unit/              # Unit tests
│   ├── Services/
│   ├── Repositories/
│   └── Models/
├── Integration/      # Integration tests
│   ├── API/
│   └── Database/
├── E2E/             # End-to-end tests
│   ├── user-journeys/
│   └── critical-paths/
├── Fixtures/        # Test data fixtures
│   ├── ProductFixtures.php
│   └── UserFixtures.php
├── bootstrap.php      # Test bootstrap
└── phpunit.xml       # PHPUnit configuration
```

### Naming Conventions

```bash
# PHP test files
ProductServiceTest.php
ProductRepositoryTest.php
ProductModelTest.php

# JavaScript test files
ProductCard.test.js
ProductList.test.js
ApiClient.test.js
```

---

## 🎯 Summary

**For all testing:**
1. **Write testable code** - Use dependency injection, SOLID principles
2. **Test thoroughly** - Cover success, failure, and edge cases
3. **Use fixtures** - Predictable test data, easy maintenance
4. **Clean up properly** - No test pollution, isolated tests
5. **Automate everything** - Unit, integration, E2E in CI
6. **Measure coverage** - Track trends, aim for 90%+
7. **Visual regression** - Catch UI changes before they reach production

**The reward:** Confident releases, fewer bugs, faster development.

---

**Version:** 1.0.0
**Last Updated:** 2026-01-23
**Maintained By:** Development Team
**Status:** ACTIVE - Extracted from assistant-quality-standards.md (~200 lines)
