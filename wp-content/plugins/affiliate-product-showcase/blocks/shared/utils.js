/**
 * Shared Utilities for Blocks
 * 
 * Common utility functions used across all blocks.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

import { __ } from '@wordpress/i18n';
import { CACHE_TTL, CACHE_SIZE } from './constants';

/**
 * Debounce function with cancel support and options
 * @param {Function} func - Function to debounce
 * @param {number} wait - Delay in milliseconds
 * @param {Object} options - Options object
 * @param {boolean} options.leading - Specify invoking on the leading edge of the timeout
 * @param {boolean} options.trailing - Specify invoking on the trailing edge of the timeout
 * @param {number} options.maxWait - The maximum time func is allowed to be delayed before it's invoked
 * @returns {Function} Debounced function with cancel method
 */
export function debounce(func, wait, options = {}) {
    let lastArgs,
        lastThis,
        maxWait,
        result,
        timerId,
        lastCallTime,
        lastInvokeTime = 0,
        leading = false,
        maxing = false,
        trailing = true;

    if (typeof func !== 'function') {
        throw new TypeError('Expected a function');
    }
    wait = +wait || 0;
    if (typeof options === 'object') {
        leading = !!options.leading;
        maxing = 'maxWait' in options;
        maxWait = maxing ? Math.max(+options.maxWait || 0, wait) : maxWait;
        trailing = 'trailing' in options ? !!options.trailing : trailing;
    }

    function invokeFunc(time) {
        const args = lastArgs;
        const thisArg = lastThis;

        lastArgs = lastThis = undefined;
        lastInvokeTime = time;
        result = func.apply(thisArg, args);
        return result;
    }

    function leadingEdge(time) {
        // Reset any existing timer
        lastInvokeTime = time;
        // Start the timer for the trailing edge
        timerId = setTimeout(timerExpired, wait);
        // Invoke the leading edge if enabled
        return leading ? invokeFunc(time) : result;
    }

    function remainingWait(time) {
        const timeSinceLastCall = time - lastCallTime;
        const timeSinceLastInvoke = time - lastInvokeTime;
        const timeWaiting = wait - timeSinceLastCall;

        return maxing
            ? Math.min(timeWaiting, maxWait - timeSinceLastInvoke)
            : timeWaiting;
    }

    function shouldInvoke(time) {
        const timeSinceLastCall = time - lastCallTime;
        const timeSinceLastInvoke = time - lastInvokeTime;

        // Either this is the first call, activity has stopped and we're at the
        // trailing edge, the system time has gone backwards and we're treating
        // it as the trailing edge, or we've hit the `maxWait` limit.
        return (
            lastCallTime === undefined ||
            timeSinceLastCall >= wait ||
            timeSinceLastCall < 0 ||
            (maxing && timeSinceLastInvoke >= maxWait)
        );
    }

    function timerExpired() {
        const time = Date.now();
        if (shouldInvoke(time)) {
            return trailingEdge(time);
        }
        // Restart the timer
        timerId = setTimeout(timerExpired, remainingWait(time));
    }

    function trailingEdge(time) {
        timerId = undefined;

        // Only invoke if we have `lastArgs` which means `func` has been
        // debounced at least once.
        if (trailing && lastArgs) {
            return invokeFunc(time);
        }
        lastArgs = lastThis = undefined;
        return result;
    }

    function cancel() {
        if (timerId !== undefined) {
            clearTimeout(timerId);
        }
        lastInvokeTime = 0;
        lastArgs = lastCallTime = lastThis = timerId = undefined;
    }

    function flush() {
        return timerId === undefined ? result : trailingEdge(Date.now());
    }

    function debounced(...args) {
        const time = Date.now();
        const isInvoking = shouldInvoke(time);

        lastArgs = args;
        lastThis = this;
        lastCallTime = time;

        if (isInvoking) {
            if (timerId === undefined) {
                return leadingEdge(time);
            }
            if (maxing) {
                // Handle invocations in a tight loop caused by `maxWait`
                clearTimeout(timerId);
                timerId = setTimeout(timerExpired, wait);
                return invokeFunc(time);
            }
        }
        if (timerId === undefined) {
            timerId = setTimeout(timerExpired, wait);
        }
        return result;
    }

    debounced.cancel = cancel;
    debounced.flush = flush;
    return debounced;
}

/**
 * Simple LRU cache with TTL support
 */
export class SimpleCache {
    constructor(maxSize = CACHE_SIZE, ttl = CACHE_TTL) {
        this.cache = new Map(); // Map preserves insertion order, we'll use it for LRU
        this.maxSize = maxSize;
        this.ttl = ttl;
    }

    get(key) {
        if (!this.cache.has(key)) return null;

        const cached = this.cache.get(key);

        // TTL Check
        if (Date.now() - cached.timestamp > this.ttl) {
            this.cache.delete(key);
            return null;
        }

        // Refresh LRU: delete and re-insert to move to end (most recently used)
        this.cache.delete(key);
        this.cache.set(key, { ...cached, timestamp: Date.now() }); // Also refresh timestamp on access?
        // User requested "TTL refresh on access", so yes:

        return cached.data;
    }

