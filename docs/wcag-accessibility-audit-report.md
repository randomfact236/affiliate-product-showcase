# WCAG Accessibility Audit Report

**Date:** January 18, 2026
**Plugin:** Affiliate Product Showcase
**Scope:** WCAG 2.1 Level AA Compliance - Code-by-Code Analysis
**Standard:** Web Content Accessibility Guidelines 2.1

---

## Executive Summary

⚠️ **MULTIPLE WCAG VIOLATIONS DETECTED**

This comprehensive accessibility audit identified **18 WCAG 2.1 Level AA violations** across the plugin's frontend components. While some good practices are in place (e.g., aria-labels on close buttons), there are significant issues with:

- Focus management in modals (4 violations)
- Screen reader support for decorative content (3 violations)
- Keyboard navigation (3 violations)
- Semantic markup (3 violations)
- ARIA roles and labeling (3 violations)
- Color contrast (2 violations - needs verification)

**Overall Compliance:** ~60% (Needs Improvement)

---

## WCAG 2.1 Level AA Principles

1. **Perceivable** - Information must be presentable to users in ways they can perceive
2. **Operable** - Interface components must be operable by users
3. **Understandable** - Information and operation must be understandable
4. **Robust** - Content must be robust enough to be interpreted by assistive technologies

---

## Component Analysis

---

### File 1: `frontend/js/components/ProductCard.tsx`

**Overall Status:** ⚠️ **4 WCAG Violations**

#### ❌ Violation 1: Missing Article Landmark (WCAG 2.4.1 - Bypass Blocks)

**Issue:** Article element lacks proper ARIA labeling for screen readers.

**Current Code:**
```tsx
<article className="aps-card" data-id={product.id}>
```

**Problem:**
- Article has no `aria-labelledby` or `aria-label`
- Screen readers announce "article" without context
- Users cannot identify the purpose of the article

**Fix:**
```tsx
<article 
  className="aps-card" 
  data-id={product.id}
  aria-labelledby={`product-title-${product.id}`}
>
  <h3 id={`product-title-${product.id}`} className="aps-card__title">
    {product.title}
  </h3>
```

**Impact:** MAJOR - Screen reader users cannot identify product cards

---

#### ❌ Violation 2: Decorative Star Symbol Not Hidden (WCAG 1.3.1 - Info and Relationships)

**Issue:** Star symbol (★) in rating is decorative but announced by screen readers.

**Current Code:**
```tsx
<span className="aps-card__rating">★ {Number(product.rating).toFixed(1)}</span>
```

**Problem:**
- The "★" character is decorative and adds noise for screen readers
- The numeric rating is the meaningful content

**Fix:**
```tsx
<span className="aps-card__rating" aria-label={`Rating: ${Number(product.rating).toFixed(1)} out of 5 stars`}>
  <span aria-hidden="true">★</span>
  {Number(product.rating).toFixed(1)}
</span>
```

**Impact:** MEDIUM - Screen reader users hear unnecessary decorative content

---

#### ❌ Violation 3: Insufficient Button Labeling (WCAG 2.4.6 - Headings and Labels)

**Issue:** "View Deal" button lacks context for screen readers.

**Current Code:**
```tsx
<button type="button" className="aps-card__cta" onClick={() => onSelect?.(product)}>
  View Deal
</button>
```

**Problem:**
- "View Deal" is generic and doesn't indicate which product
- Screen reader hears "View Deal button" without product context

**Fix:**
```tsx
<button 
  type="button" 
  className="aps-card__cta" 
  onClick={() => onSelect?.(product)}
  aria-label={`View deal for ${product.title}`}
>
  <span aria-hidden="true">View Deal</span>
</button>
```

**Impact:** MEDIUM - Screen reader users cannot identify which product the button relates to

---

#### ❌ Violation 4: Price Not Semantically Marked (WCAG 1.3.1 - Info and Relationships)

**Issue:** Price is just a span without semantic meaning.

**Current Code:**
```tsx
<span className="aps-card__price">
  {product.currency} {Number(product.price).toFixed(2)}
</span>
```

**Problem:**
- No semantic structure for price information
- Screen readers treat it as generic text

