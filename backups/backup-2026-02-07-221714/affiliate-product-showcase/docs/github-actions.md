# GitHub Actions Workflows

This document describes the GitHub Actions workflows for the Affiliate Product Showcase WordPress plugin.

## Overview

The plugin includes comprehensive CI/CD workflows that automate code quality checks, testing, security scanning, and deployment processes.

## Workflow Files

### 1. CI Workflow (ci.yml)

**Location:** `.github/workflows/ci.yml`

**Purpose:** Basic continuous integration checks

**Triggers:**
- Push to `main` and `develop` branches
- Pull requests to `main` and `develop` branches

**Jobs:**
- **phpcs:** PHP Code Sniffer - Checks code style compliance
- **phpstan:** PHPStan - Static analysis for type errors
- **phpunit:** PHPUnit - Unit tests
- **eslint:** ESLint - JavaScript linting
- **build:** Build verification - Ensures assets build correctly

**Usage:**
```bash
# Runs automatically on push/PR
# No manual trigger needed
```

### 2. Code Quality Workflow (code-quality.yml)

**Location:** `.github/workflows/code-quality.yml`

**Purpose:** Comprehensive code quality analysis using professional tools

**Triggers:**
- Push to `main` and `develop` branches
- Pull requests to `main` and `develop` branches
- Manual trigger via GitHub UI

**Jobs:**
- **phpstan:** PHPStan static analysis (level max)
- **psalm:** Psalm type checking
- **phpcs:** PHPCS code style (PSR-12 + WPCS)
- **eslint:** ESLint JavaScript linting
- **stylelint:** Stylelint CSS linting
- **quality-summary:** Generates comprehensive quality report

**Features:**
- JSON reports for all tools
- Artifact uploads for analysis
- PR comments with results
- Quality score calculation

**Usage:**
```bash
# Runs automatically on push/PR
# Or manually trigger via GitHub UI:
# Actions → Code Quality → Run workflow
```

### 3. Testing Workflow (testing.yml)

**Location:** `.github/workflows/testing.yml`

**Purpose:** Comprehensive testing with coverage and mutation testing

**Triggers:**
- Push to `main` and `develop` branches
- Pull requests to `main` and `develop` branches
- Manual trigger via GitHub UI

**Jobs:**
- **phpunit:** PHPUnit unit tests with coverage
- **phpunit-coverage:** HTML coverage report generation
- **frontend-tests:** Frontend JavaScript tests
- **integration-tests:** Integration tests with MySQL
- **mutation-testing:** Mutation testing with Infection
- **test-summary:** Generates comprehensive test report

**Features:**
- Code coverage reports (Clover, HTML)
- Mutation testing with minimum MSI requirements
- Integration tests with database
- Frontend test results
- PR comments with test results

**Usage:**
```bash
# Runs automatically on push/PR
# Or manually trigger via GitHub UI:
# Actions → Testing → Run workflow
```

### 4. Security Scan Workflow (security-scan.yml)

**Location:** `.github/workflows/security-scan.yml`

**Purpose:** Comprehensive security scanning and vulnerability detection

**Triggers:**
- Push to `main` and `develop` branches
- Pull requests to `main` and `develop` branches
- Scheduled: Daily at 2 AM UTC
- Manual trigger via GitHub UI

**Jobs:**
- **composer-audit:** PHP dependency security audit
- **npm-audit:** JavaScript dependency security audit
- **psalm-security:** Psalm security analysis (taint analysis)
- **sensitive-data:** Detects hardcoded secrets and debug code
- **wordpress-security:** WordPress security compliance checks
- **security-summary:** Generates comprehensive security report

**Features:**
- Daily automated security scans
- Dependency vulnerability detection
- Sensitive data detection (API keys, tokens, passwords)
- WordPress security standards compliance
- PR comments with security results

**Usage:**
```bash
# Runs automatically on push/PR
# Runs daily at 2 AM UTC
# Or manually trigger via GitHub UI:
# Actions → Security Scan → Run workflow
```

### 5. Deployment Workflow (deployment.yml)

**Location:** `.github/workflows/deployment.yml`

**Purpose:** Build, package, and deploy the plugin

**Triggers:**
- Push to `main` branch (production deployment)
- Tag push starting with `v*` (WordPress.org deployment)
- Manual trigger via GitHub UI

**Jobs:**
- **build:** Build and package the plugin
- **deploy-staging:** Deploy to staging environment (manual)
- **deploy-production:** Deploy to production (on main push)
- **deploy-wordpress-org:** Deploy to WordPress.org (on tag)
- **create-release:** Create GitHub release (on tag)
- **deployment-summary:** Generates deployment report

**Features:**
- Automatic version detection
- Asset building and optimization
- Distribution package creation
- Multiple deployment targets
- GitHub release creation
- PR comments with deployment status

