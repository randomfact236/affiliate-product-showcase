/**
 * GridPreview Component
 *
 * Handles the main preview of the grid block in the editor.
 * Orchestrates data fetching, loading states, and grid rendering.
 */

import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { useProducts, useProductCache } from '../../shared/hooks';
import { ErrorBoundary, LoadingSpinner } from '../../shared/components';
import { VIRTUALIZATION_THRESHOLD } from '../../shared/constants';
import ProductCard from './ProductCard';
import PropTypes from 'prop-types';
import { lazy, Suspense } from '@wordpress/element';

// Lazy load VirtualizedGrid to split react-window from main bundle
let VirtualizedGrid;
if (process.env.NODE_ENV === 'test') {
    VirtualizedGrid = require('./VirtualizedGrid').default;
} else {
    VirtualizedGrid = lazy(() => import(/* webpackChunkName: "virtualized-grid" */ './VirtualizedGrid'));
}

const GridPreview = ({ attributes, clientId }) => {
    const {
        perPage,
        columns,
        gap,
        showPrice,
        showRating,
        showBadge,
        hoverEffect,
    } = attributes;

    const blockProps = useBlockProps({
        className: 'aps-block aps-block--grid',
        style: {
            '--aps-grid-columns': columns,
            '--aps-grid-gap': `${gap}px`,
        },
        'data-hover-effect': hoverEffect,
    });

    // Use shared cache
    const cache = useProductCache();

    // Fetch products using custom hook
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
                {products.length > VIRTUALIZATION_THRESHOLD ? (
                    <Suspense fallback={<LoadingSpinner message={__('Loading grid...', 'affiliate-product-showcase')} />}>
                        <VirtualizedGrid
                            products={products}
                            columns={columns}
                            gap={gap}
                            showPrice={showPrice}
                            showRating={showRating}
                            showBadge={showBadge}
                            clientId={clientId}
                        />
                    </Suspense>
                ) : (
                    // Use regular mapping for small datasets
                    products.map((product) => (
                        <ProductCard
                            key={product.id}
                            product={product}
                            showPrice={showPrice}
                            showRating={showRating}
                            showBadge={showBadge}
                            clientId={clientId}
                        />
                    ))
                )}
            </ErrorBoundary>
        </div>
    );
};

GridPreview.propTypes = {
    attributes: PropTypes.object.isRequired,
    clientId: PropTypes.string,
};

export default GridPreview;
