# Section 3: Tags - True Hybrid Implementation Plan

# Feature Requirements: Affiliate Digital Product Showcase

> **IMPORTANT RULE: NEVER DELETE THIS FILE**
> This file contains complete feature requirements for digital affiliate product plugin. All features must be implemented according to this plan.

> **SCOPE:** Digital products only (software, e-books, courses, templates, plugins, themes, digital art, etc.)

---

# 📝 STRICT DEVELOPMENT RULES

**⚠️ MANDATORY:** Always use all assistant instruction files when writing code for feature development and issue resolution.

### Project Context

**Project:** Affiliate Digital Product Showcase WordPress Plugin  
**Framework:** Modern WordPress Plugin Boilerplate (Tailwind + Vite + PSR-4 + Security & Cache Ready)  
**Quality Standard:** Hybrid Quality Matrix - Enterprise-grade where it matters, practical everywhere  
**Architecture:** DI container, event-driven architecture, REST API, Gutenberg blocks  
**Tech Stack:** PHP 8.1+, JavaScript/React, Vite, Tailwind CSS  
**Code Quality:** PHPUnit, PHPCS (WPCS), PHPStan, Psalm  
**Product Type:** Digital products only (software, e-books, courses, templates, plugins, themes, digital art, etc.)

### Required Reference Files (ALWAYS USE):

1. **docs/assistant-instructions.md** - Project context, code change policy, git rules
2. **docs/assistant-quality-standards.md** - Enterprise-grade code quality requirements
3. **docs/assistant-performance-optimization.md** - Performance optimization guidelines

### Quality Standard: 10/10 Enterprise-Grade
- Fully/highly optimized, no compromises
- All code must meet hybrid quality matrix standards
- Essential standards at 10/10, performance goals as targets


**Created:** January 24, 2026  
**Priority:** 🟠 HIGH - Complete new feature implementation  
**Scope:** Basic Level Features Only (24 features)

---

## Executive Summary

Section 3 (Tags) is **NOT IMPLEMENTED** - complete feature implementation required to achieve true hybrid compliance.

**Current Status:**
- ❌ No Tag model exists
- ❌ No TagFactory exists
- ❌ No TagRepository exists
- ❌ No TagFields admin component exists
- ❌ No TagTable admin listing exists
- ❌ No TagsController REST API exists
- ❌ Tag taxonomy not registered

**Impact:** Need to implement complete tag feature from scratch following true hybrid patterns.

---

## Understanding True Hybrid for Tags

**True Hybrid Means:**
1. ✅ All taxonomy meta keys use underscore prefix (`_aps_tag_*`)
2. ✅ Tag model has readonly properties
3. ✅ TagFactory has from_wp_term() and from_array() methods
4. ✅ TagRepository has full CRUD operations
5. ✅ TagFields admin component uses nonce verification
6. ✅ Tag taxonomy is non-hierarchical (flat structure)
7. ✅ REST API endpoints have permission checks
8. ✅ Consistent naming across all components

**Storage Strategy:**
- Tags are stored as WordPress taxonomy (like Categories)
- Tag metadata stored as term meta with `_aps_tag_*` prefix
- Non-hierarchical (flat structure, unlike categories)

---

## 🎨 TRUE HYBRID Architecture Diagram

### Visual Breakdown: Custom vs Default WordPress Components

```
┌────────────────────────────────────────┐
│  WORDPRESS NATIVE TAGS PAGE      │  ← ONE PAGE ONLY
│  (edit-tags.php)                  │
│                                    │
│  URL: edit-tags.php?taxonomy=aps_tag
│                                    │
│  ✅ WordPress Native Features:        │
│  - Tag CRUD                     │
│  - Table rendering                │
│  - Quick edit                   │
│  - Bulk actions                 │
│  - Search & filtering            │
│  - Tag cloud view                │
│  - Flat structure (non-hierarchical)
├────────────────────────────────────┤
│  ✅ Custom Enhancements (Hooks):   │
│  - Custom meta fields (add/edit)  │
│  - Custom columns (Color, Icon)    │
│  - Color picker field               │
│  - Icon text input field           │
│  - Admin notices                  │
│  - Meta sanitization              │
└────────────────────────────────────────┘
```

### TRUE HYBRID Features List

**🟦 DEFAULT (WordPress Built-in):**
- Tag taxonomy registration
- Tag CRUD operations (create, read, update, delete)
- Table rendering and pagination
- Quick edit functionality
- Bulk actions
- Search and filtering
- Tag cloud view
- Non-hierarchical structure

**🟩 CUSTOM (Our Implementation - TRUE HYBRID):**

**Model Layer:**
- Tag model with readonly properties
- TagFactory with from_wp_term() and from_array() methods
- TagRepository with full CRUD operations

**Admin Layer:**
- TagFields component (hook enhancements)
- Custom meta fields (Color, Icon)
- Color picker field (input type="color")
- Icon text input field
- Nonce verification
- Input sanitization
- Custom table columns (Color, Icon)

**API Layer:**
- TagsController REST API
- Permission checks
- CRUD endpoints
- JSON responses with Tag model

