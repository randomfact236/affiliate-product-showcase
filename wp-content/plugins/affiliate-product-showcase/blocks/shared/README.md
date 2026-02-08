# Shared Block Utilities

Reusable utilities, components, and types for all blocks.

## Quick Import

```jsx
import {
  // Utilities
  debounce,
  SimpleCache,
  renderStars,
  formatPrice,
  truncateText,
  generateA11yId,
  
  // Components
  ErrorBoundary,
  LoadingSpinner,
  EmptyState,
  ProductImage,
  ProductPrice,
  ProductBadge,
  ProductRating,
  AffiliateButton,
} from '../shared';
```

## API Reference

### `debounce(fn, wait)`

Returns debounced function with `cancel()` method.

```jsx
const debouncedSave = debounce((value) => {
  setAttributes({ columns: value });
}, 300);

// Cleanup on unmount
useEffect(() => () => debouncedSave.cancel(), []);
```

### `SimpleCache`

LRU cache with TTL support.

```jsx
const cache = new SimpleCache(10, 5 * 60 * 1000); // 10 entries, 5min TTL

cache.set('key', data);
const data = cache.get('key'); // null if expired/missing
cache.clear();
```

### Components

All components are memoized and include proper ARIA attributes.

```jsx
<ErrorBoundary>
  {isLoading ? (
    <LoadingSpinner message={__('Loading...', 'affiliate-product-showcase')} />
  ) : products.length === 0 ? (
    <EmptyState message={__('No products found.', 'affiliate-product-showcase')} />
  ) : (
    products.map(product => (
      <article key={product.id}>
        <ProductImage src={product.image_url} alt={product.title} />
        <ProductBadge badge={product.badge} />
        <ProductPrice price={product.price} originalPrice={product.original_price} />
        <ProductRating rating={product.rating} />
        <AffiliateButton href={product.affiliate_link} productTitle={product.title}>
          {__('View Deal', 'affiliate-product-showcase')}
        </AffiliateButton>
      </article>
    ))
  )}
</ErrorBoundary>
```

## Testing

```bash
npm test -- --config=jest.config.blocks.js
```
