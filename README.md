# Affiliate Product Showcase

> **🔒 100% Standalone - No External Dependencies**
> 
> [![Standalone](https://img.shields.io/badge/Standalone-100%25-brightgreen?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
> [![Privacy First](https://img.shields.io/badge/Privacy-First-blue?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
> [![No CDN](https://img.shields.io/badge/No%20CDNs-Purple?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)
> [![No Phone Home](https://img.shields.io/badge/No%20Phone--Home-red?style=for-the-badge)](https://github.com/randomfact236/affiliate-product-showcase)

A modern, enterprise-grade WordPress plugin for showcasing affiliate products with zero external dependencies and strict privacy guarantees.

---

## 🔒 Standalone & Privacy Guarantees

This plugin is **100% standalone** and **privacy-first by design**. We guarantee:

### ✅ What We Do NOT Use

| Category | Status | Details |
|----------|--------|---------|
| **CDNs** | ❌ None | All assets are bundled locally |
| **External Fonts** | ❌ None | Uses system fonts only |
| **External Icons** | ❌ None | SVG icons embedded in codebase |
| **External Libraries** | ❌ None | All dependencies are npm/Composer packages bundled locally |
| **External APIs** | ❌ None | No third-party API calls |
| **Phone Home / Telemetry** | ❌ None | No data sent to any server |
| **External Update Checks** | ❌ None | Uses WordPress core update system only |
| **Analytics** | ❌ None | No tracking or analytics |

### ✅ What We DO Use

| Dependency | Type | Bundled? |
|------------|------|----------|
| TailwindCSS | CSS Framework | ✅ Yes (bundled CSS) |
| Vue.js | Frontend Framework | ✅ Yes (bundled JS) |
| WordPress Core | WordPress API | ✅ Yes (via WordPress) |
| PHPMailer | Email (WordPress) | ✅ Yes (via WordPress) |

### ✅ All Assets Are Local

- **JavaScript**: Bundled via Vite, no external scripts
- **CSS**: TailwindCSS compiled to local assets, no external stylesheets
- **Images**: All images stored locally in plugin directory
- **Fonts**: System fonts only, no Google Fonts or other external font services
- **Icons**: Inline SVGs, no icon font libraries

---

## 📋 Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Features](#features)
- [Privacy Policy](#privacy-policy)
- [Security](#security)
- [Development](#development)
- [Contributing](#contributing)
- [License](#license)

---

## 🚀 Installation

### Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher

### Via WordPress Admin

1. Download the latest release from [GitHub Releases](https://github.com/randomfact236/affiliate-product-showcase/releases)
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the `.zip` file
4. Activate the plugin

### Via FTP/SFTP

1. Download and extract the plugin zip file
2. Upload the `affiliate-product-showcase` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Activate the plugin

### Via WP-CLI

```bash
wp plugin install https://github.com/randomfact236/affiliate-product-showcase/releases/latest/download/affiliate-product-showcase.zip --activate
```

---

## 🎯 Quick Start

### 1. Configure Your Affiliate Settings

After activation, go to **Affiliate Showcase → Settings**:

1. Enter your affiliate tracking ID
2. Configure display settings (grid layout, number of products, etc.)
3. Save your settings

### 2. Add Your First Product

Go to **Affiliate Showcase → Products → Add New**:

1. Enter product name, description, and price
2. Upload product images (stored locally)
3. Add your affiliate link
4. Publish the product

### 3. Display Products on Your Site

Use the block editor or shortcode:

```php
// Shortcode
[affiliate_showcase limit="6"]

// Block
Affiliate Showcase Block (in block editor)

// PHP function
<?php echo do_shortcode('[affiliate_showcase]'); ?>
```

---

## ✨ Features

### Product Management

- Add, edit, and delete affiliate products
- Upload and manage product images (stored locally)
- Organize products with categories and tags
- Set pricing and display options
- Affiliate link management with tracking

### Display Options

- Grid and list layouts
- Responsive design (mobile, tablet, desktop)
- Customizable color schemes
- Filter by category and tags
- Sort by price, date, popularity
- Pagination support

### Performance

- Lazy loading for images
- Caching system built-in
- Optimized database queries
- Minimal CSS/JS footprint
- No external requests = faster load times

### Security

- Input sanitization and validation
- CSRF protection on all forms
- Prepared SQL statements
- Capability-based access control
- No data sent externally
- Regular security audits

---

## 📜 Privacy Policy

This plugin is designed with privacy as a top priority:

### Data Storage

- All product data is stored in your WordPress database
- All images are stored on your server
- No personal data is transmitted to third parties
- No cookies are set by this plugin

### Data Collection

- **This plugin does NOT collect any data**
- **No analytics or tracking**
- **No telemetry or usage statistics**
- **No phone-home mechanisms**

### External Connections

- **This plugin makes NO external connections**
- No CDN usage
- No external fonts or libraries
- No external update checks
- Uses WordPress core's update system only

### User Rights

As a site administrator, you have full control:
- Export all plugin data anytime
- Delete all plugin data via uninstall
- View all stored data in your WordPress database
- Modify or delete individual products

For a complete privacy policy template to use with your site's users, see [docs/privacy-policy-template.md](docs/privacy-policy-template.md).

---

## 🔐 Security

### Security Best Practices

- **Input Validation**: All user inputs are sanitized and validated
- **SQL Injection Protection**: Uses WordPress `wpdb->prepare()` for all queries
- **XSS Protection**: Output escaping with WordPress functions
- **CSRF Protection**: Nonces on all form submissions
- **Capability Checks**: User capability verification before actions
- **No External Calls**: Eliminates external attack vectors

### Vulnerability Reporting

If you discover a security vulnerability, please report it responsibly:

1. Email: security@example.com (replace with actual security email)
2. Do NOT disclose publicly until patched
3. Include details on reproduction steps
4. Allow 14 days for patching before disclosure

---

## 🛠 Development

### Prerequisites

- Node.js 18+ and npm
- PHP 8.0+
- Composer 2.x
- Docker (for local development)

### Setup Development Environment

```bash
# Clone the repository
git clone https://github.com/randomfact236/affiliate-product-showcase.git
cd affiliate-product-showcase

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build development assets
npm run dev

# Start Docker environment
docker-compose up -d
```

### Available Scripts

```bash
# Development
npm run dev          # Start development server with hot reload
npm run build        # Build production assets
npm run watch        # Watch for changes and rebuild

# Code Quality
npm run lint         # Lint JavaScript/CSS
npm run lint:fix     # Auto-fix linting issues
npm run format       # Format code with Prettier

# PHP
composer cs-check    # Check PHP code style
composer cs-fix      # Fix PHP code style
composer test        # Run PHP tests
```

### Project Structure

```
wp-content/plugins/affiliate-product-showcase/
├── assets/              # Frontend assets (CSS, JS)
├── src/
│   ├── Models/         # Data models
│   ├── Services/       # Business logic
│   ├── Repositories/   # Data access
│   ├── Controllers/    # HTTP handlers
│   ├── Views/          # Templates
│   └── Helpers/        # Utility functions
├── tests/              # PHPUnit tests
└── docs/               # Documentation
```

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Workflow

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Write/update tests
5. Run tests: `npm test && composer test`
6. Commit your changes
7. Push to branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

---

## 📄 License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Credits

- Built with [WordPress](https://wordpress.org/)
- Styled with [TailwindCSS](https://tailwindcss.com/)
- Frontend powered by [Vue.js](https://vuejs.org/)

---

## 📞 Support

- **Documentation**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/randomfact236/affiliate-product-showcase/issues)
- **Discussions**: [GitHub Discussions](https://github.com/randomfact236/affiliate-product-showcase/discussions)

---

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

<p align="center">
  <strong>Made with ❤️ for the WordPress community</strong>
</p>
