# Affiliate Product Showcase — Step-by-step Plan (Source)

> This is the editable plan outline.
>
> **Workflow rule:** Edit this file, then run `node plan/plan_sync_todos.cjs`.
> Do not manually edit `plan_sync.md`, `plan_sync_todo.md`, or `plan_todos.json` (they are generated).

## Priority badges
# Affiliate Product Showcase — Step-by-step Plan

> Numbered step plan with priority levels.

## Priority
- High — Critical milestones and blockers
- Medium — Important features
- Low — Nice-to-have, docs, marketing

---

# Step 1 — Step 1 — Setup

## 1.1 Docker Environment — Docker compose setup to bring up local environment and services
### 1.1.1 WordPress 6.7+ container with PHP 8.3-fpm-alpine
   - 1.1.1.1 Pull and pin the WordPress PHP-FPM image (use exact tag)
   - 1.1.1.2 Configure environment variables and DB connection for container
   - 1.1.1.3 Mount plugin source into container for development
   - 1.1.1.4 Add PHP-FPM `www.conf` and php.ini overrides for dev
   - 1.1.1.5 Add container healthcheck and CI integration tests
   - 1.1.1.6 Document WP-CLI helper commands and test entrypoints

### 1.1.2 MySQL 8.0 container with persistent volumes
   - 1.1.2.1 Map DB volume to host path for backups (e.g., `docker/mysql_data`) — recommended for easy host-level backups and inspection
   - 1.1.2.2 Add DB seeding for tests (e.g., `tests/db-seed.php`) to enable repeatable test setups
   - 1.1.2.3 Configure MySQL environment variables and credentials for compose
   - 1.1.2.4 Add DB healthcheck and readiness probe for compose
   - 1.1.2.5 Secure collection: document backup/restore steps and credentials handling

### 1.1.3 Nginx container with SSL/TLS configuration
   - 1.1.3.1 Let's Encrypt automation: use Certbot/lego/nginx-proxy-companion or consider Caddy for automatic issuance and renewal
   - 1.1.3.2 Auto-renewal handling: mount certificates to a volume and add a post-renewal reload hook to reload Nginx
   - 1.1.3.3 HTTP→HTTPS redirect: expose port 80 only for redirects and ACME challenges; force HTTPS for site traffic
   - 1.1.3.4 Strong TLS policy: enable TLS 1.2+ and TLS 1.3, set explicit cipher suites, and disable weak ciphers and legacy protocols
   - 1.1.3.5 HSTS header: configure `Strict-Transport-Security` with an appropriate `max-age` and document preload considerations
   - 1.1.3.6 OCSP stapling and TLS session caching: enable for improved performance and certificate validation
   - 1.1.3.7 Container healthcheck & logging: add a simple health endpoint and stream access/error logs to stdout/stderr
   - 1.1.3.8 Volume & secret management: document where certs/keys live and how secrets are injected (avoid baking keys into images)
   - 1.1.3.9 Graceful reloads and scaling: ensure zero-downtime reload strategy when certs or config change (signal handling, rolling reloads)
   - 1.1.3.10 Optional extras: mutual TLS (client certs), rate-limiting, security headers (CSP, X-Frame-Options), and IPv6 support
### 1.1.4 Redis container for object caching
   - 1.1.4.1 Add a Redis service to `docker/docker-compose.yml` and expose it to the PHP and WordPress containers
   - 1.1.4.2 Install and enable the PHP `redis` extension in the PHP-FPM image (document Dockerfile/build steps)
   - 1.1.4.3 Add a WordPress `object-cache.php` drop-in (or enable the Redis Object Cache plugin) and document configuration (host, port, and env vars)
   - 1.1.4.4 Add a Redis healthcheck and optional volume for persistence in development
   - 1.1.4.5 Document example `docker-compose.override.yml` and environment variable examples for local development
### 1.1.5 MailHog container for email testing
   - 1.1.5.1 Docker MailHog service (SMTP 1025, Web UI 8025)
   - 1.1.5.2 SMTP configuration (PHP + WordPress)
   - 1.1.5.3 Web UI access (port 8025)
   - 1.1.5.4 Healthcheck for MailHog service
   - 1.1.5.5 Basic testing (send/receive, mail capture)
   - 1.1.5.6 Basic documentation (how-to, env vars, warnings)
