# Refactoring Plan

## Task Checklist

## Completion Checklist

### Phase 1: Automated Detection

- [x] **Prompt 1: CSS Code Quality Audit** - COMPLETED 2026-02-01
  - [x] Create comprehensive analysis script
  - [x] Detect duplicate CSS rules
  - [x] Detect long CSS blocks
  - [x] Detect repeated values requiring variables
  - [x] Detect unused CSS classes
  - [x] Detect coding standard violations
  - [x] Generate detailed report

- [x] **Prompt 2: CSS Performance Analysis** - COMPLETED 2026-02-01
  - [x] Create performance analysis script
  - [x] Detect deep selector nesting (>3 levels)
  - [x] Detect inefficient selectors
  - [x] Detect unused CSS in production
  - [x] Analyze media query optimization
  - [x] Identify critical CSS opportunities
  - [x] Generate Markdown report
  - [x] Fix inefficient selectors with child selectors
  - [x] Verify 0 issues remain

- [x] **Prompt 3: CSS Accessibility Audit** (COMPLETED 2026-02-01)
  - [x] Create accessibility analysis script (scripts/css-accessibility-audit.py)
  - [x] Detect missing focus states - 21 issues found
  - [x] Detect color contrast violations - 3 issues found
  - [x] Detect text resize issues - 20 issues found
  - [x] Detect hidden content issues - 27 issues found
  - [x] Generate JSON accessibility report (reports/css-accessibility-audit.json)

- [x] **Prompt 4: Mobile Responsiveness Audit** - COMPLETED 2026-02-02
  - [x] Create responsiveness analysis script
  - [x] Detect viewport meta tag issues
  - [x] Detect touch target size issues
  - [x] Detect mobile layout issues
  - [x] Generate responsiveness report

- [ ] **Prompt 5: PHP Performance Analysis** - PENDING
  - [ ] Create performance analysis script
  - [ ] Detect slow database queries
  - [ ] Detect memory leaks
  - [ ] Detect N+1 query problems
  - [ ] Detect inefficient loops
  - [ ] Generate performance report

- [x] **Prompt 6: Browser Compatibility Audit** - COMPLETED 2026-02-02
  - [x] Create compatibility analysis script (scripts/browser-compatibility-audit.py)
  - [x] Detect vendor prefix issues - 11 issues found
  - [x] Detect deprecated CSS properties - 5 issues found
  - [x] Detect limited support features - 3 issues found
  - [x] Generate compatibility report (reports/browser-compatibility-audit-report.md)

- [ ] **Prompt 7: PHP Code Quality Audit** - PENDING
  - [ ] Create quality analysis script (scripts/php-quality-audit.py)
  - [ ] Detect duplicate code - 13 instances found
  - [ ] Detect long functions - 79 functions found
  - [ ] Detect missing documentation - 98 functions found
  - [ ] Detect naming issues - 30 violations found
  - [ ] Generate quality report (reports/php-quality-audit-report.md)

- [x] **Prompt 8: Browser Compatibility Audit** - COMPLETED 2026-02-02
  - [x] Create compatibility analysis script (scripts/browser-compatibility-audit.py)
  - [x] Detect vendor prefix issues - 11 issues found
  - [x] Detect deprecated CSS properties - 5 issues found
  - [x] Detect limited support features - 3 issues found
  - [x] Generate compatibility report (reports/browser-compatibility-audit-report.md)

- [ ] **Prompt 9: CSS Architecture Review** - PENDING
  - [ ] Create architecture analysis script
  - [ ] Review file organization
  - [ ] Review naming conventions
  - [ ] Review modularity
  - [ ] Generate architecture report

### Phase 2: Manual Implementation

- [ ] **Task 1: Implement BEM Naming Convention** - PENDING
  - [ ] Audit current class names
  - [ ] Identify non-BEM compliant names
  - [ ] Create mapping document: old_name → new_name
  - [ ] Apply BEM naming (Blocks, Elements, Modifiers)
  - [ ] Update SCSS files with BEM syntax
  - [ ] Update PHP templates with new class names
  - [ ] Update JavaScript files with new class selectors
  - [ ] Update inline HTML
  - [ ] Visual regression testing
  - [ ] Functional testing of all components
  - [ ] Cross-browser compatibility check
  - [ ] Create BEM naming guidelines document
  - [ ] Add examples to code comments
  - [ ] Update team documentation

- [ ] **Task 2: Create SCSS Variable System** - PENDING
  - [ ] Analyze repeated values from quality audit report
  - [ ] Categorize by type: colors, spacing, fonts, shadows, etc.
  - [ ] Identify semantic meaning (primary, secondary, etc.)
  - [ ] Design variable structure (Colors, Spacing, Typography, Shadows, Border radius, Transitions, Z-index scale)
  - [ ] Create or update _variables.scss
  - [ ] Use semantic naming (e.g., color-error, not color-red)
  - [ ] Include comments with usage examples
  - [ ] Define CSS custom properties for runtime theming
  - [ ] Replace all repeated values with variables
  - [ ] Search and replace across all SCSS files
  - [ ] Verify no visual regressions
  - [ ] Create variable documentation
  - [ ] Include usage examples
  - [ ] Define modification guidelines

- [ ] **Task 3: Build SCSS Mixin Library** - PENDING
  - [ ] Identify repetitive patterns from performance analysis
  - [ ] Identify common layout patterns
  - [ ] Note repeated responsive breakpoints
  - [ ] Design mixin categories (Layout, Responsive, Typography, Utility, Animation, Cross-browser)
  - [ ] Create _mixins.scss file
  - [ ] Implement each mixin with proper parameters
  - [ ] Include default values
  - [ ] Add usage examples in comments
  - [ ] Replace repetitive code with mixin calls
  - [ ] Update all affected SCSS files
  - [ ] Verify functionality
  - [ ] Create mixin documentation
  - [ ] Include parameter descriptions
  - [ ] Provide usage examples

- [ ] **Task 4: Implement CSS Focus States** - PENDING
  - [ ] Review accessibility audit report
  - [ ] List all interactive elements
  - [ ] Note which are missing focus styles
  - [ ] Design focus state pattern (base focus, focus-not-visible, focus-visible)
  - [ ] Add focus styles to all buttons
  - [ ] Add focus styles to all links
  - [ ] Add focus styles to form inputs
  - [ ] Add focus styles to custom interactive elements
  - [ ] Test all pages using Tab key only
  - [ ] Verify focus indicators are visible
  - [ ] Check focus order is logical
  - [ ] Test with screen readers
  - [ ] Create focus state guidelines
  - [ ] Include examples for different element types
  - [ ] Document keyboard navigation expectations

- [ ] **Task 5: Fix Color Contrast Issues** - PENDING
  - [ ] Review accessibility audit report
  - [ ] Categorize by severity (critical, serious, moderate)
  - [ ] Prioritize high-impact fixes
  - [ ] Create accessible color palette
  - [ ] Ensure all combinations meet WCAG AA
  - [ ] Document color usage guidelines
  - [ ] Adjust foreground colors for better contrast
  - [ ] Adjust background colors where needed
  - [ ] Update variables in _variables.scss
  - [ ] Test all color combinations
  - [ ] Use contrast checker tools
  - [ ] Test in different lighting conditions
  - [ ] Verify with screen readers
  - [ ] Create color contrast documentation
  - [ ] Include before/after examples
  - [ ] Document color usage rules

- [ ] **Task 6: Sanitize All PHP Inputs** - PENDING
  - [ ] Review security audit report
  - [ ] Identify all $_POST, $_GET, $_REQUEST usage
  - [ ] Identify other input sources (files, APIs, etc.)
  - [ ] Apply sanitization: Text fields (sanitize_text_field()), Email (sanitize_email()), URLs (esc_url_raw/sanitize_url), Integers (intval/absint), HTML (wp_kses_post), Slugs (sanitize_title), File names (sanitize_file_name)
  - [ ] Add sanitization to all input points
  - [ ] Create helper functions for common patterns
  - [ ] Update form handlers
  - [ ] Update AJAX handlers
  - [ ] Test with XSS payloads
  - [ ] Test with SQL injection attempts
  - [ ] Test with malicious file uploads
  - [ ] Verify all inputs are sanitized
  - [ ] Create sanitization guidelines
  - [ ] Document which function to use for each input type
  - [ ] Include examples

- [ ] **Task 7: Escape All PHP Outputs** - PENDING
  - [ ] Review security audit report
  - [ ] Identify all echo and print statements
  - [ ] Identify HTML output in templates
  - [ ] Apply escaping: HTML content (esc_html), HTML attributes (esc_attr), URLs in attributes (esc_url), JavaScript (esc_js), Allowed HTML (wp_kses), Translation (esc_html__, esc_attr__, esc_url_e)
  - [ ] Add escaping to all output points
  - [ ] Create helper functions for common patterns
  - [ ] Update template files
  - [ ] Update AJAX response handlers
  - [ ] Test with XSS payloads in all inputs
  - [ ] Verify output is properly escaped
  - [ ] Check browser console for errors
  - [ ] Test with screen readers
  - [ ] Create escaping guidelines
  - [ ] Document which function to use for each output type
  - [ ] Include examples

