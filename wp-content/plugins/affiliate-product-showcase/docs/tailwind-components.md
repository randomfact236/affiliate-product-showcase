# Tailwind Components (Affiliate Product Showcase)

This short reference explains the plugin's Tailwind components and how to use them.

Key points:

- Prefix: all utilities and components are namespaced with the `aps-` prefix (see `tailwind.config.js`).
- Scoped: Tailwind utilities are scoped under `.aps-root` via `important: '.aps-root'`.

Provided components (registered inside `tailwind.config.js`):

- `.aps-btn-wp` — WordPress-styled button (use on links or buttons).
- `.aps-card-wp` — Simple card wrapper used by product listings.
- `.aps-notice-wp` — Admin notice container with variants `.aps-notice-success`, `.aps-notice-warning`, `.aps-notice-error`, `.aps-notice-info`.
- `.aps-input-wp`, `.aps-checkbox-wp` — Form field base styles.

Usage examples (frontend):

Wrap plugin markup in the `.aps-root` container to scope utilities:

```html
<div class="aps-root">
  <article class="aps-card-wp">
    <h3 class="aps-card__title">Product</h3>
    <button class="aps-btn-wp">View Deal</button>
  </article>
</div>
```

Notes:

- If you use the plugin's block or frontend React components, the build output expects the `aps-` classes. Prefer the provided components over ad-hoc utility strings to keep markup stable.
- The `safelist` in `tailwind.config.js` prevents purge from removing dynamic classes (e.g., `aps-grid-cols-...`).

If you'd like, I can add example Blade/PHP templates or a small Storybook/preview page showing each component.
