/**
 * Shared Block Exports
 * 
 * Central export point for all shared utilities, types, and components.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

// Utilities
export {
    debounce,
    SimpleCache,
    renderStars,
    formatPrice,
    truncateText,
    generateA11yId,
    validateAffiliateUrl,
} from './utils';

// Components
export {
    ErrorBoundary,
    LoadingSpinner,
    EmptyState,
    ProductImage,
    ProductPrice,
    ProductBadge,
    ProductRating,
    AffiliateButton,
} from './components';
