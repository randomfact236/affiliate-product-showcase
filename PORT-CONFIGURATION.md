# Port Configuration Guide

## Overview

To avoid conflicts with other projects that commonly use ports 3000/3001, this project uses **alternative ports**.

## Default Ports

| Service | Port | Purpose | Notes |
|---------|------|---------|-------|
| **Web (Next.js)** | **3002** | Frontend application | Changed from 3000 |
| **API (NestJS)** | **3003** | Backend API server | Changed from 3001 |
| **PostgreSQL** | **5433** | Database | Changed from 5432 |
| **Redis** | **6380** | Cache/Session store | Changed from 6379 |
| **Elasticsearch** | 9200 | Search engine | Optional (Phase 4) |

## Why These Changes?

### Port 3000 Conflicts
Port 3000 is used by:
- Default Next.js development server
- Many React/Vue/Angular tutorials
- Other local development projects

### Port 3001 Conflicts
Port 3001 is commonly used by:
- Express.js examples
- Other NestJS projects
- Various API development tools

### Port 5432/6379 Conflicts
These are default ports that may be used by:
- Local PostgreSQL/Redis installations
- Other Docker projects
- Database management tools

## URLs

After starting the development servers:

- **Frontend**: http://localhost:3002
- **API Health**: http://localhost:3003/api/v1/health
- **API Docs**: http://localhost:3003/api/docs (when configured)
- **Prisma Studio**: http://localhost:5555 (when running)

## Changing Ports (If Still Conflicting)

If you still have conflicts, you can change the ports in these files:

### 1. Web App (`apps/web/package.json`)
```json
{
  "scripts": {
    "dev": "next dev -p 3004"
  }
}
```

### 2. API App (`apps/api/package.json`)
```json
{
  "scripts": {
    "dev": "nest start --watch --port 3005"
  }
}
```

### 3. Environment Files
Update `apps/api/.env`:
```bash
API_PORT=3005
```

Update `apps/web/.env.local`:
```bash
NEXT_PUBLIC_API_URL=http://localhost:3005
```

### 4. Docker Compose (if needed)
Update `docker/docker-compose.yml`:
```yaml
services:
  postgres:
    ports:
      - "5434:5432"  # Change from 5433
  
  redis:
    ports:
      - "6381:6379"  # Change from 6380
```

## Environment Variables

Create `.env.ports` in the root (don't commit this):

```bash
# Custom ports for this developer
WEB_PORT=3002
API_PORT=3003
DB_PORT=5433
REDIS_PORT=6380
```

## Checking Port Usage

```bash
# Check all project ports
pnpm ports:check

# Check specific port (Windows)
netstat -ano | findstr :3002

# Check specific port (macOS/Linux)
lsof -i :3002
```

## Troubleshooting

### "Port already in use" error

1. Find the process using the port:
   ```bash
   # Windows
   netstat -ano | findstr :3002
   
   # macOS/Linux
   lsof -ti:3002
   ```

2. Either:
   - Kill the process using the port
   - Or change this project's port to something else

### Docker port conflicts

If Docker says ports are already allocated:

```bash
# Stop all containers
pnpm infra:down

# Check what's using the port
docker ps

# Or use different ports in docker-compose.yml
```

## Team Standards

To ensure consistency across the team:

1. **Default ports** (3002/3003) are committed to the repo
2. **Individual developers** can use custom ports via `.env.ports` if needed
3. **Document any changes** in your local environment
4. **Never commit** your custom port changes to the main branch
