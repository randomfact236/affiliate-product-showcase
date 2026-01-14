# Enterprise Code Quality Audit — Affiliate Product Showcase

PLUGIN: Affiliate Product Showcase
AUDIT DATE: 2026-01-14
AUDIT VERSION: 1.0.0 (from plugin header)
SCOPE: wp-content/plugins/affiliate-product-showcase/
TARGET BAR: 10/10 enterprise-grade (Wordfence + WP Rocket + 10up expectations)

OVERALL GRADE: C (Score: 62/100)

CRITICAL ISSUES: 0
HIGH ISSUES: 7
MEDIUM ISSUES: 14
LOW ISSUES: 12

ESTIMATED FIX TIME: 2–4 days (1 senior engineer)
RECOMMENDATION: Fix High issues before shipping.
VERDICT: Strong foundation (Vite/Tailwind, manifest caching, some standards), but security hardening + API/design cleanup and DI/validation correctness are not yet enterprise-grade.

---

## Top 10 Must-Fix Issues (prioritized)

1) [HIGH] [S1.1/S1.2] REST create endpoint accepts unsanitized/unvalidated payload
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:40
Issue: `create_or_update()` is called with `$request->get_json_params()` directly, while the validator does not sanitize/normalize fields.
Impact: Stored XSS risk (admin-privileged or compromised admin), data integrity issues, unexpected types causing notices, and attack surface for future expansions.
Fix:
- Implement field-level sanitization + validation in `ProductValidator::validate()`.
- Prefer a schema-driven approach: add `args` with `sanitize_callback` / `validate_callback` to `register_rest_route()` and/or use `WP_REST_Request` schema.
- Ensure affiliate URLs are sanitized via `esc_url_raw()` and allowlist schemes.
Effort: Medium (2–4 hours)
Priority: Must-fix

2) [HIGH] [S2.4/O1.4] REST error responses leak raw exception messages
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:43
Issue: API responds with `[ 'message' => $e->getMessage() ]`.
Impact: Information disclosure; error strings may reveal internals or sensitive details. Also inconsistent error shape.
Fix:
- Log internal exception (using a non-static logger service or at minimum `AffiliateProductShowcase\Helpers\Logger::exception()`).
- Return a generic message + a stable machine code, e.g. `new WP_Error( 'aps_invalid_product', __( 'Invalid product payload.', 'affiliate-product-showcase' ), [ 'status' => 400 ] )`.
Effort: Low (30–60 min)
Priority: Must-fix

3) [HIGH] [S3.5/A6.1] Public REST listing route lacks constraints and request validation
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:19
Issue: `permission_callback` is `__return_true` for GET /products and the `per_page` param is not constrained/capped.
Impact: Unbounded query sizes and scraping; can degrade performance on high-traffic sites and becomes a DDoS vector.
Fix:
- Add explicit route `args` and cap `per_page` (e.g. `min=1, max=50`), default 12.
- Consider caching the list endpoint output (object cache) keyed by args + last-changed.
- If endpoint is not intended public, require auth + capability.
Effort: Medium (1–2 hours)
Priority: Must-fix

4) [HIGH] [Q5.4/P2.3] Product meta save treats “no change” as failure
File: wp-content/plugins/affiliate-product-showcase/src/Repositories/ProductRepository.php:186
Issue: Throws on `update_post_meta()` returning `false`. WP returns `false` when the value is unchanged.
Impact: Legitimate saves fail and surface as REST errors/admin errors; causes flaky behavior and breaks idempotency.
Fix:
- Do not treat `false` as failure. If you must detect failures, use `add_post_meta()`/`update_post_meta()` patterns, or compare `get_post_meta()` before/after, or accept `false` as “no change”.
Effort: Low (30–60 min)
Priority: Must-fix

5) [HIGH] [A3.1/A3.2] Dependency Injection layer is present but broken and unused
File: wp-content/plugins/affiliate-product-showcase/src/DependencyInjection/CoreServiceProvider.php:41–113
Issue: Container registrations call nonexistent or mismatching constructors (e.g., `Database::get_instance()`; `new Admin()` without required args; `new ProductService($repo)` but ProductService has no params).
Impact: DI cannot be safely introduced; dead/incorrect code increases maintenance risk and can mislead reviewers/auditors.
Fix:
- Either remove DI package and the broken provider until needed, or fix it end-to-end:
  - Make services accept dependencies via constructors (no `new` inside services).
  - Register correct factories for Admin/Public/Assets/Services.
  - Add a single composition root (bootstrap) that builds the container and pulls the Plugin entrypoint.
Effort: High (0.5–1.5 days depending on scope)
Priority: Must-fix

