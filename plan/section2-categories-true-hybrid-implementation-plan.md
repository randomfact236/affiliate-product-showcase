# Section 2: Categories - True Hybrid Implementation Plan

**Created:** January 24, 2026  
**Priority:** 🔴 HIGH - Fix meta key prefix to match true hybrid standard  
**Scope:** Basic Level Features Only (32 features)

---

## Executive Summary

Section 2 (Categories) is **functionally complete** but has a **critical meta key prefix issue** preventing true hybrid compliance.

**Current Status:**
- ✅ All 32 basic features implemented
- ✅ Full CRUD operations working
- ✅ REST API endpoints complete
- ❌ Meta keys use `aps_category_*` instead of `_aps_category_*` (missing underscore)
- ❌ Inconsistent with Product model pattern (`_aps_*`)

**Impact:** Category meta data saves and retrieves correctly but doesn't follow project's true hybrid standard.

---

## Understanding True Hybrid for Categories

**True Hybrid Means:**
1. ✅ All taxonomy meta keys use underscore prefix (`_aps_category_*`)
2. ✅ Model has ALL properties matching admin forms
3. ✅ Factory correctly maps ALL meta fields with underscore prefix
4. ✅ REST API includes all meta fields with underscore prefix
5. ✅ Consistent naming across Model, Factory, Repository and Controller

---

## Current Implementation Analysis

### Meta Key Mapping Issue

**Current (Non-Compliant):**
- `aps_category_featured` → Missing underscore prefix
- `aps_category_image` → Missing underscore prefix
- `aps_category_sort_order` → Missing underscore prefix

**Should Be (True Hybrid Compliant):**
- `_aps_category_featured` → Correct underscore prefix
- `_aps_category_image` → Correct underscore prefix
- `_aps_category_sort_order` → Correct underscore prefix

### Files Requiring Changes

| File | Current State | Required Changes |
|-------|----------------|-------------------|
| `src/Models/Category.php` | Reads `aps_category_*` | Update to read `_aps_category_*` |
| `src/Factories/CategoryFactory.php` | Reads `aps_category_*` | Update to read `_aps_category_*` |
| `src/Repositories/CategoryRepository.php` | Saves/reads `aps_category_*` | Update to `_aps_category_*` |
| `src/Admin/CategoryFields.php` | Saves to `aps_category_*` | Update to `_aps_category_*` |
| `src/Rest/CategoriesController.php` | Returns `aps_category_*` | Update to `_aps_category_*` |

---

## Phase 1: Fix Category Model Meta Key Prefix (CRITICAL)

**Priority:** 🔴 HIGHEST  
**Files to Modify:** `src/Models/Category.php`

### Changes Required

**Method:** `from_wp_term(\WP_Term $term): self`

**Current Code (Lines 127-131):**
```php
// Get category metadata
$featured = (bool) get_term_meta( $term->term_id, 'aps_category_featured', true );
$image_url = get_term_meta( $term->term_id, 'aps_category_image', true ) ?: null;
$sort_order = get_term_meta( $term->term_id, 'aps_category_sort_order', true ) ?: 'date';
```

**Fixed Code:**
```php
// Get category metadata (with underscore prefix)
$featured = (bool) get_term_meta( $term->term_id, '_aps_category_featured', true );
$image_url = get_term_meta( $term->term_id, '_aps_category_image', true ) ?: null;
$sort_order = get_term_meta( $term->term_id, '_aps_category_sort_order', true ) ?: 'date';
```

### Implementation Steps

