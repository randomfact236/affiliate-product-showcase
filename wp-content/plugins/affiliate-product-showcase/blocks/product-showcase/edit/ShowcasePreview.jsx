/**
 * ShowcasePreview Component
 *
 * Handles the main preview of the showcase block in the editor.
 */

import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { useProducts, useProductCache } from '../../shared/hooks';
import { ErrorBoundary, LoadingSpinner } from '../../shared/components';
import ProductCard from './ProductCard';
import PropTypes from 'prop-types';

const ShowcasePreview = ({ attributes, clientId }) => {
    const {
        layout,
        columns,
        gap,
        showPrice,
        showDescription,
        showButton,
        buttonText,
        perPage,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'aps-block aps-block--showcase',
        style: {
            '--aps-showcase-columns': columns,
            '--aps-showcase-gap': `${gap}px`,
        },
        'data-layout': layout,
        'data-show-price': showPrice.toString(),
        'data-show-description': showDescription.toString(),
        'data-show-button': showButton.toString(),
    });

    // Use shared cache
    const cache = useProductCache();

    // Fetch products
    const { products, isLoading, error } = useProducts(
        { per_page: perPage },
        { cache }
    );

    if (isLoading) {
        return (
            <div {...blockProps}>
                <LoadingSpinner message={__('Loading products...', 'affiliate-product-showcase')} />
            </div>
        );
    }

    if (error) {
        return (
            <div {...blockProps}>
                <Notice status="error" isDismissible={false}>
                    {error}
                </Notice>
            </div>
        );
    }

    if (products.length === 0) {
        return (
            <div {...blockProps}>
                <div className="aps-block-empty">
                    <p>{__('No products found. Add some products to see them here.', 'affiliate-product-showcase')}</p>
                </div>
            </div>
        );
    }

    return (
        <div {...blockProps}>
            <ErrorBoundary>
                {products.map((product) => (
                    <ProductCard
                        key={product.id}
                        product={product}
                        layout={layout}
                        showPrice={showPrice}
                        showDescription={showDescription}
                        showButton={showButton}
                        buttonText={buttonText}
                        clientId={clientId}
                    />
                ))}
            </ErrorBoundary>
        </div>
    );
};

ShowcasePreview.propTypes = {
    attributes: PropTypes.object.isRequired,
    clientId: PropTypes.string,
};

export default ShowcasePreview;
