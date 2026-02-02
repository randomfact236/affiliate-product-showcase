# Refactoring Plan

## Overview

This plan is organized into **4 milestones** based on priority and risk:
- **Milestone 1**: Security & Critical Performance (MUST DO)
- **Milestone 2**: Accessibility & UX (SHOULD DO)
- **Milestone 3**: Code Quality & Maintainability (NICE TO HAVE)
- **Milestone 4**: Architecture & Automation (FUTURE)

---

## Quick Reference: Task Status

| Milestone | Status | Tasks | Est. Time |
|-----------|--------|-------|-----------|
| **M1: Security & Performance** | 🔴 In Progress | 5 tasks | 25-35 hrs |
| **M2: Accessibility & UX** | 🟡 Pending | 4 tasks | 18-26 hrs |
| **M3: Code Quality** | ⚪ Pending | 5 tasks | 35-47 hrs |
| **M4: Architecture & Automation** | ⚪ Future | 13 tasks | 59-83 hrs |

**Legend:** 🔴 Critical | 🟡 Important | 🟢 Low Priority | ⚪ Future

---

# MILESTONE 1: SECURITY & CRITICAL PERFORMANCE

**Priority:** 🔴 CRITICAL - Do First  
**Estimated Time:** 25-35 hours  
**Goal:** Fix vulnerabilities and database performance issues

---

## M1.1: PHP Security Audit

**Type:** [Script-Detect]  
**Output:** `reports/php-security-audit.json`  
**Time:** 2-3 hours  
**Status:** ⏳ PENDING

### Detection Scope
1. **Input Sanitization** - Unsanitized `$_POST`, `$_GET`, `$_REQUEST`
2. **Output Escaping** - Unescaped `echo`/`print` statements
3. **Nonce Verification** - Missing `check_ajax_referer()`
4. **Capability Checks** - Missing `current_user_can()`
5. **SQL Injection** - Unprepared `$wpdb` queries

### Output Format
```json
{
  "summary": { "critical": 0, "high": 0, "medium": 0, "low": 0 },
  "input_sanitization": [],
  "output_escaping": [],
  "nonce_verification": [],
  "capability_checks": [],
  "sql_injection": []
}
```

---

## M1.2: Fix N+1 Query Problems

**Type:** [Manual-Fix]  
**Depends On:** M1.3 (PHP Performance Analysis)  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Critical Issues to Fix
| File | Line | Issue |
|------|------|-------|
| `AjaxHandler.php:313` | `wp_get_post_terms()` in loop |
| `AjaxHandler.php:510` | `get_post_meta()` in loop |
| `BulkActions.php:96` | `update_post_meta()` in loop |
| `BulkActions.php:198` | `get_posts()` in loop |
| `RibbonFields.php:200` | `get_term_meta()` in loop |

### Implementation Steps
1. Cache query results before loops
2. Use `wp_cache_get()` / `wp_cache_set()`
3. Implement batch queries with `WP_Query`
4. Test performance before/after

---

## M1.3: PHP Performance Analysis

**Type:** [Script-Detect]  
**Output:** `reports/php-performance-analysis.json`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. **N+1 Query Problems** - Queries inside loops
2. **Inefficient Loops** - Operations that can be moved outside
3. **Missing Caching** - `WP_Query` without transients
4. **Memory Leaks** - Large arrays, unnecessary retention

---

## M1.4: Sanitize All PHP Inputs

