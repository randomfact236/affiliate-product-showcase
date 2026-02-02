# Standard Taxonomy UI Design (Based on Actual Category Implementation)

**Purpose:** Define standard UI layout pattern for Categories and Tags management pages based on working category UI
**Created:** 2026-01-25  
**Version:** 2.1.0
**Reference:** CategoryFields.php (actual working implementation)

---

## 🚨 CRITICAL RULE

### **DON'T DELETE THIS FILE**

This file contains authoritative design pattern for taxonomy UI based on actual working implementation. Do not delete or modify without explicit approval.

---

## 📋 Category UI Analysis (Reference Implementation)

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php`

### Key Patterns Identified:

1. **Status View Tabs** - Uses WordPress native `views_edit-{taxonomy}` filter
2. **Sort Dropdown** - Injected via JavaScript in `admin_footer-edit-tags.php`, placed before bulk actions
3. **Inline Status Edit** - AJAX-powered dropdown in status column
4. **Bulk Actions** - Context-aware (different actions based on current view)
5. **Admin Notices** - Displayed via `admin_notices` hook with URL parameters
6. **Form Field Layout** - Side-by-side checkboxes (Featured + Default) below slug

---

## 📐 Standard Layout Pattern

### **WordPress Native Layout:**

```
┌─────────────────────────────────────────────────────────────────┐
│  WordPress Native Taxonomy Page                             │
├─────────────────────────┬───────────────────────────────────────┤
│  LEFT COLUMN          │  RIGHT COLUMN                        │
│  (Form - ~30%)      │  (Table - ~70%)                     │
│                       │                                     │
│  ┌─────────────────┐  │  ┌───────────────────────────────────┐  │
│  │ Add/Edit       │  │  │ Top Section (WordPress Native)    │  │
│  │ Taxonomy Form  │  │  │                               │  │
│  └─────────────────┘  │  │ [All (2) | Published (2) |      │  │
│                       │  │  Draft (0) | Trash (0)]          │  │
│  Native Fields:       │  │                               │  │
│  • Name             │  │ [Search Box]              [Search] │  │
│  • Slug             │  │                               │  │
│  • Parent (cat only) │  │ [Date (Newest First) ▼] [Bulk] │  │
│  • Description       │  │                            [Apply] │  │
│                       │  └───────────────────────────────────┘  │
│  Custom Fields:       │                                     │
│  • Featured         │  ┌───────────────────────────────────┐  │
│  • Default (cat)    │  │ WordPress Native Table         │  │
│  • Image URL        │  │                               │  │
│  • Status           │  │ Columns:                      │  │
│                       │  │ • Name                       │  │
│  Section Divider:     │  │ • Description                │  │
│  "=== [Settings] ===" │  │ • Slug                       │  │
│                       │  │ • Status (inline dropdown)     │  │
│  [Add Button]        │  │ • Count                      │  │
│                       │  └───────────────────────────────────┘  │
└───────────────────────┴───────────────────────────────────────┘
```

---

## 📝 Left Column: Add/Edit Form

### **Field Order (Top to Bottom):**

#### 1. **Name Input**
- Type: Text
- Required: Yes
- WordPress native field (keep as-is)
- Auto-slug generation

#### 2. **Slug Input**
- Type: Text
- Required: No
- WordPress native field (keep as-is)
- Auto-generated from name

#### 3. **Featured + Default Checkboxes** *(Side by Side)*
- Position: **Below Slug field** (via JavaScript)
- Layout: Side-by-side (flex or inline-block)
- Structure:
  ```
  [Featured Checkbox] [Default Checkbox]
  ```
  
**Featured Checkbox:**
- Type: Checkbox
- Label: "Featured [Category/Tag]"
- Value: "1" (checked) / "0" (unchecked)
- Default: Unchecked
- Meta key: `_aps_{taxonomy}_featured`
- Description: "Display this [category/tag] prominently on frontend."

**Default Checkbox** *(Categories only)*
- Type: Checkbox
- Label: "Default [Category]"
- Value: "1" (checked) / "0" (unchecked)
- Default: Unchecked
- Meta key: `_aps_category_is_default`
- Description: "Products without a category will be assigned to this category automatically."
- **NOT applicable for Tags** (flat taxonomy)
- Behavior: Only one default category allowed (exclusive)

#### 4. **Parent Dropdown** *(Categories only)*
- Type: Select dropdown
- Required: No
- WordPress native field (keep as-is)
- **NOT applicable for Tags** (flat taxonomy)
- Options: Hierarchical category list

#### 5. **Description Textarea**
- Type: Textarea
- Required: No
- WordPress native field (keep as-is)
- No character limit

#### 6. **Section Divider**
- Text: "=== [Category/Tag] Settings ==="
- Style: Horizontal line with centered text
- Position: Below checkboxes, above image URL
- Implementation: Custom HTML divider

#### 7. **Image URL Input**
- Type: Text/URL
- Label: "[Category/Tag] Image URL"
- Placeholder: "https://example.com/image.jpg"
- Meta key: `_aps_{taxonomy}_image`
- Description: "Enter URL for [category/tag] image."
- Validation: URL format via `esc_url_raw()`

#### 8. **Add/Update Button**
- WordPress native button (keep as-is)
- Text: "Add New [Category/Tag]" or "Update [Category/Tag]"
- Position: Bottom of form

---

## 📊 Right Column: Management Table

### **Top Section Layout:**

```
┌────────────────────────────────────────────────────────────────────┐
│ [All (2)] [Published (2)] [Draft (0)] [Trash (0)]         │
│                                                              │
│ [Search _____________] [Search]                               │
│                                                              │
│ [Date (Newest First) ▼] [Bulk actions ▼] [Apply]           │
└────────────────────────────────────────────────────────────────────┘
```

#### **Component 1: Status View Tabs**

**Implementation:** WordPress `views_edit-{taxonomy}` filter

**Position:** Top of right column (WordPress native position)

**Format:**
```php
// Filter callback
public function add_status_view_tabs( array $views ): array {
    // Count by status
    $all_count = $this->count_by_status('all');
    $published_count = $this->count_by_status('published');
    $draft_count = $this->count_by_status('draft');
    $trash_count = $this->count_by_status('trash');
    
    // Build views
    $new_views['all'] = '<a href="...">All <span class="count">(' . $all_count . ')</span></a>';
    $new_views['published'] = '<a href="...">Published <span class="count">(' . $published_count . ')</span></a>';
    $new_views['draft'] = '<a href="...">Draft <span class="count">(' . $draft_count . ')</span></a>';
    $new_views['trash'] = '<a href="...">Trash <span class="count">(' . $trash_count . ')</span></a>';
    
    return $new_views;
}
```

**URL Parameters:**
- All: `edit-tags.php?taxonomy=aps_{taxonomy}&post_type=aps_product`
- Published: `...&status=published`
- Draft: `...&status=draft`
- Trash: `...&status=trash`

**Active State:** CSS class `current` on active tab

**Styling:**
- Display: Inline links with counts
- Active tab: Background color, white text
- Inactive tab: Blue text link

---

#### **Component 2: Search Box**

**Position:** Below status tabs, right-aligned (WordPress native)

**Type:** WordPress native search (keep as-is)

**Functionality:**
- Text input for search query
- Search button next to input
- URL Parameter: `?s={query}`

---

#### **Component 3: Date Sort Dropdown**

**Implementation:** JavaScript injection in `admin_footer-edit-tags.php`

**Position:** Below search box, **before bulk actions** (left-aligned)

**Code Pattern:**
```php
public function add_sort_order_html(): void {
    $screen = get_current_screen();
    if ( !$screen || $screen->taxonomy !== 'aps_category' ) {
        return;
    }
    
    $current_sort_order = isset( $_GET['aps_sort_order'] ) 
        ? sanitize_text_field( $_GET['aps_sort_order'] ) 
        : 'date';
    
    ?>
    <script>
    jQuery(document).ready(function($) {
        var $bulkActions = $('.bulkactions');
        var $searchForm = $('form#posts-filter');
        
        if ($bulkActions.length && $searchForm.length) {
            // Insert sort dropdown BEFORE bulk actions
            $bulkActions.before(`
                <div class="alignleft actions aps-sort-filter">
                    <label for="aps_sort_order" class="screen-reader-text">
                        <?php esc_html_e( 'Sort By', 'affiliate-product-showcase' ); ?>
                    </label>
                    <select name="aps_sort_order" id="aps_sort_order" class="postform">
                        <option value="date" <?php selected( $current_sort_order, 'date' ); ?>>
                            <?php esc_html_e( 'Date (Newest First)', 'affiliate-product-showcase' ); ?>
                        </option>
                    </select>
                </div>
            `);
            
            // Ensure alignment
            $('.aps-sort-filter').css('float', 'left');
            $bulkActions.css('float', 'left');
        }
    });
    </script>
    <?php
}
```

**Options:**
- "Date (Newest First)" - Default
- "Date (Oldest First)" - Optional future enhancement

**URL Parameters:**
- Newest: `?aps_sort_order=date`
- Oldest: `?aps_sort_order=date&order=asc` (future)

**Styling:**
- Class: `alignleft actions aps-sort-filter`
- Float: Left
- Margin: Right 10px
- Width: Inline-block

---

#### **Component 4: Bulk Actions Dropdown**

**Implementation:** WordPress `bulk_actions-edit-{taxonomy}` filter

**Position:** After sort dropdown, before Apply button

**Context-Aware Actions:**

**When NOT in Trash view:**
```php
$bulk_actions['move_to_draft'] = __( 'Move to Draft', 'affiliate-product-showcase' );
$bulk_actions['move_to_trash'] = __( 'Move to Trash', 'affiliate-product-showcase' );
```

**When in Trash view:**
```php
$bulk_actions['restore'] = __( 'Restore', 'affiliate-product-showcase' );
$bulk_actions['delete_permanently'] = __( 'Delete Permanently', 'affiliate-product-showcase' );
```

**Handling:**
```php
public function handle_custom_bulk_actions( string $redirect_url, string $action_name, array $term_ids ): string {
    $count = 0;
    
    switch ( $action_name ) {
        case 'move_to_draft':
            foreach ( $term_ids as $term_id ) {
                $result = update_term_meta( $term_id, '_aps_category_status', 'draft' );
                if ( $result !== false ) $count++;
            }
            // Add notice parameter
            $redirect_url = add_query_arg( ['moved_to_draft' => $count], $redirect_url );
            break;
            
        case 'move_to_trash':
            // Similar pattern for trash
            break;
            
        case 'restore':
            // Similar pattern for restore
            break;
            
        case 'delete_permanently':
            // Similar pattern for permanent delete
            break;
    }
    
    return $redirect_url;
}
```

---

#### **Component 5: Apply Button**

**Type:** WordPress native button (keep as-is)

**Function:** Execute selected bulk action

**Position:** Right of bulk actions dropdown

---

### **Table Section**

#### **Column Order (Left to Right):**

1. **Name Column**
   - Type: Text (link to edit)
   - Sortable: Yes
   - Width: Auto
   - Content: Category/Tag name with edit link

2. **Description Column**
   - Type: Text
   - Sortable: No
   - Width: Auto
   - Content: Description text (truncated if long)

3. **Slug Column**
   - Type: Text
   - Sortable: Yes
   - Width: Auto
   - Content: Slug value (monospace font)

4. **Status Column** *(Inline Editable)*
   - Type: Dropdown (inline)
   - Sortable: No
   - Width: Fixed (120px minimum)
   
   **Implementation:**
   ```php
   public function render_custom_columns( string $content, string $column_name, int $term_id ): string {
       if ( $column_name === 'status' ) {
           $status = get_term_meta( $term_id, '_aps_category_status', true ) ?: 'published';
           $is_default = get_term_meta( $term_id, '_aps_category_is_default', true ) === '1';
           
           if ( $is_default ) {
               // Default category - read-only
               $icon = $status === 'published' ? '✓' : '-';
               return sprintf(
                   '<span class="aps-status-readonly">%s %s <span class="note">(Default)</span></span>',
                   $icon,
                   $status === 'published' ? 'Published' : 'Draft'
               );
           } else {
               // Non-default - editable dropdown
               return sprintf(
                   '<select class="aps-status-select" data-term-id="%d" aria-label="%s">
                       <option value="published" %s>Published</option>
                       <option value="draft" %s>Draft</option>
                   </select>',
                   $term_id,
                   esc_attr__('Change status', 'affiliate-product-showcase'),
                   selected($status, 'published', false),
                   selected($status, 'draft', false)
               );
           }
       }
       return $content;
   }
   ```
   
   **Status Values:**
   - `published` - Green indicator
   - `draft` - Gray indicator
   - `trashed` - Red indicator (shown in Trash view only)
   
   **Special Behavior:**
   - Default category: Read-only status (cannot be changed)
   - Non-default: Editable dropdown
   - AJAX save on change (no page reload)

5. **Count Column**
   - Type: Number
   - Sortable: Yes
   - Width: Auto
   - Alignment: Right
   - Content: Product count
   - Label: "Products" or "Items"

---

## 🔄 AJAX Implementation

### **Inline Status Update**

**Handler:** `wp_ajax_{plugin}_toggle_{taxonomy}_status`

**Flow:**
1. User changes status dropdown
2. JavaScript sends AJAX request
3. Server validates nonce and permissions
4. Server updates term meta
5. Server sends JSON response
6. JavaScript shows success/error notice
7. Dropdown returns to original state if error

**JavaScript:**
```javascript
$(document).on('change', '.aps-status-select', function() {
    var $this = $(this);
    var termId = $this.data('term-id');
    var newStatus = $this.val();
    var originalStatus = $this.find('option:selected').text();
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'aps_toggle_category_status',
            nonce: aps_admin_vars.nonce,
            term_id: termId,
            status: newStatus
        },
        beforeSend: function() {
            $this.prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                $this.prop('disabled', false);
                // Show success notice
                $('.wrap h1').after('<div class="notice notice-success is-dismissible"><p>' + aps_admin_vars.success_text + '</p></div>');
                setTimeout(function() { $('.notice-success').fadeOut(); }, 3000);
            } else {
                // Revert to original
                $this.val(originalStatus === 'Published' ? 'published' : 'draft');
                alert(response.data.message);
            }
        },
        error: function() {
            // Revert to original
            $this.val(originalStatus === 'Published' ? 'published' : 'draft');
            alert(aps_admin_vars.error_text);
        }
    });
});
```

**Localization:**
```php
wp_localize_script( 'jquery', 'aps_admin_vars', [
    'nonce' => wp_create_nonce('aps_toggle_category_status'),
    'success_text' => __('Status updated successfully.', 'affiliate-product-showcase'),
    'error_text' => __('An error occurred. Please try again.', 'affiliate-product-showcase'),
] );
```

---

## 📢 Admin Notices

### **Bulk Action Notices**

**Implementation:** `admin_notices` hook with URL parameter check

**Code Pattern:**
```php
public function display_bulk_action_notices(): void {
    if ( isset( $_GET['moved_to_draft'] ) ) {
        $count = intval( $_GET['moved_to_draft'] );
        echo '<div class="notice notice-success is-dismissible"><p>';
        printf( __( '%d categories moved to draft.', 'affiliate-product-showcase' ), $count );
        echo '</p></div>';
    }
    
    if ( isset( $_GET['moved_to_trash'] ) ) {
        $count = intval( $_GET['moved_to_trash'] );
        echo '<div class="notice notice-success is-dismissible"><p>';
        printf( __( '%d categories moved to trash.', 'affiliate-product-showcase' ), $count );
        echo '</p></div>';
    }
    
    // Similar for restored_from_trash, permanently_deleted
}
```

**Notice Types:**
- Success (green): Actions completed successfully
- Error (red): Action failed (optional)
- Info (blue): Informational (optional)

---

## 🎨 Styling Guidelines

### **Layout CSS:**
```css
/* Two-column layout */
body.taxonomy-aps_category #col-container {
    display: flex;
    gap: 30px;
}

