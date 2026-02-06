1. SCSS Architecture Structure
Copy
assets/scss/
├── 01-settings/
│   ├── _tokens.scss
│   └── _breakpoints.scss
├── 02-tools/
│   ├── _functions.scss
│   └── _mixins.scss
├── 03-generic/
│   ├── _reset.scss
│   └── _box-sizing.scss
├── 04-elements/
│   ├── _buttons.scss
│   ├── _forms.scss
│   └── _typography.scss
├── 05-objects/
│   ├── _grid.scss
│   ├── _layout.scss
│   └── _container.scss
├── 06-components/
│   ├── _card.scss
│   ├── _tabs.scss
│   ├── _filters.scss
│   ├── _taxonomy.scss
│   ├── _modal.scss
│   ├── _toast.scss
│   └── _showcase.scss
├── 07-utilities/
│   └── _accessibility.scss
├── admin.scss
├── frontend.scss
└── core.scss
2. Settings Layer
01-settings/_tokens.scss
scss
Copy
/**
 * Design Tokens
 * 
 * Core design system variables
 */

// Color Palette
$color-primary: #2271b1;
$color-primary-hover: #135e96;
$color-primary-dark: #0d4a8a;

$color-secondary: #646970;
$color-success: #00a32a;
$color-warning: #d97706;
$color-danger: #d63638;
$color-info: #3b82f6;

// Frontend Brand Colors
$color-brand: #3b82f6;
$color-brand-hover: #2563eb;

// Text Colors
$color-text-primary: #1d2327;
$color-text-secondary: #3c434a;
$color-text-muted: #8c8f94;

// Background Colors
$color-bg-primary: #ffffff;
$color-bg-secondary: #f0f0f1;
$color-bg-hover: #f6f7f7;

// Border Colors
$color-border: #c3c4c7;
$color-border-light: #dcdcde;

// Spacing Scale (8px base)
$spacing-unit: 1rem;
$spacing-1: 0.25rem;   // 4px
$spacing-2: 0.5rem;    // 8px
$spacing-3: 0.75rem;   // 12px
$spacing-4: 1rem;      // 16px
$spacing-5: 1.25rem;   // 20px
$spacing-6: 1.5rem;    // 24px
$spacing-8: 2rem;      // 32px
$spacing-10: 2.5rem;   // 40px
$spacing-12: 3rem;     // 48px

// Typography
$font-family-base: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
$font-family-mono: "SFMono-Regular", consolas, "Liberation Mono", menlo, monospace;

$font-size-base: 0.875rem;   // 14px
$font-size-xs: 0.75rem;      // 12px
$font-size-sm: 0.8125rem;    // 13px
$font-size-md: 1rem;         // 16px
$font-size-lg: 1.125rem;     // 18px
$font-size-xl: 1.5rem;       // 24px
$font-size-2xl: 2rem;        // 32px

$font-weight-normal: 400;
$font-weight-medium: 500;
$font-weight-semibold: 600;
$font-weight-bold: 700;

$line-height-tight: 1.25;
$line-height-normal: 1.5;
$line-height-relaxed: 1.75;

// Border Radius
$radius-sm: 0.25rem;   // 4px
$radius-base: 0.375rem; // 6px
$radius-md: 0.5rem;    // 8px
$radius-lg: 0.75rem;   // 12px
$radius-full: 9999px;

// Shadows
$shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
$shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
$shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

// Transitions
$transition-fast: 150ms ease-in-out;
$transition-base: 200ms ease-in-out;
$transition-slow: 300ms ease-in-out;

// Z-index Scale
$z-dropdown: 100;
$z-sticky: 200;
$z-fixed: 300;
$z-modal-backdrop: 400;
$z-modal: 500;
$z-popover: 600;
$z-tooltip: 700;
01-settings/_breakpoints.scss
scss
Copy
/**
 * Breakpoints
 * 
 * EM-based breakpoints for accessibility (respects user zoom)
 */

