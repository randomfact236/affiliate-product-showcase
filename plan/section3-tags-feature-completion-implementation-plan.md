# Section 3: Tags - Feature Completion Implementation Plan

**Created:** January 25, 2026  
**Based on:** findings/section3-tags-hybrid-compliance-report.md  
**Goal:** Complete missing features and achieve 100% true hybrid compliance

---

## Executive Summary

**Current Status:** 7/10 (PARTIALLY TRUE HYBRID)  
**Target Status:** 10/10 (FULL TRUE HYBRID)  
**Completion:** 6/11 phases (55%)

**Primary Gaps:**
1. Missing status management features (featured, default, draft/publish)
2. Missing status editing in admin table
3. Missing status filter links (All | Published | Draft | Trash)
4. Missing enhanced bulk actions
5. Missing sort by order functionality
6. Incomplete DI container registration

---

## 1. Overview

This plan addresses the missing features identified in the compliance report and the user's original requirements:

**User Requirements:**
1. ✅ Create featured, default feature in tag form
2. ✅ Make status editable inside tag table
3. ✅ Add default sort by order above table
4. ✅ Add bulk actions: move to draft, move to trash, delete
5. ✅ Add status filter links: All (2) | Published (2) | Draft (0) | Trash (0)

**True Hybrid Compliance Requirements:**
1. Complete DI container registration
2. Implement all missing features per true hybrid pattern
3. Maintain WordPress native + custom approach

---

## 2. Implementation Phases

### Phase 1: Enhance Tag Model with Status Fields
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** None

**Objective:** Add status management fields to Tag model

**Tasks:**
1.1. Add status-related properties to Tag model
   - `featured` (bool) - whether tag is featured
   - `default` (bool) - whether tag is default selection
   - `status` (string) - draft, publish, trash
   - `order` (int) - display order

1.2. Update Tag::from_wp_term() to include new fields
   - Read `_aps_tag_featured` meta
   - Read `_aps_tag_default` meta
   - Read `_aps_tag_status` meta
   - Read `_aps_tag_order` meta

1.3. Update Tag::from_array() to include new fields
   - Accept featured, default, status, order from array
   - Set default values: featured=false, default=false, status='publish', order=0

1.4. Update Tag::to_array() to include new fields
   - Include featured, default, status, order in output

**Files to Modify:**
- `src/Models/Tag.php`

**Code Example:**
```php
// Tag.php - Add new properties
public readonly bool $featured;
public readonly bool $default;
public readonly string $status;
public readonly int $order;

// Update constructor
public function __construct(
    int $id,
    string $name,
    string $slug,
    string $description = '',
    int $count = 0,
    ?string $color = null,
    ?string $icon = null,
    bool $featured = false,
    bool $default = false,
    string $status = 'publish',
    int $order = 0
) {
    // ... existing code
    $this->featured = $featured;
    $this->default = $default;
    $this->status = $status;
    $this->order = $order;
}
```

**Acceptance Criteria:**
- [ ] Tag model has all status-related properties
- [ ] from_wp_term() reads new meta fields
- [ ] from_array() accepts new fields with defaults
- [ ] to_array() includes new fields
- [ ] Type hints correct for all new properties
- [ ] PHPDoc updated for new properties

---

### Phase 2: Enhance TagRepository with Status Handling
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 1 complete

**Objective:** Update repository to handle status fields and order

**Tasks:**
2.1. Update save_metadata() to save new fields
   - Save `_aps_tag_featured`
   - Save `_aps_tag_default`
   - Save `_aps_tag_status`
   - Save `_aps_tag_order`

2.2. Update delete_metadata() to delete new fields
   - Delete `_aps_tag_featured`
   - Delete `_aps_tag_default`
   - Delete `_aps_tag_status`
   - Delete `_aps_tag_order`

2.3. Add status filtering to all() method
   - Support `status` parameter (draft, publish, trash)
   - Filter by `_aps_tag_status` meta

2.4. Add order sorting to all() method
   - Support `orderby` parameter (name, order, count)
   - Default to `order` if not specified

2.5. Add bulk status change methods
   - `change_status(int[] $ids, string $status): bool`
   - `change_featured(int[] $ids, bool $featured): bool`
   - `change_default(int[] $ids, bool $default): bool`
   - `move_to_trash(int[] $ids): bool`
   - `delete_permanently(int[] $ids): bool`

