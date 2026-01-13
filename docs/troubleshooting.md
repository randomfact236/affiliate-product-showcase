# Troubleshooting Guide

## Overview

This guide provides solutions to common issues you may encounter while using the Affiliate Product Showcase plugin. Follow the systematic approach below to diagnose and resolve problems.

## Quick Diagnostic Steps

Before diving into specific issues, perform these quick checks:

1. **Verify Plugin Status**
   ```bash
   wp plugin status affiliate-product-showcase
   ```

2. **Check WordPress Version**
   ```bash
   wp core version
   ```

3. **Verify PHP Version**
   ```bash
   php -v
   ```

4. **Check for Conflicts**
   ```bash
   wp plugin list --status=active
   ```

5. **Enable Debug Mode**
   ```php
   // Add to wp-config.php
   define( 'WP_DEBUG', true );
   define( 'WP_DEBUG_LOG', true );
   define( 'WP_DEBUG_DISPLAY', false );
   ```

## Common Issues & Solutions

### 1. Plugin Activation Issues

#### Problem: Plugin fails to activate

**Symptoms:**
- White screen after activation
- "Plugin could not be activated" message
- Fatal error in error log

**Solutions:**

1. **Check PHP Version**
   ```bash
   php -v
   ```
   Required: PHP 7.4+

2. **Check for Missing Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Check Error Log**
   ```bash
   tail -f wp-content/debug.log
   ```

4. **Manual Activation via WP-CLI**
   ```bash
   wp plugin activate affiliate-product-showcase --force
   ```

5. **Check File Permissions**
   ```bash
   chmod 644 wp-content/plugins/affiliate-product-showcase/affiliate-product-showcase.php
   chmod 755 wp-content/plugins/affiliate-product-showcase
   ```

#### Problem: "Headers already sent" error

**Cause:** Whitespace or BOM characters in PHP files

**Solution:**
```bash
# Check for BOM
grep -r $'\xEF\xBB\xBF' wp-content/plugins/affiliate-product-showcase/

# Remove BOM from all PHP files
find wp-content/plugins/affiliate-product-showcase/ -name "*.php" -exec sed -i '1s/^\xEF\xBB\xBF//' {} \;
```

### 2. Shortcode Display Issues

#### Problem: Shortcode shows as plain text

**Solutions:**

1. **Verify Plugin Active**
   ```bash
   wp plugin list | grep affiliate-product-showcase
   ```

2. **Check Shortcode Syntax**
   ```php
   // Correct
   [affiliate_products limit="6"]
   
   // Incorrect - missing quotes
   [affiliate_products limit=6]
   ```

3. **Test in Different Contexts**
   ```php
   // Test in theme template
   <?php echo do_shortcode( '[affiliate_products limit="3"]' ); ?>
   
   // Test in PHP
   $output = do_shortcode( '[affiliate_products limit="3"]' );
   echo $output;
   ```

4. **Check for Theme Conflicts**
   - Switch to Twenty Twenty-Four theme
   - Test shortcode again
   - If works, theme has conflict

5. **Check for Plugin Conflicts**
   ```bash
   # Deactivate all plugins except Affiliate Product Showcase
   wp plugin deactivate --all
   wp plugin activate affiliate-product-showcase
   
   # Test shortcode
   # Then reactivate plugins one by one
   ```

#### Problem: Products not displaying

**Solutions:**

1. **Verify Products Exist**
   ```bash
   wp affiliate product list --limit=10
   ```

2. **Check Product Status**
   ```bash
   wp affiliate product list --status=publish
   ```

3. **Clear Cache**
   ```bash
   wp affiliate cache clear
   ```

4. **Check Query Arguments**
   ```php
   // Add to functions.php for debugging
   add_filter( 'affiliate_product_showcase_query_args', function( $args ) {
       error_log( 'Query Args: ' . print_r( $args, true ) );
       return $args;
   } );
   ```

5. **Verify Categories/Tags**
   ```bash
   wp affiliate category list
   ```

### 3. CSS/JavaScript Issues

#### Problem: Products display without styling

**Solutions:**

1. **Check CSS Enqueue**
   ```php
   // Add to functions.php
   add_action( 'wp_enqueue_scripts', function() {
       wp_enqueue_style( 'affiliate-product-showcase' );
   } );
   ```

