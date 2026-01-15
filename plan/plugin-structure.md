# Affiliate Product Showcase Plugin Structure

Complete directory and file structure for the Affiliate Product Showcase WordPress plugin.

```
wp-content/plugins/affiliate-product-showcase/
│
├── 📄 affiliate-product-showcase.php      # Main plugin file
├── 📄 uninstall.php                        # Plugin uninstallation script
├── 📄 README.md                            # Main documentation
├── 📄 readme.txt                           # WordPress.org readme
├── 📄 CHANGELOG.md                         # Version history
├── 📄 package.json                         # NPM dependencies
├── 📄 package-lock.json                    # NPM lock file
├── 📄 composer.json                        # PHP dependencies
├── 📄 composer.lock                        # PHP lock file
├── 📄 tsconfig.json                        # TypeScript configuration
├── 📄 vite.config.js                       # Vite build configuration
├── 📄 tailwind.config.js                   # Tailwind CSS configuration
├── 📄 postcss.config.js                    # PostCSS configuration
├── 📄 phpcs.xml.dist                       # PHP CodeSniffer config
├── 📄 phpunit.xml.dist                     # PHPUnit test configuration
├── 📄 infection.json.dist                  # Infection mutation testing config
├── 📄 commitlint.config.cjs                # Commit linting config
├── 📄 .lintstagedrc.json                   # Lint-staged configuration
├── 📄 .a11y.json                           # Accessibility configuration
├── 📄 .env.example                         # Environment variables example
├── 📄 run_phpunit.php                      # PHPUnit runner script
│
├──  assets/                              # Static assets
│   └── 📁 images/
│       ├── banner-772x250.png
│       ├── banner-1544x500.png
│       ├── icon-128x128.png
│       ├── icon-256x256.png
│       ├── logo.svg
│       ├── placeholder-product.png
│       └── screenshot-1.png
│
├── 📁 blocks/                              # Gutenberg Blocks
│   ├── 📁 product-grid/
│   │   ├── block.json                      # Block configuration
│   │   ├── index.js                        # Block entry point
│   │   ├── edit.jsx                        # Editor component
│   │   ├── save.jsx                        # Save component
│   │   ├── editor.scss                     # Editor styles
│   │   └── style.scss                      # Frontend styles
│   │
│   └── 📁 product-showcase/
│       ├── block.json                      # Block configuration
│       ├── index.js                        # Block entry point
│       ├── edit.jsx                        # Editor component
│       ├── save.jsx                        # Save component
│       ├── editor.scss                     # Editor styles
│       └── style.scss                      # Frontend styles
│
├── 📁 docs/                                # Documentation
│   ├── automatic-backup-guide.md
│   ├── cli-commands.md
│   ├── code-quality-tools.md
│   ├── developer-guide.md
│   ├── hooks-filters.md
│   ├── migrations.md
│   ├── rest-api.md
│   ├── tailwind-components.md
│   ├── user-guide.md
│   └── wordpress-org-compliance.md
│
├── 📁 frontend/                            # Frontend build assets
│   ├── 📄 index.php                        # Frontend entry point
│   │
│   ├── 📁 js/
│   │   ├── 📄 index.php                    # JavaScript loader
│   │   │
│   │   ├── 📁 components/
│   │   │   └── 📄 index.php                # Component exports
│   │   │
│   │   └── 📁 utils/
│   │       └── 📄 index.php                # Utility functions
│   │
│   └── 📁 styles/
│       ├── 📄 index.php                    # Styles loader
│       │
│       └── 📁 components/
│           └── 📄 index.php                # Component styles
│
├── 📁 src/                                 # PHP source code
│   ├── 📄 index.php                        # Source entry point
│   │
│   ├── 📁 Abstracts/                       # Abstract base classes
│   │   └── 📄 index.php
│   │
│   ├── 📁 Admin/                           # Admin interface
│   │   ├── 📄 index.php                    # Admin main class
│   │   │
│   │   └── 📁 partials/                    # Admin view templates
│   │       └── 📄 index.php
│   │
│   ├── 📁 Assets/                          # Asset management
│   │   └── 📄 index.php
│   │
│   ├── 📁 Blocks/                          # Block registration
│   │   ├── 📄 index.php                    # Block loader
│   │   │
│   │   └── 📁 product-showcase/
│   │       └── 📄 index.php                # Product showcase block
│   │
│   ├── 📁 Cache/                           # Caching system
│   │   └── 📄 index.php
│   │
│   ├── 📁 Cli/                             # WP-CLI commands
│   │   └── 📄 index.php
│   │
│   ├── 📁 Database/                        # Database operations
│   │   └── 📄 index.php
│   │
│   ├── 📁 Events/                          # Event system
│   │   └── 📄 index.php
│   │
│   ├── 📁 Exceptions/                      # Custom exceptions
│   │   └── 📄 index.php
│   │
│   ├── 📁 Factories/                       # Factory pattern
│   │   └── 📄 index.php
│   │
│   ├── 📁 Formatters/                      # Data formatters
│   │   └── 📄 index.php
│   │
│   ├── 📁 Frontend/                        # Frontend logic
│   │   ├── 📄 index.php                    # Frontend main class
│   │   │
│   │   └── 📁 partials/                    # Frontend view templates
│   │       └── 📄 index.php
│   │
│   ├── 📁 Helpers/                         # Helper functions
│   │   └── 📄 index.php
│   │
│   ├── 📁 Interfaces/                      # Interface definitions
│   │   └── 📄 index.php
│   │
│   ├── 📁 Models/                          # Data models
│   │   └── 📄 index.php
│   │
│   ├── 📁 Plugin/                          # Core plugin logic
│   │   └── 📄 index.php
│   │
│   ├── 📁 Privacy/                         # Privacy compliance
│   │   └── 📄 index.php
│   │
│   ├── 📁 Public/                          # Public interface
│   │   └── 📄 index.php
│   │
│   ├── 📁 Repositories/                    # Data repositories
│   │   └── 📄 index.php
│   │
│   ├── 📁 Rest/                            # REST API
│   │   └── 📄 index.php                    # REST controllers
│   │
│   ├── 📁 Sanitizers/                      # Input sanitization
│   │   └── 📄 index.php
│   │
│   ├── 📁 Security/                        # Security features
│   │   └── 📄 index.php                    # Security handlers
│   │
│   ├── 📁 Services/                        # Business logic
│   │   └── 📄 index.php
│   │
│   ├── 📁 Traits/                          # Reusable traits
│   │   └── 📄 index.php
│   │
│   └── 📁 Validators/                      # Validation logic
│       └── 📄 index.php
│
├── 📁 includes/                            # Include files
│   └── 📄 asset-manifest.php               # Generated asset manifest
│
├── 📁 languages/                           # Translations
│   ├── affiliate-product-showcase.pot     # Translation template
│   ├── affiliate-product-showcase-.po     # English translation
│   └── affiliate-product-showcase-.mo     # Compiled translation
│
├── 📁 resources/                           # Build resources
│   └── 📁 css/
│       ├── app.css                         # Main stylesheet
│       └── 📁 components/                  # Component styles
│           ├── button.css
│           ├── card.css
│           └── form.css
│
├── 📁 scripts/                             # Utility scripts
│   ├── backup.ps1
│   ├── backup.sh
│   ├── create-backup-branch.ps1
│   ├── create-backup-branch.sh
│   ├── db-backup.ps1
│   ├── db-backup.sh
│   ├── db-restore.ps1
│   ├── db-restore.sh
│   ├── db-seed.ps1
│   ├── db-seed.sh
│   ├── hook-test-fresh.txt
│   ├── init.ps1
│   ├── init.sh
│   ├── install-git-hooks.ps1
│   ├── install-git-hooks.sh
│   ├── mark-task-complete.js
│   ├── npm-prepare.cjs
│   ├── push-and-return.ps1
│   ├── restore.ps1
│   ├── restore.sh
│   ├── update-plan.ps1
│   ├── update-plan.sh
│   └── wait-wordpress-healthy.ps1
│
├── 📁 tests/                               # Test suite
│   ├── bootstrap.php                      # Test bootstrap
│   │
│   ├── 📁 fixtures/                        # Test fixtures
│   │   └── sample-products.php
│   │
│   ├── 📁 integration/                    # Integration tests
│   │   ├── AssetsTest.php
│   │   ├── MultiSiteTest.php
│   │   └── test-rest-endpoints.php
│   │
│   └── 📁 unit/                           # Unit tests
│       ├── test-affiliate-service.php
│       ├── test-analytics-service.php
│       ├── test-product-service.php
│       │
│       ├── 📁 Assets/
│       │   ├── ManifestTest.php
│       │   └── SRITest.php
│       │
│       ├── 📁 DependencyInjection/
│       │   └── ContainerTest.php
│       │
│       ├── 📁 Models/
│       │   └── ProductTest.php
│       │
│       └── 📁 Repositories/
│           └── ProductRepositoryTest.php
│
├── 📁 tools/                               # Build tools
│   ├── check-external-requests.js
│   ├── compress-assets.js
│   └── generate-sri.js
│
├── 📁 vite-plugins/                        # Vite plugins
│   └── wordpress-manifest.js              # WordPress manifest plugin
│
├── 📁 src_backup_20260114_224130/          # Backup directory
│   └── [Backup structure mirrors src/]
│
└── 📁 .github/                              # GitHub workflows
    └── [GitHub Actions configurations]
```

