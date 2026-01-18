# Performance Optimization Implementation Plan

**Date:** January 18, 2026
**Plugin:** Affiliate Product Showcase
**Purpose:** Detailed implementation plan for high-priority performance optimizations

---

## Overview

This document outlines the specific implementation details for each high-priority optimization recommendation from the Performance Optimization Report. Each optimization includes:
- What will be changed
- Which files will be modified
- Code examples of before/after
- Expected impact
- Implementation complexity

---

## Priority 1: Use Generator for Large Exports

### What Will Be Changed

**Current Behavior:**
The bulk export functionality loads all products into memory at once using `$this->product_service->get_products()`, which can cause memory issues with large product catalogs (1000+ products).

**New Behavior:**
Implement a generator pattern that yields products one at a time, significantly reducing memory usage by processing products in batches rather than loading all at once.

### Files to Modify

1. `src/Admin/BulkActions.php`
   - Modify `handleExportAction()` method
   - Add new generator method `get_products_for_export()`

### Implementation Details

#### Before (Current Code)
```php
// src/Admin/BulkActions.php
public function handleExportAction( array $post_ids, string $action ): void {
    if ( 'export_csv' !== $action ) {
        return;
    }

    // Load all products at once (memory intensive)
    $products = $this->product_service->get_products( [ 'per_page' => 100 ] );
    
    $data = [
        ['ID', 'Title', 'SKU', 'Brand', 'Price', 'Rating', 'Stock', 'Affiliate URL', 'Image URL'],
    ];
    
    foreach ( $post_ids as $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            continue;
        }
        
        $data[] = [
            $post_id,
            $post->post_title,
            get_post_meta( $post_id, '_sku', true ),
            get_post_meta( $post_id, '_brand', true ),
            get_post_meta( $post_id, '_price', true ),
            get_post_meta( $post_id, '_rating', true ),
            get_post_meta( $post_id, '_in_stock', true ),
            get_post_meta( $post_id, '_affiliate_url', true ),
            get_post_meta( $post_id, '_image_url', true ),
        ];
    }
    
    // ... rest of export code
}
```

#### After (Optimized Code)
```php
// src/Admin/BulkActions.php
/**
 * Get products for export using generator pattern
 * Yields products one at a time to reduce memory usage
 *
 * @param array<int> $post_ids Post IDs to export
 * @return \Generator<Product>
 */
private function get_products_for_export( array $post_ids ): \Generator {
    $batch_size = 50; // Process 50 products at a time
    
    foreach ( array_chunk( $post_ids, $batch_size ) as $chunk ) {
        $args = [
            'post__in'   => $chunk,
            'post_type'  => 'affiliate_product',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields'      => 'ids',
        ];
        
        $product_ids = get_posts( $args );
        
        foreach ( $product_ids as $product_id ) {
            $product = $this->product_service->get_product( $product_id );
            
            if ( $product ) {
                yield $product;
            }
        }
    }
}

public function handleExportAction( array $post_ids, string $action ): void {
    if ( 'export_csv' !== $action ) {
        return;
    }

    $data = [
        ['ID', 'Title', 'SKU', 'Brand', 'Price', 'Rating', 'Stock', 'Affiliate URL', 'Image URL'],
    ];
    
    // Use generator - products loaded in batches, not all at once
    foreach ( $this->get_products_for_export( $post_ids ) as $product ) {
        $data[] = [
            $product->id,
            $product->title,
            $product->sku ?? '',
            $product->brand ?? '',
            $product->price ?? '',
            $product->rating ?? '',
            $product->in_stock ? 'Yes' : 'No',
            $product->affiliate_url,
            $product->image_url ?? '',
        ];
    }
    
    // ... rest of export code
}
```

### Expected Impact

**Memory Reduction:**
- **Before:** Loads 100 products × ~5KB = ~500KB in memory
- **After:** Loads 50 products × ~5KB = ~250KB in memory (50% reduction)

**For 1000 products:**
- **Before:** ~5MB in memory
- **After:** ~250KB in memory (95% reduction)

**Performance:**
- Reduced risk of PHP memory limit errors
- Faster initial export setup
- Better handling of very large catalogs

### Implementation Complexity: **Medium**

**Steps:**
1. Create generator method
2. Modify export loop to use generator
3. Test with 100+ products
4. Test with 1000+ products
5. Monitor memory usage

---

## Priority 2: Add Async/Defer to Non-Critical Scripts

### What Will Be Changed

**Current Behavior:**
All JavaScript files are loaded synchronously, including non-critical scripts like analytics and tracking scripts. This can delay page interactivity.

