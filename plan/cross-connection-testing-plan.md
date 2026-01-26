# Cross-Connection Testing Plan

**Purpose:** Verify all interactions between different plugin components work correctly  
**Priority:** Critical 🔴  
**Target:** 100% cross-connection reliability  
**Version:** 2.0.0  
**Last Updated:** 2026-01-26  

---

## 📋 Overview

### What Are Cross-Connections?

Cross-connections are **interactions between different plugin components**. While individual features might work perfectly, connections between them often break.

**Example:**
- ✅ Product creation works
- ✅ Ribbon creation works
- ❌ Assigning ribbon to product might fail (cross-connection issue)

### Why This Testing is Critical

**Most bugs hide in cross-connections:**
- Data relationships not saving
- Settings not affecting display
- Orphaned records in database
- Cascade failures on delete
- Priority system not working

**Without cross-connection testing:**
- Plugin appears to work in isolation
- Fails in real-world scenarios
- Users encounter data corruption
- Difficult to debug issues

**With cross-connection testing:**
- All interactions verified
- Data integrity guaranteed
- Edge cases handled
- Production-ready quality

---

## 🎯 Testing Objectives

### Primary Goals

1. **Verify Data Relationships**
   - Products ↔ Categories save and retrieve correctly
   - Products ↔ Tags save and retrieve correctly
   - Products ↔ Ribbons save and retrieve correctly

2. **Verify Settings Impact**
   - Global settings affect admin UI correctly
   - Settings cascade to all backend components
   - Frontend display connections (future)

3. **Verify Display Logic**
   - Priority system works correctly (backend)
   - Filter settings applied correctly
   - Limits enforced correctly

4. **Verify Data Integrity**
   - No orphaned records
   - Proper cascade deletes
   - Bulk operations work correctly
   - Import/Export preserves relationships

### Success Criteria

- ✅ All backend cross-connections tested and verified
- ✅ No data loss in any scenario
- ✅ Settings changes reflect immediately in admin
- ✅ Edge cases handled gracefully
- ✅ No orphaned database records
- ✅ Priority system works as expected

### Critical Testing Principle

**📋 REPORT AFTER EACH TEST STEP**

After completing each individual test scenario, you must:
1. **Document the test result** (Pass/Fail)
2. **Record any issues found** (with details)
3. **Verify database state** (check for orphans, etc.)
4. **Create a mini-report** before proceeding

**Why Report After Each Step?**
- Issues caught early are easier to fix
- Prevents cascading failures
- Provides clear audit trail
- Makes debugging faster
- Ensures no issues are missed

**Example Testing Workflow:**
```
Test 1: Assign single category to product
├─ Execute test
├─ Document result (Pass/Fail)
├─ Check database
├─ Report findings
└─ Only then proceed to Test 2
```

---

## 🔗 Complete Cross-Connection Map

**⚠️ IMPORTANT:** Frontend has NOT been created yet. 

### Testing Status Legend:
- ✅ **READY TO TEST NOW** - Backend connections available for immediate testing
- 🔜 **FUTURE** - Requires frontend development (not yet created)
- 📅 **PLANNED** - To be implemented later

**Current Testing Scope:** Backend connections only (Admin, Database, REST API)  
**Frontend Testing Scope:** Pending frontend development

---

### 1. Products ↔ Categories (Taxonomy) ✅ READY TO TEST NOW

**Connection Type:** Many-to-Many  
**Database Tables:** `posts`, `term_relationships`, `term_taxonomy`, `terms`  
**Affected Features:**
- Product creation/edit
- Category management
- Frontend filtering (future)
- Import/Export

**Test Scenarios:**

| Scenario | Test Steps | Expected Result |
|----------|------------|-----------------|
| Assign single category | Create product → Select "Electronics" → Save | Product assigned to "Electronics" category |
| Assign multiple categories | Create product → Select "Electronics", "Gadgets" → Save | Product assigned to both categories |
| Edit product categories | Edit product → Change to "Sale" → Save | Category updated correctly |
| Remove all categories | Edit product → Deselect all → Save | Product has no categories |
| Delete category with products | Delete "Sale" category → Products reassign or warn | Products handled gracefully |
| Bulk assign categories | Select 5 products → Assign "Featured" | All 5 products assigned to "Featured" |
| Filter by category | Filter products → Select "Electronics" | Shows only Electronics products |
| Category meta sync | Add meta to category → Product displays correctly | Meta data available on product |

