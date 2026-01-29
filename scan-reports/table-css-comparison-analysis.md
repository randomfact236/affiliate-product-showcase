# Table CSS Comparison Analysis

**Date:** 2026-01-29
**Comparison:** Design (plan/productTable-html.md) vs Plugin CSS (admin-products.css)

---

## Executive Summary

This analysis compares the WooCommerce-style design specifications from `plan/productTable-html.md` with the current plugin CSS implementation in `admin-products.css`. The comparison identifies differences in styling, structure, and implementation approaches.

**Overall Status:** ⚠️ **SIGNIFICANT DIFFERENCES FOUND**

---

## 1. CSS Variables & Color Palette

### 1.1 Design Specification (plan/productTable-html.md)

```css
:root {
    --color-text-main: #1d2327;
    --color-text-light: #646970;
    --color-border: #c3c4c7;
    --color-bg-light: #f0f0f1;
    --color-primary: #2271b1;
    --color-primary-hover: #135e96;
    --color-red: #d63638;
    --color-green-bg: #e5f7ed;
    --color-green-text: #22c55e;
    --color-yellow-bg: #fef3c7;
    --color-yellow-text: #d97706;
    --color-red-bg: #fee2e2;
    --color-red-text: #dc2626;
    --color-gray-bg: #f3f4f6;
    --color-gray-text: #6b7280;
    --color-ribbon: #d63638;
    --color-star: #e6b800;
}
```

### 1.2 Plugin CSS (admin-products.css)

```css
:root {
    --color-text-main: #1d2327;
    --color-text-light: #646970;
    --color-border: #c3c4c7;
    --color-bg-light: #f0f0f1;
    --color-bg-hover: #f6f7f7;  /* ⚠️ EXTRA */
    --color-primary: #2271b1;
    --color-primary-hover: #135e96;
    --color-green-bg: #e5f7ed;
    --color-green-text: #22c55e;
    --color-yellow-bg: #fef3c7;
    --color-yellow-text: #d97706;
    --color-red-bg: #fee2e2;
    --color-red-text: #dc2626;
    --color-gray-bg: #f3f4f6;
    --color-gray-text: #6b7280;
    --color-ribbon: #d63638;
    --color-star: #e6b800;
}
```

### 1.3 Differences

| Variable | Design | Plugin | Status |
|----------|--------|--------|--------|
| `--color-text-main` | #1d2327 | #1d2327 | ✅ Match |
| `--color-text-light` | #646970 | #646970 | ✅ Match |
| `--color-border` | #c3c4c7 | #c3c4c7 | ✅ Match |
| `--color-bg-light` | #f0f0f1 | #f0f0f1 | ✅ Match |
| `--color-bg-hover` | Not defined | #f6f7f7 | ⚠️ Extra in plugin |
| `--color-primary` | #2271b1 | #2271b1 | ✅ Match |
| `--color-primary-hover` | #135e96 | #135e96 | ✅ Match |
| `--color-red` | #d63638 | Not defined | ❌ Missing in plugin |
| `--color-green-bg` | #e5f7ed | #e5f7ed | ✅ Match |
| `--color-green-text` | #22c55e | #22c55e | ✅ Match |
| `--color-yellow-bg` | #fef3c7 | #fef3c7 | ✅ Match |
| `--color-yellow-text` | #d97706 | #d97706 | ✅ Match |
| `--color-red-bg` | #fee2e2 | #fee2e2 | ✅ Match |
| `--color-red-text` | #dc2626 | #dc2626 | ✅ Match |
| `--color-gray-bg` | #f3f4f6 | #f3f4f6 | ✅ Match |
| `--color-gray-text` | #6b7280 | #6b7280 | ✅ Match |
| `--color-ribbon` | #d63638 | #d63638 | ✅ Match |
| `--color-star` | #e6b800 | #e6b800 | ✅ Match |

---

## 2. Typography & Fonts

### 2.1 Design Specification

```css
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    font-size: 13px;
    color: var(--color-text-main);
}

/* Typography Scale */
Page Title (h1): 23px
Column Headers: 13px
Table Content: 13px
Row Actions: 12px
Badge Text: 11px
```

