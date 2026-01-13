# Code Quality Tools

## Overview

The Affiliate Product Showcase plugin implements a comprehensive, enterprise-grade code quality toolchain that ensures code reliability, security, and maintainability across both PHP and JavaScript codebases.

## PHP Quality Tools

### 1. PHP_CodeSniffer (PHPCS)

**Configuration**: `phpcs.xml.dist`

**Standards Applied**:
- WordPress Coding Standards (WordPress-Core, WordPress-Docs, WordPress-Extra)
- Security rules (severity: 10)
- Performance rules (severity: 7)
- Best practices (severity: 5)

**Key Rules**:
- Line length: 120 characters
- PHP Compatibility: 7.4+ with modern features
- Yoda conditions: Disabled (modern natural order preferred)
- Modern WordPress hooks: Fully supported

**Usage**:
```bash
# Run PHPCS
composer phpcs

# Fix auto-fixable issues
composer phpcs-fix
```

### 2. PHPStan (Static Analysis)

**Configuration**: `phpstan.neon`

**Analysis Levels**:
- Level 8 (strict) with WordPress-specific rules
- Memory limit: 1GB
- Baseline support for gradual improvement

**Key Features**:
- Type safety enforcement
- WordPress function analysis
- Hook usage validation
- Deprecated function detection

**Usage**:
```bash
# Run analysis
composer phpstan

# Generate baseline
composer phpstan-baseline
```

### 3. Psalm (Static Analysis)

**Configuration**: `psalm.xml.dist`

**Features**:
- Type inference and checking
- Security analysis
- Dead code detection
- 4-thread parallel processing

**Usage**:
```bash
# Run analysis
composer psalm

# Initialize configuration
composer psalm-init
```

### 4. PHPUnit (Testing)

**Configuration**: `phpunit.xml.dist`

**Features**:
- Bootstrap: `tests/bootstrap.php`
- Color output
- Coverage reporting
- Integration with CI

**Usage**:
```bash
# Run tests
composer test

# With coverage
composer test-coverage

# HTML coverage report
composer phpunit-html
```

### 5. Infection (Mutation Testing)

**Configuration**: `infection.json.dist`

**Features**:
- 4-thread parallel execution
- Mutation score indicator
- Integration with Xdebug

**Usage**:
```bash
composer infection
```

### 6. PHP Parallel Lint

**Features**:
- Fast syntax checking
- Supports PHP 7.4+
- Console highlighter

**Usage**:
```bash
composer parallel-lint
```

### 7. Laravel Pint (Code Formatter)

**Features**:
- PSR-12 compliant
- Laravel style guide
- Auto-fixing

**Usage**:
```bash
composer pint
```

### 8. Composer Normalize

**Features**:
- Standardizes composer.json format
- Ensures consistency

**Usage**:
```bash
composer composer-normalize
```

## JavaScript Quality Tools

### 1. ESLint

**Configuration**: Uses `@wordpress/eslint-plugin` with custom rules

**Key Features**:
- React support (hooks, JSX)
- Accessibility (jsx-a11y)
- WordPress coding standards
- TypeScript support
- Max warnings: 0 (strict)

**Usage**:
```bash
# Lint all JS/TS files
npm run lint:js

# Auto-fix
npm run lint:fix
```

**Package Dependencies**:
- `@wordpress/eslint-plugin`: ^15.1.0
- `eslint`: ^8.56.0
- `eslint-plugin-react`: ^7.33.2
- `eslint-plugin-react-hooks`: ^4.6.0
- `eslint-plugin-jsx-a11y`: ^6.8.0

### 2. Prettier

**Configuration**: Integrated with ESLint and Stylelint

**Features**:
- Code formatting for JS, TS, CSS, SCSS, JSON, MD, YAML
- Consistent style across all files
- Integration with lint-staged

**Usage**:
```bash
# Format all files
npm run format

# Check formatting
npm run format:check
```

### 3. Stylelint

**Configuration**: Uses `stylelint-config-standard` with custom rules

**Key Features**:
- CSS/SCSS linting
- BEM pattern support
- Order enforcement
- Max warnings: 0 (strict)

**Usage**:
```bash
# Lint styles
npm run lint:css

# Auto-fix
npm run lint:fix
```

**Package Dependencies**:
- `stylelint`: ^16.2.0
- `stylelint-config-standard`: ^36.0.0
- `stylelint-order`: ^6.0.4
- `stylelint-selector-bem-pattern`: ^3.0.1

### 4. TypeScript

**Configuration**: `tsconfig.json`

**Compiler Options**:
- Target: ES2019
- Strict mode enabled
- React JSX support
- Path aliases (@/, @js/, @components/, etc.)
- Vite client types

**Usage**:
```bash
# Type check only
npm run typecheck
```

### 5. Vite (Build Tool)

**Configuration**: `vite.config.js`

**Quality Features**:
- Production source maps (hidden)
- CSS code splitting
- Chunk optimization
- Asset optimization
- SRI generation (SHA384)
- Bundle analyzer

