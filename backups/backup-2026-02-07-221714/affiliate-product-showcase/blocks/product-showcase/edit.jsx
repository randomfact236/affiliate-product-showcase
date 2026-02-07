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
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes, isSelected, clientId }) {
	const {
		layout = 'grid',
		columns = 3,
		gap = 16,
		showPrice = true,
		showDescription = true,
		showButton = true,
		buttonText = 'View Details',
	} = attributes;

	const [products, setProducts] = useState([]);
	const [isLoading, setIsLoading] = useState(true);
	const [error, setError] = useState(null);

	// Fetch products on mount
	useEffect(() => {
		async function fetchProducts() {
			setIsLoading(true);
			setError(null);

			try {
				const response = await apiFetch({
					path: '/affiliate-product-showcase/v1/products',
					method: 'GET',
					params: {
						per_page: 6,
						status: 'publish',
					},
				});

				if (response && Array.isArray(response)) {
					setProducts(response);
				} else {
					setProducts([]);
				}
			} catch (err) {
				console.error('Error fetching products:', err);
				setError('Failed to load products. Please try again.');
				setProducts([]);
			} finally {
				setIsLoading(false);
			}
		}

		fetchProducts();
	}, []);

	const blockProps = useBlockProps({
		className: 'aps-block aps-block--showcase',
		style: {
			'--aps-showcase-columns': columns,
			'--aps-showcase-gap': `${gap}px`,
		},
		'data-layout': layout,
	});

	return (
		<>
			<InspectorControls>
				<PanelBody
					title="Layout Settings"
					icon={<Gridicon icon="grid-view" />}
					initialOpen={true}
				>
					<SelectControl
						label="Layout Type"
						value={layout}
						options={[
							{ label: 'Grid Layout', value: 'grid' },
							{ label: 'List Layout', value: 'list' },
						]}
						onChange={(value) => setAttributes({ layout: value })}
						help="Choose between grid or list layout"
					/>

					{layout === 'grid' && (
						<RangeControl
							label="Columns"
							min={1}
							max={6}
							value={columns}
							onChange={(value) => setAttributes({ columns: value })}
							help="Number of columns in grid layout"
						/>
					)}

					<RangeControl
						label="Gap (px)"
						min={0}
						max={48}
						value={gap}
						onChange={(value) => setAttributes({ gap: value })}
						help="Spacing between items"
					/>
				</PanelBody>

				<PanelBody title="Display Options" initialOpen={false}>
					<ToggleControl
						label="Show Price"
						checked={showPrice}
						onChange={(value) => setAttributes({ showPrice: value })}
						help="Display product prices"
					/>

					<ToggleControl
						label="Show Description"
						checked={showDescription}
						onChange={(value) => setAttributes({ showDescription: value })}
						help="Display product descriptions"
					/>

					<ToggleControl
						label="Show Button"
						checked={showButton}
						onChange={(value) => setAttributes({ showButton: value })}
						help="Display call-to-action button"
					/>

					{showButton && (
						<TextControl
							label="Button Text"
							value={buttonText}
							onChange={(value) => setAttributes({ buttonText: value })}
							help="Custom text for CTA button"
						/>
					)}
				</PanelBody>

				<PanelBody title="Style Presets" initialOpen={false}>
					<div className="aps-style-presets">
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								layout: 'grid',
								columns: 3,
								gap: 16,
								showPrice: true,
								showDescription: true,
								showButton: true,
								buttonText: 'View Details'
							})}
						>
							Default Grid
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								layout: 'list',
								gap: 16,
								showPrice: true,
								showDescription: true,
								showButton: true,
								buttonText: 'Learn More'
							})}
						>
							List View
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								layout: 'grid',
								columns: 4,
								gap: 12,
								showPrice: true,
								showDescription: false,
								showButton: false
							})}
						>
							Compact
						</button>
						<button
							className="aps-preset-btn"
							onClick={() => setAttributes({
								layout: 'grid',
								columns: 2,
								gap: 24,
								showPrice: true,
								showDescription: true,
								showButton: true,
								buttonText: 'Buy Now'
							})}
						>
							Featured
						</button>
					</div>
				</PanelBody>

				<PanelBody title="Advanced" initialOpen={false}>
					<PanelRow>
						<span>Layout Type:</span>
						<strong>{layout === 'grid' ? 'Grid' : 'List'}</strong>
					</PanelRow>
					{layout === 'grid' && (
						<PanelRow>
							<span>Grid Columns:</span>
							<strong>{columns}</strong>
						</PanelRow>
					)}
					<PanelRow>
						<span>Gap Size:</span>
						<strong>{gap}px</strong>
					</PanelRow>
					<PanelRow>
						<span>Elements Shown:</span>
						<strong>
							{showPrice && 'Price '}
							{showDescription && '• Description '}
							{showButton && '• Button'}
						</strong>
					</PanelRow>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{isLoading ? (
					<div className="aps-block-loading">
						<Spinner />
						<p>Loading products...</p>
					</div>
				) : error ? (
					<Notice status="error" isDismissible={false}>
						{error}
					</Notice>
				) : products.length === 0 ? (
					<div className="aps-block-empty">
						<p>No products found. Add some products to see them here.</p>
					</div>
				) : (
					products.map((product) => (
						<div key={product.id} className="aps-showcase-item">
							{product.image_url && (
								<img
									src={product.image_url}
									alt={product.title}
									className="aps-product-image"
									loading="lazy"
								/>
							)}
							{product.badge && (
								<span className="aps-product-badge">{product.badge}</span>
							)}
							<div className="aps-product-content">
								<h3 className="aps-product-title">{product.title}</h3>
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
								{showDescription && product.description && (
									<p className="aps-product-description">
										{layout === 'list' 
											? product.description?.substring(0, 150)
											: product.description?.substring(0, 100)
										}...
									</p>
								)}
								{showButton && (
									<a
										href={product.affiliate_link}
										target="_blank"
										rel="nofollow sponsored"
										className="aps-product-button"
									>
										{buttonText}
									</a>
								)}
							</div>
						</div>
					))
				)}
			</div>
		</>
	);
}
