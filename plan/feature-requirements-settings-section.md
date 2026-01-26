# SECTION 7: DYNAMIC SETTINGS

**What's Included:**
- ✅ Complete settings list for all features
- ✅ Settings architecture design
- ✅ Settings validation and sanitization
- ✅ REST API for settings management
- ✅ **Total:** 103 settings across all features (NO ANALYTICS, NO PERFORMANCE, NO SEO, NO CACHE)

**Milestone:** Fully dynamic settings system for complete plugin configuration

---

# 📋 OVERVIEW

**Purpose:** Create a comprehensive, fully dynamic settings system that allows users to configure all plugin features from a centralized settings page.

**Design Principles:**
- ✅ Organized settings by feature section (Products, Categories, Tags, Ribbons, Display, Security, etc.)
- ✅ Dynamic settings registration (add/remove settings without code changes)
- ✅ Input validation and sanitization
- ✅ Default values for all settings
- ✅ REST API for programmatic access
- ✅ Settings export/import functionality
- ✅ Settings reset to defaults
- ✅ WordPress standards compliance (Settings API)
- ✅ **NO PERFORMANCE SETTINGS** - LiteSpeed/LS Cache handles all performance optimization
- ✅ **NO SEO/INTEGRATION SETTINGS** - Rank Math handles all SEO

**Architecture:**
- Settings stored in WordPress options table
- Organized by sections/tabs
- Dynamic field registration system
- Type-safe value retrieval
- Cached settings for performance

---

# 📊 SETTINGS SUMMARY

| Section | Settings Count | Status |
|----------|----------------|---------|
| **7.1 General** | 4 | ⏸️ Not Started |
| **7.2 Products** | 13 | ⏸️ Not Started |
| **7.3 Categories** | 11 | ⏸️ Not Started |
| **7.4 Tags** | 10 | ⏸️ Not Started |
| **7.5 Ribbons** | 9 | ⏸️ Not Started |
| **7.6 Display** | 20 | ⏸️ Not Started |
| **7.7 Performance** | 0 | ⏸️ SKIPPED (LiteSpeed/LS Cache) |
| **7.8 Security** | 11 | ⏸️ Not Started |
| **7.9 Integration/SEO** | 0 | ⏸️ SKIPPED (Rank Math) |
| **7.10 Import/Export** | 9 | ⏸️ Not Started |
| **7.11 Shortcodes** | 6 | ⏸️ Not Started |
| **7.12 Widgets** | 7 | ⏸️ Not Started |
| **TOTAL** | **103** | ⏸️ Not Started |

---

# 🔧 SETTINGS ARCHITECTURE

## Settings Class Structure

