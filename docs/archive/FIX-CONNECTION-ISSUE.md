# 🔧 FIX: Connection Refused Error

## Problem
You're seeing `ERR_CONNECTION_REFUSED` at `localhost:3002` because the development servers are **not running**.

## Root Cause
The website requires **3 things** to be running simultaneously:
1. Docker (database) - ✅ Running
2. API Server (port 3003) - ❌ Not running
3. Web Server (port 3002) - ❌ Not running

## Solution (CHOOSE ONE)

### Option 1: Double-Click to Start (Easiest)

1. **Double-click** `start-servers.bat` in the project folder
2. Wait 10 seconds
3. Browser will open automatically

### Option 2: Use PowerShell Script

```powershell
# In project root folder
powershell -ExecutionPolicy Bypass -File start-all.ps1
```

### Option 3: Manual Start (If others fail)

**Step 1:** Open new terminal, run:
```bash
cd apps/api
npm run dev
```
Wait for: `🚀 API Server running on http://localhost:3003`

**Step 2:** Open ANOTHER new terminal, run:
```bash
cd apps/web
npm run dev
```
Wait for: `ready started server on 0.0.0.0:3002`

**Step 3:** Open browser to http://localhost:3002

## How to Stop

- Close the terminal windows to stop servers
- Or press `Ctrl+C` in each terminal

## Verify It's Working

After starting, you should see:

```
✅ Docker containers running (ports 5433, 6380)
✅ API Server running (port 3003)
✅ Web Server running (port 3002)
```

Then browser shows the website instead of error.

## Still Not Working?

Run diagnostic:
```bash
pnpm diagnose
```

Or check detailed guide: `TROUBLESHOOTING.md`
