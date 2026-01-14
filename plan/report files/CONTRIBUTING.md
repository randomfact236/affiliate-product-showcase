# Plan contributing rules


Do NOT edit generated plan files directly. The source of truth is:

- `plan/plan_source.md` — edit this file to change the plan.

To update generated outputs, run:

```bash
node plan/plan_sync_todos.cjs
```

This will produce:

- `plan/plan_sync.md`
- `plan/plan_sync_todo.md`
- `plan/plan_todos.json`
- `plan/plan_state.json`

Enforcement:

- A GitHub Actions workflow `./.github/workflows/verify-generated.yml` will fail CI if generated files are modified without updating the source and regenerating. Always run the script and commit the regenerated files in the same PR.

Local developer checks:

- We provide a local git hook at `.githooks/pre-commit` that runs the same verification used in CI. To enable it for your repository run:

```bash
git config core.hooksPath .githooks
```

After enabling, commits that modify generated plan files without regenerating will be blocked with an error instructing you to run the script and commit the results.

Notes:

- CI is authoritative; enabling the local hook is optional but recommended.
- For CI to enforce this, keep `node` available in the runner (the workflow sets up Node.js 18).

