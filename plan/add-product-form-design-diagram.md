# Add Product Form - Visual Design Diagram
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
## Form Layout Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    EDIT PRODUCT                          [× Close] │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─ QUICK NAVIGATION ──────────────────────────────────────────┐   │
│  │ [📝 Product Info] [🖼️ Images] [🔗 Affiliate] │   │
│  │ [📋 Features] [🏷️ Pricing] [📂 Categories]  │   │
│  │ [📊 Stats]                                          │   │
│  └───────────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ PRODUCT INFO                                            ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌─────────────────────┐  ┌──────────────────┐            ║   │
│  ║  │ Product Title *  │  │ Status         │            ║   │
│  ║  │ [Enter title...] │  │ [Draft ▼]      │            ║   │
│  ║  └─────────────────────┘  └──────────────────┘            ║   │
│  ║                                                           ║   │
│  ║  ☑ Featured Product                                        ║   │
│  ║                                                           ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ PRODUCT IMAGES                                          ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌──────────────────────┐  ┌──────────────────────┐       ║   │
│  ║  │   📷 Upload Logo   │  │ 👕 Brand Image    │       ║   │
│  ║  │  (Drag & Drop)     │  │  (Drag & Drop)     │       ║   │
│  ║  │  ┌──────────────┐  │  │  ┌──────────────┐  │       ║   │
│  ║  │  │ [Image Preview]│  │  │ [Image Preview]│  │       ║   │
│  ║  │  └──────────────┘  │  │  └──────────────┘  │       ║   │
│  ║  └──────────────────────┘  └──────────────────────┘       ║   │
│  ║                                                           ║   │
│  ║  ┌──────────────────────┐  ┌──────────────────────┐       ║   │
│  ║  │ Logo URL           │  │ Brand Image URL    │       ║   │
│  ║  │ https://...        │  │ https://...        │       ║   │
│  ║  └──────────────────────┘  └──────────────────────┘       ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ AFFILIATE DETAILS                                       ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌──────────────────────────────┐  ┌──────────────────┐      ║   │
│  ║  │ Affiliate URL            │  │ Button Name   │      ║   │
│  ║  │ https://example.com/...   │  │ Buy Now       │      ║   │
│  ║  └──────────────────────────────┘  └──────────────────┘      ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ SHORT DESCRIPTION                                       ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌────────────────────────────────────────────────────────┐        ║   │
│  ║  │ Enter short description (max 40 words)...      │        ║   │
│  ║  │                                                │        ║   │
│  ║  └────────────────────────────────────────────────────────┘        ║   │
│  ║                                               0/40 Words     ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ FEATURE LIST                                            ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌──────────────────────────────┐  ┌──────────────────┐      ║   │
│  ║  │ Add new feature...       │  │ [+ Add]       │      ║   │
│  ║  └──────────────────────────────┘  └──────────────────┘      ║   │
│  ║                                                           ║   │
│  ║  ┌────────────────────────────────────────────────────────┐        ║   │
│  ║  │ Feature 1 text                     [🔼] [🔽] [🗑️] │        ║   │
│  ║  └────────────────────────────────────────────────────────┘        ║   │
│  ║  ┌────────────────────────────────────────────────────────┐        ║   │
│  ║  │ Feature 2 text                     [🔼] [🔽] [🗑️] │        ║   │
│  ║  └────────────────────────────────────────────────────────┘        ║   │
│  ║  (Each feature has: Highlight, Move Up/Down, Delete)          ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ PRICING                                                ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌──────────────────┐  ┌──────────────────┐  ┌──────────┐ ║   │
│  ║  │ Current Price   │  │ Original Price  │  │ Discount  │ ║   │
│  ║  │ 30.00          │  │ 60.00          │  │ 50% OFF   │ ║   │
│  ║  └──────────────────┘  └──────────────────┘  └──────────┘ ║   │
│  ║  (Auto-calculated discount %)                             ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ CATEGORIES & RIBBONS                                   ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌────────────────────────┐  ┌───────────────────────┐       ║   │
│  ║  │ Category            │  │ Ribbon Badge        │       ║   │
│  ║  │ [Electronics] [×]   │  │ [HOT] [×] [NEW] [×] │       ║   │
│  ║  │ [Fashion] [×]       │  │ [SALE] [×]          │       ║   │
│  ║  │ [Select categories...] │  │ [Select ribbons...]  │       ║   │
│  ║  │ ▼                    │  │ ▼                    │       ║   │
│  ║  └────────────────────────┘  └───────────────────────┘       ║   │
│  ║  (Multi-select dropdowns with removable tags)                  ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ PRODUCT TAGS                                           ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ☑ New Arrival     ☑ Best Seller   ☑ On Sale          ║   │
│  ║  ☑ Limited Edition                                       ║   │
│  ║  (Checkbox group)                                         ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║ PRODUCT STATISTICS                                      ║   │
│  ╠═════════════════════════════════════════════════════════╣   │
│  ║                                                           ║   │
│  ║  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ ║   │
│  ║  │ Rating      │  │ Views       │  │ User Count   │ ║   │
│  ║  │ 4.5         │  │ 325         │  │ 1.5K         │ ║   │
│  ║  └──────────────┘  └──────────────┘  └──────────────┘ ║   │
│  ║                                                           ║   │
│  ║  ┌──────────────┐                                       ║   │
│  ║  │ No. of Reviews                                       ║   │
│  ║  │ 12                                                  ║   │
│  ║  └──────────────┘                                       ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
│                                                                 │
│  ╔═════════════════════════════════════════════════════════╗   │
│  ║                                                      ║   │
│  ║                                              [Cancel] [Update Product] ║   │
│  ║                                                      ║   │
│  ╚═════════════════════════════════════════════════════════╝   │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Section Breakdown

