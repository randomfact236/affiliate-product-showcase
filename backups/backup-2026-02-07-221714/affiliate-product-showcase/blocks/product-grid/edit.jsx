import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	RangeControl,
	ToggleControl,
	SelectControl,
	TextControl,
	Spinner,
	Notice,
} from '@wordpress/components';
import { Gridicon } from '@wordpress/icons';
import { useState, useEffect, useCallback, memo, Component } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { FixedSizeGrid } from 'react-window';
import { __ } from '@wordpress/i18n';

// Error Boundary component to catch render errors
class ErrorBoundary extends Component {
	constructor(props) {
		super(props);
		this.state = { hasError: false };
	}

	static getDerivedStateFromError(error) {
		return { hasError: true };
	}

	componentDidCatch(error, errorInfo) {
		if (process.env.NODE_ENV === 'development') {
			console.error('Product Grid Error:', error, errorInfo);
		}
	}

	render() {
		if (this.state.hasError) {
			return (
				<div className="aps-error">
					<p>{__('Failed to load product. Please try again.', 'affiliate-product-showcase')}</p>
				</div>
			);
		}
		return this.props.children;
	}
}

// Memoized Product Item component
const ProductItem = memo(({ product, showPrice, showRating, showBadge }) => {
	return (
		<div className="aps-grid-item">
			{product.image_url && (
				<img
					src={product.image_url}
					alt={product.title}
					className="aps-product-image"
					loading="lazy"
				/>
			)}
			{showBadge && product.badge && (
				<span className="aps-product-badge">{product.badge}</span>
			)}
			<div className="aps-product-content">
				<h3 className="aps-product-title">{product.title}</h3>
				{showRating && product.rating && (
					<div className="aps-product-rating">
						{renderStars(product.rating)}
					</div>
				)}
				{showPrice && product.price && (
					<div className="aps-product-price">
						<span className="aps-current-price">${product.price}</span>
						{product.original_price && (
							<span className="aps-original-price">
								${product.original_price}
							</span>
						)}
					</div>
				)}
				<p className="aps-product-description">
					{product.description?.substring(0, 100)}...
				</p>
				<a
					href={product.affiliate_link}
					target="_blank"
					rel="nofollow sponsored"
					className="aps-product-button"
				>
					{__('View Deal', 'affiliate-product-showcase')}
				</a>
			</div>
		</div>
	);
});

// Simple API cache with 5-minute TTL
const productCache = new Map();
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

// Simple debounce function
function debounce(func, wait) {
	let timeout;
	return function executedFunction(...args) {
		const later = () => {
			clearTimeout(timeout);
			func(...args);
		};
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
	};
}

