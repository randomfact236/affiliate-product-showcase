# Quality Standards: Enterprise-Grade 10/10

## 📋 Purpose

This document defines **quality standards** for all code written in this project. Every piece of code must meet these standards before being committed.

**Standard:** Hybrid quality matrix - Essential standards at 10/10, performance goals as targets
**Philosophy:** Maintain quality excellence while supporting sustainable development

---

## 📊 Hybrid Quality Matrix Overview

| Requirement | Type | Target | Notes |
|------------|-------|---------|-------|
| **Type hints** | MANDATORY | 10/10 | Strict typing required |
| **Security** | MANDATORY | 10/10 | Non-negotiable |
| **Coding standards** | MANDATORY | 10/10 | WPCS enforced via PHPCS; follow PSR-12 conventions in namespaced code |
| **Query time** | TRACK | Target: 300ms | Track trends, don't block |
| **API time** | MONITOR | Target: 500ms | Monitor, don't enforce |
| **Test coverage** | MANDATORY | 90%+ | Enterprise-grade requirement |
| **PHPDoc** | BASIC | Essential docs | Public methods + complex logic |
| **Monitoring** | BASIC | Track trends | No strict time rules |
| **LCP/FID** | GOAL | Track trends | Monitor improvement |
| **Psalm level** | MANDATORY | Level 4-5 | WordPress-realistic |

**Legend:**
- **MANDATORY (10/10)**: Required for all code
- **TRACK/MONITOR**: Measure and optimize when needed, don't block
- **GOAL**: Aim to achieve, but not a hard requirement
- **BASIC**: Essential documentation and monitoring only

---

## 📝 Universal Requirements (Apply to All Code)

### Every piece of code MUST:
- ✅ Follow project coding standards (WPCS via PHPCS; PSR-12 conventions in namespaced code)
- ✅ Have 100% type hints (PHP 8.1+ strict types)
- ✅ Include basic PHPDoc/JSDoc comments (public methods + complex logic)
- ✅ Pass all static analysis tools (Psalm level 4-5)
- ✅ Pass all code quality tools (PHPStan, ESLint)
- ✅ Have meaningful error handling
- ✅ Be secure (no XSS, SQL injection, CSRF vulnerabilities)
- ✅ Be accessible (WCAG 2.1 AA minimum)
- ✅ Be performant (no obvious bottlenecks)
- ✅ Be maintainable (DRY, SOLID principles)
- ✅ Have corresponding tests (90%+ coverage)

---

## ✅ Local Verification Commands (Required)

Run these from the repo root:

```bash
# PHP: static analysis + style
composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze

# PHP: tests (with coverage when needed)
composer --working-dir=wp-content/plugins/affiliate-product-showcase test
composer --working-dir=wp-content/plugins/affiliate-product-showcase test-coverage

# Frontend: lint + tests
npm --prefix wp-content/plugins/affiliate-product-showcase run lint
npm --prefix wp-content/plugins/affiliate-product-showcase run test
```

Notes:
- The canonical codebase for these standards is in `wp-content/plugins/affiliate-product-showcase/`.
- If a tool fails due to missing config files, treat that as a blocker: fix the toolchain before claiming 10/10 compliance.

---

## 1️⃣ Code Quality Standards

### PHP Code Requirements

   - Type Safety
```php
<?php
declare(strict_types=1);

namespace Your\Namespace;

use Psr\Log\LoggerInterface;

final class YourClass {
    // ✅ CORRECT: All properties typed
    private readonly string $property;
    private ?int $nullable_property = null;

    // ✅ CORRECT: All parameters and return types
    public function __construct(
        private readonly Dependency $dependency,
        private readonly LoggerInterface $logger
    ) {}

    // ✅ CORRECT: Method fully typed
    public function process_data(string $input): array {
        $sanitized = sanitize_text_field($input);
        return $this->dependency->transform($sanitized);
    }
}
```

**Requirements:**
- ✅ `declare(strict_types=1)` at top of every file
- ✅ All properties have explicit types
- ✅ All parameters have explicit types
- ✅ All return types declared
- ✅ Use `readonly` for immutable properties
- ✅ Use `?` for nullable types (not `@var`)

   - Function/Method Standards
```php
// ✅ CORRECT: Single responsibility, < 20 lines
public function get_product(int $id): ?Product {
    if ($id <= 0) {
        throw new InvalidArgumentException('ID must be positive');
    }

    $cached = $this->cache->get("product_{$id}");
    if ($cached !== null) {
        return $cached;
    }

    $product = $this->repository->find($id);
    if ($product !== null) {
        $this->cache->set("product_{$id}", $product, 3600);
    }

    return $product;
}

// ❌ WRONG: Too complex, multiple responsibilities
public function getAndCacheAndLogAndValidate(int $id): ?Product {
    // 50+ lines, doing too much
}
```

**Requirements:**
- ✅ Functions < 20 lines (unless necessary)
- ✅ Methods < 30 lines
- ✅ Cyclomatic complexity < 10
- ✅ Single responsibility per function
- ✅ No God classes (max 300 lines)
- ✅ No God methods (max 50 lines)

   - Error Handling
```php
// ✅ CORRECT: Proper exception handling
public function process_order(array $data): Order {
    try {
        $order = $this->create_order($data);
        $this->payment_service->charge($order);
        $this->notification_service->notify($order);
        
        return $order;
    } catch (PaymentException $e) {
        $this->logger->error('Payment failed', [
            'order_id' => $data['id'] ?? null,
            'error' => $e->getMessage()
        ]);
        throw new OrderException('Payment processing failed', 0, $e);
    } catch (\Throwable $e) {
        $this->logger->critical('Unexpected error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw new OrderException('Unable to process order', 0, $e);
    }
}

// ❌ WRONG: Silent failures
public function process_order(array $data): Order {
    $order = $this->create_order($data);
    // No error handling, might fail silently
    return $order;
}
```

**Requirements:**
- ✅ All exceptions caught and handled
- ✅ Never use `@` operator
- ✅ Never silently swallow exceptions
- ✅ Log all errors with context
- ✅ Throw domain-specific exceptions
- ✅ Use finally for cleanup

   - PHPDoc Documentation
```php
<?php
declare(strict_types=1);

namespace Your\Namespace;

use Your\Models\Product;
use Your\Exceptions\NotFoundException;

/**
 * Product service
 *
 * Handles business logic for product operations including
 * retrieval, creation, and updates with caching support.
 *
 * @since 1.0.0
 * @author Development Team
 */
final class ProductService {
    /**
     * Get a product by ID
     *
     * Retrieves product from database with caching support.
     * Returns null if product not found.
     *
     * @param int $id Product ID (must be positive integer)
     * @return Product|null Product instance or null
     * @throws InvalidArgumentException If ID is not positive
     * @throws RepositoryException If database query fails
     */
    public function get_product(int $id): ?Product {
        // Implementation
    }
}
```