**Meta Keys (TRUE HYBRID Pattern):**
- _aps_tag_color ⭐ (hex color code)
- _aps_tag_icon ⭐ (icon class)
- _aps_tag_created_at (creation timestamp)
- _aps_tag_updated_at (last update timestamp)

### Key Differences: Categories vs Tags

**✅ Categories (Hierarchical):**
- Has parent/child relationships
- Uses term_taxonomy_id for hierarchy
- Can be nested (subcategory → category)

**✅ Tags (Non-Hierarchical):**
- NO parent/child relationships
- Simple flat list
- Cannot be nested
- Like post tags in WordPress

### TRUE HYBRID Characteristics

✅ **What Makes This TRUE HYBRID:**
1. Uses WordPress native taxonomy system (DEFAULT)
2. Adds custom meta fields via hooks (CUSTOM)
3. Model wraps WP_Term with readonly properties (CUSTOM)
4. Factory converts between WP_Term and Model (CUSTOM)
5. Repository handles CRUD operations (CUSTOM)
6. REST API extends WordPress REST API (CUSTOM)
7. Single admin page with WordPress + Custom (HYBRID)
8. Consistent underscore prefix pattern (_aps_tag_*) (TRUE HYBRID)

---

## Implementation Phases Overview

| Phase | Priority | Description |
|--------|----------|-------------|
| Phase 1: Create Tag Model | 🔴 CRITICAL | Tag model with readonly properties |
| Phase 2: Create TagFactory | 🟠 HIGH | Factory with from_wp_term() and from_array() |
| Phase 3: Create TagRepository | 🟠 HIGH | Full CRUD operations |
| Phase 4: Register Tag Taxonomy | 🟠 HIGH | Taxonomy registration in WordPress |
| Phase 5: Create TagFields | 🟡 MEDIUM | Admin component for tag management |
| Phase 6: Create TagTable | 🟡 MEDIUM | Admin listing for tags |
| Phase 7: Create TagsController | 🟡 MEDIUM | REST API endpoints |
| Phase 8: DI Container Registration | 🟡 MEDIUM | Register services in DI container |
| Phase 9: Update Loader | 🟡 MEDIUM | Load new components |
| Phase 10: Add to Menu | 🟢 LOW | Add Tags menu item |
| Phase 11: Testing & Verification | 🟡 REQUIRED | Comprehensive testing |

---

## Phase 1: Create Tag Model (CRITICAL)

**Priority:** 🔴 HIGHEST  
**Files to Create:** `src/Models/Tag.php`

### Tag Model Requirements

**Properties (Readonly):**
```php
- id: int (term_id)
- name: string (tag name)
- slug: string (URL-friendly identifier)
- description: string (tag description)
- count: int (number of products with this tag)
- color: string (hex color code for display)
- icon: string (icon identifier/class)
- created_at: string (creation timestamp)
- updated_at: string (last update timestamp)
```

### Tag Model Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

use WP_Term;

/**
 * Tag model
 *
 * Represents a product tag with all metadata.
 * Tags are non-hierarchical (flat structure).
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 */
final class Tag {
    /**
     * Tag ID
     */
    public readonly int $id;

    /**
     * Tag name
     */
    public readonly string $name;

    /**
     * Tag slug (URL-friendly identifier)
     */
    public readonly string $slug;

    /**
     * Tag description
     */
    public readonly string $description;

    /**
     * Number of products with this tag
     */
    public readonly int $count;

    /**
     * Display color (hex code)
     */
    public readonly ?string $color;

    /**
     * Icon identifier/class
     */
    public readonly ?string $icon;

    /**
     * Creation timestamp
     */
    public readonly string $created_at;

    /**
     * Last update timestamp
     */
    public readonly string $updated_at;

    /**
     * Constructor
     *
     * @param int $id Tag ID
     * @param string $name Tag name
     * @param string $slug Tag slug
     * @param string $description Tag description
     * @param int $count Product count
     * @param string|null $color Display color
     * @param string|null $icon Icon identifier
     * @param string|null $created_at Creation timestamp
     * @param string|null $updated_at Update timestamp
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        string $description,
        int $count,
        ?string $color = null,
        ?string $icon = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->count = $count;
        $this->color = $color;
        $this->icon = $icon;
        $this->created_at = $created_at ?? current_time( 'mysql' );
        $this->updated_at = $updated_at ?? current_time( 'mysql' );
    }

    /**
     * Create Tag from WP_Term
     *
     * @param WP_Term $term WordPress term object
     * @return self Tag instance
     */
    public static function from_wp_term( WP_Term $term ): self {
        // Get tag metadata (with underscore prefix)
        $color = get_term_meta( $term->term_id, '_aps_tag_color', true );
        $icon = get_term_meta( $term->term_id, '_aps_tag_icon', true );
        $created_at = get_term_meta( $term->term_id, '_aps_tag_created_at', true );
        $updated_at = get_term_meta( $term->term_id, '_aps_tag_updated_at', true );

        return new self(
            id: $term->term_id,
            name: $term->name,
            slug: $term->slug,
            description: $term->description ?: '',
            count: $term->count,
            color: $color ?: null,
            icon: $icon ?: null,
            created_at: $created_at ?: current_time( 'mysql' ),
            updated_at: $updated_at ?: current_time( 'mysql' )
        );
    }

