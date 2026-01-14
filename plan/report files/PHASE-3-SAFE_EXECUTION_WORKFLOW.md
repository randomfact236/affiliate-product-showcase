# Safe Execution Workflow: Affiliate Product Showcase Plugin

**Generated:** January 14, 2026\
**Based On:** COMPREHENSIVE_IMPLEMENTATION_PLAN_REVISED.md\
**Purpose:** Step-by-step safe execution with pre-fix, execute, test, commit/rollback, and decision checkpoints\
**Total Issues:** 87

---

## CRITICAL CONSIDERATIONS & KNOWN LIMITATIONS

**Important Weak Points to Address:**

### 1. Optimistic Effort Estimates

- **DI Container Fix:** Estimated 3-4 hours → Realistic: 6-14 hours (depending on test coverage)
- **Complete REST Validation:** Estimated 2 hours → Realistic: 4-9 hours for comprehensive coverage
- **Impact:** These complex architectural changes require more time for proper testing and validation

### 2. Manual Testing Approach

- **Current State:** Testing is almost completely manual/smoke tests
- **Missing:**
  - No PHPUnit unit tests
  - No integration tests
  - No request mocking for REST API testing
  - No automated regression tests
- **Risk:** After plan completion, still very low automated coverage → regression risk remains high
- **Recommendation:** Add automated testing before each major fix

### 3. Manual DI Container Decision

- **Current Recommendation:** Remove container entirely (manual DI)
- **Pros:** Fine for small/medium plugins, simpler to understand
- **Cons:** If project scales to enterprise level, will need proper container later anyway
- **Decision Point:** Consider project roadmap before choosing manual vs container approach

### 4. No Performance/Load Testing

- **Missing:** Performance testing for critical fixes:
  - Cache stampede protection (1.9)
  - posts_per_page limiting (1.7)
- **Risk:** Cannot verify performance improvements under actual load
- **Recommendation:** Add load testing benchmarks before/after performance fixes

### 5. No Static Analysis After Big Changes

- **Missing:** Run static analysis after each architectural change:
  - PHPStan (type analysis)
  - Psalm (static analysis)
  - PHPCS (code style)
- **Risk:** New bugs may not be caught without automated analysis
- **Recommendation:** Add static analysis verification to COMMIT OR ROLLBACK section

### 6. Verbose Commit Messages

- **Current Style:** Very detailed multi-line commit messages
- **Pros:** Excellent for audit trail and code reviews
- **Cons:** Can become annoying in real teams after \~50-60 commits
- **Recommendation:** Consider using shorter, focused commit messages for routine changes, reserve verbose style for major refactors

---

## RECOMMENDED ADDITIONS TO WORKFLOW

**Add to each issue's COMMIT OR ROLLBACK section:**

```bash
# After tests pass, run static analysis:
vendor/bin/phpstan analyse src/ --level=5
vendor/bin/psalm src/
vendor/bin/phpcs --standard=WordPress src/

# Only commit if all analysis passes
```

**Add performance testing for performance-related issues:**

```bash
# Before fix - baseline:
ab -n 1000 -c 10 http://localhost/wp-json/affiliate-product-showcase/v1/products

# After fix - compare:
ab -n 1000 -c 10 http://localhost/wp-json/affiliate-product-showcase/v1/products
```

**Add automated testing requirement:**

- Before executing any fix, write failing test
- After fix, verify test passes
- Commit test with fix in same commit

---

## INSTRUCTIONS

Each issue follows this SAFE EXECUTION WORKFLOW:

```
□ PRE-FIX - Safety checks before starting
□ EXECUTE - Step-by-step implementation
□ TEST - Verification after implementation
□ COMMIT OR ROLLBACK - Safe commit or rollback procedure
□ DECISION - Go/No-Go checkpoint
```

**IMPORTANT:**

- Always complete PRE-FIX checklist before EXECUTE
- Always complete TEST before COMMIT OR ROLLBACK
- Always respect DECISION checkpoint criteria
- Roll back immediately if tests fail
- Document any deviations in notes

---