### 1. Header
- Title: "Edit Product"
- Close button (X icon)
- White background with border bottom

### 2. Quick Navigation (7 Links)
| Icon | Link | Anchor |
|-------|-------|--------|
| 📝 | Product Info | #product-info |
| 🖼️ | Images | #images |
| 🔗 | Affiliate | #affiliate |
| 📋 | Features | #features |
| 🏷️ | Pricing | #pricing |
| 📂 | Categories & Tags | #taxonomy |
| 📊 | Stats | #stats |

### 3. Product Info
- **Product Title** (Required) - Text input
- **Status** - Dropdown: Draft / Published
- **Featured Product** - Checkbox

### 4. Product Images (Side by Side)
- **Logo Upload** - Drag & drop area + URL input
- **Brand Image Upload** - Drag & drop area + URL input
- Both show image preview after upload

### 5. Affiliate Details
- **Affiliate URL** - URL input field
- **Button Name** - Text input (e.g., "Buy Now")

### 6. Short Description
- Textarea (max 200 characters / 40 words)
- Word counter: "0/40 Words"

### 7. Feature List
- Input field to add new features
- Each feature has:
  - **Highlight** (bold icon) - Toggle highlight styling
  - **Move Up** (arrow up icon)
  - **Move Down** (arrow down icon)
  - **Delete** (trash icon)

### 8. Pricing (3 Columns)
| Current Price | Original Price | Discount |
|--------------|----------------|----------|
| Number input | Number input | **Read-only** (auto-calculated) |

**Auto-calculation formula:**
```
Discount % = ((Original - Current) / Original) × 100
```
Example: (60 - 30) / 60 × 100 = 50% OFF

### 9. Categories & Ribbons (Multi-Select)

**Category Multi-Select:**
- Click box to open dropdown
- Select multiple categories
- Selected items show as removable tags
- Options: Electronics, Fashion, Home & Garden, Beauty, Sports, Books

**Ribbon Badge Multi-Select:**
- Click box to open dropdown
- Select multiple ribbons
- Selected items show as removable tags
- Options: HOT, NEW ARRIVAL, SALE, LIMITED, BEST SELLER

### 10. Product Tags (Checkbox Group)
- ☑ New Arrival
- ☑ Best Seller
- ☑ On Sale
- ☑ Limited Edition

### 11. Product Statistics
| Rating | Views | User Count | No. of Reviews |
|---------|--------|-------------|----------------|
| Number (0-5) | Number | Text (e.g., 1.5K) | Number |

### 12. Footer Buttons
- **Cancel** (Secondary button) - Reloads page
- **Update Product** (Primary button) - Shows success toast

---

## Design Characteristics

### Colors
```css
Primary:    #2271b1 (WordPress Blue)
Primary Hover: #135e96
Background:   #f0f0f1
Card:        #ffffff
Text:        #1d2327
Muted:       #646970
Border:       #c3c4c7
Danger:      #d63638
Success:      #00a32a
```

### Typography
- Font: Inter (Google Fonts)
- Sizes: 13px (buttons), 14px (inputs), 20px (header)
- Weight: 400 (regular), 500, 600 (semibold)

### UI Components

**Inputs:**
- 8px padding, 12px padding
- Border radius: 4px
- Focus: Blue border (#2271b1) + shadow

**Buttons:**
- Height: 36px
- Padding: 0 20px
- Border radius: 4px
- Primary: Blue background
- Secondary: Gray background

**Multi-Select Tags:**
- Background: #e6f0f5 (light blue)
- Color: #2271b1 (blue)
- Remove icon: Red on hover

**Feature List:**
- Hover: Light gray background (#f8f9f9)
- Highlighted: Bold text + blue background + padding

---

## Key Features

1. **Quick Navigation** - Fast access to all sections
2. **Image Preview** - Shows uploaded images instantly
3. **Auto-Calculate Discount** - Automatic % calculation
4. **Multi-Select** - Select multiple categories/ribbons with removable tags
5. **Feature Management** - Add, reorder, highlight, delete features
6. **Word Counter** - Real-time word count for description
7. **Responsive** - Grid layouts adjust on mobile (768px breakpoint)
8. **Toast Notifications** - Success/error messages appear bottom-right

---

## Form Data Structure

```json
{
  "title": "Product Name",
  "status": "publish",
  "featured": true,
  "logo_image": "logo.png",
  "brand_image": "brand.png",
  "logo_url": "https://...",
  "brand_url": "https://...",
  "affiliate_url": "https://...",
  "button_name": "Buy Now",
  "short_description": "...",
  "features": [
    { "text": "Feature 1", "highlighted": true },
    { "text": "Feature 2", "highlighted": false }
  ],
  "current_price": 30.00,
  "original_price": 60.00,
  "discount": "50% OFF",
  "categories": ["electronics", "fashion"],
  "ribbons": ["hot", "new"],
  "tags": ["new", "bestseller"],
  "rating": 4.5,
  "views": 325,
  "user_count": "1.5K",
  "reviews": 12
}
```

---

## Responsive Breakpoints

- **Desktop (> 768px):**
  - 2-column grids (Product Info, Images, Affiliate, Taxonomy, Stats)
  - 3-column grid (Pricing)
  - Side-by-side image uploads

- **Mobile (≤ 768px):**
  - Single-column layout for all grids
  - Stacked image uploads
  - Full-width inputs

---

## Dependencies

### CSS Frameworks
- None (Custom CSS)

### External Libraries
- **Font Awesome 6.4.0** (Icons)
- **Google Fonts - Inter** (Typography)

### Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- ES6 JavaScript support required

---

*Generated: 2026-01-22*
*Source: `wp-content/plugins/affiliate-product-showcase/docs/add-product-form-visual.html`*