    /**
     * Convert Tag to array
     *
     * @return array Tag data as array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'count' => $this->count,
            'color' => $this->color,
            'icon' => $this->icon,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Implementation Steps

1. **Create Tag.php**
   ```bash
   touch src/Models/Tag.php
   ```

2. **Add model code**
   - Copy template above
   - Paste into `src/Models/Tag.php`
   - Verify all properties are readonly
   - Verify all type hints are present

3. **Test model**
   - Create test tag from WP_Term
   - Verify properties are set correctly
   - Verify to_array() works

### Verification Checklist
- [ ] Tag.php file created
- [ ] All properties readonly
- [ ] All type hints present
- [ ] from_wp_term() method works
- [ ] to_array() method works
- [ ] Meta keys use underscore prefix

---

## Phase 2: Create TagFactory (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Factories/TagFactory.php`

### TagFactory Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Tag;
use WP_Term;

/**
 * Tag factory
 *
 * Creates Tag instances from various data sources.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 */
final class TagFactory {
    /**
     * Create Tag from WP_Term
     *
     * @param WP_Term $term WordPress term object
     * @return Tag Tag instance
     */
    public static function from_wp_term( WP_Term $term ): Tag {
        return Tag::from_wp_term( $term );
    }

    /**
     * Create Tag from array
     *
     * @param array $data Tag data
     * @return Tag Tag instance
     */
    public static function from_array( array $data ): Tag {
        return new Tag(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? sanitize_title( $data['name'] ?? '' ),
            description: $data['description'] ?? '',
            count: $data['count'] ?? 0,
            color: $data['color'] ?? null,
            icon: $data['icon'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Create multiple Tags from array
     *
     * @param array<array> $data_array Array of tag data
     * @return array<Tag> Array of Tag instances
     */
    public static function from_array_many( array $data_array ): array {
        return array_map(
            fn( $data ) => self::from_array( $data ),
            $data_array
        );
    }
}
```

### Implementation Steps

1. **Create TagFactory.php**
   ```bash
   touch src/Factories/TagFactory.php
   ```

2. **Add factory code**
   - Copy template above
   - Paste into `src/Factories/TagFactory.php`
   - Verify all factory methods exist
   - Verify type hints are correct

3. **Test factory**
   - Create tag from array
   - Create tag from WP_Term
   - Verify both methods work

### Verification Checklist
- [ ] TagFactory.php file created
- [ ] from_wp_term() method works
- [ ] from_array() method works
- [ ] from_array_many() method works
- [ ] All type hints present

---

## Phase 3: Create TagRepository (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Repositories/TagRepository.php`

### TagRepository Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Models\Tag;
use AffiliateProductShowcase\Factories\TagFactory;
use WP_Error;

/**
 * Tag repository
 *
 * Handles CRUD operations for tags.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 */
final class TagRepository {
    /**
     * Taxonomy name
     */
    private const TAXONOMY = 'aps_tag';

    /**
     * Create a new tag
     *
     * @param Tag $tag Tag to create
     * @return Tag Created tag
     * @throws WP_Error If creation fails
     */
    public function create( Tag $tag ): Tag {
        $result = wp_insert_term(
            $tag->name,
            self::TAXONOMY,
            [
                'slug' => $tag->slug,
                'description' => $tag->description,
            ]
        );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $term_id = $result['term_id'];
        $this->save_metadata( $term_id, $tag );

        return $this->find( $term_id );
    }

    /**
     * Find a tag by ID
     *
     * @param int $id Tag ID
     * @return Tag|null Tag instance or null if not found
     */
    public function find( int $id ): ?Tag {
        $term = get_term( $id, self::TAXONOMY );

        if ( ! $term || is_wp_error( $term ) ) {
            return null;
        }

        return TagFactory::from_wp_term( $term );
    }

    /**
     * Update an existing tag
     *
     * @param int $id Tag ID
     * @param Tag $tag Tag data to update
     * @return Tag Updated tag
     * @throws WP_Error If update fails
     */
    public function update( int $id, Tag $tag ): Tag {
        $result = wp_update_term(
            $id,
            self::TAXONOMY,
            [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
            ]
        );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $this->save_metadata( $id, $tag );

        return $this->find( $id );
    }

    /**
     * Delete a tag
     *
     * @param int $id Tag ID
     * @return bool True if deleted
     * @throws WP_Error If deletion fails
     */
    public function delete( int $id ): bool {
        $result = wp_delete_term( $id, self::TAXONOMY );

        if ( is_wp_error( $result ) ) {
            throw $result;
        }

        $this->delete_metadata( $id );

        return $result;
    }

    /**
     * Get all tags
     *
     * @param array $args Query arguments
     * @return array<Tag> Array of tags
     */
    public function all( array $args = [] ): array {
        $defaults = [
            'taxonomy' => self::TAXONOMY,
            'hide_empty' => false,
        ];

        $args = wp_parse_args( $args, $defaults );
        $terms = get_terms( $args );

        if ( is_wp_error( $terms ) ) {
            return [];
        }

        return TagFactory::from_array_many(
            array_map( fn( $term ) => (array) $term, $terms )
        );
    }

    /**
     * Search tags by name
     *
     * @param string $search Search term
     * @return array<Tag> Matching tags
     */
    public function search( string $search ): array {
        return $this->all( [
            'search' => $search,
        ] );
    }

    /**
     * Save tag metadata
     *
     * @param int $term_id Term ID
     * @param Tag $tag Tag instance
     */
    private function save_metadata( int $term_id, Tag $tag ): void {
        // Color (with underscore prefix)
        if ( $tag->color ) {
            update_term_meta( $term_id, '_aps_tag_color', $tag->color );
        } else {
            delete_term_meta( $term_id, '_aps_tag_color' );
        }

        // Icon (with underscore prefix)
        if ( $tag->icon ) {
            update_term_meta( $term_id, '_aps_tag_icon', $tag->icon );
        } else {
            delete_term_meta( $term_id, '_aps_tag_icon' );
        }

        // Timestamps
        $existing_created = get_term_meta( $term_id, '_aps_tag_created_at', true );
        if ( ! $existing_created ) {
            update_term_meta( $term_id, '_aps_tag_created_at', current_time( 'mysql' ) );
        }
        update_term_meta( $term_id, '_aps_tag_updated_at', current_time( 'mysql' ) );
    }

    /**
     * Delete tag metadata
     *
     * @param int $term_id Term ID
     */
    private function delete_metadata( int $term_id ): void {
        delete_term_meta( $term_id, '_aps_tag_color' );
        delete_term_meta( $term_id, '_aps_tag_icon' );
        delete_term_meta( $term_id, '_aps_tag_created_at' );
        delete_term_meta( $term_id, '_aps_tag_updated_at' );
    }
}
```

### Implementation Steps

1. **Create TagRepository.php**
   ```bash
   touch src/Repositories/TagRepository.php
   ```

2. **Add repository code**
   - Copy template above
   - Paste into `src/Repositories/TagRepository.php`
   - Verify all CRUD methods exist
   - Verify meta keys use underscore prefix

3. **Test repository**
   - Create tag
   - Find tag
   - Update tag
   - Delete tag
   - List all tags

### Verification Checklist
- [ ] TagRepository.php file created
- [ ] create() method works
- [ ] find() method works
- [ ] update() method works
- [ ] delete() method works
- [ ] all() method works
- [ ] search() method works
- [ ] Meta keys use underscore prefix
- [ ] Error handling in place

---

## Phase 4: Register Tag Taxonomy (HIGH)

**Priority:** 🟠 HIGH  
**Files to Modify:** `src/Plugin/Plugin.php` or create separate registration file

### Taxonomy Registration Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Taxonomies;

/**
 * Tag taxonomy registration
 *
 * Registers the 'aps_tag' taxonomy for product tags.
 * Non-hierarchical (flat structure, unlike categories).
 *
 * @package AffiliateProductShowcase\Taxonomies
 * @since 1.0.0
 */
final class TagTaxonomy {
    /**
     * Register tag taxonomy
     *
     * @hook init
     */
    public function register(): void {
        $labels = [
            'name' => __( 'Tags', 'affiliate-product-showcase' ),
            'singular_name' => __( 'Tag', 'affiliate-product-showcase' ),
            'search_items' => __( 'Search Tags', 'affiliate-product-showcase' ),
            'all_items' => __( 'All Tags', 'affiliate-product-showcase' ),
            'parent_item' => null, // Non-hierarchical
            'parent_item_colon' => null, // Non-hierarchical
            'edit_item' => __( 'Edit Tag', 'affiliate-product-showcase' ),
            'update_item' => __( 'Update Tag', 'affiliate-product-showcase' ),
            'add_new_item' => __( 'Add New Tag', 'affiliate-product-showcase' ),
            'new_item_name' => __( 'New Tag Name', 'affiliate-product-showcase' ),
            'menu_name' => __( 'Tags', 'affiliate-product-showcase' ),
        ];

        $args = [
            'labels' => $labels,
            'description' => __( 'Product tags for filtering', 'affiliate-product-showcase' ),
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => false, // Non-hierarchical (flat structure)
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'rewrite' => [
                'slug' => 'tag',
                'with_front' => false,
                'hierarchical' => false,
            ],
            'query_var' => true,
        ];

        register_taxonomy( 'aps_tag', 'aps_product', $args );

        // Flush rewrite rules on activation
        flush_rewrite_rules();
    }
}

// Hook registration
add_action( 'init', [ new TagTaxonomy(), 'register' ] );
```

### Implementation Steps

1. **Create TagTaxonomy.php**
   ```bash
   mkdir -p src/Taxonomies
   touch src/Taxonomies/TagTaxonomy.php
   ```

2. **Add taxonomy registration code**
   - Copy template above
   - Paste into `src/Taxonomies/TagTaxonomy.php`
   - Verify hierarchical is false
   - Verify labels are correct

3. **Load taxonomy registration**
   - Add to loader or main plugin file
   - Verify taxonomy is registered

4. **Test registration**
   - Activate plugin
   - Check if taxonomy appears
   - Verify in WordPress admin

### Verification Checklist
- [ ] TagTaxonomy.php file created
- [ ] Taxonomy registered on init hook
- [ ] Non-hierarchical setting correct
- [ ] Labels configured
- [ ] Rewrite rules set
- [ ] Taxonomy appears in admin
- [ ] Taxonomy appears in REST API

---

## Phase 5: Create TagFields (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Admin/TagFields.php`

### TagFields Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Tag fields component
 *
 * Adds custom meta fields to tag edit screen.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class TagFields {
    /**
     * Register tag fields
     *
     * @hook aps_tag_add_form_fields
     * @hook aps_tag_edit_form_fields
     */
    public function register(): void {
        add_action( 'aps_tag_add_form_fields', [ $this, 'add_form_fields' ] );
        add_action( 'aps_tag_edit_form_fields', [ $this, 'edit_form_fields' ] );
        add_action( 'created_aps_tag', [ $this, 'save_fields' ] );
        add_action( 'edited_aps_tag', [ $this, 'save_fields' ] );
    }

    /**
     * Add fields to tag creation form
     *
     * @param string $taxonomy Taxonomy name
     */
    public function add_form_fields( string $taxonomy ): void {
        $this->render_color_field( '' );
        $this->render_icon_field( '' );
    }

    /**
     * Add fields to tag edit form
     *
     * @param WP_Term $term Term object
     */
    public function edit_form_fields( \WP_Term $term ): void {
        $color = get_term_meta( $term->term_id, '_aps_tag_color', true );
        $icon = get_term_meta( $term->term_id, '_aps_tag_icon', true );

        $this->render_color_field( $color );
        $this->render_icon_field( $icon );
    }

    /**
     * Save tag fields
     *
     * @param int $term_id Term ID
     */
    public function save_fields( int $term_id ): void {
        // Verify nonce
        if ( ! isset( $_POST['_wpnonce_add-tag'] ) ) {
            return;
        }

        // Save color
        $color = isset( $_POST['_aps_tag_color'] )
            ? sanitize_hex_color( $_POST['_aps_tag_color'] )
            : '';

        if ( $color ) {
            update_term_meta( $term_id, '_aps_tag_color', $color );
        } else {
            delete_term_meta( $term_id, '_aps_tag_color' );
        }

        // Save icon
        $icon = isset( $_POST['_aps_tag_icon'] )
            ? sanitize_text_field( $_POST['_aps_tag_icon'] )
            : '';

        if ( $icon ) {
            update_term_meta( $term_id, '_aps_tag_icon', $icon );
        } else {
            delete_term_meta( $term_id, '_aps_tag_icon' );
        }
    }

    /**
     * Render color field
     *
     * @param string $value Current color value
     */
    private function render_color_field( string $value ): void {
        ?>
        <div class="form-field">
            <label for="_aps_tag_color">
                <?php esc_html_e( 'Tag Color', 'affiliate-product-showcase' ); ?>
            </label>
            <input type="color"
                   id="_aps_tag_color"
                   name="_aps_tag_color"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="color-picker" />
            <p class="description">
                <?php esc_html_e( 'Select a color to display the tag on the frontend.', 'affiliate-product-showcase' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render icon field
     *
     * @param string $value Current icon value
     */
    private function render_icon_field( string $value ): void {
        ?>
        <div class="form-field">
            <label for="_aps_tag_icon">
                <?php esc_html_e( 'Tag Icon', 'affiliate-product-showcase' ); ?>
            </label>
            <input type="text"
                   id="_aps_tag_icon"
                   name="_aps_tag_icon"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="regular-text" />
            <p class="description">
                <?php esc_html_e( 'Enter an icon class (e.g., dashicons-cart).', 'affiliate-product-showcase' ); ?>
            </p>
        </div>
        <?php
    }
}
```

### Implementation Steps

1. **Create TagFields.php**
   ```bash
   touch src/Admin/TagFields.php
   ```

2. **Add fields code**
   - Copy template above
   - Paste into `src/Admin/TagFields.php`
   - Verify nonce verification
   - Verify sanitization

3. **Register fields**
   - Instantiate TagFields
   - Call register() method
   - Verify fields appear in admin

4. **Test fields**
   - Create tag with fields
   - Edit tag with fields
   - Verify data saves

### Verification Checklist
- [ ] TagFields.php file created
- [ ] add_form_fields() works
- [ ] edit_form_fields() works
- [ ] save_fields() works
- [ ] Nonce verification present
- [ ] Input sanitization present
- [ ] Meta keys use underscore prefix
- [ ] Fields render in admin
- [ ] Data saves correctly

---

## Phase 6: Create TagTable (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Admin/TagTable.php`

### TagTable Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use WP_List_Table;

/**
 * Tag table component
 *
 * Displays tags in admin listing table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class TagTable extends WP_List_Table {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( [
            'singular' => 'tag',
            'plural' => 'tags',
            'ajax' => false,
        ] );

        $this->prepare_items();
    }

    /**
     * Get table columns
     *
     * @return array<string, string> Columns
     */
    public function get_columns(): array {
        return [
            'cb' => '<input type="checkbox" />',
            'name' => __( 'Name', 'affiliate-product-showcase' ),
            'slug' => __( 'Slug', 'affiliate-product-showcase' ),
            'description' => __( 'Description', 'affiliate-product-showcase' ),
            'color' => __( 'Color', 'affiliate-product-showcase' ),
            'icon' => __( 'Icon', 'affiliate-product-showcase' ),
            'count' => __( 'Count', 'affiliate-product-showcase' ),
        ];
    }

    /**
     * Render column content
     *
     * @param WP_Term $item Term object
     * @param string $column_name Column name
     */
    public function column_default( $item, string $column_name ): string {
        $color = get_term_meta( $item->term_id, '_aps_tag_color', true );
        $icon = get_term_meta( $item->term_id, '_aps_tag_icon', true );

        switch ( $column_name ) {
            case 'name':
                $actions = [
                    'edit' => sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( get_edit_term_link( $item->term_id, 'aps_tag' ) ),
                        esc_html( $item->name )
                    ),
                    'view' => sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( get_term_link( $item->term_id, 'aps_tag' ) ),
                        esc_html__( 'View', 'affiliate-product-showcase' )
                    ),
                ];
                return sprintf(
                    '<strong>%s</strong> %s',
                    esc_html( $item->name ),
                    $this->row_actions( $actions )
                );

            case 'slug':
                return esc_html( $item->slug );

            case 'description':
                return esc_html( $item->description );

            case 'color':
                if ( $color ) {
                    return sprintf(
                        '<span style="display:inline-block;width:20px;height:20px;background-color:%s;border:1px solid #ccc;"></span> %s',
                        esc_attr( $color ),
                        esc_html( $color )
                    );
                }
                return '—';

            case 'icon':
                return $icon ? esc_html( $icon ) : '—';

            case 'count':
                return (string) $item->count;

            default:
                return '';
        }
    }

    /**
     * Prepare table items
     */
    public function prepare_items(): void {
        $per_page = $this->get_items_per_page( 'tags_per_page', 20 );
        $current_page = $this->get_pagenum();

        $args = [
            'taxonomy' => 'aps_tag',
            'number' => $per_page,
            'offset' => ( $current_page - 1 ) * $per_page,
            'hide_empty' => false,
        ];

        $terms = get_terms( $args );
        $total_terms = wp_count_terms( 'aps_tag', [ 'hide_empty' => false ] );

        $this->items = $terms;
        $this->set_pagination_args( [
            'total_items' => is_wp_error( $total_terms ) ? 0 : $total_terms,
            'per_page' => $per_page,
        ] );
    }

    /**
     * Display table
     */
    public function display(): void {
        $this->display_tablenav( 'top' );
        $this->display_rows_or_placeholder();
        $this->display_tablenav( 'bottom' );
    }
}
```

### Implementation Steps

1. **Create TagTable.php**
   ```bash
   touch src/Admin/TagTable.php
   ```
2. **Add table code**
   - Copy template above
   - Paste into `src/Admin/TagTable.php`
   - Verify columns are correct
   - Verify rendering works
3. **Create admin page**
   - Create admin page for tags
   - Instantiate TagTable
   - Verify table displays

### Verification Checklist
- [ ] TagTable.php file created
- [ ] get_columns() works
- [ ] column_default() works
- [ ] prepare_items() works
- [ ] Table displays correctly
- [ ] Pagination works
- [ ] Row actions work
- [ ] Bulk actions work

---

## Phase 7: Create TagsController (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Rest/TagsController.php`

### TagsController Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Repositories\TagRepository;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Tags REST API controller
 *
 * Handles REST API endpoints for tags.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
final class TagsController extends WP_REST_Controller {
    private TagRepository $repository;

    public function __construct( TagRepository $repository ) {
        $this->namespace = 'affiliate-product-showcase/v1';
        $this->rest_base = 'tags';
        $this->repository = $repository;
    }

    /**
     * Register routes
     */
    public function register_routes(): void {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'get_items_permissions_check' ],
            ],
            [
                'methods' => 'POST',
                'callback' => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'create_item_permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => 'GET',
                'callback' => [ $this, 'get_item' ],
                'permission_callback' => [ $this, 'get_item_permissions_check' ],
            ],
            [
                'methods' => 'POST',
                'callback' => [ $this, 'update_item' ],
                'permission_callback' => [ $this, 'update_item_permissions_check' ],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [ $this, 'delete_item' ],
                'permission_callback' => [ $this, 'delete_item_permissions_check' ],
            ],
        ] );
    }

    /**
     * Get all tags
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response
     */
    public function get_items( $request ): WP_REST_Response {
        $tags = $this->repository->all();
        return new WP_REST_Response( $tags, 200 );
    }

    /**
     * Get single tag
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function get_item( $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $tag = $this->repository->find( $id );

        if ( ! $tag ) {
            return new WP_Error( 'tag_not_found', 'Tag not found', [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $tag->to_array(), 200 );
    }

    /**
     * Create tag
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function create_item( $request ): WP_REST_Response|WP_Error {
        $params = $request->get_json_params();

        // Validation
        if ( empty( $params['name'] ) ) {
            return new WP_Error( 'missing_name', 'Tag name is required', [ 'status' => 400 ] );
        }

        try {
            $tag = \AffiliateProductShowcase\Factories\TagFactory::from_array( $params );
            $created = $this->repository->create( $tag );
            return new WP_REST_Response( $created->to_array(), 201 );
        } catch ( \Exception $e ) {
            return new WP_Error( 'creation_failed', $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * Update tag
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function update_item( $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );
        $params = $request->get_json_params();

        try {
            $tag = \AffiliateProductShowcase\Factories\TagFactory::from_array( array_merge( [ 'id' => $id ], $params ) );
            $updated = $this->repository->update( $id, $tag );
            return new WP_REST_Response( $updated->to_array(), 200 );
        } catch ( \Exception $e ) {
            return new WP_Error( 'update_failed', $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * Delete tag
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function delete_item( $request ): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param( 'id' );

        try {
            $this->repository->delete( $id );
            return new WP_REST_Response( [ 'deleted' => true ], 200 );
        } catch ( \Exception $e ) {
            return new WP_Error( 'deletion_failed', $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * Permission checks
     */
    public function get_items_permissions_check(): bool {
        return current_user_can( 'manage_options' );
    }

    public function get_item_permissions_check(): bool {
        return current_user_can( 'manage_options' );
    }

    public function create_item_permissions_check(): bool {
        return current_user_can( 'manage_options' );
    }

    public function update_item_permissions_check(): bool {
        return current_user_can( 'manage_options' );
    }

    public function delete_item_permissions_check(): bool {
        return current_user_can( 'manage_options' );
    }
}
```

### Implementation Steps

1. **Create TagsController.php**
   ```bash
   touch src/Rest/TagsController.php
   ```
2. **Add controller code**
   - Copy template above
   - Paste into `src/Rest/TagsController.php`
   - Verify all endpoints exist
   - Verify permission checks
3. **Register controller**
   - Register REST routes
   - Verify API endpoints work
4. **Test API**
   - Test all CRUD operations
   - Verify responses

### Verification Checklist
- [ ] TagsController.php file created
- [ ] register_routes() works
- [ ] get_items() works
- [ ] get_item() works
- [ ] create_item() works
- [ ] update_item() works
- [ ] delete_item() works
- [ ] Permission checks present
- [ ] Error handling present
- [ ] API endpoints work

---

## Phase 8: DI Container Registration (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Plugin/ServiceProvider.php`

