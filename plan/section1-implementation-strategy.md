# Section 1 — Products Implementation Strategy

> **Created:** 2026-01-21  
> **Updated:** 2026-01-21  
> **Status:** Ready for Implementation  
> **Batch Size:** 5 features per batch  
> **Total Features:** 109 features (83 incomplete)

---

## 📊 Executive Summary

**Section 1: Products** - Core product functionality
- **Total Features:** 109
- **Currently Completed:** 26 (24%)
- **Remaining:** 83 (76%)
- **Strategy:** Create 22 groups of 5 features each (3 groups have <5 features)

**Implementation Timeline:** 22 weeks (estimated)
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

### Quality Standard: 10/10 Enterprise-Grade
- Fully/highly optimized, no compromises
- All code must meet hybrid quality matrix standards
- Essential standards at 10/10, performance goals as targets

---


## ⚠️ MANDATORY IMPLEMENTATION RULE: Three-Step Sequential Feature Approach

### Overview

This rule governs how ALL features must be implemented to ensure quality, completeness, and maintainability.

### Configuration

- **Batch Size:** 5 features per batch
- **Approach:** Three-step process (Plan → Code → Test)
- **Quality Standard:** Enterprise-grade 10/10
- **Reference Files:** docs/assistant-instructions.md, docs/assistant-quality-standards.md, docs/assistant-performance-optimization.md (ALL three files required)

---

## 🎯 Implementation Strategy

### Step 1: File Discovery & Planning

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

### Step 2: Code Writing

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

### Step 3: Testing & Verification

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
- Before implementing product display features (Groups 12-15)
- Before implementing category/tag display features
- Before implementing product templates

**See Full Details:** `plan/seo-plugin-integration-requirements.md`

---

## 📋 Feature Groups (22 Groups)

### Group 1: Core Product Basics (Features 1-5)

**Status:** 4/5 complete (80%) ✅

- [x] 1. Product Title (required)
- [x] 2. Product Slug (auto-generated from title, editable)
- [x] 3. Product Description (textarea with WYSIWYG editor)
- [ ] 4. Product Short Description (short excerpt)
- [x] 5. Product Price (numeric, required)

**Implementation Note:** Feature #4 needs to be completed. All others are done.

---

### Group 2: Product Media & Status (Features 6-10)

**Status:** 5/5 complete (100%) ✅

- [x] 6. Affiliate URL (required, validates URL format)
- [x] 7. Featured Image (WordPress media uploader)
- [x] 8. Product Status (Draft, Published, Pending Review, Private)
- [x] 9. Publish Date (date picker)
- [x] 10. Single product page template

**Implementation Note:** All features complete. Ready for review.

---

### Group 3: Product Display (Features 11-15)

**Status:** 5/5 complete (100%) ✅

- [x] 11. Product grid/list view options
- [x] 12. Basic product card with image, title, price
- [x] 13. "Buy Now" / "View Deal" button linking to affiliate URL
- [x] 14. Product URL rel="nofollow sponsored" attributes
- [x] 15. Responsive design for mobile/tablet/desktop

**Implementation Note:** All features complete. Ready for review.

---

### Group 4: Product Management - Core (Features 16-20)

**Status:** 3/5 complete (60%)

- [x] 16. Add new product form (classic editor)
- [x] 17. Edit existing product form
- [x] 18. Delete product (move to trash)
- [ ] 19. Restore product from trash
- [ ] 20. Delete permanently

**Implementation Note:** Features #19 and #20 need to be implemented.

---

### Group 5: Bulk & Search Operations (Features 21-25)

**Status:** 5/5 complete (100%) ✅

- [x] 21. Bulk actions: Set In Stock, Set Out of Stock, Reset Clicks, Export
- [x] 22. Search products by title/description
- [x] 23. Filter by status, brand, stock
- [x] 24. GET `/v1/products` - List products (paginated)
- [x] 25. GET `/v1/products/{id}` - Get single product

