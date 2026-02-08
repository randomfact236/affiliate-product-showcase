# Getting Started Guide

> First-time setup for new developers joining the Affiliate Website project

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Quick Start](#quick-start-5-minutes)
3. [Manual Setup](#manual-setup-if-script-fails)
   - Step 1: Install Dependencies
   - Step 2: Set Up Environment Variables
   - Step 3: Start Infrastructure
   - Step 4: Run Database Migrations
   - Step 5: Configure Port Management
   - Step 6: Start Development Servers
4. [Verify Installation](#verify-installation)
5. [Project Structure](#project-structure-overview)
6. [Common Commands](#common-commands)
7. [Development Workflow](#development-workflow)
8. [Troubleshooting](#troubleshooting)
9. [Port Security Best Practices](#port-security-best-practices)
10. [Next Steps](#next-steps)

## Prerequisites

Before you begin, ensure you have the following installed:

- [ ] Node.js 20+ (`node --version`)
- [ ] pnpm 8+ (`pnpm --version`)
- [ ] Docker & Docker Compose (`docker --version`)
- [ ] Git (`git --version`)
- [ ] VS Code (recommended)
  - Extensions: ESLint, Prettier, Prisma, Tailwind CSS IntelliSense

## Quick Start (5 minutes)

```bash
# 1. Clone the repository
git clone <repository-url>
cd affiliate-platform

# 2. Start Docker infrastructure
pnpm infra:up

# 3. Start API server (in new terminal)
cd apps/api && npm run dev

# 4. Start Web server (in another terminal)
cd apps/web && npm run dev

# 5. Open browser
# Frontend: http://localhost:3002
# API: http://localhost:3003/api/v1/health
```

**Important**: You need **THREE** terminal windows:
1. Docker infrastructure (runs continuously)
2. API server (`apps/api`)
3. Web server (`apps/web`)

## Manual Setup (if script fails)

### Step 1: Install Dependencies

```bash
pnpm install
```

### Step 2: Set Up Environment Variables

```bash
# Copy example env files
cp apps/api/.env.example apps/api/.env
cp apps/web/.env.example apps/web/.env

# Edit the files with your values
# Ask team lead for any required secrets
```

### Step 3: Start Infrastructure

The `docker/docker-compose.yml` file is included in the repository. It defines:
- **PostgreSQL** (port 5433) - Main database
- **Redis** (port 6380) - Cache and session store

> **Note:** We use ports 5433 and 6380 (instead of 5432/6379) to avoid conflicts with existing local services.

Start the infrastructure with Docker Compose:

```bash
docker-compose -p affiliate-website -f docker/docker-compose.yml up -d
```

**What this does:**
- Creates containers named `affiliate-website-db` and `affiliate-website-cache`
- Creates volumes for persistent data
- Sets up a dedicated Docker network

**Tip:** The `-p affiliate-website` flag sets the project name, which prefixes all containers, networks, and volumes.

### Step 4: Run Database Migrations

```bash
cd apps/api
npx prisma migrate dev
npx prisma db seed
```

### Step 5: Configure Port Management & Security

Create a port lock configuration to prevent port conflicts and unauthorized access:

```bash
# Create port lock directory
mkdir -p .port-locks

# Set restrictive permissions (only current user can access)
chmod 700 .port-locks

# On Windows (PowerShell - Administrator):
# New-Item -ItemType Directory -Path .port-locks -Force
# icacls .port-locks /inheritance:r /grant:r "$($env:USERNAME):(OI)(CI)F"
```

### Step 6: Start Development Servers (with Port Protection)

Use the provided scripts that ensure ports are locked and properly released:

```bash
# Terminal 1 - Backend (Port 3003)
pnpm dev:api:secure

# Terminal 2 - Frontend (Port 3002)
pnpm dev:web:secure

# Or start both with port protection
pnpm dev:secure
```

**Manual start (if scripts unavailable):**
```bash
# Terminal 1 - Backend with port file lock
echo $$ > .port-locks/api.pid
cd apps/api
pnpm dev &
API_PID=$!
echo $API_PID >> .port-locks/api.pid
trap "kill $API_PID 2>/dev/null; rm -f .port-locks/api.pid" EXIT
wait $API_PID

# Terminal 2 - Frontend with port file lock
echo $$ > .port-locks/web.pid
cd apps/web
pnpm dev &
WEB_PID=$!
echo $WEB_PID >> .port-locks/web.pid
trap "kill $WEB_PID 2>/dev/null; rm -f .port-locks/web.pid" EXIT
wait $WEB_PID
```

## Verify Installation

### 1. Check Port Status

```bash
pnpm ports:check
```

Expected output:
```
🔍 Checking port status for Affiliate Website...

Port 3002 : ✅ Available  (Web - Next.js)
Port 3003 : ✅ Available  (API - NestJS)
Port 5433 : 🔒 IN USE     (PostgreSQL)
Port 6380 : 🔒 IN USE     (Redis)
Port 9200 : ✅ Available  (Elasticsearch - optional)
```

### 2. Open your browser and check:

- [ ] Frontend: http://localhost:3002
- [ ] API: http://localhost:3003/api/v1/health
- [ ] API Docs: http://localhost:3003/api/docs

### 3. Verify Port Locks

Check that lock files were created:
```bash
ls -la .port-locks/
# Should show: api.pid, web.pid
```

## Project Structure Overview

```
affiliate-platform/
├── apps/
│   ├── api/              # NestJS backend (Port 3003)
│   │   ├── src/
│   │   ├── prisma/       # Database schema
│   │   └── package.json
│   └── web/              # Next.js frontend (Port 3002)
│       ├── src/
│       ├── public/
│       └── package.json
├── packages/
│   ├── shared-types/     # Shared TypeScript types
│   ├── eslint-config/
│   └── typescript-config/
├── docker/                        # Infrastructure orchestration
│   ├── docker-compose.yml         # PostgreSQL (5433), Redis (6380)
│   ├── init-db.sql                # Database initialization
│   └── nginx/                     # Nginx configuration
├── scripts/              # Utility scripts
│   ├── dev-secure.sh     # Secure development startup
│   └── port-check.sh     # Port availability checker
├── .port-locks/          # Port lock files (auto-created)
├── package.json          # Root workspace config
└── .env.ports            # Port configuration
```

## Port Management Configuration

### Default Ports

| Service | Port | Purpose |
|---------|------|---------|
| Frontend (Next.js) | 3002 | Web application |
| Backend (NestJS) | 3003 | API server |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache/Queue |
| Elasticsearch | 9200 | Search engine |

### Environment-based Port Configuration

Create `.env.ports` in the root directory:

```bash
# .env.ports - Port configuration (DO NOT COMMIT)
WEB_PORT=3002
API_PORT=3003
DB_PORT=5432
REDIS_PORT=6379
ES_PORT=9200

# Port lock timeout (seconds)
PORT_LOCK_TIMEOUT=30
```

### Secure Development Scripts

Add to root `package.json`:

```json
{
  "scripts": {
    "dev:secure": "concurrently \"pnpm:dev:api:secure\" \"pnpm:dev:web:secure\"",
    "dev:api:secure": "./scripts/dev-secure.sh api 3003",
    "dev:web:secure": "./scripts/dev-secure.sh web 3002",
    "ports:check": "./scripts/port-check.sh",
    "ports:release": "rm -rf .port-locks/*"
  }
}
```

### Port Security Scripts

Create `scripts/dev-secure.sh`:

```bash
#!/bin/bash
# scripts/dev-secure.sh - Secure development startup with port locking

APP=$1        # 'api' or 'web'
PORT=$2       # Port number
LOCK_DIR=".port-locks"
LOCK_FILE="$LOCK_DIR/$APP.pid"

echo "🔒 Starting $APP on port $PORT with port protection..."

# Create lock directory if not exists
mkdir -p "$LOCK_DIR"
chmod 700 "$LOCK_DIR"

# Check if port is already locked by another process
if [ -f "$LOCK_FILE" ]; then
    OLD_PID=$(cat "$LOCK_FILE" | head -1)
    if ps -p "$OLD_PID" > /dev/null 2>&1; then
        echo "❌ Port $PORT is locked by process $OLD_PID"
        echo "   Run: pnpm ports:release  # to force release"
        exit 1
    else
        echo "⚠️  Stale lock file found, removing..."
        rm -f "$LOCK_FILE"
    fi
fi

# Check if port is in use by another process
if lsof -Pi :"$PORT" -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "❌ Port $PORT is already in use by another process"
    lsof -Pi :"$PORT" -sTCP:LISTEN
    exit 1
fi

# Create lock file with current PID and parent shell info
echo $$ > "$LOCK_FILE"
echo "$(date): Started by $(whoami) in $(pwd)" >> "$LOCK_FILE"

# Function to cleanup on exit
cleanup() {
    echo "🔓 Releasing port $PORT lock..."
    rm -f "$LOCK_FILE"
    exit
}

# Set trap to cleanup on exit
trap cleanup EXIT INT TERM

# Start the development server
echo "🚀 Starting $APP development server..."
if [ "$APP" = "api" ]; then
    cd apps/api && pnpm dev
elif [ "$APP" = "web" ]; then
    cd apps/web && pnpm dev
fi
```

Create `scripts/port-check.sh`:

```bash
#!/bin/bash
# scripts/port-check.sh - Check port availability and locks

LOCK_DIR=".port-locks"
PORTS="3002 3003 5433 6380 9200"

echo "🔍 Checking port status..."
echo ""

for PORT in $PORTS; do
    printf "Port %-5s: " "$PORT"
    
    # Check if port is in use
    if lsof -Pi :"$PORT" -sTCP:LISTEN -t >/dev/null 2>&1; then
        PID=$(lsof -Pi :"$PORT" -sTCP:LISTEN -t | head -1)
        PROCESS=$(ps -p "$PID" -o comm= 2>/dev/null || echo "unknown")
        
        # Check if we have a lock file for this port
        LOCK_FOUND=""
        for LOCK in "$LOCK_DIR"/*.pid; do
            if [ -f "$LOCK" ]; then
                LOCK_PID=$(cat "$LOCK" | head -1)
                if [ "$LOCK_PID" = "$PID" ]; then
                    LOCK_FOUND=$(basename "$LOCK" .pid)
                    break
                fi
            fi
        done
        
        if [ -n "$LOCK_FOUND" ]; then
            echo "🔒 LOCKED by $LOCK_FOUND (PID: $PID)"
        else
            echo "⚠️  IN USE by $PROCESS (PID: $PID) - NOT MANAGED"
        fi
    else
        echo "✅ Available"
    fi
done

echo ""
echo "Lock files in $LOCK_DIR:"
ls -la "$LOCK_DIR"/*.pid 2>/dev/null || echo "  (none)"
```

### Windows PowerShell Scripts

Create `scripts/dev-secure.ps1`:

```powershell
# scripts/dev-secure.ps1 - Windows version
param(
    [Parameter(Mandatory=$true)]
    [string]$App,
    
    [Parameter(Mandatory=$true)]
    [int]$Port
)

$LockDir = ".port-locks"
$LockFile = "$LockDir\$App.pid"

Write-Host "🔒 Starting $App on port $Port with port protection..."

# Create lock directory
New-Item -ItemType Directory -Path $LockDir -Force | Out-Null

# Check for existing lock
if (Test-Path $LockFile) {
    $OldPid = Get-Content $LockFile | Select-Object -First 1
    if (Get-Process -Id $OldPid -ErrorAction SilentlyContinue) {
        Write-Host "❌ Port $Port is locked by process $OldPid"
        exit 1
    } else {
        Remove-Item $LockFile -Force
    }
}

# Check if port is in use
$Connection = Test-NetConnection -ComputerName localhost -Port $Port -WarningAction SilentlyContinue
if ($Connection.TcpTestSucceeded) {
    Write-Host "❌ Port $Port is already in use"
    netstat -ano | findstr ":$Port"
    exit 1
}

# Create lock file
$PID | Set-Content $LockFile
"$(Get-Date): Started by $env:USERNAME" | Add-Content $LockFile

# Cleanup function
function Cleanup {
    Write-Host "`n🔓 Releasing port $Port lock..."
    Remove-Item $LockFile -Force -ErrorAction SilentlyContinue
}

# Register cleanup
trap { Cleanup; exit }

# Start dev server
try {
    if ($App -eq "api") {
        Set-Location apps/api
    } else {
        Set-Location apps/web
    }
    pnpm dev
} finally {
    Cleanup
}
```

## Common Commands

```bash
# Development (with port protection)
pnpm dev:secure       # Start all services with port locks
pnpm dev:api:secure   # Start backend only with port lock
pnpm dev:web:secure   # Start frontend only with port lock

# Development (standard - without port protection)
pnpm dev              # Start all services
pnpm dev:api          # Start backend only
pnpm dev:web          # Start frontend only

# Port management
pnpm ports:check      # Check port status and locks
pnpm ports:release    # Force release all port locks
pnpm ports:kill       # Kill processes using project ports

# Code Quality
pnpm lint             # Run ESLint
pnpm lint:fix         # Fix ESLint errors
pnpm format           # Run Prettier
pnpm typecheck        # Run TypeScript checks

# Testing
pnpm test             # Run all tests
pnpm test:api         # Run backend tests
pnpm test:web         # Run frontend tests
pnpm test:e2e         # Run E2E tests

# Database
cd apps/api
npx prisma migrate dev      # Create migration
npx prisma migrate deploy   # Apply migrations
npx prisma db seed          # Seed data
npx prisma studio           # Open Prisma Studio
npx prisma generate         # Generate client

# Build
pnpm build            # Build all apps
pnpm build:api        # Build backend
pnpm build:web        # Build frontend
```

## Development Workflow

1. **Create a branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make changes**
   - Follow code style guidelines
   - Write tests for new functionality
   - Update documentation

3. **Run checks locally**
   ```bash
   pnpm lint
   pnpm typecheck
   pnpm test
   ```

4. **Commit changes**
   ```bash
   git add .
   git commit -m "feat: your descriptive message"
   ```
   Follow [Conventional Commits](https://www.conventionalcommits.org/)

5. **Push and create PR**
   ```bash
   git push origin feature/your-feature-name
   ```
   Then create a PR using the template

## Troubleshooting

### Port Issues

**Problem:** "Port XXXX is locked by process YYYY"  
**Solution:**
```bash
# Check which process is using the port
pnpm ports:check

# If the process is dead but lock file remains:
pnpm ports:release

# If another terminal has the port, find and kill it:
# macOS/Linux:
lsof -ti:3002 | xargs kill -9  # For port 3002 (Web)
lsof -ti:3003 | xargs kill -9  # For port 3003 (API)

# Windows (PowerShell - Admin):
Get-Process -Id (Get-NetTCPConnection -LocalPort 3002).OwningProcess | Stop-Process -Force
```

**Problem:** "Port XXXX is already in use by another process"  
**Solution:**
```bash
# Find the process using the port
# macOS/Linux:
lsof -i :3002

# Windows:
netstat -ano | findstr :3002

# Kill the process (replace PID with actual process ID)
kill -9 <PID>          # macOS/Linux
taskkill /PID <PID> /F # Windows
```

**Problem:** Port locks not releasing on terminal close  
**Solution:**
```bash
# Force clean all locks
rm -rf .port-locks/*

# Kill any remaining node processes on project ports
kill $(lsof -ti:3002,3003) 2>/dev/null
```

### Docker Issues

**Problem:** Ports already in use  
**Solution:**
```bash
docker-compose -p affiliate-website -f docker/docker-compose.yml down
# Or kill processes on ports 5432, 6379, 9200
```

**Problem:** Database connection refused  
**Solution:**
```bash
# Check if containers are running
docker-compose -p affiliate-website -f docker/docker-compose.yml ps

# Restart containers
docker-compose -p affiliate-website -f docker/docker-compose.yml restart
```

### Node.js Issues

**Problem:** Module not found errors  
**Solution:**
```bash
pnpm clean
pnpm install
```

**Problem:** Type errors after pulling changes  
**Solution:**
```bash
pnpm build
# or
pnpm typecheck
```

### Database Issues

**Problem:** Migration conflicts  
**Solution:**
```bash
cd apps/api
npx prisma migrate reset
# Warning: This will delete all data
```

### Permission Issues (Port Locks)

**Problem:** Cannot create `.port-locks` directory  
**Solution:**
```bash
# Create with correct permissions
mkdir -p .port-locks
chmod 700 .port-locks

# On Windows (PowerShell - Admin):
New-Item -ItemType Directory -Path .port-locks -Force
icacls .port-locks /inheritance:r /grant:r "$($env:USERNAME):(OI)(CI)F"
```

## Resources

- [Architecture Overview](../ENTERPRISE-ARCHITECTURE.md)
- [Phase 1: Infrastructure](./phase-01-infrastructure-foundation.md)
- [Development Standards](../ENTERPRISE-ARCHITECTURE.md#8-development-standards--code-quality)

## Getting Help

1. Check this guide and troubleshooting section
2. Search existing issues/discussions
3. Ask in team Slack channel: #dev-help
4. Tag @tech-lead for architectural questions

## Port Security Best Practices

### 1. Always Use Secure Scripts

```bash
# ✅ Good - Uses port protection
pnpm dev:api:secure
pnpm dev:web:secure

# ⚠️  Avoid - No port protection (use only if secure scripts fail)
pnpm dev:api
pnpm dev:web
```

### 2. Never Share Terminal Sessions

Each developer should run their own terminal session. Port locks are user-specific.

### 3. Clean Up on Exit

Always use `Ctrl+C` to stop servers gracefully. This ensures port locks are released.

If terminal crashes:
```bash
pnpm ports:release
```

### 4. Git Ignore Port Locks

Ensure `.port-locks/` is in `.gitignore`:

```gitignore
# Port lock files
.port-locks/
*.pid

# Environment port config
.env.ports
```

### 5. CI/CD Considerations

Port protection is disabled in CI environments. Set in CI config:

```yaml
# .github/workflows/ci.yml
env:
  PORT_PROTECTION: disabled
  WEB_PORT: 3002
  API_PORT: 3003
```

### 6. Multi-Developer Environment

If working on a shared server, use different ports per developer:

```bash
# .env.ports (per developer)
WEB_PORT=3002    # Developer 2
API_PORT=3003    # Developer 2
```

## Next Steps

After setup, read:
1. [Phase 1 Implementation Plan](./phase-01-infrastructure-foundation.md) (if starting)
2. Current phase document (if joining ongoing work)
3. [Project Tracker](./project-tracker.md) for current status

Welcome to the team! 🚀

