/**
 * useDebounce Hook
 * 
 * A React-friendly wrapper around the debounce utility.
 * Handles cleanup on unmount automatically.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import { useMemo, useEffect } from '@wordpress/element';
import { debounce } from '../utils';

/**
 * useDebounce
 * @param {Function} func - Function to debounce
 * @param {number} wait - Delay in milliseconds
 * @param {Object} options - Debounce options
 * @param {Array} deps - Dependency array for useMemo
 * @returns {Function} Debounced function
 */
export default function useDebounce(func, wait, options = {}, deps = []) {
    const debouncedFunc = useMemo(() => {
        return debounce(func, wait, options);
    }, [...deps, func, wait, JSON.stringify(options)]);

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            debouncedFunc.cancel();
        };
    }, [debouncedFunc]);

    return debouncedFunc;
}
