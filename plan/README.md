**Quick Start**

- **Run generator (safe sync):** Use the bundled Node script from the `plan/` folder. Example (Windows PowerShell):

```powershell
cd plan
node plan_sync_todos.cjs --out-plan ../plan/plan_sync.md --out-todo-md ../plan/plan_sync_todo.md --out-todo-json ../plan/plan_todos.json
```

- **Generator-authoritative workflow:** When regenerating canonical plan files in CI or locally, set the `PLAN_GENERATOR=1` environment variable so the pre-commit hook allows the generated outputs to be committed.


**CLI Flags (summary)**

- **--validate**: Run structural validation on `plan_source.md` and report problems without writing generated files. Useful in CI or as a quick check.
- **--strict**: When used with `--validate`, treat missing sibling items as errors (non-zero exit). Without `--strict` missing siblings are warnings by default.
- **--fix-missing**: Detect missing sibling numeric items and offer an automatic conservative fix (inserts placeholder TODO lines). Use together with `--preview` or `--apply`.
- **--preview**: Show a preview of what would be changed (used with `--fix-missing`). Exits non-zero (1) to indicate there are changes to review.
- **--apply**: When combined with `--fix-missing`, actually write the auto-inserted placeholders into `plan_source.md`.
- **--bootstrap**: Create `plan_source.md` from an existing generated `plan_sync.md` (one-time operation if you started from generated files).
- **--copy-source**: Preserve the original `plan_source.md` formatting by embedding it into the generated plan file (with generated header and injected markers).
- **--copy-todo**: Copy source content into the generated todo MD instead of producing a flattened todo list.
- **--print-source**: Print the generated plan to stdout and exit (preview mode).
- **--print-todo**: Print the generated todo MD to stdout and exit (preview mode).
- **--quiet**: Reduce console output.
- **--source <path>**: Specify an alternate `plan_source.md` path.
- **--state <path>**: Specify an alternate `plan_state.json` path.
- **--out-plan <path>**: Path to write the generated plan (default: `plan_sync.md`).
- **--out-todo-md <path>**: Path to write the generated flattened todo markdown (default: `plan_sync_todo.md`).
- **--out-todo-json <path>**: Path to write the generated todos JSON (default: `plan_todos.json`).


**Status Marker Rules**

- The generator computes two related values for each node:
  - `marker`: a visible marker (emoji string) that may include multiple emojis when rollups are present.
  - `derivedStatus`: a single canonical status string for compatibility with existing tooling.

- Multi-marker rollup order and semantics (displayed left-to-right):
  - ⛔ (blocked): included if any descendant is `blocked`.
  - ⏳ (in-progress): included if any descendant is `in-progress`.
  - ✅ (completed): included when there are some completed descendants and there are NO pending descendants.
  - ❌ (cancelled): included if any descendant is `cancelled`.

- Examples:
  - A topic with both blocked and in-progress children => `⛔⏳`.
  - A subtree where all children are completed => `✅` (no `⏳`).

- Backwards compatibility: `derivedStatus` remains a single value chosen from `pending`, `in-progress`, `blocked`, `cancelled`, `completed`. Use `marker` for richer UI badges.


**Workflow Examples**

- Developer (local regenerate and commit):

```powershell
# regenerate outputs locally
cd plan
node plan_sync_todos.cjs --out-plan ../plan/plan_sync.md --out-todo-md ../plan/plan_sync_todo.md --out-todo-json ../plan/plan_todos.json

# then commit the generated files; the repository has a pre-commit hook that allows generator-originated commits when PLAN_GENERATOR=1
SETX /M PLAN_GENERATOR 1  # or set in CI/environment for the commit step
git add plan/plan_sync.md plan/plan_sync_todo.md plan/plan_todos.json plan/plan_state.json
git commit -m "chore(plan): regenerate plan outputs"
git push
```

- CI check (validate only on PR):

```yaml
# Example: run in GitHub Actions as a PR check
run: node plan/plan_sync_todos.cjs --validate --strict
```

- Safe fix-missing flow (preview before applying):

```powershell
# Preview missing siblings (non-destructive)
node plan/plan_sync_todos.cjs --validate --fix-missing --preview

# If preview looks good, apply changes (this writes plan_source.md)
node plan/plan_sync_todos.cjs --validate --fix-missing --apply
```


**Exit Codes**

- `0` — Success: validation passed or generator completed with no noteworthy warnings.
- `1` — Warnings-only: validation found non-strict issues (e.g., missing siblings when `--strict` is not used) or preview output was shown (e.g., `--fix-missing --preview`).
- `2` — Errors: validation errors (malformed/duplicate/orphan codes) or `--strict` caused missing-sibling issues to be treated as failures.
- `>2` — Unexpected runtime error (the generator prints the error message to stderr and exits non-zero).


**Troubleshooting**

- Pre-commit hook blocks plan edits:
  - The repository includes a pre-commit hook that prevents manual edits to the `plan/` outputs. Always regenerate files with the generator and commit with `PLAN_GENERATOR=1` set in the environment when committing generated outputs.

- Validation reports many missing siblings:
  - The validator extracts numeric codes from meaningful lines. If your `plan_source.md` contains other numeric lists or artifacts, consider adjusting formatting (use headings like `### 1.2.3 Title` for plan items) or run `--validate` and inspect `--fix-missing --preview` to see conservative placeholder suggestions.

- I accidentally committed a manual change to generated files:
  - Re-run the generator locally with `PLAN_GENERATOR=1` and commit the regenerated outputs to restore consistency.

- Want stricter CI enforcement:
  - Add a CI job that runs `node plan/plan_sync_todos.cjs --validate --strict` on pull requests and fail the check if it returns non-zero.


If anything here is unclear or you want the README expanded with more examples (GitHub Actions job YAML, PowerShell vs Bash snippets, or contributor guidance), tell me which part to expand and I'll update it.
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
node plan/plan_sync_todos.cjs --bootstrap
```

Sync after any change:

```powershell
node plan/plan_sync_todos.cjs
```

Update status for a specific code (then auto-sync):

```powershell
node plan/plan_status.cjs --code 1.1.1 --status in-progress
```

Valid statuses: `pending`, `in-progress`, `blocked`, `cancelled`, `completed`.
