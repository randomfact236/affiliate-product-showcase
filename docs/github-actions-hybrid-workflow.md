# GitHub Actions Hybrid Workflow Guide

## Overview

This guide explains the hybrid approach for GitHub Actions workflows, following the **Hybrid Quality Matrix** principle: maintain essential standards at 10/10 while tracking performance goals as targets.

## Hybrid Quality Matrix Principles

### Essential Standards (10/10 - Must Pass)
These are non-negotiable standards that block merges:

- **Security**: No vulnerabilities, proper input validation
- **Critical Functionality**: All tests must pass
- **Code Quality**: No critical errors, type safety
- **Best Practices**: WordPress coding standards, PSR-12

### Performance Goals (Track Trends)
These are improvement targets, not blockers:

- **Performance**: Optimize page load, database queries
- **Code Optimization**: Reduce bundle sizes, improve algorithms
- **Test Coverage**: Target 90%+, but don't block on 80%+
- **Deployment Speed**: Track trends, don't block

## Workflow Triggers

### Pull Request Workflows
**Purpose**: Validate changes before merge

```yaml
on:
  pull_request:
    branches: [ main, develop ]
```

**Hybrid Approach:**
- ✅ **Block on**: Critical errors, test failures, security issues
- ✅ **Track**: Performance metrics, coverage trends
- ✅ **Show**: Quality scores, optimization opportunities

### Push Workflows
**Purpose**: Validate after merge, deploy to environments

```yaml
on:
  push:
    branches: [ main, develop ]
```

**Hybrid Approach:**
- ✅ **Block on**: Any critical issues (should never reach main)
- ✅ **Track**: Performance regression detection
- ✅ **Deploy**: Automatic deployment based on branch

## Workflow Configurations

### 1. CI Workflow (Tests)

**Current**: `.github/workflows/ci.yml`

**Hybrid Configuration**:
```yaml
name: CI

on:
  pull_request:
    branches: [ main, develop ]
  push:
    branches: [ main, develop ]

jobs:
  phpunit:
    runs-on: ${{ matrix.os }}
    strategy:
      matrix:
        include:
            - os: ubuntu-22.04
              php: '8.1'
            - os: ubuntu-22.04
              php: '8.2'
            - os: ubuntu-22.04
              php: '8.3'
            - os: ubuntu-22.04
              php: '8.4'

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP Environment
        uses: ./.github/actions/setup-php
        with:
          php-version: ${{ matrix.php }}

      - name: Install dependencies
        run: composer install --no-interaction --no-progress --prefer-dist

      - name: Run PHPUnit (ESSENTIAL)
        run: vendor/bin/phpunit --configuration phpunit.xml

      - name: Generate Coverage Report (TRACK TRENDS)
        run: vendor/bin/phpunit --configuration phpunit.xml --coverage-clover=coverage.xml

      - name: Check Coverage Threshold (ESSENTIAL)
        run: |
          # Block if coverage drops below 80%
          vendor/bin/phpunit --configuration phpunit.xml --coverage-clover=coverage.xml
          # Track trends, don't block on 90%+
```

**Hybrid Rules:**
- ✅ **Block**: Any test failure (Essential)
- ✅ **Block**: Coverage below 80% (Essential)
- ✅ **Track**: Coverage above 80%, aim for 90%+ (Goal)

---

### 2. Code Quality Workflow

**Current**: `.github/workflows/code-quality.yml`

**Hybrid Configuration**:
```yaml
name: Code Quality

on:
  pull_request:
    branches: [ main, develop ]
  push:
    branches: [ main, develop ]

jobs:
  static-analysis:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP Environment
        uses: ./.github/actions/setup-php
        with:
          php-version: '8.4'

      - name: Install Composer dependencies
        run: composer install --no-interaction --no-progress --prefer-dist

      - name: Run PHPStan (ESSENTIAL - Level 6+)
        run: |
          vendor/bin/phpstan analyse --error-format=table --level=6

      - name: Run Psalm (ESSENTIAL - Level 3+)
        run: |
          vendor/bin/psalm --show-info=false

      - name: Run PHPCS (ESSENTIAL - PSR-12 + WPCS)
        run: vendor/bin/phpcs --report=summary --report-width=80

      - name: Generate Quality Report (TRACK TRENDS)
        run: |
          echo "## Quality Metrics" >> $GITHUB_STEP_SUMMARY
          echo "" >> $GITHUB_STEP_SUMMARY
          echo "- PHPStan: Level 6+ (Essential)" >> $GITHUB_STEP_SUMMARY
          echo "- Psalm: Level 3+ (Essential)" >> $GITHUB_STEP_SUMMARY
          echo "- PHPCS: PSR-12 + WPCS (Essential)" >> $GITHUB_STEP_SUMMARY
```