**Requirements:**
- ✅ All classes have class-level PHPDoc
- ✅ All public methods have PHPDoc
- ✅ All parameters documented with `@param`
- ✅ All return types documented with `@return`
- ✅ All exceptions documented with `@throws`
- ✅ Use `@since` for version added
- ✅ Use `@author` for author

---

### JavaScript/React Code Requirements

   - Type Safety (TypeScript/Prop-Types)
```typescript
// ✅ CORRECT: TypeScript with full typing
interface ProductProps {
  id: number;
  title: string;
  price: number;
  onAddToCart: (id: number) => void;
}

const ProductCard: React.FC<ProductProps> = ({ 
  id, 
  title, 
  price, 
  onAddToCart 
}) => {
  const handleClick = () => {
    onAddToCart(id);
  };

  return (
    <article className="product-card">
      <h3>{title}</h3>
      <p>${price}</p>
      <button onClick={handleClick}>
        Add to Cart
      </button>
    </article>
  );
};

export default ProductCard;
```

**Requirements:**
- ✅ Use TypeScript for all new code
- ✅ All components have explicit prop types
- ✅ All functions have return types
- ✅ No `any` types (use `unknown` instead)
- ✅ Enable `strict: true` in tsconfig

   - Component Standards
```typescript
// ✅ CORRECT: Functional component with hooks
const ProductList: React.FC<ProductListProps> = ({ products }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Effect implementation
  }, [products.length]);

  if (isLoading) {
    return <Spinner />;
  }

  if (error) {
    return <ErrorMessage message={error} />;
  }

  return (
    <div className="product-list">
      {products.map(product => (
        <ProductCard key={product.id} {...product} />
      ))}
    </div>
  );
};
```

**Requirements:**
- ✅ Functional components only (no class components)
- ✅ Use React hooks
- ✅ Components < 200 lines
- ✅ Proper key props on lists
- ✅ Memoize expensive computations
- ✅ Memoize callback functions

   - Error Boundaries
```typescript
// ✅ CORRECT: Error boundary component
class ErrorBoundary extends React.Component<
  { children: React.ReactNode },
  { hasError: boolean }
> {
  state = { hasError: false };

  static getDerivedStateFromError(error: Error) {
    return { hasError: true };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('Error caught by boundary:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return <ErrorMessage />;
    }
    return this.props.children;
  }
}
```

**Requirements:**
- ✅ Wrap all major sections in ErrorBoundary
- ✅ Log all errors
- ✅ Provide user-friendly error messages
- ✅ Allow recovery when possible

---

## 2️⃣ Performance Standards

### Frontend Performance

   - Image Standards
```html
<!-- ✅ CORRECT: Optimized image -->
<img
  src="product.webp"
  srcset="product-400.webp 400w,
          product-800.webp 800w,
          product-1200.webp 1200w"
  sizes="(max-width: 600px) 400px,
         (max-width: 1200px) 800px,
         1200px"
  width="1200"
  height="800"
  alt="Blue widget showing three angles"
  loading="lazy"
  decoding="async"
  fetchpriority="auto"
/>

<!-- ✅ BEST: AVIF with WebP fallback -->
<picture>
  <source srcset="product.avif" type="image/avif">
  <source srcset="product.webp" type="image/webp">
  <img src="product.jpg" width="1200" height="800" alt="Description">
</picture>
```

**Requirements:**
- ✅ All images WebP or AVIF format
- ✅ Responsive srcset on all images
- ✅ Proper width/height attributes (prevent CLS)
- ✅ Lazy loading for below-fold images
- ✅ Alt text on all images
- ✅ Max 500KB per image

   - CSS Optimization
```css
/* ✅ CORRECT: Critical CSS inline */
<style>
/* Above-the-fold only */
.header { /* ... */ }
.hero { /* ... */ }
</style>

<!-- Non-critical deferred -->
<link rel="stylesheet" href="styles.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="styles.css"></noscript>
```

**Requirements:**
- ✅ Critical CSS < 14KB (inline)
- ✅ Non-critical CSS deferred
- ✅ All CSS minified
- ✅ No unused CSS
- ✅ Use CSS containment
- ✅ Prefer CSS over JS animations

   - JavaScript Optimization
```typescript
// ✅ CORRECT: Code splitting
const ProductGrid = React.lazy(() => import('./ProductGrid'));

function App() {
  return (
    <Suspense fallback={<Spinner />}>
      <ProductGrid />
    </Suspense>
  );
}

// ✅ CORRECT: Lazy load images
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const img = entry.target as HTMLImageElement;
      img.src = img.dataset.src || '';
      observer.unobserve(img);
    }
  });
});
```

**Requirements:**
- ✅ Code splitting for chunks > 100KB
- ✅ Tree shaking enabled
- ✅ All JS minified
- ✅ Lazy load non-critical JS
- ✅ Use Intersection Observer for lazy loading
- ✅ Debounce/throttle event handlers

### Backend Performance

   - Database Queries
```php
// ✅ CORRECT: Optimized query with cache
public function get_product(int $id): ?Product {
    $cache_key = "product_{$id}";
    $cached = wp_cache_get($cache_key, 'products');
    
    if ($cached !== false) {
        return $cached;
    }
    
    global $wpdb;
    $product = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'product'",
            $id
        ),
        ARRAY_A
    );
    
    if ($product) {
        wp_cache_set($cache_key, $product, 'products', 3600);
    }
    
    return $product ? $this->factory->from_array($product) : null;
}
```