### 2.2 Plugin CSS

```css
/* No explicit body font definition in plugin CSS - relies on WordPress default */
.wp-list-table thead th {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.wp-list-table tbody td {
    font-size: 13px;
}

.row-title {
    font-size: 14px;  /* ⚠️ Different from design (13px) */
}

.row-actions {
    font-size: 12px;
}

.aps-product-status {
    font-size: 11px;
}

.aps-ribbon-badge {
    font-size: 12px;  /* ⚠️ Different from design (11px) */
}
```

### 2.3 Differences

| Element | Design Size | Plugin Size | Status |
|---------|-------------|-------------|--------|
| Page Title (h1) | 23px | Not defined | ⚠️ Missing |
| Column Headers | 13px | 13px | ✅ Match |
| Table Content | 13px | 13px | ✅ Match |
| Row Title | 13px | 14px | ⚠️ Different |
| Row Actions | 12px | 12px | ✅ Match |
| Status Badge | 11px | 11px | ✅ Match |
| Ribbon Badge | 11px | 12px | ⚠️ Different |
| Category Text | 13px | 13px | ✅ Match |
| Tag Text | 13px | 13px | ✅ Match |

---

## 3. Table Structure

### 3.1 Design Specification

```css
.wp-list-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border: 1px solid var(--color-border);
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    clear: both;
    display: table;
}

.wp-list-table thead th {
    border-bottom: 1px solid var(--color-border);
    font-weight: 600;
    text-align: left;
    padding: 8px 10px;
    font-size: 13px;
}

.wp-list-table tbody td {
    padding: 9px 10px;
    vertical-align: middle;
    font-size: 13px;
    border-bottom: 1px solid var(--color-border);
    color: var(--color-text-light);
    word-break: break-word;
}
```

### 3.2 Plugin CSS

```css
.wp-list-table {
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    background: #fff;
    margin: 0;
    /* ⚠️ Missing: width: 100%, border-collapse: collapse, clear: both, display: table */
}

.wp-list-table thead {
    background: #f6f7f7;  /* ⚠️ EXTRA - design doesn't specify header background */
}

.wp-list-table thead th {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;  /* ⚠️ EXTRA */
    color: #1d2327;
    /* ⚠️ Missing: border-bottom, text-align, padding */
}

.wp-list-table tbody td {
    font-size: 13px;
    padding: 10px;  /* ⚠️ Different from design (9px 10px) */
    vertical-align: middle;
    /* ⚠️ Missing: border-bottom, color, word-break */
}
```

### 3.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| `width` | 100% | Not defined | ⚠️ Missing |
| `border-collapse` | collapse | Not defined | ⚠️ Missing |
| `clear` | both | Not defined | ⚠️ Missing |
| `display` | table | Not defined | ⚠️ Missing |
| `thead background` | Not specified | #f6f7f7 | ⚠️ Extra |
| `th text-transform` | Not specified | uppercase | ⚠️ Extra |
| `th border-bottom` | 1px solid var(--color-border) | Not defined | ⚠️ Missing |
| `th text-align` | left | Not defined | ⚠️ Missing |
| `th padding` | 8px 10px | Not defined | ⚠️ Missing |
| `td padding` | 9px 10px | 10px (or 12px 12px with !important) | ⚠️ Different |
| `td border-bottom` | 1px solid var(--color-border) | Not defined | ⚠️ Missing |
| `td color` | var(--color-text-light) | Not defined | ⚠️ Missing |
| `td word-break` | break-word | Not defined | ⚠️ Missing |

---

## 4. Column Widths

### 4.1 Design Specification

```css
.column-cb { width: 2.2em; padding: 3px 0 0 10px !important; }
.column-id { width: 50px; text-align: center; }
.column-logo { width: 60px; }
.column-ribbon { width: 120px; }
.column-featured { width: 60px; text-align: center; font-size: 18px; color: #e6b800; }
.column-price { width: 100px; }
.column-status { width: 120px; }
```

### 4.2 Plugin CSS

