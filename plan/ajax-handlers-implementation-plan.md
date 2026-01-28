# AJAX Handlers Implementation Plan

**Date:** January 28, 2026  
**Task:** Implement missing AJAX handlers for product table  
**Priority:** CRITICAL - Features currently non-functional  
**Estimated Time:** 45-60 minutes

---

## Executive Summary

**Issue:** JavaScript makes AJAX calls to actions that are not registered in PHP

**Impact:**
- ❌ Bulk trash functionality fails
- ❌ Single product trash fails
- ❌ Quick edit save fails
- Users see errors or no feedback

**Solution:** Register 3 missing AJAX handlers in AjaxHandler.php

---

## Current State Analysis

### JavaScript AJAX Calls (admin-products.js)

| Action Name | Purpose | File Location |
|------------|---------|---------------|
| `aps_bulk_trash_products` | Bulk trash multiple products | admin-products.js ~340 |
| `aps_trash_product` | Trash single product | admin-products.js ~378 |
| `aps_quick_edit_product` | Quick edit save | admin-products.js ~446 |

### PHP AJAX Handlers (AjaxHandler.php) - Current

| Action Name | Status | Notes |
|------------|--------|-------|
| `aps_filter_products` | ✅ Registered | Not used by admin-products.js |
| `aps_bulk_action` | ✅ Registered | Generic handler, not specific to trash |
| `aps_update_status` | ✅ Registered | Not used by admin-products.js |
| `aps_check_links` | ✅ Registered | Not used by admin-products.js |

### Missing Handlers

| Action Name | Status | Impact |
|------------|--------|---------|
| `aps_bulk_trash_products` | ❌ NOT REGISTERED | Bulk trash fails |
| `aps_trash_product` | ❌ NOT REGISTERED | Single trash fails |
| `aps_quick_edit_product` | ❌ NOT REGISTERED | Quick edit fails |

---

## Implementation Plan

### Phase 1: aps_bulk_trash_products Handler

**Purpose:** Handle bulk trash action for multiple products

**Request Format (from JavaScript):**
```javascript
$.ajax({
    url: apsProductsData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'aps_bulk_trash_products',
        nonce: apsProductsData.nonce,
        product_ids: [1, 2, 3, ...]  // Array of product IDs
    },
    success: function(response) {
        // Handle success
    }
});
```

**Response Format:**
```json
{
    "success": true,
    "data": {
        "message": "2 products moved to trash.",
        "count": 2,
        "trashed_ids": [1, 2]
    }
}
```

**Implementation Details:**
1. Verify nonce: `aps_products_nonce`
2. Check permissions: `manage_options`
3. Validate product IDs (must be integers, post type = aps_product)
4. Use `wp_trash_post()` for each product
5. Count successful trashes
6. Return success/error response
7. Clear product cache if applicable

**Error Handling:**
- Invalid nonce: 403 error
- Insufficient permissions: 403 error
- Invalid product IDs: Return partial success with errors
- Trashing failures: Return partial success with error count

---

### Phase 2: aps_trash_product Handler

**Purpose:** Handle single product trash action

**Request Format (from JavaScript):**
```javascript
$.ajax({
    url: apsProductsData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'aps_trash_product',
        nonce: apsProductsData.nonce,
        product_id: 123  // Single product ID
    },
    success: function(response) {
        // Handle success
    }
});
```

**Response Format:**
```json
{
    "success": true,
    "data": {
        "message": "Product moved to trash.",
        "product_id": 123
    }
}
```

**Implementation Details:**
1. Verify nonce: `aps_products_nonce`
2. Check permissions: `manage_options`
3. Validate product ID (must be integer, post type = aps_product)
4. Use `wp_trash_post()`
5. Return success/error response
6. Clear product cache if applicable

**Error Handling:**
- Invalid nonce: 403 error
- Insufficient permissions: 403 error
- Invalid product ID: 400 error
- Trashing failure: 500 error with message

---

### Phase 3: aps_quick_edit_product Handler

**Purpose:** Handle quick edit save for product

**Request Format (from JavaScript):**
```javascript
$.ajax({
    url: apsProductsData.ajaxUrl,
    type: 'POST',
    data: {
        action: 'aps_quick_edit_product',
        nonce: apsProductsData.nonce,
        product_id: 123,
        data: {
            title: 'Updated Title',
            price: '29.99',
            original_price: '49.99',
            status: 'publish',
            featured: '1',
            ribbon: 'sale'
        }
    },
    success: function(response) {
        // Handle success
    }
});
```

**Response Format:**
```json
{
    "success": true,
    "data": {
        "message": "Product updated successfully.",
        "product_id": 123,
        "updated_fields": {
            "title": "Updated Title",
            "price": "29.99",
            "original_price": "49.99",
            "status": "publish",
            "featured": true,
            "ribbon": "sale"
        }
    }
}
```

**Implementation Details:**
1. Verify nonce: `aps_products_nonce`
2. Check permissions: `manage_options`
3. Validate product ID (must be integer, post type = aps_product)
4. Sanitize and validate all fields
5. Update post title and status
6. Update meta fields:
   - `_aps_price`
   - `_aps_original_price`
   - `_aps_featured`
   - `_aps_ribbon`
