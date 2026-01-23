# Section 1 — Products Implementation Strategy (UPDATED)

> **Created:** 2026-01-21  
> **Updated:** 2026-01-24  
> **Status:** Ready for Implementation  
> **Batch Size:** 3-5 features (flexible based on complexity)  
> **Total Features:** 61 features (Phase 1) + ~60 (Future)  
> **Phase 1 Remaining:** 35 features

---

## 📊 Executive Summary

**Section 1: Products** - Core product functionality
- **Phase 1 Features:** 61 (Basic + Essential Advanced)
- **Phase 2 Features:** ~60 (Future Improvements)
- **Currently Completed:** 26 (43%)
- **Phase 1 Remaining:** 35 (57%)
- **Strategy:** Create 10 groups of 3-5 features each (flexible batch sizing)

**Implementation Timeline:** 8-10 weeks for Phase 1  
**Quality Standard:** Enterprise-grade 10/10

---

> **IMPORTANT RULE: NEVER DELETE THIS FILE**
> This file contains complete feature requirements for plugin. All features must be implemented according to this plan.

---

# 📝 STRICT DEVELOPMENT RULES

**⚠️ MANDATORY:** Always use all assistant instruction files when writing code for feature development and issue resolution.

### Project Context

**Project:** Affiliate Product Showcase WordPress Plugin  
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)  
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere  
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks  
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS  
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm  

### Required Reference Files (ALWAYS USE):

1. **docs/assistant-instructions.md** - Project context, code change policy, git rules
2. **docs/assistant-quality-standards.md** - Enterprise-grade code quality requirements
3. **docs/assistant-performance-optimization.md** - Performance optimization guidelines
4. **plan/feature-requirements.md** - Complete feature requirements (UPDATED)

### Quality Standard: 10/10 Enterprise-Grade
- Fully/highly optimized, no compromises
- All code must meet hybrid quality matrix standards
- Essential standards at 10/10, performance goals as targets

---

## 🎯 RECOMMENDATION: How Many Features to Implement at a Time?

### Flexible Batch Sizing Strategy

Based on feature complexity, team capacity, and maintainability:

| Batch Size | When to Use | Duration | Pros | Cons |
|------------|--------------|-----------|-------|-------|
| **2-3 features** | High complexity (REST API, database migrations, auto-features) | 1 week | Deep focus, thorough testing | Slower progress |
| **4-5 features** | Medium complexity (basic CRUD, display features) | 1-2 weeks | Balanced pace, good rhythm | May feel rushed |
| **5-7 features** | Low complexity (UI updates, minor fields) | 1-2 weeks | Fast progress, quick wins | Less depth per feature |

### Recommended Approach: **Hybrid Batch Sizing**

**Small Batches (2-3 features):**
- REST API endpoints (Create, Update, Delete, Restore)
- Database schema changes (new fields, migrations)
- Auto-generation features (auto-fetch, auto-extract)
- Complex algorithms (similar products, recommendations)

**Medium Batches (4-5 features):**
- Basic CRUD operations (Add, Edit, Delete)
- Display features (templates, cards, grids)
- Simple UI components (forms, inputs, selects)
- Basic filters and search

**Large Batches (5-7 features):**
- Minor field additions (single properties)
- CSS styling updates
- Simple validation rules
- Label/description changes

### Why This Hybrid Approach?

1. **Maintain Quality:** Complex features get more attention
2. **Sustainable Pace:** Quick wins keep momentum
3. **Flexibility:** Adjust based on feature complexity
4. **Reduced Risk:** Smaller batches = easier testing and debugging
5. **Better Planning:** Can adapt based on actual implementation speed

---

## ⚠️ MANDATORY IMPLEMENTATION RULE: Three-Step Sequential Feature Approach

### Overview

This rule governs how ALL features must be implemented to ensure quality, completeness, and maintainability.

### Configuration

- **Batch Size:** 3-5 features (flexible based on complexity)
- **Approach:** Three-step process (Plan → Code → Test)
- **Quality Standard:** Enterprise-grade 10/10
- **Reference Files:** docs/assistant-instructions.md, docs/assistant-quality-standards.md, docs/assistant-performance-optimization.md (ALL three files required)

