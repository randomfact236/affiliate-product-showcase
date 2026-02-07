# JavaScript Architecture

## Namespace Convention

All JavaScript functionality is exposed under the unified `window.APS` namespace:

```
window.APS
├── Utils          # Shared utility functions (utils.js)
│   ├── debounce()
│   ├── escapeHtml()
│   ├── sanitizeUrl()
│   ├── showNotice()
│   ├── ajax()
│   ├── getCurrentStatusView()
│   ├── shouldKeepRowInCurrentView()
│   └── parseQueryParamsFromUrl()
├── Frontend       # Public-facing functionality (showcase-frontend.js)
│   ├── refresh()
│   └── clearFilters()
└── Admin          # Admin-only functionality (auto-initialized)
```

## File Organization

| File | Purpose | Dependencies |
|------|---------|--------------|
| `utils.js` | Shared utilities, AJAX helpers | None (vanilla JS) |
| `admin-add-product.js` | Add/Edit product form | jQuery, Utils |
| `admin-products.js` | Product listing page | jQuery, Utils |
| `admin-ribbon.js` | Ribbon taxonomy management | jQuery, Utils |
| `admin-tag.js` | Tag taxonomy management | jQuery, Utils |
| `admin-aps_category.js` | Category taxonomy management | jQuery, Utils |
| `showcase-frontend.js` | Public product showcase | None (vanilla JS) |

## Loading Order

Scripts must be loaded in this order:

1. `utils.js` - Creates `window.APS.Utils` namespace
2. Feature-specific scripts - Consume utilities

## Best Practices

### 1. Always Check for Namespace Existence

```javascript
if (window.APS && window.APS.Utils) {
    window.APS.Utils.showNotice('success', 'Message');
}
```

### 2. Use Vanilla JS for New Frontend Code

Prefer vanilla JS over jQuery for public-facing code to reduce dependencies.

### 3. Use jQuery Only for WordPress Admin

jQuery is bundled with WordPress admin and provides consistency with WP core.

### 4. Always Escape User-Generated Content

```javascript
var safeHtml = window.APS.Utils.escapeHtml(userInput);
var safeUrl = window.APS.Utils.sanitizeUrl(userUrl);
```

### 5. Use Proper AJAX Pattern

```javascript
window.APS.Utils.ajax({
    data: {
        action: 'aps_my_action',
        nonce: myNonce,
        param: value
    }
}).then(function(response) {
    if (response.success) {
        // Handle success
    }
}).catch(function(error) {
    console.error('Request failed:', error);
});
```

## Build Process

```bash
# Build all assets (SCSS + JS)
npm run build

# Build only JS (minified with source maps)
npm run build:js

# Build only SCSS
npm run build:scss

# Watch files during development
npm run watch

# Lint all files
npm run lint

# Lint and auto-fix
npm run lint:scss:fix
npm run lint:js:fix
```

## Minified Files

All minified files are generated with source maps for debugging:

| Source | Minified |
|--------|----------|
| `utils.js` | `utils.min.js` |
| `admin-add-product.js` | `admin-add-product.min.js` |
| `admin-products.js` | `admin-products.min.js` |
| `admin-ribbon.js` | `admin-ribbon.min.js` |
| `admin-tag.js` | `admin-tag.min.js` |
| `admin-aps_category.js` | `admin-aps_category.min.js` |
| `showcase-frontend.js` | `showcase-frontend.min.js` |

## Backward Compatibility

For backward compatibility, `window.APS_Utils` is aliased to `window.APS.Utils`:

```javascript
// Both of these work:
window.APS.Utils.escapeHtml(str);   // Preferred
window.APS_Utils.escapeHtml(str);   // Legacy alias
```
