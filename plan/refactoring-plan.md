# Refactoring Plan

## Overview

This plan uses a **Balanced Hybrid** approach, alternating between PHP and CSS/SCSS work for steady progress across the entire codebase.

| Approach | Structure |
|----------|-----------|
| **Sprint 1** | Security Foundation (PHP Security + CSS Critical) |
| **Sprint 2** | CSS Foundation (CSS Architecture + PHP Performance) |
| **Sprint 3** | Performance Optimization (PHP + CSS) |
| **Sprint 4** | Polish & Automation (Accessibility + CI/CD) |

---

## Quick Checklist

### 🔴 Sprint 1: Security Foundation (CRITICAL)
| ID | Task | Type | Status |
|----|------|------|--------|
| S1.1 | PHP Security Audit | Script | ⏳ |
| S1.2 | PHP Performance Analysis | Script | ✅ |
| S1.3 | Fix N+1 Query Problems | Manual | ⏳ |
| S1.4 | Sanitize PHP Inputs | Manual | ⏳ |
| S1.5 | Escape PHP Outputs | Manual | ⏳ |
| S1.6 | CSS Code Quality Audit | Script | ✅ |
| S1.7 | Fix Critical CSS Issues | Manual | ✅ |

### 🟡 Sprint 2: CSS Foundation (IMPORTANT)
| ID | Task | Type | Status |
|----|------|------|--------|
| S2.1 | CSS Architecture Review | Script | ⏳ |
| S2.2 | Create SCSS Variable System | Manual | ⏳ |
| S2.3 | Build SCSS Mixin Library | Manual | ⏳ |
| S2.4 | Implement SCSS File Structure | Manual | ⏳ |
| S2.5 | PHP Code Quality Audit | Script | ✅ |
| S2.6 | Extract Duplicate PHP Code | Manual | ⏳ |

### 🟡 Sprint 3: Performance Optimization (IMPORTANT)
| ID | Task | Type | Status |
|----|------|------|--------|
| S3.1 | CSS Performance Analysis | Script | ✅ |
| S3.2 | CSS Performance Fixes | Manual | ✅ |
| S3.3 | Break Long PHP Functions | Manual | ⏳ |
| S3.4 | Implement PHP Error Handling | Manual | ⏳ |
| S3.5 | Browser Compatibility Audit | Script | ✅ |

### 🟢 Sprint 4: Polish & Automation (FUTURE)
| ID | Task | Type | Status |
|----|------|------|--------|
| S4.1 | CSS Accessibility Audit | Script | ✅ |
| S4.2 | Implement CSS Focus States | Manual | ⏳ |
| S4.3 | Fix Color Contrast Issues | Manual | ⏳ |
| S4.4 | Mobile Responsiveness Audit | Script | ✅ |
| S4.5 | Mobile-First Responsive Design | Manual | ⏳ |
| S4.6 | BEM Naming Convention | Manual | ⚪ |
| S4.7 | Nonce Verification | Manual | ⏳ |
| S4.8 | Capability Checks | Manual | ⏳ |
| S4.9 | Logic/Presentation Separation | Manual | ⚪ |
| S4.10 | PHPDoc Documentation | Manual | ⏳ |
| S4.11 | Build Process Automation | Auto | ⏳ |

### Summary
| Status | Count | Color |
|--------|-------|-------|
| Completed | 9 | ✅ |
| Pending | 17 | ⏳ |
| Optional | 3 | ⚪ |
| **Total** | **29** | - |

---

## Quick Reference

| Sprint | Focus | Tasks | Est. Time | Priority |
|--------|-------|-------|-----------|----------|
| **S1: Security Foundation** | PHP Security + CSS Critical | 7 | 20-28 hrs | 🔴 Critical |
| **S2: CSS Foundation** | CSS Architecture + PHP Perf | 6 | 22-32 hrs | 🟡 Important |
| **S3: Performance** | PHP + CSS Optimization | 5 | 18-26 hrs | 🟡 Important |
| **S4: Polish** | Accessibility + Automation | 11 | 55-77 hrs | 🟢 Future |
| **Total** | - | **29** | **115-163 hrs** | - |

