# 🚀 FRESH PLUGIN BUILD PROMPT - Feature-by-Feature Implementation

## CONTEXT FOR CLAUDE AI

I am building the **Affiliate Product Showcase WordPress plugin** from scratch using the **Modern WordPress Plugin Boilerplate** framework.

- **Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
- **Quality Standard:** 10/10 enterprise-grade, fully optimized, no compromises
- **Approach:** Build ONE complete feature at a time, grouped files together, 100% correct before moving to next feature

### DO NOT:
- ❌ Analyze existing code
- ❌ Fix bugs in current files
- ❌ Suggest improvements to what exists

### DO:
- ✅ Show me complete file structure for entire framework
- ✅ Group related files by feature domain
- ✅ Give me full implementation code for each feature group
- ✅ Build features in correct dependency order
- ✅ Make each feature 100% production-ready before moving on

---

## YOUR MISSION: 4-PART DELIVERY

### PART 1: COMPLETE FILE STRUCTURE MAP
Show me the entire framework structure (~80-95 files) with all files organized by feature groups, including priority and dependencies.

### PART 2: FEATURE-BY-FEATURE IMPLEMENTATION
For each feature group, provide complete implementation with:
- Overview of the feature
- Purpose of each file
- Complete production-ready code for each file
- Testing checklist
- Success criteria

### PART 3: TESTING & VERIFICATION
For each feature, provide:
- Pre-requisites
- Unit test commands
- Integration test commands
- Manual verification steps
- Success criteria

### PART 4: DEPENDENCY ORDER & BUILD SEQUENCE
Show the exact order to build features with:
- Week-by-week breakdown
- Hour estimates
- Dependencies between features
- Expected outcomes

---

# PART 1: COMPLETE FILE STRUCTURE MAP

## 📂 Modern WordPress Plugin Boilerplate - Complete File Structure

### Total Files: 93 files organized in 12 feature groups

### 🌳 COMPLETE FOLDER TREE DIAGRAM

> **IMPORTANT:** This tree diagram shows the **THEORETICAL target structure** for Modern WordPress Plugin Boilerplate. Your **actual current plugin structure** is shown in the section below. Use this theoretical structure as the blueprint for building all 12 feature groups from scratch.

---

#### 📋 THEORETICAL TARGET STRUCTURE (From Scratch)