$breakpoint-xs: 29.9375em;    // 479px / 16px
$breakpoint-sm: 39.9375em;    // 639px / 16px
$breakpoint-md: 47.9375em;    // 767px / 16px
$breakpoint-wp-tablet: 48.875em; // 782px / 16px (WordPress admin breakpoint)
$breakpoint-lg: 63.9375em;    // 1023px / 16px
$breakpoint-xl: 79.9375em;    // 1279px / 16px
$breakpoint-2xl: 95.9375em;   // 1535px / 16px

// Breakpoint map for mixins
$breakpoints: (
  xs: $breakpoint-xs,
  sm: $breakpoint-sm,
  md: $breakpoint-md,
  wp-tablet: $breakpoint-wp-tablet,
  lg: $breakpoint-lg,
  xl: $breakpoint-xl,
  2xl: $breakpoint-2xl
);
3. Tools Layer
02-tools/_functions.scss
scss
Copy
/**
 * SCSS Functions
 */

/// Strip unit from value
@function strip-unit($value) {
  @return $value / ($value * 0 + 1);
}

/// Convert pixels to em
@function em($pixels, $context: 16) {
  @if (unitless($pixels)) {
    $pixels: $pixels * 1px;
  }
  @if (unitless($context)) {
    $context: $context * 1px;
  }
  @return $pixels / $context * 1em;
}

/// Convert pixels to rem
@function rem($pixels, $context: 16) {
  @if (unitless($pixels)) {
    $pixels: $pixels * 1px;
  }
  @if (unitless($context)) {
    $context: $context * 1px;
  }
  @return $pixels / $context * 1rem;
}

/// Get color with fallback
@function color($key) {
  $colors: (
    primary: $color-primary,
    primary-hover: $color-primary-hover,
    secondary: $color-secondary,
    success: $color-success,
    warning: $color-warning,
    danger: $color-danger,
    info: $color-info,
    text-primary: $color-text-primary,
    text-secondary: $color-text-secondary,
    text-muted: $color-text-muted,
    bg-primary: $color-bg-primary,
    bg-secondary: $color-bg-secondary,
    border: $color-border,
    border-light: $color-border-light
  );
  
  @if map-has-key($colors, $key) {
    @return map-get($colors, $key);
  }
  
  @warn "Unknown color: #{$key}";
  @return null;
}

/// Calculate spacing
@function spacing($multiplier) {
  @return $spacing-unit * $multiplier;
}
02-tools/_mixins.scss
scss
Copy
/**
 * SCSS Mixins
 */

@import 'functions';

/// Responsive breakpoint
@mixin respond-to($breakpoint) {
  @if map-has-key($breakpoints, $breakpoint) {
    @media (max-width: map-get($breakpoints, $breakpoint)) {
      @content;
    }
  } @else {
    @warn "Unknown breakpoint: #{$breakpoint}";
  }
}

/// Responsive breakpoint (min-width)
@mixin respond-from($breakpoint) {
  @if map-has-key($breakpoints, $breakpoint) {
    @media (min-width: map-get($breakpoints, $breakpoint)) {
      @content;
    }
  } @else {
    @warn "Unknown breakpoint: #{$breakpoint}";
  }
}

/// Focus visible styles
@mixin focus-visible {
  &:focus-visible {
    outline: 2px solid $color-primary;
    outline-offset: 2px;
    @content;
  }
  
  &:focus:not(:focus-visible) {
    outline: none;
  }
}

/// Button base styles
@mixin button-base {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: $spacing-2 $spacing-4;
  font-family: $font-family-base;
  font-size: $font-size-base;
  font-weight: $font-weight-medium;
  line-height: $line-height-normal;
  text-decoration: none;
  border-radius: $radius-base;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all $transition-base;
  white-space: nowrap;
  
  @include focus-visible;
  
  &:disabled,
  &.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
  }
}