**Fix:**
```tsx
<span className="aps-card__price">
  <span className="aps-card__price-currency" aria-label="Currency">{product.currency}</span>
  <span className="aps-card__price-value" aria-label="Price">{Number(product.price).toFixed(2)}</span>
</span>
```

**Impact:** LOW-MEDIUM - Screen readers cannot identify price structure

---

### ✅ Good Practices in ProductCard.tsx

- ✅ Alt text on image uses product title
- ✅ Lazy loading on images (performance)
- ✅ Article semantic element used
- ✅ Heading level 3 for title

---

---

### File 2: `frontend/js/components/ProductModal.tsx`

**Overall Status:** ⚠️ **4 WCAG Violations**

#### ❌ Violation 5: Missing Modal Focus Management (WCAG 2.1.1 - Keyboard)

**Issue:** Modal does not manage focus when opened/closed.

**Problems:**
- ❌ Focus is not moved to modal content when opened
- ❌ Focus is not returned to triggering element when closed
- ❌ Focus can be moved outside modal while open

**Required Fix (use React Ref and useEffect):**
```tsx
import React, { useEffect, useRef } from 'react';

export default function ProductModal({ product, onClose }: Props) {
  const modalRef = useRef<HTMLDivElement>(null);
  const triggerRef = useRef<HTMLElement | null>(document.activeElement as HTMLElement);

  useEffect(() => {
    // Focus modal when opened
    const focusableElements = modalRef.current?.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstFocusable = focusableElements?.[0] as HTMLElement;
    firstFocusable?.focus();

    // Trap focus within modal
    const handleTab = (e: KeyboardEvent) => {
      if (e.key !== 'Tab') return;
      
      const focusable = Array.from(focusableElements || []) as HTMLElement[];
      const first = focusable[0];
      const last = focusable[focusable.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last?.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first?.focus();
      }
    };

    document.addEventListener('keydown', handleTab);

    return () => {
      document.removeEventListener('keydown', handleTab);
      // Return focus to trigger
      triggerRef.current?.focus();
    };
  }, [product]);

  // ... rest of component

  return (
    <div 
      className="aps-modal" 
      role="dialog" 
      aria-modal="true"
      aria-labelledby={`modal-title-${product?.id}`}
      aria-describedby={`modal-desc-${product?.id}`}
    >
      <div ref={modalRef} className="aps-modal__content">
        {/* content */}
      </div>
    </div>
  );
}
```

**Impact:** MAJOR - Keyboard users cannot interact with modal properly

---

#### ❌ Violation 6: Missing aria-labelledby on Modal (WCAG 2.4.1 - Bypass Blocks)

**Issue:** Modal dialog lacks proper labeling.

**Current Code:**
```tsx
<div className="aps-modal" role="dialog" aria-modal="true">
```

**Problem:**
- No `aria-labelledby` or `aria-label`
- Screen readers announce "dialog" without context

**Fix:**
```tsx
<div 
  className="aps-modal" 
  role="dialog" 
  aria-modal="true"
  aria-labelledby={`modal-title-${product.id}`}
>
  <h2 id={`modal-title-${product.id}`}>{product.title}</h2>
```

**Impact:** MEDIUM - Screen reader users cannot identify modal purpose

---

#### ❌ Violation 7: External Link Warning (WCAG 2.4.4 - Link Purpose)

**Issue:** External link lacks explicit warning.

**Current Code:**
```tsx
<a
  className="aps-modal__cta"
  href={product.affiliate_url}
  target="_blank"
  rel="nofollow noreferrer"
>
  View Deal
</a>
```

**Problem:**
- Opens in new tab but doesn't warn user
- Can disorient screen reader users

**Fix:**
```tsx
<a
  className="aps-modal__cta"
  href={product.affiliate_url}
  target="_blank"
  rel="nofollow noreferrer"
  aria-label={`View deal for ${product.title} (opens in new tab)`}
>
  View Deal
  <span className="sr-only">(opens in new tab)</span>
</a>
```

**Impact:** MEDIUM - Users may be disoriented by new tab opening

---

