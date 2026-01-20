# Step 2 — Content Types & Taxonomies — Implementation Plan

> Date: 2026-01-20
> Status: In Progress
> Items to implement: 81 total across Steps 2.2-2.5

## Overview

This implementation plan focuses on completing the missing features for:
- **Step 2.2**: Taxonomy: Product Categories (37 incomplete items)
- **Step 2.3**: Taxonomy: Product Tags (13 incomplete items)
- **Step 2.4**: Taxonomy: Product Ribbons (20 incomplete items)
- **Step 2.5**: Type Hints & PHPDoc (11 incomplete items)

Current implementation status: Basic taxonomy registration is complete in `ProductService.php`. The following tasks need to be implemented to achieve full feature parity.

---

## Phase 1: High Priority Core Features

### Priority: Critical - Required for basic functionality

| Task Code | Description | Files to Modify | Dependencies |
|-----------|-------------|------------------|--------------|
| 2.2.8 | Show in admin bar: true | `src/Services/ProductService.php` | None |
| 2.2.13 | Query var: true | `src/Services/ProductService.php` | None |
| 2.2.14 | Rewrite: true with custom slug | `src/Services/ProductService.php` | None |
| 2.2.15 | Capabilities: manage_categories, edit_categories, delete_categories, assign_categories | `src/Services/ProductService.php` | None |
| 2.3.13 | Query var: true | `src/Services/ProductService.php` | None |
| 2.3.14 | Rewrite: true with custom slug | `src/Services/ProductService.php` | None |
| 2.4.10 | Query var: true | `src/Services/ProductService.php` | None |
| 2.4.11 | Rewrite: true | `src/Services/ProductService.php` | None |

**Implementation Notes:**
- Add `show_in_admin_bar => true` to taxonomy registration args
- Add `query_var => true` to enable custom queries
- Add custom capabilities array to `capabilities` arg
- These are simple boolean/array additions to existing registration code

---

## Phase 2: Term Meta Support

### Priority: High - Enhances taxonomy functionality significantly

**Categories (2.2.16-2.2.27):**
- 2.2.16: Meta box callback: custom hierarchical UI
- 2.2.17: Update count callback: custom function
- 2.2.19: Support for term meta
- 2.2.20: Term meta: category icon (SVG upload)
- 2.2.21: Term meta: category color (hex picker)
- 2.2.22: Term meta: category image (thumbnail)
- 2.2.23: Term meta: display order (sortable)
- 2.2.24: Term meta: featured flag
- 2.2.25: Term meta: hide from menu flag
- 2.2.26: Term meta: SEO title
- 2.2.27: Term meta: SEO description

**Tags (2.3.18-2.3.20):**
- 2.3.18: Tag meta: tag color
- 2.3.19: Tag meta: tag icon
- 2.3.20: Tag meta: featured flag

**Ribbons (2.4.13-2.4.21):**
- 2.4.13: Ribbon meta: ribbon text (e.g., 'Best Seller', 'New', 'Sale')
- 2.4.14: Ribbon meta: ribbon color (background)
- 2.4.15: Ribbon meta: text color
- 2.4.16: Ribbon meta: ribbon position (top-left, top-right, bottom-left, bottom-right)
- 2.4.17: Ribbon meta: ribbon style (badge, corner, banner, diagonal)
- 2.4.18: Ribbon meta: icon (SVG or Heroicon name)
- 2.4.19: Ribbon meta: display order/priority
- 2.4.20: Ribbon meta: expiration date
- 2.4.21: Ribbon meta: start date (scheduled ribbons)

**Implementation Notes:**
- Create `src/Admin/TermMeta.php` to handle term meta registration and rendering
- Use WordPress `add_term_meta()` and `update_term_meta()` functions
- Add form fields in admin edit screens for each taxonomy
- Store term meta as serialized arrays or individual keys
- Use WordPress meta box API for custom meta boxes

---

## Phase 3: UI/UX Features

### Priority: Medium - Improves admin usability

**Categories (2.2.28-2.2.44):**
- 2.2.28: Category archive template
- 2.2.29: Category permalink structure
- 2.2.30: Breadcrumb support
- 2.2.31: Parent/child relationship display
- 2.2.32: Product count display
- 2.2.33: Empty category handling
- 2.2.34: Category quick edit
- 2.2.35: Category bulk edit
- 2.2.36: Category sorting/ordering UI
- 2.2.37: Category search functionality
- 2.2.38: Category filter in admin list
- 2.2.39: Category assignment on product edit
- 2.2.40: Multiple category assignment
- 2.2.41: Category-based product filtering
- 2.2.42: Category widget for sidebar
- 2.2.43: Category shortcode
- 2.2.44: Category REST endpoints

**Tags (2.3.15-2.3.29):**
- 2.3.15: Meta box callback: custom tag UI with autocomplete
- 2.3.16: Tag suggestions based on content
- 2.3.17: Popular tags display
- 2.3.21: Tag cloud widget
- 2.3.22: Tag archive template
- 2.3.23: Tag search functionality
- 2.3.24: Tag assignment on product edit
- 2.3.25: Multiple tag assignment
- 2.3.26: Tag-based product filtering
- 2.3.27: Tag shortcode
- 2.3.28: Tag REST endpoints
- 2.3.29: Tag import/export

**Ribbons (2.4.12-2.4.29):**
- 2.4.12: Meta box: custom UI for ribbon selection
- 2.4.22: Ribbon preview in admin
- 2.4.23: Multiple ribbons per product (configurable limit)
- 2.4.24: Ribbon quick edit
- 2.4.26: Ribbon REST endpoints
- 2.4.27: Ribbon import/export
- 2.4.28: Pre-defined ribbon templates
- 2.4.29: Custom ribbon CSS class support

