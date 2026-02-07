/**
 * APS Shared Utilities
 * 
 * Centralized utility functions for Affiliate Product Showcase.
 * Implements DRY principles and standardized error handling.
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

(function (window) {
    'use strict';

    /**
     * @typedef {Object} AjaxOptions
     * @property {string} [url] - AJAX endpoint URL
     * @property {string} [type] - HTTP method (GET, POST, etc.)
     * @property {Object} [data] - Request data
     * @property {string} [dataType] - Expected response type
     */

    /**
     * @typedef {Object} NoticeOptions
     * @property {'success'|'error'|'warning'|'info'} type - Notice type
     * @property {string} message - Notice message
     */

    // Initialize main namespace
    window.APS = window.APS || {};

    /**
     * Utility functions namespace
     * @namespace APS.Utils
     */
    window.APS.Utils = {
        /**
         * Get AJAX URL from available WordPress globals
         * @returns {string} WordPress AJAX URL
         */
        getAjaxUrl: function () {
            if (typeof apsAdminVars !== 'undefined' && apsAdminVars.ajax_url) {
                return apsAdminVars.ajax_url;
            }
            if (typeof apsData !== 'undefined' && apsData.ajaxUrl) {
                return apsData.ajaxUrl;
            }
            if (typeof ajaxurl !== 'undefined') {
                return ajaxurl;
            }
            return '/wp-admin/admin-ajax.php';
        },

        /**
         * Debounce function execution
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @returns {Function} Debounced function
         */
        debounce: function (func, wait) {
            let timeout;
            return function () {
                var context = this;
                var args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    func.apply(context, args);
                }, wait);
            };
        },

        /**
         * Escape HTML string to prevent XSS
         * @param {string} str - String to escape
         * @returns {string} Escaped string
         */
        escapeHtml: function (str) {
            if (typeof str !== 'string') return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Sanitize URL for safe use in CSS/HTML context
         * Only allows http:, https:, and data: protocols
         * @param {string} url - URL to sanitize
         * @returns {string} Sanitized URL or empty string
         */
        sanitizeUrl: function (url) {
            if (typeof url !== 'string') return '';
            url = url.trim();
            if (!url) return '';
            const allowedProtocols = /^(https?:|data:)/i;
            if (!allowedProtocols.test(url)) {
                if (url.indexOf('//') === 0) {
                    return 'https:' + url;
                }
                return '';
            }
            return url;
        },

        /**
         * Get current status view from URL parameters
         * @returns {string} Current status filter value or 'all'
         */
        getCurrentStatusView: function () {
            const params = new URLSearchParams(window.location.search);
            return params.get('status') || params.get('aps_status') || 'all';
        },

        /**
         * Determine if a row should remain visible in current view
         * @param {string} newStatus - The new status of the item
         * @returns {boolean} True if row should stay visible
         */
        shouldKeepRowInCurrentView: function (newStatus) {
            const currentView = this.getCurrentStatusView();
            if (currentView === 'all') {
                return newStatus !== 'trashed';
            }
            return currentView === newStatus;
        },

        /**
         * Parse URL query parameters
         * @param {string} url - URL to parse
         * @returns {URLSearchParams} Parsed parameters
         */
        parseQueryParamsFromUrl: function (url) {
            try {
                const urlObj = new URL(url, window.location.origin);
                return urlObj.searchParams;
            } catch (e) {
                return new URLSearchParams();
            }
        },

        /**
         * Show admin notice with auto-dismiss (vanilla JS - no jQuery dependency)
         * @param {'success'|'error'|'warning'|'info'} type - Notice type
         * @param {string} message - Message text
         */
        showNotice: function (type, message) {
            const self = this;

            // Remove existing notices
            const existingNotices = document.querySelectorAll('.aps-js-notice');
            existingNotices.forEach(function (notice) {
                notice.remove();
            });

            // Determine target
            let target = document.querySelector('.wrap h1, .wrap h2');
            if (!target) target = document.querySelector('.wrap');
            if (!target) return;

            // Normalize type
            const validTypes = ['success', 'error', 'warning', 'info'];
            const noticeType = validTypes.indexOf(type) !== -1 ? type : 'info';

            // Create notice using vanilla JS
            const notice = document.createElement('div');
            notice.className = 'notice notice-' + noticeType + ' is-dismissible aps-js-notice';

            const paragraph = document.createElement('p');
            paragraph.textContent = message;
            notice.appendChild(paragraph);

            // Add dismiss button
            const dismissBtn = document.createElement('button');
            dismissBtn.type = 'button';
            dismissBtn.className = 'notice-dismiss';
            const srText = document.createElement('span');
            srText.className = 'screen-reader-text';
            srText.textContent = 'Dismiss this notice.';
            dismissBtn.appendChild(srText);
            notice.appendChild(dismissBtn);

            target.parentNode.insertBefore(notice, target.nextSibling);

            // Auto-dismiss after 5 seconds
            const timeout = setTimeout(function () {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.2s';
                setTimeout(function () {
                    if (notice.parentNode) {
                        notice.remove();
                    }
                }, 200);
            }, 5000);

            // Manual dismiss handler
            dismissBtn.addEventListener('click', function () {
                clearTimeout(timeout);
                notice.remove();
            });
        },

        /**
         * Standardized AJAX Request wrapper using fetch API
         * @param {AjaxOptions} options - AJAX options
         * @returns {Promise} Fetch promise
         */
        ajax: function (options) {
            const self = this;
            const defaults = {
                url: this.getAjaxUrl(),
                method: 'POST',
                timeout: 30000, // 30 second timeout
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            const settings = Object.assign({}, defaults, options);
            const formData = new FormData();

            if (settings.data) {
                Object.keys(settings.data).forEach(function (key) {
                    formData.append(key, settings.data[key]);
                });
            }

            // Create AbortController for timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(function () {
                controller.abort();
            }, settings.timeout);

            return fetch(settings.url, {
                method: settings.method,
                body: formData,
                headers: settings.headers,
                credentials: 'same-origin',
                signal: controller.signal
            }).then(function (response) {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            }).catch(function (error) {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    throw new Error('Request timed out');
                }
                throw error;
            });
        }
    };

    // Backward compatibility alias
    window.APS_Utils = window.APS.Utils;

})(window);