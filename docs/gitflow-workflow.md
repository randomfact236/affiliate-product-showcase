# GitFlow Workflow Guide

## Overview

GitFlow is a branching model for Git that provides a robust framework for managing feature development, releases, and hotfixes. This guide outlines the GitFlow workflow for the Affiliate Product Showcase WordPress Plugin.

## Branch Structure

### Main Branches

   - `main` (Production)
- **Purpose**: Production-ready code
- **Protection**: Highly protected, direct commits disabled
- **Merge sources**: `release/*` branches, `hotfix/*` branches
- **Deployment**: Automatically deployed to production via GitHub Actions
- **Stability**: Always stable and deployable

   - `develop` (Development)
- **Purpose**: Integration branch for features
- **Protection**: Protected, direct commits disabled
- **Merge sources**: `feature/*` branches
- **Deployment**: Automatically deployed to staging via GitHub Actions
- **Stability**: Should always be in a releasable state

### Supporting Branches

   - `feature/*` (Feature Development)
- **Purpose**: Develop new features
- **Branch from**: `develop`
- **Merge into**: `develop`
- **Naming**: `feature/[ticket-number]-[description]`
- **Examples**:
  - `feature/2.4-psr3-logging`
  - `feature/2.6-health-check`
  - `feature/3.1-admin-dashboard`

   - `release/*` (Release Preparation)
- **Purpose**: Prepare for production release
- **Branch from**: `develop`
- **Merge into**: `main` and `develop`
- **Naming**: `release/[version]`
- **Examples**:
  - `release/1.0.0`
  - `release/2.0.0`
- **Activities**: Version bumping, changelog updates, final testing

   - `hotfix/*` (Emergency Fixes)
- **Purpose**: Fix critical production bugs
- **Branch from**: `main`
- **Merge into**: `main` and `develop`
- **Naming**: `hotfix/[version]`
- **Examples**:
  - `hotfix/1.0.1`
  - `hotfix/1.0.2`
- **Activities**: Emergency bug fixes, security patches

## Workflow Diagram

```
                    main (production)
                   /                \
                  /                  \
            release/1.0.0        hotfix/1.0.1
            /         \           /         \
           /           \         /           \
      develop          \       /          develop
      /     \           \     /           /     \
     /       \           \   /           /       \
feature/1    feature/2    \ /       feature/3    feature/4
```

## Daily Workflow

### Starting a New Feature

1. **Ensure you're on develop branch**
   ```bash
   git checkout develop
   git pull origin develop
   ```

2. **Create feature branch**
   ```bash
   git checkout -b feature/[ticket-number]-[description]
   ```
   Example:
   ```bash
   git checkout -b feature/2.8-add-product-comparison
   ```

3. **Develop your feature**
   - Make commits frequently
   - Write clear commit messages
   - Keep feature focused and small

4. **Push to remote**
   ```bash
   git push -u origin feature/[ticket-number]-[description]
   ```

5. **Create Pull Request**
   - Target: `develop` branch
   - Title: `feature/[ticket-number]-[description]`
   - Description: What was changed and why
   - Assign reviewers

6. **Code Review**
   - Address review comments
   - Ensure all checks pass (CI, tests, linting)
   - Update PR description if needed

7. **Merge to develop**
   - Squash and merge for clean history
   - Delete feature branch after merge

### Creating a Release

1. **Ensure develop is ready**
   ```bash
   git checkout develop
   git pull origin develop
   ```

2. **Create release branch**
   ```bash
   git checkout -b release/[version]
   ```
   Example:
   ```bash
   git checkout -b release/1.0.0
   ```

3. **Prepare release**
   - Update version in `affiliate-product-showcase.php`
   - Update `CHANGELOG.md`
   - Run final tests
   - Update documentation

4. **Push to remote**
   ```bash
   git push -u origin release/[version]
   ```

5. **Create Pull Request**
   - Target: `main` branch
   - Title: `release/[version]`
   - Description: Release notes and changes