/// Form control base
@mixin form-control {
  width: 100%;
  padding: $spacing-2 $spacing-3;
  font-family: $font-family-base;
  font-size: $font-size-base;
  line-height: $line-height-normal;
  color: $color-text-primary;
  background: $color-bg-primary;
  border: 1px solid $color-border;
  border-radius: $radius-sm;
  box-shadow: $shadow-sm;
  transition: border-color $transition-fast, box-shadow $transition-fast;
  
  @include focus-visible {
    border-color: $color-primary;
    box-shadow: 0 0 0 1px $color-primary;
  }
  
  &::placeholder {
    color: $color-text-muted;
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: $color-bg-secondary;
  }
}

/// Card base
@mixin card {
  position: relative;
  background: $color-bg-primary;
  border: 1px solid $color-border-light;
  border-radius: $radius-md;
  overflow: hidden;
  transition: transform $transition-base, box-shadow $transition-base;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
    border-color: $color-primary;
  }
  
  &:focus-within {
    outline: 3px solid $color-primary;
    outline-offset: 2px;
  }
}

/// Reduced motion
@mixin reduced-motion {
  @media (prefers-reduced-motion: reduce) {
    @content;
  }
}

/// High contrast
@mixin high-contrast {
  @media (prefers-contrast: high) {
    @content;
  }
}

/// Print styles
@mixin print {
  @media print {
    @content;
  }
}

/// Screen reader only
@mixin sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/// Touch target (WCAG 2.5.5)
@mixin touch-target {
  @media (hover: none) and (pointer: coarse) {
    min-height: 44px;
    min-width: 44px;
  }
}

/// Spacing utility generator
@mixin spacing-utilities($property, $prefix) {
  @for $i from 0 through 8 {
    $value: if($i == 0, 0, $spacing-1 * $i);
    
    .u-#{$prefix}-#{$i} {
      #{$property}: $value;
    }
  }
  
  // Directional variants
  .u-#{$prefix}t-0 { #{$property}-top: 0; }
  .u-#{$prefix}t-1 { #{$property}-top: $spacing-1; }
  .u-#{$prefix}t-2 { #{$property}-top: $spacing-2; }
  .u-#{$prefix}t-3 { #{$property}-top: $spacing-3; }
  .u-#{$prefix}t-4 { #{$property}-top: $spacing-4; }
  
  .u-#{$prefix}b-0 { #{$property}-bottom: 0; }
  .u-#{$prefix}b-1 { #{$property}-bottom: $spacing-1; }
  .u-#{$prefix}b-2 { #{$property}-bottom: $spacing-2; }
  .u-#{$prefix}b-3 { #{$property}-bottom: $spacing-3; }
  .u-#{$prefix}b-4 { #{$property}-bottom: $spacing-4; }
}
4. Generic Layer
03-generic/_box-sizing.scss
scss
Copy
/**
 * Box Sizing Reset
 */

*,
*::before,
*::after {
  box-sizing: border-box;
}
03-generic/_reset.scss
scss
Copy
/**
 * Modern CSS Reset
 */

* {
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-size-adjust: 100%;
}

body {
  font-family: $font-family-base;
  font-size: $font-size-base;
  line-height: $line-height-normal;
  color: $color-text-primary;
  background-color: $color-bg-secondary;
}

img,
picture,
video,
canvas,
svg {
  display: block;
  max-width: 100%;
  height: auto;
}

input,
button,
textarea,
select {
  font: inherit;
  color: inherit;
}

button {
  cursor: pointer;
  background: none;
  border: none;
}

ul,
ol {
  list-style: none;
}

a {
  color: inherit;
  text-decoration: none;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
}
5. Elements Layer
04-elements/_buttons.scss
scss
Copy
/**
 * Button Elements
 */