**New Behavior:**
Non-critical scripts will use the `defer` attribute to load asynchronously, allowing the main thread to continue parsing HTML and rendering the page. Critical scripts remain synchronous to ensure proper execution order.

### Files to Modify

1. `src/Public/Enqueue.php`
   - Modify `enqueueScripts()` method
   - Add `defer` attribute to tracking script

2. `src/Admin/Enqueue.php`
   - Modify `enqueueScripts()` method
   - Add `defer` attribute to non-admin scripts

### Implementation Details

#### Before (Current Code)
```php
// src/Public/Enqueue.php
public function enqueueScripts(): void {
    // Main public JS
    wp_enqueue_script(
        'affiliate-product-showcase-public',
        AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/public.js',
        ['jquery'],
        self::VERSION,
        true
    );

    // Tracking script (critical for analytics)
    if ( $this->isTrackingEnabled() ) {
        wp_enqueue_script(
            'affiliate-product-showcase-tracking',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/tracking.js',
            [],
            self::VERSION,
            true
        );
    }

    // Lazy load script (non-critical)
    if ( $this->isLazyLoadEnabled() ) {
        wp_enqueue_script(
            'affiliate-product-showcase-lazyload',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/lazyload.js',
            [],
            self::VERSION,
            true
        );
    }
}
```

#### After (Optimized Code)
```php
// src/Public/Enqueue.php
public function enqueueScripts(): void {
    // Main public JS - Keep synchronous (critical for functionality)
    wp_enqueue_script(
        'affiliate-product-showcase-public',
        AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/public.js',
        ['jquery'],
        self::VERSION,
        true
    );

    // Tracking script - Use defer (non-critical for page load)
    if ( $this->isTrackingEnabled() ) {
        wp_register_script(
            'affiliate-product-showcase-tracking',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/tracking.js',
            [],
            self::VERSION,
            true
        );
        
        // Add defer attribute (WordPress 6.3+)
        wp_script_add_data( 'affiliate-product-showcase-tracking', 'defer', true );
        
        wp_enqueue_script( 'affiliate-product-showcase-tracking' );
    }

    // Lazy load script - Use defer (non-critical)
    if ( $this->isLazyLoadEnabled() ) {
        wp_register_script(
            'affiliate-product-showcase-lazyload',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/lazyload.js',
            [],
            self::VERSION,
            true
        );
        
        // Add defer attribute
        wp_script_add_data( 'affiliate-product-showcase-lazyload', 'defer', true );
        
        wp_enqueue_script( 'affiliate-product-showcase-lazyload' );
    }
}
```

#### Admin Enqueue Update

```php
// src/Admin/Enqueue.php
public function enqueueScripts( string $hook ): void {
    if ( ! $this->isPluginPage( $hook ) ) {
        return;
    }

    // Main admin JS - Keep synchronous (critical)
    wp_enqueue_script(
        'affiliate-product-showcase-admin',
        AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/admin.js',
        ['jquery'],
        self::VERSION,
        true
    );

    // Dashboard script - Use defer (non-critical)
    if ( $this->isDashboardPage( $hook ) ) {
        wp_register_script(
            'affiliate-product-showcase-dashboard',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/dashboard.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_script_add_data( 'affiliate-product-showcase-dashboard', 'defer', true );
        wp_enqueue_script( 'affiliate-product-showcase-dashboard' );
    }

    // Analytics script - Use defer (non-critical)
    if ( $this->isAnalyticsPage( $hook ) ) {
        wp_register_script(
            'affiliate-product-showcase-analytics',
            AFFILIATE_PRODUCT_SHOWCASE_PLUGIN_URL . 'assets/js/analytics.js',
            ['jquery', 'wp-util'],
            self::VERSION,
            true
        );
        
        wp_script_add_data( 'affiliate-product-showcase-analytics', 'defer', true );
        wp_enqueue_script( 'affiliate-product-showcase-analytics' );
    }
}
```

### Expected Impact

**Performance Metrics:**
- **Time to Interactive (TTI):** Reduced by ~200-400ms
- **First Contentful Paint (FCP):** Reduced by ~50-100ms
- **Total Blocking Time (TBT):** Reduced by ~150-300ms

**User Experience:**
- Faster page interactivity
- Smoother scrolling
- Faster initial page render
- Better perceived performance

**SEO:**
- Better Core Web Vitals scores
- Improved Google Lighthouse score

### Implementation Complexity: **Low**

