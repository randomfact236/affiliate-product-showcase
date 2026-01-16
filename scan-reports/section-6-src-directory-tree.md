# Section 6: src/ Directory Visual Tree

**Date:** 2026-01-16  
**Directory:** wp-content/plugins/affiliate-product-showcase/src/  
**Total Files:** 90 PHP files across 25 subdirectories

---

## Complete Directory Tree

```
src/
├── 📄 index.php                          # Source entry point
│
├── 📁 Abstracts/                         # Abstract base classes
│   ├── 📄 index.php
│   ├── 📄 AbstractRepository.php         # Repository base class
│   ├── 📄 AbstractService.php            # Service base class
│   └── 📄 AbstractValidator.php         # Validator base class
│
├── 📁 Admin/                            # WordPress admin interface
│   ├── 📄 index.php
│   ├── 📄 Admin.php                     # Main admin class
│   ├── 📄 BulkActions.php               # Bulk operations handler
│   ├── 📄 Columns.php                   # Admin column management
│   ├── 📄 Enqueue.php                   # Admin asset loading
│   ├── 📄 Menu.php                      # Admin menu creation
│   ├── 📄 MetaBoxes.php                 # Meta box registration
│   ├── 📄 Settings.php                  # Settings page handling
│   │
│   └── 📁 partials/                     # Admin view templates
│       ├── 📄 index.php
│       ├── 📄 dashboard-widget.php        # Dashboard widget template
│       ├── 📄 product-meta-box.php       # Product meta box template
│       └── 📄 settings-page.php          # Settings page template
│
├── 📁 Assets/                           # Asset management
│   ├── 📄 index.php
│   ├── 📄 Assets.php                    # Main asset loading class
│   ├── 📄 Manifest.php                  # Asset manifest management
│   └── 📄 SRI.php                      # Subresource Integrity management
│
├── 📁 Blocks/                           # Gutenberg block registration
│   ├── 📄 index.php
│   ├── 📄 Blocks.php                    # Block registration class
│   │
│   ├── 📁 templates/                     # Block templates
│   │   ├── 📄 product-grid-item.php     # Product grid item template
│   │   └── 📄 product-showcase-item.php # Product showcase item template
│   │
│   └── 📁 product-showcase/              # Product showcase block
│       └── 📄 index.php
│
├── 📁 Cache/                            # Caching system
│   ├── 📄 index.php
│   └── 📄 Cache.php                     # Main caching class
│
├── 📁 Cli/                              # WP-CLI commands
│   ├── 📄 index.php
│   └── 📄 ProductsCommand.php           # Products management command
│
├── 📁 Database/                         # Database operations
│   ├── 📄 index.php
│   ├── 📄 Database.php                   # Database manager class
│   ├── 📄 Migrations.php                 # Migration system
│   │
│   └── 📁 seeders/                      # Database seeders
│       └── 📄 sample-products.php       # Sample product seeder
│
├── 📁 Events/                           # Event-driven architecture
│   ├── 📄 index.php
│   ├── 📄 EventDispatcher.php           # Event dispatcher implementation
│   └── 📄 EventDispatcherInterface.php    # Event dispatcher interface
│
├── 📁 Exceptions/                        # Custom exceptions
│   ├── 📄 index.php
│   ├── 📄 PluginException.php           # General plugin exception
│   └── 📄 RepositoryException.php       # Repository-specific exception
│
├── 📁 Factories/                         # Factory pattern
│   ├── 📄 index.php
│   └── 📄 ProductFactory.php             # Product object factory
│
├── 📁 Formatters/                        # Data formatting utilities
│   ├── 📄 index.php
│   ├── 📄 DateFormatter.php             # Date/time formatting
│   └── 📄 PriceFormatter.php            # Price/currency formatting
│
├── 📁 Frontend/                          # Frontend logic
│   ├── 📄 index.php
│   │
│   └── 📁 partials/                     # Frontend view templates
│       └── 📄 index.php
│
├── 📁 Helpers/                           # Utility functions
│   ├── 📄 index.php
│   ├── 📄 Env.php                       # Environment helper
│   ├── 📄 FormatHelper.php              # Formatting helper
│   ├── 📄 helpers.php                   # Global helper functions
│   ├── 📄 Logger.php                    # Logging helper
│   ├── 📄 Options.php                   # WordPress options helper
│   └── 📄 Paths.php                    # File path helper
│
├── 📁 Interfaces/                       # Interface definitions
│   ├── 📄 index.php
│   ├── 📄 RepositoryInterface.php        # Repository contract
│   └── 📄 ServiceInterface.php          # Service contract
│
├── 📁 Models/                            # Data models
│   ├── 📄 index.php
│   ├── 📄 AffiliateLink.php             # Affiliate link model
│   └── 📄 Product.php                   # Product model
│
├── 📁 Plugin/                            # Core plugin functionality
│   ├── 📄 index.php
│   ├── 📄 Activator.php                 # Plugin activation handler
│   ├── 📄 Constants.php                 # Plugin constants
│   ├── 📄 Container.php                 # DI container (singleton)
│   ├── 📄 Deactivator.php               # Plugin deactivation handler
│   ├── 📄 Loader.php                    # Hooks/filters loader
│   ├── 📄 Plugin.php                    # Main plugin class
│   └── 📄 ServiceProvider.php            # Service provider for DI
│
├── 📁 Privacy/                           # GDPR/privacy compliance
│   ├── 📄 index.php
│   └── 📄 GDPR.php                      # GDPR implementation
│
├── 📁 Public/                           # Public interface
│   ├── 📄 index.php
│   ├── 📄 Enqueue.php                   # Public asset loading
│   ├── 📄 Public_.php                   # Main public class
│   ├── 📄 Shortcodes.php                # Shortcode registration
│   ├── 📄 Widgets.php                   # Widget registration
│   │
│   └── 📁 partials/                     # Frontend view templates
│       ├── 📄 index.php
│       ├── 📄 product-card.php           # Product card template
│       ├── 📄 product-grid.php           # Product grid template
│       └── 📄 single-product.php        # Single product template
│
├── 📁 Repositories/                     # Data access layer
│   ├── 📄 index.php
│   ├── 📄 AnalyticsRepository.php       # Analytics data access
│   ├── 📄 ProductRepository.php        # Product data access
│   └── 📄 SettingsRepository.php       # Settings data access
│
├── 📁 Rest/                             # REST API
│   ├── 📄 index.php
│   ├── 📄 AffiliatesController.php      # Affiliate links endpoint
│   ├── 📄 AnalyticsController.php       # Analytics endpoint
│   ├── 📄 HealthController.php         # Health check endpoint
│   ├── 📄 ProductsController.php        # Products endpoint
│   ├── 📄 RestController.php           # Base REST controller
│   └── 📄 SettingsController.php       # Settings endpoint
│
├── 📁 Sanitizers/                       # Input sanitization
│   ├── 📄 index.php
│   └── 📄 InputSanitizer.php           # Input sanitization class
│
├── 📁 Security/                         # Security features
│   ├── 📄 index.php
│   ├── 📄 AuditLogger.php               # Security audit logging
│   ├── 📄 CSRFProtection.php           # CSRF protection
│   ├── 📄 Headers.php                  # Security headers
│   ├── 📄 PermissionManager.php        # Permission management
│   ├── 📄 RateLimiter.php              # Rate limiting
│   ├── 📄 Sanitizer.php                # Data sanitization
│   └── 📄 Validator.php                # Input validation
│
├── 📁 Services/                         # Business logic layer
│   ├── 📄 index.php
│   ├── 📄 AffiliateService.php          # Affiliate link service
│   ├── 📄 AnalyticsService.php          # Analytics service
│   ├── 📄 NotificationService.php      # Notification service
│   ├── 📄 ProductService.php            # Product service
│   └── 📄 ProductValidator.php          # Product validation
│
├── 📁 Traits/                           # Reusable code snippets
│   ├── 📄 index.php
│   ├── 📄 HooksTrait.php                # Hooks management trait
│   └── 📄 SingletonTrait.php            # Singleton pattern trait
│
└── 📁 Validators/                       # Input validation logic
    ├── 📄 index.php
    └── 📄 ProductValidator.php          # Product validation
```