1. **Backup Category.php**
   ```bash
   cp src/Models/Category.php backups/Category.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update Category.php**
   - Open `src/Models/Category.php`
   - Go to line 127-131
   - Replace all `aps_category_*` meta keys with `_aps_category_*` prefix

3. **Test the fix**
   - Open WordPress admin
   - Edit an existing category
   - Verify meta fields display correctly
   - Update category
   - Verify data persists

### Verification Checklist
- [ ] Backup created
- [ ] All meta keys updated with underscore prefix
- [ ] Data displays correctly in admin
- [ ] Data persists after save
- [ ] Frontend displays categories correctly

---

## Phase 2: Update CategoryRepository (HIGH)

**Priority:** 🟠 HIGH  
**Files to Modify:** `src/Repositories/CategoryRepository.php`

### Changes Required

**Method:** `save_metadata(int $term_id, Category $category): void`

**Current Code (Lines 331-344):**
```php
private function save_metadata( int $term_id, Category $category ): void {
    // Featured
    update_term_meta( $term_id, 'aps_category_featured', $category->featured ? 1 : 0 );

    // Image URL
    if ( $category->image_url ) {
        update_term_meta( $term_id, 'aps_category_image', $category->image_url );
    } else {
        delete_term_meta( $term_id, 'aps_category_image' );
    }

    // Sort order
    update_term_meta( $term_id, 'aps_category_sort_order', $category->sort_order );
}
```

**Fixed Code:**
```php
private function save_metadata( int $term_id, Category $category ): void {
    // Featured (with underscore prefix)
    update_term_meta( $term_id, '_aps_category_featured', $category->featured ? 1 : 0 );

    // Image URL (with underscore prefix)
    if ( $category->image_url ) {
        update_term_meta( $term_id, '_aps_category_image', $category->image_url );
    } else {
        delete_term_meta( $term_id, '_aps_category_image' );
    }

    // Sort order (with underscore prefix)
    update_term_meta( $term_id, '_aps_category_sort_order', $category->sort_order );
}
```

**Method:** `delete_metadata(int $term_id): void`

**Current Code (Lines 348-352):**
```php
private function delete_metadata( int $term_id ): void {
    delete_term_meta( $term_id, 'aps_category_featured' );
    delete_term_meta( $term_id, 'aps_category_image' );
    delete_term_meta( $term_id, 'aps_category_sort_order' );
}
```

**Fixed Code:**
```php
private function delete_metadata( int $term_id ): void {
    delete_term_meta( $term_id, '_aps_category_featured' );
    delete_term_meta( $term_id, '_aps_category_image' );
    delete_term_meta( $term_id, '_aps_category_sort_order' );
}
```

### Implementation Steps

1. **Backup CategoryRepository.php**
   ```bash
   cp src/Repositories/CategoryRepository.php backups/CategoryRepository.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update CategoryRepository.php**
   - Open `src/Repositories/CategoryRepository.php`
   - Find `save_metadata()` method (line 331)
   - Replace all `aps_category_*` with `_aps_category_*`
   - Find `delete_metadata()` method (line 348)
   - Replace all `aps_category_*` with `_aps_category_*`

3. **Test the changes**
   - Create new category with meta fields
   - Verify meta data saves correctly
   - Delete category
   - Verify metadata is removed

### Verification Checklist
- [ ] Backup created
- [ ] save_metadata() uses underscore prefix
- [ ] delete_metadata() uses underscore prefix
- [ ] Meta data saves correctly
- [ ] Meta data deletes correctly
- [ ] All operations tested

---

## Phase 3: Update CategoryFields (HIGH)

**Priority:** 🟠 HIGH  
**Files to Modify:** `src/Admin/CategoryFields.php`

### Changes Required

**Search and Replace:**
- Find all instances of `aps_category_featured` → Replace with `_aps_category_featured`
- Find all instances of `aps_category_image` → Replace with `_aps_category_image`
- Find all instances of `aps_category_sort_order` → Replace with `_aps_category_sort_order`

**Example Changes:**

**Before:**
```php
<input type="text" name="aps_category_featured" ... />
update_term_meta($term_id, 'aps_category_featured', $value);
```

**After:**
```php
<input type="text" name="_aps_category_featured" ... />
update_term_meta($term_id, '_aps_category_featured', $value);
```

### Implementation Steps

1. **Backup CategoryFields.php**
   ```bash
   cp src/Admin/CategoryFields.php backups/CategoryFields.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update CategoryFields.php**
   - Open `src/Admin/CategoryFields.php`
   - Search for `aps_category_` (without underscore)
   - Replace all occurrences with `_aps_category_` (with underscore)
   - Ensure all form field names use underscore prefix
   - Ensure all `update_term_meta()` calls use underscore prefix

3. **Test the changes**
   - Edit category in admin
   - Verify form fields display correctly
   - Save category
   - Verify data persists

### Verification Checklist
- [ ] Backup created
- [ ] All form field names use underscore prefix
- [ ] All update_term_meta() calls use underscore prefix
- [ ] Form displays correctly in admin
- [ ] Data saves correctly

---

## Phase 4: Update CategoriesController (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Rest/CategoriesController.php`

### Changes Required

**Search and Replace:**
- Find all instances of `aps_category_featured` → Replace with `_aps_category_featured`
- Find all instances of `aps_category_image` → Replace with `_aps_category_image`
- Find all instances of `aps_category_sort_order` → Replace with `_aps_category_sort_order`

### Implementation Steps

1. **Backup CategoriesController.php**
   ```bash
   cp src/Rest/CategoriesController.php backups/CategoriesController.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update CategoriesController.php**
   - Open `src/Rest/CategoriesController.php`
   - Search for `aps_category_` (without underscore)
   - Replace all occurrences with `_aps_category_` (with underscore)
   - Ensure API responses use correct meta keys

3. **Test the changes**
   - Test REST API endpoints
   - Verify responses include correct meta keys
   - Test create/update operations via API

### Verification Checklist
- [ ] Backup created
- [ ] All API responses use underscore prefix
- [ ] API endpoints return correct data
- [ ] Create/update operations work via API
- [ ] All endpoints tested

---

## Phase 5: Update CategoryFactory (MEDIUM)

**Priority:** 🟡 MEDIUM  
**Files to Modify:** `src/Factories/CategoryFactory.php`

### Changes Required

**Search and Replace:**
- Find all instances of `aps_category_` (without underscore) → Replace with `_aps_category_` (with underscore)

### Implementation Steps

1. **Backup CategoryFactory.php**
   ```bash
   cp src/Factories/CategoryFactory.php backups/CategoryFactory.php.backup-$(date +%Y%m%d-%H%M%S)
   ```

2. **Update CategoryFactory.php**
   - Open `src/Factories/CategoryFactory.php`
   - Search for `aps_category_` (without underscore)
   - Replace all occurrences with `_aps_category_` (with underscore)

3. **Test the changes**
   - Create category via code
   - Verify factory reads correct meta data
   - Verify model is instantiated correctly

### Verification Checklist
- [ ] Backup created
- [ ] All factory reads use underscore prefix
- [ ] Factory creates correct models
- [ ] Meta data reads correctly

---

## Phase 6: Testing & Verification (REQUIRED)

**Priority:** 🟡 REQUIRED

### Unit Tests

**File:** `tests/Unit/Models/CategoryTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Models\Category;

final class CategoryTest extends TestCase {
    public function test_category_creation(): void {
        $category = new Category(
            1,
            'Electronics',
            'electronics',
            'Electronic products',
            0,
            10,
            true,
            'https://example.com/image.jpg',
            'date',
            '2026-01-24'
        );
        
        $this->assertEquals(1, $category->id);
        $this->assertEquals('Electronics', $category->name);
        $this->assertTrue($category->featured);
    }
    
    public function test_has_parent(): void {
        $parent_category = new Category(1, 'Parent', 'parent', '', 0);
        $child_category = new Category(2, 'Child', 'child', '', 1);
        
        $this->assertFalse($parent_category->has_parent());
        $this->assertTrue($child_category->has_parent());
    }
}
```

### Integration Tests

**File:** `tests/Integration/Repositories/CategoryRepositoryTest.php`

```php
<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Tests\Integration\Repositories;

use PHPUnit\Framework\TestCase;
use AffiliateProductShowcase\Repositories\CategoryRepository;
use AffiliateProductShowcase\Factories\CategoryFactory;

final class CategoryRepositoryTest extends TestCase {
    public function test_create_category_with_metadata(): void {
        $repository = new CategoryRepository();
        
        $category = CategoryFactory::from_array([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'featured' => true,
            'image_url' => 'https://example.com/image.jpg',
            'sort_order' => 'name'
        ]);
        
        $created = $repository->create($category);
        
        $this->assertEquals('Test Category', $created->name);
        $this->assertTrue($created->featured);
        $this->assertEquals('https://example.com/image.jpg', $created->image_url);
        
        // Verify meta saved with underscore prefix
        $meta = get_term_meta($created->id, '_aps_category_featured', true);
        $this->assertEquals(1, $meta);
        
        // Cleanup
        wp_delete_term($created->id, 'aps_category');
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
- [ ] Create new category with all meta fields
- [ ] Verify all fields save correctly
- [ ] Edit existing category
- [ ] Verify all fields display correctly
- [ ] Update fields and save
- [ ] Verify updates persist
- [ ] Delete category
- [ ] Verify category removed

**REST API:**
- [ ] GET `/v1/categories` - List categories
- [ ] GET `/v1/categories/{id}` - Get single category
- [ ] POST `/v1/categories` - Create category
- [ ] POST `/v1/categories/{id}` - Update category
- [ ] DELETE `/v1/categories/{id}` - Delete category

**Frontend Display:**
- [ ] View category listing page
- [ ] Verify all categories display
- [ ] Verify category images display
- [ ] Verify featured categories work
- [ ] Verify sort order works
- [ ] Test responsive design

---

## Summary

### Phase Overview

| Phase | Priority | Files Modified |
|--------|----------|---------------|
| Phase 1: Fix Category Model | 🔴 CRITICAL | Category.php |
| Phase 2: Update CategoryRepository | 🟠 HIGH | CategoryRepository.php |
| Phase 3: Update CategoryFields | 🟠 HIGH | CategoryFields.php |
| Phase 4: Update CategoriesController | 🟡 MEDIUM | CategoriesController.php |
| Phase 5: Update CategoryFactory | 🟡 MEDIUM | CategoryFactory.php |
| Phase 6: Testing & Verification | 🟡 REQUIRED | Test files |

### Dependencies

- Phase 1 must be completed before Phase 2
- Phase 2 must be completed before Phase 3
- Phase 3-5 can be done in parallel
- Phase 6 depends on all previous phases

### Risk Mitigation

**Backup Strategy:**
- Create backups before each phase
- Keep backups for at least 1 week
- Test on staging environment first

**Rollback Plan:**
```bash
# If issues occur, restore from backup
cp backups/Category.php.backup-YYYYMMDD-HHMMSS src/Models/Category.php
cp backups/CategoryRepository.php.backup-YYYYMMDD-HHMMSS src/Repositories/CategoryRepository.php
cp backups/CategoryFields.php.backup-YYYYMMDD-HHMMSS src/Admin/CategoryFields.php
```

**Testing Strategy:**
- Run unit tests after each phase
- Run integration tests after each phase
- Manual testing after each phase
- Static analysis before committing

---

## Next Steps

1. **Start with Phase 1** (most critical)
2. Complete phases in order
3. Test thoroughly after each phase
4. Commit changes with proper messages
5. Update feature-requirements.md with completion status

**Note:** Do not proceed to next section (Tags) until Section 2 is complete and verified.

---

## Expected Outcome

After completing all phases, Section 2 (Categories) will be **100% true hybrid compliant**:
- ✅ All meta keys use underscore prefix (`_aps_category_*`)
- ✅ Model, Factory, Repository, and Controller all consistent
- ✅ Matches Product model pattern
- ✅ Follows project's true hybrid standard
- ✅ All 32 basic features working correctly
- ✅ Ready for production use