**Files to Modify:**
- `src/Repositories/TagRepository.php`

**Code Example:**
```php
// TagRepository.php - Update save_metadata()
private function save_metadata(int $term_id, Tag $tag): void {
    // ... existing code for color, icon
    
    update_term_meta($term_id, '_aps_tag_featured', $tag->featured);
    update_term_meta($term_id, '_aps_tag_default', $tag->default);
    update_term_meta($term_id, '_aps_tag_status', $tag->status);
    update_term_meta($term_id, '_aps_tag_order', $tag->order);
}

// Add bulk status change method
public function change_status(array $ids, string $status): bool {
    foreach ($ids as $id) {
        update_term_meta($id, '_aps_tag_status', $status);
    }
    return true;
}

// Add status filtering to all()
public function all(array $args = []): array {
    $defaults = [
        'status' => null,  // draft, publish, trash
        'orderby' => 'order',  // name, order, count
        'order' => 'ASC',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $query_args = [
        'taxonomy' => 'aps_tag',
        'hide_empty' => false,
    ];
    
    // Add status filter
    if ($args['status']) {
        $query_args['meta_query'][] = [
            'key' => '_aps_tag_status',
            'value' => $args['status'],
        ];
    }
    
    // Add order sorting
    $query_args['orderby'] = $args['orderby'] === 'order' ? 'meta_value_num' : $args['orderby'];
    if ($args['orderby'] === 'order') {
        $query_args['meta_key'] = '_aps_tag_order';
    }
    $query_args['order'] = $args['order'];
    
    $terms = get_terms($query_args);
    return TagFactory::from_wp_terms($terms);
}
```

**Acceptance Criteria:**
- [ ] save_metadata() saves all new fields
- [ ] delete_metadata() deletes all new fields
- [ ] all() supports status filtering
- [ ] all() supports order sorting (default)
- [ ] Bulk status change methods work correctly
- [ ] All methods have proper error handling

---

### Phase 3: Enhance TagFields with Status Inputs
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 1, Phase 2 complete

**Objective:** Add status field inputs to tag add/edit forms

**Tasks:**
3.1. Add featured checkbox to add_tag_fields()
   - Label: "Featured Tag"
   - Description: "Mark as featured for highlighting"
   - Nonce verification included

3.2. Add default checkbox to add_tag_fields()
   - Label: "Default Tag"
   - Description: "Set as default selection"
   - Nonce verification included

3.3. Add status select to add_tag_fields()
   - Options: Draft, Publish
   - Default: Publish
   - Nonce verification included

3.4. Add order input to add_tag_fields()
   - Type: number
   - Default: 0
   - Description: "Display order (lower numbers first)"
   - Nonce verification included

3.5. Add same fields to edit_tag_fields()
   - Pre-fill with current values
   - Same field structure as add form

3.6. Update save_tag_fields() to save new fields
   - Sanitize featured (checkbox)
   - Sanitize default (checkbox)
   - Sanitize status (select)
   - Sanitize order (integer)

**Files to Modify:**
- `src/Admin/TagFields.php`

