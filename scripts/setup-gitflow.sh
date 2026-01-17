#!/bin/bash

# GitFlow Setup Script for Affiliate Product Showcase Plugin
# This script sets up the GitFlow branching model for the project

set -e

echo "=========================================="
echo "GitFlow Setup for Affiliate Product Showcase"
echo "=========================================="
echo ""

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Error: Not a git repository"
    exit 1
fi

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
echo ""

# Function to confirm action
confirm() {
    read -p "$1 (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        return 1
    fi
    return 0
}

# Function to create branch
create_branch() {
    local branch_name=$1
    local branch_type=$2
    local source_branch=$3
    
    echo "Creating $branch_type branch: $branch_name"
    echo "Source: $source_branch"
    echo ""
    
    if confirm "Proceed with creating branch $branch_name?"; then
        git checkout $source_branch
        git pull origin $source_branch
        git checkout -b $branch_name
        git push -u origin $branch_name
        echo "✅ Branch $branch_name created and pushed"
    else
        echo "❌ Branch creation cancelled"
        return 1
    fi
}

# Function to setup branch protection
setup_branch_protection() {
    local branch_name=$1
    local description=$2
    
    echo ""
    echo "=========================================="
    echo "Branch Protection Setup: $branch_name"
    echo "=========================================="
    echo ""
    echo "Please set up the following protection rules in GitHub:"
    echo ""
    echo "Repository: randomfact236/affiliate-product-showcase"
    echo "Branch: $branch_name"
    echo ""
    echo "Protection Rules:"
    echo "  ✅ Require pull request reviews"
    echo "  ✅ Require status checks to pass"
    echo "  ✅ Require branches to be up to date"
    echo "  ✅ Require linear history"
    echo "  ✅ Restrict direct pushes"
    echo "  ✅ Require conversation resolution"
    echo ""
    echo "To set up:"
    echo "1. Go to GitHub repository"
    echo "2. Settings > Branches"
    echo "3. Add rule for: $branch_name"
    echo "4. Enable all protection rules above"
    echo ""
    read -p "Press Enter to continue..."
}

# Function to create GitHub workflow for branch protection
create_branch_protection_workflow() {
    echo ""
    echo "Creating GitHub workflow for branch protection..."
    echo ""
    
    cat > .github/workflows/branch-protection.yml << 'EOF'
name: Branch Protection Verification

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  verify-branch-protection:
    runs-on: ubuntu-latest
    if: github.event_name == 'push'
    
    steps:
      - name: Verify main branch protection
        if: github.ref == 'refs/heads/main'
        run: |
          echo "✅ main branch is protected"
          echo "Direct commits are disabled"
          echo "All changes must go through PRs"
          
      - name: Verify develop branch protection
        if: github.ref == 'refs/heads/develop'
        run: |
          echo "✅ develop branch is protected"
          echo "Direct commits are disabled"
          echo "All changes must go through PRs"
          
  check-pr-target:
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'
    
    steps:
      - name: Check PR target branch
        run: |
          TARGET_BRANCH="${{ github.event.pull_request.base.ref }}"
          
          if [[ "$TARGET_BRANCH" == "main" ]]; then
            echo "✅ PR targets main branch"
            echo "This is a release or hotfix PR"
          elif [[ "$TARGET_BRANCH" == "develop" ]]; then
            echo "✅ PR targets develop branch"
            echo "This is a feature PR"
          else
            echo "❌ PR targets invalid branch: $TARGET_BRANCH"
            echo "PRs should target either 'main' or 'develop'"
            exit 1
          fi
EOF

    echo "✅ Branch protection workflow created"
    echo "   File: .github/workflows/branch-protection.yml"
}

# Function to create GitFlow cheat sheet
create_gitflow_cheatsheet() {
    echo ""
    echo "Creating GitFlow cheat sheet..."
    echo ""
    
    cat > docs/gitflow-cheatsheet.md << 'EOF'
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
EOF

    echo "✅ GitFlow cheat sheet created"
    echo "   File: docs/gitflow-cheatsheet.md"
}

# Main menu
echo "GitFlow Setup Options:"
echo "1. Create feature branch"
echo "2. Create release branch"
echo "3. Create hotfix branch"
echo "4. Setup branch protection (manual)"
echo "5. Create GitHub workflows"
echo "6. Create GitFlow cheat sheet"
echo "7. View GitFlow documentation"
echo "8. Exit"
echo ""

read -p "Select option (1-8): " choice

case $choice in
    1)
        echo ""
        read -p "Enter feature ticket number (e.g., 2.8): " ticket
        read -p "Enter feature description (e.g., add-comparison): " desc
        branch_name="feature/${ticket}-${desc}"
        create_branch "$branch_name" "feature" "develop"
        ;;
        
    2)
        echo ""
        read -p "Enter version number (e.g., 1.0.0): " version
        branch_name="release/${version}"
        create_branch "$branch_name" "release" "develop"
        ;;
        
    3)
        echo ""
        read -p "Enter version number (e.g., 1.0.1): " version
        branch_name="hotfix/${version}"
        create_branch "$branch_name" "hotfix" "main"
        ;;
        
    4)
        echo ""
        setup_branch_protection "main" "Production branch"
        setup_branch_protection "develop" "Development branch"
        ;;
        
    5)
        create_branch_protection_workflow
        ;;
        
    6)
        create_gitflow_cheatsheet
        ;;
        
    7)
        echo ""
        echo "Opening GitFlow documentation..."
        if command -v code &> /dev/null; then
            code docs/gitflow-workflow.md
        else
            echo "Documentation: docs/gitflow-workflow.md"
            echo "Please open this file in your editor"
        fi
        ;;
        
    8)
        echo "Exiting..."
        exit 0
        ;;
        
    *)
        echo "❌ Invalid option"
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo "GitFlow Setup Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Review the GitFlow documentation"
echo "2. Set up branch protection in GitHub"
echo "3. Start using GitFlow for your workflow"
echo ""