2. **Verify File Existence**
   ```bash
   ls -la wp-content/plugins/affiliate-product-showcase/assets/css/
   ```

3. **Check for CSS Minification Conflicts**
   - Disable CSS minification plugins
   - Test again

4. **Browser Console Check**
   - Open DevTools (F12)
   - Check Console for errors
   - Check Network tab for 404s

5. **Force CSS Reload**
   ```bash
   wp affiliate cache clear --type=products
   ```

#### Problem: JavaScript not working

**Solutions:**

1. **Check for JS Errors**
   ```javascript
   // In browser console
   console.log( 'Affiliate Showcase loaded' );
   ```

2. **Verify jQuery Dependency**
   ```php
   // Plugin should declare dependency
   wp_enqueue_script( 
       'affiliate-product-showcase',
       plugins_url( 'assets/js/main.js', __FILE__ ),
       array( 'jquery' ),
       '1.0.0',
       true
   );
   ```

3. **Check for JS Minification**
   - Disable JS minification
   - Test in development mode

4. **Verify Script Loading Order**
   ```bash
   wp affiliate settings get script_loading_order
   ```

### 4. Performance Issues

#### Problem: Page loads slowly

**Solutions:**

1. **Enable Caching**
   ```bash
   wp affiliate settings set cache_enabled true --type=boolean
   ```

2. **Reduce Product Limit**
   ```php
   [affiliate_products limit="6" columns="3"]
   ```

3. **Use Pagination**
   ```php
   [affiliate_products limit="12" pagination="true"]
   ```

4. **Check Database Queries**
   ```php
   // Add to functions.php
   add_action( 'shutdown', function() {
       if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
           global $wpdb;
           echo '<!-- Queries: ' . $wpdb->num_queries . ' -->';
       }
   } );
   ```

5. **Optimize Images**
   ```bash
   # Use smaller images
   wp affiliate settings set image_size 'thumbnail' --type=string
   ```

#### Problem: High server resource usage

**Solutions:**

1. **Check Cache Hit Rate**
   ```bash
   wp affiliate maintenance stats
   ```

2. **Implement Transient Caching**
   ```php
   add_filter( 'affiliate_product_showcase_cache_duration', function( $duration ) {
       return HOUR_IN_SECONDS * 24; // 24 hours
   } );
   ```

3. **Use CDN for Images**
   ```php
   add_filter( 'affiliate_product_showcase_product_image', function( $url ) {
       return str_replace( 'example.com', 'cdn.example.com', $url );
   } );
   ```

4. **Limit Concurrent Queries**
   ```bash
   wp affiliate settings set max_concurrent_queries 5 --type=integer
   ```

### 5. Import/Export Issues

#### Problem: Import fails or times out

**Solutions:**

1. **Check File Format**
   ```bash
   # Validate CSV
   head -n 5 import.csv
   ```

2. **Increase PHP Limits**
   ```ini
   ; In php.ini
   max_execution_time = 300
   memory_limit = 256M
   upload_max_filesize = 64M
   post_max_size = 64M
   ```

3. **Import in Batches**
   ```bash
   # Split large file
   split -l 100 large-import.csv batch_
   
   # Import each batch
   wp affiliate product import batch_aa.csv --dry-run
   wp affiliate product import batch_aa.csv
   ```

4. **Use WP-CLI for Large Imports**
   ```bash
   nohup wp affiliate product import large-file.csv > import.log 2>&1 &
   ```

5. **Check Error Log**
   ```bash
   tail -f wp-content/debug.log | grep -i "affiliate"
   ```

#### Problem: Export produces empty file

**Solutions:**

1. **Verify Products Exist**
   ```bash
   wp affiliate product list --format=count
   ```

2. **Check Status Filter**
   ```bash
   wp affiliate product export test.csv --status=publish
   ```

3. **Verify Permissions**
   ```php
   // Check if user can export
   if ( ! current_user_can( 'manage_options' ) ) {
       wp_die( 'Insufficient permissions' );
   }
   ```

4. **Check File Path**
   ```bash
   # Ensure writable directory
   ls -la wp-content/uploads/
   ```

### 6. WP-CLI Command Issues

#### Problem: Command not found

