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

## PHASE 1: CRITICAL SECURITY FIXES

### 1.1 Add ABSPATH Protection to All PHP Files

**Severity:** CRITICAL\
**Files Affected:** 58+ files in `src/` directory\
**Effort:** 45 minutes

---

**□ PRE-FIX**

```
1. Backup:
   cp -r src/ src-backup-abspath-$(date +%Y%m%d-%H%M%S)/
   
2. Git:
   git checkout -b fix/1.1-abspath-protection
   
3. Verify current state:
   find src -name "*.php" -type f -exec grep -L "ABSPATH" {} \;
   # Should list all files needing protection
```

---

**□ EXECUTE**

```
1. Create protection script:
   cat > scripts/add-abspath-protection.sh << 'EOF'
   #!/bin/bash
   find src -name "*.php" -type f -exec sh -c '
     if ! grep -q "ABSPATH" "$1"; then
       echo "Adding ABSPATH check to $1"
       sed -i "1a\\\\nif ( ! defined( '\''ABSPATH'\'' ) ) {\\\\n\\\\texit;\\\\n}" "$1"
     fi
   ' _ {} \;
   EOF
   
2. Make script executable:
   chmod +x scripts/add-abspath-protection.sh
   
3. Test on ONE file first:
   cp src/Services/ProductService.php src/Services/ProductService.php.test
   
   # Run on test file only:
   sed -i "1a\\\\nif ( ! defined( '\''ABSPATH'\'' ) ) {\\\\n\\\\texit;\\\\n}" src/Services/ProductService.php.test
   
   # Verify test file:
   head -3 src/Services/ProductService.php.test
   
   # Expected output:
   # <?php
   #
   # if ( ! defined( 'ABSPATH' ) ) {
   # 	exit;
   # }
   
4. If test successful, run on all files:
   bash scripts/add-abspath-protection.sh
   
5. Verify all files:
   find src -name "*.php" -type f -exec grep -L "ABSPATH" {} \;
   # Should return empty list
```

---

**□ TEST**

```
1. Syntax check:
   find src -name "*.php" -type f -exec php -l {} \;
   # All files should show "No syntax errors detected"
   
2. Verify protection exists:
   grep -r "if ( ! defined( 'ABSPATH' ) )" src/ --include="*.php"
   # Should list all files
   
3. Activate plugin:
   wp plugin activate affiliate-product-showcase
   
4. Check for errors:
   tail -n 50 wp-content/debug.log | grep -i "affiliate-product-showcase"
   # Should show no errors
   
5. Test direct access prevention:
   curl -I http://localhost/wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php
   # Should return 403 or 404 (not 200)
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/
  git commit -m "Fix 1.1: Add ABSPATH protection to all PHP files
  
  - Added ABSPATH protection to 58+ PHP files
  - Prevents direct file access via HTTP
  - Meets WordPress.org approval requirement
  
  Fixes: Critical security issue from Audit C, G"
  
If ANY test fails:
  git reset --hard HEAD
  rm -rf src-backup-abspath-*/
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.2 if:
   - All PHP files have ABSPATH protection
   - No syntax errors in any file
   - Plugin activates successfully
   - No errors in debug.log
   - Direct file access is blocked

✗ Stop if:
   - Syntax errors detected
   - Plugin activation fails
   - Errors in debug.log
   - Direct file access still works
   - Any file missing ABSPATH protection
```

---

### 1.2 Fix Broken/Unused DI Container

**Severity:** CRITICAL\
**File:** `src/DependencyInjection/CoreServiceProvider.php`\
**Effort:** 3-4 hours\
**Decision Point:** Remove container OR fix it

---

**□ PRE-FIX**

```
1. Backup:
   cp -r src/DependencyInjection/ src-backup-di-$(date +%Y%m%d-%H%M%S)/
   
2. Git:
   git checkout -b fix/1.2-di-container
   
3. Analyze current usage:
   grep -r "CoreServiceProvider" src/ --include="*.php"
   grep -r "Container" src/ --include="*.php"
   
4. Decision: Choose ONE approach:
   [ ] Option A: Remove container entirely (RECOMMENDED)
   [ ] Option B: Fix container usage
```

---

**□ EXECUTE - OPTION A: REMOVE CONTAINER (RECOMMENDED)**

```
1. Remove CoreServiceProvider:
   rm src/DependencyInjection/CoreServiceProvider.php
   
2. Remove container references from Plugin.php:
   # Edit src/Plugin/Plugin.php
   # Remove: use AffiliateProductShowcase\DependencyInjection\CoreServiceProvider;
   # Remove: new CoreServiceProvider()
   
3. Implement manual DI in bootstrap():
   
   # Edit src/Plugin/Plugin.php - bootstrap() method
   
   private function bootstrap(): void {
       // Create all dependencies manually
       $repository = new ProductRepository();
       $validator = new ProductValidator();
       $factory = new ProductFactory();
       $formatter = new PriceFormatter();
       $cache = new Cache();
       $logger = new Logger();
       $affiliate_service = new AffiliateService($cache);
       
       // Inject into services
       $this->product_service = new ProductService(
           $repository,
           $validator,
           $factory,
           $formatter,
           $logger
       );
       
       $this->analytics_service = new AnalyticsService($repository);
   }
   
4. Remove DependencyInjection directory if empty:
   rmdir src/DependencyInjection/
```

