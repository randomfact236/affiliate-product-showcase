# Hooks & Filters Reference

## Overview

The Affiliate Product Showcase plugin provides extensive WordPress hooks (actions and filters) for customization and integration. This reference documents all available hooks with examples and use cases.

## Action Hooks

Actions are triggered at specific points during plugin execution. Use `add_action()` to execute custom code.

### Plugin Lifecycle

#### `affiliate_product_showcase_loaded`

Fired when the plugin is fully loaded and initialized.

**Usage:**
```php
add_action( 'affiliate_product_showcase_loaded', function() {
    // Plugin is ready, perform initialization
    error_log( 'Affiliate Product Showcase plugin loaded' );
} );
```

**When:** After plugin files are loaded, before any admin/frontend logic

#### `affiliate_product_showcase_activated`

Fired when the plugin is activated.

**Usage:**
```php
add_action( 'affiliate_product_showcase_activated', function() {
    // Setup default options, create tables, etc.
    update_option( 'affiliate_product_showcase_version', '1.0.0' );
} );
```

**When:** Plugin activation hook

#### `affiliate_product_showcase_deactivated`

Fired when the plugin is deactivated.

**Usage:**
```php
add_action( 'affiliate_product_showcase_deactivated', function() {
    // Clean up, remove transients, etc.
    delete_transient( 'affiliate_product_showcase_cache' );
} );
```

**When:** Plugin deactivation hook

#### `affiliate_product_showcase_uninstalled`

Fired when the plugin is uninstalled (all data removed).

**Usage:**
```php
add_action( 'affiliate_product_showcase_uninstalled', function() {
    // Clean up all plugin data
    // Remove options, transients, custom tables
} );
```

**When:** Plugin uninstall

### Admin Area

#### `affiliate_product_showcase_admin_menu`

Fired before admin menu is registered.

**Usage:**
```php
add_action( 'affiliate_product_showcase_admin_menu', function() {
    // Add custom submenu pages
    add_submenu_page(
        'affiliate-product-showcase',
        'Custom Page',
        'Custom',
        'manage_options',
        'affiliate-custom',
        'my_custom_page_callback'
    );
} );
```

#### `affiliate_product_showcase_admin_init`

Fired during admin initialization.

**Usage:**
```php
add_action( 'affiliate_product_showcase_admin_init', function() {
    // Register admin settings, scripts, etc.
    wp_enqueue_script( 'my-custom-admin-script' );
} );
```

#### `affiliate_product_showcase_admin_head`

Fired in admin head section.

**Usage:**
```php
add_action( 'affiliate_product_showcase_admin_head', function() {
    // Add custom admin styles
    echo '<style>.my-custom-class { color: red; }</style>';
} );
```

#### `affiliate_product_showcase_admin_footer`

Fired in admin footer.

**Usage:**
```php
add_action( 'affiliate_product_showcase_admin_footer', function() {
    // Add custom admin scripts or debug info
    echo '<div id="custom-admin-debug">Debug Info</div>';
} );
```

### Frontend

#### `affiliate_product_showcase_before_shortcode`

Fired before shortcode output.

**Parameters:**
- `$atts` - Shortcode attributes array

**Usage:**
```php
add_action( 'affiliate_product_showcase_before_shortcode', function( $atts ) {
    // Enqueue custom styles/scripts
    wp_enqueue_style( 'my-custom-styles' );
    
    // Log for debugging
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'Shortcode attributes: ' . print_r( $atts, true ) );
    }
} );
```

#### `affiliate_product_showcase_after_shortcode`

Fired after shortcode output.

**Parameters:**
- `$atts` - Shortcode attributes array
- `$output` - Generated HTML output

**Usage:**
```php
add_action( 'affiliate_product_showcase_after_shortcode', function( $atts, $output ) {
    // Track impressions
    if ( isset( $atts['id'] ) ) {
        do_action( 'affiliate_product_track_impression', $atts['id'] );
    }
}, 10, 2 );
```

#### `affiliate_product_showcase_before_product`

Fired before each product is rendered.

**Parameters:**
- `$product` - Product object

