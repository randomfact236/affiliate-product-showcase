/**
 * Products Table Inline Editing
 *
 * Enables inline editing for product table columns:
 * - Category: Dropdown with existing categories + Add New
 * - Tags: Multi-select dropdown with existing tags + Add New
 * - Ribbon: Dropdown with available ribbons + None
 * - Price: Input with currency validation
 * - Status: Dropdown (Publish/Draft)
 *
 * Features:
 * - Auto-save on blur
 * - Loading states
 * - Success/error indicators
 * - Auto-discount calculation for price
 * - Bulk status actions
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

(function() {
    'use strict';

    // Safety check: Exit if localized data not available
    if (typeof apsInlineEditData === 'undefined') {
        console.error('[APS Inline Edit] apsInlineEditData not defined. Script not loaded properly.');
        return;
    }

    // Configuration
    const config = {
        apiBase: apsInlineEditData.restUrl,
        nonce: apsInlineEditData.nonce,
        editableCells: ['category', 'tags', 'ribbon', 'price', 'status'],
        selectors: {
            table: '#the-list',
            row: 'tr',
            cell: 'td'
        }
    };

    // Cache for dropdown options
    const cache = {
        categories: null,
        tags: null,
        ribbons: null
    };

    // Currently editing cell
    let editingCell = null;
    let originalContent = null;
    let isSaving = false;
    
    // Flag to prevent duplicate listeners
    let listenersAttached = false;
    let retryCount = 0;
    const MAX_RETRIES = 10;

    /**
     * Initialize inline editing
     */
    function init() {
        const table = document.querySelector(config.selectors.table);
        
        if (!table) {
            console.error('[APS Inline Edit] Table not found:', config.selectors.table);
            
            // Retry after short delay (handles async rendering)
            if (retryCount < MAX_RETRIES) {
                retryCount++;
                console.log(`[APS Inline Edit] Retrying... (${retryCount}/${MAX_RETRIES})`);
                setTimeout(init, 100);
            } else {
                console.error('[APS Inline Edit] Max retries reached. Table not found.');
            }
            return;
        }
        
        console.log('[APS Inline Edit] Table found, initializing...');
        
        // Use MutationObserver to watch for table changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    console.log('[APS Inline Edit] Table updated, re-initializing...');
                    // Don't re-add listeners if already attached
                    if (!listenersAttached) {
                        addEventListeners();
                    }
                }
            });
        });
        
        observer.observe(table, {
            childList: true,
            subtree: true
        });
        
        addEventListeners();
        loadBulkActionHandlers();
        console.log('[APS Inline Edit] Initialized successfully');
    }

    /**
     * Add event listeners for inline editing
     */
    function addEventListeners() {
        // Prevent duplicate listeners
        if (listenersAttached) {
            console.log('[APS Inline Edit] Listeners already attached, skipping');
            return;
        }
        
        // Attach listener to table only (more efficient)
        const table = document.querySelector(config.selectors.table);
        if (!table) {
            console.error('[APS Inline Edit] Cannot attach listeners: table not found');
            return;
        }
        
        // Use event delegation on table
        table.addEventListener('click', handleCellClick);
        
        // Document-level listeners for outside clicks and keyboard
        document.addEventListener('click', handleOutsideClick);
        document.addEventListener('keydown', handleKeyDown);
        
        listenersAttached = true;
        console.log('[APS Inline Edit] Event listeners attached');
    }

    /**
     * Handle cell click - start editing
     */
    function handleCellClick(e) {
        const cell = e.target.closest(config.selectors.cell);
        if (!cell) return;

        const row = cell.closest(config.selectors.row);
        if (!row) return;

        const productId = row.querySelector('input[type="checkbox"]')?.value;
        if (!productId) return;

        // Check if this is an editable cell
        const cellType = getCellType(cell);
        
        // Enhanced debugging
        if (cell.classList.contains('column-status') || cell.classList.contains('column-category') || 
            cell.classList.contains('column-tags') || cell.classList.contains('column-price') || 
            cell.classList.contains('column-ribbon')) {
            console.log('[APS Inline Edit] Cell clicked:', {
                classes: cell.className,
                cellType: cellType,
                productId: productId,
                dataField: cell.dataset.field,
                childDataField: cell.querySelector('[data-field]')?.dataset?.field,
                isEditable: config.editableCells.includes(cellType)
            });
        }
        
        if (!cellType || !config.editableCells.includes(cellType)) {
            return;
        }

        // Don't start editing if clicking inside existing editor
        if (cell.querySelector('.aps-inline-editor')) {
            return;
        }

        // Prevent multiple edits
        if (isSaving) {
            return;
        }

        console.log('[APS Inline Edit] Starting edit for:', cellType);

        // Start editing this cell
        startEditing(cell, cellType, productId);
    }

    /**
     * Get cell type from column class or data attribute
     */
    function getCellType(cell) {
        // Method 1: Check data attribute (most reliable)
        if (cell.dataset.field) {
            return cell.dataset.field;
        }
        
        // Method 2: Check child elements with data-field
        const childWithField = cell.querySelector('[data-field]');
        if (childWithField) {
            return childWithField.dataset.field;
        }
        
        // Method 3: Check class-based detection (fallback)
        const classes = cell.className.split(' ');
        for (const cls of classes) {
            if (cls.startsWith('column-')) {
                return cls.replace('column-', '');
            }
        }
        
        // Method 4: Check parent cell classes
        const parent = cell.closest('td');
        if (parent) {
            const parentClasses = parent.className.split(' ');
            for (const cls of parentClasses) {
                if (cls.startsWith('column-')) {
                    return cls.replace('column-', '');
                }
            }
        }
        
        console.warn('[APS Inline Edit] Could not determine cell type:', cell);
        return null;
    }

    /**
     * Start editing a cell
     */
    function startEditing(cell, type, productId) {
        // Save previous edit if any
        if (editingCell && editingCell !== cell) {
            saveCurrentEdit();
        }

        // Store original content
        originalContent = cell.innerHTML;
        editingCell = cell;

        // Clear cell and add editor
        cell.innerHTML = '';
        cell.classList.add('aps-editing');

        // Create appropriate editor based on type
        switch (type) {
            case 'category':
                createDropdownEditor(cell, 'category', productId);
                break;
            case 'tags':
                createMultiSelectEditor(cell, 'tags', productId);
                break;
            case 'ribbon':
                createDropdownEditor(cell, 'ribbon', productId);
                break;
            case 'price':
                createPriceEditor(cell, productId);
                break;
            case 'status':
                createStatusEditor(cell, productId);
                break;
        }

        // Focus the editor
        const editor = cell.querySelector('.aps-inline-editor');
        if (editor) {
            const input = editor.querySelector('input, select');
            if (input) {
                setTimeout(() => input.focus(), 0);
            }
        }
    }

    /**
     * Create dropdown editor (for category, ribbon)
     */
    async function createDropdownEditor(cell, type, productId) {
        const options = type === 'category' ? await getCategories() : await getRibbons();
        
        const currentValues = getCurrentValues(cell, type);
        const currentValue = currentValues.length > 0 ? currentValues[0] : '';

        const editor = document.createElement('div');
        editor.className = 'aps-inline-editor aps-editor-dropdown';
        editor.dataset.field = type;
        editor.dataset.productId = productId;

        let html = `<select class="aps-inline-select" data-field="${type}" data-product-id="${productId}">`;

        // Add "None" option for ribbon
        if (type === 'ribbon') {
            html += `<option value="">— None —</option>`;
        }

        // Add "Add New" option
        html += `<option value="_add_new_">+ Add New ${type === 'category' ? 'Category' : 'Ribbon'}</option>`;

        // Add options
        options.forEach(opt => {
            const selected = currentValue === opt.id ? 'selected' : '';
            html += `<option value="${opt.id}" ${selected}>${escHtml(opt.name)}</option>`;
        });

        html += '</select>';
        editor.innerHTML = html;

        cell.appendChild(editor);

        // Add blur handler
        const select = editor.querySelector('select');
        select.addEventListener('change', () => {
            if (select.value === '_add_new_') {
                showAddNewDialog(cell, type, productId);
            }
        });

        select.addEventListener('blur', () => {
            setTimeout(() => saveField(type, productId, select.value, cell), 100);
        });
    }

    /**
     * Create multi-select editor (for tags)
     */
    async function createMultiSelectEditor(cell, type, productId) {
        const options = await getTags();
        const currentValues = getCurrentValues(cell, type);

        const editor = document.createElement('div');
        editor.className = 'aps-inline-editor aps-editor-multiselect';
        editor.dataset.field = type;
        editor.dataset.productId = productId;

        let html = `
            <div class="aps-multiselect-container">
                <div class="aps-multiselect-options">
        `;

        options.forEach(opt => {
            const checked = currentValues.includes(opt.id) ? 'checked' : '';
            html += `
                <label class="aps-multiselect-item">
                    <input type="checkbox" value="${opt.id}" ${checked} />
                    <span>${escHtml(opt.name)}</span>
                </label>
            `;
        });

        html += `
                </div>
                <div class="aps-multiselect-add">
                    <button type="button" class="button button-small">+ Add New Tag</button>
                </div>
            </div>
        `;

        editor.innerHTML = html;
        cell.appendChild(editor);

        // Add blur handler
        const container = editor.querySelector('.aps-multiselect-container');
        container.addEventListener('blur', (e) => {
            setTimeout(() => {
                const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');
                const values = Array.from(checkboxes).map(cb => cb.value);
                saveField(type, productId, values, cell);
            }, 100);
        }, true);

        // Add "Add New" handler
        const addBtn = editor.querySelector('.aps-multiselect-add button');
        addBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showAddNewDialog(cell, type, productId);
        });
    }

    /**
     * Create price editor
     */
    function createPriceEditor(cell, productId) {
        const row = cell.closest('tr');
        const priceContainer = cell.querySelector('[data-price]');
        
        const currentPrice = priceContainer ? parseFloat(priceContainer.dataset.price) : 0;
        const originalPrice = priceContainer ? parseFloat(priceContainer.dataset.originalPrice) : null;
        const currency = priceContainer ? priceContainer.dataset.currency : '$';

        const editor = document.createElement('div');
        editor.className = 'aps-inline-editor aps-editor-price';
        editor.dataset.productId = productId;

        let html = `
            <div class="aps-price-editor-container">
                <input type="number" 
                       class="aps-inline-price-input" 
                       step="0.01" 
                       min="0" 
                       value="${currentPrice}"
                       placeholder="0.00" />
                <span class="aps-price-currency">${getCurrencySymbol(currency)}</span>
                <span class="aps-price-discount-preview"></span>
            </div>
        `;

        editor.innerHTML = html;
        cell.appendChild(editor);

        const input = editor.querySelector('input');

        // Calculate discount on input change
        input.addEventListener('input', () => {
            updateDiscountPreview(input, originalPrice, currency);
        });

        // Save on blur
        input.addEventListener('blur', () => {
            setTimeout(() => saveField('price', productId, input.value, cell), 100);
        });

        // Save on Enter key
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            }
        });

        // Initial discount preview
        updateDiscountPreview(input, originalPrice, currency);
    }

    /**
     * Create status editor
     */
    function createStatusEditor(cell, productId) {
        const currentStatus = getStatusValue(cell);

        const editor = document.createElement('div');
        editor.className = 'aps-inline-editor aps-editor-status';
        editor.dataset.productId = productId;

        const editorHtml = `
            <div class="aps-status-editor-container">
                <select class="aps-inline-status-select">
                    <option value="publish" ${currentStatus === 'publish' ? 'selected' : ''}>Published</option>
                    <option value="draft" ${currentStatus === 'draft' ? 'selected' : ''}>Draft</option>
                </select>
            </div>
        `;

        editor.innerHTML = editorHtml;
        cell.appendChild(editor);

        const select = editor.querySelector('select');
        select.addEventListener('blur', () => {
            setTimeout(() => saveField('status', productId, select.value, cell), 100);
        });
    }

    /**
     * Update discount preview
     */
    function updateDiscountPreview(input, originalPrice, currency) {
        const preview = input.parentElement.querySelector('.aps-price-discount-preview');
        const newPrice = parseFloat(input.value) || 0;

        if (originalPrice && originalPrice > newPrice) {
            const discount = ((originalPrice - newPrice) / originalPrice * 100).toFixed(1);
            preview.textContent = `${discount}% OFF`;
            preview.className = 'aps-price-discount-preview visible';
        } else {
            preview.textContent = '';
            preview.className = 'aps-price-discount-preview';
        }
    }

    /**
     * Save field to API
     */
    async function saveField(fieldName, productId, value, cell) {
        if (isSaving || !editingCell) {
            return;
        }

        isSaving = true;
        showLoading(cell);

        try {
            const response = await fetch(`${config.apiBase}/products/${productId}/field`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify({
                    field_name: fieldName,
                    field_value: value
                })
            });

            const data = await response.json();

            if (data.code === 'success') {
                showSuccess(cell);
                updateCellContent(cell, fieldName, data.product);
            } else {
                showError(cell, data.message || 'Failed to save');
                setTimeout(() => restoreOriginalContent(cell), 2000);
            }
        } catch (error) {
            console.error('[APS Inline Edit] Save error:', error);
            showError(cell, 'Network error');
            setTimeout(() => restoreOriginalContent(cell), 2000);
        } finally {
            isSaving = false;
            editingCell = null;
        }
    }

    /**
     * Update cell content after successful save
     */
    function updateCellContent(cell, fieldName, product) {
        cell.classList.remove('aps-editing');

        switch (fieldName) {
            case 'category':
                updateCategoryCell(cell, product);
                break;
            case 'tags':
                updateTagsCell(cell, product);
                break;
            case 'ribbon':
                updateRibbonCell(cell, product);
                break;
            case 'price':
                updatePriceCell(cell, product);
                break;
            case 'status':
                updateStatusCell(cell, product);
                break;
        }
    }

    /**
     * Update category cell content
     */
    function updateCategoryCell(cell, product) {
        if (!product.category_names || product.category_names.length === 0) {
            cell.innerHTML = '—';
            return;
        }

        const badges = product.category_names.map(name => 
            `<span class="aps-product-category">${escHtml(name)} <span aria-hidden="true">×</span></span>`
        );
        cell.innerHTML = badges.join(' ');
    }

    /**
     * Update tags cell content
     */
    function updateTagsCell(cell, product) {
        if (!product.tag_names || product.tag_names.length === 0) {
            cell.innerHTML = '—';
            return;
        }

        const badges = product.tag_names.map(name => 
            `<span class="aps-product-tag">${escHtml(name)} <span aria-hidden="true">×</span></span>`
        );
        cell.innerHTML = badges.join(' ');
    }

    /**
     * Update ribbon cell content
     */
    function updateRibbonCell(cell, product) {
        if (!product.ribbon_names || product.ribbon_names.length === 0) {
            cell.innerHTML = '—';
            return;
        }

        const badges = product.ribbon_names.map(name => 
            `<span class="aps-product-badge">${escHtml(name)}</span>`
        );
        cell.innerHTML = badges.join(' ');
    }

    /**
     * Update price cell content
     */
    function updatePriceCell(cell, product) {
        const currency = getCurrencySymbol(product.currency || 'USD');
        const price = parseFloat(product.price);
        const originalPrice = parseFloat(product.original_price);
        
        let html = `<span class="aps-product-price">${currency}${formatPrice(price)}</span>`;

        if (originalPrice && originalPrice > price) {
            const discount = Math.round(((originalPrice - price) / originalPrice) * 100);
            html += `
                <span class="aps-product-price-original">${currency}${formatPrice(originalPrice)}</span>
                <span class="aps-product-price-discount">${discount}% OFF</span>
            `;
        }

        cell.innerHTML = html;
    }

    /**
     * Update status cell content
     */
    function updateStatusCell(cell, product) {
        const status = product.post_status || 'draft';
        const label = status.toUpperCase();
        const statusClass = `aps-product-status aps-product-status-${status}`;
        cell.innerHTML = `<span class="${statusClass}">${label}</span>`;
    }

    /**
     * Handle outside click - save current edit
     */
    function handleOutsideClick(e) {
        if (!editingCell) return;

        // Check if click is outside the editor
        if (!editingCell.contains(e.target)) {
            saveCurrentEdit();
        }
    }

    /**
     * Handle keyboard events
     */
    function handleKeyDown(e) {
        if (e.key === 'Escape' && editingCell) {
            e.preventDefault();
            restoreOriginalContent(editingCell);
        }
    }

    /**
     * Save current edit
     */
    function saveCurrentEdit() {
        if (!editingCell) return;

        const editor = editingCell.querySelector('.aps-inline-editor');
        if (!editor) return;

        const type = editor.dataset.field;
        const productId = editor.dataset.productId;

        switch (type) {
            case 'category':
            case 'ribbon':
                const select = editor.querySelector('select');
                if (select && select.value !== '_add_new_') {
                    saveField(type, productId, select.value, editingCell);
                }
                break;

            case 'tags':
                const checkboxes = editor.querySelectorAll('input[type="checkbox"]:checked');
                const values = Array.from(checkboxes).map(cb => cb.value);
                saveField(type, productId, values, editingCell);
                break;

            case 'price':
                const priceInput = editor.querySelector('input[type="number"]');
                if (priceInput) {
                    saveField('price', productId, priceInput.value, editingCell);
                }
                break;

            case 'status':
                const statusSelect = editor.querySelector('select');
                if (statusSelect) {
                    saveField('status', productId, statusSelect.value, editingCell);
                }
                break;
        }
    }

    /**
     * Restore original content
     */
    function restoreOriginalContent(cell) {
        cell.innerHTML = originalContent;
        cell.classList.remove('aps-editing', 'aps-loading', 'aps-success', 'aps-error');
        editingCell = null;
        originalContent = null;
    }

    /**
     * Show loading state
     */
    function showLoading(cell) {
        cell.classList.add('aps-loading');
        const editor = cell.querySelector('.aps-inline-editor');
        if (editor) {
            editor.classList.add('aps-editor-loading');
        }
    }

    /**
     * Show success state
     */
    function showSuccess(cell) {
        cell.classList.add('aps-success');
        setTimeout(() => cell.classList.remove('aps-success'), 2000);
    }

    /**
     * Show error state
     */
    function showError(cell, message) {
        cell.classList.add('aps-error');
        const editor = cell.querySelector('.aps-inline-editor');
        if (editor) {
            const errorMsg = document.createElement('div');
            errorMsg.className = 'aps-editor-error';
            errorMsg.textContent = message;
            editor.appendChild(errorMsg);
        }
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.aps-toast-notification');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast
        const toast = document.createElement('div');
        toast.className = `aps-toast-notification aps-toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            toast.classList.add('aps-toast-hiding');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /**
     * Show "Add New" dialog
     */
    /**
     * Show Add New dialog for creating new category/tag/ribbon
     */
    async function showAddNewDialog(cell, type, productId) {
        const name = prompt(`Enter new ${type} name:`);
        if (!name || !name.trim()) {
            // Restore original editor
            cell.innerHTML = '';
            startEditing(cell, type, productId);
            return;
        }

        showLoading(cell);

        try {
            let endpoint;
            switch (type) {
                case 'category':
                    endpoint = `${config.apiBase}/categories`;
                    break;
                case 'tags':
                    endpoint = `${config.apiBase}/tags`;
                    break;
                case 'ribbon':
                    endpoint = `${config.apiBase}/ribbons`;
                    break;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify({
                    name: name.trim()
                })
            });

            const data = await response.json();

            if (data.code === 'success' || response.ok) {
                // Clear cache to force reload
                cache[type === 'ribbon' ? 'ribbons' : type === 'category' ? 'categories' : 'tags'] = null;
                
                // Show success message
                showSuccess(cell);
                showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} created successfully!`, 'success');
                
                // Re-open editor with new option selected
                setTimeout(() => {
                    cell.innerHTML = '';
                    startEditing(cell, type, productId);
                    
                    // Select the newly created item
                    const editor = cell.querySelector('.aps-inline-editor');
                    if (editor) {
                        const select = editor.querySelector('select');
                        const newItemId = data.data?.id || data.category?.id || data.tag?.id || data.ribbon?.id;
                        if (select && newItemId) {
                            select.value = newItemId;
                            // Auto-save the new selection
                            setTimeout(() => saveField(type, productId, newItemId, cell), 100);
                        }
                    }
                }, 500);
            } else {
                showError(cell, data.message || 'Failed to create');
                showToast(`Failed to create ${type}: ${data.message || 'Unknown error'}`, 'error');
                setTimeout(() => {
                    cell.innerHTML = '';
                    startEditing(cell, type, productId);
                }, 2000);
            }
        } catch (error) {
            console.error(`[APS Inline Edit] Failed to create ${type}:`, error);
            showError(cell, 'Network error');
            showToast('Network error occurred', 'error');
            setTimeout(() => {
                cell.innerHTML = '';
                startEditing(cell, type, productId);
            }, 2000);
        }
    }

    /**
     * Load bulk action handlers
     */
    function loadBulkActionHandlers() {
        const bulkActions = document.getElementById('bulk-action-selector-top');
        if (!bulkActions) return;

        const applyButton = document.getElementById('doaction');
        if (!applyButton) return;

        // Remove default WordPress handler
        applyButton.removeEventListener('click', defaultBulkActionHandler);
        
        // Add custom handler
        applyButton.addEventListener('click', handleBulkAction);
    }

    /**
     * Handle bulk actions
     */
    async function handleBulkAction(e) {
        e.preventDefault();

        const actionSelect = document.getElementById('bulk-action-selector-top');
        const action = actionSelect.value;
        
        if (!action || action === '-1') {
            return;
        }

        // Get selected product IDs
        const checkboxes = document.querySelectorAll('input[name="post[]"]:checked');
        const productIds = Array.from(checkboxes).map(cb => cb.value);

        if (productIds.length === 0) {
            alert('Please select at least one product');
            return;
        }

        // Handle different bulk actions
        if (action === 'publish' || action === 'move_to_draft') {
            await handleBulkStatusChange(productIds, action);
        } else if (action === 'trash') {
            // Use default WordPress trash action
            defaultBulkActionHandler(e);
        } else if (action === 'export_csv') {
            // TODO: Implement CSV export
            alert('CSV export will be implemented later');
        } else {
            // Use default WordPress handler for other actions
            defaultBulkActionHandler(e);
        }
    }

    /**
     * Handle bulk status change
     */
    async function handleBulkStatusChange(productIds, action) {
        const targetStatus = action === 'publish' ? 'publish' : 'draft';
        const actionName = action === 'publish' ? 'publish' : 'move to draft';
        
        if (!confirm(`Are you sure you want to ${actionName} ${productIds.length} product(s)?`)) {
            return;
        }

        showBulkLoading();

        try {
            const response = await fetch(`${config.apiBase}/products/bulk-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': config.nonce
                },
                body: JSON.stringify({
                    product_ids: productIds,
                    status: targetStatus
                })
            });

            const data = await response.json();

            if (data.code === 'success') {
                showToast(data.message, 'success');
                location.reload();
            } else if (data.code === 'partial_success') {
                showToast(
                    `${data.message}\\n\\nFailed: ${data.failed_count || data.failed_ids?.length || 0} products`,
                    'warning'
                );
                location.reload();
            } else {
                showToast('Failed to update status: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (error) {
            console.error('[APS Inline Edit] Bulk status error:', error);
            showToast('Network error occurred', 'error');
        } finally {
            hideBulkLoading();
        }
    }

    /**
     * Default WordPress bulk action handler
     */
    function defaultBulkActionHandler(e) {
        // This will use WordPress's default bulk action handling
        const form = document.querySelector('form#posts-filter');
        if (form) {
            form.submit();
        }
    }

    /**
     * Show bulk loading state
     */
    function showBulkLoading() {
        const applyButton = document.getElementById('doaction');
        if (applyButton) {
            applyButton.disabled = true;
            applyButton.textContent = 'Processing...';
        }
    }

    /**
     * Hide bulk loading state
     */
    function hideBulkLoading() {
        const applyButton = document.getElementById('doaction');
        if (applyButton) {
            applyButton.disabled = false;
            applyButton.textContent = 'Apply';
        }
    }

    /**
     * Get current values from cell
     */
    function getCurrentValues(cell, type) {
        const values = [];
        
        switch (type) {
            case 'category':
                const categoryBadges = cell.querySelectorAll('[data-category-id]');
                categoryBadges.forEach(badge => {
                    values.push(badge.dataset.categoryId);
                });
                break;

            case 'tags':
                const tagBadges = cell.querySelectorAll('[data-tag-id]');
                tagBadges.forEach(badge => {
                    values.push(badge.dataset.tagId);
                });
                break;

            case 'ribbon':
                const ribbonBadges = cell.querySelectorAll('[data-ribbon-id]');
                ribbonBadges.forEach(badge => {
                    values.push(badge.dataset.ribbonId);
                });
                break;
        }

        return values;
    }

    /**
     * Get status value from cell
     */
    function getStatusValue(cell) {
        const statusSpan = cell.querySelector('[data-status]');
        if (!statusSpan) return 'draft';
        
        return statusSpan.dataset.status || 'draft';
    }

    /**
     * Get currency from row
     */
    function getCurrency(row) {
        const priceCell = row.querySelector('.column-price [data-currency]');
        return priceCell ? priceCell.dataset.currency : '$';
    }

    /**
     * Get currency symbol
     */
    function getCurrencySymbol(currency) {
        const symbols = {
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'JPY': '¥',
            'CAD': 'C$',
            'AUD': 'A$'
        };
        return symbols[currency] || '$';
    }

    /**
     * Parse price from string
     */
    function parsePrice(priceText) {
        return parseFloat(priceText.replace(/[^0-9.-]+/g, '')) || 0;
    }

    /**
     * Format price
     */
    function formatPrice(price) {
        return parseFloat(price).toFixed(2);
    }

    /**
     * Escape HTML
     */
    function escHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Fetch categories from API
     */
    async function getCategories() {
        if (cache.categories) {
            return cache.categories;
        }

        try {
            // Remove trailing slash to avoid double slashes
            const baseUrl = config.apiBase.replace(/\/$/, '');
            const response = await fetch(`${baseUrl}/categories`);
            
            if (!response.ok) {
                console.error('[APS Inline Edit] Categories API error:', response.status, response.statusText);
                return [];
            }
            
            const data = await response.json();
            
            // Handle different response formats
            // CategoriesController returns: { categories: [...], total: X, pages: Y }
            if (data.categories && Array.isArray(data.categories)) {
                cache.categories = data.categories;
                return data.categories;
            } else if (Array.isArray(data)) {
                cache.categories = data;
                return data;
            } else if (data.data && Array.isArray(data.data)) {
                cache.categories = data.data;
                return data.data;
            } else {
                console.error('[APS Inline Edit] Unexpected categories response:', data);
                return [];
            }
        } catch (error) {
            console.error('[APS Inline Edit] Failed to fetch categories:', error);
            return [];
        }
    }

    /**
     * Fetch tags from API
     */
    async function getTags() {
        if (cache.tags) {
            return cache.tags;
        }

        try {
            // Remove trailing slash to avoid double slashes
            const baseUrl = config.apiBase.replace(/\/$/, '');
            const response = await fetch(`${baseUrl}/tags`);
            
            if (!response.ok) {
                console.error('[APS Inline Edit] Tags API error:', response.status, response.statusText);
                return [];
            }
            
            const data = await response.json();
            
            // Handle different response formats
            // TagsController returns: { tags: [...], total: X, pages: Y }
            if (data.tags && Array.isArray(data.tags)) {
                cache.tags = data.tags;
                return data.tags;
            } else if (Array.isArray(data)) {
                cache.tags = data;
                return data;
            } else if (data.data && Array.isArray(data.data)) {
                cache.tags = data.data;
                return data.data;
            } else {
                console.error('[APS Inline Edit] Unexpected tags response:', data);
                return [];
            }
        } catch (error) {
            console.error('[APS Inline Edit] Failed to fetch tags:', error);
            return [];
        }
    }

    /**
     * Fetch ribbons from API
     */
    async function getRibbons() {
        if (cache.ribbons) {
            return cache.ribbons;
        }

        try {
            // Remove trailing slash to avoid double slashes
            const baseUrl = config.apiBase.replace(/\/$/, '');
            const response = await fetch(`${baseUrl}/ribbons`);
            
            if (!response.ok) {
                console.error('[APS Inline Edit] Ribbons API error:', response.status, response.statusText);
                return [];
            }
            
            const data = await response.json();
            
            // Handle different response formats
            // RibbonsController likely returns: { ribbons: [...], total: X, pages: Y }
            if (data.ribbons && Array.isArray(data.ribbons)) {
                cache.ribbons = data.ribbons;
                return data.ribbons;
            } else if (Array.isArray(data)) {
                cache.ribbons = data;
                return data;
            } else if (data.data && Array.isArray(data.data)) {
                cache.ribbons = data.data;
                return data.data;
            } else {
                console.error('[APS Inline Edit] Unexpected ribbons response:', data);
                return [];
            }
        } catch (error) {
            console.error('[APS Inline Edit] Failed to fetch ribbons:', error);
            return [];
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();