---

## Directory Statistics

| Directory | Files | Subdirectories | Purpose |
|-----------|-------|----------------|----------|
| Abstracts/ | 4 | 0 | Base classes for common patterns |
| Admin/ | 11 | 1 | WordPress admin interface |
| Assets/ | 4 | 0 | Asset management system |
| Blocks/ | 5 | 2 | Gutenberg block registration |
| Cache/ | 2 | 0 | Caching implementation |
| Cli/ | 2 | 0 | WP-CLI commands |
| Database/ | 4 | 1 | Database operations & migrations |
| Events/ | 3 | 0 | Event system |
| Exceptions/ | 3 | 0 | Custom exceptions |
| Factories/ | 2 | 0 | Factory pattern |
| Formatters/ | 3 | 0 | Data formatting |
| Frontend/ | 2 | 1 | Frontend logic |
| Helpers/ | 7 | 0 | Utility functions |
| Interfaces/ | 3 | 0 | Interface definitions |
| Models/ | 3 | 0 | Data models |
| Plugin/ | 8 | 0 | Core plugin functionality |
| Privacy/ | 2 | 0 | GDPR compliance |
| Public/ | 7 | 1 | Public interface |
| Repositories/ | 4 | 0 | Data access layer |
| Rest/ | 7 | 0 | REST API endpoints |
| Sanitizers/ | 2 | 0 | Input sanitization |
| Security/ | 9 | 0 | Security features |
| Services/ | 6 | 0 | Business logic |
| Traits/ | 3 | 0 | Reusable traits |
| Validators/ | 2 | 0 | Input validation |
| **Total** | **90** | **5** | **25 directories** |