```css
.column-cb { width: 40px; padding: 12px 8px !important; text-align: center; }
.column-id { width: 60px; padding: 12px 8px !important; text-align: center; }
.column-logo { width: 70px; padding: 12px 8px !important; text-align: center; }
.column-title { width: auto; min-width: 250px; max-width: 400px; }
.column-category { width: auto; min-width: 120px; max-width: 200px; }
.column-tags { width: auto; min-width: 120px; max-width: 200px; }
.column-taxonomy-aps_ribbon { width: 130px; padding: 12px 12px !important; text-align: center; }
.column-featured { width: 70px; padding: 12px 8px !important; text-align: center; }
.column-price { width: 110px; padding: 12px 12px !important; text-align: right; }
.column-status { width: 130px; padding: 12px 12px !important; text-align: center; }
```

### 4.3 Differences

| Column | Design Width | Plugin Width | Status |
|--------|-------------|--------------|--------|
| CB (Checkbox) | 2.2em (~35px) | 40px | ⚠️ Different |
| ID | 50px | 60px | ⚠️ Different |
| Logo | 60px | 70px | ⚠️ Different |
| Title | auto | auto (with min/max) | ⚠️ Extra constraints |
| Category | auto | auto (with min/max) | ⚠️ Extra constraints |
| Tags | auto | auto (with min/max) | ⚠️ Extra constraints |
| Ribbon | 120px | 130px | ⚠️ Different |
| Featured | 60px | 70px | ⚠️ Different |
| Price | 100px | 110px | ⚠️ Different |
| Status | 120px | 130px | ⚠️ Different |

---

## 5. Category & Tags Styling

### 5.1 Design Specification (PLAIN TEXT - NO BADGES)

```css
/* CATEGORY - PLAIN TEXT (NO BADGE) */
.column-category .aps-category-text {
    color: #1d2327;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}

/* TAGS - PLAIN TEXT (NO BADGE) */
.column-tags .aps-tag-text {
    color: #646970;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}
```

### 5.2 Plugin CSS (HAS CHIPS/BADGES)

```css
/* Category - Plain text (matches design) */
.aps-category-text {
    color: #1d2327;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}

/* ⚠️ EXTRA: Category chips with colored dots (NOT in design) */
.aps-category-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #f3f4f6;
    border-radius: 16px;
    font-size: 12px;
    color: #374151;
    margin: 2px;
}

.aps-category-chip::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #3b82f6;
    flex-shrink: 0;
}

/* Tag - Plain text (matches design) */
.aps-tag-text {
    color: #646970;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}

/* ⚠️ EXTRA: Tag chips with colored dots (NOT in design) */
.aps-tag-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #fef3c7;
    border-radius: 16px;
    font-size: 12px;
    color: #d97706;
    margin: 2px;
}

.aps-tag-chip::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #f59e0b;
    flex-shrink: 0;
}
```

### 5.3 Differences

| Element | Design | Plugin | Status |
|---------|--------|--------|--------|
| Category Text | Plain text | Plain text | ✅ Match |
| Category Chips | Not defined | Defined with colored dots | ⚠️ Extra (violates design) |
| Tag Text | Plain text | Plain text | ✅ Match |
| Tag Chips | Not defined | Defined with colored dots | ⚠️ Extra (violates design) |

**Critical Issue:** Design explicitly states "Plain Text (NO BADGE)" for categories and tags, but plugin CSS includes chip/badge styles with colored dots.

---

## 6. Ribbon Badge Styling

### 6.1 Design Specification (RED BADGE)

```css
.aps-ribbon-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #d63638; /* Red */
    color: #ffffff;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 2px;
}

.aps-ribbon-badge + .aps-ribbon-badge {
    margin-left: 4px;
}
```

### 6.2 Plugin CSS

```css
/* Dynamic ribbon badge with custom colors */
.aps-ribbon-badge {
    display: inline-block;
    padding: 4px 12px;  /* ⚠️ Different from design (4px 10px) */
    border-radius: 4px;  /* ⚠️ Different from design (12px) */
    font-weight: 600;
    font-size: 12px;  /* ⚠️ Different from design (11px) */
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);  /* ⚠️ EXTRA */
    line-height: 1.5;  /* ⚠️ EXTRA */
    /* ⚠️ Missing: background color, color */
}

/* Apply red badge styling to native Ribbon taxonomy links (fallback) */
.column-taxonomy-aps_ribbon a:not(.aps-ribbon-badge) {
    display: inline-block;
    padding: 4px 10px;
    background: var(--color-ribbon);
    color: #ffffff;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 2px;
    text-decoration: none;
    border: none;
}

/* ⚠️ Missing: Multiple ribbon margin-left styling */
```