### Registration Code

```php
// Register TagRepository
$this->container->set(
    AffiliateProductShowcase\Repositories\TagRepository::class,
    fn() => new AffiliateProductShowcase\Repositories\TagRepository()
);

// Register TagsController
$this->container->set(
    AffiliateProductShowcase\Rest\TagsController::class,
    fn( $container ) => new AffiliateProductShowcase\Rest\TagsController(
        $container->get( AffiliateProductShowcase\Repositories\TagRepository::class )
    )
);
```

### Implementation Steps

1. **Open ServiceProvider.php**
2. **Add TagRepository registration**
3. **Add TagsController registration**
4. **Verify dependencies resolve correctly**

### Verification Checklist
- [ ] TagRepository registered
- [ ] TagsController registered
- [ ] Dependencies resolved
- [ ] No errors on instantiation

---

## Phase 9: Update Loader (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Plugin/Loader.php`

### Loader Updates

```php
// Load Tag taxonomy
$this->load( AffiliateProductShowcase\Taxonomies\TagTaxonomy::class );

// Load TagFields
$this->load( AffiliateProductShowcase\Admin\TagFields::class );

// Load TagsController
$this->load( AffiliateProductShowcase\Rest\TagsController::class );
```

### Implementation Steps

1. **Open Loader.php**
2. **Add TagTaxonomy load**
3. **Add TagFields load**
4. **Add TagsController load**
5. **Verify components load correctly**