---

## Plugin Structure List Format

### 1. Root Level Files
- `affiliate-product-showcase.php` - `root`
- `uninstall.php` - `root`
- `README.md` - `root`
- `readme.txt` - `root`
- `CHANGELOG.md` - `root`
- `package.json` - `root`
- `package-lock.json` - `root`
- `composer.json` - `root`
- `composer.lock` - `root`
- `tsconfig.json` - `root`
- `vite.config.js` - `root`
- `tailwind.config.js` - `root`
- `postcss.config.js` - `root`
- `phpcs.xml.dist` - `root`
- `phpunit.xml.dist` - `root`
- `infection.json.dist` - `root`
- `commitlint.config.cjs` - `root`
- `.lintstagedrc.json` - `root`
- `.a11y.json` - `root`
- `.env.example` - `root`
- `run_phpunit.php` - `root`

### 2. assets/
**Purpose:** Contains static assets including images, banners, icons, logos, and screenshots used throughout the plugin.
#### 2.1 images/
- `banner-772x250.png`
- `banner-1544x500.png`
- `icon-128x128.png`
- `icon-256x256.png`
- `logo.svg`
- `placeholder-product.png`
- `screenshot-1.png`

**Related Root Files:**
- `affiliate-product-showcase.php` - `root`
- `uninstall.php` - `root`