---

## 🎯 Implementation Strategy

### Step1: File Discovery & Planning

For each feature or feature group:

1. **List ALL Related Files**
   - Identify every file requiring changes
   - Include: Model, Repository, Service, API, Templates, Tests, Validators, Factories

2. **Document Specific Changes**
   - For each file, document exact changes needed
   - Specify methods to add/modify/delete
   - Note integration points with existing code
   - Identify hooks/filters to add

3. **Create Implementation Checklist**
   - Checklist of all changes per file
   - Verification steps for feature completion
   - Testing requirements

### Step2: Code Writing

After planning is complete:

1. **Write Code in ALL Listed Files**
   - Follow quality standards (WPCS, PSR-12, PHPDoc, type hints)
   - Add proper error handling
   - Sanitize all inputs
   - Escape all outputs
   - Add comprehensive PHPDoc

2. **Follow Enterprise-Grade Standards**
   - Use ALL assistant instruction files
   - Ensure 10/10 quality on essential standards
   - Track performance goals as targets

### Step3: Testing & Verification

After coding is complete:

1. **Write/Update Tests**
   - Unit tests for Model/Repository (PHPUnit)
   - Integration tests for Service/API (PHPUnit)
   - Update test coverage (minimum 90%)

2. **Run Static Analysis**
   ```bash
   # PHP Static Analysis
   composer --working-dir=wp-content/plugins/affiliate-product-showcase analyze
   
   # Frontend Linting
   npm --prefix wp-content/plugins/affiliate-product-showcase run lint
   ```

3. **Manual Testing**
   - Test in WordPress admin interface
   - Verify templates display correctly
   - Test REST API endpoints
   - Check for regressions in existing features

4. **Mark Feature Complete**
   - Only mark complete when ALL steps done
   - Update plan_sync.md with completed status
   - Move to next feature/batch

---

## 🔌 SEO Plugin Integration (Critical Requirement)

**Status:** ⚠️ NOT STARTED - Must be implemented for all features

**Important:** All product, category, tag, and ribbon features MUST be SEO-friendly and integrate seamlessly with popular SEO plugins.

**Reference Document:** `plan/seo-plugin-integration-requirements.md`

**Key Requirements:**
- ✅ All post types SEO-friendly (standard WordPress fields)
- ✅ SEO plugins auto-detect custom post types
- ✅ Schema.org structured data generates automatically
- ✅ Open Graph meta tags generate automatically
- ✅ Twitter Card meta tags generate automatically
- ✅ Integration hooks for SEO plugins work correctly
- ✅ Semantic HTML structure on all templates
- ✅ No conflicts with popular SEO plugins

**Supported SEO Plugins:**
- Yoast SEO
- Rank Math SEO
- All in One SEO Pack
- SEOPress
- The SEO Framework
- Any WordPress-compliant SEO plugin

**Implementation Files:**
1. `src/Seo/SeoIntegration.php` (NEW)
2. `src/Seo/SchemaGenerator.php` (NEW)
3. `src/Seo/OpenGraphGenerator.php` (NEW)
4. `src/Services/ProductService.php` (MODIFY)
5. `src/Public/Public_.php` (MODIFY)
6. `src/Public/SingleProduct.php` (MODIFY)

**When to Implement:**
- Before implementing product display features (Groups 8-10)
- Before implementing category/tag display features
- Before implementing product templates

**See Full Details:** `plan/seo-plugin-integration-requirements.md`

---

## 📋 Feature Groups with Optimized Batch Sizing

### Group 1: Core Product Basics (Features 1-9)

**Status:** 8/9 complete (89%) ✅  
**Batch Size:** 1 feature (small batch - 1 remaining feature)

- [x] 1. Product Title (required)
- [x] 2. Product Slug (auto-generated from title, editable)
- [x] 3. Product Description (textarea with WYSIWYG editor)
- [ ] 4. Product Short Description (short excerpt) ⭐ **NEXT PRIORITY**
- [x] 5. Product Price (numeric, required)
- [x] 6. Affiliate URL (required, validates URL format)
- [x] 7. Featured Image (WordPress media uploader)
- [x] 8. Product Status (Draft, Published, Pending Review, Private)
- [x] 9. Publish Date (date picker)