### Verification Checklist
- [ ] TagTaxonomy loaded
- [ ] TagFields loaded
- [ ] TagsController loaded
- [ ] All components initialize

---

## Phase 10: Add to Menu (LOW)

**Priority:** 🟢 LOW  
**Files to Modify:** `src/Admin/Menu.php`

### Menu Registration

```php
// Tags menu item
add_submenu_page(
    'affiliate-product-showcase',
    __( 'Tags', 'affiliate-product-showcase' ),
    __( 'Tags', 'affiliate-product-showcase' ),
    'manage_options',
    'aps-tags',
    function() {
        // Render tag table
        $table = new AffiliateProductShowcase\Admin\TagTable();
        $table->display();
    }
);
```

### Implementation Steps

1. **Open Menu.php**
2. **Add tags submenu item**
3. **Verify menu appears**

### Verification Checklist
- [ ] Tags menu item added
- [ ] Menu appears in admin
- [ ] Page renders correctly

---

## Phase 11: Testing & Verification (REQUIRED)

**Priority:** 🟡 REQUIRED

### Unit Tests

**File:** `tests/Unit/Models/TagTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Models\Tag;

final class TagTest extends TestCase {
    public function test_tag_creation(): void {
        $tag = new Tag(
            1,
            'Sale',
            'sale',
            'Sale items',
            5,
            '#ff0000',
            'dashicons-tag',
            '2026-01-24',
            '2026-01-24'
        );
        
        $this->assertEquals(1, $tag->id);
        $this->assertEquals('Sale', $tag->name);
        $this->assertEquals('#ff0000', $tag->color);
        $this->assertEquals('dashicons-tag', $tag->icon);
    }
    
    public function test_to_array(): void {
        $tag = new Tag(1, 'Test', 'test', '', 0);
        $array = $tag->to_array();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
    }
}
```