**Legend:** 🔴 Critical | 🟡 Important | 🟢 Low Priority

---

# SPRINT 1: SECURITY FOUNDATION

**Priority:** 🔴 CRITICAL  
**Estimated Time:** 20-28 hours  
**Goal:** Secure the plugin and fix critical CSS issues

---

## S1.1: PHP Security Audit

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

---

## S1.2: PHP Performance Analysis

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

## S1.3: Fix N+1 Query Problems

**Type:** [Manual-Fix]  
**Depends On:** S1.2  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Critical Issues
| File | Line | Issue |
|------|------|-------|
| `AjaxHandler.php:313` | `wp_get_post_terms()` in loop |
| `AjaxHandler.php:510` | `get_post_meta()` in loop |
| `BulkActions.php:96` | `update_post_meta()` in loop |
| `BulkActions.php:198` | `get_posts()` in loop |
| `RibbonFields.php:200` | `get_term_meta()` in loop |

---

## S1.4: Sanitize All PHP Inputs

**Type:** [Manual-Fix]  
**Depends On:** S1.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Sanitization Functions
| Input Type | Function |
|------------|----------|
| Text fields | `sanitize_text_field()` |
| Email | `sanitize_email()` |
| URLs | `esc_url_raw()`, `sanitize_url()` |
| Integers | `intval()`, `absint()` |
| HTML | `wp_kses_post()` |

---

## S1.5: Escape All PHP Outputs

**Type:** [Manual-Fix]  
**Depends On:** S1.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Escaping Functions
| Output Type | Function |
|-------------|----------|
| HTML content | `esc_html()` |
| HTML attributes | `esc_attr()` |
| URLs | `esc_url()` |
| JavaScript | `esc_js()` |

---

## S1.6: CSS Code Quality Audit

**Type:** [Script-Detect]  
**Output:** `reports/css-quality-audit.json`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. Duplicate CSS rules
2. Long CSS blocks (>50 lines)
3. Repeated values (need variables)
4. Unused CSS classes
5. Coding standard violations

---

## S1.7: Fix Critical CSS Issues

**Type:** [Manual-Fix]  
**Depends On:** S1.6  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

### Fixes Applied
- Replaced descendant selectors with child selectors
- Fixed hardcoded `#667eea` with `$aps-color-primary`
- Verified SCSS build passes

---

# SPRINT 2: CSS FOUNDATION

**Priority:** 🟡 IMPORTANT  
**Estimated Time:** 22-32 hours  
**Goal:** Establish CSS architecture and PHP code quality

---

## S2.1: CSS Architecture Review

**Type:** [Script-Detect]  
**Output:** `reports/css-architecture-report.md`  
**Time:** 2-3 hours  
**Status:** ⏳ PENDING

### Detection Scope
1. File organization review
2. Naming convention compliance (BEM)
3. Modularity and dependencies
4. Best practices audit

---

## S2.2: Create SCSS Variable System

**Type:** [Manual-Fix]  
**Depends On:** S1.6, S2.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Variable Categories
```scss
_variables.scss
├── Colors (brand, semantic, neutral)
├── Spacing (xs, sm, md, lg, xl)
├── Typography (fonts, sizes, weights)
├── Shadows, Border radius, Z-index
└── CSS custom properties for runtime theming
```

---

## S2.3: Build SCSS Mixin Library

**Type:** [Manual-Fix]  
**Depends On:** S2.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Mixin Categories
- **Layout:** Flexbox, Grid, Container queries
- **Responsive:** Breakpoint system, Mobile-first
- **Typography:** Text truncation, Line clamping
- **Utility:** Focus styles, Visually hidden, Clearfix

---

## S2.4: Implement SCSS File Structure

