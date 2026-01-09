```markdown
affiliate-product-showcase/
├── affiliate-product-showcase.php    # Main plugin file
├── readme.txt                        # WordPress.org readme
├── uninstall.php                     # Cleanup on uninstall
├── composer.json                     # PHP dependencies
├── package.json                      # Node dependencies
├── .gitignore
├── phpcs.xml                         # Code standards
├── README.md                         # Developer docs
│
├── includes/                         # Core PHP Logic (Enterprise Layer)
│   ├── class-plugin.php              # Main orchestrator
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── class-loader.php              # Hook manager
│   ├── class-assets.php              # Asset loader (reads manifest.json)
│   ├── class-cache.php               # Transients/Object Cache wrapper
│   ├── class-blocks.php              # Block registry
│   │
│   ├── abstracts/
│   │   ├── abstract-repository.php
│   │   ├── abstract-service.php
│   │   └── abstract-validator.php
│   │
│   ├── interfaces/
│   │   ├── repository-interface.php
│   │   └── service-interface.php
│   │
│   ├── traits/
│   │   ├── singleton-trait.php
│   │   └── hooks-trait.php
│   │
│   ├── models/
│   │   ├── class-product.php
│   │   └── class-affiliate-link.php
│   │
│   ├── repositories/
│   │   ├── class-product-repository.php
│   │   └── class-settings-repository.php
│   │
│   ├── services/
│   │   ├── class-product-service.php
│   │   ├── class-affiliate-service.php
│   │   └── class-analytics-service.php
│   │
│   ├── validators/
│   │   └── class-product-validator.php
│   │
│   ├── sanitizers/
│   │   └── class-input-sanitizer.php
│   │
│   ├── formatters/
│   │   └── class-price-formatter.php
│   │
│   ├── factories/
│   │   └── class-product-factory.php
│   │
│   ├── exceptions/
│   │   └── class-plugin-exception.php
│   │
│   ├── helpers/
│   │   └── functions.php
│   │
│   └── hooks/
│       ├── class-admin-hooks.php
│       └── class-public-hooks.php
│
│   # Implementation / namespaced variants present in the repository
│   ├── Plugin/
│   │   ├── Plugin.php               # includes/Plugin/Plugin.php (PSR-style)
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   └── Loader.php
│   
│   ├── Assets/
│   │   └── Assets.php
│   
│   ├── Blocks/
│   │   └── Blocks.php
│   
│   ├── Cache/
│   │   └── Cache.php
│
├── admin/                            # Admin Interface
│   ├── class-admin.php               # Admin controller
│   ├── class-settings.php            # Settings API
│   ├── class-meta-boxes.php          # Meta box handler
│   ├── Admin.php                     # Concrete admin helper (implementation variant)
│   ├── MetaBoxes.php                 # Implementation helper
│   └── partials/                     # Admin templates
│       ├── settings-page.php
│       ├── product-meta-box.php
│       └── dashboard-widget.php
│
├── public/                           # Frontend
│   ├── class-public.php              # Public controller
│   ├── class-shortcodes.php          # Shortcode handlers
│   ├── class-widgets.php             # Widget handlers
│   ├── PublicSite.php                # Concrete public helper (implementation variant)
│   ├── Shortcodes.php                # Implementation helper
│   └── partials/                     # Frontend templates
│       ├── product-grid.php
│       ├── product-card.php
│       └── single-product.php
│
├── blocks/                           # Gutenberg Blocks
│   ├── product-showcase/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── edit.js
│   │   ├── save.js
│   │   └── style.scss
│   └── product-grid/
│       ├── block.json
│       ├── index.js
│       ├── edit.js
│       ├── save.js
│       └── style.scss
│
├── src/                              # Modern Source Files
│   ├── js/                           # JavaScript source
│   │   ├── admin.js
│   │   ├── frontend.js
│   │   ├── blocks.js
│   │   ├── components/
│   │   │   ├── ProductCard.jsx
│   │   │   ├── Modal.jsx
│   │   │   └── LoadingSpinner.jsx
│   │   └── utils/
│   │       ├── api.js
│   │       ├── helpers.js
│   │       └── validation.js
│   │
│   ├── styles/                       # Style source
│   │   ├── tailwind.css
│   │   ├── admin.scss
│   │   ├── frontend.scss
│   │   ├── editor.scss
│   │   └── components/
│   │       ├── _buttons.scss
│   │       ├── _cards.scss
│   │       └── _forms.scss
│   │
│   ├── tailwind.config.js
│   ├── vite.config.js
│   └── postcss.config.js
│
├── assets/                           # Build Output & Static
│   ├── dist/                         # Compiled assets [generated]
│   │   ├── css/                      # [generated]
│   │   │   ├── admin-[hash].css      # [generated]
│   │   │   ├── frontend-[hash].css   # [generated]
│   │   │   └── editor-[hash].css     # [generated]
│   │   ├── js/                       # [generated]
│   │   │   ├── admin-[hash].js       # [generated]
│   │   │   ├── frontend-[hash].js    # [generated]
│   │   │   └── blocks-[hash].js      # [generated]
│   │   └── manifest.json             # Read by includes/class-assets.php
│   ├── images/                       # [to be added]
│   │   ├── logo.svg                  # [to be added]
│   │   └── placeholder.png           # [to be added]
│   └── fonts/                        # [to be added]
│
├── api/                              # REST API
│   ├── class-rest-controller.php
│   ├── class-products-endpoint.php
│   ├── class-analytics-endpoint.php
│   ├── RestController.php            # Implementation variant
│   ├── ProductsEndpoint.php
│   └── AnalyticsEndpoint.php
│
├── cli/                              # WP-CLI Commands
│   ├── class-product-command.php
│   └── ProductCommand.php            # Implementation variant
│
├── languages/                        # Internationalization
│   └── affiliate-product-showcase.pot
│
├── tests/                            # Testing
│   ├── bootstrap.php
│   ├── unit/
│   │   └── test-product-service.php
│   ├── integration/
│   │   └── test-api-endpoints.php
│   └── fixtures/
│       └── sample-products.php
│
├── docs/                             # Documentation
│   ├── user-guide/                   # [to be added]
│   └── developer/                    # [to be added]
│
└── vendor/                           # Composer dependencies (gitignored) — populate with `composer install` locally

```