**Usage:**
```php
add_action( 'affiliate_product_showcase_before_product', function( $product ) {
    // Add custom wrapper
    echo '<div class="custom-product-wrapper" data-product-id="' . esc_attr( $product->ID ) . '">';
} );
```

#### `affiliate_product_showcase_after_product`

Fired after each product is rendered.

**Parameters:**
- `$product` - Product object

**Usage:**
```php
add_action( 'affiliate_product_showcase_after_product', function( $product ) {
    // Close custom wrapper
    echo '</div>';
} );
```

#### `affiliate_product_showcase_before_cta`

Fired before CTA button.

**Parameters:**
- `$product` - Product object
- `$atts` - Shortcode attributes

**Usage:**
```php
add_action( 'affiliate_product_showcase_before_cta', function( $product, $atts ) {
    // Add tracking attributes
    echo 'data-tracking="affiliate-click" data-product="' . esc_attr( $product->ID ) . '"';
}, 10, 2 );
```

#### `affiliate_product_showcase_after_cta`

Fired after CTA button.

**Parameters:**
- `$product` - Product object
- `$atts` - Shortcode attributes

**Usage:**
```php
add_action( 'affiliate_product_showcase_after_cta', function( $product, $atts ) {
    // Add secondary call-to-action
    echo '<button class="compare-btn" data-id="' . esc_attr( $product->ID ) . '">Compare</button>';
}, 10, 2 );
```

### Product Management

#### `affiliate_product_showcase_product_created`

Fired after a product is created.

**Parameters:**
- `$product_id` - Created product ID
- `$data` - Product data array

**Usage:**
```php
add_action( 'affiliate_product_showcase_product_created', function( $product_id, $data ) {
    // Send notification
    wp_mail(
        get_option( 'admin_email' ),
        'New Affiliate Product',
        'Product created: ' . $data['name']
    );
    
    // Sync to external service
    my_sync_to_crm( $product_id );
}, 10, 2 );
```

#### `affiliate_product_showcase_product_updated`

Fired after a product is updated.

**Parameters:**
- `$product_id` - Updated product ID
- `$old_data` - Old product data
- `$new_data` - New product data

**Usage:**
```php
add_action( 'affiliate_product_showcase_product_updated', function( $product_id, $old_data, $new_data ) {
    // Check if price changed
    if ( $old_data['price'] !== $new_data['price'] ) {
        do_action( 'affiliate_product_price_changed', $product_id, $old_data['price'], $new_data['price'] );
    }
    
    // Clear cache
    wp_cache_delete( 'affiliate_product_' . $product_id );
}, 10, 3 );
```

#### `affiliate_product_showcase_product_deleted`

Fired after a product is deleted.

**Parameters:**
- `$product_id` - Deleted product ID
- `$force` - Whether deletion was permanent

**Usage:**
```php
add_action( 'affiliate_product_showcase_product_deleted', function( $product_id, $force ) {
    // Clean up related data
    delete_post_meta( $product_id, 'affiliate_clicks' );
    delete_post_meta( $product_id, 'affiliate_revenue' );
    
    // Log deletion
    error_log( "Product $product_id deleted (permanent: " . var_export( $force, true ) . ")" );
}, 10, 2 );
```

#### `affiliate_product_showcase_product_imported`

Fired after products are imported.

**Parameters:**
- `$count` - Number of products imported
- `$results` - Import results array

**Usage:**
```php
add_action( 'affiliate_product_showcase_product_imported', function( $count, $results ) {
    // Send summary notification
    $message = "Imported $count products.\n";
    $message .= "Success: " . $results['success'] . "\n";
    $message .= "Failed: " . $results['failed'] . "\n";
    
    wp_mail( get_option( 'admin_email' ), 'Import Complete', $message );
}, 10, 2 );
```

### Tracking & Analytics

#### `affiliate_product_track_click`

Fired when an affiliate link is clicked.

**Parameters:**
- `$product_id` - Product ID
- `$data` - Click data (IP, user agent, referrer, etc.)

**Usage:**
```php
add_action( 'affiliate_product_track_click', function( $product_id, $data ) {
    // Send to Google Analytics
    // Send to custom analytics service
    // Log to database
}, 10, 2 );
```