---

**□ EXECUTE - OPTION B: FIX CONTAINER (ALTERNATIVE)**

```
1. Fix CoreServiceProvider registration:
   
   # Edit src/DependencyInjection/CoreServiceProvider.php
   public function register_services(Container $container): void {
       // Register all services with proper resolution
       $container->addShared('product.repository', ProductRepository::class);
       $container->addShared('product.validator', ProductValidator::class);
       $container->addShared('product.factory', ProductFactory::class);
       $container->addShared('product.formatter', PriceFormatter::class);
       $container->addShared('product.cache', Cache::class);
       $container->addShared('product.logger', Logger::class);
       $container->addShared('product.affiliate_service', AffiliateService::class);
       $container->addShared('product.service', ProductService::class);
       $container->addShared('analytics.service', AnalyticsService::class);
   }
   
2. Fix Plugin.php to use container:
   
   # Edit src/Plugin/Plugin.php - bootstrap() method
   private function bootstrap(): void {
       $container = new Container();
       $service_provider = new CoreServiceProvider();
       $service_provider->register_services($container);
       
       $this->product_service = $container->get('product.service');
       $this->analytics_service = $container->get('analytics.service');
   }
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Plugin/Plugin.php
   
2. Verify dependencies are injected:
   # Check that no 'new' keyword in service constructors
   grep -r "new " src/Services/ProductService.php | head -5
   
3. Activate plugin:
   wp plugin deactivate affiliate-product-showcase
   wp plugin activate affiliate-product-showcase
   
4. Test product creation:
   wp post create --post_type='aps_product' --post_title='Test Product'
   
5. Check debug.log:
   tail -n 50 wp-content/debug.log | grep -i "error"
   # Should show no errors
   
6. Verify service instantiation:
   # Test if services work correctly
   php -r "
   require_once 'wp-load.php';
   \$plugin = AffiliateProductShowcase\Plugin\Plugin::get_instance();
   var_dump(\$plugin->get_product_service());
   "
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass (Option A - Remove Container):
  git add src/Plugin/Plugin.php
  git rm src/DependencyInjection/CoreServiceProvider.php
  git commit -m "Fix 1.2: Remove broken DI container, implement manual DI
  
  - Removed broken CoreServiceProvider
  - Implemented manual dependency injection in bootstrap
  - All dependencies properly instantiated and injected
  - Removed tight coupling
  
  Fixes: Critical architecture issue from Audit G, C"

If ALL tests pass (Option B - Fix Container):
  git add src/DependencyInjection/CoreServiceProvider.php
  git add src/Plugin/Plugin.php
  git commit -m "Fix 1.2: Fix DI container implementation
  
  - Fixed service registration in CoreServiceProvider
  - Properly configured container resolution
  - All services use container for instantiation
  - Easy to mock for testing
  
  Fixes: Critical architecture issue from Audit G, C"

If ANY test fails:
  git reset --hard HEAD
  cp -r src-backup-di-*/* src/
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.3 if:
   - All services properly initialized
   - Plugin activates successfully
   - Product creation works
   - No errors in debug.log
   - Dependencies are injected (not hard-coded)
   - Container either removed or fixed completely

✗ Stop if:
   - Plugin activation fails
   - Service instantiation errors
   - Still using 'new' keyword in services
   - Broken container remains
   - Any PHP errors
```

---

### 1.3 Fix Uninstall Data Loss Default

**Severity:** CRITICAL\
**File:** `uninstall.php:21-23`\
**Effort:** 30 minutes

---

**□ PRE-FIX**

```
1. Backup:
   cp uninstall.php uninstall.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.3-uninstall-data-loss
   
3. Verify current state:
   grep -n "APS_UNINSTALL_REMOVE_ALL_DATA" uninstall.php
   # Should show current default value
```

---

**□ EXECUTE**

```
1. Change default from true to false:
   
   # Edit uninstall.php
   # Find and replace:
   
   # BEFORE:
   const APS_UNINSTALL_REMOVE_ALL_DATA = true;
   
   # AFTER:
   const APS_UNINSTALL_REMOVE_ALL_DATA = false;
   
2. OR better: Add user setting (choose one):
   
   # OPTION A: Simple default change (quicker)
   const APS_UNINSTALL_REMOVE_ALL_DATA = false;
   
   # OPTION B: User opt-in (better UX)
   const APS_UNINSTALL_REMOVE_ALL_DATA = (bool) get_option('aps_uninstall_remove_data', false);
   
3. Test implementation:
   # If using Option B, add test:
   wp option get aps_uninstall_remove_data
   # Should return empty (false by default)
```

