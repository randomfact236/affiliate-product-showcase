/**
 * Products Page JavaScript
 * 
 * Handles products listing page functionality including filtering,
 * bulk actions, quick edit, and toast notifications.
 * 
 * Uses APS_Utils for shared functionality.
 * 
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * @typedef {Object} ProductsConfig
     * @property {string} ajaxUrl - WordPress AJAX URL
     * @property {string} nonce - Security nonce
     * @property {ProductStrings} strings - Localized strings
     */

    /**
     * @typedef {Object} ProductStrings
     * @property {string} bulkActionRequired - Message for missing bulk action
     * @property {string} noItemsSelected - Message for no items selected
     * @property {string} bulkDeleteConfirm - Bulk delete confirmation
     * @property {string} bulkDeleteSuccess - Bulk delete success message
     * @property {string} deleteConfirm - Delete confirmation
     * @property {string} deleteSuccess - Delete success message
     */

    /**
     * @typedef {Object} ProductState
     * @property {number[]} selectedProducts - Selected product IDs
     * @property {string} searchTerm - Current search term
     * @property {string} categoryFilter - Current category filter
     * @property {string} statusFilter - Current status filter
     * @property {string} currentFilter - Current tab filter
     */

    /**
     * @typedef {Object} DOMElements
     * @property {HTMLElement|null} table - Products table
     * @property {HTMLElement|null} form - Products form
     * @property {HTMLElement[]} bulkActionSelectors - Bulk action dropdowns
     * @property {HTMLElement[]} applyButtons - Apply bulk action buttons
     * @property {HTMLElement|null} categoryFilter - Category filter dropdown
     * @property {HTMLElement|null} statusFilter - Status filter dropdown
     * @property {HTMLElement|null} searchInput - Search input field
     * @property {HTMLElement|null} searchSubmit - Search submit button
     * @property {HTMLElement|null} filterSubmit - Filter submit button
     * @property {NodeListOf<Element>} navTabs - Navigation tabs
     * @property {HTMLElement|null} toastContainer - Toast notification container
     * @property {HTMLElement|null} quickEditModal - Quick edit modal
     * @property {HTMLElement|null} quickEditForm - Quick edit form
     * @property {HTMLElement|null} quickEditSave - Quick edit save button
     * @property {HTMLElement|null} quickEditCancel - Quick edit cancel button
     * @property {HTMLElement|null} quickEditClose - Quick edit close button
     * @property {HTMLElement|null} quickEditOverlay - Quick edit overlay
     */

    // Import shared utilities with fallbacks
    const { debounce, escapeHtml } = window.APS_Utils || {
        debounce: function (fn, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        },
        escapeHtml: function (str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    };

    // Localize config with existence check
    const config = typeof apsProductsData !== 'undefined' ? apsProductsData : {
        ajaxUrl: window.ajaxurl || '/wp-admin/admin-ajax.php',
        nonce: '',
        strings: {
            bulkActionRequired: 'Please select a bulk action.',
            noItemsSelected: 'Please select at least one item.',
            bulkDeleteConfirm: 'Are you sure you want to delete %d products?',
            bulkDeleteSuccess: '%d products moved to trash.',
            deleteConfirm: 'Are you sure you want to delete this product?',
            deleteSuccess: 'Product deleted successfully.'
        }
    };

    /**
     * Application state
     * @type {ProductState}
     */
    const state = {
        selectedProducts: [],
        searchTerm: '',
        categoryFilter: 'all',
        statusFilter: 'all',
        currentFilter: 'all'
    };

    /**
     * Cached DOM elements
     * @type {DOMElements}
     */
    let elements = {};

    /**
     * Get DOM elements with caching
     * @returns {DOMElements}
     */
    function getElements() {
        return {
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
    }

    /**
     * Initialize the products page
     */
    function init() {
        elements = getElements();
        setupEventListeners();
        initializeData();
        addDataAttributes();
    }

    /**
     * Setup all event listeners
     */
    function setupEventListeners() {
        elements.navTabs.forEach(tab => tab.addEventListener('click', handleTabClick));

        elements.categoryFilter?.addEventListener('change', handleFilterChange);
        elements.statusFilter?.addEventListener('change', handleFilterChange);

        elements.searchInput?.addEventListener('keyup', debounce(handleSearch, 300));
        elements.searchSubmit?.addEventListener('click', handleSearchSubmit);

        elements.applyButtons.forEach(btn => btn?.addEventListener('click', handleBulkAction));

        document.querySelectorAll('#cb-select-all-1, #cb-select-all-2')
            .forEach(cb => cb?.addEventListener('change', handleSelectAll));

        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', handleCheckboxChange);
        });

        document.querySelectorAll('.aps-inline-edit').forEach(link => {
            link.addEventListener('click', handleQuickEditOpen);
        });

        document.querySelectorAll('.aps-trash-product').forEach(link => {
            link.addEventListener('click', handleTrash);
        });

        // Modal events
        elements.quickEditClose?.addEventListener('click', closeModal);
        elements.quickEditOverlay?.addEventListener('click', closeModal);
        elements.quickEditCancel?.addEventListener('click', closeModal);
        elements.quickEditSave?.addEventListener('click', handleQuickEditSave);
        document.addEventListener('keydown', handleEscapeKey);

        elements.filterSubmit?.addEventListener('click', handleFilterSubmit);
    }

    /**
     * Initialize state from URL parameters
     */
    function initializeData() {
        const urlParams = new URLSearchParams(window.location.search);
        state.currentFilter = urlParams.get('status') || 'all';
        state.categoryFilter = urlParams.get('category') || 'all';
        state.statusFilter = urlParams.get('status_filter') || 'all';
        state.searchTerm = urlParams.get('s') || '';

        if (elements.categoryFilter) elements.categoryFilter.value = state.categoryFilter;
        if (elements.statusFilter) elements.statusFilter.value = state.statusFilter;
    }

    /**
     * Add responsive data attributes to table cells
     */
    function addDataAttributes() {
        const headers = document.querySelectorAll('.wp-list-table thead th');
        const colMap = {};

        headers.forEach((header, idx) => {
            const name = header.className.replace(/manage-column|column-/g, '').trim();
            if (name) colMap[idx] = name.charAt(0).toUpperCase() + name.slice(1);
        });

        document.querySelectorAll('.wp-list-table tbody tr').forEach(row => {
            row.querySelectorAll('td').forEach((cell, idx) => {
                if (colMap[idx]) cell.setAttribute('data-colname', colMap[idx]);
            });
        });
    }

    /**
     * Handle navigation tab click
     * @param {Event} e - Click event
     */
    function handleTabClick(e) {
        e.preventDefault();
        const filter = e.target.getAttribute('data-filter');
        if (filter) updateUrl({ status: filter, status_filter: null });
    }

    /**
     * Handle filter dropdown change
     * @param {Event} e - Change event
     */
    function handleFilterChange(e) {
        const filter = e.target.value;
        const key = e.target.id === 'category-filter-top' ? 'category' : 'status_filter';
        updateUrl({ [key]: filter });
    }

    /**
     * Handle search input
     * @param {KeyboardEvent} e - Keyup event
     */
    function handleSearch(e) {
        state.searchTerm = e.target.value.trim();
        if (e.key === 'Enter') handleSearchSubmit(e);
    }

    /**
     * Handle search form submit
     * @param {Event} e - Submit event
     */
    function handleSearchSubmit(e) {
        e.preventDefault();
        updateUrl({ s: state.searchTerm || null });
    }

    /**
     * Handle filter form submit
     * @param {Event} e - Submit event
     */
    function handleFilterSubmit(e) {
        e.preventDefault();
        updateUrl({
            category: elements.categoryFilter?.value !== 'all' ? elements.categoryFilter.value : null,
            status_filter: elements.statusFilter?.value !== 'all' ? elements.statusFilter.value : null
        });
    }

    /**
     * Update URL with new parameters
     * @param {Object.<string, string|null>} params - Parameters to update
     */
    function updateUrl(params) {
        const url = new URL(window.location.href);
        Object.entries(params).forEach(([key, val]) => {
            if (val === null) url.searchParams.delete(key);
            else url.searchParams.set(key, val);
        });
        window.location.href = url.toString();
    }

    /**
     * Handle select all checkbox
     * @param {Event} e - Change event
     */
    function handleSelectAll(e) {
        const isChecked = e.target.checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = isChecked);
        updateSelectedProducts();
    }

    /**
     * Handle individual checkbox change
     */
    function handleCheckboxChange() {
        updateSelectedProducts();
        const checkboxes = Array.from(document.querySelectorAll('.row-checkbox'));
        const allChecked = checkboxes.every(cb => cb.checked);
        const anyChecked = checkboxes.some(cb => cb.checked);

        document.querySelectorAll('#cb-select-all-1, #cb-select-all-2').forEach(cb => {
            cb.checked = allChecked;
            cb.indeterminate = anyChecked && !allChecked;
        });
    }

    /**
     * Update selected products array
     */
    function updateSelectedProducts() {
        state.selectedProducts = Array.from(document.querySelectorAll('.row-checkbox:checked'))
            .map(cb => parseInt(cb.value, 10));
    }

    /**
     * Handle bulk action
     * @param {Event} e - Click event
     */
    function handleBulkAction(e) {
        e.preventDefault();
        const selectorId = e.target.id === 'doaction' ? 'bulk-action-selector-top' : 'bulk-action-selector-bottom';
        const action = document.getElementById(selectorId)?.value;

        if (!action || action === '-1') return showToast(config.strings.bulkActionRequired, 'error');
        if (!state.selectedProducts.length) return showToast(config.strings.noItemsSelected, 'error');

        if (action === 'trash') {
            if (confirm(config.strings.bulkDeleteConfirm.replace('%d', state.selectedProducts.length))) {
                performBulkAction('aps_bulk_trash_products', state.selectedProducts)
                    .then(count => {
                        showToast(config.strings.bulkDeleteSuccess.replace('%d', count), 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    });
            }
        }
    }

    /**
     * Perform bulk action via AJAX
     * @param {string} action - AJAX action name
     * @param {number[]} productIds - Product IDs
     * @returns {Promise<number>} Number of affected products
     */
    function performBulkAction(action, productIds) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', config.nonce);
        formData.append('product_ids', JSON.stringify(productIds));

        return fetch(config.ajaxUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) return state.selectedProducts.length;
                throw new Error(data.data?.message || 'Failed');
            })
            .catch(err => {
                showToast(err.message, 'error');
                return 0;
            });
    }

    /**
     * Handle single product trash
     * @param {Event} e - Click event
     */
    function handleTrash(e) {
        e.preventDefault();
        const id = parseInt(e.target.getAttribute('data-id'), 10);
        if (confirm(config.strings.deleteConfirm)) {
            const formData = new FormData();
            formData.append('action', 'aps_trash_product');
            formData.append('nonce', config.nonce);
            formData.append('product_id', id);

            fetch(config.ajaxUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(config.strings.deleteSuccess, 'success');
                        document.querySelector(`.row-checkbox[value="${id}"]`)?.closest('tr')?.remove();
                    } else {
                        showToast(data.data?.message || 'Failed', 'error');
                    }
                })
                .catch(() => showToast('Error occurred', 'error'));
        }
    }

    /**
     * Handle quick edit modal open
     * @param {Event} e - Click event
     */
    function handleQuickEditOpen(e) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        const row = e.target.closest('tr');

        // Populate form
        const productIdInput = document.getElementById('quick-edit-product-id');
        const titleInput = document.getElementById('quick-edit-title');
        const priceInput = document.getElementById('quick-edit-price');

        if (productIdInput) productIdInput.value = id;
        if (titleInput) titleInput.value = row.querySelector('.row-title')?.textContent || '';
        if (priceInput) priceInput.value = row.querySelector('.aps-price')?.textContent.replace(/[^0-9.]/g, '') || 0;

        if (elements.quickEditModal) {
            elements.quickEditModal.style.display = 'flex';
        }
    }

    /**
     * Handle quick edit save
     */
    function handleQuickEditSave() {
        const id = document.getElementById('quick-edit-product-id')?.value;
        const title = document.getElementById('quick-edit-title')?.value.trim();
        const price = parseFloat(document.getElementById('quick-edit-price')?.value) || 0;

        if (!title) return showToast('Title is required', 'error');

        const formData = new FormData();
        formData.append('action', 'aps_quick_edit_product');
        formData.append('nonce', config.nonce);
        formData.append('product_id', id);
        formData.append('title', title);
        formData.append('price', price);

        fetch(config.ajaxUrl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Saved', 'success');
                    closeModal();
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.data?.message || 'Failed', 'error');
                }
            });
    }

    /**
     * Close quick edit modal
     */
    function closeModal() {
        if (elements.quickEditModal) elements.quickEditModal.style.display = 'none';
        elements.quickEditForm?.reset();
    }

    /**
     * Handle escape key to close modal
     * @param {KeyboardEvent} e - Keydown event
     */
    function handleEscapeKey(e) {
        if (e.key === 'Escape') closeModal();
    }

    /**
     * Show toast notification
     * @param {string} message - Toast message
     * @param {'success'|'error'} [type='success'] - Toast type
     */
    function showToast(message, type = 'success') {
        const container = elements.toastContainer;
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `aps-toast aps-toast-${type}`;

        // Safe DOM construction
        const messageDiv = document.createElement('div');
        messageDiv.className = 'aps-toast-message';
        messageDiv.textContent = message;
        toast.appendChild(messageDiv);

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Initialize on DOM ready
    $(document).ready(init);

})(jQuery);