**Code Example:**
```php
// TagFields.php - Add to add_tag_fields()
public function add_tag_fields(): void {
    // ... existing color, icon fields
    
    ?>
    <div class="form-field aps-tag-featured">
        <label for="_aps_tag_featured">
            <?php esc_html_e('Featured Tag', 'affiliate-product-showcase'); ?>
        </label>
        <input type="checkbox" 
               id="_aps_tag_featured" 
               name="_aps_tag_featured" 
               value="1">
        <p class="description">
            <?php esc_html_e('Mark this tag as featured for highlighting.', 'affiliate-product-showcase'); ?>
        </p>
    </div>
    
    <div class="form-field aps-tag-default">
        <label for="_aps_tag_default">
            <?php esc_html_e('Default Tag', 'affiliate-product-showcase'); ?>
        </label>
        <input type="checkbox" 
               id="_aps_tag_default" 
               name="_aps_tag_default" 
               value="1">
        <p class="description">
            <?php esc_html_e('Set this as the default tag selection.', 'affiliate-product-showcase'); ?>
        </p>
    </div>
    
    <div class="form-field aps-tag-status">
        <label for="_aps_tag_status">
            <?php esc_html_e('Status', 'affiliate-product-showcase'); ?>
        </label>
        <select id="_aps_tag_status" name="_aps_tag_status">
            <option value="draft"><?php esc_html_e('Draft', 'affiliate-product-showcase'); ?></option>
            <option value="publish" selected><?php esc_html_e('Published', 'affiliate-product-showcase'); ?></option>
        </select>
    </div>
    
    <div class="form-field aps-tag-order">
        <label for="_aps_tag_order">
            <?php esc_html_e('Display Order', 'affiliate-product-showcase'); ?>
        </label>
        <input type="number" 
               id="_aps_tag_order" 
               name="_aps_tag_order" 
               value="0" 
               min="0"
               step="1">
        <p class="description">
            <?php esc_html_e('Order for displaying (lower numbers appear first).', 'affiliate-product-showcase'); ?>
        </p>
    </div>
    <?php
}

// Update save_tag_fields()
public function save_tag_fields(int $tag_id, \WP_Term $term): void {
    // ... existing nonce verification
    
    // Save featured
    $featured = isset($_POST['_aps_tag_featured']) ? true : false;
    update_term_meta($tag_id, '_aps_tag_featured', $featured);
    
    // Save default
    $default = isset($_POST['_aps_tag_default']) ? true : false;
    update_term_meta($tag_id, '_aps_tag_default', $default);
    
    // Save status
    $status = isset($_POST['_aps_tag_status']) 
        ? sanitize_text_field(wp_unslash($_POST['_aps_tag_status'])) 
        : 'publish';
    update_term_meta($tag_id, '_aps_tag_status', $status);
    
    // Save order
    $order = isset($_POST['_aps_tag_order']) 
        ? intval(wp_unslash($_POST['_aps_tag_order'])) 
        : 0;
    update_term_meta($tag_id, '_aps_tag_order', $order);
}
```

**Acceptance Criteria:**
- [ ] Featured checkbox appears in add form
- [ ] Default checkbox appears in add form
- [ ] Status select appears in add form
- [ ] Order input appears in add form
- [ ] All fields pre-filled in edit form
- [ ] save_tag_fields() saves all new fields
- [ ] All fields properly sanitized
- [ ] Nonce verification works

---

### Phase 4: Add Status Column and Inline Editing
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 1, Phase 2, Phase 3 complete

**Objective:** Add status column to table with inline editing

**Tasks:**
4.1. Add status column to table
   - Hook into `manage_edit-aps_tag_columns`
   - Add 'status' column after 'name'
   - Column label: "Status"

4.2. Add order column to table
   - Add 'order' column after 'status'
   - Column label: "Order"

4.3. Render status column content
   - Hook into `manage_aps_tag_custom_column`
   - Display badge: Published (green), Draft (yellow)
   - Add edit button for quick status change

4.4. Render order column content
   - Display order number
   - Add edit button for quick order change

4.5. Add AJAX for inline editing
   - AJAX action: `aps_edit_tag_status`
   - AJAX action: `aps_edit_tag_order`
   - Nonce verification
   - Permission checks
   - Update via TagRepository

**Files to Modify:**
- `src/Admin/TagFields.php` (for columns)
- `src/Admin/AjaxHandler.php` (for AJAX)

**Code Example:**
```php
// TagFields.php - Add columns
public function add_custom_columns(array $columns): array {
    // ... existing code
    
    // Insert status after name
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'name') {
            $new_columns['status'] = __('Status', 'affiliate-product-showcase');
            $new_columns['order'] = __('Order', 'affiliate-product-showcase');
        }
    }
    
    return $new_columns;
}

// Render columns
public function render_custom_columns(string $column_name, int $term_id): void {
    if ($column_name === 'status') {
        $status = get_term_meta($term_id, '_aps_tag_status', true) ?: 'publish';
        $badge_class = $status === 'publish' ? 'published' : 'draft';
        $status_label = $status === 'publish' 
            ? __('Published', 'affiliate-product-showcase')
            : __('Draft', 'affiliate-product-showcase');
        ?>
        <span class="aps-status-badge <?php echo esc_attr($badge_class); ?>">
            <?php echo esc_html($status_label); ?>
        </span>
        <button class="aps-quick-edit button button-small" 
                data-term-id="<?php echo esc_attr($term_id); ?>"
                data-field="status">
            <?php esc_html_e('Edit', 'affiliate-product-showcase'); ?>
        </button>
        <?php
    }
    
    if ($column_name === 'order') {
        $order = get_term_meta($term_id, '_aps_tag_order', true) ?: 0;
        ?>
        <span class="aps-order-display"><?php echo intval($order); ?></span>
        <button class="aps-quick-edit button button-small"
                data-term-id="<?php echo esc_attr($term_id); ?>"
                data-field="order">
            <?php esc_html_e('Edit', 'affiliate-product-showcase'); ?>
        </button>
        <?php
    }
}
```

