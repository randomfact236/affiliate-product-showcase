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

3. Confirm it works by attempting a `git push`. The hook will prompt for `yes`.

Notes:
- Hooks in `.git/hooks` are local to each clone and are not versioned by git.
- Keeping a versioned copy in `plan/git-hooks` lets contributors install the same hooks.
