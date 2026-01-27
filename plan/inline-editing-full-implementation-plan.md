# Inline Editing Full Implementation Plan

## User Request

Make the Products table fully editable with inline editing capabilities. Currently, the table displays products but requires clicking into each product to edit.

### Requirements

1. **Inline Editable Columns:**
   - Category: Click to edit, dropdown with existing categories + "Add new" option
   - Tags: Click to edit, multi-select dropdown with existing tags + "Add new" option
   - Ribbon: Click to edit, dropdown with available ribbons or "None"
   - Price: Click to edit, input field with currency validation
   - Status: Click to change between Published/Draft directly from table

2. **Auto-discount Calculation:**
   - When regular price is edited, automatically recalculate and display discount percentage
   - Show both original price and discounted price in the Price column
   - Update discount badge in real-time

3. **Bulk Status Actions:**
   - Fix "Publish" bulk action - should move selected drafts to published
   - Fix "Move to Draft" bulk action - should move selected published items to draft
   - Show success notification after bulk actions

4. **Save Behavior:**
   - Auto-save changes on blur (when user clicks away)
   - Show loading spinner during save
   - Show success/error indicator after save
   - No page reload required

5. **UI/UX:**
   - Highlight editable cells on hover
   - Show pencil icon on hover for editable fields
   - Inline validation (e.g., price must be numeric)
   - Preserve current table layout and styling

**Current file:** `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`

---

## Current State Analysis

### ✅ Already Implemented

1. **Backend API Endpoints** (ProductsController.php)
   - `POST /products/{id}/field` - Update single field
   - `POST /products/bulk-status` - Bulk status update
   - CSRF protection via nonce verification
   - Input validation and sanitization

2. **Frontend Inline Editing** (products-table-inline-edit.js)
   - Click-to-edit functionality for category, tags, ribbon, price, status
   - Auto-save on blur
   - Loading, success, and error states
   - Auto-discount calculation for price
   - Bulk status change handlers

3. **CSS Styling** (products-table-inline-edit.css)
   - Editable cell hover effects
   - Pencil icon on hover
   - Editor controls styling
   - State indicators (loading, success, error)
   - Responsive design

### ❌ Issues Identified

1. **Bulk Actions Missing "Move to Draft"**
   - ProductsTable.php `get_bulk_actions()` has `publish` but not `move_to_draft`
   - JavaScript expects `move_to_draft` action

2. **"Add New" Not Implemented**
   - Shows TODO placeholder alert
   - No actual API endpoint for creating new categories/tags/ribbons

3. **Cell Data Attributes Missing**
   - Cells don't have `data-field` attributes
   - Makes JavaScript cell type detection less reliable

4. **Current Values Detection Issues**
   - `getCurrentValues()` tries to find hidden inputs that don't exist
   - Category/tag/ribbon IDs not stored in row

---

## Implementation Plan

### Phase 1: Fix Bulk Actions (Priority: HIGH)

**Files to Modify:**
- `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`

**Changes Required:**

1. Update `get_bulk_actions()` method to include "Move to Draft":
```php
public function get_bulk_actions(): array {
    $actions = [
        'publish'          => __( 'Publish', 'affiliate-product-showcase' ),
        'move_to_draft'    => __( 'Move to Draft', 'affiliate-product-showcase' ),  // ADD THIS
        'set_in_stock'     => __( 'Set In Stock', 'affiliate-product-showcase' ),
        'set_out_of_stock' => __( 'Set Out of Stock', 'affiliate-product-showcase' ),
        'set_featured'     => __( 'Set Featured', 'affiliate-product-showcase' ),
        'unset_featured'   => __( 'Unset Featured', 'affiliate-product-showcase' ),
        'reset_clicks'     => __( 'Reset Clicks', 'affiliate-product-showcase' ),
        'export_csv'       => __( 'Export to CSV', 'affiliate-product-showcase' ),
    ];

    return $actions;
}
```

**Testing:**
- Verify "Move to Draft" appears in bulk actions dropdown
- Verify selecting multiple products and clicking "Move to Draft" works
- Verify success notification appears
- Verify products are actually moved to draft status

---

### Phase 2: Add Cell Data Attributes (Priority: HIGH)

**Files to Modify:**
- `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`

