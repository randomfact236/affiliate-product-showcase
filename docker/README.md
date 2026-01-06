# Docker: Redis + PHP Redis extension

This document explains the Redis setup for local development and how the PHP `redis` extension is provided to the WordPress PHP-FPM image.

Summary of changes made:
- A `redis` service was added to `docker/docker-compose.yml` (image `redis:7-alpine`) with a healthcheck.
- The `wordpress` service was changed to build from `docker/php-fpm/Dockerfile` (image `aps_wordpress:6.7-php8.3-fpm`) which installs the PHP `redis` extension via `pecl`.

How the WordPress PHP-FPM image is built
- Build the image (from the top-level `docker/` directory):

```bash
docker compose build wordpress
```

- Recreate the container:

```bash
docker compose up -d --no-deps --force-recreate wordpress
```

- Verify the extension is loaded inside the running container:

```bash
docker compose exec wordpress php -m | grep redis
# or
docker compose exec wordpress php -r "var_dump(extension_loaded('redis'));"
```

WP Redis configuration
- Recommended: install the official "Redis Object Cache" plugin (or use a drop-in `wp-content/object-cache.php`).
- If using the plugin it will automatically connect to `redis:6379` when configured with environment variables below.

Environment variables (examples to add to your project `.env`):

```
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DB=0
REDIS_PASSWORD=
WP_REDIS_PREFIX=aps_
```

object-cache.php drop-in note
- If you prefer a drop-in, place an `object-cache.php` file at `wp-content/object-cache.php` (the typical drop-in from the Redis plugin works with the `phpredis` extension). Do not commit production secrets into source control.

Redis persistence and development volume (optional)
- The `redis` service in `docker/docker-compose.yml` currently runs ephemeral by default. To enable persistence during development, add a volume and a simple config override. See `docker/docker-compose.override.yml` for an example that mounts `redis_data` to `/data` and enables default RDB persistence.

Example `docker/docker-compose.override.yml` (local development)
- See `docker/docker-compose.override.yml` shipped alongside this README for a ready-to-use example.

Notes and troubleshooting
- If `php -m` does not list `redis`, rebuild the image and recreate the container.
- To test connectivity from PHP you can run `docker compose exec wordpress sh -c "php -r \"var_dump(fsockopen('redis', 6379));\""` or use `redis-cli` from inside the container.
- To disable Redis for a particular developer, use an override file that removes the `redis` service or set `REDIS_HOST` to an empty value.

If you'd like, I can add a sample `wp-content/object-cache.php` drop-in that delegates to the Redis plugin when present — tell me if you want that scaffolded.
 
MailHog (email testing)
 - The compose stack includes a `mailhog` service (image `mailhog/mailhog`) exposing SMTP on `1025` and a web UI on `8025`.
 - To route WordPress email to MailHog in development, set the SMTP host/port in `wp-config.php` or use a mail plugin that supports custom SMTP settings:

```
SMTP_HOST=mailhog
SMTP_PORT=1025
```

 - Open the MailHog web UI in your browser at `http://localhost:8025` to view captured messages.
 - The `mailhog` service in `docker/docker-compose.yml` already includes a simple healthcheck; exposing port 8025 to the host is optional but convenient for development.

Security note: Do not expose MailHog ports in production environments.