## PHASE 3: MEDIUM PRIORITY - COMPLETION & POLISH

**Timeline:** Week 4 (8-12 hours)\
**Priority:** NICE-TO-HAVE (Professional polish)\
**Goal:** Complete test coverage and documentation

---

### 3.1 Complete README.md Documentation

**Severity:** MEDIUM\
**Audits:** C, V, G\
**Files:** `README.md`\
**Effort:** 2-3 hours

---

**□ PRE-FIX**

```
1. Backup:
   cp README.md README.md-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/3.1-readme-documentation
   
3. Analyze current README:
   # Check existing sections
   grep -n "^##" README.md
```

---

**□ EXECUTE**

```
1. Update README.md with complete sections:
   
   # Add installation section:
   ## Installation
   
   ### From WordPress.org
   1. Go to Plugins > Add New
   2. Search for "Affiliate Product Showcase"
   3. Click Install Now
   4. Activate the plugin
   
   ### Manual Installation
   1. Download the plugin ZIP file
   2. Upload to wp-content/plugins/
   3. Activate from Plugins screen
   
   # Add usage section:
   ## Usage
   
   ### Shortcodes
   [affiliate_products limit="10" category="electronics"]
   
   ### Gutenberg Blocks
   Use the "Affiliate Product" block in the block editor
   
   ### REST API
   
   #### Get Products
   GET /wp-json/affiliate-product-showcase/v1/products
   
   #### Create Product
   POST /wp-json/affiliate-product-showcase/v1/products
   {
     "title": "Product Name",
     "affiliate_url": "https://example.com/product",
     "price": 19.99,
     "currency": "USD"
   }
   
   # Add API documentation:
   ## API Documentation
   
   ### Authentication
   All write operations require authentication with WordPress nonce
   
   ### Endpoints
   
   **GET /products**
   - Description: List all products
   - Parameters: per_page, page, orderby, order
   - Response: Array of product objects
   
   **POST /products**
   - Description: Create a new product
   - Required: title, affiliate_url
   - Optional: price, currency, description, image_url, rating, badge
   
   # Add development setup:
   ## Development Setup
   
   ### Requirements
   - PHP 8.0+
   - WordPress 6.0+
   - Composer
   - Node.js 16+
   
   ### Installation
   ```bash
   git clone https://github.com/randomfact236/affiliate-product-showcase.git
   cd affiliate-product-showcase
   composer install
   npm install
```

### Running Tests

```bash
vendor/bin/phpunit
npm test
```

# Add contributing guidelines:

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

# Add support information:

## Support

- Documentation: https://github.com/randomfact236/affiliate-product-showcase/wiki
- Issues: https://github.com/randomfact236/affiliate-product-showcase/issues
- Email: support@example.com

```

---

**□ TEST**
```

1. Verify README renders correctly:

   # View in markdown viewer

   cat README.md

2. Check all code examples:

   # Verify PHP code blocks are properly formatted

   grep -n "\`\`\`php" README.md

3. Test links:

   # Verify all external links work

   grep -E "https?://" README.md

4. Spell check:

   # Use markdown linter

   npm run lint:md || markdownlint README.md

```

---

**□ COMMIT OR ROLLBACK**
```

If ALL tests pass: git add README.md git commit -m "Fix 3.1: Complete README.md documentation

- Added installation instructions (WordPress.org and manual)
- Added complete usage examples (shortcodes, blocks, REST API)
- Added API documentation with endpoints and examples
- Added development setup instructions
- Added contributing guidelines
- Added support information

Fixes: Medium priority documentation issue from Audit C, V, G"

If ANY test fails: git reset --hard HEAD cp README.md-backup-\* README.md echo "FAILED: Tests did not pass. Rollback complete." exit 1

```

---

**□ DECISION**
```

✓ Proceed to 3.2 if:

- README is complete and well-structured
- Installation instructions clear
- Usage examples work
- API documentation accurate
- Development setup instructions correct

✗ Stop if:

- README incomplete
- Broken code examples
- Missing sections

```

---

### 3.2 Add Affiliate Disclosure Feature