#### `affiliate_product_track_impression`

Fired when a product is displayed.

**Parameters:**
- `$product_id` - Product ID

**Usage:**
```php
add_action( 'affiliate_product_track_impression', function( $product_id ) {
    // Increment impression counter
    $count = get_post_meta( $product_id, 'impressions', true );
    update_post_meta( $product_id, 'impressions', (int) $count + 1 );
} );
```

#### `affiliate_product_track_conversion`

Fired when a conversion is tracked.

**Parameters:**
- `$product_id` - Product ID
- `$revenue` - Conversion revenue
- `$data` - Additional conversion data

**Usage:**
```php
add_action( 'affiliate_product_track_conversion', function( $product_id, $revenue, $data ) {
    // Update revenue total
    $current = get_post_meta( $product_id, 'total_revenue', true );
    update_post_meta( $product_id, 'total_revenue', (float) $current + $revenue );
    
    // Send notification for high-value conversions
    if ( $revenue > 100 ) {
        wp_mail( get_option( 'admin_email' ), 'High Value Conversion', "Product: $product_id, Revenue: $revenue" );
    }
}, 10, 3 );
```

### Cache Management

#### `affiliate_product_showcase_cache_cleared`

Fired after cache is cleared.

**Parameters:**
- `$type` - Cache type cleared

**Usage:**
```php
add_action( 'affiliate_product_showcase_cache_cleared', function( $type ) {
    // Log cache clearing
    error_log( "Cache cleared: $type" );
    
    // Notify external services
    if ( $type === 'all' || $type === 'products' ) {
        // Clear CDN cache
    }
} );
```

#### `affiliate_product_showcase_cache_warmed`

Fired after cache is warmed.

**Parameters:**
- `$count` - Number of items warmed
- `$type` - Cache type

**Usage:**
```php
add_action( 'affiliate_product_showcase_cache_warmed', function( $count, $type ) {
    // Log warming completion
    error_log( "Cache warmed: $count items of type $type" );
}, 10, 2 );
```

### Settings

#### `affiliate_product_showcase_settings_saved`

Fired after settings are saved.

**Parameters:**
- `$old_settings` - Old settings array
- `$new_settings` - New settings array

**Usage:**
```php
add_action( 'affiliate_product_showcase_settings_saved', function( $old_settings, $new_settings ) {
    // Clear cache if cache settings changed
    if ( isset( $new_settings['cache_enabled'] ) && $new_settings['cache_enabled'] !== $old_settings['cache_enabled'] ) {
        do_action( 'affiliate_product_showcase_cache_cleared', 'all' );
    }
    
    // Flush permalinks if URL settings changed
    if ( isset( $new_settings['rewrite_rules'] ) && $new_settings['rewrite_rules'] !== $old_settings['rewrite_rules'] ) {
        flush_rewrite_rules();
    }
}, 10, 2 );
```

#### `affiliate_product_showcase_settings_reset`

Fired after settings are reset to defaults.

**Usage:**
```php
add_action( 'affiliate_product_showcase_settings_reset', function() {
    // Clear all related transients
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_affiliate_%'" );
    
    // Notify admin
    wp_mail( get_option( 'admin_email' ), 'Settings Reset', 'Plugin settings have been reset to defaults.' );
} );
```

### WP-CLI

#### `affiliate_product_showcase_cli_before_command`

Fired before any WP-CLI command execution.

**Parameters:**
- `$command` - Command name
- `$args` - Command arguments
- `$assoc_args` - Associative arguments

**Usage:**
```php
add_action( 'affiliate_product_showcase_cli_before_command', function( $command, $args, $assoc_args ) {
    // Log command execution
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( "CLI Command: $command, Args: " . print_r( $args, true ) );
    }
}, 10, 3 );
```

#### `affiliate_product_showcase_cli_after_command`

Fired after any WP-CLI command execution.

**Parameters:**
- `$command` - Command name
- `$result` - Command result
- `$args` - Command arguments

