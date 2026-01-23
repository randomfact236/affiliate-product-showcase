# All Products Page - Flowchart Diagram
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
## All Product Page

graph TD
    subgraph MainContent[Main Content]
        direction TB
        ManageProducts[Manage Products] --> Overview[Quick overview of your catalog with actions, filters, and bulk selection.]
        Overview --> Buttons
        subgraph Buttons
            direction LR
            AddNew[Add New Product] --> TrashBtn[Trash]
            TrashBtn --> BulkUpload[Bulk Upload]
            BulkUpload --> CheckLinks[Check Links]
        end

        Buttons --> Counts
        subgraph Counts
            direction LR
            All[5 ALL] --> Published[1 PUBLISHED]
            Published --> Draft[2 DRAFT]
            Draft --> Trash[0 TRASH]
        end

        Counts --> Filters
        subgraph Filters
            direction LR
            SelectAction[Select action] --> Search[Search products...]
            Search --> AllCategories[All Categories]
            AllCategories --> Sort[Latest <- Oldest]
            Sort --> ShowFeatured[Show Featured]
            ShowFeatured --> ClearFilters[Clear filters]
        end

        Filters --> Table
        subgraph Table
            direction TB
            Headers --> Rows
            subgraph Headers
                direction LR
                CheckboxHeader[ ] --> Num[#]
                Num --> Logo[Logo]
                Logo --> Product[Product]
                Product --> Category[Category]
                Category --> Tags[Tags]
                Tags --> Ribbon[Ribbon]
                Ribbon --> Featured[Featured]
                Featured --> Price[Price]
                Price --> Status[Status]
            end

            subgraph Rows
                direction TB
                Row1
                subgraph Row1
                    direction LR
                    Checkbox[ ] --> RowNum[2]
                    RowNum --> ImgLogo[IMG]
                    ImgLogo --> ProdName[demo2 five <br> ID #2 Edit Delete]
                    ProdName --> Cat[xsacsac ×]
                    Cat --> RowTags[cewer × Default ×]
                    RowTags --> Rib[7777 ×]
                    Rib --> Feat[★]
                    Feat --> RowPrice[$50 <br> $55 9% OFF]
                    RowPrice --> Pub[PUBLISHED]
                end
            end
        end
    end

## 📊 User Flow - All Products Page

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    ALL PRODUCTS PAGE                            │
│                    [User Lands]                            │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  CHECK AUTHENTICATION                         │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Is User Logged In?                                     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│      ┌────────────────────┐                           │
│      │ NO                 │                           │
│      ▼                   │                           │
│  [Redirect to Login]      │                           │
│                         │                           │
│      ┌────────────────────┐ │                           │
│      │ YES                │ ▼                           │
│      ▼                   │ ┌────────────────────────────────────────────────┐│
│                        │ │ Load All Products Page                         ││
│                        │ │ with Filters & Search                         ││
│                        │ └────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  PRODUCTS AVAILABLE?                             │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products Exist in Database?                           │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│      ┌────────────────────┐                           │
│      │ NO                 │                           │
│      ▼                   │                           │
│  [Show Empty State]      │                           │
│  ┌────────────────────┐ │                           │
│  │ "No Products Found"   │                           │
│  │ [Create First Product]│                           │
│  │ [Add New Product]   │                           │
│  └────────────────────┘ │                           │
│                         │                           │
│      ┌────────────────────┐ │                           │
│      │ YES                │ ▼                           │
│      ▼                   │                           │
│                        │                           │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  RENDER PRODUCT GRID                            │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [Product Grid with Cards]                              │ │
│  │ ┌─────────────────────────────────────────────────────┐   │ │
│  │ │ Product Card 1   Product Card 2   Product Card 3   │   │ │
│  │ │ [Image]         [Image]         [Image]         │   │ │
│  │ │ [Title]         [Title]         [Title]         │   │ │
│  │ │ [Price]         [Price]         [Price]         │   │ │
│  │ │ [Badges]        [Badges]        [Badges]        │   │ │
│  │ │ [Buy Now Btn]   [Buy Now Btn]   [Buy Now Btn]   │   │ │
│  │ │ [Quick View]    [Quick View]    [Quick View]    │   │ │
│  │ └─────────────────────────────────────────────────────┘   │ │
│  │                                                            │   │ │
│  │ [Product Card 4   Product Card 5   Product Card 6   │   │ │
│  │ [...etc...]                                              │   │ │
│  └─────────────────────────────────────────────────────────────┘   │ │
│                                                              │
│  [Pagination: 1 2 3 ... Next]                             │
│                                                              │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  USER INTERACTIONS                             │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ User Can Interact With Page                         │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│      ┌──────────────────────────────────────────────────────────┐ │
│      │ OPTIONS                                                │ │
│      └──────────────────────────────────────────────────────────┘ │
│      ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │
│      │ [Filter] │  │ [Sort]  │  │ [View]  │  │ [Search]│    │
│      │ Button   │  │ Dropdown│  │ Toggle  │  │ Bar    │    │
│      └──────────┘  └──────────┘  └──────────┘  └──────────┘    │
│                                                              │
│      ┌──────────────────────────────────────────────────────────┐ │
│      │ PRODUCT CARD INTERACTIONS                           │ │
│      └──────────────────────────────────────────────────────────┘ │
│      ┌──────────┐  ┌──────────┐  ┌──────────┐                   │
│      │ [Quick   │  │ [Add to │  │ [Add to │                   │
│      │ View]   │  │ Wishlist]│  │ Compare] │                   │
│      └──────────┘  └──────────┘  └──────────┘                   │
│                                                              │
│      ┌──────────────────────────────────────────────────────────┐ │
│      │ [Buy Now Button] - Opens Affiliate URL              │ │
│      └──────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔍 FILTER SYSTEM FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  FILTER BUTTON CLICKED                        │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  FILTER DROPDOWN SHOWS                         │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Select Filter Type                                     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │[Category]│  │ [Tag]    │  │ [Ribbon] │  │[Price] │      │
│  │ Filters   │  │ Filters  │  │ Filters  │  │ Range  │      │
│  └────────────┘  └──────────┘  └──────────┘  └──────────┘      │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  SELECT FILTERS (EXAMPLE: CATEGORY)               │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Category Selection                                     │ │
│  │ ┌──────────────────────────────────────────────────┐     │ │
│  │ │ ☑ Electronics  (23 products)  [×]          │     │ │
│  │ │ ☑ Fashion (45 products)          [×]          │     │ │
│  │ │ ☑ Home & Garden (12 products)      [×]          │     │ │
│  │ │ ☑ Sports (34 products)          [×]          │     │ │
│  │ │ ☑ Books (67 products)          [×]          │     │ │
│  │ │ [Select Categories...]                        │     │ │
│  │ └──────────────────────────────────────────────────┘     │ │
│  │                                                            │     │ │
│  │ [Apply Filters]                                      │     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Selected Filters Show as Active Badges                 │ │
│  │ ┌─────────────────────────────┐                      │     │ │
│  │ │ [Electronics] [×] [Fashion] │                      │     │ │
│  │ │ [Clear All] [×]          │                      │     │ │
│  │ └─────────────────────────────┘                      │     │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  FILTER RESULTS FETCH                           │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ API Request: GET /v1/products?category=electronics     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [Loading Spinner]                                      │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products Re-render with Filtered Results                │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  CLEAR SINGLE FILTER                            │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ User Clicks [×] on Category Badge                      │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Filter Removed, Products Update                     │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Remaining Active Badges Show                            │ │
│  │ ┌─────────────────────────────┐                      │     │ │
│  │ │ [Fashion] [×]            │                      │     │ │
│  │ │ [Clear All] [×]          │                      │     │ │
│  │ └─────────────────────────────┘                      │     │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 SORTING OPTIONS FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  SORT DROPDOWN CLICKED                          │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  SORT OPTIONS SHOW                              │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Select Sort Criteria                                    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐    │
│  │[Name]    │  │[Price]   │  │[Date]    │  │[Rating]  │    │
│  │Ascending│  │Ascending│  │Ascending│  │Ascending│    │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  SORT APPLIED                                    │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Selected Sort Shows as Active                           │ │
│  │ Example: [Price ▲]                                 │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products Re-sorted                                    │ │
│  │ Example: $10, $15, $20, $25, $30                     │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🖼️ QUICK VIEW MODAL FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  PRODUCT CARD CLICKED                           │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  QUICK VIEW MODAL OPENS                        │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                    ┌────────────────────────────┐       │ │
│  │                    │ Product Details            │       │ │
│  │                    │ ┌──────────────────────────┐│       │ │
│  │                    │ │ [Product Image]        ││       │ │
│  │                    │ └──────────────────────────┘│       │ │
│  │                    │                            │       │ │
│  │                    │ Title: Product Name         │       │ │
│  │                    │                            │       │ │
│  │                    │ Price: $30.00               │       │ │
│  │                    │ Original: $60.00          │       │ │
│  │                    │ Discount: 50% OFF          │       │ │
│  │                    │                            │       │ │
│  │                    │ Rating: ⭐⭐⭐⭐☆         │       │ │
│  │                    │                            │       │ │
│  │                    │ Short Description...       │       │ │
│  │                    └────────────────────────────┘       │ │
│  │                                                │       │ │
│  │  ┌────────────────────────────────────────────┐│       │ │
│  │ │ Action Buttons                           ││       │ │
│  │ │ ┌────────┐  ┌────────┐  ┌─────────┐ ││       │ │
│  │ │ │[Add to]│  │[Add to]│ │[Close  │││       │ │
│  │ │ │Compare]│  │Wishlist]│ │      ]│││       │ │
│  │ │ └────────┘  └────────┘  └─────────┘ ││       │ │
│  │ └────────────────────────────────────────────┘│       │ │
│  │                                                │       │ │
│  │              ┌─────────────────────────┐      │       │ │
│  │              │ [Buy Now Button]         │      │       │ │
│  │              │ Opens Affiliate URL         │      │       │ │
│  │              └─────────────────────────┘      │       │ │
│  └──────────────────────────────────────────────────────┘       │ │
│                                                │       │ │
│  ┌────────────────────────────────────────────────────┐│       │ │
│  │ Product Specifications                      ││       │ │
│  │ ┌───────────────────────────────────────────┐││       │ │
│  │ │ Brand: Electronics Co.                  │││       │ │
│  │ │ SKU: PROD-00123                        │││       │ │
│  │ │ Weight: 2.5 lbs                       │││       │ │
│  │ │ Dimensions: 10×8×2 inches             │││       │ │
│  │ │ Stock: In Stock                       │││       │ │
│  │ └───────────────────────────────────────────┘││       │ │
│  └──────────────────────────────────────────────────────┘│       │ │
│                                                │       │ │
│  ┌────────────────────────────────────────────────────┐│       │ │
│  │ Related Products                            ││       │ │
│  │ ┌────────────┐  ┌────────────┐  ┌────────────┐││       │ │
│  │ │ Product 1 │  │ Product 2 │  │ Product 3 │││       │ │
│  │ │ [Image]  │  │ [Image]  │  │ [Image]  │││       │ │
│  │ │ [Title]  │  │ [Title]  │  │ [Title]  │││       │ │
│  │ │ [$40]   │  │ [$35]   │  │ [$50]   │││       │ │
│  │ └────────────┘  └────────────┘  └────────────┘││       │ │
│  └──────────────────────────────────────────────────────┘│       │ │
│                                                │       │ │
│  │            [× Close Modal]                    │       │ │
│  │                                                │       │ │
│  └─────────────────────────────────────────────────────────┘       │
│                                                              │
└───────────────────────────────────────────────────────────────────┘
```

---

## 📱️ PAGINATION FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  PAGE NAVIGATION                                │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products: 45 total                                    │ │
│  │ [◄ Prev] [1] [2] [3] [4] [Next ▶]                  │ │
│  │ Showing 1-12 of 45                                    │ │
│  │ ┌──────────────┐                                       │ │
│  │ │ Items/Page: ▼                                         │ │
│  │ │ ┌─────────┐                                         │ │
│  │ │ │ 12     │                                         │ │
│  │ │ │ 24     │                                         │ │
│  │ │ │ 48     │                                         │ │
│  │ │ │ 96     │                                         │ │
│  │ │ └─────────┘                                         │ │
│  │ └──────────────┘                                       │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ User Clicks [Next ▶]                                  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Page Scrolls to Top                                    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [Loading Spinner]                                      │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Page 2 Products Load and Render                         │ │
│  │ Updates: [◄ Prev] [1] [2] [3] [4] [Next ▶]           │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔍 SEARCH FUNCTIONALITY FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  SEARCH BAR INPUT                              │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [🔍 Search products...]                               │ │
│  │                                                 [Search]  │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  USER TYPES SEARCH TERM                       │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [🔍 Search products...elec]                          │ │
│  │                                                      │ │
│  │ Debounce Timer: 300ms                                 │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  USER PAUSES TYPING                           │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [🔍 Search products...elec...]                        │ │
│  │                                                      │ │
│  │ Debounce Timer Resets                                    │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  DEBOUNCE TIME COMPLETES                      │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [🔍 Search products...electronics]                     │ │
│  │                                                      │ │
│  │ API Request: GET /v1/products?search=electronics     │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  SEARCH RESULTS DISPLAY                          │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products with "electronics" highlighted show              │ │
│  │ ┌──────────────────────────────────────────────────────┐   │ │
│  │ │ Product: <mark>Electronics</mark> Co. ...   │   │ │
│  │ │ Product: <mark>Electronics</mark> Co. ...   │   │ │
│  │ │ Product: <mark>Electronics</mark> Co. ...   │   │ │
│  │ └──────────────────────────────────────────────────────┘   │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  │ [Clear Search [×]]                                      │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 VIEW TOGGLE FLOWCHART

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  VIEW TOGGLE BUTTON CLICKED                    │
└─────────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  VIEW OPTIONS SHOW                                 │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Select View Type                                       │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌────────────┐  ┌────────────┐                                 │
│  │[Grid View] │  │[List View] │                                 │
│  │◯          │  │○          │                                 │
│  └────────────┘  └────────────┘                                 │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                  VIEW APPLIED (EXAMPLE: LIST)                    │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Selected View Shows as Active                            │ │
│  │ Example: [List View ◯]                                │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Products Re-render in List View                          │ │
│  │ ┌────────────────────────────────────────────────────┐   │ │
│  │ │ Product Name                     Price        │   │ │
│  │ │ [Image] Product Name             $30.00        │   │ │
│  │ │ Product Name                     Price        │   │ │
│  │ │ [Image] Product Name             $45.00        │   │ │
│  │ └────────────────────────────────────────────────────┘   │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📱️ RESPONSIVE MOBILE VIEW

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  MOBILE VIEW (≤768px)                          │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ [☰ Menu]              ALL PRODUCTS         [Filter] [Sort]│ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Search Bar                                            │ │
│  │ ┌──────────────────────────────────────────────────┐    │ │
│  │ │ [🔍 Search products...]                [🔍]│    │ │
│  │ └──────────────────────────────────────────────────┘    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Filter Button Selected (Mobile Menu Opens)              │ │
│  │ ┌──────────────────────────────────────────────────┐    │ │
│  │ │ ┌────────────────────────────────────┐          │    │ │
│  │ │ │ Category                         │          │    │ │
│  │ │ │ ☑ Electronics (23)             │          │    │ │
│  │ │ │ ☑ Fashion (45)                 │          │    │ │
│  │ │ │ ☑ Home & Garden (12)           │          │    │ │
│  │ │ │ [Select categories...]         │          │    │ │
│  │ │ └────────────────────────────────────┘          │    │ │
│  │ │                                            │          │    │ │
│  │ │ ┌────────────────────────────────────┐          │    │ │
│  │ │ │ Tag                              │          │    │ │
│  │ │ │ ☑ New Arrival                 │          │    │ │
│  │ │ │ ☑ Best Seller                 │          │    │ │
│  │ │ │ ☑ On Sale                    │          │    │ │
│  │ │ └────────────────────────────────────┘          │    │ │
│  │ │                                            │          │    │ │
│  │ │ ┌────────────────────────────────────┐          │    │ │
│  │ │ │ Ribbon                           │          │    │ │
│  │ │ │ ☑ HOT                          │          │    │ │
│  │ │ │ ☑ NEW ARRIVAL                 │          │    │ │
│  │ │ │ ☑ SALE                         │          │    │ │
│  │ │ │ [Select ribbons...]             │          │    │ │
│  │ │ └────────────────────────────────────┘          │    │ │
│  │ │                                            │          │    │ │
│  │ │ [Price Range: $0 - $500]                       │          │    │ │
│  │ │ ┌──────────────────────────────────────────┐          │    │ │
│  │ │ │ ◀────────●──────────●──────●──────●─────│          │    │ │
│  │ │ │ $0            $250           $500    │          │    │ │
│  │ │ └──────────────────────────────────────────┘          │    │ │
│  │ │                                            │          │    │ │
│  │ │ [Apply Filters]                               │          │    │ │
│  │ │                                            │          │    │ │
│  │ │ [Close Menu]                                 │          │    │ │
│  │ └──────────────────────────────────────────────────┘          │    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ Product Grid (Single Column)                            │ │
│  │ ┌──────────────────────────────────────────────┐          │ │
│  │ │ ┌──────────┐  ┌──────────┐  ┌──────────┐          │ │
│  │ │ │          │  │          │  │          │          │
│  │ │ │ [Image]  │  │ [Image]  │  │ [Image]  │          │
│  │ │ │          │  │          │  │          │          │
│  │ │ │ [Title]  │  │ [Title]  │  │ [Title]  │          │
│  │ │ │ $30.00  │  │ $45.00  │  │ $20.00  │          │
│  │ │ │ [Buy Now]│  │ [Buy Now]│  │ [Buy Now]│          │
│  │ │ └──────────┘  └──────────┘  └──────────┘          │
│  │ └──────────────────────────────────────────────┘          │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                              │
│  [Load More Products (Showing 3 of 45)]                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 LOADING & ERROR STATES

```
┌─────────────────────────────────────────────────────────────────────────┐
│                  LOADING STATE                                 │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                                                        │ │
│  │              ⭮⭮                                        │ │
│  │                                                        │ │
│  │            Loading products...                     │ │
│  │                                                        │ │
│  │              Please wait...                         │ │
│  │                                                        │ │
│  │              ◯                                          │ │
│  │                                                        │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                  ERROR STATE                                    │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ ❌ Error Loading Products                            │ │
│  │                                                        │ │
│  │ We couldn't load the products at this time.          │ │
│  │ Please try again later or contact support.            │ │
│  │                                                        │ │
│  │                                                      │ │
│  │              [Retry]              [Go Back]          │ │
│  │                                                        │ │
│  │ Error Code: 500 Internal Server Error                 │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                  EMPTY STATE                                    │
│                                                              │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                                                      │ │
│  │              📦                                        │ │
│  │                                                        │ │
│  │            No products found matching your criteria.      │ │
│  │                                                        │ │
│  │            Try clearing filters or search terms.         │ │
│  │                                                        │ │
│  │              ◯                                          │ │
│  │                                                        │ │
│  │                                                      │ │
│  │              [Clear All Filters]     [Add New Product]   │ │
│  │                                                        │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📝 Implementation Notes

### Page Components

1. **Header Section**
   - Logo/Brand
   - Title: "All Products"
   - Search bar (always visible)
   - Filter toggle button (mobile: opens menu)

2. **Filter System**
   - Category filters (checkboxes)
   - Tag filters (checkboxes)
   - Ribbon filters (checkboxes)
   - Price range slider
   - Sort dropdown
   - Clear all filters button
   - Active filter badges with remove buttons

3. **Product Grid**
   - Responsive layout (3 columns desktop, 2 tablet, 1 mobile)
   - Product cards with:
     - Featured image
     - Product title
     - Current price
     - Original price (if discounted)
     - Discount badge
     - Ribbon badges
     - Rating
     - "Buy Now" button
     - Quick view button
     - Add to comparison button
     - Add to wishlist button

4. **Pagination**
   - Page numbers
   - Previous/Next buttons
   - Items per page selector (12, 24, 48, 96)
   - "Showing X-Y of Z" text

5. **View Toggle**
   - Grid view (default)
   - List view (alternative)

6. **Loading States**
   - Initial page load spinner
   - Filter apply spinner
   - Pagination load spinner
   - Search result spinner

7. **Error States**
   - API error message
   - Retry button
   - Support link

8. **Empty State**
   - "No products found" message
   - Clear filters button
   - "Add new product" CTA

### User Interactions

- **Filter Selection:** Click checkbox to activate, badge shows on filter button
- **Multiple Filters:** Can combine category, tag, and ribbon filters
- **Clear Filter:** Click X on badge to remove single filter
- **Clear All:** Click "Clear All" to remove all filters
- **Sort Selection:** Choose sort criteria, products re-sort immediately
- **View Toggle:** Switch between grid and list views
- **Search:** Real-time search with debouncing (300ms)
- **Pagination:** Click page number or Prev/Next to navigate
- **Quick View:** Click product card to open modal
- **Buy Now:** Click button to open affiliate URL in new tab
- **Add to Wishlist:** Click button to add to wishlist (future feature)
- **Add to Compare:** Click button to add to comparison list (future feature)

### State Transitions

- **Initial Load:** Shows loading spinner → Fetch products from API → Display grid
- **Filter Applied:** Shows badge → Trigger filtered API call → Re-render grid
- **Sort Applied:** Shows active sort → Re-sort products locally
- **View Changed:** Updates grid to list or vice versa immediately
- **Page Navigation:** Loads new page → Scrolls to top → Re-renders
- **Search Results:** Debounced search → API call → Highlighted results
- **Quick View Open:** Modal appears with product details
- **Quick View Close:** Modal disappears
- **Error State:** Shows error → Retry button → Re-fetch
- **Empty State:** Shows message + CTAs → Clear filters or add product

### API Integration

**Endpoints:**
- `GET /v1/products` - List all products (paginated)
- `GET /v1/products?category={id}` - Filter by category
- `GET /v1/products?tag={id}` - Filter by tag
- `GET /v1/products?ribbon={id}` - Filter by ribbon
- `GET /v1/products?min_price={price}` - Filter by min price
- `GET /v1/products?max_price={price}` - Filter by max price
- `GET /v1/products?search={term}` - Search products
- `GET /v1/products?sort={field}` - Sort products

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 12)
- `orderby` - Sort field (date, title, price, rating)
- `order` - Sort direction (asc, desc)

