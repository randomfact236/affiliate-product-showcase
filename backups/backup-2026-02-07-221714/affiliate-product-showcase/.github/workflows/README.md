# GitHub Actions Workflows

This directory contains all GitHub Actions workflows for the Affiliate Product Showcase WordPress plugin.

## Workflow Files

### 1. ci.yml - Continuous Integration
**Purpose:** Basic CI checks for every push and pull request

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

**Jobs:**
- `phpcs` - PHP Code Sniffer (code style)
- `phpstan` - PHPStan (static analysis)
- `psalm` - Psalm (type checking)
- `phpunit` - PHPUnit (unit tests)
- `eslint` - ESLint (JavaScript linting)
- `stylelint` - Stylelint (CSS linting)
- `build` - Build verification
- `ci-summary` - Generate summary report

**Artifacts:**
- `ci-summary.md` - CI summary report

**PR Comments:** Yes, posts summary to pull requests

---

### 2. code-quality.yml - Code Quality Analysis
**Purpose:** Comprehensive code quality analysis using professional tools

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Manual trigger via GitHub UI

**Jobs:**
- `phpstan` - PHPStan static analysis (level max)
- `psalm` - Psalm type checking
- `phpcs` - PHPCS code style (PSR-12 + WPCS)
- `eslint` - ESLint JavaScript linting
- `stylelint` - Stylelint CSS linting
- `quality-summary` - Generate comprehensive quality report

**Artifacts:**
- `phpstan-report.json` - PHPStan analysis results
- `psalm-report.json` - Psalm analysis results
- `phpcs-report.json` - PHPCS analysis results
- `eslint-report.json` - ESLint analysis results
- `stylelint-report.json` - Stylelint analysis results
- `quality-summary.md` - Quality summary report

**PR Comments:** Yes, posts summary to pull requests

---

### 3. testing.yml - Comprehensive Testing
**Purpose:** Full test suite with coverage and mutation testing

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Manual trigger via GitHub UI

**Jobs:**
- `phpunit` - PHPUnit unit tests with coverage
- `phpunit-coverage` - HTML coverage report generation
- `frontend-tests` - Frontend JavaScript tests
- `integration-tests` - Integration tests with MySQL
- `mutation-testing` - Mutation testing with Infection
- `test-summary` - Generate comprehensive test report

**Artifacts:**
- `coverage.xml` - PHPUnit coverage (Clover format)
- `phpunit-coverage-html/` - HTML coverage report
- `frontend-test-results/` - Frontend test results
- `mutation-testing-report/` - Mutation testing results
- `test-summary.md` - Test summary report

**PR Comments:** Yes, posts summary to pull requests

---

### 4. security-scan.yml - Security Scanning
**Purpose:** Comprehensive security scanning and vulnerability detection

**Triggers:**
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Scheduled: Daily at 2 AM UTC
- Manual trigger via GitHub UI

**Jobs:**
- `composer-audit` - PHP dependency security audit
- `npm-audit` - JavaScript dependency security audit
- `psalm-security` - Psalm security analysis (taint analysis)
- `sensitive-data` - Detects hardcoded secrets and debug code
- `wordpress-security` - WordPress security compliance checks
- `security-summary` - Generate comprehensive security report

**Artifacts:**
- `composer-audit-report.json` - Composer audit results
- `npm-audit-report.json` - NPM audit results
- `psalm-security-report.json` - Psalm security analysis
- `security-summary.md` - Security summary report

**PR Comments:** Yes, posts summary to pull requests

---

### 5. deployment.yml - Deployment
**Purpose:** Build, package, and deploy the plugin

**Triggers:**
- Push to `main` branch (production deployment)
- Tag push starting with `v*` (WordPress.org deployment)
- Manual trigger via GitHub UI

**Jobs:**
- `build` - Build and package the plugin
- `deploy-staging` - Deploy to staging environment (manual)
- `deploy-production` - Deploy to production (on main push)
- `deploy-wordpress-org` - Deploy to WordPress.org (on tag)
- `create-release` - Create GitHub release (on tag)
- `deployment-summary` - Generate deployment report