**Usage:**
```php
add_action( 'affiliate_product_showcase_cli_after_command', function( $command, $result, $args ) {
    // Log completion
    error_log( "CLI Command $command completed" );
}, 10, 3 );
```

## Filter Hooks

Filters allow you to modify data before it's used. Use `add_filter()` to modify values.

### Product Data

#### `affiliate_product_showcase_product_data`

Filter product data before it's used.

**Parameters:**
- `$data` - Product data array
- `$product_id` - Product ID

**Returns:** Modified product data array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_data', function( $data, $product_id ) {
    // Add custom field
    $data['custom_field'] = get_post_meta( $product_id, 'custom_field', true );
    
    // Modify price
    if ( isset( $data['price'] ) ) {
        $data['price'] = number_format( $data['price'], 2 );
    }
    
    return $data;
}, 10, 2 );
```

#### `affiliate_product_showcase_product_name`

Filter product name.

**Parameters:**
- `$name` - Product name
- `$product_id` - Product ID

**Returns:** Modified product name

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_name', function( $name, $product_id ) {
    // Add prefix
    return 'Recommended: ' . $name;
}, 10, 2 );
```

#### `affiliate_product_showcase_product_price`

Filter product price.

**Parameters:**
- `$price` - Product price
- `$product_id` - Product ID
- `$format` - Whether to format (default: true)

**Returns:** Modified price

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_price', function( $price, $product_id, $format ) {
    // Add currency symbol
    if ( $format ) {
        return '$' . number_format( $price, 2 );
    }
    return $price;
}, 10, 3 );
```

#### `affiliate_product_showcase_product_url`

Filter affiliate URL.

**Parameters:**
- `$url` - Affiliate URL
- `$product_id` - Product ID

**Returns:** Modified URL

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_url', function( $url, $product_id ) {
    // Add tracking parameters
    return add_query_arg( array(
        'utm_source' => 'affiliate',
        'utm_medium' => 'product-showcase',
        'utm_campaign' => $product_id
    ), $url );
}, 10, 2 );
```

#### `affiliate_product_showcase_product_image`

Filter product image URL.

**Parameters:**
- `$image_url` - Image URL
- `$product_id` - Product ID
- `$size` - Image size (thumbnail, full, etc.)

**Returns:** Modified image URL

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_image', function( $image_url, $product_id, $size ) {
    // Use placeholder if no image
    if ( empty( $image_url ) ) {
        return plugins_url( 'assets/images/placeholder.jpg', __FILE__ );
    }
    
    // Use CDN
    if ( $size === 'thumbnail' ) {
        return str_replace( 'example.com', 'cdn.example.com', $image_url );
    }
    
    return $image_url;
}, 10, 3 );
```

#### `affiliate_product_showcase_product_description`

Filter product description/excerpt.

**Parameters:**
- `$description` - Product description
- `$product_id` - Product ID

**Returns:** Modified description

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_description', function( $description, $product_id ) {
    // Truncate if too long
    if ( strlen( $description ) > 200 ) {
        $description = substr( $description, 0, 200 ) . '...';
    }
    
    // Add disclaimer
    return $description . ' <em>(Affiliate link)</em>';
}, 10, 2 );
```

#### `affiliate_product_showcase_product_features`

Filter product features array.

**Parameters:**
- `$features` - Array of features
- `$product_id` - Product ID

**Returns:** Modified features array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_features', function( $features, $product_id ) {
    // Add custom feature
    $features[] = 'Custom Feature';
    
    // Sort features
    sort( $features );
    
    return $features;
}, 10, 2 );
```

#### `affiliate_product_showcase_product_ribbons`

Filter product ribbons/badges.

**Parameters:**
- `$ribbons` - Array of ribbon labels
- `$product_id` - Product ID

**Returns:** Modified ribbons array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_ribbons', function( $ribbons, $product_id ) {
    // Add dynamic ribbon based on price
    $price = get_post_meta( $product_id, 'price', true );
    if ( $price < 50 ) {
        $ribbons[] = 'Budget Pick';
    }
    
    return $ribbons;
}, 10, 2 );
```

### Shortcode Attributes

#### `affiliate_product_showcase_shortcode_attributes`