### 1.1.6 phpMyAdmin container for database management
   - 1.1.6.1 REQUIRED - Image: phpmyadmin/phpmyadmin
   - 1.1.6.2 REQUIRED - DB connectivity: set `PMA_HOST=db` so phpMyAdmin connects to the MySQL service
   - 1.1.6.3 REQUIRED - Credentials: use the MySQL env vars (e.g., `MYSQL_ROOT_PASSWORD`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`) for authentication
   - 1.1.6.4 REQUIRED - Network: attach phpMyAdmin to the same Docker network as the MySQL service
   - 1.1.6.5 REQUIRED - Port mapping: expose phpMyAdmin on host `8080:80` for local access

   - 1.1.6.6 GOOD - Blowfish secret: set `PMA_BLOWFISH_SECRET` for cookie encryption
   - 1.1.6.7 GOOD - Localhost binding: bind host port to `127.0.0.1:8080` when running locally to limit external access
   - 1.1.6.8 GOOD - Healthcheck: add a simple HTTP healthcheck for container reliability
   - 1.1.6.9 GOOD - depends_on: add a `depends_on` for MySQL readiness (or a wait-for script) to ensure startup order
   - 1.1.6.10 GOOD - Override file: keep phpMyAdmin service config in `docker-compose.override.yml` for dev-only settings
   - 1.1.6.11 GOOD - Documentation: add usage notes (URL, default credentials, env var references) to docs/

   - 1.1.6.12 NOT REQUIRED - Persistence: phpMyAdmin is stateless; no persistent volume required for typical dev use
   - 1.1.6.13 NOT REQUIRED - Reverse proxy: unnecessary for simple localhost development
   - 1.1.6.14 NOT REQUIRED - TLS/Traefik labels: skip for local dev; use in production only
   - 1.1.6.15 NOT REQUIRED - Alternative notes: mention alternatives (Adminer, remote DB tools) under this topic for reference
### 1.1.7 WP-CLI container for automation tasks
   - 1.1.7.1 Docker service configuration — Add a `wp-cli` service (image: `wp-cli/wp-cli` or a small helper image), set DB and WP environment variables, and include a simple healthcheck and `depends_on` so it can run against the DB/PHP services. [REQUIRED]
   - 1.1.7.2 Volume and network setup — Bind-mount the project directory and `wp-content` where appropriate; attach the service to the same Docker network and share the DB named volume for direct access. [REQUIRED]
   - 1.1.7.3 Database automation scripts — Provide idempotent scripts for `db:backup`, `db:restore`, and `db:seed` using `wp db` or `mysqldump` wrapped in shell helpers placed in `scripts/` (or `Makefile` targets). [RECOMMENDED]
   - 1.1.7.4 Plugin/theme management commands — Add reusable WP-CLI commands or scripts to install/activate plugins and themes (`wp plugin install --activate`, `wp theme install --activate`) and an `init` script for first-boot setup. [RECOMMENDED]
   - 1.1.7.5 Backup automation system — Optional scheduled backups of the database and `wp-content` (cron container or host cron) with storage to a mounted backup volume or remote store. [OPTIONAL — not installed]
   - 1.1.7.6 Deployment workflow scripts — Optional containerized deploy scripts (SSH/rsync or remote WP-CLI) and helper commands to perform zero-downtime updates and remote migrations. [OPTIONAL — not installed]
   - 1.1.7.7 Cron job configuration — Optional cron service or host cron configuration to run WP-CLI scheduled tasks, or document relying on WP pseudo-cron for low-traffic dev setups. [OPTIONAL — not installed]
   - 1.1.7.8 CI/CD integration — Optional CI jobs that call WP-CLI for test DB setup, plugin installs, and migrations (e.g., GitHub Actions/GitLab CI). [OPTIONAL — not installed]
   - 1.1.7.9 Testing automation — Optional harness to use WP-CLI during tests (e.g., `wp core install` for test DB, fixtures import) and integrate with `tests/` PHPUnit setup. [OPTIONAL — not installed]
   - 1.1.7.10 Monitoring and logging — Optional healthchecks, logging best-practices, and integration points for monitoring WP-CLI driven automation tasks. [OPTIONAL — not installed]

### 1.1.8 Custom healthcheck scripts for all services
   - 1.1.8.1 Basic healthcheck in docker-compose.yml (inline commands).
   - 1.1.8.2 depends_on with service_healthy condition.
   - 1.1.8.3 MySQL: mysqladmin ping.
   - 1.1.8.4 WordPress: php-fpm check.
   - 1.1.8.5 Nginx: HTTP Probe (e.g., curl -f http://localhost).
   - 1.1.8.6 Redis: redis-cli ping (if Redis is used).
   - 1.1.8.7 Separate shell scripts. - NOT NEEDED
   - 1.1.8.8 Fallback mechanisms. - NOT NEEDED
   - 1.1.8.9 PHP alternatives. - NOT NEEDED
   - 1.1.8.10 Custom Dockerfiles for healthchecks. - NOT NEEDED
   - 1.1.8.11 Complex multi-layer checks. - NOT NEEDED
### 1.1.9 Docker Compose v3.8+ with environment variable substitution
   - 1.1.9.1 Docker Compose version - Set to 3.8 or higher in yaml file. - ACTUALLY NEEDED
   - 1.1.9.2 Variable substitution - Use ${VAR} or ${VAR:-default} syntax in services. - ACTUALLY NEEDED
   - 1.1.9.3 .env.example file - Create with sample environment variables documented. - ACTUALLY NEEDED
   - 1.1.9.4 .gitignore update - Ensure .env is listed in .gitignore so local secrets are not committed. - ACTUALLY NEEDED
   - 1.1.9.5 Multiple environment files  - NOT NEEDED
   - 1.1.9.6 Validation scripts - NOT NEEDED
   - 1.1.9.7 Secret management - NOT NEEDED
   - 1.1.9.8 Extensive documentation - NOT NEEDED
   - 1.1.9.9 * Secret management * Extensive documentation - NOT NEEDED

### 1.1.10 Volume mounts for plugin development directory
   - 1.1.10.1 Mount plugin folder in docker-compose.yml - ACTUALLY NEEDED
   - 1.1.10.2 Path: ./plugins/your-plugin:/var/www/html/wp-content/plugins/your-plugin - ACTUALLY NEEDED
   - 1.1.10.3 Test write permission works- ACTUALLY NEEDED
   - 1.1.10.4 Verify changes reflect in container- ACTUALLY NEEDED
   - 1.1.10.5 Multiple plugin mounts - NOT NEEDED
   - 1.1.10.6 Hot reload config - NOT NEEDED
   - 1.1.10.7 File watchers - NOT NEEDED
   - 1.1.10.8 Permission scripts - NOT NEEDED
   - 1.1.10.9 Sync tools - NOT NEEDED
### 1.1.11 Network isolation between services
   - 1.1.11.1 Custom network defined in docker-compose.yml. - ACTUALLY NEEDED
   - 1.1.11.2 All services on same network. - ACTUALLY NEEDED
   - 1.1.11.3 Network driver bridge. - ACTUALLY NEEDED
   - 1.1.11.4 Services can communicate internally. - ACTUALLY NEEDED
   - 1.1.11.5 Only Nginx exposes ports to host; internal services (DB/PHP) have no ports mapping. - ACTUALLY NEEDED
   - 1.1.11.6 Multiple networks per service. - NOT NEEDED
   - 1.1.11.7 Advanced firewall rules. - NOT NEEDED
   - 1.1.11.8 Network policies. - NOT NEEDED
   - 1.1.11.9 Service mesh. - NOT NEEDED
   - 1.1.11.10 Complex routing configuration. - NOT NEEDED
   - 1.1.11.11 VPN configuration. - NOT NEEDED
### 1.1.12 Automated database seeding with sample data
   - 1.1.12.1 Seed script in scripts/db-seed.sh - ACTUALLY NEEDED
   - 1.1.12.2 Sample posts and pages - ACTUALLY NEEDED
   - 1.1.12.3 Sample product data for plugin - ACTUALLY NEEDED
   - 1.1.12.4 Run seed on first container start - ACTUALLY NEEDED
   - 1.1.12.5 Verify seeding works - ACTUALLY NEEDED
   - 1.1.12.6 Complex data generators - NOT NEEDED
   - 1.1.12.7 Large datasets - NOT NEEDED
   - 1.1.12.8 External data sources - NOT NEEDED
   - 1.1.12.9 Automatic re-seeding - NOT NEEDED
   - 1.1.12.10 Database migrations system - NOT NEEDED
   - 1.1.12.11 Fixtures framework - NOT NEEDED

## 1.2 Folder Structure — create folder structure and repository layout

1.2.1 Framework: Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
1.2.2 Plugin Name: Affiliate Product Showcase
1.2.3 Goal: Standalone, production-ready, secure, cache-compatible, instantly working on any WordPress site, suitable for CodeCanyon / premium marketplace submission
1.2.4 Standards: Follows WordPress Coding Standards, REST API best practices, accessibility, security hardening, proper prefixing, i18n, RTL support
1.2.5 Root Level Files
1.2.6 affiliate-product-showcase.php – Main plugin file (header, constants, bootstrap, textdomain loading)
1.2.7 readme.txt – WordPress.org readme format
1.2.8 README.md – Developer documentation & GitHub readme
1.2.9 uninstall.php – Responsible cleanup (with data preservation option)
1.2.10 composer.json – PSR-4 autoload, dependencies, dev tools & scripts
1.2.11 composer.lock
1.2.12 package.json – Node dependencies & build scripts
1.2.13 package-lock.json
1.2.14 vite.config.js – Vite build configuration
1.2.15 tailwind.config.js – Tailwind configuration
1.2.16 postcss.config.js – PostCSS configuration
1.2.17 phpcs.xml.dist – WordPress-Extra + WordPress-Docs + prefix + textdomain enforcement
1.2.18 phpunit.xml.dist – PHPUnit configuration
1.2.19 .gitignore – Smart ignores (maps, test configs, vendor, node_modules, etc.)
1.2.20 .editorconfig
1.2.21 .eslintrc.cjs – Modern JavaScript linting config
1.2.22 .prettierrc – Code formatting rules
1.2.23 SECURITY.md – Security policy
1.2.24 CONTRIBUTING.md – Contribution guidelines
1.2.25 CODE_OF_CONDUCT.md – Community standards
1.2.26 CHANGELOG.md – Version history
1.2.27 LICENSE – GPL-2.0-or-later license
1.2.28 wp-tests-config-sample.php – Sample test configuration for contributors
1.2.29 PHP Source Code (src/ – PSR-4 root namespace: AffiliateProductShowcase)
1.2.30 src/Plugin/Constants.php – All plugin constants (VERSION, TEXTDOMAIN, PREFIX, etc.)
1.2.31 src/Plugin/Plugin.php – Main plugin singleton + initialization
1.2.32 src/Plugin/Activator.php – Activation + version upgrade routines
1.2.33 src/Plugin/Deactivator.php – Deactivation cleanup (events, rewrites)
1.2.34 src/Plugin/Loader.php – Central hook, shortcode, widget, REST registration
1.2.35 src/Admin/Admin.php – Admin menu, notices, enqueue logic
1.2.36 src/Admin/Settings.php – Settings API wrapper + sanitization
1.2.37 src/Admin/MetaBoxes.php – Product custom meta box handler
1.2.38 src/Admin/partials/settings-page.php
1.2.39 src/Admin/partials/product-meta-box.php
1.2.40 src/Admin/partials/dashboard-widget.php
1.2.41 src/Public/Public.php – Public frontend logic + enqueue
1.2.42 src/Public/Shortcodes.php – Shortcode handlers
1.2.43 src/Public/Widgets.php – Widget registration & rendering
1.2.44 src/Public/partials/product-grid.php
1.2.45 src/Public/partials/product-card.php
1.2.46 src/Public/partials/single-product.php
1.2.47 src/Blocks/Blocks.php – Dynamic block.json scanner & registration
1.2.48 src/Rest/RestController.php – Base REST controller with capability checks
1.2.49 src/Rest/ProductsController.php – REST endpoint: /affiliate/v1/products
1.2.50 src/Rest/AnalyticsController.php – REST endpoint: /affiliate/v1/analytics
1.2.51 src/Cache/Cache.php – Cache wrapper (transient + object cache + group support)
1.2.52 src/Assets/Assets.php – Vite manifest reader + RTL + versioned assets
1.2.53 src/Services/ProductService.php
1.2.54 src/Services/AffiliateService.php
1.2.55 src/Services/AnalyticsService.php
1.2.56 src/Repositories/ProductRepository.php
1.2.57 src/Repositories/SettingsRepository.php
1.2.58 src/Models/Product.php
1.2.59 src/Models/AffiliateLink.php
1.2.60 src/Validators/ProductValidator.php
1.2.61 src/Sanitizers/InputSanitizer.php
1.2.62 src/Formatters/PriceFormatter.php
1.2.63 src/Factories/ProductFactory.php
1.2.64 src/Abstracts/AbstractRepository.php
1.2.65 src/Abstracts/AbstractService.php
1.2.66 src/Abstracts/AbstractValidator.php
1.2.67 src/Interfaces/RepositoryInterface.php
1.2.68 src/Interfaces/ServiceInterface.php
1.2.69 src/Traits/SingletonTrait.php
1.2.70 src/Traits/HooksTrait.php
1.2.71 src/Exceptions/PluginException.php
1.2.72 src/Helpers/helpers.php – Prefixed global helper functions
1.2.73 src/Cli/ProductsCommand.php – WP-CLI commands (wp aps products ...)
1.2.74 Frontend Development Source (Vite + React + Tailwind)
1.2.75 frontend/js/admin.js
1.2.76 frontend/js/frontend.js
1.2.77 frontend/js/blocks.js
1.2.78 frontend/js/components/ProductCard.jsx
1.2.79 frontend/js/components/ProductModal.jsx
1.2.80 frontend/js/components/LoadingSpinner.jsx
1.2.81 frontend/js/components/index.js – Barrel export
1.2.82 frontend/js/utils/api.js
1.2.83 frontend/js/utils/i18n.js – WordPress JavaScript i18n helper
1.2.84 frontend/js/utils/format.js
1.2.85 frontend/styles/tailwind.css
1.2.86 frontend/styles/admin.scss
1.2.87 frontend/styles/frontend.scss
1.2.88 frontend/styles/editor.scss
1.2.89 frontend/styles/components/_buttons.scss
1.2.90 frontend/styles/components/_cards.scss
1.2.91 frontend/styles/components/_forms.scss
1.2.92 frontend/styles/components/_modals.scss
1.2.93 Gutenberg Blocks (per-block folder structure)
1.2.94 blocks/product-showcase/block.json
1.2.95 blocks/product-showcase/index.js
1.2.96 blocks/product-showcase/edit.jsx
1.2.97 blocks/product-showcase/save.jsx
1.2.98 blocks/product-showcase/style.scss
1.2.99 blocks/product-showcase/editor.scss
1.2.100 blocks/product-grid/block.json
1.2.101 blocks/product-grid/index.js
1.2.102 blocks/product-grid/edit.jsx
1.2.103 blocks/product-grid/save.jsx
1.2.104 blocks/product-grid/style.scss
1.2.105 blocks/product-grid/editor.scss
1.2.106 Build Output & Static Assets
1.2.107 assets/dist/manifest.json – Vite manifest (committed for marketplace)
1.2.108 assets/dist/css/admin-[hash].css
1.2.109 assets/dist/css/frontend-[hash].css
1.2.110 assets/dist/css/editor-[hash].css
1.2.111 assets/dist/js/admin-[hash].js
1.2.112 assets/dist/js/frontend-[hash].js
1.2.113 assets/dist/js/blocks-[hash].js
1.2.114 assets/images/logo.svg
1.2.115 assets/images/icon-128x128.png
1.2.116 assets/images/icon-256x256.png
1.2.117 assets/images/banner-772x250.png
1.2.118 assets/images/banner-1544x500.png
1.2.119 assets/images/screenshot-*.png
1.2.120 assets/images/placeholder-product.png
1.2.121 assets/fonts/ – optional custom fonts folder
1.2.122 Testing & Quality Assurance
1.2.123 tests/bootstrap.php
1.2.124 tests/wp-tests-config.php – gitignored
1.2.125 tests/unit/test-product-service.php
1.2.126 tests/integration/test-rest-endpoints.php
1.2.127 tests/fixtures/sample-products.php
1.2.128 Internationalization
1.2.129 languages/affiliate-product-showcase.pot
1.2.130 languages/affiliate-product-showcase-.po
1.2.131 languages/affiliate-product-showcase-.mo
1.2.132 Documentation
1.2.133 docs/user-guide.md
1.2.134 docs/developer-guide.md
1.2.135 docs/hooks-filters.md
1.2.136 docs/rest-api.md
1.2.137 docs/cli-commands.md
1.2.138 Continuous Integration & Automation
1.2.139 .github/workflows/ci.yml – PHPCS + PHPUnit + Frontend lint/build pipeline
1.2.140 Composer Dependencies
1.2.141 vendor/ – gitignored

1.2.142 src/Assets/Manifest.php – WordPress manifest generator (reads `assets/dist/manifest.json`)
1.2.143 src/Assets/SRI.php – Asset hashing / SRI (reads `assets/dist/sri.json`)
1.2.144 tools/compress.js (or `package.json` script) – Compression (precompress `.gz` / `.br` in `assets/dist/`)
1.2.145 vite.config.js / package.json#scripts – Bundle analyzer (on-demand via `vite-plugin-visualizer`)
1.2.146 tsconfig.json / package.json#scripts – Type checking (CI `tsc --noEmit`)
1.2.147 vite.config.js – Build integration notes (manifest, SRI, analyzer, compression hooks)

## 1.3 Git Repository — initialize Git repository and basic branches
   1.3.1 Initialize Git with main branch (Essential)
   1.3.2 Create develop branch for active development (Essential)
   1.3.3 Create feature/* branches for new features (Essential)
   1.3.4 Create hotfix/* branches for critical fixes (Essential)
   1.3.5 Create release/* branches for version releases (Essential)
   1.3.6 Branch protection rules (no direct push to main) (Essential)
   1.3.7 Require pull request reviews (minimum 1 approval) (Essential)
   1.3.8 Configure .gitignore for WordPress plugin (Essential)
   1.3.9 Set up .gitattributes for line endings and exports (Essential)

   1.3.10 Git Flow workflow implementation (Recommended)
   1.3.11 Require status checks before merge (Recommended)
   1.3.12 Require signed commits (Recommended)
   1.3.13 Semantic commit messages (conventional commits) (Recommended)
   1.3.14 Create PR templates (.github/pull_request_template.md) (Recommended)
   1.3.15 Create issue templates (.github/ISSUE_TEMPLATE/) (Recommended)
   1.3.16 Create CONTRIBUTING.md with Git workflow guide (Recommended)
   1.3.17 Create .editorconfig for consistent code formatting (Recommended)

   1.3.18 Configure branch merge strategies (squash vs merge commit) (Helpful - NOT NEEDED)
   1.3.19 Set up automated changelog generation (conventional-changelog) (Helpful - NOT NEEDED)
   1.3.20 Configure Husky pre-commit hooks (lint, test) (Helpful - NOT NEEDED)
   1.3.21 Set up commitlint for commit message validation (Helpful - NOT NEEDED)
   1.3.22 Configure GitHub Actions for automated PR checks (Helpful - NOT NEEDED)

   1.3.23 Set up semantic-release for automated versioning (Optional - NOT NEEDED)

## 1.4 Composer Configuration — composer.json and related setup
   1.4.1 Package name: `vendor/affiliate-product-showcase` (Essential)
   1.4.2 Type: `wordpress-plugin` (Essential)
   1.4.3 PSR-4 autoloading for plugin namespace (Essential)
   1.4.4 Config with platform check enabled (Essential)
   1.4.5 Config with optimize-autoloader enabled (Essential)
   1.4.6 Config with sort-packages enabled (Essential)

   1.4.7 PHP version requirement: `>=7.4 <8.4` (Recommended)
   1.4.8 WordPress version requirement: `>=6.4` (Recommended)
   1.4.9 PSR-4 autoloading for tests namespace (Recommended)
   1.4.10 Autoload optimization (Recommended)
   1.4.11 Production require (empty or minimal) (Recommended)
   1.4.12 Scripts with test, lint, analyze commands (Recommended)
   1.4.13 Scripts with build, package commands (Recommended)

   1.4.14 Classmap autoloading for legacy code (Helpful)
   1.4.15 Dev require: `phpunit/phpunit ^9.5` (Helpful)
   1.4.16 Dev require: `phpstan/phpstan ^1.10` (Helpful)
   1.4.17 Dev require: `vimeo/psalm ^5.0` (Helpful)
   1.4.18 Dev require: `squizlabs/php_codesniffer ^3.7` (Helpful)
   1.4.19 Dev require: `wp-coding-standards/wpcs ^3.0` (Helpful)
   1.4.20 Dev require: `phpcompatibility/phpcompatibility-wp ^2.1` (Helpful)
   1.4.21 Dev require: `mockery/mockery ^1.5` (Helpful)
   1.4.22 Dev require: `fakerphp/faker ^1.23` (Helpful)

   1.4.23 Dev require: `automattic/vipwpcs ^3.0` (Optional)
   1.4.24 Exclude vendor-dir from packaging (Optional)


## 1.5 NPM Configuration & package.json Checklist — package.json and lockfiles


### 1.5.1 Implementation Checklist - Basic Metadata
- [ ] 1.5.1.1 Field: `name` - value: "affiliate-product-showcase"
- [ ] 1.5.1.2 Field: `version` - value: "1.0.0"
- [ ] 1.5.1.3 Field: `description` - value: "Affiliate Product Showcase — admin UI built with Vite, React, TypeScript and Tailwind for WordPress"
- [ ] 1.5.1.4 Field: `private` - value: true
- [ ] 1.5.1.5 Field: `license` - value: "GPL-2.0-or-later"
- [ ] 1.5.1.6 Field: `author` - value: "Your Name <you@example.com>"
- [ ] 1.5.1.7 Field: `homepage` - value: project homepage or repo README URL
- [ ] 1.5.1.8 Field: `repository` - value: `{ type: "git", url: "https://github.com/your-org/affiliate-product-showcase.git" }`
- [ ] 1.5.1.9 Field: `bugs` - value: repo issues URL (optional)
- [ ] 1.5.1.10 Field: `keywords` - value: [ "wordpress", "plugin", "vite", "react", "typescript", "tailwind" ]
- [ ] 1.5.1.11 Field: `main` - value: "index.js" or plugin bootstrap entry (kept for packaging)
- [ ] 1.5.1.12 Field: `files` - value: [ "dist/", "build/", "src/", "languages/", "readme.txt", "readme.html", "index.php" ]
- [ ] 1.5.1.13 Field: `sideEffects` - value: false (see decisions)
- [ ] 1.5.1.14 Field: `engines` - value: `{ "node": ">=18.0.0", "npm": ">=9.0.0" }`

### 1.5.2 Implementation Checklist - Policy & Constraints (project requirements)
- [ ] 1.5.2.1 Policy: All versions use caret ranges (`^`) for `dependencies` and `devDependencies`.
- [ ] 1.5.2.2 Policy: Offline / no-CDN operation — no runtime asset loading from external URLs (no unpkg/jsDelivr/CDN fonts/icons).
- [ ] 1.5.2.3 Policy: Bundle all JS/CSS locally via Vite output; PHP enqueues only local files from `dist/` (and/or `assets/dist/`).
- [ ] 1.5.2.4 Policy: Avoid deps or build steps that download binaries at install-time unless strictly necessary.

### 1.5.3 Implementation Checklist - Required Top-level Fields (explicitly present)
- [ ] 1.5.3.1 Field: `scripts` - value: includes `dev`, `build`, `preview`, `lint`, `format`, `type-check`, `clean` (plus optional `prepare`).
- [ ] 1.5.3.2 Field: `dependencies` - value: runtime packages list below.
- [ ] 1.5.3.3 Field: `devDependencies` - value: build/lint/tooling packages list below.

### 1.5.4 Implementation Checklist - Production Dependencies (runtime)
- [ ] 1.5.4.1 Package: `react` - version: `^18.x.x` - purpose: UI library for admin React UI
- [ ] 1.5.4.2 Package: `react-dom` - version: `^18.x.x` - purpose: DOM renderer for React
- [ ] 1.5.4.3 Package: `@wordpress/element` - version: `^x.x.x` - purpose: WP-compatible React abstraction (use when integrating with WP packages)
- [ ] 1.5.4.4 Package: `@wordpress/components` - version: `^x.x.x` - purpose: WP UI components for consistency with WP admin
- [ ] 1.5.4.5 Package: `@wordpress/api-fetch` - version: `^x.x.x` - purpose: interact with WP REST API (auth, nonce handling)
- [ ] 1.5.4.6 Package: `@wordpress/i18n` - version: `^x.x.x` - purpose: translations and localization helpers
- [ ] 1.5.4.7 Package: `@wordpress/data` - version: `^x.x.x` - purpose: centralized state management (optional)
- [ ] 1.5.4.8 Package: `@wordpress/hooks` - version: `^x.x.x` - purpose: WP-style hooks/pubsub utilities
- [ ] 1.5.4.9 Package: `@heroicons/react` - version: `^x.x.x` - purpose: optional icon set for UI
- [ ] 1.5.4.10 Package: `clsx` - version: `^1.x.x` - purpose: helper for conditional classNames

Notes: Replace `^x.x.x` with the verified latest stable `dist-tags.latest` versions from npm as of Jan 2026.

### 1.5.5 Implementation Checklist - Development Dependencies (build-time)
- [ ] 1.5.5.1 Package: `vite` - version: `^5.x.x` - purpose: dev server and bundler (Vite 5)
- [ ] 1.5.5.2 Package: `typescript` - version: `^5.x.x` - purpose: type checking and TS compilation
- [ ] 1.5.5.3 Package: `tailwindcss` - version: `^3.4.x` - purpose: utility-first CSS framework
- [ ] 1.5.5.4 Package: `postcss` - version: `^8.x.x` - purpose: PostCSS runner
- [ ] 1.5.5.5 Package: `postcss-import` - version: `^x.x.x` - purpose: allow `@import` in PostCSS
- [ ] 1.5.5.6 Package: `postcss-nested` - version: `^x.x.x` - purpose: nested CSS rules support
- [ ] 1.5.5.7 Package: `autoprefixer` - version: `^10.x.x` - purpose: vendor prefixing for CSS
- [ ] 1.5.5.8 Package: `eslint` - version: `^8.x.x` - purpose: linting JS/TS
- [ ] 1.5.5.9 Package: `@wordpress/eslint-plugin` - version: `^x.x.x` - purpose: WP-specific lint rules
- [ ] 1.5.5.10 Package: `@typescript-eslint/parser` - version: `^6.x.x` - purpose: parse TS for ESLint
- [ ] 1.5.5.11 Package: `@typescript-eslint/eslint-plugin` - version: `^6.x.x` - purpose: TS-specific lint rules
- [ ] 1.5.5.12 Package: `eslint-config-prettier` - version: `^x.x.x` - purpose: disable formatting rules conflicting with Prettier
- [ ] 1.5.5.13 Package: `eslint-plugin-prettier` - version: `^x.x.x` - purpose: run Prettier as ESLint rule (optional)
- [ ] 1.5.5.14 Package: `eslint-plugin-react` - version: `^7.x.x` - purpose: React-specific lint rules
- [ ] 1.5.5.15 Package: `eslint-plugin-react-hooks` - version: `^4.x.x` - purpose: React hooks lint rules
- [ ] 1.5.5.16 Package: `prettier` - version: `^3.x.x` or `^2.x.x` (verify stable) - purpose: code formatting
- [ ] 1.5.5.17 Package: `stylelint` - version: `^15.x.x` - purpose: CSS linting
- [ ] 1.5.5.18 Package: `stylelint-config-standard` - version: `^33.x.x` - purpose: base stylelint rules
- [ ] 1.5.5.19 Package: `stylelint-config-tailwindcss` - version: `^0.x.x` or latest compatible - purpose: Tailwind-aware linting
- [ ] 1.5.5.20 Package: `rimraf` - version: `^5.x.x` - purpose: cross-platform rm -rf for `clean`
- [ ] 1.5.5.21 Package: `concurrently` - version: `^8.x.x` - purpose: run multiple scripts locally (if needed)
- [ ] 1.5.5.22 Package: `cross-env` - version: `^7.x.x` - purpose: set env vars cross-platform in scripts
- [ ] 1.5.5.23 Package: `vite-plugin-checker` - version: `^x.x.x` - purpose: run type-checking / diagnostics in Vite dev
- [ ] 1.5.5.24 Package: `@types/react` - version: `^18.x.x` - purpose: TypeScript types for React
- [ ] 1.5.5.25 Package: `@types/react-dom` - version: `^18.x.x` - purpose: TypeScript types for ReactDOM

Notes: Use caret ranges for all versions. Verify `prettier` major (v3 released 2023-2024) compatibility with `eslint-config-prettier` and other plugins.

### 1.5.6 Implementation Checklist - Scripts
- [ ] 1.5.6.1 Script: `dev` - command: `cross-env NODE_ENV=development vite` - purpose: start Vite dev server with HMR
- [ ] 1.5.6.2 Script: `build` - command: `cross-env NODE_ENV=production vite build` - purpose: produce production `dist/` assets and manifest
- [ ] 1.5.6.3 Script: `preview` - command: `cross-env NODE_ENV=production vite preview --port 5173` - purpose: locally preview built assets
- [ ] 1.5.6.4 Script: `clean` - command: `rimraf dist build .vite .turbo` - purpose: remove build outputs and caches
- [ ] 1.5.6.5 Script: `type-check` - command: `tsc --noEmit` - purpose: run full TypeScript check
- [ ] 1.5.6.6 Script: `lint` - command: `eslint --max-warnings=0 "src/**/*.{ts,tsx,js,jsx}"` - purpose: enforce linting rules and fail on warnings
- [ ] 1.5.6.7 Script: `format` - command: `prettier --write "src/**/*.{ts,tsx,js,jsx,css,scss,md,json}"` - purpose: format codebase
- [ ] 1.5.6.8 Script: `prepare` - command: `npm run build` or `node ./scripts/prepare-dist.js` - purpose: produce artifacts for packaging
- [ ] 1.5.6.9 Script: `check` (optional) - command: `concurrently "npm:dev" "npm:watch:types"` - purpose: run multiple watchers during dev (if desired)

### 1.5.7 Implementation Checklist - Configuration Fields
- [ ] 1.5.7.1 Field: `engines.node` - value: ">=18.0.0"
- [ ] 1.5.7.2 Field: `engines.npm` - value: ">=9.0.0"
- [ ] 1.5.7.3 Field: `files` - value: [ "dist/", "build/", "src/", "languages/", "readme.txt", "readme.html", "index.php" ]
- [ ] 1.5.7.4 Field: `sideEffects` - value: false (list global CSS files in `sideEffects` if needed)
- [ ] 1.5.7.5 Field: `repository` - value: as above
- [ ] 1.5.7.6 Field: `bugs` - value: repo issues URL (optional)
- [ ] 1.5.7.7 Field: `keywords` - value: as above
- [ ] 1.5.7.8 Field: `homepage` - value: repo or plugin page
- [ ] 1.5.7.9 Field: `private` - value: true
- [ ] 1.5.7.10 Field: `license` - value: "GPL-2.0-or-later"
- [ ] 1.5.7.11 Field: `author` - value: as above

### 1.5.8 Implementation Checklist - Excluded Packages (commented placeholders)
- [ ] 1.5.8.1 Comment: Testing stack — `vitest`, `@testing-library/react`, `@testing-library/jest-dom` — add when implementing unit/DOM tests; recommended versions to be chosen when opting into testing.
- [ ] 1.5.8.2 Comment: Git hooks — `husky`, `lint-staged` — add when enabling pre-commit checks; only add if you want local commit-time formatting/linting.
- [ ] 1.5.8.3 Comment: Bundle analysis — `rollup-plugin-visualizer`, `vite-plugin-visualizer` — add temporarily to analyze bundle size during optimization.
- [ ] 1.5.8.4 Comment: Tailwind plugins — `@tailwindcss/forms`, `@tailwindcss/typography`, `@tailwindcss/aspect-ratio` — add only when those features are required.
- [ ] 1.5.8.5 Comment: Optional testing CI helpers — `playwright`, `cypress` — add when adding e2e tests.


### 1.5.9 Version Verification (process items)
- [ ] 1.5.9.1 Verify `dist-tags.latest` for each package on npmjs.org (or via `npm view <pkg> dist-tags.latest`) and record the version (stable) as of Jan 2026.
- [ ] 1.5.9.2 Confirm `vite@^5.x` compatibility with chosen `vite-plugin-checker` version.
- [ ] 1.5.9.3 Confirm `typescript@^5.x` compatibility with `@types/react` and `@types/react-dom`.
- [ ] 1.5.9.4 Confirm `prettier` major version compatibility with ESLint plugins and `eslint-config-prettier`.
- [ ] 1.5.9.5 Confirm `stylelint-config-tailwindcss` compatibility with chosen `tailwindcss@3.4.x`.
- [ ] 1.5.9.6 Ensure no peerDependency conflicts between `@wordpress/*` packages and React major version (use WP packages that support React 18).
- [ ] 1.5.9.7 Record all verified versions into a draft `package.json` before running `npm install`.

### 1.5.10 Version Verification (per-package checklist)
- [ ] 1.5.10.1 Verify stable version: `react` (React 18 line)
- [ ] 1.5.10.2 Verify stable version: `react-dom` (React 18 line)
- [ ] 1.5.10.3 Verify stable version: `@wordpress/element`
- [ ] 1.5.10.4 Verify stable version: `@wordpress/components`
- [ ] 1.5.10.5 Verify stable version: `@wordpress/api-fetch`
- [ ] 1.5.10.6 Verify stable version: `@wordpress/i18n`
- [ ] 1.5.10.7 Verify stable version: `@wordpress/data`
- [ ] 1.5.10.8 Verify stable version: `@wordpress/hooks`
- [ ] 1.5.10.9 Verify stable version: `@heroicons/react`
- [ ] 1.5.10.10 Verify stable version: `clsx`
- [ ] 1.5.10.11 Verify stable version: `vite` (Vite 5 line)
- [ ] 1.5.10.12 Verify stable version: `typescript`
- [ ] 1.5.10.13 Verify stable version: `tailwindcss` (3.4 line)
- [ ] 1.5.10.14 Verify stable version: `postcss`
- [ ] 1.5.10.15 Verify stable version: `postcss-import`
- [ ] 1.5.10.16 Verify stable version: `postcss-nested`
- [ ] 1.5.10.17 Verify stable version: `autoprefixer`
- [ ] 1.5.10.18 Verify stable version: `eslint`
- [ ] 1.5.10.19 Verify stable version: `@wordpress/eslint-plugin`
- [ ] 1.5.10.20 Verify stable version: `@typescript-eslint/parser`
- [ ] 1.5.10.21 Verify stable version: `@typescript-eslint/eslint-plugin`
- [ ] 1.5.10.22 Verify stable version: `eslint-config-prettier`
- [ ] 1.5.10.23 Verify stable version: `eslint-plugin-prettier`
- [ ] 1.5.10.24 Verify stable version: `eslint-plugin-react`
- [ ] 1.5.10.25 Verify stable version: `eslint-plugin-react-hooks`
- [ ] 1.5.10.26 Verify stable version: `prettier`
- [ ] 1.5.10.27 Verify stable version: `stylelint`
- [ ] 1.5.10.28 Verify stable version: `stylelint-config-standard`
- [ ] 1.5.10.29 Verify stable version: `stylelint-config-tailwindcss`
- [ ] 1.5.10.30 Verify stable version: `rimraf`
- [ ] 1.5.10.31 Verify stable version: `concurrently`
- [ ] 1.5.10.32 Verify stable version: `cross-env`
- [ ] 1.5.10.33 Verify stable version: `vite-plugin-checker`
- [ ] 1.5.10.34 Verify stable version: `@types/react` (match React 18)
- [ ] 1.5.10.35 Verify stable version: `@types/react-dom` (match React 18)

---

Summary answers (after checklist)

1) Total count of checklist items
- 58 actionable checklist lines (checkboxes) across all sections.

2) Estimated time to implement (assuming versions verified)
- Quick implementation (create/update `package.json` + scaffold basic config files, no installs): 30–60 minutes.
- Full implementation (pin versions, install, scaffold `vite.config.ts`, `postcss.config.cjs`, `tailwind.config.cjs`, `tsconfig.json`, basic `src/` + PHP enqueue + lint config): 2–3 hours.
- Verification + testing + fix peer conflicts: additional 1–2 hours.

3) Potential version conflicts or compatibility issues to watch for
- `@wordpress/*` packages historically align to specific React versions inside WordPress core; ensure the chosen `@wordpress/*` versions are compatible with React 18 (some older `@wordpress` packages may expect older React APIs).
- `vite-plugin-checker` major release compatibility with Vite 5 — prefer a plugin release that documents Vite 5 support.
- `prettier@3` vs older ESLint integrations — some `eslint-plugin-prettier` or `eslint-config-prettier` versions may lag; verify compatibility matrix.
- `stylelint-config-tailwindcss` naming/major versions changed historically — pick a config version compatible with Tailwind 3.4.
- `postcss-import` / `postcss` versions must be compatible; PostCSS 8 is the stable base for Tailwind 3.x.
- TypeScript (`^5.x`) and `@types/react` versions must match React major line to avoid mismatched types.

4) Recommended order of implementation
- Step 1: Version verification — run `npm view <pkg> dist-tags.latest` or consult npm to record exact stable versions (Jan 2026). (Essential)
- Step 2: Draft `package.json` with caret ranges using verified versions (do not `npm install` yet if you want review).
- Step 3: Scaffold config files: `tsconfig.json`, `vite.config.ts`, `postcss.config.cjs`, `tailwind.config.cjs`.
- Step 4: Add lint/format configs: `.eslintrc.cjs`, `.prettierrc`, `.stylelintrc.cjs`.
- Step 5: Create a minimal `src/admin/index.tsx`, `src/styles/index.css`, and PHP enqueue stub that reads Vite manifest.
- Step 6: Run `npm install` and then `npm run dev` — iterate on any peerDependency issues.
- Step 7: Add CI/test tooling (vitest, testing-library) and git hooks (husky, lint-staged) only after core build works.
- Step 8: Run bundle analysis and optimize if needed.

---

## 1.6 Configuration Files — `.gitignore`, `phpcs.xml`, `phpunit.xml`, `.editorconfig`, `.dockerignore`
   1.6.1 `.gitignore` with comprehensive exclusions
   1.6.2 `.gitignore` excludes: `.env*`, `node_modules/`, `vendor/`, `dist/`, `*.log`, `.DS_Store`
   1.6.3 `.gitignore` excludes: `docker/mysql/`, `docker/redis/`, `*.zip`, `*.tar.gz`
   1.6.4 `.editorconfig` with WordPress standards
   1.6.5 `.editorconfig` settings: indent_style=tab, indent_size=4, end_of_line=lf, charset=utf-8
   1.6.6 `.dockerignore` mirroring `.gitignore`
   1.6.7 `phpcs.xml` with WordPress-Core, WordPress-Extra, WordPress-Docs rulesets
   1.6.8 `phpcs.xml` with WordPress-VIP-Go ruleset
   1.6.9 `phpcs.xml` with PHPCompatibility ruleset
   1.6.10 `phpcs.xml` with minimum_supported_wp_version=6.4
   1.6.11 `phpcs.xml` with testVersion=7.4-8.3
   1.6.12 `phpcs.xml` with text domain check enabled
   1.6.13 `phpcs.xml` with prefix check enabled
   1.6.14 `phpunit.xml` with coverage settings
   1.6.15 `phpunit.xml` with 95% coverage requirement
   1.6.16 `phpunit.xml` with testdox output
   1.6.17 `phpunit.xml` test-setup file
   1.6.18 `phpstan.neon` with level 8
   1.6.19 `phpstan.neon` with WordPress stubs
   1.6.20 `phpstan.neon` with strict rules enabled
   1.6.21 `psalm.xml` with errorLevel 1
   1.6.22 `psalm.xml` with WordPress stubs
   1.6.23 `psalm.xml` with totallyTyped enabled
   1.6.24 `.eslintrc.json` with WordPress config
   1.6.25 `.eslintrc.json` with TypeScript support
   1.6.26 `.eslintrc.json` with React hooks rules
   1.6.27 `.prettierrc.json` with WordPress code style
   1.6.28 `stylelint.config.js` with standard + Tailwind config
   1.6.29 `tailwind.config.js` with custom theme
   1.6.30 `tailwind.config.js` with content paths
   1.6.31 `tailwind.config.js` with safelist for dynamic classes
   1.6.32 `postcss.config.js` with Tailwind + Autoprefixer
   1.6.33 `vite.config.ts` with React plugin
   1.6.34 `vite.config.ts` with build output to dist/
   1.6.35 `vite.config.ts` with chunk splitting
   1.6.36 `vite.config.ts` with asset inlining threshold
   1.6.37 `tsconfig.json` with strict mode
   1.6.38 `tsconfig.json` with WordPress globals
   1.6.39 `tsconfig.json` with path aliases
   1.6.40 `vite.config.*` manifest handling – enable `manifest: true` and set `build.manifest` output to `assets/dist/manifest.json` for PHP mapping.
   1.6.41 SRI/hash generation – build hook or separate `tools/generate-sri.js` to produce `assets/dist/sri.json` for server-side SRI verification.
   1.6.42 Pre-compression output – optional build step producing `.gz` and `.br` files in `assets/dist/` and sample Nginx `docker/nginx/` config to serve precompressed assets.
   1.6.43 Bundle analyzer plugin – `vite-plugin-visualizer` config entry that runs only when `--mode analyze` or `npm run analyze` is invoked.
   1.6.44 Typecheck integration – CI `npm run typecheck` step using `tsc --noEmit` (if TS is used); mention IDE typechecking still recommended.

## 1.7 Environment Variables — .env for dev, WP Options fallback
   1.7.1 `PLUGIN_DEV_MODE` - Enable/disable dev features
   1.7.2 `PLUGIN_DEBUG` - Enable debug logging
   1.7.3 `DB_HOST` - Database host (fallback to WP constant)
   1.7.4 `DB_NAME` - Database name (fallback to WP constant)
   1.7.5 `DB_USER` - Database user (fallback to WP constant)
   1.7.6 `DB_PASSWORD` - Database password (fallback to WP constant)
   1.7.7 `DB_CHARSET` - Database charset (fallback to WP constant)
   1.7.8 `DB_COLLATE` - Database collation (fallback to WP constant)
   1.7.9 `REDIS_HOST` - Redis host for caching
   1.7.10 `REDIS_PORT` - Redis port
   1.7.11 `REDIS_DATABASE` - Redis database number
   1.7.12 WordPress Options API fallback for all settings
   1.7.13 Function `get_plugin_option($key, $default)` with env fallback
   1.7.14 Function `update_plugin_option($key, $value)` uses WP Options API
   1.7.15 Never commit `.env` file
   1.7.16 `.env` excluded from packaging script
   1.7.17 Documentation: 'Use .env for local dev only'
   1.7.18 Documentation: 'Production uses WP Options API'
   1.7.19 Security note: 'Never store API keys in .env on production'

## 1.8 WordPress Path/URL Functions — canonical helpers for URLs and paths
   1.8.1 Use `plugins_url()` for all asset URLs
   1.8.2 Use `plugin_dir_path(__FILE__)` for file paths
   1.8.3 Use `plugin_dir_url(__FILE__)` for directory URLs
   1.8.4 Use `plugin_basename(__FILE__)` for plugin identification
   1.8.5 Use `get_home_url()` for site URL
   1.8.6 Use `admin_url()` for admin URLs
   1.8.7 Use `rest_url()` for REST API URLs
   1.8.8 Use `wp_upload_dir()` for upload paths
   1.8.9 Never hardcode paths like `/wp-content/plugins/`
   1.8.10 Never hardcode domains like `https://example.com`
   1.8.11 Support subdirectory installations
   1.8.12 Support domain mapping
   1.8.13 Support multisite network installations
   1.8.14 Support custom wp-content directory names

## 1.9 Database Table Prefix — configurable DB prefix and migration notes
   1.9.1 Use `$wpdb->prefix` for all table names
   1.9.2 Custom tables: `{$wpdb->prefix}affiliate_products_meta`
   1.9.4 Custom tables: `{$wpdb->prefix}affiliate_products_submissions`
   1.9.5 Never hardcode `wp_` prefix
   1.9.6 Support custom table prefixes
   1.9.7 Support multisite base prefix
   1.9.8 Table creation uses `$wpdb->get_charset_collate()`
   1.9.9 Use `$wpdb->prepare()` for all queries
   1.9.10 Index creation on custom tables
   1.9.11 Foreign key relationships where appropriate
   1.9.12 Database version tracking for migrations
   1.9.13 Rollback capability for failed migrations
   1.9.14 Cleanup old migration data
   - 1.9.3 TODO (auto-inserted)

## 1.10 Standalone & Privacy Guarantees — standalone mode, data handling, privacy
   1.10.1 Zero external HTTP requests during normal operation
   1.10.2 No CDNs (Cloudflare, jsDelivr, unpkg, etc.)
   1.10.3 No Google Fonts or any web font service
   1.10.4 No Font Awesome or external icon libraries
   1.10.5 No Google Analytics, Mixpanel, Segment, etc.
   1.10.6 No tracking pixels or beacons
   1.10.7 No external image URLs (reject on submission)
   1.10.8 No external JS libraries loaded via CDN
   1.10.9 No update checker (completely disabled)
   1.10.10 No license verification servers
   1.10.11 No phone-home behavior of any kind
   1.10.12 All assets bundled and loaded locally
   1.10.13 System font stack only
   1.10.14 Local SVG icons only
   1.10.15 No built-in analytics/tracking (handled by Site Kit / separate plugin)
   1.10.16 No external API calls except user-clicked affiliate links
   1.10.17 CAPTCHA disabled by default (optional, no external service code)
   1.10.18 README prominently states '100% Standalone - No External Dependencies'
   1.10.19 README lists all bundled vs. excluded dependencies

## 1.11 Code Quality Tools — PHPCS, PHPUnit, linters and config
   1.11.1 Husky pre-commit hooks installation
   1.11.2 Lint-staged configuration for staged files only
   1.11.3 Pre-commit: Run PHPCS on PHP files
   1.11.4 Pre-commit: Run PHPStan on PHP files
   1.11.5 Pre-commit: Run Psalm on PHP files
   1.11.6 Pre-commit: Run ESLint on JS/TS files
   1.11.7 Pre-commit: Run Prettier on JS/TS files
   1.11.8 Pre-commit: Run Stylelint on CSS files
   1.11.9 Pre-commit: Run PHPUnit tests
   1.11.10 Pre-commit: Check for `strict_types=1` in all PHP files
   1.11.11 Pre-commit: Check for PHPDoc blocks
   1.11.12 Pre-commit: Prevent commits with `var_dump`, `dd`, `console.log`
   1.11.13 Pre-commit: Check for merge conflict markers
   1.11.14 Pre-commit: Check file size limits
   1.11.15 Pre-commit: Check for trailing whitespace
   1.11.16 Pre-push: Run full test suite
   1.11.17 Pre-push: Verify code coverage ≥95%
   1.11.18 Pre-push: Run security scans
   1.11.19 Commit-msg hook: Validate conventional commits format

## 1.12 README Documentation — installation, local setup, and developer notes
   1.12.1 Plugin name and tagline
   1.12.2 '100% Standalone' badge
   1.12.3 'Zero External Dependencies' badge
   1.12.4 'Privacy-First' badge
   1.12.5 'Enterprise-Grade' badge
   1.12.6 Feature highlights list
   1.12.7 Screenshots
   1.12.8 Requirements: PHP, WordPress, MySQL versions
   1.12.9 Installation instructions (manual + WP admin)
   1.12.10 Quick start guide
   1.12.11 Configuration guide
   1.12.12 Shortcode documentation
   1.12.13 REST API documentation
   1.12.14 WP-CLI commands documentation
   1.12.15 Hooks and filters reference
   1.12.16 Troubleshooting
   1.12.17 FAQ
   1.12.18 Changelog with semantic versioning
   1.12.19 Contributing guidelines
   1.12.20 License information (GPL v2 or later)
   1.12.21 Credits and acknowledgments
   1.12.22 Support channels
   1.12.23 Donation/sponsorship links
   1.12.24 Security policy and reporting
   1.12.25 Privacy policy template for users

# Step 2 — Step 2 — Content Types & Taxonomies

## 2.1 Custom Post Type: affiliate_product
   2.1.1 Post type slug: `affiliate_product`
   2.1.2 Labels: singular 'Affiliate Product', plural 'Affiliate Products'
   2.1.3 Public: true
   2.1.4 Publicly queryable: true
   2.1.5 Show UI: true
   2.1.6 Show in menu: true with custom icon
   2.1.7 Menu position: 25 (below Comments)
   2.1.8 Menu icon: custom SVG dashicon
   2.1.9 Show in nav menus: true
   2.1.10 Show in admin bar: true
   2.1.11 Capability type: `affiliate_product`
   2.1.12 Custom capabilities: edit, read, delete, edit_others, publish, read_private
   2.1.13 Map meta cap: true
   2.1.14 Hierarchical: false
   2.1.15 Supports: title, editor, thumbnail, excerpt, author, revisions
   2.1.16 Has archive: true
   2.1.17 Archive slug: `affiliate-products`
   2.1.18 Rewrite: true with custom slug
   2.1.19 Query var: true
   2.1.20 Can export: true
   2.1.21 Delete with user: false
   2.1.22 Show in REST: true
   2.1.23 REST base: `affiliate-products`
   2.1.24 REST controller: custom controller class
   2.1.25 REST namespace: `affiliate-showcase/v1`
   2.1.26 Custom REST fields for meta data
   2.1.29 Custom REST endpoint for submissions
   2.1.30 Exclude from search: false (but customizable)
   2.1.31 Template file: `single-affiliate_product.php`
   2.1.32 Archive template: `archive-affiliate_product.php`
   2.1.33 Custom query vars support
   2.1.34 Custom orderby support
   2.1.35 Featured product flag
   2.1.36 Trending product flag
   2.1.37 New arrival flag
   2.1.38 Best seller flag
   2.1.39 Sale/discount flag
   2.1.40 Out of stock flag
   2.1.41 Status: draft, pending, publish, private
   2.1.42 Custom status: submitted (for frontend submissions)
   2.1.43 Custom status: approved
   2.1.44 Custom status: rejected
   2.1.45 Post format support: standard only
   2.1.46 Revisions limit: 10
   2.1.47 Autosave support
   2.1.48 Trash support
   2.1.49 Duplicate product functionality
   - 2.1.28 TODO (auto-inserted)
   - 2.1.27 TODO (auto-inserted)

## 2.2 Taxonomy: Product Categories
   2.2.1 Taxonomy slug: `product_category`
   2.2.2 Labels: singular 'Category', plural 'Categories'
   2.2.3 Hierarchical: true
   2.2.4 Public: true
   2.2.5 Show UI: true
   2.2.6 Show in menu: true
   2.2.7 Show in nav menus: true
   2.2.8 Show in admin bar: true
   2.2.9 Show in REST: true
   2.2.10 REST base: `product-categories`
   2.2.11 Show tagcloud: true
   2.2.12 Show admin column: true
   2.2.13 Query var: true
   2.2.14 Rewrite: true with custom slug
   2.2.15 Capabilities: manage_categories, edit_categories, delete_categories, assign_categories
   2.2.16 Meta box callback: custom hierarchical UI
   2.2.17 Update count callback: custom function
   2.2.18 Default term: 'Uncategorized'
   2.2.19 Support for term meta
   2.2.20 Term meta: category icon (SVG upload)
   2.2.21 Term meta: category color (hex picker)
   2.2.22 Term meta: category image (thumbnail)
   2.2.23 Term meta: display order (sortable)
   2.2.24 Term meta: featured flag
   2.2.25 Term meta: hide from menu flag
   2.2.26 Term meta: SEO title
   2.2.27 Term meta: SEO description
   2.2.28 Category archive template
   2.2.29 Category permalink structure
   2.2.30 Breadcrumb support
   2.2.31 Parent/child relationship display
   2.2.32 Product count display
   2.2.33 Empty category handling
   2.2.34 Category quick edit
   2.2.35 Category bulk edit
   2.2.36 Category sorting/ordering UI
   2.2.37 Category search functionality
   2.2.38 Category filter in admin list
   2.2.39 Category assignment on product edit
   2.2.40 Multiple category assignment
   2.2.41 Category-based product filtering
   2.2.42 Category widget for sidebar
   2.2.43 Category shortcode
   2.2.44 Category REST endpoints

## 2.3 Taxonomy: Product Tags
   2.3.1 Taxonomy slug: `product_tag`
   2.3.2 Labels: singular 'Tag', plural 'Tags'
   2.3.3 Hierarchical: false
   2.3.4 Public: true
   2.3.5 Show UI: true
   2.3.6 Show in menu: true
   2.3.7 Show in nav menus: true
   2.3.8 Show in admin bar: true
   2.3.9 Show in REST: true
   2.3.10 REST base: `product-tags`
   2.3.11 Show tagcloud: true
   2.3.12 Show admin column: true
   2.3.13 Query var: true
   2.3.14 Rewrite: true with custom slug
   2.3.15 Meta box callback: custom tag UI with autocomplete
   2.3.16 Tag suggestions based on content
   2.3.17 Popular tags display
   2.3.18 Tag meta: tag color
   2.3.19 Tag meta: tag icon
   2.3.20 Tag meta: featured flag
   2.3.21 Tag cloud widget
   2.3.22 Tag archive template
   2.3.23 Tag search functionality
   2.3.24 Tag assignment on product edit
   2.3.25 Multiple tag assignment
   2.3.26 Tag-based product filtering
   2.3.27 Tag shortcode
   2.3.28 Tag REST endpoints
   2.3.29 Tag import/export

## 2.4 Taxonomy: Product Ribbons
   2.4.1 Taxonomy slug: `product_ribbon`
   2.4.2 Labels: singular 'Ribbon', plural 'Ribbons'
   2.4.3 Hierarchical: false
   2.4.4 Public: true
   2.4.5 Show UI: true
   2.4.6 Show in menu: true
   2.4.7 Show in REST: true
   2.4.8 REST base: `product-ribbons`
   2.4.9 Show admin column: true
   2.4.10 Query var: true
   2.4.11 Rewrite: true
   2.4.12 Meta box: custom UI for ribbon selection
   2.4.13 Ribbon meta: ribbon text (e.g., 'Best Seller', 'New', 'Sale')
   2.4.14 Ribbon meta: ribbon color (background)
   2.4.15 Ribbon meta: text color
   2.4.16 Ribbon meta: ribbon position (top-left, top-right, bottom-left, bottom-right)
   2.4.17 Ribbon meta: ribbon style (badge, corner, banner, diagonal)
   2.4.18 Ribbon meta: icon (SVG or Heroicon name)
   2.4.19 Ribbon meta: display order/priority
   2.4.20 Ribbon meta: expiration date
   2.4.21 Ribbon meta: start date (scheduled ribbons)
   2.4.22 Ribbon preview in admin
   2.4.23 Multiple ribbons per product (configurable limit)
   2.4.24 Ribbon quick edit
   2.4.25 Ribbon shortcode
   2.4.26 Ribbon REST endpoints
   2.4.27 Ribbon import/export
   2.4.28 Pre-defined ribbon templates
   2.4.29 Custom ribbon CSS class support

## 2.5 Type Hints & PHPDoc
   2.5.1 `declare(strict_types=1);` in every PHP file
   2.5.2 Full PHPDoc block on every class
   2.5.3 Full PHPDoc block on every method
   2.5.4 Full PHPDoc block on every function
   2.5.5 `@package` tag with plugin namespace
   2.5.6 `@since` tag with version number
   2.5.7 `@version` tag when updated
   2.5.8 `@param` tag with type, name, description
   2.5.9 `@return` tag with type and description
   2.5.10 `@throws` tag for all exceptions
   2.5.11 `@global` tag for global variables
   2.5.12 `@see` tag for related functions
   2.5.13 `@link` tag for external references
   2.5.14 `@uses` tag for dependencies
   2.5.15 `@used-by` tag for callers
   2.5.16 Type hints for all parameters
   2.5.17 Return type declarations for all methods
   2.5.18 Nullable type support (`?Type`)
   2.5.19 Union type support (PHP 8.0+)
   2.5.20 Mixed type where appropriate
   2.5.21 Void return type where applicable
   2.5.22 Array shape documentation in PHPDoc
   2.5.23 Generic type documentation with `@template`
   2.5.24 PHPStan level 8 compliance
   2.5.25 Psalm errorLevel 1 compliance
   2.5.26 No `@suppressWarnings` without justification
   2.5.27 No `mixed` type without documentation
   2.5.28 Interface documentation
   2.5.29 Trait documentation
   2.5.30 Abstract class documentation
   2.5.31 Constant documentation
   2.5.32 Property documentation
   2.5.33 Hook documentation (`@action`, `@filter`)
   2.5.34 Example code in PHPDoc
   2.5.35 Deprecation notices with `@deprecated`
   2.5.36 TODO comments with ticket references
   2.5.37 FIXME comments with priority
   2.5.38 Code generation from PHPDoc (API docs)
   2.5.39 IDE autocomplete support
   2.5.40 Static analysis integration

# Step 3 — Step 3 — Admin UI & Meta

## 3.1 Product Data Meta Box
   3.1.1 Meta box title: 'Product Information'
   3.1.2 Meta box context: normal
   3.1.3 Meta box priority: high
   3.1.4 Tabbed interface: General, Pricing, Links, Features, Media, Advanced
   3.1.5 Tab: General
   3.1.6 Tab: Pricing
   3.1.7 Tab: Links
   3.1.8 Tab: Features
   3.1.9 Tab: Media
   3.1.10 Tab: Advanced
   3.1.11 Meta box save validation
   3.1.12 Meta box nonce verification
   3.1.13 Meta box capability check
   3.1.14 Meta box AJAX autosave support
   3.1.15 Meta box field dependency logic (show/hide based on other fields)
   3.1.16 Meta box unsaved changes warning
   3.1.17 Meta box help tooltips
   3.1.18 Meta box field error display
   3.1.19 Meta box bulk edit support

## 3.2 Analytics / Tracking (Removed)
   3.2.1 This plugin does not include built-in analytics or tracking functionality.

## 3.3 Submission Status Meta Box
   3.3.1 Meta box title: 'Submission Details'
   3.3.2 Display only for submitted products
   3.3.3 Submitter information (name, email, IP)
   3.3.4 Submission date and time
   3.3.5 Submission status (Pending, Approved, Rejected)
   3.3.6 Admin notes (textarea, internal only)
   3.3.7 Rejection reason (select + custom text)
   3.3.8 Approve button (with email notification)
   3.3.9 Reject button (with email notification)
   3.3.10 Request changes button
   3.3.11 Email submitter button
   3.3.12 Submission history log
   3.3.13 Duplicate check results
   3.3.14 Auto-moderation flags

## 3.4 Admin List Table Columns
   3.4.1 Checkbox (bulk actions)
   3.4.2 Thumbnail (featured image, 50×50px)
   3.4.3 Title (linked to edit)
   3.4.4 Brand (sortable)
   3.4.5 Categories (filterable)
   3.4.6 Tags (filterable)
   3.4.7 Ribbons (visual badges)
   3.4.8 Price (sortable, with sale indicator)
   3.4.12 Rating (star display, sortable)
   3.4.13 Status (Published, Draft, Pending, etc.)
   3.4.14 Author (filterable)
   3.4.15 Date (sortable)
   3.4.16 Actions (Edit, Quick Edit, Trash, View, Duplicate)
   3.4.17 Featured flag (star icon, clickable toggle)
   3.4.18 Trending flag (fire icon)
   3.4.19 Stock status (color-coded)
   - 3.4.11 TODO (auto-inserted)
   - 3.4.10 TODO (auto-inserted)
   - 3.4.9 TODO (auto-inserted)

## 3.5 Admin List Table Filters
   3.5.1 All / Published / Draft / Pending / Trash
   3.5.2 Filter by category (dropdown)
   3.5.3 Filter by tag (dropdown)
   3.5.4 Filter by ribbon (dropdown)
   3.5.5 Filter by brand (dropdown, searchable)
   3.5.6 Filter by price range (min-max inputs)
   3.5.7 Filter by rating (select: 1-5 stars)
   3.5.8 Filter by featured status
   3.5.9 Filter by stock status
   3.5.10 Filter by date range (date pickers)
   3.5.11 Filter by author (for multi-author sites)
   3.5.12 Filter by submission status
   3.5.13 Advanced filters toggle
   3.5.14 Save filter presets

## 3.6 Admin List Table Sorting
   3.6.1 Sort by title (A-Z, Z-A)
   3.6.2 Sort by brand
   3.6.3 Sort by price (low to high, high to low)
   3.6.7 Sort by rating
   3.6.8 Sort by date added (newest, oldest)
   3.6.9 Sort by last modified
   3.6.10 Sort by random (for testing)
   3.6.11 Multi-level sorting (primary + secondary)
   - 3.6.6 TODO (auto-inserted)
   - 3.6.5 TODO (auto-inserted)
   - 3.6.4 TODO (auto-inserted)

## 3.7 Quick Edit
   3.7.1 Title edit
   3.7.2 Brand edit
   3.7.3 Price edit
   3.7.4 Sale price edit
   3.7.5 Affiliate link edit
   3.7.6 Categories assignment
   3.7.7 Tags assignment
   3.7.8 Ribbons assignment
   3.7.9 Featured flag toggle
   3.7.10 Stock status change
   3.7.11 Status change (publish, draft, pending)
   3.7.12 Save and continue editing
   3.7.13 Cancel button
   3.7.14 AJAX save with feedback

## 3.8 Bulk Actions
   3.8.1 Delete permanently
   3.8.2 Move to trash
   3.8.3 Restore from trash
   3.8.4 Mark as featured
   3.8.5 Remove featured flag
   3.8.6 Mark as trending
   3.8.7 Change stock status (bulk)
   3.8.8 Assign categories (bulk)
   3.8.9 Assign tags (bulk)
   3.8.10 Assign ribbons (bulk)
   3.8.11 Update price (bulk, with percentage or fixed increase/decrease)
   3.8.12 Enable/disable sale (bulk)
   3.8.13 Approve submissions (bulk)
   3.8.14 Reject submissions (bulk)
   3.8.15 Export selected (CSV, JSON)
   3.8.16 Duplicate selected products
   3.8.17 Bulk action confirmation dialogs
   3.8.19 Bulk action progress indicator
   - 3.8.18 TODO (auto-inserted)

## 3.9 Admin Search
   3.9.1 Search by title
   3.9.2 Search by content
   3.9.3 Search by excerpt
   3.9.4 Search by brand
   3.9.5 Search by SKU
   3.9.6 Search by meta fields
   3.9.7 Search suggestions/autocomplete
   3.9.8 Recent searches
   3.9.9 Search result highlighting
   3.9.10 Advanced search form (modal)
   3.9.11 Save search queries

## 3.10 Admin Dashboard Widgets
   3.10.1 Widget: Overview Stats (total products, revenue)
   3.10.2 Widget: Recent Products (last 5 added)
   3.10.3 Widget: Pending Submissions (count + quick links)
   3.10.4 Widget: Top Performers (admin-defined top products)
   3.10.5 Widget: Recent Activity Log
   3.10.6 Widget: Quick Links (Add Product, View Submissions, Settings)
   3.10.7 Widget: Revenue Summary (if tracked)
   3.10.8 Widget: Low Stock Alerts
   3.10.9 Widget: Expiring Sales
   3.10.10 Widget configuration (show/hide, position)
   3.10.11 Widget refresh button
   3.10.13 Widget export data option
   3.10.14 Widget date range selector
   3.10.15 Widget full-screen view
   - 3.10.12 TODO (auto-inserted)

# Step 4 — Step 4 — Submission Flow & Security

## 4.1 Frontend Submission Form
   4.1.1 Form location: dedicated page template
   4.1.2 Form shortcode: `[affiliate_submit_form]`
   4.1.3 Form widget for sidebars
   4.1.4 Form: Product Info, Pricing, Media, Contact
   4.1.9 Form validation
   4.1.10 Form submission
   4.1.11 Form security (see 4.2)
   4.1.12 Form accessibility
   4.1.13 Form styling
   4.1.14 Form customization
   - 4.1.8 TODO (auto-inserted)
   - 4.1.7 TODO (auto-inserted)
   - 4.1.6 TODO (auto-inserted)
   - 4.1.5 TODO (auto-inserted)

## 4.2 Security: File Upload
   4.2.1 Allowed file types: JPEG, PNG, WebP, GIF (configurable)
   4.2.2 Blocked file types: PHP, JS, EXE, BAT, SH, etc.
   4.2.3 MIME type validation (server-side)
   4.2.4 File extension validation
   4.2.5 Magic bytes validation (verify actual file type)
   4.2.6 File size limit (default 5MB, configurable)
   4.2.7 Total upload size limit per submission
   4.2.8 Image dimension validation
   4.2.9 Virus scanning integration (if available)
   4.2.10 Rename uploaded files (sanitize filename)
   4.2.11 Store uploads in secure directory (non-public or .htaccess protected)
   4.2.12 Generate unique filenames to prevent overwrite
   4.2.13 Image reprocessing (strip EXIF, metadata)
   4.2.14 Create multiple sizes (thumbnail, medium, large)
   4.2.15 Validate image integrity (check for corruption)
   4.2.16 Reject SVG uploads (XSS risk) unless explicitly allowed with sanitization
   4.2.17 Reject XML-based formats unless sanitized
   4.2.18 Reject polyglot files (multiple valid file types)
   4.2.19 Check for hidden PHP code in images
   4.2.20 Verify GD or Imagick can process the image
   4.2.21 Reject external URLs on submission
   4.2.22 Reject base64-encoded external URLs
   4.2.23 Reject data URIs pointing to external sources
   4.2.24 Reject URLs with IP addresses
   4.2.25 Whitelist local domains only
   4.2.26 Upload error handling with user-friendly messages
   4.2.27 Log failed upload attempts
   4.2.28 Rate limit file uploads per IP
   4.2.29 Temporary file cleanup

## 4.3 Security: Input Sanitization
   4.3.1 Sanitize all text inputs with `sanitize_text_field()`
   4.3.2 Sanitize textareas with `sanitize_textarea_field()`
   4.3.3 Sanitize URLs with `esc_url_raw()`
   4.3.4 Sanitize emails with `sanitize_email()`
   4.3.5 Sanitize HTML with `wp_kses()` or `wp_kses_post()`
   4.3.6 Sanitize numbers with `absint()` or `floatval()`
   4.3.7 Sanitize array keys with `sanitize_key()`
   4.3.8 Sanitize file names with `sanitize_file_name()`
   4.3.9 Sanitize CSS with custom function (strip <style>, <script>)
   4.3.10 Sanitize JS (reject or severely restrict)
   4.3.11 Remove null bytes from strings
   4.3.12 Remove control characters
   4.3.13 Normalize Unicode (NFC)
   4.3.14 Strip PHP tags
   4.3.15 Strip HTML comments
   4.3.16 Limit string length (prevent DoS)
   4.3.17 Trim whitespace
   4.3.18 Lowercase where appropriate (email, slug)
   4.3.19 Sanitize meta keys (underscores, lowercase, alphanumeric)
   4.3.20 Sanitize taxonomy terms
   4.3.21 Recursive sanitization for arrays
   4.3.22 Type casting for expected data types
   4.3.23 Reject malformed JSON
   4.3.24 Sanitize shortcode attributes

## 4.4 Security: Output Escaping
   4.4.1 Escape HTML with `esc_html()`
   4.4.2 Escape attributes with `esc_attr()`
   4.4.3 Escape URLs with `esc_url()`
   4.4.4 Escape JavaScript with `esc_js()`
   4.4.5 Escape textareas with `esc_textarea()`
   4.4.6 Escape i18n strings with `esc_html__()`, `esc_attr__()`, etc.
   4.4.7 Use `wp_kses_post()` for post content
   4.4.8 Use `wp_json_encode()` for JSON output
   4.4.9 Escape SQL with `$wpdb->prepare()`
   4.4.10 Never use variables directly in SQL queries
   4.4.11 Escape shell commands (if used)
   4.4.12 Context-aware escaping (HTML, attribute, URL, JS)
   4.4.13 Late escaping (escape at output, not input)
   4.4.14 Double escaping prevention
   4.4.15 Escape in loops
   4.4.16 Escape in templates
   4.4.17 Escape in AJAX responses
   4.4.18 Escape in REST API responses
   4.4.19 Escape in admin pages

## 4.5 Security: Validation
   4.5.1 Validate email format with `is_email()`
   4.5.2 Validate URL format with `wp_http_validate_url()`
   4.5.3 Validate numbers with `is_numeric()`, type checks
   4.5.4 Validate required fields (not empty)
   4.5.5 Validate string length (min/max)
   4.5.6 Validate data types (string, int, bool, array)
   4.5.7 Validate enum values (whitelist)
   4.5.8 Validate date formats
   4.5.9 Validate price ranges (min/max)
   4.5.10 Validate file uploads (see 4.2)
   4.5.11 Validate nonces (see 4.6)
   4.5.12 Validate capabilities (see 4.7)
   4.5.13 Validate AJAX referers
   4.5.14 Validate REST requests
   4.5.15 Validate POST/GET data presence
   4.5.16 Validate array structures
   4.5.17 Validate JSON format
   4.5.18 Validate taxonomy terms exist
   4.5.19 Validate post IDs exist
   4.5.20 Validate user IDs exist
   4.5.21 Cross-field validation (password confirmation, date ranges)
   4.5.22 Business logic validation (price > 0, sale < regular)
   4.5.23 Duplicate detection validation
   4.5.24 Spam detection validation

## 4.6 Security: Nonce Verification
   4.6.1 Generate nonce with `wp_create_nonce()`
   4.6.2 Verify nonce with `wp_verify_nonce()`
   4.6.3 Check AJAX referer with `check_ajax_referer()`
   4.6.4 Check admin referer with `check_admin_referer()`
   4.6.5 Unique nonce action names
   4.6.6 Nonce in all forms (hidden field)
   4.6.7 Nonce in all AJAX requests
   4.6.8 Nonce in URL query strings (when needed)
   4.6.9 Nonce expiration (default 12-24 hours)
   4.6.10 Fail gracefully on invalid nonce
   4.6.11 Log failed nonce checks
   4.6.12 Never bypass nonce checks
   4.6.13 Nonce regeneration on sensitive actions
   4.6.14 Nonce tied to user session

## 4.7 Security: Capability Checks
   4.7.1 Check `current_user_can()` before any write operation
   4.7.2 Custom capability: `edit_affiliate_products`
   4.7.3 Custom capability: `publish_affiliate_products`
   4.7.4 Custom capability: `delete_affiliate_products`
   4.7.5 Custom capability: `edit_others_affiliate_products`
   4.7.6 Custom capability: `delete_others_affiliate_products`
   4.7.7 Custom capability: `read_private_affiliate_products`
   4.7.8 Custom capability: `manage_affiliate_settings`
   4.7.9 Custom capability: `approve_affiliate_submissions`
   4.7.11 Map capabilities to roles (Administrator, Editor, Author, Contributor)
   4.7.12 Allow role customization via plugin settings
   4.7.13 Check capabilities in admin pages
   4.7.14 Check capabilities in AJAX handlers
   4.7.15 Check capabilities in REST API endpoints
   4.7.16 Check capabilities before meta box display
   4.7.17 Check capabilities before meta save
   4.7.18 Check capabilities before bulk actions
   4.7.19 Check capabilities before CSV export
   - 4.7.10 TODO (auto-inserted)

## 4.8 Security: Prepared Statements
   4.8.1 Use `$wpdb->prepare()` for all custom queries
   4.8.2 Use `%s` placeholder for strings
   4.8.3 Use `%d` placeholder for integers
   4.8.4 Use `%f` placeholder for floats
   4.8.5 Never concatenate variables into SQL
   4.8.6 Never use variables in table/column names (use whitelisting)
   4.8.7 Use `$wpdb->insert()` for inserts
   4.8.8 Use `$wpdb->update()` for updates
   4.8.9 Use `$wpdb->delete()` for deletes
   4.8.10 Use `$wpdb->get_results()` for selects
   4.8.11 Use `$wpdb->get_var()` for single values
   4.8.12 Use `$wpdb->get_row()` for single rows
   4.8.13 Check for SQL errors with `$wpdb->last_error`
   4.8.14 Log SQL errors

## 4.9 Security: Rate Limiting
   4.9.1 Limit submission form to 3 per hour per IP
   4.9.2 Limit submission form to 10 per day per user
   4.9.3 Limit AJAX requests to 60 per minute per IP
   4.9.4 Limit API requests to 100 per hour per key
   4.9.5 Limit failed login attempts (WordPress default)
   4.9.6 Limit password reset requests
   4.9.7 Store rate limit data in transients
   4.9.8 Clear rate limit after timeout
   4.9.9 Display rate limit error message
   4.9.10 Log rate limit violations
   4.9.11 IP-based rate limiting
   4.9.12 User-based rate limiting
   4.9.13 Combination rate limiting (IP + user)
   4.9.14 Whitelist trusted IPs from rate limiting
   4.9.15 Exponential backoff on repeated violations
   4.9.16 Configurable rate limit thresholds
   4.9.17 Different limits for authenticated vs anonymous users
   4.9.18 Rate limit bypass for administrators
   4.9.19 Rate limit statistics in admin dashboard

## 4.10 Security: CAPTCHA
   4.10.1 CAPTCHA disabled by default
   4.10.2 Optional CAPTCHA toggle in settings
   4.10.3 Custom implementation (no external service)
   4.10.4 Math CAPTCHA (2 + 3 = ?)
   4.10.5 Image CAPTCHA (local image generation)
   4.10.6 Audio CAPTCHA for accessibility
   4.10.7 Honeypot field (hidden, should remain empty)
   4.10.8 Time-based CAPTCHA (min time to fill form)
   4.10.9 Question-based CAPTCHA (custom questions)
   4.10.10 CAPTCHA only after X failed attempts
   4.10.11 CAPTCHA only for anonymous users
   4.10.12 CAPTCHA refresh button
   4.10.13 CAPTCHA error messages
   4.10.14 CAPTCHA session storage (not cookies)
   4.10.15 CAPTCHA expiration (10 minutes)
   4.10.16 No external CAPTCHA service code bundled
   4.10.17 No reCAPTCHA, hCaptcha, Cloudflare Turnstile
   4.10.18 CAPTCHA settings documentation
   4.10.19 CAPTCHA accessibility compliance

## 4.11 Security: Dependency Injection
   4.11.1 Use DI container (PHP-DI or custom)
   4.11.2 Inject database service
   4.11.3 Inject validation service
   4.11.4 Inject sanitization service
   4.11.5 Inject file upload service
   4.11.6 Inject email service
   4.11.7 Inject cache service
   4.11.8 Inject logging service
   4.11.9 Constructor injection for dependencies
   4.11.10 Interface-based dependencies
   4.11.11 Mock dependencies in tests
   4.11.12 Service locator pattern (if needed)
   4.11.13 Factory pattern for object creation
   4.11.14 Lazy loading for heavy services

## 4.12 Security: Logging
   4.12.1 Log all submission attempts
   4.12.2 Log failed submissions (with reason)
   4.12.3 Log successful submissions
   4.12.4 Log file upload attempts
   4.12.5 Log failed file uploads
   4.12.6 Log validation errors
   4.12.7 Log security violations (failed nonce, capability, etc.)
   4.12.8 Log rate limit hits
   4.12.9 Log suspicious activity (spam indicators)
   4.12.10 Log IP addresses (GDPR-compliant hashing)
   4.12.11 Log user agents
   4.12.12 Log timestamps
   4.12.13 Store logs in custom database table
   4.12.14 Log rotation (delete logs older than 90 days)
   4.12.15 Log export to CSV
   4.12.16 Log search functionality
   4.12.17 Log filtering by type, date, user, IP
   4.12.18 Log dashboard widget
   4.12.19 Never log sensitive data (passwords, payment info)

## 4.13 Security: Spam Protection
   4.13.1 Honeypot field (hidden, should be empty)
   4.13.2 Time-based check (min 3 seconds to submit)
   4.13.3 Detect repeated submissions (same IP, same data)
   4.13.4 Check for spam keywords in content
   4.13.5 Check for excessive links in content
   4.13.6 Check for all-caps content
   4.13.7 Check for gibberish (random characters)
   4.13.8 User agent check (block known spambots)
   4.13.9 Referrer check (if present)
   4.13.10 Session validation
   4.13.11 JavaScript check (ensure browser, not bot)
   4.13.12 Mouse movement tracking (advanced bot detection)
   4.13.13 IP blacklist check
   4.13.14 Email domain blacklist
   4.13.15 Disposable email detection
   4.13.16 Spam score calculation (0-100)
   4.13.17 Auto-reject if spam score > threshold
   4.13.18 Quarantine suspicious submissions (admin review)
   4.13.19 Whitelist trusted submitters
   4.13.20 Bayesian spam filter (optional)
   4.13.21 Integration with Akismet (optional, external service disclaimer)
   4.13.22 Manual spam reporting by admins
   4.13.23 Learn from spam reports (improve filters)
   4.13.24 Spam statistics dashboard
   4.13.25 Bulk delete spam submissions

# Step 5 — Step 5 — Frontend Components

## 5.1 Tailwind CSS & Vite Setup
   5.1.1 Install Tailwind CSS via NPM
   5.1.2 Configure `tailwind.config.js` with custom theme
   5.1.3 Theme: custom color palette (primary, secondary, accent, neutral, success, warning, error)
   5.1.4 Theme: custom fonts (system stack)
   5.1.5 Theme: custom spacing scale
   5.1.6 Theme: custom breakpoints (sm, md, lg, xl, 2xl)
   5.1.7 Theme: custom border radius
   5.1.8 Theme: custom shadows
   5.1.9 Theme: dark mode support (class-based)
   5.1.10 Content paths: all PHP, JS, TS files
   5.1.11 Safelist: dynamic classes for colors, sizes
   5.1.12 Plugins: forms, typography, aspect-ratio, line-clamp
   5.1.13 PostCSS configuration with Autoprefixer
   5.1.14 PurgeCSS in production build
   5.1.15 Vite configuration file
   5.1.16 Vite: React plugin
   5.1.17 Vite: TypeScript support
   5.1.18 Vite: Build output to `dist/`
   5.1.19 Vite: Entry points (main.ts, admin.ts)
   5.1.20 Vite: Chunk splitting (vendor, components, utils)
   5.1.21 Vite: Asset inlining threshold (10KB)
   5.1.22 Vite: CSS code splitting
   5.1.23 Vite: Source maps in dev, not in prod
   5.1.24 Vite: Minification in production
   5.1.25 Vite: Tree shaking
   5.1.26 Vite: Legacy browser support (optional)
   5.1.27 Vite: Dev server with HMR (Hot Module Replacement)
   5.1.28 Vite: Dev server proxy for WordPress (if needed)
   5.1.29 Vite: Environment variables support
   5.1.30 NPM scripts: `npm run dev`, `npm run build`, `npm run preview`
   5.1.31 Ensure all compiled CSS/JS bundled in `dist/`
   5.1.32 WordPress enqueue functions use `plugins_url('dist/...')`
   5.1.33 No external CDN links in enqueued assets
   5.1.34 Asset versioning (file hash in build)
   5.1.35 Critical CSS inlining (optional)
   5.1.36 Preload key assets
   5.1.37 Defer non-critical JS
   5.1.38 Async CSS loading
   5.1.39 Resource hints (preconnect, prefetch)

## 5.2 Typography & Fonts
   5.2.1 System font stack: `system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif`
   5.2.2 Monospace stack: `ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace`
   5.2.3 No Google Fonts or any external web font service
   5.2.4 No `@font-face` with external URLs
   5.2.5 No web font CDNs (Google Fonts, Adobe Fonts, etc.)
   5.2.6 No font-picker in settings
   5.2.7 Custom font upload explicitly forbidden
   5.2.8 Typography settings: font size, line height, letter spacing
   5.2.9 Typography settings: heading styles (H1-H6)
   5.2.10 Typography settings: body text style
   5.2.11 Typography settings: link style (color, underline, hover)
   5.2.12 Typography settings: bold/italic styles
   5.2.13 Typography presets (default, compact, spacious)
   5.2.14 Responsive typography (fluid type scale)
   5.2.15 Accessibility: minimum font size (14px)
   5.2.16 Accessibility: sufficient line height (1.5)
   5.2.17 Accessibility: sufficient color contrast (WCAG AA)
   5.2.18 Typography utilities in Tailwind
   5.2.19 Text truncation utilities

## 5.3 Icons
   5.3.1 Use bundled local SVG icons only
   5.3.2 Use Tailwind Heroicons (bundled via NPM)
   5.3.3 No Font Awesome or external icon libraries
   5.3.4 No icon CDNs (Font Awesome CDN, etc.)
   5.3.5 Icon component in React (reusable)
   5.3.6 Icon size prop (sm, md, lg, xl)
   5.3.7 Icon color prop (inherits text color)
   5.3.8 Icon rotation prop (0, 90, 180, 270)
   5.3.9 Icon flip prop (horizontal, vertical)
   5.3.10 Icon animation prop (spin, pulse, bounce)
   5.3.11 Icon accessibility: ARIA labels
   5.3.12 Icon sets: outline, solid, mini
   5.3.13 Custom icon support (upload SVG to library)
   5.3.14 Icon library browser in admin
   5.3.15 Icon search functionality
   5.3.16 Icon categories (arrows, commerce, communication, etc.)
   5.3.17 Icon export/import
   5.3.18 SVG optimization (SVGO)
   5.3.19 Inline SVG for better control

## 5.4 Images & Media
   5.4.1 All images stored locally (WordPress media library)
   5.4.2 Reject external image URLs in product card
   5.4.3 Reject external image URLs in admin meta box
   5.4.4 Reject external image URLs in submission form
   5.4.5 Validate image URLs on save (check domain)
   5.4.6 Use WordPress image functions: `wp_get_attachment_image()`
   5.4.7 Use WordPress image sizes (thumbnail, medium, large, full)
   5.4.8 Custom image sizes: product-card (400×400), product-grid (300×300)
   5.4.9 Responsive images with `srcset` and `sizes`
   5.4.10 Lazy loading images with `loading='lazy'`
   5.4.11 Image placeholder (blur-up, solid color, SVG)
   5.4.12 Image alt text (required, fallback to product title)
   5.4.13 Image title attribute
   5.4.14 Image caption support
   5.4.15 Image gallery (slider, grid, masonry, lightbox)
   5.4.16 Image zoom on hover or click
   5.4.17 Image comparison slider (before/after)
   5.4.18 ° image viewer (for multiple images)
   5.4.19 Video support (local upload or embed code, no YouTube API)
   5.4.20 Video thumbnail (auto-generate or manual upload)
   5.4.21 Video player controls
   5.4.22 Video accessibility: captions, transcripts
   5.4.23 WebP format support
   5.4.24 AVIF format support (if server supports)
   5.4.25 Image optimization on upload (compress, resize)
   5.4.26 Progressive JPEG encoding
   5.4.27 Image CDN integration (optional, local fallback)
   5.4.28 Image security: prevent hotlinking
   5.4.29 Image security: serve with proper headers

## 5.5 JavaScript & Asset Loading
   5.5.1 All JS/TS bundled via Vite
   5.5.2 No external JS libraries from CDNs
   5.5.3 No dynamic imports from external URLs
   5.5.4 All enqueued scripts use `plugins_url('dist/...')`
   5.5.5 Script dependencies declared (React, WordPress packages)
   5.5.6 Script localization for i18n
   5.5.7 Script handles prefixed (e.g., `affiliate-showcase-main`)
   5.5.8 Script versioning (plugin version or file hash)
   5.5.9 Conditional script loading (only on pages that need it)
   5.5.10 Enqueue in footer (except critical scripts)
   5.5.11 Defer or async attributes where appropriate
   5.5.12 No inline scripts (except critical path)
   5.5.13 No `document.write()`
   5.5.14 Use `wp_add_inline_script()` for small inline scripts
   5.5.15 Use `wp_localize_script()` for PHP-to-JS data
   5.5.16 Security: escape all PHP-to-JS data with `wp_json_encode()`
   5.5.17 Security: nonce in JS global object
   5.5.18 Security: REST API URL in JS global object
   5.5.19 Performance: code splitting by route/component
   5.5.20 Performance: tree shaking to remove unused code
   5.5.21 Performance: minification in production
   5.5.22 Performance: gzip/brotli compression
   5.5.23 Performance: HTTP/2 push (if server supports)
   5.5.24 Performance: bundle size analysis
   5.5.25 Bundle size budget (250KB initial, 500KB total)
   5.5.26 Polyfills for older browsers (conditional loading)
   5.5.27 ES6+ features with Babel transpilation
   5.5.28 Browser compatibility: last 2 versions, > 1% usage
   5.5.29 No jQuery dependency (use vanilla JS or React)

## 5.6 CSS & Styling
   5.6.1 All CSS bundled via Vite
   5.6.2 Tailwind CSS as base framework
   5.6.3 Custom CSS in separate file (loaded after Tailwind)
   5.6.4 No external stylesheets from CDNs
   5.6.5 No `@import` with external URLs
   5.6.6 Enqueue styles with `wp_enqueue_style()`
   5.6.7 Style handles prefixed (e.g., `affiliate-showcase-main`)
   5.6.8 Style versioning (plugin version or file hash)
   5.6.9 Conditional style loading
   5.6.10 Inline critical CSS for above-the-fold content
   5.6.11 Async load non-critical CSS
   5.6.12 CSS custom properties (variables) for theming
   5.6.13 CSS scoping (avoid global styles)
   5.6.14 CSS naming convention (BEM or utility-first)
   5.6.15 CSS specificity management
   5.6.16 CSS reset or normalize
   5.6.17 CSS for print media (if needed)
   5.6.18 CSS animations and transitions
   5.6.19 CSS grid and flexbox for layouts
   5.6.20 Responsive design (mobile-first)
   5.6.21 Breakpoint utilities in Tailwind
   5.6.22 Dark mode support (class-based, toggle in settings)
   5.6.23 High contrast mode support
   5.6.24 Reduce motion support (prefers-reduced-motion)
   5.6.25 RTL support (right-to-left languages)
   5.6.26 CSS minification in production
   5.6.27 CSS autoprefixer for vendor prefixes
   5.6.28 CSS purging (remove unused classes)
   5.6.29 CSS file size budget (100KB)

## 5.7 Product Card Component
   5.7.1 Component file: `ProductCard.tsx`
   5.7.2 Component props: product data object
   5.7.3 Card layout: image + content
   5.7.6 Card variants
   5.7.7 Card styling
   5.7.8 Card interactions
   5.7.9 Card animations
   5.7.10 Card accessibility
   5.7.11 Card responsiveness
   - 5.7.5 TODO (auto-inserted)
   - 5.7.4 TODO (auto-inserted)

## 5.8 Grid & Layout Components
   5.8.1 ProductGrid component (container for cards)
   5.8.2 Grid columns: 1, 2, 3, 4, 5, 6 (configurable)
   5.8.3 Grid gap: xs, sm, md, lg, xl (configurable)
   5.8.4 Grid responsive breakpoints
   5.8.5 Masonry layout option (Pinterest-style)
   5.8.6 List layout option (full-width cards)
   5.8.7 Table layout option (data table)
   5.8.8 Slider/carousel layout (Swiper-like, custom)
   5.8.9 Grid empty state (no products found)
   5.8.10 Grid loading state (skeleton cards)
   5.8.11 Grid error state
   5.8.12 Grid header (title, count, view switcher)
   5.8.13 Grid footer (pagination, load more)
   5.8.14 View switcher (grid, list, table icons)
   5.8.15 Layout persistence (save user preference)
   5.8.16 Grid container max-width
   5.8.17 Grid container padding
   5.8.18 Grid full-width option
   5.8.19 Grid sidebar support (filters + grid)

## 5.9 Filter & Sort Components
   5.9.1 FilterBar component
   5.9.2 Filter by category (dropdown or checkbox list)
   5.9.3 Filter by tag (dropdown or checkbox list)
   5.9.4 Filter by ribbon (dropdown or checkbox list)
   5.9.5 Filter by brand (dropdown or checkbox list, searchable)
   5.9.6 Filter by price range (slider or min/max inputs)
   5.9.7 Filter by rating (select: 4+ stars, 3+, etc.)
   5.9.8 Filter by stock status (In Stock, Out of Stock)
   5.9.9 Filter by featured status
   5.9.10 Filter by sale status (On Sale)
   5.9.11 Multi-select filters (AND/OR logic)
   5.9.12 Filter active badges (removable)
   5.9.13 Clear all filters button
   5.9.14 Filter count (X filters active)
   5.9.15 Filter collapse/expand on mobile
   5.9.16 Filter sidebar (off-canvas on mobile)
   5.9.17 Filter modal (popup)
   5.9.18 Filter dropdown (inline)
   5.9.19 SortDropdown component
   5.9.20 Sort by featured (default)
   5.9.21 Sort by newest
   5.9.22 Sort by oldest
   5.9.23 Sort by price: low to high
   5.9.24 Sort by price: high to low
   5.9.25 Sort by rating: high to low
   5.9.26 Sort by popularity (admin-defined)
   5.9.27 Sort by engagement (admin-defined)
   5.9.28 Sort by alphabetical (A-Z, Z-A)
   5.9.29 Sort by random
   5.9.30 Sort persistence (save user preference)
   5.9.31 URL parameter sync (filters and sort in query string)
   5.9.32 Browser back/forward support
   5.9.33 SEO-friendly URLs
   5.9.34 Filter/sort AJAX loading (no page reload)
   5.9.35 Loading indicator during filter/sort
   5.9.36 Debounce filter changes
   5.9.37 Filter result count ('Showing X of Y products')
   5.9.38 Filter presets (save filter combinations)

## 5.10 Pagination Component
   5.10.1 Pagination component with numbered pages
   5.10.2 Previous/Next buttons
   5.10.3 First/Last buttons (optional)
   5.10.4 Current page indicator (highlighted)
   5.10.5 Page number links
   5.10.6 Ellipsis for many pages (1, 2, 3, ..., 10)
   5.10.7 Items per page selector (12, 24, 48, 96, All)
   5.10.8 Total items count display
   5.10.9 Current range display ('Showing 1-12 of 120')
   5.10.10 URL parameter sync (page in query string)
   5.10.11 Scroll to top on page change (optional)
   5.10.12 Smooth scroll animation
   5.10.13 Loading state during page change
   5.10.14 Keyboard navigation (arrow keys)
   5.10.15 Accessibility: ARIA labels, roles
   5.10.16 Pagination styles (default, minimal, pills)
   5.10.17 Pagination position (top, bottom, both)
   5.10.18 Infinite scroll option (alternative to pagination)
   5.10.19 Load more button option (alternative to pagination)

## 5.11 Search Component
   5.11.1 SearchBar component
   5.11.2 Search input with icon
   5.11.3 Search placeholder text (customizable)
   5.11.4 Search button (optional)
   5.11.5 Clear search button (X icon)
   5.11.6 Live search (autocomplete suggestions)
   5.11.7 Search suggestions based on title
   5.11.8 Search suggestions based on brand
   5.11.9 Search suggestions based on category
   5.11.10 Search suggestions based on tags
   5.11.11 Recent searches
   5.11.12 Popular searches
   5.11.13 Search history (per user)
   5.11.14 Search by title
   5.11.15 Search by description
   5.11.16 Search by SKU
   5.11.17 Search by brand
   5.11.18 Search by features
   5.11.19 Fuzzy search (typo tolerance)
   5.11.20 Search highlighting (matched terms)
   5.11.21 Search result count
   5.11.22 Search filters (combine with category, price, etc.)
   5.11.23 Search sort options
   5.11.24 No results message
   5.11.25 Search suggestions on no results
   5.11.26 URL parameter sync (search query in URL)
   5.11.27 AJAX search (no page reload)
   5.11.28 Debounce search input (300ms)
   5.11.29 Loading indicator during search
   5.11.30 Voice search support (Web Speech API)
   5.11.32 Search keyboard shortcuts (Ctrl+K, Cmd+K)
   5.11.33 Search modal/overlay (full-screen search)
   5.11.34 Search accessibility (ARIA labels, keyboard nav)
   - 5.11.31 TODO (auto-inserted)

## 5.12 Modal & Lightbox Components
   5.12.1 Modal component (reusable)
   5.12.2 Modal overlay (semi-transparent background)
   5.12.3 Modal content area
   5.12.4 Modal close button (X icon)
   5.12.5 Close on overlay click (optional)
   5.12.6 Close on ESC key
   5.12.7 Prevent body scroll when modal open
   5.12.8 Focus trap inside modal
   5.12.9 Return focus on close
   5.12.10 Modal animation (fade in, slide up, zoom)
   5.12.11 Modal sizes (sm, md, lg, xl, full)
   5.12.12 Modal header (title + close button)
   5.12.13 Modal body (scrollable content)
   5.12.14 Modal footer (action buttons)
   5.12.15 Modal accessibility (ARIA attributes)
   5.12.16 Lightbox component (image viewer)
   5.12.17 Lightbox navigation (prev/next arrows)
   5.12.18 Lightbox thumbnails
   5.12.19 Lightbox zoom controls
   5.12.20 Lightbox fullscreen mode
   5.12.21 Lightbox slideshow/autoplay
   5.12.22 Lightbox caption display
   5.12.23 Lightbox keyboard controls (arrow keys, ESC)
   5.12.24 Lightbox touch/swipe gestures
   5.12.25 Lightbox close on click outside
   5.12.26 Quick View modal (product details)
   5.12.27 Quick View: product image
   5.12.28 Quick View: product info
   5.12.29 Quick View: CTA button
   5.12.30 Quick View: add to favorites
   5.12.31 Quick View: share buttons
   5.12.32 Quick View: related products
   5.12.33 Video modal (embed player)
   5.12.34 Comparison modal (compare products)

## 5.13 Loading & Error States
   5.13.1 Skeleton loader component (card placeholder)
   5.13.2 Skeleton: image placeholder
   5.13.3 Skeleton: text line placeholders
   5.13.4 Skeleton: button placeholder
   5.13.5 Skeleton animation (shimmer effect)
   5.13.6 Spinner component (loading indicator)
   5.13.7 Spinner sizes (sm, md, lg)
   5.13.8 Spinner colors (customizable)
   5.13.9 Progress bar component
   5.13.10 Progress bar: determinate (percentage)
   5.13.11 Progress bar: indeterminate (animated)
   5.13.12 Loading overlay (full-screen or container)
   5.13.13 Loading text ('Loading products...')
   5.13.14 Error message component
   5.13.15 Error: generic error
   5.13.16 Error: network error
   5.13.17 Error: not found (404)
   5.13.18 Error: server error (500)
   5.13.19 Error: permission denied (403)
   5.13.20 Error message with icon
   5.13.21 Error message with retry button
   5.13.22 Error message with support link
   5.13.23 Empty state component
   5.13.24 Empty state: no products found
   5.13.25 Empty state: no results for search
   5.13.26 Empty state: no favorites
   5.13.27 Empty state with icon/illustration
   5.13.28 Empty state with CTA ('Add Products')
   5.13.29 Empty state with suggestions

## 5.14 TypeScript & React Patterns
   5.14.1 TypeScript mandatory for all components
   5.14.2 Interface definitions for all props
   5.14.3 Type definitions for all state
   5.14.4 Type definitions for API responses
   5.14.5 Type definitions for Redux/Context state
   5.14.6 Strict type checking enabled
   5.14.7 No any type (use unknown or specific type)
   5.14.8 Use React.FC or function component with explicit return type
   5.14.9 PropTypes + TypeScript for runtime validation
   5.14.10 React Hooks: useState, useEffect, useContext, useReducer
   5.14.11 Custom hooks for reusable logic
   5.14.12 useFetch hook for API calls
   5.14.13 useDebounce hook for search/filter
   5.14.14 useLocalStorage hook for persistence
   5.14.15 useIntersectionObserver hook for lazy loading
   5.14.16 useMediaQuery hook for responsive design
   5.14.17 usePagination hook
   5.14.18 useFilters hook
   5.14.19 useSort hook
   5.14.20 React Context for global state (filters, settings)
   5.14.21 React.memo for expensive components
   5.14.22 useMemo for expensive calculations
   5.14.23 useCallback for event handlers (prevent re-renders)
   5.14.24 React.lazy for code splitting
   5.14.25 Suspense for lazy-loaded components
   5.14.26 Error boundaries for component errors
   5.14.27 Refs for DOM manipulation (useRef)
   5.14.28 forwardRef for ref forwarding
   5.14.29 useImperativeHandle (when needed)
   5.14.30 useLayoutEffect (when needed, prefer useEffect)
   5.14.31 Component composition over inheritance
   5.14.32 Render props pattern (where appropriate)
   5.14.33 HOC pattern (higher-order components, sparingly)
   5.14.34 Compound components pattern
   5.14.35 Controlled vs uncontrolled components
   5.14.36 Form handling with controlled inputs
   5.14.37 Event handling best practices
   5.14.38 Side effect management (useEffect cleanup)
   5.14.39 Avoid prop drilling (use Context)

## 5.15 ESLint & Prettier
   5.15.1 ESLint configuration with WordPress preset
   5.15.2 ESLint: TypeScript parser
   5.15.3 ESLint: React plugin rules
   5.15.4 ESLint: React Hooks rules
   5.15.5 ESLint: Accessibility plugin (jsx-a11y)
   5.15.6 ESLint: Import plugin (resolve, no-unresolved)
   5.15.7 ESLint: No unused variables
   5.15.8 ESLint: No console.log (warn, not error)
   5.15.9 ESLint: No debugger
   5.15.10 ESLint: Prefer const over let
   5.15.11 ESLint: Arrow function style
   5.15.12 ESLint: Object shorthand
   5.15.13 ESLint: Template literals over string concatenation
   5.15.14 ESLint: Destructuring
   5.15.15 ESLint: Async/await over promises (where clearer)
   5.15.16 Prettier configuration
   5.15.17 Prettier: Single quotes
   5.15.18 Prettier: Semicolons (consistent)
   5.15.19 Prettier: Trailing commas (es5)
   5.15.20 Prettier: Tab width (2 spaces)
   5.15.21 Prettier: Print width (80 or 100)
   5.15.22 Prettier: Arrow function parentheses (always)
   5.15.23 Prettier: End of line (lf)
   5.15.24 Prettier: JSX brackets on same line (false)
   5.15.25 ESLint + Prettier integration (no conflicts)

# Step 6 — Step 6 — Shortcodes, Filters & Sorting

## 6.1 Primary Shortcode: [affiliate_products]
   6.1.1 Shortcode name: affiliate_products
   6.1.2 Attribute: category (slug or ID, comma-separated)
   6.1.3 Attribute: tag (slug or ID, comma-separated)
   6.1.4 Attribute: ribbon (slug or ID, comma-separated)
   6.1.5 Attribute: brand (name, comma-separated)
   6.1.6 Attribute: ids (specific product IDs, comma-separated)
   6.1.7 Attribute: exclude (product IDs to exclude, comma-separated)
   6.1.8 Attribute: featured (true/false, show only featured)
   6.1.9 Attribute: trending (true/false, show only trending)
   6.1.10 Attribute: on_sale (true/false, show only on sale)
   6.1.11 Attribute: in_stock (true/false, show only in stock)
   6.1.12 Attribute: limit (number of products to show, default 12)
   6.1.13 Attribute: columns (1-6, default 3)
   6.1.14 Attribute: orderby (featured, date, title, price, rating, random)
   6.1.15 Attribute: order (ASC, DESC)
   6.1.16 Attribute: layout (grid, list, table, slider)
   6.1.17 Attribute: pagination (true/false, default true)
   6.1.18 Attribute: filter (true/false, show filter bar, default false)
   6.1.19 Attribute: search (true/false, show search bar, default false)
   6.1.20 Attribute: sort (true/false, show sort dropdown, default false)
   6.1.21 Attribute: show_image (true/false, default true)
   6.1.22 Attribute: show_brand (true/false, default true)
   6.1.23 Attribute: show_rating (true/false, default true)
   6.1.24 Attribute: show_price (true/false, default true)
   6.1.25 Attribute: show_features (true/false, default true)
   6.1.26 Attribute: show_cta (true/false, default true)
   6.1.27 Attribute: cta_text (custom text, default 'View Deal')
   6.1.28 Attribute: class (custom CSS class)
   6.1.29 Attribute: style (custom inline styles, sanitized)
   6.1.30 Shortcode builder in admin (GUI for generating shortcodes)
   6.1.31 Shortcode preview in admin
   6.1.32 Shortcode documentation in admin
   6.1.33 Shortcode examples in documentation
   6.1.34 Nested shortcode support (if needed)
   6.1.35 Shortcode caching for performance
   6.1.36 Shortcode output escaping
   6.1.37 Shortcode attribute sanitization
   6.1.38 Shortcode error handling (invalid attributes)
   6.1.39 Shortcode localization (i18n)

## 6.2 Additional Shortcodes
   6.2.1 [affiliate_submit_form] - Frontend submission form
   6.2.2 [affiliate_categories] - Category list or grid
   6.2.3 [affiliate_tags] - Tag cloud
   6.2.4 [affiliate_brands] - Brand list or grid
   6.2.5 [affiliate_featured] - Featured products (alias for [affiliate_products featured=true])
   6.2.6 [affiliate_trending] - Trending products
   6.2.7 [affiliate_on_sale] - On sale products
   6.2.8 [affiliate_search] - Standalone search bar
   6.2.9 [affiliate_filter] - Standalone filter widget
   6.2.10 [affiliate_compare] - Product comparison table
   6.2.11 [affiliate_single id=123] - Single product display
   6.2.12 [affiliate_slider] - Product slider/carousel
   6.2.13 [affiliate_grid] - Product grid (alias)
   6.2.14 [affiliate_list] - Product list (alias)

## 6.3 Filter UI Implementation
   6.3.1 FilterBar component (React) 6.3.2
   6.3.10 Filter logic: AND vs OR
   6.3.11 Active filters display
   6.3.12 Filter UI layouts
   6.3.13 Filter responsiveness
   6.3.14 Filter persistence
   6.3.15 Filter performance
   - 6.3.9 TODO (auto-inserted)
   - 6.3.8 TODO (auto-inserted)
   - 6.3.7 TODO (auto-inserted)
   - 6.3.6 TODO (auto-inserted)
   - 6.3.5 TODO (auto-inserted)
   - 6.3.4 TODO (auto-inserted)
   - 6.3.3 TODO (auto-inserted)
   - 6.3.2 TODO (auto-inserted)

## 6.4 Sort Implementation
   6.4.1 SortDropdown component (React)
   6.4.2 Sort options
   6.4.3 Sort UI
   6.4.4 Sort position
   6.4.5 Sort + Filter interaction
   6.4.6 Sort persistence
   6.4.7 Sort performance

## 6.5 AJAX Handlers
   6.5.1 AJAX endpoint: affiliate_filter_products
   6.5.2 AJAX endpoint: affiliate_sort_products
   6.5.3 AJAX endpoint: affiliate_paginate_products
   6.5.4 AJAX endpoint: affiliate_search_products
   6.5.5 AJAX endpoint: affiliate_load_more
   6.5.6 AJAX endpoint: affiliate_quick_view (product modal data)
   6.5.7 AJAX endpoint: affiliate_toggle_favorite
   6.5.10 AJAX handler: nonce verification
   6.5.11 AJAX handler: capability check (if needed)
   6.5.12 AJAX handler: input sanitization
   6.5.13 AJAX handler: query construction (WP_Query)
   6.5.14 AJAX handler: pagination calculation
   6.5.15 AJAX handler: response formatting (JSON)
   6.5.16 AJAX handler: error handling
   6.5.17 AJAX handler: return product HTML or data
   6.5.18 AJAX handler: return total count
   6.5.19 AJAX handler: return available filters (dynamic)
   6.5.20 AJAX handler: caching results (transients)
   6.5.21 AJAX handler: rate limiting
   - 6.5.9 TODO (auto-inserted)
   - 6.5.8 TODO (auto-inserted)

## 6.6 Query Optimization
   6.6.1 Use WP_Query with optimized arguments
   6.6.2 Query only needed fields (no unnecessary meta queries)
   6.6.3 Use tax_query for taxonomy filters
   6.6.4 Use meta_query for meta field filters
   6.6.5 Use date_query for date filters
   6.6.6 Avoid posts_per_page=-1 (load all), use pagination
   6.6.7 Use no_found_rows=>false for pagination (count total)
   6.6.8 Use update_post_meta_cache=>false if meta not needed
   6.6.9 Use update_post_term_cache=>false if terms not needed
   6.6.10 Use object caching (Redis, Memcached if available)
   6.6.11 Cache expensive queries (transients)
   6.6.12 Set transient expiration (1 hour for dynamic data)
   6.6.13 Clear transients on product update/delete
   6.6.14 Database indexes on custom tables (tracking)
   6.6.15 Database indexes on meta_key, meta_value if queried often
   6.6.16 Avoid slow queries (nested meta queries, complex tax queries)
   6.6.17 Use direct SQL for complex queries (with $wpdb->prepare)
   6.6.18 Monitor slow queries (Query Monitor plugin in dev)
   6.6.19 Query result pagination (don't load all results)

## 6.7 Random Products
   6.7.1 Shortcode attribute: orderby='random'
   6.7.2 Query: 'orderby' => 'rand'
   6.7.3 Cache random results per user session (avoid recalculation)
   6.7.4 Seed random for consistency (optional)
   6.7.5 Weighted random (favor featured products)
   6.7.6 Exclude recently shown products (for returning visitors)
   6.7.7 Random rotation (show different products on refresh)
   6.7.8 Performance: limit random query to small subset first, then randomize

# Step 7 — Step 7 — Link Tracking & Redirects

## 7.1 Redirect Handler
   7.1.1 Custom endpoint: /go/{product-slug}/
   7.1.2 Custom endpoint: /go/{custom-redirect-slug}/
   7.1.3 Custom endpoint: /out/{product-id}/
   7.1.4 Rewrite rules for custom endpoints
   7.1.5 Flush rewrite rules on activation/deactivation
   7.1.6 Redirect handler function
   7.1.7 Validate product exists before redirect
   7.1.8 Validate affiliate link exists
   7.1.9 Get affiliate link from product meta
   7.1.18 HTTP 301 permanent redirect
   7.1.19 HTTP 302 temporary redirect (setting)
   7.1.20 Add rel attributes (nofollow, sponsored, ugc)
   7.1.21 Add UTM parameters to link (from settings)
   7.1.23 Open in new tab/window (via JS, not redirect)
   7.1.24 Delay redirect (optional, show 'Redirecting...' message)
   7.1.25 Interstitial page (optional, 'You are leaving our site')
   7.1.26 Security: validate redirect URL (no open redirects)
   7.1.27 Security: whitelist allowed domains (if enabled)
   7.1.28 Security: prevent redirect loops
   7.1.29 Security: rate limit redirects per IP
   - 7.1.22 TODO (auto-inserted)
   - 7.1.17 TODO (auto-inserted)
   - 7.1.16 TODO (auto-inserted)
   - 7.1.15 TODO (auto-inserted)
   - 7.1.14 TODO (auto-inserted)
   - 7.1.13 TODO (auto-inserted)
   - 7.1.12 TODO (auto-inserted)
   - 7.1.11 TODO (auto-inserted)
   - 7.1.10 TODO (auto-inserted)

## 7.5 Link Management
   7.5.1 Admin: bulk edit affiliate links
   7.5.2 Admin: test affiliate link (check if live)
   7.5.3 Admin: broken link checker (scan all links)
   7.5.4 Admin: broken link notifications (email admin)
   7.5.5 Admin: link expiration dates (warn before expiration)
   7.5.6 Admin: link rotation (A/B test different links)
   7.5.7 Admin: link shortening (built-in, no external service)
   7.5.8 Admin: link QR code generation
   7.5.9 Admin: link preview (show destination before redirect)
   7.5.10 Admin: link notes (internal notes for each link)
   7.5.11 Link history (changes to affiliate links)
   7.5.12 Link backup (export all links)
   7.5.13 Link import (CSV with product ID + link)
   7.5.14 Link validation on save (check URL format)
   7.5.15 Link sanitization (remove tracking pixels, etc.)
   7.5.16 Link cloaking (hide affiliate parameters)
   7.5.17 Link deobfuscation (decode obfuscated links)
   7.5.18 Link attribution (via UTM parameters; recommend external analytics for attribution)
   7.5.19 Link permissions (who can edit links)
   7.5.20 Link audit log (who changed what, when)
   - 7.4 TODO (auto-inserted)
   - 7.3 TODO (auto-inserted)
   - 7.2 TODO (auto-inserted)

# Step 8 — Step 8 — Settings & Styling Controls

## 8.1 Settings Page Architecture
   8.1.1 Admin page: "Settings" under plugin menu
   8.1.2 React-based settings page (TypeScript)
   8.1.3 Tabbed interface: General, Styling, Advanced, Import/Export, Help
   8.1.4 Settings API: WordPress Settings API + REST API
   8.1.5 Save settings via AJAX (no page reload)
   8.1.6 Nonce verification on save
   8.1.7 Capability check: manage_options
   8.1.8 Settings validation on save
   8.1.9 Settings sanitization on save
   8.1.10 Settings error handling (display error messages)
   8.1.11 Settings success message (saved confirmation)
   8.1.12 Settings reset to defaults (with confirmation)
   8.1.13 Settings export (JSON file)
   8.1.14 Settings import (JSON file upload)
   8.1.15 Settings documentation (help text for each setting)
   8.1.16 Settings search (filter settings by keyword)
   8.1.17 Settings change tracking (unsaved changes warning)
   8.1.18 Settings version (track settings schema version)
   8.1.19 Settings migration (upgrade from old version)
   8.1.20 Settings backup (auto-backup before save)

## 8.2 (generated)
### 8.2.1 (generated)
      8.2.1.1 Plugin enable/disable toggle
      8.2.1.2 Default posts per page (12, 24, 48, custom)
      8.2.1.3 Default columns (1-6)
      8.2.1.4 Default layout (grid, list, table, slider)
      8.2.1.5 Default orderby (featured, date, price, etc.)
      8.2.1.6 Default order (ASC, DESC)
      8.2.1.7 Product archive slug (default: affiliate-products)
      8.2.1.8 Single product slug (default: affiliate-product)
      8.2.1.9 Category slug (default: product-category)
      8.2.1.10 Tag slug (default: product-tag)

### 8.2.2 (generated)
      8.2.2.1 Show product image (global toggle)
      8.2.2.2 Show brand name
      8.2.2.3 Show product rating
      8.2.2.4 Show product price
      8.2.2.5 Show sale badge
      8.2.2.6 Show stock status
      8.2.2.7 Show ribbons
      8.2.2.8 Show excerpt
      8.2.2.9 Show features list
      8.2.2.10 Show CTA button
      8.2.2.11 Show share buttons
      8.2.2.12 Show categories/tags
      8.2.2.13 Excerpt length (words)
      8.2.2.14 Features count (max number to show)

### 8.2.3 (generated)
      8.2.3.1 Enable pagination (toggle)
      8.2.3.2 Pagination type (numbered, prev/next, load more, infinite scroll)
      8.2.3.3 Pagination position (top, bottom, both)
      8.2.3.4 Pagination style (default, minimal, pills)
      8.2.3.5 Show pagination info ("Showing X of Y")
      8.2.3.6 Scroll to top on page change
      8.2.3.7 Smooth scroll animation

### 8.2.4 (generated)
      8.2.4.1 Enable filters (toggle)
      8.2.4.2 Enable sorting (toggle)
      8.2.4.3 Enable search (toggle)
      8.2.4.4 Filter layout (sidebar, top bar, drawer)
      8.2.4.5 Filter position (left, right)
      8.2.4.6 Sticky filter sidebar
      8.2.4.7 Show filter count
      8.2.4.8 Show active filters
      8.2.4.9 Default sort option
      8.2.4.10 Available sort options (multi-select)

### 8.2.5 (generated)
      8.2.5.1 Enable single product page (or redirect to affiliate link)
      8.2.5.2 Single product layout (default, sidebar, full-width)
      8.2.5.3 Show related products
      8.2.5.4 Related products count
      8.2.5.5 Show breadcrumbs
      8.2.5.6 Enable social sharing
      8.2.5.7 Share networks (Facebook, Twitter, LinkedIn, Pinterest, Email)
      8.2.5.8 Enable product schema markup
      8.2.5.9 CTA button position (top, bottom, sticky)

### 8.2.6 (generated)
      8.2.6.1 Enable frontend submission (toggle)
      8.2.6.2 Submission page (select from pages)
      8.2.6.3 Allow anonymous submissions
      8.2.6.4 Require email verification (send confirmation email)
      8.2.6.5 Auto-approve submissions (or require manual approval)
      8.2.6.6 Default submission status (draft, pending, publish)
      8.2.6.7 Notify admin on new submission
      8.2.6.8 Admin notification email address
      8.2.6.9 Notify submitter on approval/rejection
      8.2.6.10 Submission success message (custom text)
      8.2.6.11 Submission redirect URL (after success)
      8.2.6.12 Max submissions per user per day
      8.2.6.13 Max file upload size (MB)
      8.2.6.14 Allowed file types (JPEG, PNG, WebP, GIF)

### 8.2.7 (generated)
      8.2.7.1 Who can add products (select roles)
      8.2.7.2 Who can edit products (select roles)
      8.2.7.3 Who can delete products (select roles)
      8.2.7.4 Who can publish products (select roles)
      8.2.7.5 Who can approve submissions (select roles)
      8.2.7.6 Who can manage settings (select roles)

## 8.3 (generated)
### 8.3.1 (generated)
      8.3.1.1 Primary color (color picker)
      8.3.1.2 Secondary color
      8.3.1.3 Accent color
      8.3.1.4 Success color (green for CTA, badges)
      8.3.1.5 Warning color (yellow/orange)
      8.3.1.6 Error color (red)
      8.3.1.7 Text color (dark)
      8.3.1.8 Background color (light)
      8.3.1.9 Border color
      8.3.1.10 Color scheme presets (light, dark, custom)
      8.3.1.11 Dark mode toggle (enable dark mode)
      8.3.1.12 Dark mode auto (based on system preference)

### 8.3.2 (generated)
      8.3.2.1 Base font size (px, default 16px)
      8.3.2.2 Heading font size scale (1.2x, 1.5x, 2x, etc.)
      8.3.2.3 Line height (1.5, 1.6, 1.75, etc.)
      8.3.2.4 Letter spacing (normal, wide, wider)
      8.3.2.5 Font weight (normal, medium, bold)
      8.3.2.6 Link style (underline, no underline, hover underline)
      8.3.2.7 Link color (inherit, custom)
      8.3.2.8 Link hover color
      8.3.2.9 Typography preset (default, compact, spacious)

### 8.3.3 (generated)
      8.3.3.1 Card border width (0-5px)
      8.3.3.2 Card border style (solid, dashed, dotted, none)
      8.3.3.3 Card border color
      8.3.3.4 Card border radius (0-20px, full)
      8.3.3.5 Card shadow (none, sm, md, lg, xl, 2xl)
      8.3.3.6 Card background color
      8.3.3.7 Card hover effect (lift, shadow, border, none)
      8.3.3.8 Card hover lift amount (px)
      8.3.3.9 Card padding (sm, md, lg, xl)
      8.3.3.10 Card gap (space between cards, px)

### 8.3.4 (generated)
      8.3.4.1 Button style (solid, outline, ghost, gradient)
      8.3.4.2 Button size (sm, md, lg, xl)
      8.3.4.3 Button text (custom default text, e.g., "View Deal")
      8.3.4.4 Button color (primary, secondary, custom)
      8.3.4.5 Button text color
      8.3.4.6 Button hover color
      8.3.4.7 Button hover text color
      8.3.4.8 Button border radius (0-full)
      8.3.4.9 Button font weight
      8.3.4.10 Button icon (left, right, none)
      8.3.4.11 Button icon type (arrow, external link, etc.)
      8.3.4.12 Button full-width (on mobile)

### 8.3.5 (generated)
      8.3.5.1 Sale badge color
      8.3.5.2 Sale badge text color
      8.3.5.3 Sale badge style (badge, corner, banner)
      8.3.5.4 Stock badge color (in stock, out of stock)
      8.3.5.5 Ribbon position (top-left, top-right, etc.)
      8.3.5.6 Ribbon animation (none, pulse, bounce, rotate)
      8.3.5.7 Ribbon size (sm, md, lg)

### 8.3.6 (generated)
      8.3.6.1 Image aspect ratio (1:1, 4:3, 16:9, custom)
      8.3.6.2 Image object fit (cover, contain, fill)
      8.3.6.3 Image border radius
      8.3.6.4 Image hover effect (zoom, fade, slide, none)
      8.3.6.5 Image hover zoom scale (1.05, 1.1, 1.2)
      8.3.6.6 Image placeholder type (blur, color, skeleton)
      8.3.6.7 Image placeholder color
      8.3.6.8 Lazy load images (toggle)

### 8.3.7 (generated)
      8.3.7.1 Container max-width (px, full-width, custom)
      8.3.7.2 Container padding (sm, md, lg, xl)
      8.3.7.3 Spacing (vertical gap between sections)
      8.3.7.4 Row gap (vertical gap between rows)
      8.3.7.5 Column gap (horizontal gap between columns)
      8.3.7.6 Content spacing (space between card elements)
      8.3.7.7 Responsive breakpoints (sm, md, lg, xl, custom)

### 8.3.8 (generated)
      8.3.8.1 Enable animations (toggle)
      8.3.8.2 Animation type (fade, slide, zoom, none)
      8.3.8.3 Animation duration (ms)
      8.3.8.4 Animation easing (ease, linear, ease-in, ease-out, ease-in-out)
      8.3.8.5 Stagger animation delay (ms between cards)
      8.3.8.6 Hover animation (scale, rotate, lift)
      8.3.8.7 Respect prefers-reduced-motion

### 8.3.9 (generated)
      8.3.9.1 Custom CSS textarea (advanced users)
      8.3.9.2 CSS editor with syntax highlighting
      8.3.9.3 CSS validation (basic linting)
      8.3.9.4 CSS preview (live preview in iframe)
      8.3.9.5 CSS priority (load order: plugin CSS → custom CSS)

## 8.4 (generated)
### 8.4.2 (generated)
      8.4.2.1 Enable cloaked links (toggle)
      8.4.2.2 Redirect type (301 permanent, 302 temporary)
      8.4.2.3 Redirect delay (0 seconds, 1, 2, 3, custom)
      8.4.2.4 Show interstitial page ("You are leaving...")
      8.4.2.5 Interstitial page custom message
      8.4.2.6 Open links in new tab (toggle)
      8.4.2.7 Add nofollow to affiliate links
      8.4.2.8 Add sponsored to affiliate links
      8.4.2.9 Add UGC to affiliate links
      8.4.2.10 UTM parameters (source, medium, campaign)
      8.4.2.11 Custom URL parameters (key=value pairs)
      8.4.2.12 Broken link checker (toggle)
      8.4.2.13 Broken link check frequency (daily, weekly)
      8.4.2.14 Broken link notification email

### 8.4.3 (generated)
      8.4.3.1 Enable rate limiting (toggle)
      8.4.3.2 Rate limit: submissions per hour per IP
      8.4.3.3 Rate limit: submissions per day per user
      8.4.3.4 Rate limit: AJAX requests per minute
      8.4.3.5 Enable CAPTCHA (toggle, disabled by default)
      8.4.3.6 CAPTCHA type (math, image, question, honeypot)
      8.4.3.7 CAPTCHA difficulty (easy, medium, hard)
      8.4.3.8 CAPTCHA for anonymous only (toggle)
      8.4.3.9 Honeypot field name (randomize)
      8.4.3.10 Enable spam protection (toggle)
      8.4.3.11 Spam keywords (comma-separated list)
      8.4.3.12 Spam score threshold (0-100)
      8.4.3.13 Block disposable email domains
      8.4.3.14 IP blacklist (comma-separated IPs)
      8.4.3.15 Email domain blacklist

### 8.4.4 (generated)
      8.4.4.1 Enable caching (toggle)
      8.4.4.2 Cache duration (transient TTL, seconds)
      8.4.4.3 Clear cache on product update
      8.4.4.4 Clear all cache (button)
      8.4.4.5 Enable lazy loading (toggle)
      8.4.4.6 Lazy load threshold (px before entering viewport)
      8.4.4.7 Enable image optimization (toggle)
      8.4.4.8 Image quality (0-100, compression)
      8.4.4.9 Generate WebP versions
      8.4.4.10 Enable minification (CSS, JS, toggle)
      8.4.4.11 Load scripts in footer
      8.4.4.12 Defer non-critical JS
      8.4.4.13 Preload key assets
      8.4.4.14 Enable HTTP/2 push (if supported)
      8.4.4.15 Database optimization (button: optimize tables)

### 8.4.5 (generated)
      8.4.5.1 Enable schema markup (toggle)
      8.4.5.2 Schema type (Product, AggregateOffer)
      8.4.5.3 Default brand for schema
      8.4.5.4 Default availability (InStock, OutOfStock)
      8.4.5.5 Default condition (NewCondition, RefurbishedCondition)
      8.4.5.6 Include rating in schema
      8.4.5.7 Include review count in schema
      8.4.5.8 Open Graph tags (toggle)
      8.4.5.9 Twitter Card tags (toggle)
      8.4.5.10 Canonical URLs (toggle)
      8.4.5.11 Meta robots (index, noindex, follow, nofollow)

### 8.4.6 (generated)
      8.4.6.1 CSV column mapping tool
      8.4.6.2 Import validation (dry run before actual import)
      8.4.6.3 Import log (success, errors, skipped)
      8.4.6.4 Export products to CSV
      8.4.6.6 Export all or filtered products
      8.4.6.7 Export format (CSV, JSON, XML)
      8.4.6.8 Include meta data in export
      8.4.6.9 Include taxonomy data in export
   - 8.4.6.5 TODO (auto-inserted)

### 8.4.7 (generated)
      8.4.7.1 Enable debug mode (toggle)
      8.4.7.2 Debug log level (error, warning, info, debug)
      8.4.7.3 Debug log destination (file, database, both)
      8.4.7.4 Clear debug log (button)
      8.4.7.5 Enable REST API (toggle)
      8.4.7.6 REST API authentication (WordPress auth, API key, both)
      8.4.7.7 Generate API key (button)
      8.4.7.8 API rate limiting (requests per hour)
      8.4.7.9 Enable WP-CLI commands (toggle)
      8.4.7.10 Custom hook documentation (link to developer docs)
      8.4.7.11 Database table prefix (read-only, for reference)
      8.4.7.12 Plugin version (read-only, for reference)
   - 8.4.1 TODO (auto-inserted)

## 8.5 (generated)
### 8.5.1 (generated)
      8.5.1.1 Export all settings (JSON download)
      8.5.1.2 Export button
      8.5.1.3 Export includes: general, styling, advanced
      8.5.1.4 Export excludes: API keys, sensitive data
      8.5.1.5 Export filename (auto-generated with date)

### 8.5.2 (generated)
      8.5.2.1 Import settings from JSON file
      8.5.2.2 File upload field
      8.5.2.3 Import button
      8.5.2.4 Import validation (check file format, version)
      8.5.2.5 Import preview (show what will be imported)
      8.5.2.6 Import confirmation (proceed or cancel)
      8.5.2.7 Import success message
      8.5.2.8 Import error handling (display errors)
      8.5.2.9 Backup current settings before import
      8.5.2.10 Restore from backup (if import fails)

### 8.5.3 (generated)
      8.5.3.1 Export all products (CSV, JSON)
      8.5.3.2 Export filtered products (based on current filters)
      8.5.3.3 Select fields to export (checkboxes)
      8.5.3.4 Include images in export (URLs only)
      8.5.3.5 Include categories, tags in export
      8.5.3.6 Include meta data in export
      8.5.3.7 Export button
      8.5.3.8 Export progress indicator (for large datasets)
      8.5.3.9 Export filename (custom or auto-generated)
      8.5.3.10 Export format options (CSV, JSON, XML)

### 8.5.4 (generated)
      8.5.4.1 Import products from CSV
      8.5.4.2 Import products from JSON
      8.5.4.3 File upload field
      8.5.4.4 CSV column mapping tool (map CSV columns to product fields)
      8.5.4.5 Sample CSV download (template)
      8.5.4.6 Import mode (create new, update existing, both)
      8.5.4.7 Match existing by (ID, slug, SKU, title)
      8.5.4.8 Dry run option (preview import without actually importing)
      8.5.4.9 Import validation (check data types, required fields)
      8.5.4.10 Import progress indicator
      8.5.4.11 Import log (created, updated, skipped, errors)
      8.5.4.12 Import error handling (display detailed errors)
      8.5.4.13 Rollback option (undo last import)

## 8.6 (generated)
### 8.6.1 (generated)
      8.6.1.1 Quick start guide (steps to add first product)
      8.6.1.2 Video tutorials (links to videos)
      8.6.1.3 Documentation links
      8.6.1.4 FAQ
      8.6.1.5 Troubleshooting guide

### 8.6.2 (generated)
      8.6.2.1 Support forum link
      8.6.2.2 Email support contact
      8.6.2.3 Bug report form (link to GitHub issues)
      8.6.2.4 Feature request form
      8.6.2.5 Live chat widget (if available)

### 8.6.3 (generated)
      8.6.3.1 WordPress version
      8.6.3.2 PHP version
      8.6.3.3 MySQL version
      8.6.3.4 Server software (Apache, Nginx)
      8.6.3.5 Active theme
      8.6.3.6 Active plugins (list)
      8.6.3.7 Plugin version
      8.6.3.8 Database tables (list custom tables)
      8.6.3.9 Memory limit
      8.6.3.10 Max upload size
      8.6.3.11 Copy system info (button)
      8.6.3.12 Download system info (text file)

### 8.6.4 (generated)
      8.6.4.1 Database repair tool
      8.6.4.2 Cache clear tool
      8.6.4.3 Rewrite rules flush tool
      8.6.4.4 Regenerate thumbnails tool
      8.6.4.5 Check for updates tool (disabled if update checker removed)
      8.6.4.6 Plugin reset tool (reset all settings, with confirmation)

### 8.6.5 (generated)
      8.6.5.1 Plugin name and version
      8.6.5.2 Developer credits
      8.6.5.3 License information (GPL v2+)
      8.6.5.4 Changelog (recent versions)
      8.6.5.5 Roadmap (upcoming features)
      8.6.5.6 Donate/sponsor link

## 8.7 (generated)
   8.7.1 ColorPicker component (Hue/Saturation/Lightness)
   8.7.2 Slider component (range input with value display)
   8.7.3 Toggle component (checkbox styled as switch)
   8.7.4 Select component (dropdown with search)
   8.7.5 MultiSelect component (checkbox list or tag selector)
   8.7.6 TextInput component (with validation)
   8.7.7 Textarea component (with character count)
   8.7.8 NumberInput component (with min/max, step)
   8.7.9 FileUpload component (drag-and-drop)
   8.7.10 Button component (primary, secondary, danger)
   8.7.11 ButtonGroup component (radio-style buttons)
   8.7.12 IconPicker component (select from bundled icons)
   8.7.13 DatePicker component (calendar popup)
   8.7.14 TimePicker component (hour/minute selector)
   8.7.15 CodeEditor component (for custom CSS/JS, syntax highlighting)
   8.7.16 ImageUploader component (with preview)
   8.7.17 RangeSlider component (dual handles for min/max)
   8.7.18 Repeater component (add/remove multiple fields)
   8.7.19 Accordion component (collapsible sections)
   8.7.20 Tabs component (tabbed interface)
   8.7.21 Modal component (popup dialog)
   8.7.22 Tooltip component (help text on hover)
   8.7.23 Badge component (status indicators)
   8.7.24 Alert component (success, error, warning, info)
   8.7.25 ProgressBar component (loading indicator)
   8.7.26 Skeleton component (loading placeholder)
   8.7.27 All components with TypeScript types
   8.7.28 All components with PropTypes validation
   8.7.29 All components memoized (React.memo)
   8.7.30 All components accessible (ARIA, keyboard nav)

## 8.8 (generated)
   8.8.1 Save settings via REST API
   8.8.2 Endpoint: /wp-json/affiliate-showcase/v1/settings
   8.8.3 Method: GET (retrieve settings)
   8.8.4 Method: POST (save settings)
   8.8.5 Nonce authentication
   8.8.6 Capability check (manage_options)
   8.8.7 Input sanitization on server
   8.8.8 Validation on server
   8.8.9 Return validation errors (JSON)
   8.8.10 Return success message (JSON)
   8.8.11 Store settings in WordPress options table
   8.8.12 Option name: affiliate_showcase_settings
   8.8.13 Serialize settings as JSON
   8.8.14 Autoload option: false (for large settings)
   8.8.15 Cache settings in object cache (if available)
   8.8.16 Clear cache on settings save
   8.8.17 Settings versioning (track changes)
   8.8.18 Settings backup before save
   8.8.19 Rollback to previous version
   8.8.20 Settings changelog (audit log)

# Step 9 — Step 9 — Testing & Standards

## 9.1 Code Standards & Linting
   9.1.1 PHP CodeSniffer (phpcs) with WordPress-Core ruleset
   9.1.2 PHP CodeSniffer with WordPress-Extra ruleset
   9.1.3 PHP CodeSniffer with WordPress-Docs ruleset
   9.1.4 PHP CodeSniffer with WordPress-VIP-Go ruleset
   9.1.5 PHP CodeSniffer with PHPCompatibility ruleset
   9.1.6 PHPStan level 8 analysis
   9.1.7 Psalm errorLevel 1 analysis
   9.1.8 All PHP files have declare(strict_types=1);
   9.1.9 All classes, methods, functions have full PHPDoc blocks
   9.1.10 All parameters have type hints
   9.1.11 All methods have return type declarations
   9.1.12 No any types, no mixed without documentation
   9.1.13 ESLint with WordPress preset for JS/TS
   9.1.14 TypeScript strict mode enabled
   9.1.15 Prettier for code formatting
   9.1.16 Stylelint for CSS linting
   9.1.17 No warnings or errors allowed
   9.1.18 Pre-commit hooks run all linters
   9.1.19 CI/CD fails on any linting errors

## 9.2 Unit Testing (PHPUnit)
   9.2.1 PHPUnit 9.5+ installed
   9.2.2 Test bootstrap file (tests/bootstrap.php)
   9.2.3 WordPress test library installed
   9.2.4 Test database setup (separate from dev database)
   9.2.5 Test suite configuration (phpunit.xml)
   9.2.6 Unit tests for all core classes
   9.2.7 Unit tests for all helper functions
   9.2.8 Unit tests for all validators
   9.2.9 Unit tests for all sanitizers
   9.2.10 Unit tests for all formatters
   9.2.11 Unit tests for all models
   9.2.12 Unit tests for all services
   9.2.13 Unit tests for all repositories
   9.2.14 Mock WordPress functions where needed (using Mockery or WP_Mock)
   9.2.15 Test edge cases (empty inputs, null, invalid types)
   9.2.16 Test error conditions
   9.2.17 Test boundary conditions
   9.2.18 Test data providers for multiple inputs
   9.2.19 Test assertions for expected outputs
   9.2.20 Test exceptions (expect specific exceptions to be thrown)
   9.2.21 Test coverage: minimum 95% overall
   9.2.22 Test coverage: 100% for critical paths (security, data integrity)
   9.2.23 Coverage report generation (HTML, Clover XML)
   9.2.24 CI fails on coverage below 90%
   9.2.25 Test documentation (PHPDoc for test methods)
   9.2.26 Test naming: descriptive (test_it_should_validate_email)
   9.2.27 Test organization: group related tests
   9.2.28 Test fixtures for sample data
   9.2.29 Test teardown (clean up after each test)

## 9.3 Integration Testing
   9.3.1 Integration tests for API endpoints
   9.3.2 Integration tests for AJAX handlers
   9.3.3 Integration tests for shortcodes
   9.3.4 Integration tests for admin pages
   9.3.5 Integration tests for frontend rendering
   9.3.6 Integration tests for database operations (insert, update, delete)
   9.3.7 Integration tests for file uploads
   9.3.8 Integration tests for email sending
   9.3.9 Integration tests for cron jobs (if any)
   9.3.10 Integration tests for cache operations
   9.3.11 Integration tests for session management
   9.3.12 Integration tests for user authentication/authorization
   9.3.13 Integration tests for multi-site compatibility
   9.3.14 Integration tests with real WordPress environment
   9.3.15 Integration tests with different themes
   9.3.16 Integration tests with popular plugins (WooCommerce, etc.)
   9.3.17 Integration tests for data import/export
   9.3.18 Integration tests for settings save/retrieve
   9.3.19 Integration test coverage: minimum 80%

## 9.4 Security Testing
   9.4.1 Manual security audit of all input points
   9.4.2 OWASP Top 10 vulnerability checks
   9.4.3 Automated security scanning (WPScan, Snyk)
   9.4.4 Penetration testing (manual or automated)
   9.4.5 File upload security testing (malicious file detection)
   9.4.6 Rate limiting effectiveness testing
   9.4.7 CAPTCHA bypass testing
   9.4.8 Authentication bypass testing
   9.4.9 Authorization bypass testing
   9.4.10 Session hijacking testing
   9.4.11 Open redirect testing (no open redirects)
   9.4.12 Path traversal testing (no directory traversal)
   9.4.13 Command injection testing (no shell commands with user input)
   9.4.14 LDAP injection testing (if applicable)
   9.4.15 Template injection testing
   9.4.16 SSRF testing (no server-side requests with user-controlled URLs)
   9.4.17 Clickjacking testing (X-Frame-Options header)
   9.4.18 Sensitive data in logs (no passwords, API keys in logs)
   9.4.19 Security headers check (CSP, HSTS, X-Content-Type-Options, etc.)

## 9.5 Performance Testing
   9.5.1 Page load time testing (under 2 seconds)
   9.5.2 AJAX request time testing (under 500ms)
   9.5.3 Database query time monitoring (under 100ms per query)
   9.5.4 Slow query detection and optimization
   9.5.5 N+1 query detection and resolution
   9.5.6 Cache hit rate monitoring (>80%)
   9.5.7 Memory usage profiling (under 128MB peak)
   9.5.8 CPU usage profiling
   9.5.9 Asset size analysis (total under 1MB)
   9.5.10 Bundle size budget enforcement
   9.5.11 Image optimization verification (compression, WebP)
   9.5.12 Lazy loading effectiveness
   9.5.13 HTTP request count (under 50 requests)
   9.5.14 Render-blocking resources (minimize)
   9.5.15 Time to First Byte (TTFB under 200ms)
   9.5.16 First Contentful Paint (FCP under 1.5s)
   9.5.17 Largest Contentful Paint (LCP under 2.5s)
   9.5.18 Cumulative Layout Shift (CLS under 0.1)
   9.5.19 First Input Delay (FID under 100ms)
   9.5.20 Lighthouse score: Performance >90
   9.5.21 GTmetrix score: A grade
   9.5.22 WebPageTest score: all A's
   9.5.23 Load testing (100 concurrent users)
   9.5.24 Stress testing (1000 concurrent users)
   9.5.25 Spike testing (sudden traffic surge)
   9.5.26 Endurance testing (24-hour sustained load)
   9.5.27 Database load testing (10K products)
   9.5.28 API rate limit testing

## 9.6 Accessibility Testing
   9.6.1 WCAG 2.1 Level AA compliance
   9.6.2 Keyboard navigation testing (Tab, Enter, Esc, Arrow keys)
   9.6.3 Focus management testing (visible focus indicators)
   9.6.4 Screen reader testing (NVDA, JAWS, VoiceOver)
   9.6.5 ARIA attributes validation
   9.6.6 Semantic HTML validation
   9.6.7 Heading hierarchy validation (H1, H2, H3...)
   9.6.8 Alt text for all images
   9.6.9 Form label associations
   9.6.10 Color contrast checking (4.5:1 for text, 3:1 for large text)
   9.6.11 Non-color indicators (don't rely on color alone)
   9.6.12 Text resize testing (up to 200%)
   9.6.13 Zoom testing (up to 400%)
   9.6.14 High contrast mode support
   9.6.15 Prefers-reduced-motion support
   9.6.16 Skip to content link
   9.6.17 Landmark regions (header, nav, main, footer)
   9.6.18 Link text clarity ('View Product X' vs. 'Click here')
   9.6.19 Error message clarity
   9.6.20 Success message clarity
   9.6.21 Automated accessibility testing (axe, pa11y)
   9.6.22 Manual accessibility audit
   9.6.23 Accessibility statement page
   9.6.24 Accessibility feedback mechanism

## 9.7 Compatibility Testing
   9.7.1 WordPress version compatibility: 6.4 to latest
   9.7.2 PHP version compatibility: 7.4 to 8.3
   9.7.3 MySQL version compatibility: 5.6 to 8.0
   9.7.4 MariaDB version compatibility: 10.3 to latest
   9.7.5 Apache compatibility (2.4+)
   9.7.6 Nginx compatibility (1.18+)
   9.7.7 Litespeed compatibility
   9.7.8 Browser compatibility: Chrome (last 2 versions)
   9.7.9 Browser compatibility: Firefox (last 2 versions)
   9.7.10 Browser compatibility: Safari (last 2 versions)
   9.7.11 Browser compatibility: Edge (last 2 versions)
   9.7.12 Mobile browser compatibility: Chrome, Safari, Firefox
   9.7.13 Tablet compatibility: iPad, Android tablets
   9.7.14 Screen size compatibility: 320px to 4K
   9.7.15 Theme compatibility: Twenty Twenty-Four, Astra, GeneratePress
   9.7.16 Page builder compatibility: Elementor, Beaver Builder, Divi
   9.7.17 Plugin compatibility: WooCommerce, Yoast SEO, Contact Form 7
   9.7.18 Caching plugin compatibility: WP Rocket, W3 Total Cache, LiteSpeed Cache
   9.7.19 Security plugin compatibility: Wordfence, iThemes Security
   9.7.20 Multilingual plugin compatibility: WPML, Polylang
   9.7.21 Multi-site compatibility
   9.7.22 Subdirectory installation compatibility
   9.7.23 Domain mapping compatibility
   9.7.24 Custom wp-content directory compatibility
   9.7.25 Custom database prefix compatibility
   9.7.26 RTL language compatibility
   9.7.27 IPv6 compatibility
   9.7.28 SSL/TLS compatibility
   9.7.29 HTTP/2 compatibility

## 9.8 User Acceptance Testing (UAT)
   9.8.1 Beta testing with select users
   9.8.2 Feedback collection form
   9.8.3 Bug reporting system
   9.8.4 Feature request system
   9.8.5 Usability testing sessions
   9.8.6 Task completion rate (process metric)
   9.8.7 Time on task (process metric)
   9.8.8 Error rate (process metric)
   9.8.9 User satisfaction surveys
   9.8.10 Net Promoter Score (NPS)
   9.8.11 A/B testing for UI elements
   9.8.12 Heatmap analysis (where users click)
   9.8.13 Session recording analysis
   9.8.14 Funnel analysis (submission process)
   9.8.15 Drop-off point identification
   9.8.16 User journey mapping
   9.8.17 Persona-based testing
   9.8.18 Accessibility user testing (users with disabilities)
   9.8.19 Mobile user testing

## 9.9 Rate Limiting Implementation
   9.9.1 Implement rate limiting on submission form (3/hour per IP)
   9.9.2 Store rate limit data in transients (set_transient())
   9.9.3 Key format: affiliate_rate_limit_{IP}_{endpoint}
   9.9.4 Increment counter on each request
   9.9.5 Check counter before processing request
   9.9.6 Return 429 Too Many Requests on limit exceeded
   9.9.7 Display user-friendly error message
   9.9.8 Log rate limit violations
   9.9.9 Whitelist administrator IPs from rate limiting
   9.9.10 Configurable rate limit thresholds in settings
   9.9.11 Different limits for authenticated vs. anonymous users
   9.9.12 Exponential backoff for repeat offenders
   9.9.13 Clear rate limit data after timeout
   9.9.14 Rate limit statistics in admin dashboard
   9.9.15 Rate limit reset option in admin

## 9.10 CAPTCHA Implementation (Optional, Disabled by Default)
   9.10.1 CAPTCHA toggle in settings (disabled by default)
   9.10.2 CAPTCHA type selector (math, image, question)
   9.10.3 Math CAPTCHA: generate random math problem (2 + 3 = ?)
   9.10.4 Store correct answer in session (not exposed to client)
   9.10.5 Validate answer on form submit
   9.10.6 Image CAPTCHA: generate distorted text image (local, no external service)
   9.10.7 Use GD or Imagick for image generation
   9.10.8 Audio CAPTCHA for accessibility
   9.10.9 CAPTCHA refresh button (generate new CAPTCHA)
   9.10.10 CAPTCHA expiration (10 minutes)
   9.10.11 Question CAPTCHA: custom admin-defined questions
   9.10.12 Honeypot field (hidden, should remain empty)
   9.10.13 CAPTCHA only after X failed attempts (configurable)
   9.10.14 CAPTCHA only for anonymous users (skip for logged-in)
   9.10.15 No external CAPTCHA service code (no reCAPTCHA, hCaptcha, etc.)
   9.10.16 CAPTCHA accessibility compliance (WCAG 2.1 AA)
   9.10.17 CAPTCHA error messages
   9.10.18 CAPTCHA logging (failed attempts)
   9.10.19 CAPTCHA bypass for whitelisted IPs

## 9.11 Update Checker Removal
   9.11.1 Remove all pre_set_site_transient_update_plugins filters
   9.11.2 Remove all site_transient_update_plugins filters
   9.11.3 Remove all version comparison code
   9.11.4 Remove all external update API calls
   9.11.5 Remove all update notification code
   9.11.6 Remove all auto-update code
   9.11.7 Remove all update checker classes/files
   9.11.8 No ping-home for version checks
   9.11.9 No analytics or telemetry on plugin usage

## 9.12 Composer Production Requirements
   9.12.1 composer.json 'require': empty or minimal
   9.12.2 No Guzzle in production (use wp_remote_request() instead)
   9.12.3 No Monolog in production (use error_log() or custom logger)
   9.12.4 No Illuminate (Laravel components) in production
   9.12.5 No Symfony components in production (unless absolutely necessary)
   9.12.6 No Carbon (use DateTime instead)
   9.12.7 Evaluate every dependency: is it truly needed?
   9.12.8 For each dependency: can we replace with WordPress functions or custom code?
   9.12.9 Document why each production dependency is included
   9.12.10 Keep total vendor size under 1MB
   9.12.11 Use composer install --no-dev for production build
   9.12.12 Autoload optimization: composer dump-autoload -o
   9.12.13 Remove unused classes from autoload
   9.12.14 Security audit of all dependencies (composer audit)
   9.12.15 License compatibility check (all GPL-compatible)

# Step 10 — Step 10 — Docs, Accessibility & QA

## 10.1 PHPDoc Standards
   10.1.1 Every class has PHPDoc block with @package, @since, @version
   10.1.2 Every method has PHPDoc block with description
   10.1.3 Every method has @param tags with type, name, description
   10.1.4 Every method has @return tag with type and description
   10.1.5 Every method has @throws tag if exceptions are thrown
   10.1.6 Every function has PHPDoc block
   10.1.7 Every property has PHPDoc block with @var type
   10.1.8 Every constant has PHPDoc block
   10.1.9 Interface documentation with @see for implementations
   10.1.10 Trait documentation with @see for usage
   10.1.11 Abstract class documentation
   10.1.12 Hook documentation with @action or @filter tags
   10.1.13 Example code in PHPDoc (in @example tag)
   10.1.14 Internal functions marked with @internal
   10.1.15 Deprecated functions marked with @deprecated and alternative
   10.1.16 TODO comments with ticket reference and assignee
   10.1.17 FIXME comments with priority (P0, P1, P2)
   10.1.18 Complex code has inline comments explaining logic
   10.1.19 Magic methods documented (__construct, __get, __set, etc.)

## 10.2 API Documentation Generation
   10.2.1 Use phpDocumentor or Sami to generate API docs
   10.2.2 Configure documentation generator
   10.2.3 Output directory: /docs/api/
   10.2.4 Include all classes, interfaces, traits, functions
   10.2.5 Generate class hierarchy diagram
   10.2.6 Generate namespace structure
   10.2.7 Generate method index (alphabetical)
   10.2.8 Generate class index (alphabetical)
   10.2.9 Generate package index
   10.2.10 Cross-reference links between classes
   10.2.11 Search functionality in generated docs
   10.2.12 Syntax highlighting for code examples
   10.2.13 Responsive documentation theme
   10.2.14 Version selector (docs for different versions)
   10.2.15 Export as PDF option
   10.2.16 Export as Markdown option
   10.2.17 Host documentation on GitHub Pages
   10.2.18 Auto-generate on release
   10.2.19 Documentation badge in README

## 10.3 User Documentation
   10.3.1 Installation guide (manual and WordPress admin methods)
   10.3.2 Quick start guide (add first product in 5 minutes)
   10.3.3 Admin interface overview with screenshots
   10.3.4 Product creation guide
   10.3.5 Category and taxonomy management guide
   10.3.6 Frontend submission setup guide
   10.3.7 Shortcode reference with all attributes
   10.3.8 Widget configuration guide
   10.3.9 Settings page walkthrough
   10.3.10 Styling customization guide
   10.3.11 Template customization guide
   10.3.12 Import/export guide
   10.3.14 Multi-site setup guide
   10.3.15 Translation guide
   10.3.16 Troubleshooting guide (common issues and solutions)
   10.3.17 FAQ (20+ questions)
   10.3.18 Video tutorials (YouTube playlist)
   10.3.19 Glossary of terms
   - 10.3.13 TODO (auto-inserted)

## 10.4 Developer Documentation
   10.4.1 Plugin architecture overview
   10.4.2 File structure explanation
   10.4.3 Coding standards guide
   10.4.4 Hooks reference (actions and filters)
   10.4.5 Filter hooks with examples (50+ filters)
   10.4.6 Action hooks with examples (30+ actions)
   10.4.7 REST API endpoints documentation
   10.4.8 WP-CLI commands documentation
   10.4.9 Database schema documentation
   10.4.10 Custom post type and taxonomy details
   10.4.11 Template hierarchy explanation
   10.4.12 Theme integration guide
   10.4.13 Child theme creation guide
   10.4.14 Plugin extension guide (how to extend)
   10.4.15 Custom field addition guide
   10.4.16 Custom validation/sanitization guide
   10.4.18 Custom export format guide
   10.4.19 Testing guide (how to run tests)
   10.4.20 Contributing guide (pull requests, code review)
   10.4.21 Changelog format (conventional commits)
   10.4.22 Versioning strategy (semantic versioning)
   10.4.23 Security policy (how to report vulnerabilities)
   10.4.24 Development environment setup
   10.4.25 Build process documentation
   10.4.26 Debugging guide
   10.4.27 Performance optimization tips
   10.4.28 Code examples repository
   10.4.29 Boilerplate/starter templates
   - 10.4.17 TODO (auto-inserted)

## 10.5 Accessibility Implementation
   10.5.1 All interactive elements keyboard accessible
   10.5.2 Tab order logical and predictable
   10.5.3 Focus indicators visible (2px solid outline, high contrast)
   10.5.4 Skip to main content link
   10.5.5 Semantic HTML throughout (<header>, <nav>, <main>, <article>, <aside>, <footer>)
   10.5.6 Heading hierarchy correct (H1 → H2 → H3, no skipping levels)
   10.5.7 Landmark regions marked with role or HTML5 elements
   10.5.8 ARIA labels on all interactive elements without visible text
   10.5.9 ARIA-describedby for additional context
   10.5.10 ARIA-live regions for dynamic content updates
   10.5.11 ARIA-expanded on collapsible elements
   10.5.12 ARIA-controls for relationship indication
   10.5.13 ARIA-hidden on decorative elements
   10.5.14 Role='button' on div/span buttons (or use <button>)
   10.5.15 Color contrast: 4.5:1 for normal text, 3:1 for large text, 3:1 for UI components
   10.5.16 Don't rely on color alone (use icons, text, patterns)
   10.5.17 Text resizable to 200% without loss of content or functionality
   10.5.18 Zoom support to 400%
   10.5.19 No horizontal scrolling at 320px width
   10.5.20 Form labels associated with inputs (<label for='id'>)
   10.5.21 Required fields marked with required attribute and visually
   10.5.22 Error messages descriptive and linked to inputs
   10.5.23 Success messages announced to screen readers
   10.5.24 All images have alt text (empty for decorative)
   10.5.25 Complex images have long descriptions
   10.5.26 Link text descriptive ('View Product X' not 'Click here')
   10.5.27 External links indicated (icon or text)
   10.5.28 New window links warned ('opens in new window')
   10.5.29 Button text descriptive ('Save Settings' not just icon)
   10.5.30 Modal dialogs trap focus, ESC key closes
   10.5.31 Modal dialogs return focus on close
   10.5.32 Dropdown menus keyboard navigable (arrow keys)
   10.5.33 Autocomplete suggestions keyboard navigable
   10.5.34 Pagination keyboard navigable
   10.5.35 Slider controls have text alternatives
   10.5.36 Video/audio have captions and transcripts
   10.5.37 Animations respect prefers-reduced-motion
   10.5.38 No auto-playing media (or provide pause)
   10.5.39 Timeout warnings (if applicable)

## 10.6 WCAG 2.1 AA Compliance
   10.6.1 Perceivable: Text alternatives for non-text content
   10.6.2 Perceivable: Captions and alternatives for time-based media
   10.6.3 Perceivable: Content adaptable (different ways to view)
   10.6.4 Perceivable: Distinguishable (contrast, resize, spacing)
   10.6.5 Operable: Keyboard accessible
   10.6.6 Operable: Enough time to read and use
   10.6.7 Operable: No seizure-inducing content (no flashing)
   10.6.8 Operable: Navigable (skip links, headings, focus order)
   10.6.9 Operable: Input modalities (keyboard, touch, voice)
   10.6.10 Understandable: Readable text
   10.6.11 Understandable: Predictable behavior
   10.6.12 Understandable: Input assistance (labels, errors, suggestions)
   10.6.13 Robust: Compatible with assistive technologies
   10.6.14 Robust: Valid HTML markup
   10.6.15 Level AA: Contrast ratio minimum 4.5:1
   10.6.16 Level AA: Resize text to 200%
   10.6.17 Level AA: Images of text avoided (use real text)
   10.6.18 Level AA: Multiple ways to find pages
   10.6.19 Level AA: Headings and labels descriptive
   10.6.20 Level AA: Focus visible
   10.6.21 Level AA: Language of page identified
   10.6.22 Level AA: Language of parts identified
   10.6.23 Level AA: On focus, no unexpected context change
   10.6.24 Level AA: On input, no unexpected context change
   10.6.25 Level AA: Consistent navigation
   10.6.26 Level AA: Consistent identification
   10.6.27 Level AA: Error suggestion provided
   10.6.28 Level AA: Error prevention (legal, financial, data)
   10.6.29 Level AA: Status messages programmatically determined

## 10.7 Screen Reader Testing
   10.7.1 Test with NVDA (Windows, free)
   10.7.2 Test with JAWS (Windows, trial)
   10.7.3 Test with VoiceOver (macOS/iOS, built-in)
   10.7.4 Test with TalkBack (Android, built-in)
   10.7.5 Test with Narrator (Windows, built-in)
   10.7.6 All interactive elements announced correctly
   10.7.7 Form fields announced with labels and errors
   10.7.8 Dynamic content changes announced (ARIA-live)
   10.7.9 Navigation landmarks announced
   10.7.10 Heading hierarchy clear when navigating by headings
   10.7.11 Link purpose clear from link text
   10.7.12 Button purpose clear from button text
   10.7.13 Image alt text descriptive and concise
   10.7.14 Tables have proper headers and captions
   10.7.15 Lists properly marked up (ul, ol, dl)
   10.7.16 No empty elements that cause confusion
   10.7.17 Reading order logical (matches visual order)
   10.7.18 No 'clickable' divs without proper roles
   10.7.19 Modal dialogs announced and escapable

## 10.8 Fresh Install Testing
   10.8.1 Test on fresh WordPress installation (no other plugins)
   10.8.2 Test activation process (no errors)
   10.8.3 Test database table creation
   10.8.4 Test default options creation
   10.8.5 Test rewrite rules flush
   10.8.6 Test custom post type registration
   10.8.7 Test taxonomy registration
   10.8.8 Test admin menu creation
   10.8.9 Test settings page load
   10.8.10 Test frontend display (default shortcode)
   10.8.11 Test with no products (empty state)
   10.8.12 Add sample product, verify display
   10.8.13 Test deactivation (no errors)
   10.8.14 Test reactivation (data persists)
   10.8.15 Test uninstall (cleanup: options, tables, meta)
   10.8.16 Verify no orphaned data after uninstall
   10.8.17 Test with default WordPress theme
   10.8.18 Test with popular themes (Astra, GeneratePress)
   10.8.19 Test with no internet connection (offline mode)

## 10.9 Multi-Environment Testing
   10.9.1 Test on PHP 7.4, 8.0, 8.1, 8.2, 8.3
   10.9.2 Test on WordPress 6.4, 6.5, 6.6, 6.7, latest
   10.9.3 Test on MySQL 5.6, 5.7, 8.0
   10.9.4 Test on MariaDB 10.3, 10.4, 10.5, 10.6, latest
   10.9.5 Test with custom table prefix (wp_, custom_, site1_)
   10.9.6 Test with subdirectory installation (/blog/, /wp/)
   10.9.7 Test with custom wp-content directory name
   10.9.8 Test with custom plugin directory name
   10.9.9 Test on Apache with mod_rewrite
   10.9.10 Test on Nginx with WordPress rules
   10.9.11 Test on Litespeed
   10.9.12 Test on different server OS (Ubuntu, CentOS, Windows Server)
   10.9.13 Test with different PHP memory limits (128MB, 256MB, 512MB)
   10.9.14 Test with different max upload sizes (2MB, 10MB, 64MB)
   10.9.15 Test with different PHP extensions enabled/disabled
   10.9.16 Test with object caching (Redis, Memcached)
   10.9.17 Test with opcode caching (OPcache, APC)
   10.9.18 Test with CDN (Cloudflare, KeyCDN - verify compatibility)
   10.9.19 Test with security plugins (Wordfence, iThemes Security)

## 10.10 Offline Functionality Verification
   10.10.1 Disable internet on test environment
   10.10.2 Activate plugin (should succeed)
   10.10.3 Access admin pages (should load)
   10.10.4 Access frontend pages (should load)
   10.10.5 View products (should display)
   10.10.6 Use filters (should work)
   10.10.7 Use search (should work)
   10.10.8 Submit form (should work)
   10.10.9 Upload images (should work)
   10.10.10 Save settings (should work)
   10.10.11 Network tab shows zero external requests
   10.10.12 No console errors related to failed external loads
   10.10.13 All images load from local server
   10.10.14 All fonts load from local system stack
   10.10.15 All icons display (SVG, local)
   10.10.16 All scripts execute (bundled, local)
   10.10.17 All styles apply (bundled, local)
   10.10.18 No CDN calls in source code
   10.10.19 No Google Fonts calls in source code

## 10.11 Standalone Audit
   10.11.1 Run grep -Ri 'https?://' dist/ vendor/ in plugin directory
   10.11.2 Verify only results are user-entered affiliate links
   10.11.3 No googleapis.com, gstatic.com, google.com
   10.11.4 No cdnjs.cloudflare.com, unpkg.com, jsdelivr.com
   10.11.5 No fontawesome.com, fonts.com
   10.11.6 No analytics.google.com, googletagmanager.com
   10.11.7 No facebook.com, twitter.com (except share links)
   10.11.8 No gravatar.com (use local avatars if needed)
   10.11.9 No external API endpoints (except user affiliate links)
   10.11.10 Source code review: no external @import in CSS
   10.11.11 Source code review: no external src in HTML
   10.11.12 Source code review: no external script tags
   10.11.13 Source code review: no external link tags (except preconnect for user domains)
   10.11.14 Source code review: no wp_remote_get() except for user-triggered actions
   10.11.15 Package.json: no CDN dependencies
   10.11.16 Composer.json: production require (minimal or empty)
   10.11.17 All assets in dist/ are bundled and minified
   10.11.18 No update checker code present
   10.11.19 No telemetry or phone-home code present

## 10.12 Security Hardening Checklist
   10.12.1 All inputs sanitized (100% coverage)
   10.12.2 All outputs escaped (100% coverage)
   10.12.3 All SQL queries use prepared statements
   10.12.4 All nonces verified before state changes
   10.12.5 All capabilities checked before privileged operations
   10.12.6 No direct file access (check ABSPATH defined)
   10.12.7 No eval() or similar dangerous functions
   10.12.8 No unserialize() on user input
   10.12.9 No shell_exec() or exec() with user input
   10.12.10 File uploads strictly validated (type, size, content)
   10.12.11 Uploaded files stored securely (non-executable directory)
   10.12.12 No PHP files accepted in uploads
   10.12.13 Rate limiting on all forms and API endpoints
   10.12.14 CSRF protection on all state-changing actions
   10.12.15 XSS protection on all user-generated content
   10.12.16 SQL injection protection on all database operations
   10.12.17 Path traversal protection on file operations
   10.12.18 Open redirect protection on redirect handler
   10.12.19 Session management secure (httponly, secure cookies)
   10.12.20 No sensitive data in logs or error messages
   10.12.21 No exposed debug information in production
   10.12.22 Security headers set (X-Frame-Options, X-Content-Type-Options)
   10.12.23 Permissions follow least privilege principle
   10.12.24 No hardcoded credentials or API keys
   10.12.25 Third-party dependencies audited and minimal

# Step 11 — Step 11 — CI/CD & Packaging

## 11.1 GitHub Actions Workflow
   11.1.1 Workflow file: .github/workflows/main.yml
   11.1.2 Trigger on: push to main, pull request to main
   11.1.3 Trigger on: release tag creation
   11.1.4 Job: Lint PHP (phpcs, phpstan, psalm)
   11.1.5 Job: Lint JS/TS (eslint, prettier)
   11.1.6 Job: Lint CSS (stylelint)
   11.1.7 Job: Unit tests (PHPUnit)
   11.1.8 Job: Integration tests
   11.1.9 Job: Build assets (npm run build)
   11.1.10 Job: Security scan (npm audit, composer audit)
   11.1.11 Job: Accessibility tests (pa11y, axe)
   11.1.12 Job: Performance tests (Lighthouse CI)
   11.1.13 Job: Compatibility tests (PHP version matrix)
   11.1.14 Job: Code coverage (upload to Codecov)
   11.1.15 Job: Create release package (.zip)
   11.1.16 Job: Deploy documentation (GitHub Pages)
   11.1.17 Job: Notify on failure (Slack, email)
   11.1.18 Fail on any linting error
   11.1.19 Fail on test failure
   11.1.20 Fail on coverage below 90%
   11.1.21 Fail on security vulnerabilities (high/critical)
   11.1.22 Fail on accessibility errors
   11.1.23 Artifact: test reports
   11.1.24 Artifact: coverage reports
   11.1.25 Artifact: build logs
   11.1.26 Artifact: release package (.zip)
   11.1.27 Cache: Composer dependencies
   11.1.28 Cache: NPM dependencies
   11.1.29 Cache: Build outputs (if applicable)

## 11.2 Packaging Script
   11.2.1 Script file: build/package.sh or package.js
   11.2.2 Install production dependencies: composer install --no-dev --optimize-autoloader
   11.2.3 Install NPM dependencies: npm ci
   11.2.4 Build assets: npm run build
   11.2.5 Create package directory: build/affiliate-product-showcase/
   11.2.6 Copy plugin files to package directory
   11.2.7 Include: *.php (main plugin file, includes, admin, public)
   11.2.8 Include: /vendor/ (production dependencies only)
   11.2.9 Include: /dist/ (compiled assets)
   11.2.10 Include: /languages/ (.pot, .po, .mo files)
   11.2.11 Include: /assets/ (non-compiled assets, if any)
   11.2.12 Include: README.txt (WordPress.org format)
   11.2.13 Include: readme.md (GitHub format)
   11.2.14 Include: LICENSE (GPL v2 or later)
   11.2.15 Include: CHANGELOG.md
   11.2.16 Exclude: .git/, .github/
   11.2.17 Exclude: node_modules/
   11.2.18 Exclude: src/ (uncompiled source)
   11.2.19 Exclude: tests/
   11.2.20 Exclude: .env*, .env.example
   11.2.21 Exclude: docker/, docker-compose.yml
   11.2.22 Exclude: build/ (build scripts, not included in package)
   11.2.23 Exclude: phpunit.xml, phpcs.xml, phpstan.neon, psalm.xml
   11.2.24 Exclude: .eslintrc*, .prettierrc*, stylelint.config.*
   11.2.25 Exclude: tsconfig.json, vite.config.ts, tailwind.config.js
   11.2.26 Exclude: composer.json, composer.lock, package.json, package-lock.json
   11.2.27 Exclude: .editorconfig, .gitignore, .gitattributes
   11.2.28 Exclude: any other development files
   11.2.29 Create .zip archive: affiliate-product-showcase-{version}.zip

## 11.3 Final Package Audit
   11.3.1 Extract .zip to temporary directory
   11.3.2 Verify no development files present
   11.3.3 Verify no .env files present
   11.3.4 Verify no node_modules/ present
   11.3.5 Verify no src/ (uncompiled source) present
   11.3.6 Verify no tests/ present
   11.3.7 Verify /vendor/ contains production dependencies only
   11.3.8 Verify /dist/ contains compiled assets
   11.3.9 Run grep -R -i 'https?://' dist/ vendor/ - should return zero external domains (except comments)
   11.3.10 Verify no googleapis.com, google.com, cdnjs.com, etc.
   11.3.11 Verify plugin activates without errors
   11.3.12 Verify admin pages load
   11.3.13 Verify frontend displays correctly
   11.3.14 Verify settings save correctly
   11.3.15 Verify no PHP errors or warnings in debug log
   11.3.16 Verify no JavaScript console errors
   11.3.17 Test offline mode (no internet, plugin functions fully)
   11.3.18 Network tab: zero outbound requests (except user affiliate clicks)
   11.3.19 Verify file size reasonable (under 5MB for .zip)

## 11.4 Fresh WordPress Install Test
   11.4.1 Spin up fresh WordPress install (Docker or local server)
   11.4.2 WordPress version: latest stable
   11.4.3 PHP version: 8.3
   11.4.4 Default theme: Twenty Twenty-Four
   11.4.5 No other plugins installed
   11.4.6 Upload plugin .zip via admin
   11.4.7 Activate plugin
   11.4.8 Verify activation success (no errors)
   11.4.9 Verify admin menu present
   11.4.10 Verify settings page loads
   11.4.11 Add sample product
   11.4.12 Verify product saves correctly
   11.4.13 View product on frontend
   11.4.14 Verify product displays correctly
   11.4.15 Add shortcode to page
   11.4.16 Verify shortcode renders correctly
   11.4.17 Test filtering, sorting, search
   11.4.18 Deactivate plugin
   11.4.19 Verify deactivation success (no errors)
   11.4.20 Reactivate plugin
   11.4.21 Verify data persists
   11.4.22 Uninstall plugin
   11.4.23 Verify cleanup: options deleted
   11.4.24 Verify cleanup: custom tables deleted

## 11.5 Offline Mode Test
   11.5.1 Disable internet connection on test environment
   11.5.2 Fresh WordPress install
   11.5.3 Upload plugin .zip via admin (already downloaded)
   11.5.4 Activate plugin - should succeed
   11.5.5 Access admin pages - should load fully
   11.5.6 Access frontend - should load fully
   11.5.7 All images load (local only)
   11.5.8 All fonts render (system stack)
   11.5.9 All icons display (local SVG)
   11.5.10 All scripts execute (bundled)
   11.5.11 All styles apply (bundled)
   11.5.12 Open browser DevTools Network tab
   11.5.13 Verify zero external requests (all should fail or not exist)
   11.5.14 No console errors related to failed external resources
   11.5.15 All functionality works: CRUD, filtering, submission, etc.
   11.5.16 Settings save successfully
   11.5.17 Affiliate link redirects work (to external sites, which will fail, but redirect logic works)
   11.5.19 Deactivate plugin - should succeed
   - 11.5.18 TODO (auto-inserted)

## 11.6 Marketplace Asset Preparation
   11.6.1 Plugin header comment (Name, Plugin URI, Description, Version, Author, License)
   11.6.2 README.txt for WordPress.org (required sections)
   11.6.3 Screenshots (high quality, 1200px+ width)
   11.6.4 Banner image (772×250 px for WordPress.org)
   11.6.5 Banner image retina (1544×500 px)
   11.6.6 Icon image (128×128 px)
   11.6.7 Icon image retina (256×256 px)
   11.6.8 Plugin logo (SVG or high-res PNG)
   11.6.9 Video demo/walkthrough (YouTube or Vimeo, 2-5 minutes)

## 11.7 Release Checklist
   11.7.1 All tests passing (unit, integration, E2E)
   11.7.2 Code coverage ≥95%
   11.7.3 No linting errors or warnings
   11.7.4 No security vulnerabilities
   11.7.5 No accessibility errors
   11.7.6 Documentation up to date
   11.7.7 Changelog updated
   11.7.8 Version number bumped (semver)
   11.7.9 README.txt updated (tested up to, stable tag)
   11.7.10 Translation files generated (.pot)
   11.7.11 All strings translatable (i18n complete)
   11.7.12 Screenshots current and accurate
   11.7.13 Assets compiled and optimized
   11.7.14 Package created and tested
   11.7.15 Fresh install test passed
   11.7.16 Offline test passed
   11.7.17 Standalone audit passed (zero external dependencies)
   11.7.18 Multi-environment test passed
   11.7.19 Performance benchmarks met
   11.7.20 Accessibility audit passed
   11.7.21 Security audit passed
   11.7.22 Beta testing feedback addressed
   11.7.23 Known issues documented
   11.7.24 Support channels ready
   11.7.25 Marketing materials ready
   11.7.26 Demo site live and functional
   11.7.27 Git tag created (e.g., v1.0.0)
   11.7.28 GitHub release created with changelog
   11.7.29 Plugin submitted to WordPress.org (if applicable)
   11.7.30 Announcement prepared (blog post, social media)

# Step 12 — Step 12 — Marketing & Launch

## 12.1 Demo Site Setup
   12.1.1 Live demo site URL (e.g., demo.affiliateshowcase.com)
   12.1.2 Fresh WordPress installation with plugin installed
   12.1.3 Sample products: 50-100 diverse products across categories
   12.1.4 Sample categories: Electronics, Software, Home & Garden, Fashion, etc.
   12.1.5 Sample brands: mix of well-known brands
   12.1.6 Sample ribbons: Best Seller, New, Sale, Trending
   12.1.7 Realistic product images (royalty-free)
   12.1.8 Realistic descriptions and features
   12.1.9 Varied price points ($10 to $1000+)
   12.1.10 Demo pages showing different layouts (grid, list, slider)
   12.1.11 Demo pages showing filter and sort functionality
   12.1.12 Demo page with submission form
   12.1.13 Demo page with single product view
   12.1.14 Demo page with comparison table
   12.1.15 Homepage with hero and featured products
   12.1.16 Demo admin credentials (viewer role, limited access)
   12.1.17 Admin dashboard visible (sample data)
   12.1.18 Settings page visible (locked to prevent changes)
   12.1.19 SSL certificate installed
   12.1.20 Fast hosting (under 1s page load time)
   12.1.21 Mobile-responsive showcase
   12.1.22 Dark mode toggle on demo site
   12.1.23 Contact form for demo inquiries
   12.1.24 Link to documentation from demo site

## 12.2 Marketing Copy
   12.2.1 Headline: 'The Enterprise-Level Affiliate Product Showcase Plugin for WordPress'
   12.2.2 Subheadline: '100% Standalone. Zero External Dependencies. Privacy-First.'
   12.2.3 Unique Selling Points (USPs):
   12.2.4 Feature sections:
   12.2.5 Benefits:
   12.2.6 Testimonials (collect from beta users)
   12.2.7 Comparison table (vs. competitors)
   12.2.8 Pricing (if premium) or Free highlights
   12.2.9 Call-to-action: 'Download Free' or 'Get Started'
   12.2.10 Money-back guarantee (if applicable)
   12.2.11 Support promise (response time, channels)

## 12.3 (reserved)
   12.3.1 Screenshot descriptions optimized for SEO
   12.3.2 Captions highlighting key features
   12.3.3 Before/after comparisons (if applicable)
   12.3.4 Mobile screenshots (separate set)
   12.3.5 Tablet screenshots (if necessary)
   12.3.6 Animated GIFs for complex features (filter, sort, etc.)
   12.3.7 Video walkthrough script
   12.3.8 Video length: 3-5 minutes
   12.3.9 Video quality: 1080p minimum
   12.3.10 Video hosting: YouTube (public or unlisted)
   12.3.11 Video thumbnail: eye-catching, branded
   12.3.12 Video closed captions: for accessibility
   12.3.13 Social media graphics (1200×628 for Facebook, 1024×512 for Twitter)
   12.3.14 Pinterest graphics (1000×1500)

## 12.4 WordPress.org Submission
   12.4.1 Create WordPress.org account (if not exists)
   12.4.2 Submit plugin via Add Your Plugin form
   12.4.3 Wait for plugin review (1-2 weeks typically)
   12.4.4 Address reviewer feedback promptly
   12.4.5 Ensure compliance with WordPress.org guidelines:
   12.4.6 Once approved, commit to SVN repository
   12.4.7 Tag first release (e.g., 1.0.0)
   12.4.8 Upload screenshots and banner to assets/ folder in SVN
   12.4.9 Verify plugin page displays correctly

## 12.5 GitHub Release
   12.5.1 Create GitHub release for version tag (e.g., v1.0.0)
   12.5.2 Release title: 'Version 1.0.0 - Initial Release'
   12.5.3 Release description: changelog with all features
   12.5.4 Upload .zip package as release asset
   12.5.5 Mark as 'Latest' release
   12.5.6 Link to demo site in release notes
   12.5.7 Link to documentation in release notes
   12.5.8 Mention notable features in release notes
   12.5.9 Credit contributors in release notes

## 12.6 Documentation Site
   12.6.1 Dedicated documentation site (docs.affiliateshowcase.com or GitHub Pages)
   12.6.2 Clean, searchable documentation theme (Docsify, MkDocs, etc.)
   12.6.3 Sections: Getting Started, Features, Shortcodes, Hooks, Settings, FAQs
   12.6.4 Search functionality
   12.6.5 Version selector (docs for each major version)
   12.6.6 Code examples with syntax highlighting
   12.6.7 Screenshots and GIFs throughout docs
   12.6.8 Responsive design (mobile-friendly)
   12.6.9 Edit on GitHub links (for contributions)

## 12.7 Support Channels
   12.7.1 WordPress.org support forum (monitor daily)
   12.7.2 GitHub Issues (for bug reports and feature requests)
   12.7.3 Email support (support@affiliateshowcase.com)
   12.7.4 Contact form on plugin site
   12.7.5 FAQ page (constantly updated based on common questions)
   12.7.6 Live chat widget (if resources allow)
   12.7.7 Community Slack/Discord (if community grows)
   12.7.8 Support response time: under 24 hours for email, under 48 hours for forum
   12.7.9 Clear issue templates on GitHub (bug report, feature request)

## 12.8 Launch Announcement
   12.8.1 Blog post on plugin website
   12.8.2 Social media announcements:
   12.8.3 Email announcement (if list exists)
   12.8.4 Outreach to WordPress bloggers/influencers
   12.8.5 Submit to WordPress news sites (WPTavern, etc.)
   12.8.6 Submit to plugin directories (besides WordPress.org)
   12.8.7 Post in relevant Facebook groups (WordPress, affiliate marketing)

## 12.9 SEO Optimization
   12.9.1 Keyword research (affiliate plugin, product showcase, WordPress affiliate, etc.)
   12.9.2 Plugin page optimized for keywords
   12.9.3 Blog posts targeting long-tail keywords
   12.9.4 Meta descriptions optimized (under 160 characters)
   12.9.5 Title tags optimized (under 60 characters)
   12.9.6 Schema markup on plugin pages (SoftwareApplication)
   12.9.7 Internal linking between docs, blog posts, and main pages
   12.9.8 External backlinks (reach out for guest posts, mentions)
   12.9.9 Image alt text optimized
   12.9.10 Fast page load times (Core Web Vitals optimized)
   12.9.11 Mobile-friendly pages
   12.9.12 SSL certificate installed
   12.9.13 Sitemap submitted to Google Search Console
   12.9.14 Robots.txt configured

## 12.10 Continuous Improvement
   12.10.1 Monitor support channels daily (WordPress.org, GitHub, email)
   12.10.2 Collect feedback and feature requests
   12.10.3 Prioritize bugs and security issues (fix within 24-48 hours)
   12.10.4 Monthly minor releases (bug fixes, small improvements)
   12.10.5 Quarterly major releases (new features)
   12.10.6 Maintain changelog (all changes documented)
   12.10.7 Notify users of updates (via WordPress admin notice)
   12.10.8 Monitor analytics: downloads, active installs, ratings
   12.10.9 Respond to all reviews (thank positive, address negative)
   12.10.10 Regular security audits (quarterly)
   12.10.11 Regular performance audits (quarterly)
   12.10.12 Regular accessibility audits (bi-annually)
   12.10.13 Keep dependencies updated (WordPress, PHP compatibility)
   12.10.14 Deprecate old features gracefully (6-month notice)
   12.10.15 Maintain backward compatibility (within major versions)
   12.10.16 Community engagement (respond to discussions, tweets, etc.)
   12.10.17 Celebrate milestones (1000 downloads, 100 5-star reviews, etc.)
   12.10.18 User surveys (annually, to guide roadmap)
   12.10.19 Competitive analysis (monitor competitor features)
   12.10.20 Iterate on UX based on user feedback and analytics
