/**
 * VirtualizedGrid Component
 *
 * Efficiently renders large lists of products using windowing.
 */

import { memo, useRef, useState, useEffect, useMemo } from '@wordpress/element';
import { FixedSizeGrid } from 'react-window';
import PropTypes from 'prop-types';
import ProductCard from './ProductCard';

// Virtualized grid row renderer
const VirtualizedRow = memo(({ columnIndex, rowIndex, style, data }) => {
    const { products, columns, showPrice, showRating, showBadge, clientId } = data;
    const productIndex = rowIndex * columns + columnIndex;

    // Don't render if product index is out of bounds
    if (productIndex >= products.length) {
        return <div style={style} />;
    }

    const product = products[productIndex];

    return (
        <div style={style}>
            <ProductCard
                product={product}
                showPrice={showPrice}
                showRating={showRating}
                showBadge={showBadge}
                clientId={clientId}
            />
        </div>
    );
});

VirtualizedRow.displayName = 'VirtualizedRow';

VirtualizedRow.propTypes = {
    columnIndex: PropTypes.number.isRequired,
    rowIndex: PropTypes.number.isRequired,
    style: PropTypes.object.isRequired,
    data: PropTypes.shape({
        products: PropTypes.array.isRequired,
        columns: PropTypes.number.isRequired,
        showPrice: PropTypes.bool,
        showRating: PropTypes.bool,
        showBadge: PropTypes.bool,
        clientId: PropTypes.string,
    }).isRequired,
};

const VirtualizedGrid = ({ products, columns, gap, showPrice, showRating, showBadge, clientId }) => {
    const containerRef = useRef(null);
    const [containerWidth, setContainerWidth] = useState(900);

    useEffect(() => {
        if (containerRef.current) {
            const observer = new ResizeObserver((entries) => {
                setContainerWidth(entries[0].contentRect.width);
            });
            observer.observe(containerRef.current);
            return () => observer.disconnect();
        }
    }, []);

    const { columnWidth, rowHeight } = useMemo(() => {
        const cWidth = Math.floor((containerWidth - (columns - 1) * gap) / columns);
        const rHeight = Math.min(400, cWidth * 1.5); // Aspect ratio approximation
        return { columnWidth: cWidth, rowHeight: rHeight };
    }, [containerWidth, columns, gap]);

    return (
        <div ref={containerRef} style={{ width: '100%', height: 600 }}>
            <FixedSizeGrid
                columnCount={columns}
                columnWidth={columnWidth}
                height={600}
                rowCount={Math.ceil(products.length / columns)}
                rowHeight={rowHeight}
                width={containerWidth}
                itemData={{ products, columns, showPrice, showRating, showBadge, clientId }}
                itemKey={(data, rowIndex, columnIndex) => {
                    const productIndex = rowIndex * columns + columnIndex;
                    return productIndex < products.length ? products[productIndex].id : `${rowIndex}-${columnIndex}`;
                }}
            >
                {VirtualizedRow}
            </FixedSizeGrid>
        </div>
    );
};

VirtualizedGrid.propTypes = {
    products: PropTypes.array.isRequired,
    columns: PropTypes.number.isRequired,
    gap: PropTypes.number.isRequired,
    showPrice: PropTypes.bool,
    showRating: PropTypes.bool,
    showBadge: PropTypes.bool,
    clientId: PropTypes.string,
};

export default VirtualizedGrid;