**Implementation Note:** All features complete. Ready for review.

---

### Group 6: REST API - Create & Update (Features 26-30)

**Status:** 1/5 complete (20%)

- [x] 26. POST `/v1/products` - Create product
- [ ] 27. POST `/v1/products/{id}` - Update product
- [ ] 28. DELETE `/v1/products/{id}` - Delete product
- [ ] 29. POST `/v1/products/{id}/trash` - Trash product
- [ ] 30. POST `/v1/products/{id}/restore` - Restore product

**Implementation Note:** Only #26 is complete. #27-30 need implementation.

---

### Group 7: REST API - Advanced (Features 31, P1-P5)

**Status:** 0/5 complete (0%)

- [ ] 31. DELETE `/v1/products/{id}/delete-permanently` - Permanent delete
- [ ] P1. Original Price (for discount calculation)
- [ ] P2. Discount Percentage (auto-calculated)
- [ ] P3. Currency Selection (USD, EUR, GBP, etc.)
- [ ] P4. Product SKU/ID (custom identifier)

**Implementation Note:** All features need implementation. Critical for advanced functionality.

---

### Group 8: Product Metadata - Part 1 (Features P5-P9)

**Status:** 0/5 complete (0%)

- [ ] P5. Stock Status (In Stock, Out of Stock, Pre-order)
- [ ] P6. Availability Date (for pre-orders)
- [ ] P7. Product Rating (0-5 stars)
- [ ] P8. Review Count
- [ ] P9. Product Weight (for shipping calculation)

**Implementation Note:** All features need database schema updates and model changes.

---

### Group 9: Product Metadata - Part 2 (Features P10-P14)

**Status:** 0/5 complete (0%)

- [ ] P10. Product Dimensions (L x W x H)
- [ ] P11. Product Brand/Manufacturer
- [ ] P12. Product Warranty Information
- [ ] P13. Product Video URL (YouTube, Vimeo embed)
- [ ] P14. Product Gallery (multiple images)

**Implementation Note:** Gallery and video support require media management system.

---

### Group 10: Product Documentation (Features P15-P19)

**Status:** 0/5 complete (0%)

- [ ] P15. Product PDF Brochure (file upload)
- [ ] P16. Product FAQ Section (accordion)
- [ ] P17. Product Specifications Table
- [ ] P18. Product Comparison Chart
- [ ] P19. Product Release Date

**Implementation Note:** Features need admin UI updates and display logic.

---

### Group 11: Product Lifecycle (Features P20-P21, P22-P24)

**Status:** 0/5 complete (0%)

- [ ] P20. Product Expiration Date
- [ ] P21. Product Priority (display order)
- [ ] P22. Quick view modal (AJAX)
- [ ] P23. Add to comparison list
- [ ] P24. Add to wishlist

**Implementation Note:** Wishlist and comparison require new database tables.

---

### Group 12: Product Display Enhancements (Features P25-P30)

**Status:** 0/5 complete (0%)

- [ ] P25. Product zoom on image hover
- [ ] P26. Image gallery with thumbnails
- [ ] P27. Video preview in gallery
- [ ] P28. Related products section
- [ ] P29. Recently viewed products

**Implementation Note:** Features require JavaScript components and CSS animations.

---

### Group 13: Product Marketing (Features P30-P35)

**Status:** 0/5 complete (0%)

- [ ] P30. Products you may also like
- [ ] P31. Product share buttons (social media)
- [ ] P32. Print product page button
- [ ] P33. Product review form
- [ ] P34. Product rating display

**Implementation Note:** Social sharing requires API integration and icons.

---

### Group 14: Product Badges (Features P35-P39)

**Status:** 0/5 complete (0%)

- [ ] P35. Discount badge with percentage
- [ ] P36. "On Sale" indicator
- [ ] P37. "New Arrival" badge
- [ ] P38. "Best Seller" badge
- [ ] P39. Countdown timer for limited offers