**Requirements:**
- ✅ All queries use prepared statements
- ✅ Query results cached
- ✅ Only select needed columns
- ✅ Use indexed columns in WHERE clauses
- ✅ No N+1 query problems
- ✅ Track query time (target: 300ms, don't block)

   - API Response Time
```php
// ✅ CORRECT: Fast response with pagination
public function list(array $args = []): array {
    $per_page = min($args['per_page'] ?? 20, 100);
    
    $query = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'paged' => $args['page'] ?? 1,
        'fields' => 'ids', // Only get IDs for speed
    ]);
    
    // Cache metadata to prevent N+1
    $products = $this->hydrate_products($query->posts);
    
    return $products;
}
```

**Requirements:**
- ✅ Monitor API response time (target: 500ms, don't enforce)
- ✅ Pagination for all list endpoints
- ✅ Rate limiting implemented
- ✅ Proper HTTP status codes
- ✅ Gzip/Brotli compression enabled
- ✅ API documented (OpenAPI/Swagger)

   - API Rate Limiting
```php
// ✅ CORRECT: Rate limiting middleware
class RateLimiter {
    private string $prefix = 'api_rate_limit_';
    private int $requests_per_minute = 60;
    private int $requests_per_hour = 1000;
    
    public function check_limit(string $identifier): bool {
        $minute_key = $this->prefix . 'minute_' . $identifier;
        $hour_key = $this->prefix . 'hour_' . $identifier;
        
        $minute_count = (int) wp_cache_get($minute_key, 'api') ?: 0;
        $hour_count = (int) wp_cache_get($hour_key, 'api') ?: 0;
        
        if ($minute_count >= $this->requests_per_minute) {
            return false;
        }
        
        if ($hour_count >= $this->requests_per_hour) {
            return false;
        }
        
        // Increment counters
        wp_cache_set($minute_key, $minute_count + 1, 'api', 60);
        wp_cache_set($hour_key, $hour_count + 1, 'api', 3600);
        
        return true;
    }
    
    public function get_headers(string $identifier): array {
        $minute_count = (int) wp_cache_get($this->prefix . 'minute_' . $identifier, 'api') ?: 0;
        $hour_count = (int) wp_cache_get($this->prefix . 'hour_' . $identifier, 'api') ?: 0;
        
        return [
            'X-RateLimit-Limit-Minute' => $this->requests_per_minute,
            'X-RateLimit-Remaining-Minute' => $this->requests_per_minute - $minute_count,
            'X-RateLimit-Limit-Hour' => $this->requests_per_hour,
            'X-RateLimit-Remaining-Hour' => $this->requests_per_hour - $hour_count,
        ];
    }
}

// Usage in REST API
add_filter('rest_authentication_errors', function($result) {
    if (is_wp_error($result)) {
        return $result;
    }
    
    $rate_limiter = new RateLimiter();
    $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if (!$rate_limiter->check_limit($identifier)) {
        return new WP_Error(
            'rate_limit_exceeded',
            'API rate limit exceeded. Please try again later.',
            ['status' => 429]
        );
    }
    
    return $result;
});
```

**Rate Limiting Specifications:**

| Endpoint | Requests/Minute | Requests/Hour | Notes |
|----------|-----------------|----------------|-------|
| Public APIs | 60 | 1000 | Standard public access |
| Authenticated Users | 120 | 2000 | Double limit for logged-in users |
| Admin APIs | 300 | 5000 | Higher limit for admin operations |
| Webhook Endpoints | 10 | 100 | Stricter limit for webhooks |

**Requirements:**
- ✅ All API endpoints must have rate limiting
- ✅ Different limits for different user roles
- ✅ Rate limit headers in all responses
- ✅ Exponential backoff on rate limit errors
- ✅ Cache-based rate limiting (Redis recommended)
- ✅ IP-based and user-based tracking
- ✅ Rate limit reset headers included
- ✅ 429 status code on rate limit exceeded
- ✅ Clear error messages explaining limits

---

## 3️⃣ Security Standards

### Input Validation
```php
// ✅ CORRECT: Validate and sanitize all input
public function create_product(array $data): Product {
    $validated = [
        'title' => $this->validate_title($data['title'] ?? ''),
        'price' => $this->validate_price($data['price'] ?? 0),
        'description' => $this->validate_description($data['description'] ?? ''),
        'affiliate_url' => $this->validate_url($data['affiliate_url'] ?? ''),
    ];
    
    return $this->repository->save(
        $this->factory->from_array($validated)
    );
}

private function validate_title(string $title): string {
    $title = sanitize_text_field($title);
    
    if (empty($title)) {
        throw new ValidationException('Title is required');
    }
    
    if (strlen($title) > 200) {
        throw new ValidationException('Title must be less than 200 characters');
    }
    
    return $title;
}
```

**Requirements:**
- ✅ All user input validated
- ✅ All user input sanitized
- ✅ Use WordPress sanitization functions
- ✅ Validate data types
- ✅ Validate data ranges
- ✅ Never trust client-side validation

### XSS Prevention
```php
// ✅ CORRECT: Context-aware escaping
<h1><?php echo esc_html($product->title); ?></h1>

<p><?php echo wp_kses_post($product->description); ?></p>

<a href="<?php echo esc_url($product->url); ?>" 
   class="button">
   <?php echo esc_html($button_text); ?>
</a>
```

**Requirements:**
- ✅ All output escaped
- ✅ Use context-aware functions (`esc_html`, `esc_url`, `esc_attr`)
- ✅ HTML content uses `wp_kses_post`
- ✅ JSON output uses `wp_json_encode`
- ✅ Never output untrusted data without escaping
- ✅ Use nonce verification for actions

### SQL Injection Prevention
```php
// ✅ CORRECT: Prepared statements
global $wpdb;
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE post_title = %s AND post_status = %s",
        $search_term,
        'publish'
    )
);

// ✅ CORRECT: Parameterized queries
$products = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE ID IN (%s)",
        implode(',', array_map('intval', $ids))
    )
);
```

**Requirements:**
- ✅ All queries use `$wpdb->prepare`
- ✅ Never concatenate query strings
- ✅ Never use user input directly in queries
- ✅ Cast integers with `intval`
- ✅ Use `esc_sql` when necessary

### CSRF Protection
```php
// ✅ CORRECT: Nonce verification
<form method="post">
    <?php wp_nonce_field('delete_product_' . $product->id, 'delete_nonce'); ?>
    <button type="submit" name="action" value="delete">Delete</button>
</form>

// Handler
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if (!wp_verify_nonce($_POST['delete_nonce'], 'delete_product_' . $product_id)) {
        wp_die('Invalid nonce');
    }
    
    $this->product_service->delete($product_id);
}
```

**Requirements:**
- ✅ All forms have nonce fields
- ✅ All non-GET requests verify nonces
- ✅ Unique nonces per action
- ✅ Clear error messages on nonce failure
- ✅ Use `wp_create_nonce` and `wp_verify_nonce`

### Security Headers
```php
// ✅ CORRECT: Security headers
add_action('send_headers', function() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'');
    header('Referrer-Policy: strict-origin-when-cross-origin');
});
```

**Requirements:**
- ✅ X-Content-Type-Options: nosniff
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ X-XSS-Protection: enabled
- ✅ Strict-Transport-Security (HTTPS only)
- ✅ Content-Security-Policy
- ✅ Referrer-Policy

### Content Security Policy (CSP) Implementation

   - Basic CSP Configuration
```php
// ✅ CORRECT: Comprehensive CSP for WordPress plugin
add_action('send_headers', function() {
    $csp = [
        // Default to self only
        "default-src 'self'",
        
        // Scripts: Allow inline scripts (for WordPress admin) and specific CDN
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
        
        // Styles: Allow inline styles (for WordPress admin) and specific CDN
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
        
        // Images: Allow self, data URIs, and trusted image CDNs
        "img-src 'self' data: https://images.unsplash.com https://via.placeholder.com",
        
        // Connect: Allow API endpoints
        "connect-src 'self' https://api.example.com",
        
        // Fonts: Allow self and Google Fonts
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        
        // Objects: Block all plugins
        "object-src 'none'",
        
        // Media: Allow self only
        "media-src 'self'",
        
        // Frame: Only allow same origin (prevents clickjacking)
        "frame-src 'self'",
        
        // Base: Allow self only
        "base-uri 'self'",
        
        // Form actions: Allow same origin only
        "form-action 'self'",
        
        // Report URI: Report violations (optional, for monitoring)
        "report-uri /csp-violation-report-endpoint"
    ];
    
    header('Content-Security-Policy: ' . implode('; ', $csp));
});

// Optional: CSP violation report handler
add_action('init', function() {
    add_rewrite_rule('^csp-violation-report-endpoint/?$', 'index.php?csp_report=1', 'top');
});

add_filter('query_vars', function($query_vars) {
    $query_vars[] = 'csp_report';
    return $query_vars;
});

add_action('template_redirect', function() {
    if (get_query_var('csp_report')) {
        $report = json_decode(file_get_contents('php://input'), true);
        
        // Log CSP violations for debugging
        error_log('CSP Violation: ' . json_encode($report));
        
        // Send to monitoring service (Sentry, etc.)
        if (function_exists('sentry_capture_message')) {
            sentry_capture_message('CSP Violation', [
                'level' => 'warning',
                'extra' => $report
            ]);
        }
        
        status_header(204);
        exit;
    }
});
```

   - CSP with Nonce for Dynamic Scripts
```php
// ✅ CORRECT: CSP with nonce for inline scripts
class CSPManager {
    private $script_nonce;
    private $style_nonce;
    
    public function __construct() {
        add_action('wp_head', [$this, 'generate_nonces']);
        add_action('send_headers', [$this, 'set_csp_headers']);
    }
    
    public function generate_nonces() {
        $this->script_nonce = wp_create_nonce('csp-script');
        $this->style_nonce = wp_create_nonce('csp-style');
    }
    
    public function set_csp_headers() {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$this->script_nonce}' 'strict-dynamic'",
            "style-src 'self' 'nonce-{$this->style_nonce}'",
            "img-src 'self' data: https://*.example.com",
            "font-src 'self' https://fonts.googleapis.com",
            "object-src 'none'",
            "frame-src 'self'",
            "connect-src 'self' https://api.example.com"
        ];
        
        header('Content-Security-Policy: ' . implode('; ', $csp));
    }
    
    /**
     * Get inline script with nonce attribute
     */
    public function get_inline_script(string $script): string {
        return sprintf(
            '<script nonce="%s">%s</script>',
            wp_create_nonce('csp-script'),
            $script
        );
    }
    
    /**
     * Get inline style with nonce attribute
     */
    public function get_inline_style(string $style): string {
        return sprintf(
            '<style nonce="%s">%s</style>',
            wp_create_nonce('csp-style'),
            $style
        );
    }
}

// Usage
$csp_manager = new CSPManager();
echo $csp_manager->get_inline_script('console.log("Hello!");');
```

   - CSP for REST API Endpoints
```php
// ✅ CORRECT: Separate CSP for API responses
add_filter('rest_post_dispatch', function($result, $server, $request) {
    if ($request->get_route() === '/your-plugin/v1/products') {
        // More restrictive CSP for API
        header('Content-Security-Policy: default-src \'none\'; script-src \'none\'');
    }
    return $result;
}, 10, 3);
```

   - CSP Migration Strategy (Report-Only Mode)
```php
// ✅ CORRECT: Test CSP in report-only mode first
add_action('send_headers', function() {
    $is_development = wp_get_environment_type() === 'development';
    
    if ($is_development) {
        // Report-only mode: Don't block, just report violations
        header('Content-Security-Policy-Report-Only: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net');
    } else {
        // Enforce mode in production
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net');
    }
});
```

**CSP Directives Reference:**

| Directive | Purpose | Best Practice |
|-----------|---------|---------------|
| `default-src` | Fallback for all directives | Set to `'self'` only |
| `script-src` | Controls script loading | Use nonces for inline scripts |
| `style-src` | Controls style loading | Use nonces for inline styles |
| `img-src` | Controls image loading | Allow data URIs for base64 images |
| `font-src` | Controls font loading | Include Google Fonts CDN |
| `connect-src` | Controls AJAX/Fetch/WebSocket | Restrict to API endpoints |
| `object-src` | Controls plugins (Flash, etc.) | Always set to `'none'` |
| `frame-src` | Controls iframe loading | Restrict to trusted domains |
| `base-uri` | Controls base URL | Set to `'self'` |
| `form-action` | Controls form submissions | Restrict to same origin |

**Requirements:**
- ✅ Implement CSP in all environments
- ✅ Start with report-only mode for testing
- ✅ Use specific directives (avoid wildcard `*`)
- ✅ Use nonces for inline scripts/styles
- ✅ Monitor CSP violations
- ✅ Allow only trusted CDNs
- ✅ Block object-src (no Flash, etc.)
- ✅ Separate CSP for API endpoints
- ✅ Include CSP violation report endpoint
- ✅ Test CSP thoroughly before enabling enforce mode

**Common CSP Issues and Solutions:**

```php
// Issue 1: Inline scripts blocked
// Solution: Use nonce or move to external file
<script nonce="<?php echo wp_create_nonce('csp-script'); ?>">
    // Inline script code
</script>

// Issue 2: Inline styles blocked
// Solution: Use nonce or move to external file
<style nonce="<?php echo wp_create_nonce('csp-style'); ?>">
    /* Inline styles */
</style>

// Issue 3: External font blocked
// Solution: Add font domain to font-src
"font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net"

// Issue 4: Image from CDN blocked
// Solution: Add CDN domain to img-src
"img-src 'self' data: https://images.unsplash.com https://cdn.example.com"

// Issue 5: WordPress admin scripts blocked
// Solution: Allow 'unsafe-inline' and 'unsafe-eval' for admin area only
if (is_admin()) {
    $script_src = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
} else {
    $script_src = "script-src 'self' 'nonce-{$nonce}'";
}
```

**Testing CSP:**
```bash
# Test CSP headers
curl -I https://yoursite.com

# Check for CSP header
# Content-Security-Policy: default-src 'self'; script-src 'self'...

# Use browser dev tools to check for CSP violations
# Console will show CSP violation reports

# Online CSP testing tools
# https://csp-evaluator.withgoogle.com/
# https://report-uri.io/
```

---

## 4️⃣ Accessibility Standards

### Semantic HTML
```html
<!-- ✅ CORRECT: Semantic structure -->
<header role="banner">
  <nav aria-label="Main navigation">
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/products">Products</a></li>
    </ul>
  </nav>
</header>

<main role="main">
  <article>
    <h1>Product Title</h1>
    <p>Product description</p>
  </article>
</main>

<footer role="contentinfo">
  <p>&copy; 2026</p>
</footer>
```

**Requirements:**
- ✅ Use semantic HTML elements
- ✅ Proper heading hierarchy (h1, h2, h3...)
- ✅ Landmark regions (header, main, nav, footer)
- ✅ ARIA roles where needed
- ✅ Skip link for keyboard navigation
- ✅ Lang attribute on html tag

### Keyboard Navigation
```html
<!-- ✅ CORRECT: Keyboard accessible -->
<a href="#main-content" class="skip-link">
  Skip to main content
</a>

<button 
  type="button"
  aria-label="Close dialog"
  aria-describedby="close-help"
>
  <span aria-hidden="true">&times;</span>
</button>
<span id="close-help" class="sr-only">
  Press Escape to close
</span>
```

**Requirements:**
- ✅ All interactive elements keyboard accessible
- ✅ Visible focus indicators
- ✅ Tab order logical
- ✅ Skip links present
- ✅ ARIA labels on interactive elements
- ✅ No keyboard traps

### Color & Contrast
```css
/* ✅ CORRECT: Sufficient contrast (7:1 for AAA) */
.button-primary {
  color: #ffffff;
  background-color: #0066cc; /* 7:1 contrast ratio */
}

/* ✅ CORRECT: Focus visible */
button:focus-visible {
  outline: 3px solid #0066cc;
  outline-offset: 2px;
}

/* ✅ CORRECT: Reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Requirements:**
- ✅ Text contrast minimum 4.5:1 (AA) / 7:1 (AAA)
- ✅ Interactive elements contrast minimum 3:1
- ✅ Focus indicators visible
- ✅ No color-only information
- ✅ Respects prefers-reduced-motion
- ✅ High contrast mode support

### Screen Reader Support
```html
<!-- ✅ CORRECT: Screen reader friendly -->
<img src="product.webp" alt="Blue widget, $29.99, includes 3-year warranty">

<button aria-expanded="false" aria-controls="menu">
  <span class="icon" aria-hidden="true">☰</span>
  <span class="text">Menu</span>
</button>

<div role="status" aria-live="polite">
  Product added to cart
</div>
```

**Requirements:**
- ✅ All images have alt text
- ✅ Decorative images have empty alt
- ✅ ARIA live regions for dynamic content
- ✅ ARIA labels for complex interactions
- ✅ Hidden elements use `.sr-only` class
- ✅ Tested with NVDA/JAWS/VoiceOver

---

## 5️⃣ Testing Standards

### Unit Tests
```php
<?php
declare(strict_types=1);

namespace Your\Tests\Services;

use PHPUnit\Framework\TestCase;
use Your\Services\ProductService;
use Your\Repositories\ProductRepository;
use Your\Models\Product;

final class ProductServiceTest extends TestCase {
    private ProductService $service;
    private ProductRepository $repository;

    protected function setUp(): void {
        parent::setUp();
        $this->repository = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repository);
    }

    /** @test */
    public function it_gets_product_successfully(): void {
        // Arrange
        $expected = new Product(1, 'Test', 'test', 'Description', 'USD', 29.99);
        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($expected);

        // Act
        $result = $this->service->get_product(1);

        // Assert
        $this->assertSame($expected, $result);
    }
}
```

**Requirements:**
- ✅ Test coverage minimum 90% (enterprise-grade requirement)
- ✅ All public methods tested
- ✅ Test name describes behavior
- ✅ Arrange-Act-Assert pattern
- ✅ Mock external dependencies
- ✅ Test success and failure cases

### Integration Tests
```php
/** @test */
public function it_creates_product_via_api(): void {
    // Arrange
    wp_set_current_user(1); // Admin
    $data = [
        'title' => 'Test Product',
        'price' => 29.99,
        'affiliate_url' => 'https://example.com',
    ];

    // Act
    $response = $this->client->post('/wp-json/your-plugin/v1/products', [
        'json' => $data
    ]);

    // Assert
    $this->assertEquals(201, $response->getStatusCode());
    $body = json_decode($response->getBody(), true);
    $this->assertEquals('Test Product', $body['title']);
    $this->assertArrayHasKey('id', $body);
}
```

**Requirements:**
- ✅ Test API endpoints
- ✅ Test database interactions
- ✅ Test external service integrations
- ✅ Test authentication/authorization
- ✅ Test error handling
- ✅ Clean up test data

### E2E Tests
```typescript
test('user can add product to cart', async ({ page }) => {
  await page.goto('/products');
  
  await page.click('text=Add to Cart');
  
  await expect(page.locator('.cart-count')).toHaveText('1');
  await expect(page.locator('.notification')).toContainText('Product added');
});
```

**Requirements:**
- ✅ Test critical user journeys
- ✅ Test across browsers (Chrome, Firefox, Safari)
- ✅ Test on mobile devices
- ✅ Test with screen readers
- ✅ Test load handling
- ✅ Use real data (not mocks)

### Visual Regression Testing
```javascript
// Playwright visual regression test
test('product card visual regression', async ({ page }) => {
  await page.goto('/products');
  
  // Take screenshot and compare with baseline
  await expect(page).toHaveScreenshot('product-card.png', {
    maxDiffPixels: 100,
    threshold: 0.2
  });
});

// Percy.io integration
test('product page visual regression', async ({ page }) => {
  await page.goto('/products/1');
  
  // Percy takes screenshots across multiple viewports
  await percySnapshot(page, 'Product Page', {
    widths: [375, 768, 1280]
  });
});
```

**Requirements:**
- ✅ Visual tests for all major UI components
- ✅ Test across multiple viewports (mobile, tablet, desktop)
- ✅ Test across multiple browsers (Chrome, Firefox, Safari)
- ✅ Configure acceptable pixel difference threshold
- ✅ Automated visual regression in CI
- ✅ Baseline images stored in version control
- ✅ Review and approve visual changes before merge
- ✅ Exclude dynamic content from visual tests (dates, random data)

**Tools:**
- ✅ Playwright screenshot comparison
- ✅ Percy.io or Chromatic for cloud-based visual testing
- ✅ BackstopJS for visual regression testing
- ✅ Applitools for AI-powered visual testing

### Test Data Management
```php
<?php
declare(strict_types=1);

namespace Your\Tests\Fixtures;

use Your\Models\Product;

class ProductFixtures {
    /**
     * Create a test product with predictable data
     */
    public static function create_product(array $overrides = []): Product {
        return new Product(
            $overrides['id'] ?? 1,
            $overrides['title'] ?? 'Test Product',
            $overrides['slug'] ?? 'test-product',
            $overrides['description'] ?? 'Test description',
            $overrides['currency'] ?? 'USD',
            $overrides['price'] ?? 29.99
        );
    }

    /**
     * Create multiple test products
     */
    public static function create_products(int $count = 10): array {
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = self::create_product([
                'id' => $i,
                'title' => "Test Product {$i}",
                'slug' => "test-product-{$i}",
            ]);
        }
        return $products;
    }

    /**
     * Clean up test data after tests
     */
    public static function cleanup(): void {
        // Delete all test products
        $ids = get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_status' => 'any',
        ]);

        foreach ($ids as $id) {
            wp_delete_post($id, true);
        }

        // Clear caches
        wp_cache_flush();
    }
}