**Changes Required:**

Update column methods to add `data-field` attributes:

1. `column_category()`:
```php
public function column_category( $item ): string {
    $categories = get_the_terms( $item->ID, Constants::TAX_CATEGORY );

    if ( empty( $categories ) || is_wp_error( $categories ) ) {
        return sprintf( '<span data-field="category" data-product-id="%d">—</span>', (int) $item->ID );
    }

    $badges = array_map( static function( $category ) {
        return sprintf(
            '<span class="aps-product-category" data-category-id="%s">%s <span aria-hidden="true">×</span></span>',
            esc_attr( (string) $category->term_id ),
            esc_html( $category->name )
        );
    }, $categories );

    return sprintf( '<div data-field="category" data-product-id="%d">%s</div>', (int) $item->ID, implode( ' ', $badges ) );
}
```

2. `column_tags()`:
```php
public function column_tags( $item ): string {
    $tags = get_the_terms( $item->ID, Constants::TAX_TAG );

    if ( empty( $tags ) || is_wp_error( $tags ) ) {
        return sprintf( '<span data-field="tags" data-product-id="%d">—</span>', (int) $item->ID );
    }

    $badges = array_map( static function( $tag ) {
        return sprintf(
            '<span class="aps-product-tag" data-tag-id="%s">%s <span aria-hidden="true">×</span></span>',
            esc_attr( (string) $tag->term_id ),
            esc_html( $tag->name )
        );
    }, $tags );

    return sprintf( '<div data-field="tags" data-product-id="%d">%s</div>', (int) $item->ID, implode( ' ', $badges ) );
}
```

3. `column_ribbon()`:
```php
public function column_ribbon( $item ): string {
    $ribbons = get_the_terms( $item->ID, Constants::TAX_RIBBON );
    
    if ( empty( $ribbons ) || is_wp_error( $ribbons ) ) {
        return sprintf( '<span data-field="ribbon" data-product-id="%d">—</span>', (int) $item->ID );
    }

    $badges = array_map( static function( $ribbon ) {
        return sprintf( 
            '<span class="aps-product-badge" data-ribbon-id="%s">%s</span>',
            esc_attr( (string) $ribbon->term_id ),
            esc_html( $ribbon->name ) 
        );
    }, $ribbons );

    return sprintf( '<div data-field="ribbon" data-product-id="%d">%s</div>', (int) $item->ID, implode( ' ', $badges ) );
}
```

4. `column_price()`:
```php
public function column_price( $item ): string {
    // ... existing price calculation code ...
    
    $output = sprintf(
        '<div data-field="price" data-product-id="%d" data-currency="%s" data-original-price="%s" data-price="%s">',
        (int) $item->ID,
        esc_attr( $currency ),
        esc_attr( (string) $original_price ),
        esc_attr( (string) $price )
    );
    
    $output .= sprintf(
        '<span class="aps-product-price">%s%s</span>',
        esc_html( $symbol ),
        esc_html( number_format_i18n( (float) $price, 2 ) )
    );

    if ( ! empty( $original_price ) && (float) $original_price > (float) $price ) {
        $discount = (int) round( ( ( (float) $original_price - (float) $price ) / (float) $original_price ) * 100 );
        $output .= sprintf(
            '<span class="aps-product-price-original">%s%s</span><span class="aps-product-price-discount">%d%% OFF</span>',
            esc_html( $symbol ),
            esc_html( number_format_i18n( (float) $original_price, 2 ) ),
            esc_html( $discount )
        );
    }

    $output .= '</div>';
    return $output;
}
```

5. `column_status()`:
```php
public function column_status( $item ): string {
    $status = (string) get_post_status( $item->ID );
    $label = strtoupper( $status );
    $class = 'aps-product-status';

    switch ( $status ) {
        case 'publish':
            $class .= ' aps-product-status-published';
            $label = 'PUBLISHED';
            break;
        case 'draft':
            $class .= ' aps-product-status-draft';
            $label = 'DRAFT';
            break;
        case 'trash':
            $class .= ' aps-product-status-trash';
            $label = 'TRASH';
            break;
        case 'pending':
        default:
            $class .= ' aps-product-status-pending';
            $label = strtoupper( $status );
            break;
    }

    return sprintf(
        '<span class="%s" data-field="status" data-product-id="%d" data-status="%s">%s</span>',
        esc_attr( $class ),
        (int) $item->ID,
        esc_attr( $status ),
        esc_html( $label )
    );
}
```

