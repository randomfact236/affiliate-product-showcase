/**
 * ProductCard Component
 *
 * Displays a single product item card in the grid.
 */

import { memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { generateA11yId, truncateText, validateAffiliateUrl, stripHtml } from '../../shared/utils';
import { ProductImage, ProductPrice, AffiliateButton, ProductRating } from '../../shared/components';

const ProductCard = memo(({ product, showPrice, showRating, showBadge, clientId }) => {
    // Defensive check
    if (!product?.id) return null;

    const titleId = generateA11yId('product-title', product.id, clientId);

    // Strip HTML from description to prevent XSS and broken tags
    const cleanDescription = stripHtml(product.description || '');

    return (
        <article className="aps-grid-item" aria-labelledby={titleId}>
            {product.image_url && (
                <ProductImage
                    src={product.image_url}
                    alt={product.title}
                    className="aps-product-image"
                />
            )}
            {showBadge && product.badge && (
                <span className="aps-product-badge" aria-label={__('Product badge', 'affiliate-product-showcase')}>
                    {product.badge}
                </span>
            )}
            <div className="aps-product-content">
                <h3 id={titleId} className="aps-product-title">{product.title}</h3>
                {showRating && product.rating && (
                    <ProductRating rating={product.rating} />
                )}
                {showPrice && product.price && (
                    <div className="aps-product-price">
                        <ProductPrice
                            price={product.price}
                            originalPrice={product.original_price}
                        />
                    </div>
                )}
                <p className="aps-product-description">
                    {truncateText(cleanDescription, 100)}
                </p>
                <AffiliateButton
                    href={validateAffiliateUrl(product.affiliate_link)}
                    productTitle={product.title}
                    className="aps-product-button"
                >
                    {__('View Deal', 'affiliate-product-showcase')}
                </AffiliateButton>
            </div>
        </article>
    );
});

ProductCard.displayName = 'ProductCard';

ProductCard.propTypes = {
    product: PropTypes.shape({
        id: PropTypes.number.isRequired,
        title: PropTypes.string.isRequired,
        image_url: PropTypes.string,
        badge: PropTypes.string,
        rating: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        price: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        original_price: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        description: PropTypes.string,
        affiliate_link: PropTypes.string,
    }).isRequired,
    showPrice: PropTypes.bool,
    showRating: PropTypes.bool,
    showBadge: PropTypes.bool,
    clientId: PropTypes.string,
};

export default ProductCard;
