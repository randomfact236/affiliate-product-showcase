# Feature Requirements: Affiliate Digital Product Showcase

> **IMPORTANT RULE: NEVER DELETE THIS FILE**
> This file contains complete feature requirements for digital affiliate product plugin. All features must be implemented according to this plan.

> **SCOPE:** Digital products only (software, e-books, courses, templates, plugins, themes, digital art, etc.)

---

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

### Required Reference Files (ALWAYS USE):

1. **docs/assistant-instructions.md** - Project context, code change policy, git rules
2. **docs/assistant-quality-standards.md** - Enterprise-grade code quality requirements
3. **docs/assistant-performance-optimization.md** - Performance optimization guidelines

### Quality Standard: 10/10 Enterprise-Grade
- Fully/highly optimized, no compromises
- All code must meet hybrid quality matrix standards
- Essential standards at 10/10, performance goals as targets

---

# 📋 TAXONOMY-BASED IMPLEMENTATION PLAN

**Strategy:** Complete one taxonomy fully before moving to the next

## 📦 SECTION 1: PRODUCTS - START HERE

**What's Included:**
- ✅ All Basic Product Features (1-31)
- ✅ All Essential Advanced Product Features (A1-A30)
- ✅ **Total:** 61 features for initial launch

**Milestone:** Products fully functional with basic + essential advanced features

---

## 📂 SECTION 2: CATEGORIES

**What's Included:**
- ✅ All Basic Category Features (32-63)
- ✅ **Total:** 32 features for initial launch

**Milestone:** Categories fully functional with basic features

---

## 🏷️ SECTION 3: TAGS

**What's Included:**
- ✅ All Basic Tag Features (65-88)
- ✅ **Total:** 24 features for initial launch

**Milestone:** Tags fully functional with basic features

---

## 🎀 SECTION 4: RIBBONS

**What's Included:**
- ✅ All Basic Ribbon Features (89-111)
- ✅ **Total:** 23 features for initial launch

**Milestone:** Ribbons fully functional with basic features

---

## 🔗 SECTION 5: CROSS-FEATURES

**What's Included:**
- ✅ Integration Features (112-118)
- ✅ Display Features (119-126)
- ✅ Search & Filtering (127-139)
- ✅ Performance & Optimization (140-152)
- ✅ Security Features (153-159)
- ✅ Accessibility Features (160-169)
- ✅ Analytics & Reporting (170-176)
- ✅ Localization & Translation (195-201)
- ✅ **Total:** 66 features for initial launch

**Milestone:** All cross-taxonomy features implemented

---

## 🔌 SEO PLUGIN INTEGRATION (CRITICAL - Applies to ALL Sections)

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

**When to Implement:**
- Before implementing product display features
- Before implementing category/tag display features
- Before implementing product templates

**See Full Details:** `plan/seo-plugin-integration-requirements.md`

---

## ✅ SECTION 6: QUALITY & LAUNCH

**What's Included:**
- ✅ Documentation (202-211)
- ✅ Testing & Quality Assurance (212-221)
- ✅ **Total:** 20 features for initial launch

**Milestone:** Plugin tested, documented, and ready for launch

---

# 📊 IMPLEMENTATION SUMMARY

| Section | Taxonomy | Phase 1 Features | Phase 2 Features | Status |
|----------|-----------|------------------|------------------|---------|
| **Section 1** | Products | 61 (Basic + Essential) | ~60 (Future) | **START HERE** |
| **Section 2** | Categories | 32 (Basic) | ~30 (Future) | Pending |
| **Section 3** | Tags | 24 (Basic) | ~20 (Future) | Pending |
| **Section 4** | Ribbons | 23 (Basic) | ~20 (Future) | Pending |
| **Section 5** | Cross-Features | 66 | ~20 (Future) | Pending |
| **Section 6** | Quality & Launch | 20 | 0 | Pending |
| **TOTAL** | All Sections | **226** | **~150** | Phase 1 + Phase 2 |

**Total:** 226 features for Phase 1 launch + ~150 future improvements

---

# ✅ FEATURE LIST (Phase 1: BASIC & ESSENTIAL)

# SECTION 1: PRODUCTS

## Basic Level Features (Must Have)

### 1. Core Product Fields
- [x] 1. Product Title (required)
- [x] 2. Product Slug (auto-generated from title, editable)
- [x] 3. Product Description (textarea with WYSIWYG editor)
- [ ] 4. Product Short Description (short excerpt)
- [x] 5. Product Price (numeric, required)
- [x] 6. Affiliate URL (required, validates URL format)
- [x] 7. Featured Image (WordPress media uploader)
- [x] 8. Product Status (Draft, Published, Pending Review, Private)
- [x] 9. Publish Date (date picker)

### 2. Basic Product Display
- [x] 10. Single product page template
- [x] 11. Product grid/list view options
- [x] 12. Basic product card with image, title, price
- [x] 13. "Buy Now" / "View Deal" button linking to affiliate URL
- [x] 14. Product URL rel="nofollow sponsored" attributes
- [x] 15. Responsive design for mobile/tablet/desktop

### 3. Basic Product Management
- [x] 16. Add new product form (classic editor)
- [x] 17. Edit existing product form
- [x] 18. Delete product (move to trash)
- [ ] 19. Restore product from trash
- [ ] 20. Delete permanently
- [x] 21. Bulk actions: Reset Clicks, Export
- [x] 22. Search products by title/description
- [x] 23. Filter by status, brand

### 4. Basic REST API - Products
- [x] 24. GET `/v1/products` - List products (paginated)
- [x] 25. GET `/v1/products/{id}` - Get single product
- [x] 26. POST `/v1/products` - Create product
- [ ] 27. POST `/v1/products/{id}` - Update product
- [ ] 28. DELETE `/v1/products/{id}` - Delete product
- [ ] 29. POST `/v1/products/{id}/trash` - Trash product
- [ ] 30. POST `/v1/products/{id}/restore` - Restore product
- [ ] 31. DELETE `/v1/products/{id}/delete-permanently` - Permanent delete