```php
namespace Affiliate_Product_Showcase\Admin;

use Affiliate_Product_Showcase\Constants;

/**
 * Settings Manager
 *
 * Handles all plugin settings including registration, validation,
 * sanitization, and retrieval with caching support.
 *
 * @since 1.0.0
 * @author Development Team
 */
final class SettingsManager {
    private string $option_name = 'aps_settings';
    private array $settings = [];
    private array $defaults = [];
    
    // Settings sections
    public const SECTION_GENERAL = 'general';
    public const SECTION_PRODUCTS = 'products';
    public const SECTION_CATEGORIES = 'categories';
    public const SECTION_TAGS = 'tags';
    public const SECTION_RIBBONS = 'ribbons';
    public const SECTION_DISPLAY = 'display';
    public const SECTION_PERFORMANCE = 'performance';
    public const SECTION_SECURITY = 'security';
    public const SECTION_IMPORT_EXPORT = 'import_export';
    public const SECTION_SHORTCODES = 'shortcodes';
    public const SECTION_WIDGETS = 'widgets';
    
    public function __construct() {
        $this->init_defaults();
        $this->load_settings();
    }
    
    /**
     * Initialize default settings values
     */
    private function init_defaults(): void {
        $this->defaults = [
            // General Settings
            'plugin_version' => '1.0.0',
            'default_currency' => 'USD',
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            
            // Product Settings
            'auto_generate_slugs' => true,
            'enable_click_tracking' => true,
            'enable_conversion_tracking' => true,
            'default_product_status' => 'publish',
            'enable_product_sharing' => false,
            'sharing_platforms' => ['facebook', 'twitter', 'linkedin'],
            
            // Category Settings
            'default_category' => 0,
            'enable_category_hierarchy' => true,
            'category_display_style' => 'grid',
            'category_products_per_page' => 12,
            'category_default_sort' => 'date',
            'category_default_sort_order' => 'DESC',
            
            // Tag Settings
            'tag_display_style' => 'pills',
            'enable_tag_colors' => true,
            'enable_tag_icons' => true,
            'tag_cloud_limit' => 20,
            'tag_cloud_orderby' => 'count',
            
            // Ribbon Settings
            'enable_ribbons' => true,
            'ribbon_default_position' => 'top-right',
            'ribbon_default_bg_color' => '#ff6b6b',
            'ribbon_default_text_color' => '#ffffff',
            'enable_ribbon_animation' => true,
            
            // Display Settings
            'products_per_page' => 12,
            'default_view_mode' => 'grid',
            'grid_columns' => 3,
            'list_columns' => 1,
            'enable_lazy_loading' => true,
            'show_product_price' => true,
            'show_original_price' => true,
            'show_discount_percentage' => true,
            'price_display_format' => '{symbol}{price}',
            
            // Security Settings
            'enable_nonce_verification' => true,
            'enable_rate_limiting' => true,
            'rate_limit_requests_per_minute' => 60,
            'rate_limit_requests_per_hour' => 1000,
            'enable_csrf_protection' => true,
            'sanitize_all_output' => true,
            'enable_xss_protection' => true,
            'enable_content_security_policy' => false,
            'csp_report_only_mode' => true,
            'enable_frame_options' => true,
            'frame_options_value' => 'SAMEORIGIN',
            
            // Import/Export Settings
            'import_encoding' => 'UTF-8',
            'export_format' => 'csv',
            'export_include_images' => false,
        ];
    }
    
    /**
     * Load settings from database
     */
    private function load_settings(): void {
        $this->settings = wp_parse_args(
            get_option($this->option_name, []),
            $this->defaults
        );
    }
    
    /**
     * Get a single setting value
     */
    public function get(string $key, $default = null) {
        return $this->settings[$key] ?? $default ?? $this->defaults[$key] ?? null;
    }
    
    /**
     * Get all settings
     */
    public function get_all(): array {
        return $this->settings;
    }
    
    /**
     * Update a single setting
     */
    public function set(string $key, $value): bool {
        $this->settings[$key] = $value;
        return $this->save();
    }
    
    /**
     * Update multiple settings
     */
    public function set_many(array $settings): bool {
        $this->settings = wp_parse_args($settings, $this->settings);
        return $this->save();
    }
    
    /**
     * Save settings to database
     */
    private function save(): bool {
        return update_option($this->option_name, $this->settings);
    }
    
    /**
     * Reset settings to defaults
     */
    public function reset(): bool {
        $this->settings = $this->defaults;
        return $this->save();
    }
    
    /**
     * Export settings to array
     */
    public function export(): array {
        return $this->settings;
    }
    
    /**
     * Import settings from array
     */
    public function import(array $settings): bool {
        $validated = $this->validate_import($settings);
        if (is_wp_error($validated)) {
            return false;
        }
        $this->settings = wp_parse_args($validated, $this->defaults);
        return $this->save();
    }
    
    /**
     * Validate imported settings
     */
    private function validate_import(array $settings) {
        $validated = [];
        
        foreach ($settings as $key => $value) {
            if (!isset($this->defaults[$key])) {
                continue;
            }
            
            $validated[$key] = $this->sanitize_setting($key, $value);
        }
        
        return $validated;
    }
    
    /**
     * Sanitize setting value based on key
     */
    private function sanitize_setting(string $key, $value) {
        $type = $this->get_setting_type($key);
        
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'url':
                return esc_url_raw($value);
            case 'email':
                return sanitize_email($value);
            case 'text':
                return sanitize_text_field($value);
            case 'html':
                return wp_kses_post($value);
            case 'array':
                return is_array($value) ? $value : [];
            case 'json':
                return json_decode($value, true) ?: [];
            default:
                return $value;
        }
    }
    
    /**
     * Get setting type for sanitization
     */
    private function get_setting_type(string $key): string {
        $types = [
            'plugin_version' => 'text',
            'default_currency' => 'text',
            'date_format' => 'text',
            'time_format' => 'text',
            'auto_generate_slugs' => 'boolean',
            'enable_click_tracking' => 'boolean',
            'enable_conversion_tracking' => 'boolean',
            'default_product_status' => 'text',
            'enable_product_sharing' => 'boolean',
            'sharing_platforms' => 'array',
            'default_category' => 'integer',
            'enable_category_hierarchy' => 'boolean',
            'category_display_style' => 'text',
            'category_products_per_page' => 'integer',
            'category_default_sort' => 'text',
            'category_default_sort_order' => 'text',
            'tag_display_style' => 'text',
            'enable_tag_colors' => 'boolean',
            'enable_tag_icons' => 'boolean',
            'tag_cloud_limit' => 'integer',
            'tag_cloud_orderby' => 'text',
            'enable_ribbons' => 'boolean',
            'ribbon_default_position' => 'text',
            'ribbon_default_bg_color' => 'text',
            'ribbon_default_text_color' => 'text',
            'enable_ribbon_animation' => 'boolean',
            'products_per_page' => 'integer',
            'default_view_mode' => 'text',
            'grid_columns' => 'integer',
            'list_columns' => 'integer',
            'enable_lazy_loading' => 'boolean',
            'show_product_price' => 'boolean',
            'show_original_price' => 'boolean',
            'show_discount_percentage' => 'boolean',
            'price_display_format' => 'text',
            'enable_nonce_verification' => 'boolean',
            'enable_rate_limiting' => 'boolean',
            'rate_limit_requests_per_minute' => 'integer',
            'rate_limit_requests_per_hour' => 'integer',
            'enable_csrf_protection' => 'boolean',
            'sanitize_all_output' => 'boolean',
            'enable_xss_protection' => 'boolean',
            'enable_content_security_policy' => 'boolean',
            'csp_report_only_mode' => 'boolean',
            'enable_frame_options' => 'boolean',
            'frame_options_value' => 'text',
            'import_encoding' => 'text',
            'export_format' => 'text',
            'export_include_images' => 'boolean',
        ];
        
        return $types[$key] ?? 'text';
    }
}
```

