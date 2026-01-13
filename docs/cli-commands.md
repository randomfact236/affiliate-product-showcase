# CLI Commands Reference

## Overview

The Affiliate Product Showcase plugin provides comprehensive WP-CLI integration for efficient management of affiliate products, settings, and maintenance tasks. All commands are fully documented and support dry-run modes for safe execution.

## Installation & Setup

### Prerequisites

- WordPress 6.4+
- WP-CLI 2.8+
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+

### Verify WP-CLI Installation

```bash
wp --info
```

### Plugin Activation

```bash
# Activate the plugin
wp plugin activate affiliate-product-showcase

# Verify activation
wp plugin status affiliate-product-showcase
```

## Core Commands

### Product Management

#### `wp affiliate product list`

List all affiliate products with filtering and formatting options.

**Syntax:**
```bash
wp affiliate product list [--format=<format>] [--category=<category>] [--status=<status>] [--limit=<limit>] [--fields=<fields>]
```

**Options:**
- `--format` - Output format: table, json, csv, yaml, count (default: table)
- `--category` - Filter by category slug or ID
- `--status` - Filter by status: publish, draft, pending (default: publish)
- `--limit` - Limit number of results (default: 20)
- `--fields` - Comma-separated fields to display (default: ID,post_title,price,category,affiliate_url)

**Examples:**
```bash
# List all products
wp affiliate product list

# List as JSON
wp affiliate product list --format=json

# Filter by category
wp affiliate product list --category=electronics

# Show specific fields
wp affiliate product list --fields=ID,post_title,price,affiliate_url

# Limit results
wp affiliate product list --limit=10

# List draft products
wp affiliate product list --status=draft
```

**Output Fields:**
- `ID` - Product ID
- `post_title` - Product name
- `price` - Product price
- `sale_price` - Sale price (if applicable)
- `category` - Category name
- `brand` - Brand name
- `affiliate_url` - Affiliate link URL
- `status` - Post status
- `featured` - Featured status (true/false)
- `stock_status` - Stock status

#### `wp affiliate product create`

Create a new affiliate product.

**Syntax:**
```bash
wp affiliate product create <name> <price> <affiliate_url> [--category=<category>] [--brand=<brand>] [--description=<description>] [--image=<image_url>] [--featured] [--trending] [--on_sale] [--sale_price=<sale_price>] [--stock_status=<status>] [--features=<features>] [--ribbons=<ribbons>] [--dry-run]
```

**Arguments:**
- `name` - Product name (required)
- `price` - Product price (required, numeric)
- `affiliate_url` - Affiliate link URL (required)

**Options:**
- `--category` - Category slug or ID
- `--brand` - Brand name
- `--description` - Product description
- `--image` - Image URL
- `--featured` - Mark as featured
- `--trending` - Mark as trending
- `--on_sale` - Mark as on sale
- `--sale_price` - Sale price
- `--stock_status` - in_stock, out_of_stock, on_backorder (default: in_stock)
- `--features` - Comma-separated features
- `--ribbons` - Comma-separated ribbon slugs
- `--dry-run` - Preview without creating

**Examples:**
```bash
# Basic product
wp affiliate product create "Wireless Mouse" 29.99 "https://example.com/affiliate/123" --category=electronics

# Full product with all options
wp affiliate product create "Gaming Laptop" 1299.99 "https://example.com/affiliate/456" \
  --category=laptops \
  --brand=TechBrand \
  --description="High-performance gaming laptop" \
  --image="https://example.com/image.jpg" \
  --featured \
  --on_sale \
  --sale_price=1099.99 \
  --features="16GB RAM,RTX 3060,1TB SSD" \
  --ribbons=best-seller,featured

# Dry run to preview
wp affiliate product create "Test Product" 99.99 "https://example.com/test" --dry-run
```

#### `wp affiliate product update`

Update an existing affiliate product.

**Syntax:**
```bash
wp affiliate product update <id> [--name=<name>] [--price=<price>] [--affiliate_url=<url>] [--category=<category>] [--brand=<brand>] [--description=<description>] [--image=<image_url>] [--featured=<featured>] [--trending=<trending>] [--on_sale=<on_sale>] [--sale_price=<sale_price>] [--stock_status=<status>] [--features=<features>] [--ribbons=<ribbons>] [--dry-run]
```