### Responsive Design

- **Desktop (> 992px):**
  - 3-column product grid
  - Filters sidebar visible
  - Full-featured search bar
  - All buttons visible

- **Tablet (768px - 992px):**
  - 2-column product grid
  - Filters sidebar visible
  - Compact search bar
  - All buttons visible

- **Mobile (≤ 768px):**
  - Single-column product grid
  - Filters in mobile menu (toggle)
  - Compact search bar
  - View toggle (if space permits)
  - Pagination arrows

### SEO & Analytics

- **Page View Tracking:**
  - Track page views with Google Analytics
  - Track category/tag/ribbon filter combinations
  - Track search terms

- **Product Clicks:**
  - Track "Buy Now" button clicks
  - Track affiliate link clicks
  - Track quick view opens

- **SEO Features:**
  - Schema.org ProductList markup
  - Open Graph tags
  - Twitter Card tags
  - Canonical URLs
  - Meta descriptions
  - Breadcrumb navigation

### Future Features

- **Wishlist Functionality:** Add to wishlist, manage wishlist
- **Comparison Lists:** Add products to compare, side-by-side comparison
- **Advanced Filtering:** More filter options, saved filter presets
- **Bulk Actions:** Bulk delete, bulk update, bulk export (admin only)
- **Social Sharing:** Share products on social media
- **Email Notifications:** Email when products in favorite categories go on sale
- **Product Recommendations:** AI-powered product suggestions

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

*Generated: 2026-01-22*
*Reference: plan/feature-requirements.md, plan/section1-implementation-strategy.md*
