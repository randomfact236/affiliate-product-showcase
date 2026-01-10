# Contributing Guidelines

Thank you for contributing. This document summarizes the project's branching workflow, commit message style, code standards, and pull request process.

Branch workflow
- `main` — production; protected and merged only via PR.
- `develop` — integration branch for ongoing development.
- `feature/*` — new features (branch from `develop`).
- `hotfix/*` — critical fixes (branch from `main`).
- `release/*` — release preparation (branch from `develop`).

Commit message format
- Use Conventional Commits: `type(scope?): subject`
- Examples:
  - `feat(assets): add product-grid component`
  - `fix(api): handle empty product list`
  - `docs: update README examples`
- Common types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `chore`

Code standards
- PHP: WordPress Coding Standards (WPCS), PSR-12 where applicable, `declare(strict_types=1)` in PHP files, and full PHPDoc blocks for public APIs.
- JS/CSS: ESLint + Prettier, follow best practices for accessibility and performance.
- Run linters and tests locally before opening a PR.

Pull request process
- Open PR from `feature/*` → `develop` (or `hotfix/*` → `main` for urgent fixes).
- Include a clear description, changelog notes, and testing instructions.
- Ensure CI passes (lint, static analysis, unit tests, build).
- Require at least one reviewer approval before merge.

Testing requirements
- All tests must pass in CI before merging.
- Aim for at least 80% test coverage for new features; critical code paths should be covered.

Examples of good commit messages
- `feat(api): add products listing endpoint`
- `fix(auth): correct token refresh logic`
- `docs: add contributing guidelines and developer notes`

PR template
- See `.github/PULL_REQUEST_TEMPLATE.md` (add if needed) — PRs should include summary, related issues, testing steps, and screenshots if relevant.

Thank you for helping keep this project healthy and maintainable.
# Contributing

Thanks for your interest in contributing.

## Ground rules

- Be respectful and constructive.
- Keep pull requests focused and small when possible.
- Add or update documentation/tests when you change behavior.

## Development setup

This repository contains a WordPress environment plus the plugin source.

### Prerequisites

- PHP (compatible with plugin requirements)
- Composer
- Node.js (>= 18) and npm

### Install dependencies (plugin)

From the plugin directory:

1. `cd wp-content/plugins/affiliate-product-showcase`
2. `composer install`
3. `npm install`

## Code quality

Run these from `wp-content/plugins/affiliate-product-showcase`:

- PHP tests: `composer test`
- JS lint: `npm run lint`
- JS formatting: `npm run format`

## Submitting changes

1. Fork the repository
2. Create a feature branch from `main`
3. Make your changes
4. Run the checks listed above
5. Open a pull request with:
	- what changed
	- why it changed
	- how to test it

## Reporting bugs

For non-security bugs, open an issue with:

- WordPress version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