    set(key, data) {
        // If updating existing, delete first to refresh position
        if (this.cache.has(key)) {
            this.cache.delete(key);
        } else if (this.cache.size >= this.maxSize) {
            // Evict oldest (first item in Map)
            const oldestKey = this.cache.keys().next().value;
            this.cache.delete(oldestKey);
        }

        this.cache.set(key, { data, timestamp: Date.now() });
    }

    clear() {
        this.cache.clear();
    }

    get size() {
        return this.cache.size;
    }
}

/**
 * Render star rating
 * @param {number} rating - Rating value (0-5)
 * @returns {JSX.Element|null} Star rating element
 */
export function renderStars(rating) {
    if (!rating || rating < 0 || rating > 5) return null;

    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating - fullStars >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

    for (let i = 0; i < fullStars; i++) {
        stars.push(<span key={`full-${i}`} className="aps-star">★</span>);
    }
    if (hasHalfStar) {
        stars.push(<span key="half" className="aps-star aps-star-half">☆</span>);
    }
    for (let i = 0; i < emptyStars; i++) {
        stars.push(<span key={`empty-${i}`} className="aps-star empty">☆</span>);
    }

    return (
        <div className="aps-stars" aria-label={`${rating} ${__('out of 5 stars', 'affiliate-product-showcase')}`}>
            {stars}
        </div>
    );
}

/**
 * Format price with locale support
 * @param {number|string} price - Price value
 * @param {string} currency - Currency code (ISO 4217) or symbol
 * @param {string} locale - Locale code (e.g. 'en-US', 'de-DE')
 * @returns {string} Formatted price
 */
export function formatPrice(price, currency = 'USD', locale = 'en-US') {
    if (price === null || price === undefined) return '';
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;
    if (isNaN(numPrice)) return '';

    try {
        // Check if currency is a symbol (like '$') or code ('USD')
        // Intl.NumberFormat expects ISO code. If symbol provided, we might need fallback or mapping.
        // Assuming input is USD for now based on previous code defaulting to '$', but allowing overrides.
        // Ideally should assume currency is a valid ISO code like 'USD', 'EUR'.
        // If previous code passed '$', we need to handle that. 
        // For backwards compatibility, if length is 1 or starts with $, treat as USD.
        let currencyCode = currency;
        if (currency.length === 1 || currency === '$') {
            currencyCode = 'USD';
        }

        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currencyCode,
        }).format(numPrice);
    } catch (e) {
        // Fallback
        return `${currency === 'USD' ? '$' : currency}${numPrice.toFixed(2)}`;
    }
}

/**
 * Truncate text with ellipsis respecting word boundaries
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength = 100) {
    if (!text || text.length <= maxLength) return text || '';

    // Initial truncation
    let truncated = text.substring(0, maxLength);

    // Find last space to break at word boundary
    const lastSpace = truncated.lastIndexOf(' ');

    if (lastSpace > 0) {
        truncated = truncated.substring(0, lastSpace);
    }

    return `${truncated}...`;
}

/**
 * Generate unique ID for accessibility
 * @param {string} prefix - ID prefix
 * @param {string|number} id - Unique identifier
 * @param {string} clientId - Block client ID
 * @returns {string} Unique ID
 */
export function generateA11yId(prefix, id, clientId = '') {
    const safeClientId = clientId ? `${clientId}-` : '';
    return `aps-${safeClientId}${prefix}-${id}`;
}

/**
 * Validate and sanitize affiliate URL
 * @param {string} url - The URL to validate
 * @returns {string} Validated URL or '#' if invalid
 */
export function validateAffiliateUrl(url) {
    if (!url) return '#';
    try {
        const parsed = new URL(url);

        // Protocol check
        if (!['http:', 'https:'].includes(parsed.protocol)) {
            return '#';
        }

        const hostname = parsed.hostname.toLowerCase();

        // Block private IP ranges
        if (/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|0\.0\.0\.0|::1|fc00:|fe80:)/i.test(hostname)) {
            if (process.env.NODE_ENV === 'development') console.warn('Blocked private IP:', hostname);
            return '#';
        }

        // Block localhost variations
        if (['localhost', 'localhost.localdomain'].includes(hostname)) {
            return '#';
        }

        // Block credential URLs
        if (parsed.username || parsed.password) {
            return '#';
        }

        return url;
    } catch {
        return '#';
    }
}

/**
 * Strip HTML tags from string
 * @param {string} html - HTML string
 * @returns {string} Plain text
 */
export function stripHtml(html) {
    if (!html) return '';
    // Browser environment
    if (typeof document !== 'undefined') {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }
    // Fallback for non-browser environments
    return html.replace(/<[^>]*>?/gm, '');
}