**Implementation Note:** Feature #4 needs to be completed. All others are done. This is a small batch (1 feature).

---

### Group 2: Product Display (Features 10-15)

**Status:** 5/6 complete (83%)  
**Batch Size:** 1 feature (small batch - 1 remaining feature)

- [x] 10. Single product page template
- [x] 11. Product grid/list view options
- [x] 12. Basic product card with image, title, price
- [x] 13. "Buy Now" / "View Deal" button linking to affiliate URL
- [x] 14. Product URL rel="nofollow sponsored" attributes
- [x] 15. Responsive design for mobile/tablet/desktop

**Implementation Note:** All features complete. Ready for review.

---

### Group 3: Product Management - Core (Features 16-23)

**Status:** 6/8 complete (75%)  
**Batch Size:** 2 features (small batch - 2 remaining features)

- [x] 16. Add new product form (classic editor)
- [x] 17. Edit existing product form
- [x] 18. Delete product (move to trash)
- [ ] 19. Restore product from trash ⭐ **PRIORITY 2**
- [ ] 20. Delete permanently ⭐ **PRIORITY 3**
- [x] 21. Bulk actions: Reset Clicks, Export
- [x] 22. Search products by title/description
- [x] 23. Filter by status, brand

**Implementation Note:** Features #19 and #20 need to be implemented. Small batch (2 features).

---

### Group 4: REST API - Basic (Features 24-31)

**Status:** 3/8 complete (38%)  
**Batch Size:** 5 features (medium batch - 5 remaining features)

- [x] 24. GET `/v1/products` - List products (paginated)
- [x] 25. GET `/v1/products/{id}` - Get single product
- [x] 26. POST `/v1/products` - Create product
- [ ] 27. POST `/v1/products/{id}` - Update product ⭐ **PRIORITY 4**
- [ ] 28. DELETE `/v1/products/{id}` - Delete product ⭐ **PRIORITY 5**
- [ ] 29. POST `/v1/products/{id}/trash` - Trash product ⭐ **PRIORITY 6**
- [ ] 30. POST `/v1/products/{id}/restore` - Restore product ⭐ **PRIORITY 7**
- [ ] 31. DELETE `/v1/products/{id}/delete-permanently` - Permanent delete ⭐ **PRIORITY 8**

**Implementation Note:** Medium batch (5 features). These are REST API endpoints with similar complexity.

---

### Group 5: Essential Digital Product Fields (Features A1-A30)

**Status:** 0/10 complete (0%)  
**Batch Size:** 5 features (medium batch - 10 features, mixed complexity)

- [ ] A1. Original Price (for discount calculation) ⭐ **PRIORITY 9**
- [ ] A2. Discount Percentage (auto-calculated) ⭐ **PRIORITY 10**
- [ ] A3. Currency Selection (USD, EUR, GBP, etc.) ⭐ **PRIORITY 11**
- [ ] A5. Platform Requirements (e.g., "WordPress 6.0+", "Python 3.8+", etc.) ⭐ **PRIORITY 12**
- [ ] A7. Version Number (e.g., "1.0.0", "v2.5.1")
- [ ] A26. Product share buttons (social media)
- [ ] A27. Product tabs (Description, Specs, FAQ, Requirements)
- [ ] A29. Lazy loading for images

**Implementation Note:** Medium batch (8 features). Price/currency are critical, others are display features.

---

## 🚀 IMPLEMENT NOW: Basic Management Features

**Status:** Ready for immediate implementation  
**Priority:** High - These should be implemented now (from "Implement Now" section)

### Group 6: Quick Edit & Bulk Actions (Features F12, F19-F20)

**Status:** 0/3 complete (0%)  
**Batch Size:** 3 features (medium batch)