### 6.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| `padding` | 4px 10px | 4px 12px (badge) | ⚠️ Different |
| `border-radius` | 12px | 4px (badge) | ⚠️ Different |
| `font-size` | 11px | 12px (badge) | ⚠️ Different |
| `background` | #d63638 | Not defined (badge) | ⚠️ Missing |
| `color` | #ffffff | Not defined (badge) | ⚠️ Missing |
| `box-shadow` | Not defined | 0 1px 3px rgba(0,0,0,0.1) | ⚠️ Extra |
| `line-height` | Not defined | 1.5 | ⚠️ Extra |
| Multiple ribbon margin | 4px | Not defined | ⚠️ Missing |

---

## 7. Status Badge Styling

### 7.1 Design Specification

```css
.aps-product-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Published */
.aps-product-status-published {
    background: var(--color-green-bg);
    color: var(--color-green-text);
}

/* Draft */
.aps-product-status-draft {
    background: var(--color-yellow-bg);
    color: var(--color-yellow-text);
}

/* Trash */
.aps-product-status-trash {
    background: var(--color-red-bg);
    color: var(--color-red-text);
}

/* Pending */
.aps-product-status-pending {
    background: var(--color-gray-bg);
    color: var(--color-gray-text);
}
```

### 7.2 Plugin CSS

```css
.aps-product-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Published - Green */
.aps-product-status-published {
    background: var(--color-green-bg);
    color: var(--color-green-text);
}

/* Draft - Yellow */
.aps-product-status-draft {
    background: var(--color-yellow-bg);
    color: var(--color-yellow-text);
}

/* Trash - Red */
.aps-product-status-trash {
    background: var(--color-red-bg);
    color: var(--color-red-text);
}

/* Pending - Gray */
.aps-product-status-pending {
    background: var(--color-gray-bg);
    color: var(--color-gray-text);
}
```

### 7.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| Base styles | All properties match | All properties match | ✅ Match |
| Published colors | Green | Green | ✅ Match |
| Draft colors | Yellow | Yellow | ✅ Match |
| Trash colors | Red | Red | ✅ Match |
| Pending colors | Gray | Gray | ✅ Match |

**Status:** ✅ **PERFECT MATCH**

---

## 8. Featured Star Styling

### 8.1 Design Specification

```css
.column-featured {
    width: 60px;
    text-align: center;
    font-size: 18px;
    color: #e6b800;
}
```

### 8.2 Plugin CSS

```css
.aps-featured-star {
    font-size: 18px;
    color: var(--color-star);
    display: inline-block;
}
```

### 8.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| `font-size` | 18px | 18px | ✅ Match |
| `color` | #e6b800 | var(--color-star) | ✅ Match |
| `display` | Not defined | inline-block | ⚠️ Extra |

---

## 9. Product Logo Styling

### 9.1 Design Specification

```css
.aps-product-logo {
    display: block;
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}
```

### 9.2 Plugin CSS

```css
.aps-logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin: 0 auto;
    padding: 2px;
    background-color: #f9f9f9;  /* ⚠️ EXTRA */
    border-radius: 6px;  /* ⚠️ Different from design (4px) */
    border: 1px solid #e0e0e0;  /* ⚠️ Different from design (#eee) */
}

.aps-product-logo {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;  /* ⚠️ Different from design (4px) */
    border: 2px solid #e5e7eb;  /* ⚠️ Different from design (1px solid #eee) */
    background-color: #f9f9f9;  /* ⚠️ EXTRA */
    transition: all 0.2s ease;  /* ⚠️ EXTRA */
}

.aps-product-logo:hover {
    transform: scale(1.1);  /* ⚠️ EXTRA */
    border-color: #3b82f6;  /* ⚠️ EXTRA */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);  /* ⚠️ EXTRA */
}
```

