# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Affiliate disclosure feature with customizable text
- Rate limiting on REST API endpoints (100 req/hour for list, 50 req/hour for create)
- CSP headers for enhanced security on admin pages
- GDPR export/erase hooks for compliance
- Accessibility testing setup with axe-core framework
- Reusable Tailwind CSS components (cards, buttons, forms)
- Multi-site compatibility tests for isolation verification
- Comprehensive plugin documentation in README.md
- Implementation verification reports

### Changed
- Optimized analytics service for high concurrency (cache locking, atomic operations)
- Improved caching with cache locking to prevent stampede
- Enhanced database query performance with batch meta fetching
- Reduced memory usage by disabling settings autoload (autoload=false)
- Removed singleton pattern from Manifest class for better testability
- Added defer/async attributes to scripts for better performance

### Fixed
- Critical security vulnerabilities (ABSPATH protection, REST validation, XSS prevention)
- Database escape using private API replaced with proper escaping methods
- Meta save bug treating false as failure
- Uninstall data loss default changed to false
- REST API exception information disclosure mitigated

### Security
- Added ABSPATH protection to all PHP files
- Implemented rate limiting to prevent API abuse
- Added CSP headers (Content-Security-Policy, X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)
- Enhanced REST API request validation with sanitization
- Input sanitization and validation throughout
- CSRF protection for forms
- SQL injection prevention
- XSS protection

### Performance
- Cache stampede protection with lock mechanism
- posts_per_page limiting to prevent memory exhaustion
- Batch meta queries to eliminate N+1 query problem
- Script defer/async for non-blocking loading
- Settings autoload optimization

### Documentation
- Complete README.md with installation, usage, and contribution guidelines
- Phase 1, 2, 3, 4 safe execution workflows
- Implementation verification report
- Code audit documentation
- Multi-site test documentation

## [1.0.0] - 2024-01-15

### Added
- Initial plugin release
- Product management with custom post type (aps_product)
- REST API for product CRUD operations
- Shortcode support ([aps_product], [aps_products])
- Block editor support for product showcase
- Analytics tracking for views and clicks
- Admin settings page with options
- Affiliate link service with security attributes (rel="nofollow sponsored", target="_blank")
- Widget support for displaying products
- Product grid and single product templates
- Asset management with Vite manifest

### Changed
- N/A

### Fixed
- N/A

### Security
- Input sanitization and validation
- CSRF protection for forms
- SQL injection prevention with prepared statements
- XSS protection with escaping functions
- Nonce verification for AJAX requests

## [0.9.0] - 2024-01-01

### Added
- Beta version for testing
- Core functionality implemented
- Basic product display
- Admin interface
- REST API endpoints

### Changed
- Initial beta release

### Known Issues
- Limited error handling
- Basic caching only
- Minimal validation

---

## Keep Changelog Guide

### What is a change?
- **Added:** New features
- **Changed:** Changes to existing functionality
- **Deprecated:** Soon-to-be-removed features
- **Removed:** Removed features
- **Fixed:** Bug fixes
- **Security:** Security vulnerability fixes

### How to add an entry?
1. Add new entry under [Unreleased]
2. Use the appropriate category (Added, Changed, Fixed, Security)
3. Describe the change in present tense
4. When releasing, move [Unreleased] to version number
5. Add release date
6. Create git tag for version

### Example Entry
```markdown
## [1.2.0] - 2024-02-01

### Added
- New feature description
- Another new feature

### Changed
- Description of change to existing feature

### Fixed
- Bug fix description

### Security
- Security vulnerability fix
```

### Versioning
This project uses Semantic Versioning (MAJOR.MINOR.PATCH):
- **MAJOR:** Incompatible API changes
- **MINOR:** New functionality (backwards compatible)
- **PATCH:** Bug fixes (backwards compatible)