**Code Verification:**
```php
// Check relationship saves
$categories = wp_get_post_terms($product_id, 'product_category');
error_log('Product Categories: ' . print_r($categories, true));

// Verify taxonomy registration
$taxonomy = get_taxonomy('product_category');
error_log('Category Taxonomy: ' . print_r($taxonomy, true));
```

---

### 2. Products ↔ Tags (Taxonomy) ✅ READY TO TEST NOW

**Connection Type:** Many-to-Many  
**Database Tables:** `posts`, `term_relationships`, `term_taxonomy`, `terms`, `termmeta`  
**Affected Features:**
- Product creation/edit
- Tag management
- Tag filtering (sidebar)
- Import/Export
- Tag-specific styling

**Test Scenarios:**

| Scenario | Test Steps | Expected Result |
|----------|------------|-----------------|
| Assign single tag | Create product → Select "Best Seller" → Save | Product assigned to "Best Seller" tag |
| Assign multiple tags | Create product → Select "New", "Sale", "Trending" → Save | Product assigned to all 3 tags |
| Edit product tags | Edit product → Change to "Clearance" → Save | Tags updated correctly |
| Tag with meta | Create tag with color="#ff0000" → Assign to product | Product displays with red tag |
| Delete tag with products | Delete "Trending" tag → Products reassign or warn | Products handled gracefully |
| Bulk assign tags | Select 10 products → Assign "Featured" → Save | All 10 products assigned to "Featured" |
| Filter by tags | Sidebar filter → Select "Sale" tag | Shows only Sale-tagged products |
| Tag priority | Set tag priorities → Products sort by tag priority | Products display in correct order |

**Code Verification:**
```php
// Check relationship saves
$tags = wp_get_post_terms($product_id, 'product_tag');
error_log('Product Tags: ' . print_r($tags, true));

// Verify tag meta
foreach ($tags as $tag) {
    $meta = get_term_meta($tag->term_id, 'tag_color', true);
    error_log("Tag {$tag->name} color: {$meta}");
}
```

---

### 3. Products ↔ Ribbons (Custom Post Type + Meta) ✅ READY TO TEST NOW

**Connection Type:** One-to-Many (Product → Multiple Ribbons)  
**Database Tables:** `posts`, `postmeta`, `post_type=ribbon`  
**Affected Features:**
- Product creation/edit
- Ribbon management
- Ribbon display (future frontend)
- Priority system
- Import/Export

**Test Scenarios:**

| Scenario | Test Steps | Expected Result |
|----------|------------|-----------------|
| Assign single ribbon | Create product → Select "Sale" ribbon → Save | Product assigned "Sale" ribbon in admin |
| Assign multiple ribbons | Create product → Select "Sale", "New", "Featured" → Save | Product assigned all 3 ribbons in admin |
| Ribbon with custom color | Create "Hot Deal" (red) → Assign to product | Product admin shows red "Hot Deal" ribbon selection |
| Ribbon with priority | Set ribbons priorities 3,1,2 → Save | Admin displays ribbons in correct order |
| Max ribbons limit | Set max to 2 → Assign 5 ribbons → Save | Admin only allows selecting top 2 ribbons |
| Delete ribbon | Delete "Sale" ribbon → Refresh product | Product admin no longer shows "Sale" ribbon |
| Bulk assign ribbons | Select 5 products → Assign "Clearance" → Save | All 5 products show "Clearance" ribbon in admin |

**Code Verification:**
```php
// Check ribbon assignments
$ribbon_ids = get_post_meta($product_id, '_product_ribbons', false);
error_log('Product Ribbons: ' . print_r($ribbon_ids, true));

// Verify ribbon data
foreach ($ribbon_ids as $ribbon_id) {
    $ribbon = get_post($ribbon_id);
    $color = get_post_meta($ribbon_id, 'ribbon_color', true);
    $priority = get_post_meta($ribbon_id, 'ribbon_priority', true);
    error_log("Ribbon {$ribbon->post_title}: color={$color}, priority={$priority}");
}
```

---

### 4. Settings ↔ Admin UI ✅ READY TO TEST NOW

**Connection Type:** Global Settings → Admin Interface  
**Database Tables:** `options` table  
**Affected Features:**
- Admin product table
- Admin filters
- Admin pagination
- Admin sorting