**Severity:** MEDIUM  
**Audits:** C, Security  
**Files:** Settings, templates  
**Effort:** 1 hour

---

**□ PRE-FIX**
```

1. Backup: cp -r src/Settings/ src-backup-disclosure-$(date +%Y%m%d-%H%M%S)/

2. Git: git checkout -b fix/3.2-affiliate-disclosure

3. Find settings repository: grep -rn "SettingsRepository" src/Repositories/

```

---

**□ EXECUTE**
```

1. Add disclosure settings to SettingsRepository:

   # Edit src/Repositories/SettingsRepository.php

   private array $default_settings = \[ // ... existing settings ... 'disclosure_enabled' =&gt; false, 'disclosure_text' =&gt; \_\_('This post contains affiliate links. We may earn a commission if you make a purchase.', 'affiliate-product-showcase'), \];

2. Add settings fields:

   # Edit src/Admin/Settings.php

   add_settings_section( 'aps_disclosure_section', \_\_('Affiliate Disclosure', 'affiliate-product-showcase'), null, 'aps_settings_page' );

   add_settings_field( 'aps_disclosure_enabled', \_\_('Enable Disclosure', 'affiliate-product-showcase'), \[$this, 'render_checkbox_field'\], 'aps_settings_page', 'aps_disclosure_section', \[ 'id' =&gt; 'disclosure_enabled', 'label' =&gt; \_\_('Show disclosure on product cards', 'affiliate-product-showcase'), \] );

   add_settings_field( 'aps_disclosure_text', \_\_('Disclosure Text', 'affiliate-product-showcase'), \[$this, 'render_textarea_field'\], 'aps_settings_page', 'aps_disclosure_section', \[ 'id' =&gt; 'disclosure_text', 'label' =&gt; \_\_('Custom disclosure message', 'affiliate-product-showcase'), \] );

3. Add disclosure to product card template:

   # Edit src/Public/partials/product-card.php

```

---

**□ TEST**
```

1. Syntax check: php -l src/Repositories/SettingsRepository.php php -l src/Admin/Settings.php

2. Test settings page: wp plugin activate affiliate-product-showcase

   # Navigate to Settings &gt; Affiliate Product Showcase

   # Verify disclosure fields appear

3. Enable disclosure: wp option update aps_settings '{"disclosure_enabled":true,"disclosure_text":"Test disclosure"}' --format=json

4. Verify disclosure appears:

   # View product page in browser

   # Disclosure text should appear below product card

5. Test with disclosure disabled: wp option update aps_settings '{"disclosure_enabled":false}' --format=json

   # Disclosure should not appear

```

---

**□ COMMIT OR ROLLBACK**
```

If ALL tests pass: git add src/Settings/ src/Admin/Settings.php src/Public/partials/product-card.php git commit -m "Fix 3.2: Add affiliate disclosure feature

- Added disclosure settings (enabled/disabled)
- Added customizable disclosure text
- Added disclosure to product card template
- GDPR compliant disclosure

Fixes: Medium priority compliance issue from Audit C, Security"

If ANY test fails: git reset --hard HEAD cp -r src-backup-disclosure-*/* src/ echo "FAILED: Tests did not pass. Rollback complete." exit 1

```

---

**□ DECISION**
```

✓ Proceed to 3.3 if:

- Disclosure settings appear in admin
- Disclosure text customizable
- Disclosure shows on product cards when enabled
- Disclosure hidden when disabled

✗ Stop if:

- Settings not visible
- Disclosure not appearing
- Text not customizable

```

---

### 3.3 Implement Rate Limiting on REST API

**Severity:** MEDIUM  
**Audits:** C, Security  
**Files:** Create `src/Services/RateLimiter.php`  
**Effort:** 1 hour

---

**□ PRE-FIX**
```

1. Backup: cp -r src/Services/ src-backup-ratelimit-$(date +%Y%m%d-%H%M%S)/

2. Git: git checkout -b fix/3.3-rate-limiting

3. Check if RateLimiter exists: ls -la src/Services/ | grep -i rate

```

---

