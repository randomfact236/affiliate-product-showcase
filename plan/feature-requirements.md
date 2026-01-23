# Feature Requirements: Affiliate Product Showcase

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

# 📋 TAXONOMY-BASED IMPLEMENTATION PLAN

**Strategy:** Complete one taxonomy fully before moving to the next

## 📦 SECTION 1: PRODUCTS - START HERE

**What's Included:**
- ✅ All Basic Product Features (1-31)
- ✅ All Advanced Product Features (P1-P78)
- ✅ **Total:** 109 features

**Milestone:** Products fully functional with basic + advanced features

---

## 📂 SECTION 2: CATEGORIES

**What's Included:**
- ✅ All Basic Category Features (32-63)
- ✅ All Advanced Category Features (C1-C66)
- ✅ **Total:** 32 + 66 = 98 features

**Milestone:** Categories fully functional with basic + advanced features

---

## 🏷️ SECTION 3: TAGS

**What's Included:**
- ✅ All Basic Tag Features (65-88)
- ✅ All Advanced Tag Features (T1-T62)
- ✅ **Total:** 24 + 62 = 86 features

**Milestone:** Tags fully functional with basic + advanced features

---

## 🎀 SECTION 4: RIBBONS

**What's Included:**
- ✅ All Basic Ribbon Features (89-111)
- ✅ All Advanced Ribbon Features (R1-R64)
- ✅ **Total:** 23 + 64 = 87 features

**Milestone:** Ribbons fully functional with basic + advanced features

---

## 🔗 SECTION 5: CROSS-FEATURES

**What's Included:**
- ✅ Integration Features (112-118)
- ✅ Display Features (119-126)
- ✅ Search & Filtering (127-139)
- ✅ Performance & Optimization (140-149)
- ✅ Security Features (150-159)
- ✅ Accessibility Features (160-169)
- ✅ Analytics & Reporting (170-173)
- ✅ Import/Export Features (174-184)
- ✅ Multi-site Support (185-191)
- ✅ Localization & Translation (192-198)
- ✅ **Total:** 87 features

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
- Before implementing product display features (P22-P44)
- Before implementing category/tag display features
- Before implementing product templates

**See Full Details:** `plan/seo-plugin-integration-requirements.md`

---

## ✅ SECTION 6: QUALITY & LAUNCH

**What's Included:**
- ✅ Documentation (199-208)
- ✅ Testing & Quality Assurance (209-218)
- ✅ **Total:** 20 features

**Milestone:** Plugin tested, documented, and ready for launch

---

# 📊 IMPLEMENTATION SUMMARY

| Section | Taxonomy | Features | Status |
|----------|-----------|-----------|---------|
| **Section 1** | Products | 109 | **START HERE** |
| **Section 2** | Categories | 98 | Pending |
| **Section 3** | Tags | 86 | Pending |
| **Section 4** | Ribbons | 87 | Pending |
| **Section 5** | Cross-Features | 87 | Pending |
| **Section 6** | Quality & Launch | 20 | Pending |

**Total:** 487 features across 6 taxonomy sections

---

# ✅ FEATURE LIST (Numbered by Implementation Order)

# SECTION 1: PRODUCTS

## Basic Level Features

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
- [x] 21. Bulk actions: Set In Stock, Set Out of Stock, Reset Clicks, Export
- [x] 22. Search products by title/description
- [x] 23. Filter by status, brand, stock

### 4. Basic REST API - Products
- [x] 24. GET `/v1/products` - List products (paginated)
- [x] 25. GET `/v1/products/{id}` - Get single product
- [x] 26. POST `/v1/products` - Create product
- [ ] 27. POST `/v1/products/{id}` - Update product
- [ ] 28. DELETE `/v1/products/{id}` - Delete product
- [ ] 29. POST `/v1/products/{id}/trash` - Trash product
- [ ] 30. POST `/v1/products/{id}/restore` - Restore product
- [ ] 31. DELETE `/v1/products/{id}/delete-permanently` - Permanent delete

## Advanced Level Features

