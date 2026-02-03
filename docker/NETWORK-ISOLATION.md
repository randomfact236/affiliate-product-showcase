# Docker Network Isolation Implementation

## Overview

This document describes the Docker network isolation implementation for the Affiliate Product Showcase plugin development environment. The configuration ensures that services are isolated from other Docker projects and external networks.

## Changes Implemented

### 1. Unique Network Name (Replaced `app_net` with `aps_network`)

**Files Modified:**
- `docker-compose.yml`
- `docker-compose.override.yml`

**Change:**
```yaml
# Before (generic, conflict-prone)
networks:
  app_net:
    driver: bridge

# After (project-specific)
networks:
  aps_network:
    driver: bridge
    name: aps_network  # Explicit name prevents Docker from prefixing
```

**Benefit:** Other Docker projects using the generic `app_net` network will not conflict with this project.

---

### 2. Localhost-Only Port Binding

**Files Modified:**
- `docker-compose.yml` (nginx service)
- `docker-compose.override.yml` (redis service)

**Change:**
```yaml
# Before (exposed to all interfaces - INSECURE)
ports:
  - "8000:80"
  - "8443:443"

# After (localhost only - SECURE)
ports:
  - "127.0.0.1:8000:80"
  - "127.0.0.1:8443:443"
```

**Benefits:**
- Other Docker projects cannot access WordPress on port 8000
- External machines cannot access development services
- Databases are isolated at the network level

---

### 3. Documentation Updates

**Files Modified:**
- `.env.example` - Added network isolation documentation
- `docker-compose.yml` - Added inline comments explaining isolation
- `docker-compose.override.yml` - Added security comments

---

### 4. Verification Script

**New File:**
- `docker/verify-isolation.sh` - Script to verify isolation is working

---

## Access Control Matrix

| Service | Port | External Access | Other Docker Projects | Localhost |
|---------|------|-----------------|----------------------|-----------|
| WordPress (HTTP) | 8000 | ❌ Blocked | ❌ Blocked | ✅ Allowed |
| WordPress (HTTPS) | 8443 | ❌ Blocked | ❌ Blocked | ✅ Allowed |
| phpMyAdmin | 8080 | ❌ Blocked | ❌ Blocked | ✅ Allowed |
| Redis | 6379 | ❌ Blocked | ❌ Blocked | ✅ Allowed |
| MySQL | 3306 | ❌ Blocked | ❌ Blocked | ❌ Internal only |

---

## How to Verify Isolation

### 1. Check Network Isolation
```bash
cd docker
docker network ls
```

You should see `aps_network` (not `app_net`).

### 2. Check Port Binding
```bash
docker port aps_nginx
```

You should see `127.0.0.1:8000->80/tcp` (not `0.0.0.0:8000`).

### 3. Run Verification Script
```bash
# Linux/Mac:
./docker/verify-isolation.sh

# Windows (PowerShell):
bash ./docker/verify-isolation.sh
```

### 4. Manual Test
```bash
# From your host machine (should SUCCEED):
curl http://127.0.0.1:8000

# From another Docker container (should FAIL):
docker run --rm curlimages/curl http://host.docker.internal:8000
```

---

## Granting Explicit Access (Cross-Project)

If you need another container to access this project's network:

```bash
# Connect another container to this network
docker network connect aps_network <other-container-name>

# Example: Allow project-b's nginx to access project-a's WordPress
docker network connect aps_network project-b_nginx
```

**Note:** This is the "explicit permission" model - access is denied by default and must be explicitly granted.

---

## Migration Guide

If you have existing containers using the old configuration:

```bash
# 1. Stop existing containers
cd docker
docker-compose down

# 2. Remove old network (optional, but recommended)
docker network rm app_net  # If no other projects use it

# 3. Start with new configuration
docker-compose up -d

# 4. Verify isolation
./verify-isolation.sh
```

---

## Troubleshooting

### Issue: "Port already in use"
**Cause:** Another Docker project is using the same port.  
**Solution:** Change the port in `.env`:
```env
NGINX_HTTP_PORT=8001  # Use a different port
```

### Issue: "Cannot access from host"
**Cause:** Port is bound to 127.0.0.1 but you're using a different IP.  
**Solution:** Access via `http://127.0.0.1:8000` or `http://localhost:8000`.

### Issue: "Container cannot reach another container"
**Cause:** Containers are on isolated networks (by design).  
**Solution:** Connect them explicitly:
```bash
docker network connect aps_network <other-container>
```

---

## Security Best Practices

1. **Never remove `127.0.0.1:` prefix** from port bindings in development
2. **Never share the `aps_network`** with other projects unless explicitly needed
3. **Regularly run `./verify-isolation.sh`** to ensure isolation is working
4. **Use strong passwords** even in development (already configured in `.env`)

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     Docker Host                              │
│                                                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │           aps_network (isolated bridge)                │  │
│  │                                                        │  │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐            │  │
│  │  │  aps_db  │  │aps_redis │  │aps_wp    │            │  │
│  │  │  :3306   │  │  :6379   │  │  :9000   │            │  │
│  │  └────┬─────┘  └────┬─────┘  └────┬─────┘            │  │
│  │       │             │             │                   │  │
│  │       └─────────────┴─────────────┘                   │  │
│  │                     │                                  │  │
│  │              ┌──────┴──────┐                          │  │
│  │              │ aps_nginx   │                          │  │
│  │              │  :80 :443   │                          │  │
│  │              └──────┬──────┘                          │  │
│  │                     │                                  │  │
│  │         127.0.0.1:8000:80                             │  │
│  └─────────────────────┼─────────────────────────────────┘  │
│                        │                                     │
│  ┌─────────────────────┼─────────────────────────────────┐  │
│  │                     ▼                                  │  │
│  │            Localhost Only (:8000)                      │  │
│  │         (Other projects CANNOT access)                 │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  Other Project Networks (isolated):                          │
│  ┌─────────────────┐  ┌─────────────────┐                   │
│  │  project_b_net  │  │  project_c_net  │                   │
│  │  (cannot reach  │  │  (cannot reach  │                   │
│  │   aps_network)  │  │   aps_network)  │                   │
│  └─────────────────┘  └─────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

---

## Summary

✅ **Network Isolation:** Uses unique `aps_network` instead of generic `app_net`  
✅ **Port Security:** All ports bound to `127.0.0.1` (localhost only)  
✅ **Default Deny:** Other Docker projects cannot access services  
✅ **Explicit Permission:** Cross-project access requires manual `docker network connect`  

This configuration ensures your WordPress development environment is isolated from other projects while remaining fully functional for local development.