**□ EXECUTE**
```

1. Create RateLimiter service:

   # Create src/Services/RateLimiter.php

   cat &gt; src/Services/RateLimiter.php &lt;&lt; 'EOF'

   = $limit) { return false; } set_transient($key, $count + 1, self::WINDOW); return true; } public function get_remaining(string $identifier, int $limit = self::DEFAULT_LIMIT): int { $key = 'aps_ratelimit\_' . md5($identifier); $count = (int) get_transient($key); return max(0, $limit - $count); } public function reset(string $identifier): void { $key = 'aps_ratelimit\_' . md5($identifier); delete_transient($key); } } EOF

2. Inject RateLimiter into REST controllers:

   # Edit src/Rest/ProductsController.php

   private RateLimiter $rate_limiter;

   public function \__construct( ProductService $product_service, RateLimiter $rate_limiter ) { $this-&gt;product_service = $product_service; $this-&gt;rate_limiter = $rate_limiter; }

3. Use rate limiter in endpoints:

   # In list() method:

   public function list(\\WP_REST_Request $request): \\WP_REST_Response { $ip = $request-&gt;get_header('X-Forwarded-For') ?? $\_SERVER\['REMOTE_ADDR'\];

   ```
   if (!$this->rate_limiter->check($ip, 100)) {
       return $this->respond([
           'message' => __('Rate limit exceeded', 'affiliate-product-showcase'),
           'retry_after' => 3600,
       ], 429);
   }
   
   // ... rest of method
   ```

   }

   # In create() method:

   public function create(\\WP_REST_Request $request): \\WP_REST_Response { $ip = $request-&gt;get_header('X-Forwarded-For') ?? $\_SERVER\['REMOTE_ADDR'\];

   ```
   if (!$this->rate_limiter->check($ip, 50)) {
       return $this->respond([
           'message' => __('Rate limit exceeded', 'affiliate-product-showcase'),
           'retry_after' => 3600,
       ], 429);
   }
   
   // ... rest of method
   ```

   }

```

---

**□ TEST**
```

1. Syntax check: php -l src/Services/RateLimiter.php php -l src/Rest/ProductsController.php

2. Test rate limiting: php -r " require_once 'wp-load.php'; $limiter = new AffiliateProductShowcase\\Services\\RateLimiter();

   # Make 10 requests

   for ($i = 0; $i &lt; 10; $i++) { $allowed = $limiter-&gt;check('test_ip'); echo "Request $i: " . ($allowed ? 'Allowed' : 'Blocked') . "\\n"; } "

   # First 100 requests should be allowed

3. Test rate limit exceeded:

   # Make 101 requests

   # 101st request should return 429

4. Test remaining count: php -r " require_once 'wp-load.php'; $limiter = new AffiliateProductShowcase\\Services\\RateLimiter(); $remaining = $limiter-&gt;get_remaining('test_ip'); echo "Remaining: $remaining\\n"; "

5. Test rate limit reset:

   # Reset and verify requests allowed again

```

---

**□ COMMIT OR ROLLBACK**
```

If ALL tests pass: git add src/Services/RateLimiter.php src/Rest/ProductsController.php git commit -m "Fix 3.3: Implement rate limiting on REST API

- Created RateLimiter service
- Added rate limiting to list endpoint (100/hour)
- Added rate limiting to create endpoint (50/hour)
- Returns 429 on rate limit exceeded
- Shows retry_after header

Fixes: Medium priority security issue from Audit C, Security"

If ANY test fails: git reset --hard HEAD cp -r src-backup-ratelimit-*/* src/ echo "FAILED: Tests did not pass. Rollback complete." exit 1

```

---

**□ DECISION**
```

✓ Proceed to 3.4 if:

- Rate limiting implemented
- 429 response on limit exceeded
- retry_after header included
- Different limits for read/write operations

✗ Stop if:

- No rate limiting
- Unlimited requests allowed
- No 429 response

```

---

### 3.4 Add CSP Headers to Admin Pages

**Severity:** MEDIUM  
**Audits:** C, Security  
**Files:** `src/Admin/Admin.php`  
**Effort:** 30 minutes

