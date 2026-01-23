# All Products Page - Visual Design Diagram
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
                    RowNum --> ImgLogo[IMG <br> <span style='font-size:10px'>aps-product-logo</span>]
                    ImgLogo --> ProdName[demo2 five <br> ID #2 Edit Delete]
                    ProdName --> Cat[xsacsac × <br> <span style='font-size:10px'>aps-product-category</span>]
                    Cat --> RowTags[cewer × Default × <br> <span style='font-size:10px'>aps-product-tag</span>]
                    RowTags --> Rib[7777 × <br> <span style='font-size:10px'>aps-product-badge</span>]
                    Rib --> Feat[★ <br> <span style='font-size:10px'>aps-product-featured</span>]
                    Feat --> RowPrice[$50 <br> $55 9% OFF <br> <span style='font-size:10px'>aps-product-price, aps-product-price-original, aps-product-price-discount</span>]
                    RowPrice --> Pub[PUBLISHED <br> <span style='font-size:10px'>aps-product-status, aps-product-status-published</span>]
                end
            end
        end
    end

---

# 🎨 CSS Classes Reference for Product Table

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