---

## Architecture Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                     Presentation Layer                        │
├─────────────────────────────────────────────────────────────────┤
│  Admin/           Public/           Frontend/           Blocks/  │
│  - Admin.php       - Public_.php      - Frontend/       - Blocks.php  │
│  - Settings.php    - Shortcodes.php                      - templates/  │
│  - MetaBoxes.php   - Widgets.php                           │
│  - partials/       - partials/                             │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      Service Layer                           │
├─────────────────────────────────────────────────────────────────┤
│  Services/            Formatters/       Factories/              │
│  - ProductService   - DateFormatter  - ProductFactory          │
│  - AffiliateService - PriceFormatter                          │
│  - AnalyticsService                                         │
│  - NotificationService                                       │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    Data Access Layer                          │
├─────────────────────────────────────────────────────────────────┤
│  Repositories/         Models/        Abstracts/                │
│  - ProductRepository   - Product.php   - AbstractRepository    │
│  - SettingsRepository - AffiliateLink.php - AbstractService     │
│  - AnalyticsRepository                - AbstractValidator       │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                   Infrastructure Layer                        │
├─────────────────────────────────────────────────────────────────┤
│  Plugin/              Cache/          Database/                 │
│  - Container.php      - Cache.php     - Database.php           │
│  - ServiceProvider.php               - Migrations.php          │
│  - Loader.php        - Events/       - seeders/               │
│  - Activator.php      - EventDispatcher.php                  │
│  - Deactivator.php   - EventDispatcherInterface.php          │
│                                                        │
│  Assets/             Security/       Helpers/                  │
│  - Assets.php         - AuditLogger.php - Logger.php            │
│  - Manifest.php       - CSRFProtection.php - Options.php         │
│  - SRI.php           - Headers.php    - Paths.php             │
│                     - RateLimiter.php - Env.php               │
│                     - PermissionManager.php - FormatHelper.php   │
│                     - Sanitizer.php   - helpers.php          │
│                     - Validator.php                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Key Architectural Components