---

**□ TEST**

```
1. Verify uninstall behavior:
   
   # Simulate uninstall (without actually uninstalling):
   php -r "
   const APS_UNINSTALL_REMOVE_ALL_DATA = false;
   var_dump(APS_UNINSTALL_REMOVE_ALL_DATA);
   "
   # Should output: bool(false)
   
2. Check database options exist:
   wp option get aps_settings
   
3. If using Option B, test opt-in:
   wp option update aps_uninstall_remove_data 1
   wp option get aps_uninstall_remove_data
   # Should return: 1
   
   # Reset to safe default:
   wp option delete aps_uninstall_remove_data
   
4. Verify uninstall.php syntax:
   php -l uninstall.php
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add uninstall.php
  git commit -m "Fix 1.3: Change uninstall data deletion default to false
  
  - Changed APS_UNINSTALL_REMOVE_ALL_DATA default from true to false
  - Data not deleted by default on uninstall
  - User has opt-in choice
  - Meets WordPress.org approval requirement
  
  Fixes: Critical data loss issue from Audit G, Security"

If ANY test fails:
  git reset --hard HEAD
  cp uninstall.php-backup-* uninstall.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.4 if:
   - Default value is false
   - Uninstall won't delete data by default
   - No syntax errors
   - User opt-in works (if Option B used)

✗ Stop if:
   - Default still true
   - Syntax errors
   - Data still deleted without consent
```

---

### 1.4 Fix Meta Save Bug (Treats False as Failure)

**Severity:** CRITICAL\
**File:** `src/Repositories/ProductRepository.php:143-152`\
**Effort:** 30 minutes

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Repositories/ProductRepository.php src/Repositories/ProductRepository.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.4-meta-save-bug
   
3. Locate the bug:
   grep -n "saveMeta" src/Repositories/ProductRepository.php
   # Should find the method around line 143-152
```

---

**□ EXECUTE**

```
1. Edit src/Repositories/ProductRepository.php
   
   # Find the saveMeta method (around line 143-152)
   
   # BEFORE (WRONG):
   private function saveMeta(int $post_id, array $meta): void {
       foreach ($meta as $key => $value) {
           if (update_post_meta($post_id, $key, $value) === false) {
               // This treats legitimate "disable value" saves as errors
               throw new RepositoryException("Failed to save meta: {$key}");
           }
       }
   }
   
   # AFTER (CORRECT):
   private function saveMeta(int $post_id, array $meta): void {
       foreach ($meta as $key => $value) {
           // Only update if value is actually changed
           $current = get_post_meta($post_id, $key, true);
           
           if ($value !== $current) {
               $result = update_post_meta($post_id, $key, $value);
               
               // update_post_meta returns false on FAILURE, not when value === false
               // It returns the old value on success (which might be false)
               if ($result === false && !in_array($value, [false, ''], true)) {
                   throw new RepositoryException("Failed to save meta: {$key}");
               }
           }
       }
   }
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Repositories/ProductRepository.php
   
2. Test saving false value:
   php -r "
   require_once 'wp-load.php';
   
   // Create test post
   \$post_id = wp_insert_post(['post_title' => 'Test', 'post_type' => 'aps_product']);
   
   // Save false value (should work now)
   update_post_meta(\$post_id, 'test_meta', false);
   
   // Verify it saved correctly
   \$saved = get_post_meta(\$post_id, 'test_meta', true);
   var_dump(\$saved === false); // Should be bool(true)
   
   // Cleanup
   wp_delete_post(\$post_id, true);
   "
   # Expected output: bool(true)
   
3. Test saving empty string (should also work):
   php -r "
   require_once 'wp-load.php';
   \$post_id = wp_insert_post(['post_title' => 'Test', 'post_type' => 'aps_product']);
   update_post_meta(\$post_id, 'test_meta', '');
   \$saved = get_post_meta(\$post_id, 'test_meta', true);
   var_dump(\$saved === ''); // Should be bool(true)
   wp_delete_post(\$post_id, true);
   "
   # Expected output: bool(true)
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Repositories/ProductRepository.php
  git commit -m "Fix 1.4: Fix meta save bug that treats false as failure
  
  - Updated saveMeta() to properly handle false values
  - Now checks if value changed before updating
  - False and empty string values save correctly
  - No more false positives for failed saves
  
  Fixes: Critical data loss issue from Audit G"

If ANY test fails:
  git reset --hard HEAD
  cp src/Repositories/ProductRepository.php-backup-* src/Repositories/ProductRepository.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.5 if:
   - False values save correctly
   - Empty string values save correctly
   - No false positive failure detection
   - Product creation/update works