### 3. blocks/
**Purpose:** Gutenberg block definitions with separate folders for each block, including configuration files, React components, and stylesheets.
#### 3.1 product-grid/
- `block.json` - Block configuration
- `index.js` - Block entry point
- `edit.jsx` - Editor component
- `save.jsx` - Save component
- `editor.scss` - Editor styles
- `style.scss` - Frontend styles
#### 3.2 product-showcase/
- `block.json` - Block configuration
- `index.js` - Block entry point
- `edit.jsx` - Editor component
- `save.jsx` - Save component
- `editor.scss` - Editor styles
- `style.scss` - Frontend styles

**Related Root Files:**
- `package.json` - `root`
- `package-lock.json` - `root`
- `tsconfig.json` - `root`
- `.a11y.json` - `root`

### 4. docs/
**Purpose:** Comprehensive documentation including developer guides, user manuals, CLI commands, API documentation, and compliance guides.
- `automatic-backup-guide.md`
- `cli-commands.md`
- `code-quality-tools.md`
- `developer-guide.md`
- `hooks-filters.md`
- `migrations.md`
- `rest-api.md`
- `tailwind-components.md`
- `user-guide.md`
- `wordpress-org-compliance.md`

**Related Root Files:**
- `README.md` - `root`
- `readme.txt` - `root`
- `CHANGELOG.md` - `root`

### 5. frontend/
**Purpose:** Frontend build assets containing JavaScript and CSS loaders organized into components and utilities with index.php files for asset management.
- `index.php` - Frontend entry point
#### 5.1 js/
- `index.php` - JavaScript loader
##### 5.1.1 components/
- `index.php` - Component exports
##### 5.1.2 utils/
- `index.php` - Utility functions
#### 5.2 styles/
- `index.php` - Styles loader
##### 5.2.1 components/
- `index.php` - Component styles