**Usage:**
```bash
# Production deployment (automatic):
git push origin main

# WordPress.org deployment (automatic):
git tag v1.0.0
git push origin v1.0.0

# Staging deployment (manual):
# Actions → Deployment → Run workflow
# Select environment: staging
```

## Workflow Comparison

| Workflow | Triggers | Purpose | Frequency |
|----------|----------|---------|-----------|
| **ci.yml** | Push/PR to main/develop | Basic CI checks | Every push/PR |
| **code-quality.yml** | Push/PR to main/develop | Code quality analysis | Every push/PR |
| **testing.yml** | Push/PR to main/develop | Comprehensive testing | Every push/PR |
| **security-scan.yml** | Push/PR + Daily at 2 AM | Security scanning | Every push/PR + Daily |
| **deployment.yml** | Push to main + Tags | Build & deploy | On release |

## Workflow Execution Order

### On Pull Request
1. **ci.yml** - Basic checks (fast)
2. **code-quality.yml** - Code quality analysis
3. **testing.yml** - Comprehensive tests
4. **security-scan.yml** - Security scanning

### On Push to Main
1. **ci.yml** - Basic checks
2. **code-quality.yml** - Code quality analysis
3. **testing.yml** - Comprehensive tests
4. **security-scan.yml** - Security scanning
5. **deployment.yml** - Build & deploy to production

### On Tag Push (Release)
1. **ci.yml** - Basic checks
2. **code-quality.yml** - Code quality analysis
3. **testing.yml** - Comprehensive tests
4. **security-scan.yml** - Security scanning
5. **deployment.yml** - Build, deploy to production, create GitHub release, deploy to WordPress.org

### On Manual Trigger
1. Select workflow
2. Configure inputs (if any)
3. Workflow executes
4. Results uploaded as artifacts
5. PR comments posted (if applicable)

## Artifacts and Reports

All workflows generate artifacts that can be downloaded:

### Code Quality Artifacts
- `phpstan-report.json` - PHPStan analysis results
- `psalm-report.json` - Psalm analysis results
- `phpcs-report.json` - PHPCS analysis results
- `eslint-report.json` - ESLint analysis results
- `stylelint-report.json` - Stylelint analysis results
- `quality-summary.md` - Quality summary report

### Testing Artifacts
- `coverage.xml` - PHPUnit coverage (Clover format)
- `phpunit-coverage-html/` - HTML coverage report
- `frontend-test-results/` - Frontend test results
- `mutation-testing-report/` - Mutation testing results
- `test-summary.md` - Test summary report

### Security Artifacts
- `composer-audit-report.json` - Composer audit results
- `npm-audit-report.json` - NPM audit results
- `psalm-security-report.json` - Psalm security analysis
- `security-summary.md` - Security summary report

### Deployment Artifacts
- `affiliate-product-showcase-{version}.zip` - Distribution package
- `deployment-summary.md` - Deployment summary report

## PR Comments

All workflows automatically post comments on pull requests with:
- Summary of results
- Status (✅ Pass / ❌ Fail / ⚠️ Warning)
- Links to detailed reports
- Action items if needed

## Secrets Required

### For Deployment Workflows

**GitHub Secrets:**
- `GITHUB_TOKEN` - Automatically provided (for releases)
- `STAGING_SERVER` - Staging server URL/credentials
- `PRODUCTION_SERVER` - Production server URL/credentials
- `WORDPRESS_ORG_USERNAME` - WordPress.org username
- `WORDPRESS_ORG_PASSWORD` - WordPress.org password

**Setting Secrets:**
1. Go to repository Settings
2. Navigate to Secrets and variables → Actions
3. Add new repository secret
4. Enter secret name and value

## Environment Configuration

### Staging Environment
- **Name:** `staging`
- **Deployment:** Manual trigger
- **Purpose:** Testing before production

### Production Environment
- **Name:** `production`
- **Deployment:** Automatic on main push
- **Purpose:** Live production deployment

### WordPress.org Environment
- **Name:** `wordpress-org`
- **Deployment:** Automatic on tag push
- **Purpose:** WordPress plugin directory

## Best Practices

### 1. Branch Strategy
- `main` - Production-ready code
- `develop` - Development branch
- Feature branches - `feature/feature-name`
- Bug fix branches - `fix/bug-name`

### 2. Commit Messages
Use conventional commits:
```
feat: add new feature
fix: fix a bug
docs: update documentation
style: code style changes
refactor: code refactoring
test: add tests
chore: build/process changes
```

### 3. Pull Request Process
1. Create feature branch from `develop`
2. Make changes
3. Run local checks: `npm run lint`, `composer test`
4. Push to remote
5. Create PR to `develop`
6. Wait for all checks to pass
7. Request review
8. Merge when approved

