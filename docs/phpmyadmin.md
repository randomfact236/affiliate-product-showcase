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