**Related Root Files:**
- `package.json` - `root`
- `package-lock.json` - `root`
- `tsconfig.json` - `root`
- `vite.config.js` - `root`
- `tailwind.config.js` - `root`
- `postcss.config.js` - `root`
- `.a11y.json` - `root`

### 6. src/
**Purpose:** PHP source code organized by architectural components including admin interface, assets management, blocks, caching, database operations, events, REST API, security, services, and more.
- `index.php` - Source entry point

**Related Root Files:**
- `composer.json` - `root`
- `composer.lock` - `root`
- `phpcs.xml.dist` - `root`
#### 6.1 Abstracts/
- `index.php` - Abstract base classes
#### 6.2 Admin/
- `index.php` - Admin main class
##### 6.2.1 partials/
- `index.php` - Admin view templates
#### 6.3 Assets/
- `index.php` - Asset management
#### 6.4 Blocks/
- `index.php` - Block loader
##### 6.4.1 product-showcase/
- `index.php` - Product showcase block
#### 6.5 Cache/
- `index.php` - Caching system
#### 6.6 Cli/
- `index.php` - WP-CLI commands
#### 6.7 Database/
- `index.php` - Database operations
#### 6.8 Events/
- `index.php` - Event system
#### 6.9 Exceptions/
- `index.php` - Custom exceptions
#### 6.10 Factories/
- `index.php` - Factory pattern
#### 6.11 Formatters/
- `index.php` - Data formatters
#### 6.12 Frontend/
- `index.php` - Frontend main class
##### 6.12.1 partials/
- `index.php` - Frontend view templates
#### 6.13 Helpers/
- `index.php` - Helper functions
#### 6.14 Interfaces/
- `index.php` - Interface definitions
#### 6.15 Models/
- `index.php` - Data models
#### 6.16 Plugin/
- `index.php` - Core plugin logic
#### 6.17 Privacy/
- `index.php` - Privacy compliance
#### 6.18 Public/
- `index.php` - Public interface
#### 6.19 Repositories/
- `index.php` - Data repositories
#### 6.20 Rest/
- `index.php` - REST controllers
#### 6.21 Sanitizers/
- `index.php` - Input sanitization
#### 6.22 Security/
- `index.php` - Security handlers
#### 6.23 Services/
- `index.php` - Business logic
#### 6.24 Traits/
- `index.php` - Reusable traits
#### 6.25 Validators/
- `index.php` - Validation logic

### 7. includes/
**Purpose:** Include files for generated assets and manifest files used by plugin.
- `asset-manifest.php` - Generated asset manifest

### 8. languages/
**Purpose:** Translation files for internationalization support including .pot template, .po source files, and compiled .mo binary files.
- `affiliate-product-showcase.pot` - Translation template
- `affiliate-product-showcase-.po` - English translation
- `affiliate-product-showcase-.mo` - Compiled translation

### 9. resources/
**Purpose:** Build resources including CSS files and component stylesheets that are compiled and used in production.
#### 9.1 css/
- `app.css` - Main stylesheet
##### 9.1.1 components/
- `button.css`
- `card.css`
- `form.css`

### 10. scripts/
**Purpose:** Utility scripts for development, deployment, and maintenance tasks including backup, database operations, and Git hooks in both PowerShell and Bash formats.
- `backup.ps1` - PowerShell backup script
- `backup.sh` - Bash backup script
- `create-backup-branch.ps1` - PowerShell backup branch script
- `create-backup-branch.sh` - Bash backup branch script
- `db-backup.ps1` - PowerShell database backup
- `db-backup.sh` - Bash database backup
- `db-restore.ps1` - PowerShell database restore
- `db-restore.sh` - Bash database restore
- `db-seed.ps1` - PowerShell database seeding
- `db-seed.sh` - Bash database seeding
- `hook-test-fresh.txt`
- `init.ps1` - PowerShell initialization
- `init.sh` - Bash initialization
- `install-git-hooks.ps1` - PowerShell git hooks
- `install-git-hooks.sh` - Bash git hooks
- `mark-task-complete.js`
- `npm-prepare.cjs`
- `push-and-return.ps1`
- `restore.ps1` - PowerShell restore
- `restore.sh` - Bash restore
- `update-plan.ps1`
- `update-plan.sh`
- `wait-wordpress-healthy.ps1`