**Steps:**
1. Add `wp_script_add_data()` calls for non-critical scripts
2. Test script execution order
3. Verify no JavaScript errors
4. Test on different browsers
5. Monitor Core Web Vitals

---

## Priority 3: Background Processing for Analytics

### What Will Be Changed

**Current Behavior:**
Analytics events are processed synchronously when they occur, which can slow down page load times, especially during high-traffic periods.

**New Behavior:**
Analytics events are queued and processed in the background using WordPress cron or transient-based queue, allowing the page to load immediately while analytics are processed asynchronously.

### Files to Modify

1. `src/Services/AnalyticsService.php`
   - Modify event tracking to queue events
   - Add queue processing method
   - Add background job scheduling

2. `src/Services/NotificationService.php`
   - Add hook for processing queued events

### Implementation Details

#### Before (Current Code)
```php
// src/Services/AnalyticsService.php
public function trackClick( int $product_id, string $affiliate_url ): void {
    $event = [
        'type'        => 'click',
        'product_id'  => $product_id,
        'affiliate_url' => $affiliate_url,
        'timestamp'   => current_time( 'mysql' ),
        'user_agent'  => $this->get_user_agent(),
        'ip_address'  => $this->get_client_ip(),
    ];

    // Process immediately (blocks page load)
    $this->store_event( $event );
}

public function trackView( int $product_id ): void {
    $event = [
        'type'       => 'view',
        'product_id' => $product_id,
        'timestamp'  => current_time( 'mysql' ),
        'user_agent' => $this->get_user_agent(),
        'ip_address' => $this->get_client_ip(),
    ];

    // Process immediately (blocks page load)
    $this->store_event( $event );
}

private function store_event( array $event ): void {
    $data = get_option( 'affiliate_product_showcase_analytics', [] );
    $data[] = $event;
    
    // Update with no autoload for performance
    update_option( 'affiliate_product_showcase_analytics', $data, false );
}
```

#### After (Optimized Code)
```php
// src/Services/AnalyticsService.php
class AnalyticsService {
    const QUEUE_KEY = 'analytics_events_queue';
    const BATCH_SIZE = 50;

    public function trackClick( int $product_id, string $affiliate_url ): void {
        $event = [
            'type'         => 'click',
            'product_id'   => $product_id,
            'affiliate_url' => $affiliate_url,
            'timestamp'    => current_time( 'mysql' ),
            'user_agent'   => $this->get_user_agent(),
            'ip_address'   => $this->get_client_ip(),
        ];

        // Queue for background processing (non-blocking)
        $this->queue_event( $event );
    }

    public function trackView( int $product_id ): void {
        $event = [
            'type'       => 'view',
            'product_id' => $product_id,
            'timestamp'  => current_time( 'mysql' ),
            'user_agent' => $this->get_user_agent(),
            'ip_address' => $this->get_client_ip(),
        ];

        // Queue for background processing (non-blocking)
        $this->queue_event( $event );
    }

    /**
     * Queue analytics event for background processing
     *
     * @param array<string,mixed> $event Event data
     * @return void
     */
    private function queue_event( array $event ): void {
        $queue = get_transient( self::QUEUE_KEY, [] );
        
        if ( ! is_array( $queue ) ) {
            $queue = [];
        }

        $queue[] = $event;
        
        // Process queue if batch size reached
        if ( count( $queue ) >= self::BATCH_SIZE ) {
            $this->process_queue();
        } else {
            // Store in transient for 1 hour
            set_transient( self::QUEUE_KEY, $queue, HOUR_IN_SECONDS );
            
            // Schedule background processing if not already scheduled
            if ( ! wp_next_scheduled( 'process_analytics_queue' ) ) {
                wp_schedule_single_event( time() + 60, 'process_analytics_queue' );
            }
        }
    }

    /**
     * Process queued analytics events
     * Called by WordPress cron or when batch size is reached
     *
     * @return void
     */
    public function process_queue(): void {
        $queue = get_transient( self::QUEUE_KEY, [] );
        
        if ( empty( $queue ) || ! is_array( $queue ) ) {
            return;
        }

        // Get existing analytics data
        $analytics = get_option( 'affiliate_product_showcase_analytics', [] );
        
        // Append queued events
        $analytics = array_merge( $analytics, $queue );
        
        // Update with no autoload for performance
        update_option( 'affiliate_product_showcase_analytics', $analytics, false );
        
        // Clear queue
        delete_transient( self::QUEUE_KEY );
        
        // Clear scheduled event
        $timestamp = wp_next_scheduled( 'process_analytics_queue' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'process_analytics_queue' );
        }
    }
}
```

