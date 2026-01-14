# Affiliate Product Showcase

A modern, secure, and performant WordPress plugin for displaying and managing affiliate products with built-in analytics, caching, and REST API support.

## Features

- 🛍️ **Product Management** - Create and manage affiliate products with custom post type
- 📊 **Built-in Analytics** - Track views and clicks with batched processing for performance
- 🎨 **Multiple Display Options** - Shortcodes, Gutenberg blocks, and widgets
- 🔒 **Security First** - Input validation, URL blocking, and XSS protection
- ⚡ **High Performance** - Query caching, strict types, and optimized code
- 🔄 **REST API** - Full CRUD operations via WordPress REST API
- 📱 **Responsive Design** - Mobile-friendly product cards and grids
- 🌍 **GDPR Compliant** - Personal data export and erase hooks
- 🏥 **Health Check** - Built-in health monitoring endpoint

## Requirements

- PHP 8.0 or higher
- WordPress 6.0 or higher
- MySQL 5.7+ or MariaDB 10.2+

## Installation

### From WordPress.org

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "Affiliate Product Showcase"
3. Click **Install Now**
4. Activate the plugin

### Manual Installation

1. Download the plugin ZIP file from the [releases page](https://github.com/randomfact236/affiliate-product-showcase/releases)
2. Go to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin**
4. Select the ZIP file and click **Install Now**
5. Activate the plugin

### From Git

```bash
git clone https://github.com/randomfact236/affiliate-product-showcase.git
cd affiliate-product-showcase
composer install
npm install
```

## Usage

### Shortcodes

Display products anywhere on your site using shortcodes:

```php
// Display latest products
[affiliate_products]

// Limit number of products
[affiliate_products limit="10"]

// Filter by category
[affiliate_products category="electronics"]

// Order products
[affiliate_products orderby="price" order="ASC"]

// Display featured products
[affiliate_products featured="true"]
```

Available shortcode attributes:
- `limit` - Number of products to show (default: 10)
- `category` - Filter by product category slug
- `orderby` - Order by: title, price, date, views, clicks
- `order` - Order direction: ASC, DESC (default: DESC)
- `featured` - Show only featured products (true/false)

### Gutenberg Blocks

Use the **Affiliate Product** block in the block editor:

1. Edit any post or page
2. Click the **+** button to add a block
3. Search for "Affiliate Product"
4. Configure display settings in the block sidebar

### Widgets

Add affiliate products to sidebars and widget areas:

1. Go to **Appearance > Widgets**
2. Drag the **Affiliate Products** widget to any widget area
3. Configure display options
4. Save the widget

## API Documentation

### Authentication

All write operations require authentication with WordPress nonce.

Get a nonce:
```bash
GET /wp-admin/admin-ajax.php?action=rest-nonce
```

Include the nonce in requests:
```
X-WP-Nonce: <your-nonce-token>
```

### Endpoints

#### GET /wp-json/affiliate-product-showcase/v1/products

List all products.

**Query Parameters:**
- `per_page` - Number of items per page (default: 10, max: 100)
- `page` - Current page number (default: 1)
- `orderby` - Order by: title, price, date, views, clicks (default: date)
- `order` - Order direction: asc, desc (default: desc)
- `category` - Filter by category slug

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "title": "Product Name",
      "affiliate_url": "https://example.com/product",
      "price": 19.99,
      "currency": "USD",
      "image_url": "https://example.com/image.jpg",
      "rating": 4.5,
      "badge": "Best Seller",
      "views": 1000,
      "clicks": 150
    }
  ],
  "meta": {
    "total": 50,
    "pages": 5
  }
}
```

#### GET /wp-json/affiliate-product-showcase/v1/products/{id}

Get a single product by ID.

**Response:**
```json
{
  "id": 123,
  "title": "Product Name",
  "affiliate_url": "https://example.com/product",
  "price": 19.99,
  "currency": "USD",
  "description": "Product description",
  "image_url": "https://example.com/image.jpg",
  "rating": 4.5,
  "badge": "Best Seller",
  "views": 1000,
  "clicks": 150
}
```

#### POST /wp-json/affiliate-product-showcase/v1/products

Create a new product.

**Required Fields:**
- `title` - Product title
- `affiliate_url` - Affiliate product URL

**Optional Fields:**
- `price` - Product price
- `currency` - Currency code (default: USD)
- `description` - Product description
- `image_url` - Product image URL (must be from your domain)
- `rating` - Rating (1-5)
- `badge` - Badge text

**Request:**
```json
{
  "title": "Amazing Product",
  "affiliate_url": "https://example.com/product",
  "price": 29.99,
  "currency": "USD",
  "description": "A great product description",
  "image_url": "https://yoursite.com/product.jpg",
  "rating": 4.5,
  "badge": "Hot"
}
```

**Response:**
```json
{
  "id": 124,
  "title": "Amazing Product",
  ...
}
```

#### PUT /wp-json/affiliate-product-showcase/v1/products/{id}

Update an existing product.

**Request:** Same as POST endpoint

#### DELETE /wp-json/affiliate-product-showcase/v1/products/{id}

Delete a product.

**Response:**
```json
{
  "deleted": true,
  "previous": {
    "id": 124,
    "title": "Amazing Product"
  }
}
```

#### GET /wp-json/affiliate-product-showcase/v1/analytics

Get analytics data.

**Response:**
```json
{
  "data": {
    "123": {
      "views": 1000,
      "clicks": 150
    },
    "124": {
      "views": 500,
      "clicks": 75
    }
  }
}
```

#### POST /wp-json/affiliate-product-showcase/v1/analytics/track

Track analytics events.

**Request:**
```json
{
  "product_id": 123,
  "event": "click" // or "view"
}
```

#### GET /wp-json/affiliate-product-showcase/v1/health

Health check endpoint for monitoring.

**Response (Healthy):**
```json
{
  "status": "healthy",
  "data": {
    "timestamp": "2026-01-14 12:00:00",
    "plugin_version": "1.0.0",
    "checks": {
      "database": "healthy",
      "cache": "healthy",
      "filesystem": "healthy",
      "php": "healthy",
      "wordpress": "healthy"
    }
  }
}
```

**Response (Unhealthy):**
```json
{
  "status": "unhealthy",
  "data": {
    "timestamp": "2026-01-14 12:00:00",
    "plugin_version": "1.0.0",
    "checks": {
      "database": "unhealthy",
      "cache": "healthy",
      "filesystem": "healthy",
      "php": "healthy",
      "wordpress": "healthy"
    }
  }
}
```

## Development

### Project Structure

```
affiliate-product-showcase/
├── src/                    # PHP source code (PSR-4)
│   ├── Admin/              # Admin functionality
│   ├── Assets/             # Asset management
│   ├── Blocks/             # Gutenberg blocks
│   ├── Cache/              # Caching layer
│   ├── Factories/          # Object factories
│   ├── Formatters/         # Data formatters
│   ├── Logging/            # PSR-3 logging
│   ├── Models/             # Data models
│   ├── Public/             # Frontend functionality
│   ├── Repositories/       # Data access layer
│   ├── Rest/               # REST API controllers
│   ├── Services/           # Business logic
│   └── Plugin/             # Core plugin classes
├── tests/                  # PHPUnit tests
├── frontend/               # Frontend assets
├── docs/                   # Documentation
└── scripts/                # Build/utility scripts
```

### Setup Development Environment

1. **Clone the repository:**
   ```bash
   git clone https://github.com/randomfact236/affiliate-product-showcase.git
   cd affiliate-product-showcase
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