### 11. tests/
**Purpose:** Comprehensive test suite including unit tests, integration tests, and test fixtures for ensuring code quality and functionality.
- `bootstrap.php` - Test bootstrap

**Related Root Files:**
- `phpunit.xml.dist` - `root`
- `infection.json.dist` - `root`
- `phpcs.xml.dist` - `root`
- `run_phpunit.php` - `root`
#### 11.1 fixtures/
- `sample-products.php` - Test fixtures
#### 11.2 integration/
- `AssetsTest.php`
- `MultiSiteTest.php`
- `test-rest-endpoints.php`
#### 11.3 unit/
- `test-affiliate-service.php`
- `test-analytics-service.php`
- `test-product-service.php`
##### 11.3.1 Assets/
- `ManifestTest.php`
- `SRITest.php`
##### 11.3.2 DependencyInjection/
- `ContainerTest.php`
##### 11.3.3 Models/
- `ProductTest.php`
##### 11.3.4 Repositories/
- `ProductRepositoryTest.php`

### 12. tools/
**Purpose:** Build tools and utilities for asset compression, SRI (Subresource Integrity) generation, and external request checking.
- `check-external-requests.js`
- `compress-assets.js`
- `generate-sri.js`

### 13. vite-plugins/
**Purpose:** Custom Vite plugins for WordPress integration including manifest generation for asset management.
- `wordpress-manifest.js` - WordPress manifest plugin

**Related Root Files:**
- `package.json` - `root`
- `package-lock.json` - `root`
- `vite.config.js` - `root`

### 14. src_backup_20260114_224130/
**Purpose:** Backup directory created on 2026-01-14 containing a mirror of the src/ directory structure for version control and rollback purposes.
- Backup directory (mirrors src/)

### 15. .github/
**Purpose:** GitHub Actions and workflow configurations for CI/CD, automated testing, and deployment processes.
- GitHub Actions configurations

---

## Directory Structure Overview
- `affiliate-product-showcase.php` - Main plugin file
- `uninstall.php` - Plugin uninstallation script
- `README.md` - Main documentation
- `readme.txt` - WordPress.org readme
- `CHANGELOG.md` - Version history
- `package.json` - NPM dependencies
- `package-lock.json` - NPM lock file
- `composer.json` - PHP dependencies
- `composer.lock` - PHP lock file
- `tsconfig.json` - TypeScript configuration
- `vite.config.js` - Vite build configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `postcss.config.js` - PostCSS configuration
- `phpcs.xml.dist` - PHP CodeSniffer config
- `phpunit.xml.dist` - PHPUnit test configuration
- `infection.json.dist` - Infection mutation testing config
- `commitlint.config.cjs` - Commit linting config
- `.lintstagedrc.json` - Lint-staged configuration
- `.a11y.json` - Accessibility configuration
- `.env.example` - Environment variables example
- `run_phpunit.php` - PHPUnit runner script

### assets/
#### images/
- `banner-772x250.png`
- `banner-1544x500.png`
- `icon-128x128.png`
- `icon-256x256.png`
- `logo.svg`
- `placeholder-product.png`
- `screenshot-1.png`

### blocks/
#### product-grid/
- `block.json` - Block configuration
- `index.js` - Block entry point
- `edit.jsx` - Editor component
- `save.jsx` - Save component
- `editor.scss` - Editor styles
- `style.scss` - Frontend styles
#### product-showcase/
- `block.json` - Block configuration
- `index.js` - Block entry point
- `edit.jsx` - Editor component
- `save.jsx` - Save component
- `editor.scss` - Editor styles
- `style.scss` - Frontend styles

### docs/
- `automatic-backup-guide.md`
- `cli-commands.md`
- `code-quality-tools.md`
- `developer-guide.md`
- `hooks-filters.md`
- `migrations.md`
- `rest-api.md`
- `tailwind-components.md`
- `user-guide.md`
- `wordpress-org-compliance.md`