### 4. Release Process
1. Update version in `affiliate-product-showcase.php`
2. Update `CHANGELOG.md`
3. Create release branch: `release/v1.0.0`
4. Test thoroughly
5. Merge to `main`
6. Create tag: `v1.0.0`
7. Push tag
8. GitHub Actions will:
   - Build and package
   - Deploy to production
   - Create GitHub release
   - Deploy to WordPress.org

### 5. Hotfix Process
1. Create hotfix branch from `main`: `hotfix/urgent-fix`
2. Make minimal changes
3. Test thoroughly
4. Merge to `main`
5. Create tag: `v1.0.1`
6. Push tag

## Troubleshooting

### Workflow Fails
1. Check workflow logs in GitHub Actions tab
2. Identify which job failed
3. Review error messages
4. Fix issues locally
5. Push fixes

### Common Issues

**PHPStan Errors:**
```bash
# Run locally
cd wp-content/plugins/affiliate-product-showcase
vendor/bin/phpstan analyse src --level=max
```

**PHPCS Errors:**
```bash
# Run locally
cd wp-content/plugins/affiliate-product-showcase
vendor/bin/phpcs --standard=phpcs.xml.dist
# Auto-fix
vendor/bin/phpcs --standard=phpcs.xml.dist -- --fix
```

**Build Failures:**
```bash
# Run locally
cd wp-content/plugins/affiliate-product-showcase
npm ci
npm run build
```

**Test Failures:**
```bash
# Run locally
cd wp-content/plugins/affiliate-product-showcase
vendor/bin/phpunit
npm run test
```

### Workflow Not Triggering
1. Check workflow file syntax
2. Verify branch names in triggers
3. Check GitHub Actions permissions
4. Ensure workflow is not disabled

### Artifacts Not Uploaded
1. Check workflow file paths
2. Verify artifact names
3. Check workflow logs for upload errors
4. Ensure `if: always()` condition for summary jobs

## Performance Optimization

### Caching
All workflows use caching:
- Node.js: `actions/setup-node` with cache
- Composer: Installed with `--prefer-dist`

### Parallel Execution
- Code quality jobs run in parallel
- Testing jobs run in parallel where possible
- Security scans run in parallel

### Artifact Management
- Artifacts are uploaded for analysis
- Large artifacts are compressed
- Artifacts are automatically cleaned up after 30 days

## Security Considerations

### Secrets Management
- Never commit secrets to repository
- Use GitHub Secrets for sensitive data
- Rotate secrets regularly
- Use environment-specific secrets

### Workflow Security
- Use specific action versions (not `@latest`)
- Review third-party actions
- Use minimal permissions
- Validate inputs

### Dependency Security
- Regular security audits
- Update vulnerable dependencies
- Use lock files for reproducibility

## Monitoring and Alerts

### GitHub Notifications
- Email notifications for workflow failures
- Slack integration (optional)
- Webhook notifications

### Status Badges
Add to README.md:
```markdown
![CI](https://github.com/{owner}/{repo}/workflows/CI/badge.svg)
![Code Quality](https://github.com/{owner}/{repo}/workflows/Code%20Quality/badge.svg)
![Testing](https://github.com/{owner}/{repo}/workflows/Testing/badge.svg)
![Security Scan](https://github.com/{owner}/{repo}/workflows/Security%20Scan/badge.svg)
```

## Advanced Configuration

### Custom Triggers
```yaml
on:
  workflow_dispatch:
    inputs:
      environment:
        description: 'Environment'
        required: true
        default: 'staging'
        type: choice
        options:
          - staging
          - production
```

### Conditional Execution
```yaml
jobs:
  deploy:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
```

### Matrix Strategy
```yaml
strategy:
  matrix:
    php-version: ['8.1', '8.2', '8.3']
```

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Workflow Syntax Reference](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [GitHub Actions Marketplace](https://github.com/marketplace?type=actions)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Psalm Documentation](https://psalm.dev/docs/)
- [PHPCS Documentation](https://github.com/squizlabs/PHP_CodeSniffer)
- [ESLint Documentation](https://eslint.org/docs/user-guide/getting-started)
- [Stylelint Documentation](https://stylelint.io/user-guide/get-started)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Infection Documentation](https://infection.github.io/guide/)

## Support

For issues with workflows:
1. Check workflow logs
2. Review this documentation
3. Check GitHub Actions status: https://www.githubstatus.com/
4. Open an issue on GitHub

## Contributing

To improve these workflows:
1. Fork the repository
2. Create a feature branch
3. Make changes
4. Test locally using `act` (optional)
5. Create PR with description of changes
6. Wait for review and merge

## License

These workflows are part of the Affiliate Product Showcase plugin and follow the same license.