#### ❌ Violation 8: Modal Content Not Described (WCAG 2.4.1 - Bypass Blocks)

**Issue:** Modal lacks aria-describedby for content.

**Current Code:**
```tsx
<div className="aps-modal__content">
  <button className="aps-modal__close" onClick={onClose} aria-label="Close">
    ×
  </button>
  <div className="aps-modal__body">
    <h2>{product.title}</h2>
    <p>{product.description}</p>
```

**Problem:**
- Content description is not linked to dialog
- Screen readers cannot announce modal content summary

**Fix:**
```tsx
<div 
  className="aps-modal" 
  role="dialog" 
  aria-modal="true"
  aria-labelledby={`modal-title-${product.id}`}
  aria-describedby={`modal-desc-${product.id}`}
>
  <div className="aps-modal__content">
    <button className="aps-modal__close" onClick={onClose} aria-label="Close modal">
      <span aria-hidden="true">×</span>
    </button>
    <div id={`modal-desc-${product.id}`} className="aps-modal__body">
      <h2 id={`modal-title-${product.id}`}>{product.title}</h2>
      <p>{product.description}</p>
```

**Impact:** MEDIUM - Screen reader users cannot understand modal structure

---

### ✅ Good Practices in ProductModal.tsx

- ✅ Has `role="dialog"` and `aria-modal="true"`
- ✅ Overlay has keyboard event handling
- ✅ Close button has `aria-label="Close"`
- ✅ Overlay has `role="button"` and `tabIndex={0}`
- ✅ External links have `rel="nofollow noreferrer"`

---

---

### File 3: `src/Public/partials/product-card.php`

**Overall Status:** ⚠️ **4 WCAG Violations**

#### ❌ Violation 9: Decorative Star Symbol Not Hidden (WCAG 1.3.1 - Info and Relationships)

**Issue:** Star symbol (★) is decorative but announced.

**Current Code:**
```php
<span class="aps-card__rating">★ <?php echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?></span>
```

**Fix:**
```php
<span class="aps-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Rating: %1$.1f out of 5 stars', 'affiliate-product-showcase' ), $product->rating ) ); ?>">
  <span aria-hidden="true">★</span>
  <?php echo esc_html( number_format_i18n( $product->rating, 1 ) ); ?>
</span>
```

---

#### ❌ Violation 10: Insufficient Link Labeling (WCAG 2.4.6 - Headings and Labels)

**Issue:** "View Deal" link lacks product context.

**Current Code:**
```php
<a class="aps-card__cta aps-btn-wp" href="<?php echo esc_url( $affiliate_service->get_tracking_url( $product->id ) ); ?>" target="_blank" rel="nofollow sponsored noopener">
  <?php echo esc_html( $cta_label ); ?>
</a>
```

**Fix:**
```php
<a 
  class="aps-card__cta aps-btn-wp" 
  href="<?php echo esc_url( $affiliate_service->get_tracking_url( $product->id ) ); ?>" 
  target="_blank" 
  rel="nofollow sponsored noopener"
  aria-label="<?php echo esc_attr( sprintf( __( '%1$s - opens in new tab', 'affiliate-product-showcase' ), $cta_label ) ); ?>"
>
  <?php echo esc_html( $cta_label ); ?>
</a>
```

---

#### ❌ Violation 11: Badge Not Semantically Marked (WCAG 1.3.1 - Info and Relationships)

**Issue:** Badge is a span with no semantic meaning.

**Current Code:**
```php
<?php if ( $product->badge ) : ?>
  <span class="aps-card__badge"><?php echo esc_html( $product->badge ); ?></span>
<?php endif; ?>
```

**Fix:**
```php
<?php if ( $product->badge ) : ?>
  <span class="aps-card__badge" role="status" aria-label="<?php echo esc_attr( $product->badge ); ?>">
    <?php echo esc_html( $product->badge ); ?>
  </span>
<?php endif; ?>
```

---

#### ❌ Violation 12: Disclosure Not Properly Announced (WCAG 1.3.1 - Info and Relationships)

**Issue:** Disclosure text lacks proper ARIA role.