.aps-btn {
  @include button-base;
  
  // Variants
  &--primary {
    background: $color-primary;
    color: $color-bg-primary;
    
    &:hover:not(:disabled) {
      background: $color-primary-hover;
    }
  }
  
  &--secondary {
    background: $color-bg-primary;
    color: $color-text-primary;
    border-color: $color-border;
    
    &:hover:not(:disabled) {
      background: $color-bg-hover;
      border-color: $color-text-muted;
    }
  }
  
  &--danger {
    background: $color-danger;
    color: $color-bg-primary;
    
    &:hover:not(:disabled) {
      background: #b32d2e;
    }
  }
  
  &--ghost {
    background: transparent;
    color: $color-primary;
    
    &:hover:not(:disabled) {
      background: rgba($color-primary, 0.1);
    }
  }
  
  // Sizes
  &--sm {
    padding: $spacing-1 $spacing-3;
    font-size: $font-size-sm;
  }
  
  &--lg {
    padding: $spacing-3 $spacing-6;
    font-size: $font-size-md;
  }
  
  // Modifiers
  &--block {
    display: flex;
    width: 100%;
  }
  
  &--loading {
    position: relative;
    color: transparent;
    
    &::after {
      content: "";
      position: absolute;
      width: 1rem;
      height: 1rem;
      top: 50%;
      left: 50%;
      margin-top: -0.5rem;
      margin-left: -0.5rem;
      border: 2px solid currentColor;
      border-top-color: transparent;
      border-radius: 50%;
      animation: aps-spin 1s linear infinite;
    }
  }
  
  @include reduced-motion {
    transition: none;
    
    &:hover {
      transform: none;
    }
  }
}

@keyframes aps-spin {
  to {
    transform: rotate(360deg);
  }
}
04-elements/_forms.scss
scss
Copy
/**
 * Form Elements
 */

.aps-form-control {
  @include form-control;
  min-height: rem(40);
}

.aps-form-select {
  @include form-control;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23646970' d='M6 9L2 5h8z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right $spacing-3 center;
  padding-right: $spacing-8;
}

.aps-form-textarea {
  @include form-control;
  min-height: 5rem;
  resize: vertical;
}

.aps-form-check {
  display: flex;
  align-items: center;
  gap: $spacing-2;
  cursor: pointer;
  
  input[type="checkbox"],
  input[type="radio"] {
    width: rem(18);
    height: rem(18);
    cursor: pointer;
    
    @include focus-visible;
  }
}

.aps-form-group {
  margin-bottom: $spacing-4;
}

.aps-form-label {
  display: block;
  margin-bottom: $spacing-2;
  font-size: $font-size-sm;
  font-weight: $font-weight-medium;
  color: $color-text-primary;
}

.aps-form-hint {
  margin-top: $spacing-1;
  font-size: $font-size-xs;
  color: $color-text-muted;
}
04-elements/_typography.scss
scss
Copy
/**
 * Typography Elements
 */

.aps-h1 {
  font-size: $font-size-2xl;
  font-weight: $font-weight-bold;
  line-height: $line-height-tight;
  color: $color-text-primary;
}

.aps-h2 {
  font-size: $font-size-xl;
  font-weight: $font-weight-bold;
  line-height: $line-height-tight;
  color: $color-text-primary;
}

.aps-h3 {
  font-size: $font-size-lg;
  font-weight: $font-weight-semibold;
  line-height: $line-height-tight;
  color: $color-text-primary;
}

.aps-h4 {
  font-size: $font-size-md;
  font-weight: $font-weight-semibold;
  line-height: $line-height-tight;
  color: $color-text-primary;
}

.aps-text-lead {
  font-size: $font-size-md;
  color: $color-text-secondary;
  line-height: $line-height-relaxed;
}

.aps-text-small {
  font-size: $font-size-sm;
  color: $color-text-muted;
}

.aps-text-muted {
  color: $color-text-muted;
}
6. Objects Layer
05-objects/_grid.scss
scss
Copy
/**
 * Grid Object
 */

