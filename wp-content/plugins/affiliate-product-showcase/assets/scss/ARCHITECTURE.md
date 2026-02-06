# SCSS 7-1 Architecture

## Overview

This folder follows the **7-1 Architecture Pattern** for organizing SCSS files.

## The 7 Layers

| Layer | Folder | Purpose |
|-------|--------|---------|
| 1 | `01-settings/` | Design tokens, variables, configuration |
| 2 | `02-tools/` | Sass functions and mixins |
| 3 | `03-generic/` | Reset, box-sizing, base styles |
| 4 | `04-elements/` | Unclassed HTML elements |
| 5 | `05-objects/` | Layout patterns and object abstractions |
| 6 | `06-components/` | UI components |
| 7 | `07-utilities/` | Helper classes |

## Entry Points

| File | Purpose | Output |
|------|---------|--------|
| `main.scss` | Complete bundle (all styles) | `affiliate-product-showcase.css` |
| `admin.scss` | WordPress Admin only | `admin.css` |
| `frontend.scss` | Public website only | `frontend.css` |

## Build Commands

```bash
# Build all
npm run build

# Build individual targets
npm run build:admin      # → assets/css/admin.css
npm run build:frontend   # → assets/css/frontend.css
npm run build:main       # → assets/css/affiliate-product-showcase.css

# Watch mode
npm run watch

# Development mode (expanded + sourcemaps)
npm run dev
```

## File Structure

```
assets/scss/
├── 01-settings/
│   ├── _index.scss
│   └── _tokens.scss          # Colors, spacing, typography, breakpoints
├── 02-tools/
│   ├── _index.scss
│   ├── _functions.scss       # Unit conversions, color functions
│   └── _mixins.scss          # Breakpoints, accessibility, focus states
├── 03-generic/
│   ├── _index.scss
│   └── _reset.scss           # CSS reset, box-sizing
├── 04-elements/
│   ├── _index.scss
│   ├── _buttons.scss         # Button base styles
│   ├── _forms.scss           # Form inputs
│   └── _typography.scss      # Headings, text styles
├── 05-objects/
│   ├── _index.scss
│   ├── _container.scss       # Width constraints
│   ├── _grid.scss            # Grid layouts
│   └── _layout.scss          # Flexbox layouts
├── 06-components/
│   ├── _index.scss
│   ├── _card.scss            # Product cards
│   ├── _tabs.scss            # Settings tabs
│   ├── _filters.scss         # Table filters
│   ├── _taxonomy.scss        # Category/tag management
│   ├── _modal.scss           # Dialog modals
│   ├── _toast.scss           # Notifications
│   ├── _product-showcase.scss # Block: showcase
│   └── _product-grid.scss     # Block: grid
├── 07-utilities/
│   ├── _index.scss
│   ├── _spacing.scss         # Margin/padding helpers
│   ├── _colors.scss          # Text/bg color helpers
│   ├── _display.scss         # Display/flex helpers
│   ├── _text.scss            # Text alignment helpers
│   └── _accessibility.scss   # Screen reader, skip link
├── admin.scss                # Admin entry point
├── frontend.scss             # Frontend entry point
└── main.scss                 # Complete bundle entry point
```

## Usage in WordPress

```php
// Admin pages
wp_enqueue_style('aps-admin', plugins_url('assets/css/admin.css', __FILE__), [], '2.0.0');

// Frontend
wp_enqueue_style('aps-frontend', plugins_url('assets/css/frontend.css', __FILE__), [], '2.0.0');
```

## Design Tokens

All visual values are stored in `01-settings/_tokens.scss`:

- **Colors**: `$color-primary`, `$color-success`, etc.
- **Spacing**: `$spacing-1` through `$spacing-16`
- **Typography**: `$font-size-sm`, `$font-weight-bold`, etc.
- **Breakpoints**: `$breakpoint-sm`, `$breakpoint-md`, etc.
- **WordPress**: `$wp-admin-theme-color` (adapts to admin theme)

## Legacy Files Deleted

The following CSS files have been removed (replaced by SCSS):

- ❌ `admin-aps_category.css`
- ❌ `admin-table-filters.css`
- ❌ `product-card.css`
- ❌ `settings.css`
- ❌ `showcase-frontend.min.css`
- ❌ `showcase-frontend-isolated.css`
- ❌ `tokens.css`