body.taxonomy-aps_category #col-left {
    flex: 0 0 30%;
}

body.taxonomy-aps_category #col-right {
    flex: 0 0 70%;
}

/* Side-by-side checkboxes */
.aps-category-checkboxes-wrapper {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.aps-category-checkboxes-wrapper .form-field {
    flex: 1;
}
```

### **Status Tabs CSS:**
```css
/* WordPress native styling */
.subsubsub {
    margin-bottom: 15px;
}

.subsubsub li a {
    display: inline-block;
    padding: 6px 12px;
    color: #2271b1;
    text-decoration: none;
}

.subsubsub li.current a {
    background-color: #2271b1;
    color: #fff;
}
```

### **Sort Filter CSS:**
```css
.aps-sort-filter {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 10px;
    float: left;
}

.aps-sort-filter .postform {
    margin-right: 5px;
}

.bulkactions {
    display: inline-block;
    float: left;
}
```

### **Inline Status CSS:**
```css
.aps-status-select {
    min-width: 120px;
    padding: 5px 8px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    cursor: pointer;
}

.aps-status-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.aps-status-readonly {
    display: inline-block;
    padding: 5px 10px;
    background-color: #f0f0f1;
    border: 1px solid #c3c4c7;
    border-radius: 3px;
}

.aps-status-readonly .note {
    color: #646970;
    font-size: 12px;
    margin-left: 5px;
}
```

### **Responsive Design:**
```css
/* Tablet (768px-1200px) */
@media (max-width: 1200px) and (min-width: 769px) {
    body.taxonomy-aps_category #col-left {
        flex: 0 0 40%;
    }
    body.taxonomy-aps_category #col-right {
        flex: 0 0 60%;
    }
}

