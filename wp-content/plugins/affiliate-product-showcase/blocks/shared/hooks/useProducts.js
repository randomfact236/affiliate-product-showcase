/**
 * useProducts Hook
 * 
 * Manages product data fetching, caching, loading states, and errors.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * useProducts
 * @param {Object} params - Query parameters for the API
 * @param {Object} options - Options object
 * @param {Object} options.cache - Cache instance from useProductCache
 * @param {boolean} options.skip - Whether to skip fetching
 * @returns {Object} { products, isLoading, error, refetch }
 */
export default function useProducts(params = {}, { cache = null, skip = false } = {}) {
    const [products, setProducts] = useState([]);
    const [isLoading, setIsLoading] = useState(!skip);
    const [error, setError] = useState(null);

    // Keep track of the latest params to avoid race conditions if needed, 
    // though AbortController handles most of it.
    const paramsRef = useRef(params);
    paramsRef.current = params;

    const fetchProducts = useCallback(async (abortSignal) => {
        if (skip) return;

        setIsLoading(true);
        setError(null);

        try {
            // Generate a stable cache key based on params
            // We sort keys to ensure object property order doesn't affect the key
            const cacheKey = 'products:' + JSON.stringify(
                Object.keys(params).sort().reduce((obj, key) => {
                    obj[key] = params[key];
                    return obj;
                }, {})
            );

            // 1. Check Cache
            if (cache) {
                const cachedData = cache.get(cacheKey);
                if (cachedData) {
                    setProducts(cachedData);
                    setIsLoading(false);
                    return;
                }
            }

            // 2. Fetch from API
            const response = await apiFetch({
                path: '/affiliate-product-showcase/v1/products',
                method: 'GET',
                params: {
                    ...params,
                    status: 'publish', // Always fetch published
                },
                signal: abortSignal,
            });

            if (response && Array.isArray(response)) {
                // 3. Update Cache
                if (cache) {
                    cache.set(cacheKey, response);
                }
                setProducts(response);
            } else {
                setProducts([]);
            }
        } catch (err) {
            if (err.name === 'AbortError') {
                return; // Ignore abort errors
            }
            if (process.env.NODE_ENV === 'development') {
                console.error('Error fetching products:', err);
            }
            setError(__('Failed to load products. Please try again.', 'affiliate-product-showcase'));
            setProducts([]);
        } finally {
            if (!abortSignal?.aborted) {
                setIsLoading(false);
            }
        }
    }, [JSON.stringify(params), cache, skip]);

    useEffect(() => {
        const abortController = new AbortController();
        fetchProducts(abortController.signal);

        return () => {
            abortController.abort();
        };
    }, [fetchProducts]);

    return {
        products,
        isLoading,
        error,
        refetch: () => fetchProducts(),
    };
}
