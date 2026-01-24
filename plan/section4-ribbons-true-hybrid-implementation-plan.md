# Section 4: Ribbons - True Hybrid Implementation Plan

**Created:** January 24, 2026  
**Priority:** 🟠 HIGH - Complete new feature implementation  
**Scope:** Basic Level Features Only (23 features)

---

## Executive Summary

Section 4 (Ribbons) is **NOT IMPLEMENTED** - complete feature implementation required to achieve true hybrid compliance.

**Current Status:**
- ❌ No Ribbon model exists
- ❌ No RibbonFactory exists
- ❌ No RibbonRepository exists
- ❌ No RibbonFields admin component exists
- ❌ No RibbonTable admin listing exists
- ❌ No RibbonsController REST API exists
- ❌ No ribbon options management exists

**Impact:** Need to implement complete ribbon feature from scratch following true hybrid patterns.

**Important Note:** Ribbons are stored as WordPress options (wp_options), NOT as taxonomy terms.

---

## Understanding True Hybrid for Ribbons

**True Hybrid Means:**
1. ✅ All option keys use underscore prefix (`_aps_ribbon_*`)
2. ✅ Ribbon model has readonly properties
3. ✅ RibbonFactory has from_wp_option() and from_array() methods
4. ✅ RibbonRepository has full CRUD operations (using wp_options)
5. ✅ RibbonFields admin component uses nonce verification
6. ✅ REST API endpoints have permission checks
7. ✅ Consistent naming across all components

**Storage Strategy:**
- Ribbons stored as WordPress options (NOT taxonomy terms)
- Each ribbon is a separate option key: `_aps_ribbon_{id}`
- Ribbon list stored as option: `_aps_ribbons_list`
- This is different from Categories/Tags which use taxonomy + term meta

---

## Implementation Phases Overview

| Phase | Priority | Description |
|--------|----------|-------------|
| Phase 1: Create Ribbon Model | 🔴 CRITICAL | Ribbon model with readonly properties |
| Phase 2: Create RibbonFactory | 🟠 HIGH | Factory with from_wp_option() and from_array() |
| Phase 3: Create RibbonRepository | 🟠 HIGH | Full CRUD operations (using wp_options) |
| Phase 4: Create RibbonFields | 🟡 MEDIUM | Admin component for ribbon management |
| Phase 5: Create RibbonTable | 🟡 MEDIUM | Admin listing for ribbons |
| Phase 6: Create RibbonsController | 🟡 MEDIUM | REST API endpoints |
| Phase 7: DI Container Registration | 🟡 MEDIUM | Register services in DI container |
| Phase 8: Update Loader | 🟡 MEDIUM | Load new components |
| Phase 9: Add to Menu | 🟢 LOW | Add Ribbons menu item |
| Phase 10: Testing & Verification | 🟡 REQUIRED | Comprehensive testing |

---

## Phase 1: Create Ribbon Model (CRITICAL)

**Priority:** 🔴 HIGHEST  
**Files to Create:** `src/Models/Ribbon.php`

### Ribbon Model Requirements

**Properties (Readonly):**
```php
- id: string (unique ribbon identifier, e.g., "ribbon_123")
- name: string (ribbon name for display)
- color: string (text color - hex code)
- background_color: string (background color - hex code)
- text_color: string (text color - hex code)
- sort_order: int (display order)
- is_default: bool (default ribbon selection)
- created_at: string (creation timestamp)
- updated_at: string (last update timestamp)
```

### Ribbon Model Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Models;

/**
 * Ribbon model
 *
 * Represents a product ribbon/badge.
 * Stored as WordPress options (NOT taxonomy terms).
 *
 * @package AffiliateProductShowcase\Models
 * @since 1.0.0
 */
final class Ribbon {
    /**
     * Ribbon ID (unique identifier)
     */
    public readonly string $id;

    /**
     * Ribbon name
     */
    public readonly string $name;

    /**
     * Text color (hex code)
     */
    public readonly string $color;

    /**
     * Background color (hex code)
     */
    public readonly string $background_color;

    /**
     * Text color (hex code)
     */
    public readonly string $text_color;

    /**
     * Sort order
     */
    public readonly int $sort_order;