**Implementation Notes:**
- Create `src/Admin/TermUI.php` for enhanced admin UI
- Implement JavaScript for autocomplete and live previews
- Create shortcode handlers in `src/Public/Shortcodes.php`
- Add REST controllers for term CRUD operations
- Implement export/import functionality in `src/Services/ImportExportService.php`

---

## Phase 4: Type Hints & PHPDoc Enhancement

### Priority: High - Code quality and maintainability

**Advanced PHPDoc (2.5.5-2.5.7):**
- 2.5.5: `@package` tag with plugin namespace
- 2.5.6: `@since` tag with version number
- 2.5.7: `@version` tag when updated

**Advanced Documentation (2.5.11-2.5.15):**
- 2.5.11: `@global` tag for global variables
- 2.5.13: `@link` tag for external references
- 2.5.14: `@uses` tag for dependencies
- 2.5.15: `@used-by` tag for callers

**Static Analysis (2.5.24-2.5.27):**
- 2.5.24: PHPStan level 8 compliance
- 2.5.25: Psalm errorLevel 1 compliance
- 2.5.26: No `@suppressWarnings` without justification
- 2.5.27: No `mixed` type without documentation

**Development Tools (2.5.35-2.5.40):**
- 2.5.35: Deprecation notices with `@deprecated`
- 2.5.36: TODO comments with ticket references
- 2.5.37: FIXME comments with priority
- 2.5.38: Code generation from PHPDoc (API docs)
- 2.5.39: IDE autocomplete support
- 2.5.40: Static analysis integration

**Implementation Notes:**
- Run `composer require --dev phpstan/phpstan`
- Run `composer require --dev vimeo/psalm`
- Configure `phpstan.neon` at level 8
- Configure `psalm.xml` at errorLevel 1
- Add phpdoc-tools for documentation generation
- Run static analysis in CI pipeline
- Update `.editorconfig` and IDE settings

---

## Implementation Order

### Sprint 1: Core Registration Features
1. Phase 1: High Priority Core Features
   - Add query_var support to all taxonomies
   - Add show_in_admin_bar to categories
   - Add custom capabilities to categories
   - Mark tasks as completed: `node plan/set-status.cjs 2.2.8 completed` (repeat for each)

### Sprint 2: Term Meta Foundation
2. Phase 2: Term Meta Support
   - Implement term meta registration system
   - Add meta fields for categories (icon, color, image, order, featured, SEO)
   - Add meta fields for tags (color, icon, featured)
   - Add meta fields for ribbons (text, colors, position, style, icon, dates)
   - Create custom meta boxes for taxonomies

### Sprint 3: Basic UI Features
3. Phase 3: UI/UX Features - Categories & Tags
   - Category archive template and breadcrumbs
   - Category quick edit and bulk edit
   - Category widget and shortcode
   - Tag autocomplete and suggestions
   - Tag cloud widget and shortcode
   - Category and tag assignment on product edit

### Sprint 4: Advanced UI Features
4. Phase 3 (continued): UI/UX Features
   - Category sorting/ordering and search
   - Category-based and tag-based product filtering
   - Ribbon meta box with preview
   - Multiple ribbons per product
   - Ribbon templates and CSS classes

### Sprint 5: Code Quality
5. Phase 4: Type Hints & PHPDoc Enhancement
   - Add advanced PHPDoc tags to all classes/methods
   - Configure PHPStan level 8 and Psalm errorLevel 1
   - Run static analysis and fix all issues
   - Set up API documentation generation
   - Integrate static analysis into CI

---

## Task Tracking Commands

### View incomplete tasks:
```bash
grep "⏳" plan/plan_sync.md | grep -E "^## (Step 2\.(2|3|4|5))"
```

### Mark task as in-progress:
```bash
node plan/set-status.cjs 2.2.15 in-progress
```

### Mark task as completed:
```bash
node plan/set-status.cjs 2.2.15 completed
```

### Regenerate plan view after changes:
```bash
node plan/plan_sync_todos.cjs
```

---

## Notes

1. **Code Structure**: All taxonomy registration is in `src/Services/ProductService.php`. Keep modifications focused there.
2. **Testing**: After each phase, test term meta CRUD operations and UI functionality.
3. **Backward Compatibility**: Ensure new features don't break existing functionality. Use default values for new meta fields.
4. **Performance**: Cache term meta queries where possible. Use WordPress caching APIs.
5. **Security**: Sanitize all term meta inputs. Use nonce verification for term updates.
6. **Documentation**: Update inline comments and PHPDoc as features are implemented.

---

## Dependencies

- WordPress 6.4+
- PHP 7.4+
- Composer packages (for static analysis tools)
- Existing plugin infrastructure (Repositories, Services, etc.)

---

## Success Criteria

Phase 1 complete when:
- [ ] All taxonomies have query_var enabled
- [ ] Categories show in admin bar
- [ ] Categories have custom capabilities

Phase 2 complete when:
- [ ] Term meta system registered
- [ ] All meta fields can be created/updated/deleted
- [ ] Meta boxes display correctly in admin

Phase 3 complete when:
- [ ] Category archive template works
- [ ] Category/tag widgets render
- [ ] Shortcodes function correctly
- [ ] Product can have multiple categories/tags assigned

Phase 4 complete when:
- [ ] All PHPDoc tags present
- [ ] PHPStan level 8 passes with 0 errors
- [ ] Psalm errorLevel 1 passes with 0 errors
- [ ] Static analysis integrated in CI