// PHPUnit bootstrap - Set up test database
$tests_dir = __DIR__;
require_once $tests_dir . '/includes/functions.php';

// Create test database
$wpdb->query("CREATE DATABASE IF NOT EXISTS test_wordpress");
$wpdb->select_db('test_wordpress');

// Load WordPress test environment
tests_add_filter('wp_die_handler', function() {
    throw new WPDieException('wp_die called');
});
```

```javascript
// JavaScript test fixtures for frontend tests
export const productFixtures = {
  createProduct(overrides = {}) {
    return {
      id: overrides.id || 1,
      title: overrides.title || 'Test Product',
      price: overrides.price || 29.99,
      currency: overrides.currency || 'USD',
      description: overrides.description || 'Test description',
      affiliateUrl: overrides.affiliateUrl || 'https://example.com',
      ...overrides
    };
  },

  createProducts(count = 10) {
    return Array.from({ length: count }, (_, i) => 
      this.createProduct({
        id: i + 1,
        title: `Test Product ${i + 1}`
      })
    );
  },

  createProductWithOriginalPrice() {
    return {
      ...this.createProduct(),
      originalPrice: 49.99,
      price: 29.99
    };
  }
};
```

**Requirements:**
- ✅ Separate test database from production/development
- ✅ Predictable test data (no random values in tests)
- ✅ Test fixtures for common data structures
- ✅ Clean up test data after each test
- ✅ Isolate tests (no shared state between tests)
- ✅ Use transactions for database tests (rollback after each test)
- ✅ Mock external dependencies (APIs, services)
- ✅ Seed test database with consistent data
- ✅ Version control test fixtures
- ✅ Document fixture data structure

**Best Practices:**
```php
// ✅ CORRECT: Use transactions and rollback
public function test_create_product() {
    $this->wpdb->query('START TRANSACTION');
    
    try {
        $product = $this->service->create($data);
        $this->assertNotNull($product);
    } finally {
        $this->wpdb->query('ROLLBACK');
    }
}