✗ Stop if:
   - False values still fail
   - Empty strings still fail
   - Syntax errors
```

---

### 1.5 Fix REST API Exception Information Disclosure

**Severity:** CRITICAL\
**File:** `src/Rest/ProductsController.php:35-38`\
**Effort:** 1 hour

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Rest/ProductsController.php src/Rest/ProductsController.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.5-rest-exception-disclosure
   
3. Locate the problem:
   grep -n "\$e->getMessage()" src/Rest/ProductsController.php
   # Should find locations where exceptions are exposed
```

---

**□ EXECUTE**

```
1. Edit src/Rest/ProductsController.php
   
   # Find create() method (around line 35-38)
   
   # BEFORE (WRONG):
   public function create(\WP_REST_Request $request): \WP_REST_Response {
       try {
           $product = $this->product_service->create_or_update($request->get_json_params() ?? []);
           return $this->respond($product->to_array(), 201);
       } catch (\Exception $e) {
           return $this->respond([
               'message' => $e->getMessage(), // EXPOSES DETAILS
           ], 500);
       }
   }
   
   # AFTER (CORRECT):
   public function create(\WP_REST_Request $request): \WP_REST_Response {
       try {
           $product = $this->product_service->create_or_update($request->get_json_params() ?? []);
           return $this->respond($product->to_array(), 201);
           
       } catch (\AffiliateProductShowcase\Exceptions\PluginException $e) {
           // Log full error internally (includes details)
           error_log(sprintf(
               '[APS] Product creation failed: %s in %s:%d',
               $e->getMessage(),
               $e->getFile(),
               $e->getLine()
           ));
           
           // Return safe message to client
           return $this->respond([
               'message' => __('Failed to create product', 'affiliate-product-showcase'),
               'code' => 'product_creation_error',
           ], 400);
           
       } catch (\Throwable $e) {
           // Catch-all for unexpected errors
           error_log('[APS] Unexpected error in product creation: ' . $e->getMessage());
           
           return $this->respond([
               'message' => __('An unexpected error occurred', 'affiliate-product-showcase'),
               'code' => 'server_error',
           ], 500);
       }
   }
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Rest/ProductsController.php
   
2. Test with valid request:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"Test Product","affiliate_url":"https://example.com"}'
   # Should return 201 with product data
   
3. Test with invalid request (should return safe error):
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"","affiliate_url":"not-a-url"}'
   # Should return 400 with safe message, NOT expose validation details
   
4. Verify error logging:
   tail -n 20 wp-content/debug.log | grep -i "APS.*Product creation failed"
   # Should show detailed error in logs
   
5. Verify no exposure:
   # Check response doesn't contain server paths or technical details
   curl -s -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"","affiliate_url":"not-a-url"}' | grep -i "/var/www\|stack trace\|line "
   # Should return nothing (no technical details exposed)
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Rest/ProductsController.php
  git commit -m "Fix 1.5: Fix REST API exception information disclosure
  
  - Updated exception handling to log errors internally
  - Return safe, generic messages to clients
  - No longer exposes server paths or stack traces
  - Meets security best practices
  
  Fixes: Critical information disclosure issue from Audit G, Security"

If ANY test fails:
  git reset --hard HEAD
  cp src/Rest/ProductsController.php-backup-* src/Rest/ProductsController.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.6 if:
   - Valid requests return proper responses
   - Invalid requests return safe generic messages
   - Detailed errors logged internally only
   - No server paths or technical details exposed
   - Client receives user-friendly messages

✗ Stop if:
   - Exception details still exposed
   - Server paths in responses
   - Stack traces visible
   - No error logging
```

---

---

### 1.6 Apply AffiliateService to All Template URLs

**Severity:** CRITICAL\
**Files:** Multiple template files\
**Effort:** 2 hours

---

**□ PRE-FIX**

```
1. Backup:
   cp -r src/Public/partials/ src-backup-templates-$(date +%Y%m%d-%H%M%S)/
   
2. Git:
   git checkout -b fix/1.6-affiliate-service-urls
   
3. Find all template files:
   find src -name "*.php" -path "*/partials/*" -o -name "*template*.php"
   
4. Find raw URL outputs:
   grep -rn "esc_url.*affiliate_url" src/Public/
```

---

**□ EXECUTE**

