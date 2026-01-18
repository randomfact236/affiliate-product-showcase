# Contributing to Affiliate Product Showcase

Thank you for your interest in contributing to the Affiliate Product Showcase plugin! We welcome contributions from the community and are pleased to have you join us.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Development Workflow](#development-workflow)
- [Code Quality Standards](#code-quality-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Pull Request Process](#pull-request-process)
- [Reporting Issues](#reporting-issues)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Community](#community)

---

## 🤝 Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to [maintainer email].

**Key Principles:**
- Be respectful, inclusive, and constructive
- Welcome newcomers and help them learn
- Focus on the technology and ideas, not people
- Be mindful of your language and tone
- Show empathy and patience

**See:** [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for full details.

---

## 🚀 Getting Started

### Ways to Contribute

1. **Report Bugs**: Found a bug? Open an issue!
2. **Suggest Features**: Have an idea? We'd love to hear it!
3. **Improve Documentation**: Help make our docs clearer
4. **Write Code**: Fix bugs or add new features
5. **Help Others**: Answer questions in discussions

### Before You Start

1. **Check Existing Issues**: Avoid duplicate work
2. **Discuss First**: For major changes, open an issue first
3. **Read the Docs**: Understand the project structure
4. **Set Up Your Environment**: Follow the setup guide below

---

## 💻 Development Setup

### Prerequisites

| Tool | Version | Purpose |
|------|---------|---------|
| **PHP** | 7.4+ | Plugin runtime |
| **Composer** | 2.x | PHP dependencies |
| **Node.js** | 18+ | Build tools |
| **npm** | 9+ | JavaScript dependencies |
| **Git** | 2.x | Version control |
| **WordPress** | 6.4+ | Testing environment |

### Initial Setup

   - 1. Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/affiliate-product-showcase.git
cd affiliate-product-showcase

# Add upstream remote
git remote add upstream https://github.com/randomfact236/affiliate-product-showcase.git
```

   - 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install

# Build assets
npm run build
```

   - 3. Set Up Development Environment

**Option A: Docker (Recommended)**

```bash
cd docker
docker-compose up -d
# WordPress at http://localhost:8080
```

**Option B: Local WordPress**

1. Install WordPress locally
2. Copy plugin to `wp-content/plugins/affiliate-product-showcase`
3. Activate plugin in WordPress admin

   - 4. Verify Setup

```bash
# Run all checks
composer test
npm run lint
```

---

## 🔄 Development Workflow

### Branch Strategy

```
main (production)
  ↑
develop (development)
  ↑
feature/* (new features)
hotfix/* (urgent fixes)
release/* (pre-release)
```

### Step-by-Step Workflow

   - 1. Create Feature Branch

```bash
git checkout develop
git pull upstream develop
git checkout -b feature/your-feature-name
```

   - 2. Make Changes

- Write clear, focused code
- Follow coding standards
- Add tests for new functionality
- Update documentation

   - 3. Test Your Changes

```bash
# Run all tests
composer test
npm run lint

# Run specific tests
vendor/bin/phpunit --filter YourTest
npm run lint:js -- src/your-file.js
```

   - 4. Commit Your Changes

Use [Conventional Commits](https://www.conventionalcommits.org/):

```bash
# Format: type(scope): description
git commit -m "feat(shortcodes): add custom class attribute"

# Examples:
# feat: add new feature
# fix: fix a bug
# docs: update documentation
# test: add tests
# refactor: code refactoring
# chore: maintenance tasks
```

   - 5. Push and Create PR

```bash
git push origin feature/your-feature-name
# Then create Pull Request on GitHub
```

### Code Review Process

1. **Automated Checks**: CI runs tests and linting
2. **Review**: At least one maintainer reviews
3. **Discussion**: Address feedback and questions
4. **Approval**: Once approved, merge to develop
5. **Release**: Merged to main for production

---

## 🎯 Code Quality Standards

### PHP Standards

- **Standard**: PSR-12 + WordPress Coding Standards
- **Tools**: PHP_CodeSniffer, PHPStan
- **Checks**: `composer cs-check`, `composer analyze`

**Requirements:**
```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase;

use AffiliateProductShowcase\Helpers\Options;

/**
 * Class Example
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */
class Example {
    /**
     * Example property.
     *
     * @var string
     */
    private string $example;

    /**
     * Constructor.
     *
     * @param string $example Example parameter.
     */
    public function __construct(string $example) {
        $this->example = $example;
    }

    /**
     * Get example.
     *
     * @return string
     */
    public function getExample(): string {
        return $this->example;
    }
}
```

### JavaScript Standards

- **Standard**: ESLint (WordPress preset)
- **Format**: Prettier
- **Checks**: `npm run lint`, `npm run format`

**Requirements:**
```javascript
/**
 * Example function description.
 *
 * @param {string} example - Example parameter
 * @return {string} Example result
 */
export function exampleFunction(example) {
    const result = `Example: ${example}`;
    return result;
}
```

### CSS Standards

- **Standard**: Stylelint (standard rules)
- **Framework**: TailwindCSS
- **Checks**: `npm run lint:css`

### Documentation Standards

- **Language**: Clear, concise English
- **Format**: Markdown
- **Examples**: Include code examples
- **Updates**: Keep in sync with code changes

---

## 🧪 Testing

### Test Types

   - 1. Unit Tests

```bash
# Run all unit tests
vendor/bin/phpunit --testsuite=unit

# Run specific test
vendor/bin/phpunit tests/Unit/ExampleTest.php
```

**Location**: `tests/Unit/`

**Coverage Goal**: 80%+ for new code

   - 2. Integration Tests

```bash
# Run all integration tests
vendor/bin/phpunit --testsuite=integration
```

**Location**: `tests/Integration/`

**Purpose**: Test WordPress integration

   - 3. End-to-End Tests

```bash
# Requires Docker environment
npm run test:e2e
```

**Location**: `tests/E2E/`

**Purpose**: Test user workflows

### Writing Tests

```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Helpers\Options;

class OptionsTest extends TestCase {
    public function test_get_plugin_option_returns_default(): void {
        $result = Options::get_plugin_option('nonexistent', 'default');
        $this->assertEquals('default', $result);
    }

    public function test_update_plugin_option_persists(): void {
        Options::update_plugin_option('test_key', 'test_value');
        $stored = get_option('affiliate_product_showcase_test_key');
        $this->assertEquals('test_value', $stored);
    }
}
```

### Test Coverage

```bash
# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/

# Check coverage threshold
vendor/bin/phpunit --coverage-text --coverage-filter src/
```

---

## 📚 Documentation

### What to Document

1. **Code Comments**: PHPDoc blocks for all classes/methods
2. **Inline Comments**: Complex logic explanations
3. **README Updates**: New features and changes
4. **Changelog**: All notable changes
5. **Commit Messages**: Clear, descriptive messages

### Documentation Standards

```php
/**
 * Short description (one line).
 *
 * Long description with more details about what this method does,
 * its purpose, and any important notes about usage.
 *
 * @since 1.0.0
 *
 * @param string $param1 Description of first parameter.
 * @param int    $param2 Description of second parameter.
 * @param array  $param3 Description of third parameter.
 *
 * @return string Description of return value.
 *
 * @throws \Exception Description of when exception is thrown.
 *
 * @example
 * ```php
 * $result = example_function('test', 42, ['key' => 'value']);
 * ```
 */
public function example_function(string $param1, int $param2, array $param3 = []): string {
    // Implementation
}
```

### Updating Documentation

When making code changes:

1. Update relevant docblocks
2. Update README if needed
3. Update CHANGELOG.md
4. Update technical docs in `docs/` folder

---

## 📤 Pull Request Process

### PR Checklist

Before submitting a PR, ensure:

- [ ] Code follows coding standards
- [ ] Tests pass (unit, integration)
- [ ] Linting passes
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] PR description is clear
- [ ] Related issues are linked

### PR Description Template

```markdown
## Description

Brief description of changes

## Type of Change

- [ ] Bug fix (non-breaking change)
- [ ] New feature (backward compatible)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## How to Test

1. Step 1: ...
2. Step 2: ...
3. Expected result: ...

## Checklist

- [ ] I have performed a self-review of my code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## Related Issues

Closes #123
Fixes #456
```

### Review Process

1. **Automated Checks**: CI runs tests, linting, security scans
2. **Code Review**: Maintainers review for:
   - Code quality
   - Security implications
   - Performance impact
   - Backward compatibility
   - Documentation completeness
3. **Discussion**: Address feedback
4. **Approval**: 1+ maintainer approval required
5. **Merge**: Squash and merge to develop

---

## 🐛 Reporting Issues

### Bug Reports

Use the bug report template:

```markdown
## Bug Description

[Clear, concise description]

## Steps to Reproduce

1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

## Expected Behavior

[What you expected to happen]

## Actual Behavior

[What actually happened]

## Environment

- WordPress Version: [e.g., 6.5]
- PHP Version: [e.g., 8.1]
- Plugin Version: [e.g., 1.0.0]
- Browser: [e.g., Chrome 120]
- OS: [e.g., Windows 11]

## Additional Context

[Screenshots, logs, etc.]
```

### Feature Requests

```markdown
## Feature Description

[Clear description of feature]

## Problem Statement

[What problem does this solve?]

## Proposed Solution

[How should it work?]

## Alternatives Considered

[Other approaches you considered]

## Additional Context

[Mockups, examples, etc.]
```

---

## 🔒 Security Vulnerabilities

### ⚠️ IMPORTANT: Do NOT use public issues for security vulnerabilities

### Reporting Process

1. **Email**: Send to security@example.com (replace with actual email)
2. **Include**:
   - Description of vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)
3. **Wait**: Allow 14 days for patching
4. **Disclosure**: Do not disclose publicly until patched

### What to Report

- SQL injection vulnerabilities
- XSS vulnerabilities
- CSRF vulnerabilities
- Authentication bypasses
- Data exposure
- Privilege escalation
- Any other security concerns

### Response Timeline

- **24 hours**: Acknowledgment of receipt
- **7 days**: Initial assessment and response
- **14 days**: Patch development and testing
- **14+ days**: Public disclosure with credit

---

## 🌍 Community

### Communication Channels

| Channel | Purpose | Link |
|---------|---------|------|
| **GitHub Issues** | Bug reports & feature requests | [Issues](https://github.com/randomfact236/affiliate-product-showcase/issues) |
| **GitHub Discussions** | Q&A & community help | [Discussions](https://github.com/randomfact236/affiliate-product-showcase/discussions) |
| **WordPress.org** | Plugin support forum | [Plugin Page](https://wordpress.org/plugins/affiliate-product-showcase/) |

### Getting Help

1. **Documentation**: Check `docs/` folder
2. **FAQ**: See `docs/faq.md`
3. **Discussions**: Search existing discussions
4. **Issues**: Search existing issues
5. **Contact**: Email maintainer (last resort)

### Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md file
- Credited in release notes
- Mentioned in blog posts (if applicable)
- Eligible for swag (if available)

---

## 📄 License

By contributing, you agree that your contributions will be licensed under the [GPL-2.0-or-later](LICENSE) license.

---

## 🙏 Thank You!

Your contributions make this project better for everyone. Whether it's a small documentation fix or a major feature, every contribution matters.

**Happy coding! 🚀**

---

*Last updated: January 2026*
*Version: 1.0.0*