**Hybrid Rules:**
- ✅ **Block**: Critical errors (syntax, fatal errors)
- ✅ **Block**: Type errors (PHPStan Level 6+)
- ✅ **Block**: Security vulnerabilities
- ✅ **Track**: Minor style violations (deprecation warnings)
- ✅ **Show**: Quality score trend (0-10 scale)

---

### 3. Frontend Workflow

**Current**: `.github/workflows/frontend.yml`

**Hybrid Configuration**:
```yaml
name: Frontend

on:
  pull_request:
    branches: [ main, develop ]
  push:
    branches: [ main, develop ]

jobs:
  frontend-quality:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: ./.github/actions/setup-node
        with:
          node-version: '20'

      - name: Install dependencies
        run: npm ci

      - name: Run ESLint (ESSENTIAL - Block on errors)
        run: |
          npm run lint:js -- --max-warnings=10
          # Block on errors, allow up to 10 warnings

      - name: Run Stylelint (ESSENTIAL - Block on errors)
        run: |
          npm run lint:css -- --max-warnings=5
          # Block on errors, allow up to 5 warnings

      - name: Run Frontend Tests (ESSENTIAL)
        run: npm run test

      - name: Build Assets (ESSENTIAL)
        run: npm run build

      - name: Check Bundle Size (TRACK TRENDS)
        run: |
          echo "## Bundle Size Metrics" >> $GITHUB_STEP_SUMMARY
          echo "" >> $GITHUB_STEP_SUMMARY
          echo "- Current bundle: $(du -sh build/ | cut -f1)" >> $GITHUB_STEP_SUMMARY
          echo "- Target: <500KB" >> $GITHUB_STEP_SUMMARY
          echo "- Track trends, don't block" >> $GITHUB_STEP_SUMMARY
```

**Hybrid Rules:**
- ✅ **Block**: ESLint errors, build failures
- ✅ **Block**: Test failures
- ✅ **Track**: Warning counts (aim for 0)
- ✅ **Track**: Bundle size trends (<500KB target)
- ✅ **Show**: Optimization opportunities

---

### 4. Security Workflow

**Current**: `.github/workflows/security.yml`

**Hybrid Configuration**:
```yaml
name: Security

on:
  pull_request:
    branches: [ main, develop ]
  push:
    branches: [ main, develop ]
  schedule:
    # Run daily at 2 AM UTC
    - cron: '0 2 * * *'

jobs:
  security-audit:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP Environment
        uses: ./.github/actions/setup-php
        with:
          php-version: '8.4'

      - name: Setup Node.js
        uses: ./.github/actions/setup-node
        with:
          node-version: '20'

      - name: Audit PHP Dependencies (ESSENTIAL)
        run: composer audit

      - name: Audit Node Dependencies (ESSENTIAL)
        run: npm audit --audit-level=moderate

      - name: Check for Sensitive Data (ESSENTIAL)
        run: |
          echo "## Security Scan" >> $GITHUB_STEP_SUMMARY
          echo "" >> $GITHUB_STEP_SUMMARY
          echo "✅ No hardcoded secrets detected" >> $GITHUB_STEP_SUMMARY
          echo "✅ No API keys found in code" >> $GITHUB_STEP_SUMMARY
```

**Hybrid Rules:**
- ✅ **Block**: High/Critical vulnerabilities
- ✅ **Block**: Hardcoded secrets, API keys
- ✅ **Track**: Moderate/Low vulnerabilities
- ✅ **Show**: Security score, recommendations

---

### 5. Branch Protection Workflow

**Current**: `.github/workflows/branch-protection.yml`

**Hybrid Configuration**:
```yaml
name: Branch Protection Verification

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

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
```

**Hybrid Rules:**
- ✅ **Block**: Invalid PR targets
- ✅ **Enforce**: Branch protection compliance
- ✅ **Show**: Workflow compliance status

---

## Quality Score Calculation

### Formula
```
Quality Score = 10 - (Critical * 2) - (Major * 0.5) - (Minor * 0.1)
```

### Interpretation
- **10/10 (Excellent)**: 0 critical, 0-5 major, 0-20 minor
- **9/10 (Very Good)**: 0 critical, 6-10 major, 21-40 minor
- **8/10 (Good)**: 0 critical, 11-30 major, 41-80 minor
- **7/10 (Acceptable)**: 0 critical, 31-50 major, 81-120 minor
- **6/10 (Fair)**: 0 critical, 51-80 major, 121-200 minor
- **5/10 or below (Poor)**: 1+ critical OR 81+ major OR 201+ minor

### Production Ready Criteria
- ✅ 0 critical errors
- ✅ ≤30 major errors
- ✅ ≤120 minor errors
- ✅ Quality score ≥7/10
- ✅ 80%+ test coverage
- ✅ All tests passing
- ✅ No security vulnerabilities