7. Return success/error response with updated data
8. Clear product cache if applicable

**Field Validation:**
- `title`: Required, max 200 chars
- `price`: Required, positive number
- `original_price`: Optional, positive number, >= price
- `status`: Required, one of: publish, draft, pending
- `featured`: Optional, boolean
- `ribbon`: Optional, string

**Error Handling:**
- Invalid nonce: 403 error
- Insufficient permissions: 403 error
- Invalid product ID: 400 error
- Validation errors: 400 error with field-specific messages
- Update failure: 500 error

---

## Security Considerations

### 1. Nonce Verification
- Use `aps_products_nonce` (from Enqueue.php localization)
- Verify nonce for all actions
- Return 403 on invalid nonce

### 2. Permission Checks
- Require `manage_options` capability
- Verify user can edit products
- Return 403 on insufficient permissions

### 3. Input Validation
- Sanitize all user input
- Validate data types (integers, strings, booleans)
- Check post type is `aps_product`
- Validate field values against allowed ranges

### 4. Output Escaping
- Escape all output in responses
- Use `wp_send_json_success()` and `wp_send_json_error()`
- Never output raw user data

---

## Code Structure

### Handler Method Signature

```php
/**
 * Handle [action description]
 *
 * @return void
 */
public function handle[ActionName](): void {
    // 1. Verify nonce
    // 2. Check permissions
    // 3. Validate input
    // 4. Process request
    // 5. Send response
}
```

### Registration

```php
private function registerAjaxHandlers(): void {
    // ... existing handlers ...
    
    // New handlers
    add_action('wp_ajax_aps_bulk_trash_products', [$this, 'handleBulkTrashProducts']);
    add_action('wp_ajax_aps_trash_product', [$this, 'handleTrashProduct']);
    add_action('wp_ajax_aps_quick_edit_product', [$this, 'handleQuickEditProduct']);
}
```

**Note:** No `nopriv` actions needed - only logged-in admins can manage products.

---

## Testing Checklist

### Bulk Trash Handler
- [ ] Trashes multiple products successfully
- [ ] Returns correct success count
- [ ] Returns correct message
- [ ] Handles invalid nonce (403 error)
- [ ] Handles insufficient permissions (403 error)
- [ ] Handles invalid product IDs (partial success)
- [ ] Handles non-existent product IDs (partial success)

### Single Trash Handler
- [ ] Trashes single product successfully
- [ ] Returns correct success message
- [ ] Returns correct product_id
- [ ] Handles invalid nonce (403 error)
- [ ] Handles insufficient permissions (403 error)
- [ ] Handles invalid product ID (400 error)
- [ ] Handles non-existent product (500 error)

### Quick Edit Handler
- [ ] Updates title successfully
- [ ] Updates price successfully
- [ ] Updates original_price successfully
- [ ] Updates status successfully
- [ ] Updates featured successfully
- [ ] Updates ribbon successfully
- [ ] Returns updated data in response
- [ ] Handles invalid nonce (403 error)
- [ ] Handles insufficient permissions (403 error)
- [ ] Handles invalid product ID (400 error)
- [ ] Handles validation errors (400 error with details)
- [ ] Handles invalid price (validation error)
- [ ] Handles original_price < price (validation error)

### Integration Testing
- [ ] Test from products page UI
- [ ] Test bulk trash from UI
- [ ] Test single trash from UI
- [ ] Test quick edit from UI
- [ ] Verify no JavaScript console errors
- [ ] Verify toast notifications appear
- [ ] Verify table refreshes after action

---

## Implementation Order

1. **Phase 1:** Add `aps_bulk_trash_products` handler (15 min)
2. **Phase 2:** Add `aps_trash_product` handler (10 min)
3. **Phase 3:** Add `aps_quick_edit_product` handler (20 min)
4. **Phase 4:** Test all handlers (15 min)
5. **Phase 5:** Document changes (5 min)

**Total Estimated Time:** 65 minutes

---

## Success Criteria

- ✅ All 3 handlers registered successfully
- ✅ All handlers pass security checks
- ✅ All handlers return correct response format
- ✅ All handlers handle errors properly
- ✅ Integration tests pass
- ✅ No JavaScript errors in console
- ✅ Features work from UI

---

## Risks and Mitigation

### Risk 1: Breaking Existing Functionality
**Mitigation:** Only add new handlers, don't modify existing ones

### Risk 2: Performance Issues
**Mitigation:** Use WordPress core functions, optimize queries, limit batch sizes

### Risk 3: Race Conditions
**Mitigation:** Use WordPress transactions where applicable, validate before processing

### Risk 4: Data Loss
**Mitigation:** Use WordPress trash (not delete), validate before updating, backup option

---

## Rollback Plan

If issues arise:
1. Comment out new handler registrations
2. Restore backup of AjaxHandler.php
3. Test existing functionality
4. Report issues

---

## Post-Implementation Tasks

1. Update documentation to reflect new handlers
2. Update API documentation
3. Add integration tests to test suite
4. Update CHANGELOG.md
5. Create user-facing release notes

---

**Status:** Ready for Implementation  
**Next Action:** Implement handlers in AjaxHandler.php  
**Priority:** CRITICAL