export default function Edit({ attributes, setAttributes, isSelected, clientId }) {
	const {
		perPage = 6,
		columns = 3,
		gap = 16,
		showPrice = true,
		showRating = true,
		showBadge = true,
		hoverEffect = 'lift',
	} = attributes;

	const [products, setProducts] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState(null);

	// Debounced handler for perPage (triggers API call)
	const debouncedSetPerPage = useCallback(
		debounce((value) => {
			setAttributes({ perPage: value });
		}, 500),
		[setAttributes]
	);

	// Debounced handler for columns (layout change)
	const debouncedSetColumns = useCallback(
		debounce((value) => {
			setAttributes({ columns: value });
		}, 300),
		[setAttributes]
	);

	// Debounced handler for gap (layout change)
	const debouncedSetGap = useCallback(
		debounce((value) => {
			setAttributes({ gap: value });
		}, 300),
		[setAttributes]
	);

	// Fetch products on mount and when perPage changes
	useEffect(() => {
		async function fetchProducts() {
			setIsLoading(true);
			setError(null);

			try {
				// Check cache first
				const cacheKey = JSON.stringify({ perPage });
				const cached = productCache.get(cacheKey);
				
				if (cached && Date.now() - cached.timestamp < CACHE_TTL) {
					setProducts(cached.data);
					setIsLoading(false);
					return;
				}

				// Fetch from API
				const response = await apiFetch({
					path: '/affiliate-product-showcase/v1/products',
					method: 'GET',
					params: {
						per_page: perPage,
						status: 'publish',
					},
				});

				if (response && Array.isArray(response)) {
					// Cache the response
					productCache.set(cacheKey, {
						data: response,
						timestamp: Date.now()
					});
					setProducts(response);
				} else {
					setProducts([]);
				}
			} catch (err) {
				if (process.env.NODE_ENV === 'development') {
					console.error('Error fetching products:', err);
				}
				setError(__('Failed to load products. Please try again.', 'affiliate-product-showcase'));
				setProducts([]);
			} finally {
				setIsLoading(false);
			}
		}

		fetchProducts();
	}, [perPage]);

	const blockProps = useBlockProps({
		className: 'aps-block aps-block--grid',
		style: {
			'--aps-grid-columns': columns,
			'--aps-grid-gap': `${gap}px`,
		},
		'data-hover-effect': hoverEffect,
	});

	return (
		<>
			<InspectorControls>
				<PanelBody
					title="Grid Settings"
					icon={<Gridicon icon="grid-view" />}
					initialOpen={true}
				>
					<RangeControl
						label="Products per page"
						min={2}
						max={12}
						value={perPage}
						onChange={debouncedSetPerPage}
						help="Number of products to display"
					/>

					<RangeControl
						label="Columns"
						min={1}
						max={6}
						value={columns}
						onChange={debouncedSetColumns}
						help="Number of columns in grid layout"
					/>

					<RangeControl
						label="Gap (px)"
						min={0}
						max={48}
						value={gap}
						onChange={debouncedSetGap}
						help="Spacing between grid items"
					/>
				</PanelBody>

				<PanelBody title="Display Options" initialOpen={false}>
					<ToggleControl
						label="Show Price"
						checked={showPrice}
						onChange={(value) => setAttributes({ showPrice: value })}
						help="Display product prices in grid"
					/>

					<ToggleControl
						label="Show Rating"
						checked={showRating}
						onChange={(value) => setAttributes({ showRating: value })}
						help="Display star ratings"
					/>

					<ToggleControl
						label="Show Badge"
						checked={showBadge}
						onChange={(value) => setAttributes({ showBadge: value })}
						help="Display product badges (e.g., 'Sale', 'New')"
					/>
				</PanelBody>

				<PanelBody title="Hover Effect" initialOpen={false}>
					<SelectControl
						label="Hover Effect Type"
						value={hoverEffect}
						options={[
							{ label: 'None', value: 'none' },
							{ label: 'Lift Up', value: 'lift' },
							{ label: 'Scale', value: 'scale' },
							{ label: 'Shadow', value: 'shadow' },
						]}
						onChange={(value) => setAttributes({ hoverEffect: value })}
						help="Animation effect when hovering over product cards"
					/>
				</PanelBody>

				<PanelBody title="Style Presets" initialOpen={false}>
					<div className="aps-style-presets">
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								columns: 3,
								gap: 16,
								showPrice: true,
								showRating: true,
								showBadge: true,
								hoverEffect: 'lift'
							})}
						>
							Default
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								columns: 4,
								gap: 12,
								showPrice: true,
								showRating: false,
								showBadge: false,
								hoverEffect: 'lift'
							})}
						>
							Compact
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								columns: 2,
								gap: 24,
								showPrice: true,
								showRating: true,
								showBadge: true,
								hoverEffect: 'shadow'
							})}
						>
							Featured
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								columns: 6,
								gap: 8,
								showPrice: false,
								showRating: false,
								showBadge: false,
								hoverEffect: 'none'
							})}
						>
							Minimal
						</button>
					</div>
				</PanelBody>

				<PanelBody title="Advanced" initialOpen={false}>
					<PanelRow>
						<span>Total Products:</span>
						<strong>{perPage}</strong>
					</PanelRow>
					<PanelRow>
						<span>Grid Columns:</span>
						<strong>{columns}</strong>
					</PanelRow>
					<PanelRow>
						<span>Gap Size:</span>
						<strong>{gap}px</strong>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{isLoading ? (
					<div className="aps-block-loading">
						<Spinner />
						<p>{__('Loading products...', 'affiliate-product-showcase')}</p>
					</div>
				) : error ? (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				) : products.length === 0 ? (
					<div className="aps-block-empty">
						<p>{__('No products found. Add some products to see them here.', 'affiliate-product-showcase')}</p>
					</div>
				) : (
					<ErrorBoundary>
						{products.length > 50 ? (
							// Use virtualization for large datasets (50+ products)
							<FixedSizeGrid
								columnCount={columns}
								columnWidth={300}
								height={600}
								rowCount={Math.ceil(products.length / columns)}
								rowHeight={400}
								width={columns * 300 + (columns - 1) * gap}
								itemData={{ products, columns, showPrice, showRating, showBadge }}
								itemKey={(data, rowIndex, columnIndex) => {
									const productIndex = rowIndex * columns + columnIndex;
									return productIndex < products.length ? products[productIndex].id : `${rowIndex}-${columnIndex}`;
								}}
							>
								{VirtualizedRow}
							</FixedSizeGrid>
						) : (
							// Use regular mapping for small datasets (50 or fewer products)
							products.map((product) => (
								<ProductItem
									key={product.id}
									product={product}
									showPrice={showPrice}
									showRating={showRating}
									showBadge={showBadge}
								/>
							))
						)}
					</ErrorBoundary>
				)}
			</div>
		</>
	);
}

// Memoized renderStars function
const renderStars = useCallback((rating) => {
	const stars = [];
	const fullStars = Math.floor(rating);
	const hasHalfStar = rating - fullStars >= 0.5;
	const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

	for (let i = 0; i < fullStars; i++) {
		stars.push(<span key={`full-${i}`} className="aps-star">★</span>);
	}
	if (hasHalfStar) {
		stars.push(<span key="half" className="aps-star">★</span>);
	}
	for (let i = 0; i < emptyStars; i++) {
		stars.push(<span key={`empty-${i}`} className="aps-star empty">★</span>);
	}

	return <div className="aps-stars">{stars}</div>;
}, []);

// Virtualized grid row renderer
const VirtualizedRow = memo(({ columnIndex, rowIndex, style, data }) => {
	const { products, columns, showPrice, showRating, showBadge } = data;
	const productIndex = rowIndex * columns + columnIndex;
	
	// Don't render if product index is out of bounds
	if (productIndex >= products.length) {
		return <div style={style} />;
	}

	const product = products[productIndex];

	return (
		<div style={style}>
			<ProductItem
				product={product}
				showPrice={showPrice}
				showRating={showRating}
				showBadge={showBadge}
			/>
		</div>
	);
});