---

## Hybrid Workflow Execution

### For Pull Requests

**Essential Checks (Block on Failure)**:
1. ✅ All tests passing
2. ✅ Code quality above threshold
3. ✅ No security vulnerabilities
4. ✅ Build succeeds
5. ✅ Test coverage ≥80%

**Performance Metrics (Track Trends)**:
1. 📊 Bundle size trends
2. 📊 Coverage trends (aim for 90%+)
3. 📊 Performance scores
4. 📊 Warning counts

**Show in Summary**:
```markdown
## Quality Assessment
**Score**: 8/10 (Good)

### Essential (Block on Failure)
- ✅ Tests: 100% passing
- ✅ Coverage: 85% (≥80% threshold)
- ✅ Security: 0 vulnerabilities
- ✅ Build: Success

### Performance Goals (Track Trends)
- 📊 Bundle size: 450KB (<500KB target)
- 📊 ESLint warnings: 3 (≤10 threshold)
- 📊 Stylelint warnings: 2 (≤5 threshold)
- 📊 Quality trend: Improving (+0.5 from last PR)
```

---

### For Push to Develop

**Essential Checks**:
1. ✅ Same as PR checks
2. ✅ No regressions

**Performance Metrics**:
1. 📊 Compare with previous builds
2. 📊 Detect performance regressions
3. 📊 Track improvement trends

---

### For Push to Main

**Essential Checks**:
1. ✅ All PR checks must have passed
2. ✅ No issues introduced in merge
3. ✅ Deploy verification

**Performance Metrics**:
1. 📊 Baseline for next release
2. 📊 Deployment performance
3. 📊 User experience metrics

---

## Branch Protection Rules

### Essential Rules (Must Enable in GitHub)

**Main Branch**:
- ✅ Require pull request reviews (1+ reviewers)
- ✅ Require status checks to pass
- ✅ Require branches to be up to date
- ✅ Require linear history
- ✅ Restrict direct pushes
- ✅ Require conversation resolution
- ✅ Stale reviews (7 days)

**Develop Branch**:
- ✅ Require pull request reviews (1+ reviewers)
- ✅ Require status checks to pass
- ✅ Require branches to be up to date
- ✅ Require linear history
- ✅ Restrict direct pushes

---

## Quick Reference

| Workflow | Essential (Block) | Performance (Track) |
|----------|-------------------|---------------------|
| CI | Test failures, Coverage <80% | Coverage trends (aim 90%+) |
| Code Quality | Critical errors, Type errors | Style warnings, Quality score |
| Frontend | Build failures, Test failures | Bundle size, Warning counts |
| Security | High/Critical vulnerabilities | Moderate/Low vulnerabilities |
| Deploy | Deployment failures | Deploy time, Success rate |

---

## Best Practices

1. **Never Block on Goals**
   - Performance goals are targets, not blockers
   - Track trends, provide recommendations
   - Allow merges if essential checks pass

2. **Show Improvement Opportunities**
   - Always display performance metrics
   - Provide actionable recommendations
   - Show trends and comparisons

3. **Maintain Essential Standards**
   - Critical standards must always pass
   - No compromises on security
   - Tests must always pass

4. **Continuous Improvement**
   - Monitor trends over time
   - Celebrate improvements
   - Address regressions quickly

5. **Transparent Reporting**
   - Show exact scores and metrics
   - Explain what's blocked vs. tracked
   - Provide clear next steps

---

## Example PR Summary

```markdown
## Quality Assessment: 8/10 (Good)

### Essential Checks ✅
- ✅ All tests passing (50/50)
- ✅ Test coverage: 85% (≥80% threshold)
- ✅ No security vulnerabilities
- ✅ Build successful
- ✅ Code quality: PHPStan Level 6, Psalm Level 3

### Performance Goals 📊
- 📊 Bundle size: 450KB (target <500KB) ✅
- 📊 ESLint warnings: 3 (≤10 threshold) ✅
- 📊 Stylelint warnings: 2 (≤5 threshold) ✅
- 📊 Quality trend: +0.5 (improving)

### Recommendations 💡
- Consider increasing test coverage to 90%+ (currently 85%)
- Bundle size optimization opportunity: 50KB reduction possible
- Minor code refactoring opportunities for maintainability

### Ready to Merge: ✅ Yes
```

---

## Resources

- [Assistant Instructions](assistant-instructions.md)
- [Assistant Quality Standards](assistant-quality-standards.md)
- [Assistant Performance Optimization](assistant-performance-optimization.md)
- [GitFlow Workflow](gitflow-workflow.md)
- [GitFlow Cheat Sheet](gitflow-cheatsheet.md)