```
affiliate-product-showcase/
├── affiliate-product-showcase.php                    # Main plugin file (Feature 1)
├── composer.json                                    # PHP dependencies
├── package.json                                     # Node.js dependencies
├── phpunit.xml.dist                                 # PHPUnit config (Feature 12)
├── phpcs.xml.dist                                  # PHPCS config (Feature 12)
├── phpstan.neon                                    # PHPStan config (Feature 12)
├── psalm.xml                                       # Psalm config (Feature 12)
├── docker-compose.yml                               # Docker setup (Feature 12)
├── vite.config.js                                  # Vite config (Feature 11)
├── tailwind.config.js                               # Tailwind config (Feature 11)
├── tsconfig.json                                    # TypeScript config (Feature 11)
│
├── .github/                                        # GitHub workflows (Feature 12)
│   └── workflows/
│       ├── ci.yml                                  # CI pipeline
│       ├── deploy.yml                              # Deployment pipeline
│       └── security.yml                            # Security scanning
│   └── dependabot.yml                              # Dependency updates
│
├── src/                                           # Source code
│   ├── Plugin/                                    # 🏗️ FEATURE 1: CORE BOOTSTRAP (5 files)
│   │   ├── Plugin.php                             # Main plugin orchestrator
│   │   ├── Container.php                           # DI container wrapper
│   │   ├── ServiceProvider.php                      # Service registration
│   │   ├── Constants.php                           # Global constants
│   │   └── Activator.php                          # Plugin activation
│   │
│   ├── Security/                                  # 🔐 FEATURE 2: SECURITY FOUNDATION (8 files)
│   │   ├── Headers.php                            # Security headers
│   │   ├── RateLimiter.php                        # API rate limiting
│   │   ├── PermissionManager.php                   # Authorization checks
│   │   ├── AuditLogger.php                         # Security event logging
│   │   ├── Sanitizer.php                          # Input sanitization
│   │   ├── Validator.php                          # Input validation
│   │   └── CSRFProtection.php                     # CSRF token management
│   │
│   ├── Privacy/                                   # 📋 FEATURE 3: GDPR COMPLIANCE (6 files)
│   │   ├── GDPR.php                               # Export/erasure hooks
│   │   ├── ConsentService.php                      # User consent management
│   │   └── DataRetention.php                      # Retention policies
│   │
│   ├── Models/                                    # 💾 FEATURE 4: DATA LAYER (3 files)
│   │   ├── Product.php                            # Product model
│   │   ├── Analytics.php                          # Analytics model
│   │   └── Settings.php                           # Settings model
│   │
│   ├── Repositories/                              # 💾 FEATURE 4: DATA LAYER (4 files)
│   │   ├── ProductRepository.php                  # Product CRUD
│   │   ├── SettingsRepository.php                 # Settings CRUD
│   │   ├── AnalyticsRepository.php                 # Analytics CRUD
│   │   └── UserDataRepository.php                 # User data CRUD
│   │
│   ├── Factories/                                 # 💾 FEATURE 4: DATA LAYER (2 files)
│   │   ├── ProductFactory.php                      # Product object creation
│   │   └── ModelFactory.php                       # Generic factory base
│   │
│   ├── Database/                                  # 💾 FEATURE 4: DATA LAYER (2 files)
│   │   ├── QueryBuilder.php                       # SQL query builder
│   │   └── Migration.php                          # Database migrations
│   │
│   ├── Cache/                                     # ⚡ FEATURE 5: CACHING SYSTEM (3 files)
│   │   ├── Cache.php                              # Cache abstraction layer
│   │   ├── CacheWarmer.php                        # Pre-populate cache
│   │   └── CacheInvalidator.php                   # Smart cache invalidation
│   │
│   ├── Services/                                  # 🎯 FEATURE 6: BUSINESS LOGIC (4 files)
│   │   ├── ProductService.php                     # Product business logic
│   │   ├── AnalyticsService.php                   # Analytics tracking
│   │   ├── AffiliateService.php                   # Affiliate link generation
│   │   └── NotificationService.php                # Admin notifications
│   │
│   ├── Validators/                                # 🎯 FEATURE 6: BUSINESS LOGIC (2 files)
│   │   ├── ProductValidator.php                    # Product validation
│   │   └── SettingsValidator.php                  # Settings validation
│   │
│   ├── Formatters/                                # 🎯 FEATURE 6: BUSINESS LOGIC (2 files)
│   │   ├── PriceFormatter.php                     # Price formatting
│   │   └── DateFormatter.php                      # Date formatting
│   │
│   ├── Helpers/                                   # 🎯 FEATURE 6: BUSINESS LOGIC (1 file)
│   │   └── ArrayHelper.php                       # Array utilities
│   │
│   ├── Rest/                                      # 🌐 FEATURE 7: REST API (8 files)
│   │   ├── RestController.php                     # Base controller
│   │   ├── ProductsController.php                 # Product CRUD endpoints
│   │   ├── AnalyticsController.php                # Analytics endpoints
│   │   ├── SettingsController.php                  # Settings endpoints
│   │   ├── HealthController.php                   # Health check endpoints
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php                 # JWT/Basic Auth
│   │   │   └── RateLimitMiddleware.php           # Rate limiting middleware
│   │   └── Responses/
│   │       └── ErrorResponse.php                   # Error response formatter
│   │
│   ├── Admin/                                     # 🎨 FEATURE 8: ADMIN INTERFACE (10 files)
│   │   ├── Admin.php                              # Admin initialization
│   │   ├── Settings.php                           # Settings page
│   │   ├── MetaBoxes.php                          # Product meta boxes
│   │   ├── Columns.php                            # Admin list columns
│   │   ├── BulkActions.php                        # Bulk operations
│   │   ├── Notices.php                            # Admin notices
│   │   ├── PrivacyTools.php                        # GDPR admin tools
│   │   └── partials/
│   │       ├── settings-page.php                  # Settings template
│   │       ├── meta-box-product.php               # Product meta box template
│   │       └── privacy-dashboard.php              # Privacy dashboard template
│   │
│   ├── Public/                                    # 🎭 FEATURE 9: PUBLIC INTERFACE (7 files)
│   │   ├── Public_.php                            # Public initialization
│   │   ├── Shortcodes.php                         # Shortcode handlers
│   │   ├── TemplateLoader.php                     # Template system
│   │   ├── Widgets.php                            # Custom widgets
│   │   └── partials/
│   │       ├── product-card.php                    # Product card template
│   │       ├── product-grid.php                   # Product grid template
│   │       ├── product-list.php                   # Product list template
│   │       └── single-product.php                 # Single product template
│   │
│   ├── Blocks/                                    # 🧱 FEATURE 10: GUTENBERG BLOCKS (3 files)
│   │   ├── Blocks.php                             # Block registration
│   │   ├── ProductBlock.php                        # Single product block
│   │   └── ProductGridBlock.php                    # Product grid block
│   │
│   ├── Assets/                                    # 🔧 FEATURE 11: ASSETS & BUILD (3 files)
│   │   ├── Assets.php                             # Asset enqueue manager
│   │   ├── Manifest.php                           # Vite manifest reader
│   │   └── SRI.php                               # Subresource integrity
│   │
│   └── Cli/                                       # CLI Commands
│       └── ProductsCommand.php                     # WP-CLI commands
│
├── resources/                                     # Frontend resources (Feature 11)
│   ├── css/
│   │   ├── admin.css                              # Admin styles
│   │   ├── public.css                             # Public styles
│   │   └── blocks/
│   │       └── editor.css                         # Block editor styles
│   │
│   └── js/
│       ├── admin.ts                               # Admin JavaScript entry point
│       ├── frontend.ts                            # Frontend JavaScript entry point
│       ├── blocks.ts                              # Block JavaScript entry point
│       ├── blocks/
│       │   ├── product-block.tsx                   # React component
│       │   └── product-grid-block.tsx             # React component
│       ├── components/
│       │   └── index.ts                          # Component exports
│       └── utils/
│           ├── api.ts                              # API utilities
│           ├── format.ts                           # Formatting utilities
│           └── i18n.ts                           # Internationalization
│
├── tests/                                         # Test files (Features 2-12)
│   ├── unit/
│   │   ├── Plugin/                               # Feature 1 tests (2 files)
│   │   │   ├── PluginTest.php
│   │   │   └── ContainerTest.php
│   │   ├── Security/                              # Feature 2 tests (8 files)
│   │   ├── Privacy/                               # Feature 3 tests (6 files)
│   │   ├── Repositories/                          # Feature 4 tests (7 files)
│   │   ├── Cache/                                 # Feature 5 tests (4 files)
│   │   ├── Services/                              # Feature 6 tests (10 files)
│   │   └── Blocks/                               # Feature 10 tests (4 files)
│   │
│   └── integration/
│       ├── Rest/                                  # Feature 7 tests (9 files)
│       ├── Admin/                                 # Feature 8 tests (7 files)
│       ├── Public/                                # Feature 9 tests (5 files)
│       └── Blocks/                                # Feature 10 tests (4 files)
│
├── languages/                                     # Translation files
│   └── affiliate-product-showcase.pot
│
├── scripts/                                      # Utility scripts (Feature 12)
│   ├── deploy.sh                                  # Deployment script
│   ├── build-distribution.sh                      # Build distribution
│   └── install-git-hooks.sh                       # Git hooks setup
│
├── tools/                                         # Build tools (Feature 11)
│   ├── check-external-requests.js                 # External request checker
│   ├── compress-assets.js                         # Asset compression
│   └── generate-sri.js                          # SRI generator
│
├── docs/                                          # Documentation
│   ├── developer-guide.md
│   ├── shortcode-reference.md
│   ├── wp-cli.md
│   └── hooks-filters.md
│
├── plan/                                          # Planning documents
│   ├── plugin-breakdown-prompt.md                 # This file
│   └── PLAN_WORKFLOW.md
│
├── vendor/                                        # Composer dependencies (gitignored)
├── node_modules/                                  # Node.js dependencies (gitignored)
├── .gitignore                                     # Git ignore rules
├── .editorconfig                                  # Editor configuration
├── .eslintrc.json                                # ESLint configuration
├── .prettierrc                                   # Prettier configuration
├── README.md                                      # Main documentation
└── LICENSE                                        # GPL v2 or later

---


---
