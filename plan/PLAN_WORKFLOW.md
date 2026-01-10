# Plan Workflow Enforcement

Rule: Do not edit generated plan files (`plan/plan_sync.md`, `plan/plan_sync_todo.md`, `plan/plan_todos.json`, `plan/plan_state.json`) directly.

Source of truth: `plan/plan_source.md` is the editable source. Whenever you need to change the plan, edit `plan/plan_source.md` only and regenerate the derived files.

How to regenerate and commit (recommended):

- Unix/macOS:

```sh
./scripts/update-plan.sh
```

- Windows (PowerShell):

```powershell
.\scripts\update-plan.ps1
```

What the helper does:
- Runs `node plan/plan_sync_todos.cjs` to regenerate the generated files.
- Adds and commits the regenerated files with `PLAN_GENERATOR=1` so the repository hook allows the changes.

If you must force a manual commit to the generated files (not recommended), set the environment variable `PLAN_GENERATOR=1` when committing. Prefer editing `plan/plan_source.md` instead.