.aps-o-grid {
  display: grid;
  gap: $spacing-4;
  
  &--2cols {
    grid-template-columns: repeat(2, 1fr);
    
    @include respond-to(md) {
      grid-template-columns: 1fr;
    }
  }
  
  &--3cols {
    grid-template-columns: repeat(3, 1fr);
    
    @include respond-to(md) {
      grid-template-columns: repeat(2, 1fr);
    }
    
    @include respond-to(sm) {
      grid-template-columns: 1fr;
    }
  }
  
  &--4cols {
    grid-template-columns: repeat(4, 1fr);
    
    @include respond-to(lg) {
      grid-template-columns: repeat(2, 1fr);
    }
    
    @include respond-to(sm) {
      grid-template-columns: 1fr;
    }
  }
}
05-objects/_layout.scss
scss
Copy
/**
 * Layout Object
 */

.aps-o


Complete 7-1 Architecture
1. Settings Layer (scss/01-settings/)
_tokens.scss
scss
Copy
// Design Tokens - Single source of truth
// ======================================

// Breakpoints (em for accessibility)
$breakpoints: (
  'xs': 29.9375em,   // 479px
  'sm': 39.9375em,   // 639px
  'md': 47.9375em,   // 767px
  'wp-tablet': 48.875em, // 782px
  'lg': 63.9375em,   // 1023px
  'xl': 79.9375em,   // 1279px
  '2xl': 95.9375em   // 1535px
);

// Colors
$colors: (
  'primary': #2271b1,
  'primary-hover': #135e96,
  'primary-dark': #0d4a8a,
  'secondary': #646970,
  'success': #00a32a,
  'warning': #d97706,
  'danger': #d63638,
  'info': #3b82f6,
  'text-primary': #1d2327,
  'text-secondary': #3c434a,
  'text-muted': #8c8f94,
  'bg-primary': #ffffff,
  'bg-secondary': #f0f0f1,
  'bg-hover': #f6f7f7,
  'border': #c3c4c7,
  'border-light': #dcdcde
);

// Frontend-specific colors
$frontend-colors: (
  'primary': #3b82f6,
  'primary-hover': #2563eb
);

// Spacing scale
$spacing: (
  '0': 0,
  '1': 0.25rem,
  '2': 0.5rem,
  '3': 0.75rem,
  '4': 1rem,
  '5': 1.25rem,
  '6': 1.5rem,
  '8': 2rem,
  '10': 2.5rem,
  '12': 3rem
);

// Typography
$font-family-base: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
$font-size-base: 0.875rem;
$line-height-base: 1.5;

// Border radius
$radius: (
  'sm': 0.25rem,
  'base': 0.375rem,
  'lg': 0.5rem,
  'xl': 0.75rem,
  'full': 9999px
);

// Shadows
$shadows: (
  'sm': 0 1px 2px 0 rgba(0, 0, 0, 0.05),
  'md': 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
  'lg': 0 10px 15px -3px rgba(0, 0, 0, 0.1)
);

// Transitions
$transition-base: 200ms ease-in-out;
$transition-fast: 150ms ease-in-out;
2. Tools Layer (scss/02-tools/)
_functions.scss
scss
Copy
// Utility Functions
// =================

@function color($key) {
  @if not map-has-key($colors, $key) {
    @error 'Color `#{$key}` not found in $colors.';
  }
  @return map-get($colors, $key);
}

@function frontend-color($key) {
  @if not map-has-key($frontend-colors, $key) {
    @error 'Color `#{$key}` not found in $frontend-colors.';
  }
  @return map-get($frontend-colors, $key);
}

@function spacing($key) {
  @if not map-has-key($spacing, $key) {
    @error 'Spacing `#{$key}` not found in $spacing.';
  }
  @return map-get($spacing, $key);
}

@function radius($key) {
  @if not map-has-key($radius, $key) {
    @error 'Radius `#{$key}` not found in $radius.';
  }
  @return map-get($radius, $key);
}

@function shadow($key) {
  @if not map-has-key($shadows, $key) {
    @error 'Shadow `#{$key}` not found in $shadows.';
  }
  @return map-get($shadows, $key);
}
_mixins.scss
scss
Copy
// Mixins
// ======

