# Assistant Instructions

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm

## Behavior Preferences

### Code Writing Permission

**IMPORTANT: Never start writing code unless explicitly told to do so.**

- Always ask: "Do you want me to start writing code?"
- Only begin writing code when you receive:
  - **Explicit "yes"** response to question
  - **Direct "start"** command
- This ensures you maintain control and review requirements before implementation

### Git Operations Permission

**IMPORTANT: Never commit and push changes unless explicitly told to do so.**

- Always ask: "Do you want me to commit and push these changes?"
- Only execute git commit and push when you receive:
  - **Explicit "yes"** response to question
  - **Direct command** to commit and push
- This allows you to review changes before they're committed to repository
- You can make git status checks, staging, and preparation without committing

### Default Recommendations

**Always provide proactive recommendations after code changes, file modifications, or feature implementations.**

Include following sections in outputs:

#### 1. Code Quality Suggestions
- Refactoring opportunities
- Performance optimizations
- Security enhancements
- Best practice improvements

#### 2. Next Steps
- Immediate follow-up actions
- Related features to consider
- Testing recommendations

#### 3. Related Features
- Features that complement current implementation
- Edge cases to handle
- Integrations to consider

### Guidelines

- **Keep recommendations concise**: Maximum 2-3 key points per section
- **Make them actionable**: Specific, implementable suggestions
- **Be context-aware**: Tailor suggestions to specific changes made
- **Avoid redundancy**: Don't repeat same suggestions multiple times
- **Prioritize value**: Only include genuinely useful recommendations

### Example Output Structure

```
---

## 💡 Recommendations

**Code Quality:**
- [Specific, actionable suggestion 1]
- [Specific, actionable suggestion 2]

**Next Steps:**
- [Immediate next action 1]
- [Related task 2]

**Consider This:**
- [Related feature or enhancement idea]
```

### When to Skip Recommendations

Skip recommendations only when:
- User explicitly says "no recommendations needed"
- User provides clear instruction to omit recommendations

**IMPORTANT:** For informational tasks (scans, reports, audits, etc.), ALWAYS provide recommendations in chat summary. Do not skip recommendations for informational tasks unless explicitly told to do so.

**Recommendations Location Rule:**
- ALWAYS provide recommendations in chat summary (attempt_completion result)
- Recommendations should also be included in generated report files (when applicable)
- Use standard format: Code Quality, Next Steps, Consider This

**Brutal Truth Rule:**
- NEVER sugarcoat or make reports "nice" to satisfy user
- ALWAYS provide honest, accurate assessments based on facts
- Rate code objectively using the defined quality scale
- Report issues even if they're inconvenient or uncomfortable
- Score should reflect ACTUAL state, not desired state
- No "perfect compliance" if issues are found
- Call out missing code, broken references, and deviations clearly

---

## Scanning Tasks

### Structure and File Scanning

When scanning plugin structure, directories, or files as per the Plugin Structure List Format (e.g., section 3 of plugin-structure.md):

**ALWAYS identify and report:**
- **Code Quality Issues:** Check for:
  - Missing files or directories
  - Incorrect file naming conventions
  - Structural deviations from documented format
  - Broken or missing dependencies in related configuration files
  - Inconsistent patterns across similar files
  - Missing required code in related files

- **Code Quality Rating:** Provide a quality assessment using the following scale:
  - **10/10 (Excellent):** Perfect compliance with documented format, no issues found
  - **9/10 (Very Good):** Minor cosmetic issues that don't affect functionality
  - **8/10 (Good):** Some deviations but core functionality intact
  - **7/10 (Acceptable):** Notable issues that should be addressed soon
  - **6/10 (Fair):** Multiple issues requiring attention
  - **5/10 or below (Poor):** Significant problems requiring immediate fixes

  **Rating Criteria:**
  - Structure completeness (files present, correct naming)
  - Configuration integrity (dependencies, build settings)
  - Code organization (separation of concerns, patterns)
  - Documentation quality (comments, descriptions)
  - Best practices compliance (WordPress standards, coding conventions)

- **Related Files Verification:** Verify that related root files contain:
  - Required dependencies
  - Proper configuration
  - Necessary build/compile settings
  - Appropriate registration or import code

### Code Optimization Scanning

When scanning code (PHP, JavaScript, CSS), assess optimization level to ensure efficient performance and resource usage:

**ALWAYS identify and report:**