## Essential Advanced Features (Must Have for Affiliate Marketing)

### A1. Essential Digital Product Fields
- [ ] A1. Original Price (for discount calculation)
- [ ] A2. Discount Percentage (auto-calculated)
- [ ] A3. Currency Selection (USD, EUR, GBP, etc.)
- [ ] A5. Platform Requirements (e.g., "WordPress 6.0+", "Python 3.8+", etc.)
- [ ] A7. Version Number (e.g., "1.0.0", "v2.5.1")
- [ ] A26. Product share buttons (social media)
- [ ] A29. Lazy loading for images

### A2. Essential Product Display Features
- [ ] A27. Product tabs (Description, Specs, FAQ, Requirements)

## 📦 PRODUCTS - Future Improvements

### F1. Enhanced Product Display Features (High Priority)
- [ ] F1. Quick view modal (AJAX)
- [ ] F2. Add to comparison list
- [ ] F3. Add to wishlist
- [ ] F4. Product zoom on image hover 
- [ ] F5. Product comparison chart
- [ ] F6. Print product page button
- [ ] F7. Recently viewed products 
- [ ] F8. Available/License indicator
- [ ] F9. Version/Update badge
- [ ] F10. Platform compatibility badge
- [ ] F11. Countdown timer for limited offers

### F2. Enhanced Product Management Features (Medium Priority)
- [ ] F13. Clone/Duplicate product
- [ ] F16. Product scheduling (auto-publish)
- [ ] F17. Product expiration (auto-unpublish)
- [ ] F18. Bulk price update (increase/decrease by %)
- [ ] F21. Product duplicate checker (by URL/ID)
- [ ] F23. Auto-extract product images from affiliate URL
- [ ] F24. Auto-fetch product details from affiliate URL
- [ ] F25. Product preview before publishing
- [ ] F26. Product change history/log
- [ ] F27. Product approval workflow (for multi-author sites)
- [ ] F28. Version history tracking
- [ ] F29. Release notes management
- [ ] F30. Gutenberg block editor support

### F3. Advanced Product REST API (Low Priority)
- [ ] F31. GET `/v1/products?featured=true` - Get featured products
- [ ] F32. GET `/v1/products?trial=true` - Get products with trial
- [ ] F33. GET `/v1/products?type={type}` - Filter by product type
- [ ] F34. GET `/v1/products?platform={req}` - Filter by platform requirements
- [ ] F35. GET `/v1/products?license={type}` - Filter by license type
- [ ] F36. GET `/v1/products?version_min={ver}` - Filter by minimum version
- [ ] F37. GET `/v1/products?updated_since={date}` - Get recently updated products
- [ ] F38. GET `/v1/products?on_sale=true` - Get products on sale
- [ ] F39. GET `/v1/products?search={term}` - Search products
- [ ] F40. GET `/v1/products?category={id}` - Filter by category
- [ ] F41. GET `/v1/products?tag={id}` - Filter by tag
- [ ] F42. GET `/v1/products?ribbon={id}` - Filter by ribbon
- [ ] F43. GET `/v1/products?min_price={price}` - Filter by min price
- [ ] F44. GET `/v1/products?max_price={price}` - Filter by max price
- [ ] F45. GET `/v1/products?sort={field}` - Sort products
- [ ] F46. GET `/v1/products?include={ids}` - Get specific products
- [ ] F47. GET `/v1/products/similar/{id}` - Get similar products
- [ ] F48. POST `/v1/products/bulk` - Bulk create/update
- [ ] F49. POST `/v1/products/{id}/duplicate` - Clone product
- [ ] F50. POST `/v1/products/import` - Import products
- [ ] F51. GET `/v1/products/export` - Export products

### F4. Review & Rating System (Low Priority - Optional)
- [ ] F52. Product Rating (0-5 stars)
- [ ] F53. Review Count
- [ ] F54. Product review form
- [ ] F55. Product rating display

### F5. Download/File Management (Low Priority - Not Needed for Affiliate)
- [ ] F56. File Format field
- [ ] F57. File Size (in MB/GB)
- [ ] F58. Download Type (Direct Link, External Platform, License Key)
- [ ] F59. Download URL or Platform Link
- [ ] F60. Download Expiration
- [ ] F61. Download Limit
- [ ] F62. Trial Available field
- [ ] F63. DRM Protection field

### Future Product Type Fields
- [ ] A4. Product Type (Software, E-book, Course, Template, Plugin, Theme, Digital Art, Audio, Video, Other)
- [ ] A6. License Type (Single Use, Unlimited, Commercial, Personal, Educational)
- [ ] A8. Demo/Preview URL
- [ ] A9. Documentation URL
- [ ] A10. Support URL
- [ ] A11. Product Brand/Manufacturer
- [ ] A12. Product Video URL (YouTube, Vimeo embed)
- [ ] A13. Product Gallery (multiple images)
- [ ] A15. Product FAQ Section (accordion)
- [ ] A16. Product Specifications Table (digital specs)
- [ ] A17. Product Release Date
- [ ] A18. Product Expiration Date (if applicable)
- [ ] A19. Product Language (for courses, e-books)

### Future Product Display Features
- [ ] A20. Discount badge with percentage
- [ ] A21. "On Sale" indicator
- [ ] A22. "New Arrival" badge
- [ ] A23. "Best Seller" badge
- [ ] A24. Related products section
- [ ] A25. Products you may also like
- [ ] A28. Sticky "Buy Now" button on scroll
- [ ] A30. Video preview in gallery

---

# SECTION 2: CATEGORIES

## Basic Level Features (Must Have)

### 5. Core Category Fields
- [ ] 32. Category Name (required)
- [ ] 33. Category Slug (auto-generated, editable)
- [ ] 35. Parent Category (dropdown)
- [ ] 43. Product count per category

### 6. Basic Category Display
- [ ] 39. Category listing page
- [ ] 44. Category tree/hierarchy view
- [ ] 45. Responsive design

### 7. Basic Category Management
- [ ] 46. Add new category form
- [ ] 47. Edit existing category
- [ ] 48. Delete category (move to trash)
- [ ] 49. Restore category from trash
- [ ] 50. Delete permanently
- [ ] 51. Bulk actions: Delete, Featured toggle
- [ ] 52. Quick edit (name, slug, description)
- [ ] 53. Drag-and-drop reordering
- [ ] 54. Category search

### 8. Basic REST API - Categories
- [ ] 55. GET `/v1/categories` - List categories
- [ ] 56. GET `/v1/categories/{id}` - Get single category
- [ ] 57. POST `/v1/categories` - Create category
- [ ] 58. POST `/v1/categories/{id}` - Update category
- [ ] 59. DELETE `/v1/categories/{id}` - Delete category
- [ ] 60. POST `/v1/categories/{id}/trash` - Trash category
- [ ] 61. POST `/v1/categories/{id}/restore` - Restore category
- [ ] 62. DELETE `/v1/categories/{id}/delete-permanently` - Permanent delete
- [ ] 63. POST `/v1/categories/trash/empty` - Empty trash

## 📂 CATEGORIES - Future Improvements

### C1. Advanced Category Fields (Medium Priority)
- [ ] C1. Category Order (numeric, display priority)
- [ ] C2. Featured Category (checkbox)
- [ ] C3. Hide from Menu (checkbox)
- [ ] C4. Hide from Homepage (checkbox)
- [ ] C5. SEO Title (custom meta title)
- [ ] C6. SEO Description (custom meta description)
- [ ] C7. Category Banner Image (full-width banner)
- [ ] C8. Category Background Color (hex)
- [ ] C9. Category Text Color (hex)
- [ ] C10. Category Icon Type (emoji, SVG, or image)
- [ ] C12. Category Layout Style (grid, list, masonry)
- [ ] C13. Products per page (for this category)
- [ ] C15. Category Widget Title Override
- [ ] C17. Category RSS Feed URL
- [ ] C18. Category Last Updated Date

### C2. Advanced Category Display (Low Priority)
- [ ] C19. Category mega menu
- [ ] C20. Category dropdown with product preview
- [ ] C21. Category widget with icons
- [ ] C22. Category carousel/slider
- [ ] C23. Category filter sidebar
- [ ] C24. Category breadcrumb with icons
- [ ] C25. Category banner with CTA
- [ ] C26. Featured products in category
- [ ] C27. Subcategory grid
- [ ] C28. Category count badges
- [ ] C29. Category icon/color hover effects
- [ ] C30. Category description popup
- [ ] C31. Category tree expand/collapse
- [ ] C32. Category search autocomplete
- [ ] C33. Category quick filter chips
- [ ] C34. Category featured products slider
- [ ] C35. Category products sorting options
- [ ] C36. Category products view toggle
- [ ] C37. Category products per page selector
- [ ] C38. Category lazy loading

### C3. Advanced Category Management (Low Priority)
- [ ] C39. Quick edit modal
- [ ] C40. Bulk actions: Set featured, Set order, Toggle menu
- [ ] C41. Bulk category image upload
- [ ] C42. Category cloning
- [ ] C43. Category merging (move products to another)
- [ ] C44. Category export (CSV)
- [ ] C45. Category import (CSV)
- [ ] C46. Category drag-and-drop ordering
- [ ] C47. Category reordering by priority
- [ ] C48. Category duplicate checker
- [ ] C49. Category change history
- [ ] C50. Category approval workflow
- [ ] C51. Auto-generate category from product tags
- [ ] C52. Bulk category icon assignment
- [ ] C53. Bulk category color assignment
- [ ] C54. Category template selector
- [ ] C55. Category custom fields support

### C4. Advanced Category REST API (Low Priority)
- [ ] C56. GET `/v1/categories?featured=true` - Get featured categories
- [ ] C57. GET `/v1/categories?type={type}` - Filter by digital product type
- [ ] C58. GET `/v1/categories?search={term}` - Search categories
- [ ] C59. GET `/v1/categories?parent={id}` - Get subcategories
- [ ] C60. GET `/v1/categories?hide_empty=true` - Filter empty
- [ ] C61. GET `/v1/categories?include={ids}` - Get specific categories
- [ ] C62. GET `/v1/categories/tree` - Get category tree
- [ ] C63. POST `/v1/categories/bulk` - Bulk create/update
- [ ] C64. POST `/v1/categories/{id}/duplicate` - Clone category
- [ ] C65. POST `/v1/categories/{id}/merge` - Merge categories
- [ ] C66. GET `/v1/categories/popular` - Get most popular
- [ ] C67. GET `/v1/categories/{id}/products` - Get products in category

### Future Category Fields
- [ ] 34. Category Description (textarea)
- [ ] 36. Category Icon (emoji or SVG)
- [ ] 37. Category Color (hex color picker)
- [ ] 38. Category Image (WordPress media uploader)

### Future Category Display Features
- [ ] 40. Category card with icon/color/image
- [ ] 41. Breadcrumb navigation
- [ ] 42. Category description display

---


# SECTION 3: TAGS

## Basic Level Features (Must Have)

### 9. Core Tag Fields
- [ ] 65. Tag Name (required)
- [ ] 66. Tag Slug (auto-generated, editable)
- [ ] 68. Tag Color (hex color picker)
- [ ] 69. Tag Icon (emoji or SVG)

### 10. Basic Tag Display
- [ ] 70. Tag listing page
- [ ] 71. Tag cloud widget
- [ ] 73. Tag filtering in product list and connect with with sort by list also
- [ ] 76. Responsive design

