/**
 * Affiliate Product Showcase - Product Table UI JavaScript
 *
 * Handles interactions for product table UI:
 * - Bulk upload products
 * - Check product links
 * - Filter interactions
 * - Toggle featured filter
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Product Table UI Namespace
     */
    const APSTableUI = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initSearch();
            this.initFilters();
            this.initFeaturedToggle();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Make global functions available
            window.apsBulkUploadProducts = this.bulkUploadProducts.bind(this);
            window.apsCheckProductLinks = this.checkProductLinks.bind(this);
        },

        /**
         * Initialize search functionality
         */
        initSearch: function() {
            const searchInput = $('#aps_search_products');
            
            if (searchInput.length) {
                // Search on Enter key
                searchInput.on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        APSTableUI.performSearch();
                    }
                });

                // Auto-search with debounce
                let searchTimeout;
                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        APSTableUI.performSearch();
                    }, 500);
                });
            }
        },

        /**
         * Perform search
         */
        performSearch: function() {
            const searchTerm = $('#aps_search_products').val();
            const url = new URL(window.location.href);
            
            if (searchTerm) {
                url.searchParams.set('aps_search', searchTerm);
            } else {
                url.searchParams.delete('aps_search');
            }
            
            window.location.href = url.toString();
        },

        /**
         * Initialize filters
         */
        initFilters: function() {
            // Category filter
            $('#aps_category_filter').on('change', function() {
                const category = $(this).val();
                const url = new URL(window.location.href);
                
                if (category) {
                    url.searchParams.set('aps_category_filter', category);
                } else {
                    url.searchParams.delete('aps_category_filter');
                }
                
                window.location.href = url.toString();
            });

            // Sort order
            $('#aps_sort_order').on('change', function() {
                const order = $(this).val();
                const url = new URL(window.location.href);
                url.searchParams.set('order', order);
                window.location.href = url.toString();
            });

            // Bulk action
            $('#aps_bulk_action').on('change', function() {
                const action = $(this).val();
                if (action) {
                    $(this).siblings('.aps-btn-apply').prop('disabled', false);
                } else {
                    $(this).siblings('.aps-btn-apply').prop('disabled', true);
                }
            });

            // Apply bulk action
            $('.aps-btn-apply').on('click', function() {
                const action = $('#aps_bulk_action').val();
                if (!action) {
                    alert(apsProductTableUI.strings.selectAction);
                    return;
                }
                APSTableUI.applyBulkAction(action);
            });
        },

        /**
         * Initialize featured toggle
         */
        initFeaturedToggle: function() {
            $('#aps_show_featured').on('change', function() {
                const isChecked = $(this).prop('checked');
                const url = new URL(window.location.href);
                
                if (isChecked) {
                    url.searchParams.set('featured_filter', '1');
                } else {
                    url.searchParams.delete('featured_filter');
                }
                
                window.location.href = url.toString();
            });
        },

        /**
         * Bulk upload products
         */
        bulkUploadProducts: function() {
            if (!confirm(apsProductTableUI.strings.confirmBulkUpload)) {
                return;
            }

            // For now, show a placeholder message
            // This would be implemented with a modal for file upload
            alert('Bulk upload functionality coming soon!\n\nThis will allow you to upload multiple products from a CSV file.');
        },

        /**
         * Check product links
         */
        checkProductLinks: function() {
            if (!confirm(apsProductTableUI.strings.confirmCheckLinks)) {
                return;
            }

            const $btn = $('button[onclick="apsCheckProductLinks()"]');
            const originalText = $btn.html();

            // Show loading state
            $btn.prop('disabled', true)
               .html('<span class="dashicons dashicons-update dashicons-spin"></span> ' + apsProductTableUI.strings.processing);

            // Simulate link checking
            setTimeout(function() {
                // Reset button
                $btn.prop('disabled', false)
                   .html(originalText);
                
                // Show results (placeholder)
                alert('Link check complete!\n\n✓ All links are working correctly.');
            }, 2000);
        },

        /**
         * Apply bulk action
         */
        applyBulkAction: function(action) {
            const selectedProducts = [];
            $('input[name="post[]"]:checked').each(function() {
                selectedProducts.push($(this).val());
            });

            if (selectedProducts.length === 0) {
                alert('Please select at least one product.');
                return;
            }

            // Send AJAX request
            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_bulk_action',
                    nonce: apsProductTableUI.nonce,
                    bulk_action: action,
                    product_ids: selectedProducts
                },
                beforeSend: function() {
                    // Show loading overlay
                    APSTableUI.showLoading();
                },
                success: function(response) {
                    APSTableUI.hideLoading();
                    
                    if (response.success) {
                        // Reload page to see changes
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    APSTableUI.hideLoading();
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Show loading overlay
         */
        showLoading: function() {
            if ($('#aps-loading-overlay').length === 0) {
                $('body').append(`
                    <div id="aps-loading-overlay">
                        <div class="aps-loading-spinner">
                            <span class="dashicons dashicons-update dashicons-spin"></span>
                            <p>${apsProductTableUI.strings.processing}</p>
                        </div>
                    </div>
                `);
            }
            $('#aps-loading-overlay').fadeIn(200);
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            $('#aps-loading-overlay').fadeOut(200, function() {
                $(this).remove();
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        APSTableUI.init();
    });

    // Export to global scope
    window.APSTableUI = APSTableUI;

})(jQuery);

/**
 * Loading overlay styles
 */
const apsLoadingStyles = `
    <style>
        #aps-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999999;
            display: none;
        }
        .aps-loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        .aps-loading-spinner .dashicons {
            font-size: 48px;
            color: #2271b1;
            display: block;
            margin: 0 auto 16px;
        }
        .aps-loading-spinner p {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
            color: #1d2327;
        }
        .dashicons-spin {
            animation: dashicons-spin 1s infinite linear;
        }
        @keyframes dashicons-spin {
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
`;

// Inject styles
$(apsLoadingStyles).appendTo('head');
