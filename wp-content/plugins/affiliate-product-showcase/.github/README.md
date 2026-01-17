# GitHub Actions Workflows

This directory contains all GitHub Actions workflows for the Affiliate Product Showcase WordPress plugin.

## Quick Reference

### Workflows Overview

| Workflow | File | Purpose | Triggers |
|----------|------|---------|----------|
| **CI** | `ci.yml` | Basic continuous integration | Push/PR to main/develop |
| **Code Quality** | `code-quality.yml` | Comprehensive code analysis | Push/PR + Manual |
| **Testing** | `testing.yml` | Full test suite with coverage | Push/PR + Manual |
| **Security Scan** | `security-scan.yml` | Security vulnerability scanning | Push/PR + Daily + Manual |
| **Deployment** | `deployment.yml` | Build & deploy plugin | Push to main + Tags + Manual |

### Quick Commands

```bash
# Run all checks locally (from plugin directory)
cd wp-content/plugins/affiliate-product-showcase

# Code quality
composer phpstan
composer psalm
composer phpcs
npm run lint:js
npm run lint:css

# Testing
composer test
npm run test

# Security
composer audit
npm audit

# Build
npm run build
```

## Workflow Details

### 1. CI Workflow (ci.yml)

**What it does:**
- Runs PHPCS (code style)
- Runs PHPStan (static analysis)
- Runs Psalm (type checking)
- Runs PHPUnit (unit tests)
- Runs ESLint (JavaScript linting)
- Runs Stylelint (CSS linting)
- Verifies build process
- Posts summary to PR

**When it runs:**
- Every push to `main` or `develop`
- Every pull request to `main` or `develop`

**Artifacts:**
- `ci-summary.md` - Summary report

### 2. Code Quality Workflow (code-quality.yml)

**What it does:**
- Comprehensive static analysis
- Type checking with multiple tools
- Code style enforcement
- Quality score calculation
- Detailed JSON reports

**When it runs:**
- Every push to `main` or `develop`
- Every pull request to `main` or `develop`
- Manual trigger (GitHub UI)

**Artifacts:**
- `phpstan-report.json`
- `psalm-report.json`
- `phpcs-report.json`
- `eslint-report.json`
- `stylelint-report.json`
- `quality-summary.md`

### 3. Testing Workflow (testing.yml)

**What it does:**
- PHPUnit with code coverage
- HTML coverage reports
- Frontend tests
- Integration tests with MySQL
- Mutation testing (Infection)
- Test summary generation

**When it runs:**
- Every push to `main` or `develop`
- Every pull request to `main` or `develop`
- Manual trigger (GitHub UI)

**Artifacts:**
- `coverage.xml` (Clover format)
- `phpunit-coverage-html/` (HTML reports)
- `frontend-test-results/`
- `mutation-testing-report/`
- `test-summary.md`

### 4. Security Scan Workflow (security-scan.yml)

**What it does:**
- Composer dependency audit
- NPM dependency audit
- Psalm security analysis (taint analysis)
- Sensitive data detection (secrets, API keys)
- WordPress security compliance checks
- Security summary generation

**When it runs:**
- Every push to `main` or `develop`
- Every pull request to `main` or `develop`
- Daily at 2 AM UTC (scheduled)
- Manual trigger (GitHub UI)

**Artifacts:**
- `composer-audit-report.json`
- `npm-audit-report.json`
- `psalm-security-report.json`
- `security-summary.md`

### 5. Deployment Workflow (deployment.yml)

**What it does:**
- Build and optimize assets
- Create distribution package
- Deploy to staging (manual)
- Deploy to production (automatic on main)
- Deploy to WordPress.org (automatic on tag)
- Create GitHub release
- Generate deployment report

**When it runs:**
- Push to `main` → Production deployment
- Tag push (v*) → WordPress.org deployment + GitHub release
- Manual trigger → Staging deployment

**Artifacts:**
- `affiliate-product-showcase-{version}.zip`
- `deployment-summary.md`

## PR Comments

All workflows automatically post comments on pull requests with:
- ✅ Pass / ❌ Fail / ⚠️ Warning status
- Summary of results
- Links to detailed reports
- Action items if needed

## Secrets Required

### For Deployment

Configure these in repository Settings → Secrets and variables → Actions:

| Secret | Purpose |
|--------|---------|
| `STAGING_SERVER` | Staging server credentials |
| `PRODUCTION_SERVER` | Production server credentials |
| `WORDPRESS_ORG_USERNAME` | WordPress.org username |
| `WORDPRESS_ORG_PASSWORD` | WordPress.org password |

**Note:** `GITHUB_TOKEN` is automatically provided by GitHub.

## Environments

### Staging
- **Name:** `staging`
- **Trigger:** Manual
- **Purpose:** Pre-production testing

### Production
- **Name:** `production`
- **Trigger:** Push to `main`
- **Purpose:** Live deployment

### WordPress.org
- **Name:** `wordpress-org`
- **Trigger:** Tag push (v*)
- **Purpose:** Plugin directory

## Branch Strategy

```
main          ← Production (deployed automatically)
  ↑
develop       ← Development (CI/CD runs here)
  ↑
feature/*     ← Feature branches
fix/*         ← Bug fix branches
hotfix/*      ← Hotfix branches
release/*     ← Release preparation
```

## Release Process