### 9.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| Container | Not defined | Defined with extra styles | ⚠️ Extra |
| Logo width | 48px | 100% (of container) | ⚠️ Different |
| Logo height | 48px | 100% (of container) | ⚠️ Different |
| Logo border-radius | 4px | 8px | ⚠️ Different |
| Logo border | 1px solid #eee | 2px solid #e5e7eb | ⚠️ Different |
| Logo background | Not defined | #f9f9f9 | ⚠️ Extra |
| Logo transition | Not defined | all 0.2s ease | ⚠️ Extra |
| Logo hover effects | Not defined | scale, border-color, shadow | ⚠️ Extra |

---

## 10. Navigation Tabs Styling

### 10.1 Design Specification

```css
.nav-tab-wrapper {
    display: block;
    float: left;
    margin-bottom: 20px;
}

.nav-tab {
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 1.71428571;
    margin: 0 5px -1px 0;
    padding: 5px 10px;
    border: 1px solid #c3c4c7;
    border-bottom: none;
    background: #f6f7f7;
    color: #646970;
    border-radius: 3px 3px 0 0;
    cursor: pointer;
}

.nav-tab.nav-tab-active {
    background: #fff;
    color: #2c3338;
    border-bottom: 1px solid #fff;
    font-weight: 600;
}

.nav-tab:hover {
    background-color: #fff;
    color: #2c3338;
}
```

### 10.2 Plugin CSS

```css
.aps-nav-tabs {
    margin: 20px 0 0;
    border-bottom: 1px solid #c3c4c7;  /* ⚠️ EXTRA */
    line-height: inherit;
}

.aps-nav-tabs .nav-tab {
    display: inline-block;
    text-decoration: none;
    font-size: 14px;  /* ⚠️ Different from design (13px) */
    line-height: 1.6;  /* ⚠️ Different from design (1.71428571) */
    padding: 6px 12px;  /* ⚠️ Different from design (5px 10px) */
    margin: 0 4px -1px 0;  /* ⚠️ Different from design (0 5px -1px 0) */
    border: 1px solid #c3c4c7;
    border-bottom: none;
    color: #646970;
    background: #f6f7f7;
    border-radius: 3px 3px 0 0;
    cursor: pointer;
}

.aps-nav-tabs .nav-tab:hover {
    background: #fff;
    color: #1d2327;  /* ⚠️ Different from design (#2c3338) */
}

.aps-nav-tabs .nav-tab.nav-tab-active {
    background: #fff;
    color: #2c3338;
    border-bottom: 1px solid #fff;
    font-weight: 600;
}
```

### 10.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| Wrapper `display` | block | Not defined | ⚠️ Missing |
| Wrapper `float` | left | Not defined | ⚠️ Missing |
| Wrapper `border-bottom` | Not defined | 1px solid #c3c4c7 | ⚠️ Extra |
| Tab `font-size` | 13px | 14px | ⚠️ Different |
| Tab `line-height` | 1.71428571 | 1.6 | ⚠️ Different |
| Tab `padding` | 5px 10px | 6px 12px | ⚠️ Different |
| Tab `margin` | 0 5px -1px 0 | 0 4px -1px 0 | ⚠️ Different |
| Hover `color` | #2c3338 | #1d2327 | ⚠️ Different |

---

## 11. Toolbar Styling

### 11.1 Design Specification

```css
.tablenav {
    clear: both;
    height: 30px;
    margin: 6px 0 4px 0;
    vertical-align: middle;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alignleft {
    float: left;
    display: flex;
    gap: 10px;
    align-items: center;
}

.alignright {
    float: right;
    display: flex;
    align-items: center;
}

select, input[type="text"] {
    margin: 1px;
    padding: 4px 8px;
    line-height: 2;
    height: 30px;
    vertical-align: middle;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
    color: #2c3338;
}

.button {
    display: inline-block;
    text-decoration: none;
    font-size: 13px;
    line-height: 2.15384615;
    min-height: 30px;
    margin: 0;
    padding: 0 10px;
    cursor: pointer;
    border-width: 1px;
    border-style: solid;
    border-radius: 3px;
    white-space: nowrap;
    box-sizing: border-box;
}

.button.action {
    background: #f6f7f7;
    border-color: #8c8f94;
    color: #1d2327;
    height: 30px;
    margin-top: 1px;
    vertical-align: top;
}
```