---

# 📊 COMPLETE SETTINGS LIST

## SECTION 7.1: GENERAL SETTINGS (4 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `plugin_version` | text | '1.0.0' | Plugin version number (read-only) | - |
| `default_currency` | select | 'USD' | Default currency for all products | USD, EUR, GBP, JPY, AUD, CAD, CHF, CNY, INR |
| `date_format` | select | WP default | Date format for display | WordPress date formats |
| `time_format` | select | WP default | Time format for display | WordPress time formats |

---

## SECTION 7.2: PRODUCT SETTINGS (13 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `auto_generate_slugs` | checkbox | true | Automatically generate slugs from titles | - |
| `enable_click_tracking` | checkbox | true | Track affiliate link clicks | - |
| `enable_conversion_tracking` | checkbox | true | Track product conversions | - |
| `default_product_status` | select | 'publish' | Default status for new products | draft, publish, pending, private |
| `enable_product_sharing` | checkbox | false | Enable social sharing buttons | - |
| `sharing_platforms` | multiselect | ['facebook', 'twitter', 'linkedin'] | Social platforms for sharing | facebook, twitter, linkedin, pinterest, whatsapp |
| `show_product_version` | checkbox | true | Display product version number | - |
| `show_platform_requirements` | checkbox | true | Display platform requirements | - |
| `enable_product_tabs` | checkbox | true | Enable tabbed product display | - |
| `product_tabs_order` | text | 'description,specs,faq,requirements' | Order of product tabs | Comma-separated |
| `enable_product_ratings` | checkbox | false | Enable product ratings system | - |
| `enable_product_reviews` | checkbox | false | Enable product reviews | - |
| `enable_wishlist` | checkbox | false | Enable wishlist functionality | - |