**Implementation Note:** Badge system requires admin interface and display logic.

---

### Group 15: Product Information Display (Features P40-P44)

**Status:** 0/5 complete (0%)

- [ ] P40. Stock status indicator
- [ ] P41. Estimated delivery date
- [ ] P42. Product tabs (Description, Specs, Reviews, FAQ)
- [ ] P43. Sticky "Buy Now" button on scroll
- [ ] P44. Lazy loading for images

**Implementation Note:** Features #44 is marked complete in requirements but needs verification.

---

### Group 16: Advanced Management - Part 1 (Features P45-P49)

**Status:** 0/5 complete (0%)

- [ ] P45. Gutenberg block editor support
- [ ] P46. Quick edit in product list
- [ ] P47. Bulk edit (price, stock, categories, tags)
- [ ] P48. Clone/Duplicate product
- [ ] P49. Import products (CSV/XML)

**Implementation Note:** Gutenberg blocks require React and block development.

---

### Group 17: Advanced Management - Part 2 (Features P50-P54)

**Status:** 0/5 complete (0%)

- [ ] P50. Export products (CSV/XML)
- [ ] P51. Product versioning (save drafts)
- [ ] P52. Product scheduling (auto-publish)
- [ ] P53. Product expiration (auto-unpublish)
- [ ] P54. Bulk price update (increase/decrease by %)

**Implementation Note:** Versioning and scheduling require WordPress cron jobs.

---

### Group 18: Advanced Management - Part 3 (Features P55-P59)

**Status:** 0/5 complete (0%)

- [ ] P55. Bulk category/tag assignment
- [ ] P56. Bulk ribbon assignment
- [ ] P57. Product duplicate checker (by URL/ID)
- [ ] P58. Auto-generate slugs from title
- [ ] P59. Auto-extract product images from affiliate URL

**Implementation Note:** Auto-features require external API integration.

---

### Group 19: Advanced Management - Part 4 (Features P60-P64)

**Status:** 0/5 complete (0%)

- [ ] P60. Auto-fetch product details from affiliate URL
- [ ] P61. Product preview before publishing
- [ ] P62. Product change history/log
- [ ] P63. Product approval workflow (for multi-author sites)
- [ ] P64. GET `/v1/products?featured=true` - Get featured products

**Implementation Note:** History and approval require new database tables.

---

### Group 20: REST API - Filtering (Features P65-P69)

**Status:** 0/5 complete (0%)

- [ ] P65. GET `/v1/products?on_sale=true` - Get products on sale
- [ ] P66. GET `/v1/products?search={term}` - Search products
- [ ] P67. GET `/v1/products?category={id}` - Filter by category
- [ ] P68. GET `/v1/products?tag={id}` - Filter by tag
- [ ] P69. GET `/v1/products?ribbon={id}` - Filter by ribbon

**Implementation Note:** All filtering endpoints are critical for frontend.

---

### Group 21: REST API - Advanced (Features P70-P74)

**Status:** 0/5 complete (0%)

- [ ] P70. GET `/v1/products?min_price={price}` - Filter by min price
- [ ] P71. GET `/v1/products?max_price={price}` - Filter by max price
- [ ] P72. GET `/v1/products?sort={field}` - Sort products
- [ ] P73. GET `/v1/products?include={ids}` - Get specific products
- [ ] P74. GET `/v1/products/similar/{id}` - Get similar products

**Implementation Note:** Similar products algorithm needs tuning for performance.

---

### Group 22: REST API - Bulk & Import (Features P75-P78)

**Status:** 0/5 complete (0%)

- [ ] P75. POST `/v1/products/bulk` - Bulk create/update
- [ ] P76. POST `/v1/products/{id}/duplicate` - Clone product
- [ ] P77. POST `/v1/products/import` - Import products
- [ ] P78. GET `/v1/products/export` - Export products

**Implementation Note:** Import/Export require validation and error handling.