- [ ] F12. Quick Edit in Product List ⭐ **COMPLEX**
- [ ] F19. Bulk Category/Tag Assignment ⭐ **COMPLEX**
- [ ] F20. Bulk Ribbon Assignment ⭐ **COMPLEX**

**Implementation Tasks:**

**F12. Quick Edit in Product List:**
- Add "Quick Edit" button to products list row actions
- Create AJAX modal for quick editing (title, slug, price, status)
- Add `wp_ajax_aps_product_quick_edit` action
- Implement nonce verification
- Update product on save without page refresh

**F19. Bulk Category/Tag Assignment:**
- Add "Assign Categories" bulk action to products list
- Add "Assign Tags" bulk action to products list
- Create AJAX modal with category/tag checkboxes
- Implement bulk update logic
- Show success message after assignment

**F20. Bulk Ribbon Assignment:**
- Add "Assign Ribbon" bulk action to products list
- Create dropdown to select ribbon
- Implement bulk ribbon update
- Remove previous ribbons before assigning new one

**Files to Modify:**
- `src/Admin/ProductsList.php` - Add quick edit and bulk actions
- `src/Admin/BulkActions.php` - Handle bulk assignments
- `src/Admin/AjaxHandlers.php` - Handle quick edit AJAX
- `assets/js/admin-products.js` - Quick edit modal and bulk action logic

---

### Group 7: Import/Export Products (Features F14-F15)

**Status:** 0/2 complete (0%)  
**Batch Size:** 2 features (medium batch)

- [ ] F14. Import Products (CSV/XML) ⭐ **COMPLEX**
- [ ] F15. Export Products (CSV/XML) ⭐ **COMPLEX**

**Implementation Tasks:**

**F14. Import Products (CSV/XML):**
- Create admin menu page: "Import Products"
- Add file upload form for CSV/XML files
- Parse CSV/XML file format validation
- Map fields: title, slug, price, affiliate_url, image, category, tags
- Create products from imported data
- Show import progress and results
- Add import error logging

**F15. Export Products (CSV/XML):**
- Create export form with filters (by category, tag, date range)
- Generate CSV/XML file with all product data
- Include meta fields: price, original_price, affiliate_url, clicks, conversions
- Offer downloadable file
- Add export progress for large datasets

**Files to Create:**
- `src/Admin/ImportExport.php` - Import/export handler
- `templates/admin/import-products.php` - Import page template
- `templates/admin/export-products.php` - Export page template
- `assets/js/admin-import-export.js` - Import/export form logic

**API Endpoints:**
- POST `/v1/products/import` - Accept file upload
- GET `/v1/products/export` - Generate downloadable file

---

### Group 8: Auto-Generate Slugs (Feature F22)

**Status:** 0/1 complete (0%)  
**Batch Size:** 1 feature (small batch)

- [ ] F22. Auto-Generate Slugs from Title ⭐ **AUTOMATION**

**Implementation Tasks:**
- Hook into `save_post` action
- Check if slug is empty
- Generate slug from title using WordPress `sanitize_title()`
- Ensure slug uniqueness (append number if duplicate)
- Update post slug

**Files to Create:**
- `src/Services/SlugGenerator.php` - Auto-slug generation logic

**Implementation:**
```php
add_action('save_post_aps_product', function($post_id) {
    $post = get_post($post_id);
    if (empty($post->post_name)) {
        $slug = sanitize_title($post->post_title);
        $unique_slug = wp_unique_post_slug($slug, $post_id, 'post', 'aps_product');
        wp_update_post([
            'ID' => $post_id,
            'post_name' => $unique_slug
        ]);
    }
});
```

---

## 📅 Optimized Implementation Timeline (8-10 weeks for Phase 1)

### Phase 1: Complete Core Basics (Weeks 1-2)

**Week 1:** Group 1 - Feature #4 Only
- Implement Feature #4 (Product Short Description)
- Database schema update (excerpt field)
- Update Product model and factory
- Add to admin form and display
- **Expected Outcome:** 27/61 complete (44%)

**Week 2:** Group 3 - Features #19-20
- Implement Feature #19 (Restore from trash)
- Implement Feature #20 (Delete permanently)
- Add WordPress hook handlers
- Update admin UI
- **Expected Outcome:** 29/61 complete (48%)