**Usage**:
```bash
# Development
npm run dev

# Production build
npm run build

# Analyze bundle
npm run analyze
```

### 6. Husky + Lint-staged

**Configuration**: 
- Husky: `.husky/` directory
- Lint-staged: `.lintstagedrc.json`

**Pre-commit Hooks**:
- PHP: PHPCS + PHPStan
- JS/TS: ESLint + Prettier
- CSS/SCSS: Stylelint + Prettier
- JSON/YAML/MD: Prettier

**Pre-push Hooks**:
- Full quality suite
- Coverage assertion

**Usage**:
```bash
# Install hooks
npm run prepare

# Manually run lint-staged
npx lint-staged
```

### 7. Commitlint

**Configuration**: `commitlint.config.cjs`

**Features**:
- Conventional commit format
- Type enforcement
- Subject length limits
- Case restrictions

**Usage**:
```bash
# Validate commit message
npx commitlint --edit <commit-hash>
```

### 8. SRI Generator

**Configuration**: Integrated in Vite build

**Features**:
- SHA384 hash generation
- Automatic on production build
- PHP manifest generation

**Usage**:
```bash
npm run generate:sri
```

### 9. Asset Compressor

**Configuration**: `tools/compress-assets.js`

**Features**:
- Gzip/Brotli compression
- Pre-compressed assets
- Size optimization

**Usage**:
```bash
npm run compress
```

## Combined Quality Workflows

### 1. Full Quality Check

```bash
# PHP + JS + CSS + Type checking + Tests
npm run quality
```

This runs:
- PHP linting (PHPCS)
- JS linting (ESLint)
- CSS linting (Stylelint)
- TypeScript checking
- PHP unit tests

### 2. Pre-commit Workflow

```bash
# Automatically runs on git commit
# 1. Lint-staged (formatting + linting)
# 2. Debug check
```

### 3. Pre-push Workflow

```bash
# Automatically runs on git push
# 1. Full quality suite
# 2. Coverage assertion
```

### 4. CI Pipeline

```bash
# Complete validation
composer ci
```

This runs:
- Composer validation
- Parallel lint
- PHPCS
- PHPStan
- Psalm
- PHPUnit
- Infection (mutation testing)

## Coverage Requirements

### PHP Coverage
- **Target**: 95%+ coverage
- **Tools**: PHPUnit + Xdebug
- **Reports**: Text, HTML, Clover XML

### JavaScript Coverage
- **Target**: 90%+ coverage (when Jest is added)
- **Tools**: TBD (Jest/Vitest)

## Performance Monitoring

### Build Performance
- Chunk size limit: 1000KB
- Min chunk size: 20KB
- Asset inline limit: 4KB
- Module preload polyfill enabled

### Runtime Performance
- No external CDNs
- Optimized chunking strategy
- Tree-shaking enabled
- Modern browser targeting

## Security Tools

### PHP Security
- **PHPCS Security Rules**: Enabled (severity 10)
- **Static Analysis**: Psalm security analysis
- **Dependencies**: Roave security advisories

### JavaScript Security
- **ESLint Security**: No external requests detection
- **SRI**: SHA384 hashes for all assets
- **CSP**: Development headers configured

## Code Quality Metrics

### PHP Metrics
- **Cyclomatic Complexity**: < 10 per function
- **Class Size**: < 300 lines
- **Method Size**: < 50 lines
- **Parameter Count**: < 5 per method

### JavaScript Metrics
- **Function Size**: < 50 lines
- **Component Size**: < 300 lines
- **File Size**: < 1000 lines
- **Cyclomatic Complexity**: < 15

## IDE Integration

### VS Code
- ESLint extension
- Prettier extension
- Stylelint extension
- PHP Intelephense
- PHP Debug

### PhpStorm
- PHPCS integration
- PHPStan integration
- Psalm integration
- ESLint integration
- Prettier integration

## Continuous Improvement

### Baseline Strategy
- PHPStan baseline for gradual improvement
- Psalm suppression for known issues
- Coverage baseline for critical paths

### Regular Maintenance
- Weekly dependency updates
- Monthly security audits
- Quarterly performance reviews
- Bi-annual tool version upgrades

## Documentation

All tools are documented in:
- `docs/code-quality-tools.md` (this file)
- `docs/cli-commands.md` (command reference)
- `README.md` (quick start)

## Summary

This toolchain provides **10/10 code quality** through:

✅ **Static Analysis**: PHPStan, Psalm, ESLint  
✅ **Code Standards**: PHPCS, ESLint, Stylelint  
✅ **Testing**: PHPUnit, Infection  
✅ **Formatting**: Prettier, Pint  
✅ **Security**: SRI, Security rules, Dependency scanning  
✅ **Performance**: Bundle analysis, Optimization  
✅ **Automation**: Husky, Lint-staged, CI/CD  
✅ **Type Safety**: TypeScript, PHP type hints  

The result is enterprise-grade code that is secure, performant, and maintainable.
