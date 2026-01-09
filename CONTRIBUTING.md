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