**Artifacts:**
- `affiliate-product-showcase-{version}.zip` - Distribution package
- `deployment-summary.md` - Deployment summary report

**PR Comments:** Yes, posts summary to pull requests

---

## Workflow Comparison

| Workflow | Triggers | Purpose | Frequency | PR Comments |
|----------|----------|---------|-----------|-------------|
| **ci.yml** | Push/PR to main/develop | Basic CI checks | Every push/PR | ✅ |
| **code-quality.yml** | Push/PR + Manual | Code quality analysis | Every push/PR | ✅ |
| **testing.yml** | Push/PR + Manual | Comprehensive testing | Every push/PR | ✅ |
| **security-scan.yml** | Push/PR + Daily at 2 AM | Security scanning | Every push/PR + Daily | ✅ |
| **deployment.yml** | Push to main + Tags | Build & deploy | On release | ✅ |

## Execution Order

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

## Secrets Required

### For Deployment Workflows

Configure these in repository Settings → Secrets and variables → Actions:

| Secret | Purpose |
|--------|---------|
| `GITHUB_TOKEN` | Automatically provided (for releases) |
| `STAGING_SERVER` | Staging server URL/credentials |
| `PRODUCTION_SERVER` | Production server URL/credentials |
| `WORDPRESS_ORG_USERNAME` | WordPress.org username |
| `WORDPRESS_ORG_PASSWORD` | WordPress.org password |

## Environments

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

## Quick Start

### Local Development

```bash
# Navigate to plugin directory
cd wp-content/plugins/affiliate-product-showcase

# Run all checks (like CI)
./run-scan.bat

# Or run individually
composer phpstan
composer psalm
composer phpcs
composer test
npm run lint:js
npm run lint:css
npm run test
npm run build
```

### Trigger Workflows

**Automatic:**
```bash
# Push to main (deploys to production)
git push origin main

# Create release (deploys to WordPress.org)
git tag v1.0.0
git push origin v1.0.0
```

**Manual:**
1. Go to GitHub repository
2. Click "Actions" tab
3. Select workflow
4. Click "Run workflow"
5. Configure inputs (if any)
6. Click "Run workflow"

## Artifacts and Reports

All workflows generate artifacts that can be downloaded from the Actions tab:

### Code Quality Artifacts
- JSON reports from all analysis tools
- Quality summary markdown file

### Testing Artifacts
- Code coverage reports (Clover, HTML)
- Test results
- Mutation testing reports

### Security Artifacts
- Dependency audit reports
- Security analysis reports
- Security summary

### Deployment Artifacts
- Distribution package (zip)
- Deployment summary

## PR Comments

All workflows automatically post comments on pull requests with:
- ✅ Pass / ❌ Fail / ⚠️ Warning status
- Summary of results
- Links to detailed reports
- Action items if needed

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

## Best Practices

### Branch Strategy
- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - Feature branches
- `fix/*` - Bug fix branches
- `hotfix/*` - Hotfix branches
- `release/*` - Release preparation

### Commit Messages
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

### Release Process
1. Update version in `affiliate-product-showcase.php`
2. Update `CHANGELOG.md`
3. Create release branch
4. Test thoroughly
5. Merge to `main`
6. Create and push tag
7. GitHub Actions handles deployment

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
- Email notifications for workflow failures
- Slack integration (optional)
- Webhook notifications (optional)

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Workflow Syntax Reference](https://docs.github.com/en/actions/using-workflows/workflow-syntax-for-github-actions)
- [GitHub Actions Marketplace](https://github.com/marketplace?type=actions)
- [Full Documentation](../docs/github-actions.md)

## Support

For issues with workflows:
1. Check workflow logs
2. Review documentation
3. Check GitHub Actions status: https://www.githubstatus.com/
4. Open an issue on GitHub

## Contributing

To improve these workflows:
1. Fork the repository
2. Create a feature branch
3. Make changes
4. Test locally using `act` (optional)
5. Create PR with description
6. Wait for review and merge

## License

These workflows are part of the Affiliate Product Showcase plugin and follow the same license.
