# Screen Resolutions & Breakpoints

This website uses **Tailwind CSS default breakpoints** for responsive design.

---

## Breakpoint Reference Table

| Tailwind Class | CSS Media Query | Device Type | Pixel Width |
|----------------|-----------------|-------------|-------------|
| **Default** (no prefix) | - | Mobile (portrait) | < 640px |
| `sm:` | `@media (min-width: 640px)` | Mobile (landscape) | ≥ 640px |
| `md:` | `@media (min-width: 768px)` | Tablet (portrait) | ≥ 768px |
| `lg:` | `@media (min-width: 1024px)` | Tablet (landscape) / Laptop | ≥ 1024px |
| `xl:` | `@media (min-width: 1280px)` | Desktop | ≥ 1280px |
| `2xl:` | `@media (min-width: 1536px)` | Large Desktop | ≥ 1536px |

---

## Where Breakpoints Are Used

### Products Page (`/products`)
```tsx
// Product Grid Layout
grid grid-cols-1        // Mobile: 1 column (< 768px)
md:grid-cols-2          // Tablet: 2 columns (≥ 768px)
xl:grid-cols-3          // Desktop: 3 columns (≥ 1280px)
```

**Grid Behavior:**
- **Mobile (< 768px)**: 1 product per row
- **Tablet (768px - 1279px)**: 2 products per row
- **Desktop (≥ 1280px)**: 3 products per row

### Home Page (`/`)
```tsx
// Hero Section
text-4xl                 // Mobile base size
sm:text-5xl             // ≥ 640px
lg:text-6xl             // ≥ 1024px

// Category Grid
grid-cols-2             // Mobile: 2 columns
md:grid-cols-4          // ≥ 768px: 4 columns

// Featured Products
grid-cols-1             // Mobile: 1 column
sm:grid-cols-2          // ≥ 640px: 2 columns
lg:grid-cols-4          // ≥ 1024px: 4 columns
```

### Navbar
```tsx
// Mobile Menu Button
md:hidden               // Hidden on ≥ 768px

// Desktop Navigation
hidden md:flex          // Hidden on mobile, flex on ≥ 768px

// Search Input
hidden md:block         // Hidden on mobile, block on ≥ 768px
```

### Admin Dashboard
```tsx
// Admin Sidebar
hidden lg:flex          // Hidden on < 1024px, flex on ≥ 1024px

// Stats Grid
md:grid-cols-2          // 2 columns on ≥ 768px
lg:grid-cols-4          // 4 columns on ≥ 1024px
```

### Footer
```tsx
// Footer Links
grid-cols-1             // Mobile: stacked
md:grid-cols-4          // Desktop: 4 columns
```

---

## Common Device Resolutions

| Device | Width | Breakpoint Applied |
|--------|-------|-------------------|
| iPhone SE | 375px | Default (mobile) |
| iPhone 12/13/14 | 390px | Default (mobile) |
| iPhone 14 Pro Max | 430px | Default (mobile) |
| iPad Mini | 768px | `md:` (tablet) |
| iPad Air/Pro | 820-1024px | `md:` to `lg:` |
| Small Laptop | 1024-1280px | `lg:` |
| Desktop | 1280-1536px | `xl:` |
| Large Monitor | 1920px+ | `2xl:` |

---

## Sidebar Width

The sidebar has a **fixed width of 256px** (`w-64`):
```tsx
<aside className="w-64 flex-shrink-0">
```

This means:
- **Minimum recommended viewport**: ~1100px for 3-column product grid + sidebar
- **Optimal viewport**: 1280px+ (xl breakpoint)

---

## Container Behavior

The main container uses Tailwind's container with auto margins:
```tsx
<div className="container mx-auto px-4">
```

- **Max width**: Follows Tailwind's container defaults
- **Padding**: 16px (px-4) on all sides
- **Centered**: Auto margins center the content

---

## Testing Checklist

When testing responsive design, verify at these widths:

- [ ] **375px** - iPhone SE (smallest common mobile)
- [ ] **390px** - iPhone 12/13/14
- [ ] **768px** - iPad Mini (tablet breakpoint)
- [ ] **1024px** - iPad Pro / small laptop
- [ ] **1280px** - Desktop (3-column grid activates)
- [ ] **1440px** - Common desktop
- [ ] **1920px** - Full HD monitor

---

## Key Design Decisions

1. **Mobile-First**: Base styles are for mobile, breakpoints add complexity
2. **Sidebar Collapse**: Sidebar stays visible on all sizes (fixed 256px)
3. **Product Grid**: Responsive from 1 → 2 → 3 columns
4. **Typography**: Scales from text-4xl → text-5xl → text-6xl
5. **Navigation**: Mobile menu on small screens, full nav on md+

---

## Customizing Breakpoints

To customize, edit `tailwind.config.ts`:

```typescript
theme: {
  screens: {
    'xs': '475px',    // Add extra small
    'sm': '640px',
    'md': '768px',
    'lg': '1024px',
    'xl': '1280px',
    '2xl': '1536px',
    '3xl': '1920px',  // Add extra large
  },
}
```

**Note**: Currently using Tailwind defaults (no custom breakpoints defined).