### 11. Basic Tag Management
- [ ] 77. Add new tag form
- [ ] 78. Edit existing tag
- [ ] 79. Delete tag
- [ ] 80. Bulk actions: Delete
- [ ] 81. Quick edit (name, slug, color)
- [ ] 82. Tag search
- [ ] 83. Tag merging

### 12. Basic REST API - Tags
- [ ] 84. GET `/v1/tags` - List tags
- [ ] 85. GET `/v1/tags/{id}` - Get single tag
- [ ] 86. POST `/v1/tags` - Create tag
- [ ] 87. POST `/v1/tags/{id}` - Update tag
- [ ] 88. DELETE `/v1/tags/{id}` - Delete tag

## 🏷️ TAGS - Future Improvements

### T1. Enhanced Tag Fields (Medium Priority)
- [ ] T1. Featured Tag (checkbox)
- [ ] T2. Tag Order (numeric, display priority)
- [ ] T3. Tag Icon Type (emoji, SVG, or image)
- [ ] T4. Tag Background Color (hex)
- [ ] T5. Tag Text Color (hex)
- [ ] T6. Tag Border Radius (px or %)
- [ ] T7. Tag Border Style (solid, dashed, dotted)
- [ ] T8. Tag Border Color (hex)
- [ ] T9. Tag Hover Effect (color change, scale, glow)
- [ ] T10. Tag Font Size (px)
- [ ] T11. Tag Font Weight (normal, bold)
- [ ] T12. Tag Uppercase (checkbox)
- [ ] T13. Tag Widget Title Override
- [ ] T14. Tag Shortcode (for embedding)
- [ ] T15. Tag RSS Feed URL

### T2. Advanced Tag Display (Low Priority)
- [ ] T16. Tag cloud with different sizes
- [ ] T17. Tag color variations
- [ ] T18. Tag icon variations
- [ ] T19. Tag grouping (by category, popularity)
- [ ] T21. Tag autocomplete in product edit
- [ ] T22. Tag quick select chips
- [ ] T23. Tag search in product form
- [ ] T24. Tag suggestions (based on product title)
- [ ] T25. Tag trending (most used)
- [ ] T26. Tag new (recently added)
- [ ] T27. Tag related (auto-suggest)
- [ ] T28. Tag hover tooltips
- [ ] T29. Tag badges with icons
- [ ] T30. Tag badges with color
- [ ] T31. Tag badges with count
- [ ] T33. Tag carousel/slider
- [ ] T34. Tag grid display
- [ ] T35. Tag list display

### T3. Advanced Tag Management (Low Priority)
- [ ] T36. Quick edit modal
- [ ] T37. Bulk actions: Set featured, Set color
- [ ] T38. Bulk tag icon assignment
- [ ] T39. Tag cloning
- [ ] T40. Tag merging
- [ ] T41. Tag export (CSV)
- [ ] T42. Tag import (CSV)
- [ ] T43. Tag reordering by priority
- [ ] T44. Tag duplicate checker
- [ ] T45. Tag change history
- [ ] T46. Auto-generate tags from product title
- [ ] T47. Auto-extract tags from product description
- [ ] T48. Tag synonym support
- [ ] T49. Tag parent/child relationships
- [ ] T50. Tag custom fields support
- [ ] T51. Tag permission management

### T4. Advanced Tag REST API (Low Priority)
- [ ] T52. GET `/v1/tags?featured=true` - Get featured tags
- [ ] T53. GET `/v1/tags?search={term}` - Search tags
- [ ] T54. GET `/v1/tags?popular=true` - Get popular tags
- [ ] T55. GET `/v1/tags?recent=true` - Get recent tags
- [ ] T56. GET `/v1/tags?include={ids}` - Get specific tags
- [ ] T57. POST `/v1/tags/bulk` - Bulk create/update
- [ ] T58. POST `/v1/tags/{id}/duplicate` - Clone tag
- [ ] T59. POST `/v1/tags/{id}/merge` - Merge tags
- [ ] T60. GET `/v1/tags/{id}/products` - Get products with tag
- [ ] T61. GET `/v1/tags/cloud` - Get tag cloud data
- [ ] T62. GET `/v1/tags/suggest/{term}` - Get tag suggestions

### Future Tag Fields
- [ ] 67. Tag Description (textarea)

### Future Tag Display Features
- [ ] 72. Tag badges on products
- [ ] 74. Tag description display
- [ ] 75. Product count per tag

---


# SECTION 4: RIBBONS

## Basic Level Features (Must Have)

### 13. Core Ribbon Fields
- [ ] 89. Ribbon Name (required, internal ID)
- [ ] 90. Ribbon Text (display text, e.g., "Best Seller")
- [ ] 91. Ribbon Background Color (hex)
- [ ] 92. Ribbon Text Color (hex)
- [ ] 93. Ribbon Position (top-left, top-right, bottom-left, bottom-right)

### 14. Basic Ribbon Display
- [ ] 94. Ribbon badge on product image
- [ ] 95. Ribbon positioning options
- [ ] 96. Ribbon text display
- [ ] 97. Ribbon color customization
- [ ] 98. Responsive ribbon size
- [ ] 99. Ribbon animation on hover

### 15. Basic Ribbon Management
- [ ] 100. Add new ribbon form
- [ ] 101. Edit existing ribbon
- [ ] 102. Delete ribbon
- [ ] 103. Bulk actions: Delete
- [ ] 104. Quick edit (text, colors, position)
- [ ] 105. Ribbon search
- [ ] 106. Ribbon preview

### 16. Basic REST API - Ribbons
- [ ] 107. GET `/v1/ribbons` - List ribbons
- [ ] 108. GET `/v1/ribbons/{id}` - Get single ribbon
- [ ] 109. POST `/v1/ribbons` - Create ribbon
- [ ] 110. POST `/v1/ribbons/{id}` - Update ribbon
- [ ] 111. DELETE `/v1/ribbons/{id}` - Delete ribbon

## 🎀 RIBBONS - Future Improvements

### R1. Enhanced Ribbon Fields (Medium Priority)
- [ ] R1. Ribbon Style (badge, corner, banner, diagonal)
- [ ] R2. Ribbon Icon (SVG or emoji)
- [ ] R3. Ribbon Priority (numeric, display order)
- [ ] R4. Ribbon Start Date (scheduled display)
- [ ] R5. Ribbon Expiration Date (scheduled removal)
- [ ] R6. Ribbon Border Color (hex)
- [ ] R7. Ribbon Border Width (px)
- [ ] R8. Ribbon Border Radius (px or %)
- [ ] R9. Ribbon Font Size (px)
- [ ] R10. Ribbon Font Weight (normal, bold)
- [ ] R11. Ribbon Shadow (checkbox)
- [ ] R12. Ribbon Shadow Color (hex)
- [ ] R13. Ribbon Shadow Blur (px)
- [ ] R14. Ribbon Shadow Offset (x, y)
- [ ] R15. Ribbon Animation (fade, slide, bounce, pulse)
- [ ] R16. Ribbon Animation Speed (slow, normal, fast)
- [ ] R17. Ribbon Click Action (link to URL, open modal, none)
- [ ] R18. Ribbon Click URL (external link)
- [ ] R19. Ribbon Hover Effect (scale, rotate, glow)
- [ ] R20. Ribbon Hover Color (hex)

### R2. Advanced Ribbon Display (Low Priority)
- [ ] R21. Ribbon style variations (badge, corner, banner, diagonal)
- [ ] R22. Ribbon icon integration
- [ ] R23. Ribbon scheduling (start/end dates)
- [ ] R24. Ribbon priority system (multiple ribbons)
- [ ] R25. Ribbon stacking (multiple ribbons on same product)
- [ ] R26. Ribbon animation effects
- [ ] R27. Ribbon hover effects
- [ ] R28. Ribbon click actions
- [ ] R29. Ribbon responsive sizing
- [ ] R30. Ribbon device-specific display
- [ ] R31. Ribbon product filtering
- [ ] R32. Ribbon conditional display (based on category, tag, price)
- [ ] R33. Ribbon countdown timer (for time-limited offers)
- [ ] R34. Ribbon progress bar (for stock limits)
- [ ] R35. Ribbon gradient backgrounds
- [ ] R36. Ribbon pattern backgrounds
- [ ] R37. Ribbon glowing effect
- [ ] R38. Ribbon 3D effect
- [ ] R39. Ribbon parallax effect

### R3. Advanced Ribbon Management (Low Priority)
- [ ] R40. Quick edit modal
- [ ] R41. Bulk actions: Set priority, Duplicate
- [ ] R42. Ribbon cloning
- [ ] R43. Ribbon templates (preset styles)
- [ ] R44. Ribbon import/export (JSON)
- [ ] R45. Ribbon preview in product grid
- [ ] R46. Ribbon reordering by priority
- [ ] R47. Ribbon duplicate checker
- [ ] R48. Ribbon change history
- [ ] R49. Ribbon scheduling dashboard
- [ ] R50. Bulk ribbon assignment (to products)
- [ ] R51. Ribbon analytics (views, clicks)
- [ ] R52. Ribbon A/B testing
- [ ] R53. Ribbon custom fields support
- [ ] R54. Ribbon permission management

### R4. Advanced Ribbon REST API (Low Priority)
- [ ] R55. GET `/v1/ribbons?active=true` - Get active ribbons
- [ ] R56. GET `/v1/ribbons?style={style}` - Filter by style
- [ ] R57. GET `/v1/ribbons?priority={order}` - Sort by priority
- [ ] R58. GET `/v1/ribbons?scheduled=true` - Get scheduled ribbons
- [ ] R59. GET `/v1/ribbons?expired=true` - Get expired ribbons
- [ ] R60. GET `/v1/ribbons/{id}/products` - Get products with ribbon
- [ ] R61. POST `/v1/ribbons/bulk` - Bulk create/update
- [ ] R62. POST `/v1/ribbons/{id}/duplicate` - Clone ribbon
- [ ] R63. POST `/v1/ribbons/templates` - Get ribbon templates
- [ ] R64. POST `/v1/ribbons/{id}/schedule` - Schedule ribbon

---



# SECTION 5: CROSS-FEATURES

## 17. Integration Features
- [x] 112. Product-Category relationship (many-to-many)
- [x] 113. Product-Tag relationship (many-to-many)
- [x] 114. Product-Ribbon relationship (one-to-one or one-to-many)
- [ ] 115. Category-Tag inheritance (products in category inherit tags)
- [ ] 116. Ribbon auto-assignment (by category, tag, price range)
- [ ] 117. Cross-referencing (related products by category/tag)
- [ ] 118. Filtering combinations (category + tag + ribbon)

## 18. Display Features
- [ ] 119. Shortcode: `[products]` - Display products
- [ ] 120. Shortcode: `[product id="1"]` - Display single product
- [ ] 121. Shortcode: `[category id="1"]` - Display category products
- [ ] 122. Shortcode: `[tag id="1"]` - Display tag products
- [ ] 123. Shortcode: `[ribbon id="1"]` - Display ribbon products
- [ ] 124. Gutenberg Blocks: Product Grid, Product List, Category Display, Tag Cloud
- [ ] 125. Widgets: Product Slider, Category Widget, Tag Cloud, Featured Products
- [ ] 126. REST API for frontend consumption