**Test Scenarios:**

| Setting | Test Steps | Expected Result |
|---------|------------|-----------------|
| Per page setting | Set to 20 → Go to Products page | 20 products per page in admin |
| Sort order setting | Set to "Title ASC" → Go to Products page | Products sorted by title ascending |
| Ribbon setting | Disable ribbons → Go to Products page | Ribbon column hidden or disabled in table |
| Category filter | Enable category filter → Go to Products page | Category filter dropdown visible |
| Tag filter | Enable tag filter → Go to Products page | Tag filter dropdown visible |

**Verification:**
```php
function verify_settings_admin_ui() {
    $settings = get_option('affiliate_product_showcase_settings');
    
    echo "<h3>Settings → Admin UI Verification</h3>";
    
    // Check per-page setting
    $per_page = $settings['products']['products_per_page'] ?? 10;
    $screen = get_current_screen();
    $user_per_page = get_user_meta(get_current_user_id(), 'edit_' . $screen->id . '_per_page', true);
    
    echo "<p>Setting: {$per_page} | User: {$user_per_page} | Match: " . ($per_page == $user_per_page ? 'Yes' : 'No') . "</p>";
    
    // Check if filters are visible
    $has_category_filter = has_action('restrict_manage_posts', 'aff_ps_category_filter');
    $has_tag_filter = has_action('restrict_manage_posts', 'aff_ps_tag_filter');
    
    echo "<p>Category filter: " . ($has_category_filter ? 'Enabled' : 'Disabled') . "</p>";
    echo "<p>Tag filter: " . ($has_tag_filter ? 'Enabled' : 'Disabled') . "</p>";
}
```

---

### 5. Import/Export ↔ All Relationships ✅ READY TO TEST NOW

**Connection Type:** JSON Import/Export → Data Restoration  
**Affected Features:**
- All product data
- Categories
- Tags
- Ribbons
- All relationships

**Test Scenarios:**

| Scenario | Test Steps | Expected Result |
|----------|------------|-----------------|
| Export with all data | Export products | JSON includes products + categories + tags + ribbons |
| Import to fresh site | Import JSON to new WP site | All data restored correctly |
| Import with existing categories | Import with overlapping categories | Categories merged or duplicated appropriately |
| Import with missing ribbons | Import referencing deleted ribbons | Handles gracefully (create or skip) |
| Partial import | Import only products | Products imported, relationships preserved |

**Code Verification:**
```php
// Check export structure
$export_data = [
    'products' => $products,
    'categories' => $categories,
    'tags' => $tags,
    'ribbons' => $ribbons,
];

error_log('Export Structure: ' . print_r($export_data, true));

// Verify import relationships
foreach ($imported_products as $product) {
    $categories = get_post_meta($product->id, '_product_categories', true);
    error_log("Product {$product->title} categories: " . print_r($categories, true));
}
```

---

### 6. Database ↔ All Features (Data Integrity) ✅ READY TO TEST NOW

**Connection Type:** CRUD Operations → Data Consistency  
**Affected Features:**
- All data operations
- Cascade deletes
- Bulk operations
- Data cleanup

**Test Scenarios:**

| Scenario | Test Steps | Expected Result |
|----------|------------|-----------------|
| Save product | Create product with all data | All tables updated correctly |
| Update product | Edit product → Change category → Save | Old relationships removed, new added |
| Delete product | Delete product | All related data cleaned up (no orphans) |
| Bulk delete | Delete 10 products | All related data cleaned up |
| Bulk update | Update 5 products → Assign category | All 5 updated correctly |
| Orphan check | Manual database query | No orphaned records found |

**Code Verification:**
```php
// Check for orphaned records
global $wpdb;

// Check orphaned term_relationships
$orphaned = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->term_relationships} tr
    LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
    WHERE p.ID IS NULL
");

error_log('Orphaned term relationships: ' . $orphaned);

// Check orphaned postmeta
$orphaned_meta = $wpdb->get_var("
    SELECT COUNT(*)
    FROM {$wpdb->postmeta} pm
    LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE p.ID IS NULL
");

error_log('Orphaned postmeta: ' . $orphaned_meta);
```

---

### 7. Settings ↔ Frontend Display 🔜 FUTURE (Requires Frontend)

**Connection Type:** Global Settings → Frontend Output  
**Status:** FRONTEND NOT CREATED YET  
**When to Test:** AFTER frontend development complete  
**Estimated Tests:** 11