### frontend/
- `index.php` - Frontend entry point
#### js/
- `index.php` - JavaScript loader
##### components/
- `index.php` - Component exports
##### utils/
- `index.php` - Utility functions
#### styles/
- `index.php` - Styles loader
##### components/
- `index.php` - Component styles

### src/
- `index.php` - Source entry point
#### Abstracts/
- `index.php` - Abstract base classes
#### Admin/
- `index.php` - Admin main class
##### partials/
- `index.php` - Admin view templates
#### Assets/
- `index.php` - Asset management
#### Blocks/
- `index.php` - Block loader
##### product-showcase/
- `index.php` - Product showcase block
#### Cache/
- `index.php` - Caching system
#### Cli/
- `index.php` - WP-CLI commands
#### Database/
- `index.php` - Database operations
#### Events/
- `index.php` - Event system
#### Exceptions/
- `index.php` - Custom exceptions
#### Factories/
- `index.php` - Factory pattern
#### Formatters/
- `index.php` - Data formatters
#### Frontend/
- `index.php` - Frontend main class
##### partials/
- `index.php` - Frontend view templates
#### Helpers/
- `index.php` - Helper functions
#### Interfaces/
- `index.php` - Interface definitions
#### Models/
- `index.php` - Data models
#### Plugin/
- `index.php` - Core plugin logic
#### Privacy/
- `index.php` - Privacy compliance
#### Public/
- `index.php` - Public interface
#### Repositories/
- `index.php` - Data repositories
#### Rest/
- `index.php` - REST controllers
#### Sanitizers/
- `index.php` - Input sanitization
#### Security/
- `index.php` - Security handlers
#### Services/
- `index.php` - Business logic
#### Traits/
- `index.php` - Reusable traits
#### Validators/
- `index.php` - Validation logic

### includes/
- `asset-manifest.php` - Generated asset manifest

### languages/
- `affiliate-product-showcase.pot` - Translation template
- `affiliate-product-showcase-.po` - English translation
- `affiliate-product-showcase-.mo` - Compiled translation

### resources/
#### css/
- `app.css` - Main stylesheet
##### components/
- `button.css`
- `card.css`
- `form.css`

### scripts/
- `backup.ps1` - PowerShell backup script
- `backup.sh` - Bash backup script
- `create-backup-branch.ps1` - PowerShell backup branch script
- `create-backup-branch.sh` - Bash backup branch script
- `db-backup.ps1` - PowerShell database backup
- `db-backup.sh` - Bash database backup
- `db-restore.ps1` - PowerShell database restore
- `db-restore.sh` - Bash database restore
- `db-seed.ps1` - PowerShell database seeding
- `db-seed.sh` - Bash database seeding
- `hook-test-fresh.txt`
- `init.ps1` - PowerShell initialization
- `init.sh` - Bash initialization
- `install-git-hooks.ps1` - PowerShell git hooks
- `install-git-hooks.sh` - Bash git hooks
- `mark-task-complete.js`
- `npm-prepare.cjs`
- `push-and-return.ps1`
- `restore.ps1` - PowerShell restore
- `restore.sh` - Bash restore
- `update-plan.ps1`
- `update-plan.sh`
- `wait-wordpress-healthy.ps1`

### tests/
- `bootstrap.php` - Test bootstrap
#### fixtures/
- `sample-products.php` - Test fixtures
#### integration/
- `AssetsTest.php`
- `MultiSiteTest.php`
- `test-rest-endpoints.php`
#### unit/
- `test-affiliate-service.php`
- `test-analytics-service.php`
- `test-product-service.php`
##### Assets/
- `ManifestTest.php`
- `SRITest.php`
##### DependencyInjection/
- `ContainerTest.php`
##### Models/
- `ProductTest.php`
##### Repositories/
- `ProductRepositoryTest.php`

### tools/
- `check-external-requests.js`
- `compress-assets.js`
- `generate-sri.js`

### vite-plugins/
- `wordpress-manifest.js` - WordPress manifest plugin

