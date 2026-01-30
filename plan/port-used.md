# Ports Used by Affiliate Product Showcase Plugin

**Date:** 2026-01-29

---

## Quick Reference

| Port | Status | Service | Host Access |
|-------|---------|----------|--------------|
| 8000 | ✅ IN USE | HTTP (Nginx) | Yes |
| 8443 | ✅ IN USE | HTTPS (Nginx) | Yes |
| 3306 | ✅ IN USE | MySQL | No (internal) |
| 6379 | ⚠️ INTERNAL | Redis | No (internal) |
| 8025 | ⚠️ INTERNAL | Mailhog Web UI | No (internal) |
| 1025 | ✅ IN USE | Mailhog SMTP | No (internal) |
| 80 | ✅ IN USE | Nginx Internal | No (internal) |
| 443 | ✅ IN USE | Nginx Internal | No (internal) |
| 8080 | ❌ NOT USED | phpMyAdmin | No (not exposed) |

---

## Ports Available for Other Applications

You can safely use these ports on your host machine:

- **6379** - Redis is internal-only
- **8025** - Mailhog web UI is internal-only
- **1025** - Mailhog SMTP is internal-only
- **8080** - phpMyAdmin port is defined but not exposed

---

## Configuration Files

- Docker Compose: [`docker/docker-compose.yml`](docker/docker-compose.yml)
- Environment Variables: [`.env`](.env)
