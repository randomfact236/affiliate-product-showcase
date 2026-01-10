# Plan Workflow Enforcement

Single source of truth:
- Edit only: `plan/plan_source.md`
- Update statuses only via: `plan/plan_state.json` (prefer the command below)

Generated files (never edit manually):
- `plan/plan_sync.md`
- `plan/plan_sync_todo.md`
- `plan/plan_todos.json`

## Daily commands

Regenerate generated files (and stage them):

```sh
node plan/manage-plan.js regenerate
```

Set a task status (updates `plan_state.json`, regenerates, stages):

```sh
node plan/manage-plan.js set 1.3.1 completed
node plan/manage-plan.js set 1.3.1 in-progress
node plan/manage-plan.js set 1.3.1 pending
```

Validate workflow (used by pre-commit):

```sh
node plan/manage-plan.js validate
```

## Helper scripts

- Unix/macOS: `./scripts/update-plan.sh`
- Windows PowerShell: `./scripts/update-plan.ps1`

Both helpers call `node plan/manage-plan.js regenerate`.

## Pre-commit enforcement

The git hook rejects commits when:
- A generated plan file is staged without also staging `plan/plan_source.md` or `plan/plan_state.json`.
- The generator output does not match what is committed (drift).

If the hook blocks your commit, run:

```sh
node plan/manage-plan.js regenerate
```
