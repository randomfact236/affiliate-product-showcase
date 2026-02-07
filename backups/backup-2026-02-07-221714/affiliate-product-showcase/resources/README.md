# Affiliate Product Showcase Component Library

**Purpose:** Standalone CSS component library for development, reference, and integration examples.

---

## Overview

This directory contains a comprehensive CSS component library built with Tailwind CSS, providing a complete set of UI components for the Affiliate Product Showcase plugin.

## Directory Structure

```
resources/
└── css/
    ├── app.css                    # Main stylesheet with Tailwind imports
    └── components/
        ├── button.css             # Button components
        ├── card.css               # Card components
        └── form.css               # Form components
```

## Integration with Build System

The `resources/` directory is now **integrated with the Vite build system**.

### Build Configuration

The component library is configured as a separate entry point in `vite.config.js`:

```javascript
{
  name: 'component-library',
  path: '../resources/css/app.css',
  required: false
}
```

### Build Output

When you run `npm run build`, the component library is compiled to:

```
assets/dist/css/component-library.[hash].css
```

### Usage in WordPress

To use the component library in your WordPress plugin:

```php
<?php
// Enqueue the component library CSS
wp_enqueue_style(
    'affiliate-product-showcase-components',
    plugins_url('assets/dist/css/component-library.css', __FILE__),
    array(), // No dependencies
    '1.0.0', // Version
    'all' // Media
);
```

Or use the asset manifest for version-controlled enqueuing:

```php
<?php
$manifest = require 'includes/asset-manifest.php';

if (isset($manifest['component-library.css'])) {
    wp_enqueue_style(
        'affiliate-product-showcase-components',
        plugins_url('assets/dist/' . $manifest['component-library.css']['file'], __FILE__),
        array(),
        null, // Version managed by manifest
        'all'
    );
}
```

---

## Components

### Button Component (`button.css`)

**Purpose:** Comprehensive button system with multiple variants, sizes, and states.

**Classes:**
- `.aps-btn` - Base button
- `.aps-btn--primary` - Primary action (blue)
- `.aps-btn--secondary` - Secondary action (gray)
- `.aps-btn--outline` - Outline style (border only)
- `.aps-btn--ghost` - Ghost style (transparent background)
- `.aps-btn--danger` - Danger action (red)
- `.aps-btn--success` - Success action (green)
- `.aps-btn--sm` - Small button
- `.aps-btn--lg` - Large button
- `.aps-btn--xl` - Extra large button
- `.aps-btn--full` - Full width button
- `.aps-btn--icon` - Button with icon
- `.aps-btn--icon-only` - Icon-only button
- `.aps-btn--loading` - Loading state
- `.aps-btn--fab` - Floating action button
- `.aps-btn--badge` - Button with badge

**Example:**
```html
<button class="aps-btn aps-btn--primary">Primary Button</button>
<button class="aps-btn aps-btn--secondary aps-btn--icon">
  <span class="aps-btn__icon aps-btn__icon--left">📄</span>
  Download
</button>
```

### Card Component (`card.css`)

**Purpose:** Card component for displaying affiliate products with multiple variants.

**Classes:**
- `.aps-card` - Base card
- `.aps-card--hover` - Hover effects
- `.aps-card--with-image` - Card with image
- `.aps-card--compact` - Compact variant
- `.aps-card__header` - Card header
- `.aps-card__body` - Card body
- `.aps-card__footer` - Card footer
- `.aps-card__title` - Title styling
- `.aps-card__subtitle` - Subtitle styling
- `.aps-card__price` - Price styling
- `.aps-card__description` - Description
- `.aps-card__image` - Image container
- `.aps-card__badge` - Badges (sale, new, featured)
- `.aps-card__rating` - Star rating
- `.aps-card__meta` - Meta information
- `.aps-card__actions` - Action buttons
- `.aps-card-grid` - Grid layout

**Example:**
```html
<div class="aps-card aps-card--hover">
  <div class="aps-card__image">
    <img src="product.jpg" alt="Product">
  </div>
  <div class="aps-card__header">
    <span class="aps-card__badge aps-card__badge--sale">Sale</span>
  </div>
  <div class="aps-card__body">
    <h3 class="aps-card__title">Product Title</h3>
    <p class="aps-card__price">$99.99</p>
    <p class="aps-card__description">Product description here...</p>
  </div>
  <div class="aps-card__footer">
    <button class="aps-btn aps-btn--primary">Buy Now</button>
  </div>
</div>
```

### Form Component (`form.css`)

**Purpose:** Complete form system with all form elements and states.