Filter shortcode attributes before processing.

**Parameters:**
- `$atts` - Attributes array
- `$defaults` - Default attributes array

**Returns:** Modified attributes array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_shortcode_attributes', function( $atts, $defaults ) {
    // Force limit for non-admin users
    if ( ! current_user_can( 'manage_options' ) && isset( $atts['limit'] ) ) {
        $atts['limit'] = min( $atts['limit'], 10 );
    }
    
    // Set default category if none specified
    if ( empty( $atts['category'] ) ) {
        $atts['category'] = 'featured';
    }
    
    return $atts;
}, 10, 2 );
```

#### `affiliate_product_showcase_query_args`

Filter database query arguments.

**Parameters:**
- `$args` - WP_Query arguments
- `$atts` - Shortcode attributes

**Returns:** Modified query arguments

**Usage:**
```php
add_filter( 'affiliate_product_showcase_query_args', function( $args, $atts ) {
    // Add custom meta query
    if ( isset( $atts['custom_field'] ) ) {
        $args['meta_query'][] = array(
            'key' => 'custom_field',
            'value' => $atts['custom_field'],
            'compare' => '='
        );
    }
    
    // Modify tax query
    if ( isset( $atts['category'] ) ) {
        $args['tax_query'][] = array(
            'taxonomy' => 'affiliate_category',
            'field' => 'slug',
            'terms' => explode( ',', $atts['category'] )
        );
    }
    
    return $args;
}, 10, 2 );
```

### HTML Output

#### `affiliate_product_showcase_product_html`

Filter complete product HTML output.

**Parameters:**
- `$html` - Generated HTML
- `$product` - Product object
- `$atts` - Shortcode attributes

**Returns:** Modified HTML

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_html', function( $html, $product, $atts ) {
    // Wrap in custom container
    return '<div class="my-custom-product">' . $html . '</div>';
}, 10, 3 );
```

#### `affiliate_product_showcase_cta_html`

Filter CTA button HTML.

**Parameters:**
- `$html` - CTA HTML
- `$product` - Product object
- `$atts` - Shortcode attributes

**Returns:** Modified CTA HTML

**Usage:**
```php
add_filter( 'affiliate_product_showcase_cta_html', function( $html, $product, $atts ) {
    // Add custom attributes
    $html = str_replace(
        '<a ',
        '<a data-tracking="affiliate" data-product="' . esc_attr( $product->ID ) . '" ',
        $html
    );
    
    return $html;
}, 10, 3 );
```

#### `affiliate_product_showcase_wrapper_classes`

Filter wrapper CSS classes.

**Parameters:**
- `$classes` - Array of classes
- `$atts` - Shortcode attributes

**Returns:** Modified classes array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_wrapper_classes', function( $classes, $atts ) {
    // Add custom class based on layout
    if ( isset( $atts['layout'] ) && $atts['layout'] === 'slider' ) {
        $classes[] = 'is-slider';
    }
    
    // Add responsive class
    $classes[] = 'responsive-' . ( isset( $atts['columns'] ) ? $atts['columns'] : '3' );
    
    return $classes;
}, 10, 2 );
```

#### `affiliate_product_showcase_product_classes`

Filter product item CSS classes.

**Parameters:**
- `$classes` - Array of classes
- `$product` - Product object

**Returns:** Modified classes array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_classes', function( $classes, $product ) {
    // Add featured class
    if ( get_post_meta( $product->ID, 'featured', true ) ) {
        $classes[] = 'is-featured';
    }
    
    // Add sale class
    if ( get_post_meta( $product->ID, 'on_sale', true ) ) {
        $classes[] = 'is-on-sale';
    }
    
    return $classes;
}, 10, 2 );
```

### Import/Export

#### `affiliate_product_showcase_import_data`

Filter import data before processing.

**Parameters:**
- `$data` - Import data array
- `$format` - Import format (csv, json)

**Returns:** Modified import data

