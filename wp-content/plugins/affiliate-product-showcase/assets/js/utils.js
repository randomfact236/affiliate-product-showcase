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

    window.APS_Utils = {
        /**
         * Get AJAX URL
         * @returns {string} WordPress AJAX URL
         */
        getAjaxUrl: function () {
            if (typeof aps_admin_vars !== 'undefined' && aps_admin_vars.ajax_url) {
                return aps_admin_vars.ajax_url;
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
            return function (...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        },

        /**
         * Escape HTML string
         * @param {string} str - String to escape
         * @returns {string} Escaped string
         */
        escapeHtml: function (str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Sanitize URL for safe use in CSS context
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
                return 'https:' + url;
            }
            return url;
        },

        /**
         * Show admin notice
         * @param {string} type - 'success', 'error', 'warning', 'info'
         * @param {string} message - Message text
         */
        showNotice: function (type, message) {
            // Remove existing notices
            const existingNotices = document.querySelectorAll('.aps-js-notice');
            existingNotices.forEach(notice => notice.remove());

            // Determine target
            let target = document.querySelector('.wrap h1, .wrap h2');
            if (!target) target = document.querySelector('.wrap');
            if (!target) return;

            // Normalize type
            const noticeType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';

            // Create notice using jQuery for consistency with animation
            const $notice = $(`<div class="notice notice-${noticeType} is-dismissible aps-js-notice"><p>${this.escapeHtml(message)}</p></div>`);

            $(target).after($notice);

            // Auto-dismiss after 5 seconds
            const timeout = setTimeout(() => {
                $notice.fadeOut(200, function () {
                    $(this).remove();
                });
            }, 5000);

            // Clear timeout if manually dismissed
            $notice.on('click', '.notice-dismiss', function () {
                clearTimeout(timeout);
            });
        },

        /**
         * Standardized AJAX Request
         * @param {Object} options - jQuery AJAX options
         * @returns {Promise} jQuery AJAX promise
         */
        ajax: function (options) {
            const defaults = {
                url: this.getAjaxUrl(),
                type: 'POST',
                dataType: 'json'
            };

            const settings = { ...defaults, ...options };

            return $.ajax(settings);
        }
    };

})(window);