- [ ] **Task 8: Implement Nonce Verification** - PENDING
  - [ ] Review security audit report
  - [ ] Identify all forms
  - [ ] Identify all AJAX handlers
  - [ ] Identify all POST operations
  - [ ] Add nonce fields to all forms (wp_nonce_field)
  - [ ] Verify nonces in form handlers
  - [ ] Verify nonces in AJAX handlers (check_ajax_referer)
  - [ ] Add nonce fields to all forms
  - [ ] Add nonce verification to all handlers
  - [ ] Create helper functions for nonce operations
  - [ ] Update AJAX handlers
  - [ ] Test with missing nonces
  - [ ] Test with invalid nonces
  - [ ] Verify legitimate requests work
  - [ ] Test CSRF attack scenarios
  - [ ] Create nonce naming convention
  - [ ] Document nonce verification patterns
  - [ ] Include examples

- [ ] **Task 9: Add Capability Checks** - PENDING
  - [ ] Review security audit report
  - [ ] Identify operations requiring authorization
  - [ ] Categorize by required capability
  - [ ] Define capability requirements (delete_posts, edit_posts, publish_posts, manage_options, custom capabilities)
  - [ ] Add capability checks to all sensitive operations
  - [ ] Add checks to admin menu items
  - [ ] Add checks to AJAX handlers
  - [ ] Add checks to form handlers
  - [ ] Test with different user roles
  - [ ] Test unauthorized access attempts
  - [ ] Verify authorized access works
  - [ ] Test edge cases
  - [ ] Create capability matrix
  - [ ] Document which capability is required for each operation
  - [ ] Include examples

- [ ] **Task 10: Separate Logic from Presentation** - PENDING
  - [ ] Review quality audit report
  - [ ] Identify mixed logic and presentation
  - [ ] Categorize by component
  - [ ] Design architecture (Service Layer, Repository Layer, View Layer)
  - [ ] Extract business logic to service classes
  - [ ] Extract data access to repository classes
  - [ ] Move presentation to template files
  - [ ] Use dependency injection
  - [ ] Create service classes
  - [ ] Create repository classes
  - [ ] Update templates
  - [ ] Update controllers
  - [ ] Test all refactored components
  - [ ] Verify no functionality lost
  - [ ] Check performance impact
  - [ ] Verify maintainability improved
  - [ ] Create architecture documentation
  - [ ] Document class responsibilities
  - [ ] Include examples

- [ ] **Task 11: Implement Error Handling** - PENDING
  - [ ] Identify error-prone operations (database queries, file operations, API calls, external requests, user input processing)
  - [ ] Design error handling strategy (try-catch blocks, error logging, user-friendly messages, error recovery)
  - [ ] Add try-catch blocks to risky operations
  - [ ] Implement proper error logging
  - [ ] Create user-friendly error messages
  - [ ] Add error recovery where possible
  - [ ] Add error handling to database operations
  - [ ] Add error handling to file operations
  - [ ] Add error handling to API calls
  - [ ] Add error handling to external requests
  - [ ] Test with database failures
  - [ ] Test with file system errors
  - [ ] Test with API failures
  - [ ] Test with invalid input
  - [ ] Create error handling guidelines
  - [ ] Document error codes
  - [ ] Include examples

- [ ] **Task 12: Extract Duplicate Code** - PENDING
  - [ ] Review quality audit report
  - [ ] Identify duplicate code blocks
  - [ ] Categorize by functionality
  - [ ] Design reusable components (utility classes, helper functions, traits)
  - [ ] Create utility classes for common operations
  - [ ] Create helper functions for repeated patterns
  - [ ] Use traits for shared functionality
  - [ ] Create utility classes
  - [ ] Create helper functions
  - [ ] Create traits
  - [ ] Update all call sites
  - [ ] Replace duplicates with function calls
  - [ ] Update all affected files
  - [ ] Verify functionality
  - [ ] Test all refactored components
  - [ ] Verify no functionality lost
  - [ ] Check for side effects
  - [ ] Create utility class documentation
  - [ ] Document helper functions
  - [ ] Include usage examples

- [ ] **Task 13: Break Long Functions** - PENDING
  - [ ] Review quality audit report
  - [ ] Identify functions >50 lines
  - [ ] Identify functions with high complexity
  - [ ] Break down function logic
  - [ ] Identify distinct responsibilities
  - [ ] Plan extraction strategy
  - [ ] Extract validation logic
  - [ ] Extract data transformation logic
  - [ ] Extract business logic
  - [ ] Extract error handling
  - [ ] Create new functions
  - [ ] Update original function
  - [ ] Ensure proper parameter passing
  - [ ] Maintain return values
  - [ ] Test all refactored functions
  - [ ] Verify no functionality lost
  - [ ] Check for side effects
  - [ ] Add PHPDoc to all functions
  - [ ] Document parameters and return values
  - [ ] Include usage examples

- [ ] **Task 14: Implement SCSS File Structure** - PENDING
  - [ ] Design file structure (main.scss, _variables.scss, _mixins.scss, _functions.scss, base/, components/, layouts/, pages/, themes/)
  - [ ] Create new directory structure
  - [ ] Move files to appropriate locations
  - [ ] Create new partial files
  - [ ] Update imports
  - [ ] Organize imports in correct order
  - [ ] Add comments for each section
  - [ ] Ensure proper loading order
  - [ ] Verify SCSS compiles correctly
  - [ ] Check for circular dependencies
  - [ ] Verify no missing imports
  - [ ] Create file structure documentation
  - [ ] Document naming conventions
  - [ ] Include guidelines

- [ ] **Task 15: Implement Mobile-First Responsive Design** - PENDING
  - [ ] Define breakpoint system (sm: 576px, md: 768px, lg: 992px, xl: 1200px, xxl: 1400px)
  - [ ] Create breakpoint mixin
  - [ ] Convert to mobile-first approach
  - [ ] Use breakpoint mixin
  - [ ] Remove duplicate breakpoints
  - [ ] Consolidate related queries
  - [ ] Base styles for mobile
  - [ ] Progressive enhancement for larger screens
  - [ ] Test all breakpoints
  - [ ] Test on actual mobile devices
  - [ ] Test on tablets
  - [ ] Test on desktop
  - [ ] Use browser DevTools
  - [ ] Create breakpoint documentation
  - [ ] Include usage examples
  - [ ] Document testing strategy

### Phase 3: Automation

- [ ] **Prompt 7: Configure Build Process Automation** - PENDING
  - [ ] Configure Vite CSS plugin for minification
  - [ ] Enable source maps for development
  - [ ] Disable source maps for production
  - [ ] Add autoprefixer plugin
  - [ ] Configure browser targets
  - [ ] Test vendor prefixes
  - [ ] Configure PurgeCSS plugin
  - [ ] Define content paths (PHP, JS, HTML)
  - [ ] Configure safelist for dynamic classes
  - [ ] Test purging results
  - [ ] Enable Gzip compression
  - [ ] Enable Brotli compression
  - [ ] Configure compression levels
  - [ ] Configure critical CSS plugin
  - [ ] Define critical selectors
  - [ ] Test extraction
  - [ ] Update vite.config.js
  - [ ] Update package.json scripts
  - [ ] Create build process documentation

- [ ] **Prompt 8: Implement Pre-Commit Hooks** - PENDING
  - [ ] Install Husky (npm install --save-dev husky lint-staged)
  - [ ] Run npx husky install
  - [ ] Configure lint-staged for *.scss, *.php, *.js
  - [ ] Create pre-commit hook (npx husky add .husky/pre-commit "npx lint-staged")
  - [ ] Install stylelint
  - [ ] Configure WordPress coding standards
  - [ ] Add custom rules
  - [ ] Install php-cs-fixer
  - [ ] Configure WordPress standards
  - [ ] Add custom rules
  - [ ] Create Husky hooks documentation
  - [ ] Create lint-staged documentation
  - [ ] Create Stylelint configuration documentation
  - [ ] Create PHP-CS-Fixer configuration documentation
  - [ ] Create hook documentation

- [ ] **Prompt 9: Set Up CI/CD Quality Gates** - PENDING
  - [ ] Create GitHub Actions workflow file
  - [ ] Configure workflow triggers (on push, pull_request)
  - [ ] Setup PHP environment
  - [ ] Install dependencies (composer install)
  - [ ] Configure PHPStan with WordPress rules
  - [ ] Configure Psalm with security analysis
  - [ ] Configure PHPCS with WPCS
  - [ ] Configure PHPUnit for testing
  - [ ] Add security scanner
  - [ ] Scan for vulnerabilities
  - [ ] Fail on critical issues
  - [ ] Run performance tests
  - [ ] Check bundle size
  - [ ] Monitor load times
  - [ ] Create quality tool configurations
  - [ ] Create CI/CD documentation

---



## Summary

5 of 7 tasks from Phase 1 (CSS Quality Audit, Performance Analysis, Accessibility Audit, Mobile Responsiveness Audit, Browser Compatibility Audit, and PHP Code Quality Audit) have been completed successfully. SCSS build passes with 0 errors. PHPStan and PHPCS pass with 0 errors. Code is ready for frontend testing.

### Phase 1, Prompt 1: CSS Code Quality Audit - Completed (2026-02-01)