---

## SECTION 7.3: CATEGORY SETTINGS (11 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `default_category` | select | 0 | Default category for unassigned products | All categories |
| `enable_category_hierarchy` | checkbox | true | Enable category hierarchy (parent/child) | - |
| `category_display_style` | select | 'grid' | Default category display style | grid, list, compact |
| `category_products_per_page` | number | 12 | Products per category page | 6, 12, 18, 24, 36, 48 |
| `category_default_sort` | select | 'date' | Default sort order for categories | name, price, date, popularity, random |
| `category_default_sort_order` | select | 'DESC' | Default sort direction | ASC, DESC |
| `show_category_description` | checkbox | true | Show category description | - |
| `show_category_image` | checkbox | true | Show category image | - |
| `show_category_count` | checkbox | true | Show product count per category | - |
| `enable_category_featured_products` | checkbox | false | Enable featured products per category | - |
| `category_featured_products_limit` | number | 4 | Number of featured products to show | 1, 2, 3, 4, 6, 8 |
| `enable_empty_category_display` | checkbox | false | Display empty categories | - |

---

## SECTION 7.4: TAG SETTINGS (10 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `tag_display_style` | select | 'pills' | Default tag display style | pills, badges, links, dropdown |
| `enable_tag_colors` | checkbox | true | Enable custom tag colors | - |
| `enable_tag_icons` | checkbox | true | Enable tag icons (emoji/SVG) | - |
| `tag_cloud_limit` | number | 20 | Number of tags in tag cloud | 10, 20, 30, 40, 50 |
| `tag_cloud_orderby` | select | 'count' | Tag cloud ordering | name, count, slug, random |
| `tag_cloud_order` | select | 'DESC' | Tag cloud order direction | ASC, DESC |
| `show_tag_description` | checkbox | false | Show tag description | - |
| `show_tag_count` | checkbox | true | Show product count per tag | - |
| `enable_tag_filtering` | checkbox | true | Enable tag filtering on product pages | - |
| `tag_filter_display_mode` | select | 'checkboxes' | Tag filter display mode | checkboxes, links, dropdown |

---

## SECTION 7.5: RIBBON SETTINGS (9 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `enable_ribbons` | checkbox | true | Enable ribbon/badge system | - |
| `ribbon_default_position` | select | 'top-right' | Default ribbon position | top-left, top-right, bottom-left, bottom-right |
| `ribbon_default_bg_color` | color | '#ff6b6b' | Default ribbon background color | Any hex color |
| `ribbon_default_text_color` | color | '#ffffff' | Default ribbon text color | Any hex color |
| `enable_ribbon_animation` | checkbox | true | Enable ribbon hover animations | - |
| `ribbon_animation_type` | select | 'pulse' | Ribbon animation type | pulse, bounce, shake, none |
| `ribbon_size` | select | 'medium' | Ribbon badge size | small, medium, large |
| `enable_ribbon_priority` | checkbox | false | Enable ribbon priority system | - |
| `max_ribbons_per_product` | number | 1 | Maximum ribbons per product | 1, 2, 3 |

---