/* Mobile (<768px) */
@media (max-width: 768px) {
    body.taxonomy-aps_category #col-container {
        flex-direction: column;
    }
    body.taxonomy-aps_category #col-left {
        flex: 0 0 auto;
        width: 100%;
    }
    body.taxonomy-aps_category #col-right {
        flex: 0 0 auto;
        width: 100%;
    }
    
    /* Stack checkboxes on mobile */
    .aps-category-checkboxes-wrapper {
        flex-direction: column;
    }
}
```

---

## 📋 Differences: Categories vs Tags

### **Categories (Hierarchical):**

| Feature | Required | Implementation |
|---------|-----------|----------------|
| Parent dropdown | ✅ Yes | WordPress native field |
| Default checkbox | ✅ Yes | Custom checkbox (exclusive) |
| Hierarchy indicators | ✅ Yes | WordPress native tree view |
| Default protection | ✅ Yes | Cannot change status/delete |

### **Tags (Non-Hierarchical):**

| Feature | Required | Implementation |
|---------|-----------|----------------|
| Parent dropdown | ❌ No | Not applicable |
| Default checkbox | ❌ No | Not applicable |
| Hierarchy indicators | ❌ No | Flat table view |
| Default protection | ❌ No | No special handling |

### **Common Fields (Both):**

| Feature | Categories | Tags |
|---------|------------|-------|
| Name input | ✅ | ✅ |
| Slug input | ✅ | ✅ |
| Featured checkbox | ✅ | ✅ |
| Description textarea | ✅ | ✅ |
| Status dropdown (inline) | ✅ | ✅ |
| Image URL input | ✅ | ✅ |
| Status filter links | ✅ | ✅ |
| Search box | ✅ | ✅ |
| Date sort dropdown | ✅ | ✅ |
| Bulk actions | ✅ | ✅ |
| Apply button | ✅ | ✅ |

---

## 🔧 Implementation Hooks Reference

### **Form Hooks:**
```php
// Add fields to add form
add_action( 'aps_{taxonomy}_add_form_fields', [$this, 'add_{taxonomy}_fields'] );