Notes:
- Items marked `[generated]` are build outputs (run `npm install` and `npm run build` in the plugin root to generate them).
- Items marked `[to be added]` are placeholders for design assets or documentation to be provided later.
- `vendor/` is intentionally gitignored; run `composer install` to populate locally.
affiliate-product-showcase/
├── affiliate-product-showcase.php    # Main plugin file
├── readme.txt                        # WordPress.org readme
├── uninstall.php                     # Cleanup on uninstall
├── composer.json                     # PHP dependencies
├── package.json                      # Node dependencies
├── .gitignore
├── phpcs.xml                         # Code standards
├── README.md                         # Developer docs
│
├── includes/                         # Core PHP Logic (Enterprise Layer)
│   ├── class-plugin.php              # Main orchestrator
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── class-loader.php              # Hook manager
│   ├── class-assets.php              # Asset loader (reads manifest.json)
│   ├── class-cache.php               # Transients/Object Cache wrapper
│   ├── class-blocks.php              # Block registry
│   │
│   ├── abstracts/
│   │   ├── abstract-repository.php
│   │   ├── abstract-service.php
│   │   └── abstract-validator.php
│   │
│   ├── interfaces/
│   │   ├── repository-interface.php
│   │   └── service-interface.php
│   │
│   ├── traits/
│   │   ├── singleton-trait.php
│   │   └── hooks-trait.php
│   │
│   ├── models/
│   │   ├── class-product.php
│   │   └── class-affiliate-link.php
│   │
│   ├── repositories/
│   │   ├── class-product-repository.php
│   │   └── class-settings-repository.php
│   │
│   ├── services/
│   │   ├── class-product-service.php
│   │   ├── class-affiliate-service.php
│   │   └── class-analytics-service.php
│   │
│   ├── validators/
│   │   └── class-product-validator.php
│   │
│   ├── sanitizers/
│   │   └── class-input-sanitizer.php
│   │
│   ├── formatters/
│   │   └── class-price-formatter.php
│   │
│   ├── factories/
│   │   └── class-product-factory.php
│   │
│   ├── exceptions/
│   │   └── class-plugin-exception.php
│   │
│   ├── helpers/
│   │   └── functions.php
│   │
│   └── hooks/
│       ├── class-admin-hooks.php
│       └── class-public-hooks.php
│
├── admin/                            # Admin Interface
│   ├── class-admin.php               # Admin controller
│   ├── class-settings.php            # Settings API
│   ├── class-meta-boxes.php          # Meta box handler
│   └── partials/                     # Admin templates
│       ├── settings-page.php
│       ├── product-meta-box.php
│       └── dashboard-widget.php
│
├── public/                           # Frontend
│   ├── class-public.php              # Public controller
│   ├── class-shortcodes.php          # Shortcode handlers
│   ├── class-widgets.php             # Widget handlers
│   └── partials/                     # Frontend templates
│       ├── product-grid.php
│       ├── product-card.php
│       └── single-product.php
│
├── blocks/                           # Gutenberg Blocks
│   ├── product-showcase/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── edit.js
│   │   ├── save.js
│   │   └── style.scss
│   └── product-grid/
│       ├── block.json
│       ├── index.js
│       ├── edit.js
│       ├── save.js
│       └── style.scss
│
├── src/                              # Modern Source Files
│   ├── js/                           # JavaScript source
│   │   ├── admin.js
│   │   ├── frontend.js
│   │   ├── blocks.js
│   │   ├── components/
│   │   │   ├── ProductCard.jsx
│   │   │   ├── Modal.jsx
│   │   │   └── LoadingSpinner.jsx
│   │   └── utils/
│   │       ├── api.js
│   │       ├── helpers.js
│   │       └── validation.js
│   │
│   ├── styles/                       # Style source
│   │   ├── tailwind.css
│   │   ├── admin.scss
│   │   ├── frontend.scss
│   │   ├── editor.scss
│   │   └── components/
│   │       ├── _buttons.scss
│   │       ├── _cards.scss
│   │       └── _forms.scss
│   │
│   ├── tailwind.config.js
│   ├── vite.config.js
│   └── postcss.config.js
│
├── assets/                           # Build Output & Static
│   ├── dist/                         # Compiled assets
│   │   ├── css/
│   │   │   ├── admin-[hash].css
│   │   │   ├── frontend-[hash].css
│   │   │   └── editor-[hash].css
│   │   ├── js/
│   │   │   ├── admin-[hash].js
│   │   │   ├── frontend-[hash].js
│   │   │   └── blocks-[hash].js
│   │   └── manifest.json             # Read by includes/class-assets.php
│   ├── images/
│   │   ├── logo.svg
│   │   └── placeholder.png
│   └── fonts/
│
├── api/                              # REST API
│   ├── class-rest-controller.php
│   ├── class-products-endpoint.php
│   └── class-analytics-endpoint.php
│
├── cli/                              # WP-CLI Commands
│   └── class-product-command.php
│
├── languages/                        # Internationalization
│   └── affiliate-product-showcase.pot
│
├── tests/                            # Testing
│   ├── bootstrap.php
│   ├── unit/
│   │   └── test-product-service.php
│   ├── integration/
│   │   └── test-api-endpoints.php
│   └── fixtures/
│       └── sample-products.php
│
├── docs/                             # Documentation
│   ├── user-guide/
│   └── developer/
│
└── vendor/                           # Composer dependencies (gitignored)