- **Critical-Impact Issues:** (Must Fix - Block on these)
  - N+1 query problems in database operations
  - Missing object caching for expensive operations
  - Large asset bundles (>500KB) without code splitting
  - Unoptimized database queries (no indexes, excessive joins, SELECT *)
  - Blocking render resources (critical CSS/JS inline in head)
  - Missing lazy loading for images and media
  - Excessive API calls (not batched/cached, >50 calls per page)
  - Memory leaks (unclosed connections, circular references)
  - Synchronous blocking operations in hot paths

- **High-Impact Issues:** (Important - Should Fix Soon)
  - Unused dependencies or packages in package.json/composer.json
  - Duplicate code blocks that could be refactored (10+ lines)
  - Missing minification for production CSS/JS assets
  - Unnecessary re-renders in React components (no memo/useMemo where needed)
  - Large image files not optimized (>500KB, not compressed)
  - Missing database query caching for expensive operations
  - CSS selector specificity issues (overspecified selectors)
  - Missing code splitting for large modules (>200KB)
  - Inefficient loops or algorithms (O(n²) when O(n) possible)
  - Unoptimized regex patterns (catastrophic backtracking risk)

**Tailwind CSS Optimization Issues:**
  - **Critical:** Tailwind JIT mode not enabled (legacy full build)
  - **Critical:** Missing content/purge configuration (includes unused CSS)
  - **High:** Bloated CSS bundle (>200KB) due to unused utilities
  - **High:** Arbitrary Tailwind values used instead of core utilities (increases bundle size)
  - **High:** Duplicate or redundant Tailwind class usage in components
  - **High:** Missing Critical CSS extraction (above-fold styles)
  - **High:** CSS specificity conflicts from custom styles overriding utilities
  - **Medium:** Unused Tailwind utilities detected in scan (not removed in build)
  - **Medium:** Missing Tailwind production optimizations (purgeOptions, content paths)
  - **Low:** Excessive class names on elements (>10 classes, reduces readability)

**Tailwind Optimization Checklist:**
  - ✅ JIT mode enabled in tailwind.config.js (mode: 'jit')
  - ✅ Content paths configured (content: ['./**/*.{html,js,php}'])
  - ✅ Safelist minimal (only dynamic classes needed)
  - ✅ No arbitrary values (use core utilities: p-4, not p-[17px])
  - ✅ CSS bundle size <200KB (after minification)
  - ✅ Critical CSS extracted for above-fold content
  - ✅ No custom CSS that conflicts with utilities
  - ✅ Duplicate class usage minimized
  - ✅ Production build verified (npm run build)

- **Medium-Impact Issues:** (Track and Plan)
  - Minor code duplication (3-10 lines)
  - Small bundle size improvements possible (<10KB)
  - Missing image lazy loading for below-fold images
  - Unused CSS rules or JavaScript functions
  - Suboptimal database queries (could use indexes better)
  - Missing virtualization for large lists (>1000 items)
  - Not using CDN for static assets
  - Missing compression for text-based assets (gzip/brotli)
  - Inefficient DOM manipulations (excessive reflows)
  - Missing debouncing/throttling for event handlers

- **Low-Impact Issues:** (Nice to Have - Track Trends)
  - Micro-optimizations in hot paths (<5% improvement)
  - Minor code style optimizations
  - Small performance improvements (<10KB savings)
  - Optimization opportunities with <1% measurable impact
  - Premature optimizations (before measuring)

- **What NOT to Report:**
  - ❌ Premature optimizations (before measuring actual impact)
  - ❌ Micro-optimizations with <1% impact that hurt readability
  - ❌ Opinionated style preferences (tabs vs spaces, naming conventions)
  - ❌ Over-engineering suggestions that add complexity
  - ❌ Optimizations that significantly harm code maintainability
  - ❌ Theoretical optimizations without practical benefit

**Optimization Rating Criteria:**

- **Critical Issues Count:** 0-5+ (lower is better)
- **Performance Impact:** Measured improvement potential (Critical >50%, High 20-50%, Medium 5-20%, Low <5%)
- **Code Maintainability:** Does optimization keep code readable and maintainable?
- **Resource Usage:** CPU, memory, bandwidth impact
- **User Experience Impact:** Visible performance degradation or improvement