---

## 📅 Implementation Timeline (22 weeks)

### Phase 1: Core Functionality Completion (Weeks 1-5)

**Week 1-2:** Groups 1-3 (Core Product Features)
- Complete Feature #4 (Short Description)
- Review and validate Groups 2-3
- **Expected Outcome:** Basic product fields complete

**Week 3:** Group 4 (Core Management)
- Implement Feature #19 (Restore from trash)
- Implement Feature #20 (Delete permanently)
- **Expected Outcome:** Complete CRUD operations

**Week 4:** Group 5 (Bulk & Search)
- Review existing bulk actions
- Validate search functionality
- **Expected Outcome:** Core management verified

**Week 5:** Group 6 (REST API - Create & Update)
- Implement Features #27-30 (Update, Delete, Trash, Restore)
- Complete REST API basic operations
- **Expected Outcome:** Full CRUD REST API

---

### Phase 2: Advanced Product Fields (Weeks 6-8)

**Week 6:** Group 7 (Advanced REST - Part 1)
- Implement Feature #31 (Permanent delete)
- Implement Features P1-P4 (Original Price, Discount, Currency, SKU)
- Database schema migrations
- **Expected Outcome:** Advanced product fields + permanent delete

**Week 7:** Group 8 (Product Metadata - Part 1)
- Implement Features P5-P9 (Stock, Rating, Review Count, Weight)
- Update Product model
- Update ProductFactory
- **Expected Outcome:** Rich product metadata

**Week 8:** Group 9 (Product Metadata - Part 2)
- Implement Features P10-P14 (Dimensions, Brand, Warranty, Video, Gallery)
- Add video player component
- Add gallery system
- **Expected Outcome:** Complete product metadata system

---

### Phase 3: Documentation & Lifecycle (Weeks 9-10)

**Week 9:** Group 10 (Product Documentation)
- Implement Features P15-P19 (PDF, FAQ, Specs, Comparison, Release Date)
- Add admin UI for documentation
- Update templates to display documentation
- **Expected Outcome:** Product documentation system

**Week 10:** Group 11 (Product Lifecycle)
- Implement Features P20-P24 (Expiration, Priority, Quick View, Comparison, Wishlist)
- Create database tables for wishlist/comparison
- Implement quick view modal
- **Expected Outcome:** Product lifecycle management

---

### Phase 4: Enhanced Display (Weeks 11-13)

**Week 11:** Group 12 (Display Enhancements - Part 1)
- Implement Features P25-P29 (Zoom, Gallery, Video, Related, Recently Viewed)
- Add image zoom component
- Add related products algorithm
- **Expected Outcome:** Enhanced product display

**Week 12:** Group 13 (Product Marketing)
- Implement Features P30-P34 (You May Also Like, Share, Print, Review, Rating)
- Add social sharing buttons
- Implement review system
- **Expected Outcome:** Marketing features

**Week 13:** Group 14 (Product Badges)
- Implement Features P35-P39 (Discount, Sale, New, Best Seller, Countdown)
- Implement badge system
- Add countdown timer component
- **Expected Outcome:** Badge system

---

### Phase 5: Information Display (Weeks 14-15)

**Week 14:** Group 15 (Information Display)
- Implement Features P40-P44 (Stock, Delivery, Tabs, Sticky Button, Lazy Loading)
- Add tabs system
- Implement sticky button
- **Expected Outcome:** Professional product display

**Week 15:** Buffer & Testing
- Test Phase 1-4 features
- Fix bugs and edge cases
- Performance optimization
- **Expected Outcome:** Stable core features

---

### Phase 6: Advanced Management (Weeks 16-19)

**Week 16:** Group 16 (Advanced Management - Part 1)
- Implement Features P45-P49 (Gutenberg, Quick Edit, Bulk Edit, Clone, Import)
- Develop Gutenberg blocks
- Add quick edit modal
- **Expected Outcome:** Advanced management tools

