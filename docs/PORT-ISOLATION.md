# 🔒 Port Protection System

## Overview

This project uses **STANDARD ports** that are protected and reserved exclusively:

### Active Ports (Currently Used)

| Port | Service | Purpose |
|------|---------|---------|
| **3000** | Next.js Web | Frontend application (Active) |
| **3003** | API Server | REST API backend (Active) |
| **5433** | PostgreSQL | Primary database |
| **6380** | Redis | Cache & sessions |
| **9000** | MinIO API | S3-compatible storage |
| **9001** | MinIO Console | Storage management UI |
| **5672** | RabbitMQ AMQP | Message queue protocol |
| **15672** | RabbitMQ Mgmt | Queue management UI |

### Legacy Ports (Reserved for Compatibility)

| Port | Service | Reason |
|------|---------|--------|
| **3001** | API Server (Legacy) | Old NestJS API port - Reserved |
| **3002** | Web Server (Legacy) | Old Next.js port - Reserved |

## What This Means

✅ **These 10 ports are BOOKED for this project ONLY**  
❌ **No other project can use these ports**  
🧱 **Windows Firewall creates a WALL around these ports**  
🔒 **Other applications trying to use these ports will be BLOCKED**

## Quick Start

### 1. Setup Port Protection (Run as Administrator)

```powershell
.\scripts\port-manager.ps1 -Action setup
```

This will:
- Check if any other application is using these ports
- Create Windows Firewall rules to isolate these ports
- Create lock files to track port ownership
- Kill conflicting processes if `-Force` flag is used

### 2. Start Services

```batch
START-PROJECT.bat
```

Or manually:

```powershell
# Terminal 1: API Server on port 3003
cd apps/api
$env:API_PORT=3003
node simple-server.js

# Terminal 2: Web Server on port 3000
cd apps/web
$env:PORT=3000
npm run dev
```

### 3. Access Application

- **Web**: http://localhost:3000
- **API**: http://localhost:3003
- **API Health**: http://localhost:3003/api/v1/health

### 4. Release Ports (When Done)

```powershell
.\scripts\port-manager.ps1 -Action release
```

## Port Manager Commands

```powershell
# Check port status
.\scripts\port-manager.ps1 -Action check

# Verify configuration
.\scripts\port-manager.ps1 -Action verify

# Reserve ports (create lock files)
.\scripts\port-manager.ps1 -Action reserve

# Release ports (remove lock files)
.\scripts\port-manager.ps1 -Action release

# Setup firewall rules only
.\scripts\port-manager.ps1 -Action firewall

# Full setup (recommended) - Run as Administrator
.\scripts\port-manager.ps1 -Action setup

# Force setup (kill conflicting processes) - Run as Administrator
.\scripts\port-manager.ps1 -Action setup -Force
```

## Security Features

### 1. Windows Firewall Rules

The setup creates firewall rules that:
- ✅ Allow local connections (localhost, 127.0.0.1)
- ❌ Block external network access
- ❌ Block other applications from binding to these ports

### 2. Port Binding

All services bind to `127.0.0.1` (localhost only):
```yaml
ports:
  - "127.0.0.1:3000:3000"  # Not accessible from other machines
```

### 3. Lock Files

Port reservations are tracked in `.port-locks/`:
```
.port-locks/
├── web.3000.pid
├── api_legacy.3001.pid
├── web_legacy.3002.pid
├── api.3003.pid
├── postgres.5433.pid
└── ...
```

### 4. Conflict Detection

If another application tries to use these ports:
```
[ERROR] PORT CONFLICTS DETECTED:

 Port  | Key             | Status
-------|-----------------|-----------------------------------
 3000  | web             | ✗ CONFLICT - node (PID: 12345)
 3001  | api_legacy      | ✗ CONFLICT - python (PID: 12346)

These ports are RESERVED for affiliate-product-showcase only!
```

## Troubleshooting

### Port Already in Use by Another Project

```powershell
# Check what's using the port
.\scripts\port-manager.ps1 -Action check

# Force kill conflicting processes (be careful!)
.\scripts\port-manager.ps1 -Action setup -Force
```

### Firewall Blocking

```powershell
# View firewall rules
Get-NetFirewallRule | Where-Object { $_.DisplayName -like "aps-2026*" }

# Remove all project firewall rules
Get-NetFirewallRule | Where-Object { $_.DisplayName -like "aps-2026*" } | Remove-NetFirewallRule
```

### Reset Everything

```powershell
# Release all ports
.\scripts\port-manager.ps1 -Action release

# Kill all node processes
Get-Process node | Stop-Process -Force

# Remove lock files
Remove-Item .port-locks/*.pid -Force

# Restart fresh
.\scripts\port-manager.ps1 -Action setup
```

## Configuration Files

| File | Purpose |
|------|---------|
| `.project-ports.config.json` | Port registry (all 10 ports) |
| `.env` | Root environment variables |
| `apps/api/.env` | API server environment (port 3003) |
| `apps/web/.env.local` | Web frontend environment (port 3000) |
| `docker/docker-compose.yml` | Infrastructure services |
| `scripts/port-manager.ps1` | Port management script |

## Project Isolation Wall

```
┌─────────────────────────────────────────────────────────────────┐
│  OTHER PROJECTS                                                  │
│  Cannot use these ports:                                         │
│  ❌ 3000, 3001, 3002, 3003 (Application - RESERVED)            │
│  ❌ 5433, 6380 (Database - RESERVED)                             │
│  ❌ 9000, 9001, 5672, 15672 (Infrastructure - RESERVED)         │
│                                                                  │
│  ╔═══════════════════════════════════════════════════════════╗   │
│  ║  THIS PROJECT                                             ║   │
│  ║  Active Ports:                                            ║   │
│  ║  • Port 3000 - Web (Active)                               ║   │
│  ║  • Port 3003 - API (Active)                               ║   │
│  ║                                                           ║   │
│  ║  Legacy Ports (Protected):                                ║   │
│  ║  • Port 3001 - API Legacy (prevent conflicts)             ║   │
│  ║  • Port 3002 - Web Legacy (prevent conflicts)             ║   │
│  ║                                                           ║   │
│  ║  Infrastructure:                                          ║   │
│  ║  • Port 5433 - PostgreSQL                                 ║   │
│  ║  • Port 6380 - Redis                                      ║   │
│  ║  • Port 9000/9001 - MinIO                                 ║   │
│  ║  • Port 5672/15672 - RabbitMQ                             ║   │
│  ╚═══════════════════════════════════════════════════════════╝   │
│                                                                  │
│  🧱 WALL: Windows Firewall + Port Binding + Lock Files          │
└─────────────────────────────────────────────────────────────────┘
```

## Why Include Legacy Ports (3001, 3002)?

These ports are protected because:
- 📚 **Old Documentation** - References these ports in archived docs
- 🔧 **Old Scripts** - Some scripts may still reference them
- 🔄 **Backward Compatibility** - Prevents confusion when switching
- 🛡️ **Conflict Prevention** - Ensures no other project uses them

---

**Project ID**: `aps-2026`  
**Reserved Ports**: `3000, 3001, 3002, 3003, 5433, 6380, 9000, 9001, 5672, 15672` (10 ports total)  
**Last Updated**: 2026-02-13
