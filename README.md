# Affiliate Product Showcase

> **Enterprise-Grade WordPress Plugin** | **100% Standalone** | **Privacy-First** | **Zero External Dependencies**

[![WordPress](https://img.shields.io/badge/WordPress-6.4%2B-blue?style=flat-square)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?style=flat-square)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green?style=flat-square)](LICENSE)
[![Standards](https://img.shields.io/badge/Code%20Standards-PSR%2012%20%2B%20WPCS-blue?style=flat-square)](https://www.php-fig.org/psr/psr-12/)

[![Standalone](https://img.shields.io/badge/Standalone-100%25-brightgreen?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
[![Privacy First](https://img.shields.io/badge/Privacy-First-blue?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
[![No CDN](https://img.shields.io/badge/No%20CDNs-Purple?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
[![No Phone Home](https://img.shields.io/badge/No%20Phone--Home-red?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)

A modern, enterprise-grade WordPress plugin for showcasing affiliate products with zero external dependencies, strict privacy guarantees, and comprehensive features.

**Version:** 1.0.0 | **Last Updated:** January 2026 | **PHP:** 7.4-8.3 | **WordPress:** 6.4+

---

## 📋 Quick Navigation

- [🔒 Privacy & Security](#-privacy--security)
- [🚀 Installation](#-installation)
- [⚡ Quick Start](#-quick-start)
- [📖 Documentation](#-documentation)
- [✨ Features](#-features)
- [🛠 Development](#-development)
- [🤝 Contributing](#-contributing)
- [📞 Support](#-support)

---

## 🔒 Privacy & Security Guarantees

### ✅ 100% Standalone - Zero External Dependencies

| Category | Status | Details |
|----------|--------|---------|
| **CDNs** | ❌ **None** | All assets bundled locally |
| **External Fonts** | ❌ **None** | System fonts only |
| **External Icons** | ❌ **None** | Inline SVGs |
| **External Libraries** | ❌ **None** | npm/Composer packages bundled |
| **External APIs** | ❌ **None** | No third-party calls |
| **Telemetry** | ❌ **None** | No data collection |
| **Analytics** | ❌ **None** | No tracking |
| **Update Checks** | ❌ **None** | WordPress core only |

### ✅ Privacy-First by Design

- **No personal data collection**
- **No user tracking or analytics**
- **No cookies or local storage**
- **No data sent to external servers**
- **All data stored locally on your server**
- **GDPR & CCPA compliant out of the box**

### ✅ Security Features

- Input sanitization & validation
- CSRF protection with nonces
- Prepared SQL statements
- Capability-based access control
- XSS protection with output escaping
- Regular security audits

**See:** [SECURITY.md](SECURITY.md) for vulnerability reporting

---

## 🚀 Installation

### System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **WordPress** | 6.4+ | Latest stable |
| **PHP** | 7.4+ | 8.2+ |
| **MySQL** | 5.7+ | 8.0+ |
| **MariaDB** | 10.3+ | 10.6+ |
| **WP-CLI** | 2.8+ | Latest |

### Method 1: WordPress Admin (Recommended)

1. Download latest release: [GitHub Releases](https://github.com/randomfact236/affiliate-product-showcase/releases)
2. Navigate to **Plugins → Add New → Upload Plugin**
3. Upload the `.zip` file
4. Click **Activate**

### Method 2: WP-CLI

```bash
# Install from latest release
wp plugin install https://github.com/randomfact236/affiliate-product-showcase/releases/latest/download/affiliate-product-showcase.zip --activate

# Or from local file
wp plugin install affiliate-product-showcase.zip --activate
```

### Method 3: Manual (FTP/SFTP)

```bash
# Extract and upload
unzip affiliate-product-showcase.zip
scp -r affiliate-product-showcase user@server:/wp-content/plugins/

# Activate via WP-CLI or WordPress admin
wp plugin activate affiliate-product-showcase
```

### Method 4: Docker Development

```bash
cd docker
docker-compose up -d
# WordPress will be available at http://localhost:8080
```

---

## ⚡ Quick Start

### Step 1: Initial Setup

After activation, navigate to **Affiliate Products → Settings**:

```php
// Or via WP-CLI
wp affiliate settings set cache_enabled true --type=boolean
wp affiliate settings set items_per_page 12 --type=integer
```

### Step 2: Add Your First Product

**Via Admin:**
1. Go to **Affiliate Products → Add New**
2. Fill in:
   - Product Name
   - Price (e.g., `99.99`)
   - Affiliate URL
   - Category
   - Description & Features
   - Product Image
3. Click **Publish**

**Via WP-CLI:**
```bash
wp affiliate product create "Wireless Mouse" 29.99 "https://affiliate.com/link" \
  --category=electronics \
  --brand=TechBrand \
  --features="2.4GHz,USB-C,Long Battery" \
  --ribbons=featured
```

### Step 3: Display Products

**Shortcode:**
```php
[affiliate_products limit="6" columns="3" category="electronics"]
```

**Gutenberg Block:**
- Add "Affiliate Products" block
- Configure attributes in sidebar
- Preview and publish

**PHP Template:**
```php
<?php
echo do_shortcode( '[affiliate_products featured="true" limit="4"]' );
?>
```

### Step 4: Advanced Usage

```php
// Multiple filters
[affiliate_products 
  category="electronics,laptops" 
  featured="true" 
  limit="12" 
  columns="4"
  filter="true"
  sort="true"
  pagination="true"
]

// Minimal display
[affiliate_products 
  show_image="false" 
  show_features="false" 
  show_rating="false" 
  layout="list"
]
```

---

## 📖 Documentation

### Core Documentation

| Document | Description |
|----------|-------------|
| **[Shortcode Reference](docs/shortcode-reference.md)** | Complete shortcode attributes & examples |
| **[CLI Commands](docs/cli-commands.md)** | All WP-CLI commands with examples |
| **[Hooks & Filters](docs/hooks-filters.md)** | Action & filter hooks for customization |
| **[Troubleshooting](docs/troubleshooting.md)** | Common issues & solutions |
| **[FAQ](docs/faq.md)** | Frequently asked questions |
| **[Privacy Policy Template](docs/privacy-policy-template.md)** | GDPR/CCPA compliant template |

### Quick Reference

```php
// Basic Usage
[affiliate_products limit="6"]

// With Filters
[affiliate_products category="electronics" featured="true" limit="12"]

// Custom Styling
[affiliate_products class="my-custom-class" style="max-width: 800px;"]

// No Cache (Development)
[affiliate_products cache="false"]
```

### WP-CLI Commands

```bash
# Product Management
wp affiliate product list
wp affiliate product create "Name" 99.99 "URL"
wp affiliate product update 123 --price=89.99
wp affiliate product delete 123

# Import/Export
wp affiliate product import products.csv
wp affiliate product export backup.csv

# Cache Management
wp affiliate cache clear
wp affiliate cache warm

# Maintenance
wp affiliate maintenance stats
wp affiliate maintenance verify --fix
```

---

## ✨ Features

### Product Management

✅ **Create & Manage Products**
- Add/edit/delete affiliate products
- Bulk import/export (CSV/JSON)
- Categories, tags, brands
- Pricing with sale prices
- Stock status tracking
- Featured/trending/on-sale flags

✅ **Rich Product Data**
- Name, description, excerpt
- Price, sale price
- Affiliate URL
- Categories & tags
- Brand
- Features list
- Ribbons/badges
- Images (local storage)

### Display & Layout

✅ **Multiple Layouts**
- Grid (1-6 columns)
- List view
- Table view
- Slider/carousel

✅ **Responsive Design**
- Mobile-first approach
- Custom breakpoints
- Touch-friendly

✅ **Interactive Elements**
- Filter bar
- Search functionality
- Sort dropdown
- Pagination
- Comparison table

### Performance & Caching

✅ **Built-in Caching**
- Query result caching
- Transient-based
- Configurable duration
- Cache warming

✅ **Optimized Queries**
- Efficient database queries
- Lazy loading
- Minimal DOM size
- No external requests

### Security & Privacy

✅ **Enterprise Security**
- Input validation
- SQL injection prevention
- XSS protection
- CSRF tokens
- Capability checks
- Audit logging

✅ **Privacy Compliance**
- GDPR ready
- CCPA compliant
- No data collection
- Local storage only
- Export/delete tools

### Developer Features

✅ **Extensible Architecture**
- PSR-4 autoloading
- Action & filter hooks
- REST API endpoints
- Custom templates
- WP-CLI commands

✅ **Development Tools**
- Vite.js build system
- TailwindCSS framework
- Vue.js components
- PHPUnit tests
- Code quality tools

---

## 🛠 Development

### Setup

```bash
# Clone repository
git clone https://github.com/randomfact236/affiliate-product-showcase.git
cd affiliate-product-showcase

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Start dev server
npm run dev
```

### Available Commands

```bash
# Development
npm run dev          # Hot reload dev server
npm run build        # Production build
npm run watch        # Watch & rebuild

# Code Quality
npm run lint         # Lint JS/CSS
npm run lint:fix     # Auto-fix
npm run format       # Format code

# PHP Quality
composer cs-check    # Check coding standards
composer cs-fix      # Fix standards
composer test        # Run tests
composer analyze     # PHPStan analysis
```

### Project Structure

```
affiliate-product-showcase/
├── assets/                    # Compiled assets
│   ├── css/
│   ├── js/
│   └── images/
├── src/
│   ├── Models/               # Data models
│   ├── Services/             # Business logic
│   ├── Repositories/         # Data access
│   ├── Controllers/          # HTTP handlers
│   ├── Views/                # Templates
│   ├── Helpers/              # Utilities
│   └── CLI/                  # WP-CLI commands
├── tests/                    # PHPUnit tests
├── docs/                     # Documentation
├── docker/                   # Docker setup
└── plan/                     # Development plan
```

### Testing

```bash
# Run all tests
composer test

# Unit tests only
vendor/bin/phpunit --testsuite=unit

# Integration tests
vendor/bin/phpunit --testsuite=integration

# With coverage
vendor/bin/phpunit --coverage-html coverage/
```

---

## 🤝 Contributing

We welcome contributions! Please follow our guidelines:

### Getting Started

1. **Read the docs:** See [CONTRIBUTING.md](CONTRIBUTING.md)
2. **Check issues:** Look for open issues or create one
3. **Fork & branch:** `git checkout -b feature/your-feature`
4. **Code standards:** Follow PSR-12 + WordPress standards
5. **Write tests:** Cover new functionality
6. **Submit PR:** Include description and test results

### Development Workflow

```bash
# 1. Create feature branch
git checkout -b feature/amazing-feature

# 2. Make changes
# ... code ...

# 3. Run quality checks
npm run lint
composer cs-check
composer test

# 4. Commit with conventional commits
git commit -m "feat: add amazing feature

- Implements X functionality
- Adds Y tests
- Fixes Z issue"

# 5. Push & create PR
git push origin feature/amazing-feature
```

### Code Standards

- **PHP:** PSR-12 + WordPress Coding Standards
- **JavaScript:** ESLint (WordPress preset)
- **CSS:** Stylelint (standard rules)
- **Commit Messages:** Conventional Commits

---

## 📞 Support

### Documentation

- **[Shortcodes](docs/shortcode-reference.md)** - Usage examples
- **[CLI](docs/cli-commands.md)** - Command reference
- **[Hooks](docs/hooks-filters.md)** - Customization
- **[Troubleshooting](docs/troubleshooting.md)** - Common issues
- **[FAQ](docs/faq.md)** - Quick answers

### Community & Help

| Channel | Link | Purpose |
|---------|------|---------|
| **GitHub Issues** | [Report Issues](https://github.com/randomfact236/affiliate-product-showcase/issues) | Bug reports & feature requests |
| **GitHub Discussions** | [Discussions](https://github.com/randomfact236/affiliate-product-showcase/discussions) | Q&A & community help |
| **WordPress.org** | [Plugin Page](https://wordpress.org/plugins/affiliate-product-showcase/) | Reviews & support forum |
| **Security** | [SECURITY.md](SECURITY.md) | Vulnerability reporting |

### Professional Support

For enterprise needs, custom development, or priority support:
- **Email:** [Contact maintainer]
- **Response Time:** 24-48 hours
- **Services:** Custom integrations, training, consulting

---

## 📄 License & Legal

### License

This plugin is licensed under the **GPL-2.0-or-later** license.
See [LICENSE](LICENSE) for full details.

**You are free to:**
- Use on unlimited sites
- Modify for your needs
- Distribute (with GPL compatibility)
- Use commercially

### Privacy

See [docs/privacy-policy-template.md](docs/privacy-policy-template.md) for a complete privacy policy template for your site.

### Security

Report vulnerabilities responsibly: [SECURITY.md](SECURITY.md)

---

## 📝 Changelog

### Version 1.0.0 (January 2026)

**Initial Release**
- ✅ Complete product management system
- ✅ Multiple display layouts (grid, list, table, slider)
- ✅ WP-CLI command suite
- ✅ Comprehensive hook system
- ✅ Built-in caching & performance optimization
- ✅ Enterprise security features
- ✅ 100% standalone - zero external dependencies
- ✅ Privacy-first design
- ✅ Full documentation suite

**See:** [CHANGELOG.md](CHANGELOG.md) for detailed version history

---

## 🙏 Credits & Acknowledgments

### Core Technologies

- **[WordPress](https://wordpress.org/)** - Platform foundation
- **[PHP](https://php.net/)** - Backend language
- **[Vite.js](https://vitejs.dev/)** - Build tool
- **[TailwindCSS](https://tailwindcss.com/)** - CSS framework
- **[Vue.js](https://vuejs.org/)** - Frontend framework

### Development Tools

- **[Composer](https://getcomposer.org/)** - PHP dependency management
- **[npm](https://www.npmjs.com/)** - JavaScript package manager
- **[PHPUnit](https://phpunit.de/)** - Testing framework
- **[PHPStan](https://phpstan.org/)** - Static analysis
- **[ESLint](https://eslint.org/)** - JavaScript linting

### Standards & Best Practices

- **[PSR-12](https://www.php-fig.org/psr/psr-12/)** - PHP coding standards
- **[WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)** - WP standards
- **[Conventional Commits](https://www.conventionalcommits.org/)** - Commit message format
- **[Semantic Versioning](https://semver.org/)** - Version numbering

---

## 🎯 Quality Assurance

### Testing Coverage

- ✅ Unit Tests
- ✅ Integration Tests
- ✅ Code Style Checks
- ✅ Static Analysis
- ✅ Security Audits
- ✅ Performance Benchmarks

### Compatibility

- ✅ WordPress 6.4+
- ✅ PHP 7.4, 8.0, 8.1, 8.2, 8.3
- ✅ MySQL 5.7+ / MariaDB 10.3+
- ✅ All modern browsers
- ✅ Mobile responsive
- ✅ Multisite compatible
- ✅ WPML/Polylang ready

---

<p align="center">
  <strong>Enterprise-Grade | Privacy-First | Zero Dependencies</strong><br>
  <sub>Made with ❤️ for the WordPress Community</sub>
</p>