**Type:** [Manual-Fix]  
**Depends On:** S2.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Structure
```
assets/scss/
├── main.scss
├── _variables.scss
├── _mixins.scss
├── _functions.scss
├── base/
├── components/
├── layouts/
├── pages/
└── themes/
```

---

## S2.5: PHP Code Quality Audit

**Type:** [Script-Detect]  
**Output:** `reports/php-quality-audit.json`  
**Time:** 3-4 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. Duplicate code
2. Long functions (>50 lines)
3. Unused code
4. Naming issues
5. Missing documentation

---

## S2.6: Extract Duplicate PHP Code

**Type:** [Manual-Fix]  
**Depends On:** S2.5  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Approach
- Create utility classes for common operations
- Create helper functions for repeated patterns
- Use traits for shared functionality

---

# SPRINT 3: PERFORMANCE OPTIMIZATION

**Priority:** 🟡 IMPORTANT  
**Estimated Time:** 18-26 hours  
**Goal:** Optimize both PHP and CSS performance

---

## S3.1: CSS Performance Analysis

**Type:** [Script-Detect]  
**Output:** `reports/css-performance-analysis-report.md`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. Deep selector nesting (>3 levels)
2. Inefficient selectors
3. Unused CSS in production
4. Media query optimization
5. Critical CSS opportunities

---

## S3.2: CSS Performance Fixes

**Type:** [Manual-Fix]  
**Depends On:** S3.1  
**Time:** 3-5 hours  
**Status:** ✅ COMPLETED

### Fixes Applied
- Created shared breakpoint mixins
- Fixed inefficient selectors in `_tags.scss`
- Consolidated duplicate breakpoints

---

## S3.3: Break Long PHP Functions

**Type:** [Manual-Fix]  
**Depends On:** S2.5  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Target Functions
- Functions >50 lines
- Cyclomatic complexity >10
- More than 5 parameters

---

## S3.4: Implement PHP Error Handling

**Type:** [Manual-Fix]  
**Depends On:** S2.5  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Error-Prone Operations
- Database queries
- File operations
- API calls
- External requests

---

## S3.5: Browser Compatibility Audit

**Type:** [Script-Detect]  
**Output:** `reports/browser-compatibility-audit-report.md`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. CSS compatibility (vendor prefixes, fallbacks)
2. JavaScript ES6+ features
3. Configuration check (autoprefixer, babel)

---

# SPRINT 4: POLISH & AUTOMATION

**Priority:** 🟢 FUTURE  
**Estimated Time:** 55-77 hours  
**Goal:** Accessibility, BEM naming, and CI/CD automation

---

## S4.1: CSS Accessibility Audit

**Type:** [Script-Detect]  
**Output:** `reports/css-accessibility-audit.json`  
**Time:** 1-2 hours  
**Status:** ✅ COMPLETED

### Detection Scope
1. Missing focus states
2. Color contrast violations
3. Text resize issues
4. Hidden content issues

---

## S4.2: Implement CSS Focus States

**Type:** [Manual-Fix]  
**Depends On:** S4.1  
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

## S4.3: Fix Color Contrast Issues

**Type:** [Manual-Fix]  
**Depends On:** S4.1  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### WCAG Requirements
- Normal text: **4.5:1** contrast ratio
- Large text: **3:1** contrast ratio

---

## S4.4: Mobile Responsiveness Audit

**Type:** [Script-Detect]  
**Output:** `reports/mobile-responsiveness-audit.json`  
**Time:** 2-3 hours  
**Status:** ✅ COMPLETED

---

## S4.5: Implement Mobile-First Responsive Design

**Type:** [Manual-Fix]  
**Depends On:** S4.4  
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

## S4.6: Implement BEM Naming Convention

**Type:** [Manual-Fix]  
**Depends On:** S2.1  
**Time:** 8-12 hours  
**Status:** ⚪ OPTIONAL

### Naming Pattern
- **Block:** `.product-card`
- **Element:** `.product-card__title`
- **Modifier:** `.product-card--featured`