## SECTION 7.6: DISPLAY SETTINGS (20 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `products_per_page` | number | 12 | Default products per page | 6, 12, 18, 24, 36, 48 |
| `default_view_mode` | select | 'grid' | Default product view mode | grid, list |
| `enable_view_mode_toggle` | checkbox | true | Allow users to toggle view mode | - |
| `grid_columns` | number | 3 | Number of columns in grid view | 2, 3, 4, 5 |
| `list_columns` | number | 1 | Number of columns in list view | 1, 2 |
| `enable_lazy_loading` | checkbox | true | Enable image lazy loading | - |
| `lazy_load_threshold` | number | 50 | Number of products before lazy loading | 20, 50, 100 |
| `show_product_price` | checkbox | true | Show product price | - |
| `show_original_price` | checkbox | true | Show original price (if discount) | - |
| `show_discount_percentage` | checkbox | true | Show discount percentage | - |
| `price_display_format` | text | '{symbol}{price}' | Price display format | Customizable |
| `show_currency_symbol` | checkbox | true | Show currency symbol | - |
| `show_product_sku` | checkbox | false | Show product SKU | - |
| `show_product_brand` | checkbox | true | Show product brand | - |
| `show_product_rating` | checkbox | false | Show product rating | - |
| `show_product_clicks` | checkbox | false | Show product click count | - |
| `enable_product_quick_view` | checkbox | false | Enable product quick view modal | - |
| `quick_view_animation` | select | 'fade' | Quick view animation | fade, slide, zoom |
| `enable_product_comparison` | checkbox | false | Enable product comparison | - |
| `max_comparison_items` | number | 4 | Maximum items for comparison | 2, 3, 4, 5 |

---

## SECTION 7.7: PERFORMANCE SETTINGS (SKIPPED)

**Status:** ⏸️ SKIPPED - LiteSpeed/LS Cache handles all performance optimization

**Reason:** You're using LiteSpeed server with LS Cache plugin, which provides:
- Page caching
- Object caching
- Database caching
- Image optimization
- WebP conversion
- Critical CSS
- Lazy loading
- Minification (CSS/JS)
- CDN integration

**No settings needed in your plugin** - rely on LiteSpeed/LS Cache for all performance optimization.

---

## SECTION 7.8: SECURITY SETTINGS (11 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `enable_nonce_verification` | checkbox | true | Verify nonces on all form submissions | - |
| `enable_rate_limiting` | checkbox | true | Enable API rate limiting | - |
| `rate_limit_requests_per_minute` | number | 60 | API requests per minute limit | 30, 60, 90, 120 |
| `rate_limit_requests_per_hour` | number | 1000 | API requests per hour limit | 500, 1000, 2000, 5000 |
| `enable_csrf_protection` | checkbox | true | Enable CSRF protection | - |
| `sanitize_all_output` | checkbox | true | Sanitize all output data | - |
| `enable_xss_protection` | checkbox | true | Enable XSS protection headers | - |
| `enable_content_security_policy` | checkbox | false | Enable Content Security Policy | - |
| `csp_report_only_mode` | checkbox | true | CSP report-only mode (development) | - |
| `enable_frame_options` | checkbox | true | Set X-Frame-Options header | - |
| `frame_options_value` | select | 'SAMEORIGIN' | X-Frame-Options value | SAMEORIGIN, DENY |

---

## SECTION 7.9: INTEGRATION/SEO SETTINGS (SKIPPED)

**Status:** ⏸️ SKIPPED - Rank Math handles all SEO

**Reason:** You're using Rank Math SEO plugin, which provides:
- Schema markup (Product, Course, etc.)
- Open Graph tags
- Twitter Cards
- Canonical URLs
- JSON-LD
- Breadcrumb schema
- Rating/Review schema

**Your Plugin Should Do:**
- Let Rank Math handle ALL SEO
- Your plugin just creates/manages frontend content
- Rank Math automatically picks it up

**Optional Enhancement:**
- Detect if Rank Math is active
- Show notice in settings: "SEO handled by Rank Math ✓"

**No settings needed in your plugin** - rely on Rank Math for all SEO functionality.

---