// ❌ WRONG: No cleanup, pollutes test database
public function test_create_product() {
    $product = $this->service->create($data);
    $this->assertNotNull($product);
    // Product remains in database!
}
```

**Requirements:**
- ✅ Use database transactions for faster tests
- ✅ Rollback transactions after each test
- ✅ Clean up file uploads after tests
- ✅ Clear caches between tests
- ✅ Use factories for creating test data
- ✅ Avoid hard-coded test data in test methods
- ✅ Use data providers for testing multiple scenarios

---

## 6️⃣ Documentation Standards

### Code Documentation
```php
/**
 * Product repository
 *
 * Handles database operations for products including
 * CRUD operations, caching, and query optimization.
 *
 * @package Your\Plugin\Repositories
 * @since 1.0.0
 * @author Development Team
 */
final class ProductRepository {
    /**
     * Find a product by ID
     *
     * Retrieves a single product from database with caching.
     * Returns null if product not found or if ID is invalid.
     *
     * @param int $id Product ID (must be positive integer)
     * @return Product|null Product instance or null if not found
     * @throws InvalidArgumentException If ID is not positive
     * @throws RepositoryException If database query fails
     * 
     * @example
     * ```php
     * $product = $repository->find(123);
     * if ($product !== null) {
     *     echo $product->title;
     * }
     * ```
     */
    public function find(int $id): ?Product {
        // Implementation
    }
}
```

**Requirements:**
- ✅ All classes have class-level PHPDoc
- ✅ All public methods have PHPDoc
- ✅ All complex logic has inline comments
- ✅ Uses `@package` and `@since`
- ✅ Includes `@example` where helpful
- ✅ Explains "why", not "what"

### API Documentation
```yaml
# openapi.yaml
paths:
  /products:
    get:
      summary: List products
      description: Retrieves a paginated list of products with optional filtering
      tags:
        - Products
      parameters:
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          schema:
            type: integer
            default: 20
            maximum: 100
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: '#/components/schemas/Product'
```

**Requirements:**
- ✅ All endpoints documented
- ✅ Request/response schemas
- ✅ Authentication requirements
- ✅ Rate limits documented
- ✅ Error responses documented
- ✅ Example requests/responses

---

## 7️⃣ DevOps & Infrastructure Standards

### Version Control Strategy
```bash
# Branch naming convention
feature/product-original-price    # New features
fix/auth-nonce-verification      # Bug fixes
hotfix/security-csrf-issue        # Critical hotfixes
refactor/caching-service          # Code refactoring
release/v1.0.0                  # Release preparation