---

### Phase 2: REST API Core (Weeks 3-4)

**Week 3:** Group 4 - Features #27-29
- Implement Feature #27 (REST API: Update product)
- Implement Feature #28 (REST API: Delete product)
- Implement Feature #29 (REST API: Trash product)
- Add validation and error handling
- Write integration tests
- **Expected Outcome:** 32/61 complete (52%)

**Week 4:** Group 4 - Features #30-31
- Implement Feature #30 (REST API: Restore product)
- Implement Feature #31 (REST API: Permanent delete)
- Add validation and error handling
- Write integration tests
- **Expected Outcome:** 34/61 complete (56%)

---

### Phase 3: Essential Digital Product Fields (Weeks 5-6)

**Week 5:** Group 5 - Features A1-A3
- Implement Feature A1 (Original Price)
- Implement Feature A2 (Discount Percentage)
- Implement Feature A3 (Currency Selection)
- Database schema updates
- Add currency formatting logic
- **Expected Outcome:** 37/61 complete (61%)

**Week 6:** Group 5 - Features A5, A7, A26, A27, A29
- Implement Feature A5 (Platform Requirements)
- Implement Feature A7 (Version Number)
- Implement Feature A26 (Share Buttons)
- Implement Feature A27 (Product Tabs)
- Verify Feature A29 (Lazy Loading)
- **Expected Outcome:** 42/61 complete (69%)

---

### Phase 4: Implement Now Features (Weeks 7-9)

**Week 7:** Group 6 - Features F12, F19-F20
- Implement Feature F12 (Quick Edit)
- Implement Feature F19 (Bulk Category/Tag Assignment)
- Implement Feature F20 (Bulk Ribbon Assignment)
- Add AJAX handlers and modals
- **Expected Outcome:** 45/61 complete (74%)

**Week 8:** Group 7 - Features F14-F15
- Implement Feature F14 (Import Products)
- Implement Feature F15 (Export Products)
- Add import/export handlers
- **Expected Outcome:** 47/61 complete (77%)

**Week 9:** Group 8 - Feature F22
- Implement Feature F22 (Auto-Generate Slugs)
- Add save_post hook
- **Expected Outcome:** 48/61 complete (79%)

---

### Phase 5: Testing & Quality Assurance (Week 10)

**Week 10:** Final Testing & Review
- Run all static analysis (PHPStan, Psalm, PHPCS)
- Execute all tests (PHPUnit, E2E)
- Manual testing in WordPress admin
- Performance testing (Lighthouse)
- Security testing
- **Expected Outcome:** 48/61 complete (79%) - READY FOR PHASE 2

---

## 🎯 Success Criteria

### Functionality (48/61 Phase 1 features)
- ✅ All core product fields working
- ✅ Full CRUD REST API
- ✅ Essential digital product fields
- ✅ Basic product management features
- ✅ Import/Export functionality
- ✅ Auto-generation features

### Quality Standards
- ✅ PHPStan Level 6 passed
- ✅ Psalm Level 4 passed
- ✅ PHPCS WPCS compliant
- ✅ Test coverage ≥90%
- ✅ No security vulnerabilities
- ✅ Performance score ≥90 (Lighthouse)

### User Experience
- ✅ Mobile-responsive design
- ✅ Accessible (WCAG 2.1 AA)
- ✅ Fast page loads (<2s)
- ✅ Intuitive admin interface
- ✅ Clear documentation

---

## 📊 Progress Tracking

**Starting Point:** 26/61 features complete (43%)

**By Week 1:** 27/61 features (44%) - Core Basics Complete  
**By Week 2:** 29/61 features (48%) - Basic Management Complete  
**By Week 3:** 32/61 features (52%) - REST API Part 1 Complete  
**By Week 4:** 34/61 features (56%) - REST API Core Complete  
**By Week 5:** 37/61 features (61%) - Essential Fields Part 1 Complete  
**By Week 6:** 42/61 features (69%) - Essential Fields Complete  
**By Week 7:** 45/61 features (74%) - Implement Now Part 1 Complete  
**By Week 8:** 47/61 features (77%) - Import/Export Complete  
**By Week 9:** 48/61 features (79%) - Auto-Features Complete  
**By Week 10:** 48/61 features (79%) - **PHASE 1 COMPLETE** ✅

