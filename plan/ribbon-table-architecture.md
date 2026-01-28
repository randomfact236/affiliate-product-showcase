# Ribbon Table Architecture Plan

## Overview

The ribbon table follows a **hybrid architecture** combining WordPress native taxonomy columns with custom plugin columns. This approach leverages WordPress core functionality while adding plugin-specific features.

---

## 📋 Table Structure

### WordPress Native Columns (Read-Only)

| Column | Type | Source | Description | Customizable |
|---------|-------|---------|-------------|--------------|
| **Name** | Native | WordPress Core | Ribbon name with link to edit page | ✅ Yes (via CSS/JS) |
| **Slug** | Native | WordPress Core | URL-friendly version of name | ❌ No |
| **Description** | Native | WordPress Core | Text description (hidden in UI) | ❌ No |
| **Posts** | Native | WordPress Core | Count of products using this ribbon | ⚠️ Replaced by custom |

**Note:** WordPress renders these columns automatically. We CANNOT override their content directly via filters.

---

### Custom Columns (Plugin-Specific)

| Column | Type | Source | Description | Customizable |
|---------|-------|---------|-------------|--------------|
| **Color** | Custom | `_aps_ribbon_color` meta | Text color (hex code) | ✅ Yes |
| **Background** | Custom | `_aps_ribbon_bg_color` meta | Background color (hex code) | ✅ Yes |
| **Icon** | Custom | `_aps_ribbon_icon` meta | Icon identifier | ✅ Yes |
| **Status** | Custom | `_aps_ribbon_status` meta | Published/Draft/Trashed | ✅ Yes |
| **Count** | Custom | Override | Product count with custom display | ✅ Yes |

**Note:** These columns are fully controlled by the plugin.

---

## 🔧 Architecture Layers

### Layer 1: WordPress Core (Native Table)

```
┌─────────────────────────────────────────────────────────────┐
│                  WordPress Native Layer                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────┐│
│  │   Name   │  │   Slug   │  │ Desc.    │  │Posts ││
│  └──────────┘  └──────────┘  └──────────┘  └──────┘│
│                                                            │
│  - Automatically rendered by WordPress                      │
│  - Cannot override content via filters                      │
│  - Base functionality for all taxonomies                    │
└─────────────────────────────────────────────────────────────┘
```

### Layer 2: Plugin Custom Columns (Custom Table)

```
┌─────────────────────────────────────────────────────────────┐
│                   Plugin Custom Layer                      │
│  ┌────────┐  ┌───────────┐  ┌──────┐  ┌────────┐│
│  │ Color  │  │Background │  │Icon  │  │Status ││
│  └────────┘  └───────────┘  └──────┘  └────────┘│
│                                                            │
│  - Fully controlled by plugin                            │
│  - Rendered via custom filters                             │
│  - Plugin-specific functionality                            │
└─────────────────────────────────────────────────────────────┘
```

### Layer 3: Dynamic Styling (JavaScript Layer)