---

## S4.7: Implement Nonce Verification

**Type:** [Manual-Fix]  
**Depends On:** S1.1  
**Time:** 3-4 hours  
**Status:** ⏳ PENDING

### Implementation Pattern
```php
// Add to forms
wp_nonce_field('save_product_action', 'product_nonce');

// Verify in handlers
check_ajax_referer('save_product_action', 'nonce');
```

---

## S4.8: Add Capability Checks

**Type:** [Manual-Fix]  
**Depends On:** S1.1  
**Time:** 3-4 hours  
**Status:** ⏳ PENDING

### Capability Matrix
| Operation | Capability |
|-----------|------------|
| Delete products | `delete_posts` |
| Edit products | `edit_posts` |
| Manage settings | `manage_options` |

---

## S4.9: Separate Logic from Presentation

**Type:** [Manual-Fix]  
**Depends On:** S2.5  
**Time:** 12-16 hours  
**Status:** ⚪ OPTIONAL

### Architecture Pattern
```
Service Layer (Business Logic)
├── ProductService

Repository Layer (Data Access)
├── ProductRepository

View Layer (Presentation)
├── Templates
```

---

## S4.10: Add PHPDoc Documentation

**Type:** [Manual-Fix]  
**Depends On:** S2.5  
**Time:** 6-8 hours  
**Status:** ⏳ PENDING

### Required Tags
- `@param` for all parameters
- `@return` for return values
- `@throws` for exceptions

---

## S4.11: Configure Build Process Automation

**Type:** [Automated]  
**Time:** 4-6 hours  
**Status:** ⏳ PENDING

### Tasks
- Configure Vite CSS minification
- Add autoprefixer
- Configure PurgeCSS
- Enable Gzip/Brotli compression

---

# SUMMARY

## Sprint Breakdown

| Sprint | PHP Tasks | CSS Tasks | Total Time | Deliverable |
|--------|-----------|-----------|------------|-------------|
| **S1: Security** | 4 | 3 | 20-28 hrs | Secure plugin |
| **S2: Foundation** | 2 | 4 | 22-32 hrs | CSS architecture |
| **S3: Performance** | 3 | 2 | 18-26 hrs | Optimized code |
| **S4: Polish** | 5 | 6 | 55-77 hrs | Production ready |
| **Total** | **14** | **15** | **115-163 hrs** | - |

## Recommended Execution

### Phase 1: S1 (Security) - MUST DO
```
S1.2 PHP Performance Analysis ✅
S1.1 PHP Security Audit ⏳
S1.3 Fix N+1 Queries ⏳
S1.4 Sanitize Inputs ⏳
S1.5 Escape Outputs ⏳
S1.6 CSS Quality Audit ✅
S1.7 Critical CSS Fixes ✅
```
**Result:** Secure plugin with working CSS

### Phase 2: S2 (Foundation) - SHOULD DO
```
S1.6 CSS Quality Audit ✅
S2.1 CSS Architecture ⏳
S2.2 SCSS Variables ⏳
S2.3 SCSS Mixins ⏳
S2.4 File Structure ⏳
S2.5 PHP Quality Audit ✅
S2.6 Extract Duplicate Code ⏳
```
**Result:** Maintainable codebase

### Phase 3: S3 + S4 (Polish) - DO AS NEEDED
- Pick tasks based on priorities
- Skip optional items (BEM, Architecture separation)
- Focus on accessibility and automation

## Decision Points

| Checkpoint | Decision |
|------------|----------|
| After S1 | Plugin secure? Continue to S2 |
| After S2 | CSS maintainable? Continue to S3 |
| After S3 | Performance acceptable? Continue to S4 or stop |

---

**Last Updated:** 2026-02-02  
**Total Tasks:** 29 across 4 sprints  
**Minimum Viable:** S1 only (7 tasks, 20-28 hrs)  
**Recommended:** S1 + S2 (13 tasks, 42-60 hrs)
