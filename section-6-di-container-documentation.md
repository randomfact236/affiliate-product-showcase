# Section 6: Dependency Injection Container & Service Provider Documentation

**Date:** 2026-01-16  
**Component:** Dependency Injection (DI) Architecture  
**Files:** `src/Plugin/Container.php`, `src/Plugin/ServiceProvider.php`

---

## Table of Contents

1. [Overview](#overview)
2. [Dependency Injection Container](#dependency-injection-container)
3. [Service Provider](#service-provider)
4. [Service Registration](#service-registration)
5. [Using the Container](#using-the-container)
6. [Dependency Resolution](#dependency-resolution)
7. [Best Practices](#best-practices)
8. [Examples](#examples)
9. [Testing with DI](#testing-with-di)
10. [Extending the Container](#extending-the-container)

---

## Overview

The plugin uses **League Container** as its dependency injection (DI) container, providing a powerful and flexible way to manage dependencies throughout the application. The DI container implements the **Singleton Pattern** to ensure only one container instance exists per plugin lifecycle.

### Key Benefits

- **Loose Coupling:** Classes depend on interfaces, not concrete implementations
- **Testability:** Easy to mock dependencies in unit tests
- **Automatic Resolution:** Dependencies are automatically resolved via reflection
- **Shared Instances:** Services are shared for performance
- **Centralized Configuration:** All service registrations in one place

### Architecture

```
┌─────────────────────────────────────────────┐
│          Singleton Container              │
│   (Plugin/Container.php)               │
└──────────────┬──────────────────────────┘
               │
               ├─→ Service Provider
               │   (Plugin/ServiceProvider.php)
               │
               ├─→ Reflection Container
               │   (Automatic dependency resolution)
               │
               └─→ Shared Instances
                   (All services registered)
```

---

## Dependency Injection Container

### File: `src/Plugin/Container.php`

The container extends `League\Container\Container` and implements the Singleton pattern.

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use League\Container\Container as BaseContainer;
use League\Container\ReflectionContainer;

/**
 * Dependency Injection Container
 *
 * Extends League\Container to provide a singleton instance for plugin.
 * Uses reflection container for automatic dependency resolution.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 */
final class Container extends BaseContainer {
    private static ?Container $instance = null;

    /**
     * Get singleton instance of container
     *
     * @return Container Container instance
     */
    public static function get_instance(): Container {
        if ( self::$instance === null ) {
            self::$instance = new self();
            self::$instance->addServiceProvider( new ServiceProvider() );
            self::$instance->delegate( new ReflectionContainer() );
        }
        return self::$instance;
    }

    /**
     * Prevent cloning of instance
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Prevent unserializing of instance
     *
     * @return void
     */
    public function __wakeup() {
        throw new \Exception( 'Cannot unserialize singleton' );
    }
}
```

### Key Components

1. **Singleton Pattern:** Only one instance exists per plugin lifecycle
2. **Service Provider:** Registers all services with their dependencies
3. **Reflection Container:** Automatically resolves constructor dependencies
4. **Thread-Safe:** Prevents cloning and unserialization

### Initialization Flow

```
1. Container::get_instance() called
       ↓
2. Check if instance exists
       ↓
3. If null, create new Container
       ↓
4. Add ServiceProvider (register all services)
       ↓
5. Delegate to ReflectionContainer (auto-resolution)
       ↓
6. Return container instance
```

---

## Service Provider

### File: `src/Plugin/ServiceProvider.php`

The service provider implements `League\Container\ServiceProvider\ServiceProviderInterface` and is responsible for registering all services with their dependencies.

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Plugin;

use League\Container\ServiceProvider\ServiceProviderInterface;

/**
 * Service Provider for Dependency Injection Container
 *
 * Registers all services with their dependencies in the container.
 * Uses shared instances where appropriate for performance.
 *
 * @package AffiliateProductShowcase\Plugin
 * @since 1.0.0
 */
final class ServiceProvider implements ServiceProviderInterface {
    /**
     * List of services provided by this provider
     *
     * @return array<string> Service class names
     */
    public function provides( string $id ): bool {
        $services = [
            // Cache
            Cache::class,
            // Repositories
            ProductRepository::class,
            SettingsRepository::class,
            // ... (all services)
        ];

        return in_array( $id, $services );
    }

    /**
     * Register services with container
     *
     * @return void
     */
    public function register(): void {
        // Service registration code here
    }
}
```

### Key Responsibilities

1. **Service Registration:** Register all services with the container
2. **Dependency Configuration:** Define constructor arguments for each service
3. **Shared Instances:** Configure which services should be shared
4. **Service Discovery:** Implement `provides()` method for service discovery

---

## Service Registration

### Registration Methods

The service provider uses two registration methods:

#### 1. `addShared()` - Shared Instances

Used for services that should be shared across the entire request:

```php
$this->getContainer()->addShared( ProductRepository::class );
```

**When to use:**
- Services that maintain state (caching, database connections)
- Services that are expensive to instantiate
- Services that implement singleton pattern internally
- Most services should be shared

#### 2. `add()` - New Instances

Used for services that need fresh instances each time:

```php
$this->getContainer()->add( ProductFactory::class );
```

**When to use:**
- Stateless services
- Services that should not maintain state between calls
- Objects that represent entities (models, DTOs)

### Dependency Configuration

Dependencies are configured using the `addArgument()` method:

```php
$this->getContainer()->addShared( ProductService::class )
    ->addArgument( ProductRepository::class )
    ->addArgument( ProductValidator::class )
    ->addArgument( ProductFactory::class )
    ->addArgument( PriceFormatter::class )
    ->addArgument( Cache::class );
```

This configures the `ProductService` constructor as:

```php
public function __construct(
    ProductRepository $repository,
    ProductValidator $validator,
    ProductFactory $factory,
    PriceFormatter $formatter,
    Cache $cache
) {
    // Constructor implementation
}
```

### Dependency Resolution Order

The container resolves dependencies in the following order:

1. **Explicit Arguments:** Defined via `addArgument()`
2. **Reflection Container:** Automatically resolved via constructor type hints
3. **Default Values:** Used for optional parameters
4. **Shared Instances:** If already registered as shared, use existing instance

---

## Service Registration Breakdown

### Cache Layer (Performance Critical)

```php
$this->getContainer()->addShared( Cache::class );
```

**Purpose:** Caching system for expensive operations
**Dependencies:** None
**Shared:** Yes - Single instance for performance

---

### Repositories (Performance Critical)

```php
$this->getContainer()->addShared( ProductRepository::class );
$this->getContainer()->addShared( SettingsRepository::class );
```

**Purpose:** Data access layer
**Dependencies:** None (uses WordPress database)
**Shared:** Yes - Single instance for performance

---

### Validators (Performance Critical)

```php
$this->getContainer()->addShared( ProductValidator::class );
```

**Purpose:** Input validation
**Dependencies:** None
**Shared:** Yes - Single instance for performance

---

### Factories (Performance Critical)

```php
$this->getContainer()->addShared( ProductFactory::class );
```

**Purpose:** Object creation
**Dependencies:** None
**Shared:** Yes - Single instance for performance

---

### Formatters (Performance Critical)

```php
$this->getContainer()->addShared( PriceFormatter::class );
```

**Purpose:** Data formatting
**Dependencies:** None
**Shared:** Yes - Single instance for performance

---

### Services (Business Logic Layer)

```php
$this->getContainer()->addShared( ProductService::class )
    ->addArgument( ProductRepository::class )
    ->addArgument( ProductValidator::class )
    ->addArgument( ProductFactory::class )
    ->addArgument( PriceFormatter::class )
    ->addArgument( Cache::class );

$this->getContainer()->addShared( AffiliateService::class )
    ->addArgument( SettingsRepository::class );

$this->getContainer()->addShared( AnalyticsService::class )
    ->addArgument( Cache::class );
```

**Purpose:** Business logic layer
**Dependencies:** Repositories, validators, factories, formatters, cache
**Shared:** Yes - Single instance for request scope

---

### Assets (Performance Critical)

```php
$this->getContainer()->addShared( Manifest::class );
$this->getContainer()->addShared( SRI::class )
    ->addArgument( Manifest::class );
$this->getContainer()->addShared( Assets::class )
    ->addArgument( Manifest::class );
```

**Purpose:** Asset management with manifest and SRI
**Dependencies:** Manifest for SRI and Assets
**Shared:** Yes - Single instance for performance

---

### Security (Performance Critical)

```php
$this->getContainer()->addShared( Headers::class );
```

**Purpose:** Security headers
**Dependencies:** None
**Shared:** Yes - Single instance for performance

---

### Admin (Request Scope)

```php
$this->getContainer()->addShared( Settings::class );
$this->getContainer()->addShared( Admin::class )
    ->addArgument( Assets::class )
    ->addArgument( ProductService::class )
    ->addArgument( Headers::class );
```

**Purpose:** WordPress admin interface
**Dependencies:** Assets, ProductService, Headers
**Shared:** Yes - Single instance for request scope

---

### Public (Request Scope)

```php
$this->getContainer()->addShared( Public_::class )
    ->addArgument( Assets::class )
    ->addArgument( ProductService::class );
```

**Purpose:** Public-facing features
**Dependencies:** Assets, ProductService
**Shared:** Yes - Single instance for request scope

---

### Blocks (Request Scope)

```php
$this->getContainer()->addShared( Blocks::class )
    ->addArgument( ProductService::class );
```

**Purpose:** Gutenberg blocks
**Dependencies:** ProductService
**Shared:** Yes - Single instance for request scope

---

### REST Controllers (Request Scope)

```php
$this->getContainer()->addShared( ProductsController::class )
    ->addArgument( ProductService::class );

$this->getContainer()->addShared( AnalyticsController::class )
    ->addArgument( AnalyticsService::class );

$this->getContainer()->addShared( HealthController::class );
```

**Purpose:** REST API endpoints
**Dependencies:** Services (ProductService, AnalyticsService)
**Shared:** Yes - Single instance for request scope

---

### CLI (Request Scope)

```php
$this->getContainer()->addShared( ProductsCommand::class )
    ->addArgument( ProductService::class );
```

**Purpose:** WP-CLI commands
**Dependencies:** ProductService
**Shared:** Yes - Single instance for request scope

---

### Privacy (Request Scope)

```php
$this->getContainer()->addShared( GDPR::class );
```

**Purpose:** GDPR compliance
**Dependencies:** None
**Shared:** Yes - Single instance for request scope

---

## Using the Container

### Getting Services

To retrieve a service from the container:

```php
use AffiliateProductShowcase\Plugin\Container;
use AffiliateProductShowcase\Services\ProductService;

// Get the container instance
$container = Container::get_instance();

// Get a service (shared instance)
$productService = $container->get( ProductService::class );

// Use the service
$products = $productService->get_all_products();
```

### Dependency Injection in Constructors

When a class needs a service, define it as a constructor parameter:

```php
<?php
namespace AffiliateProductShowcase\Admin;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Assets\Assets;

class Admin {
    private ProductService $productService;
    private Assets $assets;

    public function __construct(
        ProductService $productService,
        Assets $assets
    ) {
        $this->productService = $productService;
        $this->assets = $assets;
    }

    public function init(): void {
        // Use injected services
        $this->assets->enqueue_admin_assets();
    }
}
```

### Automatic Resolution

The container automatically resolves dependencies:

```php
// Admin class requires ProductService and Assets
// Container automatically resolves both dependencies
$admin = $container->get( Admin::class );
```

The container will:
1. Instantiate `ProductService` (shared instance)
2. Instantiate `Assets` (shared instance)
3. Inject both into `Admin` constructor
4. Return `Admin` instance

---

## Dependency Resolution

### Constructor Injection

The preferred method is constructor injection:

```php
class MyController {
    private ProductService $productService;
    private Cache $cache;

    public function __construct(
        ProductService $productService,
        Cache $cache
    ) {
        $this->productService = $productService;
        $this->cache = $cache;
    }
}
```

### Property Injection (Not Recommended)

Avoid property injection for better testability:

```php
// ❌ AVOID: Property injection
class MyController {
    public ProductService $productService;
}
```

### Setter Injection (Not Recommended)

Avoid setter injection for better immutability:

```php
// ❌ AVOID: Setter injection
class MyController {
    private ProductService $productService;

    public function setProductService( ProductService $service ): void {
        $this->productService = $service;
    }
}
```

### Interface-Based Injection

For better testability, inject interfaces:

```php
interface RepositoryInterface {
    public function find( int $id ): ?Product;
}

class ProductRepository implements RepositoryInterface {
    // Implementation
}

class ProductService {
    private RepositoryInterface $repository;

    public function __construct( RepositoryInterface $repository ) {
        $this->repository = $repository;
    }
}

// Register concrete implementation
$container->addShared( RepositoryInterface::class, ProductRepository::class );
```

---

## Best Practices

### 1. Use Constructor Injection

✅ **Recommended:**

```php
class MyClass {
    private Dependency $dependency;

    public function __construct( Dependency $dependency ) {
        $this->dependency = $dependency;
    }
}
```

❌ **Avoid:**

```php
class MyClass {
    public function __construct() {
        $this->dependency = Container::get_instance()->get( Dependency::class );
    }
}
```

### 2. Register Services as Shared

Most services should be shared for performance:

```php
// ✅ Recommended: Shared
$container->addShared( ProductService::class );

// ❌ Avoid: New instance each time (unless necessary)
$container->add( ProductService::class );
```

### 3. Use Type Hints for Auto-Resolution

Let the container resolve dependencies automatically:

```php
// ✅ Recommended: Auto-resolution
$container->addShared( MyService::class );

// Container will automatically resolve dependencies in constructor

// ❌ Avoid: Manual argument configuration (unless needed)
$container->addShared( MyService::class )
    ->addArgument( Dependency1::class )
    ->addArgument( Dependency2::class );
```

### 4. Use Interfaces for Contracts

Define interfaces for services:

```php
interface ServiceInterface {
    public function execute(): void;
}

class MyService implements ServiceInterface {
    // Implementation
}

// Register interface
$container->addShared( ServiceInterface::class, MyService::class );
```

### 5. Keep Service Provider Organized

Organize service registrations logically:

```php
// Cache Layer
$this->getContainer()->addShared( Cache::class );

// Repositories
$this->getContainer()->addShared( ProductRepository::class );
$this->getContainer()->addShared( SettingsRepository::class );

// Services
$this->getContainer()->addShared( ProductService::class )
    ->addArgument( ProductRepository::class );
```

### 6. Avoid Circular Dependencies

Be careful of circular dependencies:

```php
// ❌ AVOID: Circular dependency
class ServiceA {
    public function __construct( ServiceB $serviceB ) {}
}

class ServiceB {
    public function __construct( ServiceA $serviceA ) {}
}

// ✅ FIX: Use events or refactor
class ServiceA {
    public function __construct( EventDispatcher $dispatcher ) {
        $dispatcher->listen( 'event', [ $this, 'handle' ] );
    }
}
```

---

## Examples

### Example 1: Getting a Service

```php
use AffiliateProductShowcase\Plugin\Container;
use AffiliateProductShowcase\Services\ProductService;

// Get container
$container = Container::get_instance();

// Get product service
$productService = $container->get( ProductService::class );

// Get products
$products = $productService->get_all_products();
```

### Example 2: Custom Service Registration

Add a new service to the service provider:

```php
// In ServiceProvider.php::register()

$this->getContainer()->addShared( MyCustomService::class )
    ->addArgument( ProductRepository::class )
    ->addArgument( Cache::class );
```

### Example 3: Using Services in a Class

```php
<?php
namespace AffiliateProductShowcase\MyFeature;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Cache\Cache;

class MyFeature {
    private ProductService $productService;
    private Cache $cache;

    public function __construct(
        ProductService $productService,
        Cache $cache
    ) {
        $this->productService = $productService;
        $this->cache = $cache;
    }

    public function get_featured_products(): array {
        $cache_key = 'featured_products';
        
        // Check cache first
        $products = $this->cache->get( $cache_key );
        
        if ( $products === null ) {
            // Fetch from service
            $products = $this->productService->get_featured_products();
            
            // Cache for 1 hour
            $this->cache->set( $cache_key, $products, 3600 );
        }
        
        return $products;
    }
}
```

### Example 4: Registering Your Feature

Add to service provider:

```php
// In ServiceProvider.php::register()

$this->getContainer()->addShared( MyFeature::class )
    ->addArgument( ProductService::class )
    ->addArgument( Cache::class );
```

### Example 5: Using Your Feature

```php
use AffiliateProductShowcase\Plugin\Container;
use AffiliateProductShowcase\MyFeature\MyFeature;

$container = Container::get_instance();
$feature = $container->get( MyFeature::class );

$products = $feature->get_featured_products();
```

---

## Testing with DI

### Unit Testing with Mocked Dependencies

```php
<?php
namespace AffiliateProductShowcase\Tests\Unit;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Repositories\ProductRepository;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase {
    private ProductService $productService;
    private ProductRepository $mockRepository;

    protected function setUp(): void {
        // Create mock repository
        $this->mockRepository = $this->createMock( ProductRepository::class );
        
        // Create service with mocked dependency
        $this->productService = new ProductService(
            $this->mockRepository,
            // ... other dependencies
        );
    }

    public function test_get_product_by_id(): void {
        // Set up mock expectation
        $this->mockRepository
            ->expects( $this->once() )
            ->method( 'find' )
            ->with( 1 )
            ->willReturn( $this->createMockProduct() );

        // Test
        $product = $this->productService->get_product_by_id( 1 );
        
        $this->assertNotNull( $product );
    }
}
```

### Integration Testing with Real Container

```php
<?php
namespace AffiliateProductShowcase\Tests\Integration;

use AffiliateProductShowcase\Plugin\Container;
use PHPUnit\Framework\TestCase;

class ContainerIntegrationTest extends TestCase {
    public function test_container_resolves_dependencies(): void {
        // Get container
        $container = Container::get_instance();
        
        // Get service
        $productService = $container->get( \AffiliateProductShowcase\Services\ProductService::class );
        
        // Assert service is instance
        $this->assertInstanceOf( 
            \AffiliateProductShowcase\Services\ProductService::class,
            $productService
        );
        
        // Assert service works
        $products = $productService->get_all_products();
        $this->assertIsArray( $products );
    }
}
```

---

## Extending the Container

### Adding a New Service

1. **Create the Service Class**

```php
<?php
namespace AffiliateProductShowcase\Services;

class MyNewService {
    public function do_something(): string {
        return 'Hello, World!';
    }
}
```

2. **Add to Service Provider**

```php
// In ServiceProvider.php::register()

$this->getContainer()->addShared( MyNewService::class );
```

3. **Use the Service**

```php
$container = Container::get_instance();
$service = $container->get( MyNewService::class );
$result = $service->do_something();
```

### Adding a Service with Dependencies

1. **Create the Service Class**

```php
<?php
namespace AffiliateProductShowcase\Services;

class MyNewService {
    private ProductService $productService;
    private Cache $cache;

    public function __construct(
        ProductService $productService,
        Cache $cache
    ) {
        $this->productService = $productService;
        $this->cache = $cache;
    }

    public function get_cached_products(): array {
        return $this->productService->get_all_products();
    }
}
```

2. **Add to Service Provider**

```php
// In ServiceProvider.php::register()

$this->getContainer()->addShared( MyNewService::class )
    ->addArgument( ProductService::class )
    ->addArgument( Cache::class );
```

3. **Update provides() Method**

```php
// In ServiceProvider.php::provides()

public function provides( string $id ): bool {
    $services = [
        // ... existing services
        MyNewService::class,
    ];

    return in_array( $id, $services );
}
```

### Adding a New Repository

1. **Create Repository Interface**

```php
<?php
namespace AffiliateProductShowcase\Repositories;

interface MyRepositoryInterface {
    public function find( int $id ): ?array;
    public function all(): array;
}
```

2. **Create Repository Implementation**

```php
<?php
namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Repositories\MyRepositoryInterface;

class MyRepository implements MyRepositoryInterface {
    public function find( int $id ): ?array {
        // Implementation
        return null;
    }

    public function all(): array {
        // Implementation
        return [];
    }
}
```

3. **Add to Service Provider**

```php
// In ServiceProvider.php::register()

$this->getContainer()->addShared( MyRepositoryInterface::class, MyRepository::class );
```

---

## Performance Considerations

### Shared Instances vs. New Instances

**Shared Instances (Recommended for most services):**
- Pros: Better performance, less memory usage
- Cons: Maintains state, potential side effects
- Use for: Services, repositories, caches, factories

**New Instances (Use sparingly):**
- Pros: Fresh state, no side effects
- Cons: Poorer performance, more memory usage
- Use for: Stateless utilities, data objects

### Container Resolution Caching

The container caches resolved services:

```php
// First call: Creates instance
$service1 = $container->get( ProductService::class );

// Second call: Returns cached instance
$service2 = $container->get( ProductService::class );

// Both point to same instance
$this->assertSame( $service1, $service2 );
```

### Lazy Loading

Services are only instantiated when first requested:

```php
// Container created, but services not instantiated yet
$container = Container::get_instance();

// ProductService instantiated on first access
$productService = $container->get( ProductService::class );
```

---

## Security Considerations

### Singleton Pattern Enforcement

The container prevents cloning and unserialization:

```php
// Prevent cloning
private function __clone() {}

// Prevent unserialization
public function __wakeup() {
    throw new \Exception( 'Cannot unserialize singleton' );
}
```

### Dependency Injection Security

- **Type Safety:** Constructor type hints prevent injection of wrong types
- **Immutable Dependencies:** Constructor injection prevents runtime changes
- **Explicit Dependencies:** All dependencies are declared in constructor

---

## Troubleshooting

### Issue: Service Not Found

**Error:** `Class "SomeService" not found`

**Solution:**
1. Check if service is registered in `ServiceProvider.php`
2. Check if service is listed in `provides()` method
3. Verify namespace and class name are correct

### Issue: Circular Dependency

**Error:** `Circular dependency detected`

**Solution:**
1. Review dependency graph
2. Use event dispatcher to break circular dependencies
3. Refactor to remove circular dependency

### Issue: Wrong Class Injected

**Error:** Unexpected class type injected

**Solution:**
1. Check service registration
2. Verify interface implementations
3. Check for multiple registrations of same interface

---

## Summary

### Key Takeaways

1. **Singleton Pattern:** Container is a singleton for the plugin
2. **Service Provider:** Registers all services with dependencies
3. **Shared Instances:** Most services are shared for performance
4. **Auto-Resolution:** Dependencies automatically resolved via reflection
5. **Constructor Injection:** Preferred method for dependency injection
6. **Testability:** Easy to mock dependencies in tests

### Best Practices

- ✅ Use constructor injection
- ✅ Register services as shared
- ✅ Use type hints for auto-resolution
- ✅ Define interfaces for contracts
- ✅ Keep service provider organized
- ✅ Avoid circular dependencies

### Resources

- **League Container Documentation:** https://container.thephpleague.com/
- **PSR-11 Container Interface:** https://www.php-fig.org/psr/psr-11/
- **Dependency Injection Patterns:** https://en.wikipedia.org/wiki/Dependency_injection

---

## Related Files

- `src/Plugin/Container.php` - DI container implementation
- `src/Plugin/ServiceProvider.php` - Service provider implementation
- `src/Plugin/Loader.php` - Hooks and filters loader
- `src/Services/*` - Business logic services
- `src/Repositories/*` - Data access repositories