**Testing:**
- Verify all cells have `data-field` attributes
- Verify all cells have `data-product-id` attributes
- Verify price cell has `data-price`, `data-original-price`, `data-currency`
- Verify status cell has `data-status`
- Verify category/tag/ribbon badges have `data-*-id` attributes

---

### Phase 3: Update JavaScript to Use Data Attributes (Priority: HIGH)

**Files to Modify:**
- `wp-content/plugins/affiliate-product-showcase/assets/js/products-table-inline-edit.js`

**Changes Required:**

1. Simplify `getCellType()` function:
```javascript
function getCellType(cell) {
    // Check data attribute first (most reliable)
    if (cell.dataset.field) {
        return cell.dataset.field;
    }

    // Fallback to checking child elements
    const childWithField = cell.querySelector('[data-field]');
    if (childWithField) {
        return childWithField.dataset.field;
    }

    // Fallback to class-based detection
    const classes = cell.className.split(' ');
    for (const cls of classes) {
        if (cls.startsWith('column-')) {
            return cls.replace('column-', '');
        }
    }

    return null;
}
```

2. Simplify `getCurrentValues()` function:
```javascript
function getCurrentValues(cell, type) {
    const values = [];
    
    switch (type) {
        case 'category':
            const categoryBadges = cell.querySelectorAll('[data-category-id]');
            categoryBadges.forEach(badge => {
                values.push(badge.dataset.categoryId);
            });
            break;

        case 'tags':
            const tagBadges = cell.querySelectorAll('[data-tag-id]');
            tagBadges.forEach(badge => {
                values.push(badge.dataset.tagId);
            });
            break;

        case 'ribbon':
            const ribbonBadges = cell.querySelectorAll('[data-ribbon-id]');
            ribbonBadges.forEach(badge => {
                values.push(badge.dataset.ribbonId);
            });
            break;
    }

    return values;
}
```

3. Simplify `getStatusValue()` function:
```javascript
function getStatusValue(cell) {
    const statusSpan = cell.querySelector('[data-status]');
    if (!statusSpan) return 'draft';
    
    return statusSpan.dataset.status || 'draft';
}
```

4. Simplify `getCurrency()` function:
```javascript
function getCurrency(row) {
    const priceCell = row.querySelector('.column-price [data-currency]');
    return priceCell ? priceCell.dataset.currency : '$';
}
```

5. Simplify `createPriceEditor()` to use data attributes:
```javascript
function createPriceEditor(cell, productId) {
    const priceElement = cell.querySelector('[data-price]');
    const originalPriceElement = cell.querySelector('[data-original-price]');
    const currencyElement = cell.querySelector('[data-currency]');
    
    const currentPrice = priceElement ? parseFloat(priceElement.dataset.price) : 0;
    const originalPrice = originalPriceElement ? parseFloat(originalPriceElement.dataset.originalPrice) : null;
    const currency = currencyElement ? currencyElement.dataset.currency : '$';

    // ... rest of the function remains the same
}
```

**Testing:**
- Verify inline editing works correctly with data attributes
- Verify current values are properly detected
- Verify all editors open and close correctly
- Verify save functionality works

---

### Phase 4: Implement "Add New" Functionality (Priority: MEDIUM)

**Files to Modify:**
- `wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php` (Create if not exists)
- `wp-content/plugins/affiliate-product-showcase/src/Rest/TagsController.php` (Update if exists)
- `wp-content/plugins/affiliate-product-showcase/src/Rest/RibbonsController.php` (Update if exists)
- `wp-content/plugins/affiliate-product-showcase/assets/js/products-table-inline-edit.js`

**Backend Changes:**