6) [HIGH] [S5.2/DATA] Uninstall deletes all plugin data by default
File: wp-content/plugins/affiliate-product-showcase/uninstall.php:18
Issue: `APS_UNINSTALL_REMOVE_ALL_DATA` defaults to `true`.
Impact: Data loss on uninstall with no explicit opt-in. This is a WordPress.org “surprise destructive behavior” smell.
Fix:
- Default to `false` and require explicit opt-in (setting + clear UI warning).
- Document behavior in readme.
Effort: Low (15–30 min)
Priority: Must-fix

7) [HIGH] [S4.4] Uninstall runs raw SQL with dynamic identifiers without robust hardening
File: wp-content/plugins/affiliate-product-showcase/uninstall.php:64
Issue: `DROP TABLE IF EXISTS `$table`` where `$table` comes from `$wpdb->prefix . 'aps_*'`.
Impact: Low likelihood but still a hard-to-audit raw-SQL path; can trigger WAF false-positives and makes security review harder.
Fix:
- Validate table names against a strict allowlist derived from known constants; do not interpolate arbitrary names.
- Consider using `$wpdb->query( 'DROP TABLE IF EXISTS ' . esc_sql( $table ) )` only after strict allowlisting.
Effort: Medium (1–2 hours)
Priority: Must-fix

8) [MEDIUM] [W6.3] Outbound affiliate links missing `noreferrer`
File: wp-content/plugins/affiliate-product-showcase/src/Public/partials/product-card.php:26
Issue: Uses `target="_blank"` and `rel="nofollow sponsored noopener"` (missing `noreferrer`).
Impact: Referrer leakage and weaker tabnabbing protections.
Fix: Use `rel="sponsored noopener noreferrer"` (and drop `nofollow` unless explicitly desired).
Effort: Low (5–10 min)
Priority: Should-fix

9) [MEDIUM] [P2.4] Cache helper cannot cache `false` and has stampede risk
File: wp-content/plugins/affiliate-product-showcase/src/Cache/Cache.php:11–36
Issue: `remember()` treats `false` as cache-miss; no lock/jitter.
Impact: Recomputes for legit falsey values; stampedes on hot keys.
Fix:
- Use `$found` param in `wp_cache_get( $key, $group, false, $found )`.
- Consider a simple lock key pattern for expensive resolvers.
Effort: Medium (1–2 hours)
Priority: Should-fix

10) [MEDIUM] [S6.3/A4.3] AffiliateService exists but is not applied to rendered URLs
File: wp-content/plugins/affiliate-product-showcase/src/Services/AffiliateService.php:1–120
Related output: wp-content/plugins/affiliate-product-showcase/src/Public/partials/product-card.php:26
Issue: The template outputs `$product->affiliate_url` directly and does not pass through `AffiliateService::build_link()`.
Impact: All the strict URL protection logic is bypassed.
Fix:
- Centralize outbound link building in one place (e.g., Product model accessor or a renderer/formatter service).
- Call `AffiliateService::build_link()` before output and handle exceptions gracefully.
Effort: Medium (2–4 hours)
Priority: Should-fix

---

## Category Scores (weighted)
- Security: 12/20
- Performance: 10/15
- Architecture: 7/15
- Code Quality: 6/10
- WordPress Integration: 6/8
- Frontend: 6/7
- Testing: 4/7
- Documentation: 4/5
- Observability: 2/5
- DevOps: 3/5
- API Design: 2/5
- Compliance: 2/5
- i18n: 2/3
- Ecosystem: 2/3
- Advanced Security: 1/5
- Modern Standards: 4/5
- Block Editor: 4/5
- Ecosystem Integration: 1/3
- Enterprise Features: 1/3
- Future-Proofing: 2/3
- AI/ML: 0/2
- Tooling: 1/2
- Infrastructure: 1/2
- Business: 0/2

---

## Detailed Findings (by dimension)

### SECURITY

[HIGH] [S1.1] Product payload not sanitized
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:40
Issue: Passing raw JSON params into service.
Impact: Stored XSS + data integrity risk.
Fix: Sanitize + validate each field (title, slug, description via `wp_kses_post`, URLs via `esc_url_raw`, numbers via `floatval` + bounds).
Effort: Medium
Priority: Must-fix

[HIGH] [S2.4] REST leaks raw exception messages
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:43
Issue: Returns `$e->getMessage()`.
Impact: Information disclosure.
Fix: Return `WP_Error` with generic message; log exception server-side.
Effort: Low
Priority: Must-fix

[HIGH] [S3.5] Public REST endpoint has no validation and allows oversized requests
File: wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:19
Issue: `__return_true` for GET.
Impact: Scraping/perf abuse.
Fix: Route args validation + cap per_page, add caching.
Effort: Medium
Priority: Must-fix