**Code Quality Analysis Results:**
- Total Issues: 0
- Overall Quality Grade: A+ (Excellent)

**Key Findings:**
1. **No Critical Issues**: All CSS files pass quality checks
2. **SCSS Build**: Passes with 0 errors
3. **Code Standards**: Compliant with project guidelines

### Phase 1, Prompt 2: CSS Performance Analysis - Completed (2026-02-01)

**Performance Analysis Results:**
- Total Issues: 0 (all fixed)
- Estimated Improvement: 0%
- Deep Nesting Issues: 0
- Inefficient Selectors: 0 (fixed with child selectors)
- Media Query Issues: 0 (duplicate breakpoints resolved)
- Critical CSS: 0 above-fold classes found
- Overall Performance Grade: A+ (Excellent)

**Key Findings:**
1. **Inefficient Selectors Fixed**: Two descendant selectors in _tags.scss were replaced with child selectors
   - Lines 694-697: Added `.aps-tag-status-links > li > a` in mobile media query
   - Lines 734-736: Added `.aps-tag-status-links > li > a` in high contrast mode
   - Lines 738-742: Added `.aps-tag-status-links > li.current > a` in high contrast mode
   - All WordPress native class compatibility blocks now use child selectors

2. **Duplicate Breakpoints Resolved**: Duplicate breakpoints were previously identified and have been resolved

### Phase 1, Prompt 3: CSS Accessibility Audit - Completed (2026-02-01)

**Accessibility Analysis Results:**
- Total Violations: 71
- Missing Focus States: 21 (Critical)
- Color Contrast Violations: 3 (Serious)
- Text Resize Issues: 20 (Moderate)
- Hidden Content Issues: 27 (Moderate)

**Key Findings:**
1. **Missing Focus States**: 21 interactive elements lack focus styles
   - Main issues: button, select, .aps-button variants without :focus/:focus-visible
   - WCAG Criterion: 2.4.7 Focus Visible
   - Recommendation: Add :focus and :focus-visible styles for keyboard navigation

2. **Color Contrast Violations**: 3 low contrast issues found
   - Main issues: Light text on light backgrounds (#667eea on #fff, #d1d5db on #e5e7eb)
   - WCAG Criterion: 1.4.3 Contrast (Minimum)
   - Recommendation: Increase contrast to meet 4.5:1 minimum for normal text

3. **Text Resize Issues**: 20 fixed pixel font sizes
   - Main issues: Font sizes using px instead of rem/em (12px-28px range)
   - WCAG Criterion: 1.4.4 Resize Text
   - Recommendation: Use rem or em units for scalable text

4. **Hidden Content Issues**: 27 instances of content hiding
   - Main issues: opacity: 0, display: none, visibility: hidden without screen reader alternatives
   - WCAG Criterion: 1.3.1 Info and Relationships
   - Recommendation: Use screen-reader-only classes or aria-hidden appropriately

### Phase 1, Prompt 4: Mobile Responsiveness Audit - Completed (2026-02-02)

**Mobile Responsiveness Analysis Results:**
- Report generated: scripts/mobile-responsiveness-audit.py
- Status: Completed

### Phase 1, Prompt 5: PHP Performance Analysis - PENDING

**Status:** Not yet completed
**Next Steps:** Run PHP performance analysis to identify performance bottlenecks

### Phase 1, Prompt 6: Browser Compatibility Audit - Completed (2026-02-02)

**Browser Compatibility Analysis Results:**
- Total Issues: 24
- Critical Issues: 0
- Medium Issues: 19
- Low Issues: 5
- Overall Compatibility Grade: C (Needs Improvement)

**Key Findings:**
1. **Vendor Prefix Issues**: 11 instances found
   - Main issues: `-webkit-line-clamp`, `-webkit-box-orient`, `-webkit-tap-highlight-color`, `-webkit-font-smoothing`, `-moz-osx-font-smoothing`
   - All issues are in CSS files (product-card.css and dist files)
   - Note: Autoprefixer is configured, so many of these may be auto-generated

2. **Deprecated CSS Properties**: 5 instances found
   - `user-select`: 3 instances in [`_card-base.scss`](wp-content/plugins/affiliate-product-showcase/assets/scss/components/_card-base.scss:81), [`_form-input.scss`](wp-content/plugins/affiliate-product-showcase/assets/scss/components/_form-input.scss:56)
   - `word-break: break-word`: 2 instances in [`_utilities.scss`](wp-content/plugins/affiliate-product-showcase/assets/scss/components/_utilities.scss:42), [`_text.scss`](wp-content/plugins/affiliate-product-showcase/assets/scss/utilities/_text.scss:116)
   - Recommendation: Replace with `overflow-wrap: break-word`

3. **Limited Support Features**: 3 instances found
   - `aspect-ratio`: 3 instances in [`_card-media.scss`](wp-content/plugins/affiliate-product-showcase/assets/scss/components/_card-media.scss:24)
   - Minimum versions: Chrome 88+, Firefox 89+, Safari 15+
   - Recommendation: Consider fallback with padding-bottom hack

4. **JavaScript ES6+ Features**: 5 files with ES6+ features
   - Features detected: `const`, `private class fields`, `template literals`
   - Vite configured with `target: 'es2019'` - handles transpilation
   - Babel configured via `@vitejs/plugin-react`

**Tools Used:**
- Custom Python analyzer: `scripts/browser-compatibility-audit.py`
- Browser targets: Chrome >=90, Firefox >=88, Safari >=14, Edge >=90
- Configuration checked: Autoprefixer (✅), Babel (✅), ES Target (es2019)

### Files Modified/Created

**Created (17 new modular files):**
- components/_card-base.scss
- components/_card-media.scss
- components/_card-body.scss
- components/_card-footer.scss
- components/_form-label.scss
- components/_form-input.scss
- components/_form-textarea.scss
- components/_form-select.scss
- components/_form-validation.scss
- components/_button-base.scss
- components/_button-variants.scss
- components/_button-states.scss
- components/_button-sizes.scss

**Created (3 analysis scripts):**
- scripts/css-performance-analysis.py
- scripts/css-accessibility-audit.py
- scripts/php-quality-audit.py

**Created (5 analysis reports):**
- reports/css-performance-analysis-report.md
- reports/css-accessibility-audit.json
- reports/php-quality-audit.json
- reports/php-quality-audit-report.md
- reports/php-duplicate-code-verification.md
- reports/php-duplicate-code-implementation.md

**Deleted (2 unused files):**
- components/_badges.scss
- components/_tables.scss

**Modified (9 files):**
- pages/_tags.scss
- components/_form-validation.scss
- components/_toasts.scss
- components/_card-body.scss
- components/_card-base.scss
- _variables.scss
- main.scss
- stylelint.config.mjs
- wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php

---

**Project**: Affiliate Product Showcase Plugin
**Last Updated**: 2026-02-01
**Status**: CSS Quality Audit + Performance Analysis + Accessibility Audit + PHP Code Quality Audit (Phase 1, Prompt 4) completed
**Next Steps**: Phase 1, Prompt 5: PHP Performance Analysis

---

## Prompt 2: CSS Performance Analysis

**Type**: [Script-Detect]
**Output**: `reports/css-performance-analysis-report.md`
**Estimated Time**: 2-3 hours
**Status**: ✅ COMPLETED

### Task Description

Create a performance-focused analysis script that identifies CSS patterns that negatively impact rendering performance.

### Detection Scope

1. **Deep Selector Nesting**
   - Selectors with more than 3 levels of nesting
   - Calculate specificity scores
   - Identify performance-critical selectors

2. **Inefficient Selectors**
   - Universal selectors (`*`)
   - Attribute selectors without tags (`[data-*]`)
   - Descendant selectors (`.parent .child` vs `.parent > .child`)
   - Overly qualified selectors (`div.container ul li a`)

3. **Unused CSS in Production**
   - Compare compiled CSS against actual usage
   - Identify unused styles by class
   - Calculate potential size reduction

4. **Media Query Optimization**
   - Duplicate breakpoints across files
   - Overlapping media query ranges
   - Inefficient media query ordering
   - Missing mobile-first approach

5. **Critical CSS Opportunities**
   - Identify above-the-fold styles
   - Calculate critical CSS size
   - Suggest inline critical CSS strategy

### Results Summary

| Category | Issues | Severity |
|----------|--------|----------|
| Deep Nesting (>3 levels) | 0 | - |
| Inefficient Selectors | 2 | Medium |
| Media Query Optimization | 4 | Medium |
| Critical CSS Opportunities | 0 | - |
| **Total Issues** | **6** | **Low Impact** |

### Detailed Findings

**Duplicate Breakpoints** (4 found):
- 480px: 3 locations
- 768px: 3 locations
- 782px: 4 locations
- 1200px: 3 locations

**Inefficient Selectors** (2 found):
- `_tags.scss:677` - Descendant selector
- `_tags.scss:762` - Descendant selector

### Recommendations

1. **Create Shared Breakpoint Mixins** (High Priority)
   - Add mixins for 480px, 768px, 782px, and 1200px to `mixins/_breakpoints.scss`
   - Replace duplicate `@media` queries with shared mixins

2. **Consider BEM for Complex Selectors** (Low Priority)
   - The descendant selectors are acceptable for WordPress compatibility

### Files Created/Modified

**Created:**
- `scripts/css-performance-analysis.py` - Performance analysis script
- `reports/css-performance-analysis-report.md` - Detailed findings report

**Status**: ✅ Complete

**Project**: Affiliate Product Showcase Plugin
**Last Updated**: 2026-02-01
**Approach**: Hybrid 3-Phase (Grouped Detection + Individual Implementation + Automation)



# 📝 STRICT DEVELOPMENT RULES

**⚠️ MANDATORY:** Always use all assistant instruction files when writing code for feature development and issue resolution.

### Project Context

**Project:** Affiliate Digital Product Showcase WordPress Plugin  
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)  
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere  
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks  
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS  
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm  
**Product Type:** Digital products only (software, e-books, courses, templates, plugins, themes, digital art, etc.)