**Test Scenarios (Future):**

| Setting | Test Steps | Expected Result |
|---------|------------|-----------------|
| Enable/Disable ribbons | Uncheck → Save → View frontend | All ribbons disappear |
| Ribbon Position | Select position → Save → View frontend | Ribbons move to new position |
| Ribbon Size | Select size → Save → View frontend | Ribbons display in new size |
| Animation Type | Select animation → Save → Refresh | Ribbons animate with new type |
| Grid columns | Set columns → Save → View frontend | Grid layout changes |
| Card style | Select style → Save → View frontend | Card appearance changes |

**Note:** Cannot test until frontend is created.

---

### 8. Shortcodes ↔ All Features 🔜 FUTURE (Requires Frontend)

**Connection Type:** Shortcode Parameters → Display Logic  
**Status:** FRONTEND NOT CREATED YET  
**When to Test:** AFTER frontend development complete  
**Estimated Tests:** 7

**Test Scenarios (Future):**

| Shortcode | Parameters | Expected Result |
|-----------|-------------|-----------------|
| `[affiliate_products]` | None | Displays all products |
| `[affiliate_products category="electronics"]` | category param | Shows filtered products |
| `[affiliate_products limit="5"]` | limit param | Shows 5 products |
| `[affiliate_products show_ribbons="false"]` | show_ribbons param | Hides ribbons |

**Note:** Cannot test until frontend is created.

---

### 9. Widgets ↔ All Features 🔜 FUTURE (Requires Frontend)

**Connection Type:** Widget Settings → Display Logic  
**Status:** FRONTEND NOT CREATED YET  
**When to Test:** AFTER frontend development complete  
**Estimated Tests:** 4

**Test Scenarios (Future):**

| Widget | Configuration | Expected Result |
|---------|----------------|-----------------|
| Product Widget | Title, limit | Shows products in sidebar |
| Category Filter Widget | Show counts | Category list with counts |
| Tag Filter Widget | Top tags | Shows most used tags |

**Note:** Cannot test until frontend is created.

---

## 🧪 Testing Methodology

### Phase 1: Preparation (Before Testing)

**1. Create Test Data**

```bash
# Create test products with various combinations
- Product A: 1 category, 2 tags, 1 ribbon
- Product B: 2 categories, 1 tag, 3 ribbons
- Product C: No categories, 3 tags, 0 ribbons
- Product D: 1 category, 0 tags, 2 ribbons

# Create test taxonomies
- Categories: Electronics, Gadgets, Sale, Featured
- Tags: New, Hot, Best Seller, Clearance, Trending

# Create test ribbons
- Sale (red, priority 1)
- New (blue, priority 2)
- Featured (green, priority 3)
- Clearance (orange, priority 1)
```

**2. Set Up Testing Environment**

```bash
# Enable debug logging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

# Install Query Monitor plugin
```

**3. Document Initial State**

```php
// Capture initial database state
$initial_state = [
    'product_count' => wp_count_posts('product')->publish,
    'category_count' => wp_count_terms('product_category'),
    'tag_count' => wp_count_terms('product_tag'),
    'ribbon_count' => wp_count_posts('ribbon')->publish,
];
error_log('Initial State: ' . print_r($initial_state, true));
```

---

### Phase 2: Execute Backend Tests (NOW)

#### Test 1: Products ↔ Categories

**Step-by-Step:**

**Step 1: Create Product with Single Category**
- Go to Products → Add New
- Enter title "Test Product 1"
- Select "Electronics" category
- Save
- Expected: Product assigned to "Electronics"

**Report Requirements:**
- [ ] Document result (Pass/Fail)
- [ ] Check database for term_relationships entry
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Verification:**
```php
// Check term_relationships table
SELECT * FROM wp_term_relationships 
WHERE object_id = [product_id]

// Should have 1 entry for "Electronics"
```

**Step 2: Create Product with Multiple Categories**
- Go to Products → Add New
- Enter title "Test Product 2"
- Select "Electronics", "Gadgets"
- Save
- Expected: Product assigned to both categories

