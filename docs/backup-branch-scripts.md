# Backup Branch Creation Scripts

These scripts help you create backup branches with automatic date and timestamp insertion.

## Overview

The backup branch scripts automatically append the current date and time to your branch name, making it easy to organize and track when backups were created.

### Format

Branch names follow this pattern: `{prefix}-{YYYY-MM-DD-HHMMSS}`

**Example**: `backup-2026-01-13-115959`

## Available Scripts

### 1. Bash Script (Linux/Mac/Git Bash)

**File**: `scripts/create-backup-branch.sh`

**Usage**:
```bash
# Use default prefix "backup"
./scripts/create-backup-branch.sh

# Use custom prefix
./scripts/create-backup-branch.sh backup_1-12

# Use topic-based prefix
./scripts/create-backup-branch.sh topic-1-7
```

**Make executable** (first time only):
```bash
chmod +x scripts/create-backup-branch.sh
```

### 2. PowerShell Script (Windows)

**File**: `scripts/create-backup-branch.ps1`

**Usage**:
```powershell
# Use default prefix "backup"
.\scripts\create-backup-branch.ps1

# Use custom prefix
.\scripts\create-backup-branch.ps1 -BranchPrefix "backup_1-12"

# Use topic-based prefix
.\scripts\create-backup-branch.ps1 -BranchPrefix "topic-1-7"
```

**Note**: You may need to enable script execution in PowerShell:
```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
```

## Features

✅ **Automatic Date/Time**: Appends current date and time in `YYYY-MM-DD-HHMMSS` format
✅ **Custom Prefix**: Specify any prefix for your backup branch
✅ **Error Handling**: Checks for existing branches and git repository
✅ **Remote Push**: Automatically pushes to remote with upstream tracking
✅ **Auto Switch**: Returns to original branch after creating backup
✅ **Colored Output**: Easy-to-read colored console output
✅ **Bypass Hooks**: Uses `--no-verify` to skip pre-push hooks

## Example Output

```
ℹ Current branch: main
ℹ Creating backup branch: backup_1-12-2026-01-13-115959
ℹ Creating new branch from current state...
ℹ Pushing branch to remote...
ℹ Switching back to main...
✓ Backup branch created successfully!

Backup Details:
  Branch Name: backup_1-12-2026-01-13-115959
  Remote: origin/backup_1-12-2026-01-13-115959
  Date/Time: 2026-01-13-115959
  Based on: main

ℹ To view all backup branches: git branch -a | grep backup
ℹ To delete this backup: git branch -D backup_1-12-2026-01-13-115959 && git push origin --delete backup_1-12-2026-01-13-115959
```

## Recommended Branch Naming Patterns

### Topic-Based Backups
```bash
./scripts/create-backup-branch.sh topic-1-7
./scripts/create-backup-branch.sh topic-1-10
./scripts/create-backup-branch.sh topic-1-11
./scripts/create-backup-branch.sh topic-1-12
```

### Feature-Based Backups
```bash
./scripts/create-backup-branch.sh feature-admin-panel
./scripts/create-backup-branch.sh feature-api-integration
```

### Milestone Backups
```bash
./scripts/create-backup-branch.sh milestone-v1.0
./scripts/create-backup-branch.sh milestone-beta-release
```

### Pre-Release Backups
```bash
./scripts/create-backup-branch.sh pre-release
./scripts/create-backup-branch.sh pre-submission
```

## Managing Backup Branches

### View All Backup Branches
```bash
# Bash/Git Bash
git branch -a | grep backup

# PowerShell
git branch -a | Select-String backup
```

### Delete a Backup Branch
```bash
# Delete both local and remote
git branch -D branch-name && git push origin --delete branch-name
```

### List Backup Branches Sorted by Date
```bash
# Bash/Git Bash
git branch -a --sort=-committerdate | grep backup

# PowerShell
git branch -a | Select-String backup | Sort-Object
```

## Common Use Cases

### 1. Before Major Changes
```bash
./scripts/create-backup-branch.sh pre-refactor
```

### 2. After Completing Topics/Milestones
```bash
./scripts/create-backup-branch.sh topic-1-12-complete
```

### 3. Before Merging PRs
```bash
./scripts/create-backup-branch.sh pre-merge-123
```

### 4. Daily/Weekly Backups
```bash
# Quick backup with default prefix
./scripts/create-backup-branch.sh
```

## Error Handling

The scripts handle common errors:

- **Not in git repository**: Exits with error message
- **Branch already exists**: Prompts for confirmation before continuing
- **Push failed**: Warns that branch was created locally but not pushed
- **Checkout failed**: Warns if can't switch back to original branch

## Integration with Git Hooks

The scripts use `--no-verify` flag to bypass pre-push hooks. If you want to enable hooks, remove the `--no-verify` flag from the script:

**Bash**: Change line 67 to:
```bash
git push -u origin "${BRANCH_NAME}"
```

**PowerShell**: Change line 65 to:
```powershell
git push -u origin $BranchName
```

## Best Practices

1. **Create backups before major changes**: Always backup before refactoring or significant updates
2. **Use descriptive prefixes**: Choose prefixes that describe what the backup represents
3. **Clean up old backups**: Regularly delete outdated backup branches to keep repository clean
4. **Document important backups**: Keep notes on what each backup contains
5. **Test after restore**: When using a backup, verify everything works correctly

## Troubleshooting

### Script won't run (Permission denied - Linux/Mac)
```bash
chmod +x scripts/create-backup-branch.sh
```

### Script won't run (Execution policy - PowerShell)
```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
```

### Branch already exists
The script will prompt you to confirm. If you want to create a new one, wait a minute or use a different prefix.

### Push failed
Check your internet connection and verify you have push permissions to the repository.

## Additional Resources

- [Git Branching Documentation](https://git-scm.com/docs/git-branch)
- [Git Checkout Documentation](https://git-scm.com/docs/git-checkout)
- [Git Push Documentation](https://git-scm.com/docs/git-push)