---

## 🚨 Risk Assessment

### High Risk
- Database migrations for advanced fields (test thoroughly on staging)
- Import/Export functionality (validate data carefully)
- Auto-generation features (review for edge cases and performance)

### Medium Risk
- Bulk operations (ensure no data corruption)
- Quick edit functionality (test with different data types)
- REST API endpoints (can be rolled back individually)

### Low Risk
- Display features (isolated impact)
- Basic field additions (low complexity)
- Auto-slug generation (well-tested pattern)

### Mitigation
- Feature flags for all new features
- Staging environment testing before production
- Rollback plan for each phase
- Continuous monitoring after deployment
- Automated tests for critical paths

---

## 📝 Immediate Next Steps

### Priority Order (What to Start Now)

**Week 1 - Feature #4 (Product Short Description):**
1. Database schema: Add `post_excerpt` support to product post type
2. Model: Update Product model to include short description
3. Factory: Update ProductFactory to handle excerpt
4. Admin Form: Add excerpt field to product form
5. Template: Display excerpt in product templates
6. REST API: Include excerpt in product responses
7. Tests: Write unit and integration tests

**Implementation Files:**
- `src/Models/Product.php` (MODIFY)
- `src/Factories/ProductFactory.php` (MODIFY)
- `src/Admin/ProductForm.php` (MODIFY or CREATE)
- `src/Public/SingleProduct.php` (MODIFY)
- `src/API/ProductController.php` (MODIFY)
- `tests/Unit/Models/ProductTest.php` (MODIFY)
- `tests/Integration/API/ProductControllerTest.php` (MODIFY)

**Ready to Start:** Yes ✅

---

## 🎨 CSS Classes Reference for Product Table

## Complete CSS Classes List (14 Total)

### Class Naming Convention
All admin table classes follow this pattern:
```
aps-product-[element]-[modifier]
```

### By Column

#### 1. Logo Column (2 classes)
- `aps-product-logo` - Product image display
- `aps-product-logo-placeholder` - Fallback placeholder

#### 2. Category Column (1 class)
- `aps-product-category` - Category badge styling

#### 3. Tags Column (1 class)
- `aps-product-tag` - Tag pill styling

#### 4. Ribbon/Badge Column (1 class)
- `aps-product-badge` - Product ribbon/badge styling

#### 5. Featured Column (1 class)
- `aps-product-featured` - Featured star icon styling

#### 6. Price Column (3 classes)
- `aps-product-price` - Main price container and display
- `aps-product-price-original` - Original price with strikethrough
- `aps-product-price-discount` - Discount percentage display

#### 7. Status Column (5 classes)
- `aps-product-status` - Base status styling
- `aps-product-status-published` - Published status (green)
- `aps-product-status-draft` - Draft status (gray)
- `aps-product-status-trash` - Trashed status (red)
- `aps-product-status-pending` - Pending review status (yellow)

## Implementation Files
- **CSS Styles:** `assets/css/admin-table.css`
- **PHP Implementation:** `src/Admin/Columns.php`
- **Enqueue Script:** `src/Admin/Enqueue.php`

## Developer Guidelines
- ✅ Use ONLY these 14 approved CSS classes
- ✅ Follow `aps-product-` prefix convention
- ✅ No inline styles in HTML
- ✅ Document any new classes in this reference
- ❌ Do NOT create new classes without updating this reference

---

**IMPORTANT:** This implementation strategy applies to Phase 1 features (61 total). Future improvements (~60 features) are tracked in the "Future Improvements" section of `plan/feature-requirements.md` and will be implemented in Phase 2+.

*Plan Updated: 2026-01-24*  
*Reference: plan/feature-requirements.md (v4.0.0), docs/assistant-quality-standards.md, docs/assistant-performance-optimization.md*
