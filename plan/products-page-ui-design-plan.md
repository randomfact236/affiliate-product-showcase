# Products Page UI Design Plan

## Overview
Design specification for Products List page following WooCommerce-style UI with WordPress admin standards.

**Status:** Design Phase  
**Target Implementation:** WordPress Admin Page  
**Style Reference:** WooCommerce Products List  

---

## 0. Feature Classification

### 0.1 Default vs Custom Features

This design plan combines **default WordPress/WooCommerce features** with **custom plugin-specific features**. Understanding which is which helps with implementation and maintenance.

#### Default Features (WordPress/WooCommerce Standard)
These features are standard WordPress/WooCommerce admin patterns and should use built-in WordPress functions:

| Feature | Type | Implementation Method |
|----------|-------|---------------------|
| **WP_List_Table class** | WordPress Core | Use native `WP_List_Table` class for table structure |
| **Page Header** | WordPress Admin | Standard `add_menu_page()` with `page_title` parameter |
| **Navigation Tabs** | WordPress Admin | Use `<h2 class="nav-tab-wrapper">` with `.nav-tab` classes |
| **Toolbar** | WordPress Admin | Standard `.tablenav` structure with `.alignleft`/`.alignright` |
| **Bulk Actions** | WordPress Core | Use `get_bulk_actions()` method in WP_List_Table |
| **Pagination** | WordPress Core | Use `set_pagination_args()` in WP_List_Table |
| **Search Input** | WordPress Admin | Standard search box with `.search-box` class |
| **Row Actions (Edit, Quick Edit, Trash, View)** | WordPress Core | Use `get_bulk_actions()` and `row_actions()` filters |
| **Checkbox Selection** | WordPress Core | Built-in column handling for checkboxes |
| **Responsive Design** | WordPress Core | WordPress handles mobile layout with built-in media queries |
| **Toast Notifications** | WordPress Admin | Use `admin_notices` hook for notifications |

#### Custom Features (Plugin-Specific)
These features are unique to Affiliate Product Showcase plugin:

| Feature | Type | Why Custom | Placement Rationale |
|----------|-------|------------|-------------------|
| **Logo Column** | Custom | Affiliate products need visual preview | Placed 3rd (after ID) for quick visual identification |
| **Category Column** | Custom | Custom taxonomy `aps_category` | Placed after Title for product categorization context |
| **Tags Column** | Custom | Custom taxonomy `aps_tag` | Placed after Category to group related data |
| **Ribbon Column** | Custom | Custom ribbon system for marketing labels | Placed near center to draw attention to promotions |
| **Featured Column** | Custom | Featured flag for special products | Placed before Price to highlight important items |
| **Price Column** | Custom | Affiliate pricing display | Placed before Status for quick price reference |
| **Status Column** | Custom | Custom status management | Placed last (before actions) for quick status check |
| **Category Filter Dropdown** | Custom | Filter by `aps_category` taxonomy | In toolbar left, next to bulk actions for workflow efficiency |
| **Status Filter Dropdown** | Custom | Filter by custom status values | In toolbar left, grouped with category filter |
| **Tab Count Badges** | Semi-Custom | WordPress tabs, custom counts | Tab labels show dynamic product counts per status |
| **Plain Text Categories/Tags** | Custom Design | Deliberate design choice (no badges) | Improves readability for multiple items |

### 0.2 Placement Rationale

This section explains WHY each element is placed in its specific position on the page.

#### Header Section Placement
```
┌─────────────────────────────────────────────────────────────┐
│ [Page Title]                          [Primary Action] │
└─────────────────────────────────────────────────────────────┘
```

**Placement Rationale:**
- **Page Title (Left)**: Standard WordPress pattern, users expect titles on left
- **Add New Product Button (Right)**: Primary action button placed on right for:
  - Following F-shaped reading pattern (left-to-right, top-to-bottom)
  - Users scan title first, then look for action button on right
  - WordPress admin convention (matches Posts, Pages, other CPTs)
  - Easy thumb reach for right-handed mouse users

#### Navigation Tabs Placement
```
[All (4)] [Published (2)] [Drafts (1)] [Trash (1)]
```

