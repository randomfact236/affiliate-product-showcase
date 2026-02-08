/**
 * ProductCard Component (Showcase Block)
 *
 * Displays a product item, supporting both Grid and List layouts.
 */

import { memo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { generateA11yId, truncateText, validateAffiliateUrl, stripHtml } from '../../shared/utils';
import { ProductImage, ProductPrice, AffiliateButton } from '../../shared/components';

const ProductCard = memo(({ product, layout, showPrice, showDescription, showButton, buttonText, clientId }) => {
    // Defensive check
    if (!product?.id) return null;

    const titleId = generateA11yId('showcase-title', product.id, clientId);
    const cleanDescription = stripHtml(product.description || '');
    const userButtonText = buttonText || __('View Details', 'affiliate-product-showcase');

    return (
        <article className="aps-showcase-item" aria-labelledby={titleId}>
            {product.image_url && (
                <ProductImage
                    src={product.image_url}
                    alt={product.title}
                    className="aps-product-image"
                />
            )}
            {product.badge && (
                <span className="aps-product-badge" aria-label={__('Product badge', 'affiliate-product-showcase')}>
                    {product.badge}
                </span>
            )}
            <div className="aps-product-content">
                <h3 id={titleId} className="aps-product-title">{product.title}</h3>
                {showPrice && product.price && (
                    <div className="aps-product-price">
                        <ProductPrice
                            price={product.price}
                            originalPrice={product.original_price}
                        />
                    </div>
                )}
                {showDescription && cleanDescription && (
                    <p className="aps-product-description">
                        {truncateText(cleanDescription, layout === 'list' ? 150 : 100)}
                    </p>
                )}
                {showButton && (
                    <AffiliateButton
                        href={validateAffiliateUrl(product.affiliate_link)}
                        productTitle={product.title}
                        className="aps-product-button"
                    >
                        {userButtonText}
                    </AffiliateButton>
                )}
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
        price: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        original_price: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
        description: PropTypes.string,
        affiliate_link: PropTypes.string,
    }).isRequired,
    layout: PropTypes.string,
    showPrice: PropTypes.bool,
    showDescription: PropTypes.bool,
    showButton: PropTypes.bool,
    buttonText: PropTypes.string,
    clientId: PropTypes.string,
};

export default ProductCard;
