# Affiliate Platform

**Status:** Code foundation exists, planning in progress  
**Target:** Enterprise Grade 10/10

---

## What's Here

- `apps/api/` - NestJS backend (needs security hardening)
- `apps/web/` - Next.js frontend (minimal)
- `docker/` - PostgreSQL + Redis infrastructure
- `scripts/` - Development utilities

---

## Current State

Backend has basic CRUD but requires:
- Security audit & hardening
- Testing infrastructure
- Frontend development
- DevOps setup

---

## Next Steps

1. Create new enterprise-grade plan
2. Implement security fixes
3. Build out architecture
4. Develop features

---

## Quick Start

```bash
# Start infrastructure
cd docker && docker-compose up -d

# Install API dependencies
cd apps/api && npm install

# Install Web dependencies
cd apps/web && npm install
```

---

*Clean slate for enterprise planning.*