**Placement Rationale:**
- **Below Header**: Standard WordPress admin pattern
- **Left-aligned**: Users expect navigation on left side
- **Before Table**: Filters should apply before displaying content
- **Why Tab + Dropdown Filters**: 
  - Tabs = High-level status filtering (most common use case)
  - Dropdowns = Advanced filtering (less common, more specific)
  - Tab filters are quicker (single click vs dropdown selection)
  - Follows WooCommerce pattern (familiar to e-commerce users)

#### Toolbar Placement
```
[Bulk Actions] [Category Filter] [Status Filter]    [Search 🔍]
```

**Placement Rationale:**

**Left Side (Bulk Actions + Filters):**
- **Bulk Actions First**: Most important multi-item operation
  - Primary workflow: Select → Act → Confirm
  - Placed at far left for sequential workflow (select checkboxes left, act on left)
  - Standard WordPress pattern
  
- **Category Filter Second**: Primary attribute filter
  - Users often filter by category first
  - Placed near bulk actions for combined operations
  
- **Status Filter Third**: Secondary attribute filter
  - Used less frequently than category
  - Status tabs provide quick access, dropdown for advanced use
  - Groups related filters together

**Right Side (Search + Pagination):**
- **Search Input**: Most used single-item operation
  - Placed on right for visual balance
  - Matches user expectation (search typically top-right)
  - WordPress admin standard (Posts, Pages, Media)
  
- **Pagination Below Search**: Shows result context
  - "X items" provides context for search/filter results
  - Page navigation (‹ 1 ›) for browsing results
  - Right-aligned to balance left-side filters

#### Table Column Placement
```
[CB] [ID] [Logo] [Title] [Category] [Tags] [Ribbon] [Featured] [Price] [Status]
```

**Placement Rationale (Left to Right):**

1. **Checkbox (CB)** - First column
   - **Why First**: Users select items before acting on them
   - Visual pattern: Scan → Select → Act
   - Standard WordPress placement

2. **ID** - Second column
   - **Why Second**: Unique identifier for quick reference
   - Developers/power users need ID for debugging
   - Narrow width (50px) doesn't disrupt visual flow

