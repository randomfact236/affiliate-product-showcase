/**
 * Inline Edit Debug & Diagnostic Script
 * 
 * Run this in browser console to diagnose inline editing issues
 */

console.log('=== APS Inline Edit Diagnostic ===');

// 1. Check if script is loaded
console.log('1. Checking if apsInlineEditData exists...');
if (typeof apsInlineEditData !== 'undefined') {
    console.log('✅ apsInlineEditData is defined:', apsInlineEditData);
} else {
    console.error('❌ apsInlineEditData is NOT defined - script may not be loaded');
}

// 2. Check if table exists
console.log('\n2. Checking if table exists...');
const table = document.querySelector('#the-list');
if (table) {
    console.log('✅ Table found:', table);
    console.log('   - Table has', table.children.length, 'rows');
} else {
    console.error('❌ Table #the-list not found');
    console.log('   Available tables:', document.querySelectorAll('table'));
}

// 3. Check first editable cell
console.log('\n3. Checking for editable cells...');
const editableCells = document.querySelectorAll('.column-category, .column-tags, .column-ribbon, .column-price, .column-status');
console.log('✅ Found', editableCells.length, 'potentially editable cells');

// 4. Check data attributes on first cell
if (editableCells.length > 0) {
    console.log('\n4. Checking data attributes on first cell...');
    const firstCell = editableCells[0];
    console.log('   Cell:', firstCell);
    console.log('   Cell classes:', firstCell.className);
    
    const dataField = firstCell.querySelector('[data-field]');
    if (dataField) {
        console.log('✅ Found element with data-field:', dataField.dataset.field);
        console.log('   data-product-id:', dataField.dataset.productId);
    } else {
        console.warn('⚠️  No element with data-field found in cell');
    }
}

// 5. Check if event listeners are attached
console.log('\n5. Checking event listeners...');
if (table) {
    const listeners = getEventListeners(table);
    if (listeners && listeners.click) {
        console.log('✅ Click listeners on table:', listeners.click.length);
    } else {
        console.warn('⚠️  No click listeners found on table');
    }
}

// 6. Try clicking a cell programmatically
console.log('\n6. Testing cell click programmatically...');
if (editableCells.length > 0) {
    console.log('   Try clicking the first editable cell manually to test');
    console.log('   Cell to click:', editableCells[0]);
}

// 7. Check REST API endpoint
console.log('\n7. Checking REST API...');
if (typeof apsInlineEditData !== 'undefined') {
    console.log('   REST URL:', apsInlineEditData.restUrl);
    console.log('   Nonce:', apsInlineEditData.nonce ? '✅ Present' : '❌ Missing');
}

// 8. Check console for initialization messages
console.log('\n8. Look for these initialization messages:');
console.log('   - [APS Inline Edit] Table found, initializing...');
console.log('   - [APS Inline Edit] Event listeners attached');
console.log('   - [APS Inline Edit] Initialized successfully');

console.log('\n=== End of Diagnostic ===');
console.log('If you see errors above, report them for debugging.');