**Acceptance Criteria:**
- [ ] Status column appears in table
- [ ] Order column appears in table
- [ ] Status badges display correctly
- [ ] Inline edit buttons appear
- [ ] AJAX updates work for status
- [ ] AJAX updates work for order
- [ ] Nonce verification in AJAX
- [ ] Permission checks in AJAX

---

### Phase 5: Add Status Filter Links Above Table
**Priority:** HIGH  
**Estimated Complexity:** Low  
**Dependencies:** Phase 2 complete

**Objective:** Add status filter links: All (2) | Published (2) | Draft (0) | Trash (0)

**Tasks:**
5.1. Hook into `restrict_manage_posts` for tags
   - Use `restrict_manage_aps_tag` hook if available
   - Or use JavaScript injection

5.2. Render status filter links
   - Count tags by status
   - Display: All (count) | Published (count) | Draft (count) | Trash (count)
   - Highlight current filter
   - Add query parameters for filtering

5.3. Handle filter in TagRepository
   - Check `$_GET['tag_status']` parameter
   - Pass status to all() method

**Files to Modify:**
- `src/Admin/TagFields.php` (or create new `TagFilters.php`)

**Code Example:**
```php
// TagFields.php - Add status filters
public function add_status_filters(): void {
    $current_status = isset($_GET['tag_status']) ? sanitize_text_field($_GET['tag_status']) : '';
    
    // Count by status
    $all_count = wp_count_terms('aps_tag', ['hide_empty' => false]);
    $published_count = wp_count_terms('aps_tag', [
        'hide_empty' => false,
        'meta_query' => [
            ['key' => '_aps_tag_status', 'value' => 'publish']
        ]
    ]);
    $draft_count = wp_count_terms('aps_tag', [
        'hide_empty' => false,
        'meta_query' => [
            ['key' => '_aps_tag_status', 'value' => 'draft']
        ]
    ]);
    $trash_count = wp_count_terms('aps_tag', [
        'hide_empty' => false,
        'meta_query' => [
            ['key' => '_aps_tag_status', 'value' => 'trash']
        ]
    ]);
    
    ?>
    <div class="aps-status-filters">
        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_tag&post_type=aps_product')); ?>"
                   class="<?php echo $current_status === '' ? 'current' : ''; ?>">
                    <?php printf(
                        /* translators: %s: count */
                        esc_html__('All (%s)', 'affiliate-product-showcase'),
                        esc_html($all_count)
                    ); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_tag&post_type=aps_product&tag_status=publish')); ?>"
                   class="<?php echo $current_status === 'publish' ? 'current' : ''; ?>">
                    <?php printf(
                        /* translators: %s: count */
                        esc_html__('Published (%s)', 'affiliate-product-showcase'),
                        esc_html($published_count)
                    ); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_tag&post_type=aps_product&tag_status=draft')); ?>"
                   class="<?php echo $current_status === 'draft' ? 'current' : ''; ?>">
                    <?php printf(
                        /* translators: %s: count */
                        esc_html__('Draft (%s)', 'affiliate-product-showcase'),
                        esc_html($draft_count)
                    ); ?>
                </a> |
            </li>
            <li>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=aps_tag&post_type=aps_product&tag_status=trash')); ?>"
                   class="<?php echo $current_status === 'trash' ? 'current' : ''; ?>">
                    <?php printf(
                        /* translators: %s: count */
                        esc_html__('Trash (%s)', 'affiliate-product-showcase'),
                        esc_html($trash_count)
                    ); ?>
                </a>
            </li>
        </ul>
    </div>
    <?php
}
```

**Acceptance Criteria:**
- [ ] Status filter links appear above table
- [ ] Counts are accurate for each status
- [ ] Current filter is highlighted
- [ ] Clicking filter applies correctly
- [ ] All filter works (shows all tags)
- [ ] Published filter works
- [ ] Draft filter works
- [ ] Trash filter works

