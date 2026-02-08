/**
 * @jest-environment jsdom
 */

import { render, screen } from '@testing-library/react';
import Edit from './edit';

// Mock WordPress components and functions
jest.mock('@wordpress/block-editor', () => ({
    InspectorControls: ({ children }) => <div>{children}</div>,
    useBlockProps: () => ({ className: 'aps-block-showcase' }),
}));

jest.mock('@wordpress/components', () => ({
    PanelBody: ({ title, children }) => <div><h2>{title}</h2>{children}</div>,
    PanelRow: ({ children }) => <div>{children}</div>,
    RangeControl: ({ label }) => <div>{label}</div>,
    ToggleControl: ({ label }) => <div>{label}</div>,
    SelectControl: ({ label }) => <div>{label}</div>,
    TextControl: ({ label }) => <div>{label}</div>,
    Spinner: () => <div>Loading...</div>,
    Placeholder: ({ children }) => <div>{children}</div>,
    Button: ({ children, onClick }) => <button onClick={onClick}>{children}</button>,
    Notice: ({ children }) => <div>{children}</div>,
}));

jest.mock('@wordpress/i18n', () => ({
    __: (text) => text,
}));

jest.mock('@wordpress/api-fetch', () => jest.fn());

// Mock shared utils
jest.mock('../shared/utils', () => ({
    stripHtml: (text) => text,
    truncateText: (text) => text,
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

describe('Product Showcase Edit', () => {
    const mockAttributes = {
        layout: 'grid',
        columns: 3,
        gap: 16,
        showPrice: true,
        showDescription: true,
        showButton: true,
        buttonText: 'View Details'
    };

    const mockSetAttributes = jest.fn();

    it('renders without crashing', async () => {
        require('../shared/hooks').useProducts.mockReturnValue({
            products: [],
            isLoading: true,
            error: null
        });

        render(
            <Edit
                attributes={mockAttributes}
                setAttributes={mockSetAttributes}
            />
        );

        // Should show loading state initially
        expect(screen.getByText('Loading...')).toBeInTheDocument();
    });

    it('renders products', async () => {
        const mockProducts = [
            { id: 1, title: 'Product 1', price: 10, image_url: 'img1.jpg' }
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

        expect(await screen.findByText('Product 1')).toBeInTheDocument();
    });

    it('renders inspector controls', () => {
        render(
            <Edit
                attributes={mockAttributes}
                setAttributes={mockSetAttributes}
            />
        );

        expect(screen.getByText('Layout Settings')).toBeInTheDocument();
        expect(screen.getByText('Display Options')).toBeInTheDocument();
        expect(screen.getByText('Style Presets')).toBeInTheDocument();
    });
});
