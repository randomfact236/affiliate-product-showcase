# GitFlow Cheat Sheet

## Quick Reference

### Feature Development
```bash
# Start new feature
git checkout develop
git pull origin develop
git checkout -b feature/[ticket]-[description]

# Work on feature
# ... make changes ...
git add .
git commit -m "feat(scope): description"

# Push and create PR
git push -u origin feature/[ticket]-[description]
# Create PR on GitHub targeting develop
```

### Release Process
```bash
# Prepare release
git checkout develop
git pull origin develop
git checkout -b release/[version]

# Update version and changelog
# ... edit files ...
git add .
git commit -m "chore(release): prepare version [version]"

# Push and create PR
git push -u origin release/[version]
# Create PR on GitHub targeting main
```

### Hotfix Process
```bash
# Create hotfix
git checkout main
git pull origin main
git checkout -b hotfix/[version]

# Fix critical bug
# ... make minimal changes ...
git add .
git commit -m "hotfix(scope): fix critical bug"

# Push and create PR
git push -u origin hotfix/[version]
# Create PR on GitHub targeting main
```

## Branch Naming Convention

| Branch Type | Pattern | Example |
|-------------|---------|---------|
| Feature | `feature/[ticket]-[description]` | `feature/2.8-add-comparison` |
| Release | `release/[version]` | `release/1.0.0` |
| Hotfix | `hotfix/[version]` | `hotfix/1.0.1` |
| Backup | `backup/[description]-[timestamp]` | `backup/pre-release-2026-01-17-1900` |

## Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting
- `refactor`: Code restructuring
- `test`: Tests
- `chore`: Maintenance
- `security`: Security fixes
- `performance`: Performance improvements

### Examples

**Feature:**
```
feat(auth): add JWT token authentication

Implement JWT-based authentication for REST API.
Tokens expire after 24 hours.

Closes #123
```

**Bug Fix:**
```
fix(api): fix product query pagination

Fixed incorrect pagination results when filtering
by category.

Closes #456
```

**Hotfix:**
```
hotfix(security): sanitize affiliate URL output

Prevent XSS vulnerability in affiliate display.
Added esc_url() to all affiliate URL outputs.

Security issue reported by @researcher
```

## GitHub Actions Integration

### Automatic Deployments

| Event | Action | Environment |
|-------|--------|-------------|
| Push to `main` | Deploy to production | Production |
| Push to `develop` | Deploy to staging | Staging |
| PR to `main` | Deploy to staging for review | Staging |
| Tag push (`v*`) | Deploy to WordPress.org + GitHub Release | Production |

### Required Checks

All PRs must pass:
- ✅ Code Quality (PHPStan, Psalm, PHPCS, ESLint, Stylelint)
- ✅ Testing (PHPUnit, Frontend tests)
- ✅ Security Scan (Composer audit, NPM audit)
- ✅ Build (Asset compilation)

## Common Commands

### Check Branch Status
```bash
# Current branch
git branch --show-current

# All branches
git branch -a

# Recent commits
git log --oneline -10
```

### Sync Branches
```bash
# Update develop
git checkout develop
git pull origin develop

# Update main
git checkout main
git pull origin main
```

### Create Backup
```bash
# Before major changes
git checkout develop
git checkout -b backup/pre-changes-$(date +%Y-%m-%d-%H%M)
git push -u origin backup/pre-changes-$(date +%Y-%m-%d-%H%M)
```

### Merge Process
```bash
# After PR approval
git checkout develop
git pull origin develop
git merge --no-ff feature/[ticket]-[description]
git push origin develop
```

## Troubleshooting

### Merge Conflicts
```bash
# Update target branch
git checkout develop
git pull origin develop

# Merge into feature branch
git checkout feature/[ticket]-[description]
git merge develop

# Resolve conflicts
# ... edit conflicted files ...
git add .
git commit -m "Merge develop into feature branch"
```

### Revert Changes
```bash
# Revert last commit
git revert HEAD

# Revert specific commit
git revert <commit-hash>
```

### Delete Merged Branches
```bash
# After merge to develop
git branch -d feature/[ticket]-[description]
git push origin --delete feature/[ticket]-[description]
```

## Best Practices

1. **Keep features small** - One feature per branch
2. **Write clear commits** - Follow commit message format
3. **Review before merge** - Always get PR reviews
4. **Test thoroughly** - Run all checks before merging
5. **Delete after merge** - Clean up merged branches
6. **Use backups** - Create backup branches before major changes
7. **Follow naming** - Use consistent branch naming
8. **Update regularly** - Keep branches synced with develop

## Resources

- [GitFlow Workflow Guide](gitflow-workflow.md)
- [GitHub Actions Documentation](github-actions.md)
- [Project README](../../README.md)