### P1. Enhanced Product Fields
- [ ] P1. Original Price (for discount calculation)
- [ ] P2. Discount Percentage (auto-calculated)
- [ ] P3. Currency Selection (USD, EUR, GBP, etc.)
- [ ] P4. Product SKU/ID (custom identifier)
- [ ] P5. Stock Status (In Stock, Out of Stock, Pre-order)
- [ ] P6. Availability Date (for pre-orders)
- [ ] P7. Product Rating (0-5 stars)
- [ ] P8. Review Count
- [ ] P9. Product Weight (for shipping calculation)
- [ ] P10. Product Dimensions (L x W x H)
- [ ] P11. Product Brand/Manufacturer
- [ ] P12. Product Warranty Information
- [ ] P13. Product Video URL (YouTube, Vimeo embed)
- [ ] P14. Product Gallery (multiple images)
- [ ] P15. Product PDF Brochure (file upload)
- [ ] P16. Product FAQ Section (accordion)
- [ ] P17. Product Specifications Table
- [ ] P18. Product Comparison Chart
- [ ] P19. Product Release Date
- [ ] P20. Product Expiration Date
- [ ] P21. Product Priority (display order)

### P2. Advanced Product Display
- [ ] P22. Quick view modal (AJAX)
- [ ] P23. Add to comparison list
- [ ] P24. Add to wishlist
- [ ] P25. Product zoom on image hover
- [ ] P26. Image gallery with thumbnails
- [ ] P27. Video preview in gallery
- [ ] P28. Related products section
- [ ] P29. Recently viewed products
- [ ] P30. Products you may also like
- [ ] P31. Product share buttons (social media)
- [ ] P32. Print product page button
- [ ] P33. Product review form
- [ ] P34. Product rating display
- [ ] P35. Discount badge with percentage
- [ ] P36. "On Sale" indicator
- [ ] P37. "New Arrival" badge
- [ ] P38. "Best Seller" badge
- [ ] P39. Countdown timer for limited offers
- [ ] P40. Stock status indicator
- [ ] P41. Estimated delivery date
- [ ] P42. Product tabs (Description, Specs, Reviews, FAQ)
- [ ] P43. Sticky "Buy Now" button on scroll
- [ ] P44. Lazy loading for images

### P3. Advanced Product Management
- [ ] P45. Gutenberg block editor support
- [ ] P46. Quick edit in product list
- [ ] P47. Bulk edit (price, stock, categories, tags)
- [ ] P48. Clone/Duplicate product
- [ ] P49. Import products (CSV/XML)
- [ ] P50. Export products (CSV/XML)
- [ ] P51. Product versioning (save drafts)
- [ ] P52. Product scheduling (auto-publish)
- [ ] P53. Product expiration (auto-unpublish)
- [ ] P54. Bulk price update (increase/decrease by %)
- [ ] P55. Bulk category/tag assignment
- [ ] P56. Bulk ribbon assignment
- [ ] P57. Product duplicate checker (by URL/ID)
- [ ] P58. Auto-generate slugs from title
- [ ] P59. Auto-extract product images from affiliate URL
- [ ] P60. Auto-fetch product details from affiliate URL
- [ ] P61. Product preview before publishing
- [ ] P62. Product change history/log
- [ ] P63. Product approval workflow (for multi-author sites)

### P4. Advanced REST API - Products
- [ ] P64. GET `/v1/products?featured=true` - Get featured products
- [ ] P65. GET `/v1/products?on_sale=true` - Get products on sale
- [ ] P66. GET `/v1/products?search={term}` - Search products
- [ ] P67. GET `/v1/products?category={id}` - Filter by category
- [ ] P68. GET `/v1/products?tag={id}` - Filter by tag
- [ ] P69. GET `/v1/products?ribbon={id}` - Filter by ribbon
- [ ] P70. GET `/v1/products?min_price={price}` - Filter by min price
- [ ] P71. GET `/v1/products?max_price={price}` - Filter by max price
- [ ] P72. GET `/v1/products?sort={field}` - Sort products
- [ ] P73. GET `/v1/products?include={ids}` - Get specific products
- [ ] P74. GET `/v1/products/similar/{id}` - Get similar products
- [ ] P75. POST `/v1/products/bulk` - Bulk create/update
- [ ] P76. POST `/v1/products/{id}/duplicate` - Clone product
- [ ] P77. POST `/v1/products/import` - Import products
- [ ] P78. GET `/v1/products/export` - Export products


# SECTION 2: CATEGORIES

## Basic Level Features

### 5. Core Category Fields
- [ ] 32. Category Name (required)
- [ ] 33. Category Slug (auto-generated, editable)
- [ ] 34. Category Description (textarea)
- [ ] 35. Parent Category (dropdown)
- [ ] 36. Category Icon (emoji or SVG)
- [ ] 37. Category Color (hex color picker)
- [ ] 38. Category Image (WordPress media uploader)

### 6. Basic Category Display
- [ ] 39. Category listing page
- [ ] 40. Category card with icon/color/image
- [ ] 41. Breadcrumb navigation
- [ ] 42. Category description display
- [ ] 43. Product count per category
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