```
1. Update product-card.php:
   
   # Edit src/Public/partials/product-card.php
   
   # BEFORE (WRONG):
   <a href="<?php echo esc_url($product->affiliate_url); ?>">
       <?php echo esc_html($cta_label); ?>
   </a>
   
   # AFTER (CORRECT):
   <?php 
   $link = $this->affiliate_service->build_link($product->affiliate_url);
   ?>
   <a href="<?php echo esc_url($link); ?>" rel="nofollow sponsored noopener noreferrer" target="_blank">
       <?php echo esc_html($cta_label); ?>
   </a>
   
2. Update all other templates:
   # Find all files with affiliate_url
   grep -l "affiliate_url" src/Public/partials/*.php
   
   # For each file found, replace direct URL output with AffiliateService
   
3. Update AffiliateService to add security attributes:
   
   # Edit src/Services/AffiliateService.php
   public function build_link(string $url): string {
       // Validate URL
       $sanitized = esc_url_raw($url);
       
       // Add security attributes
       $this->link_attributes = apply_filters('aps_affiliate_link_attributes', [
           'rel' => 'nofollow sponsored noopener noreferrer',
           'target' => '_blank',
           'data-aps-tracking' => 'true',
       ]);
       
       return $sanitized;
   }
```

---

**□ TEST**

```
1. Verify templates use AffiliateService:
   grep -rn "affiliate_service->build_link" src/Public/partials/
   # Should list all updated templates
   
2. Check for remaining raw URLs:
   grep -rn "esc_url.*affiliate_url" src/Public/
   # Should return empty
   
3. Test rendered output:
   # Create a test product and view in browser
   wp post create --post_type='aps_product' --post_title='Test' \
     --post_meta='{"aps_affiliate_url":"https://example.com"}'
   
4. Inspect HTML output:
   curl -s http://localhost/ | grep -A2 "affiliate"
   # Should show rel="nofollow sponsored noopener noreferrer" target="_blank"
   
5. Verify no direct URLs:
   grep -rn "href=\"<?php" src/Public/partials/
   # Should return empty
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Public/partials/ src/Services/AffiliateService.php
  git commit -m "Fix 1.6: Apply AffiliateService to all template URLs
  
  - All URLs now processed through AffiliateService
  - Security attributes (rel, target) always applied
  - No raw URLs in templates
  - Affiliate link tracking enabled
  
  Fixes: Critical URL bypass issue from Audit G"

If ANY test fails:
  git reset --hard HEAD
  cp -r src-backup-templates-*/* src/Public/partials/
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.7 if:
   - All templates use AffiliateService
   - No raw affiliate URLs found
   - Security attributes applied
   - Tracking data-attribute present

✗ Stop if:
   - Raw URLs still in templates
   - Security attributes missing
   - Direct URL output detected
```

---

### 1.7 Add posts_per_page Cap to Public REST Endpoint

**Severity:** CRITICAL\
**File:** `src/Rest/ProductsController.php`\
**Effort:** 30 minutes

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Rest/ProductsController.php src/Rest/ProductsController.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.7-rest-pagination-cap
   
3. Find current endpoint registration:
   grep -n "register_rest_route" src/Rest/ProductsController.php
```

---

**□ EXECUTE**

```
1. Edit src/Rest/ProductsController.php
   
   # Find register_routes() method
   
   # BEFORE:
   register_rest_route(
       $this->namespace,
       '/products',
       [
           [
               'methods' => WP_REST_Server::READABLE,
               'callback' => [$this, 'list'],
               'permission_callback' => [$this, 'permissions_check'],
               'args' => [], // MISSING VALIDATION
           ],
       ]
   );
   
   # AFTER:
   register_rest_route(
       $this->namespace,
       '/products',
       [
           [
               'methods' => WP_REST_Server::READABLE,
               'callback' => [$this, 'list'],
               'permission_callback' => [$this, 'permissions_check'],
               'args' => [
                   'per_page' => [
                       'type' => 'integer',
                       'minimum' => 1,
                       'maximum' => 100, // CAP at 100
                       'default' => 20,
                       'sanitize_callback' => 'absint',
                   ],
                   'orderby' => [
                       'type' => 'string',
                       'enum' => ['date', 'title', 'modified'],
                       'default' => 'date',
                       'sanitize_callback' => 'sanitize_text_field',
                   ],
                   'order' => [
                       'type' => 'string',
                       'enum' => ['ASC', 'DESC'],
                       'default' => 'DESC',
                       'sanitize_callback' => 'sanitize_key',
                   ],
               ],
           ],
       ]
   );
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Rest/ProductsController.php
   
2. Test default pagination:
   curl -s http://localhost/wp-json/affiliate-product-showcase/v1/products | jq '. | length'
   # Should return default 20
   
3. Test with valid per_page:
   curl -s "http://localhost/wp-json/affiliate-product-showcase/v1/products?per_page=50" | jq '. | length'
   # Should return 50
   
4. Test with per_page > 100 (should be capped):
   curl -s "http://localhost/wp-json/affiliate-product-showcase/v1/products?per_page=999" | jq '. | length'
   # Should return max 100
   
5. Test with invalid per_page:
   curl -s "http://localhost/wp-json/affiliate-product-showcase/v1/products?per_page=abc"
   # Should return error or use default
   
