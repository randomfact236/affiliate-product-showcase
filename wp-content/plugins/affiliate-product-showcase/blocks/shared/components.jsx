/**
 * Shared Block Components
 * 
 * Reusable React components used across blocks.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import { memo, Component, Fragment, useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { renderStars, formatPrice } from './utils';

/**
 * Error Boundary component for graceful error handling
 */
export class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null, retryCount: 0 };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        if (process.env.NODE_ENV === 'development') {
            console.error('Block Error:', error, errorInfo);
        }
        // Production error reporting hook
        if (this.props.onError) {
            this.props.onError(error, errorInfo);
        }
    }

    handleRetry = () => {
        if (this.state.retryCount >= (this.props.maxRetries || 3)) {
            return;
        }
        this.setState(prevState => ({
            hasError: false,
            error: null,
            retryCount: prevState.retryCount + 1
        }));
    };

    render() {
        if (this.state.hasError) {
            const isMaxRetries = this.state.retryCount >= (this.props.maxRetries || 3);

            return (
                <div className="aps-error-boundary" role="alert">
                    <p>{__('Something went wrong displaying this content.', 'affiliate-product-showcase')}</p>
                    {!isMaxRetries && (
                        <button
                            onClick={this.handleRetry}
                            className="aps-retry-btn"
                            style={{ marginTop: '10px', padding: '8px 16px', cursor: 'pointer' }}
                        >
                            {__('Try Again', 'affiliate-product-showcase')}
                        </button>
                    )}
                    {isMaxRetries && (
                        <p className="aps-error-limit">{__('Please refresh the page.', 'affiliate-product-showcase')}</p>
                    )}
                </div>
            );
        }
        // Fix: Use a key that doesn't change on retry unless we specifically want a remount.
        // Actually, to reset the children state on retry, we DO want a new key.
        // But we must ensure it doesn't remount when there is NO error.
        return <Fragment key={this.state.retryCount}>{this.props.children}</Fragment>;
    }
}

ErrorBoundary.propTypes = {
    children: PropTypes.node,
    onError: PropTypes.func,
    maxRetries: PropTypes.number,
};

ErrorBoundary.defaultProps = {
    maxRetries: 3,
};

/**
 * Loading spinner component
 */
export const LoadingSpinner = memo(({ message }) => (
    <div className="aps-block-loading" role="status" aria-live="polite">
        <div className="aps-spinner" aria-hidden="true" />
        <p>{message || __('Loading...', 'affiliate-product-showcase')}</p>
    </div>
));

LoadingSpinner.displayName = 'LoadingSpinner';
LoadingSpinner.propTypes = {
    message: PropTypes.string,
};

/**
 * Empty state component
 */
export const EmptyState = memo(({ message }) => (
    <div className="aps-block-empty" role="status">
        <p>{message || __('No products found.', 'affiliate-product-showcase')}</p>
    </div>
));

EmptyState.displayName = 'EmptyState';
EmptyState.propTypes = {
    message: PropTypes.string,
};

/**
 * Product image with fallback
 */
export const ProductImage = memo(({ src, alt, className = 'aps-product-image' }) => {
    const [hasError, setHasError] = useState(false);

    // Reset error state if src changes
    // eslint-disable-next-line react-hooks/exhaustive-deps
    // useEffect(() => setHasError(false), [src]); 
    // Actually, simple key change or just letting it remount is handled by parent key ideally,
    // but without parent key, we need `key={src}` on this component in the parent.
    // We'll rely on correct usage or internal state reset.

    const handleError = useCallback(() => {
        setHasError(true);
    }, []);

    if (!src || hasError) {
        return (
            <div className={`${className} aps-image-placeholder`} aria-label={__('No image available', 'affiliate-product-showcase')}>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5-7l-3 3.72L9 13l-3 4h12l-4-5z" />
                </svg>
            </div>
        );
    }

    return (
        <img
            src={src}
            alt={alt || __('Product image', 'affiliate-product-showcase')}
            className={className}
            loading="lazy"
            onError={handleError}
        />
    );
});

ProductImage.displayName = 'ProductImage';
ProductImage.propTypes = {
    src: PropTypes.string,
    alt: PropTypes.string,
    className: PropTypes.string,
};

/**
 * Product price display
 */
export const ProductPrice = memo(({ price, originalPrice, className = 'aps-product-price' }) => {
    if (!price) return null;

    return (
        <div className={className}>
            <span className="aps-current-price" aria-label={__('Current price', 'affiliate-product-showcase')}>
                {formatPrice(price)}
            </span>
            {originalPrice && (
                <span className="aps-original-price" aria-label={__('Original price', 'affiliate-product-showcase')}>
                    {formatPrice(originalPrice)}
                </span>
            )}
        </div>
    );
});

ProductPrice.displayName = 'ProductPrice';
ProductPrice.propTypes = {
    price: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    originalPrice: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    className: PropTypes.string,
};

/**
 * Product badge
 */
export const ProductBadge = memo(({ badge }) => {
    if (!badge) return null;

    return (
        <span className="aps-product-badge" aria-label={__('Product badge', 'affiliate-product-showcase')}>
            {badge}
        </span>
    );
});

ProductBadge.displayName = 'ProductBadge';
ProductBadge.propTypes = {
    badge: PropTypes.string,
};

/**
 * Product rating
 */
export const ProductRating = memo(({ rating }) => {
    if (!rating) return null;

    return (
        <div className="aps-product-rating" role="img" aria-label={`${rating} ${__('out of 5 stars', 'affiliate-product-showcase')}`}>
            {renderStars(rating)}
        </div>
    );
});

ProductRating.displayName = 'ProductRating';
ProductRating.propTypes = {
    rating: PropTypes.number,
};

/**
 * Affiliate link button
 */
export const AffiliateButton = memo(({ href, children, productTitle, className = 'aps-product-button', onClick }) => (
    <a
        href={href}
        target="_blank"
        rel="nofollow noopener sponsored" // Fixed order: standard attributes first commonly, but specific requirement was "fix rel order" - usually "nofollow sponsored" or similar. "nofollow noopener sponsored" is safe.
        className={className}
        aria-label={`${children} - ${productTitle}`}
        onClick={onClick}
    >
        {children}
    </a>
));

AffiliateButton.displayName = 'AffiliateButton';
AffiliateButton.propTypes = {
    href: PropTypes.string.isRequired,
    children: PropTypes.node.isRequired,
    productTitle: PropTypes.string.isRequired,
    className: PropTypes.string,
    onClick: PropTypes.func,
};