## Advanced Level Features

### C1. Enhanced Category Fields
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
- [ ] C11. Category Featured Products (select specific products)
- [ ] C12. Category Layout Style (grid, list, masonry)
- [ ] C13. Products per page (for this category)
- [ ] C14. Default Sort Order (name, price, date)
- [ ] C15. Category Widget Title Override
- [ ] C16. Category Shortcode (for embedding)
- [ ] C17. Category RSS Feed URL
- [ ] C18. Category Last Updated Date

### C2. Advanced Category Display
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

### C3. Advanced Category Management
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

### C4. Advanced REST API - Categories
- [ ] C56. GET `/v1/categories?featured=true` - Get featured categories
- [ ] C57. GET `/v1/categories?search={term}` - Search categories
- [ ] C58. GET `/v1/categories?parent={id}` - Get subcategories
- [ ] C59. GET `/v1/categories?hide_empty=true` - Filter empty
- [ ] C60. GET `/v1/categories?include={ids}` - Get specific categories
- [ ] C61. GET `/v1/categories/tree` - Get category tree
- [ ] C62. POST `/v1/categories/bulk` - Bulk create/update
- [ ] C63. POST `/v1/categories/{id}/duplicate` - Clone category
- [ ] C64. POST `/v1/categories/{id}/merge` - Merge categories
- [ ] C65. GET `/v1/categories/popular` - Get most popular
- [ ] C66. GET `/v1/categories/{id}/products` - Get products in category


# SECTION 3: TAGS

## Basic Level Features

### 9. Core Tag Fields
- [ ] 65. Tag Name (required)
- [ ] 66. Tag Slug (auto-generated, editable)
- [ ] 67. Tag Description (textarea)
- [ ] 68. Tag Color (hex color picker)
- [ ] 69. Tag Icon (emoji or SVG)

### 10. Basic Tag Display
- [ ] 70. Tag listing page
- [ ] 71. Tag cloud widget
- [ ] 72. Tag badges on products
- [ ] 73. Tag filtering in product list
- [ ] 74. Tag description display
- [ ] 75. Product count per tag
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

## Advanced Level Features

### T1. Enhanced Tag Fields
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

### T2. Advanced Tag Display
- [ ] T16. Tag cloud with different sizes
- [ ] T17. Tag color variations
- [ ] T18. Tag icon variations
- [ ] T19. Tag grouping (by category, popularity)
- [ ] T20. Tag filter sidebar
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
- [ ] T32. Tag filtering widget
- [ ] T33. Tag carousel/slider
- [ ] T34. Tag grid display
- [ ] T35. Tag list display

### T3. Advanced Tag Management
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

### T4. Advanced REST API - Tags
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


# SECTION 4: RIBBONS

## Basic Level Features

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

## Advanced Level Features

### R1. Enhanced Ribbon Fields
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

### R2. Advanced Ribbon Display
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

### R3. Advanced Ribbon Management
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

### R4. Advanced REST API - Ribbons
- [ ] R55. GET `/v1/ribbons?active=true` - Get active ribbons
- [ ] R56. GET `/v1/ribbons?style={style}` - Filter by style
- [ ] R57. GET `/v1/ribbons?priority={order}` - Sort by priority
- [ ] R58. GET `/v1/ribbons?scheduled=true` - Get scheduled ribbons
- [ ] R59. GET `/v1/ribbons/expired=true` - Get expired ribbons
- [ ] R60. GET `/v1/ribbons/{id}/products` - Get products with ribbon
- [ ] R61. POST `/v1/ribbons/bulk` - Bulk create/update
- [ ] R62. POST `/v1/ribbons/{id}/duplicate` - Clone ribbon
- [ ] R63. POST `/v1/ribbons/templates` - Get ribbon templates
- [ ] R64. POST `/v1/ribbons/{id}/schedule` - Schedule ribbon


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
- [ ] 132. Filter by rating
- [ ] 133. Filter by stock status
- [ ] 134. Filter by date range
- [ ] 135. Filter by custom fields
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

## 21. Security Features
- [ ] 150. CSRF protection on all forms
- [ ] 151. Nonce verification on AJAX requests
- [x] 152. Input sanitization
- [x] 153. Output escaping
- [x] 154. SQL injection prevention
- [x] 155. XSS prevention
- [x] 156. Rate limiting on API endpoints
- [ ] 157. IP-based access control
- [ ] 158. User capability checks
- [ ] 159. Audit logging for admin actions

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