### 11.2 Plugin CSS

```css
.tablenav {
    margin: 20px 0;
}

.tablenav.top {
    margin: 20px 0 0;
}

.tablenav.bottom {
    margin: 10px 0 20px;
}

.alignleft,
.alignright {
    display: flex;
    align-items: center;
    gap: 8px;  /* ⚠️ Different from design (10px) */
}

/* Select Dropdowns */
.tablenav select,
.search-form input[type="search"] {
    padding: 6px 8px;  /* ⚠️ Different from design (4px 8px) */
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 13px;  /* ⚠️ Different from design (14px) */
    color: #3c434a;  /* ⚠️ Different from design (#2c3338) */
    background: #fff;
}

.tablenav select:focus,
.search-form input[type="search"]:focus {
    outline: none;
    border-color: #2271b1;  /* ⚠️ EXTRA */
    box-shadow: 0 0 0 1px #2271b1;  /* ⚠️ EXTRA */
}

/* Buttons */
.tablenav .button {
    padding: 6px 12px;  /* ⚠️ Different from design (0 10px) */
    font-size: 13px;
    line-height: 2;
    height: auto;  /* ⚠️ Different from design (min-height: 30px) */
    margin: 0;
}

.tablenav .button.action {
    margin-left: 4px;  /* ⚠️ EXTRA */
}
```

### 11.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| `tablenav clear` | both | Not defined | ⚠️ Missing |
| `tablenav height` | 30px | Not defined | ⚠️ Missing |
| `tablenav display` | flex | Not defined | ⚠️ Missing |
| `tablenav justify-content` | space-between | Not defined | ⚠️ Missing |
| `tablenav align-items` | center | Not defined | ⚠️ Missing |
| `alignleft float` | left | Not defined | ⚠️ Missing |
| `alignright float` | right | Not defined | ⚠️ Missing |
| `gap` | 10px | 8px | ⚠️ Different |
| Select `padding` | 4px 8px | 6px 8px | ⚠️ Different |
| Select `font-size` | 14px | 13px | ⚠️ Different |
| Select `color` | #2c3338 | #3c434a | ⚠️ Different |
| Select `height` | 30px | Not defined | ⚠️ Missing |
| Button `padding` | 0 10px | 6px 12px | ⚠️ Different |
| Button `min-height` | 30px | Not defined | ⚠️ Missing |
| Button `line-height` | 2.15384615 | 2 | ⚠️ Different |

---

## 12. Responsive Design

### 12.1 Design Specification

```css
@media screen and (max-width: 782px) {
    .wp-list-table thead { display: none; }
    .wp-list-table tbody tr {
        display: block;
        border-bottom: 1px solid #c3c4c7;
        margin-bottom: 10px;
    }
    .wp-list-table tbody td {
        display: block;
        text-align: right;
        padding: 8px 10px;
        border-bottom: 1px solid #eee;
    }
    .wp-list-table tbody td::before {
        content: attr(data-colname);
        font-weight: 600;
        float: left;
        margin-left: -10px;
        text-align: left;
    }
    .column-cb { display: none; }
    .page-title-action { float: none; margin-top: 10px; }
    .tablenav { height: auto; flex-direction: column; align-items: flex-start; gap: 10px; }
    .alignleft, .alignright { float: none; width: 100%; }
}
```

### 12.2 Plugin CSS