## 19. Search & Filtering
- [ ] 127. Global product search (AJAX)
- [ ] 128. Filter by multiple categories
- [ ] 129. Filter by multiple tags
- [ ] 130. Filter by ribbons
- [ ] 131. Filter by price range
- [ ] 132. Filter by product type (Software, E-book, Course, etc.)
- [ ] 133. Filter by platform requirements
- [ ] 134. Filter by license type (Single Use, Unlimited, etc.)
- [ ] 135. Filter by date range
- [ ] 136. Advanced search filters sidebar
- [ ] 137. Search suggestions autocomplete
- [ ] 138. Search result highlighting
- [ ] 139. Faceted search

## 20. Performance & Optimization
- [x] 140. Image lazy loading
- [ ] 141. Code splitting for JavaScript
- [x] 142. CSS minification (Tailwind)
- [x] 143. JavaScript minification (Vite)
- [x] 144. Database query caching
- [ ] 145. Object caching (WP Redis, Memcached)
- [ ] 146. CDN integration for assets
- [ ] 147. Database indexing optimization
- [ ] 148. REST API response caching
- [ ] 149. Edge caching support
- [ ] 150. Critical CSS extraction
- [ ] 151. Critical JS prioritization
- [ ] 152. Image optimization (WebP, AVIF)

## 21. Security Features
- [ ] 153. CSRF protection on all forms
- [ ] 154. Nonce verification on AJAX requests
- [x] 155. Input sanitization
- [x] 156. Output escaping
- [x] 157. SQL injection prevention
- [x] 158. XSS prevention
- [x] 159. Rate limiting on API endpoints

## 22. Accessibility Features
- [ ] 160. WCAG 2.1 AA compliance
- [ ] 161. Keyboard navigation support
- [ ] 162. Screen reader support (ARIA labels)
- [ ] 163. Focus indicators
- [ ] 164. Color contrast (4.5:1 minimum)
- [ ] 165. Alt text for all images
- [ ] 166. Skip to content links
- [ ] 167. Semantic HTML structure
- [ ] 168. Language attributes
- [ ] 169. Accessibility testing with NVDA/JAWS

## 23. Analytics & Reporting (Essential for Affiliate Marketing)
- [ ] 170. WordPress hooks for external analytics plugins (e.g., `aps_product_viewed`, `aps_affiliate_clicked`, `aps_conversion_tracked`)
- [ ] 171. Basic event data storage (for custom integrations)
- [ ] 172. Documentation for analytics plugin integration
- [ ] 173. Example code for Google Analytics
- [ ] 174. Example code for affiliate network tracking
- [ ] 175. Click tracking for affiliate links
- [ ] 176. Conversion tracking (goal completion)

**Recommended External Analytics Plugins:**
- Google Analytics by MonsterInsights
- Analytify
- ExactMetrics
- Site Kit by Google
- WP Statistics

## 24. Localization & Translation
- [ ] 195. Translation-ready (.pot file)
- [ ] 196. Translations: English (en_US)
- [ ] 197. RTL (Right-to-Left) support (optional)
- [ ] 198. Currency formatting
- [ ] 199. Date/time formatting
- [ ] 200. Number formatting
- [ ] 201. Language selector in admin

# SECTION 6: QUALITY & LAUNCH

## 27. Documentation
- [ ] 202. User manual (PDF/HTML)
- [ ] 203. Shortcode reference guide
- [ ] 204. Troubleshooting guide
- [ ] 205. FAQ section (digital product specific)
- [ ] 206. Video tutorials
- [ ] 207. Code examples
- [ ] 208. Changelog

## 28. Testing & Quality Assurance
- [ ] 209. Unit tests (PHPUnit)
- [ ] 210. Integration tests
- [ ] 211. Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] 212. Mobile testing (iOS, Android)
- [ ] 213. Performance testing (Lighthouse)
- [ ] 214. Security testing (Snyk, WPScan)
- [ ] 215. Accessibility testing (axe DevTools)
- [ ] 216. Code quality checks (PHPCS, PHPStan, Psalm)
- [ ] 217. Code coverage reporting (minimum 90%)
- [ ] 218. E2E tests (Playwright)
- [ ] 219. SEO validation testing
- [ ] 220. Schema.org structured data testing
- [ ] 221. Analytics tracking verification

---

# 🚀 IMPLEMENT NOW: BASIC FEATURES

**Status:** Ready for immediate implementation
**Priority:** High - These are marked as "Basic" and should be implemented now

---

## Product Management Features (Basic)

### F12. Quick Edit in Product List
**Implementation Tasks:**
- Add "Quick Edit" button to products list row actions
- Create AJAX modal for quick editing (title, slug, price, status)
- Add `wp_ajax_aps_product_quick_edit` action
- Implement nonce verification
- Update product on save without page refresh

**Files to Modify:**
- `src/Admin/ProductsList.php` - Add quick edit action
- `src/Admin/AjaxHandlers.php` - Handle quick edit AJAX
- `assets/js/admin-products.js` - Quick edit modal logic

---

### F14. Import Products (CSV/XML)
**Implementation Tasks:**
- Create admin menu page: "Import Products"
- Add file upload form for CSV/XML files
- Parse CSV/XML file format validation
- Map fields: title, slug, price, affiliate_url, image, category, tags
- Create products from imported data
- Show import progress and results
- Add import error logging

**Files to Create:**
- `src/Admin/ImportExport.php` - Import/export handler
- `templates/admin/import-products.php` - Import page template
- `assets/js/admin-import.js` - Import form logic

**API Endpoints:**
- POST `/v1/products/import` - Accept file upload
- Return import results (success, errors, warnings)

---

### F15. Export Products (CSV/XML)
**Implementation Tasks:**
- Create export form with filters (by category, tag, date range)
- Generate CSV/XML file with all product data
- Include meta fields: price, original_price, affiliate_url, clicks, conversions
- Offer downloadable file
- Add export progress for large datasets

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add export handler
- `templates/admin/export-products.php` - Export page template