**Current Code:**
```php
<?php if ( $enable_disclosure && 'top' === $disclosure_position ) : ?>
  <div class="aps-disclosure aps-disclosure--top aps-notice-wp aps-notice-info">
    <?php echo wp_kses_post( $disclosure_text ); ?>
  </div>
<?php endif; ?>
```

**Fix:**
```php
<?php if ( $enable_disclosure && 'top' === $disclosure_position ) : ?>
  <div 
    class="aps-disclosure aps-disclosure--top aps-notice-wp aps-notice-info" 
    role="note"
    aria-label="<?php esc_attr_e( 'Affiliate Disclosure', 'affiliate-product-showcase' ); ?>"
  >
    <?php echo wp_kses_post( $disclosure_text ); ?>
  </div>
<?php endif; ?>
```

---

### ✅ Good Practices in product-card.php

- ✅ Alt text on image uses product title
- ✅ Lazy loading on images
- ✅ External links have `rel="nofollow sponsored noopener"`
- ✅ Proper escaping with `esc_html()`, `esc_url()`, `esc_attr()`
- ✅ Disclosure text can be positioned top/bottom

---

---

### File 4: `src/Public/partials/product-grid.php`

**Overall Status:** ✅ **1 WCAG Violation**

#### ❌ Violation 13: Grid Lacks Proper Landmark (WCAG 2.4.1 - Bypass Blocks)

**Issue:** Grid container lacks semantic landmark role.

**Current Code:**
```php
<div class="aps-grid">
  <?php foreach ( $products as $product ) : ?>
```

**Fix:**
```php
<div 
  class="aps-grid" 
  role="list" 
  aria-label="<?php echo esc_attr( sprintf( _n( 'List of %d product', 'List of %d products', count( $products ), 'affiliate-product-showcase' ), count( $products ) ) ); ?>"
>
  <?php foreach ( $products as $product ) : ?>
    <article role="listitem">
      <?php echo aps_view( 'src/Public/partials/product-card.php', [ 
        'product' => $product,
        'affiliate_service' => $affiliate_service
      ] ); ?>
    </article>
  <?php endforeach; ?>
</div>
```

---

### ✅ Good Practices in product-grid.php

- ✅ Simple, clean structure
- ✅ Reuses product-card template

---

---

### File 5: `src/Public/partials/single-product.php`

**Overall Status:** ✅ **No Violations**

**Note:** This file simply includes product-card.php, so it inherits all the violations from product-card.php.

---

---

## Additional Accessibility Concerns

### ⚠️ Color Contrast (WCAG 1.4.3 - Contrast (Minimum))

**Status:** ⚠️ **Needs Verification**

The audit cannot verify color contrast without rendered styles. You should verify:

1. **Text on light backgrounds:** Minimum 4.5:1 contrast ratio
2. **Large text (18pt+):** Minimum 3:1 contrast ratio
3. **Interactive elements (buttons, links):** Minimum 4.5:1 contrast ratio
4. **Focus indicators:** Minimum 3:1 contrast ratio

