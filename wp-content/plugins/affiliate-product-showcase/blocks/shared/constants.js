/**
 * Shared Constants
 *
 * Central source of truth for configuration values.
 *
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

// Virtualization
export const VIRTUALIZATION_THRESHOLD = 50;

// Caching
export const CACHE_TTL = 5 * 60 * 1000; // 5 minutes
export const CACHE_SIZE = 10;

// Debouncing
export const DEBOUNCE_DELAY_SHORT = 300; // For layout/UI updates
export const DEBOUNCE_DELAY_LONG = 500;  // For API calls (search/pagination)

// Layout
export const DEFAULT_COLUMNS = 3;
export const DEFAULT_GAP = 16;
export const DEFAULT_PER_PAGE = 6;