## SECTION 7.10: IMPORT/EXPORT SETTINGS (9 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `import_encoding` | select | 'UTF-8' | Import file encoding | UTF-8, ISO-8859-1, Windows-1252 |
| `export_format` | select | 'csv' | Default export format | csv, xml, json |
| `export_include_images` | checkbox | false | Include image URLs in export | - |
| `export_include_metadata` | checkbox | true | Include metadata in export | - |
| `export_delimiter` | select | ',' | CSV delimiter character | comma, semicolon, tab |
| `export_enclosure` | select | '"' | CSV enclosure character | double-quote, single-quote, none |
| `export_line_ending` | select | 'CRLF' | CSV line ending style | CRLF, LF, CR |
| `enable_auto_backup` | checkbox | false | Enable automatic backups | - |
| `backup_frequency` | select | 'daily' | Backup frequency | daily, weekly, monthly |
| `backup_retention` | number | 7 | Number of backups to retain | 1, 3, 7, 14, 30 |

---

## SECTION 7.11: SHORTCODE SETTINGS (6 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `product_grid_shortcode_id` | text | 'affiliate_product_grid' | Product grid shortcode ID | Customizable |
| `featured_products_shortcode_id` | text | 'affiliate_featured_products' | Featured products shortcode ID | Customizable |
| `product_slider_shortcode_id` | text | 'affiliate_product_slider' | Product slider shortcode ID | Customizable |
| `shortcode_products_per_page` | number | 12 | Default number of products per shortcode | 6, 12, 18, 24, 36, 48 |
| `add_to_cart_button_style` | select | 'default' | Add to cart button style | default, primary, secondary, flat |
| `enable_quick_view_shortcode` | checkbox | true | Enable quick view in shortcodes | - |

**Note:** Cache settings removed - LiteSpeed/LS Cache handles all caching.

---

## SECTION 7.12: WIDGET SETTINGS (7 Settings)

| Setting Key | Type | Default | Description | Options |
|-------------|------|----------|-------------|----------|
| `enable_product_widget` | checkbox | true | Enable product list widget | - |
| `enable_featured_widget` | checkbox | true | Enable featured products widget | - |
| `enable_category_widget` | checkbox | true | Enable category widget | - |
| `enable_sale_widget` | checkbox | true | Enable sale products widget | - |
| `widget_default_layout` | select | 'grid' | Default widget layout | grid, list, compact |
| `widget_image_size` | select | 'thumbnail' | Widget image size | thumbnail, medium, large, full |
| `widget_lazy_loading` | checkbox | true | Lazy load widget content | - |

---

# 🎨 SETTINGS PAGE UI DESIGN

## Settings Page Structure

```
Affiliate Product Showcase Settings
├── General (General Settings)
├── Products (Product Configuration)
├── Categories (Category Configuration)
├── Tags (Tag Configuration)
├── Ribbons (Ribbon Configuration)
├── Display (Frontend Display)
├── Security (Security Configuration)
├── Import/Export (Data Management)
├── Shortcodes (Shortcode Configuration)
└── Widgets (Widget Configuration)
```

**Removed Sections:**
- ❌ Performance (LiteSpeed/LS Cache handles this)
- ❌ Integration/SEO (Rank Math handles this)

## Settings Field Types

1. **Text Input** - For short text values
2. **Textarea** - For longer text
3. **Number Input** - For numeric values
4. **Select Dropdown** - For single choice from options
5. **Multi-select** - For multiple choices
6. **Checkbox** - For boolean toggle
7. **Radio Buttons** - For single choice from few options
8. **Color Picker** - For color selection
9. **URL Input** - For URL values
10. **Email Input** - For email addresses
11. **File Upload** - For uploading files
12. **Toggle Switch** - Modern checkbox variant
13. **Range Slider** - For numeric range selection
14. **Image Upload** - WordPress media uploader

---

# 🔌 REST API FOR SETTINGS

## Settings API Endpoints

### GET /v1/settings
Get all settings or specific setting

**Query Parameters:**
- `key` (optional): Get specific setting key
- `section` (optional): Get settings by section

**Response:**
```json
{
  "plugin_version": "1.0.0",
  "default_currency": "USD",
  "products_per_page": 12
}
```

### POST /v1/settings
Update one or more settings

**Request Body:**
```json
{
  "products_per_page": 24,
  "default_view_mode": "list"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "updated": {
    "products_per_page": 24,
    "default_view_mode": "list"
  }
}
```