**Type:** [Manual-Fix]  
**Depends On:** M1.1 (Security Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Sanitization Functions
| Input Type | Function |
|------------|----------|
| Text fields | `sanitize_text_field()` |
| Email | `sanitize_email()` |
| URLs | `esc_url_raw()`, `sanitize_url()` |
| Integers | `intval()`, `absint()` |
| HTML | `wp_kses_post()` |
| Slugs | `sanitize_title()` |
| File names | `sanitize_file_name()` |

---

## M1.5: Escape All PHP Outputs

**Type:** [Manual-Fix]  
**Depends On:** M1.1 (Security Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Escaping Functions
| Output Type | Function |
|-------------|----------|
| HTML content | `esc_html()` |
| HTML attributes | `esc_attr()` |
| URLs | `esc_url()` |
| JavaScript | `esc_js()` |
| Translation | `esc_html__()`, `esc_attr__()` |

---

## M1.6: Implement Nonce Verification

**Type:** [Manual-Fix]  
**Depends On:** M1.1 (Security Audit)  
**Time:** 3-4 hours  
**Status:** ⏳ PENDING

### Implementation Pattern
```php
// Add to forms
wp_nonce_field('save_product_action', 'product_nonce');

// Verify in handlers
if (!wp_verify_nonce($_POST['product_nonce'], 'save_product_action')) {
    wp_die('Security check failed');
}

// For AJAX
check_ajax_referer('save_product_action', 'nonce');
```

---

## M1.7: Add Capability Checks

**Type:** [Manual-Fix]  
**Depends On:** M1.1 (Security Audit)  
**Time:** 3-4 hours  
**Status:** ⏳ PENDING

### Capability Matrix
| Operation | Capability |
|-----------|------------|
| Delete products | `delete_posts` |
| Edit products | `edit_posts` |
| Publish products | `publish_posts` |
| Manage settings | `manage_options` |

---

# MILESTONE 2: ACCESSIBILITY & UX

**Priority:** 🟡 IMPORTANT - Do After M1  
**Estimated Time:** 18-26 hours  
**Goal:** WCAG AA compliance and keyboard navigation

---

## M2.1: CSS Accessibility Audit

**Type:** [Script-Detect]  
**Output:** `reports/css-accessibility-audit.json`  
**Time:** 1-2 hours  
**Status:** ✅ COMPLETED

---

## M2.2: Implement CSS Focus States

**Type:** [Manual-Fix]  
**Depends On:** M2.1 (Accessibility Audit)  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Implementation Pattern
```scss
:focus-visible {
  outline: 2px solid $aps-color-primary;
  outline-offset: 2px;
}

:focus:not(:focus-visible) {
  outline: none;
}
```

---

## M2.3: Fix Color Contrast Issues

**Type:** [Manual-Fix]  
**Depends On:** M2.1 (Accessibility Audit)  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### WCAG Requirements
- Normal text: **4.5:1** contrast ratio
- Large text: **3:1** contrast ratio
- UI components: **3:1** contrast ratio

---

## M2.4: Mobile Responsiveness Audit

**Type:** [Script-Detect]  
**Output:** `reports/mobile-responsiveness-audit.json`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

---

## M2.5: Browser Compatibility Audit

**Type:** [Script-Detect]  
**Output:** `reports/browser-compatibility-audit-report.md`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

---

## M2.6: Implement Mobile-First Responsive Design

**Type:** [Manual-Fix]  
**Depends On:** M2.4 (Responsiveness Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Breakpoint System
```scss
$breakpoints: (
  'sm': 576px,
  'md': 768px,
  'lg': 992px,
  'xl': 1200px,
  'xxl': 1400px
);
```

---

# MILESTONE 3: CODE QUALITY

**Priority:** 🟢 LOW - Do After M1 & M2  
**Estimated Time:** 35-47 hours  
**Goal:** Maintainable, documented code

---

## M3.1: PHP Code Quality Audit

**Type:** [Script-Detect]  
**Output:** `reports/php-quality-audit.json`  
**Time:** 3-4 hours  
**Status:** ✅ COMPLETED

---

## M3.2: Extract Duplicate Code

**Type:** [Manual-Fix]  
**Depends On:** M3.1 (Quality Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Approach
- Create utility classes for common operations
- Create helper functions for repeated patterns
- Use traits for shared functionality

---

## M3.3: Break Long Functions

**Type:** [Manual-Fix]  
**Depends On:** M3.1 (Quality Audit)  
**Time:** 8-12 hours  
**Status:** ⏳ PENDING

### Target Functions
- Functions >50 lines
- Cyclomatic complexity >10
- More than 5 parameters

---

## M3.4: Implement Error Handling

**Type:** [Manual-Fix]  
**Depends On:** M3.1 (Quality Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Error-Prone Operations
- Database queries
- File operations
- API calls
- External requests

---

## M3.5: Add PHPDoc Documentation

**Type:** [Manual-Fix]  
**Depends On:** M3.1 (Quality Audit)  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Required Tags
- `@param` for all parameters
- `@return` for return values
- `@throws` for exceptions
- `@since` for versioning

---

## M3.6: Separate Logic from Presentation

**Type:** [Manual-Fix]  
**Depends On:** M3.1 (Quality Audit)  
**Time:** 12-16 hours  
**Status:** ⚪ OPTIONAL

### Architecture Pattern
```
Service Layer (Business Logic)
├── ProductService
├── OrderService

Repository Layer (Data Access)
├── ProductRepository

View Layer (Presentation)
├── Templates
```

---

# MILESTONE 4: ARCHITECTURE & AUTOMATION

**Priority:** ⚪ FUTURE - Do Last or Skip  
**Estimated Time:** 59-83 hours  
**Goal:** Modern architecture and CI/CD

---

## M4.1: CSS Code Quality Audit

**Type:** [Script-Detect]  
**Status:** ✅ COMPLETED

---

## M4.2: CSS Performance Analysis

**Type:** [Script-Detect]  
**Status:** ✅ COMPLETED

---

## M4.3: CSS Architecture Review

**Type:** [Script-Detect]  
**Output:** `reports/css-architecture-report.md`  
**Time:** 2-3 hours  
**Status:** ⏳ PENDING

---

## M4.4: Implement BEM Naming Convention

**Type:** [Manual-Fix]  
**Depends On:** M4.3 (Architecture Review)  
**Time:** 8-12 hours  
**Status:** ⚪ OPTIONAL

### Naming Pattern
- **Block:** `.product-card`
- **Element:** `.product-card__title`
- **Modifier:** `.product-card--featured`

---

## M4.5: Create SCSS Variable System

**Type:** [Manual-Fix]  
**Depends On:** M4.1 (Quality Audit)  
**Time:** 4-6 hours  
**Status:** ⚪ OPTIONAL

### Variable Categories
- Colors (brand, semantic, neutral)
- Spacing (xs, sm, md, lg, xl)
- Typography (fonts, sizes, weights)
- Shadows, Border radius, Z-index

---

## M4.6: Build SCSS Mixin Library

**Type:** [Manual-Fix]  
**Depends On:** M4.2 (Performance Analysis)  
**Time:** 6-8 hours  
**Status:** ⚪ OPTIONAL

### Mixin Categories
- Layout (flexbox, grid)
- Responsive (breakpoints)
- Typography (truncation)
- Utility (focus, visually-hidden)

---

## M4.7: Implement SCSS File Structure

**Type:** [Manual-Fix]  
**Depends On:** M4.3 (Architecture Review)  
**Time:** 6-8 hours  
**Status:** ⚪ OPTIONAL

### Structure
```
assets/scss/
├── main.scss
├── _variables.scss
├── _mixins.scss
├── base/
├── components/
├── layouts/
├── pages/
└── themes/
```

---

## M4.8: Configure Build Process Automation

**Type:** [Automated]  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Tasks
- Configure Vite CSS minification
- Add autoprefixer
- Configure PurgeCSS
- Enable Gzip/Brotli compression

---

## M4.9: Implement Pre-Commit Hooks

**Type:** [Automated]  
**Time:** 3-4 hours  
**Status:** ⏳ PENDING

### Tools
- Husky for git hooks
- lint-staged for staged files
- Stylelint for SCSS
- PHP-CS-Fixer for PHP

---

## M4.10: Set Up CI/CD Quality Gates

**Type:** [Automated]  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Pipeline Steps
- PHPStan analysis
- Psalm security scan
- PHPCS code style
- PHPUnit tests

---

# SUMMARY

## Milestone Breakdown

| Milestone | Tasks | Time | Priority | Risk |
|-----------|-------|------|----------|------|
| **M1: Security & Performance** | 7 | 25-35 hrs | 🔴 Critical | Low |
| **M2: Accessibility & UX** | 6 | 18-26 hrs | 🟡 Important | Low |
| **M3: Code Quality** | 6 | 35-47 hrs | 🟢 Low | Medium |
| **M4: Architecture** | 10 | 59-83 hrs | ⚪ Future | High |
| **Total** | **29** | **137-191 hrs** | - | - |

## Recommended Execution Order

```
Phase 1: M1 ONLY (Security & Performance)
├── M1.3 PHP Performance Analysis ✅
├── M1.1 PHP Security Audit ⏳
├── M1.2 Fix N+1 Queries ⏳
├── M1.4 Sanitize Inputs ⏳
├── M1.5 Escape Outputs ⏳
├── M1.6 Nonce Verification ⏳
└── M1.7 Capability Checks ⏳
        ↓
Phase 2: M2 (Accessibility)
├── M2.2 Focus States ⏳
├── M2.3 Color Contrast ⏳
└── M2.6 Mobile-First ⏳
        ↓
Phase 3: M3 (Code Quality)
└── Pick 2-3 tasks based on needs
        ↓
Phase 4: M4 (Future)
└── Skip or do incrementally
```

## Decision Points

| Checkpoint | Decision |
|------------|----------|
| After M1 | Is the plugin secure? If yes, continue to M2 |
| After M2 | Is it accessible? If yes, evaluate M3 needs |
| After M3 | Is code quality acceptable? If yes, stop or continue to M4 |

---

**Last Updated:** 2026-02-02  
**Total Tasks:** 29 across 4 milestones  
**Minimum Viable:** M1 only (7 tasks, 25-35 hrs)