// Media query mixin (mobile-first)
@mixin respond-to($breakpoint) {
  @if not map-has-key($breakpoints, $breakpoint) {
    @error 'Breakpoint `#{$breakpoint}` not found.';
  }
  @media (min-width: map-get($breakpoints, $breakpoint)) {
    @content;
  }
}

// Max-width media query
@mixin respond-down($breakpoint) {
  $value: map-get($breakpoints, $breakpoint);
  @media (max-width: $value) {
    @content;
  }
}

// Focus visible mixin
@mixin focus-visible($color: color('primary')) {
  &:focus-visible {
    outline: 2px solid $color;
    outline-offset: 2px;
  }
  
  &:focus:not(:focus-visible) {
    outline: none;
  }
}

// Button base mixin
@mixin button-base {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: spacing('2') spacing('4');
  font-family: $font-family-base;
  font-size: $font-size-base;
  font-weight: 500;
  line-height: $line-height-base;
  text-decoration: none;
  border-radius: radius('base');
  border: 1px solid transparent;
  cursor: pointer;
  transition: all $transition-base;
  white-space: nowrap;
  
  @include focus-visible;
  
  &:disabled,
  &.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
  }
}

// Form control mixin
@mixin form-control {
  width: 100%;
  padding: spacing('2') spacing('3');
  font-family: $font-family-base;
  font-size: $font-size-base;
  line-height: $line-height-base;
  color: color('text-primary');
  background: color('bg-primary');
  border: 1px solid color('border');
  border-radius: radius('sm');
  box-shadow: shadow('sm');
  transition: border-color $transition-fast, box-shadow $transition-fast;
  
  &::placeholder {
    color: color('text-muted');
  }
  
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: color('bg-secondary');
  }
}

// Card mixin
@mixin card {
  position: relative;
  background: color('bg-primary');
  border: 1px solid color('border-light');
  border-radius: radius('lg');
  overflow: hidden;
  transition: transform $transition-base, box-shadow $transition-base;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: shadow('lg');
    border-color: color('primary');
  }
  
  &:focus-within {
    outline: 3px solid color('primary');
    outline-offset: 2px;
  }
}

// Spacing utility generator (for core only)
@mixin spacing-utilities {
  @each $key, $value in $spacing {
    .u-m-#{$key} { margin: $value; }
    .u-mt-#{$key} { margin-top: $value; }
    .u-mb-#{$key} { margin-bottom: $value; }
    .u-ml-#{$key} { margin-left: $value; }
    .u-mr-#{$key} { margin-right: $value; }
    .u-mx-#{$key} { margin-left: $value; margin-right: $value; }
    .u-my-#{$key} { margin-top: $value; margin-bottom: $value; }
    
    .u-p-#{$key} { padding: $value; }
    .u-pt-#{$key} { padding-top: $value; }
    .u-pb-#{$key} { padding-bottom: $value; }
    .u-pl-#{$key} { padding-left: $value; }
    .u-pr-#{$key} { padding-right: $value; }
    .u-px-#{$key} { padding-left: $value; padding-right: $value; }
    .u-py-#{$key} { padding-top: $value; padding-bottom: $value; }
  }
}

// Reduced motion mixin
@mixin reduced-motion {
  @media (prefers-reduced-motion: reduce) {
    @content;
  }
}

// High contrast mixin
@mixin high-contrast {
  @media (prefers-contrast: high) {
    @content;
  }
}
3. Generic Layer (scss/03-generic/)
_reset.scss
scss
Copy
// CSS Reset
// =========

*,
*::before,
*::after {
  box-sizing: border-box;
}

* {
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-size-adjust: 100%;
}

body {
  font-family: $font-family-base;
  font-size: $font-size-base;
  line-height: $line-height-base;
  color: color('text-primary');
  background-color: color('bg-secondary');
}

img,
picture,
video,
canvas,
svg {
  display: block;
  max-width: 100%;
  height: auto;
}

input,
button,
textarea,
select {
  font: inherit;
  color: inherit;
}

button {
  cursor: pointer;
  background: none;
  border: none;
}

ul,
ol {
  list-style: none;
}