**Report Requirements:**
- [ ] Document result (Pass/Fail)
- [ ] Check database for 2 term_relationships entries
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Verification Script:**
```php
function verify_product_category_connection($product_id) {
    $categories = wp_get_post_terms($product_id, 'product_category');
    
    echo "<h3>Product-Category Connection Test</h3>";
    echo "<p>Product ID: {$product_id}</p>";
    echo "<p>Categories assigned: " . count($categories) . "</p>";
    
    foreach ($categories as $cat) {
        echo "<p>- {$cat->name} (ID: {$cat->term_id})</p>";
    }
}
```

---

#### Test 2: Products ↔ Tags

**Step-by-Step:**

**Step 1: Create Tag with Meta**
- Go to Tags
- Add "Best Seller" tag
- Set color: #ff0000 (red)
- Set priority: 1
- Save

**Report Requirements:**
- [ ] Document tag creation result (Pass/Fail)
- [ ] Verify tag meta saved correctly
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Step 2: Assign Tag to Product**
- Edit Test Product 1
- Select "Best Seller" tag
- Save
- Expected: Product assigned to "Best Seller"

**Report Requirements:**
- [ ] Document assignment result (Pass/Fail)
- [ ] Check database for tag assignment
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Verification Script:**
```php
function verify_product_tag_connection($product_id) {
    $tags = wp_get_post_terms($product_id, 'product_tag');
    
    echo "<h3>Product-Tag Connection Test</h3>";
    echo "<p>Product ID: {$product_id}</p>";
    echo "<p>Tags assigned: " . count($tags) . "</p>";
    
    foreach ($tags as $tag) {
        $color = get_term_meta($tag->term_id, 'tag_color', true);
        $priority = get_term_meta($tag->term_id, 'tag_priority', true);
        
        echo "<p>";
        echo "<span style='background: {$color}; padding: 2px 5px;'>{$tag->name}</span>";
        echo " (Priority: {$priority})";
        echo "</p>";
    }
}
```

---

#### Test 3: Products ↔ Ribbons

**Step-by-Step:**

**Step 1: Create Ribbon with Custom Settings**
- Go to Ribbons
- Add "Hot Deal" ribbon
- Set color: #ff4444
- Set priority: 1
- Save

**Report Requirements:**
- [ ] Document ribbon creation result (Pass/Fail)
- [ ] Verify ribbon meta saved correctly
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Step 2: Assign Ribbon to Product**
- Edit Test Product 1
- Select "Hot Deal" ribbon
- Save
- Expected: Product assigned "Hot Deal" ribbon

**Report Requirements:**
- [ ] Document assignment result (Pass/Fail)
- [ ] Check database for ribbon assignment
- [ ] Record any issues found
- [ ] Save mini-report before next step

**Verification Script:**
```php
function verify_product_ribbon_connection($product_id) {
    $ribbon_ids = get_post_meta($product_id, '_product_ribbons', false);
    $settings = get_option('affiliate_product_showcase_settings');
    $max_ribbons = $settings['ribbons']['max_ribbons_per_product'] ?? 3;
    
    echo "<h3>Product-Ribbon Connection Test</h3>";
    echo "<p>Product ID: {$product_id}</p>";
    echo "<p>Ribbons assigned: " . count($ribbon_ids) . "</p>";
    echo "<p>Max ribbons setting: {$max_ribbons}</p>";
    
    $ribbons = [];
    foreach ($ribbon_ids as $ribbon_id) {
        $ribbon = get_post($ribbon_id);
        $color = get_post_meta($ribbon_id, 'ribbon_color', true);
        $priority = get_post_meta($ribbon_id, 'ribbon_priority', true);
        
        $ribbons[] = [
            'id' => $ribbon_id,
            'title' => $ribbon->post_title,
            'color' => $color,
            'priority' => $priority,
        ];
        
        echo "<p>";
        echo "<span style='background: {$color}; color: white; padding: 2px 5px;'>{$ribbon->post_title}</span>";
        echo " (Priority: {$priority})";
        echo "</p>";
    }
    
    // Sort by priority
    usort($ribbons, function($a, $b) {
        return $b['priority'] <=> $a['priority']; // Descending
    });
    
    // Apply max limit
    $displayed_ribbons = array_slice($ribbons, 0, $max_ribbons);
    
    echo "<h4>Ribbons to display (after priority & limit):</h4>";
    foreach ($displayed_ribbons as $ribbon) {
        echo "<p>- {$ribbon['title']} (Priority: {$ribbon['priority']})</p>";
    }
}
```

---

#### Test 4: Orphaned Records Check

**Purpose:** Ensure no orphaned data exists

**Test Script:**
```php
function check_orphaned_records() {
    global $wpdb;
    
    echo "<h3>Orphaned Records Check</h3>";
    
    // Check orphaned term relationships
    $orphaned_term_rels = $wpdb->get_var("
        SELECT COUNT(*)
        FROM {$wpdb->term_relationships} tr
        LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        WHERE p.ID IS NULL
    ");
    
    echo "<p>Orphaned term_relationships: " . ($orphaned_term_rels ?: 'None') . "</p>";
    
    if ($orphaned_term_rels > 0) {
        echo "<p style='color: red;'>WARNING: {$orphaned_term_rels} orphaned term relationships found!</p>";
    }
    
    // Check orphaned postmeta
    $orphaned_postmeta = $wpdb->get_var("
        SELECT COUNT(*)
        FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE p.ID IS NULL
    ");
    
    echo "<p>Orphaned postmeta: " . ($orphaned_postmeta ?: 'None') . "</p>";
    
    if ($orphaned_postmeta > 0) {
        echo "<p style='color: red;'>WARNING: {$orphaned_postmeta} orphaned postmeta found!</p>";
    }
    
    // Check orphaned termmeta
    $orphaned_termmeta = $wpdb->get_var("
        SELECT COUNT(*)
        FROM {$wpdb->termmeta} tm
        LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
        WHERE t.term_id IS NULL
    ");
    
    echo "<p>Orphaned termmeta: " . ($orphaned_termmeta ?: 'None') . "</p>";
    
    if ($orphaned_termmeta > 0) {
        echo "<p style='color: red;'>WARNING: {$orphaned_termmeta} orphaned termmeta found!</p>";
    }
    
    return [
        'term_relationships' => $orphaned_term_rels,
        'postmeta' => $orphaned_postmeta,
        'termmeta' => $orphaned_termmeta,
    ];
}
```

---

## 📊 Testing Checklist

### Backend Testing Master Checklist (Can Test NOW)

#### Products ↔ Categories ✅
- [ ] Assign single category to product
- [ ] Assign multiple categories to product
- [ ] Edit product categories
- [ ] Remove all categories
- [ ] Delete category with products
- [ ] Bulk assign categories
- [ ] Filter products by category
- [ ] Category meta sync works

#### Products ↔ Tags ✅
- [ ] Assign single tag to product
- [ ] Assign multiple tags to product
- [ ] Edit product tags
- [ ] Tag with meta (color, priority) works
- [ ] Delete tag with products
- [ ] Bulk assign tags
- [ ] Filter products by tags
- [ ] Tag priority sorting works

#### Products ↔ Ribbons ✅
- [ ] Assign single ribbon to product
- [ ] Assign multiple ribbons to product
- [ ] Ribbon with custom color works
- [ ] Ribbon with priority works
- [ ] Max ribbons limit enforced
- [ ] Delete ribbon works
- [ ] Bulk assign ribbons

#### Settings ↔ Admin UI ✅
- [ ] Per page setting affects admin page
- [ ] Sort order setting affects admin page
- [ ] Ribbon setting affects admin display
- [ ] Category filter visibility setting
- [ ] Tag filter visibility setting

#### Import/Export ↔ Relationships ✅
- [ ] Export includes all data
- [ ] Import restores correctly
- [ ] Import handles existing categories
- [ ] Import handles missing ribbons
- [ ] Partial import works

#### Database ↔ Data Integrity ✅
- [ ] No orphaned term_relationships
- [ ] No orphaned postmeta
- [ ] No orphaned termmeta
- [ ] Product delete cleans up
- [ ] Category delete cleans up
- [ ] Tag delete cleans up
- [ ] Ribbon delete cleans up
- [ ] Bulk operations work

---

### Frontend Testing Master Checklist (FUTURE - Requires Frontend Development)

#### Settings ↔ Frontend Display 🔜
- [ ] Enable/disable ribbons works
- [ ] Ribbon position changes
- [ ] Ribbon size changes
- [ ] Animation type changes
- [ ] Enable/disable animations
- [ ] Priority system toggle
- [ ] Max ribbons setting
- [ ] Grid column setting
- [ ] Card style setting
- [ ] Per page setting
- [ ] Sort order setting