6. **Test on staging**
   - GitHub Actions will deploy to staging
   - Perform manual testing
   - Verify all features work

7. **Merge to main**
   - Create GitHub release
   - Tag the commit: `v[version]`
   - Merge to main
   - Merge back to develop

### Hotfix Process

1. **Create hotfix branch from main**
   ```bash
   git checkout main
   git pull origin main
   git checkout -b hotfix/[version]
   ```
   Example:
   ```bash
   git checkout -b hotfix/1.0.1
   ```

2. **Fix the issue**
   - Make minimal changes
   - Focus on the critical bug only
   - Write clear commit messages

3. **Push to remote**
   ```bash
   git push -u origin hotfix/[version]
   ```

4. **Create Pull Request**
   - Target: `main` branch
   - Title: `hotfix/[version]`
   - Description: What bug is being fixed

5. **Test and merge**
   - All checks must pass
   - Quick review and approval
   - Merge to main
   - Merge back to develop
   - Create GitHub release

## GitHub Actions Integration

### Automatic Deployments

| Event | Action | Environment |
|-------|--------|-------------|
| Push to `main` | Deploy to production | Production |
| Push to `develop` | Deploy to staging | Staging |
| PR to `main` | Deploy to staging for review | Staging |
| Tag push (`v*`) | Deploy to WordPress.org + GitHub Release | Production |

### Branch Protection Rules

   - `main` Branch
- ✅ Require pull request reviews
- ✅ Require status checks to pass
- ✅ Require branches to be up to date
- ✅ Require linear history
- ✅ Restrict direct pushes
- ✅ Require conversation resolution

   - `develop` Branch
- ✅ Require pull request reviews
- ✅ Require status checks to pass
- ✅ Require branches to be up to date
- ✅ Restrict direct pushes
- ✅ Require conversation resolution

## Commit Message Format

### Standard Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style (formatting, semicolons, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Build process, dependencies, etc.
- `security`: Security fixes
- `performance`: Performance improvements

### Examples

**Feature:**
```
feat(auth): add JWT token authentication

Implement JWT-based authentication for REST API endpoints.
Tokens expire after 24 hours and can be refreshed.

Closes #123
```

**Bug Fix:**
```
fix(api): fix product query pagination

Fixed issue where pagination was returning incorrect results
when filtering by category.

Closes #456
```

**Hotfix:**
```
hotfix(security): sanitize affiliate URL output

Prevent XSS vulnerability in affiliate product display.
Added esc_url() to all affiliate URL outputs.

Security issue reported by @researcher
```

## Release Process

### Version Numbering

Follow Semantic Versioning: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes (incompatible API changes)
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Checklist

- [ ] Update version in `affiliate-product-showcase.php`
- [ ] Update `CHANGELOG.md` with release notes
- [ ] Run full test suite
- [ ] Run security audit
- [ ] Update documentation if needed
- [ ] Create release branch
- [ ] Test on staging environment
- [ ] Merge to main
- [ ] Create GitHub release
- [ ] Deploy to WordPress.org (if applicable)
- [ ] Announce release

## Common Workflows

### Workflow 1: Feature Development

```bash
# 1. Start feature
git checkout develop
git pull origin develop
git checkout -b feature/2.8-add-comparison

# 2. Develop
# ... make changes ...
git add .
git commit -m "feat(comparison): add product comparison feature"

# 3. Push and create PR
git push -u origin feature/2.8-add-comparison
# Create PR on GitHub

# 4. After approval, merge to develop
# (via GitHub UI - squash and merge)
```

### Workflow 2: Release

```bash
# 1. Prepare release
git checkout develop
git pull origin develop
git checkout -b release/1.0.0

# 2. Update version and changelog
# ... edit files ...
git add .
git commit -m "chore(release): prepare version 1.0.0"

# 3. Push and create PR
git push -u origin release/1.0.0
# Create PR to main

#
