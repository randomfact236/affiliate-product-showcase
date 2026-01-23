# Git Standards Guide

## 📋 Purpose

This guide defines **Git standards and best practices** for version control in this project.

**Standard:** Professional Git workflow
**Philosophy:** Clean commits, clear history, automated safety

---

## 📊 Version Control Strategy

### Branch Naming Convention

```bash
# Feature branches
feature/product-original-price    # New features

# Bug fix branches
fix/auth-nonce-verification      # Bug fixes

# Hotfix branches
hotfix/security-csrf-issue        # Critical hotfixes

# Refactor branches
refactor/caching-service          # Code refactoring

# Release branches
release/v1.0.0                  # Release preparation
```

### Workflow Structure

```bash
main                            # Production-ready code only
├── develop                      # Integration branch
│   ├── feature/*                # Feature branches
│   ├── fix/*                   # Bug fix branches
│   └── hotfix/*               # Hotfix branches (merge to main & develop)
└── release/*                    # Release branches
```

### Git Flow Workflow

**Requirements:**
- ✅ Git Flow workflow or similar
- ✅ Feature branches from develop
- ✅ Pull requests required for all changes
- ✅ At least 1 approval required for merge
- ✅ All tests must pass before merge
- ✅ No direct commits to main branch
- ✅ Semantic versioning (MAJOR.MINOR.PATCH)
- ✅ Tag releases with version numbers

---

## 📝 Commit Message Standards

### Conventional Commits Format

```bash
✅ CORRECT: Conventional Commits

feat(product): Add original_price field
- Add property to Product model
- Update ProductFactory to handle original_price
- Add migration script for database

fix(authentication): Fix nonce verification on login
- Add missing nonce check
- Return proper error message
- Add test for nonce failure

docs(readme): Update installation instructions
- Clarify PHP version requirement
- Add Docker installation steps
- Update screenshots

refactor(services): Extract caching logic to CacheService
- Create dedicated CacheService class
- Move cache operations from ProductService
- Add cache invalidation methods

test(products): Add integration tests for API
- Test all CRUD operations
- Test error handling
- Test authentication

style(coding): Apply coding standards (WPCS/PSR-12)
- Fix indentation
- Remove trailing whitespace
- Add missing type hints

chore(deps): Update WordPress to 6.4
```

### Commit Message Requirements

- ✅ Use conventional commits
- ✅ Subject line < 50 characters
- ✅ Imperative mood (Add, Fix, Update)
- ✅ Body explains what and why
- ✅ Reference issues if applicable
- ✅ No typos or grammar errors

### Commit Message Template

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Maintenance tasks
- `perf`: Performance improvements
- `ci`: CI/CD changes

**Scope:**
- Specific area affected (e.g., product, auth, ui)
- Optional but recommended

---

## 🔀 Pull Request Standards

### PR Description Template

```markdown
✅ CORRECT: PR Description

## Description
Adds support for original_price field to products, allowing display of sale prices and discounts.

## Changes
- Added `original_price` property to Product model
- Updated ProductFactory to handle original_price in both from_post() and from_array()
- Added migration script for database updates
- Updated API to include original_price in responses
- Added discount percentage calculation

## Testing
- Unit tests for Product model with original_price
- Integration tests for API responses
- Manual testing with products having/missing original_price

## Screenshots
[Screenshot of product grid showing original price with discount]

## Checklist
- [x] Code follows project coding standards (WPCS/PSR-12)
- [x] All tests passing
- [x] Documentation updated
- [x] No breaking changes
- [x] Performance tested
```

### PR Requirements

**Description:**
- ✅ Clear description of changes
- ✅ List of all changes
- ✅ Testing information
- ✅ Screenshots for UI changes
- ✅ Checklist completed
- ✅ Link to related issues

### PR Size Limits

```yaml
# GitHub PR size limits enforced via .github/pr-size-limit.yml
pr_size_limit:
  max_added_lines: 400
  max_deleted_lines: 400
  max_changed_files: 15
  ignore_files:
    - "package-lock.json"
    - "composer.lock"
    - "*.min.js"
    - "*.min.css"
```

**Requirements:**
- ✅ PR must not exceed 400 added lines
- ✅ PR must not exceed 400 deleted lines
- ✅ PR must not exceed 15 changed files
- ✅ Large changes must be split into multiple PRs
- ✅ Each PR should focus on a single feature/fix
- ✅ Automated size check in CI

---

## 👀 Code Review Checklist