#### Shortcodes ↔ Features 🔜
- [ ] Basic shortcode works
- [ ] Category filter parameter
- [ ] Tag filter parameter
- [ ] Limit parameter
- [ ] Order parameter
- [ ] Show ribbons parameter
- [ ] Columns parameter

#### Widgets ↔ Features 🔜
- [ ] Product widget displays
- [ ] Category filter widget works
- [ ] Tag filter widget works
- [ ] Recent products widget works

---

## 🔧 Testing Tools

### 1. WordPress Debug Log

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// View logs
tail -f wp-content/debug.log
```

### 2. Query Monitor Plugin

**Install:** https://wordpress.org/plugins/query-monitor/

**Features:**
- View all database queries
- Check for N+1 queries
- Monitor query performance
- View hooks and filters

**Usage:**
1. Install and activate Query Monitor
2. Open any admin page
3. Click Query Monitor toolbar
4. Check "Queries" tab for cross-connection queries
5. Look for JOIN queries, WHERE clauses with IDs

### 3. Custom Testing Page

Create a page at `/wp-admin/admin.php?page=aff-ps-test-connections`:

```php
<?php
// Add to Admin.php
add_action('admin_menu', function() {
    add_submenu_page(
        'affiliate-manager',
        'Test Connections',
        'Test Connections',
        'manage_options',
        'aff-ps-test-connections',
        'aff_ps_render_test_page'
    );
});

function aff_ps_render_test_page() {
    ?>
    <div class="wrap">
        <h1>Cross-Connection Testing Dashboard</h1>
        
        <?php
        // Test 1: Product-Category Connection
        verify_product_category_connection(1);
        
        // Test 2: Product-Tag Connection
        verify_product_tag_connection(1);
        
        // Test 3: Product-Ribbon Connection
        verify_product_ribbon_connection(1);
        
        // Test 4: Orphaned Records
        check_orphaned_records();
        ?>
    </div>
    <?php
}
```

### 4. Automated Test Script

```php
<?php
/**
 * Run all backend cross-connection tests
 * Usage: wp eval cross-connection-test.php
 */

require_once __DIR__ . '/wp-load.php';

echo "=== Backend Cross-Connection Tests ===\n\n";

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Product-Category Connection
echo "Test 1: Product-Category Connection\n";
try {
    verify_product_category_connection(1);
    $tests_passed++;
    echo "✓ PASSED\n\n";
} catch (Exception $e) {
    $tests_failed++;
    echo "✗ FAILED: {$e->getMessage()}\n\n";
}

// Test 2: Product-Tag Connection
echo "Test 2: Product-Tag Connection\n";
try {
    verify_product_tag_connection(1);
    $tests_passed++;
    echo "✓ PASSED\n\n";
} catch (Exception $e) {
    $tests_failed++;
    echo "✗ FAILED: {$e->getMessage()}\n\n";
}

// Test 3: Product-Ribbon Connection
echo "Test 3: Product-Ribbon Connection\n";
try {
    verify_product_ribbon_connection(1);
    $tests_passed++;
    echo "✓ PASSED\n\n";
} catch (Exception $e) {
    $tests_failed++;
    echo "✗ FAILED: {$e->getMessage()}\n\n";
}

// Test 4: Orphaned Records
echo "Test 4: Orphaned Records\n";
try {
    $orphans = check_orphaned_records();
    $total_orphans = array_sum($orphans);
    
    if ($total_orphans === 0) {
        $tests_passed++;
        echo "✓ PASSED: No orphaned records\n\n";
    } else {
        $tests_failed++;
        echo "✗ FAILED: {$total_orphans} orphaned records found\n\n";
    }
} catch (Exception $e) {
    $tests_failed++;
    echo "✗ FAILED: {$e->getMessage()}\n\n";
}

// Summary
echo "=== Test Summary ===\n";
echo "Passed: {$tests_passed}\n";
echo "Failed: {$tests_failed}\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";