1. Ensure CategoriesController has `create()` endpoint:
```php
public function create( WP_REST_Request $request ): WP_REST_Response {
    $name = $request->get_param( 'name' );
    
    if ( empty( $name ) ) {
        return $this->respond( [
            'message' => __( 'Name is required.', 'affiliate-product-showcase' ),
            'code'    => 'missing_name',
        ], 400 );
    }

    $term = wp_insert_term( $name, Constants::TAX_CATEGORY );
    
    if ( is_wp_error( $term ) ) {
        return $this->respond( [
            'message' => $term->get_error_message(),
            'code'    => 'create_failed',
        ], 400 );
    }

    return $this->respond( [
        'message' => __( 'Category created successfully.', 'affiliate-product-showcase' ),
        'code'    => 'success',
        'category' => [
            'id'   => $term['term_id'],
            'name' => $name,
        ],
    ], 201 );
}
```

2. Ensure TagsController has `create()` endpoint (similar structure)

3. Ensure RibbonsController has `create()` endpoint (similar structure)

**Frontend Changes:**

Update `showAddNewDialog()` function:
```javascript
async function showAddNewDialog(cell, type, productId) {
    const name = prompt(`Enter new ${type} name:`);
    if (!name || !name.trim()) {
        // Restore original editor
        cell.innerHTML = '';
        startEditing(cell, type, productId);
        return;
    }

    showLoading(cell);

    try {
        let endpoint;
        switch (type) {
            case 'category':
                endpoint = `${config.apiBase}/categories`;
                break;
            case 'tags':
                endpoint = `${config.apiBase}/tags`;
                break;
            case 'ribbon':
                endpoint = `${config.apiBase}/ribbons`;
                break;
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                name: name.trim()
            })
        });

        const data = await response.json();

        if (data.code === 'success') {
            // Clear cache to force reload
            cache[type === 'ribbon' ? 'ribbons' : type + 's'] = null;
            
            // Show success message
            showSuccess(cell);
            
            // Re-open editor with new option selected
            setTimeout(() => {
                cell.innerHTML = '';
                startEditing(cell, type, productId);
                
                // Select the newly created item
                const editor = cell.querySelector('.aps-inline-editor');
                if (editor) {
                    const select = editor.querySelector('select');
                    const newItemId = data[type]?.id || data.category?.id;
                    if (select && newItemId) {
                        select.value = newItemId;
                        // Auto-save the new selection
                        setTimeout(() => saveField(type, productId, newItemId, cell), 100);
                    }
                }
            }, 500);
        } else {
            showError(cell, data.message || 'Failed to create');
            setTimeout(() => {
                cell.innerHTML = '';
                startEditing(cell, type, productId);
            }, 2000);
        }
    } catch (error) {
        console.error(`[APS Inline Edit] Failed to create ${type}:`, error);
        showError(cell, 'Network error');
        setTimeout(() => {
            cell.innerHTML = '';
            startEditing(cell, type, productId);
        }, 2000);
    }
}
```

**Testing:**
- Test creating new category via inline editor
- Test creating new tag via inline editor
- Test creating new ribbon via inline editor
- Verify newly created items appear in dropdown
- Verify newly created item is selected automatically
- Verify cache is cleared after creation

---

### Phase 5: Improve Error Handling & User Feedback (Priority: MEDIUM)

**Files to Modify:**
- `wp-content/plugins/affiliate-product-showcase/assets/js/products-table-inline-edit.js`
- `wp-content/plugins/affiliate-product-showcase/assets/css/products-table-inline-edit.css`

**Changes Required:**

1. Add toast notification system to JavaScript:
```javascript
/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.querySelector('.aps-toast-notification');
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast
    const toast = document.createElement('div');
    toast.className = `aps-toast-notification aps-toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        toast.classList.add('aps-toast-hiding');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