**Usage:**
```php
add_filter( 'affiliate_product_showcase_import_data', function( $data, $format ) {
    // Sanitize all fields
    foreach ( $data as &$row ) {
        if ( isset( $row['name'] ) ) {
            $row['name'] = sanitize_text_field( $row['name'] );
        }
        if ( isset( $row['price'] ) ) {
            $row['price'] = floatval( $row['price'] );
        }
        if ( isset( $row['affiliate_url'] ) ) {
            $row['affiliate_url'] = esc_url_raw( $row['affiliate_url'] );
        }
    }
    
    return $data;
}, 10, 2 );
```

#### `affiliate_product_showcase_export_data`

Filter export data before output.

**Parameters:**
- `$data` - Export data array
- `$format` - Export format (csv, json)

**Returns:** Modified export data

**Usage:**
```php
add_filter( 'affiliate_product_showcase_export_data', function( $data, $format ) {
    // Remove sensitive data for CSV
    if ( $format === 'csv' ) {
        foreach ( $data as &$row ) {
            unset( $row['internal_notes'] );
            unset( $row['api_key'] );
        }
    }
    
    return $data;
}, 10, 2 );
```

### Cache

#### `affiliate_product_showcase_cache_key`

Filter cache key.

**Parameters:**
- `$key` - Cache key
- `$type` - Cache type
- `$args` - Cache arguments

**Returns:** Modified cache key

**Usage:**
```php
add_filter( 'affiliate_product_showcase_cache_key', function( $key, $type, $args ) {
    // Add user role to key for role-based caching
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $key .= '_role_' . implode( '-', $user->roles );
    }
    
    return $key;
}, 10, 3 );
```

#### `affiliate_product_showcase_cache_duration`

Filter cache duration.

**Parameters:**
- `$duration` - Duration in seconds
- `$type` - Cache type

**Returns:** Modified duration

**Usage:**
```php
add_filter( 'affiliate_product_showcase_cache_duration', function( $duration, $type ) {
    // Longer cache for product data
    if ( $type === 'products' ) {
        return HOUR_IN_SECONDS * 24; // 24 hours
    }
    
    // Shorter cache for queries
    if ( $type === 'queries' ) {
        return HOUR_IN_SECONDS; // 1 hour
    }
    
    return $duration;
}, 10, 2 );
```

### Settings

#### `affiliate_product_showcase_settings`

Filter all settings.

**Parameters:**
- `$settings` - Settings array

**Returns:** Modified settings array

**Usage:**
```php
add_filter( 'affiliate_product_showcase_settings', function( $settings ) {
    // Add custom setting
    $settings['custom_setting'] = get_option( 'affiliate_product_showcase_custom_setting', 'default' );
    
    return $settings;
} );
```

#### `affiliate_product_showcase_setting_{key}`

Filter specific setting by key.

**Parameters:**
- `$value` - Setting value
- `$default` - Default value

**Returns:** Modified setting value

**Usage:**
```php
// Filter specific setting
add_filter( 'affiliate_product_showcase_setting_cache_enabled', function( $value, $default ) {
    // Disable cache on staging
    if ( defined( 'WP_ENV' ) && WP_ENV === 'staging' ) {
        return false;
    }
    
    return $value;
}, 10, 2 );
```

### URLs

#### `affiliate_product_showcase_product_page_url`

Filter product page URL.

**Parameters:**
- `$url` - Product page URL
- `$product_id` - Product ID

**Returns:** Modified URL

**Usage:**
```php
add_filter( 'affiliate_product_showcase_product_page_url', function( $url, $product_id ) {
    // Add nofollow to external URLs
    return add_query_arg( 'rel', 'nofollow', $url );
}, 10, 2 );
```

#### `affiliate_product_showcase_admin_url`

Filter admin page URLs.

**Parameters:**
- `$url` - Admin URL
- `$page` - Page slug

**Returns:** Modified URL

**Usage:**
```php
add_filter( 'affiliate_product_showcase_admin_url', function( $url, $page ) {
    // Add custom parameter
    return add_query_arg( 'ref', 'custom', $url );
}, 10, 2 );
```

### Messages

#### `affiliate_product_showcase_message`

Filter admin messages.

**Parameters:**
- `$message` - Message text
- `$type` - Message type (success, error, warning, info)

**Returns:** Modified message

