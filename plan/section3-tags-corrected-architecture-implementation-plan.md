# Section 3: Tags - Corrected Architecture Implementation Plan

**Created:** January 25, 2026  
**Based on:** User feedback on architectural issues  
**Status:** CORRECTED - Using proper WordPress taxonomy systems

---

## Executive Summary

**Previous Plan Status:** ❌ INCORRECT ARCHITECTURE  
**Corrected Plan Status:** ✅ PROPER WORDPRESS PATTERNS  
**Issue:** Using term_meta for filtering data instead of taxonomies

**Critical Correction:**
- **WRONG:** Storing status/featured/default as term_meta
- **RIGHT:** Using separate taxonomies for filtering/grouping data
- **CORRECT:** Using term_meta for display-only data (color, icon, order)

---

## 1. Architectural Problem & Solution

### ❌ The Problem (Previous Plan)

```php
// WRONG - Using term_meta for filtering data
update_term_meta($term_id, '_aps_tag_status', 'publish');    // ❌
update_term_meta($term_id, '_aps_tag_featured', true);       // ❌
update_term_meta($term_id, '_aps_tag_default', true);        // ❌
```

**Why It's Wrong:**
- Querying by term meta is slow (value column not indexed)
- WordPress best practice: Use taxonomies for grouping/filtering
- Custom fields are for item-level data, not classification

### ✅ The Solution (Corrected Architecture)

```php
// CORRECT - Using taxonomies for filtering/grouping
wp_set_object_terms($tag_id, 'published', 'aps_tag_visibility');  // ✅
wp_set_object_terms($tag_id, 'featured', 'aps_tag_flags');     // ✅

// CORRECT - Using term_meta for display-only data
update_term_meta($term_id, '_aps_tag_color', '#ff0000');     // ✅
update_term_meta($term_id, '_aps_tag_icon', 'icon-class');      // ✅
update_term_meta($term_id, '_aps_tag_order', 0);               // ✅
```

---

## 2. Corrected Architecture Overview

### Taxonomy-Based Status Management

**Feature | Implementation | Type | Rationale**
---------|----------------|-------|-----------
**Status** | `aps_tag_visibility` taxonomy | Taxonomy terms: 'published', 'draft', 'trash' | Filtering/grouping data
**Featured** | `aps_tag_flags` taxonomy | Taxonomy terms: 'featured', 'none' | Filtering/grouping data
**Default** | WordPress option | `aps_default_tag` option | Single global setting
**Order** | Term meta | `_aps_tag_order` | Display-only (acceptable)
**Color** | Term meta | `_aps_tag_color` | Display-only
**Icon** | Term meta | `_aps_tag_icon` | Display-only

### Data Flow

```
Tag Creation/Editing:
├─ User inputs: Name, Description, Featured, Default, Status, Order, Color, Icon
├─ Save to Taxonomies:
│  ├─ aps_tag (main tag taxonomy)
│  ├─ aps_tag_visibility (status: published/draft/trash)
│  └─ aps_tag_flags (featured flag: featured/none)
├─ Save to Term Meta:
│  ├─ _aps_tag_order (display order)
│  ├─ _aps_tag_color (display color)
│  └─ _aps_tag_icon (display icon)
└─ Save to Options:
   └─ aps_default_tag (if default checked)

Filtering:
├─ Filter by status: Query aps_tag_visibility taxonomy
├─ Filter by featured: Query aps_tag_flags taxonomy
└─ Display sorted by: Order term meta

Bulk Actions:
├─ Change status: Change aps_tag_visibility term
├─ Toggle featured: Change aps_tag_flags term
└─ Delete: Remove from all taxonomies
```

---

## 3. Implementation Phases

### Phase 1: Create Status Taxonomy
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** None

**Objective:** Create `aps_tag_visibility` taxonomy for status management

**Tasks:**
1.1. Register aps_tag_visibility taxonomy
   - Non-hierarchical
   - Not public (admin-only)
   - No UI (hide from users)
   - Terms: published, draft, trash

1.2. Pre-populate status terms
   - Create 'published' term on activation
   - Create 'draft' term on activation
   - Create 'trash' term on activation

1.3. Add helper methods for status
   - `get_tag_visibility(int $tag_id): ?string`
   - `set_tag_visibility(int $tag_id, string $status): void`

**Files to Modify:**
- `src/Services/ProductService.php` (register taxonomy)
- Create: `src/Admin/TagStatus.php` (helper methods)