// Add fields to edit form
add_action( 'aps_{taxonomy}_edit_form_fields', [$this, 'edit_{taxonomy}_fields'] );

// Save fields
add_action( 'created_aps_{taxonomy}', [$this, 'save_{taxonomy}_fields'], 10, 2 );
add_action( 'edited_aps_{taxonomy}', [$this, 'save_{taxonomy}_fields'], 10, 2 );
```

### **Table Hooks:**
```php
// Add custom columns
add_filter( 'manage_edit-aps_{taxonomy}_columns', [$this, 'add_custom_columns'] );

// Render custom columns
add_filter( 'manage_aps_{taxonomy}_custom_column', [$this, 'render_custom_columns'], 10, 3 );

// Add bulk actions
add_filter( 'bulk_actions-edit-aps_{taxonomy}', [$this, 'add_custom_bulk_actions'] );

// Handle bulk actions
add_filter( 'handle_bulk_actions-edit-aps_{taxonomy}', [$this, 'handle_custom_bulk_actions'], 10, 3 );
```

### **View/Filter Hooks:**
```php
// Add status view tabs
add_filter( 'views_edit-aps_{taxonomy}', [$this, 'add_status_view_tabs'] );

// Filter by status
add_filter( 'get_terms', [$this, 'filter_{taxonomy}_by_status'], 10, 3 );
```

### **Admin Hooks:**
```php
// Add sort dropdown (via JavaScript)
add_action( 'admin_footer-edit-tags.php', [$this, 'add_sort_order_html'] );