## 23. Analytics & Reporting (Optional - External Plugins Recommended)

**Note:** Full analytics and reporting can be handled by dedicated analytics plugins (Google Analytics, MonsterInsights, Analytify, etc.). This plugin provides basic event tracking hooks for integration.

- [ ] 170. WordPress hooks for external analytics plugins (e.g., `aps_product_viewed`, `aps_buy_now_clicked`)
- [ ] 171. Basic event data storage (for custom integrations)
- [ ] 172. Documentation for analytics plugin integration
- [ ] 173. Example code for popular analytics plugins (Google Analytics, etc.)

**Recommended External Analytics Plugins:**
- Google Analytics by MonsterInsights
- Analytify
- ExactMetrics
- Site Kit by Google
- WP Statistics

## 24. Import/Export Features
- [ ] 174. Import products from CSV
- [ ] 175. Import products from XML
- [ ] 176. Export products to CSV
- [ ] 177. Export products to XML
- [ ] 178. Import categories from CSV
- [ ] 179. Export categories to CSV
- [ ] 180. Import tags from CSV
- [ ] 181. Export tags to CSV
- [ ] 182. Bulk image upload
- [ ] 183. Mapping field validation
- [ ] 184. Import error logging

## 25. Multi-site Support
- [ ] 185. Network-wide product sharing
- [ ] 186. Network-wide category sharing
- [ ] 187. Network-wide tag sharing
- [ ] 188. Network-wide ribbon sharing
- [ ] 189. Site-specific overrides
- [ ] 190. Cross-site product cloning
- [ ] 191. Network admin dashboard

## 26. Localization & Translation
- [ ] 192. Translation-ready (.pot file)
- [ ] 193. Translations: English (en_US)
- [ ] 194. Translations: Additional languages
- [ ] 195. RTL (Right-to-Left) support
- [ ] 196. Currency formatting
- [ ] 197. Date/time formatting
- [ ] 198. Number formatting


# SECTION 6: QUALITY & LAUNCH

## 27. Documentation
- [ ] 199. User manual (PDF/HTML)
- [ ] 200. Developer API documentation
- [ ] 201. Shortcode reference guide
- [ ] 202. REST API documentation
- [ ] 203. Hook/filter reference
- [ ] 204. Troubleshooting guide
- [ ] 205. FAQ section
- [ ] 206. Video tutorials
- [ ] 207. Code examples
- [ ] 208. Changelog

## 28. Testing & Quality Assurance
- [ ] 209. Unit tests (PHPUnit)
- [ ] 210. Integration tests
- [ ] 211. E2E tests (Playwright)
- [ ] 212. Cross-browser testing (Chrome, Firefox, Safari, Edge)
- [ ] 213. Mobile testing (iOS, Android)
- [ ] 214. Performance testing (Lighthouse)
- [ ] 215. Security testing (Snyk, WPScan)
- [ ] 216. Accessibility testing (axe DevTools)
- [ ] 217. Code quality checks (PHPCS, PHPStan, Psalm)
- [ ] 218. Code coverage reporting


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

- **Section 1: Products:** 26/109 complete (~24%)
  - ✅ Core Infrastructure: 9/9 complete (100%)
  - ✅ Basic Display: 5/6 complete (83%)
  - ✅ Templates: 2/2 complete (100%)
  - ✅ Basic Management: 6/8 complete (75%)
  - ✅ Basic REST API: 3/8 complete (38%)
  - ✅ Admin Columns: Price, SKU, Brand, Rating, Clicks, Conversions (5/5)
  - ✅ Admin Filters: Brand dropdown, Stock status filter
  - ✅ Analytics: Click count, Conversion count (last 30 days)
  - ❌ Advanced Features: 0/78 complete (0%)
  
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
  - [x] 21. Bulk actions: Set In Stock, Set Out of Stock, Reset Clicks, Export to CSV
  - [x] 22. Search products by title/description
  - [x] 23. Filter by status, brand, stock
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
  - [ ] P1-P78. All 78 Advanced Product Features

- **Section 2: Categories:** [0]/98 complete (0%)
- **Section 3: Tags:** [0]/86 complete (0%)
- **Section 4: Ribbons:** [0]/87 complete (0%)
- **Section 5: Cross-Features:** 18/87 complete (~21%)
- **Section 6: Quality & Launch:** [0]/20 complete (0%)
- **Overall Progress:** ~38/487 complete (~8%)

**Last Updated:** 2026-01-21  
**Version:** 1.0.0  
**Maintainer:** Development Team
