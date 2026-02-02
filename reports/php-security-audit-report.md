# PHP Security Audit Report

**Generated:** 2026-02-02T18:33:39.578299

## Summary

| Metric | Count |
|--------|-------|
| Files Analyzed | 152 |
| Total Issues | 195 |
| Critical | 84 |
| High | 111 |
| Medium | 0 |
| Low | 0 |

---

## Input Sanitization Issues (82)

| File | Line | Code | Issue | Solution |
|------|------|------|-------|----------|
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 82 | `if (!isset($_POST['nonce']) \|\| !wp_verify_nonce($_POST['nonce'], $action)) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 250 | `'featured' => isset($_POST['featured']) ? filter_var($_POST['featured'], FILT...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 442 | `if (!isset($_POST['nonce']) \|\| !wp_verify_nonce($_POST['nonce'], 'aps_produ...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 587 | `if (!isset($_POST['nonce']) \|\| !wp_verify_nonce($_POST['nonce'], 'aps_produ...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 757 | `$product_data = isset($_POST['data']) && is_array($_POST['data']) ? $_POST['d...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/BulkActions.php` | 252 | `if (!isset($_GET['bulk_action']) \|\| !isset($_GET['processed'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/BulkActions.php` | 354 | `if ( ! isset( $_GET['file'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php` | 197 | `$featured = isset( $_POST['_aps_category_featured'] ) && '1' === $_POST['_aps...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php` | 210 | `$image_url_input = isset( $_POST['_aps_category_image'] )` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php` | 211 | `? wp_unslash( $_POST['_aps_category_image'] )` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php` | 234 | `$is_default = isset( $_POST['_aps_category_is_default'] ) && '1' === $_POST['...` | Unsanitized direct_superglobal | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFields.php` | 401 | `$current_sort_order = isset( $_GET['aps_sort_order'] ) &&` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php` | 80 | `if ( ! isset( $_POST['aps_category_form_nonce'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php` | 144 | `'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/CategoryFormHandler.php` | 146 | `'featured'    => isset( $_POST['featured'] ) && '1' === $_POST['featured'],` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Enqueue.php` | 428 | `if ( $pagenow !== 'edit.php' \|\| ! isset( $_GET['post_type'] ) \|\| $_GET['p...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 126 | `if ( ! isset( $_GET['post'] ) && ! isset( $_GET['post_type'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 131 | `$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 142 | `if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'aps_product' ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 283 | `if (isset($_GET['post_type']) && $_GET['post_type'] === 'aps_product') {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 285 | `if (!isset($_GET['orderby'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 593 | `if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'aps_product' ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 594 | `if ( isset( $_GET['page'] ) && $_GET['page'] === 'add-product' ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 57 | `$selected = isset($_GET['aps_category_filter']) ? (int) $_GET['aps_category_f...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 97 | `$selected = isset($_GET['aps_tag_filter']) ? (int) $_GET['aps_tag_filter'] : 0;` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 127 | `$is_checked = isset($_GET['featured_filter']) ? checked('1', $_GET['featured_...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 147 | `$search_value = isset($_GET['aps_search']) ? esc_attr($_GET['aps_search']) : '';` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 171 | `if (isset($_GET['aps_category_filter']) && !empty($_GET['aps_category_filter'...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 172 | `$category_id = (int) $_GET['aps_category_filter'];` | Unsanitized direct_superglobal | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 190 | `if (isset($_GET['aps_tag_filter']) && !empty($_GET['aps_tag_filter'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 191 | `$tag_id = (int) $_GET['aps_tag_filter'];` | Unsanitized direct_superglobal | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 209 | `if (isset($_GET['featured_filter']) && '1' === $_GET['featured_filter']) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 225 | `if (isset($_GET['aps_status_filter']) && !empty($_GET['aps_status_filter'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFilters.php` | 231 | `if (isset($_GET['aps_search']) && !empty($_GET['aps_search'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 92 | `$is_update = isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] );` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 95 | `$post_id = (int) $_POST['post_id'];` | Unsanitized direct_superglobal | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 198 | `$data['is_draft'] = isset( $_POST['draft'] );` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsAjaxHandler.php` | 70 | `$product_ids = isset($_POST['product_ids']) ? json_decode(stripslashes($_POST...` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsAjaxHandler.php` | 175 | `$price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsAjaxHandler.php` | 178 | `$featured = isset($_POST['featured']) && $_POST['featured'] === '1';` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php` | 96 | `if (isset($_GET['status']) && !empty($_GET['status'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php` | 107 | `if (isset($_GET['category']) && !empty($_GET['category'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php` | 118 | `if (isset($_GET['tag']) && !empty($_GET['tag'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsTable.php` | 128 | `if (isset($_GET['s']) && !empty($_GET['s'])) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php` | 306 | `if ( isset( $_POST['aps_ribbon_color'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php` | 307 | `$color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_color'] ) );` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php` | 316 | `if ( isset( $_POST['aps_ribbon_bg_color'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php` | 317 | `$bg_color = sanitize_hex_color( wp_unslash( $_POST['aps_ribbon_bg_color'] ) );` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/RibbonFields.php` | 326 | `if ( isset( $_POST['aps_ribbon_icon'] ) ) {` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/TagFields.php` | 293 | `$featured = isset( $_POST['_aps_tag_featured'] ) ? '1' : '0';` | Unsanitized unescaped_input | Use sanitize_text_field(), sanitize_email(), or appropriate WordPress sanitization function |
| ... | ... | *32 more issues* | ... | ... |

---

## Output Escaping Issues (40)

| File | Line | Code | Issue | Solution |
|------|------|------|-------|----------|
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Menu.php` | 242 | `echo $featured ? '<span class="aps-featured-star">★</span>' : '';` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Helpers/TemplateHelpers.php` | 43 | `<div class="upload-placeholder" style="<?php echo $placeholderStyle; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Helpers/TemplateHelpers.php` | 109 | `<?php echo $requiredAttr; ?>` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Helpers/TemplateHelpers.php` | 110 | `<?php echo $attrString; ?>>` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/Helpers/TemplateHelpers.php` | 156 | `<?php echo $requiredAttr; ?>` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 22 | `class="aps-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 28 | `class="aps-tab <?php echo $active_tab === 'display' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 34 | `class="aps-tab <?php echo $active_tab === 'products' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 40 | `class="aps-tab <?php echo $active_tab === 'categories' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 46 | `class="aps-tab <?php echo $active_tab === 'tags' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 52 | `class="aps-tab <?php echo $active_tab === 'ribbons' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 58 | `class="aps-tab <?php echo $active_tab === 'performance' ? 'active' : ''; ?> d...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 65 | `class="aps-tab <?php echo $active_tab === 'security' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 71 | `class="aps-tab <?php echo $active_tab === 'import_export' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 77 | `class="aps-tab <?php echo $active_tab === 'shortcodes' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/partials/settings-page.php` | 83 | `class="aps-tab <?php echo $active_tab === 'widgets' ? 'active' : ''; ?>">` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 113 | `<?php echo $this->render_stars( $product->rating ); ?>` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 119 | `<span class="aps-current-price"><?php echo $this->price_formatter->format( $p...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 121 | `<span class="aps-original-price"><?php echo $this->price_formatter->format( $...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 175 | `data-show-price="<?php echo $show_price ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 176 | `data-show-description="<?php echo $show_description ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 177 | `data-show-button="<?php echo $show_button ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 206 | `<span class="aps-current-price"><?php echo $this->price_formatter->format( $p...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/Blocks.php` | 208 | `<span class="aps-original-price"><?php echo $this->price_formatter->format( $...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/index.php` | 75 | `data-show-price="<?php echo $show_price ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/index.php` | 76 | `data-show-description="<?php echo $show_description ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/index.php` | 77 | `data-show-button="<?php echo $show_button ? 'true' : 'false'; ?>"` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-grid-item.php` | 55 | `<?php echo $display_rating; // Already escaped in helper ?>` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-grid-item.php` | 61 | `<span class="aps-current-price"><?php echo $display_price; // Already escaped...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-grid-item.php` | 63 | `<span class="aps-original-price"><?php echo $display_original_price; // Alrea...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-showcase-item.php` | 52 | `<span class="aps-current-price"><?php echo $display_price; // Already escaped...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Blocks/templates/product-showcase-item.php` | 54 | `<span class="aps-original-price"><?php echo $display_original_price; // Alrea...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Public/Enqueue.php` | 151 | `trackingEnabled: <?php echo $this->isTrackingEnabled() ? 'true' : 'false'; ?>,` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Public/Enqueue.php` | 152 | `lazyLoad: <?php echo $this->isLazyLoadEnabled() ? 'true' : 'false'; ?>,` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Public/Widgets.php` | 45 | `echo $args['before_widget'];` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Public/Widgets.php` | 47 | `echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'...` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Public/Widgets.php` | 55 | `echo $args['after_widget'];` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Repositories/CategoryRepository.php` | 45 | `*     echo $category->name;` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Repositories/TagRepository.php` | 45 | `*     echo $tag->name;` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |
| `wp-content/plugins/affiliate-product-showcase/src/Validators/UrlValidator.php` | 128 | `*     echo $result['error'];` | Unescaped output | Use esc_html(), esc_attr(), or appropriate WordPress escaping function |

---

## Capability Check Issues (71)

| File | Line | Code | Issue | Solution |
|------|------|------|-------|----------|
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 113 | `$updated = wp_update_post([` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 132 | `$updated = wp_update_post([` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 150 | `update_post_meta($product_id, '_aps_price', $price);` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 165 | `update_post_meta($product_id, '_aps_original_price', $original_price);` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 177 | `update_post_meta($product_id, '_aps_featured', $featured ? '1' : '0');` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 419 | `$result = wp_update_post([` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 530 | `return update_post_meta($product_id, '_aps_stock_status', 'in_stock');` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 533 | `return update_post_meta($product_id, '_aps_stock_status', 'out_of_stock');` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 536 | `return update_post_meta($product_id, '_aps_featured', '1');` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 539 | `return update_post_meta($product_id, '_aps_featured', '0');` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 542 | `return update_post_meta($product_id, '_aps_clicks', 0);` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/AjaxHandler.php` | 545 | `return wp_delete_post($product_id, true) !== false;` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/BulkActions.php` | 96 | `$result = update_post_meta( $post_id, '_in_stock', $in_stock );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 352 | `$post_id = wp_insert_post(` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 390 | `$result = wp_update_post(` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 420 | `update_post_meta( $post_id, '_aps_price', $data['current_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 421 | `update_post_meta( $post_id, '_aps_currency', $data['currency'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 422 | `update_post_meta( $post_id, '_aps_affiliate_url', $data['affiliate_url'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 423 | `update_post_meta( $post_id, '_aps_image_url', $data['image_url'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 424 | `update_post_meta( $post_id, '_aps_video_url', $data['video_url'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 425 | `update_post_meta( $post_id, '_aps_rating', $data['rating'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 426 | `update_post_meta( $post_id, '_aps_featured', $data['featured'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 427 | `update_post_meta( $post_id, '_aps_stock_status', $data['stock_status'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 428 | `update_post_meta( $post_id, '_aps_seo_title', $data['seo_title'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 429 | `update_post_meta( $post_id, '_aps_seo_description', $data['seo_description'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 432 | `update_post_meta( $post_id, '_aps_logo', $data['logo'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 440 | `update_post_meta( $post_id, '_aps_original_price', $data['original_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 441 | `update_post_meta( $post_id, '_aps_price', $data['current_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 442 | `update_post_meta( $post_id, '_aps_sale_price', $data['original_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 446 | `update_post_meta( $post_id, '_aps_original_price', $existing_original_price );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 449 | `update_post_meta( $post_id, '_aps_price', $existing_sale_price );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 450 | `update_post_meta( $post_id, '_aps_sale_price', $existing_sale_price );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 453 | `update_post_meta( $post_id, '_aps_price', $data['current_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 454 | `delete_post_meta( $post_id, '_aps_sale_price' );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 458 | `delete_post_meta( $post_id, '_aps_original_price' );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 459 | `update_post_meta( $post_id, '_aps_price', $data['current_price'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 460 | `delete_post_meta( $post_id, '_aps_sale_price' );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 465 | `update_post_meta( $post_id, '_aps_discount_percentage', $data['discount_perce...` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 467 | `delete_post_meta( $post_id, '_aps_discount_percentage' );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 471 | `update_post_meta( $post_id, '_aps_platform_requirements', $data['platform_req...` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 472 | `update_post_meta( $post_id, '_aps_version_number', $data['version_number'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 475 | `update_post_meta( $post_id, '_aps_gallery', $data['gallery'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 478 | `update_post_meta( $post_id, '_aps_brand_image', $data['brand_image'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 481 | `update_post_meta( $post_id, '_aps_button_name', ! empty( $data['button_name']...` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 484 | `update_post_meta( $post_id, '_aps_user_count', $data['user_count'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 487 | `update_post_meta( $post_id, '_aps_views', $data['views'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 490 | `update_post_meta( $post_id, '_aps_reviews', $data['reviews'] );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 494 | `update_post_meta( $post_id, '_aps_features', json_encode( $data['features'] ) );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductFormHandler.php` | 496 | `delete_post_meta( $post_id, '_aps_features' );` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| `wp-content/plugins/affiliate-product-showcase/src/Admin/ProductsAjaxHandler.php` | 204 | `$result = wp_update_post($update_data);` | Admin operation without capability check | Add if (!current_user_can("capability")) { wp_die("Unauthorized"); } |
| ... | ... | *21 more issues* | ... | ... |

---

## Nonce Verification Issues (1)

| File | Line | Code | Issue | Solution |
|------|------|------|-------|----------|
| `wp-content/plugins/affiliate-product-showcase/src/Admin/TaxonomyFieldsAbstract.php` | 132 | `add_action( 'wp_ajax_aps_' . $this->get_taxonomy() . '_row_action', [ $this, ...` | AJAX handler without nonce verification | Add check_ajax_referer("action_name", "nonce_field") at the start of the handler |

---

## SQL Injection Issues (1)

| File | Line | Code | Issue | Solution |
|------|------|------|-------|----------|
| `wp-content/plugins/affiliate-product-showcase/src/Rest/HealthController.php` | 91 | `$result = $wpdb->get_var( 'SELECT 1' );` | Potential SQL injection risk | Use $wpdb->prepare("SELECT * FROM table WHERE id = %d", $id) |

---

## Recommendations

### Immediate Action Required

1. **Review SQL Injection Issues** - These pose the highest security risk
2. **Fix Input Sanitization** - Use WordPress sanitization functions:
   - `sanitize_text_field()` for text inputs
   - `sanitize_email()` for emails
   - `esc_url_raw()` for URLs
   - `intval()` / `absint()` for integers
3. **Add Output Escaping** - Use WordPress escaping functions:
   - `esc_html()` for HTML content
   - `esc_attr()` for HTML attributes
   - `esc_url()` for URLs
   - `esc_js()` for JavaScript
4. **Add Capability Checks** - Use `current_user_can()` before admin operations

### Quick Fix Examples

**Before (Unsanitized):**
```php
$name = $_POST["name"];
```

**After (Sanitized):**
```php
$name = sanitize_text_field($_POST["name"]);
```

**Before (Unescaped):**
```php
echo $name;
```

**After (Escaped):**
```php
echo esc_html($name);
```