4. **Build frontend assets:**
   ```bash
   npm run build
   ```

5. **Link to WordPress:**
   ```bash
   # Symlink to WordPress plugins directory
   ln -s $(pwd)/wp-content/plugins/affiliate-product-showcase /path/to/wordpress/wp-content/plugins/
   ```

### Running Tests

**PHP Unit Tests:**
```bash
vendor/bin/phpunit
```

**JavaScript Tests:**
```bash
npm test
```

**Linting:**
```bash
# PHP CS
vendor/bin/phpcs --standard=WordPress src/

# PHPStan (static analysis)
vendor/bin/phpstan analyse src/ --level=5

# JavaScript linting
npm run lint
```

### Coding Standards

- **PHP:** PSR-12 coding standards
- **JavaScript:** ESLint with Prettier
- **Documentation:** PHPDoc blocks for all public methods
- **Types:** Strict types enabled in all files

### Building for Production

```bash
# Build optimized assets
npm run build

# Run tests
vendor/bin/phpunit

# Create distribution package
npm run package
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository**
   - Go to https://github.com/randomfact236/affiliate-product-showcase
   - Click "Fork" button

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Follow PSR-12 coding standards
   - Add tests for new functionality
   - Update documentation as needed
   - Keep commits focused and atomic

4. **Test your changes**
   ```bash
   vendor/bin/phpunit
   npm run lint
   ```

5. **Commit your changes**
   ```bash
   git add .
   git commit -m "feat: add your feature description"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Create a Pull Request**
   - Go to the original repository
   - Click "Pull Requests"
   - Click "New Pull Request"
   - Fill in the PR template
   - Wait for review

### Commit Message Format

Follow [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes (formatting)
- `refactor:` Code refactoring
- `test:` Test additions/changes
- `chore:` Maintenance tasks

Example:
```
feat(products): add batch product import API

- Added POST /products/batch endpoint
- Supports importing up to 100 products
- Validates all data before insertion

Closes #123
```

## Support

### Documentation

- **Main Docs:** https://github.com/randomfact236/affiliate-product-showcase/wiki
- **API Reference:** https://github.com/randomfact236/affiliate-product-showcase/wiki/API-Reference
- **Developer Guide:** https://github.com/randomfact236/affiliate-product-showcase/wiki/Developer-Guide

### Getting Help

- **GitHub Issues:** https://github.com/randomfact236/affiliate-product-showcase/issues
- **Discussions:** https://github.com/randomfact236/affiliate-product-showcase/discussions
- **Email:** support@example.com

When reporting issues, please include:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected behavior
- Actual behavior

## License

This plugin is licensed under the GPL v2 or later. See [LICENSE](LICENSE) file for details.

## Credits

- Built with WordPress REST API
- Uses PSR-3 for logging
- Follows PSR-12 coding standards
- Caching via WordPress Object Cache API

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and release notes.

---

**Current Version:** 1.0.0  
**Last Updated:** January 14, 2026