6. Test ordering:
   curl -s "http://localhost/wp-json/affiliate-product-showcase/v1/products?orderby=title&order=ASC"
   # Should return products sorted by title ascending
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Rest/ProductsController.php
  git commit -m "Fix 1.7: Add posts_per_page cap to public REST endpoint
  
  - Added per_page parameter with max 100
  - Added orderby validation (date, title, modified)
  - Added order validation (ASC, DESC)
  - DoS vector eliminated
  
  Fixes: Critical DoS vulnerability from Audit G, Security"

If ANY test fails:
  git reset --hard HEAD
  cp src/Rest/ProductsController.php-backup-* src/Rest/ProductsController.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.8 if:
   - Maximum 100 products per request enforced
   - Pagination parameters validated
   - Invalid values rejected or defaulted
   - DoS vector eliminated

✗ Stop if:
   - Can request unlimited results
   - No validation on per_page
   - Pagination not enforced
```

---

### 1.8 Fix Database Escape Using Private API

**Severity:** HIGH\
**File:** `src/Database/Database.php:85-88`\
**Effort:** 1 hour

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Database/Database.php src/Database/Database.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.8-database-escape-api
   
3. Find private API usage:
   grep -n "_escape" src/Database/Database.php
```

---

**□ EXECUTE**

```
1. Edit src/Database/Database.php
   
   # Find escape() method (around line 85-88)
   
   # BEFORE (WRONG):
   private function escape(string $value): string {
       return $this->wpdb->_escape($value);
   }
   
   # AFTER (CORRECT):
   private function escape(string $value): string {
       return esc_sql($value);
   }
   
   # OR BETTER: Avoid escaping by using prepared statements
   # Update any code that calls escape() to use prepared statements instead
   
   # Example of prepared statement:
   # BEFORE:
   $sql = "SELECT * FROM {$this->wpdb->prefix}aps_products WHERE id = " . $this->escape($id);
   
   # AFTER:
   $sql = $this->wpdb->prepare(
       "SELECT * FROM {$this->wpdb->prefix}aps_products WHERE id = %d",
       $id
   );
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Database/Database.php
   
2. Verify no private API usage:
   grep -rn "_escape" src/Database/
   # Should return empty
   
3. Verify esc_sql used instead:
   grep -rn "esc_sql" src/Database/
   # Should show the new implementation
   
4. Test database operations:
   php -r "
   require_once 'wp-load.php';
   \$db = new AffiliateProductShowcase\Database\Database();
   # Test that database operations work
   var_dump(\$db);
   "
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Database/Database.php
  git commit -m "Fix 1.8: Fix database escape using private API
  
  - Replaced _escape() with esc_sql()
  - No longer uses private WordPress API
  - Proper escaping functions used
  - Security tools pass
  
  Fixes: High priority API issue from Audit G"

If ANY test fails:
  git reset --hard HEAD
  cp src/Database/Database.php-backup-* src/Database/Database.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.9 if:
   - No private API usage (_escape)
   - Proper escaping functions used
   - Database operations work
   - Security tools pass

✗ Stop if:
   - Private API still used
   - Database operations fail
   - Security tools flag issues
```

---

### 1.9 Implement Cache Locking to Prevent Stampede

**Severity:** HIGH\
**File:** `src/Cache/Cache.php:23-28`\
**Effort:** 1 hour

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Cache/Cache.php src/Cache/Cache.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.9-cache-locking
   
3. Find remember() method:
   grep -n "function remember" src/Cache/Cache.php
```

---

**□ EXECUTE**

```
1. Edit src/Cache/Cache.php
   
   # Find remember() method (around line 23-28)
   
   # BEFORE (WRONG):
   public function remember(string $key, callable $resolver, int $ttl = 300): mixed {
       $cached = $this->get($key);
       if (false !== $cached) {
           return $cached;
       }
       
       // NO LOCKING - multiple requests can regenerate simultaneously
       $value = $resolver();
       $this->set($key, $value, $ttl);
       return $value;
   }
   
   # AFTER (CORRECT):
   public function remember(string $key, callable $resolver, int $ttl = 300): mixed {
       // Try to get from cache
       $cached = $this->get($key);
       if (false !== $cached) {
           return $cached;
       }
       
       // Implement cache locking
       $lock_key = $key . '_lock';
       $lock_acquired = wp_cache_add($lock_key, 1, $this->group, 10);
       
       if ($lock_acquired) {
           try {
               // We have the lock, generate the value
               $value = $resolver();
               $this->set($key, $value, $ttl);
               return $value;
           } finally {
               // Always release the lock
               wp_cache_delete($lock_key, $this->group);
           }
       }
       
       // If we couldn't get the lock, return stale data or wait
       return $this->get($key);
   }
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Cache/Cache.php
   
2. Verify cache locking logic:
   grep -A20 "function remember" src/Cache/Cache.php
   # Should show lock acquisition and release
   