    /**
     * Is default ribbon
     */
    public readonly bool $is_default;

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
     * @param string $id Ribbon ID
     * @param string $name Ribbon name
     * @param string $color Text color
     * @param string $background_color Background color
     * @param string $text_color Text color
     * @param int $sort_order Display order
     * @param bool $is_default Default ribbon
     * @param string|null $created_at Creation timestamp
     * @param string|null $updated_at Update timestamp
     */
    public function __construct(
        string $id,
        string $name,
        string $color,
        string $background_color,
        string $text_color,
        int $sort_order = 0,
        bool $is_default = false,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->background_color = $background_color;
        $this->text_color = $text_color;
        $this->sort_order = $sort_order;
        $this->is_default = $is_default;
        $this->created_at = $created_at ?? current_time( 'mysql' );
        $this->updated_at = $updated_at ?? current_time( 'mysql' );
    }

    /**
     * Create Ribbon from WordPress option
     *
     * @param string $id Ribbon ID
     * @return self Ribbon instance
     */
    public static function from_wp_option( string $id ): self {
        $option_key = '_aps_ribbon_' . $id;
        $data = get_option( $option_key );

        if ( ! $data ) {
            throw new \RuntimeException( 'Ribbon not found' );
        }

        return new self(
            id: $id,
            name: $data['name'] ?? '',
            color: $data['color'] ?? '#000000',
            background_color: $data['background_color'] ?? '#ff0000',
            text_color: $data['text_color'] ?? '#ffffff',
            sort_order: (int) ( $data['sort_order'] ?? 0 ),
            is_default: (bool) ( $data['is_default'] ?? false ),
            created_at: $data['created_at'] ?? current_time( 'mysql' ),
            updated_at: $data['updated_at'] ?? current_time( 'mysql' )
        );
    }