### src_backup_20260114_224130/
- Backup directory (mirrors src/)

### .github/
- GitHub Actions configurations

---

## Directory Structure Overview

### Core Plugin Files
- **affiliate-product-showcase.php**: Main plugin file containing plugin header and initialization
- **uninstall.php**: Clean uninstallation process
- **README.md & readme.txt**: Documentation for developers and WordPress.org

### Configuration Files
- **Build tools**: Vite, Tailwind, PostCSS, TypeScript configurations
- **PHP tools**: Composer, PHPUnit, CodeSniffer, Infection configurations
- **Development tools**: Lint-staged, Commitlint, ESLint configurations
- **Environment**: .env.example, .a11y.json

### Assets
- **images**: Plugin banners, icons, logos, and screenshots
- **frontend**: JavaScript and CSS loaders
- **resources**: CSS files and components

### Source Code (src/)
Organized by architectural components:
- **Abstracts**: Base classes for common patterns
- **Admin**: WordPress admin interface components
- **Assets**: Asset loading and management
- **Blocks**: Gutenberg block registration and implementations
- **Cache**: Caching implementation
- **Cli**: WP-CLI command definitions
- **Database**: Database operations and migrations
- **Events**: Event-driven architecture
- **Exceptions**: Custom exception classes
- **Factories**: Object creation patterns
- **Formatters**: Data formatting utilities
- **Frontend**: Frontend logic and templates
- **Helpers**: Utility functions
- **Interfaces**: Contract definitions
- **Models**: Data models
- **Plugin**: Core plugin functionality
- **Privacy**: GDPR/compliance features
- **Public**: Public interface functionality
- **Repositories**: Data access layer
- **Rest**: REST API endpoints
- **Sanitizers**: Input sanitization
- **Security**: Security features and audit logging
- **Services**: Business logic layer
- **Traits**: Reusable code snippets
- **Validators**: Input validation

### Testing
- **Unit tests**: Isolated component testing
- **Integration tests**: Component interaction testing
- **Fixtures**: Test data and mocks

### Documentation
- **Developer guides**: Architecture and development
- **User guides**: End-user documentation
- **API docs**: REST API and hooks/filters

### Scripts & Tools
- **Backup scripts**: PowerShell and Bash backup utilities
- **Database scripts**: Database backup, restore, and seeding
- **Build tools**: Asset compression and SRI generation
- **Development tools**: Git hooks, CLI utilities

## Architecture Pattern

The plugin follows a modern, object-oriented architecture with:
- **Dependency Injection**: Container-based service management
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **Event-Driven**: Event dispatcher for loose coupling
- **REST API**: Modern RESTful endpoints
- **Block-Based**: Gutenberg block integration
- **Testing-First**: Comprehensive test coverage

## Technology Stack

- **Backend**: PHP 8.0+, WordPress 5.0+
- **Frontend**: JavaScript, React (for blocks)
- **Build Tools**: Vite, Tailwind CSS, PostCSS
- **Testing**: PHPUnit, Infection (mutation testing)
- **Code Quality**: CodeSniffer, Psalm (static analysis)
- **Package Management**: Composer, NPM

## Key Directories Explanation

### src/ Directory
The `src/` directory contains all PHP source code organized by responsibility. Each subdirectory has an `index.php` file that acts as the entry point for that module.

### frontend/ Directory
The `frontend/` directory contains JavaScript and CSS files organized into `js/` and `styles/` subdirectories. Each has an `index.php` loader file for asset management.

### blocks/ Directory
The `blocks/` directory contains Gutenberg block definitions with separate folders for each block, including configuration, components, and styles.

### tests/ Directory
The `tests/` directory contains unit and integration tests, with fixtures providing test data.

### scripts/ Directory
The `scripts/` directory contains utility scripts for development, deployment, and maintenance tasks, available in both PowerShell (.ps1) and Bash (.sh) formats.

### Documentation Files
- **CODE_IMPROVEMENTS_SUMMARY.md**: Log of code improvements
- **FRAMEWORK_COMPLIANCE_FIXES_COMPLETED.md**: Framework compliance fixes
- **FRAMEWORK_COMPLIANCE_REPORT.md**: Detailed compliance report