**Code Example:**
```php
// ProductService.php - Register status taxonomy
private static function register_taxonomies_static(): void {
    // ... existing tag category/ribbon registration
    
    // Register tag visibility taxonomy (for status)
    register_taxonomy(
        'aps_tag_visibility',
        'aps_product',
        [
            'labels' => [
                'name' => __('Tag Status', 'affiliate-product-showcase'),
                'singular_name' => __('Tag Status', 'affiliate-product-showcase'),
            ],
            'hierarchical' => false,
            'public' => false,  // Don't show on frontend
            'show_ui' => false,  // Don't show in admin UI
            'show_in_rest' => false,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'meta_box_cb' => false,  // Don't show meta box
            'rewrite' => false,
        ]
    );
}

// Activation - Create default status terms
public static function create_default_status_terms(): void {
    $status_terms = ['published', 'draft', 'trash'];
    
    foreach ($status_terms as $slug) {
        $term = get_term_by('slug', $slug, 'aps_tag_visibility');
        if (!$term) {
            wp_insert_term(
                ucfirst($slug),
                'aps_tag_visibility',
                ['slug' => $slug]
            );
        }
    }
}
```

**Acceptance Criteria:**
- [ ] aps_tag_visibility taxonomy registered
- [ ] Taxonomy is non-public
- [ ] No UI shown for taxonomy
- [ ] Default status terms created
- [ ] Helper methods work correctly

---

### Phase 2: Create Flags Taxonomy
**Priority:** HIGH  
**Estimated Complexity:** Low  
**Dependencies:** Phase 1 complete

**Objective:** Create `aps_tag_flags` taxonomy for featured flag

**Tasks:**
2.1. Register aps_tag_flags taxonomy
   - Non-hierarchical
   - Not public (admin-only)
   - No UI (hide from users)
   - Terms: featured, none

2.2. Pre-populate flag terms
   - Create 'featured' term on activation
   - Create 'none' term on activation

2.3. Add helper methods for flags
   - `get_tag_featured(int $tag_id): bool`
   - `set_tag_featured(int $tag_id, bool $featured): void`

**Files to Modify:**
- `src/Services/ProductService.php` (register taxonomy)
- Create: `src/Admin/TagFlags.php` (helper methods)

**Code Example:**
```php
// ProductService.php - Register flags taxonomy
private static function register_taxonomies_static(): void {
    // ... existing registration
    
    // Register tag flags taxonomy (for featured)
    register_taxonomy(
        'aps_tag_flags',
        'aps_product',
        [
            'labels' => [
                'name' => __('Tag Flags', 'affiliate-product-showcase'),
                'singular_name' => __('Tag Flag', 'affiliate-product-showcase'),
            ],
            'hierarchical' => false,
            'public' => false,
            'show_ui' => false,
            'show_in_rest' => false,
            'show_in_nav_menus' => false,
            'show_tagcloud' => false,
            'meta_box_cb' => false,
            'rewrite' => false,
        ]
    );
}

// Activation - Create default flag terms
public static function create_default_flag_terms(): void {
    $flag_terms = ['featured', 'none'];
    
    foreach ($flag_terms as $slug) {
        $term = get_term_by('slug', $slug, 'aps_tag_flags');
        if (!$term) {
            wp_insert_term(
                ucfirst($slug),
                'aps_tag_flags',
                ['slug' => $slug]
            );
        }
    }
}
```

**Acceptance Criteria:**
- [ ] aps_tag_flags taxonomy registered
- [ ] Taxonomy is non-public
- [ ] No UI shown for taxonomy
- [ ] Default flag terms created
- [ ] Helper methods work correctly

---

### Phase 3: Update Tag Model
**Priority:** HIGH  
**Estimated Complexity:** Low  
**Dependencies:** Phase 1, Phase 2 complete

**Objective:** Update Tag model to use taxonomy-based status/flags

**Tasks:**
3.1. Update Tag properties
   - Remove: `status` (will use taxonomy)
   - Remove: `featured` (will use taxonomy)
   - Remove: `default` (will use option)
   - Keep: `order` (term meta)
   - Keep: `color` (term meta)
   - Keep: `icon` (term meta)

3.2. Update from_wp_term() to read from taxonomies
   - Get visibility from aps_tag_visibility taxonomy
   - Get featured flag from aps_tag_flags taxonomy

3.3. Update from_array() to accept status/featured
   - Accept status (will set visibility taxonomy)
   - Accept featured (will set flags taxonomy)

**Files to Modify:**
- `src/Models/Tag.php`

