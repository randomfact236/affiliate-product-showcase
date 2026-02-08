/**
 * useProductCache Hook
 * 
 * Manages instance-level caching for blocks.
 * Ensures that each block instance has its own cache to prevent cross-contamination
 * while persisting data across re-renders.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import { useRef, useEffect } from '@wordpress/element';
import { SimpleCache } from '../utils';
import { CACHE_TTL, CACHE_SIZE } from '../constants';

/**
 * useProductCache
 * @param {number} maxSize - Maximum number of items in cache
 * @param {number} ttl - Time to live in milliseconds
 * @returns {SimpleCache} Cache instance
 */
export default function useProductCache(maxSize = CACHE_SIZE, ttl = CACHE_TTL) {
    // Use useRef to maintain the cache instance across renders
    const cacheRef = useRef(null);

    // Initialize cache if it doesn't exist
    if (!cacheRef.current) {
        cacheRef.current = new SimpleCache(maxSize, ttl);
    }

    // Cleanup on unmount (optional, but good practice if SimpleCache had subscriptions/timers)
    useEffect(() => {
        return () => {
            if (cacheRef.current) {
                cacheRef.current.clear();
            }
        };
    }, []);

    return cacheRef.current;
}