**Solutions:**

1. **Verify WP-CLI Installation**
   ```bash
   wp --info
   ```

2. **Check Plugin Activation**
   ```bash
   wp plugin status affiliate-product-showcase
   ```

3. **Clear WP-CLI Cache**
   ```bash
   wp cache flush
   ```

4. **Use Full Namespace**
   ```bash
   wp affiliate product list
   # Instead of:
   wp product list
   ```

#### Problem: Permission denied

**Solutions:**

1. **Check User Role**
   ```bash
   wp user list --field=roles
   ```

2. **Add Capabilities**
   ```bash
   wp cap add administrator affiliate_manage_products
   wp cap add administrator affiliate_manage_settings
   ```

3. **Run as Super Admin**
   ```bash
   sudo -u www-data wp affiliate product list
   ```

### 7. Cache Issues

#### Problem: Changes not reflecting

**Solutions:**

1. **Clear All Cache**
   ```bash
   wp affiliate cache clear
   ```

2. **Clear WordPress Cache**
   ```bash
   wp cache flush
   ```

3. **Clear Browser Cache**
   - Ctrl+Shift+R (Windows/Linux)
   - Cmd+Shift+R (Mac)

4. **Disable Cache Temporarily**
   ```bash
   wp affiliate settings set cache_enabled false --type=boolean
   ```

5. **Check Transients**
   ```bash
   wp transient list | grep affiliate
   wp transient delete --all
   ```

#### Problem: Cache warming fails

**Solutions:**

1. **Check Product Count**
   ```bash
   wp affiliate product list --format=count
   ```

2. **Reduce Batch Size**
   ```bash
   wp affiliate cache warm --limit=10
   ```

3. **Check Memory Limit**
   ```bash
   wp affiliate settings get memory_limit
   ```

4. **Run in Background**
   ```bash
   nohup wp affiliate cache warm > cache-warm.log 2>&1 &
   ```

### 8. Database Issues

#### Problem: Database errors

**Solutions:**

1. **Check Database Integrity**
   ```bash
   wp affiliate maintenance verify --fix
   ```

2. **Repair Database**
   ```bash
   wp db repair
   ```

3. **Check Table Prefix**
   ```bash
   wp db query "SHOW TABLES LIKE '%affiliate%'"
   ```

4. **Verify Options**
   ```bash
   wp option list --search="affiliate_product_showcase"
   ```

5. **Reset Plugin Data**
   ```bash
   wp affiliate settings reset --confirm
   ```

### 9. REST API Issues

#### Problem: REST API endpoints not working

**Solutions:**

1. **Verify REST API**
   ```bash
   curl -I https://yoursite.com/wp-json/
   ```

2. **Check Permalinks**
   ```bash
   wp rewrite flush
   ```

3. **Verify Endpoint Registration**
   ```php
   // Add to functions.php
   add_action( 'rest_api_init', function() {
       error_log( 'REST API initialized' );
   } );
   ```

4. **Check for REST API Disable Plugins**
   ```bash
   wp plugin list --status=active | grep -i "rest"
   ```

### 10. Security Issues

#### Problem: Security warnings

**Solutions:**

1. **Verify File Permissions**
   ```bash
   find wp-content/plugins/affiliate-product-showcase -type f -exec chmod 644 {} \;
   find wp-content/plugins/affiliate-product-showcase -type d -exec chmod 755 {} \;
   ```

2. **Check for Modified Core Files**
   ```bash
   git status --porcelain wp-content/plugins/affiliate-product-showcase/
   ```

3. **Validate Nonces**
   ```php
   // All forms should use nonces
   wp_nonce_field( 'affiliate_product_action', 'affiliate_nonce' );
   ```

4. **Check User Capabilities**
   ```php
   // Verify capability checks
   if ( ! current_user_can( 'manage_options' ) ) {
       wp_die( 'Insufficient permissions' );
   }
   ```

## Advanced Debugging

### Enable Detailed Logging

```php
// Add to wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// Add to functions.php
add_action( 'affiliate_product_showcase_loaded', function() {
    error_log( 'Affiliate Showcase: Plugin loaded' );
} );

add_filter( 'affiliate_product_showcase_query_args', function( $args ) {
    error_log( 'Affiliate Showcase Query: ' . print_r( $args, true ) );
    return $args;
} );
```

