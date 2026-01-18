# Database Migrations Guide

This guide explains how to work with database migrations in the Affiliate Product Showcase plugin.

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Creating New Migrations](#creating-new-migrations)
- [Running Migrations](#running-migrations)
- [Rolling Back Migrations](#rolling-back-migrations)
- [Versioning Strategy](#versioning-strategy)
- [Deployment Notes](#deployment-notes)
- [Troubleshooting](#troubleshooting)

## Overview

The Affiliate Product Showcase plugin uses a migration system to manage database schema changes. This system provides:

- **Version tracking** - Database schema version stored in WordPress options
- **Atomic operations** - Transactions ensure data integrity
- **Rollback capability** - Safely undo migrations if needed
- **Migration history** - Log of all migration executions

## Architecture

### Components

```
src/Database/
├── Database.php          # Database access layer
├── Migrations.php        # Migration manager
└── seeders/
    └── sample-products.php  # Sample data seeder
```

### Database Tables

The plugin creates custom tables with proper WordPress prefix support:

- `wp_affiliate_products_meta` - Product metadata
- `wp_affiliate_products_submissions` - Product form submissions

**Important:** Never hardcode `wp_` prefix. Always use `$wpdb->prefix` to support:
- Custom table prefixes
- Multisite installations
- Subdirectory WordPress installs

### Key Classes

   - Database Class

The `Database` class provides a standardized database access layer:

```php
use AffiliateProductShowcase\Database\Database;

$db = new Database();

// Get table name with proper prefix
$table = $db->get_table_name('meta'); // Returns wp_affiliate_products_meta

// Safe queries with prepared statements
$sql = $db->prepare("SELECT * FROM %s WHERE id = %d", $table, $id);
$results = $db->get_results($sql);

// CRUD operations
$db->insert('meta', ['product_id' => 1, 'meta_key' => 'price']);
$db->update('meta', ['meta_value' => '99.99'], ['id' => 1]);
$db->delete('meta', ['id' => 1]);
```

   - Migrations Class

The `Migrations` class manages schema versioning:

```php
use AffiliateProductShowcase\Database\Database;
use AffiliateProductShowcase\Database\Migrations;

$db = new Database();
$migrations = new Migrations($db);

// Check if migration is needed
if ($migrations->needs_migration()) {
    // Run pending migrations
    $migrations->run();
}

// Rollback last migration
$migrations->rollback();

// Rollback to specific version
$migrations->rollback('1.0.0');
```

## Creating New Migrations

### Step 1: Define the Migration

Open `src/Database/Migrations.php` and add your migration to the `register_migrations()` method:

```php
private function register_migrations(): void {
    $this->migrations = [
        '1.0.0' => [
            'up' => [$this, 'create_meta_table'],
            'down' => [$this, 'drop_meta_table'],
            'description' => 'Create affiliate products meta table',
        ],
        '1.0.1' => [
            'up' => [$this, 'create_submissions_table'],
            'down' => [$this, 'drop_submissions_table'],
            'description' => 'Create affiliate products submissions table',
        ],
        // Add your new migration here
        '1.1.0' => [
            'up' => [$this, 'add_rating_column_to_submissions'],
            'down' => [$this, 'remove_rating_column_from_submissions'],
            'description' => 'Add rating column to submissions table',
        ],
    ];
}
```

### Step 2: Implement Up Migration

Add a method to create/update your schema:

```php
/**
 * Migration 1.1.0: Add rating column to submissions (UP)
 *
 * @since 1.1.0
 * @return bool True on success, false on failure
 */
private function add_rating_column_to_submissions(): bool {
    $table_name = $this->db->get_table_name('submissions');
    
    $sql = "ALTER TABLE $table_name 
            ADD COLUMN rating decimal(3,2) DEFAULT 0.00
            AFTER category";
    
    return $this->db->query($sql) !== false;
}
```

### Step 3: Implement Down Migration

Add a method to rollback your changes:

```php
/**
 * Migration 1.1.0: Remove rating column from submissions (DOWN)
 *
 * @since 1.1.0
 * @return bool True on success, false on failure
 */
private function remove_rating_column_from_submissions(): bool {
    $table_name = $this->db->get_table_name('submissions');
    
    $sql = "ALTER TABLE $table_name 
            DROP COLUMN rating";
    
    return $this->db->query($sql) !== false;
}
```

### Step 4: Test Your Migration

Test both directions:

```php
// Test up migration
$migrations->run();

// Test down migration
$migrations->rollback();
```

## Running Migrations

### Automatic Migration

Migrations run automatically on plugin activation. The activation hook handles migration:

```php
// In your plugin main file
register_activation_hook(__FILE__, function() {
    $db = new Database();
    $migrations = new Migrations($db);
    
    if ($migrations->needs_migration()) {
        $migrations->run();
    }
});
```

### Manual Migration

You can also run migrations manually:

```php
use AffiliateProductShowcase\Database\Database;
use AffiliateProductShowcase\Database\Migrations;

$db = new Database();
$migrations = new Migrations($db);

// Check current version
$current_version = $migrations->get_current_version();
echo "Current version: $current_version\n";

// Check latest version
$latest_version = $migrations->get_latest_version();
echo "Latest version: $latest_version\n";

// Run pending migrations
if ($migrations->needs_migration()) {
    $result = $migrations->run();
    echo "Migration " . ($result ? 'successful' : 'failed') . "\n";
}
```

### Check Migration Status

```php
// Get pending migrations
$pending = $migrations->get_pending_migrations();
foreach ($pending as $version => $migration) {
    echo "Pending: $version - {$migration['description']}\n";
}

// Get applied migrations
$applied = $migrations->get_applied_migrations();
foreach ($applied as $version) {
    echo "Applied: $version\n";
}

// Get migration history
$history = $migrations->get_history();
foreach ($history as $entry) {
    echo "{$entry['timestamp']}: {$entry['direction']} {$entry['version']}\n";
}
```

## Rolling Back Migrations

### Rollback Last Migration

```php
$migrations->rollback();
```

### Rollback to Specific Version

```php
// Rollback to version 1.0.0
$migrations->rollback('1.0.0');
```

### Safe Rollback Practices

1. **Backup first** - Always backup your database before rolling back
2. **Test on staging** - Test rollback on staging environment first
3. **Check dependencies** - Ensure no other code depends on the schema
4. **Monitor errors** - Check for errors after rollback

```php
// Safe rollback with error handling
try {
    // Backup before rollback
    // (implement your backup logic here)
    
    $result = $migrations->rollback();
    
    if ($result) {
        echo "Rollback successful\n";
    } else {
        echo "Rollback failed\n";
    }
} catch (Exception $e) {
    echo "Rollback error: " . $e->getMessage() . "\n";
}
```

## Versioning Strategy

### Semantic Versioning

Use semantic versioning for database schema versions:

- `MAJOR.MINOR.PATCH`
  - `MAJOR` - Breaking changes
  - `MINOR` - New features, backward compatible
  - `PATCH` - Bug fixes, backward compatible

### Version Increments

| Change Type | Example | Impact |
|------------|---------|--------|
| New table | 1.0.0 → 1.1.0 | MINOR |
| New column | 1.1.0 → 1.1.1 | PATCH |
| Column removal | 1.1.1 → 2.0.0 | MAJOR |
| Table drop | 1.1.1 → 2.0.0 | MAJOR |

### Migration Order

Migrations execute in version order (oldest to newest). Each version:

1. Has both `up` and `down` methods
2. Executes atomically (in a transaction)
3. Logs to migration history
4. Updates version option on completion

## Deployment Notes

### Production Deployment

When deploying to production:

1. **Test migrations on staging**
   ```bash
   # Run migrations on staging first
   wp plugin activate affiliate-product-showcase --staging
   ```

2. **Backup production database**
   ```bash
   wp db export backup-$(date +%Y%m%d).sql --production
   ```

3. **Deploy plugin update**
   ```bash
   wp plugin update affiliate-product-showcase --production
   ```

4. **Verify migration**
   ```bash
   wp eval "
   \$db = new \AffiliateProductShowcase\Database\Database();
   \$migrations = new \AffiliateProductShowcase\Database\Migrations(\$db);
   echo 'Current version: ' . \$migrations->get_current_version();
   " --production
   ```

### Zero-Downtime Deployment

For high-traffic sites, consider zero-downtime deployment:

1. Deploy new plugin version
2. Migrations run in background
3. Old code continues working
4. New code activates after migration

```php
// Check migration status before running new features
if ($migrations->needs_migration()) {
    // Run migrations in maintenance mode
    wp_maintenance();
    $migrations->run();
    wp_maintenance_end();
}
```

### Multisite Considerations

For multisite installations:

```php
// Run migrations for all sites
$sites = get_sites();
foreach ($sites as $site) {
    switch_to_blog($site->blog_id);
    
    $db = new Database();
    $migrations = new Migrations($db);
    
    if ($migrations->needs_migration()) {
        $migrations->run();
    }
    
    restore_current_blog();
}
```

## Troubleshooting

### Common Issues

   - Migration Failed

**Problem:** Migration fails to execute

**Solution:**
1. Check error logs
2. Verify table exists
3. Check permissions
4. Test SQL manually

```php
// Check for errors
if ($migrations->run() === false) {
    echo "Migration failed: " . $db->get_last_error() . "\n";
}
```

   - Rollback Failed

**Problem:** Cannot rollback migration

**Solution:**
1. Ensure `down` method exists
2. Check if data dependencies exist
3. Manual SQL rollback if needed

```php
try {
    $migrations->rollback();
} catch (Exception $e) {
    // Manual rollback
    $sql = "ALTER TABLE wp_affiliate_products_submissions DROP COLUMN rating";
    $db->query($sql);
}
```

   - Version Mismatch

**Problem:** Database version doesn't match code version

**Solution:**
1. Check `wp_options` for `affiliate_products_db_version`
2. Manually update if needed
3. Rerun migrations

```sql
-- Manual version update
UPDATE wp_options 
SET option_value = '1.0.0' 
WHERE option_name = 'affiliate_products_db_version';
```

   - Table Already Exists

**Problem:** Table creation fails because table exists

**Solution:**
```php
// Check table existence first
if (!$db->table_exists('meta')) {
    $this->create_meta_table();
}
```

### Debug Mode

Enable debug mode for detailed information:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check debug log:
```bash
tail -f wp-content/debug.log
```

### Using WP-CLI

WP-CLI provides helpful commands for debugging:

```bash
# Check database version
wp option get affiliate_products_db_version

# Check migration history
wp option get affiliate_products_migration_history

# List custom tables
wp db query "SHOW TABLES LIKE 'wp_affiliate_products_%'"

# Check table structure
wp db describe wp_affiliate_products_meta
```

## Additional Resources

- [WordPress Database API](https://developer.wordpress.org/reference/classes/wpdb/)
- [Plugin Activation Hooks](https://developer.wordpress.org/reference/functions/register_activation_hook/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