    /**
     * Convert Ribbon to array
     *
     * @return array Ribbon data as array
     */
    public function to_array(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'background_color' => $this->background_color,
            'text_color' => $this->text_color,
            'sort_order' => $this->sort_order,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Implementation Steps

1. **Create Ribbon.php**
   ```bash
   touch src/Models/Ribbon.php
   ```

2. **Add model code**
   - Copy template above
   - Paste into `src/Models/Ribbon.php`
   - Verify all properties are readonly
   - Verify all type hints are present

3. **Test model**
   - Create test ribbon from option
   - Verify properties are set correctly
   - Verify to_array() works

### Verification Checklist
- [ ] Ribbon.php file created
- [ ] All properties readonly
- [ ] All type hints present
- [ ] from_wp_option() method works
- [ ] to_array() method works
- [ ] Option keys use underscore prefix

---

## Phase 2: Create RibbonFactory (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Factories/RibbonFactory.php`

### RibbonFactory Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Factories;

use AffiliateProductShowcase\Models\Ribbon;

/**
 * Ribbon factory
 *
 * Creates Ribbon instances from various data sources.
 *
 * @package AffiliateProductShowcase\Factories
 * @since 1.0.0
 */
final class RibbonFactory {
    /**
     * Create Ribbon from WordPress option
     *
     * @param string $id Ribbon ID
     * @return Ribbon Ribbon instance
     */
    public static function from_wp_option( string $id ): Ribbon {
        return Ribbon::from_wp_option( $id );
    }

    /**
     * Create Ribbon from array
     *
     * @param array $data Ribbon data
     * @return Ribbon Ribbon instance
     */
    public static function from_array( array $data ): Ribbon {
        $id = $data['id'] ?? 'ribbon_' . time();

        return new Ribbon(
            id: $id,
            name: $data['name'] ?? '',
            color: $data['color'] ?? '#000000',
            background_color: $data['background_color'] ?? '#ff0000',
            text_color: $data['text_color'] ?? '#ffffff',
            sort_order: (int) ( $data['sort_order'] ?? 0 ),
            is_default: (bool) ( $data['is_default'] ?? false ),
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Create multiple Ribbons from array
     *
     * @param array<array> $data_array Array of ribbon data
     * @return array<Ribbon> Array of Ribbon instances
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

1. **Create RibbonFactory.php**
   ```bash
   touch src/Factories/RibbonFactory.php
   ```

2. **Add factory code**
   - Copy template above
   - Paste into `src/Factories/RibbonFactory.php`
   - Verify all factory methods exist
   - Verify type hints are correct

3. **Test factory**
   - Create ribbon from array
   - Create ribbon from option
   - Verify both methods work

### Verification Checklist
- [ ] RibbonFactory.php file created
- [ ] from_wp_option() method works
- [ ] from_array() method works
- [ ] from_array_many() method works
- [ ] All type hints present

---

## Phase 3: Create RibbonRepository (HIGH)

**Priority:** 🟠 HIGH  
**Files to Create:** `src/Repositories/RibbonRepository.php`

### RibbonRepository Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Repositories;

use AffiliateProductShowcase\Models\Ribbon;
use AffiliateProductShowcase\Factories\RibbonFactory;

/**
 * Ribbon repository
 *
 * Handles CRUD operations for ribbons using WordPress options.
 *
 * @package AffiliateProductShowcase\Repositories
 * @since 1.0.0
 */
final class RibbonRepository {
    private const LIST_OPTION = '_aps_ribbons_list';
    private const OPTION_PREFIX = '_aps_ribbon_';

    /**
     * Create a new ribbon
     *
     * @param Ribbon $ribbon Ribbon to create
     * @return Ribbon Created ribbon
     */
    public function create( Ribbon $ribbon ): Ribbon {
        $id = 'ribbon_' . time();

        $data = $ribbon->to_array();
        $data['id'] = $id;

        // Save ribbon data
        update_option( self::OPTION_PREFIX . $id, $data );

        // Update list
        $this->add_to_list( $id );

        return $this->find( $id );
    }

    /**
     * Find a ribbon by ID
     *
     * @param string $id Ribbon ID
     * @return Ribbon|null Ribbon instance or null if not found
     */
    public function find( string $id ): ?Ribbon {
        $option_key = self::OPTION_PREFIX . $id;
        $data = get_option( $option_key );

        if ( ! $data ) {
            return null;
        }

        return RibbonFactory::from_array( $data );
    }

    /**
     * Update an existing ribbon
     *
     * @param string $id Ribbon ID
     * @param Ribbon $ribbon Ribbon data to update
     * @return Ribbon Updated ribbon
     */
    public function update( string $id, Ribbon $ribbon ): Ribbon {
        $option_key = self::OPTION_PREFIX . $id;

        $data = $ribbon->to_array();
        $data['id'] = $id;

        // Save updated data
        update_option( $option_key, $data );

        return $this->find( $id );
    }

    /**
     * Delete a ribbon
     *
     * @param string $id Ribbon ID
     * @return bool True if deleted
     */
    public function delete( string $id ): bool {
        $option_key = self::OPTION_PREFIX . $id;

        // Delete ribbon data
        delete_option( $option_key );

        // Remove from list
        $this->remove_from_list( $id );

        return true;
    }

    /**
     * Get all ribbons
     *
     * @param array $args Query arguments
     * @return array<Ribbon> Array of ribbons
     */
    public function all( array $args = [] ): array {
        $ribbon_ids = get_option( self::LIST_OPTION, [] );

        if ( empty( $ribbon_ids ) ) {
            return [];
        }

        $ribbons = [];
        foreach ( $ribbon_ids as $id ) {
            $ribbon = $this->find( $id );
            if ( $ribbon ) {
                $ribbons[] = $ribbon;
            }
        }

        // Sort by sort_order
        usort( $ribbons, fn( $a, $b ) => $a->sort_order <=> $b->sort_order );

        return $ribbons;
    }

    /**
     * Search ribbons by name
     *
     * @param string $search Search term
     * @return array<Ribbon> Matching ribbons
     */
    public function search( string $search ): array {
        $all_ribbons = $this->all();

        return array_filter(
            $all_ribbons,
            fn( $ribbon ) => stripos( $ribbon->name, $search ) !== false
        );
    }

    /**
     * Get default ribbon
     *
     * @return Ribbon|null Default ribbon or null
     */
    public function get_default(): ?Ribbon {
        $all_ribbons = $this->all();

        foreach ( $all_ribbons as $ribbon ) {
            if ( $ribbon->is_default ) {
                return $ribbon;
            }
        }

        return null;
    }

    /**
     * Set default ribbon
     *
     * @param string $id Ribbon ID
     */
    public function set_default( string $id ): void {
        // Remove default from all ribbons
        $all_ribbons = $this->all();
        foreach ( $all_ribbons as $ribbon ) {
            if ( $ribbon->is_default && $ribbon->id !== $id ) {
                $this->update( $ribbon->id, new Ribbon(
                    id: $ribbon->id,
                    name: $ribbon->name,
                    color: $ribbon->color,
                    background_color: $ribbon->background_color,
                    text_color: $ribbon->text_color,
                    sort_order: $ribbon->sort_order,
                    is_default: false,
                    created_at: $ribbon->created_at,
                    updated_at: $ribbon->updated_at
                ) );
            }
        }

        // Set new default
        $ribbon = $this->find( $id );
        if ( $ribbon ) {
            $this->update( $id, new Ribbon(
                id: $id,
                name: $ribbon->name,
                color: $ribbon->color,
                background_color: $ribbon->background_color,
                text_color: $ribbon->text_color,
                sort_order: $ribbon->sort_order,
                is_default: true,
                created_at: $ribbon->created_at,
                updated_at: $ribbon->updated_at
            ) );
        }
    }

    /**
     * Add ribbon ID to list
     *
     * @param string $id Ribbon ID
     */
    private function add_to_list( string $id ): void {
        $list = get_option( self::LIST_OPTION, [] );

        if ( ! in_array( $id, $list ) ) {
            $list[] = $id;
            update_option( self::LIST_OPTION, $list );
        }
    }

    /**
     * Remove ribbon ID from list
     *
     * @param string $id Ribbon ID
     */
    private function remove_from_list( string $id ): void {
        $list = get_option( self::LIST_OPTION, [] );

        $list = array_filter( $list, fn( $item ) => $item !== $id );
        update_option( self::LIST_OPTION, array_values( $list ) );
    }

    /**
     * Duplicate a ribbon
     *
     * @param string $id Ribbon ID to duplicate
     * @return Ribbon New ribbon
     */
    public function duplicate( string $id ): Ribbon {
        $original = $this->find( $id );

        if ( ! $original ) {
            throw new \RuntimeException( 'Original ribbon not found' );
        }

        $new_data = $original->to_array();
        $new_data['name'] = $new_data['name'] . ' (Copy)';
        $new_data['is_default'] = false;

        return $this->create( RibbonFactory::from_array( $new_data ) );
    }
}
```

### Implementation Steps

1. **Create RibbonRepository.php**
   ```bash
   touch src/Repositories/RibbonRepository.php
   ```

2. **Add repository code**
   - Copy template above
   - Paste into `src/Repositories/RibbonRepository.php`
   - Verify all CRUD methods exist
   - Verify option keys use underscore prefix

3. **Test repository**
   - Create ribbon
   - Find ribbon
   - Update ribbon
   - Delete ribbon
   - List all ribbons

### Verification Checklist
- [ ] RibbonRepository.php file created
- [ ] create() method works
- [ ] find() method works
- [ ] update() method works
- [ ] delete() method works
- [ ] all() method works
- [ ] search() method works
- [ ] get_default() method works
- [ ] set_default() method works
- [ ] duplicate() method works
- [ ] Option keys use underscore prefix

---

## Phase 4: Create RibbonFields (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Admin/RibbonFields.php`

### RibbonFields Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

/**
 * Ribbon fields component
 *
 * Adds ribbon fields to product form.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class RibbonFields {
    /**
     * Register ribbon fields
     *
     * @hook aps_product_meta_box
     */
    public function register(): void {
        add_meta_box(
            'aps_ribbon_meta_box',
            __( 'Ribbon', 'affiliate-product-showcase' ),
            [ $this, 'render_meta_box' ],
            'aps_product',
            'side',
            'default'
        );

        add_action( 'save_post', [ $this, 'save_fields' ] );
    }

    /**
     * Render meta box
     *
     * @param WP_Post $post Post object
     */
    public function render_meta_box( \WP_Post $post ): void {
        $selected_ribbon = get_post_meta( $post->ID, '_aps_ribbon_id', true );

        // Get all ribbons
        $repository = new \AffiliateProductShowcase\Repositories\RibbonRepository();
        $ribbons = $repository->all();

        ?>
        <div class="aps-ribbon-selector">
            <label for="aps_ribbon_id">
                <?php esc_html_e( 'Select Ribbon', 'affiliate-product-showcase' ); ?>
            </label>

            <select id="aps_ribbon_id" name="aps_ribbon_id" class="widefat">
                <option value=""><?php esc_html_e( 'None', 'affiliate-product-showcase' ); ?></option>

                <?php foreach ( $ribbons as $ribbon ) : ?>
                    <option value="<?php echo esc_attr( $ribbon->id ); ?>"
                        <?php selected( $selected_ribbon, $ribbon->id ); ?>>
                        <?php echo esc_html( $ribbon->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ( $selected_ribbon ) : ?>
                <?php
                $ribbon = $repository->find( $selected_ribbon );
                if ( $ribbon ) :
                ?>
                <div class="aps-ribbon-preview"
                     style="background-color: <?php echo esc_attr( $ribbon->background_color ); ?>; color: <?php echo esc_attr( $ribbon->text_color ); ?>;">
                    <?php echo esc_html( $ribbon->name ); ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <input type="hidden" name="aps_ribbon_nonce" value="<?php echo wp_create_nonce( 'aps_ribbon_save' ); ?>" />
        </div>
        <?php
    }

    /**
     * Save ribbon field
     *
     * @param int $post_id Post ID
     */
    public function save_fields( int $post_id ): void {
        // Verify nonce
        if ( ! isset( $_POST['aps_ribbon_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['aps_ribbon_nonce'], 'aps_ribbon_save' ) ) {
            return;
        }

        // Verify post type
        if ( get_post_type( $post_id ) !== 'aps_product' ) {
            return;
        }

        // Save ribbon ID (with underscore prefix)
        $ribbon_id = isset( $_POST['aps_ribbon_id'] )
            ? sanitize_text_field( $_POST['aps_ribbon_id'] )
            : '';

        if ( $ribbon_id ) {
            update_post_meta( $post_id, '_aps_ribbon_id', $ribbon_id );
        } else {
            delete_post_meta( $post_id, '_aps_ribbon_id' );
        }
    }
}
```

### Implementation Steps

1. **Create RibbonFields.php**
   ```bash
   touch src/Admin/RibbonFields.php
   ```

2. **Add fields code**
   - Copy template above
   - Paste into `src/Admin/RibbonFields.php`
   - Verify nonce verification
   - Verify sanitization

3. **Register fields**
   - Instantiate RibbonFields
   - Call register() method
   - Verify fields appear in admin

4. **Test fields**
   - Create product with ribbon
   - Edit product with ribbon
   - Verify data saves

### Verification Checklist
- [ ] RibbonFields.php file created
- [ ] render_meta_box() works
- [ ] save_fields() works
- [ ] Nonce verification present
- [ ] Input sanitization present
- [ ] Meta keys use underscore prefix
- [ ] Ribbon selector displays
- [ ] Live preview works
- [ ] Data saves correctly

---

## Phase 5: Create RibbonTable (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Admin/RibbonTable.php`

### RibbonTable Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Admin;

use WP_List_Table;

/**
 * Ribbon table component
 *
 * Displays ribbons in admin listing table.
 *
 * @package AffiliateProductShowcase\Admin
 * @since 1.0.0
 */
final class RibbonTable extends WP_List_Table {
    private array $ribbons = [];

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct( [
            'singular' => 'ribbon',
            'plural' => 'ribbons',
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
            'preview' => __( 'Preview', 'affiliate-product-showcase' ),
            'sort_order' => __( 'Sort Order', 'affiliate-product-showcase' ),
            'is_default' => __( 'Default', 'affiliate-product-showcase' ),
        ];
    }

    /**
     * Render column content
     *
     * @param Ribbon $item Ribbon object
     * @param string $column_name Column name
     */
    public function column_default( $item, string $column_name ): string {
        switch ( $column_name ) {
            case 'name':
                $actions = [
                    'edit' => sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( admin_url( 'admin.php?page=aps-ribbons&action=edit&ribbon=' . $item->id ) ),
                        esc_html( $item->name )
                    ),
                    'duplicate' => sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( admin_url( 'admin.php?page=aps-ribbons&action=duplicate&ribbon=' . $item->id ) ),
                        esc_html__( 'Duplicate', 'affiliate-product-showcase' )
                    ),
                    'delete' => sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( admin_url( 'admin.php?page=aps-ribbons&action=delete&ribbon=' . $item->id ) ),
                        esc_html__( 'Delete', 'affiliate-product-showcase' )
                    ),
                ];
                return sprintf(
                    '<strong>%s</strong> %s',
                    esc_html( $item->name ),
                    $this->row_actions( $actions )
                );

            case 'preview':
                return sprintf(
                    '<span class="aps-ribbon-preview" style="background-color: %s; color: %s; padding: 4px 8px;">%s</span>',
                    esc_attr( $item->background_color ),
                    esc_attr( $item->text_color ),
                    esc_html( $item->name )
                );

            case 'sort_order':
                return (string) $item->sort_order;

            case 'is_default':
                return $item->is_default
                    ? '<span class="dashicons dashicons-star-filled" style="color: gold;"></span>'
                    : '';

            default:
                return '';
        }
    }

    /**
     * Prepare table items
     */
    public function prepare_items(): void {
        $per_page = $this->get_items_per_page( 'ribbons_per_page', 20 );

        $repository = new \AffiliateProductShowcase\Repositories\RibbonRepository();
        $this->ribbons = $repository->all();

        $this->set_pagination_args( [
            'total_items' => count( $this->ribbons ),
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

1. **Create RibbonTable.php**
   ```bash
   touch src/Admin/RibbonTable.php
   ```

2. **Add table code**
   - Copy template above
   - Paste into `src/Admin/RibbonTable.php`
   - Verify columns are correct
   - Verify rendering works

3. **Create admin page**
   - Create admin page for ribbons
   - Instantiate RibbonTable
   - Verify table displays

### Verification Checklist
- [ ] RibbonTable.php file created
- [ ] get_columns() works
- [ ] column_default() works
- [ ] prepare_items() works
- [ ] Table displays correctly
- [ ] Pagination works
- [ ] Row actions work
- [ ] Preview column works
- [ ] Default ribbon indicator works

---

## Phase 6: Create RibbonsController (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Create:** `src/Rest/RibbonsController.php`

### RibbonsController Template

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Rest;

use AffiliateProductShowcase\Repositories\RibbonRepository;
use AffiliateProductShowcase\Factories\RibbonFactory;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Ribbons REST API controller
 *
 * Handles REST API endpoints for ribbons.
 *
 * @package AffiliateProductShowcase\Rest
 * @since 1.0.0
 */
final class RibbonsController extends WP_REST_Controller {
    private RibbonRepository $repository;

    public function __construct( RibbonRepository $repository ) {
        $this->namespace = 'affiliate-product-showcase/v1';
        $this->rest_base = 'ribbons';
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

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[^/]+)', [
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
     * Get all ribbons
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response
     */
    public function get_items( $request ): WP_REST_Response {
        $ribbons = $this->repository->all();
        return new WP_REST_Response(
            array_map( fn( $ribbon ) => $ribbon->to_array(), $ribbons ),
            200
        );
    }

    /**
     * Get single ribbon
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function get_item( $request ): WP_REST_Response|WP_Error {
        $id = $request->get_param( 'id' );
        $ribbon = $this->repository->find( $id );

        if ( ! $ribbon ) {
            return new WP_Error( 'ribbon_not_found', 'Ribbon not found', [ 'status' => 404 ] );
        }

        return new WP_REST_Response( $ribbon->to_array(), 200 );
    }

    /**
     * Create ribbon
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function create_item( $request ): WP_REST_Response|WP_Error {
        $params = $request->get_json_params();

        // Validation
        if ( empty( $params['name'] ) ) {
            return new WP_Error( 'missing_name', 'Ribbon name is required', [ 'status' => 400 ] );
        }

        try {
            $ribbon = RibbonFactory::from_array( $params );
            $created = $this->repository->create( $ribbon );
            return new WP_REST_Response( $created->to_array(), 201 );
        } catch ( \Exception $e ) {
            return new WP_Error( 'creation_failed', $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * Update ribbon
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function update_item( $request ): WP_REST_Response|WP_Error {
        $id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        try {
            $ribbon = RibbonFactory::from_array( array_merge( [ 'id' => $id ], $params ) );
            $updated = $this->repository->update( $id, $ribbon );
            return new WP_REST_Response( $updated->to_array(), 200 );
        } catch ( \Exception $e ) {
            return new WP_Error( 'update_failed', $e->getMessage(), [ 'status' => 500 ] );
        }
    }

    /**
     * Delete ribbon
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response or error
     */
    public function delete_item( $request ): WP_REST_Response|WP_Error {
        $id = $request->get_param( 'id' );

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

1. **Create RibbonsController.php**
   ```bash
   touch src/Rest/RibbonsController.php
   ```

2. **Add controller code**
   - Copy template above
   - Paste into `src/Rest/RibbonsController.php`
   - Verify all endpoints exist
   - Verify permission checks

3. **Register controller**
   - Register REST routes
   - Verify API endpoints work

4. **Test API**
   - Test all CRUD operations
   - Verify responses

### Verification Checklist
- [ ] RibbonsController.php file created
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

## Phase 7: DI Container Registration (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Plugin/ServiceProvider.php`

### Registration Code

```php
// Register RibbonRepository
$this->container->set(
    AffiliateProductShowcase\Repositories\RibbonRepository::class,
    fn() => new AffiliateProductShowcase\Repositories\RibbonRepository()
);

// Register RibbonsController
$this->container->set(
    AffiliateProductShowcase\Rest\RibbonsController::class,
    fn( $container ) => new AffiliateProductShowcase\Rest\RibbonsController(
        $container->get( AffiliateProductShowcase\Repositories\RibbonRepository::class )
    )
);
```

### Implementation Steps

1. **Open ServiceProvider.php**
2. **Add RibbonRepository registration**
3. **Add RibbonsController registration**
4. **Verify dependencies resolve correctly**

### Verification Checklist
- [ ] RibbonRepository registered
- [ ] RibbonsController registered
- [ ] Dependencies resolved
- [ ] No errors on instantiation

---

## Phase 8: Update Loader (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Plugin/Loader.php`

### Loader Updates

```php
// Load RibbonFields
$this->load( AffiliateProductShowcase\Admin\RibbonFields::class );

// Load RibbonsController
$this->load( AffiliateProductShowcase\Rest\RibbonsController::class );
```

### Implementation Steps

1. **Open Loader.php**
2. **Add RibbonFields load**
3. **Add RibbonsController load**
4. **Verify components load correctly**

### Verification Checklist
- [ ] RibbonFields loaded
- [ ] RibbonsController loaded
- [ ] All components initialize

---

## Phase 9: Add to Menu (LOW)

**Priority:** 🟢 LOW  
**Files to Modify:** `src/Admin/Menu.php`

### Menu Registration

```php
// Ribbons menu item
add_submenu_page(
    'affiliate-product-showcase',
    __( 'Ribbons', 'affiliate-product-showcase' ),
    __( 'Ribbons', 'affiliate-product-showcase' ),
    'manage_options',
    'aps-ribbons',
    function() {
        // Render ribbon table
        $table = new AffiliateProductShowcase\Admin\RibbonTable();
        $table->display();
    }
);
```

### Implementation Steps

1. **Open Menu.php**
2. **Add ribbons submenu item**
3. **Verify menu appears**

### Verification Checklist
- [ ] Ribbons menu item added
- [ ] Menu appears in admin
- [ ] Page renders correctly

---

## Phase 10: Testing & Verification (REQUIRED)

**Priority:** 🟡 REQUIRED

### Unit Tests

**File:** `tests/Unit/Models/RibbonTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Models\Ribbon;

final class RibbonTest extends TestCase {
    public function test_ribbon_creation(): void {
        $ribbon = new Ribbon(
            'ribbon_123',
            'Sale',
            '#ffffff',
            '#ff0000',
            '#ffffff',
            1,
            false,
            '2026-01-24',
            '2026-01-24'
        );
        
        $this->assertEquals('ribbon_123', $ribbon->id);
        $this->assertEquals('Sale', $ribbon->name);
        $this->assertEquals('#ff0000', $ribbon->background_color);
        $this->assertFalse($ribbon->is_default);
    }
    
    public function test_to_array(): void {
        $ribbon = new Ribbon('ribbon_1', 'Test', '#fff', '#f00', '#fff', 0);
        $array = $ribbon->to_array();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
    }
}
```

### Integration Tests

**File:** `tests/Integration/Repositories/RibbonRepositoryTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Repositories;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Repositories\RibbonRepository;
use AffiliateProductShowcase\Factories\RibbonFactory;

final class RibbonRepositoryTest extends TestCase {
    public function test_create_ribbon(): void {
        $repository = new RibbonRepository();
        
        $ribbon = RibbonFactory::from_array([
            'name' => 'Test Ribbon',
            'color' => '#ffffff',
            'background_color' => '#ff0000',
            'text_color' => '#ffffff',
            'sort_order' => 1
        ]);
        
        $created = $repository->create($ribbon);
        
        $this->assertEquals('Test Ribbon', $created->name);
        $this->assertEquals('#ff0000', $created->background_color);
        
        // Cleanup
        $repository->delete($created->id);
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
- [ ] Create new ribbon with all fields
- [ ] Verify all fields save correctly
- [ ] Edit existing ribbon
- [ ] Verify all fields display correctly
- [ ] Update fields and save
- [ ] Verify updates persist
- [ ] Delete ribbon
- [ ] Verify ribbon removed
- [ ] Test duplicate functionality
- [ ] Set default ribbon
- [ ] Verify default ribbon indicator

**Product Form Integration:**
- [ ] Create product with ribbon
- [ ] Verify ribbon selector appears
- [ ] Verify live preview works
- [ ] Verify selected ribbon saves

**REST API:**
- [ ] GET `/v1/ribbons` - List ribbons
- [ ] GET `/v1/ribbons/{id}` - Get single ribbon
- [ ] POST `/v1/ribbons` - Create ribbon
- [ ] POST `/v1/ribbons/{id}` - Update ribbon
- [ ] DELETE `/v1/ribbons/{id}` - Delete ribbon

**Frontend Display:**
- [ ] View product with ribbon
- [ ] Verify ribbon displays correctly
- [ ] Verify colors render properly
- [ ] Test responsive design

---

## Summary

### Phase Overview

| Phase | Priority | Description |
|--------|----------|-------------|
| Phase 1: Create Ribbon Model | 🔴 CRITICAL | Ribbon model with readonly properties |
| Phase 2: Create RibbonFactory | 🟠 HIGH | Factory with from_wp_option() and from_array() |
| Phase 3: Create RibbonRepository | 🟠 HIGH | Full CRUD operations (wp_options) |
| Phase 4: Create RibbonFields | 🟡 MEDIUM | Admin component |
| Phase 5: Create RibbonTable | 🟡 MEDIUM | Admin listing |
| Phase 6: Create RibbonsController | 🟡 MEDIUM | REST API |
| Phase 7: DI Container Registration | 🟡 MEDIUM | Service registration |
| Phase 8: Update Loader | 🟡 MEDIUM | Component loading |
| Phase 9: Add to Menu | 🟢 LOW | Menu item |
| Phase 10: Testing & Verification | 🟡 REQUIRED | Comprehensive testing |

### Dependencies

- Phases 1-3 must be completed before Phase 4
- Phases 4-6 must be completed before Phase 7
- Phase 7 must be completed before Phase 8
- Phase 8 must be completed before Phase 9
- Phase 9 must be completed before Phase 10

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

1. **Start with Phase 1** (Ribbon model - most critical)
2. Complete phases in order
3. Test thoroughly after each phase
4. Commit changes with proper messages
5. Update feature-requirements.md with completion status

**Note:** Remember ribbons use WordPress options (NOT taxonomy terms like Categories/Tags).

---

## Expected Outcome

After completing all phases, Section 4 (Ribbons) will be **100% true hybrid compliant**:
- ✅ Ribbon model with readonly properties
- ✅ RibbonFactory with from_wp_option() and from_array() methods
- ✅ RibbonRepository with full CRUD operations (using wp_options)
- ✅ RibbonFields admin component working
- ✅ RibbonTable admin listing working
- ✅ RibbonsController REST API working
- ✅ All option keys use underscore prefix (`_aps_ribbon_*`)
- ✅ All 23 basic features working
- ✅ Ready for production use