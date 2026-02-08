# Blocks Directory

Enterprise-grade Gutenberg blocks for the Affiliate Product Showcase plugin.

## Directory Structure

```
blocks/
├── shared/                    # Shared utilities and components
│   ├── index.js              # Barrel exports
│   ├── utils.js              # Utility functions
│   ├── types.ts              # TypeScript definitions
│   ├── components.jsx        # Reusable React components
│   └── __tests__/            # Unit tests
│       ├── setup.js
│       ├── utils.test.js
│       └── components.test.jsx
├── product-grid/             # Product Grid block
│   ├── block.json
│   ├── edit.jsx
│   ├── save.jsx
│   ├── index.js
│   ├── editor.scss
│   └── style.scss
└── product-showcase/         # Product Showcase block
    ├── block.json
    ├── edit.jsx
    ├── save.jsx
    ├── index.js
    ├── editor.scss
    └── style.scss
```

## Shared Utilities

### Functions (`shared/utils.js`)

| Function | Description |
|----------|-------------|
| `debounce(fn, wait)` | Debounce with cancel support |
| `SimpleCache` | LRU cache with TTL |
| `renderStars(rating)` | Render star rating |
| `formatPrice(price, currency)` | Format price display |
| `truncateText(text, max)` | Truncate with ellipsis |
| `generateA11yId(prefix, id)` | Generate accessibility IDs |

### Components (`shared/components.jsx`)

| Component | Props | Description |
|-----------|-------|-------------|
| `ErrorBoundary` | `children` | Graceful error handling |
| `LoadingSpinner` | `message` | Loading state |
| `EmptyState` | `message` | Empty content state |
| `ProductImage` | `src, alt, className` | Image with fallback |
| `ProductPrice` | `price, originalPrice` | Price display |
| `ProductBadge` | `badge` | Product badge |
| `ProductRating` | `rating` | Star rating |
| `AffiliateButton` | `href, productTitle, children` | Affiliate link |

### TypeScript Types (`shared/types.ts`)

- `Product` - Product data structure
- `ProductGridAttributes` - Grid block attributes
- `ProductShowcaseAttributes` - Showcase block attributes
- `BlockEditProps<T>` - Block editor props

## Usage

```jsx
import {
  debounce,
  SimpleCache,
  ErrorBoundary,
  LoadingSpinner,
  ProductImage,
} from '../shared';

// Use in your block
function Edit({ attributes, setAttributes }) {
  return (
    <ErrorBoundary>
      <LoadingSpinner message="Loading products..." />
    </ErrorBoundary>
  );
}
```

## Testing

```bash
# Run block tests
npm test -- --config=jest.config.blocks.js

# Run with coverage
npm test -- --config=jest.config.blocks.js --coverage
```

## Accessibility Features

- ✅ Semantic HTML (`article` elements)
- ✅ ARIA labels and labelledby
- ✅ Live regions for dynamic content
- ✅ Focus management
- ✅ `noopener` on external links
- ✅ Alt text fallbacks

## Performance Optimizations

- ✅ Debounced attribute updates
- ✅ LRU cache with TTL
- ✅ Lazy image loading
- ✅ Memoized components
- ✅ Cleanup on unmount

## Internationalization

All user-facing strings are wrapped with `__()` from `@wordpress/i18n`:

```jsx
import { __ } from '@wordpress/i18n';

// Usage
__('Loading products...', 'affiliate-product-showcase')
```

## Build

```bash
# Build all assets
npm run build

# Build JS only
npm run build:js
```