**Usage:**
```php
add_filter( 'affiliate_product_showcase_message', function( $message, $type ) {
    // Add prefix to all messages
    return '[Affiliate Showcase] ' . $message;
}, 10, 2 );
```

#### `affiliate_product_showcase_error_message`

Filter error messages specifically.

**Parameters:**
- `$message` - Error message
- `$code` - Error code

**Returns:** Modified error message

**Usage:**
```php
add_filter( 'affiliate_product_showcase_error_message', function( $message, $code ) {
    // Custom error messages
    $custom_messages = array(
        'invalid_url' => 'The affiliate URL you provided is not valid. Please check and try again.',
        'price_required' => 'Price is required for all products.',
        'product_not_found' => 'The requested product could not be found.'
    );
    
    if ( isset( $custom_messages[$code] ) ) {
        return $custom_messages[$code];
    }
    
    return $message;
}, 10, 2 );
```

### Permissions

#### `affiliate_product_showcase_capability`

Filter capability required for action.

**Parameters:**
- `$capability` - Required capability
- `$action` - Action name (manage_products, manage_settings, etc.)

**Returns:** Modified capability

**Usage:**
```php
add_filter( 'affiliate_product_showcase_capability', function( $capability, $action ) {
    // Allow editors to manage products
    if ( $action === 'manage_products' ) {
        return 'edit_posts';
    }
    
    return $capability;
}, 10, 2 );
```

#### `affiliate_product_showcase_user_can_access`

Filter access permission.

**Parameters:**
- `$can_access` - Current permission (true/false)
- `$context` - Context (admin, shortcode, api, etc.)

**Returns:** Modified permission

**Usage:**
```php
add_filter( 'affiliate_product_showcase_user_can_access', function( $can_access, $context ) {
    // Allow access to shortcode for all users
    if ( $context === 'shortcode' ) {
        return true;
    }
    
    return $can_access;
}, 10, 2 );
```

## Advanced Examples

### Complete Custom Integration

```php
// Custom tracking system
add_action( 'affiliate_product_track_click', function( $product_id, $data ) {
    // Send to Google Analytics 4
    $tracking_id = 'G-XXXXXXXXXX';
    $client_id = $_COOKIE['_ga'] ?? 'unknown';
    
    wp_remote_post( "https://www.google-analytics.com/mp/collect?api_secret=SECRET&measurement_id=$tracking_id", array(
        'body' => json_encode( array(
            'client_id' => $client_id,
            'events' => array(
                array(
                    'name' => 'affiliate_click',
                    'params' => array(
                        'product_id' => $product_id,
                        'timestamp' => time()
                    )
                )
            )
        ) )
    );
}, 10, 2 );

// Dynamic pricing based on user
add_filter( 'affiliate_product_showcase_product_price', function( $price, $product_id, $format ) {
    if ( is_user_logged_in() ) {
        $user = wp_get_current_user();
        
        // VIP users get 10% off display
        if ( in_array( 'vip', $user->roles ) ) {
            $price = $price * 0.9;
        }
    }
    
    return $format ? '$' . number_format( $price, 2 ) : $price;
}, 10, 3 );

// Custom product query with geolocation
add_filter( 'affiliate_product_showcase_query_args', function( $args, $atts ) {
    // Get user country (requires geolocation plugin)
    $country = apply_filters( 'user_country', 'US' );
    
    // Filter by country-specific products
    $args['meta_query'][] = array(
        'key' => 'available_countries',
        'value' => $country,
        'compare' => 'LIKE'
    );
    
    return $args;
}, 10, 2 );

// A/B testing for CTA buttons
add_filter( 'affiliate_product_showcase_cta_html', function( $html, $product, $atts ) {
    // Randomly assign variant
    $variant = mt_rand( 1, 2 );
    
    if ( $variant === 1 ) {
        // Variant A: Original
        return $html;
    } else {
        // Variant B: Different text
        return str_replace( 'View Deal', 'Get Offer Now', $html );
    }
}, 10, 3 );
```

### Security Hardening