**Arguments:**
- `id` - Product ID (required)

**Options:**
- All options from `create` command, all optional

**Examples:**
```bash
# Update price
wp affiliate product update 123 --price=39.99

# Update multiple fields
wp affiliate product update 123 --sale_price=29.99 --on_sale=true --featured=true

# Change category
wp affiliate product update 123 --category=electronics

# Remove featured status
wp affiliate product update 123 --featured=false

# Update features
wp affiliate product update 123 --features="New Feature 1,New Feature 2"
```

#### `wp affiliate product delete`

Delete one or more affiliate products.

**Syntax:**
```bash
wp affiliate product delete <id> [<id>...] [--force] [--dry-run]
```

**Arguments:**
- `id` - Product ID(s) (required, can be multiple)

**Options:**
- `--force` - Skip trash and delete permanently
- `--dry-run` - Preview what would be deleted

**Examples:**
```bash
# Delete single product (moves to trash)
wp affiliate product delete 123

# Delete permanently
wp affiliate product delete 123 --force

# Delete multiple products
wp affiliate product delete 123 456 789

# Preview deletion
wp affiliate product delete 123 --dry-run
```

#### `wp affiliate product get`

Get detailed information about a specific product.

**Syntax:**
```bash
wp affiliate product get <id> [--format=<format>] [--fields=<fields>]
```

**Arguments:**
- `id` - Product ID (required)

**Options:**
- `--format` - Output format: table, json, yaml (default: table)
- `--fields` - Comma-separated fields to display

**Examples:**
```bash
# Get full product details
wp affiliate product get 123

# Get as JSON
wp affiliate product get 123 --format=json

# Get specific fields
wp affiliate product get 123 --fields=ID,post_title,price,affiliate_url,features
```

#### `wp affiliate product import`

Import products from CSV or JSON file.

**Syntax:**
```bash
wp affiliate product import <file> [--format=<format>] [--category=<category>] [--dry-run] [--update-existing]
```

**Arguments:**
- `file` - Path to import file (required)

**Options:**
- `--format` - File format: csv, json (default: csv)
- `--category` - Default category for imported products
- `--dry-run` - Validate without importing
- `--update-existing` - Update products with matching IDs

**CSV Format:**
```csv
name,price,affiliate_url,category,brand,description,image,featured,on_sale,sale_price,stock_status,features,ribbons
"Wireless Mouse",29.99,https://example.com/123,electronics,TechBrand,"Wireless mouse",https://example.com/img.jpg,true,true,19.99,in_stock,"2.4GHz,USB-C","featured,best-seller"
"Laptop",999.99,https://example.com/456,laptops,BrandX,"Gaming laptop",https://example.com/img2.jpg,false,false,,in_stock,"16GB RAM,RTX 3060",
```

**JSON Format:**
```json
[
  {
    "name": "Wireless Mouse",
    "price": 29.99,
    "affiliate_url": "https://example.com/123",
    "category": "electronics",
    "brand": "TechBrand",
    "description": "Wireless mouse",
    "image": "https://example.com/img.jpg",
    "featured": true,
    "on_sale": true,
    "sale_price": 19.99,
    "stock_status": "in_stock",
    "features": ["2.4GHz", "USB-C"],
    "ribbons": ["featured", "best-seller"]
  }
]
```

**Examples:**
```bash
# Import from CSV
wp affiliate product import products.csv --format=csv

# Import from JSON
wp affiliate product import products.json --format=json

# Import with default category
wp affiliate product import products.csv --category=imported

# Validate import file
wp affiliate product import products.csv --dry-run

# Update existing products
wp affiliate product import products.csv --update-existing
```

#### `wp affiliate product export`

Export products to CSV or JSON file.

**Syntax:**
```bash
wp affiliate product export <file> [--format=<format>] [--category=<category>] [--status=<status>] [--fields=<fields>]
```

**Arguments:**
- `file` - Path to export file (required)

**Options:**
- `--format` - Export format: csv, json (default: csv)
- `--category` - Filter by category
- `--status` - Filter by status (default: publish)
- `--fields` - Comma-separated fields to export