```markdown
## Code Review Checklist

### Code Quality
- [ ] Code follows project coding standards (WPCS/PSR-12)
- [ ] All type hints present and correct
- [ ] PHPDoc complete for public methods
- [ ] No console errors or warnings
- [ ] No PHP warnings/errors
- [ ] Static analysis passes (Psalm level 4-5)
- [ ] Code is DRY and follows SOLID principles
- [ ] No code duplication
- [ ] Functions/methods are concise (< 20-30 lines)

### Functionality
- [ ] Feature works as expected
- [ ] Edge cases handled properly
- [ ] Error handling in place
- [ ] Tested manually if applicable
- [ ] Requirements fully implemented
- [ ] Backward compatibility maintained (if needed)

### Security
- [ ] All input validated
- [ ] All output escaped
- [ ] SQL queries use prepared statements
- [ ] Nonces verified for state-changing actions
- [ ] CSRF protection in place
- [ ] No sensitive data exposed
- [ ] Security headers configured

### Performance
- [ ] No obvious bottlenecks
- [ ] Images optimized
- [ ] Caching implemented where appropriate
- [ ] Database queries optimized
- [ ] N+1 query problems avoided
- [ ] Bundle size within limits

### Accessibility
- [ ] Semantic HTML used
- [ ] Keyboard navigable
- [ ] Alt text on images
- [ ] ARIA labels present where needed
- [ ] Color contrast sufficient (4.5:1 minimum)
- [ ] Focus indicators visible

### Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] E2E tests pass (if applicable)
- [ ] Test coverage minimum 90%
- [ ] Tests cover edge cases
- [ ] Tests are maintainable

### Documentation
- [ ] Code documented (PHPDoc/JSDoc)
- [ ] README updated (if needed)
- [ ] API docs updated (if applicable)
- [ ] Changelog updated
- [ ] Inline comments explain "why", not "what"
```

### Review Requirements

- ✅ All checklist items must be completed
- ✅ At least one approval required for merge
- ✅ Reviewer must verify all items
- ✅ Blocked items must be addressed before merge
- ✅ Reviewer should provide constructive feedback
- ✅ Author should respond to all comments

---

## 🔄 CI/CD Integration

### Automated Checks

```yaml
# .github/workflows/ci.yml example
name: CI
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run Psalm
        run: vendor/bin/psalm --level=4
      - name: Run tests
        run: vendor/bin/phpunit --coverage
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

**Requirements:**
- ✅ Automated testing on every commit
- ✅ Static analysis (Psalm, PHPStan) in CI
- ✅ Code quality checks (PHPCS) in CI
- ✅ Security scanning (Snyk, Dependabot)
- ✅ Test coverage reporting (Codecov)
- ✅ Automated deployment on merge to main
- ✅ Rollback capability
- ✅ Environment separation (dev/staging/prod)

---

## 📋 Pre-Commit Checklist

Before committing any code, verify:

### Code Quality
- [ ] Code follows project coding standards (WPCS/PSR-12)
- [ ] All type hints present
- [ ] PHPDoc complete for public methods
- [ ] No console errors
- [ ] No PHP warnings/errors
- [ ] Static analysis passes (Psalm level 4-5)

### Functionality
- [ ] Feature works as expected
- [ ] Edge cases handled
- [ ] Error handling in place
- [ ] Tested manually

### Security
- [ ] All input validated
- [ ] All output escaped
- [ ] SQL queries prepared
- [ ] Nonces verified
- [ ] CSRF protection in place

### Performance
- [ ] No obvious bottlenecks
- [ ] Images optimized
- [ ] Caching implemented
- [ ] Database queries optimized

### Accessibility
- [ ] Semantic HTML
- [ ] Keyboard navigable
- [ ] Alt text on images
- [ ] ARIA labels present
- [ ] Color contrast sufficient

### Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] E2E tests pass (if applicable)
- [ ] Test coverage minimum 90%

### Documentation
- [ ] Code documented
- [ ] README updated (if needed)
- [ ] API docs updated (if applicable)
- [ ] Changelog updated

---

## 🎯 Summary

**For every Git operation:**
1. **Follow Git Flow** - Use proper branching strategy
2. **Write clean commits** - Conventional commits with clear messages
3. **Create focused PRs** - One feature/fix per PR
4. **Review thoroughly** - Use checklist for comprehensive review
5. **Automate checks** - CI/CD ensures quality
6. **Tag releases** - Semantic versioning for traceability

**The reward:** Clean, maintainable, and traceable version history.

---

**Version:** 1.0.0
**Last Updated:** 2026-01-23
**Maintained By:** Development Team
**Status:** ACTIVE - Extracted from assistant-quality-standards.md (~300 lines)
