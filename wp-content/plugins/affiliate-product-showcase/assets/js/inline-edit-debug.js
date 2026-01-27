/**
 * Inline Edit Debug Script
 *
 * Run this in browser console to debug inline editing issues.
 * This script will help identify why click handlers aren't working.
 *
 * @package AffiliateProductShowcase
 */

(function() {
    'use strict';
    
    console.log('=== APS Inline Edit Debug ===');
    
    // 1. Check if table exists
    const table = document.querySelector('#the-list');
    console.log('1. Table found:', !!table);
    
    if (!table) {
        console.error('❌ Table #the-list NOT found!');
        console.log('Searching for alternative selectors...');
        
        // Try to find any table
        const tables = document.querySelectorAll('table.wp-list-table');
        console.log('Found tables:', tables.length);
        
        if (tables.length > 0) {
            tables.forEach((t, i) => {
                console.log(`Table ${i}:`, t.id, t.className);
            });
        }
        return;
    }
    
    console.log('✅ Table ID:', table.id);
    console.log('✅ Table classes:', table.className);
    
    // 2. Check for rows
    const rows = table.querySelectorAll('tr');
    console.log('\n2. Rows found:', rows.length);
    
    if (rows.length > 0) {
        const firstRow = rows[0];
        console.log('✅ First row:', firstRow);
        
        // Check for checkbox
        const checkbox = firstRow.querySelector('input[type="checkbox"]');
        console.log('✅ Checkbox found:', !!checkbox);
        if (checkbox) {
            console.log('✅ Product ID from checkbox:', checkbox.value);
        }
        
        // Check for cells
        const cells = firstRow.querySelectorAll('td');
        console.log('\n3. Cells found:', cells.length);
        
        cells.forEach((cell, i) => {
            const className = cell.className;
            const hasDataField = cell.hasAttribute('data-field');
            const dataFieldValue = cell.dataset.field;
            const text = cell.textContent.trim().substring(0, 30);
            
            console.log(`Cell ${i}:`, {
                class: className,
                hasDataField: hasDataField,
                dataField: dataFieldValue,
                text: text + (cell.textContent.length > 30 ? '...' : '')
            });
        });
    }
    
    // 3. Check for specific editable cells
    console.log('\n4. Checking editable cells:');
    
    const editableTypes = ['category', 'tags', 'ribbon', 'price', 'status'];
    
    editableTypes.forEach(type => {
        const selector = `td.column-${type}`;
        const cells = table.querySelectorAll(selector);
        console.log(`${type}: ${cells.length} cells found`);
        
        if (cells.length > 0) {
            const cell = cells[0];
            console.log(`  - data-field:`, cell.dataset.field);
            console.log(`  - class:`, cell.className);
        }
    });
    
    // 4. Check for script
    console.log('\n5. Script loading:');
    
    if (window.apsInlineEditData) {
        console.log('✅ apsInlineEditData exists');
        console.log('  - restUrl:', window.apsInlineEditData.restUrl);
        console.log('  - nonce:', window.apsInlineEditData.nonce ? '✅' : '❌');
    } else {
        console.error('❌ apsInlineEditData NOT found!');
    }
    
    // 5. Add test click listener
    console.log('\n6. Adding test click listener...');
    
    table.addEventListener('click', function(e) {
        const cell = e.target.closest('td');
        if (cell) {
            console.log('🖱️ Cell clicked:', {
                class: cell.className,
                dataField: cell.dataset.field,
                text: cell.textContent.trim().substring(0, 20)
            });
        }
    });
    
    console.log('✅ Test click listener added');
    console.log('\n=== Click on any cell to see debug info ===');
    console.log('=== Debug complete ===\n');
    
})();