**Classes:**
- `.aps-form` - Form container
- `.aps-form-group` - Form group
- `.aps-fieldset` - Fieldset
- `.aps-legend` - Legend
- `.aps-label` - Label
- `.aps-label--required` - Required label
- `.aps-input` - Input field
- `.aps-input--error` - Error state
- `.aps-input--success` - Success state
- `.aps-textarea` - Textarea
- `.aps-select` - Select
- `.aps-checkbox` - Checkbox
- `.aps-radio` - Radio
- `.aps-checkbox-group` - Checkbox group
- `.aps-radio-group` - Radio group
- `.aps-helper` - Helper text
- `.aps-error` - Error message
- `.aps-search` - Search input
- `.aps-file` - File upload
- `.aps-toggle` - Toggle switch

**Example:**
```html
<form class="aps-form">
  <div class="aps-form-group">
    <label class="aps-label aps-label--required">Name</label>
    <input type="text" class="aps-input" placeholder="Enter your name">
  </div>
  
  <div class="aps-form-group">
    <label class="aps-label">Email</label>
    <input type="email" class="aps-input" placeholder="Enter your email">
    <p class="aps-helper">We'll never share your email.</p>
  </div>
  
  <div class="aps-form-actions">
    <button class="aps-btn aps-btn--secondary">Cancel</button>
    <button class="aps-btn aps-btn--primary">Submit</button>
  </div>
</form>
```

---

## Design Principles

### BEM Naming Convention

All components follow the BEM (Block Element Modifier) naming convention:

- **Block:** `.aps-{component}` - The main component
- **Element:** `.aps-{component}__{element}` - Part of the component
- **Modifier:** `.aps-{component}--{modifier}` - Variant or state

**Example:**
```css
.aps-card { /* Block */ }
.aps-card__title { /* Element */ }
.aps-card--hover { /* Modifier */ }
```

### Tailwind CSS Integration

The component library is built with Tailwind CSS:

- Uses `@tailwind` directives for base, components, and utilities
- Applies Tailwind utilities via `@apply`
- Extends Tailwind with custom components
- Compatible with Tailwind's purging system

### Utility-First Approach

Components are built using a utility-first approach:

- Use Tailwind utilities for common patterns
- Create reusable components for complex patterns
- Maintain flexibility through composition
- Optimize for purging with Tailwind

---

## Custom Utilities

The component library includes custom utilities:

### Accessibility

- `.sr-only` - Screen reader only (visually hidden)

### Spacing

- `.space-y-0.5` - Vertical spacing (0.125rem)
- `.space-x-0.5` - Horizontal spacing (0.125rem)

### Text Truncation

- `.line-clamp-1` through `.line-clamp-4` - Multi-line truncation

### Animations

- `.animate-spin` - Rotation animation
- `.animate-ping` - Scale and fade animation
- `.animate-pulse` - Opacity pulse animation

---

## Accessibility

The component library includes basic accessibility features:

- ✅ Focus states with visual indicators
- ✅ Disabled states with proper styling
- ✅ Screen reader utility (`.sr-only`)
- ✅ Proper label associations
- ✅ ARIA-compatible structure

**Future Improvements:**
- ⚠️ Add focus-visible for keyboard navigation
- ⚠️ Add reduced motion support
- ⚠️ Add high contrast mode support
- ⚠️ Add ARIA labels and roles

---

## Browser Compatibility

**Target Browsers:**
- Chrome >= 90
- Firefox >= 88
- Safari >= 14
- Edge >= 90
- Mobile browsers (iOS, Android)

**CSS Features:**
- ✅ CSS Grid
- ✅ Flexbox
- ✅ CSS Variables
- ✅ CSS Transitions
- ✅ CSS Animations
- ✅ CSS Layers

**Note:** Internet Explorer 11 is not supported (requires CSS Grid, Layers)

---

## Performance

### CSS Size

- **Uncompiled:** ~29 KB (4 files)
- **Compiled with Tailwind:** ~15-20 KB (with purging)
- **Without Purging:** ~500 KB+ (full Tailwind)

### Optimization

- ✅ Tailwind purging removes unused styles
- ✅ CSS code splitting in build
- ✅ Minification in production
- ✅ Gzip compression (via build script)
- ✅ Brotli compression (via build script)

---

## Development

### Building the Component Library

```bash
# Build all assets (includes component library)
npm run build

# Watch for changes
npm run dev

# Preview built assets
npm run preview
```

### Testing Components

```bash
# Install Playwright for component testing
npm install -D @playwright/test

# Run component tests
npx playwright test

# Install axe-core for accessibility testing
npm install -D @axe-core/cli

# Test accessibility
axe resources/examples/
```

### Component Examples

Create example HTML files in `resources/examples/` to test components:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Component Library Examples</title>
  <link rel="stylesheet" href="../css/app.css">
</head>
<body>
  <div class="aps-card-grid">
    <!-- Component examples -->
  </div>
