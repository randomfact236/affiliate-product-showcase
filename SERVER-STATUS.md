# Server Status

## ✅ SERVERS ARE RUNNING!

Last verified: 2026-02-08 10:05 UTC

### Running Services

| Service | Port | Status | URL |
|---------|------|--------|-----|
| Docker PostgreSQL | 5433 | ✅ Running | localhost:5433 |
| Docker Redis | 6380 | ✅ Running | localhost:6380 |
| API Server | 3003 | ✅ Running | http://localhost:3003/api/v1/health |
| Web Server | 3002 | ✅ Running | http://localhost:3002 |

### Verification

```bash
# API Health Check
$ curl http://localhost:3003/api/v1/health
{"status":"ok","timestamp":"2026-02-08T10:04:58.515Z"}

# Web Server
$ curl http://localhost:3002
<!DOCTYPE html>... (HTML page)
```

### Quick Start for Future

To start the website in the future:

```bash
# Option 1: Double-click (Windows)
AUTO-START-WEBSITE.bat

# Option 2: Command line
pnpm start:all

# Option 3: Manual
pnpm infra:up
cd apps/api && node simple-server.js
cd apps/web && node simple-server.js
```

### Stop Servers

Close the terminal windows or press `Ctrl+C` in each.
