/**
 * Column Renderer Trait
 *
 * Provides common methods for rendering table columns.
 *
 * @package AffiliateProductShowcase\Admin\Traits
 * @since 1.0.0
 */

declare(strict_types=1);

namespace AffiliateProductShowcase\Admin\Traits;

use AffiliateProductShowcase\Admin\Config\ProductConfig;
use AffiliateProductShowcase\Admin\Helpers\ProductHelpers;

trait ColumnRenderer {
    
    /**
     * Render taxonomy list as comma-separated text
     *
     * @param array $items Array of items
     * @param string $class CSS class name
     * @return string Rendered HTML
     */
    protected function render_taxonomy_list(array $items, string $class): string {
        if (empty($items)) {
            return sprintf('<span class="%s">—</span>', $class);
        }
        
        return implode(', ', array_map('esc_html', $items));
    }
    
    /**
     * Render empty indicator for boolean values
     *
     * @param bool $value Value to render
     * @return string Rendered HTML
     */
    protected function render_empty_indicator(bool $value): string {
        return $value ? '<span class="aps-featured-star">★</span>' : '';
    }
    
    /**
     * Render product logo
     *
     * @param string $logo_url Logo URL
     * @return string Rendered HTML
     */
    protected function render_logo(string $logo_url): string {
        if (empty($logo_url)) {
            return '<span class="aps-no-logo">—</span>';
        }

        $width = ProductConfig::LOGO_DIMENSIONS['width'];
        $height = ProductConfig::LOGO_DIMENSIONS['height'];

        return sprintf(
            '<img src="%s" alt="" class="aps-product-logo" width="%d" height="%d" />',
            esc_url($logo_url),
            $width,
            $height
        );
    }
    
    /**
     * Render price with optional discount badge
     *
     * @param float $price Current price
     * @param string $currency Currency code
     * @param float|null $original_price Original price for discount calculation
     * @return string Rendered HTML
     */
    protected function render_price(float $price, string $currency, ?float $original_price = null): string {
        $price_html = sprintf(
            '<span class="aps-price">%s</span>',
            ProductHelpers::formatPrice($price, $currency)
        );
        
        // Add discount badge if original price exists and is higher
        if ($original_price !== null && $original_price > 0 && $original_price > $price) {
            $discount = ProductHelpers::calculateDiscount($original_price, $price);
            $price_html .= sprintf(
                ' <span class="aps-discount-badge">-%d%%</span>',
                esc_html($discount)
            );
        }
        
        return $price_html;
    }
    
    /**
     * Render status badge
     *
     * @param string $status Status code
     * @return string Rendered HTML
     */
    protected function render_status(string $status): string {
        $status_class = 'aps-product-status-' . $status;
        $status_label = ProductConfig::getStatusLabel($status);

        return sprintf(
            '<span class="aps-product-status %s">%s</span>',
            esc_attr($status_class),
            esc_html($status_label)
        );
    }
    
    /**
     * Render ribbon badge with colors
     *
     * @param string $ribbon_name Ribbon name
     * @param int $product_id Product ID for color retrieval
     * @return string Rendered HTML
     */
    protected function render_ribbon(string $ribbon_name, int $product_id): string {
        if (empty($ribbon_name)) {
            return '<span class="aps-ribbon-empty">—</span>';
        }

        $colors = ProductHelpers::getRibbonColors($product_id);
        $styles = [];
        
        if (!empty($colors['bg'])) {
            $styles[] = 'background-color: ' . esc_attr($colors['bg']);
        }
        if (!empty($colors['text'])) {
            $styles[] = 'color: ' . esc_attr($colors['text']);
        }

        $style_attr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';

        return sprintf(
            '<span class="aps-ribbon-badge"%s>%s</span>',
            $style_attr,
            esc_html($ribbon_name)
        );
    }
    
    /**
     * Render title with row actions
     *
     * @param string $title Product title
     * @param int $product_id Product ID
     * @return string Rendered HTML
     */
    protected function render_title_with_actions(string $title, int $product_id): string {
        $edit_url = ProductHelpers::getEditUrl($product_id);
        $view_url = ProductHelpers::getViewUrl($product_id);

        $actions = [
            'edit'   => sprintf(
                '<a href="%s">%s</a>',
                esc_url($edit_url),
                __('Edit', 'affiliate-product-showcase')
            ),
            'inline' => sprintf(
                '<a href="#" class="aps-inline-edit" data-id="%d">%s</a>',
                $product_id,
                __('Quick Edit', 'affiliate-product-showcase')
            ),
            'trash'  => sprintf(
                '<a href="#" class="aps-trash-product" data-id="%d">%s</a>',
                $product_id,
                __('Trash', 'affiliate-product-showcase')
            ),
            'view'   => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url($view_url),
                __('View', 'affiliate-product-showcase')
            ),
        ];

        return sprintf(
            '<strong><a href="%s" class="row-title">%s</a></strong>%s',
            esc_url($edit_url),
            esc_html($title),
            $this->row_actions($actions)
        );
    }
}