exit($tests_failed > 0 ? 1 : 0);
```

---

## 📝 Test Report Template

### Backend Cross-Connection Test Report

**Date:** YYYY-MM-DD  
**Tester:** [Name]  
**Environment:** Development/Staging/Production  
**Plugin Version:** X.X.X  
**Frontend Status:** Not Created (Backend Testing Only)

---

### Executive Summary

| Test Suite | Status | Total Tests | Passed | Failed | Pass Rate |
|-------------|--------|-------------|---------|---------|-----------|
| Products ↔ Categories | ✅ NOW | 8 | - | - | - |
| Products ↔ Tags | ✅ NOW | 8 | - | - | - |
| Products ↔ Ribbons | ✅ NOW | 9 | - | - | - |
| Settings ↔ Admin UI | ✅ NOW | 5 | - | - | - |
| Import/Export ↔ Relationships | ✅ NOW | 5 | - | - | - |
| Database ↔ Data Integrity | ✅ NOW | 10 | - | - | - |
| **BACKEND TOTAL** | ✅ NOW | **45** | **0** | **0** | **-** |
| Settings ↔ Frontend Display | 🔜 FUTURE | 11 | - | - | - |
| Shortcodes ↔ Features | 🔜 FUTURE | 7 | - | - | - |
| Widgets ↔ Features | 🔜 FUTURE | 4 | - | - | - |
| **FRONTEND TOTAL** | 🔜 FUTURE | **22** | **0** | **0** | **-** |
| **OVERALL TOTAL** | - | **67** | **0** | **0** | **-** |

---

### Detailed Results

#### Test Suite: [Suite Name]

| Test | Status | Notes |
|------|--------|-------|
| Test name | ✅/❌ | Notes |

**Issue Details (if any):**
- **Test:** [Test name]
- **Expected:** [Expected behavior]
- **Actual:** [Actual behavior]
- **Root Cause:** [Root cause]
- **Fix Required:** [What needs to be fixed]

---

### Issues Found

| ID | Severity | Issue | Location | Fix Status |
|----|----------|--------|----------|------------|
| 1 | [Severity] | [Issue description] | [Location] | [Status] |

---

### Recommendations

1. **High Priority:**
   - [High priority fix]
   - [High priority fix]

2. **Medium Priority:**
   - [Medium priority item]

3. **Low Priority:**
   - [Low priority item]

---

### Next Steps

1. [Next step 1]
2. [Next step 2]
3. [Next step 3]

---

## 🎯 Testing Order

### Backend Testing Phase (CURRENT - Can Do Now)

**Phase 1: Data Relationships**
- Products ↔ Categories (8 tests)
- Products ↔ Tags (8 tests)
- Products ↔ Ribbons (9 tests)

**Phase 2: Integration & Integrity**
- Settings ↔ Admin UI (5 tests)
- Import/Export (5 tests)
- Database ↔ Data Integrity (10 tests)

**Total Backend Tests:** 45 tests  
**Target Pass Rate:** 100%

**⚠️ CRITICAL: Report after EACH test step** - Document results, check database, record issues before proceeding

---

### Frontend Testing Phase (FUTURE - After Frontend Development)

**Phase 1: Settings Display**
- Settings ↔ Frontend Display (11 tests)

**Phase 2: Shortcodes & Widgets**
- Shortcodes ↔ Features (7 tests)
- Widgets ↔ Features (4 tests)

**Total Frontend Tests:** 22 tests  
**Target Pass Rate:** 100%

**⚠️ CRITICAL: Report after EACH test step** - Document results, verify frontend display, record issues before proceeding

---

### Overall Testing Workflow

**Phase 1: Backend Testing** - START NOW
- Test all 45 backend connections
- Verify data integrity after each step
- Fix any issues found immediately
- Document all results

**Phase 2: Frontend Development** (Future)
- Create frontend components
- Implement display logic
- Add shortcodes and widgets

**Phase 3: Frontend Testing** (Future)
- Test all 22 frontend connections
- Verify settings affect display after each step
- Test shortcodes and widgets
- Document all results

**Total Tests:** 67 tests (45 backend + 22 frontend)

---

## ✅ Conclusion

**Testing cross-connections is CRITICAL for plugin quality.**

This testing plan provides:
- ✅ Complete backend cross-connection map (45 tests ready NOW)
- ✅ Future frontend test plan (22 tests for later)
- ✅ Step-by-step test procedures
- ✅ Verification scripts
- ✅ Testing tools
- ✅ Report template
- ✅ Clear timeline

**Follow this plan to ensure:**
- All backend connections work correctly
- Settings affect admin interface properly
- No data corruption or orphaned records
- Plugin is production-ready for backend

**Ready to proceed with backend testing!**

---

**Version:** 2.0.0  
**Last Updated:** 2026-01-26  
**Status:** Ready for Backend Testing  
**Frontend Status:** Pending Development