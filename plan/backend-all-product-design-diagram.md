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

    