# Workflow
main                            # Production-ready code only
├── develop                      # Integration branch
│   ├── feature/*                # Feature branches
│   ├── fix/*                   # Bug fix branches
│   └── hotfix/*               # Hotfix branches (merge to main & develop)
└── release/*                    # Release branches
```

**Requirements:**
- ✅ Git Flow workflow or similar
- ✅ Feature branches from develop
- ✅ Pull requests required for all changes
- ✅ At least 1 approval required for merge
- ✅ All tests must pass before merge
- ✅ No direct commits to main branch
- ✅ Semantic versioning (MAJOR.MINOR.PATCH)
- ✅ Tag releases with version numbers

### CI/CD Pipeline
```yaml
# .github/workflows/ci.yml example
name: CI
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run Psalm
        run: vendor/bin/psalm --level=4
      - name: Run tests
        run: vendor/bin/phpunit --coverage
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

**Requirements:**
- ✅ Automated testing on every commit
- ✅ Static analysis (Psalm, PHPStan) in CI
- ✅ Code quality checks (PHPCS) in CI
- ✅ Security scanning (Snyk, Dependabot)
- ✅ Test coverage reporting (Codecov)
- ✅ Automated deployment on merge to main
- ✅ Rollback capability
- ✅ Environment separation (dev/staging/prod)

### Monitoring & Alerting
```php
// Web Vitals tracking
<script type="module">
import {getCLS, getFID, getFCP, getLCP, getTTFB} from 'web-vitals';

function sendToAnalytics(metric) {
  // Send to Google Analytics, DataDog, etc.
  gtag('event', metric.name, {
    value: metric.value,
    custom_map: { 'metric_value': 'value' }
  });
}

getCLS(sendToAnalytics);
getFID(sendToAnalytics);
getLCP(sendToAnalytics);
getTTFB(sendToAnalytics);
</script>
```

**Metrics to Track:**
- ✅ Core Web Vitals (LCP, FID, CLS, TTFB)
- ✅ API performance trends
- ✅ Database query trends
- ✅ Error rates (404, 500, etc.)
- ✅ User engagement metrics
- ✅ Conversion rates (for affiliate links)
- ✅ Server resource usage (CPU, memory, disk)

**Alerting Rules:**
- ✅ Error rate > 1%
- ✅ Monitor for API performance degradation
- ✅ Monitor for database performance issues
- ✅ Server CPU > 80%
- ✅ Disk space < 10%
- ✅ Security vulnerabilities detected

**Requirements:**
- ✅ Real-time monitoring (New Relic, Datadog, APM)
- ✅ Error tracking (Sentry, Rollbar)
- ✅ Logging infrastructure (ELK, Splunk)
- ✅ Custom dashboards (Grafana, Looker)
- ✅ Alerting via Slack/PagerDuty
- ✅ 24/7 monitoring for production
- ✅ Log retention (minimum 90 days)
- ✅ Searchable logs

### Deployment Checklist
```markdown
## Pre-Deployment Checklist

### Code Quality
- [ ] All tests passing (unit, integration, E2E)
- [ ] Test coverage minimum 90%
- [ ] Static analysis passes (Psalm level 4-5)
- [ ] Code review approved
- [ ] No TODOs or FIXMEs in critical paths

### Security
- [ ] Security scan passed (Snyk, Dependabot)
- [ ] No known vulnerabilities
- [ ] CSP headers configured
- [ ] Nonce verification tested
- [ ] Input/output sanitization verified

### Performance
- [ ] Lighthouse score ≥ 98 (Performance)
- [ ] Lighthouse score ≥ 95 (Accessibility)
- [ ] Lighthouse score ≥ 95 (Best Practices)
- [ ] Lighthouse score ≥ 95 (SEO)
- [ ] Core Web Vitals passing
- [ ] Bundle size < 100KB (gzipped)
- [ ] No obvious performance issues
- [ ] Database queries optimized

### Documentation
- [ ] Changelog updated
- [ ] Migration guide provided (if breaking)
- [ ] API documentation updated
- [ ] README updated (if needed)

### Testing
- [ ] Manual testing completed
- [ ] Cross-browser testing (Chrome, Firefox, Safari)
- [ ] Mobile testing (iOS, Android)
- [ ] Accessibility testing (screen readers)
- [ ] Load testing completed

### Infrastructure
- [ ] Database backups verified
- [ ] Rollback plan tested
- [ ] Monitoring configured
- [ ] Alerting rules updated
- [ ] CDN cache cleared (if needed)
```

**Requirements:**
- ✅ All checklist items completed before deployment
- ✅ Staging environment mirrors production
- ✅ Blue-green deployment or similar
- ✅ Zero downtime deployments
- ✅ Rollback plan documented and tested
- ✅ Post-deployment verification
- ✅ Deployment notifications sent

### Rollback Procedures
```bash
# Emergency rollback script
#!/bin/bash

# 1. Identify last stable version
LAST_STABLE=$(git tag -l "v*" | sort -V | tail -n 2 | head -n 1)

# 2. Rollback code
git checkout $LAST_STABLE
git push origin main

# 3. Rollback database (if needed)
wp db rollback --confirm

# 4. Clear caches
wp cache flush
# CDN cache flush

# 5. Verify rollback
curl -f https://your-site.com/health || exit 1

# 6. Notify team
slack-send "🚨 Rollback to $LAST_STABLE completed"
```

**Requirements:**
- ✅ Automated rollback scripts
- ✅ Database rollback capability
- ✅ Cache invalidation procedure
- ✅ Health check endpoint
- ✅ Post-rollback verification
- ✅ Team notification on rollback
- ✅ Root cause analysis required

### Performance Budgets
```javascript
// webpack.config.js
const path = require('path');

module.exports = {
  performance: {
    maxEntrypointSize: 244000, // 244KB
    maxAssetSize: 244000,
    hints: 'warning'
  },
  output: {
    filename: '[name].[contenthash].js',
  }
};
```

**Budgets:**
- ✅ JavaScript bundle < 100KB (gzipped)
- ✅ CSS bundle < 20KB (gzipped)
- ✅ Total page weight < 500KB (initial)
- ✅ Monitor API response time (target: 500ms, don't enforce)
- ✅ Track database query time (target: 300ms, don't block)
- ✅ Track LCP trends (monitor improvement)
- ✅ Track FID trends (monitor improvement)
- ✅ CLS < 0.05

**Requirements:**
- ✅ Performance budgets enforced in CI
- ✅ Bundle size monitoring
- ✅ Query performance monitoring
- ✅ Automated alerts on budget violations
- ✅ Regular performance audits
- ✅ Budget reviews quarterly

### Docker Development Environment
```dockerfile
# Dockerfile
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www
```

```yaml
# docker-compose.yml
version: '3.8'
services:
  wordpress:
    build: .
    ports:
      - "8080:80"
    volumes:
      - .:/var/www
    environment:
      - WORDPRESS_DB_HOST=db
      - WORDPRESS_DB_NAME=wordpress
      - WORDPRESS_DB_USER=wordpress
      - WORDPRESS_DB_PASSWORD=wordpress
  
  db:
    image: mysql:8.0
    volumes:
      - db_data:/var/lib/mysql
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=wordpress
      - MYSQL_USER=wordpress
      - MYSQL_PASSWORD=wordpress

volumes:
  db_data:
```

**Requirements:**
- ✅ Docker setup for local development
- ✅ Docker Compose for services
- ✅ Consistent environment across team
- ✅ Hot reloading enabled
- ✅ Database seeds for testing
- ✅ Development Docker Hub images
- ✅ Production Docker images

---

## 8️⃣ Git Standards

### Commit Messages
```
✅ CORRECT: Conventional Commits

feat(product): Add original_price field
- Add property to Product model
- Update ProductFactory to handle original_price
- Add migration script for database

fix(authentication): Fix nonce verification on login
- Add missing nonce check
- Return proper error message
- Add test for nonce failure

docs(readme): Update installation instructions
- Clarify PHP version requirement
- Add Docker installation steps
- Update screenshots

refactor(services): Extract caching logic to CacheService
- Create dedicated CacheService class
- Move cache operations from ProductService
- Add cache invalidation methods

test(products): Add integration tests for API
- Test all CRUD operations
- Test error handling
- Test authentication

style(coding): Apply coding standards (WPCS/PSR-12)
- Fix indentation
- Remove trailing whitespace
- Add missing type hints

chore(deps): Update WordPress to 6.4
```

**Requirements:**
- ✅ Use conventional commits
- ✅ Subject line < 50 characters
- ✅ Imperative mood (Add, Fix, Update)
- ✅ Body explains what and why
- ✅ Reference issues if applicable
- ✅ No typos or grammar errors

### Pull Request Standards

   - PR Description Template
```
✅ CORRECT: PR Description

## Description
Adds support for original_price field to products, allowing display of sale prices and discounts.

## Changes
- Added `original_price` property to Product model
- Updated ProductFactory to handle original_price in both from_post() and from_array()
- Added migration script for database updates
- Updated API to include original_price in responses
- Added discount percentage calculation

## Testing
- Unit tests for Product model with original_price
- Integration tests for API responses
- Manual testing with products having/missing original_price

## Screenshots
[Screenshot of product grid showing original price with discount]

## Checklist
- [x] Code follows project coding standards (WPCS/PSR-12)
- [x] All tests passing
- [x] Documentation updated
- [x] No breaking changes
- [x] Performance tested
```

**Requirements:**
- ✅ Clear description of changes
- ✅ List of all changes
- ✅ Testing information
- ✅ Screenshots for UI changes
- ✅ Checklist completed
- ✅ Link to related issues

   - PR Size Limits
```yaml
# GitHub PR size limits enforced via .github/pr-size-limit.yml
pr_size_limit:
  max_added_lines: 400
  max_deleted_lines: 400
  max_changed_files: 15
  ignore_files:
    - "package-lock.json"
    - "composer.lock"
    - "*.min.js"
    - "*.min.css"
```

**Requirements:**
- ✅ PR must not exceed 400 added lines
- ✅ PR must not exceed 400 deleted lines
- ✅ PR must not exceed 15 changed files
- ✅ Large changes must be split into multiple PRs
- ✅ Each PR should focus on a single feature/fix
- ✅ Automated size check in CI

   - Code Review Checklist
```markdown
## Code Review Checklist

### Code Quality
- [ ] Code follows project coding standards (WPCS/PSR-12)
- [ ] All type hints present and correct
- [ ] PHPDoc complete for public methods
- [ ] No console errors or warnings
- [ ] No PHP warnings/errors
- [ ] Static analysis passes (Psalm level 4-5)
- [ ] Code is DRY and follows SOLID principles
- [ ] No code duplication
- [ ] Functions/methods are concise (< 20-30 lines)

### Functionality
- [ ] Feature works as expected
- [ ] Edge cases handled properly
- [ ] Error handling in place
- [ ] Tested manually if applicable
- [ ] Requirements fully implemented
- [ ] Backward compatibility maintained (if needed)

### Security
- [ ] All input validated
- [ ] All output escaped
- [ ] SQL queries use prepared statements
- [ ] Nonces verified for state-changing actions
- [ ] CSRF protection in place
- [ ] No sensitive data exposed
- [ ] Security headers configured

### Performance
- [ ] No obvious bottlenecks
- [ ] Images optimized
- [ ] Caching implemented where appropriate
- [ ] Database queries optimized
- [ ] N+1 query problems avoided
- [ ] Bundle size within limits

### Accessibility
- [ ] Semantic HTML used
- [ ] Keyboard navigable
- [ ] Alt text on images
- [ ] ARIA labels present where needed
- [ ] Color contrast sufficient (4.5:1 minimum)
- [ ] Focus indicators visible

### Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] E2E tests pass (if applicable)
- [ ] Test coverage minimum 90%
- [ ] Tests cover edge cases
- [ ] Tests are maintainable

### Documentation
- [ ] Code documented (PHPDoc/JSDoc)
- [ ] README updated (if needed)
- [ ] API docs updated (if applicable)
- [ ] Changelog updated
- [ ] Inline comments explain "why", not "what"
```

**Requirements:**
- ✅ All checklist items must be completed
- ✅ At least one approval required for merge
- ✅ Reviewer must verify all items
- ✅ Blocked items must be addressed before merge
- ✅ Reviewer should provide constructive feedback
- ✅ Author should respond to all comments

---

## ✅ Pre-Commit Checklist

Before committing any code, verify:

### Code Quality
- [ ] Code follows project coding standards (WPCS/PSR-12)
- [ ] All type hints present
- [ ] PHPDoc complete for public methods
- [ ] No console errors
- [ ] No PHP warnings/errors
- [ ] Static analysis passes (Psalm level 4-5)

### Functionality
- [ ] Feature works as expected
- [ ] Edge cases handled
- [ ] Error handling in place
- [ ] Tested manually

### Security
- [ ] All input validated
- [ ] All output escaped
- [ ] SQL queries prepared
- [ ] Nonces verified
- [ ] CSRF protection in place

### Performance
- [ ] No obvious bottlenecks
- [ ] Images optimized
- [ ] Caching implemented
- [ ] Database queries optimized

### Accessibility
- [ ] Semantic HTML
- [ ] Keyboard navigable
- [ ] Alt text on images
- [ ] ARIA labels present
- [ ] Color contrast sufficient

### Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] E2E tests pass (if applicable)
- [ ] Test coverage minimum 90%

### Documentation
- [ ] Code documented
- [ ] README updated (if needed)
- [ ] API docs updated (if applicable)
- [ ] Changelog updated

---

## 🎯 Summary: Your Quality Standard

**For every piece of code you write:**
1. **Follow this document** - It's your reference
2. **Aim for 10/10** - Enterprise-grade is the only standard
3. **Test thoroughly** - No code without tests
4. **Document clearly** - Future you will thank present you
5. **Optimize proactively** - Performance matters
6. **Secure by design** - Security is everyone's responsibility
7. **Make accessible** - Everyone deserves good UX

**The reward:** Code that's maintainable, performant, secure, and professional.

---

**Version:** 1.0.0  
**Last Updated:** 2026-01-15  
**Maintained By:** Development Team  
**Status:** ACTIVE - All new code must meet these standards (Hybrid Quality Matrix)