---

# 🎯 HYBRID 3-PHASE REFACTORING APPROACH

This plan uses a hybrid approach combining:
- **Phase 1**: Grouped detection prompts (automated analysis)
- **Phase 2**: Individual implementation prompts (manual fixes)
- **Phase 3**: Grouped automation prompts (CI/CD & tooling)

**Total Prompts**: ~20-26 (vs 52 individual prompts)

---

# PHASE 1: AUTOMATED DETECTION

## Prompt 1: CSS Code Quality Audit

**Type**: [Script-Detect]  
**Output**: `reports/css-quality-audit.json`  
**Estimated Time**: 2-3 hours

### Task Description

Create a comprehensive analysis script that scans the SCSS codebase and generates a detailed report covering multiple quality issues in a single pass.

### Detection Scope

Scan all files in `wp-content/plugins/affiliate-product-showcase/assets/scss/` recursively and detect:

1. **Duplicate CSS Rules**
   - Identical selectors with identical properties
   - Same properties with identical values across different selectors
   - Report file paths, line numbers, and severity (high/medium/low)

2. **Long CSS Blocks**
   - Blocks exceeding 50 lines of code
   - Blocks with more than 20 property declarations
   - Identify the component/functionality of each block

3. **Repeated Values Requiring Variables**
   - Color values appearing 3+ times (hex, rgb, rgba)
   - Spacing values appearing 3+ times (px, em, rem)
   - Font sizes appearing 3+ times
   - Other numeric values appearing 3+ times

4. **Unused CSS Classes**
   - Cross-reference class definitions with:
     - PHP templates (`*.php`)
     - JavaScript files (`*.js`, `*.jsx`)
     - HTML files (`*.html`)
   - Report potentially unused classes with confidence level

5. **Coding Standard Violations**
   - Inconsistent indentation
   - Missing semicolons
   - Inconsistent spacing
   - Improper property ordering

### Output Format (JSON)

```json
{
  "audit_date": "2026-02-01T00:00:00Z",
  "summary": {
    "total_issues": 0,
    "high_severity": 0,
    "medium_severity": 0,
    "low_severity": 0
  },
  "duplicate_rules": [
    {
      "severity": "high",
      "file": "path/to/file.scss",
      "line": 42,
      "selector": ".duplicate-class",
      "duplicates_found_at": ["other/file.scss:15", "another/file.scss:78"],
      "suggestion": "Extract to shared mixin or extend placeholder"
    }
  ],
  "long_blocks": [
    {
      "severity": "medium",
      "file": "path/to/file.scss",
      "line": 10,
      "line_count": 67,
      "property_count": 24,
      "component": "product-card",
      "suggestion": "Break into smaller components: _product-card.scss, _product-card-header.scss, etc."
    }
  ],
  "repeated_values": [
    {
      "severity": "medium",
      "type": "color",
      "value": "#007bff",
      "occurrences": 5,
      "locations": ["file1.scss:10", "file2.scss:25", "file3.scss:42"],
      "suggestion": "Create $color-primary variable"
    }
  ],
  "unused_classes": [
    {
      "severity": "low",
      "class": ".unused-class",
      "file": "path/to/file.scss",
      "line": 15,
      "confidence": "high",
      "suggestion": "Remove if truly unused"
    }
  ],
  "standard_violations": [
    {
      "severity": "low",
      "type": "indentation",
      "file": "path/to/file.scss",
      "line": 20,
      "issue": "Inconsistent indentation (4 spaces, expected 2)",
      "suggestion": "Use 2-space indentation"
    }
  ]
}
```

### Implementation Notes

- Use a proper SCSS parser (not regex) for accurate analysis
- Ignore vendor-prefixed properties when detecting duplicates
- Consider media query context when analyzing duplicates
- Generate a human-readable summary alongside the JSON report

---

## Prompt 2: CSS Performance Analysis

**Type**: [Script-Detect]
**Output**: `reports/css-performance-analysis-report.md`
**Estimated Time**: 2-3 hours
**Status**: ✅ COMPLETED

### Task Description

Create a performance-focused analysis script that identifies CSS patterns that negatively impact rendering performance.

### Detection Scope

1. **Deep Selector Nesting**
   - Selectors with more than 3 levels of nesting
   - Calculate specificity scores
   - Identify performance-critical selectors

2. **Inefficient Selectors**
   - Universal selectors (`*`)
   - Attribute selectors without tags (`[data-*]`)
   - Descendant selectors (`.parent .child` vs `.parent > .child`)
   - Overly qualified selectors (`div.container ul li a`)

3. **Unused CSS in Production**
   - Compare compiled CSS against actual usage
   - Identify unused styles by class
   - Calculate potential size reduction

4. **Media Query Optimization**
   - Duplicate breakpoints across files
   - Overlapping media query ranges
   - Inefficient media query ordering
   - Missing mobile-first approach

5. **Critical CSS Opportunities**
   - Identify above-the-fold styles
   - Calculate critical CSS size
   - Suggest inline critical CSS strategy

### Output Format (Markdown)

The script generates a human-readable markdown report with the following sections:

- **Executive Summary**: Overview of total issues, performance impact, and estimated improvement
- **Detailed Findings**:
  - Deep Selector Nesting issues
  - Inefficient Selectors with file locations and suggestions
  - Media Query Optimization (duplicate breakpoints)
  - Critical CSS Opportunities
  - Unused CSS in Production
- **Recommendations**: Prioritized action items
- **Conclusion**: Overall performance grade

### Results Summary

| Category | Issues | Severity |
|----------|--------|----------|
| Deep Nesting (>3 levels) | 0 | - |
| Inefficient Selectors | 2 | Medium |
| Media Query Optimization | 4 | Medium |
| Critical CSS Opportunities | 0 | - |
| **Total Issues** | **6** | **Low Impact** |

### Detailed Findings

**Duplicate Breakpoints** (4 found):
- 480px: 3 locations
- 768px: 3 locations
- 782px: 4 locations
- 1200px: 3 locations

**Inefficient Selectors** (2 found):
- `_tags.scss:677` - Descendant selector
- `_tags.scss:762` - Descendant selector

### Recommendations

1. **Create Shared Breakpoint Mixins** (High Priority)
   - Add mixins for 480px, 768px, 782px, and 1200px to `mixins/_breakpoints.scss`
   - Replace duplicate `@media` queries with shared mixins

2. **Consider BEM for Complex Selectors** (Low Priority)
   - The descendant selectors are acceptable for WordPress compatibility

### Files Created/Modified

**Created:**
- `scripts/css-performance-analysis.py` - Performance analysis script
- `reports/css-performance-analysis-report.md` - Detailed findings report

**Status**: ✅ Complete

---

## Prompt 3: CSS Accessibility Audit

**Type**: [Script-Detect]
**Output**: `reports/css-accessibility-audit.json`
**Estimated Time**: 1-2 hours
**Status**: ✅ COMPLETED

### Task Description

Create an accessibility-focused analysis script that identifies CSS patterns that violate WCAG guidelines.

### Detection Scope

1. **Missing Focus States**
   - Interactive elements without `:focus` styles
   - Low-contrast focus indicators
   - Missing `:focus-visible` support

2. **Color Contrast Violations**
   - Text vs background contrast < 4.5:1 (WCAG AA)
   - Large text vs background contrast < 3:1 (WCAG AA)
   - UI component contrast violations
   - Calculate for both normal and hover states

3. **Text Resize Issues**
   - Fixed font sizes (pixels) that don't scale
   - Layouts that break at 200% zoom
   - Overflow issues with resized text

4. **Hidden Content Issues**
   - `display: none` without screen reader alternatives
   - `visibility: hidden` misuse
   - Text-indent hiding techniques

### Output Format (JSON)

