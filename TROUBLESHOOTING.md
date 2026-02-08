# Troubleshooting Guide

## "This site can't be reached" Error (ERR_CONNECTION_REFUSED)

### 🔍 Quick Diagnosis

This error means the server is not running on the expected port.

### ✅ Step-by-Step Fix

#### Step 1: Check What's Running

```bash
# Check if Docker containers are running
docker ps --filter "name=affiliate-website"

# Should show:
# - affiliate-website-db (PostgreSQL)
# - affiliate-website-cache (Redis)
```

**If containers are NOT running:**
```bash
# Start infrastructure
pnpm infra:up
# or
docker-compose -p affiliate-website -f docker/docker-compose.yml up -d
```

#### Step 2: Check Port Status

```bash
# Check all ports
pnpm ports:check
# or
powershell -ExecutionPolicy Bypass -File scripts/port-check.ps1
```

Expected output:
```
Port 3002 : ✅ Available  (should show this if web not running)
Port 3003 : ✅ Available  (should show this if API not running)
Port 5433 : 🔒 IN USE     (PostgreSQL - Docker)
Port 6380 : 🔒 IN USE     (Redis - Docker)
```

#### Step 3: Start the Dev Servers

You need **TWO terminal windows** running simultaneously:

**Terminal 1 - Start API Server:**
```bash
cd apps/api
npm run dev
```

You should see:
```
🚀 API Server running on http://localhost:3003
```

**Terminal 2 - Start Web Server:**
```bash
cd apps/web
npm run dev
```

You should see:
```
ready started server on 0.0.0.0:3002, url: http://localhost:3002
```

#### Step 4: Verify in Browser

- Frontend: http://localhost:3002
- API: http://localhost:3003/api/v1/health

---

## Common Issues & Solutions

### Issue 1: "Port already in use"

**Error:**
```
Port 3002 is already in use
```

**Solution:**
```bash
# Find what's using the port
netstat -ano | findstr :3002

# Kill the process (replace PID with actual number)
taskkill /PID <PID> /F

# Or use different port (see PORT-CONFIGURATION.md)
```

### Issue 2: "Cannot find module"

**Error:**
```
Error: Cannot find module '@nestjs/core'
```

**Solution:**
```bash
# Install API dependencies
cd apps/api
npm install

# Install Web dependencies
cd apps/web
npm install
```

### Issue 3: Database connection failed

**Error:**
```
Database connection error
```

**Solution:**
```bash
# Check if database is running
docker ps --filter "name=affiliate-website-db"

# If not running, start it
pnpm infra:up

# Check database URL in apps/api/.env
# Should be: postgresql://postgres:postgres@localhost:5433/affiliate_platform
```

### Issue 4: Prisma Client not generated

**Error:**
```
Cannot find module '@prisma/client'
```

**Solution:**
```bash
cd apps/api
npx prisma generate
```

---

## Quick Start Checklist

Use this checklist every time you start development:

- [ ] Docker Desktop is running
- [ ] Containers are up: `pnpm infra:up`
- [ ] Database is migrated: `cd apps/api && npx prisma migrate dev`
- [ ] API server running: `cd apps/api && npm run dev`
- [ ] Web server running: `cd apps/web && npm run dev`
- [ ] Check URLs:
  - [ ] http://localhost:3002 (Frontend)
  - [ ] http://localhost:3003/api/v1/health (API)

---

## Windows-Specific Issues

### PowerShell Execution Policy

If you get execution policy errors:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Port already used by WSL

If ports show as used by `wslrelay`:
```bash
# This is normal - WSL2 uses these ports
# Just use different ports (3004/3005) if needed
```

---

## Still Not Working?

### Nuclear Option (Reset Everything)

```bash
# 1. Stop everything
docker-compose -p affiliate-website -f docker/docker-compose.yml down
# Kill any node processes
taskkill /F /IM node.exe 2>$null

# 2. Remove port locks
Remove-Item .port-locks/*.pid -ErrorAction SilentlyContinue

# 3. Restart infrastructure
pnpm infra:up

# 4. Reinstall dependencies
cd apps/api
npm install
npx prisma generate
npx prisma migrate dev

cd apps/web
npm install

# 5. Start servers (in separate terminals)
# Terminal 1: cd apps/api && npm run dev
# Terminal 2: cd apps/web && npm run dev
```

---

## Getting Help

If none of these solutions work:

1. Run diagnostics:
   ```bash
   pnpm ports:check
   docker ps
   docker logs affiliate-website-db
   ```

2. Check the logs in each terminal for specific errors

3. Ask for help with the specific error message