### Query Monitor Integration

```bash
# Install Query Monitor
wp plugin install query-monitor --activate

# Check queries
# Query Monitor will show all plugin queries in admin bar
```

### Debug Bar Integration

```bash
# Install Debug Bar
wp plugin install debug-bar --activate
wp plugin install debug-bar-extender --activate
```

### Performance Profiling

```php
// Add to functions.php
add_action( 'affiliate_product_showcase_before_shortcode', function() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        global $start_time;
        $start_time = microtime( true );
    }
} );

add_action( 'affiliate_product_showcase_after_shortcode', function() {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        global $start_time;
        $end_time = microtime( true );
        $duration = ( $end_time - $start_time ) * 1000;
        error_log( "Affiliate Showcase: {$duration}ms execution time" );
    }
} );
```

## Systematic Troubleshooting Flow

### Step 1: Isolate the Problem

```bash
# 1. Check if it's plugin-specific
wp plugin deactivate --all
wp plugin activate affiliate-product-showcase
# Test shortcode

# 2. Check if it's theme-specific
wp theme activate twentytwentyfour
# Test shortcode

# 3. Check if it's cache-related
wp affiliate cache clear
wp cache flush
# Test again
```

### Step 2: Check Logs

```bash
# WordPress debug log
tail -f wp-content/debug.log

# PHP error log
tail -f /var/log/php-errors.log

# Server error log
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

### Step 3: Verify Configuration

```bash
# Check plugin settings
wp affiliate settings list

# Check system requirements
wp affiliate maintenance stats

# Verify database
wp affiliate maintenance verify
```

### Step 4: Test with Minimal Setup

```php
// Create test page with only:
[affiliate_products limit="1"]

// No custom CSS
// No custom functions
// Default theme
```

### Step 5: Escalate if Needed

If problem persists:

1. **Document exact steps to reproduce**
2. **Include error messages**
3. **Provide system info**
4. **Share debug log contents**
5. **Contact support with complete information**

## Quick Fixes Reference

| Issue | Quick Fix |
|-------|-----------|
| Shortcode not working | `wp plugin activate affiliate-product-showcase` |
| Products not showing | `wp affiliate cache clear` |
| Styling missing | `wp affiliate settings set cache_enabled false` |
| Import failing | Increase PHP timeout |
| WP-CLI not found | `wp --info` and check PATH |
| Cache not clearing | `wp cache flush` and browser clear |
| Database errors | `wp affiliate maintenance verify --fix` |
| Permission denied | `wp cap add administrator affiliate_manage_products` |
| REST API 404 | `wp rewrite flush` |
| High memory usage | Reduce limit in shortcode |

## Support Resources

### Before Contacting Support

1. **Run diagnostics:**
   ```bash
   wp affiliate maintenance stats
   wp affiliate maintenance verify
   ```

2. **Check logs:**
   ```bash
   tail -n 50 wp-content/debug.log
   ```

3. **Verify versions:**
   ```bash
   wp core version
   php -v
   wp plugin list | grep affiliate
   ```

4. **Test with minimal setup:**
   - Default theme
   - Only Affiliate Product Showcase active
   - Basic shortcode

### Information to Include

When reporting issues, provide:

1. **WordPress version**
2. **PHP version**
3. **Plugin version**
4. **Error messages** (from debug.log)
5. **Steps to reproduce**
6. **Shortcode used**
7. **Browser console errors**
8. **Server environment** (Apache/Nginx, MySQL version)

## Prevention Best Practices

### Regular Maintenance

```bash
# Weekly
wp affiliate cache clear
wp cache flush
wp affiliate maintenance verify

# Monthly
wp affiliate maintenance cleanup --type=orphaned
wp db optimize
```

### Monitoring

```bash
# Add to cron
0 2 * * * cd /var/www/html && wp affiliate maintenance stats > /var/log/affiliate-stats.log
```

### Updates

```bash
# Before updating
wp affiliate cache clear
wp db backup

# Update plugin
wp plugin update affiliate-product-showcase

# After update
wp affiliate maintenance verify
wp affiliate cache warm
```

---

*Last updated: January 2026*
*Plugin Version: 1.0.0*
