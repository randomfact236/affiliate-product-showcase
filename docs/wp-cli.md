WP-CLI Helper Commands and Test Entrypoints

This document documents recommended ways to run WP-CLI and test entrypoints for local development using the repository's Docker setup.

Notes:
- The compose service `aps_wpcli` is configured as a lightweight Alpine container and may not have `wp` installed by default. Two recommended approaches are provided below.
- Some containers (e.g., `aps_wordpress`) do not include Composer or PHPUnit by default — see the Test Entrypoints section for recommended workarounds.

## 1 — Using the `wordpress:cli` image (recommended)

This approach uses the official WP-CLI image which has WP-CLI available.

- Bash example (host):

```bash
docker run --rm \
  --network container:aps_wordpress \
  -v "${PWD}:/var/www/html" \
  wordpress:cli wp plugin list
```

- PowerShell example:

```powershell
docker run --rm --network container:aps_wordpress -v ${PWD}:/var/www/html wordpress:cli wp plugin list
```

Notes:
- `--network container:aps_wordpress` makes the `wordpress:cli` container use the same network namespace as the running `aps_wordpress` container so `WORDPRESS_DB_HOST` etc. resolve to the running DB service.
- Adjust `wp` subcommand to run theme/plugin activation, exports, or other maintenance tasks.

## 2 — Using the `aps_wpcli` service (ad-hoc)

The repository provides an `aps_wpcli` service (Alpine) that mounts the project at `/var/www/html`. It is useful as a helper runtime but does not ship WP-CLI by default.

- Interactive shell (useful for ad-hoc work):

```bash
docker compose exec aps_wpcli /bin/sh
# inside container: download wp-cli and run
php -r "copy('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar', 'wp-cli.phar');"
php wp-cli.phar --info
php wp-cli.phar plugin list --path=/var/www/html
```

On Windows PowerShell you can run the same `docker compose exec` command.

If you prefer a permanent helper, consider building a small Dockerfile that extends `alpine` and installs `php` + `wp-cli` and then switching `aps_wpcli` to use that image.

## 3 — Test entrypoints (PHPUnit and integration)

Problem: `aps_wordpress` image used here does not include Composer or PHPUnit by default, so running `vendor/bin/phpunit` inside it will fail until dependencies are installed.

Options:

- CI runner approach (recommended for reproducible runs):
  - In GitHub Actions, run `composer install` on the runner (or in a separate `composer` service), bring up the DB and other services via `docker compose`, then run `phpunit` from the runner against the test bootstrap that points to the running DB.

- Local container approach:
  - Create a dedicated test container image (Dockerfile) that:
    - Extends `wordpress:php8.3-fpm` (or similar),
    - Installs `composer` and dev dependencies,
    - Copies project files (or mounts them),
    - Exposes a `test` entrypoint that runs `vendor/bin/phpunit`.

Example quick local sequence (fast, non-ideal):

```bash
# 1) Install dependencies on host or a temporary container
docker run --rm -v "${PWD}:/app" -w /app composer:2 composer install

# 2) Run tests on host (pointing at DB started by compose)
docker run --rm \
  --network container:aps_wordpress \
  -v "${PWD}:/app" \
  -w /app \
  composer:2 php vendor/bin/phpunit --testsuite=unit
```

Notes:
- Using `composer:2` image above is just an example; ensure your tests' PHP version matches your runtime.
- For CI, prefer letting the runner handle `composer install` and running `phpunit` there or building a reproducible test image.

## 4 — Useful WP-CLI commands

- Activate plugin:

```bash
docker run --rm --network container:aps_wordpress -v "${PWD}:/var/www/html" wordpress:cli wp plugin activate affiliate-product-showcase --path=/var/www/html
```

- Deactivate plugin:

```bash
docker run --rm --network container:aps_wordpress -v "${PWD}:/var/www/html" wordpress:cli wp plugin deactivate affiliate-product-showcase --path=/var/www/html
```

- Reset database (use with caution):

```bash
docker run --rm --network container:aps_wordpress -v "${PWD}:/var/www/html" wordpress:cli wp db reset --yes --path=/var/www/html
```

## 5 — Recommendations

- Add a small `Dockerfile` for `aps_wpcli` that installs `php` and `wp-cli` so the helper container is ready-to-use.
- For tests, either add Composer to the WordPress image used for local testing or maintain a separate `test` image used by CI and local developers.
- Add short scripts in `scripts/` (PowerShell + Bash) that wrap the recommended `docker run` commands for cross-platform convenience.

---

If you want, I can:
- create a `Dockerfile` for an improved `aps_wpcli` image and update `docker/docker-compose.yml` (no push without approval),
- add `scripts/wpcli.ps1` and `scripts/wpcli.sh` wrappers, or
- commit the `docs/wp-cli.md` file and open a PR. Which would you like next?

### Example: Minimal `Dockerfile` for test image

Below is a minimal example `Dockerfile.test` to serve as a reproducible test image that installs Composer and PHPUnit.

```Dockerfile
FROM wordpress:6.7-php8.3-fpm
RUN apt-get update && apt-get install -y git unzip \
  && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && rm composer-setup.php
WORKDIR /var/www/html
COPY . /var/www/html
RUN composer install --no-interaction
ENTRYPOINT ["./vendor/bin/phpunit"]
```

Usage (build & run):

```bash
docker build -t aps-tests -f Dockerfile.test .
docker run --rm --network container:aps_wordpress -v "${PWD}:/var/www/html" aps-tests --testsuite=unit
```

### Example: CI runner sequence (GitHub Actions)

1. Checkout code.
2. Run `composer install` on the runner.
3. Start required services with `docker compose up -d --build` (DB, redis, wordpress, nginx as needed).
4. Run `phpunit` from the runner, pointing bootstrap to the DB host used by services.

This avoids adding dev toolchain into the production WordPress image and keeps CI reproducible.

### scripts wrappers

The `scripts` wrappers below provide cross-platform convenience for common WP-CLI flows.