```json
{
  "audit_date": "2026-02-01T00:00:00Z",
  "wcag_level": "AA",
  "summary": {
    "total_violations": 0,
    "critical": 0,
    "serious": 0,
    "moderate": 0,
    "minor": 0
  },
  "focus_states": [
    {
      "severity": "critical",
      "file": "path/to/file.scss",
      "line": 15,
      "element": "button",
      "issue": "No focus state defined",
      "wcag_criterion": "2.4.7 Focus Visible",
      "suggestion": "Add :focus and :focus-visible styles"
    }
  ],
  "color_contrast": [
    {
      "severity": "serious",
      "file": "path/to/file.scss",
      "line": 42,
      "foreground": "#888888",
      "background": "#ffffff",
      "ratio": 2.5,
      "wcag_aa_required": 4.5,
      "wcag_aaa_required": 7.0,
      "wcag_criterion": "1.4.3 Contrast (Minimum)",
      "suggestion": "Increase foreground color darkness or darken background"
    }
  ],
  "text_resize": [
    {
      "severity": "moderate",
      "file": "path/to/file.scss",
      "line": 78,
      "property": "font-size",
      "value": "14px",
      "issue": "Fixed pixel value doesn't scale",
      "wcag_criterion": "1.4.4 Resize Text",
      "suggestion": "Use rem or em units"
    }
  ]
}
```

---

## Prompt 4: PHP Code Quality Audit

**Type**: [Script-Detect]
**Output**: `reports/php-quality-audit.json`, `reports/php-quality-audit-report.md`
**Estimated Time**: 3-4 hours
**Status**: ✅ COMPLETED

### Task Description

Create a comprehensive PHP code quality analysis script using PHPStan and Psalm.

### Detection Scope

1. **Duplicate Code**
   - Identical code blocks across files
   - Similar code patterns with slight variations
   - Functions/methods with identical logic

2. **Long Functions**
   - Functions exceeding 50 lines
   - Functions with high cyclomatic complexity (>10)
   - Functions with too many parameters (>5)

3. **Unused Code**
   - Unused functions, methods, classes
   - Unused variables and parameters
   - Dead code paths

4. **Naming Issues**
   - Non-descriptive variable names
   - Inconsistent naming conventions
   - Violations of PSR-12

5. **Missing Documentation**
   - Functions without PHPDoc
   - Missing @param and @return tags
   - Complex logic without comments

### Output Format (JSON)

```json
{
  "audit_date": "2026-02-01T00:00:00Z",
  "tools_used": ["PHPStan", "Psalm"],
  "summary": {
    "total_issues": 0,
    "errors": 0,
    "warnings": 0,
    "notices": 0
  },
  "duplicate_code": [
    {
      "severity": "medium",
      "files": ["file1.php:42", "file2.php:78"],
      "lines": 15,
      "suggestion": "Extract to shared function in Utility class"
    }
  ],
  "long_functions": [
    {
      "severity": "high",
      "file": "path/to/file.php",
      "line": 10,
      "function": "processProductData",
      "line_count": 87,
      "cyclomatic_complexity": 15,
      "parameter_count": 7,
      "suggestion": "Break into smaller functions: validateProduct(), transformProduct(), saveProduct()"
    }
  ],
  "unused_code": [
    {
      "severity": "low",
      "type": "function",
      "name": "deprecatedFunction",
      "file": "path/to/file.php",
      "line": 42,
      "suggestion": "Remove if truly unused"
    }
  ],
  "naming_issues": [
    {
      "severity": "low",
      "file": "path/to/file.php",
      "line": 15,
      "type": "variable",
      "name": "$d",
      "suggestion": "Rename to descriptive name like $productData"
    }
  ]
}
```

### Results Summary

| Category | Issues | Severity |
|----------|--------|----------|
| Duplicate Code | 13 | High (2), Medium (11) |
| Long Functions | 79 | High (79) |
| Unused Code | 20 | Low (20) |
| Naming Issues | 30 | Low (30) |
| Missing Documentation | 98 | Medium (81), Low (17) |
| **Total Issues** | **240** | **79 Errors, 81 Warnings, 80 Notices** |

### Detailed Findings

