# Installing the git hooks

This folder contains a copy of the interactive `pre-push` hook used by this repository.

To install locally (recommended):

1. Copy the hook into your repository hooks directory:

   ```powershell
   copy .\plan\git-hooks\pre-push .git\hooks\pre-push
   ```

2. On Unix-like systems make it executable:

   ```bash
   chmod +x .git/hooks/pre-push
   ```

4. (Optional) Install the `pre-commit` confirmation hook as well:

   ```powershell
   copy .\plan\git-hooks\pre-commit .git\hooks\pre-commit
   ```

   On Unix-like systems make it executable:

   ```bash
   chmod +x .git/hooks/pre-commit
   ```

3. Confirm it works by attempting a `git push`. The hook will prompt for `yes`.

Notes:
- Hooks in `.git/hooks` are local to each clone and are not versioned by git.
- Keeping a versioned copy in `plan/git-hooks` lets contributors install the same hooks.

Environment variables:
- Set `GIT_PUSH_ALLOW=1` or `SKIP_PRE_PUSH_CONFIRM=1` to bypass the `pre-push` prompt.
- Set `GIT_COMMIT_ALLOW=1` or `SKIP_PRE_COMMIT_CONFIRM=1` to bypass the `pre-commit` prompt.
- CI environments typically set `CI=1`; both hooks respect that and will not prompt.

Prompt behavior:
- Hooks now ask for an explicit `y`/`yes` to proceed. Any other response aborts the operation.

- Hooks now prefer a native Allow/Deny dialog on Windows (PowerShell). If a GUI dialog isn't available, they fall back to a simple `Press ENTER to allow` prompt on a TTY. Any other environment is treated as non-interactive and the hooks will abort unless bypassed.

Tip: Users who prefer an automated install can copy these files into `.git/hooks` and make them executable during their local setup script.
