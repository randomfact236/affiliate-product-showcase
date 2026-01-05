**Backup & Restore (DB and wp-content)**

Overview:
- This document describes safe, repeatable steps to back up and restore the WordPress database and `wp-content` files for local development using the project's Docker Compose setup.

Principles:
- Never store credentials or unencrypted backups in the repository. Keep `.env` and local key material in `.gitignore`.
- Prefer short-lived credentials, encrypted storage (OS keychain, Vault, GPG) for backups containing secrets.
- Test restore procedures periodically on an isolated environment.

Prerequisites:
- Docker Compose running with services named by this repo (e.g. `aps_db` for MySQL, `aps_wordpress` for WP files).
- A local `.env` with `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE` defined.

Simple backup (recommended):
- Create a timestamped directory for backups: `backups/YYYYMMDD-HHMMSS/`
- Database (via mysql container):

```bash
docker exec aps_db sh -c 'exec mysqldump --single-transaction -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}"' > backups/db-${MYSQL_DATABASE}-$(date -u +%Y%m%dT%H%M%SZ).sql
```

- WordPress files (`wp-content`):

```bash
tar -C ./ -czf backups/wp-content-$(date -u +%Y%m%dT%H%M%SZ).tar.gz wp-content
```

Restore (database):

```bash
cat backups/db-<name>.sql | docker exec -i aps_db sh -c 'mysql -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}"'
```

Restore (`wp-content`):

```bash
tar -C ./ -xzf backups/wp-content-<ts>.tar.gz
```

Credentials handling recommendations:
- Keep `.env` out of the repo (already in `.gitignore`). Use `docker/.env` local placeholders only.
- For CI or shared environments, use encrypted secrets (GitHub Actions Secrets, HashiCorp Vault) and do not echo secrets in logs.
- When running scripts, prefer reading passwords from an encrypted file or environment variable rather than embedding them on the command line when possible.

Retention & rotation:
- Keep a small number of recent backups (e.g. last 7 daily snapshots) and prune older ones.
- Encrypt backups at rest with GPG when they contain production data.

Testing:
- Regularly restore backups into an isolated environment to verify integrity.

Notes:
- These instructions are for local dev workflows. For production systems, use managed backup solutions and follow your infra team's security policies.