3. Test cache behavior:
   php -r "
   require_once 'wp-load.php';
   \$cache = new AffiliateProductShowcase\Cache\Cache();
   
   \$key = 'test_cache_' . time();
   \$result = \$cache->remember(\$key, function() {
       sleep(2); // Simulate slow operation
       return 'cached_value';
   }, 60);
   
   var_dump(\$result);
   "
   
4. Test concurrent access (simulated):
   # Run multiple requests simultaneously to verify locking works
   for i in {1..5}; do
       php -r "require_once 'wp-load.php'; \$cache = new AffiliateProductShowcase\Cache\Cache(); \$cache->remember('test_concurrent', function() { sleep(2); return time(); }, 60);" &
   done
   wait
   # All should return same value (not regenerate 5 times)
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Cache/Cache.php
  git commit -m "Fix 1.9: Implement cache locking to prevent stampede
  
  - Added lock acquisition before cache regeneration
  - Only one request regenerates cache at a time
  - No cache stampede under concurrent requests
  - Better performance under load
  
  Fixes: High priority performance issue from Audit G, C"

If ANY test fails:
  git reset --hard HEAD
  cp src/Cache/Cache.php-backup-* src/Cache/Cache.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.10 if:
   - Cache locking implemented
   - Lock properly acquired and released
   - Concurrent requests don't regenerate multiple times
   - Performance improved under load

✗ Stop if:
   - No locking mechanism
   - Lock not released
   - Multiple regenerations occur
   - Performance degraded
```

---

### 1.10 Fix REST Namespace Collision

**Severity:** HIGH\
**File:** `src/Plugin/Constants.php:15` and all REST registrations\
**Effort:** 1 hour

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Plugin/Constants.php src/Plugin/Constants.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.10-rest-namespace
   
3. Find current namespace:
   grep -n "REST_NAMESPACE" src/Plugin/Constants.php
```

---

**□ EXECUTE**

```
1. Edit src/Plugin/Constants.php
   
   # BEFORE:
   const REST_NAMESPACE = 'affiliate/v1';
   
   # AFTER:
   const REST_NAMESPACE = 'affiliate-product-showcase/v1';
   
2. Verify all REST registrations use the constant:
   grep -rn "register_rest_route" src/Rest/ --include="*.php"
   
   # All should use: $this->namespace or Constants::REST_NAMESPACE
   
3. Update any hardcoded namespaces:
   # Find:
   grep -rn "'affiliate/v1'" src/
   
   # Replace with Constants::REST_NAMESPACE
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Plugin/Constants.php
   
2. Verify namespace constant:
   grep "REST_NAMESPACE" src/Plugin/Constants.php
   # Should show: const REST_NAMESPACE = 'affiliate-product-showcase/v1';
   
3. Test REST endpoint URL:
   curl -s http://localhost/wp-json/affiliate-product-showcase/v1/products
   # Should return products list
   
4. Verify old namespace doesn't work:
   curl -s http://localhost/wp-json/affiliate/v1/products
   # Should return 404
   
5. List all available routes:
   wp rest route list
   # Should show affiliate-product-showcase/v1 routes
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Plugin/Constants.php
  git commit -m "Fix 1.10: Fix REST namespace collision
  
  - Changed namespace from affiliate/v1 to affiliate-product-showcase/v1
  - Unique namespace prevents collisions
  - All routes updated
  - WordPress.org compliance improved
  
  Fixes: High priority namespace issue from Audit G, C"

If ANY test fails:
  git reset --hard HEAD
  cp src/Plugin/Constants.php-backup-* src/Plugin/Constants.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Proceed to 1.11 if:
   - Unique namespace used
   - Old namespace returns 404
   - All routes work with new namespace
   - No collision risk

✗ Stop if:
   - Generic namespace still used
   - Collision risk remains
   - Routes not updated
```

---

### 1.11 Add Complete REST API Request Validation

**Severity:** HIGH\
**File:** `src/Rest/ProductsController.php`\
**Effort:** 2 hours

---

**□ PRE-FIX**

```
1. Backup:
   cp src/Rest/ProductsController.php src/Rest/ProductsController.php-backup-$(date +%Y%m%d-%H%M%S)
   
2. Git:
   git checkout -b fix/1.11-rest-validation
   
3. Find current endpoint registrations:
   grep -n "register_rest_route" src/Rest/ProductsController.php
```

---

**□ EXECUTE**