```
┌─────────────────────────────────────────────────────────────┐
│              Dynamic Styling Layer                          │
│                                                            │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Apply colors to native "Name" column       │   │
│  │  via data attributes + JavaScript             │   │
│  └──────────────────────────────────────────────────┘   │
│                                                            │
│  - Enhances native columns                                  │
│  - Client-side styling                                      │
│  - Per-row dynamic values                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🏗️ Implementation Architecture

### Step 1: Define Columns

**WordPress Native:**
- Automatically provided by WordPress
- No code needed to create

**Custom Columns:**
```php
public function add_custom_columns( array $columns ): array {
    // Call parent for shared columns (status, count)
    $columns = parent::add_custom_columns( $columns );
    
    // Insert custom columns after 'slug'
    $new_columns = [];
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        
        if ( $key === 'slug' ) {
            $new_columns['color'] = __( 'Color', 'affiliate-product-showcase' );
            $new_columns['bg_color'] = __( 'Background', 'affiliate-product-showcase' );
            $new_columns['icon'] = __( 'Icon', 'affiliate-product-showcase' );
        }
    }
    
    return $new_columns;
}
```

### Step 2: Render Custom Columns

```php
public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
    // Custom columns - full control
    if ( $column_name === 'color' ) {
        $color = get_term_meta( $term_id, '_aps_ribbon_color', true );
        return $color 
            ? sprintf( '<span style="background-color: %s;" title="%s"></span>', 
                       $color, $color )
            : '-';
    }
    
    if ( $column_name === 'bg_color' ) {
        $bg_color = get_term_meta( $term_id, '_aps_ribbon_bg_color', true );
        return $bg_color 
            ? sprintf( '<span style="background-color: %s;" title="%s"></span>', 
                       $bg_color, $bg_color )
            : '-';
    }
    
    // Native columns - pass through
    return $content;
}
```

### Step 3: Enhance Native Columns (Hybrid)

**PHP Side - Embed Data:**
```php
public function embed_color_data_in_name( string $name, \WP_Term $term ): string {
    $bg_color = get_term_meta( $term->term_id, '_aps_ribbon_bg_color', true );
    $text_color = get_term_meta( $term->term_id, '_aps_ribbon_color', true );
    
    $data_attrs = '';
    if ( $bg_color ) $data_attrs .= sprintf( ' data-ribbon-bg="%s"', esc_attr( $bg_color ) );
    if ( $text_color ) $data_attrs .= sprintf( ' data-ribbon-text="%s"', esc_attr( $text_color ) );
    
    return sprintf( '<span%s>%s</span>', $data_attrs, $name );
}
```

**JavaScript Side - Apply Colors:**
```javascript
function applyRibbonNameColors() {
    $('.column-name span[data-ribbon-bg]').each(function() {
        var $span = $(this);
        var $nameLink = $span.closest('tr').find('.column-name a');
        
        var bgColor = $span.data('ribbon-bg');
        var textColor = $span.data('ribbon-text');
        
        $nameLink.css({
            'padding': '4px 12px',
            'background-color': bgColor,
            'color': textColor,
            'border-radius': '4px',
            'font-weight': '600'
        });
    });
}
```

---

## 🎯 Final Table Layout

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  Name (Native + Enhanced)  │ Slug (Native) │ Color (Custom) │ ... │
├─────────────────────────────────────────────────────────────────────────────────────┤
│  [🔴 BEST SELLER]        │ best-seller      │  [Red Swatch]    │ ... │
│  [🔵 NEW ARRIVAL]         │ new-arrival      │  [Blue Swatch]   │ ... │
│  [🟢 SALE]               │ sale             │  [Green Swatch]  │ ... │
└─────────────────────────────────────────────────────────────────────────────────────┘

Legend:
  🔴🔵🟢 = Dynamic colors applied via JavaScript
  [ ] = Native column content (enhanced)
  [Swatch] = Custom column content
```

---

## ✅ What's Customizable

### Native Columns (Limited Customization)

| Column | Method | Extent |
|---------|---------|---------|
| **Name** | CSS + JavaScript | ✅ Can style appearance, apply dynamic colors |
| **Slug** | CSS | ✅ Can style appearance |
| **Description** | CSS | ⚠️ Hidden in UI |
| **Posts** | CSS | ⚠️ Replaced by custom count column |

### Custom Columns (Full Customization)

| Column | Method | Extent |
|---------|---------|---------|
| **Color** | PHP + CSS | ✅ Full control over content and styling |
| **Background** | PHP + CSS | ✅ Full control over content and styling |
| **Icon** | PHP + CSS | ✅ Full control over content and styling |
| **Status** | PHP + JS + CSS | ✅ Full control with AJAX updates |
| **Count** | PHP | ✅ Custom display, counts |

---

## 🔄 Data Flow