### Core Plugin Infrastructure
- **Plugin/Container.php** - Dependency injection container (singleton)
- **Plugin/ServiceProvider.php** - Service registration for DI
- **Plugin/Loader.php** - Hooks and filters loader
- **Plugin/Activator.php** - Plugin activation
- **Plugin/Deactivator.php** - Plugin deactivation

### Data Layer
- **Repositories/** - Data access abstraction (3 repositories)
- **Models/** - Data models (2 models)
- **Database/** - Database operations and migrations
- **Factories/** - Object creation patterns

### Business Logic
- **Services/** - Business logic layer (5 services)
- **Formatters/** - Data formatting utilities (2 formatters)
- **Validators/** - Input validation (2 validators)
- **Abstracts/** - Base classes for common patterns

### Presentation
- **Admin/** - WordPress admin interface (11 files)
- **Public/** - Public-facing features (7 files)
- **Frontend/** - Frontend logic and templates
- **Blocks/** - Gutenberg block registration (5 files)
- **Rest/** - REST API endpoints (6 controllers)

### Cross-Cutting Concerns
- **Security/** - Comprehensive security suite (9 files)
- **Assets/** - Asset management with SRI (4 files)
- **Cache/** - Caching implementation (2 files)
- **Events/** - Event-driven architecture (3 files)
- **Helpers/** - Utility functions (7 files)
- **Traits/** - Reusable code snippets (3 files)
- **Exceptions/** - Custom exceptions (3 files)
- **Interfaces/** - Contract definitions (3 interfaces)
- **Sanitizers/** - Input sanitization (2 files)
- **Privacy/** - GDPR compliance (2 files)
- **Cli/** - WP-CLI integration (2 files)

---

## Design Patterns Used

### 1. Dependency Injection (DI)
- **Location:** `Plugin/Container.php`, `Plugin/ServiceProvider.php`
- **Purpose:** Loose coupling, testability, easy dependency management
- **Implementation:** League Container with reflection for auto-resolution

### 2. Repository Pattern
- **Location:** `Repositories/`, `Abstracts/AbstractRepository.php`
- **Purpose:** Data access abstraction, easy testing
- **Implementations:** ProductRepository, SettingsRepository, AnalyticsRepository

### 3. Service Layer
- **Location:** `Services/`, `Abstracts/AbstractService.php`
- **Purpose:** Business logic separation, reusable logic
- **Implementations:** ProductService, AffiliateService, AnalyticsService

### 4. Factory Pattern
- **Location:** `Factories/`
- **Purpose:** Object creation abstraction
- **Implementations:** ProductFactory

### 5. Event Dispatcher
- **Location:** `Events/`
- **Purpose:** Loose coupling, extensibility
- **Components:** EventDispatcher, EventDispatcherInterface

### 6. Singleton Pattern
- **Location:** `Plugin/Container.php`, `Traits/SingletonTrait.php`
- **Purpose:** Single instance management
- **Implementations:** DI container, shared services

### 7. Strategy Pattern
- **Location:** `Formatters/`
- **Purpose:** Interchangeable formatting algorithms
- **Implementations:** DateFormatter, PriceFormatter

---

## File Organization Principles

### 1. Single Responsibility
Each directory has a clear, focused purpose:
- `Repositories/` - Data access only
- `Services/` - Business logic only
- `Formatters/` - Data formatting only

### 2. Separation of Concerns
- **Presentation** (Admin, Public, Frontend, Blocks, Rest)
- **Business Logic** (Services, Formatters, Validators)
- **Data Access** (Repositories, Models, Database)
- **Infrastructure** (Plugin, Assets, Cache, Security, Events)

### 3. Dependency Flow
```
Presentation → Service Layer → Data Access → Infrastructure
```

### 4. Interface Segregation
- `Interfaces/RepositoryInterface.php` - Repository contract
- `Interfaces/ServiceInterface.php` - Service contract
- `Events/EventDispatcherInterface.php` - Event dispatcher contract

### 5. Open/Closed Principle
- Abstract base classes (`AbstractRepository`, `AbstractService`)
- Interfaces for contracts
- Easy to extend without modifying existing code

---

## Integration Points

### WordPress Integration
- **Admin/** - `admin_menu`, `admin_enqueue_scripts`, `add_meta_box`
- **Public/** - `wp_enqueue_scripts`, `shortcode`, `widgets_init`
- **Rest/** - REST API endpoints
- **Cli/** - WP-CLI commands
- **Plugin/** - Plugin activation/deactivation hooks

### Frontend Integration
- **Frontend/** - Frontend templates and logic
- **Blocks/** - Gutenberg block rendering
- **Assets/** - Asset loading (JS/CSS) with manifest
- **Public/partials/** - Template files

### Security Integration
- **Security/** - Security headers, CSRF protection, rate limiting
- **Sanitizers/** - Input sanitization
- **Validators/** - Input validation
- **Privacy/GDPR.php** - GDPR compliance

### Database Integration
- **Database/** - Database operations and migrations
- **Repositories/** - Data access layer
- **Models/** - Data models
- **Database/seeders/** - Database seeding

---

## Extensibility Points

### 1. Event System
- Add event listeners via `Events/EventDispatcher`
- Custom events can be dispatched throughout the plugin

### 2. Service Registration
- Add new services in `Plugin/ServiceProvider.php`
- Automatic dependency injection via DI container

### 3. Repository Pattern
- Add new repositories extending `AbstractRepository.php`
- Implement `RepositoryInterface` for consistency

### 4. Service Layer
- Add new services extending `AbstractService.php`
- Implement `ServiceInterface` for consistency

### 5. REST API
- Add new controllers extending `RestController.php`
- Register routes via WordPress REST API

### 6. Gutenberg Blocks
- Add new blocks in `Blocks/` directory
- Register via `Blocks/Blocks.php`

### 7. WP-CLI Commands
- Add new commands in `Cli/` directory
- Register via WP-CLI API

---

## Testing Support

### Unit Testing
- All services are testable via DI container
- Repositories can be mocked via interfaces
- Abstract base classes provide common test patterns

### Integration Testing
- Database operations via `Database/`
- REST API endpoints via `Rest/`
- WP-CLI commands via `Cli/`

### Test Fixtures
- `Database/seeders/sample-products.php` - Test data
- Easy to create additional seeders

---

## Performance Considerations

### Shared Instances
- All services registered as shared in `ServiceProvider.php`
- Reduces object instantiation overhead
- Improves request/response time

### Caching
- `Cache/Cache.php` for caching expensive operations
- Object cache integration via WordPress API
- Repository-level caching support

### Asset Management
- `Assets/Manifest.php` for asset loading
- `Assets/SRI.php` for security and integrity
- Optimized asset loading via Vite build process

---

## Security Considerations

### Input Validation
- `Validators/ProductValidator.php` - Product validation
- `Sanitizers/InputSanitizer.php` - Input sanitization
- Security layer in all controllers

### Authentication & Authorization
- `Security/PermissionManager.php` - Permission management
- `Security/CSRFProtection.php` - CSRF protection
- `Security/RateLimiter.php` - Rate limiting

### Audit Logging
- `Security/AuditLogger.php` - Security audit logging
- Tracks security events and violations

### Data Protection
- `Privacy/GDPR.php` - GDPR compliance
- Data export and deletion support
- Privacy by design

---

## Notes

1. **Placeholder Files:** Each directory contains an `index.php` file that serves as a placeholder for autoloading and organizational purposes.

2. **Naming Conventions:** 
   - Classes use PascalCase (e.g., `ProductRepository.php`)
   - Files match class names
   - Namespaces follow PSR-4: `AffiliateProductShowcase\*`

3. **Dependency Injection:** All dependencies managed via `Plugin/Container.php` with automatic resolution via reflection container.

4. **Shared Services:** All services registered as shared instances in `Plugin/ServiceProvider.php` for performance.

5. **Extension Points:** The architecture supports adding new services, repositories, controllers, and components without modifying existing code.