</body>
</html>
```

---

## WordPress Integration

### Enqueuing Styles

```php
<?php
// In your plugin's enqueue function
function affiliate_product_showcase_enqueue_styles() {
    // Component library
    wp_enqueue_style(
        'affiliate-product-showcase-components',
        plugins_url('assets/dist/css/component-library.css', __FILE__),
        array(),
        null, // Version managed by manifest
        'all'
    );
}
add_action('wp_enqueue_scripts', 'affiliate_product_showcase_enqueue_styles');
```

### Using Component Classes

```php
<?php
// In your PHP templates
?>
<div class="aps-card aps-card--hover">
  <?php the_post_thumbnail('medium', array('class' => 'aps-card__image')); ?>
  <div class="aps-card__body">
    <h3 class="aps-card__title"><?php the_title(); ?></h3>
    <p class="aps-card__description"><?php the_excerpt(); ?></p>
  </div>
  <div class="aps-card__footer">
    <a href="<?php the_permalink(); ?>" class="aps-btn aps-btn--primary">
      <?php _e('View Product', 'affiliate-product-showcase'); ?>
    </a>
  </div>
</div>
```

### Block Integration

For Gutenberg blocks, use component classes in block rendering:

```php
<?php
// In block render callback
function render_affiliate_product_showcase_block($attributes, $content) {
    ob_start();
    ?>
    <div class="aps-card-grid">
        <?php foreach ($products as $product) : ?>
            <div class="aps-card aps-card--hover">
                <!-- Product card content -->
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
```

---

## Customization

### Modifying Components

1. **Override Components:**
   - Copy component file to your theme
   - Modify as needed
   - Enqueue after component library

2. **Extend Components:**
   - Use component classes as base
   - Add custom modifiers via CSS
   - Maintain BEM naming

3. **Create Custom Components:**
   - Follow BEM naming convention
   - Use Tailwind utilities
   - Import in `app.css`

### Theming

Modify component colors via CSS variables:

```css
/* In your theme's style.css */
:root {
  --aps-color-primary: #2563eb;
  --aps-color-secondary: #64748b;
  --aps-color-success: #16a34a;
  --aps-color-danger: #dc2626;
}
```

---

## Best Practices

### DO ✅

1. **Use BEM Naming:**
   ```css
   .aps-card { /* Good */ }
   .aps-card__title { /* Good */ }
   .aps-card--hover { /* Good */ }
   ```

2. **Use Tailwind Utilities:**
   ```css
   .aps-input {
     @apply w-full px-3 py-2 border; /* Good */
   }
   ```

3. **Test Components:**
   - Test in multiple browsers
   - Test accessibility
   - Test responsiveness

4. **Use Semantic HTML:**
   ```html
   <button class="aps-btn">Click</button> <!-- Good -->
   <div class="aps-btn">Click</div> <!-- Bad -->
   ```

### DON'T ❌

1. **Don't Use IDs for Styling:**
   ```css
   #card { /* Bad */ }
   .aps-card { /* Good */ }
   ```

2. **Don't Nest Too Deeply:**
   ```css
   .aps-card .aps-card__body .aps-card__title { /* Bad */ }
   .aps-card__title { /* Good */ }
   ```

3. **Don't Use !important Excessively:**
   ```css
   .aps-btn {
     color: red !important; /* Bad */
   }
   .aps-btn--danger {
     @apply bg-red-600; /* Good */
   }
   ```

---

## Troubleshooting

### Components Not Showing

**Problem:** Component styles not appearing in browser.

**Solution:**
1. Check if component library is enqueued
2. Verify file path in `wp_enqueue_style()`
3. Check browser console for 404 errors
4. Clear browser cache

### Build Issues

**Problem:** Component library not compiling.

**Solution:**
1. Check `vite.config.js` entry point
2. Verify `resources/css/app.css` exists
3. Check console for build errors
4. Ensure Node.js version is compatible

### Styles Not Updating

**Problem:** Changes not reflecting after build.

**Solution:**
1. Clear build cache: `npm run clean`
2. Rebuild: `npm run build`
3. Clear browser cache
4. Check manifest for correct hash

---

## Support

For issues or questions about the component library:

1. Check this README
2. Review component documentation
3. Check examples in `resources/examples/`
4. Refer to WordPress coding standards
5. Submit issue on GitHub

---

## License

This component library is part of the Affiliate Product Showcase WordPress plugin and is licensed under GPL-2.0-or-later.

---

## Changelog

### Version 1.0.0 (2026-01-16)

- ✅ Initial component library
- ✅ Button component (6 variants, 4 sizes)
- ✅ Card component (3 variants, full sections)
- ✅ Form component (complete form system)
- ✅ Tailwind CSS integration
- ✅ BEM naming convention
- ✅ Custom utilities and animations
- ✅ Integrated with Vite build system
- ✅ Documentation and examples

---

## Resources

- [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [BEM Methodology](https://getbem.com/)
- [Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