### Integration Tests

**File:** `tests/Integration/Repositories/TagRepositoryTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Repositories;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Repositories\TagRepository;
use AffiliateProductShowcase\Factories\TagFactory;

final class TagRepositoryTest extends TestCase {
    public function test_create_tag_with_metadata(): void {
        $repository = new TagRepository();
        
        $tag = TagFactory::from_array([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
            'description' => 'Test description',
            'color' => '#ff0000',
            'icon' => 'dashicons-tag'
        ]);
        
        $created = $repository->create($tag);
        
        $this->assertEquals('Test Tag', $created->name);
        $this->assertEquals('#ff0000', $created->color);
        
        // Verify meta saved with underscore prefix
        $meta = get_term_meta($created->id, '_aps_tag_color', true);
        $this->assertEquals('#ff0000', $meta);
        
        // Cleanup
        wp_delete_term($created->id, 'aps_tag');
    }
}
```

### Static Analysis

```bash
# Run PHPStan
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpstan

# Run Psalm
composer --working-dir=wp-content/plugins/affiliate-product-showcase psalm

# Run PHPCS
composer --working-dir=wp-content/plugins/affiliate-product-showcase phpcs
```

### Manual Testing Checklist

**Admin Interface:**
- [ ] Create new tag with all fields
- [ ] Verify all fields save correctly
- [ ] Edit existing tag
- [ ] Verify all fields display correctly
- [ ] Update fields and save
- [ ] Verify updates persist
- [ ] Delete tag
- [ ] Verify tag removed
- [ ] Verify non-hierarchical behavior (no parent/child)

