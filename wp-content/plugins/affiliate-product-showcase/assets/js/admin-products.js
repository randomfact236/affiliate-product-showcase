/**
 * Products Page JavaScript
 *
 * Handles products listing page functionality including filtering,
 * bulk actions, quick edit, and toast notifications.
 *
 * @package Affiliate_Product_Showcase
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * State management
     */
    const state = {
        products: [],
        filteredProducts: [],
        currentFilter: 'all',
        selectedProducts: [],
        searchTerm: '',
        categoryFilter: 'all',
        statusFilter: 'all'
    };

    /**
     * DOM Elements
     */
    const elements = {
        table: document.querySelector('.wp-list-table'),
        form: document.getElementById('aps-products-form'),
        bulkActionSelectors: [
            document.getElementById('bulk-action-selector-top'),
            document.getElementById('bulk-action-selector-bottom')
        ],
        applyButtons: [
            document.getElementById('doaction'),
            document.getElementById('doaction2')
        ],
        categoryFilter: document.getElementById('category-filter-top'),
        statusFilter: document.getElementById('status-filter-top'),
        searchInput: document.getElementById('post-search-input'),
        searchSubmit: document.getElementById('search-submit'),
        filterSubmit: document.getElementById('filter-submit'),
        navTabs: document.querySelectorAll('.aps-nav-tabs .nav-tab'),
        toastContainer: document.getElementById('aps-toast-container'),
        quickEditModal: document.getElementById('aps-quick-edit-modal'),
        quickEditForm: document.getElementById('aps-quick-edit-form'),
        quickEditSave: document.querySelector('.aps-modal-save'),
        quickEditCancel: document.querySelector('.aps-modal-cancel'),
        quickEditClose: document.querySelector('.aps-modal-close'),
        quickEditOverlay: document.querySelector('.aps-modal-overlay')
    };

    /**
     * Initialize functionality
     */
    function init() {
        setupEventListeners();
        initializeData();
        addDataAttributes();
    }

    /**
     * Set up event listeners
     */
    function setupEventListeners() {
        // Tab navigation
        elements.navTabs.forEach(tab => {
            tab.addEventListener('click', handleTabClick);
        });

        // Filter dropdowns
        elements.categoryFilter?.addEventListener('change', handleFilterChange);
        elements.statusFilter?.addEventListener('change', handleFilterChange);

        // Search input
        elements.searchInput?.addEventListener('keyup', debounce(handleSearch, 300));
        elements.searchSubmit?.addEventListener('click', handleSearchSubmit);

        // Bulk actions
        elements.applyButtons.forEach(button => {
            button?.addEventListener('click', handleBulkAction);
        });

        // Select all checkboxes
        const selectAllCheckboxes = document.querySelectorAll('#cb-select-all-1, #cb-select-all-2');
        selectAllCheckboxes.forEach(checkbox => {
            checkbox?.addEventListener('change', handleSelectAll);
        });

        // Individual checkboxes
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleCheckboxChange);
        });

        // Quick edit links
        document.querySelectorAll('.aps-inline-edit').forEach(link => {
            link.addEventListener('click', handleQuickEditOpen);
        });

        // Trash links
        document.querySelectorAll('.aps-trash-product').forEach(link => {
            link.addEventListener('click', handleTrash);
        });

        // Modal controls
        elements.quickEditClose?.addEventListener('click', closeModal);
        elements.quickEditOverlay?.addEventListener('click', closeModal);
        elements.quickEditCancel?.addEventListener('click', closeModal);
        elements.quickEditSave?.addEventListener('click', handleQuickEditSave);

        // Escape key to close modal
        document.addEventListener('keydown', handleEscapeKey);

        // Filter submit
        elements.filterSubmit?.addEventListener('click', handleFilterSubmit);
    }

    /**
     * Initialize data
     */
    function initializeData() {
        // Get current URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        
        state.currentFilter = urlParams.get('status') || 'all';
        state.categoryFilter = urlParams.get('category') || 'all';
        state.statusFilter = urlParams.get('status_filter') || 'all';
        state.searchTerm = urlParams.get('s') || '';

        // Update filter dropdowns
        if (elements.categoryFilter) {
            elements.categoryFilter.value = state.categoryFilter;
        }
        if (elements.statusFilter) {
            elements.statusFilter.value = state.statusFilter;
        }
    }

    /**
     * Add data attributes to table cells
     * (For responsive design)
     */
    function addDataAttributes() {
        const columnHeaders = document.querySelectorAll('.wp-list-table thead th');
        const columnMap = {};
        
        columnHeaders.forEach((header, index) => {
            const columnName = header.className.replace('manage-column', '').trim().replace('column-', '');
            if (columnName) {
                columnMap[index] = columnName.charAt(0).toUpperCase() + columnName.slice(1);
            }
        });

        const tableRows = document.querySelectorAll('.wp-list-table tbody tr');
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (columnMap[index]) {
                    cell.setAttribute('data-colname', columnMap[index]);
                }
            });
        });
    }

    /**
     * Handle tab click
     */
    function handleTabClick(e) {
        e.preventDefault();
        const filter = e.target.getAttribute('data-filter');
        
        if (filter) {
            // Update URL without page reload
            const url = new URL(window.location.href);
            url.searchParams.set('status', filter);
            url.searchParams.delete('status_filter');
            window.location.href = url.toString();
        }
    }

    /**
     * Handle filter change
     */
    function handleFilterChange(e) {
        const filter = e.target.value;
        
        // Update URL
        const url = new URL(window.location.href);
        
        if (e.target.id === 'category-filter-top') {
            url.searchParams.set('category', filter);
        } else if (e.target.id === 'status-filter-top') {
            url.searchParams.set('status_filter', filter);
        }
        
        window.location.href = url.toString();
    }

    /**
     * Handle search input
     */
    function handleSearch(e) {
        state.searchTerm = e.target.value.trim();
        
        // Update URL after debounce
        if (e.key === 'Enter') {
            handleSearchSubmit(e);
        }
    }

    /**
     * Handle search submit
     */
    function handleSearchSubmit(e) {
        e.preventDefault();
        
        const url = new URL(window.location.href);
        
        if (state.searchTerm) {
            url.searchParams.set('s', state.searchTerm);
        } else {
            url.searchParams.delete('s');
        }
        
        window.location.href = url.toString();
    }

    /**
     * Handle filter submit
     */
    function handleFilterSubmit(e) {
        e.preventDefault();
        
        const url = new URL(window.location.href);
        
        if (elements.categoryFilter && elements.categoryFilter.value !== 'all') {
            url.searchParams.set('category', elements.categoryFilter.value);
        } else {
            url.searchParams.delete('category');
        }
        
        if (elements.statusFilter && elements.statusFilter.value !== 'all') {
            url.searchParams.set('status_filter', elements.statusFilter.value);
        } else {
            url.searchParams.delete('status_filter');
        }
        
        window.location.href = url.toString();
    }

    /**
     * Handle select all checkboxes
     */
    function handleSelectAll(e) {
        const isChecked = e.target.checked;
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        
        updateSelectedProducts();
    }

    /**
     * Handle checkbox change
     */
    function handleCheckboxChange() {
        updateSelectedProducts();
        
        // Update select all checkbox state
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        
        const selectAllCheckboxes = document.querySelectorAll('#cb-select-all-1, #cb-select-all-2');
        selectAllCheckboxes.forEach(cb => {
            cb.checked = allChecked;
            cb.indeterminate = anyChecked && !allChecked;
        });
    }

    /**
     * Update selected products array
     */
    function updateSelectedProducts() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        state.selectedProducts = Array.from(checkboxes).map(cb => parseInt(cb.value, 10));
    }

    /**
     * Handle bulk action
     */
    function handleBulkAction(e) {
        e.preventDefault();
        
        // Find the corresponding action selector
        const buttonId = e.target.id;
        let actionSelectorId;
        
        if (buttonId === 'doaction') {
            actionSelectorId = 'bulk-action-selector-top';
        } else if (buttonId === 'doaction2') {
            actionSelectorId = 'bulk-action-selector-bottom';
        } else {
            return;
        }
        
        const actionSelector = document.getElementById(actionSelectorId);
        const action = actionSelector?.value;
        
        if (action === '-1') {
            showToast(apsProductsData.strings.bulkActionRequired || 'Please select an action.', 'error');
            return;
        }
        
        if (state.selectedProducts.length === 0) {
            showToast(apsProductsData.strings.noItemsSelected || 'Please select at least one product.', 'error');
            return;
        }
        
        // Handle bulk trash action
        if (action === 'trash') {
            if (confirm(apsProductsData.strings.bulkDeleteConfirm || `Are you sure you want to move ${state.selectedProducts.length} products to trash?`)) {
                executeBulkTrash();
            }
        }
    }

    /**
     * Execute bulk trash action
     */
    function executeBulkTrash() {
        const formData = new FormData();
        formData.append('action', 'aps_bulk_trash_products');
        formData.append('nonce', apsProductsData.nonce);
        formData.append('product_ids', JSON.stringify(state.selectedProducts));
        
        fetch(apsProductsData.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = apsProductsData.strings.bulkDeleteSuccess?.replace('%d', state.selectedProducts.length) || 
                              `${state.selectedProducts.length} products moved to trash.`;
                showToast(message, 'success');
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.data?.message || 'Failed to move products to trash.', 'error');
            }
        })
        .catch(error => {
            console.error('Bulk trash error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    /**
     * Handle trash single product
     */
    function handleTrash(e) {
        e.preventDefault();
        const productId = parseInt(e.target.getAttribute('data-id'), 10);
        
        if (confirm(apsProductsData.strings.deleteConfirm || 'Are you sure you want to move this product to trash?')) {
            executeTrash(productId);
        }
    }

    /**
     * Execute trash action
     */
    function executeTrash(productId) {
        const formData = new FormData();
        formData.append('action', 'aps_trash_product');
        formData.append('nonce', apsProductsData.nonce);
        formData.append('product_id', productId);
        
        fetch(apsProductsData.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(apsProductsData.strings.deleteSuccess || 'Product moved to trash.', 'success');
                
                // Remove row from DOM
                const row = document.querySelector(`.row-checkbox[value="${productId}"]`)?.closest('tr');
                if (row) {
                    row.remove();
                }
            } else {
                showToast(data.data?.message || 'Failed to move product to trash.', 'error');
            }
        })
        .catch(error => {
            console.error('Trash error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    /**
     * Handle quick edit open
     */
    function handleQuickEditOpen(e) {
        e.preventDefault();
        const productId = parseInt(e.target.getAttribute('data-id'), 10);
        
        // Get product data from row
        const row = e.target.closest('tr');
        const title = row.querySelector('.row-title')?.textContent || '';
        const price = row.querySelector('.aps-price')?.textContent.replace(/[^0-9.]/g, '') || '0';
        const status = row.querySelector('.aps-product-status')?.classList
            .toString()
            .replace('aps-product-status ', '')
            .replace('aps-product-status-', '') || 'published';
        const ribbon = row.querySelector('.aps-ribbon-badge')?.textContent || '';
        const featured = !!row.querySelector('.aps-featured-star');
        
        // Populate form
        document.getElementById('quick-edit-product-id').value = productId;
        document.getElementById('quick-edit-title').value = title;
        document.getElementById('quick-edit-price').value = price;
        document.getElementById('quick-edit-status').value = status;
        document.getElementById('quick-edit-ribbon').value = ribbon;
        document.getElementById('quick-edit-featured').checked = featured;
        
        // Show modal
        elements.quickEditModal.style.display = 'flex';
    }

    /**
     * Handle quick edit save
     */
    function handleQuickEditSave() {
        const productId = parseInt(document.getElementById('quick-edit-product-id').value, 10);
        const title = document.getElementById('quick-edit-title').value.trim();
        const price = parseFloat(document.getElementById('quick-edit-price').value);
        const status = document.getElementById('quick-edit-status').value;
        const ribbon = document.getElementById('quick-edit-ribbon').value.trim();
        const featured = document.getElementById('quick-edit-featured').checked;
        
        // Validation
        if (!title) {
            showToast('Title is required.', 'error');
            return;
        }
        
        if (isNaN(price) || price < 0) {
            showToast('Please enter a valid price.', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'aps_quick_edit_product');
        formData.append('nonce', apsProductsData.nonce);
        formData.append('product_id', productId);
        formData.append('title', title);
        formData.append('price', price);
        formData.append('status', status);
        formData.append('ribbon', ribbon);
        formData.append('featured', featured ? '1' : '0');
        
        fetch(apsProductsData.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Product updated successfully.', 'success');
                closeModal();
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.data?.message || 'Failed to update product.', 'error');
            }
        })
        .catch(error => {
            console.error('Quick edit error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }

    /**
     * Close modal
     */
    function closeModal() {
        elements.quickEditModal.style.display = 'none';
        
        // Reset form
        elements.quickEditForm.reset();
    }

    /**
     * Handle escape key
     */
    function handleEscapeKey(e) {
        if (e.key === 'Escape' && elements.quickEditModal.style.display === 'flex') {
            closeModal();
        }
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        if (!elements.toastContainer) {
            return;
        }
        
        const toast = document.createElement('div');
        toast.className = `aps-toast aps-toast-${type}`;
        toast.innerHTML = `
            <div class="aps-toast-message">${escapeHtml(message)}</div>
        `;
        
        elements.toastContainer.appendChild(toast);
        
        // Auto dismiss after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease-out';
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