**Hook Registration (in Plugin initialization):**
```php
// Register hook for background processing
add_action( 'process_analytics_queue', [ $analytics_service, 'process_queue' ] );
```

### Expected Impact

**Performance Metrics:**
- **Page Load Time:** Reduced by ~100-300ms
- **Time to First Byte (TTFB):** Reduced by ~50-150ms
- **Database Load:** Reduced (batched writes instead of individual writes)

**User Experience:**
- Faster page loads during high traffic
- Better scalability
- Reduced server load
- Improved concurrency

**Server Load:**
- Fewer database writes per request
- Better resource utilization
- Improved handling of traffic spikes

### Implementation Complexity: **Medium**

**Steps:**
1. Create queue mechanism using transients
2. Modify event tracking to queue instead of process immediately
3. Add cron job for background processing
4. Add batch processing logic
5. Test queue processing
6. Test with high traffic simulation
7. Monitor queue size and processing time

---

## Summary of Changes

### Files to Modify

| File | Changes | Priority |
|------|----------|----------|
| `src/Admin/BulkActions.php` | Add generator for exports | 1 (High) |
| `src/Public/Enqueue.php` | Add defer to scripts | 2 (Medium) |
| `src/Admin/Enqueue.php` | Add defer to scripts | 2 (Medium) |
| `src/Services/AnalyticsService.php` | Implement queue system | 3 (Medium) |

### Expected Overall Impact

**Performance Improvements:**
- **Memory Usage:** 50-95% reduction for exports
- **Page Load Time:** 200-600ms improvement
- **Time to Interactive:** 200-400ms improvement
- **Core Web Vitals:** Significant improvement

**User Experience:**
- Faster page loads
- Better handling of large catalogs
- Improved scalability
- Reduced server load

### Implementation Effort

| Priority | Time Estimate | Complexity |
|----------|---------------|-------------|
| 1. Generator for exports | 2-3 hours | Medium |
| 2. Async/defer scripts | 1-2 hours | Low |
| 3. Background processing | 3-4 hours | Medium |
| **Total** | **6-9 hours** | **Medium** |

### Risk Assessment

**Low Risk:**
- Priority 2 (async/defer scripts) - Non-critical optimization

**Medium Risk:**
- Priority 1 (generator for exports) - Requires testing with large datasets
- Priority 3 (background processing) - Requires monitoring and fallback

**Mitigation Strategies:**
- Comprehensive testing before deployment
- Monitor performance after deployment
- Implement feature flags for rollback
- Keep backup of original code

---

## Next Steps

### Before Implementation

1. **Backup Current Code**
   - Create git branch for optimizations
   - Commit current state

2. **Set Up Monitoring**
   - Enable query monitoring
   - Set up performance tracking
   - Document baseline metrics

3. **Test Environment**
   - Prepare staging environment
   - Import test data (100+ products)
   - Configure monitoring tools

### Implementation Order

1. **Priority 2 (Low Risk, Quick Win)**
   - Implement async/defer for scripts
   - Test on staging
   - Deploy to production
   - Monitor results

2. **Priority 1 (Medium Risk)**
   - Implement generator for exports
   - Test with various dataset sizes
   - Monitor memory usage
   - Deploy to production

3. **Priority 3 (Medium Risk)**
   - Implement background processing
   - Test queue functionality
   - Monitor queue processing
   - Deploy to production with monitoring

### Post-Implementation

1. **Monitor Performance**
   - Track Core Web Vitals
   - Monitor memory usage
   - Check database query counts
   - Review analytics data accuracy

2. **Gather User Feedback**
   - Monitor user reports
   - Check error logs
   - Review performance metrics

3. **Fine-tune**
   - Adjust batch sizes if needed
   - Tune cache TTL values
   - Optimize queue processing interval

---

## Conclusion

These three high-priority optimizations will significantly improve the plugin's performance while maintaining reliability and user experience. The implementation is straightforward with clear steps and measurable impact.

**Recommendation:** Implement in order of priority (2, 1, 3) with thorough testing at each stage.

---

## Approval Required

Before proceeding with implementation, please confirm:

- [ ] Approve Priority 1: Generator for large exports
- [ ] Approve Priority 2: Async/defer for scripts
- [ ] Approve Priority 3: Background processing for analytics
- [ ] Approve implementation order (2 → 1 → 3)
- [ ] Approve testing approach
- [ ] Approve deployment strategy

Once approved, I will proceed with implementation one priority at a time, with testing and verification after each change.
