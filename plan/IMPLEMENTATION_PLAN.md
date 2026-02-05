# Frontend Design Implementation Plan

## Overview
Implement the exact design from `plan/frontend-design.md` to the WordPress frontend.

---

## Phase 1: CSS Extraction (30 min)

**Source:** `plan/frontend-design.md` (lines 8-607)  
**Destination:** `wp-content/plugins/affiliate-product-showcase/assets/css/showcase-frontend-isolated.css`

### Tasks:
1. Extract all CSS from the `<style>` tag in the design file
2. Prefix all CSS selectors with `.aps-` to avoid conflicts
3. Save to the isolated CSS file
4. Ensure no WordPress admin styles are affected

### CSS Class Mapping:

| Design Class | Prefixed Class | Element |
|--------------|----------------|---------|
| `.container` | `.aps-container` | Main wrapper |
| `.sidebar` | `.aps-sidebar` | Filter sidebar |
| `.main-content` | `.aps-main-content` | Content area |
| `.cards-grid` | `.aps-cards-grid` | Grid container |
| `.tool-card` | `.aps-tool-card` | Product card |
| `.featured-badge` | `.aps-featured-badge` | Featured label |
| `.card-image` | `.aps-card-image` | Card header image |
| `.card-body` | `.aps-card-body` | Card content |
| `.tool-name` | `.aps-tool-name` | Product title |
| `.price-block` | `.aps-price-block` | Pricing area |
| `.action-button` | `.aps-action-button` | CTA button |

---

## Phase 2: Update Showcase Template (20 min)

**File:** `templates/showcase.php`

### Current Issues:
- Uses generic class names (`.tab`, `.tag`) that may conflict
- Missing proper CSS prefixes

### Changes Needed:
```php
<!-- BEFORE -->
<div class="tab active">All Tools</div>

<!-- AFTER -->
<div class="aps-tab active">All Tools</div>
```

---

## Phase 3: Create Dynamic Product Card (45 min)

**File:** `src/Public/partials/product-card.php`

### Structure from Design:
```html
<article class="aps-tool-card">
    <div class="aps-featured-badge">Featured</div>
    <div class="aps-view-count">412 viewed</div>
    <div class="aps-card-image cyan">
        <button class="aps-bookmark-icon"></button>
        <span>Product Dashboard Preview</span>
    </div>
    <div class="aps-card-body">
        <!-- Header with name and price -->
        <!-- Description -->
        <!-- Features list -->
        <!-- Footer with rating and CTA -->
    </div>
</article>
```

### Dynamic PHP Mapping:

| Design Element | PHP Variable | Source |
|----------------|--------------|--------|
| Product Name | `$product->title` | Database |
| Price | `$product->price` | Database |
| Original Price | `$product->original_price` | Database |
| Description | `$product->short_description` | Database |
| Features | `$product->features` | Database (array) |
| Rating | `$product->rating` | Database |
| Reviews | `$product->reviews_count` | Database |
| Badge | `$product->badge` | Database |
| Affiliate URL | `$affiliate_service->get_tracking_url($product->id)` | Service |

### Color Gradient Logic:
```php
$gradients = ['pink', 'cyan', 'purple', 'orange', 'green', 'blue'];
$gradient_class = $gradients[$product->id % count($gradients)];
```

---

## Phase 4: Update Shortcode to Fetch Products (20 min)

**File:** `src/Public/Shortcodes.php`

### Current Code (Broken):
```php
public function renderShowcase(array $atts): string {
    $template = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/showcase.php';
    // $products is NEVER populated!
    ob_start();
    include $template;
    return ob_get_clean();
}
```

### Fixed Code:
```php
public function renderShowcase(array $atts): string {
    // Parse attributes
    $atts = shortcode_atts([
        'per_page' => 12,
        'category' => '',
    ], $atts, 'aps_showcase');
    
    // Fetch products from database
    $products = $this->product_service->get_products([
        'per_page' => (int) $atts['per_page'],
        'category' => $atts['category'],
        'status' => 'publish',
    ]);
    
    // Pass to template
    $template = AFFILIATE_PRODUCT_SHOWCASE_PATH . 'templates/showcase.php';
    
    ob_start();
    include $template;
    return ob_get_clean();
}
```

---

## Phase 5: Testing Checklist (15 min)

- [ ] CSS loads without conflicts
- [ ] Sidebar filters display correctly
- [ ] Product cards show all 3 sample products
- [ ] Dynamic data (price, rating, features) displays
- [ ] Affiliate links work
- [ ] Responsive on mobile/tablet/desktop
- [ ] No console errors

---

## File Structure After Implementation

```
plan/
├── frontend-design.md          # Design Reference (Source of Truth)
└── IMPLEMENTATION_PLAN.md      # This file

templates/
├── showcase.php                # Main layout (includes sidebar)
├── product-card.php            # ⛔ DEPRECATED - remove hardcoded version
└── product-grid.php            # ⛔ DEPRECATED - use dynamic partial

wp-content/plugins/.../src/Public/partials/
├── product-card.php            # ✅ Dynamic card template
├── product-grid.php            # ✅ Grid wrapper
└── single-product.php          # Single product view

wp-content/plugins/.../assets/css/
└── showcase-frontend-isolated.css   # ✅ Extracted design CSS
```

---

## Why This Approach is Correct

| Principle | How We Follow It |
|-----------|------------------|
| **Single Source of Truth** | `plan/frontend-design.md` = The Design |
| **Separation of Concerns** | HTML structure ≠ CSS styling ≠ PHP logic |
| **DRY (Don't Repeat)** | One template renders all products dynamically |
| **Progressive Enhancement** | Design works with/without JavaScript |
| **WordPress Best Practices** | Uses `wp_enqueue_style`, `esc_html`, `esc_url` |

---

## Quick Start Commands

```bash
# Step 1: Extract CSS
# Copy lines 8-607 from plan/frontend-design.md to CSS file
# Replace class names with .aps- prefix

# Step 2: Update templates
# Edit: templates/showcase.php
# Edit: src/Public/partials/product-card.php
# Edit: src/Public/Shortcodes.php

# Step 3: Clear cache and test
# Refresh WordPress page with [aps_showcase] shortcode
```

---

## Questions to Answer Before Starting

1. **Do you want to keep all 3 sample cards OR make it dynamic with DB products?**
   - Option A: Hardcode all 3 cards (static)
   - Option B: Fetch from database (dynamic) ← **Recommended**

2. **Which products should display initially?**
   - All published products?
   - Only featured products?
   - Specific category?

3. **Do you need the sidebar filters to work (AJAX) or just display?**
   - Static display only?
   - Full filtering functionality?

Once you confirm these, I'll implement Phase 1 for you.