---

### Phase 6: Add Enhanced Bulk Actions
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 2 complete

**Objective:** Add bulk actions: move to draft, move to trash, delete permanently

**Tasks:**
6.1. Hook into `bulk_actions-edit-aps_tag`
   - Add "Move to Draft" action
   - Add "Move to Trash" action
   - Add "Delete Permanently" action

6.2. Handle bulk actions in custom handler
   - Hook into `handle_bulk_actions-edit-aps_tag`
   - Call TagRepository bulk methods
   - Return success/error messages

6.3. Add bulk action nonce verification
   - Verify nonce for bulk actions
   - Check permissions

**Files to Modify:**
- `src/Admin/TagFields.php` (or create new `TagBulkActions.php`)

**Code Example:**
```php
// TagFields.php - Add bulk actions
public function add_bulk_actions(array $actions): array {
    $actions['move_to_draft'] = __('Move to Draft', 'affiliate-product-showcase');
    $actions['move_to_trash'] = __('Move to Trash', 'affiliate-product-showcase');
    $actions['delete_permanently'] = __('Delete Permanently', 'affiliate-product-showcase');
    
    return $actions;
}

// Handle bulk actions
public function handle_bulk_actions(string $redirect, string $action, array $tag_ids): string {
    $current_tag = isset($_REQUEST['tag']) ? intval($_REQUEST['tag']) : 0;
    
    if ($action === 'move_to_draft') {
        $this->repository->change_status($tag_ids, 'draft');
        $redirect = add_query_arg([
            'aps_bulk_drafted' => count($tag_ids),
        ], $redirect);
    } elseif ($action === 'move_to_trash') {
        $this->repository->change_status($tag_ids, 'trash');
        $redirect = add_query_arg([
            'aps_bulk_trashed' => count($tag_ids),
        ], $redirect);
    } elseif ($action === 'delete_permanently') {
        $this->repository->delete_permanently($tag_ids);
        $redirect = add_query_arg([
            'aps_bulk_deleted' => count($tag_ids),
        ], $redirect);
    }
    
    return $redirect;
}

// Add admin notices for bulk actions
public function bulk_action_notices(): void {
    if (!isset($_REQUEST['aps_bulk_drafted']) && 
        !isset($_REQUEST['aps_bulk_trashed']) && 
        !isset($_REQUEST['aps_bulk_deleted'])) {
        return;
    }
    
    if (isset($_REQUEST['aps_bulk_drafted'])) {
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: %s: count */
                _n('%s tag moved to draft.', '%s tags moved to draft.', $_REQUEST['aps_bulk_drafted'], 'affiliate-product-showcase'),
                number_format_i18n($_REQUEST['aps_bulk_drafted'])
            ))
        );
    }
    
    if (isset($_REQUEST['aps_bulk_trashed'])) {
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: %s: count */
                _n('%s tag moved to trash.', '%s tags moved to trash.', $_REQUEST['aps_bulk_trashed'], 'affiliate-product-showcase'),
                number_format_i18n($_REQUEST['aps_bulk_trashed'])
            ))
        );
    }
    
    if (isset($_REQUEST['aps_bulk_deleted'])) {
        printf(
            '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            esc_html(sprintf(
                /* translators: %s: count */
                _n('%s tag permanently deleted.', '%s tags permanently deleted.', $_REQUEST['aps_bulk_deleted'], 'affiliate-product-showcase'),
                number_format_i18n($_REQUEST['aps_bulk_deleted'])
            ))
        );
    }
}
```

**Acceptance Criteria:**
- [ ] Bulk actions appear in dropdown
- [ ] Move to Draft works
- [ ] Move to Trash works
- [ ] Delete Permanently works
- [ ] Success notices display
- [ ] Nonce verification in place
- [ ] Permission checks in place

---

### Phase 7: Add Sort by Order Above Table
**Priority:** MEDIUM  
**Estimated Complexity:** Low  
**Dependencies:** Phase 2 complete

**Objective:** Add sort by order option above table

**Tasks:**
7.1. Add sort dropdown above table
   - Options: Order (default), Name, Count
   - Pre-select current sort
   - Add query parameter for sorting

