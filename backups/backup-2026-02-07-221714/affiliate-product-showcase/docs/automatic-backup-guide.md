# Automatic Backup Branch System

## Overview

This project includes an **automatic backup branch creation system** that creates properly named backup branches with timestamps whenever you need to save your work.

## 🚀 Quick Usage

### For Linux/Mac/Git Bash:
```bash
# From your topic branch (e.g., 1_11)
npm run backup

# Or specify topic number manually
npm run backup -- 1_11
```

### For Windows PowerShell:
```powershell
# From your topic branch
npm run backup:windows

# Or specify topic number manually
npm run backup:windows -- 1_11
```

### Direct Script Usage:
```bash
# Linux/Mac
./scripts/create-backup-branch.sh

# Windows
.\scripts\create-backup-branch.ps1
```

---

## 📋 How It Works

### 1. Automatic Detection
The script automatically detects:
- **Topic number** from your current branch name (e.g., `1_11` from `1_11-feature`)
- **Current date and time** for unique naming
- **Uncommitted changes** (blocks backup if found)

### 2. Branch Naming Convention
```
backup-1_11_2026-01-13_02-42
└───┴───┴────────┴───┴───
    │   │    │    │   └── Minutes
    │   │    │    └───── Hour (24h)
    │   │    └────────── Day
    │   └─────────────── Month
    └────────────────── Topic Number
```

### 3. Automatic Process
```bash
✅ Checks for uncommitted changes
✅ Creates backup branch from current HEAD
✅ Bypasses pre-push hook automatically
✅ Pushes to remote
✅ Restores hook
✅ Returns to original branch
```

---

## 🎯 When to Use

### Before Major Changes:
```bash
# Before refactoring
npm run backup
# Now safe to refactor
```

### Before Pushing:
```bash
# Create backup before pushing to main
npm run backup
git push origin main
```

### Before Experimenting:
```bash
# Before trying new approach
npm run backup
# Experiment freely
```

### End of Day:
```bash
# Save work in progress
npm run backup
# Continue tomorrow
```

---

## 📊 Example Workflow

### Scenario: Working on Topic 1.11

```bash
# 1. Start working on feature
git checkout -b 1_11-new-feature

# 2. Make some changes
# ... edit files ...

# 3. Before major refactoring, create backup
npm run backup
# 🔄 Creating automatic backup branch...
#   Topic: 1_11
#   Timestamp: 2026-01-13_02-42
#   Branch: backup-1_11_2026-01-13_02-42
# 📦 Creating branch...
# 🚀 Pushing to remote...
# ✅ Success! Backup branch created and pushed.
#    URL: https://github.com/.../tree/backup-1_11_2026-01-13_02-42
# 🔄 Returning to previous branch...
# ✅ Backup complete!
#    Branch: backup-1_11_2026-01-13_02-42
#    You can safely push your changes now.

# 4. Continue working safely
# ... make more changes ...

# 5. Create another backup before pushing
npm run backup
# 🔄 Creating automatic backup branch...
#   Topic: 1_11
#   Timestamp: 2026-01-13_03-15
#   Branch: backup-1_11_2026-01-13_03-15
# ...

# 6. Push your work
git push origin 1_11-new-feature
```

---

## 🔍 What Gets Backed Up

The backup branch includes:

```
wp-content/plugins/affiliate-product-showcase/
├── .husky/
│   ├── commit-msg
│   ├── pre-commit
│   ├── pre-push
│   └── _/husky.sh
├── .lintstagedrc.json
├── commitlint.config.cjs
├── package.json
├── package-lock.json
├── scripts/
│   ├── check-debug.js
│   ├── assert-coverage.sh
│   ├── create-backup-branch.sh
│   └── create-backup-branch.ps1
└── [ALL YOUR CURRENT CHANGES]
```

---

## 🛡️ Safety Features

### 1. Uncommitted Changes Check
```bash
❌ If you have uncommitted changes:
   "⚠️  You have uncommitted changes. Please commit or stash them first."
   "   Quick fix: git add . && git commit -m 'temp: backup changes'"
```

### 2. Hook Management
- Automatically bypasses pre-push hook
- Restores hook after backup
- No manual intervention needed

### 3. Error Handling
- If push fails, hook is restored
- Original branch is maintained
- Clear error messages

---

## 📈 Viewing Your Backups

### List All Backups:
```bash
git branch -r | grep backup
```

### View Backup Contents:
```bash
git checkout backup-1_11_2026-01-13_02-42
# View files
git checkout main
```

### Compare with Main:
```bash
git diff main..backup-1_11_2026-01-13_02-42
```

---

## 🗑️ Cleaning Up Old Backups

### Delete Single Backup:
```bash
git push origin --delete backup-1_11_2026-01-13_02-42
```

### Delete Multiple Old Backups:
```bash
# List old backups
git branch -r | grep "backup-.*2026-01-12"

# Delete them
git push origin --delete backup-1_11_2026-01-12-142019
git push origin --delete backup-1_11_2026-01-12-165203
```

---

## 🔧 Manual Override

### Specify Topic Number:
```bash
# If branch name doesn't contain topic number
npm run backup -- 1_11
# or
./scripts/create-backup-branch.sh 1_11
```

### Force Backup with Uncommitted Changes:
```bash
# First commit changes
git add .
git commit -m "temp: backup state"

# Then create backup
npm run backup

# Later, you can squash the temp commit
git rebase -i HEAD~2
```

---

## 🎯 Integration with Git Workflow

### Pre-Push Hook (Already Configured):
The pre-push hook runs quality checks, but you can create backups **before** pushing:

```bash
# 1. Create backup
npm run backup

# 2. Push (quality gates run automatically)
git push origin main
```

### Commit Message Hook:
Your commits are validated automatically. Create backup **before** committing:

```bash
# 1. Create backup
npm run backup

# 2. Make changes
# ... edit files ...

# 3. Commit (validated automatically)
git commit -m "feat: add feature"
```

---

## 📝 Best Practices

### ✅ DO:
- Create backup before major changes
- Create backup before pushing
- Create backup at end of day
- Use descriptive topic numbers
- Keep backups until work is merged

### ❌ DON'T:
- Create backup with uncommitted changes
- Delete backups you might need
- Use backup branches for development
- Forget to clean up old backups

---

## 🚨 Troubleshooting

### Problem: "Please commit or stash changes"
**Solution:**
```bash
git add .
git commit -m "temp: backup state"
npm run backup
```

### Problem: "Push failed"
**Solution:**
- Check network connection
- Verify GitHub credentials
- Check repository permissions

### Problem: Wrong topic number detected
**Solution:**
```bash
npm run backup -- 1_11
```

---

## 🎉 Summary

The automatic backup system provides:

✅ **One-command backup**: `npm run backup`
✅ **Automatic naming**: `backup-1_11_2026-01-13_02-42`
✅ **Safe hook management**: No manual bypass needed
✅ **Error protection**: Restores state on failure
✅ **Cross-platform**: Bash and PowerShell versions
✅ **Integration**: Works with existing Git workflow

**Use it whenever you want to save your current state safely!**