**Code Example:**
```php
// Tag.php - Updated model
final class Tag {
    public readonly int $id;
    public readonly string $name;
    public readonly string $slug;
    public readonly string $description;
    public readonly int $count;
    
    // Display-only term meta
    public readonly ?string $color;
    public readonly ?string $icon;
    public readonly int $order;
    
    // Status from taxonomy
    public readonly string $status;  // 'published', 'draft', 'trash'
    
    // Featured from taxonomy
    public readonly bool $featured;  // true/false
    
    // Default from option
    public readonly bool $default;  // true/false

    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $description = '',
        int $count = 0,
        ?string $color = null,
        ?string $icon = null,
        int $order = 0,
        string $status = 'published',
        bool $featured = false,
        bool $default = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->count = $count;
        $this->color = $color;
        $this->icon = $icon;
        $this->order = $order;
        $this->status = $status;
        $this->featured = $featured;
        $this->default = $default;
    }

    public static function from_wp_term(\WP_Term $term): Tag {
        // Get visibility status from taxonomy
        $visibility_terms = wp_get_object_terms($term->term_id, 'aps_tag_visibility');
        $status = !empty($visibility_terms) ? $visibility_terms[0]->slug : 'published';
        
        // Get featured flag from taxonomy
        $flag_terms = wp_get_object_terms($term->term_id, 'aps_tag_flags');
        $featured = !empty($flag_terms) && $flag_terms[0]->slug === 'featured';
        
        // Get default from option
        $default_tag = get_option('aps_default_tag', 0);
        $is_default = ($default_tag === $term->term_id);
        
        // Get display-only meta
        $color = get_term_meta($term->term_id, '_aps_tag_color', true) ?: null;
        $icon = get_term_meta($term->term_id, '_aps_tag_icon', true) ?: null;
        $order = (int) get_term_meta($term->term_id, '_aps_tag_order', true) ?: 0;
        
        return new Tag(
            $term->term_id,
            $term->name,
            $term->slug,
            $term->description,
            $term->count,
            $color,
            $icon,
            $order,
            $status,
            $featured,
            $is_default
        );
    }
}
```

**Acceptance Criteria:**
- [ ] Tag model updated
- [ ] from_wp_term() reads from taxonomies
- [ ] Status from aps_tag_visibility
- [ ] Featured from aps_tag_flags
- [ ] Default from option
- [ ] Display meta from term_meta

---

### Phase 4: Update TagRepository with Taxonomy Methods
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 3 complete

**Objective:** Update repository to use taxonomy-based status/flags

**Tasks:**
4.1. Add status helper methods
   - `get_visibility(int $tag_id): string`
   - `set_visibility(int $tag_id, string $status): void`

4.2. Add featured helper methods
   - `get_featured(int $tag_id): bool`
   - `set_featured(int $tag_id, bool $featured): void`

4.3. Update all() method to filter by taxonomies
   - Support filtering by aps_tag_visibility
   - Support filtering by aps_tag_flags

4.4. Add bulk methods using taxonomies
   - `change_status(array $ids, string $status): void`
   - `change_featured(array $ids, bool $featured): void`

**Files to Modify:**
- `src/Repositories/TagRepository.php`