7.2. Handle sort parameter
   - Check `$_GET['orderby']` parameter
   - Check `$_GET['order']` parameter (ASC/DESC)
   - Pass to TagRepository all()

7.3. Update TagRepository to support custom sorting
   - Support `orderby` parameter
   - Support `order` parameter

**Files to Modify:**
- `src/Admin/TagFields.php` (for sort UI)
- `src/Repositories/TagRepository.php` (already done in Phase 2)

**Code Example:**
```php
// TagFields.php - Add sort dropdown
public function add_sort_dropdown(): void {
    $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'order';
    $current_order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : 'ASC';
    
    ?>
    <div class="aps-sort-dropdown">
        <label for="aps_tag_orderby">
            <?php esc_html_e('Sort by:', 'affiliate-product-showcase'); ?>
        </label>
        <select id="aps_tag_orderby" name="orderby" onchange="this.form.submit()">
            <option value="order" <?php selected($current_orderby, 'order'); ?>>
                <?php esc_html_e('Order', 'affiliate-product-showcase'); ?>
            </option>
            <option value="name" <?php selected($current_orderby, 'name'); ?>>
                <?php esc_html_e('Name', 'affiliate-product-showcase'); ?>
            </option>
            <option value="count" <?php selected($current_orderby, 'count'); ?>>
                <?php esc_html_e('Count', 'affiliate-product-showcase'); ?>
            </option>
        </select>
        
        <select id="aps_tag_order" name="order" onchange="this.form.submit()">
            <option value="ASC" <?php selected($current_order, 'ASC'); ?>>
                <?php esc_html_e('Ascending', 'affiliate-product-showcase'); ?>
            </option>
            <option value="DESC" <?php selected($current_order, 'DESC'); ?>>
                <?php esc_html_e('Descending', 'affiliate-product-showcase'); ?>
            </option>
        </select>
    </div>
    <?php
}
```

**Acceptance Criteria:**
- [ ] Sort dropdown appears above table
- [ ] Sort by Order option available
- [ ] Sort by Name option available
- [ ] Sort by Count option available
- [ ] ASC/DESC options available
- [ ] Sorting works correctly
- [ ] Current sort is pre-selected

---

### Phase 8: Update TagsController with Status Fields
**Priority:** MEDIUM  
**Estimated Complexity:** Low  
**Dependencies:** Phase 1, Phase 2 complete

**Objective:** Include status fields in REST API responses

**Tasks:**
8.1. Update index() method
   - Include status fields in responses
   - Support status filtering via query params

8.2. Update store() method
   - Accept status fields in request body
   - Validate status values

8.3. Update update() method
   - Accept status fields in request body
   - Validate status values

**Files to Modify:**
- `src/Rest/TagsController.php`

**Acceptance Criteria:**
- [ ] Status fields included in API responses
- [ ] API accepts status fields in requests
- [ ] Status filtering works via API
- [ ] Validation works for status values

---

### Phase 9: Register Tags Components in DI Container
**Priority:** MEDIUM  
**Estimated Complexity:** Medium  
**Dependencies:** All previous phases complete

**Objective:** Complete DI container registration for tags

**Tasks:**
9.1. Register TagRepository in ServiceProvider
   - Add to `register_repositories()` method
   - Bind TagRepository to RepositoryInterface

9.2. Register TagsController in ServiceProvider
   - Add to `register_controllers()` method
   - Bind TagsController

9.3. Register TagFields in ServiceProvider
   - Add to `register_admin()` method
   - Initialize TagFields->init()

9.4. Verify all dependencies injected correctly
   - Check TagRepository gets Cache
   - Check TagsController gets TagRepository
   - Check TagFields gets TagRepository

**Files to Modify:**
- `src/Plugin/ServiceProvider.php`

