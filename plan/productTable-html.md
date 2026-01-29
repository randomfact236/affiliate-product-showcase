<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Page UI - WooCommerce Style</title>
    <style>
        /* --- Reset & Base Styles --- */
        :root {
            --color-text-main: #1d2327;
            --color-text-light: #646970;
            --color-border: #c3c4c7;
            --color-bg-light: #f0f0f1;
            --color-primary: #2271b1;
            --color-primary-hover: #135e96;
            --color-red: #d63638;
            --color-green-bg: #e5f7ed;
            --color-green-text: #22c55e;
            --color-yellow-bg: #fef3c7;
            --color-yellow-text: #d97706;
            --color-red-bg: #fee2e2;
            --color-red-text: #dc2626;
            --color-gray-bg: #f3f4f6;
            --color-gray-text: #6b7280;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
            color: var(--color-text-main);
            background-color: #fff;
            margin: 0;
            padding: 20px;
            line-height: 1.5;
        }

        .wrap {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* --- Header Section --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 23px;
            font-weight: 400;
            margin: 0;
            padding: 9px 0 4px 0;
            line-height: 1.3;
        }

        .page-title-action {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 2;
            height: 28px;
            margin: 0 10px 0 0;
            padding: 0 10px;
            cursor: pointer;
            border: 1px solid var(--color-primary);
            border-radius: 3px;
            background: #f6f7f7;
            color: var(--color-primary);
            transition: .1s;
        }

        .page-title-action:hover {
            background: #f0f0f1;
            border-color: var(--color-primary-hover);
            color: var(--color-primary-hover);
        }

        .page-title-action.primary {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: #fff;
        }

        .page-title-action.primary:hover {
            background: var(--color-primary-hover);
            border-color: var(--color-primary-hover);
        }

        /* --- Navigation Tabs --- */
        .nav-tab-wrapper {
            display: block;
            float: left;
            margin-bottom: 20px;
        }

        .nav-tab {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 1.71428571;
            margin: 0 5px -1px 0;
            padding: 5px 10px;
            border: 1px solid #c3c4c7;
            border-bottom: none;
            background: #f6f7f7;
            color: #646970;
            border-radius: 3px 3px 0 0;
            cursor: pointer;
        }

        .nav-tab.nav-tab-active {
            background: #fff;
            color: #2c3338;
            border-bottom: 1px solid #fff;
            font-weight: 600;
        }

        .nav-tab:hover {
            background-color: #fff;
            color: #2c3338;
        }

        /* --- Toolbar --- */
        .tablenav {
            clear: both;
            height: 30px;
            margin: 6px 0 4px 0;
            vertical-align: middle;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alignleft {
            float: left;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .alignright {
            float: right;
            display: flex;
            align-items: center;
        }

        /* Select & Inputs */
        select, input[type="text"] {
            margin: 1px;
            padding: 4px 8px;
            line-height: 2;
            height: 30px;
            vertical-align: middle;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            color: #2c3338;
        }

        .button {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            margin: 0;
            padding: 0 10px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            border-radius: 3px;
            white-space: nowrap;
            box-sizing: border-box;
        }

        .button.action {
            background: #f6f7f7;
            border-color: #8c8f94;
            color: #1d2327;
            height: 30px;
            margin-top: 1px;
            vertical-align: top;
        }

        .button.action:hover {
            border-color: #646970;
            color: #101517;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-box label {
            margin-right: 5px;
            font-weight: 600;
            color: var(--color-text-light);
        }

        /* --- Table --- */
        .wp-list-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid var(--color-border);
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            clear: both;
            display: table;
        }

        .wp-list-table thead th {
            border-bottom: 1px solid var(--color-border);
            font-weight: 600;
            text-align: left;
            padding: 8px 10px;
            font-size: 13px;
        }

        .wp-list-table tbody td {
            padding: 9px 10px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid var(--color-border);
            color: var(--color-text-light);
            word-break: break-word;
        }

        .wp-list-table tr:nth-child(odd) {
            background-color: #f6f7f7;
        }

        .wp-list-table tr:nth-child(even) {
            background-color: #fff;
        }

        .wp-list-table tr:hover td {
            background-color: #f0f0f1;
        }

        /* Column Widths */
        .column-cb { width: 2.2em; padding: 3px 0 0 10px !important; }
        .column-id { width: 50px; text-align: center; }
        .column-logo { width: 60px; }
        .column-ribbon { width: 120px; }
        .column-featured { width: 60px; text-align: center; font-size: 18px; color: #e6b800; }
        .column-price { width: 100px; }
        .column-status { width: 120px; }
        .column-actions { width: 100px; text-align: right; }

        /* --- Specific Styles as per Plan --- */

        /* LOGO */
        .aps-product-logo {
            display: block;
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        /* TITLE */
        .row-title {
            font-weight: 600;
            color: var(--color-primary);
            text-decoration: none;
            font-size: 14px;
        }
        .row-title:hover {
            color: var(--color-primary-hover);
        }
        .post-com-count-wrapper {
            font-size: 11px;
            color: #d63638;
            margin-left: 5px;
        }

        /* CATEGORY (NO BADGE - PLAIN TEXT) */
        .column-category .aps-category-text {
            color: #1d2327;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.5;
        }

        /* TAGS (NO BADGE - PLAIN TEXT) */
        .column-tags .aps-tag-text {
            color: #646970;
            font-size: 13px;
            font-weight: 400;
            line-height: 1.5;
        }

        /* RIBBON (KEEP BADGE - Red) */
        .aps-ribbon-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #d63638; /* Red */
            color: #ffffff;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 2px;
        }

        .aps-ribbon-badge + .aps-ribbon-badge {
            margin-left: 4px;
        }

        /* STATUS (KEEP BADGE - Colored) */
        .aps-product-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .aps-product-status-published {
            background: var(--color-green-bg);
            color: var(--color-green-text);
        }

        .aps-product-status-draft {
            background: var(--color-yellow-bg);
            color: var(--color-yellow-text);
        }

        .aps-product-status-trash {
            background: var(--color-red-bg);
            color: var(--color-red-text);
        }

        .aps-product-status-pending {
            background: var(--color-gray-bg);
            color: var(--color-gray-text);
        }

        /* ACTIONS */
        .row-actions {
            font-size: 12px;
            color: #ddd;
            visibility: hidden;
            margin-top: 4px;
        }
        .wp-list-table tr:hover .row-actions {
            visibility: visible;
        }
        .row-actions span {
            color: #a7aaad;
        }
        .row-actions a {
            text-decoration: none;
            color: var(--color-primary);
        }
        .row-actions a:hover {
            color: var(--color-primary-hover);
        }
        .trash a {
            color: #a00;
        }
        .trash a:hover {
            color: #dc2626;
        }

        /* PAGINATION */
        .pagination {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .pagination button {
            background: #f6f7f7;
            border: 1px solid #c3c4c7;
            color: #2c3338;
            padding: 0 10px 3px;
            cursor: pointer;
            height: 28px;
            font-size: 13px;
        }
        .pagination button.disabled {
            opacity: 0.5;
            cursor: default;
        }
        .pagination .current-page {
            padding: 0 5px;
            font-size: 13px;
            text-align: center;
        }

        /* NOTIFICATION TOAST */
        #toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        .notice {
            background: #fff;
            border-left: 4px solid #72aee6;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            margin: 5px 0 15px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        }
        .notice-success {
            border-left-color: #00a32a;
        }
        .notice p {
            margin: 0;
            font-size: 13px;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* RESPONSIVE */
        @media screen and (max-width: 782px) {
            .wp-list-table thead { display: none; }
            .wp-list-table tbody tr {
                display: block;
                border-bottom: 1px solid #c3c4c7;
                margin-bottom: 10px;
            }
            .wp-list-table tbody td {
                display: block;
                text-align: right;
                padding: 8px 10px;
                border-bottom: 1px solid #eee;
            }
            .wp-list-table tbody td::before {
                content: attr(data-colname);
                font-weight: 600;
                float: left;
                margin-left: -10px;
                text-align: left;
            }
            .column-cb { display: none; }
            .page-title-action { float: none; margin-top: 10px; }
            .tablenav { height: auto; flex-direction: column; align-items: flex-start; gap: 10px; }
            .alignleft, .alignright { float: none; width: 100%; }
        }
    </style>
</head>
<body>

<div class="wrap">
    <div class="page-header">
        <h1>Products</h1>
        <a href="#" class="page-title-action primary" onclick="showToast('Add New Product modal would open here.'); return false;">Add New Product</a>
    </div>

    <hr class="wp-header-end">

    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" data-filter="all">All <span class="count">(4)</span></a>
        <a href="#" class="nav-tab" data-filter="published">Published <span class="count">(2)</span></a>
        <a href="#" class="nav-tab" data-filter="draft">Drafts <span class="count">(1)</span></a>
        <a href="#" class="nav-tab" data-filter="trash">Trash <span class="count">(1)</span></a>
    </h2>

    <form id="posts-filter" method="get">
        
        <!-- Top Toolbar -->
        <div class="tablenav top">
            <div class="alignleft actions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1">Bulk actions</option>
                    <option value="trash">Move to Trash</option>
                    <option value="edit">Edit</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="Apply" onclick="handleBulkAction(event)">
            </div>

            <div class="alignleft actions">
                <select name="filter-category" id="filter-category" onchange="applyFilters()">
                    <option value="all">All Categories</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Clothing">Clothing</option>
                    <option value="Books">Books</option>
                </select>

                <select name="filter-status" id="filter-status" onchange="applyFilters()">
                    <option value="all">All Statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="trash">Trash</option>
                </select>
            </div>

            <div class="alignright">
                <div class="search-box">
                    <label for="post-search-input" class="screen-reader-text">Search Products:</label>
                    <input type="text" id="post-search-input" name="s" placeholder="Search products..." onkeyup="applyFilters()">
                    <input type="submit" id="search-submit" class="button" value="Search Products" onclick="event.preventDefault(); applyFilters()">
                </div>
            </div>

            <div class="tablenav-pages">
                <span class="displaying-num">4 items</span>
                <span class="pagination-links">
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <span class="paging-input">1</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                </span>
            </div>
        </div>

        <!-- Table -->
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label class="screen-reader-text">Select All</label>
                        <input id="cb-select-all-1" type="checkbox" onclick="toggleAllCheckboxes(this)">
                    </td>
                    <th scope="col" id="id" class="manage-column column-id">ID</th>
                    <th scope="col" id="logo" class="manage-column column-logo">Logo</th>
                    <th scope="col" id="title" class="manage-column column-title column-primary">Title</th>
                    <th scope="col" id="category" class="manage-column column-category">Category</th>
                    <th scope="col" id="tags" class="manage-column column-tags">Tags</th>
                    <th scope="col" id="ribbon" class="manage-column column-ribbon">Ribbon</th>
                    <th scope="col" id="featured" class="manage-column column-featured">Featured</th>
                    <th scope="col" id="price" class="manage-column column-price">Price</th>
                    <th scope="col" id="status" class="manage-column column-status">Status</th>
                </tr>
            </thead>

            <tbody id="the-list" data-wp-lists="list:product">
                <!-- Rows will be populated by JavaScript -->
            </tbody>

            <tfoot>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <label class="screen-reader-text">Select All</label>
                        <input id="cb-select-all-2" type="checkbox" onclick="toggleAllCheckboxes(this)">
                    </td>
                    <th scope="col" class="manage-column column-id">ID</th>
                    <th scope="col" class="manage-column column-logo">Logo</th>
                    <th scope="col" class="manage-column column-title column-primary">Title</th>
                    <th scope="col" class="manage-column column-category">Category</th>
                    <th scope="col" class="manage-column column-tags">Tags</th>
                    <th scope="col" class="manage-column column-ribbon">Ribbon</th>
                    <th scope="col" class="manage-column column-featured">Featured</th>
                    <th scope="col" class="manage-column column-price">Price</th>
                    <th scope="col" class="manage-column column-status">Status</th>
                </tr>
            </tfoot>
        </table>

        <!-- Bottom Toolbar -->
        <div class="tablenav bottom">
            <div class="alignleft actions">
                <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                <select name="action2" id="bulk-action-selector-bottom">
                    <option value="-1">Bulk actions</option>
                    <option value="trash">Move to Trash</option>
                </select>
                <input type="submit" id="doaction2" class="button action" value="Apply" onclick="handleBulkAction(event)">
            </div>
            
            <div class="tablenav-pages">
                <span class="displaying-num">4 items</span>
                <span class="pagination-links">
                    <span class="tablenav-pages-navspan" aria-hidden="true">«</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <span class="paging-input">1</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <span class="tablenav-pages-navspan" aria-hidden="true">»</span>
                </span>
            </div>
        </div>
    </form>
</div>

<div id="toast-container"></div>

<script>
    // Mock Data Source
    const initialProducts = [
        {
            id: 101,
            title: "Wireless Noise Cancelling Headphones",
            logo: "https://picsum.photos/seed/tech1/48/48",
            categories: ["Electronics", "Audio"],
            tags: ["New", "Sale", "Popular"],
            ribbon: "Best Seller",
            featured: true,
            price: "$249.00",
            status: "published"
        },
        {
            id: 102,
            title: "Classic Cotton T-Shirt",
            logo: "https://picsum.photos/seed/cloth2/48/48",
            categories: ["Clothing", "Men"],
            tags: ["Summer"],
            ribbon: "",
            featured: false,
            price: "$19.99 <del>$25.00</del>",
            status: "published"
        },
        {
            id: 103,
            title: "Introduction to Web Design",
            logo: "https://picsum.photos/seed/book3/48/48",
            categories: ["Books", "Education"],
            tags: ["Hardcover"],
            ribbon: "New Arrival",
            featured: true,
            price: "$45.00",
            status: "draft"
        },
        {
            id: 104,
            title: "Old Smartphone Model X",
            logo: "https://picsum.photos/seed/tech4/48/48",
            categories: ["Electronics"],
            tags: ["Refurbished"],
            ribbon: "",
            featured: false,
            price: "$199.00",
            status: "trash"
        }
    ];

    // State
    let products = [...initialProducts];
    let currentFilter = 'all';

    // DOM Elements
    const tableBody = document.getElementById('the-list');
    const categoryFilter = document.getElementById('filter-category');
    const statusFilter = document.getElementById('filter-status');
    const searchInput = document.getElementById('post-search-input');
    const navTabs = document.querySelectorAll('.nav-tab');

    // --- Render Function ---
    function renderTable() {
        tableBody.innerHTML = '';

        // Filter Logic
        const searchTerm = searchInput.value.toLowerCase();
        const catFilter = categoryFilter.value;
        const statFilter = statusFilter.value;

        const filteredProducts = products.filter(product => {
            const matchesTab = currentFilter === 'all' || product.status === currentFilter;
            const matchesSearch = product.title.toLowerCase().includes(searchTerm) || product.id.toString().includes(searchTerm);
            const matchesCat = catFilter === 'all' || product.categories.includes(catFilter);
            const matchesStat = statFilter === 'all' || product.status === statFilter;

            return matchesTab && matchesSearch && matchesCat && matchesStat;
        });

        // Update Counts (Mocking visual count update)
        document.querySelector('.displaying-num').textContent = `${filteredProducts.length} items`;

        if (filteredProducts.length === 0) {
            tableBody.innerHTML = `
                <tr class="no-items">
                    <td class="colspanchange" colspan="10">No products found.</td>
                </tr>
            `;
            return;
        }

        // Generate Rows
        filteredProducts.forEach(product => {
            const tr = document.createElement('tr');
            
            // 1. Category Plain Text
            const categoryHtml = `<span class="aps-category-text">${product.categories.join(', ')}</span>`;

            // 2. Tags Plain Text
            const tagsHtml = `<span class="aps-tag-text">${product.tags.join(', ')}</span>`;

            // 3. Ribbon Badge (Red)
            let ribbonHtml = '';
            if (product.ribbon) {
                ribbonHtml = `<span class="aps-ribbon-badge">${product.ribbon}</span>`;
            }

            // 4. Status Badge (Colored)
            let statusHtml = '';
            const statusLabel = product.status.charAt(0).toUpperCase() + product.status.slice(1);
            statusHtml = `<span class="aps-product-status aps-product-status-${product.status}">${statusLabel}</span>`;

            // 5. Featured Star
            const featuredHtml = product.featured ? '★' : '';

            tr.innerHTML = `
                <th class="check-column" scope="row">
                    <input type="checkbox" name="post[]" value="${product.id}" class="row-checkbox">
                </th>
                <td class="column-id">${product.id}</td>
                <td class="column-logo" data-colname="Logo">
                    <img src="${product.logo}" alt="" class="aps-product-logo">
                </td>
                <td class="column-title column-primary" data-colname="Title">
                    <strong>
                        <a href="#" class="row-title">${product.title}</a>
                    </strong>
                    <div class="row-actions">
                        <span class="edit"><a href="#">Edit</a> | </span>
                        <span class="inline hide-if-no-js"><a href="#">Quick Edit</a> | </span>
                        <span class="trash"><a href="#" onclick="deleteProduct(${product.id}); return false;">Trash</a> | </span>
                        <span class="view"><a href="#">View</a></span>
                    </div>
                </td>
                <td class="column-category" data-colname="Category">${categoryHtml}</td>
                <td class="column-tags" data-colname="Tags">${tagsHtml}</td>
                <td class="column-ribbon" data-colname="Ribbon">${ribbonHtml}</td>
                <td class="column-featured" data-colname="Featured">${featuredHtml}</td>
                <td class="column-price" data-colname="Price">${product.price}</td>
                <td class="column-status" data-colname="Status">${statusHtml}</td>
            `;
            tableBody.appendChild(tr);
        });
    }

    // --- Event Handlers ---

    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function deleteProduct(id) {
        if(confirm('Are you sure you want to move this product to the trash?')) {
            const product = products.find(p => p.id === id);
            if(product) {
                product.status = 'trash';
                renderTable();
                showToast('Product moved to trash.', 'success');
            }
        }
    }

    function handleBulkAction(event) {
        event.preventDefault();
        const selectId = event.target.id === 'doaction' ? 'bulk-action-selector-top' : 'bulk-action-selector-bottom';
        const action = document.getElementById(selectId).value;
        
        if (action === '-1') return;

        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            showToast('Please select at least one product.', 'error');
            return;
        }

        if (action === 'trash') {
            const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
            let count = 0;
            ids.forEach(id => {
                const product = products.find(p => p.id === id);
                if(product && product.status !== 'trash') {
                    product.status = 'trash';
                    count++;
                }
            });
            renderTable();
            showToast(`${count} products moved to trash.`, 'success');
            // Reset checkboxes
            document.getElementById('cb-select-all-1').checked = false;
            document.getElementById('cb-select-all-2').checked = false;
        } else {
            showToast(`Bulk action "${action}" is just a demo.`, 'error');
        }
    }

    function applyFilters() {
        renderTable();
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const notice = document.createElement('div');
        notice.className = `notice notice-${type === 'success' ? 'success' : 'error'}`;
        notice.innerHTML = `<p>${message}</p>`;
        container.appendChild(notice);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notice.style.opacity = '0';
            setTimeout(() => notice.remove(), 300);
        }, 3000);
    }

    // Tab Navigation
    navTabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            navTabs.forEach(t => t.classList.remove('nav-tab-active'));
            tab.classList.add('nav-tab-active');
            currentFilter = tab.getAttribute('data-filter');
            renderTable();
        });
    });

    // Initial Render
    renderTable();

</script>

</body>
</html>