**Examples:**
```bash
# Export all products to CSV
wp affiliate product export all-products.csv

# Export to JSON
wp affiliate product export all-products.json --format=json

# Export specific category
wp affiliate product export electronics.csv --category=electronics

# Export specific fields
wp affiliate product export products.csv --fields=name,price,affiliate_url,category
```

### Category Management

#### `wp affiliate category list`

List all product categories.

**Syntax:**
```bash
wp affiliate category list [--format=<format>] [--fields=<fields>]
```

**Options:**
- `--format` - Output format (default: table)
- `--fields` - Fields to display

**Examples:**
```bash
wp affiliate category list
wp affiliate category list --format=json
wp affiliate category list --fields=term_id,name,slug,count
```

#### `wp affiliate category create`

Create a new category.

**Syntax:**
```bash
wp affiliate category create <name> [--slug=<slug>] [--description=<description>] [--parent=<parent_id>]
```

**Arguments:**
- `name` - Category name (required)

**Options:**
- `--slug` - URL-friendly slug
- `--description` - Category description
- `--parent` - Parent category ID

**Examples:**
```bash
wp affiliate category create "Electronics"
wp affiliate category create "Laptops" --slug=laptops --parent=10
```

#### `wp affiliate category delete`

Delete a category.

**Syntax:**
```bash
wp affiliate category delete <id> [--force]
```

**Arguments:**
- `id` - Category ID or slug (required)

**Options:**
- `--force` - Force delete

**Examples:**
```bash
wp affiliate category delete electronics
wp affiliate category delete 10 --force
```

### Settings Management

#### `wp affiliate settings list`

List all plugin settings.

**Syntax:**
```bash
wp affiliate settings list [--format=<format>]
```

**Examples:**
```bash
wp affiliate settings list
wp affiliate settings list --format=json
```

#### `wp affiliate settings get`

Get a specific setting value.

**Syntax:**
```bash
wp affiliate settings get <key>
```

**Arguments:**
- `key` - Setting key (required)

**Examples:**
```bash
wp affiliate settings get cache_enabled
wp affiliate settings get default_currency
```

#### `wp affiliate settings set`

Set a plugin setting.

**Syntax:**
```bash
wp affiliate settings set <key> <value> [--type=<type>]
```

**Arguments:**
- `key` - Setting key (required)
- `value` - Setting value (required)

**Options:**
- `--type` - Value type: string, integer, boolean, array (default: string)

**Examples:**
```bash
wp affiliate settings set cache_enabled true --type=boolean
wp affiliate settings set default_currency USD --type=string
wp affiliate settings set items_per_page 20 --type=integer
```

#### `wp affiliate settings reset`

Reset settings to defaults.

**Syntax:**
```bash
wp affiliate settings reset [--confirm]
```

**Options:**
- `--confirm` - Skip confirmation prompt

**Examples:**
```bash
wp affiliate settings reset
wp affiliate settings reset --confirm
```

### Cache Management

#### `wp affiliate cache clear`

Clear plugin cache.

**Syntax:**
```bash
wp affiliate cache clear [--type=<type>]
```

**Options:**
- `--type` - Cache type: all, products, queries, transients (default: all)

**Examples:**
```bash
# Clear all cache
wp affiliate cache clear

# Clear only product cache
wp affiliate cache clear --type=products

# Clear transients
wp affiliate cache clear --type=transients
```

#### `wp affiliate cache warm`

Pre-warm cache for better performance.

**Syntax:**
```bash
wp affiliate cache warm [--category=<category>] [--limit=<limit>]
```

**Options:**
- `--category` - Warm cache for specific category
- `--limit` - Number of products to cache

**Examples:**
```bash
# Warm all product cache
wp affiliate cache warm

# Warm specific category
wp affiliate cache warm --category=electronics

# Warm top 50 products
wp affiliate cache warm --limit=50
```

### Maintenance Commands

#### `wp affiliate maintenance cleanup`

Perform cleanup and maintenance tasks.

**Syntax:**
```bash
wp affiliate maintenance cleanup [--type=<type>] [--dry-run]
```

**Options:**
- `--type` - Cleanup type: all, orphaned, expired, invalid (default: all)
- `--dry-run` - Preview what would be cleaned