---

**□ PRE-FIX**
```

1. Backup: cp src/Admin/Admin.php src/Admin/Admin.php-backup-$(date +%Y%m%d-%H%M%S)

2. Git: git checkout -b fix/3.4-csp-headers

3. Find Admin.php: grep -rn "class Admin" src/Admin/

```

---

**□ EXECUTE**
```

1. Add security headers method:

   # Edit src/Admin/Admin.php

   public function add_security_headers(): void { if (false !== strpos($\_SERVER\['PHP_SELF'\], 'affiliate-product-showcase')) { header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;"); header("X-Content-Type-Options: nosniff"); header("X-Frame-Options: DENY"); header("X-XSS-Protection: 1; mode=block"); } }

2. Hook into admin initialization:

   # In constructor or register() method:

   add_action('admin_init', \[$this, 'add_security_headers'\]);

```

---

**□ TEST**
```

1. Syntax check: php -l src/Admin/Admin.php

2. Test in browser:

   # Navigate to admin page with plugin

   # Open browser dev tools &gt; Network tab

   # Refresh page

   # Check response headers

3. Verify CSP header:

   # Should see: Content-Security-Policy: default-src 'self'; ...

4. Verify X-Content-Type-Options:

   # Should see: X-Content-Type-Options: nosniff

5. Verify X-Frame-Options:

   # Should see: X-Frame-Options: DENY

6. Verify X-XSS-Protection:

   # Should see: X-XSS-Protection: 1; mode=block

```

---

**□ COMMIT OR ROLLBACK**
```

If ALL tests pass: git add src/Admin/Admin.php git commit -m "Fix 3.4: Add CSP headers to admin pages

- Added Content-Security-Policy header
- Added X-Content-Type-Options: nosniff
- Added X-Frame-Options: DENY
- Added X-XSS-Protection: 1; mode=block
- Enhanced admin security

Fixes: Medium priority security issue from Audit C, Security"

If ANY test fails: git reset --hard HEAD cp src/Admin/Admin.php-backup-\* src/Admin/Admin.php echo "FAILED: Tests did not pass. Rollback complete." exit 1

```

---

**□ DECISION**
```

✓ Proceed to 3.5 if:

- All security headers present
- CSP policy properly set
- XSS protection enabled
- Clickjacking protection enabled

✗ Stop if:

- Headers missing
- Incorrect CSP policy
- Security headers not applied

```

---

### 3.5 Add Defer/Async Attributes to Scripts

**Severity:** MEDIUM  
**Audits:** C, V, Performance  
**Files:** `src/Assets/Assets.php`  
**Effort:** 30 minutes

---

**□ PRE-FIX**
```

1. Backup: cp src/Assets/Assets.php src/Assets/Assets.php-backup-$(date +%Y%m%d-%H%M%S)

2. Git: git checkout -b fix/3.5-script-defer

3. Find Assets.php: grep -rn "wp_enqueue_script" src/Assets/

```

---

**□ EXECUTE**
```

1. Add defer attribute to admin scripts:

   # Edit src/Assets/Assets.php

   public function enqueue_admin(): void { $url = $this-&gt;assets_url('js/admin.js'); $version = $this-&gt;assets_version('js/admin.js'); $deps = \['jquery'\];

   ```
   wp_enqueue_script('aps-admin', $url, $deps, $version, true);
   wp_script_add_data('aps-admin', 'defer', true);
   ```

   }

2. Add defer attribute to public scripts:

   # Edit src/Assets/Assets.php

   public function enqueue_public(): void { $url = $this-&gt;assets_url('js/public.js'); $version = $this-&gt;assets_version('js/public.js'); $deps = \[\];

   ```
   wp_enqueue_script('aps-public', $url, $deps, $version, true);
   wp_script_add_data('aps-public', 'defer', true);
   ```

   }

```

---

**□ TEST**
```

1. Syntax check: php -l src/Assets/Assets.php

2. Test in browser:

   # Load admin page

   # Open browser dev tools &gt; Network tab

   # Check script tags

3. Verify defer attribute:

   # Should see: