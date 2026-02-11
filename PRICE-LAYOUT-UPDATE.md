# Price Layout Update - COMPLETE ✅

## Change Made

Updated the product table price column to display prices in **vertical order** instead of horizontal.

### Before (Horizontal)
```
$199 ~~$249~~ 9% OFF
```

### After (Vertical)
```
~~$249~~      <- Original price (strikethrough, small)
$199           <- Current price (bold, prominent)
9% OFF         <- Discount badge
```

## Implementation Details

**File**: `apps/web/src/app/admin/products/page.tsx`

**Change**: Modified the Price table cell (`<td>`) to use `flex-col` instead of `flex-row`:

```tsx
<td className="px-4 py-3">
  <div className="flex flex-col gap-1">
    {/* Original Price - Strikethrough */}
    {product.comparePrice && (
      <span className="text-xs text-gray-400 line-through">
        {formatPrice(product.comparePrice)}
      </span>
    )}
    
    {/* Current Price - Bold */}
    <span className="font-semibold text-gray-900">
      {formatPrice(product.price)}
    </span>
    
    {/* Discount Badge */}
    {product.comparePrice && calculateDiscount(...) && (
      <Badge className="w-fit bg-red-500 text-white text-xs">
        {calculateDiscount(...)}% OFF
      </Badge>
    )}
  </div>
</td>
```

## Visual Hierarchy

1. **Original Price** - Small, gray, strikethrough (least emphasis)
2. **Current Price** - Bold, dark, larger (most emphasis)
3. **Discount %** - Red badge (attention grabbing)

## Test URL
http://localhost:3000/admin/products

## Status
✅ Page loads successfully (HTTP 200)
✅ Vertical price layout applied