**Code Example:**
```php
// ServiceProvider.php - Register tags components
private function register_repositories(): void {
    $this->container->bind(
        'AffiliateProductShowcase\Repositories\TagRepository',
        function ($container) {
            return new \AffiliateProductShowcase\Repositories\TagRepository(
                $container->get('AffiliateProductShowcase\Cache\Cache')
            );
        }
    );
}

private function register_controllers(): void {
    $this->container->bind(
        'AffiliateProductShowcase\Rest\TagsController',
        function ($container) {
            return new \AffiliateProductShowcase\Rest\TagsController(
                $container->get('AffiliateProductShowcase\Repositories\TagRepository'),
                $container->get('AffiliateProductShowcase\Security\PermissionManager'),
                $container->get('AffiliateProductShowcase\Security\RateLimiter')
            );
        }
    );
}

private function register_admin(): void {
    $this->container->bind(
        'AffiliateProductShowcase\Admin\TagFields',
        function ($container) {
            return new \AffiliateProductShowcase\Admin\TagFields(
                $container->get('AffiliateProductShowcase\Repositories\TagRepository')
            );
        }
    );
}
```

**Acceptance Criteria:**
- [ ] TagRepository registered in DI container
- [ ] TagsController registered in DI container
- [ ] TagFields registered in DI container
- [ ] All dependencies injected correctly
- [ ] No direct instantiation in Admin.php

---

### Phase 10: Add CSS for Status Badges and UI
**Priority:** LOW  
**Estimated Complexity:** Low  
**Dependencies:** Phase 4, Phase 5, Phase 6 complete

**Objective:** Add styling for status badges, filters, and bulk actions

**Tasks:**
10.1. Add status badge styles
   - Published: green badge
   - Draft: yellow/orange badge
   - Trash: red badge

10.2. Add status filter styles
   - Filter link styling
   - Active filter highlighting

10.3. Add bulk action button styles
   - Consistent with WordPress admin styles

10.4. Add sort dropdown styles
   - Inline with filters
   - Proper spacing

**Files to Modify:**
- `assets/css/admin-tag.css`

**Code Example:**
```css
/* admin-tag.css - Status badges */
.aps-status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.aps-status-badge.published {
    background-color: #22c55e;
    color: #ffffff;
}

.aps-status-badge.draft {
    background-color: #f59e0b;
    color: #ffffff;
}

.aps-status-badge.trash {
    background-color: #ef4444;
    color: #ffffff;
}

/* Status filters */
.aps-status-filters {
    margin-bottom: 20px;
}

.aps-status-filters .subsubsub {
    margin: 0;
    padding: 0;
    list-style: none;
}

.aps-status-filters .subsubsub li {
    display: inline;
    margin-right: 0;
    padding: 0;
}

.aps-status-filters .subsubsub li a {
    text-decoration: none;
}

.aps-status-filters .subsubsub li a.current {
    font-weight: bold;
    color: #000;
}

/* Sort dropdown */
.aps-sort-dropdown {
    float: right;
    margin-top: 3px;
}

.aps-sort-dropdown label {
    font-weight: 600;
    margin-right: 10px;
}

.aps-sort-dropdown select {
    margin-right: 10px;
}

/* Quick edit buttons */
.aps-quick-edit {
    margin-left: 5px;
    font-size: 11px;
}
```

**Acceptance Criteria:**
- [ ] Status badges styled correctly
- [ ] Status filters styled correctly
- [ ] Bulk actions styled correctly
- [ ] Sort dropdown styled correctly
- [ ] Responsive design works

---

## 3. Testing & Verification

### Phase 11: Manual Testing
**Priority:** HIGH  
**Estimated Complexity:** Medium

**Test Cases:**
1. Create tag with featured flag
2. Create tag with default flag
3. Create tag as draft
4. Edit tag status inline
5. Edit tag order inline
6. Filter by status (All, Published, Draft, Trash)
7. Bulk move to draft
8. Bulk move to trash
9. Bulk delete permanently
10. Sort by order
11. Sort by name
12. Sort by count

**Acceptance Criteria:**
- [ ] All test cases pass
- [ ] No console errors
- [ ] No PHP errors
- [ ] No WordPress notices (except success)

### Phase 12: Code Quality Verification
**Priority:** MEDIUM  
**Estimated Complexity:** Low

**Tasks:**
1. Run PHPStan
2. Run Psalm
3. Run PHPCS
4. Verify type hints
5. Verify PHPDoc comments

**Acceptance Criteria:**
- [ ] PHPStan passes (level 6+)
- [ ] Psalm passes (level 4-5)
- [ ] PHPCS passes (PSR-12 + WPCS)
- [ ] All type hints present
- [ ] All PHPDoc comments present

---

## 4. Implementation Timeline