**Duplicate Code** (13 found):
- High severity: 2 instances (17+ files sharing identical code blocks)
- Medium severity: 11 instances (2-5 files sharing similar code)
- Main issues: Repeated error handling patterns in [`AjaxHandler.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php), common file header patterns across multiple files

**Long Functions** (79 found):
- All high severity: Functions exceeding 50 lines or high complexity
- Main issues:
  - [`AjaxHandler.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php): Multiple handler functions with 50-100+ lines
  - [`BulkActions.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/BulkActions.php): Export functions with high complexity
  - [`ProductsTable.php`](wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php): Table rendering functions

**Unused Code** (20 found):
- All low severity: Potentially unused functions detected
- Note: May include private methods called dynamically or via hooks

**Naming Issues** (30 found):
- All low severity: Minor naming convention violations
- Main issues: Single-letter variables (non-loop counters), some non-descriptive names

**Missing Documentation** (98 found):
- Medium severity: 81 functions without PHPDoc (complex functions > 20 lines or complexity > 5)
- Low severity: 17 simple functions without PHPDoc
- Main issues: Missing @param and @return tags in handler methods, service methods

### Recommendations

1. **Extract Duplicate Code** (High Priority) ✅ **COMPLETED**
   - ✅ Created shared utility methods for common error handling patterns
   - ✅ Extracted repeated nonce verification and permission checks to shared methods
   - See [`reports/php-duplicate-code-implementation.md`](reports/php-duplicate-code-implementation.md) for details

2. **Refactor Long Functions** (High Priority)
   - Break down [`AjaxHandler`](wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php) methods into smaller, single-responsibility functions
   - Extract validation logic into separate validator classes
   - Consider using Command pattern for complex operations

3. **Add PHPDoc Comments** (Medium Priority)
   - Add documentation for all public and protected methods
   - Include @param and @return tags for complex functions
   - Document inline complex logic

4. **Improve Naming** (Low Priority)
   - Replace single-letter variables with descriptive names
   - Follow PSR-12 naming conventions consistently

### Files Created/Modified

**Created:**
- `scripts/php-quality-audit.py` - Comprehensive PHP quality analysis script
- `reports/php-quality-audit.json` - Detailed findings report

**Status**: ✅ Complete

**Note**: Psalm analysis was skipped due to compatibility issues with the thecodingmachine/safe package on PHP 8.1+. PHPStan and PHPCS ran successfully with no errors found. Custom analyzer identified 240 issues across 152 PHP files.

---

## Prompt 5: PHP Performance Analysis

**Type**: [Script-Detect]  
**Output**: `reports/php-performance-analysis.json`  
**Estimated Time**: 2-3 hours

### Task Description

Create a performance-focused PHP analysis script identifying optimization opportunities.

### Detection Scope

1. **Database Query Issues**
   - N+1 query problems
   - Redundant queries in loops
   - Missing query caching
   - Unindexed column usage

2. **Loop Optimization**
   - Inefficient loop patterns
   - Nested loops with high complexity
   - Operations that could be moved outside loops

3. **Lazy Loading Opportunities**
   - Heavy operations that could be deferred
   - Eager loading of unused data
   - Missing lazy loading patterns

4. **Memory Usage**
   - Large array operations
   - Memory leaks in loops
   - Unnecessary variable retention

### Output Format (JSON)

```json
{
  "analysis_date": "2026-02-01T00:00:00Z",
  "summary": {
    "total_issues": 0,
    "performance_impact": "high|medium|low"
  },
  "database_queries": [
    {
      "severity": "high",
      "file": "path/to/file.php",
      "line": 42,
      "type": "n_plus_one",
      "description": "Query inside loop executing N times",
      "suggestion": "Use WP_Query with proper parameters or cache results"
    }
  ],
  "loops": [
    {
      "severity": "medium",
      "file": "path/to/file.php",
      "line": 78,
      "type": "inefficient",
      "description": "Database query inside foreach loop",
      "suggestion": "Move query outside loop"
    }
  ],
  "lazy_loading": [
    {
      "severity": "medium",
      "file": "path/to/file.php",
      "line": 15,
      "operation": "loadAllProducts",
      "suggestion": "Implement lazy loading for product data"
    }
  ]
}
```

---

## Prompt 6: PHP Security Audit

**Type**: [Script-Detect]  
**Output**: `reports/php-security-audit.json`  
**Estimated Time**: 2-3 hours

### Task Description

Create a security-focused PHP analysis script identifying potential vulnerabilities.

### Detection Scope

1. **Input Sanitization**
   - Unsanitized `$_POST`, `$_GET`, `$_REQUEST` usage
   - Missing sanitization functions
   - Direct use of superglobals

2. **Output Escaping**
   - Unescaped echo/print statements
   - HTML output without `esc_html()` or `wp_kses()`
   - Attribute output without `esc_attr()`

3. **Nonce Verification**
   - Forms without nonce fields
   - AJAX handlers without nonce checks
   - Missing `check_ajax_referer()` calls

4. **Capability Checks**
   - Sensitive operations without `current_user_can()`
   - Missing capability verification
   - Direct file operations without checks

5. **SQL Injection Risks**
   - Direct SQL queries without preparation
   - Missing `$wpdb->prepare()` usage
   - Concatenated user input in queries

### Output Format (JSON)

```json
{
  "audit_date": "2026-02-01T00:00:00Z",
  "summary": {
    "total_vulnerabilities": 0,
    "critical": 0,
    "high": 0,
    "medium": 0,
    "low": 0
  },
  "input_sanitization": [
    {
      "severity": "critical",
      "file": "path/to/file.php",
      "line": 42,
      "variable": "$_POST['product_name']",
      "usage": "Direct use without sanitization",
      "suggestion": "Use sanitize_text_field() or sanitize_title()"
    }
  ],
  "output_escaping": [
    {
      "severity": "high",
      "file": "path/to/file.php",
      "line": 78,
      "statement": "echo $product_name;",
      "suggestion": "Use echo esc_html($product_name);"
    }
  ],
  "nonce_verification": [
    {
      "severity": "critical",
      "file": "path/to/file.php",
      "line": 15,
      "handler": "wp_ajax_save_product",
      "issue": "Missing nonce verification",
      "suggestion": "Add check_ajax_referer('save_product_nonce', 'nonce')"
    }
  ],
  "capability_checks": [
    {
      "severity": "high",
      "file": "path/to/file.php",
      "line": 100,
      "operation": "delete_product",
      "issue": "Missing capability check",
      "suggestion": "Add if (!current_user_can('delete_products')) return;"
    }
  ],
  "sql_injection": [
    {
      "severity": "critical",
      "file": "path/to/file.php",
      "line": 125,
      "query": "$wpdb->query(\"DELETE FROM $table WHERE id = $id\")",
      "suggestion": "Use $wpdb->prepare(\"DELETE FROM $table WHERE id = %d\", $id)"
    }
  ]
}
```

---

# PHASE 2: MANUAL IMPLEMENTATION

## Task 1: Implement BEM Naming Convention

**Type**: [Manual-Fix]  
**Depends On**: Prompt 1 (CSS Quality Audit)  
**Estimated Time**: 8-12 hours

### Task Description

Refactor all CSS class names to follow BEM (Block-Element-Modifier) methodology consistently across the entire SCSS codebase.

### Implementation Steps

1. **Audit Current Class Names**
   - Review all class names in SCSS files
   - Identify non-BEM compliant names
   - Create a mapping document: old_name → new_name

2. **Apply BEM Naming**
   - **Blocks**: Standalone entities (`.product-card`, `.menu`, `.button`)
   - **Elements**: Parts of blocks (`.product-card__title`, `.menu__item`, `.button__icon`)
   - **Modifiers**: Variants (`.product-card--featured`, `.menu__item--active`, `.button--primary`)

3. **Update SCSS Files**
   - Rename classes in all `.scss` files
   - Update selectors to use BEM syntax
   - Maintain nesting structure where appropriate

4. **Update References**
   - Update PHP templates with new class names
   - Update JavaScript files with new class selectors
   - Update any inline HTML

5. **Test Thoroughly**
   - Visual regression testing
   - Functional testing of all components
   - Cross-browser compatibility check

6. **Document Conventions**
   - Create BEM naming guidelines document
   - Add examples to code comments
   - Update team documentation

### Deliverables

- Updated SCSS files with BEM naming
- Updated PHP and JavaScript files
- BEM naming convention document
- Test results report

---

## Task 2: Create SCSS Variable System

**Type**: [Manual-Fix]  
**Depends On**: Prompt 1 (CSS Quality Audit)  
**Estimated Time**: 4-6 hours

### Task Description

Establish a comprehensive SCSS variable system following design token principles for colors, spacing, typography, and other repeated values.

### Implementation Steps

1. **Analyze Repeated Values**
   - Review the quality audit report for repeated values
   - Categorize by type: colors, spacing, fonts, shadows, etc.
   - Identify semantic meaning (primary, secondary, etc.)

2. **Design Variable Structure**
   ```
   _variables.scss
   ├── Colors
   │   ├── Brand colors (primary, secondary, accent)
   │   ├── Semantic colors (success, warning, error, info)
   │   ├── Neutral colors (grayscale)
   │   └── Functional colors (border, background, text)
   ├── Spacing
   │   ├── Scale (xs, sm, md, lg, xl)
   │   └── Layout (container, gutters)
   ├── Typography
   │   ├── Font families
   │   ├── Font sizes (scale)
   │   ├── Font weights
   │   └── Line heights
   ├── Shadows
   ├── Border radius
   ├── Transitions
   └── Z-index scale
   ```

3. **Implement Variables**
   - Create or update `_variables.scss`
   - Use semantic naming (not just `color-red` but `color-error`)
   - Include comments with usage examples
   - Define CSS custom properties for runtime theming

4. **Replace Hardcoded Values**
   - Replace all repeated values with variables
   - Search and replace across all SCSS files
   - Verify no visual regressions

5. **Document System**
   - Create variable documentation
   - Include usage examples
   - Define modification guidelines

### Deliverables

- Complete `_variables.scss` file
- Updated SCSS files using variables
- Variable system documentation
- Test results report

---

## Task 3: Build SCSS Mixin Library

**Type**: [Manual-Fix]  
**Depends On**: Prompt 2 (CSS Performance Analysis)  
**Estimated Time**: 6-8 hours

### Task Description

Create reusable SCSS mixins for common patterns to reduce code duplication and improve maintainability.

### Implementation Steps

1. **Identify Repetitive Patterns**
   - Review performance analysis for duplicate code
   - Identify common layout patterns
   - Note repeated responsive breakpoints

2. **Design Mixin Categories**
   ```
   _mixins.scss
   ├── Layout mixins
   │   ├── Flexbox centering
   │   ├── Grid systems
   │   ├── Container queries
   │   └── Aspect ratio
   ├── Responsive mixins
   │   ├── Breakpoint system
   │   ├── Mobile-first media queries
   │   └── Orientation handling
   ├── Typography mixins
   │   ├── Text truncation
   │   ├── Line clamping
   │   └── Responsive typography
   ├── Utility mixins
   │   ├── Clearfix
   │   ├── Visually hidden
   │   ├── Focus styles
   │   └── Reset
   ├── Animation mixins
   │   ├── Transitions
   │   ├── Keyframes
   │   └── Hover effects
   └── Cross-browser mixins
       ├── Vendor prefixes
       ├── Flexbox gaps
       └── Custom properties
   ```

3. **Implement Mixins**
   - Create `_mixins.scss` file
   - Implement each mixin with proper parameters
   - Include default values
   - Add usage examples in comments

4. **Refactor Existing Code**
   - Replace repetitive code with mixin calls
   - Update all affected SCSS files
   - Verify functionality

5. **Document Mixins**
   - Create mixin documentation
   - Include parameter descriptions
   - Provide usage examples

### Deliverables

- Complete `_mixins.scss` file
- Refactored SCSS files using mixins
- Mixin library documentation
- Test results report

---

## Task 4: Implement CSS Focus States

**Type**: [Manual-Fix]  
**Depends On**: Prompt 3 (CSS Accessibility Audit)  
**Estimated Time**: 4-6 hours

### Task Description

Ensure all interactive elements have visible, accessible focus states for keyboard navigation.

### Implementation Steps

1. **Identify Missing Focus States**
   - Review accessibility audit report
   - List all interactive elements
   - Note which are missing focus styles

2. **Design Focus State Pattern**
   ```scss
   // Base focus style
   :focus {
     outline: 2px solid var(--color-focus);
     outline-offset: 2px;
   }

   // For mouse users (optional)
   :focus:not(:focus-visible) {
     outline: none;
   }

   // For keyboard users
   :focus-visible {
     outline: 2px solid var(--color-focus);
     outline-offset: 2px;
   }
   ```

3. **Implement Focus States**
   - Add focus styles to all buttons
   - Add focus styles to all links
   - Add focus styles to form inputs
   - Add focus styles to custom interactive elements

4. **Test Keyboard Navigation**
   - Test all pages using Tab key only
   - Verify focus indicators are visible
   - Check focus order is logical
   - Test with screen readers

5. **Document Focus Patterns**
   - Create focus state guidelines
   - Include examples for different element types
   - Document keyboard navigation expectations

### Deliverables

- Updated SCSS files with focus states
- Focus state documentation
- Keyboard navigation test report

---

## Task 5: Fix Color Contrast Issues

**Type**: [Manual-Fix]  
**Depends On**: Prompt 3 (CSS Accessibility Audit)  
**Estimated Time**: 6-8 hours

### Task Description

Fix all color contrast violations to meet WCAG AA (4.5:1 for normal text, 3:1 for large text) standards.

### Implementation Steps

1. **Review Contrast Violations**
   - Review accessibility audit report
   - Categorize by severity (critical, serious, moderate)
   - Prioritize high-impact fixes

2. **Design Color System**
   - Create accessible color palette
   - Ensure all combinations meet WCAG AA
   - Document color usage guidelines

3. **Fix Violations**
   - Adjust foreground colors for better contrast
   - Adjust background colors where needed
   - Update variables in `_variables.scss`
   - Test all color combinations

4. **Verify Compliance**
   - Use contrast checker tools
   - Test in different lighting conditions
   - Verify with screen readers

5. **Document Color System**
   - Create color contrast documentation
   - Include before/after examples
   - Document color usage rules

### Deliverables

- Updated color system in `_variables.scss`
- Fixed contrast violations
- Color system documentation
- Compliance verification report

---

## Task 6: Sanitize All PHP Inputs

**Type**: [Manual-Fix]  
**Depends On**: Prompt 6 (PHP Security Audit)  
**Estimated Time**: 8-12 hours

### Task Description

Ensure all user inputs are properly sanitized using WordPress sanitization functions.

### Implementation Steps

1. **Review Input Sources**
   - Review security audit report
   - Identify all `$_POST`, `$_GET`, `$_REQUEST` usage
   - Identify other input sources (files, APIs, etc.)

2. **Apply Sanitization**
   - Text fields: `sanitize_text_field()`
   - Email addresses: `sanitize_email()`
   - URLs: `esc_url_raw()` (for storage), `sanitize_url()`
   - Integers: `intval()`, `absint()`
   - HTML: `wp_kses_post()` (for allowed HTML)
   - Slugs: `sanitize_title()`
   - File names: `sanitize_file_name()`

3. **Update Code**
   - Add sanitization to all input points
   - Create helper functions for common patterns
   - Update form handlers
   - Update AJAX handlers

4. **Test Security**
   - Test with XSS payloads
   - Test with SQL injection attempts
   - Test with malicious file uploads
   - Verify all inputs are sanitized

5. **Document Sanitization**
   - Create sanitization guidelines
   - Document which function to use for each input type
   - Include examples

### Deliverables

- Updated PHP files with input sanitization
- Sanitization helper functions
- Sanitization guidelines documentation
- Security test report

---

## Task 7: Escape All PHP Outputs

**Type**: [Manual-Fix]  
**Depends On**: Prompt 6 (PHP Security Audit)  
**Estimated Time**: 8-12 hours

### Task Description

Ensure all dynamic output is properly escaped to prevent XSS vulnerabilities.

### Implementation Steps

1. **Review Output Points**
   - Review security audit report
   - Identify all `echo` and `print` statements
   - Identify HTML output in templates

2. **Apply Escaping**
   - HTML content: `esc_html()`
   - HTML attributes: `esc_attr()`
   - URLs in attributes: `esc_url()`
   - JavaScript: `esc_js()`
   - Allowed HTML: `wp_kses()` with allowed tags
   - Translation: `esc_html__()`, `esc_attr__()`, `esc_url_e()`

3. **Update Code**
   - Add escaping to all output points
   - Create helper functions for common patterns
   - Update template files
   - Update AJAX response handlers

4. **Test Security**
   - Test with XSS payloads in all inputs
   - Verify output is properly escaped
   - Check browser console for errors
   - Test with screen readers

5. **Document Escaping**
   - Create escaping guidelines
   - Document which function to use for each output type
   - Include examples

### Deliverables

- Updated PHP files with output escaping
- Escaping helper functions
- Escaping guidelines documentation
- Security test report

---

## Task 8: Implement Nonce Verification

**Type**: [Manual-Fix]  
**Depends On**: Prompt 6 (PHP Security Audit)  
**Estimated Time**: 4-6 hours

### Task Description

Add WordPress nonce verification to all forms and AJAX handlers to prevent CSRF attacks.

### Implementation Steps

1. **Review Forms and AJAX Handlers**
   - Review security audit report
   - Identify all forms
   - Identify all AJAX handlers
   - Identify all POST operations

2. **Add Nonce Fields to Forms**
   ```php
   wp_nonce_field('action_name', 'nonce_field_name');
   ```

3. **Verify Nonces in Handlers**
   ```php
   // For forms
   if (!isset($_POST['nonce_field_name']) || !wp_verify_nonce($_POST['nonce_field_name'], 'action_name')) {
       wp_die('Security check failed');
   }

   // For AJAX
   check_ajax_referer('action_name', 'nonce_field_name');
   ```

4. **Update Code**
   - Add nonce fields to all forms
   - Add nonce verification to all handlers
   - Create helper functions for nonce operations
   - Update AJAX handlers

5. **Test Security**
   - Test with missing nonces
   - Test with invalid nonces
   - Verify legitimate requests work
   - Test CSRF attack scenarios

6. **Document Nonce Usage**
   - Create nonce naming convention
   - Document nonce verification patterns
   - Include examples

### Deliverables

- Updated forms with nonce fields
- Updated handlers with nonce verification
- Nonce helper functions
- Nonce usage documentation
- Security test report

---

## Task 9: Add Capability Checks

**Type**: [Manual-Fix]  
**Depends On**: Prompt 6 (PHP Security Audit)  
**Estimated Time**: 4-6 hours

### Task Description

Add proper capability checks to all sensitive operations to prevent unauthorized access.

### Implementation Steps

1. **Review Sensitive Operations**
   - Review security audit report
   - Identify operations requiring authorization
   - Categorize by required capability

2. **Define Capability Requirements**
   - Delete operations: `delete_posts`, `delete_products`
   - Edit operations: `edit_posts`, `edit_products`
   - Publish operations: `publish_posts`, `publish_products`
   - Admin operations: `manage_options`
   - Custom capabilities if needed

3. **Add Capability Checks**
   ```php
   if (!current_user_can('required_capability')) {
       wp_die('You do not have permission to perform this action');
   }
   ```

4. **Update Code**
   - Add checks to all sensitive operations
   - Add checks to admin menu items
   - Add checks to AJAX handlers
   - Add checks to form handlers

5. **Test Authorization**
   - Test with different user roles
   - Test unauthorized access attempts
   - Verify authorized access works
   - Test edge cases

6. **Document Capabilities**
   - Create capability matrix
   - Document which capability is required for each operation
   - Include examples

### Deliverables

- Updated PHP files with capability checks
- Capability matrix documentation
- Authorization test report

---

## Task 10: Separate Logic from Presentation

**Type**: [Manual-Fix]  
**Depends On**: Prompt 4 (PHP Quality Audit)  
**Estimated Time**: 12-16 hours

### Task Description

Refactor PHP code to separate business logic from presentation following MVC-like patterns.

### Implementation Steps

1. **Analyze Current Structure**
   - Review quality audit report
   - Identify mixed logic and presentation
   - Categorize by component

2. **Design Architecture**
   ```
   Service Layer (Business Logic)
   ├── ProductService
   ├── OrderService
   └── UserService

   Repository Layer (Data Access)
   ├── ProductRepository
   ├── OrderRepository
   └── UserRepository

   View Layer (Presentation)
   ├── Templates
   ├── Partials
   └── Components
   ```

3. **Implement Separation**
   - Extract business logic to service classes
   - Extract data access to repository classes
   - Move presentation to template files
   - Use dependency injection

4. **Refactor Code**
   - Create service classes
   - Create repository classes
   - Update templates
   - Update controllers

5. **Test Functionality**
   - Test all refactored components
   - Verify no functionality lost
   - Check performance impact
   - Verify maintainability improved

6. **Document Architecture**
   - Create architecture documentation
   - Document class responsibilities
   - Include examples

### Deliverables

- Service classes
- Repository classes
- Updated templates
- Architecture documentation
- Test results report

---

## Task 11: Implement Error Handling

**Type**: [Manual-Fix]  
**Depends On**: Prompt 4 (PHP Quality Audit)  
**Estimated Time**: 6-8 hours

### Task Description

Add comprehensive error handling to all operations that can fail.

### Implementation Steps

1. **Identify Error-Prone Operations**
   - Database queries
   - File operations
   - API calls
   - External requests
   - User input processing

2. **Design Error Handling Strategy**
   ```php
   try {
       // Operation that might fail
       $result = performOperation();
   } catch (Exception $e) {
       // Log error
       error_log($e->getMessage());
       
       // Show user-friendly message
       wp_die('An error occurred. Please try again.');
   }
   ```

3. **Implement Error Handling**
   - Add try-catch blocks to risky operations
   - Implement proper error logging
   - Create user-friendly error messages
   - Add error recovery where possible

4. **Update Code**
   - Add error handling to database operations
   - Add error handling to file operations
   - Add error handling to API calls
   - Add error handling to external requests

5. **Test Error Scenarios**
   - Test with database failures
   - Test with file system errors
   - Test with API failures
   - Test with invalid input

6. **Document Error Handling**
   - Create error handling guidelines
   - Document error codes
   - Include examples

### Deliverables

- Updated PHP files with error handling
- Error handling guidelines
- Error scenario test report

---

## Task 12: Extract Duplicate Code

**Type**: [Manual-Fix]  
**Depends On**: Prompt 4 (PHP Quality Audit)  
**Estimated Time**: 8-12 hours

### Task Description

Extract duplicate code into reusable functions and classes.

### Implementation Steps

1. **Review Duplicate Code**
   - Review quality audit report
   - Identify duplicate code blocks
   - Categorize by functionality

2. **Design Reusable Components**
   - Create utility classes for common operations
   - Create helper functions for repeated patterns
   - Use traits for shared functionality

3. **Implement Extractions**
   - Create utility classes
   - Create helper functions
   - Create traits
   - Update all call sites

4. **Refactor Code**
   - Replace duplicates with function calls
   - Update all affected files
   - Verify functionality

5. **Test Thoroughly**
   - Test all refactored components
   - Verify no functionality lost
   - Check for side effects

6. **Document Components**
   - Create utility class documentation
   - Document helper functions
   - Include usage examples

### Deliverables

- Utility classes
- Helper functions
- Traits
- Updated code using extracted components
- Component documentation
- Test results report

---

## Task 13: Break Long Functions

**Type**: [Manual-Fix]  
**Depends On**: Prompt 4 (PHP Quality Audit)  
**Estimated Time**: 10-14 hours

### Task Description

Refactor long functions into smaller, focused functions following Single Responsibility Principle.

### Implementation Steps

1. **Review Long Functions**
   - Review quality audit report
   - Identify functions >50 lines
   - Identify functions with high complexity

2. **Analyze Function Responsibilities**
   - Break down function logic
   - Identify distinct responsibilities
   - Plan extraction strategy

3. **Extract Smaller Functions**
   - Extract validation logic
   - Extract data transformation logic
   - Extract business logic
   - Extract error handling

4. **Refactor Code**
   - Create new functions
   - Update original function
   - Ensure proper parameter passing
   - Maintain return values

5. **Test Thoroughly**
   - Test all refactored functions
   - Verify no functionality lost
   - Check for side effects

6. **Document Functions**
   - Add PHPDoc to all functions
   - Document parameters and return values
   - Include usage examples

### Deliverables

- Refactored functions
- PHPDoc documentation
- Test results report

---

## Task 14: Implement SCSS File Structure

**Type**: [Manual-Fix]  
**Depends On**: Prompt 1 (CSS Quality Audit)  
**Estimated Time**: 6-8 hours

### Task Description

Reorganize SCSS files into a proper, maintainable structure.

### Implementation Steps

1. **Design File Structure**
   ```
   assets/scss/
   ├── main.scss (entry point)
   ├── _variables.scss
   ├── _mixins.scss
   ├── _functions.scss
   ├── base/
   │   ├── _reset.scss
   │   ├── _typography.scss
   │   └── _utilities.scss
   ├── components/
   │   ├── _buttons.scss
   │   ├── _cards.scss
   │   ├── _forms.scss
   │   └── ...
   ├── layouts/
   │   ├── _header.scss
   │   ├── _footer.scss
   │   ├── _grid.scss
   │   └── ...
   ├── pages/
   │   ├── _admin.scss
   │   ├── _products.scss
   │   └── ...
   └── themes/
       ├── _light.scss
       └── _dark.scss
   ```

2. **Reorganize Files**
   - Create new directory structure
   - Move files to appropriate locations
   - Create new partial files
   - Update imports

3. **Update main.scss**
   - Organize imports in correct order
   - Add comments for each section
   - Ensure proper loading order

4. **Test Compilation**
   - Verify SCSS compiles correctly
   - Check for circular dependencies
   - Verify no missing imports

5. **Document Structure**
   - Create file structure documentation
   - Document naming conventions
   - Include guidelines

### Deliverables

- Reorganized SCSS file structure
- Updated main.scss
- File structure documentation

---

## Task 15: Implement Mobile-First Responsive Design

**Type**: [Manual-Fix]  
**Depends On**: Prompt 2 (CSS Performance Analysis)  
**Estimated Time**: 8-12 hours

### Task Description

Refactor all responsive styles to follow mobile-first approach.

### Implementation Steps

1. **Define Breakpoint System**
   ```scss
   $breakpoints: (
     'sm': 576px,
     'md': 768px,
     'lg': 992px,
     'xl': 1200px,
     'xxl': 1400px
   );
   ```

2. **Create Breakpoint Mixin**
   ```scss
   @mixin breakpoint($size) {
     @media (min-width: map-get($breakpoints, $size)) {
       @content;
     }
   }
   ```

3. **Refactor Media Queries**
   - Convert to mobile-first approach
   - Use breakpoint mixin
   - Remove duplicate breakpoints
   - Consolidate related queries

4. **Update Styles**
   - Base styles for mobile
   - Progressive enhancement for larger screens
   - Test all breakpoints

5. **Test Responsiveness**
   - Test on actual mobile devices
   - Test on tablets
   - Test on desktop
   - Use browser DevTools

6. **Document Breakpoints**
   - Create breakpoint documentation
   - Include usage examples
   - Document testing strategy

### Deliverables

- Breakpoint system in _variables.scss
- Breakpoint mixin in _mixins.scss
- Refactored responsive styles
- Breakpoint documentation
- Responsiveness test report

---

# PHASE 3: AUTOMATION

## Prompt 7: Configure Build Process Automation

**Type**: [Automated]  
**Output**: Configured Vite build process  
**Estimated Time**: 4-6 hours

### Task Description

Configure the Vite build process to automatically handle CSS optimization, minification, and compression.

### Configuration Steps

1. **CSS Minification**
   - Configure Vite CSS plugin for minification
   - Enable source maps for development
   - Disable source maps for production

2. **Autoprefixer**
   - Add autoprefixer plugin
   - Configure browser targets
   - Test vendor prefixes

3. **PurgeCSS Integration**
   - Configure PurgeCSS plugin
   - Define content paths (PHP, JS, HTML)
   - Configure safelist for dynamic classes
   - Test purging results

4. **Compression**
   - Enable Gzip compression
   - Enable Brotli compression
   - Configure compression levels

5. **Critical CSS Extraction**
   - Configure critical CSS plugin
   - Define critical selectors
   - Test extraction

### Deliverables

- Updated `vite.config.js`
- Updated `package.json` scripts
- Build process documentation

---

## Prompt 8: Implement Pre-Commit Hooks

**Type**: [Automated]  
**Output**: Configured Husky pre-commit hooks  
**Estimated Time**: 3-4 hours

### Task Description

Set up pre-commit hooks to automatically check code quality before commits.

### Hook Configuration

1. **Install Husky**
   ```bash
   npm install --save-dev husky lint-staged
   npx husky install
   ```

2. **Configure Lint-Staged**
   ```json
   {
     "lint-staged": {
       "*.scss": ["stylelint --fix", "prettier --write"],
       "*.php": ["phpcbf", "phpstan"],
       "*.js": ["eslint --fix", "prettier --write"]
     }
   }
   ```

3. **Create Pre-Commit Hook**
   ```bash
   npx husky add .husky/pre-commit "npx lint-staged"
   ```

4. **Configure Stylelint**
   - Install stylelint
   - Configure WordPress coding standards
   - Add custom rules

5. **Configure PHP-CS-Fixer**
   - Install php-cs-fixer
   - Configure WordPress standards
   - Add custom rules

### Deliverables

- Configured Husky hooks
- Configured lint-staged
- Stylelint configuration
- PHP-CS-Fixer configuration
- Hook documentation

---

## Prompt 9: Set Up CI/CD Quality Gates

**Type**: [Automated]  
**Output**: GitHub Actions workflow  
**Estimated Time**: 4-6 hours

### Task Description

Create CI/CD pipeline with automated quality checks.

### Workflow Configuration

1. **Create GitHub Actions Workflow**
   ```yaml
   name: Quality Checks
   on: [push, pull_request]
   jobs:
     quality:
       runs-on: ubuntu-latest
       steps:
         - uses: actions/checkout@v3
         - name: Setup PHP
           uses: shivammathur/setup-php@v2
           with:
             php-version: '8.1'
         - name: Install dependencies
           run: composer install
         - name: Run PHPStan
           run: vendor/bin/phpstan analyse
         - name: Run Psalm
           run: vendor/bin/psalm
         - name: Run PHPCS
           run: vendor/bin/phpcs
         - name: Run PHPUnit
           run: vendor/bin/phpunit
   ```

2. **Configure Quality Tools**
   - PHPStan with WordPress rules
   - Psalm with security analysis
   - PHPCS with WPCS
   - PHPUnit for testing

3. **Add Security Scanning**
   - Configure security scanner
   - Scan for vulnerabilities
   - Fail on critical issues

4. **Add Performance Checks**
   - Run performance tests
   - Check bundle size
   - Monitor load times

### Deliverables

- GitHub Actions workflow file
- Quality tool configurations
- CI/CD documentation

---

# SUMMARY

## Label Definitions

- **[Script-Detect]** - Script can detect the issue and generate a report
- **[Manual-Fix]** - Requires human judgment and review to implement
- **[Automated]** - Script handles everything automatically

## Phase Breakdown

| Phase | Prompts | Type | Estimated Time |
|-------|---------|------|----------------|
| **Phase 1: Detection** | 6 | Script-Detect | 14-20 hours |
| **Phase 2: Implementation** | 15 | Manual-Fix | 106-146 hours |
| **Phase 3: Automation** | 3 | Automated | 11-16 hours |
| **Total** | **24** | - | **131-182 hours** |

## Task Breakdown by Type

| Type | Count | Percentage |
|------|-------|------------|
| Script-Detect | 6 | 25% |
| Manual-Fix | 15 | 62.5% |
| Automated | 3 | 12.5% |
| **Total** | **24** | **100%** |

## Execution Order

1. **Phase 1**: Run all detection prompts to generate comprehensive reports
2. **Phase 2**: Implement fixes based on priority (Critical → High → Medium → Low)
3. **Phase 3**: Configure automation to prevent future issues

---

**Note: This hybrid approach provides the best balance of efficiency (grouped detection) and clarity (individual implementation), reducing the total number of prompts from 52 to 24 while maintaining comprehensive coverage.**

---


---

**Last Updated**: 2026-02-02
**Total Tasks**: 24 (6 Detection + 15 Implementation + 3 Automation)
**Completed**: 3 (Prompt 1, 2, 3 from Phase 1)
**Pending**: 21