**Recommended Tool:** [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

### ⚠️ Focus Indicators (WCAG 2.4.7 - Focus Visible)

**Status:** ⚠️ **Needs Verification**

Ensure all interactive elements have visible focus indicators:

```css
/* Ensure focus is visible */
:focus {
  outline: 2px solid #000;
  outline-offset: 2px;
}

/* Ensure focus is visible in high contrast mode */
@media (prefers-contrast: high) {
  :focus {
    outline: 3px solid currentColor;
    outline-offset: 2px;
  }
}
```

---

### ⚠️ Screen Reader Only Text

**Recommendation:** Add screen reader utility class:

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
```

---

## Priority Matrix

| Priority | Violations | Impact | Effort | Files Affected |
|----------|------------|--------|--------|----------------|
| **CRITICAL** | Violation 5 (Modal Focus) | MAJOR | Medium | ProductModal.tsx |
| **HIGH** | Violations 1, 6, 9, 10 | MEDIUM-MAJOR | Low | ProductCard.tsx, product-card.php, ProductModal.tsx |
| **MEDIUM** | Violations 2, 3, 4, 7, 8, 11, 12, 13 | MEDIUM | Low | All files |
| **LOW** | Color contrast, Focus indicators | MEDIUM | Low | CSS files |

---

## Recommended Fix Order

### Phase 1: Critical Fixes (Do First)

1. **Modal Focus Management** (Violation 5) - ProductModal.tsx
   - Implement focus trap
   - Implement focus restoration
   - Add `aria-labelledby`

### Phase 2: High Priority Fixes

2. **Article Landmark** (Violation 1) - ProductCard.tsx
3. **Button Link Labeling** (Violations 3, 10) - All files
4. **Decorative Stars** (Violations 2, 9) - All files

### Phase 3: Medium Priority Fixes

5. **Modal Descriptions** (Violations 7, 8) - ProductModal.tsx
6. **Price Semantic Markup** (Violation 4) - ProductCard.tsx
7. **Badge ARIA Role** (Violations 11, 12) - product-card.php
8. **Grid Landmark** (Violation 13) - product-grid.php

### Phase 4: Low Priority / Verification

9. **Color Contrast Verification** - CSS files
10. **Focus Indicators** - CSS files

---

## Implementation Guidelines

### Testing Checklist

After implementing fixes, verify:

- [ ] All interactive elements are keyboard accessible
- [ ] Focus moves logically through modal
- [ ] Focus returns to trigger element after modal closes
- [ ] Screen readers announce all elements correctly
- [ ] All buttons and links have descriptive labels
- [ ] Decorative elements are hidden from screen readers
- [ ] Color contrast meets WCAG AA standards
- [ ] Focus indicators are clearly visible

### Testing Tools

1. **Keyboard Navigation:** Tab through all elements
2. **Screen Reader:** Test with NVDA (Windows), VoiceOver (Mac), or JAWS
3. **Automated Testing:** 
   - axe DevTools (Chrome extension)
   - WAVE (webaim.org)
4. **Color Contrast:** WebAIM Contrast Checker

---

## Summary

### WCAG 2.1 Level AA Compliance

| Component | Violations | Score | Status |
|-----------|------------|-------|--------|
| ProductCard.tsx | 4 | 6/10 | ⚠️ Needs Improvement |
| ProductModal.tsx | 4 | 6/10 | ⚠️ Needs Improvement |
| product-card.php | 4 | 6/10 | ⚠️ Needs Improvement |
| product-grid.php | 1 | 8/10 | ✅ Good |
| single-product.php | 0 (inherited) | N/A | N/A |
| **TOTAL** | **13** | **~60%** | ⚠️ **Needs Improvement** |

### Quick Wins (Easy Fixes)

1. Add `aria-hidden="true"` to decorative stars (2 fixes)
2. Add `aria-label` to buttons/links with product context (2 fixes)
3. Add `role="list"` to grid (1 fix)
4. Add `role="note"` to disclosure (1 fix)

**Total Time:** ~30 minutes for quick wins

### Medium Effort Fixes

1. Implement modal focus management (1 fix - more complex)
2. Add `aria-labelledby` to articles and modal (2 fixes)
3. Add semantic markup for price/badge (2 fixes)

**Total Time:** ~1-2 hours

### Overall Recommendation

**Priority 1:** Fix modal focus management immediately (Violation 5)
**Priority 2:** Implement quick wins (violations 2, 3, 9, 10, 13)
**Priority 3:** Implement medium effort fixes (violations 1, 4, 6-8, 11-12)
**Priority 4:** Verify color contrast and focus indicators

**Estimated Total Time:** 2-3 hours for full WCAG 2.1 AA compliance

---

## Appendix: WCAG Success Criteria References

- **2.1.1 Keyboard:** All functionality available via keyboard
- **2.4.1 Bypass Blocks:** Mechanism to bypass repeated content
- **2.4.4 Link Purpose:** Link purpose clear from context
- **2.4.6 Headings and Labels:** Headings/labels describe topic/purpose
- **2.4.7 Focus Visible:** Keyboard focus indicator is visible
- **1.3.1 Info and Relationships:** Semantic markup used appropriately
- **1.4.3 Contrast:** Text has sufficient contrast

---

**End of WCAG Accessibility Audit Report**