### POST /v1/settings/reset
Reset settings to defaults

**Request Body:**
```json
{
  "section": "products"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Products settings reset to defaults"
}
```

### POST /v1/settings/export
Export settings to file

**Request Body:**
```json
{
  "format": "json"
}
```

**Response:**
```json
{
  "success": true,
  "download_url": "https://example.com/wp-content/uploads/aps-settings-export-20250125.json"
}
```

### POST /v1/settings/import
Import settings from file

**Request:**
- `file`: Settings file (JSON/CSV)
- `section` (optional): Import only specific section

**Response:**
```json
{
  "success": true,
  "imported": 50,
  "skipped": 2,
  "errors": 0
}
```

---

# ✅ SETTINGS IMPLEMENTATION CHECKLIST

## Phase 1: Core Settings Infrastructure
- [ ] Create SettingsManager class
- [ ] Define all default values (103 settings)
- [ ] Implement get/set methods
- [ ] Implement caching mechanism
- [ ] Add sanitization functions
- [ ] Add validation functions

## Phase 2: Settings Page UI
- [ ] Create settings page structure (10 tabs)
- [ ] Implement tabbed navigation
- [ ] Create field type components (14 types)
- [ ] Implement form handling
- [ ] Add settings registration system
- [ ] Create settings templates

## Phase 3: Settings REST API
- [ ] Register settings routes
- [ ] Implement GET /v1/settings
- [ ] Implement POST /v1/settings
- [ ] Implement POST /v1/settings/reset
- [ ] Implement POST /v1/settings/export
- [ ] Implement POST /v1/settings/import
- [ ] Add authentication/authorization

## Phase 4: Settings Validation & Sanitization
- [ ] Implement input validation
- [ ] Add sanitization for all field types
- [ ] Add error handling
- [ ] Add success/error feedback
- [ ] Test edge cases

## Phase 5: Settings Testing
- [ ] Unit tests for SettingsManager (90%+ coverage)
- [ ] Integration tests for API endpoints
- [ ] UI testing for settings page
- [ ] Cross-browser testing
- [ ] Accessibility testing (WCAG 2.1 AA)

---

# 📝 NOTES

## External Plugin Integration

### LiteSpeed/LS Cache
**Purpose:** Performance optimization

**Handles:**
- Page caching
- Object caching
- Database caching
- Image optimization
- WebP conversion
- Critical CSS
- Lazy loading
- Minification (CSS/JS)
- CDN integration

**Plugin Configuration:**
- Configure in LiteSpeed Cache plugin settings
- Your plugin automatically benefits from LiteSpeed optimizations

### Rank Math SEO
**Purpose:** SEO optimization

**Handles:**
- Schema markup (Product, Course, etc.)
- Open Graph tags
- Twitter Cards
- Canonical URLs
- JSON-LD
- Breadcrumb schema
- Rating/Review schema

**Plugin Configuration:**
- Configure in Rank Math settings
- Your plugin automatically provides content for Rank Math to optimize

### WebP Conversion Plugin
**Purpose:** Image format conversion

**Handles:**
- Automatic WebP conversion
- Serve WebP to supported browsers
- Fallback to original formats

**Plugin Configuration:**
- Configure in WebP plugin settings
- Your plugin images automatically converted to WebP

**Note:** Use existing WebP plugin rather than implementing in your plugin to avoid duplicate functionality.

---

**Status Tracking:**

- **Section 7: Settings:** [0]/104 complete (0%) for Phase 1
  - ❌ Settings Infrastructure (0/6 complete)
  - ❌ Settings Page UI (0/6 complete)
  - ❌ Settings REST API (0/6 complete)
  - ❌ Settings Validation (0/5 complete)
  - ❌ Settings Testing (0/5 complete)

---

**Last Updated:** 2026-01-26  
**Version:** 1.1.0 (Updated - 104 Settings, Skip Performance/SEO)  
**Maintainer:** Development Team