**API Endpoints:**
- GET `/v1/products/export` - Generate downloadable file
- Query params: `format=csv|xml`, `category_id`, `tag_id`, `date_from`, `date_to`

---

### F19. Bulk Category/Tag Assignment
**Implementation Tasks:**
- Add "Assign Categories" bulk action to products list
- Add "Assign Tags" bulk action to products list
- Create AJAX modal with category/tag checkboxes
- Implement bulk update logic
- Show success message after assignment

**Files to Modify:**
- `src/Admin/ProductsList.php` - Add bulk actions
- `src/Admin/BulkActions.php` - Handle bulk assignments
- `assets/js/admin-products.js` - Bulk action modal

---

### F20. Bulk Ribbon Assignment
**Implementation Tasks:**
- Add "Assign Ribbon" bulk action to products list
- Create dropdown to select ribbon
- Implement bulk ribbon update
- Remove previous ribbons before assigning new one

**Files to Modify:**
- `src/Admin/ProductsList.php` - Add ribbon bulk action
- `src/Admin/BulkActions.php` - Handle ribbon assignment

---

### F22. Auto-Generate Slugs from Title
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

## Category Features (Basic)

### C11. Category Featured Products
**Implementation Tasks:**
- Add "Featured Products" field to category edit form
- Create term meta: `aps_category_featured_products`
- Store array of product IDs
- Display featured products first in category template
- Add drag-and-drop ordering for featured products

**Files to Modify:**
- `src/Admin/CategoryFields.php` - Add featured products field
- `templates/single-category.php` - Show featured products first
- `assets/js/admin-categories.js` - Drag-and-drop ordering

**API Usage:**
```php
$featured_products = get_term_meta($category_id, 'aps_category_featured_products', true);
```

---

### C14. Default Sort Order
**Implementation Tasks:**
- Add "Default Sort Order" dropdown to category edit form
- Options: name, price, date, popularity, random
- Store in term meta: `aps_category_sort_order`
- Apply sort order in category query
- Allow frontend override via URL param

**Files to Modify:**
- `src/Admin/CategoryFields.php` - Add sort order field
- `src/Repositories/CategoryRepository.php` - Apply sort order

**Sort Options:**
- `name` - Alphabetical (A-Z)
- `price` - Price (low to high)
- `date` - Date (newest first)
- `popularity` - Most viewed/clicked
- `random` - Random order

---

### C16. Category Shortcode
**Implementation Tasks:**
- Create shortcode: `[category id="1" limit="10"]`
- Parse shortcode attributes: id, limit, orderby, order
- Query products in category
- Render category product list
- Return HTML output

**Files to Create:**
- `src/Shortcodes/CategoryShortcode.php`

**Shortcode Usage:**
```php
add_shortcode('category', function($atts) {
    $atts = shortcode_atts([
        'id' => 0,
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
        'view' => 'grid'
    ], $atts);
    
    // Query products and render
    return $this->render_category_products($atts);
});
```

---

## Tag Features (Basic)

### T20. Tag Filter Sidebar
**Implementation Tasks:**
- Create sidebar widget: "Filter by Tag"
- Display all tags as checkboxes
- Allow multiple tag selection
- Filter product list on selection
- Show product count per tag

**Files to Create:**
- `src/Widgets/TagFilterWidget.php` - Widget class
- `templates/widget-tag-filter.php` - Widget template
- `assets/js/tag-filter.js` - Filter logic

**Widget Options:**
- Title (default: "Filter by Tags")
- Show product count (checkbox)
- Display style (checkboxes or links)

---

### T32. Tag Filtering Widget in Sidebar
**Implementation Tasks:**
- Similar to T20 but for sidebar context
- Create reusable tag filter component
- Support both sidebar and widget areas
- Add AJAX filtering without page reload

**Files to Create:**
- `src/Widgets/TagWidget.php` - Main widget
- `templates/widget-tags.php` - Tag list template
- `assets/js/widget-tags.js` - Widget filtering

**Widget Options:**
- Title
- Limit (number of tags to show)
- Show product count
- Display order (name, count, random)

---

## Cross-Features: Import/Export (Basic)

### X1. Import Products from CSV
- **Already covered in F14 above**

### X2. Import Products from XML
**Implementation Tasks:**
- Extend import handler to support XML format
- Parse XML using SimpleXML
- Map XML nodes to product fields
- Validate XML schema
- Handle XML namespaces
- Import categories and tags from XML

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add XML parser

---

### X3. Export Products to CSV
- **Already covered in F15 above**

### X4. Export Products to XML
**Implementation Tasks:**
- Extend export handler to support XML format
- Generate XML with proper structure
- Include all product meta fields
- Add XML declaration and root element
- Format for external systems

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add XML generator

---

### X5. Import Categories from CSV
**Implementation Tasks:**
- Create import form for categories
- Parse CSV with: name, slug, description, parent_id
- Create terms in `aps_category` taxonomy
- Set category hierarchy (parent/child)
- Import category meta (if present)
- Show import results

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add category import

---

### X6. Export Categories to CSV
**Implementation Tasks:**
- Query all categories
- Generate CSV with: id, name, slug, description, parent_id, product_count
- Include category meta fields
- Offer downloadable file

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add category export

---

### X7. Import Tags from CSV
**Implementation Tasks:**
- Create import form for tags
- Parse CSV with: name, slug, description, color, icon
- Create terms in `aps_tag` taxonomy
- Import tag meta (color, icon)
- Show import results

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add tag import

---

### X8. Export Tags to CSV
**Implementation Tasks:**
- Query all tags
- Generate CSV with: id, name, slug, description, color, icon, product_count
- Include tag meta fields
- Offer downloadable file

**Files to Modify:**
- `src/Admin/ImportExport.php` - Add tag export

---

## Summary: Implement Now Features