a {
  color: inherit;
  text-decoration: none;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
}
4. Elements Layer (scss/04-elements/)
_buttons.scss
scss
Copy
// Button Elements
// ===============

.aps-btn {
  @include button-base;
  
  &--primary {
    background: color('primary');
    color: color('bg-primary');
    
    &:hover {
      background: color('primary-hover');
    }
  }
  
  &--secondary {
    background: color('bg-primary');
    color: color('text-primary');
    border-color: color('border');
    
    &:hover {
      background: color('bg-hover');
      border-color: color('text-muted');
    }
  }
  
  &--danger {
    background: color('danger');
    color: color('bg-primary');
    
    &:hover {
      background: #b32d2e;
    }
  }
  
  &--ghost {
    background: transparent;
    color: color('primary');
    
    &:hover {
      background: rgba(color('primary'), 0.1);
    }
  }
  
  &--sm {
    padding: spacing('1') spacing('3');
    font-size: 0.8125rem;
  }
  
  &--lg {
    padding: spacing('3') spacing('6');
    font-size: 1.125rem;
  }
  
  &--block {
    display: flex;
    width: 100%;
  }
  
  &--loading {
    position: relative;
    color: transparent;
    
    &::after {
      content: '';
      position: absolute;
      width: 1rem;
      height: 1rem;
      top: 50%;
      left: 50%;
      margin-top: -0.5rem;
      margin-left: -0.5rem;
      border: 2px solid currentColor;
      border-top-color: transparent;
      border-radius: 50%;
      animation: aps-spin 1s linear infinite;
    }
  }
}

@keyframes aps-spin {
  to { transform: rotate(360deg); }
}

@include reduced-motion {
  .aps-btn {
    transition: none;
    
    &:hover {
      transform: none;
    }
  }
}
_forms.scss
scss
Copy
// Form Elements
// =============