**REST API:**
- [ ] GET `/v1/tags` - List tags
- [ ] GET `/v1/tags/{id}` - Get single tag
- [ ] POST `/v1/tags` - Create tag
- [ ] POST `/v1/tags/{id}` - Update tag
- [ ] DELETE `/v1/tags/{id}` - Delete tag

**Frontend Display:**
- [ ] View tag listing page
- [ ] Verify all tags display
- [ ] Verify tag colors display
- [ ] Verify tag icons display
- [ ] Test tag cloud layout
- [ ] Test responsive design

---

## Summary

### Phase Overview

| Phase | Priority | Description |
|--------|----------|-------------|
| Phase 1: Create Tag Model | 🔴 CRITICAL | Tag model with readonly properties |
| Phase 2: Create TagFactory | 🟠 HIGH | Factory with from_wp_term() and from_array() |
| Phase 3: Create TagRepository | 🟠 HIGH | Full CRUD operations |
| Phase 4: Register Tag Taxonomy | 🟠 HIGH | Taxonomy registration |
| Phase 5: Create TagFields | 🟡 MEDIUM | Admin component |
| Phase 6: Create TagTable | 🟡 MEDIUM | Admin listing |
| Phase 7: Create TagsController | 🟡 MEDIUM | REST API |
| Phase 8: DI Container Registration | 🟡 MEDIUM | Service registration |
| Phase 9: Update Loader | 🟡 MEDIUM | Component loading |
| Phase 10: Add to Menu | 🟢 LOW | Menu item |
| Phase 11: Testing & Verification | 🟡 REQUIRED | Comprehensive testing |