```

2. Update bulk action handler to use toast:
```javascript
async function handleBulkStatusChange(productIds, action) {
    const targetStatus = action === 'publish' ? 'publish' : 'draft';
    const actionName = action === 'publish' ? 'publish' : 'move to draft';
    
    if (!confirm(`Are you sure you want to ${actionName} ${productIds.length} product(s)?`)) {
        return;
    }

    showBulkLoading();

    try {
        const response = await fetch(`${config.apiBase}/products/bulk-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce
            },
            body: JSON.stringify({
                product_ids: productIds,
                status: targetStatus
            })
        });

        const data = await response.json();

        if (data.code === 'success') {
            showToast(data.message, 'success');
            location.reload();
        } else if (data.code === 'partial_success') {
            showToast(
                `${data.message}\n\nFailed: ${data.failed_count} products`,
                'warning'
            );
            location.reload();
        } else {
            showToast('Failed to update status: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('[APS Inline Edit] Bulk status error:', error);
        showToast('Network error occurred', 'error');
    } finally {
        hideBulkLoading();
    }
}
```

**CSS Additions:**

```css
/* Toast Notifications */
.aps-toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 99999;
    animation: aps-slide-in 0.3s ease-out;
}

@keyframes aps-slide-in {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.aps-toast-success {
    background-color: #00a32a;
}

.aps-toast-error {
    background-color: #dc3232;
}

.aps-toast-warning {
    background-color: #d63638;
}

.aps-toast-hiding {
    animation: aps-slide-out 0.3s ease-out forwards;
}

@keyframes aps-slide-out {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(100px);
        opacity: 0;
    }
}

/* Multiple toast support */
.aps-toast-notification:nth-child(n+2) {
    bottom: calc(20px + (n - 1) * 60px);
}
```

**Testing:**
- Verify toast notifications appear on success/error
- Verify multiple toasts stack properly
- Verify toasts auto-hide after 3 seconds
- Verify bulk actions show toast notifications

---

### Phase 6: Comprehensive Testing & Validation (Priority: HIGH)

**Test Checklist:**

1. **Inline Editing - Category**
   - [ ] Click category cell to edit
   - [ ] Dropdown shows all categories
   - [ ] Select different category
   - [ ] Auto-save on blur works
   - [ ] Success indicator shows
   - [ ] Category badge updates correctly
   - [ ] "Add New" creates new category
   - [ ] New category appears in dropdown
   - [ ] New category is selected automatically

2. **Inline Editing - Tags**
   - [ ] Click tags cell to edit
   - [ ] Multi-select shows all tags
   - [ ] Select/deselect tags
   - [ ] Auto-save on blur works
   - [ ] Success indicator shows
   - [ ] Tag badges update correctly
   - [ ] "Add New" creates new tag
   - [ ] New tag appears in multi-select
   - [ ] New tag is selected automatically

3. **Inline Editing - Ribbon**
   - [ ] Click ribbon cell to edit
   - [ ] Dropdown shows all ribbons + "None"
   - [ ] Select different ribbon
   - [ ] Select "None" to remove ribbon
   - [ ] Auto-save on blur works
   - [ ] Success indicator shows
   - [ ] Ribbon badge updates correctly
   - [ ] "Add New" creates new ribbon
   - [ ] New ribbon appears in dropdown
   - [ ] New ribbon is selected automatically

4. **Inline Editing - Price**
   - [ ] Click price cell to edit
   - [ ] Input shows current price
   - [ ] Currency symbol displayed
   - [ ] Enter new price
   - [ ] Discount preview updates in real-time
   - [ ] Auto-save on blur works
   - [ ] Success indicator shows
   - [ ] Price updates correctly
   - [ ] Original price preserved
   - [ ] Discount badge updates correctly
   - [ ] Price validation works (negative numbers rejected)

5. **Inline Editing - Status**
   - [ ] Click status cell to edit
   - [ ] Dropdown shows Published/Draft options
   - [ ] Select different status
   - [ ] Auto-save on blur works
   - [ ] Success indicator shows
   - [ ] Status badge updates correctly
   - [ ] Status color updates correctly

6. **Bulk Actions**
   - [ ] Select multiple products
   - [ ] "Publish" action moves drafts to published
   - [ ] "Move to Draft" action moves published to draft
   - [ ] Success notification appears
   - [ ] Page reloads with updated status
   - [ ] Partial success shows warning notification
   - [ ] Failed products listed in error message

7. **UI/UX**
   - [ ] Editable cells highlight on hover
   - [ ] Pencil icon appears on hover
   - [ ] Pencil icon disappears when editing
   - [ ] Loading spinner appears during save
   - [ ] Success checkmark appears after save
   - [ ] Error message appears on failure
   - [ ] Cell content restored on error
   - [ ] Pressing Escape cancels edit
   - [ ] Clicking outside saves edit
   - [ ] Pressing Enter saves edit (for price)

8. **Accessibility**
   - [ ] All editors are keyboard accessible
   - [ ] Focus indicators are visible
   - [ ] ARIA labels are appropriate
   - [ ] Screen reader announces changes
   - [ ] Keyboard navigation works

9. **Cross-Browser**
   - [ ] Works in Chrome
   - [ ] Works in Firefox
   - [ ] Works in Safari
   - [ ] Works in Edge

10. **Responsive**
    - [ ] Works on desktop
    - [ ] Works on tablet
    - [ ] Works on mobile

---

## Implementation Timeline

### Day 1: Critical Fixes
- [ ] Phase 1: Fix Bulk Actions (1 hour)
- [ ] Phase 2: Add Cell Data Attributes (2 hours)
- [ ] Phase 3: Update JavaScript (1 hour)
- [ ] Testing: Basic inline editing (1 hour)

**Total: 5 hours**

### Day 2: Enhancements
- [ ] Phase 4: Implement "Add New" functionality (3 hours)
- [ ] Phase 5: Improve error handling (2 hours)
- [ ] Testing: "Add New" functionality (1 hour)
- [ ] Testing: Error handling (1 hour)

**Total: 7 hours**

### Day 3: Comprehensive Testing
- [ ] Phase 6: Full test suite (4 hours)
- [ ] Bug fixes (2 hours)
- [ ] Final verification (2 hours)

**Total: 8 hours**

**Grand Total: 20 hours (2.5 days)**

---

## Risk Assessment

### Low Risk
- Adding cell data attributes
- Updating JavaScript to use data attributes
- CSS styling for toast notifications

### Medium Risk
- Fixing bulk actions
- Implementing "Add New" functionality
- Error handling improvements

### Mitigation Strategies
1. Create backup branch before starting
2. Test each phase independently
3. Use feature flags for new functionality
4. Have rollback plan ready
5. Test on staging environment first

---

## Success Criteria

### Must Have (MVP)
- [x] All inline editing columns work correctly
- [ ] Bulk "Publish" and "Move to Draft" work
- [ ] Auto-save on blur works
- [ ] Loading/success/error states work
- [ ] UI/UX requirements met

### Should Have
- [ ] "Add New" functionality for categories/tags/ribbons
- [ ] Toast notifications for better feedback
- [ ] Auto-discount calculation works
- [ ] Inline validation works

### Nice to Have
- [ ] Keyboard shortcuts (Enter to save, Escape to cancel)
- [ ] Undo functionality
- [ ] Batch edit multiple cells
- [ ] Drag-and-drop reordering

---

## Notes

1. **Current Implementation Status**: Much of the inline editing infrastructure is already in place. This plan focuses on fixing gaps and adding missing features.

2. **Data Attributes**: Adding data attributes to cells will make JavaScript more robust and easier to maintain.

3. **API Endpoints**: Verify that CategoriesController, TagsController, and RibbonsController have create endpoints before implementing Phase 4.

4. **Testing Strategy**: Test each phase independently before moving to the next. Use browser dev tools for debugging.

5. **Performance**: The current implementation is efficient. No performance issues expected with the planned changes.

6. **Accessibility**: Ensure all changes maintain or improve accessibility. Use ARIA labels and keyboard support.

7. **Browser Compatibility**: Test in all major browsers before deploying to production.

8. **Rollback Plan**: If issues arise, can revert to previous version by restoring from backup branch.

---

## References

- **Files Modified**:
  - `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php`
  - `wp-content/plugins/affiliate-product-showcase/assets/js/products-table-inline-edit.js`
  - `wp-content/plugins/affiliate-product-showcase/assets/css/products-table-inline-edit.css`

- **Related Files**:
  - `wp-content/plugins/affiliate-product-showcase/src/Rest/ProductsController.php`
  - `wp-content/plugins/affiliate-product-showcase/src/Rest/CategoriesController.php`
  - `wp-content/plugins/affiliate-product-showcase/src/Rest/TagsController.php`
  - `wp-content/plugins/affiliate-product-showcase/src/Rest/RibbonsController.php`

- **Documentation**:
  - WordPress REST API Handbook
  - WP_List_Table documentation
  - JavaScript Event Handling best practices

---

**Created:** 2026-01-27
**Status:** Ready for Implementation
**Priority:** HIGH