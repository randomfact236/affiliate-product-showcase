# Audit G2 — Consolidated 11-Item Audit (2026 Standards)
**Date:** 2026-01-13
**Scope:** Only the 11 consolidated findings provided by the user. Audit-only — no code changes.

---

Summary: This file documents the current repository state for the 11 items, provides a concise verdict (✅ / ⚠️ / ❌ / 🔍), 2026 best-practices, and whether each item is a blocker or nice-to-have.

---

1) Docker volume mount path contains placeholder "your-plugin"

- Exact current state (file + snippet):

  File: [docker/docker-compose.yml](docker/docker-compose.yml#L49-L66)

  ```yaml
    volumes:
      - ../:/var/www/html:cached
      - ./plugins/your-plugin:/var/www/html/wp-content/plugins/your-plugin
      - ./php-fpm/www.conf:/usr/local/etc/php-fpm.d/www.conf:ro
  ```

- Verdict: ❌
- 2026 best practice: Use exact project/plugin directory names in Docker mounts (no placeholders). Automation and CI rely on deterministic mounts.
- Blocker?: YES — breaks local Docker dev environment and automated container workflows.

---

2) Only `.env.example` exists — no real `.env`

- Exact current state (file + snippet):

  File: [.env.example](.env.example#L1-L18)

  ```dotenv
  # Affiliate Product Showcase - Environment Configuration
  # Copy this file to .env and configure your settings
  # DO NOT commit .env to version control

  # Database Configuration (Docker)
  MYSQL_ROOT_PASSWORD=your_root_password_here
  MYSQL_DATABASE=affiliate_showcase
  MYSQL_USER=affiliate_user
  MYSQL_PASSWORD=your_user_password_here
  ```

  File: [docker/docker-compose.yml](docker/docker-compose.yml#L1-L12)

  ```yaml
  services:
    db:
      env_file:
        - ../.env
  ```

  File: [.gitignore](.gitignore#L19-L24)

  ```ignore
  ### Environment
  .env
  .env.*
  ```

- Verdict: ✅
- 2026 best practice: Commit `.env.example` only; do not commit `.env`. Document copy+configure instructions in README/dev docs.
- Blocker?: NO — correct security practice for repositories.

---

3) PHP requirement in header & composer.json = 7.4 / ^7.4

- Exact current state (files + snippets):

  File: [wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php](wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php#L6-L10)

  ```php
  * Requires at least: 6.0
  * Requires PHP:      7.4
  ```

  File: [wp-content/plugins/affiliate-product-showcase/composer.json](wp-content/plugins/affiliate-product-showcase/composer.json#L20-L30)

  ```json
  "require": {
    "php": "^7.4|^8.0|^8.1|^8.2|^8.3",
    // ...
  }
  ```

- Verdict: ❌
- 2026 best practice: Declare and target PHP >= 8.3 for new/enterprise work; at minimum keep composer.json and plugin header consistent. Example recommended values: plugin header `Requires PHP: 8.1` (or `8.3` if you require it), composer.json `"php": "^8.3"` and composer `config.platform.php` aligned to CI/runtime.
- Blocker?: YES — security/compatibility issue (advertises EOL PHP support and mismatched constraints).

---

4) WP minimum version = 6.0

- Exact current state (file + snippet):

  File: [wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php](wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php#L6-L8)

  ```php
  * Requires at least: 6.0
  ```

- Verdict: ❌
- 2026 best practice: Target WordPress 6.7+ for feature parity and security in 2026; set `Requires at least:` to a modern baseline (6.4+ or 6.7 depending on supported features) and ensure compatibility tests cover the declared minimum.
- Blocker?: YES — plugin claims support for outdated WP; increases compatibility burden and may miss modern APIs.

---

5) `package-lock.json` is gitignored

- Exact current state (file + snippet):

  File: [.gitignore](.gitignore#L1-L8)

  ```ignore
  ### Node & frontend
  node_modules/
  npm-debug.log*
  yarn-debug.log*
  yarn-error.log*
  package-lock.json
  ```

- Verdict: ⚠️
- 2026 best practice: Enterprise teams usually commit `package-lock.json` (npm v10+ recommends committing lockfile) to ensure deterministic installs; projects with multi-environment build systems benefit from committing lockfiles. However some repositories choose to ignore it and rely on CI-managed reproducibility.
- Blocker?: NO — operational/consistency concern; recommend committing for enterprise/CI determinism.

---

6) `assets/dist/` fully gitignored (including manifest & SRI)

- Exact current state (file + snippet):

  File: [.gitignore](.gitignore#L12-L24)

  ```ignore
  ### Build & dist
  dist/
  assets/dist/
  assets/dist/*.map
  *.min.js.map
  # ...
  wp-content/plugins/affiliate-product-showcase/assets/dist/
  wp-content/plugins/affiliate-product-showcase/assets/dist/sri-hashes.json
  wp-content/plugins/affiliate-product-showcase/assets/dist/compression-report.json
  ```

- Verdict: ❌
- 2026 best practice: For local development, ignore build artifacts. For distribution (WordPress.org) you must include compiled assets (manifest, SRI, compressed files). Approach: keep `assets/dist/` ignored in source branches, but ensure build artifacts are included in release zips or a release branch/tag that's prepared with assets.
- Blocker?: YES for WordPress.org release (must include compiled assets); NO for local development if CI/build pipeline produces distribution that includes assets.

---

7) `block.json` files are very minimal (missing attributes, supports, icon, scripts…)

- Exact current state (files + snippets):

  File: [wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/block.json](wp-content/plugins/affiliate-product-showcase/blocks/product-showcase/block.json)

  ```json
  { "apiVersion": 2, "name": "aps/product-showcase", "title": "Product Showcase", "category": "widgets" }
  ```

  File: [wp-content/plugins/affiliate-product-showcase/blocks/product-grid/block.json](wp-content/plugins/affiliate-product-showcase/blocks/product-grid/block.json)

  ```json
  {
    "apiVersion": 2,
    "name": "aps/product-grid",
    "title": "Product Grid",
    "category": "widgets",
    "attributes": { "perPage": { "type": "number", "default": 6 } },
    "supports": { "align": true }
  }
  ```

- Verdict: ⚠️
- 2026 best practice: Provide complete `block.json` metadata (description, icon, keywords, textdomain, attributes, supports, editorScript/editorStyle/style/viewScript, example, styles) for discoverability and enterprise-grade UX.
- Blocker?: NO — functional but low-quality and poor UX; not a blocker for development, but required for production polish and marketplace expectations.

---

8) Vite manifest is in `.vite/` subfolder instead of dist root

- Exact current state (evidence and notes):

  File: [wp-content/plugins/affiliate-product-showcase/assets/dist/sri-hashes.json](wp-content/plugins/affiliate-product-showcase/assets/dist/sri-hashes.json#L1-L13)

  ```json
  {
    ".vite/manifest.json": {
      "integrity": "sha384-Fz5oYiMbq5kkOqlx0l0icNC8XDmRuC8mGwx2yq7tCOzkBxX47xB7meqX39fHnKiy",
      "size": 1527,
      "gzip": { "size": 308, "ratio": 0.2017 },
      "brotli": { "size": 270, "ratio": 0.1768 }
    }
  }
  ```

  File: [wp-content/plugins/affiliate-product-showcase/src/Assets/Manifest.php](wp-content/plugins/affiliate-product-showcase/src/Assets/Manifest.php#L20-L26)

  ```php
  $this->manifest_path = $this->normalize_path( Constants::viewPath( 'assets/dist/manifest.json' ) );
  ```

- Verdict: ❌
- 2026 best practice: Configure Vite to emit `assets/dist/manifest.json` OR change PHP to read from `.vite/manifest.json`. Keep the runtime reader and build output aligned.
- Blocker?: YES — current state indicates PHP expects `assets/dist/manifest.json` while build output references `.vite/manifest.json`.

---

9) Frontend files are `.js`/`.jsx` instead of `.ts`/`.tsx` despite TS config

- Exact current state (files + snippet):

  File: [wp-content/plugins/affiliate-product-showcase/tsconfig.json](wp-content/plugins/affiliate-product-showcase/tsconfig.json#L1-L20)

  ```jsonc
  {
    "compilerOptions": {
      "jsx": "react-jsx",
      "noEmit": true,
      "strict": true,
      // ... paths mappings to frontend/*
    },
    "include": ["frontend/**/*", "src/**/*"]
  }
  ```

  File: [wp-content/plugins/affiliate-product-showcase/vite.config.js](wp-content/plugins/affiliate-product-showcase/vite.config.js#L131-L135)

  ```js
  static ENTRIES = [
    { name: 'admin', path: 'js/admin.js', required: false },
    { name: 'frontend', path: 'js/frontend.js', required: true },
  ];
  ```

- Verdict: ⚠️
- 2026 best practice: Either fully adopt TypeScript (migrate files to `.ts`/`.tsx` and enable strict checks) or remove TS config and dependencies to avoid confusion. Enterprise projects should prefer consistent use of TS or clearly documented JS-only stacks.
- Blocker?: NO — inconsistent tooling but not blocking runtime.

---

10) CI tests PHP 8.1 (too old) instead of focusing on 8.3+

- Exact current state (file + snippet):

  File: [.github/workflows/ci.yml](.github/workflows/ci.yml#L1-L20)

  ```yaml
  strategy:
    matrix:
      include:
          - os: ubuntu-22.04
            php: '8.1'
          - os: ubuntu-22.04
            php: '8.2'
          - os: ubuntu-22.04
            php: '8.4'
  ```

- Verdict: ⚠️
- 2026 best practice: If the declared minimum is PHP 8.3, CI should test 8.3 + latest stable (8.4/8.5). Remove 8.1 from the matrix to avoid implying support below target.
- Blocker?: NO for feature dev; YES for release confidence if you claim PHP ≥ 8.3.

---

11) Unnecessary/heavy prod deps: `monolog`, `illuminate/collections`, `symfony/polyfill-php80`

- Exact current state (file + snippet):

  File: [wp-content/plugins/affiliate-product-showcase/composer.json](wp-content/plugins/affiliate-product-showcase/composer.json#L20-L40)

  ```json
  "require": {
    "php": "^7.4|^8.0|^8.1|^8.2|^8.3",
    "symfony/polyfill-php80": "^1.27",
    "monolog/monolog": "^3.3",
    "league/container": "^4.2",
    "illuminate/collections": "^9.0",
    // ...
  }
  ```

- Verdict: ⚠️ / 🔍
- 2026 best practice: Avoid heavy runtime dependencies unless strictly required. Move test/dev packages to `require-dev`. Consider smaller alternatives or internal helpers for runtime-only needs to reduce install size and attack surface.
- Blocker?: NO — not an immediate blocker, but a maintenance/footprint concern for production; remediate before final release.

---

Final Summary

- Final realistic grade: B-
- Ready for feature development?: CONDITIONAL — local development possible after minor fixes, but repository is NOT ready for enterprise release or WordPress.org submission until critical issues are addressed.
- Must-fix list (minimum to unblock enterprise readiness):
  - Fix Docker volume mount placeholder (item 1)
  - Align PHP requirements (plugin header + composer.json + platform) to 8.3+ (item 3)
  - Update WordPress `Requires at least:` to modern baseline (6.7+) and confirm compatibility (item 4)
  - Ensure CI matrix includes PHP 8.3 (item 10)
  - Confirm Vite manifest location vs PHP asset loader and make them consistent (item 8)
  - Ensure release/distribution artifacts include compiled `assets/dist/` outputs (manifest + SRI) (item 6)

Additional recommended (not blocking dev but required for release):
  - Commit or otherwise manage `package-lock.json` for deterministic builds (item 5)
  - Ensure release artifacts include `assets/dist/` and SRI/manifest for marketplace distribution (item 6)
  - Adopt or remove TypeScript consistently (item 9)
  - Re-evaluate heavy composer deps and move unused ones to dev (item 11)

---

References: [plan/plan_sync.md](plan/plan_sync.md) was used to confirm intended requirements; primary evidence for the 11 items comes from repository source/config files and the current `assets/dist/` directory contents.