### Week 1: Core Data Layer
- Phase 1: Enhance Tag Model (Day 1)
- Phase 2: Enhance TagRepository (Day 2-3)
- Phase 3: Enhance TagFields (Day 4-5)

### Week 2: UI Layer
- Phase 4: Add Status Column (Day 1-2)
- Phase 5: Add Status Filters (Day 3)
- Phase 6: Add Bulk Actions (Day 4-5)
- Phase 7: Add Sort by Order (Day 5)

### Week 3: Integration & Polish
- Phase 8: Update TagsController (Day 1)
- Phase 9: DI Container Registration (Day 2)
- Phase 10: Add CSS (Day 3)
- Phase 11: Manual Testing (Day 4-5)

### Week 4: Verification & Deployment
- Phase 12: Code Quality Verification (Day 1)
- Final Testing (Day 2)
- Documentation Updates (Day 3)
- Deployment Preparation (Day 4-5)

---

## 5. Risk Assessment

### High Risk Items
1. **DI Container Registration Complexity**
   - Risk: Breaking existing functionality
   - Mitigation: Test thoroughly before deployment
   - Contingency: Revert changes if issues occur

2. **Bulk Action Security**
   - Risk: Unauthorized bulk operations
   - Mitigation: Strict nonce and permission checks
   - Contingency: Disable bulk actions temporarily

### Medium Risk Items
1. **AJAX Inline Editing**
   - Risk: Race conditions
   - Mitigation: Use WordPress AJAX API properly
   - Contingency: Remove inline editing if unstable

2. **Status Filter Performance**
   - Risk: Slow query performance
   - Mitigation: Add caching for counts
   - Contingency: Remove counts temporarily

### Low Risk Items
1. **CSS Styling**
   - Risk: Display issues
   - Mitigation: Test across browsers
   - Contingency: Revert to basic styles

---

## 6. Success Criteria

**Functional Requirements:**
- [x] All status fields working (featured, default, status, order)
- [ ] Status editing in table works
- [ ] Status filtering works
- [ ] Bulk actions work
- [ ] Sort by order works

**Quality Requirements:**
- [ ] Code quality 10/10 (enterprise-grade)
- [ ] All tests passing
- [ ] No security vulnerabilities
- [ ] Performance targets met

**True Hybrid Compliance:**
- [ ] All components registered in DI container
- [ ] WordPress native + custom approach maintained
- [ ] Consistent underscore prefix pattern
- [ ] Model-Factory-Repository pattern complete

**User Requirements:**
- [ ] Featured/default flags in tag form
- [ ] Status editable in table
- [ ] Sort by order above table
- [ ] Bulk actions (draft, trash, delete)
- [ ] Status filter links above table

---

## 7. Post-Implementation Checklist

### Immediate Actions
- [ ] All phases complete
- [ ] All acceptance criteria met
- [ ] All tests passing
- [ ] Code quality verification complete

### Documentation Updates
- [ ] Update Tag model PHPDoc
- [ ] Update TagRepository PHPDoc
- [ ] Update TagFields PHPDoc
- [ ] Create changelog entry
- [ ] Update user documentation

### Deployment Preparation
- [ ] Backup database
- [ ] Create deployment branch
- [ ] Test on staging environment
- [ ] Prepare rollback plan

### Monitoring
- [ ] Monitor error logs
- [ ] Monitor performance
- [ ] Monitor user feedback
- [ ] Track usage metrics

---

## 8. Conclusion

This implementation plan addresses all gaps identified in the compliance report and fulfills all user requirements:

**Key Deliverables:**
1. ✅ Status management features (featured, default, draft/publish)
2. ✅ Status editing in admin table
3. ✅ Status filter links (All | Published | Draft | Trash)
4. ✅ Enhanced bulk actions (move to draft, move to trash, delete)
5. ✅ Sort by order functionality
6. ✅ Complete DI container registration
7. ✅ Full true hybrid compliance

**Expected Outcome:**
- **Current:** 7/10 (PARTIALLY TRUE HYBRID)
- **Target:** 10/10 (FULL TRUE HYBRID)
- **Completion:** 11/11 phases (100%)

**Timeline:** 4 weeks  
**Team Size:** 1-2 developers  
**Risk Level:** Medium (with proper mitigation)

---

**Plan Version:** 1.0.0  
**Last Updated:** January 25, 2026  
**Based on:** findings/section3-tags-hybrid-compliance-report.md