.aps-form-control {
  @include form-control;
  
  &:focus {
    border-color: var(--wp-admin-theme-color, #{color('primary')});
    box-shadow: 0 0 0 1px var(--wp-admin-theme-color, #{color('primary')});
    outline: none;
  }
  
  &[readonly] {
    background: color('bg-secondary');
  }
}

.aps-form-select {
  @include form-control;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23646970' d='M6 9L2 5h8z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right spacing('3') center;
  padding-right: spacing('8');
  
  &:focus {
    border-color: var(--wp-admin-theme-color, #{color('primary')});
    box-shadow: 0 0 0 1px var(--wp-admin-theme-color, #{color('primary')});
    outline: none;
  }
}

.aps-form-textarea {
  @include form-control;
  min-height: 5rem;
  resize: vertical;
  
  &:focus {
    border-color: var(--wp-admin-theme-color, #{color('primary')});
    box-shadow: 0 0 0 1px var(--wp-admin-theme-color, #{color('primary')});
    outline: none;
  }
}

.aps-form-check {
  display: flex;
  align-items: center;
  gap: spacing('2');
  cursor: pointer;
  
  input[type='checkbox'],
  input[type='radio'] {
    width: 1.125rem;
    height: 1.125rem;
    cursor: pointer;
    
    @include focus-visible;
  }
}

.aps-form-group {
  margin-bottom: spacing('4');
}

.aps-form-label {
  display: block;
  margin-bottom: spacing('2');
  font-size: 0.8125rem;
  font-weight: 500;
  color: color('text-primary');
  
  &--required::after {
    content: ' *';
    color: color('danger');
  }
}

.aps-form-hint {
  margin-top: spacing('1');
  font-size: 0.75rem;
  color: color('text-muted');
}
_typography.scss
scss
Copy
// Typography Elements
// ===================

.aps-h1 {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1.25;
  color: color('text-primary');
}

.aps-h2 {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.25;
  color: color('text-primary');
}

.aps-h3 {
  font-size: 1.25rem;
  font-weight: 600;
  line-height: 1.25;
  color: color('text-primary');
}

.aps-h4 {
  font-size: 1.125rem;
  font-weight: 600;
  line-height: 1.25;
  color: color('text-primary');
}

.aps-text-lead {
  font-size: 1.125rem;
  color: color('text-secondary');
  line-height: 1.75;
}

.aps-text-small {
  font-size: 0.8125rem;
  color: color('text-muted');
}

.aps-text-muted {
  color: color('text-muted');
}
5. Objects Layer (scss/05-objects/)
_layout.scss
scss
Copy
// Layout Objects
// ==============

.aps-o-container {
  width: 100%;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
  padding-left: spacing('4');
  padding-right: spacing('4');
  
  &--narrow {
    max-width: 768px;
  }
  
  &--wide {
    max-width: 1400px;
  }
  
  &--full {
    max-width: none;
  }
}

.aps-o-layout {
  display: flex;
  gap: spacing('5');
  
  &--sidebar {
    > :first-child {
      width: 240px;
      flex-shrink: 0;
    }
    
    > :last-child {
      flex: 1;
    }
    
    @include respond-down('md') {
      flex-direction: column;
      
      > :first-child {
        width: 100%;
      }
    }
  }
  
  &--split {
    > * {
      flex: 1;
    }
    
    @include respond-down('md') {
      flex-direction: column;
    }
  }
}
_grid.scss
scss
Copy
// Grid Objects
// ============

.aps-o-grid {
  display: grid;
  gap: spacing('4');
  
  &--2cols {
    grid-template-columns: repeat(2, 1fr);
    
    @include respond-down('md') {
      grid-template-columns: 1fr;
    }
  }
  
  &--3cols {
    grid-template-columns: repeat(3, 1fr);
    
    @include respond-down('md') {
      grid-template-columns: repeat(2, 1fr);
    }
    
    @include respond-down('sm') {
      grid-template-columns: 1fr;
    }
  }
  
  &--4cols {
    grid-template-columns: repeat(4, 1fr);
    
    @include respond-down('lg') {
      grid-template-columns: repeat(2, 1fr);
    }
    
    @include respond-down('sm') {
      grid-template-columns: 1fr;
    }
  }
}
6. Components Layer (scss/06-components/)
_card.scss
scss
Copy
// Card Component
// ==============

.aps-card {
  @include card;
  display: flex;
  flex-direction: column;
  height: 100%;
  
  &--list {
    flex-direction: row;
    gap: spacing('6');
    padding: spacing('6');
    align-items: stretch;
    
    &:hover {
      transform: none;
      box-shadow: shadow('md');
    }
    
    @include respond-down('md') {
      flex-direction: column;
    }
  }
  
  &--compact {
    padding: spacing('4');
    
    .aps-card__media {
      height: 180px;
    }
    
    .aps-card__title {
      font-size: 1rem;
    }
    
    .aps-card__price {
      font-size: 1.25rem;
    }
  }
  
  &__media {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: color('bg-secondary');
    
    @supports not (aspect-ratio: 1) {
      &::before {
        content: '';
        display: block;
        padding-bottom: 56.25%;
      }
      
      img {
        position: absolute;
        top: 0;
        left: 0;
      }
    }
    
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform $transition-base;
    }
    
    .aps-card:hover & img {
      transform: scale(1.05);
    }
  }
  
  &--list &__media {
    width: 200px;
    height: 150px;
    flex-shrink: 0;
    
    @include respond-down('md') {
      width: 100%;
      height: 200px;
    }
  }
  
  &__body {
    padding: spacing('5');
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: spacing('3');
  }
  
  &--list &__body {
    padding: 0;
  }
  
  &__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.25;
    color: color('text-primary');
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    
    a {
      color: inherit;
      transition: color $transition-fast;
      
      &:hover {
        color: color('primary');
      }
    }
  }
  
  &__description {
    margin: 0;
    font-size: $font-size-base;
    line-height: $line-height-base;
    color: color('text-secondary');
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  &--list &__description {
    -webkit-line-clamp:
Your conversation with Kimi is getting too long. Try starting a new session.