### Standard Release
```bash
# 1. Update version
# Edit: affiliate-product-showcase.php (Version: header)

# 2. Update changelog
# Edit: CHANGELOG.md

# 3. Create release branch
git checkout -b release/v1.0.0

# 4. Test thoroughly
composer test
npm run test

# 5. Merge to main
git checkout main
git merge release/v1.0.0

# 6. Create and push tag
git tag v1.0.0
git push origin main
git push origin v1.0.0

# GitHub Actions will:
# - Run all checks
# - Build and package
# - Deploy to production
# - Create GitHub release
# - Deploy to WordPress.org
```

### Hotfix Release
```bash
# 1. Create hotfix branch from main
git checkout -b hotfix/urgent-fix main

# 2. Make minimal changes
# ... fix the issue ...

# 3. Test thoroughly
composer test
npm run test

# 4. Merge to main
git checkout main
git merge hotfix/urgent-fix

# 5. Create and push tag (patch version)
git tag v1.0.1
git push origin main
git push origin v1.0.1
```

## Local Development

### Run Checks Locally

```bash
cd wp-content/plugins/affiliate-product-showcase

# Code quality
composer phpstan
composer psalm
composer phpcs
npm run lint:js
npm run lint:css

# Testing
composer test
composer test-coverage
npm run test

# Security
composer audit
npm audit

# Build
npm run build

# Full check (like CI)
./run-scan.bat
```

### Fix Issues Locally

```bash
# Auto-fix PHPCS issues
vendor/bin/phpcs --standard=phpcs.xml.dist -- --fix

# Auto-fix ESLint issues
npm run lint:js -- --fix

# Auto-fix Stylelint issues
npm run lint:css -- --fix
```

## Troubleshooting

### Workflow Not Triggering
1. Check workflow file syntax
2. Verify branch names in triggers
3. Check GitHub Actions permissions
4. Ensure workflow is not disabled

### Workflow Fails
1. Check workflow logs in GitHub Actions tab
2. Identify which job failed
3. Review error messages
4. Fix issues locally
5. Push fixes

### Common Issues

**PHPStan Errors:**
```bash
vendor/bin/phpstan analyse src --level=max
```

**PHPCS Errors:**
```bash
vendor/bin/phpcs --standard=phpcs.xml.dist
vendor/bin/phpcs --standard=phpcs.xml.dist -- --fix
```

**Build Failures:**
```bash
npm ci
npm run build
```

**Test Failures:**
```bash
vendor/bin/phpunit
npm run test
```

### Artifacts Not Uploaded
1. Check workflow file paths
2. Verify artifact names
3. Check workflow logs for upload errors
4. Ensure `if: always()` condition for summary jobs

## Monitoring

### Status Badges

Add to `README.md`:

```markdown
![CI](https://github.com/{owner}/{repo}/workflows/CI/badge.svg)
![Code Quality](https://github.com/{owner}/{repo}/workflows/Code%20Quality/badge.svg)
![Testing](https://github.com/{owner}/{repo}/workflows/Testing/badge.svg)
![Security Scan](https://github.com/{owner}/{repo}/workflows/Security%20Scan/badge.svg)
```

### Notifications

- **Email:** Automatic for workflow failures
- **Slack:** Optional integration (configure in repo settings)
- **Webhooks:** Custom integrations available

## Performance

### Caching
- Node.js dependencies cached via `actions/setup-node`
- Composer dependencies installed with `--prefer-dist`
- Build artifacts cached when possible

### Parallel Execution
- All jobs run in parallel where possible
- Summary jobs wait for all dependencies
- Artifacts uploaded for analysis

### Artifact Retention
- Artifacts kept for 30 days
- Large artifacts compressed automatically
- Summary reports kept indefinitely

## Security

### Secrets Management
- ✅ Never commit secrets to repository
- ✅ Use GitHub Secrets for sensitive data
- ✅ Rotate secrets regularly
- ✅ Use environment-specific secrets

### Workflow Security
- ✅ Use specific action versions (not `@latest`)
- ✅ Review third-party actions
- ✅ Use minimal permissions
- ✅ Validate inputs

### Dependency Security
- ✅ Regular security audits (daily)
- ✅ Update vulnerable dependencies
- ✅ Use lock files for reproducibility

## Resources

### Documentation
- [GitHub Actions](https://docs.github.com/en/actions)
- [Workflow Syntax](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [GitHub Actions Marketplace](https://github.com/marketplace?type=actions)

### Tools
- [PHPStan](https://phpstan.org/user-guide/getting-started)
- [Psalm](https://psalm.dev/docs/)
- [PHPCS](https://github.com/squizlabs/PHP_CodeSniffer)
- [ESLint](https://eslint.org/docs/user-guide/getting-started)
- [Stylelint](https://stylelint.io/user-guide/get-started)
- [PHPUnit](https://phpunit.readthedocs.io/)
- [Infection](https://infection.github.io/guide/)

### Support
- [GitHub Status](https://www.githubstatus.com/)
- [GitHub Community](https://github.com/community)
- [GitHub Docs](https://docs.github.com)

## Contributing

To improve these workflows:

1. Fork the repository
2. Create a feature branch
3. Make changes
4. Test locally using `act` (optional):
   ```bash
   # Install act: https://github.com/nektos/act
   # Run workflow locally
   act -W .github/workflows/ci.yml
   ```
5. Create PR with description
6. Wait for review and merge

## License

These workflows are part of the Affiliate Product Showcase plugin and follow the same license.
