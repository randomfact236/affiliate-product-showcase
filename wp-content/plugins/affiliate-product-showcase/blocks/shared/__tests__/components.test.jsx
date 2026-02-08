/**
 * Unit Tests for Block Components
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import {
    ErrorBoundary,
    LoadingSpinner,
    EmptyState,
    ProductImage,
    ProductPrice,
    ProductBadge,
    AffiliateButton,
} from '../components';

// Mock @wordpress/i18n
jest.mock('@wordpress/i18n', () => ({
    __: (str) => str,
}));

describe('LoadingSpinner', () => {
    it('should render with default message', () => {
        render(<LoadingSpinner />);
        expect(screen.getByRole('status')).toBeInTheDocument();
        expect(screen.getByText('Loading...')).toBeInTheDocument();
    });

    it('should render with custom message', () => {
        render(<LoadingSpinner message="Loading products..." />);
        expect(screen.getByText('Loading products...')).toBeInTheDocument();
    });

    it('should have aria-live for accessibility', () => {
        render(<LoadingSpinner />);
        expect(screen.getByRole('status')).toHaveAttribute('aria-live', 'polite');
    });
});

describe('EmptyState', () => {
    it('should render with default message', () => {
        render(<EmptyState />);
        expect(screen.getByText('No products found.')).toBeInTheDocument();
    });

    it('should render with custom message', () => {
        render(<EmptyState message="Custom empty message" />);
        expect(screen.getByText('Custom empty message')).toBeInTheDocument();
    });
});

describe('ProductImage', () => {
    it('should render image with src', () => {
        render(<ProductImage src="https://example.com/image.jpg" alt="Test product" />);
        const img = screen.getByRole('img');
        expect(img).toHaveAttribute('src', 'https://example.com/image.jpg');
        expect(img).toHaveAttribute('alt', 'Test product');
    });

    it('should render placeholder when no src', () => {
        render(<ProductImage />);
        expect(screen.getByLabelText('No image available')).toBeInTheDocument();
    });

    it('should have lazy loading', () => {
        render(<ProductImage src="test.jpg" alt="Test" />);
        expect(screen.getByRole('img')).toHaveAttribute('loading', 'lazy');
    });
});

describe('ProductPrice', () => {
    it('should render current price', () => {
        render(<ProductPrice price={29.99} />);
        expect(screen.getByText('$29.99')).toBeInTheDocument();
    });

    it('should render original price when provided', () => {
        render(<ProductPrice price={19.99} originalPrice={29.99} />);
        expect(screen.getByText('$19.99')).toBeInTheDocument();
        expect(screen.getByText('$29.99')).toBeInTheDocument();
    });

    it('should not render when no price', () => {
        const { container } = render(<ProductPrice />);
        expect(container.firstChild).toBeNull();
    });

    it('should have aria-labels for accessibility', () => {
        render(<ProductPrice price={19.99} originalPrice={29.99} />);
        expect(screen.getByLabelText('Current price')).toBeInTheDocument();
        expect(screen.getByLabelText('Original price')).toBeInTheDocument();
    });
});

describe('ProductBadge', () => {
    it('should render badge text', () => {
        render(<ProductBadge badge="Sale" />);
        expect(screen.getByText('Sale')).toBeInTheDocument();
    });

    it('should not render when no badge', () => {
        const { container } = render(<ProductBadge />);
        expect(container.firstChild).toBeNull();
    });

    it('should have aria-label', () => {
        render(<ProductBadge badge="New" />);
        expect(screen.getByLabelText('Product badge')).toBeInTheDocument();
    });
});

describe('AffiliateButton', () => {
    it('should render link with correct attributes', () => {
        render(
            <AffiliateButton href="https://example.com" productTitle="Test Product">
                View Deal
            </AffiliateButton>
        );

        const link = screen.getByRole('link');
        expect(link).toHaveAttribute('href', 'https://example.com');
        expect(link).toHaveAttribute('target', '_blank');
        expect(link).toHaveAttribute('rel', 'nofollow noopener sponsored');
    });

    it('should have accessible label', () => {
        render(
            <AffiliateButton href="https://example.com" productTitle="Test Product">
                View Deal
            </AffiliateButton>
        );

        expect(screen.getByLabelText('View Deal - Test Product')).toBeInTheDocument();
    });
});

describe('ErrorBoundary', () => {
    // Suppress console.error for these tests
    const originalError = console.error;
    beforeAll(() => {
        console.error = jest.fn();
    });
    afterAll(() => {
        console.error = originalError;
    });

    const ThrowingComponent = () => {
        throw new Error('Test error');
    };

    it('should render children when no error', () => {
        render(
            <ErrorBoundary>
                <div>Child content</div>
            </ErrorBoundary>
        );
        expect(screen.getByText('Child content')).toBeInTheDocument();
    });

    it('should render error message when child throws', () => {
        render(
            <ErrorBoundary>
                <ThrowingComponent />
            </ErrorBoundary>
        );
        expect(screen.getByRole('alert')).toBeInTheDocument();
        expect(screen.getByText('Something went wrong displaying this content.')).toBeInTheDocument();
    });
});