```
User Creates Ribbon
        ↓
Save to Database
        ↓
┌───────────────────────────────┐
│   WordPress Query Terms       │
│   (Native + Custom Meta)     │
└───────────────────────────────┘
        ↓
┌───────────────────────────────┐
│   Render Table Rows         │
│   - Native: WordPress      │
│   - Custom: Plugin        │
└───────────────────────────────┘
        ↓
┌───────────────────────────────┐
│   Apply Filters           │
│   - Embed color data     │
│   - Render custom cols   │
└───────────────────────────────┘
        ↓
┌───────────────────────────────┐
│   JavaScript Enhancement  │
│   - Read data attrs     │
│   - Apply dynamic styles│
└───────────────────────────────┘
        ↓
Final Table Display
```

---

## 🎨 Styling Strategy

### CSS Styling (Base Styles)

```css
/* Native columns - base styling */
.column-name a {
    /* Can style appearance but colors are dynamic */
    font-weight: 600;
    text-decoration: none;
}

/* Custom columns - full control */
.column-color span {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 4px;
}
```

### JavaScript Styling (Dynamic Colors)

```javascript
/* Apply per-row colors */
$nameLink.css({
    'background-color': bgColor,  // Dynamic per ribbon
    'color': textColor,          // Dynamic per ribbon
});
```

---

## 🔑 Key Takeaways

### 1. Hybrid Architecture
- **WordPress Native:** Base table structure, name, slug columns
- **Plugin Custom:** Color, background, icon, status columns
- **Dynamic Styling:** JavaScript enhances native columns

### 2. Customization Limits
- **Native Columns:** Cannot override content, can only style
- **Custom Columns:** Full control over content and styling
- **Dynamic Colors:** Require JavaScript for per-row values

### 3. Best Practices
- ✅ Use WordPress native columns for base functionality
- ✅ Add custom columns for plugin-specific features
- ✅ Use JavaScript to enhance native columns dynamically
- ✅ Keep data in PHP, apply styling in JavaScript

### 4. Why This Architecture?

**Advantages:**
- Leverages WordPress core functionality
- Maintains familiar UI for users
- Adds plugin-specific features seamlessly
- Performance: Data in PHP, styling in JavaScript
- Extensible: Easy to add more custom columns

**Limitations:**
- Cannot completely replace native columns
- JavaScript required for dynamic native column styling
- More complex than pure custom table

---

## 📊 Comparison: Native vs Custom

| Feature | Native Columns | Custom Columns |
|----------|----------------|----------------|
| **Content Override** | ❌ Impossible | ✅ Full control |
| **Styling** | ✅ CSS + JS | ✅ CSS + JS |
| **Dynamic Values** | ⚠️ Via data attrs | ✅ Direct access |
| **Performance** | ✅ Optimized by WP | ✅ Optimized by plugin |
| **User Familiarity** | ✅ Familiar UI | ✅ Consistent with plugin |
| **Extensibility** | ⚠️ Limited | ✅ Unlimited |

---

## 🎯 Implementation Checklist

- [x] Define native columns (WordPress handles automatically)
- [x] Define custom columns in `add_custom_columns()`
- [x] Render custom columns in `render_custom_columns()`
- [x] Hide unnecessary native columns (description)
- [x] Enhance native name column with JavaScript
- [x] Embed color data attributes in name
- [x] Apply dynamic styles via JavaScript
- [x] Style custom columns with CSS
- [x] Add status toggle functionality
- [x] Add row actions (draft, trash, restore)

---

## 🚀 Future Enhancements

1. **Sortable Columns:** Add sorting for custom columns
2. **Search Filters:** Filter by color or status
3. **Bulk Actions:** Bulk color updates
4. **Inline Editing:** Edit colors directly in table
5. **Preview Column:** Show ribbon badge preview
6. **Export:** Export ribbons with colors

---

**Version:** 1.0.0  
**Last Updated:** 2026-01-28  
**Author:** Development Team