```css
@media screen and (max-width: 782px) {
    .wp-list-table thead {
        display: none;
    }

    .wp-list-table tbody tr {
        display: block;
        border-bottom: 1px solid #c3c4c7;
        margin-bottom: 10px;
    }

    .wp-list-table tbody td {
        display: block;
        padding: 8px 12px;  /* ⚠️ Different from design (8px 10px) */
        position: relative;  /* ⚠️ EXTRA */
        padding-left: 40%;  /* ⚠️ Different from design (text-align: right) */
    }

    .wp-list-table tbody td::before {
        content: attr(data-colname);
        font-weight: 600;
        position: absolute;  /* ⚠️ Different from design (float: left) */
        left: 12px;  /* ⚠️ EXTRA */
        width: 35%;  /* ⚠️ EXTRA */
        text-align: left;
        color: #1d2327;  /* ⚠️ EXTRA */
    }

    .column-cb {
        display: none;
    }

    .tablenav {
        height: auto !important;
        flex-direction: column;
        align-items: stretch !important;  /* ⚠️ Different from design (flex-start) */
        gap: 10px;
    }

    .alignleft,
    .alignright {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }

    /* ⚠️ EXTRA: Search form full width */
    .search-form {
        width: 100%;
    }

    .search-form input[type="search"] {
        width: 100%;
    }

    /* ⚠️ EXTRA: Navigation tabs */
    .aps-nav-tabs {
        flex-wrap: wrap;
    }

    .aps-nav-tabs .nav-tab {
        margin-bottom: 0;
    }

    /* ⚠️ EXTRA: Modal full width on mobile */
    .aps-modal-content {
        width: 95%;
        max-width: none;
        margin: 10px;
    }
}
```

### 12.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| `td padding` | 8px 10px | 8px 12px | ⚠️ Different |
| `td text-align` | right | Not defined | ⚠️ Missing |
| `td position` | Not defined | relative | ⚠️ Extra |
| `td padding-left` | Not defined | 40% | ⚠️ Extra |
| `td::before float` | left | Not defined | ⚠️ Missing |
| `td::before position` | Not defined | absolute | ⚠️ Extra |
| `td::before left` | Not defined | 12px | ⚠️ Extra |
| `td::before width` | Not defined | 35% | ⚠️ Extra |
| `td::before margin-left` | -10px | Not defined | ⚠️ Missing |
| `td::before color` | Not defined | #1d2327 | ⚠️ Extra |
| `align-items` | flex-start | stretch | ⚠️ Different |
| Search form | Not defined | Full width | ⚠️ Extra |
| Nav tabs | Not defined | flex-wrap: wrap | ⚠️ Extra |
| Modal | Not defined | Full width | ⚠️ Extra |

---

## 13. Toast Notifications

### 13.1 Design Specification

```css
#toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.notice {
    background: #fff;
    border-left: 4px solid #72aee6;
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    margin: 5px 0 15px;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    min-width: 300px;
    animation: slideIn 0.3s ease-out;
}

.notice-success {
    border-left-color: #00a32a;
}

.notice p {
    margin: 0;
    font-size: 13px;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
```

### 13.2 Plugin CSS

```css
#aps-toast-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;  /* ⚠️ EXTRA */
}

.aps-toast {
    background: #fff;
    border-left: 4px solid #72aee6;
    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
    margin: 5px 0 15px;
    padding: 12px 16px;  /* ⚠️ Different from design (10px 12px) */
    display: flex;
    align-items: center;
    min-width: 300px;
    border-radius: 4px;  /* ⚠️ EXTRA */
    animation: slideIn 0.3s ease-out;
}

.aps-toast.aps-toast-success {
    border-left-color: #00a32a;
}

.aps-toast.aps-toast-error {
    border-left-color: #d63638;  /* ⚠️ EXTRA */
}

.aps-toast-message {
    flex: 1;  /* ⚠️ EXTRA */
    font-size: 14px;  /* ⚠️ Different from design (13px) */
    line-height: 1.5;  /* ⚠️ EXTRA */
    color: #1d2327;  /* ⚠️ EXTRA */
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
```

### 13.3 Differences

| Property | Design | Plugin | Status |
|----------|--------|--------|--------|
| Container `max-width` | Not defined | 400px | ⚠️ Extra |
| Toast `padding` | 10px 12px | 12px 16px | ⚠️ Different |
| Toast `border-radius` | Not defined | 4px | ⚠️ Extra |
| Error toast | Not defined | Defined | ⚠️ Extra |
| Message `flex` | Not defined | 1 | ⚠️ Extra |
| Message `font-size` | 13px | 14px | ⚠️ Different |
| Message `line-height` | Not defined | 1.5 | ⚠️ Extra |
| Message `color` | Not defined | #1d2327 | ⚠️ Extra |