**Total Features:** 18 basic features ready for immediate implementation

**Priority Order:**
1. **F12, F19, F20** - Quick edit and bulk actions (highest impact)
2. **F14, F15** - Import/Export products (high value)
3. **C11, C14, C16** - Category enhancements (medium impact)
4. **T20, T32** - Tag filtering widgets (medium impact)
5. **F22** - Auto-generate slugs (automation/UX)
6. **X1-X8** - Import/Export extensions (value add)

**Estimated Effort:** 3-5 days for all features

---

# 📅 FUTURE IMPROVEMENTS (Phase 2+)

**Status:** Not for initial launch - can be implemented in future versions
**Priority:** Low to Medium

---



## 🔗 CROSS-FEATURES - Future Improvements

### X2. Additional Localization (Low Priority - Optional)
- [ ] X22. Translations: Additional languages (Spanish, French, German, etc.)
- [ ] X23. Multi-language support (beyond English)

### X9. Bulk Image Upload
- [ ] X9. Bulk image upload

### X10. Mapping Field Validation
- [ ] X10. Mapping field validation

### X11. Import Error Logging
- [ ] X11. Import error logging

### X12. Network-wide product sharing (multi-site)
- [ ] X12. Network-wide product sharing (multi-site)

### X13. Network-wide category sharing (multi-site)
- [ ] X13. Network-wide category sharing (multi-site)

### X14. Network-wide tag sharing (multi-site)
- [ ] X14. Network-wide tag sharing (multi-site)

### X15. Network-wide ribbon sharing (multi-site)
- [ ] X15. Network-wide ribbon sharing (multi-site)

### X16. Site-specific overrides (multi-site)
- [ ] X16. Site-specific overrides (multi-site)

### X17. Cross-site product cloning (multi-site)
- [ ] X17. Cross-site product cloning (multi-site)

### X18. Network admin dashboard (multi-site)
- [ ] X18. Network admin dashboard (multi-site)

### X19. IP-based access control
- [ ] X19. IP-based access control

### X20. User capability checks
- [ ] X20. User capability checks

### X21. Audit logging for admin actions
- [ ] X21. Audit logging for admin actions

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
- ✅ Document any new classes in this file
- ❌ Do NOT create new classes without updating this reference

---

**Status Tracking:**

- **Section 1: Products:** 26/61 complete (~43%) for Phase 1
  - ✅ Core Infrastructure: 9/9 complete (100%)
  - ✅ Basic Display: 5/6 complete (83%)
  - ✅ Templates: 2/2 complete (100%)
  - ✅ Basic Management: 6/8 complete (75%)
  - ✅ Basic REST API: 3/8 complete (38%)
  - ✅ Admin Columns: Price, SKU, Brand, Rating, Clicks, Conversions (5/5)
  - ✅ Admin Filters: Brand dropdown
  - ✅ Analytics: Click count, Conversion count (last 30 days)
  - ❌ Essential Advanced Features: 0/30 complete (0%)
  - ❌ Future Improvements: 0/63 complete (0%)
  
  **Completed:**
  - [x] 1. Product Title
  - [x] 2. Product Slug
  - [x] 3. Product Description
  - [x] 5. Product Price
  - [x] 6. Affiliate URL
  - [x] 7. Featured Image
  - [x] 8. Product Status
  - [x] 9. Publish Date
  - [x] 10. Single product page template
  - [x] 11. Product grid/list view options
  - [x] 12. Basic product card with image, title, price
  - [x] 13. "Buy Now" / "View Deal" button
  - [x] 14. Product URL rel="nofollow sponsored" attributes
  - [x] 15. Responsive design
  - [x] 16. Add new product form
  - [x] 17. Edit existing product form
  - [x] 18. Delete product
  - [x] 21. Bulk actions: Reset Clicks, Export to CSV
  - [x] 22. Search products by title/description
  - [x] 23. Filter by status, brand
  - [x] 24. GET `/v1/products` - List products
  - [x] 25. GET `/v1/products/{id}` - Get single product
  - [x] 26. POST `/v1/products` - Create product
  - [x] 112. Product-Category relationship
  - [x] 113. Product-Tag relationship
  - [x] 114. Product-Ribbon relationship
  - [x] 140. Image lazy loading
  - [x] 142. CSS minification (Tailwind)
  - [x] 143. JavaScript minification (Vite)
  - [x] 144. Database query caching
  - [x] 152. Input sanitization
  - [x] 153. Output escaping
  - [x] 154. SQL injection prevention
  - [x] 155. XSS prevention
  - [x] 156. Rate limiting on API endpoints
  
  **Missing/Incomplete:**
  - [ ] 4. Product Short Description (short excerpt)
  - [ ] 19. Restore product from trash
  - [ ] 20. Delete permanently
  - [ ] 27. POST `/v1/products/{id}` - Update product
  - [ ] 28. DELETE `/v1/products/{id}` - Delete product
  - [ ] 29. POST `/v1/products/{id}/trash` - Trash product
  - [ ] 30. POST `/v1/products/{id}/restore` - Restore product
  - [ ] 31. DELETE `/v1/products/{id}/delete-permanently` - Permanent delete
  - [ ] A1-A30. All 30 Essential Advanced Product Features

- **Section 2: Categories:** [0]/32 complete (0%) for Phase 1
- **Section 3: Tags:** [0]/24 complete (0%) for Phase 1
- **Section 4: Ribbons:** [0]/23 complete (0%) for Phase 1
- **Section 5: Cross-Features:** 18/66 complete (~27%) for Phase 1
- **Section 6: Quality & Launch:** [0]/20 complete (0%) for Phase 1
- **Overall Progress:** ~44/226 complete (~19%) for Phase 1 launch

**Last Updated:** 2026-01-24  
**Version:** 4.0.0 (Reorganized: Implement Now + Future Improvements)  
**Maintainer:** Development Team