[MEDIUM] [S3.3] Admin permissions are hardcoded to manage_options
File: wp-content/plugins/affiliate-product-showcase/src/Rest/RestController.php:12
Issue: Uses `current_user_can( 'manage_options' )`.
Impact: Too broad and not domain-specific; hard to delegate.
Fix: Define plugin-specific capabilities (e.g. `manage_aps_products`) and use those.
Effort: Medium
Priority: Should-fix

[MEDIUM] [S6.1] Settings page relies on Settings API but lacks explicit nonce/cap checks in render
File: wp-content/plugins/affiliate-product-showcase/src/Admin/Admin.php:25–49
Issue: Menu is capability-gated, but render path doesn’t re-check capability.
Impact: Low; belt-and-suspenders missing.
Fix: `current_user_can( Constants::MENU_CAP )` guard in render.
Effort: Low
Priority: Nice-to-have

[HIGH] [S5.2] Uninstall defaults to destructive behavior
File: wp-content/plugins/affiliate-product-showcase/uninstall.php:18
Issue: Removes all data by default.
Impact: Data loss.
Fix: Default to false; require opt-in.
Effort: Low
Priority: Must-fix

[MEDIUM] [S5.4] Uninstall uses best-effort deletion with suppressed errors
File: wp-content/plugins/affiliate-product-showcase/uninstall.php:23–24
Issue: `@set_time_limit`, `@ini_set`, plus `@unlink/@rmdir`.
Impact: Hard to audit; hides operational failures.
Fix: Avoid error suppression; log failures (debug-only) and rely on WP_Filesystem.
Effort: Medium
Priority: Should-fix

### PERFORMANCE

[MEDIUM] [P1.5] Public API/blocks/shortcodes can request large per_page values
Files:
- wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php:32
- wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php:34
- wp-content/plugins/affiliate-product-showcase/src/Public/Shortcodes.php:27
Issue: No upper bounds.
Impact: Heavy queries; cache pressure.
Fix: Enforce max per_page; optionally add pagination.
Effort: Medium
Priority: Should-fix

[MEDIUM] [P2.4] Cache helper doesn’t use `$found` and can stampede
File: wp-content/plugins/affiliate-product-showcase/src/Cache/Cache.php:11–36
Issue: `false` treated as miss.
Impact: Avoidable recompute; stampede risk.
Fix: Use `$found` param; add lightweight lock.
Effort: Medium
Priority: Should-fix

[MEDIUM] [P4.2] Options autoload not consistently controlled
Files:
- wp-content/plugins/affiliate-product-showcase/src/Plugin/Activator.php:8
- wp-content/plugins/affiliate-product-showcase/src/Repositories/SettingsRepository.php:53
Issue: `update_option()` without explicit autoload policy.
Impact: Potential autoload bloat.
Fix: Ensure options intended for runtime use are small and set autoload explicitly (WP supports third param).
Effort: Low
Priority: Should-fix

[LOW] [P3.2] Scripts always depend on wp-element
File: wp-content/plugins/affiliate-product-showcase/src/Assets/Assets.php:14–27
Issue: Frontend scripts may not require wp-element.
Impact: Extra bytes/deps.
Fix: Only include deps that are required by bundles.
Effort: Low
Priority: Nice-to-have

### ARCHITECTURE

[HIGH] [A2.1/A3.2] DI container is inconsistent with actual constructors
File: wp-content/plugins/affiliate-product-showcase/src/DependencyInjection/CoreServiceProvider.php:41–113
Issue: Mis-wired services (dead code).
Impact: Architecture is not auditable; future refactors risky.
Fix: Either remove DI layer now, or align constructors + composition root.
Effort: High
Priority: Must-fix

[MEDIUM] [A3.1] Services instantiate dependencies internally (hard to test)
Files:
- wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php:17–25
- wp-content/plugins/affiliate-product-showcase/src/Public/Public_.php:13–18
Issue: Uses `new ...` inside constructors.
Impact: Tight coupling; difficult unit testing.
Fix: Inject repositories/services via constructors; build in Plugin/Loader.
Effort: Medium
Priority: Should-fix

[MEDIUM] [A5.5] Singleton pattern used broadly
File: wp-content/plugins/affiliate-product-showcase/src/Plugin/Plugin.php:1–84
Issue: SingletonTrait + static instance encourages global state.
Impact: Reduced testability and hidden lifecycle.
Fix: Prefer explicit bootstrap + container; keep a minimal plugin main instance if needed.
Effort: Medium
Priority: Should-fix

