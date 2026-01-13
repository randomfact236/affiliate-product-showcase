# phpMyAdmin (development)

- **URL:** http://localhost:8080
- **Purpose:** Web UI for managing the local MySQL instance (schemas, queries, exports/imports). For development only — do not expose in production.
- **Login:** Use the same DB credentials you set in `../.env`.
  - `MYSQL_USER` / `MYSQL_PASSWORD` (or)
  - `root` / `MYSQL_ROOT_PASSWORD`
- **Notes:**
  - The service is defined in `docker/docker-compose.override.yml` and binds host port `8080` to container port `80`.
  - You can set `PMA_BLOWFISH_SECRET` in your `.env` for cookie encryption.
  - If you want to restrict access on your machine, bind to `127.0.0.1:8080` in the override compose file instead of all interfaces.

## Security Context

**Audit Finding (Issue #7):** PhpMyAdmin service exposes database management interface in development environment.

**Decision:** This is **ACCEPTABLE** for the following reasons:

1. **Development-only Service:** PhpMyAdmin is only defined in `docker/docker-compose.override.yml`, which is not used in production deployments.

2. **Not a WordPress Plugin Issue:** This is a development tool for the Docker environment, not part of the plugin codebase itself.

3. **Standard Practice:** Local development environments commonly include database management tools for developer convenience.

4. **No Production Exposure:** The service is never deployed to production WordPress environments.

5. **Proper Documentation:** The service is clearly documented as development-only and includes security recommendations.

**Security Best Practices for Development:**
- Keep Docker environment updated
- Use strong database credentials in `.env` file
- Bind to `127.0.0.1:8080` if running on shared development machine
- Never commit `.env` files with real credentials to version control
- Consider removing or disabling phpMyAdmin if not needed