```php
// Sanitize all product data
add_filter( 'affiliate_product_showcase_product_data', function( $data ) {
    $sanitized = array();
    
    foreach ( $data as $key => $value ) {
        if ( is_string( $value ) ) {
            $sanitized[$key] = sanitize_text_field( $value );
        } elseif ( is_array( $value ) ) {
            $sanitized[$key] = array_map( 'sanitize_text_field', $value );
        } else {
            $sanitized[$key] = $value;
        }
    }
    
    return $sanitized;
} );

// Validate affiliate URLs
add_filter( 'affiliate_product_showcase_product_url', function( $url ) {
    // Only allow http/https
    if ( ! preg_match( '/^https?:\/\//', $url ) ) {
        return '';
    }
    
    // Block known malicious domains
    $blocked = array( 'malicious.com', 'spam-site.org' );
    $host = parse_url( $url, PHP_URL_HOST );
    
    if ( in_array( $host, $blocked ) ) {
        return '';
    }
    
    return $url;
} );
```

## Best Practices

### 1. Always Check Context

```php
add_filter( 'affiliate_product_showcase_product_price', function( $price, $product_id, $format ) {
    // Only modify in specific contexts
    if ( is_admin() ) {
        return $price; // Don't modify in admin
    }
    
    // Apply modifications for frontend
    return $price * 1.1; // Add 10% margin
}, 10, 3 );
```

### 2. Use Proper Priorities

```php
// Low priority for late modifications
add_filter( 'affiliate_product_showcase_product_html', function( $html ) {
    // This runs last
    return $html;
}, 999, 1 );

// High priority for early modifications
add_filter( 'affiliate_product_showcase_query_args', function( $args ) {
    // This runs first
    return $args;
}, 5, 1 );
```

### 3. Cache Expensive Operations

```php
add_filter( 'affiliate_product_showcase_product_data', function( $data, $product_id ) {
    $cache_key = 'custom_data_' . $product_id;
    $custom_data = wp_cache_get( $cache_key );
    
    if ( false === $custom_data ) {
        $custom_data = expensive_operation( $product_id );
        wp_cache_set( $cache_key, $custom_data, '', HOUR_IN_SECONDS );
    }
    
    $data['custom'] = $custom_data;
    return $data;
}, 10, 2 );
```

### 4. Document Your Hooks

```php
/**
 * Filter product price for VIP users
 * 
 * @param float $price Original price
 * @param int $product_id Product ID
 * @param bool $format Whether to format
 * @return float|string Modified price
 */
add_filter( 'affiliate_product_showcase_product_price', function( $price, $product_id, $format ) {
    // Implementation...
}, 10, 3 );
```

## Hook Reference Table

| Hook Name | Type | Parameters | Description |
|-----------|------|------------|-------------|
| `affiliate_product_showcase_loaded` | Action | 0 | Plugin loaded |
| `affiliate_product_showcase_activated` | Action | 0 | Plugin activated |
| `affiliate_product_showcase_admin_menu` | Action | 0 | Admin menu setup |
| `affiliate_product_showcase_before_shortcode` | Action | 1 (atts) | Before shortcode output |
| `affiliate_product_showcase_after_shortcode` | Action | 2 (atts, output) | After shortcode output |
| `affiliate_product_showcase_product_created` | Action | 2 (id, data) | Product created |
| `affiliate_product_showcase_product_updated` | Action | 3 (id, old, new) | Product updated |
| `affiliate_product_track_click` | Action | 2 (id, data) | Affiliate click tracked |
| `affiliate_product_showcase_product_data` | Filter | 2 (data, id) | Product data |
| `affiliate_product_showcase_product_price` | Filter | 3 (price, id, format) | Product price |
| `affiliate_product_showcase_product_url` | Filter | 2 (url, id) | Affiliate URL |
| `affiliate_product_showcase_shortcode_attributes` | Filter | 2 (atts, defaults) | Shortcode attributes |
| `affiliate_product_showcase_query_args` | Filter | 2 (args, atts) | Query arguments |
| `affiliate_product_showcase_product_html` | Filter | 3 (html, product, atts) | Product HTML |

---

*Last updated: January 2026*
*Plugin Version: 1.0.0*