**Code Example:**
```php
// TagRepository.php - Add helper methods with caching
private function get_visibility_term_cached(string $slug): ?\WP_Term {
    $cache_key = "aps_visibility_term_{$slug}";
    $term = wp_cache_get($cache_key, 'aps_tag_visibility');
    
    if ($term === false) {
        $term = get_term_by('slug', $slug, 'aps_tag_visibility');
        wp_cache_set($cache_key, $term, 'aps_tag_visibility', HOUR_IN_SECONDS);
    }
    
    return $term ?: null;
}

private function get_flag_term_cached(string $slug): ?\WP_Term {
    $cache_key = "aps_flag_term_{$slug}";
    $term = wp_cache_get($cache_key, 'aps_tag_flags');
    
    if ($term === false) {
        $term = get_term_by('slug', $slug, 'aps_tag_flags');
        wp_cache_set($cache_key, $term, 'aps_tag_flags', HOUR_IN_SECONDS);
    }
    
    return $term ?: null;
}

public function get_visibility(int $tag_id): string {
    $terms = wp_get_object_terms($tag_id, 'aps_tag_visibility');
    return !empty($terms) ? $terms[0]->slug : 'published';
}

public function set_visibility(int $tag_id, string $status): void {
    $term = $this->get_visibility_term_cached($status);
    if ($term) {
        wp_set_object_terms($tag_id, [$term->term_id], 'aps_tag_visibility');
    }
}

public function get_featured(int $tag_id): bool {
    $terms = wp_get_object_terms($tag_id, 'aps_tag_flags');
    return !empty($terms) && $terms[0]->slug === 'featured';
}

public function set_featured(int $tag_id, bool $featured): void {
    $term_slug = $featured ? 'featured' : 'none';
    $term = $this->get_flag_term_cached($term_slug);
    if ($term) {
        wp_set_object_terms($tag_id, [$term->term_id], 'aps_tag_flags');
    }
}

public function change_status(array $ids, string $status): void {
    $term = $this->get_visibility_term_cached($status);
    if (!$term) {
        return;
    }
    
    foreach ($ids as $id) {
        wp_set_object_terms($id, [$term->term_id], 'aps_tag_visibility');
    }
}

public function change_featured(array $ids, bool $featured): void {
    $term_slug = $featured ? 'featured' : 'none';
    $term = $this->get_flag_term_cached($term_slug);
    if (!$term) {
        return;
    }
    
    foreach ($ids as $id) {
        wp_set_object_terms($id, [$term->term_id], 'aps_tag_flags');
    }
}

// Update all() method with taxonomy filtering
public function all(array $args = []): array {
    $defaults = [
        'status' => null,
        'featured' => null,
        'orderby' => 'order',
        'order' => 'ASC',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $query_args = [
        'taxonomy' => 'aps_tag',
        'hide_empty' => false,
    ];
    
    // Add status filter using taxonomy query
    if ($args['status']) {
        $status_term = get_term_by('slug', $args['status'], 'aps_tag_visibility');
        if ($status_term) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'aps_tag_visibility',
                'field' => 'term_id',
                'terms' => $status_term->term_id,
            ];
        }
    }
    
    // Add featured filter using taxonomy query
    if ($args['featured'] !== null) {
        $flag_slug = $args['featured'] ? 'featured' : 'none';
        $flag_term = get_term_by('slug', $flag_slug, 'aps_tag_flags');
        if ($flag_term) {
            $query_args['tax_query'][] = [
                'taxonomy' => 'aps_tag_flags',
                'field' => 'term_id',
                'terms' => $flag_term->term_id,
            ];
        }
    }
    
    // Add order sorting using term meta
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
- [ ] Helper methods get/set visibility work
- [ ] Helper methods get/set featured work
- [ ] all() supports status filtering via taxonomy
- [ ] all() supports featured filtering via taxonomy
- [ ] Bulk status change works
- [ ] Bulk featured change works

---

### Phase 5: Update TagFields with Taxonomy-Based Inputs
**Priority:** HIGH  
**Estimated Complexity:** Medium  
**Dependencies:** Phase 4 complete

**Objective:** Update form inputs to use taxonomy-based status/flags

**Tasks:**
5.1. Update status select in add_tag_fields()
   - Options: Draft, Published
   - Uses visibility taxonomy (not meta)

5.2. Update featured checkbox in add_tag_fields()
   - Uses flags taxonomy (not meta)

5.3. Update default checkbox in add_tag_fields()
   - Uses WordPress option (not meta)
   - Ensure only one default tag exists

5.4. Update save_tag_fields() to use taxonomies
   - Set visibility taxonomy term
   - Set flags taxonomy term
   - Update default option

**Files to Modify:**
- `src/Admin/TagFields.php`

**Code Example:**
```php
// TagFields.php - Update save_tag_fields()
public function save_tag_fields(int $tag_id, \WP_Term $term): void {
    // Nonce verification (existing)
    
    // Save status using taxonomy
    $status = isset($_POST['_aps_tag_status']) 
        ? sanitize_text_field(wp_unslash($_POST['_aps_tag_status'])) 
        : 'publish';
    
    $status_term = get_term_by('slug', $status, 'aps_tag_visibility');
    if ($status_term) {
        wp_set_object_terms($tag_id, [$status_term->term_id], 'aps_tag_visibility');
    }
    
    // Save featured using taxonomy
    $featured = isset($_POST['_aps_tag_featured']);
    $flag_slug = $featured ? 'featured' : 'none';
    
    $flag_term = get_term_by('slug', $flag_slug, 'aps_tag_flags');
    if ($flag_term) {
        wp_set_object_terms($tag_id, [$flag_term->term_id], 'aps_tag_flags');
    }
    
    // Save default using option (ensure only one)
    if (isset($_POST['_aps_tag_default'])) {
        // Remove default from all other tags
        $current_default = get_option('aps_default_tag', 0);
        if ($current_default && $current_default != $tag_id) {
            wp_set_object_terms($current_default, ['none'], 'aps_tag_flags');
        }
        
        // Set new default
        update_option('aps_default_tag', $tag_id);
    }
    
    // Save display-only meta (color, icon, order) - existing code
}
```

**Acceptance Criteria:**
- [ ] Status saved to visibility taxonomy
- [ ] Featured saved to flags taxonomy
- [ ] Default saved to WordPress option
- [ ] Only one default tag exists
- [ ] Display meta saved to term_meta

---

### Phase 6: Update Status Column and Filtering
**Priority:** HIGH  
**Estimated Complexity:** Low  
**Dependencies:** Phase 4 complete

**Objective:** Update table to use taxonomy-based status

**Tasks:**
6.1. Update status column rendering
   - Read from aps_tag_visibility taxonomy
   - Display badge correctly

6.2. Update status filter links
   - Count by taxonomy terms (not meta)
   - Filter by taxonomy in query

**Files to Modify:**
- `src/Admin/TagFields.php`

**Code Example:**
```php
// TagFields.php - Update status filters
public function add_status_filters(): void {
    $current_status = isset($_GET['tag_status']) ? sanitize_text_field($_GET['tag_status']) : '';
    
    // Count by taxonomy terms (not meta!)
    $all_count = wp_count_terms('aps_tag', ['hide_empty' => false]);
    
    $published_term = get_term_by('slug', 'published', 'aps_tag_visibility');
    $published_count = $published_term 
        ? wp_count_terms('aps_tag', [
            'hide_empty' => false,
            'tax_query' => [
                ['taxonomy' => 'aps_tag_visibility', 'field' => 'term_id', 'terms' => $published_term->term_id]
            ]
        ]) 
        : 0;
    
    $draft_term = get_term_by('slug', 'draft', 'aps_tag_visibility');
    $draft_count = $draft_term 
        ? wp_count_terms('aps_tag', [
            'hide_empty' => false,
            'tax_query' => [
                ['taxonomy' => 'aps_tag_visibility', 'field' => 'term_id', 'terms' => $draft_term->term_id]
            ]
        ]) 
        : 0;
    
    $trash_term = get_term_by('slug', 'trash', 'aps_tag_visibility');
    $trash_count = $trash_term 
        ? wp_count_terms('aps_tag', [
            'hide_empty' => false,
            'tax_query' => [
                ['taxonomy' => 'aps_tag_visibility', 'field' => 'term_id', 'terms' => $trash_term->term_id]
            ]
        ]) 
        : 0;
    
    // Render filter links (same as before)
}
```

**Acceptance Criteria:**
- [ ] Status counts accurate using taxonomy
- [ ] Status filters work using taxonomy
- [ ] Status column displays correctly
- [ ] Query performance improved (vs meta)

---

### Phase 7: Update Bulk Actions with Taxonomy Methods
**Priority:** HIGH  
**Estimated Complexity:** Low  
**Dependencies:** Phase 4 complete

**Objective:** Update bulk actions to use taxonomy methods

**Tasks:**
7.1. Update bulk action handlers
   - Use `change_status()` method (taxonomy-based)
   - Use `change_featured()` method (taxonomy-based)

**Files to Modify:**
- `src/Admin/TagFields.php`

**Acceptance Criteria:**
- [ ] Bulk status change works
- [ ] Bulk featured change works
- [ ] Uses taxonomy methods (not meta)
- [ ] Performance improved (vs meta)

---

### Phase 8: Update TagsController with Taxonomy Support
**Priority:** MEDIUM  
**Estimated Complexity:** Low  
**Dependencies:** Phase 3 complete

**Objective:** Update API to handle taxonomy-based status/flags

**Tasks:**
8.1. Update index() method
   - Support filtering by status taxonomy
   - Support filtering by featured taxonomy

8.2. Update store() method
   - Accept status/featured in request
   - Set taxonomy terms

8.3. Update update() method
   - Accept status/featured in request
   - Set taxonomy terms

**Files to Modify:**
- `src/Rest/TagsController.php`

**Acceptance Criteria:**
- [ ] API returns status from taxonomy
- [ ] API returns featured from taxonomy
- [ ] API accepts status/featured in requests
- [ ] API sets taxonomy terms correctly

---

## 4. Architecture Comparison

### Previous (WRONG) Architecture

```
Tag Data:
├─ aps_tag (taxonomy)
│  └─ Term data (name, slug, description)
└─ Term Meta (❌ WRONG for filtering)
   ├─ _aps_tag_status (publish/draft/trash)
   ├─ _aps_tag_featured (true/false)
   ├─ _aps_tag_default (true/false)
   ├─ _aps_tag_order (0)
   ├─ _aps_tag_color (#ff0000)
   └─ _aps_tag_icon (icon-class)

Performance: SLOW (meta queries not indexed)
```

### Corrected (RIGHT) Architecture

```
Tag Data:
├─ aps_tag (main taxonomy)
│  └─ Term data (name, slug, description)
├─ aps_tag_visibility (taxonomy) ✅
│  └─ Terms: published, draft, trash
├─ aps_tag_flags (taxonomy) ✅
│  └─ Terms: featured, none
├─ aps_default_tag (option) ✅
│  └─ Single integer (tag ID)
└─ Term Meta (display-only) ✅
   ├─ _aps_tag_order (0)
   ├─ _aps_tag_color (#ff0000)
   └─ _aps_tag_icon (icon-class)

Performance: FAST (taxonomy queries indexed)
```

---

## 5. Benefits of Corrected Architecture

### Performance
- ✅ **Faster queries**: Taxonomy queries are indexed
- ✅ **Better caching**: WordPress caches taxonomy data
- ✅ **Scalability**: Handles thousands of tags efficiently

### WordPress Best Practices
- ✅ **Proper patterns**: Using taxonomies for grouping
- ✅ **Future-proof**: Leverages WordPress core
- ✅ **Maintainable**: Follows WordPress conventions

### Flexibility
- ✅ **Easy filtering**: Taxonomy queries are powerful
- ✅ **Extendable**: Easy to add new status types
- ✅ **Compatible**: Works with WordPress ecosystem

---

## 6. Implementation Timeline

### Week 1: Core Architecture
- Phase 1: Create Status Taxonomy (Day 1-2)
- Phase 2: Create Flags Taxonomy (Day 2)
- Phase 3: Update Tag Model (Day 3)
- Phase 4: Update TagRepository (Day 4-5)

### Week 2: UI Integration
- Phase 5: Update TagFields (Day 1-2)
- Phase 6: Update Status Column (Day 3)
- Phase 7: Update Bulk Actions (Day 4)

### Week 3: API & Polish
- Phase 8: Update TagsController (Day 1-2)
- CSS updates (Day 3)
- Testing (Day 4-5)

### Week 4: Verification & Deployment
- Manual testing (Day 1-2)
- Code quality verification (Day 3)
- Documentation (Day 4)
- Deployment (Day 5)

---

## 7. Success Criteria

**Functional Requirements:**
- [ ] Status managed via aps_tag_visibility taxonomy
- [ ] Featured managed via aps_tag_flags taxonomy
- [ ] Default managed via WordPress option
- [ ] Order/Color/Icon managed via term_meta
- [ ] All filtering works correctly
- [ ] All bulk actions work correctly

**Quality Requirements:**
- [ ] Code quality 10/10 (enterprise-grade)
- [ ] WordPress best practices followed
- [ ] Performance targets met (taxonomy queries)
- [ ] All tests passing

**True Hybrid Compliance:**
- [ ] Taxonomies used for filtering/grouping
- [ ] Term meta used for display-only
- [ ] Model-Factory-Repository pattern complete
- [ ] WordPress native + custom approach maintained

---

## 8. Conclusion

**Architecture Status:** ✅ CORRECTED

This corrected plan addresses the fundamental architectural issue:

**Key Corrections:**
1. ✅ Status: aps_tag_visibility taxonomy (not meta)
2. ✅ Featured: aps_tag_flags taxonomy (not meta)
3. ✅ Default: WordPress option (not meta)
4. ✅ Order/Color/Icon: Term meta (display-only)

**Expected Outcome:**
- **Previous:** ❌ Incorrect architecture (meta for filtering)
- **Corrected:** ✅ Proper WordPress patterns (taxonomies for filtering)
- **Performance:** FAST (indexed taxonomy queries)

**Timeline:** 4 weeks  
**Team Size:** 1-2 developers  
**Risk Level:** Low (following WordPress best practices)

---

**Plan Version:** 2.0.0 (CORRECTED)  
**Last Updated:** January 25, 2026  
**Based on:** User feedback on architectural issues