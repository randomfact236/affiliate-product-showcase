/**
 * Affiliate Product Showcase - Frontend JavaScript v2.0
 * Enterprise-grade with debouncing, caching, and error recovery
 *
 * @package AffiliateProductShowcase\Assets
 * @since   2.0.0
 */

(function (window, document) {
    'use strict';

    /**
     * @typedef {Object} FrontendConfig
     * @property {number} debounceDelay - Delay for search debouncing in ms
     * @property {number} animationDuration - Duration for UI animations in ms
     * @property {number} maxRetries - Maximum retry attempts for failed requests
     * @property {number} retryDelay - Base delay between retries in ms
     */

    /**
     * @typedef {Object} FrontendState
     * @property {boolean} isLoading - Whether a request is in progress
     * @property {AbortController|null} currentRequest - Current request controller
     * @property {number} retryCount - Current retry attempt count
     * @property {Map<string, Object>} cache - Response cache
     */

    /**
     * @typedef {Object} FilterState
     * @property {string} category - Selected category filter
     * @property {string[]} tags - Selected tag filters
     * @property {string} sort - Sort order
     * @property {string} search - Search query
     * @property {number} page - Current page number
     * @property {number} per_page - Items per page
     */

    /**
     * @typedef {Object} ApsData
     * @property {string} ajaxUrl - WordPress AJAX endpoint
     * @property {string} nonce - Security nonce
     * @property {Object} i18n - Localized strings
     */

    /** @type {FrontendConfig} */
    const CONFIG = {
        debounceDelay: 300,
        animationDuration: 200,
        maxRetries: 3,
        retryDelay: 1000
    };

    /** @type {FrontendState} */
    const state = {
        isLoading: false,
        currentRequest: null,
        retryCount: 0,
        cache: new Map()
    };

    /**
     * Initialize when DOM is ready
     */
    function init() {
        if (typeof apsData === 'undefined') {
            console.error('APS: apsData not localized. Script may not be properly enqueued.');
            return;
        }

        const container = document.querySelector('.aps-showcase-container');
        if (!container) {
            console.warn('APS: Showcase container not found');
            return;
        }

        // Cache DOM elements for performance
        cacheElements(container);

        // Initialize all handlers
        initSearch(container);
        initFilters(container);
        initSorting(container);
        initPagination(container);
        initAccessibility(container);
    }

    /**
     * Cache DOM elements for better performance
     */
    function cacheElements(container) {
        container._cachedElements = {
            searchInput: container.querySelector('#aps-search-input'),
            searchSpinner: container.querySelector('.aps-search-spinner'),
            tabs: container.querySelectorAll('.aps-tab'),
            tags: container.querySelectorAll('.aps-tags-grid .aps-tag'),
            clearBtn: container.querySelector('.aps-clear-all'),
            sortSelect: container.querySelector('.aps-sort-select'),
            grid: container.querySelector('.aps-cards-grid'),
            pagination: container.querySelector('.aps-pagination'),
            resultsInfo: container.querySelector('.aps-results-info')
        };
    }

    /**
     * Search functionality with debouncing
     */
    function initSearch(container) {
        const cached = container._cachedElements;
        const searchInput = cached.searchInput;
        const spinner = cached.searchSpinner;

        if (!searchInput) return;

        let debounceTimer;

        searchInput.addEventListener('input', function (e) {
            clearTimeout(debounceTimer);
            if (spinner) spinner.classList.add('active');

            debounceTimer = setTimeout(() => {
                const query = e.target.value.trim();
                performFilter(container, { search: query });
                if (spinner) spinner.classList.remove('active');
            }, CONFIG.debounceDelay);
        });

        // Clear search on Escape
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                this.value = '';
                performFilter(container, { search: '' });
                this.blur();
            }
        });
    }

    /**
     * Category and tag filters
     */
    function initFilters(container) {
        // Category tabs
        container.querySelectorAll('.aps-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                if (this.classList.contains('active')) return;

                // Update UI
                container.querySelectorAll('.aps-tab').forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                // Trigger filter
                performFilter(container, {
                    category: this.dataset.category,
                    page: 1 // Reset to first page on filter change
                });
            });
        });

        // Tags
        container.querySelectorAll('.aps-tags-grid .aps-tag').forEach(tag => {
            tag.addEventListener('click', function () {
                this.classList.toggle('active');
                const isPressed = this.classList.contains('active');
                this.setAttribute('aria-pressed', isPressed.toString());

                performFilter(container, {
                    tags: getSelectedTags(container),
                    page: 1
                });
            });
        });

        // Clear all
        const clearBtn = container.querySelector('.aps-clear-all');
        if (clearBtn) {
            clearBtn.addEventListener('click', function (e) {
                e.preventDefault();
                clearAllFilters(container);
            });
        }
    }

    /**
     * Sorting dropdown
     */
    function initSorting(container) {
        const sortSelect = container.querySelector('.aps-sort-select');
        if (!sortSelect) return;

        sortSelect.addEventListener('change', function () {
            performFilter(container, { sort: this.value });
        });
    }

    /**
     * Pagination controls
     */
    function initPagination(container) {
        container.addEventListener('click', function (e) {
            const btn = e.target.closest('.aps-pagination-number, .aps-pagination-prev, .aps-pagination-next');
            if (!btn || btn.classList.contains('disabled') || btn.classList.contains('active')) return;

            const page = parseInt(btn.dataset.page, 10);
            if (isNaN(page)) return;

            performFilter(container, { page });

            // Scroll to top of grid
            const grid = container.querySelector('.aps-cards-grid');
            grid?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    /**
     * Accessibility enhancements
     */
    function initAccessibility(container) {
        // Announce changes to screen readers
        const announce = (message) => {
            const liveRegion = container.querySelector('[aria-live="polite"]');
            if (liveRegion) {
                liveRegion.textContent = message;
            }
        };

        // Keyboard navigation for tags
        container.querySelectorAll('.aps-tags-grid').forEach(grid => {
            grid.addEventListener('keydown', function (e) {
                const tags = Array.from(this.querySelectorAll('.aps-tag'));
                const currentIndex = tags.indexOf(document.activeElement);

                if (e.key === 'ArrowRight' && currentIndex < tags.length - 1) {
                    e.preventDefault();
                    tags[currentIndex + 1].focus();
                } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
                    e.preventDefault();
                    tags[currentIndex - 1].focus();
                }
            });
        });
    }

    /**
     * Get selected tags
     */
    function getSelectedTags(container) {
        return Array.from(
            container.querySelectorAll('.aps-tags-grid .aps-tag.active')
        ).map(tag => tag.dataset.tag);
    }

    /**
     * Get current filter state
     */
    function getFilterState(container) {
        const activeTab = container.querySelector('.aps-tab.active');
        const sortSelect = container.querySelector('.aps-sort-select');
        const searchInput = container.querySelector('#aps-search-input');

        return {
            category: activeTab?.dataset.category || 'all',
            tags: getSelectedTags(container),
            sort: sortSelect?.value || 'featured',
            search: searchInput?.value.trim() || '',
            page: parseInt(container.dataset.currentPage, 10) || 1,
            per_page: parseInt(container.dataset.perPage, 10) || 12
        };
    }

    /**
     * Clear all filters
     */
    function clearAllFilters(container) {
        // Reset tabs
        container.querySelectorAll('.aps-tab').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        const allTab = container.querySelector('.aps-tab[data-category="all"]');
        if (allTab) {
            allTab.classList.add('active');
            allTab.setAttribute('aria-selected', 'true');
        }

        // Reset tags
        container.querySelectorAll('.aps-tags-grid .aps-tag').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-pressed', 'false');
        });

        // Reset sort
        const sortSelect = container.querySelector('.aps-sort-select');
        if (sortSelect) {
            sortSelect.value = 'featured';
        }

        // Reset search
        const searchInput = container.querySelector('#aps-search-input');
        if (searchInput) {
            searchInput.value = '';
        }

        performFilter(container, { category: 'all', tags: [], search: '', sort: 'featured', page: 1 });
    }

    /**
     * Main filter function with caching and error handling
     */
    function performFilter(container, updates = {}) {
        if (state.isLoading) {
            state.currentRequest?.abort();
        }

        const currentState = getFilterState(container);
        const newState = { ...currentState, ...updates };

        // Generate cache key
        const cacheKey = JSON.stringify(newState);

        // Check cache
        if (state.cache.has(cacheKey)) {
            updateUI(container, state.cache.get(cacheKey), newState);
            return;
        }

        // Update URL params for shareability
        updateURLParams(newState);

        // Prepare request
        const formData = new FormData();
        formData.append('action', 'aps_filter_products');
        formData.append('nonce', apsData.nonce);
        formData.append('category', newState.category);
        formData.append('tags', JSON.stringify(newState.tags));
        formData.append('sort', newState.sort);
        formData.append('search', newState.search);
        formData.append('page', newState.page);
        formData.append('per_page', newState.per_page);

        // Abort controller for cancellation
        const controller = new AbortController();
        state.currentRequest = controller;

        // UI loading state
        setLoadingState(container, true);

        fetch(apsData.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: controller.signal
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (!data.success) throw new Error(data.data?.message || 'Unknown error');

                // Cache successful response
                state.cache.set(cacheKey, data.data);
                if (state.cache.size > 50) state.cache.delete(state.cache.keys().next().value);

                updateUI(container, data.data, newState);
                state.retryCount = 0;
            })
            .catch(error => {
                if (error.name === 'AbortError') return;

                console.error('APS: Filter error', error);

                if (state.retryCount < CONFIG.maxRetries) {
                    state.retryCount++;
                    setTimeout(() => performFilter(container, updates), CONFIG.retryDelay * state.retryCount);
                } else {
                    showError(container, error.message);
                    state.retryCount = 0;
                }
            })
            .finally(() => {
                setLoadingState(container, false);
                state.isLoading = false;
            });
    }

    /**
     * Update UI with new data
     */
    function updateUI(container, data, state) {
        const cached = container._cachedElements;
        const grid = cached.grid || container.querySelector('.aps-cards-grid');
        const pagination = cached.pagination || container.querySelector('.aps-pagination');

        if (!grid) return;

        // Update grid with animation
        grid.style.opacity = '0';

        setTimeout(() => {
            // Clear grid safely
            while (grid.firstChild) {
                grid.removeChild(grid.firstChild);
            }

            if (data.products && data.products.trim()) {
                // Use DOMParser for safe HTML insertion
                const parser = new DOMParser();
                const doc = parser.parseFromString(data.products, 'text/html');
                // Move all children from parsed document to grid
                const fragment = document.createDocumentFragment();
                while (doc.body.firstChild) {
                    fragment.appendChild(doc.body.firstChild);
                }
                grid.appendChild(fragment);
            } else {
                // Safe DOM construction - no innerHTML
                const p = document.createElement('p');
                p.className = 'aps-no-products';
                p.textContent = apsData.i18n.noProducts || 'No products found';
                grid.appendChild(p);
            }

            // Update pagination
            if (pagination && data.pagination) {
                pagination.outerHTML = data.pagination;
            }

            // Update state attributes
            container.dataset.currentPage = state.page;
            container.dataset.totalPages = data.total_pages || 1;

            // Fade in
            grid.style.opacity = '1';

            // Announce to screen readers
            const liveRegion = container.querySelector('.aps-results-info');
            if (liveRegion && data.count !== undefined) {
                liveRegion.textContent = `${data.count} products found`;
            }
        }, CONFIG.animationDuration);
    }

    /**
     * Set loading state
     */
    function setLoadingState(container, isLoading) {
        state.isLoading = isLoading;
        const cached = container._cachedElements;
        const grid = cached.grid || container.querySelector('.aps-cards-grid');

        if (grid) {
            if (isLoading) {
                grid.classList.add('loading');
            } else {
                grid.classList.remove('loading');
            }
        }
    }

    /**
     * Show error message
     */
    function showError(container, message) {
        const cached = container._cachedElements;
        const grid = cached.grid || container.querySelector('.aps-cards-grid');
        if (!grid) return;

        // Clear grid safely
        while (grid.firstChild) {
            grid.removeChild(grid.firstChild);
        }

        // Safe DOM construction - no innerHTML
        const errorDiv = document.createElement('div');
        errorDiv.className = 'aps-error';

        const p = document.createElement('p');
        p.textContent = apsData.i18n?.error || message || 'An error occurred';
        errorDiv.appendChild(p);

        const retryBtn = document.createElement('button');
        retryBtn.type = 'button';
        retryBtn.className = 'aps-retry-btn';
        retryBtn.textContent = apsData.i18n?.retry || 'Retry';
        retryBtn.addEventListener('click', function() {
            location.reload();
        });
        errorDiv.appendChild(retryBtn);

        grid.appendChild(errorDiv);
    }

    /**
     * Update URL parameters for shareable filters
     */
    function updateURLParams(state) {
        if (!window.history || !window.URLSearchParams) return;

        const params = new URLSearchParams();

        if (state.category !== 'all') params.set('aps_category', state.category);
        if (state.tags.length) params.set('aps_tags', state.tags.join(','));
        if (state.sort !== 'featured') params.set('aps_sort', state.sort);
        if (state.search) params.set('aps_search', state.search);
        if (state.page > 1) params.set('aps_page', state.page);

        const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
        window.history.replaceState({ aps: state }, '', newUrl);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use using unified namespace
    window.APS = window.APS || {};
    window.APS.Frontend = {
        refresh: function (container) {
            performFilter(container || document.querySelector('.aps-showcase-container'), {});
        },
        clearFilters: function (container) {
            clearAllFilters(container || document.querySelector('.aps-showcase-container'));
        }
    };

})(window, document);
