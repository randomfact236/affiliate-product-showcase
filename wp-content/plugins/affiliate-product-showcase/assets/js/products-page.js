(function($) {
    'use strict';

    /**
     * Products Page JavaScript
     *
     * Handles AJAX functionality for products page including
     * search, filtering, sorting, and bulk actions.
     */

    const ProductsPage = {
        nonce: '',
        ajaxUrl: '',
        searchTimeout: null,
        isProcessing: false,

        /**
         * Initialize
         */
        init: function() {
            this.nonce = $('#aps_nonce').val() || '';
            this.ajaxUrl = $('#aps_ajax_url').val() || '';

            if (!this.nonce || !this.ajaxUrl) {
                console.error('APS: Nonce or AJAX URL not found');
                return;
            }

            this.bindEvents();
            console.log('APS Products Page initialized');
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Search with debounce
            $('#aps_search_products').on('keyup', $.proxy(this.onSearch, this));

            // Category filter
            $('#aps_category_filter').on('change', $.proxy(this.onCategoryChange, this));

            // Featured filter
            $('#aps_show_featured').on('change', $.proxy(this.onFeaturedChange, this));

            // Sort order
            $('#aps_sort_order').on('change', $.proxy(this.onSortChange, this));

            // Status counts
            $('.aps-count-item').on('click', $.proxy(this.onStatusClick, this));
        },

        /**
         * Handle search input
         */
        onSearch: function(e) {
            const searchTerm = $(e.target).val().trim();

            // Clear previous timeout
            clearTimeout(this.searchTimeout);

            // Debounce search (300ms)
            this.searchTimeout = setTimeout(() => {
                if (searchTerm.length === 0 || searchTerm.length >= 2) {
                    this.applyFilters();
                }
            }, 300);
        },

        /**
         * Handle category change
         */
        onCategoryChange: function(e) {
            const categoryId = $(e.target).val();
            console.log('APS: Category changed to', categoryId);
            this.applyFilters();
        },

        /**
         * Handle featured filter change
         */
        onFeaturedChange: function(e) {
            const showFeatured = $(e.target).is(':checked');
            console.log('APS: Featured filter changed to', showFeatured);
            this.applyFilters();
        },

        /**
         * Handle sort order change
         */
        onSortChange: function(e) {
            const sortOrder = $(e.target).val();
            console.log('APS: Sort order changed to', sortOrder);
            this.applyFilters();
        },

        /**
         * Handle status count click
         */
        onStatusClick: function(e) {
            e.preventDefault();
            
            const $target = $(e.currentTarget);
            const status = $target.data('status');

            // Update active state
            $('.aps-count-item').removeClass('active');
            $target.addClass('active');

            console.log('APS: Status changed to', status);

            // Navigate to appropriate page
            if (status === 'all') {
                window.location.href = 'edit.php?post_type=aps_product';
            } else {
                window.location.href = `edit.php?post_type=aps_product&post_status=${status}`;
            }
        },

        /**
         * Apply filters
         */
        applyFilters: function() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;
            this.showLoading();

            const filters = {
                action: 'aps_filter_products',
                nonce: this.nonce,
                search: $('#aps_search_products').val().trim(),
                category: $('#aps_category_filter').val(),
                featured: $('#aps_show_featured').is(':checked') ? 1 : 0,
                sort_order: $('#aps_sort_order').val()
            };

            $.ajax({
                url: this.ajaxUrl,
                type: 'POST',
                data: filters,
                dataType: 'json',
                success: $.proxy(this.onFilterSuccess, this),
                error: $.proxy(this.onFilterError, this),
                complete: $.proxy(() => {
                    this.isProcessing = false;
                    this.hideLoading();
                }, this)
            });
        },

        /**
         * Handle filter success
         */
        onFilterSuccess: function(response) {
            if (!response.success) {
                console.error('APS: Filter failed', response.data);
                return;
            }

            console.log('APS: Filter successful', response.data);

            // Update table
            this.updateTable(response.data.products);

            // Update counts
            this.updateCounts(response.data.counts);

            // Show success message
            this.showMessage('Products filtered successfully', 'success');
        },

        /**
         * Handle filter error
         */
        onFilterError: function(xhr, status, error) {
            console.error('APS: Filter error', status, error);
            this.showMessage('Failed to filter products. Please try again.', 'error');
        },

        /**
         * Update table with new products
         */
        updateTable: function(products) {
            const $tableBody = $('.wp-list-table tbody');
            
            // Clear existing rows
            $tableBody.empty();

            if (products.length === 0) {
                $tableBody.append('<tr><td colspan="10" style="text-align:center; padding: 20px;">No products found matching your filters.</td></tr>');
                return;
            }

            // Add new rows
            products.forEach(product => {
                const row = this.createProductRow(product);
                $tableBody.append(row);
            });
        },

        /**
         * Create product row
         */
        createProductRow: function(product) {
            const featured = product.featured ? '★' : '-';
            const statusClass = `aps-product-status-${product.status}`;
            const statusLabel = product.status.toUpperCase();

            return `
                <tr id="product-${product.id}">
                    <th class="check-column">
                        <input type="checkbox" name="product[]" value="${product.id}">
                    </th>
                    <td class="logo-column">
                        ${product.logo 
                            ? `<img src="${product.logo}" alt="${product.title}" class="aps-product-logo" width="40" height="40" />`
                            : `<div class="aps-product-logo-placeholder">${product.title.charAt(0).toUpperCase()}</div>`
                        }
                    </td>
                    <td class="title-column column-title">
                        <strong>
                            <a href="post.php?post_type=aps_product&action=edit&post=${product.id}">
                                ${product.title}
                            </a>
                        </strong>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="post.php?post_type=aps_product&action=edit&post=${product.id}">Edit</a>
                            </span>
                        </div>
                    </td>
                    <td class="categories-column">${this.renderTerms(product.categories)}</td>
                    <td class="tags-column">${this.renderTerms(product.tags)}</td>
                    <td class="ribbon-column">
                        ${product.ribbon ? `<span class="aps-product-badge">${product.ribbon}</span>` : '-'}
                    </td>
                    <td class="featured-column">${featured}</td>
                    <td class="price-column">
                        <span class="aps-product-price">$${product.price.toFixed(2)}</span>
                        ${product.original_price > product.price 
                            ? `<span class="aps-product-price-original">$${product.original_price.toFixed(2)}</span>`
                            : ''
                        }
                    </td>
                    <td class="status-column">
                        <span class="aps-product-status ${statusClass}">${statusLabel}</span>
                    </td>
                </tr>
            `;
        },

        /**
         * Render terms (categories/tags)
         */
        renderTerms: function(terms) {
            if (!terms || terms.length === 0) {
                return '-';
            }

            return terms.map(term => 
                `<span class="aps-product-term">${term}</span>`
            ).join(' ');
        },

        /**
         * Update counts
         */
        updateCounts: function(counts) {
            $('.aps-count-item[data-status="all"] .aps-count-number').text(counts.all);
            $('.aps-count-item[data-status="publish"] .aps-count-number').text(counts.publish);
            $('.aps-count-item[data-status="draft"] .aps-count-number').text(counts.draft);
            $('.aps-count-item[data-status="trash"] .aps-count-number').text(counts.trash);
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $('.wp-list-table').addClass('aps-loading');
            if (!$('.aps-loading-overlay').length) {
                $('.wrap').append('<div class="aps-loading-overlay"><div class="spinner"></div></div>');
            }
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            $('.wp-list-table').removeClass('aps-loading');
            $('.aps-loading-overlay').remove();
        },

        /**
         * Show message
         */
        showMessage: function(message, type) {
            const className = `notice notice-${type} is-dismissible`;
            const notice = $(`<div class="${className}"><p>${message}</p></div>`);
            
            $('.wrap').prepend(notice);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 3000);
        }
    };

    /**
     * Global functions (for onclick handlers)
     */
    window.apsBulkUploadProducts = function() {
        console.log('APS: Bulk upload clicked');
        // Navigate to bulk upload page
        window.location.href = 'admin.php?page=affiliate-manager-bulk-upload';
    };

    window.apsCheckProductLinks = function() {
        console.log('APS: Check links clicked');
        
        const nonce = $('#aps_nonce').val();
        const ajaxUrl = $('#aps_ajax_url').val();
        
        if (!nonce || !ajaxUrl) {
            alert('Unable to perform link check. Please refresh the page.');
            return;
        }

        // Get selected products
        const selectedProducts = $('input[name="product[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedProducts.length === 0) {
            alert('Please select at least one product to check links.');
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'aps_check_product_links',
                nonce: nonce,
                products: selectedProducts
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(`Link check completed! Checked ${response.data.checked} products. Found ${response.data.broken} broken links.`);
                } else {
                    alert('Link check failed: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('APS: Link check error', error);
                alert('Link check failed. Please try again.');
            }
        });
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        ProductsPage.init();
    });

})(jQuery);
