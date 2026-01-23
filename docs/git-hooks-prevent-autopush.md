# Git Hooks - Prevent Auto-Push

## Overview

This project includes a pre-push Git hook that prevents automatic pushes to the remote repository. Every push requires explicit confirmation, helping to prevent accidental commits from being pushed.

## What the Hook Does

The pre-push hook:
1. **Intercepts** every `git push` command before it executes
2. **Displays** clear warning and information about the push attempt
3. **Prompts** for explicit confirmation (y/N)
4. **Approves** push only if user types 'y' or 'Y'
5. **Cancels** push for any other input (including Enter)

## Installation

### On Windows (PowerShell)

Run the installation script:

```powershell
.\scripts\install-git-hooks.ps1
```

This script will:
- Copy the pre-push hook to the correct location
- Display installation status
- Provide usage instructions

### On Linux/macOS

Make the hook executable:

```bash
chmod +x .git/hooks/pre-push
```

## Usage

### Normal Workflow

1. Make your changes and commit them:
   ```bash
   git add .
   git commit -m "Your commit message"
   ```

2. Attempt to push:
   ```bash
   git push
   ```

3. **Hook will activate** and display:
   ```
   =================================
     GIT PUSH BLOCKED
   =================================

   ⚠️  AUTOMATIC PUSH PREVENTION HOOK

   You are attempting to push to the remote repository.

   IMPORTANT:
     - Auto-pushing is blocked by pre-push hook
     - You must explicitly approve each push
     - This prevents accidental commits from being pushed

   Remote: origin
   URL:    https://github.com/your-repo.git

   Do you want to continue with this push? [y/N]:
   ```

4. **Type 'y'** and press Enter to approve the push

5. **Or press Enter** (or type anything else) to cancel

### Cancelling a Push

If you change your mind:

- Simply press Enter (default is 'N')
- Type any character other than 'y' or 'Y'
- Press Ctrl+C to abort

## Hook Behavior

### When Push is Approved

```
✓ Push approved by user. Continuing...
```

The push proceeds normally.

### When Push is Cancelled

```
✗ Push cancelled by user.

To push again, you need to:
  1. Review your changes with: git status
  2. Confirm that push is intentional
  3. Run git push and approve when prompted
```

The push is aborted and nothing is sent to remote.

## Benefits

1. **Prevents Accidental Pushes**: No more pushing incomplete or wrong commits
2. **Forces Review**: You must think before each push
3. **Safety Net**: Gives you a chance to verify changes
4. **Peace of Mind**: Protects your remote repository from unintended changes

## Disabling the Hook

If you need to temporarily disable the hook:

```bash
# Rename the hook
mv .git/hooks/pre-push .git/hooks/pre-push.disabled

# When you want to re-enable
mv .git/hooks/pre-push.disabled .git/hooks/pre-push
```

## Troubleshooting

### Hook Not Running

If the hook isn't activating:

1. Check if hook file exists:
   ```bash
   ls -la .git/hooks/pre-push
   ```

2. Verify it's executable (Linux/macOS):
   ```bash
   chmod +x .git/hooks/pre-push
   ```

3. Check Git configuration:
   ```bash
   git config core.hooksPath
   ```
   If set, it should be `.git/hooks` or unset

### Hook Permissions Error

On Windows, Git for Windows typically handles permissions automatically. If you see errors:

- Run Git Bash as administrator
- Or use the PowerShell installation script
- Check that `.git/hooks/pre-push` exists

### Want to Force Push Without Confirmation

If you absolutely need to bypass the hook (not recommended):

```bash
# Push with --no-verify flag
git push --no-verify
```

**Warning**: Use `--no-verify` sparingly and only when you're certain about the push.

## Technical Details

### Hook Location

```
.git/hooks/pre-push
```

### Hook Parameters

The hook receives two arguments:
- `$1` - Name of the remote repository (e.g., "origin")
- `$2` - URL of the remote repository

### Return Codes

- `0` - Allow push to proceed
- `1` - Cancel the push

### Supported Platforms

- ✅ Windows (with Git for Windows)
- ✅ Linux
- ✅ macOS

## Customization

You can customize the hook by editing `.git/hooks/pre-push`:

- Change the warning message
- Modify the confirmation prompt
- Add additional checks or validations
- Change the acceptance key (currently 'y' or 'Y')

## Related Documentation

- [Git Hooks Documentation](https://git-scm.com/docs/githooks)
- [pre-push Hook Reference](https://git-scm.com/docs/githooks#pre-push)
- [Project Git Workflow](./git-workflow.md)

## Support

If you encounter issues with the pre-push hook:

1. Check the hook file permissions
2. Verify Git configuration
3. Try the PowerShell installation script (Windows)
4. Check Git logs for errors: `GIT_TRACE=1 git push`

---

**Note**: This hook is designed to prevent accidental pushes while maintaining your normal Git workflow. It does not affect commit operations, only pushes to remote repositories.