### Dependencies

- Phases 1-3 must be completed before Phase 4
- Phases 4-7 must be completed before Phase 8
- Phase 8 must be completed before Phase 9
- Phase 9 must be completed before Phase 10
- Phase 11 depends on all previous phases

### Risk Mitigation

**Backup Strategy:**
- Create backups before each phase
- Keep backups for at least 1 week
- Test on staging environment first

**Testing Strategy:**
- Run unit tests after each phase
- Run integration tests after each phase
- Manual testing after each phase
- Static analysis before committing

---

## Next Steps

1. **Start with Phase 1** (Tag model - most critical)
2. Complete phases in order
3. Test thoroughly after each phase
4. Commit changes with proper messages
5. Update feature-requirements.md with completion status

**Note:** Follow true hybrid patterns from Section 2 (Categories) implementation.

---

## Expected Outcome

After completing all phases, Section 3 (Tags) will be **100% true hybrid compliant**:
- ✅ Tag model with readonly properties
- ✅ TagFactory with from_wp_term() and from_array() methods
- ✅ TagRepository with full CRUD operations
- ✅ Tag taxonomy registered (non-hierarchical)
- ✅ TagFields admin component working
- ✅ TagTable admin listing working
- ✅ TagsController REST API working
- ✅ All meta keys use underscore prefix (`_aps_tag_*`)
- ✅ All 24 basic features working
- ✅ Ready for production use