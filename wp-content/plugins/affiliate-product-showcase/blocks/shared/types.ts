/**
 * TypeScript Type Definitions for Blocks
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

/**
 * Product data structure from API
 */
export interface Product {
    id: number;
    title: string;
    description?: string;
    price?: number | string;
    original_price?: number | string;
    image_url?: string;
    affiliate_link: string;
    badge?: string;
    rating?: number;
    rating_count?: number;
    category?: string;
    status: 'publish' | 'draft' | 'pending';
}

/**
 * Product Grid block attributes
 */
export interface ProductGridAttributes {
    perPage: number;
    columns: number;
    gap: number;
    showPrice: boolean;
    showRating: boolean;
    showBadge: boolean;
    hoverEffect: 'none' | 'lift' | 'scale' | 'shadow';
}

/**
 * Product Showcase block attributes
 */
export interface ProductShowcaseAttributes {
    layout: 'grid' | 'list';
    columns: number;
    gap: number;
    showPrice: boolean;
    showDescription: boolean;
    showButton: boolean;
    buttonText: string;
}

/**
 * Block editor props
 */
export interface BlockEditProps<T> {
    attributes: T;
    setAttributes: (attrs: Partial<T>) => void;
    isSelected: boolean;
    clientId: string;
}

/**
 * Cache entry structure
 */
export interface CacheEntry<T> {
    data: T;
    timestamp: number;
}

/**
 * Debounced function interface
 */
export interface DebouncedFunction<T extends (...args: any[]) => any> {
    (...args: Parameters<T>): void;
    cancel: () => void;
}

/**
 * API response for products
 */
export type ProductsApiResponse = Product[];

/**
 * Error state
 */
export interface ErrorState {
    message: string;
    code?: string;
}