3. **Logo** - Third column
   - **Why Third**: Visual anchor for product recognition
   - Users identify products by image faster than text
   - Placed before Title to provide context
   - **Custom feature** (WordPress posts don't have thumbnails in list)

4. **Title** - Fourth column (Primary)
   - **Why Primary**: Most important product attribute
   - Wide column (auto-width) for long titles
   - Includes row actions for efficient workflow
   - **Standard feature** (all WordPress lists have title)

5. **Category** - Fifth column
   - **Why After Title**: Organizational context
   - Users scan title, then check category
   - **Custom feature** (specific to affiliate products)
   - Plain text (no badge) for multiple categories readability

6. **Tags** - Sixth column
   - **Why After Category**: Related metadata grouped together
   - Tags and Categories are both taxonomies
   - **Custom feature** (specific to affiliate products)
   - Plain text (no badge) for multiple tags readability

7. **Ribbon** - Seventh column
   - **Why Middle**: Marketing emphasis placement
   - Draws attention to promotions/discounts
   - Red badge creates visual break in table
   - **Custom feature** (WooCommerce has no ribbon system)
   - Placed in "middle-third" for visual prominence

8. **Featured** - Eighth column
   - **Why Before Price**: Highlights special products
   - Star icon (★) creates visual interest
   - Users look for featured items before checking price
   - **Custom feature** (WooCommerce has featured, different implementation)

9. **Price** - Ninth column
   - **Why Before Status**: Key decision-making data
   - Price is primary product attribute after title
   - **Custom feature** (WordPress posts don't show price)
   - Right-side placement follows data density pattern

10. **Status** - Last column
    - **Why Last**: Quick reference before actions
    - Users check status to determine if editing needed
    - Colored badges provide instant visual cue
    - **Custom feature** (WordPress uses published/draft/trash differently)
    - Before implicit "Actions" column (row actions)

#### Row Actions Placement
```
Title Link
  ├── Edit
  ├── Quick Edit
  ├── Trash
  └── View
```

**Placement Rationale:**
- **Under Title (on hover)**: Keeps interface clean
  - Actions only visible when user interacts with row
  - Prevents visual clutter
  - **Standard WordPress pattern**
- **Order**: Edit → Quick Edit → Trash → View
  - Most common actions first
  - Destructive action (Trash) after edit options
  - View (read-only) last

#### Footer Toolbar Placement
```
[Bulk Actions]    [Pagination]
```

**Placement Rationale:**
- **Mirrors Top Toolbar**: Consistent UX pattern
- **Bulk Actions Available**: Users can act from either top or bottom
  - Important for long tables (don't have to scroll up)
  - **Standard WordPress pattern**
- **Pagination Duplicated**: Users see pagination at both ends
  - Convenience for browsing large datasets
  - **Standard WordPress pattern**

---

## 1. Page Structure

### 1.1 Header Section
```
┌─────────────────────────────────────────────────────────────┐
│ Products                                  [Add New Product] │
└─────────────────────────────────────────────────────────────┘
```

**Components:**
- **Page Title**: "Products" (h1, 23px) - **[Default Feature]**
- **Primary Action Button**: "Add New Product" (blue primary button) - **[Default Feature]**
  - Background: `#2271b1`
  - Text: White
  - Hover: `#135e96`

### 1.2 Navigation Tabs (Status Filters)
```
[All (4)] [Published (2)] [Drafts (1)] [Trash (1)]
```

**Tabs:** - **[Default Feature]**
- All (shows all products)
- Published (status = published)
- Drafts (status = draft)
- Trash (status = trash)

**Active Tab Styling:**
- Background: `#fff`
- Color: `#2c3338`
- Font-weight: 600
- Border-bottom: 1px solid `#fff`

**Note:** Tab counts (4), (2), (1) are **[Custom Feature]** - dynamically calculated from product data

---

## 2. Toolbar Section

### 2.1 Top Toolbar Layout
```
[Bulk Actions ▼] [Category Filter ▼] [Status Filter ▼]    [Search... 🔍]
```

**Components (Left Side):**
1. **Bulk Actions Dropdown** - **[Default Feature]**
   - Options:
     - Bulk actions (default)
     - Move to Trash
     - Edit
   - Apply button (gray action button)

2. **Category Filter Dropdown** - **[Custom Feature]**
   - All Categories (default)
   - [Dynamic categories from system]

3. **Status Filter Dropdown** - **[Custom Feature]**
   - All Statuses (default)
   - Published
   - Draft
   - Trash

**Components (Right Side):**
1. **Search Input** - **[Default Feature]**
   - Placeholder: "Search products..."
   - Real-time filtering
   - Search Products button

2. **Pagination Display** - **[Default Feature]**
   - "X items" showing count
   - Page navigation (‹ 1 ›)

---

## 3. Data Table Design

### 3.1 Table Columns

| Column | Width | Alignment | Content Type | Styling | Feature Type |
|--------|-------|-----------|--------------|---------|--------------|
| Checkbox | 2.2em | Left | Checkbox | Standard | **[Default]** |
| ID | 50px | Center | Integer | Plain text | **[Default]** |
| Logo | 60px | Center | Image | 48x48 rounded | **[Custom]** |
| Title | Auto | Left | Link + Actions | Primary column | **[Default]** |
| Category | Auto | Left | Text | **Plain text** | **[Custom]** |
| Tags | Auto | Left | Text | **Plain text** | **[Custom]** |
| Ribbon | 120px | Left | Badge | **Red badge** | **[Custom]** |
| Featured | 60px | Center | Star | Yellow star (★) | **[Custom]** |
| Price | 100px | Left | Text | Currency format | **[Custom]** |
| Status | 120px | Left | Badge | **Colored badge** | **[Custom]** |

### 3.2 Column Styling Rules

#### ✅ Categories Column - PLAIN TEXT (NO BADGE) - **[Custom Feature]**
```css
.aps-category-text {
    color: #1d2327;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}
```
- **Multiple categories**: Display as comma-separated text
- **Example**: "Electronics, Audio, Deals"

#### ✅ Tags Column - PLAIN TEXT (NO BADGE) - **[Custom Feature]**
```css
.aps-tag-text {
    color: #646970;
    font-size: 13px;
    font-weight: 400;
    line-height: 1.5;
}
```
- **Multiple tags**: Display as comma-separated text
- **Example**: "New, Sale, Popular"

#### ✅ Ribbon Column - RED BADGE - **[Custom Feature]**
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
```
- **Multiple ribbons**: Stacked with 4px margin-left
- **Examples**: "Best Seller", "New Arrival", "Limited"

#### ✅ Status Column - COLORED BADGES - **[Custom Feature]**
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
    background: #e5f7ed;
    color: #22c55e;
}

/* Draft */
.aps-product-status-draft {
    background: #fef3c7;
    color: #d97706;
}

/* Trash */
.aps-product-status-trash {
    background: #fee2e2;
    color: #dc2626;
}

/* Pending */
.aps-product-status-pending {
    background: #f3f4f6;
    color: #6b7280;
}
```

#### ✅ Featured Column - STAR ICON - **[Custom Feature]**
```css
.column-featured {
    width: 60px;
    text-align: center;
    font-size: 18px;
    color: #e6b800;
}
```
- **Featured**: Display star emoji (★)
- **Not Featured**: Empty cell

#### ✅ Logo Column - IMAGE - **[Custom Feature]**
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

#### ✅ Title Column - LINK + ACTIONS - **[Default Feature]**
```css
.row-title {
    font-weight: 600;
    color: #2271b1;
    text-decoration: none;
    font-size: 14px;
}

.row-actions {
    font-size: 12px;
    color: #a7aaad;
    visibility: hidden;
    margin-top: 4px;
}

/* Show actions on hover */
.wp-list-table tr:hover .row-actions {
    visibility: visible;
}
```
**Row Actions:** - **[Default Feature]**
- Edit (blue link)
- Quick Edit (blue link)
- Trash (red link)
- View (blue link)

### 3.3 Row Styling
```css
/* Alternating rows */
.wp-list-table tr:nth-child(odd) {
    background-color: #f6f7f7;
}

.wp-list-table tr:nth-child(even) {
    background-color: #fff;
}

/* Hover effect */
.wp-list-table tr:hover td {
    background-color: #f0f0f1;
}
```

---

## 4. Color Palette

### 4.1 Core Colors
```css
:root {
    /* Text Colors */
    --color-text-main: #1d2327;
    --color-text-light: #646970;
    
    /* UI Colors */
    --color-border: #c3c4c7;
    --color-bg-light: #f0f0f1;
    
    /* Primary (Buttons, Links) */
    --color-primary: #2271b1;
    --color-primary-hover: #135e96;
    
    /* Status Badges */
    --color-green-bg: #e5f7ed;
    --color-green-text: #22c55e;
    --color-yellow-bg: #fef3c7;
    --color-yellow-text: #d97706;
    --color-red-bg: #fee2e2;
    --color-red-text: #dc2626;
    --color-gray-bg: #f3f4f6;
    --color-gray-text: #6b7280;
    
    /* Ribbon Badge */
    --color-ribbon: #d63638;
    
    /* Featured Star */
    --color-star: #e6b800;
}
```

---

## 5. Interactive Features

### 5.1 Bulk Actions - **[Default Feature]**
```javascript
// Supported Actions
- Move to Trash: Update status to 'trash' for selected items
- Edit: Open bulk edit modal (future enhancement)
```

**Workflow:**
1. User selects products via checkboxes
2. Selects bulk action from dropdown
3. Clicks "Apply"
4. Confirmation dialog
5. Action executed
6. Success toast notification

### 5.2 Filtering System

#### Tab Filtering - **[Default Feature]**
- Clicking status tabs filters by product status
- Updates table content dynamically
- Updates item counts

#### Dropdown Filtering - **[Custom Feature]**
- **Category Filter**: Filters by product categories
- **Status Filter**: Filters by product status
- **Search**: Real-time text search (title, ID)
- Filters work in combination (AND logic)

### 5.3 Individual Actions - **[Default Feature]**
```javascript
// Row Actions
- Edit: Redirect to edit page
- Quick Edit: Open inline edit modal (future)
- Trash: Move to trash with confirmation
- View: Open product preview in new tab
```

### 5.4 Toast Notifications - **[Default Feature]**
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
}

.notice-success {
    border-left-color: #00a32a;
}

.notice-error {
    border-left-color: #d63638;
}
```
**Auto-dismiss after 3 seconds**

---

## 6. Responsive Design - **[Default Feature]**

### 6.1 Mobile Breakpoint (< 782px)
```css
@media screen and (max-width: 782px) {
    /* Hide table headers */
    .wp-list-table thead { display: none; }
    
    /* Convert rows to card layout */
    .wp-list-table tbody tr {
        display: block;
        border-bottom: 1px solid #c3c4c7;
        margin-bottom: 10px;
    }
    
    /* Show data attributes as labels */
    .wp-list-table tbody td::before {
        content: attr(data-colname);
        font-weight: 600;
        float: left;
        margin-left: -10px;
        text-align: left;
    }
    
    /* Hide checkbox column */
    .column-cb { display: none; }
    
    /* Stack toolbar elements */
    .tablenav {
        height: auto;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
```

---

## 7. JavaScript Functionality

### 7.1 State Management
```javascript
// State object
const state = {
    products: [],           // All products
    filteredProducts: [],   // Filtered products
    currentFilter: 'all',   // Active tab filter
    selectedProducts: [],   // Selected product IDs
    
    // Filters
    searchTerm: '',
    categoryFilter: 'all',
    statusFilter: 'all'
};
```

### 7.2 Core Functions
```javascript
// 1. Render table
function renderTable() {
    // Filter products
    // Generate HTML rows
    // Update UI
}

// 2. Apply filters
function applyFilters() {
    // Combine all filters (tab, search, dropdowns)
    // Update filteredProducts
    // Re-render table
}

// 3. Toggle all checkboxes
function toggleAllCheckboxes(source) {
    // Select/deselect all row checkboxes
}

// 4. Handle bulk action
function handleBulkAction(action) {
    // Get selected products
    // Execute action
    // Show notification
    // Re-render table
}

// 5. Delete product
function deleteProduct(id) {
    // Confirm dialog
    // Update status to 'trash'
    // Show notification
    // Re-render table
}

// 6. Show toast notification
function showToast(message, type = 'success') {
    // Create toast element
    // Append to container
    // Auto-dismiss after 3 seconds
}
```

### 7.3 Event Listeners
```javascript
// Tab navigation
navTabs.forEach(tab => {
    tab.addEventListener('click', (e) => {
        e.preventDefault();
        currentFilter = tab.getAttribute('data-filter');
        renderTable();
    });
});

// Filter dropdowns
categoryFilter.addEventListener('change', applyFilters);
statusFilter.addEventListener('change', applyFilters);

// Search input
searchInput.addEventListener('keyup', applyFilters);

// Bulk action buttons
document.getElementById('doaction').addEventListener('click', handleBulkAction);
document.getElementById('doaction2').addEventListener('click', handleBulkAction);

// Select all checkboxes
document.getElementById('cb-select-all-1').addEventListener('change', toggleAllCheckboxes);
document.getElementById('cb-select-all-2').addEventListener('change', toggleAllCheckboxes);
```

---

## 8. File Structure

### 8.1 PHP Files
```
wp-content/plugins/affiliate-product-showcase/src/Admin/
├── ProductsPage.php          # Main products page class
├── ProductsTable.php         # Table class (extends WP_List_Table)
└── partials/
    └── products-page.php     # Page template
```

### 8.2 CSS Files
```
wp-content/plugins/affiliate-product-showcase/assets/css/
└── admin-products.css        # Products page styles
```

### 8.3 JavaScript Files
```
wp-content/plugins/affiliate-product-showcase/assets/js/
└── admin-products.js        # Products page functionality
```

---

## 9. Implementation Phases

### Phase 1: Core Structure (Priority: CRITICAL)
- [ ] Create ProductsPage.php class
- [ ] Register admin menu page
- [ ] Create products-page.php template
- [ ] Set up basic page layout (header, tabs, toolbar)

### Phase 2: Table Implementation (Priority: CRITICAL)
- [ ] Create ProductsTable.php extending WP_List_Table
- [ ] Define table columns
- [ ] Implement column rendering
- [ ] Add row actions (Edit, Quick Edit, Trash, View)

### Phase 3: Styling (Priority: HIGH)
- [ ] Create admin-products.css
- [ ] Implement WordPress admin styles
- [ ] Style badges (ribbon, status)
- [ ] Style plain text columns (category, tags)
- [ ] Add hover effects
- [ ] Implement responsive design

### Phase 4: JavaScript Functionality (Priority: HIGH)
- [ ] Create admin-products.js
- [ ] Implement tab filtering
- [ ] Implement dropdown filtering
- [ ] Implement search functionality
- [ ] Implement bulk actions
- [ ] Add toast notifications

### Phase 5: Data Integration (Priority: HIGH)
- [ ] Connect to ProductService
- [ ] Fetch products from REST API
- [ ] Populate table with real data
- [ ] Handle empty states

### Phase 6: Advanced Features (Priority: MEDIUM)
- [ ] Quick Edit modal
- [ ] Pagination
- [ ] Sorting
- [ ] Inline editing

---

## 10. Key Design Decisions

### 10.1 Badge Usage Policy
| Field | Badge Type | Rationale |
|-------|------------|-----------|
| Category | ❌ None | Plain text for readability, multiple categories |
| Tags | ❌ None | Plain text for readability, multiple tags |
| Ribbon | ✅ Red Badge | Visual emphasis for marketing labels |
| Status | ✅ Colored Badges | Quick status identification (green/yellow/red/gray) |
| Featured | ⭐ Star Icon | Visual indicator for featured items |

### 10.2 Text vs Badges
**Plain Text (Categories, Tags):**
- Multiple items display cleanly as comma-separated
- Better readability
- Follows WordPress admin conventions
- Less visual clutter

**Badges (Ribbon, Status):**
- Single or few items
- Need visual emphasis
- Convey important information quickly
- Color-coded for instant recognition

### 10.3 Typography Scale
```css
Page Title (h1): 23px
Column Headers: 13px
Table Content: 13px
Row Actions: 12px
Badge Text: 11px
```

---

## 11. Accessibility

### 11.1 ARIA Labels
```html
<!-- Checkbox column -->
<label class="screen-reader-text">Select All</label>
<input id="cb-select-all-1" type="checkbox">

<!-- Search input -->
<label for="post-search-input" class="screen-reader-text">Search Products:</label>

<!-- Data attributes for mobile -->
<td data-colname="Category">...</td>
```

### 11.2 Keyboard Navigation
- Tab order: Header → Tabs → Bulk Actions → Filters → Search → Table → Pagination
- Enter/Space to activate buttons
- Focus indicators on all interactive elements

### 11.3 Color Contrast
- All text meets WCAG 2.1 AA (4.5:1 minimum)
- Badge backgrounds provide sufficient contrast
- Status badges use appropriate color combinations

---

## 12. Testing Checklist

### 12.1 Visual Testing
- [ ] Verify badge colors match design
- [ ] Check plain text formatting for categories/tags
- [ ] Test ribbon badge display (red, rounded)
- [ ] Test status badge colors (green/yellow/red/gray)
- [ ] Verify featured star icon
- [ ] Check hover effects on rows
- [ ] Test responsive layout on mobile

### 12.2 Functional Testing
- [ ] Tab navigation filters correctly
- [ ] Category filter works
- [ ] Status filter works
- [ ] Search filters in real-time
- [ ] Bulk actions execute correctly
- [ ] Individual actions work
- [ ] Toast notifications appear and dismiss

### 12.3 Cross-Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

---

## 13. Reference Code

### 13.1 Mock Data Structure
```javascript
const mockProduct = {
    id: 101,
    title: "Wireless Noise Cancelling Headphones",
    logo: "https://example.com/product-image.jpg",
    categories: ["Electronics", "Audio"],
    tags: ["New", "Sale", "Popular"],
    ribbon: "Best Seller",
    featured: true,
    price: "$249.00",
    status: "published"
};
```

### 13.2 Table Row Template
```html
<tr>
    <th class="check-column" scope="row">
        <input type="checkbox" name="post[]" value="{id}" class="row-checkbox">
    </th>
    <td class="column-id">{id}</td>
    <td class="column-logo">
        <img src="{logo}" alt="" class="aps-product-logo">
    </td>
    <td class="column-title column-primary">
        <strong>
            <a href="#" class="row-title">{title}</a>
        </strong>
        <div class="row-actions">
            <span class="edit"><a href="#">Edit</a> | </span>
            <span class="inline"><a href="#">Quick Edit</a> | </span>
            <span class="trash"><a href="#">Trash</a> | </span>
            <span class="view"><a href="#">View</a></span>
        </div>
    </td>
    <td class="column-category">
        <span class="aps-category-text">{categories}</span>
    </td>
    <td class="column-tags">
        <span class="aps-tag-text">{tags}</span>
    </td>
    <td class="column-ribbon">
        <span class="aps-ribbon-badge">{ribbon}</span>
    </td>
    <td class="column-featured">{★ or empty}</td>
    <td class="column-price">{price}</td>
    <td class="column-status">
        <span class="aps-product-status aps-product-status-{status}">
            {Status}
        </span>
    </td>
</tr>
```

---

## 14. WordPress Integration Notes

### 14.1 WP_List_Table Class
```php
class ProductsTable extends WP_List_Table {
    
    // Define columns
    public function get_columns() {
        return [
            'cb'      => '<input type="checkbox" />',
            'id'      => 'ID',
            'logo'    => 'Logo',
            'title'   => 'Title',
            'category'=> 'Category',
            'tags'    => 'Tags',
            'ribbon'  => 'Ribbon',
            'featured'=> 'Featured',
            'price'   => 'Price',
            'status'  => 'Status'
        ];
    }
    
    // Column content
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id': return $item['id'];
            case 'logo': return $this->render_logo($item['logo']);
            case 'category': return $this->render_category($item['categories']);
            case 'tags': return $this->render_tags($item['tags']);
            case 'ribbon': return $this->render_ribbon($item['ribbon']);
            case 'featured': return $item['featured'] ? '★' : '';
            case 'price': return $item['price'];
            case 'status': return $this->render_status($item['status']);
            default: return '';
        }
    }
}
```

### 14.2 Enqueue Assets
```php
function enqueue_products_assets($hook) {
    if ('affiliate-products_page_products' !== $hook) {
        return;
    }
    
    // CSS
    wp_enqueue_style(
        'aps-admin-products',
        APS_PLUGIN_URL . 'assets/css/admin-products.css',
        [],
        APS_VERSION
    );
    
    // JavaScript
    wp_enqueue_script(
        'aps-admin-products',
        APS_PLUGIN_URL . 'assets/js/admin-products.js',
        [],
        APS_VERSION,
        true
    );
    
    // Localize script with data
    wp_localize_script('aps-admin-products', 'apsProductsData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('aps_products_nonce'),
        'strings' => [
            'deleteConfirm' => __('Are you sure you want to move this product to trash?', 'affiliate-product-showcase'),
            'bulkDeleteSuccess' => __('%d products moved to trash.', 'affiliate-product-showcase'),
        ]
    ]);
}
add_action('admin_enqueue_scripts', 'enqueue_products_assets');
```

---

## 15. Success Criteria

### 15.1 Visual Quality
- ✅ Matches WooCommerce design aesthetic
- ✅ Clean, professional appearance
- ✅ Consistent with WordPress admin
- ✅ Proper color usage (red ribbon badges, colored status badges)
- ✅ Plain text for categories/tags (no badges)

### 15.2 Functionality
- ✅ All tabs filter correctly
- ✅ All dropdown filters work
- ✅ Search works in real-time
- ✅ Bulk actions execute
- ✅ Individual actions work
- ✅ Toast notifications appear

### 15.3 Code Quality
- ✅ Follows WordPress coding standards
- ✅ Proper file organization
- ✅ Efficient JavaScript
- ✅ Optimized CSS
- ✅ No console errors
- ✅ Accessible markup

---

## 16. Future Enhancements

- [ ] Inline editing for quick updates
- [ ] Drag-and-drop for featured products
- [ ] Advanced filters (price range, date range)
- [ ] Export to CSV/Excel
- [ ] Import products
- [ ] Quick view modal
- [ ] Bulk edit modal
- [ ] Column customization (show/hide columns)
- [ ] Saved filters
- [ ] Keyboard shortcuts

---

**Version:** 1.1.0  
**Created:** 2026-01-27  
**Last Updated:** 2026-01-27 - Added feature classification and placement rationale  
**Status:** Ready for Implementation  
**Priority:** HIGH