```markdown
# Plan + Todo Sync System

This folder is managed by the plan synchronization scripts.

## File roles

**Editable (source of truth)**
- `plan_source.md` — the plan outline (Steps → Topics → nested items). Edit this file.
- `plan_state.json` — status mapping by code (`pending`, `in-progress`, `completed`, `blocked`, `cancelled`). Prefer updating via the status script.

**Generated (do not edit manually)**
- `plan_sync.md` — rendered plan with status markers.
- `plan_sync_todo.md` — flattened todo list.
- `plan_todos.json` — flattened JSON export.

## Commands

Regenerate outputs:

```powershell
node plan/plan_sync_todos.cjs
```

Update a single code status (then auto-sync):

```powershell
node plan/set-status.cjs 1.3.1 start
node plan/set-status.cjs 1.3.1 done
```

## Status rules

**Parent status propagation (only two rules):**
1. If ANY child is `in-progress` → parent becomes `in-progress`
2. If ALL children are `completed` → parent becomes `completed`

**Marker mapping (direct):**
- `completed` → ✅
- `in-progress` → ⏳
- `blocked` → ⛔
- `cancelled` → ❌
- `pending` → (no marker)
```