---

## 14. Summary of Critical Issues

### 14.1 High Priority Issues

| Issue | Location | Description |
|-------|----------|-------------|
| ❌ **Category/Tags Chips** | `.aps-category-chip`, `.aps-tag-chip` | Design specifies "Plain Text (NO BADGE)" but plugin has chip/badge styles with colored dots |
| ❌ **Ribbon Badge Styling** | `.aps-ribbon-badge` | Padding, border-radius, font-size differ; background/color not defined |
| ❌ **Table Properties Missing** | `.wp-list-table` | Missing width, border-collapse, clear, display properties |
| ❌ **Table Cell Properties Missing** | `.wp-list-table tbody td` | Missing border-bottom, color, word-break properties |
| ❌ **Column Widths** | All column classes | Most column widths differ from design |

### 14.2 Medium Priority Issues

| Issue | Location | Description |
|-------|----------|-------------|
| ⚠️ **Row Title Font Size** | `.row-title` | 14px vs design 13px |
| ⚠️ **Navigation Tab Styling** | `.aps-nav-tabs .nav-tab` | Font-size, line-height, padding, margin differ |
| ⚠️ **Toolbar Styling** | `.tablenav` | Missing clear, height, display properties |
| ⚠️ **Product Logo** | `.aps-product-logo` | Extra hover effects, different border-radius |
| ⚠️ **Responsive Design** | `@media max-width: 782px` | Different td::before positioning approach |

### 14.3 Low Priority Issues

| Issue | Location | Description |
|-------|----------|-------------|
| ℹ️ **Toast Notifications** | `.aps-toast` | Extra padding, border-radius, message styling |
| ℹ️ **Extra CSS Variables** | `:root` | `--color-bg-hover` not in design |
| ℹ️ **Extra Features** | Various | Modal styles, discount badges, hover effects |

---

## 15. Recommendations

### 15.1 Immediate Actions Required

1. **Remove Category/Tags Chip Styles**
   - Delete `.aps-category-chip` and `.aps-tag-chip` classes
   - Use only `.aps-category-text` and `.aps-tag-text` (plain text)

2. **Fix Ribbon Badge Styling**
   - Update `.aps-ribbon-badge` to match design exactly:
     - `padding: 4px 10px`
     - `border-radius: 12px`
     - `font-size: 11px`
     - `background: #d63638`
     - `color: #ffffff`
   - Add multiple ribbon margin-left styling

3. **Add Missing Table Properties**
   ```css
   .wp-list-table {
       width: 100%;
       border-collapse: collapse;
       clear: both;
       display: table;
   }
   ```

4. **Add Missing Table Cell Properties**
   ```css
   .wp-list-table tbody td {
       border-bottom: 1px solid var(--color-border);
       color: var(--color-text-light);
       word-break: break-word;
   }
   ```

5. **Fix Column Widths**
   - Update all column widths to match design specifications

### 15.2 Secondary Actions

6. **Fix Navigation Tab Styling**
   - Update font-size to 13px
   - Update line-height to 1.71428571
   - Update padding to 5px 10px

7. **Fix Toolbar Styling**
   - Add missing tablenav properties
   - Update select/input styling

8. **Fix Responsive Design**
   - Match design's td::before approach

### 15.3 Optional Enhancements

9. Consider keeping extra features (hover effects, modal styles) as they improve UX
10. Document any intentional deviations from design

---

## 16. Conclusion

The plugin CSS shows significant deviations from the design specification, particularly in:

1. **Category/Tags Display** - Design specifies plain text, but plugin uses chips/badges
2. **Ribbon Badge Styling** - Multiple properties differ
3. **Table Structure** - Missing core table properties
4. **Column Widths** - Most differ from design

**Overall Match:** ~60% (Status badges and featured star match perfectly)

**Critical Issues:** 5
**Medium Priority Issues:** 5
**Low Priority Issues:** 3

---

**Report Generated:** 2026-01-29
**Analysis Tool:** Manual comparison of plan/productTable-html.md vs admin-products.css