[LOW] [A2.2] Composer autoload defines multiple roots not present
File: wp-content/plugins/affiliate-product-showcase/composer.json:44–52
Issue: PSR-4 maps `app/`, `domain/`, `infrastructure/`, `shared/` but those dirs may be empty.
Impact: Noise; confusing to reviewers.
Fix: Remove unused roots until they exist.
Effort: Low
Priority: Nice-to-have

### CODE QUALITY

[HIGH] [Q5.4] `update_post_meta` failure detection is incorrect
File: wp-content/plugins/affiliate-product-showcase/src/Repositories/ProductRepository.php:186
Issue: Treats `false` as failure.
Impact: Functional bugs.
Fix: Accept `false` as “unchanged”; do not throw.
Effort: Low
Priority: Must-fix

[MEDIUM] [Q2.4] `declare(strict_types=1)` not consistently applied
Files: multiple (e.g., ProductService, Shortcodes, Public_, Cache, ProductsController)
Issue: Mixed strict typing.
Impact: Type safety weaker; static analysis less effective.
Fix: Add `declare(strict_types=1);` across PHP files where feasible.
Effort: Medium
Priority: Should-fix

[MEDIUM] [Q5.4] Direct `error_log()` in repository loop
File: wp-content/plugins/affiliate-product-showcase/src/Repositories/ProductRepository.php:100–108
Issue: Logs in production without control.
Impact: Log noise; possible data leak.
Fix: Use a logger with levels + debug gating.
Effort: Low
Priority: Should-fix

[MEDIUM] [S4.3/Q5.4] Database escape method uses private wpdb API
File: wp-content/plugins/affiliate-product-showcase/src/Database/Database.php:466–467
Issue: Calls `$wpdb->_escape()`.
Impact: Private API; can change; misleading safety.
Fix: Remove or replace with `esc_sql()` where appropriate; prefer `$wpdb->prepare()`.
Effort: Low
Priority: Should-fix

[LOW] [Q4.5] `aps_view()` uses `extract()`
File: wp-content/plugins/affiliate-product-showcase/src/Helpers/helpers.php:19
Issue: Variable injection into templates.
Impact: Maintainability/debuggability.
Fix: Prefer passing a single `$context` array and referencing `$context['product']` in templates.
Effort: Medium
Priority: Nice-to-have

### WORDPRESS INTEGRATION

[MEDIUM] [W3.1] REST namespace too generic
File: wp-content/plugins/affiliate-product-showcase/src/Plugin/Constants.php:95
Issue: Uses `affiliate/v1`.
Impact: Namespace collision risk with other plugins.
Fix: Use `affiliate-product-showcase/v1` or `aps/v1`.
Effort: Low
Priority: Should-fix

[MEDIUM] [W4.2] CPT uses default `capability_type => post`
File: wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php:37
Issue: No custom caps.
Impact: Contributors/editors may manage affiliate products unintentionally.
Fix: Define custom caps and `map_meta_cap`.
Effort: Medium
Priority: Should-fix

### OBSERVABILITY

[HIGH] [O4.1] Health check endpoint missing
Issue: No `/wp-json/.../health` endpoint.
Impact: Hard to monitor plugin state and dependencies.
Fix: Add a read-only health controller that checks: DB access, object cache, manifest readability, and returns a safe JSON response.
Effort: Medium
Priority: Must-fix (for enterprise targets)

[MEDIUM] [O1.1] Logging exists but is static/global
File: wp-content/plugins/affiliate-product-showcase/src/Helpers/Logger.php:1–120
Issue: Static logger + always logs at INFO/ERROR.
Impact: Hard to swap or test; can spam logs.
Fix: Make logger injectable; gate by config; ensure no PII.
Effort: Medium
Priority: Should-fix

---

## Implementation Roadmap

Phase 1 (Week 1 / 1–2 days): Security & correctness blockers
- [ ] Harden REST endpoints: schemas, sanitization, capped pagination, safe errors.
- [ ] Fix ProductRepository meta-save behavior.
- [ ] Change uninstall default to non-destructive.

Phase 2 (Week 2 / 1–2 days): Architecture alignment
- [ ] Decide: remove DI scaffolding or implement it properly end-to-end.
- [ ] Inject repositories/services (stop `new` inside core classes).
- [ ] Use plugin-specific capabilities + map_meta_cap.

Phase 3 (Week 3+): Enterprise extras
- [ ] Add health endpoint and optional structured logging.
- [ ] Improve caching semantics (`$found`, locking).
- [ ] Add tests for validator + REST route args + repository save behavior.

---

## Commands worth running (locally)
- `composer run analyze`
- `composer test`
- `npm run build`

(These are already wired in composer.json; they should be part of CI.)