**Examples:**
```bash
# Full cleanup
wp affiliate maintenance cleanup

# Remove orphaned products
wp affiliate maintenance cleanup --type=orphaned

# Preview cleanup
wp affiliate maintenance cleanup --dry-run
```

#### `wp affiliate maintenance verify`

Verify plugin data integrity.

**Syntax:**
```bash
wp affiliate maintenance verify [--type=<type>] [--fix]
```

**Options:**
- `--type` - Verification type: all, products, settings, relationships (default: all)
- `--fix` - Attempt to fix issues automatically

**Examples:**
```bash
# Verify everything
wp affiliate maintenance verify

# Verify products only
wp affiliate maintenance verify --type=products

# Verify and fix
wp affiliate maintenance verify --fix
```

#### `wp affiliate maintenance stats`

Display plugin statistics.

**Syntax:**
```bash
wp affiliate maintenance stats [--format=<format>]
```

**Options:**
- `--format` - Output format: table, json (default: table)

**Examples:**
```bash
wp affiliate maintenance stats
wp affiliate maintenance stats --format=json
```

### Reporting Commands

#### `wp affiliate report clicks`

Generate affiliate click report.

**Syntax:**
```bash
wp affiliate report clicks [--start=<date>] [--end=<date>] [--format=<format>] [--product=<product_id>]
```

**Options:**
- `--start` - Start date (YYYY-MM-DD)
- `--end` - End date (YYYY-MM-DD)
- `--format` - Output format (default: table)
- `--product` - Filter by product ID

**Examples:**
```bash
# Last 30 days
wp affiliate report clicks

# Specific date range
wp affiliate report clicks --start=2024-01-01 --end=2024-01-31

# For specific product
wp affiliate report clicks --product=123

# Export as JSON
wp affiliate report clicks --format=json
```

#### `wp affiliate report revenue`

Generate revenue report (if tracking enabled).

**Syntax:**
```bash
wp affiliate report revenue [--start=<date>] [--end=<date>] [--format=<format>] [--product=<product_id>]
```

**Options:**
- Same as clicks report

**Examples:**
```bash
wp affiliate report revenue --start=2024-01-01 --end=2024-01-31
```

#### `wp affiliate report products`

Generate product performance report.

**Syntax:**
```bash
wp affiliate report products [--limit=<limit>] [--format=<format>] [--sort=<sort>]
```

**Options:**
- `--limit` - Number of products (default: 10)
- `--format` - Output format (default: table)
- `--sort` - Sort by: clicks, revenue, views (default: clicks)

**Examples:**
```bash
# Top 10 by clicks
wp affiliate report products

# Top 20 by revenue
wp affiliate report products --limit=20 --sort=revenue
```

### Utility Commands

#### `wp affiliate tools validate-links`

Validate affiliate links for broken URLs.

**Syntax:**
```bash
wp affiliate tools validate-links [--product=<product_id>] [--timeout=<seconds>] [--dry-run]
```

**Options:**
- `--product` - Validate specific product
- `--timeout` - Request timeout in seconds (default: 5)
- `--dry-run` - Check without marking status

**Examples:**
```bash
# Validate all links
wp affiliate tools validate-links

# Validate specific product
wp affiliate tools validate-links --product=123

# With custom timeout
wp affiliate tools validate-links --timeout=10
```

#### `wp affiliate tools generate-test-data`

Generate test products for development.

**Syntax:**
```bash
wp affiliate tools generate-test-data <count> [--category=<category>] [--dry-run]
```

**Arguments:**
- `count` - Number of products to generate (required)

**Options:**
- `--category` - Category for test products
- `--dry-run` - Preview without creating

**Examples:**
```bash
# Generate 10 test products
wp affiliate tools generate-test-data 10

# Generate in specific category
wp affiliate tools generate-test-data 20 --category=test
```

#### `wp affiliate tools flush-permalinks`

Flush permalinks for affiliate product URLs.

**Syntax:**
```bash
wp affiliate tools flush-permalinks
```

**Examples:**
```bash
wp affiliate tools flush-permalinks
```

## Global Options

All commands support these global options:

