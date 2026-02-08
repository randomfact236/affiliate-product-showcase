import { renderHook, waitFor } from '@testing-library/react';
import { useProducts } from '../index';
import apiFetch from '@wordpress/api-fetch';


// Mock apiFetch
jest.mock('@wordpress/api-fetch', () => jest.fn());

// Mock SimpleCache to avoid actual cache dependency
const mockCache = {
    get: jest.fn(),
    set: jest.fn(),
};

describe('useProducts', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    it('should fetch products successfully', async () => {
        const mockProducts = [{ id: 1, title: 'Product 1' }];
        apiFetch.mockResolvedValueOnce(mockProducts);

        const { result } = renderHook(() => useProducts({ per_page: 5 }));

        expect(result.current.isLoading).toBe(true);
        expect(result.current.products).toEqual([]);

        await waitFor(() => expect(result.current.isLoading).toBe(false));

        expect(result.current.products).toEqual(mockProducts);
        expect(result.current.error).toBeNull();
        expect(apiFetch).toHaveBeenCalledWith(expect.objectContaining({
            path: '/affiliate-product-showcase/v1/products',
            params: expect.objectContaining({ per_page: 5 }),
        }));
    });

    it('should handle API errors', async () => {
        apiFetch.mockRejectedValueOnce(new Error('API Error'));

        const { result } = renderHook(() => useProducts());

        await waitFor(() => expect(result.current.isLoading).toBe(false));

        expect(result.current.error).toBeTruthy();
        expect(result.current.products).toEqual([]);
    });

    it('should use cache if available', async () => {
        const cachedProducts = [{ id: 2, title: 'Cached Product' }];
        mockCache.get.mockReturnValueOnce(cachedProducts);

        const { result } = renderHook(() => useProducts({ per_page: 5 }, { cache: mockCache }));

        await waitFor(() => expect(result.current.isLoading).toBe(false));

        expect(result.current.products).toEqual(cachedProducts);
        expect(apiFetch).not.toHaveBeenCalled();
    });
});
