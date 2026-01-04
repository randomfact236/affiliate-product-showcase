# Plan + Todo Sync System

This folder is managed by the plan synchronization scripts.

## File Roles (Strict)

**Editable (source of truth)**
- `plan_source.md` — the plan outline (Steps → Topics → Subtopics / nested tasks). Edit this file only.
- `plan_state.json` — status mapping by code (pending / in-progress / blocked / cancelled / completed). Prefer updating via the status script.

**Generated (do not edit manually)**
- `plan_sync.md` — rendered plan with status markers applied bottom-up.
- `plan_sync_todo.md` — flattened todo list for quick reference.
- `plan_todos.json` — flattened JSON export (all nodes with derived markers).

## Status Marker Rules

Markers are computed bottom-up with strict priority:
1. ✅ Completed (leaf is completed OR all children completed)
2. ❌ Cancelled (self or any child cancelled)
3. ⛔ Blocked (self or any child blocked)
4. ⏳ In-progress (self or any child in-progress)
5. (no marker) Pending

## Commands

Bootstrap once (creates `plan_source.md` from an existing `plan_sync.md`):

```powershell
node scripts/sync_todos.cjs --bootstrap
```

Sync after any change:

```powershell
node scripts/sync_todos.cjs
```

Update status for a specific code (then auto-sync):

```powershell
node scripts/plan_status.cjs --code 1.1.1 --status in-progress
```

Valid statuses: `pending`, `in-progress`, `blocked`, `cancelled`, `completed`.