**Week 17:** Group 17 (Advanced Management - Part 2)
- Implement Features P50-P54 (Export, Versioning, Scheduling, Expiration, Bulk Price)
- Implement versioning system
- Add scheduling hooks
- **Expected Outcome:** Workflow features

**Week 18:** Group 18 (Advanced Management - Part 3)
- Implement Features P55-P59 (Bulk Assignments, Duplicate Checker, Auto-generate, Auto-extract)
- Implement duplicate checker
- Add auto-generation features
- **Expected Outcome:** Automation features

**Week 19:** Group 19 (Advanced Management - Part 4)
- Implement Features P60-P64 (Auto-fetch, Preview, History, Approval, Featured Filter)
- Add change history logging
- Implement approval workflow
- **Expected Outcome:** Enterprise management

---

### Phase 7: Advanced REST API (Weeks 20-21)

**Week 20:** Group 20 (REST API - Filtering)
- Implement Features P65-P69 (On Sale, Search, Category, Tag, Ribbon filters)
- Add filter parameters to endpoints
- Implement search algorithm
- **Expected Outcome:** Complete filtering system

**Week 21:** Group 21 (Advanced REST API)
- Implement Features P70-P74 (Price Range, Sort, Include, Similar Products)
- Implement sorting algorithm
- Add similar products algorithm
- **Expected Outcome:** Advanced REST API

---

### Phase 8: Bulk & Import (Week 22)

**Week 22:** Group 22 (REST API - Bulk & Import)
- Implement Features P75-P78 (Bulk, Clone, Import, Export)
- Add bulk operations
- Implement import/export
- **Expected Outcome:** Complete REST API

---

## 🎯 Success Criteria

### Functionality (109/109 features)
- ✅ All core product fields working
- ✅ Full CRUD REST API
- ✅ Advanced product metadata
- ✅ Enhanced product display (badges, gallery, tabs)
- ✅ Advanced management features
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

**Starting Point:** 26/109 features complete (24%)

**By Week 5:** 30/109 features (28%) - Core Features Done
**By Week 8:** 45/109 features (41%) - Advanced Fields Done
**By Week 10:** 54/109 features (50%) - Documentation Done
**By Week 13:** 65/109 features (60%) - Enhanced Display Done
**By Week 15:** 70/109 features (64%) - Information Display Done
**By Week 19:** 95/109 features (87%) - Advanced Management Done
**By Week 21:** 104/109 features (95%) - Advanced REST API Done
**By Week 22:** 109/109 features (100%) - **COMPLETE** ✅

---

## 🚨 Risk Assessment

### High Risk
- Database migrations for advanced fields (test thoroughly on staging)
- Import/Export functionality (validate data carefully)
- Gutenberg block development (test with different block editor configurations)

### Medium Risk
- Auto-generation features (review for edge cases)
- Similar products algorithm (may need tuning for performance)
- Bulk operations (ensure no data corruption)

### Low Risk
- Display features (isolated impact)
- REST API endpoints (can be rolled back individually)
- Badge system (well-tested pattern)

### Mitigation
- Feature flags for all new features
- Staging environment testing before production
- Rollback plan for each phase
- Continuous monitoring after deployment

---

## 📝 Next Steps

1. **Review this plan** with development team
2. **Approve timeline** (22 weeks)
3. **Create feature branch** for Group 1
   - Branch: `feature/group1-core-basics`
4. **Start implementation** with Group 1 (Week 1-2)
5. **Track progress** in feature-requirements.md
6. **Update weekly** and adjust plan as needed

---

**IMPORTANT:** This implementation strategy applies to ALL future feature implementations. Follow this approach consistently for Section 2 (Categories), Section 3 (Tags), and all subsequent sections.

*Plan Updated: 2026-01-21*  
*Reference: plan/feature-requirements.md, docs/assistant-quality-standards.md, docs/assistant-performance-optimization.md*
