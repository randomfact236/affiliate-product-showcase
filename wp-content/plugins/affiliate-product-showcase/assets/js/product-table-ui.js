/**
 * Affiliate Product Showcase - Product Table UI JavaScript (Enhanced)
 *
 * Handles interactions for product table UI with AJAX:
 * - AJAX-based filtering (no page reloads)
 * - Client-side sorting (instant)
 * - Smooth animations and transitions
 * - Live status updates
 * - Search highlighting
 * - Bulk actions
 * - Link checking
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
         * Cached products data
         */
        products: [],

        /**
         * Current sort state
         */
        sortState: {
            column: 'date',
            direction: 'desc'
        },

        /**
         * Current filter state
         */
        filterState: {
            search: '',
            category: 0,
            featured: false,
            status: 'all',
            per_page: 20,
            page: 1
        },

        /**
         * Initialize
         */
        init: function() {
            // Layout-first mode: unless explicitly enabled, avoid AJAX filtering/sorting.
            // This keeps the server-rendered WP_List_Table stable and matches the flowchart design.
            if (!window.apsProductTableUI || window.apsProductTableUI.enableAjax !== true) {
                this.bindEvents();
                return;
            }

            // Don't load initial products via AJAX
            // WordPress already loads them on page load
            // We only use AJAX for filtering/sorting after user interaction
            
            this.bindEvents();
            this.initSearch();
            this.initFilters();
            this.initFeaturedToggle();
            this.initSorting();
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
                // Auto-search with debounce
                let searchTimeout;
                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        APSTableUI.filterState.search = $(this).val();
                        APSTableUI.filterState.page = 1;
                        APSTableUI.filterProducts();
                    }, 300);
                });
            }
        },

        /**
         * Initialize filters
         */
        initFilters: function() {
            // Category filter
            $('#aps_category_filter').on('change', function() {
                APSTableUI.filterState.category = $(this).val();
                APSTableUI.filterState.page = 1;
                APSTableUI.filterProducts();
            });

            // Sort order
            $('#aps_sort_order').on('change', function() {
                const order = $(this).val();
                APSTableUI.sortState.direction = order;
                APSTableUI.sortProducts();
            });

            // Apply bulk action
            $('.aps-btn-apply').on('click', function() {
                const action = $('#aps_bulk_action').val();
                const selectedProducts = [];
                $('input[name="post[]"]:checked').each(function() {
                    selectedProducts.push($(this).val());
                });

                if (selectedProducts.length === 0) {
                    alert('Please select at least one product.');
                    return;
                }

                // Confirmation dialog for safety
                const actionText = $('#aps_bulk_action option:selected').text();
                if (!confirm(`Are you sure you want to perform "${actionText}" on ${selectedProducts.length} product(s)?`)) {
                    return;
                }

                APSTableUI.applyBulkAction(action);
            });

            // Status count links
            $('.aps-count-item').on('click', function(e) {
                e.preventDefault();
                const status = $(this).data('status');
                APSTableUI.filterState.status = status;
                APSTableUI.filterState.page = 1;
                APSTableUI.filterProducts();
                
                // Update active state
                $('.aps-count-item').removeClass('active');
                $(this).addClass('active');
            });
        },

        /**
         * Initialize featured toggle
         */
        initFeaturedToggle: function() {
            $('#aps_show_featured').on('change', function() {
                APSTableUI.filterState.featured = $(this).prop('checked');
                APSTableUI.filterState.page = 1;
                APSTableUI.filterProducts();
            });
        },

        /**
         * Initialize sorting
         */
        initSorting: function() {
            // Add click handlers to table headers
            $('.wp-list-table th.sortable').on('click', function() {
                const column = $(this).data('sort-column');
                
                // Toggle direction if clicking same column
                if (APSTableUI.sortState.column === column) {
                    APSTableUI.sortState.direction = APSTableUI.sortState.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    APSTableUI.sortState.column = column;
                    APSTableUI.sortState.direction = 'asc';
                }
                
                APSTableUI.sortProducts();
                
                // Update sort indicator
                $('.wp-list-table th.sortable').removeClass('sorted-asc sorted-desc');
                $(this).addClass('sorted-' + APSTableUI.sortState.direction);
            });
        },

        /**
         * Filter products via AJAX
         */
        filterProducts: function() {
            // Show loading state
            APSTableUI.showTableLoading();

            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_filter_products',
                    nonce: apsProductTableUI.nonce,
                    search: this.filterState.search,
                    category: this.filterState.category,
                    featured: this.filterState.featured,
                    status: this.filterState.status,
                    per_page: this.filterState.per_page,
                    page: this.filterState.page,
                },
                success: function(response) {
                    if (response.success) {
                        APSTableUI.products = response.data.products;
                        APSTableUI.updateTable(response.data.products);
                        APSTableUI.updatePagination(response.data);
                        APSTableUI.highlightSearchTerms();
                    } else {
                        console.error('Filter error:', response.data);
                        alert('Error: ' + (response.data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Filter error:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status:', status);
                    alert('An error occurred while filtering products. Please try again.');
                },
                complete: function() {
                    APSTableUI.hideTableLoading();
                }
            });
        },

        /**
         * Sort products client-side
         */
        sortProducts: function() {
            const column = this.sortState.column;
            const direction = this.sortState.direction;

            this.products.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                // Handle nested properties
                if (column.includes('.')) {
                    const parts = column.split('.');
                    valA = a;
                    valB = b;
                    for (const part of parts) {
                        valA = valA?.[part];
                        valB = valB?.[part];
                    }
                }

                // Handle undefined/null values
                if (valA == null) valA = '';
                if (valB == null) valB = '';

                // String comparison
                if (typeof valA === 'string' && typeof valB === 'string') {
                    if (direction === 'asc') {
                        return valA.localeCompare(valB);
                    } else {
                        return valB.localeCompare(valA);
                    }
                }

                // Number comparison
                if (typeof valA === 'number' && typeof valB === 'number') {
                    if (direction === 'asc') {
                        return valA - valB;
                    } else {
                        return valB - valA;
                    }
                }

                // Boolean comparison
                if (typeof valA === 'boolean' && typeof valB === 'boolean') {
                    if (direction === 'asc') {
                        return valA === valB ? 0 : (valA ? 1 : -1);
                    } else {
                        return valA === valB ? 0 : (valA ? -1 : 1);
                    }
                }

                return 0;
            });

            this.updateTable(this.products);
        },

        /**
         * Update table with new data
         */
        updateTable: function(products) {
            const $tbody = $('#the-list');
            
            // Animate out
            $tbody.fadeOut(150, function() {
                $(this).empty();
                
                if (products.length === 0) {
                    $(this).append(`
                        <tr class="no-items">
                            <td colspan="8">
                                <p>${apsProductTableUI.strings.noProducts || 'No products found.'}</p>
                            </td>
                        </tr>
                    `);
                } else {
                    // Render each product row
                    products.forEach(product => {
                        const row = APSTableUI.renderProductRow(product);
                        $(this).append(row);
                    });
                }
                
                // Animate in
                $(this).fadeIn(150);
                
                // Rebind event handlers
                APSTableUI.bindRowEvents();
            });
        },

        /**
         * Render single product row
         */
        renderProductRow: function(product) {
            const logoHtml = product.logo 
                ? `<img src="${product.logo}" alt="${escAttr(product.title)}" class="aps-product-logo">`
                : `<div class="aps-product-logo-placeholder">${product.title.charAt(0).toUpperCase()}</div>`;

            const categoriesHtml = product.categories.map(cat => 
                `<span class="aps-product-category">${escHtml(cat)}</span>`
            ).join(' ');

            const tagsHtml = product.tags.map(tag => 
                `<span class="aps-product-tag">${escHtml(tag)}</span>`
            ).join(' ');

            const ribbonHtml = product.ribbon 
                ? `<span class="aps-product-badge">${escHtml(product.ribbon)}</span>`
                : '-';

            const featuredHtml = product.featured 
                ? `<span class="aps-product-featured">★</span>`
                : '-';

            let priceHtml = `<span class="aps-product-price">$${parseFloat(product.price).toFixed(2)}</span>`;
            if (product.original_price > 0 && product.original_price > product.price) {
                priceHtml += `
                    <span class="aps-product-price-original">$${parseFloat(product.original_price).toFixed(2)}</span>
                    <span class="aps-product-price-discount">${product.discount_percentage}% OFF</span>
                `;
            }

            const statusClasses = {
                'publish': 'aps-product-status-published',
                'draft': 'aps-product-status-draft',
                'trash': 'aps-product-status-trash',
                'pending': 'aps-product-status-pending',
            };
            const statusClass = statusClasses[product.status] || '';
            const statusHtml = `<span class="aps-product-status ${statusClass}">${product.status.toUpperCase()}</span>`;

            const editUrl = `${window.location.href.split('&')[0]}&post_type=aps_product&page=add-product&id=${product.id}`;

            return `
                <tr id="product-${product.id}" class="iedit" data-id="${product.id}">
                    <th class="check-column" scope="row">
                        <input type="checkbox" name="post[]" value="${product.id}">
                    </th>
                    <td class="logo-column">${logoHtml}</td>
                    <td class="product-column">
                        <strong class="row-title"><a href="${editUrl}">${escHtml(product.title)}</a></strong>
                        <div class="row-actions">
                            <span class="inline">
                                <a href="${editUrl}" class="editinline">Edit</a> |
                            </span>
                            <span class="trash">
                                <a href="#" onclick="APSTableUI.deleteProduct(${product.id}); return false;" class="submitdelete">Trash</a>
                            </span>
                        </div>
                    </td>
                    <td class="category-column">${categoriesHtml}</td>
                    <td class="tags-column">${tagsHtml}</td>
                    <td class="ribbon-column">${ribbonHtml}</td>
                    <td class="featured-column">${featuredHtml}</td>
                    <td class="price-column">${priceHtml}</td>
                    <td class="status-column">${statusHtml}</td>
                </tr>
            `;
        },

        /**
         * Bind row-specific events
         */
        bindRowEvents: function() {
            // Status badge click for quick toggle
            $('.aps-product-status').on('click', function() {
                const $row = $(this).closest('tr');
                const productId = $row.data('id');
                const currentStatus = $(this).text().toLowerCase();
                
                // Toggle between published and draft
                const newStatus = currentStatus === 'published' ? 'draft' : 'publish';
                APSTableUI.updateProductStatus(productId, newStatus);
            });
        },

        /**
         * Update product status via AJAX
         */
        updateProductStatus: function(productId, newStatus) {
            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_update_status',
                    nonce: apsProductTableUI.nonce,
                    product_id: productId,
                    status: newStatus,
                },
                success: function(response) {
                    if (response.success) {
                        APSTableUI.updateStatusBadge(productId, newStatus);
                        APSTableUI.updateStatusCount();
                    } else {
                        alert('Error: ' + (response.data.message || 'Failed to update status'));
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Update status badge in table
         */
        updateStatusBadge: function(productId, newStatus) {
            const $badge = $(`#product-${productId} .aps-product-status`);
            
            if ($badge.length) {
                $badge.fadeOut(150, function() {
                    const statusClasses = {
                        'publish': 'aps-product-status-published',
                        'draft': 'aps-product-status-draft',
                        'trash': 'aps-product-status-trash',
                        'pending': 'aps-product-status-pending',
                    };
                    
                    $(this).removeClass('aps-product-status-published aps-product-status-draft aps-product-status-trash aps-product-status-pending')
                           .addClass(statusClasses[newStatus])
                           .text(newStatus.toUpperCase())
                           .fadeIn(150);
                });
            }
        },

        /**
         * Update status count
         */
        updateStatusCount: function() {
            const newStatus = this.filterState.status === 'all' ? 'all' : this.filterState.status;
            const $countItem = $(`.aps-count-${newStatus} .aps-count-number`);
            
            if ($countItem.length) {
                const currentCount = parseInt($countItem.text());
                $countItem.text(currentCount + 1);
            }
        },

        /**
         * Highlight search terms
         */
        highlightSearchTerms: function() {
            const searchTerm = this.filterState.search.trim();
            
            if (searchTerm.length < 2) {
                // Remove all highlights
                $('.row-title mark').contents().unwrap();
                return;
            }

            // Remove old highlights
            $('.row-title mark').contents().unwrap();

            // Add new highlights
            $('.row-title a').each(function() {
                const $element = $(this);
                const text = $element.text();
                const regex = new RegExp(`(${APSTableUI.escapeRegex(searchTerm)})`, 'gi');
                
                if (regex.test(text)) {
                    const highlighted = text.replace(regex, '<mark>$1</mark>');
                    $element.html(highlighted);
                }
            });
        },

        /**
         * Escape regex special characters
         */
        escapeRegex: function(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        },

        /**
         * Update pagination
         */
        updatePagination: function(data) {
            // Update pagination info
            $('.displaying-num').text(`Showing ${data.current_page} of ${data.pages} pages`);
            
            // Disable/enable pagination buttons
            if (data.current_page >= data.pages) {
                $('.tablenav-pages .next-page').addClass('disabled');
            } else {
                $('.tablenav-pages .next-page').removeClass('disabled');
            }
            
            if (data.current_page <= 1) {
                $('.tablenav-pages .prev-page').addClass('disabled');
            } else {
                $('.tablenav-pages .prev-page').removeClass('disabled');
            }
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

            // AJAX link check
            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_check_links',
                    nonce: apsProductTableUI.nonce,
                },
                success: function(response) {
                    if (response.success) {
                        alert(`${response.data.message}\n\n✓ Valid: ${response.data.valid_count}\n✗ Invalid: ${response.data.invalid_count}`);
                    } else {
                        alert('Error: ' + (response.data.message || 'Failed to check links'));
                    }
                },
                error: function() {
                    alert('An error occurred while checking links. Please try again.');
                },
                complete: function() {
                    // Reset button
                    $btn.prop('disabled', false)
                       .html(originalText);
                }
            });
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

            // Additional confirmation for destructive actions
            if (action === 'delete' || action === 'set_out_of_stock') {
                const actionText = $('#aps_bulk_action option:selected').text();
                if (!confirm(`Warning: This action will ${actionText} for ${selectedProducts.length} product(s). Continue?`)) {
                    return;
                }
            }

            // Send AJAX request
            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_bulk_action',
                    nonce: apsProductTableUI.nonce,
                    bulk_action: action,
                    product_ids: selectedProducts,
                },
                beforeSend: function() {
                    APSTableUI.showLoading();
                },
                success: function(response) {
                    APSTableUI.hideLoading();
                    
                    if (response.success) {
                        alert(response.data.message);
                        // Reload table to see changes
                        APSTableUI.filterProducts();
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
         * Delete product
         */
        deleteProduct: function(productId) {
            if (!confirm('Are you sure you want to move this product to trash?')) {
                return;
            }

            $.ajax({
                url: apsProductTableUI.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'aps_bulk_action',
                    nonce: apsProductTableUI.nonce,
                    bulk_action: 'delete',
                    product_ids: [productId],
                },
                success: function(response) {
                    if (response.success) {
                        // Animate row removal
                        $(`#product-${productId}`).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
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
        },

        /**
         * Show table loading state
         */
        showTableLoading: function() {
            $('#the-list').addClass('loading');
        },

        /**
         * Hide table loading state
         */
        hideTableLoading: function() {
            $('#the-list').removeClass('loading');
        },
    };

    // Utility functions
    function escHtml(text) {
        return text.replace(/&/g, '&')
                  .replace(/</g, '<')
                  .replace(/>/g, '>')
                  .replace(/"/g, '"')
                  .replace(/'/g, '&#039;');
    }

    function escAttr(text) {
        return text.replace(/&/g, '&')
                  .replace(/"/g, '"')
                  .replace(/'/g, '&#039;')
                  .replace(/</g, '<')
                  .replace(/>/g, '>');
    }

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
        #the-list.loading {
            opacity: 0.5;
            pointer-events: none;
        }
        #the-list.loading tr {
            animation: pulse 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        mark {
            background-color: #fff59d;
            color: #1d2327;
            padding: 2px 4px;
            border-radius: 2px;
        }
    </style>
`;

// Inject styles
$(apsLoadingStyles).appendTo('head');