- `--dry-run` - Preview without making changes
- `--format` - Output format
- `--fields` - Specific fields to display
- `--quiet` - Suppress output
- `--debug` - Show debug information

## Exit Codes

- `0` - Success
- `1` - General error
- `2` - Invalid arguments
- `3` - Permission denied
- `4` - Not found
- `5` - Validation error

## Best Practices

### 1. Always Use Dry-Run First

```bash
# Preview before making changes
wp affiliate product delete 123 --dry-run
wp affiliate product import new-products.csv --dry-run
```

### 2. Backup Before Bulk Operations

```bash
# Export before bulk delete
wp affiliate product export backup-$(date +%Y%m%d).csv

# Then perform operation
wp affiliate product delete 123 456 789
```

### 3. Use Specific Filters

```bash
# Instead of listing all
wp affiliate product list --limit=50 --category=electronics
```

### 4. Verify After Changes

```bash
# After import
wp affiliate product list --category=imported --limit=10

# After update
wp affiliate product get 123
```

### 5. Monitor Performance

```bash
# Check stats regularly
wp affiliate maintenance stats

# Clear cache after major changes
wp affiliate cache clear
```

## Error Handling

### Common Errors

**Permission Denied:**
```bash
# Ensure proper capabilities
wp cap add administrator affiliate_manage_products
```

**Invalid Product ID:**
```bash
# Verify product exists
wp affiliate product list --fields=ID
```

**Import Validation Failed:**
```bash
# Check CSV format
wp affiliate product import file.csv --dry-run
```

### Debug Mode

Enable debug output:
```bash
WP_DEBUG=true wp affiliate product list --debug
```

## Automation Examples

### Cron Job for Daily Reports

```bash
# Add to crontab
0 8 * * * cd /var/www/html && wp affiliate report clicks --start=yesterday --end=yesterday --format=json > /var/log/affiliate-clicks-$(date +\%Y\%m\%d).json
```

### Bash Script for Bulk Updates

```bash
#!/bin/bash
# bulk-update-prices.sh

echo "Starting price update..."
wp affiliate product list --format=json | jq -c '.[] | select(.category == "electronics")' | while read product; do
    ID=$(echo $product | jq -r '.ID')
    NEW_PRICE=$(echo $product | jq -r '.price * 0.9') # 10% discount
    wp affiliate product update $ID --price=$NEW_PRICE
    echo "Updated product $ID to $NEW_PRICE"
done
echo "Done!"
```

### PowerShell Script for Windows

```powershell
# export-and-backup.ps1
$date = Get-Date -Format "yyyyMMdd"
wp affiliate product export "backup-$date.csv"
wp affiliate cache clear
Write-Host "Backup completed: backup-$date.csv"
```

## Integration with CI/CD

### GitHub Actions Example

```yaml
name: Affiliate Product Sync
on:
  schedule:
    - cron: '0 2 * * *'

jobs:
  sync:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup WP-CLI
        run: |
          curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
          chmod +x wp-cli.phar
          sudo mv wp-cli.phar /usr/local/bin/wp
      - name: Import Products
        run: |
          wp affiliate product import products.csv --dry-run
          wp affiliate product import products.csv
```

## Troubleshooting

### Command Not Found

```bash
# Ensure WP-CLI is installed
wp --info

# Check plugin is active
wp plugin status affiliate-product-showcase
```

### Permission Issues

```bash
# Check user capabilities
wp cap list $(wp user get $(wp user list --field=ID --format=csv | head -1) --field=roles)

# Add capabilities if needed
wp cap add administrator affiliate_manage_products affiliate_manage_settings
```

### Database Errors

```bash
# Check database integrity
wp affiliate maintenance verify --fix

# Clear transients
wp affiliate cache clear --type=transients
```

## Performance Tips

1. **Use --limit** for large datasets
2. **Batch operations** when possible
3. **Clear cache** after bulk operations
4. **Use --dry-run** to validate before execution
5. **Monitor query performance** with --debug flag

## Version Compatibility

- WP-CLI: 2.8+
- WordPress: 6.4+
- PHP: 7.4+
- MySQL: 5.7+ / MariaDB: 10.3+

---

*Last updated: January 2026*
*Plugin Version: 1.0.0*
*WP-CLI Version: 2.8+*