```
1. Edit src/Rest/ProductsController.php - register_routes() method
   
   # Add comprehensive argument schemas to ALL endpoints
   
   # For POST /products endpoint:
   'args' => [
       'title' => [
           'required' => true,
           'type' => 'string',
           'validate_callback' => function($value) {
               return !empty($value) && strlen($value) <= 200;
           },
           'sanitize_callback' => 'sanitize_text_field',
       ],
       'price' => [
           'required' => false,
           'type' => 'number',
           'validate_callback' => function($value) {
               return is_numeric($value) && $value >= 0 && $value <= 999999.99;
           },
           'sanitize_callback' => 'floatval',
           'default' => 0,
       ],
       'affiliate_url' => [
           'required' => true,
           'type' => 'string',
           'format' => 'uri',
           'validate_callback' => function($value) {
               return filter_var($value, FILTER_VALIDATE_URL) !== false;
           },
           'sanitize_callback' => 'esc_url_raw',
       ],
       'currency' => [
           'required' => false,
           'type' => 'string',
           'sanitize_callback' => 'sanitize_text_field',
           'default' => 'USD',
       ],
       'description' => [
           'required' => false,
           'type' => 'string',
           'sanitize_callback' => 'wp_kses_post',
       ],
       'image_url' => [
           'required' => false,
           'type' => 'string',
           'format' => 'uri',
           'sanitize_callback' => 'esc_url_raw',
       ],
       'rating' => [
           'required' => false,
           'type' => 'number',
           'validate_callback' => function($value) {
               return is_numeric($value) && $value >= 0 && $value <= 5;
           },
           'sanitize_callback' => 'floatval',
       ],
       'badge' => [
           'required' => false,
           'type' => 'string',
           'sanitize_callback' => 'sanitize_text_field',
       ],
   ],
   
   # For GET /products/{id} endpoint:
   'args' => [
       'id' => [
           'required' => true,
           'type' => 'integer',
           'validate_callback' => 'absint',
           'sanitize_callback' => 'absint',
       ],
   ],
   
   # For PUT /products/{id} endpoint:
   # Add same validation as POST (but all optional)
   
2. Add validation error handler:
   
   # Add to ProductsController class:
   private function get_validation_errors(WP_REST_Request $request): array {
       $errors = [];
       $params = $request->get_json_params();
       
       // Validate title
       if (empty($params['title'])) {
           $errors['title'] = __('Title is required', 'affiliate-product-showcase');
       }
       
       // Validate URL
       if (!empty($params['affiliate_url']) && !filter_var($params['affiliate_url'], FILTER_VALIDATE_URL)) {
           $errors['affiliate_url'] = __('Invalid URL format', 'affiliate-product-showcase');
       }
       
       // Validate price
       if (isset($params['price']) && (!is_numeric($params['price']) || $params['price'] < 0)) {
           $errors['price'] = __('Price must be a positive number', 'affiliate-product-showcase');
       }
       
       return $errors;
   }
```

---

**□ TEST**

```
1. Syntax check:
   php -l src/Rest/ProductsController.php
   
2. Test valid request:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"Test","affiliate_url":"https://example.com"}'
   # Should return 201
   
3. Test missing required field:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"affiliate_url":"https://example.com"}'
   # Should return 400 with error message
   
4. Test invalid URL:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"Test","affiliate_url":"not-a-url"}'
   # Should return 400 with error
   
5. Test invalid price:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"Test","affiliate_url":"https://example.com","price":-50}'
   # Should return 400 with error
   
6. Test XSS attempt:
   curl -X POST http://localhost/wp-json/affiliate-product-showcase/v1/products \
     -H "Content-Type: application/json" \
     -d '{"title":"<script>alert(1)</script>","affiliate_url":"https://example.com"}'
   # Should sanitize and return 201 with clean title
```

---

**□ COMMIT OR ROLLBACK**

```
If ALL tests pass:
  git add src/Rest/ProductsController.php
  git commit -m "Fix 1.11: Add complete REST API request validation
  
  - Added comprehensive validation schemas for all endpoints
  - Invalid requests return 400 with error messages
  - All input sanitized before processing
  - XSS and injection attacks prevented
  
  Fixes: High priority security issue from Audit G, Security"

If ANY test fails:
  git reset --hard HEAD
  cp src/Rest/ProductsController.php-backup-* src/Rest/ProductsController.php
  echo "FAILED: Tests did not pass. Rollback complete."
  exit 1
```

---

**□ DECISION**

```
✓ Phase 1 Complete if:
   - All endpoints have validation schemas
   - Invalid requests return 400
   - All input sanitized
   - XSS/injection prevented
   - All 11 critical issues resolved

✗ Stop if:
   - Missing validation on any endpoint
   - Unvalidated data reaches business logic
   - Sanitization incomplete
   - Any security vulnerability remains

**PHASE 1 COMPLETE - PROCEED TO PHASE 2**
```

---

## PHASE 1 SUMMARY

**Total Issues Fixed:** 11 (All Critical + 4 High)\
**Total Effort:** 16-20 hours\
**Grade Improvement:** C (62/100) → B+ (82/100)\
**Status:** Production-Safe

**All Phase 1 issues now in safe execution workflow format.**

---

---