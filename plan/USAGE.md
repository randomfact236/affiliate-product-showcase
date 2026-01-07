# Plan USAGE — Quick Reference

This file is a compact, opinionated reference for using the plan generator and related workflows.

Quick Reference
- Regenerate outputs (plan, todo MD, todo JSON, state):
  - `node plan/plan_sync_todos.cjs`
- Validate only (no changes):
  - `node plan/plan_sync_todos.cjs --validate` 
- Validate strictly (CI style):
  - `node plan/plan_sync_todos.cjs --validate --strict`
- Preview auto-fix for missing siblings:
  - `node plan/plan_sync_todos.cjs --validate --fix-missing --preview`
- Apply auto-fix (writes to `plan_source.md`):
  - `node plan/plan_sync_todos.cjs --validate --fix-missing --apply`

All CLI flags
- `--validate` : Run structural validation (duplicates, malformed, orphan, missing siblings).
- `--strict` : Treat warnings as errors (use in CI).
- `--fix-missing` : Detect missing siblings and prepare conservative placeholder inserts.
- `--preview` : Show proposed fixes without modifying files.
- `--apply` : Apply proposed fixes to `plan/plan_source.md` (use `PLAN_GENERATOR=1` when committing generated outputs).
- `--source`, `--state`, `--out-plan`, `--out-todo-md`, `--out-todo-json` : Paths to source/state/output files (see help output).
- Additional flags: `--bootstrap`, `--copy-source`, `--copy-todo`, `--print-source`, `--print-todo`, `--quiet` — run `node plan/plan_sync_todos.cjs --help` for details.

Common workflows
- Regenerate and commit generated outputs:
  1. `node plan/plan_sync_todos.cjs`
 2. `git add plan/plan_sync.md plan/plan_sync_todo.md plan/plan_todos.json plan/plan_state.json`
 3. `set PLAN_GENERATOR=1` (Windows) or `export PLAN_GENERATOR=1` (Unix)
 4. `git commit -m "chore(plan): regenerate generated plan outputs"`

- Fix missing siblings (preview then apply):
  1. `node plan/plan_sync_todos.cjs --validate --fix-missing --preview`
 2. Inspect proposed placeholders; if acceptable:
 3. `node plan/plan_sync_todos.cjs --validate --fix-missing --apply`
 4. Commit with `PLAN_GENERATOR=1` set.

- Run the test harness locally (recommended before pushing large generator changes):
  - `node plan/test_plan_generator.cjs`

Exit codes
- `0` — Success (no blocking validation errors).
- Non-zero — Failure; check console output. When `--strict` is used, warnings may exit non-zero.

Troubleshooting
- CI failing `verify-generated` or `check-plan-format`:
  - Regenerate outputs locally, run the formatter (`node scripts/format_plan_source.js`), then commit the regenerated files with `PLAN_GENERATOR=1`.
- Spurious timestamp diffs in CI:
  - The verify workflow normalizes `lastSyncAt` fields; if you still see diffs, regenerate outputs locally and commit.
- If validation flags report duplicates or malformed codes:
  - Inspect `plan/plan_source.md` for duplicate codes or malformed markers; run `--validate` to get the list.

Commit suggestion
- Commit message: `docs(plan): add USAGE reference guide`

If you want, I can commit this file now using that message.
