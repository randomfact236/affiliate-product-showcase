# Affiliate Product Showcase

Enterprise-ready WordPress plugin boilerplate (Tailwind + Vite + PSR-4).

This repository provides a hardened, performance-conscious plugin scaffold with modern frontend tooling, strict PHP tooling, and CI-friendly scripts.

Goals:
- PSR-4 autoloading and strict typing
- Fully configured dev tooling: PHPUnit, PHPStan, Psalm, PHPCS + WPCS
- Build pipeline ready for Tailwind + Vite
- Production packaging with vendor exclusion and optimized autoloader

---

## Quick start

Prerequisites:
- PHP 7.4 - 8.3 (project configured for `>=7.4 <8.4`)
- Composer (https://getcomposer.org)
- Node.js & npm (for frontend tooling)

Clone and install dependencies:

```bash
git clone <repo-url> affiliate-product-showcase
cd affiliate-product-showcase
composer install
npm install
```

Validate composer metadata:

```bash
composer validate --no-check-publish
```

Generate optimized autoloader (verifies PSR-4 mapping):

```bash
composer dump-autoload -o
```

Quick autoload sanity check (replace `Example` with a real class when present):

```bash
php -r "require 'vendor/autoload.php'; var_export(class_exists('AffiliateProductShowcase\\Example'));"
```

---

## Running tests, linting and analysis

All shortcuts are available via `composer` scripts.

Run unit tests:

```bash
composer test
# or directly
vendor/bin/phpunit --configuration phpunit.xml
```

Run linting (PHPCS with WPCS):

```bash
composer lint
# to run phpcs directly with the included ruleset
vendor/bin/phpcs --standard=phpcs.xml src tests
```

Static analysis:

```bash
composer analyze
composer run-script analyze:psalm
```

Build (production install + optimize autoloader):

```bash
composer build
npm run build   # frontend build via Vite (project must include package.json scripts)
```

Create packaged archive (zip):

```bash
composer package
```

---

## Development workflow

1. Create a feature branch: `git checkout -b feat/short-description`
2. Run unit tests and static analysis locally
3. Lint and fix issues: `composer lint` / `vendor/bin/phpcbf` where applicable
4. Build frontend assets during development:

```bash
npm run dev   # Vite dev server
```

5. Commit and push; open PR. CI should run `composer install --no-interaction`, `composer test`, `composer analyze`, `npm ci`, and `npm run build`.

Release checklist:
- Update `readme.html`/plugin header metadata and version
- Run `composer build` and verify `build/affiliate-product-showcase.zip`
- Sign release and publish to the chosen distribution channel

---

## Directory structure

- `src/` — PSR-4 plugin source (`AffiliateProductShowcase\`)
	- `Core/Plugin.php` — main bootstrap singleton
	- other PSR-4 classes
- `includes/` — legacy/classmap-loaded helpers (classmap autoloaded)
- `tests/` — PHPUnit tests (autoload-dev configured)
- `vendor/` — Composer dependencies (excluded from archive)
- `build/` — build artifacts and archives (generated)
- `plan/` — project plan and todo artifacts
- `docs/` — documentation and maintenance notes

---

## Security & Best Practices

- Use nonces and capabilities for all admin actions
- Escape all output (esc_html, esc_attr, wp_kses) and sanitize inputs
- Prefer prepared statements / WPDB placeholders when using direct DB queries
- Use object caching (Redis/Memcached) for expensive operations and add `object-cache.php` drop-in for persistent caches
- Run static analysis (PHPStan/Psalm) and fix level issues before merging

## Notes about Composer and Packaging

- `composer.json` is configured with `archive.exclude` to omit `vendor/`, `node_modules/`, `tests/`, and other dev artifacts during `composer archive` or packaging.
- The project sets `config.platform-check`, `optimize-autoloader` and `sort-packages` to align with production best practices.

---

If you'd like, I can:
- Add CI workflow files for GitHub Actions (install, test, lint, analyze, build)
- Add `package.json` and Vite/Tailwind scaffolding
- Wire plugin main file to call `AffiliateProductShowcase\Core\Plugin::get_instance()` on `plugins_loaded`

Read more in [plan/plan_source.md](plan/plan_source.md).

