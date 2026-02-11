# Auto-Recovery System for ERR_NETWORK_IO_SUSPENDED

> **Status:** ✅ ACTIVE  
> **Last Updated:** 2026-02-09

## Problem

`ERR_NETWORK_IO_SUSPENDED` occurs when:
- Computer goes to sleep/hibernate
- Network adapter power saving kicks in
- Server process crashes or stops
- WiFi/Ethernet connection drops

## Solution Overview

A multi-layered auto-recovery system:

```
┌─────────────────────────────────────────────────────────────┐
│                    BROWSER LAYER                            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  ConnectionRecovery Component                        │   │
│  │  • Detects connection loss                           │   │
│  │  • Shows recovery UI                                 │   │
│  │  • Auto-reloads when connection restored             │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   SERVER LAYER                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  auto-recovery-system.ps1                            │   │
│  │  • Health checks every 10 seconds                    │   │
│  │  • Auto-restarts server on failure                   │   │
│  │  • Network stack repair                              │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Quick Recovery

### Method 1: Double-Click Recovery (Recommended)
```
1. Double-click: AUTO-RECOVERY.bat
2. Select option [1] FULL RECOVERY NOW
3. System will restart server and monitor
```

### Method 2: Quick Fix Only
```
1. Double-click: AUTO-RECOVERY.bat
2. Select option [2] QUICK FIX
3. Server restarts immediately
```

### Method 3: Background Monitor
```
1. Double-click: AUTO-RECOVERY.bat
2. Select option [4] BACKGROUND MONITOR
3. Monitor runs hidden, auto-recovers silently
4. Check logs at: Scan-report\recovery-log.md
```

## Components

### 1. Browser-Side Recovery
**File:** `apps/web/src/components/connection-recovery.tsx`

Features:
- Heartbeat checks every 5 seconds
- Detects `online`/`offline` browser events
- Shows user-friendly recovery UI
- Auto-reloads when connection restored
- Manual reload/retry buttons

### 2. Server-Side Recovery
**File:** `scripts/auto-recovery-system.ps1`

Features:
- Health monitoring every 10 seconds
- Auto-restart on 3 consecutive failures
- Network stack repair (ipconfig)
- Cache clearing
- Dependency verification
- Browser auto-open on recovery

### 3. Launcher
**File:** `AUTO-RECOVERY.bat`

Menu options:
1. Full Recovery + Monitor
2. Quick Fix (restart only)
3. Network Repair Only
4. Background Monitor
5. Stop All Monitoring

## How It Works

### Detection Flow
```
1. Health check → FAIL
2. Retry (1/3) → FAIL  
3. Retry (2/3) → FAIL
4. Retry (3/3) → FAIL → TRIGGER RECOVERY
```

### Recovery Flow
```
1. Stop all Node processes
2. Repair network stack
3. Clear Next.js cache
4. Check dependencies
5. Start API server
6. Start Web server
7. Verify health
8. Open browser
9. Resume monitoring
```

## Prevention

### Windows Power Settings
1. Open Power Options
2. Change plan settings
3. Change advanced power settings
4. Set "Sleep after" to "Never" when plugged in
5. Disable "Allow hybrid sleep"

### Network Adapter
1. Device Manager → Network adapters
2. Right-click adapter → Properties
3. Power Management tab
4. Uncheck "Allow computer to turn off this device"

## Logs

All recovery actions are logged:
- **Location:** `Scan-report/recovery-log.md`
- **Includes:** Timestamps, actions, success/failure

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Recovery loop | Check port conflicts, kill other node processes |
| Network repair fails | Run as Administrator |
| Dependencies missing | Run `npm install` in apps/web and apps/api |
| Browser doesn't open | Check default browser settings |
| Background monitor not working | Check Windows Task Scheduler permissions |

## Integration

The recovery system integrates with:
- `START-WEBSITE.bat` - Uses recovery on startup failure
- `SCAN-AND-FIX.bat` - Runs health checks
- `Enterprise Scanner` - Reports server health

## Status

```
✅ ConnectionRecovery component: ACTIVE
✅ Auto-recovery PowerShell: READY
✅ Batch launcher: READY
✅ Browser integration: DEPLOYED
```

## Next Steps

1. Run `AUTO-RECOVERY.bat` and select [1]
2. Server will restart and begin monitoring
3. Browser will open automatically
4. System will auto-recover from future interruptions