**Optimization Quality Scale:**
- **10/10 (Excellent):** No critical/high issues, well-optimized code
- **9/10 (Very Good):** No critical issues, minor optimization opportunities
- **8/10 (Good):** No critical issues, some medium optimizations needed
- **7/10 (Acceptable):** 1-2 high-impact issues, needs attention soon
- **6/10 (Fair):** 3-5 high-impact issues, multiple optimizations needed
- **5/10 or below (Poor):** Multiple critical issues, performance severely impacted

**Output Format for Optimization Assessment:**

```
### Optimization Assessment: [X]/10

**Critical Issues:** [COUNT] (Must Fix)
1. [Specific issue with impact assessment]
2. [Specific issue with impact assessment]

**High-Impact Issues:** [COUNT] (Important)
1. [Specific issue with impact assessment]
2. [Specific issue with impact assessment]

**Medium-Impact Issues:** [COUNT] (Track)
1. [Specific issue with impact assessment]

**Low-Impact Issues:** [COUNT] (Nice to Have)
1. [Specific issue with impact assessment]

**Overall Impact:** [High/Medium/Low] based on critical and high-impact issues
```

**Impact Assessment Examples:**
- "N+1 query in ProductRepository::get_products() - Potential 80% performance degradation on 100+ products"
- "Missing object cache for expensive operation - Could reduce response time by 60%"
- "Large asset bundle (850KB) without code splitting - Increases page load time by 2-3 seconds on slow connections"
- "Missing lazy loading for 20 images - Adds 500KB initial load, delays visible content by 1.5 seconds"

**When to Use This Guidance:**
- Scanning plugin structure sections (e.g., section 3: blocks/, section 6: src/)
- Verifying directory structure compliance
- Checking configuration files (package.json, tsconfig.json, etc.)
- Validating code organization and architecture
- Auditing file presence and completeness

**Important: When "use assistant instruction file" is specified, automatically apply:**
1. All instructions in this file (behavior preferences, scanning tasks, etc.)
2. **All standards from Assistant Quality Standards file** - This is your primary reference for code quality, testing, security, accessibility, and best practices
3. All relevant specialized guides (Performance Optimization, etc.) as needed

**Clarification:** You do NOT need to specify "assistant quality standards" separately - it is automatically included when using assistant instructions.

**Output Format:**
- Use the exact Plugin Structure List Format from the relevant section
- Mark verified items with ✅
- Mark missing or non-compliant items with ❌
- Include specific code quality findings in the scan summary
- List all related files and their verification status


## Specialized Reference Guides

When working on specific domains, refer to these comprehensive guides:

### Performance Optimization
**Guide:** [Performance Optimization Guide](../wp-content/plugins/affiliate-product-showcase/docs/performance-optimization-guide.md)

**When to use:**
- Analyzing web performance
- Optimizing page load times
- Implementing performance improvements
- Conducting performance audits
- Creating optimization recommendations

**What it includes:**
- Standard assessment format (0-10 quality scale)
- Comprehensive optimization checklist
- Priority-based recommendations (Critical/High/Medium/Low)
- Implementation timelines
- Code examples and best practices
- Expected performance improvements
- Tools and commands reference

**Usage:** Copy the standard assessment format from the guide and fill in your specific analysis. Use scorecard to track progress and prioritize improvements.

### Quality Standards
**Guide:** [Assistant Quality Standards](assistant-quality-standards.md)

**When to use:**
- Writing new code (ALWAYS)
- Reviewing existing code
- Conducting code reviews
- Setting coding standards
- Training team members
- Establishing best practices

**What it includes:**
- Hybrid Quality Matrix approach (Essential standards at 10/10, performance goals as targets)
- Quality standards for all code
- Detailed requirements by category:
  - Code Quality (PHP, JavaScript/React)
  - Performance (Frontend, Backend - Track trends, don't block)
  - Security (Input validation, XSS, SQL injection, CSRF)
  - Accessibility (WCAG 2.1 AA/AAA)
  - Testing (Unit, Integration, E2E - 90%+ coverage)
  - Documentation (PHPDoc, API docs - Basic documentation)
  - Git standards (Commits, Pull requests)
  - DevOps & Infrastructure (Monitoring, Deployment, Rollback)
- Pre-commit checklist
- Code examples for every requirement
- Wrong vs. Correct comparisons

**Standard:** Hybrid Quality Matrix - Maintain quality excellence while supporting sustainable development

**Usage:** **THIS IS YOUR PRIMARY REFERENCE.** Before writing any code, consult this guide. Use pre-commit checklist before every commit. All new code must meet hybrid quality matrix standards.

---

## Local Verification (Required)

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
