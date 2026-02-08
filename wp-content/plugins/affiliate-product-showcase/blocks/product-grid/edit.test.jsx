/**
 * @jest-environment jsdom
 */

import { render, screen } from '@testing-library/react';
import Edit from './edit';

// Mock WordPress components and functions
jest.mock('@wordpress/block-editor', () => ({
    InspectorControls: ({ children }) => <div>{children}</div>,
    useBlockProps: () => ({ className: 'aps-block-grid' }),
}));

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div><h2>{title}</h2>{children}</div>,
    PanelRow: ({ children }) => <div>{children}</div>,
    RangeControl: ({ label }) => <div>{label}</div>,
    ToggleControl: ({ label }) => <div>{label}</div>,
    SelectControl: ({ label }) => <div>{label}</div>,
    TextControl: ({ label }) => <div>{label}</div>,
    Placeholder: ({ children }) => <div>{children}</div>,
    Spinner: () => <div>Loading...</div>,
    Button: ({ children, onClick }) => <button onClick={onClick}>{children}</button>,
    Notice: ({ children }) => <div>{children}</div>,
}));

jest.mock('@wordpress/i18n', () => ({
    __: (text) => text,
}));

jest.mock('@wordpress/api-fetch', () => jest.fn());

// Mock shared utils
jest.mock('../shared/utils', () => ({
    debounce: (func) => {
        const debounced = func;
        debounced.cancel = jest.fn();
        return debounced;
    },
    SimpleCache: class {
        get() { return null; }
        set() { }
    },
    renderStars: () => <div>Stars</div>,
    formatPrice: (price) => `$${price}`,
    truncateText: (text) => text,
    stripHtml: (text) => text,
    validateAffiliateUrl: (url) => url,
    generateA11yId: (prefix, id) => `${prefix}-${id}`,
}));

// Mock shared hooks
jest.mock('../shared/hooks', () => ({
    useDebounce: (func) => {
        const debounced = func;
        debounced.cancel = jest.fn();
        return debounced;
    },
    useProductCache: () => ({
        get: jest.fn(),
        set: jest.fn(),
    }),
    useProducts: jest.fn(),
}));

// Mock shared components
jest.mock('../shared/components', () => ({
    ErrorBoundary: ({ children }) => <div>{children}</div>,
    ProductImage: () => <img alt="test" />,
    ProductPrice: () => <div>$10.00</div>,
    AffiliateButton: ({ children }) => <button>{children}</button>,
    LoadingSpinner: () => <div>Loading...</div>,
    ProductRating: () => <div>Stars</div>,
}));

// Mock react-window
// Mock react-window
jest.mock('react-window', () => ({
    FixedSizeGrid: ({ children, itemData }) => (
        <div>
            {itemData.products.map((product, index) => (
                <div key={index}>
                    {children({
                        columnIndex: index % itemData.columns,
                        rowIndex: Math.floor(index / itemData.columns),
                        style: {},
                        data: itemData
                    })}
                </div>
            ))}
        </div>
    )
}));

// Mock GridInspector and GridPreview to isolate Edit component logic or Integration test
// Actually, since this is an integration test of Edit -> Preview, we WANT Preview to run.
// But we might want to mock VirtualizedGrid to avoid lazy loading issues or complex rendering.
jest.mock('./edit/VirtualizedGrid', () => ({
    __esModule: true,
    default: ({ products }) => {
        console.log('VirtualizedGrid rendered with:', products);
        return (
            <div data-testid="virtualized-grid">
                {products.map(p => <div key={p.id}>{p.title}</div>)}
            </div>
        );
    },
}));

// We also need to mock GridInspector and GridPreview? 
// No, the test imports `Edit` which imports them. 
// If they are default exports, we can mock them if path matches.
// But GridPreview is inside `edit` folder.
// The test is in `blocks/product-grid`.
// The file is `blocks/product-grid/edit.jsx`.
// It imports `./edit/GridPreview`.
// So valid path is `./edit/GridPreview`.


describe('Product Grid Edit', () => {
    const mockAttributes = {
        perPage: 6,
        columns: 3,
        gap: 16,
        showPrice: true,
        showRating: true,
        showBadge: true,
        hoverEffect: 'lift'
    };

    const mockSetAttributes = jest.fn();

    it('renders products after fetch', async () => {
        const mockProducts = [
            { id: 1, title: 'Product 1', price: 10, image_url: 'img1.jpg' },
            { id: 2, title: 'Product 2', price: 20, image_url: 'img2.jpg' }
        ];

        require('../shared/hooks').useProducts.mockReturnValue({
            products: mockProducts,
            isLoading: false,
            error: null
        });

        render(
            <Edit
                attributes={mockAttributes}
                setAttributes={mockSetAttributes}
            />
        );

        // Wait for products to appear
        const product1 = await screen.findByText('Product 1');
        expect(product1).toBeInTheDocument();
        expect(screen.getByText('Product 2')).toBeInTheDocument();
    });

    it('handles fetch errors gracefully', async () => {
        require('../shared/hooks').useProducts.mockReturnValue({
            products: [],
            isLoading: false,
            error: 'Failed to load products'
        });

        render(
            <Edit
                attributes={mockAttributes}
                setAttributes={mockSetAttributes}
            />
        );

        const errorMsg = await screen.findByText(/Failed to load products/i);
        expect(errorMsg).toBeInTheDocument();
    });


});