// Display bulk action notices
add_action( 'admin_notices', [$this, 'display_bulk_action_notices'] );

// Enqueue assets
add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_assets'] );

// Localize script
add_action( 'admin_head-edit-tags.php', [$this, 'localize_admin_script'] );
```

### **AJAX Hooks:**
```php
// Inline status update
add_action( 'wp_ajax_{plugin}_toggle_{taxonomy}_status', [$this, 'ajax_toggle_{taxonomy}_status'] );
```

---

## 🎯 Implementation Checklist

### **Phase 1: Core Structure**
- [ ] Register taxonomy (hierarchical vs non-hierarchical)
- [ ] Set up form hooks (add/edit/save)
- [ ] Set up table hooks (columns/render/bulk)
- [ ] Set up view/filter hooks (status tabs)
- [ ] Set up admin hooks (notices/assets)

### **Phase 2: Form Implementation**
- [ ] Featured checkbox
- [ ] Default checkbox (categories only)
- [ ] Image URL input
- [ ] Section divider
- [ ] Side-by-side checkbox layout
- [ ] Nonce field for security

### **Phase 3: Table Implementation**
- [ ] Status view tabs (WordPress filter)
- [ ] Sort dropdown (JavaScript injection)
- [ ] Custom columns (Name, Description, Slug, Status, Count)
- [ ] Inline status dropdown
- [ ] Bulk actions (context-aware)
- [ ] Apply button

### **Phase 4: AJAX Implementation**
- [ ] AJAX handler registration
- [ ] Inline status update handler
- [ ] Nonce verification
- [ ] Permission checks
- [ ] Error handling
- [ ] Success/error responses

### **Phase 5: Admin Notices**
- [ ] Bulk action notice handler
- [ ] URL parameter checks
- [ ] Success messages
- [ ] Auto-dismissal (optional)

### **Phase 6: Styling**
- [ ] Two-column layout CSS
- [ ] Status tabs styling
- [ ] Sort dropdown styling
- [ ] Inline status styling
- [ ] Responsive breakpoints
- [ ] Mobile optimization

### **Phase 7: Testing**
- [ ] Form field order
- [ ] Table column order
- [ ] Inline status editing
- [ ] Status view tabs
- [ ] Date sort dropdown
- [ ] Bulk actions
- [ ] Admin notices
- [ ] Responsive design
- [ ] Accessibility (keyboard, screen readers)

---

## ✅ Success Criteria

### **UI Completeness:**
- ✅ All fields in correct order
- ✅ Two-column layout (30% left, 70% right)
- ✅ Status view tabs working
- ✅ Sort dropdown positioned correctly
- ✅ Table columns in correct order
- ✅ Inline status editing functional
- ✅ Bulk actions context-aware
- ✅ Admin notices displayed

### **Code Quality:**
- ✅ TRUE HYBRID compliance (term meta only)
- ✅ Underscore prefix for meta keys
- ✅ Proper sanitization
- ✅ Nonce verification
- ✅ AJAX security
- ✅ Permission checks
- ✅ Error handling
- ✅ WordPress hooks used correctly

### **Performance:**
- ✅ Efficient queries (no N+1)
- ✅ AJAX for inline edits
- ✅ Caching where appropriate
- ✅ Optimized CSS/JS
- ✅ Minimal DOM manipulation

### **Accessibility:**
- ✅ Keyboard navigation
- ✅ Focus indicators
- ✅ Screen reader support
- ✅ ARIA labels
- ✅ Color contrast (4.5:1 minimum)
- ✅ Reduced motion support

---

## 📝 Notes

### **WordPress Native Fields:**
- Keep Name, Slug, Parent, Description as-is
- Don't duplicate or override native functionality
- Use WordPress hooks for customization

### **Custom Field Storage:**
- Use term meta: `get_term_meta()` / `update_term_meta()`
- Meta keys: `_aps_{taxonomy}_{field_name}`
- Example: `_aps_category_featured`, `_aps_tag_image_url`

### **TRUE HYBRID Compliance:**
- All custom fields MUST use term meta
- NO auxiliary taxonomy queries in active code
- NO custom database tables
- WordPress native tables only

### **JavaScript Injection Strategy:**
- Use `admin_footer-{page}` hook for targeting
- Target specific screen ID for precision
- Use jQuery for DOM manipulation
- Enqueue scripts in `admin_enqueue_scripts`

---

## 📞 Support

**Questions?**
- Refer to CategoryFields.php as reference implementation
- This document is based on actual working code
- DO NOT delete or modify without explicit approval
- Contact development team for clarification

---

**Version History:**
- 1.0.0 (2026-01-25): Initial creation, theoretical pattern
- 2.0.0 (2026-01-25): Based on actual CategoryFields.php implementation
- 2.1.0 (2026-01-25): Fixed numbering inconsistency in form field order

**Status:** ✅ ACTIVE - Use this as reference for all taxonomy UI implementations