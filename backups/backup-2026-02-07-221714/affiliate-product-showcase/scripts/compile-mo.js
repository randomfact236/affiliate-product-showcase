/**
 * Compile PO file to MO file using Node.js
 * 
 * Usage: node scripts/compile-mo.js [input.po] [output.mo]
 * Example: node scripts/compile-mo.js languages/affiliate-product-showcase-en_US.po languages/affiliate-product-showcase-en_US.mo
 */

import fs from 'fs';
import path from 'path';

if (process.argv.length < 4) {
    console.log('Usage: node compile-mo.js [input.po] [output.mo]');
    console.log('Example: node compile-mo.js languages/affiliate-product-showcase-en_US.po languages/affiliate-product-showcase-en_US.mo');
    process.exit(1);
}

const poFile = process.argv[2];
const moFile = process.argv[3];

if (!fs.existsSync(poFile)) {
    console.error(`Error: Input file '${poFile}' not found.`);
    process.exit(1);
}

try {
    const poContent = fs.readFileSync(poFile, 'utf8');
    const entries = parsePoFile(poContent);
    
    if (entries.length === 0) {
        console.warn('Warning: No translation entries found in PO file.');
    }
    
    writeMoFile(entries, moFile);
    
    console.log(`Successfully compiled '${poFile}' to '${moFile}'`);
    console.log(`Total translations: ${entries.length}`);
} catch (error) {
    console.error('Error:', error.message);
    process.exit(1);
}

/**
 * Parse PO file into array of entries
 */
function parsePoFile(content) {
    const entries = [];
    const lines = content.split('\n');
    let currentEntry = {};
    let currentKey = '';
    let currentValue = '';
    let inMultiline = false;
    
    for (let i = 0; i < lines.length; i++) {
        let line = lines[i].trimEnd();
        
        // Skip comments
        if (line.startsWith('#')) {
            continue;
        }
        
        // Empty line ends current entry
        if (line === '') {
            if (Object.keys(currentEntry).length > 0 && currentEntry.msgid !== undefined) {
                entries.push(currentEntry);
                currentEntry = {};
            }
            inMultiline = false;
            currentKey = '';
            currentValue = '';
            continue;
        }
        
        // Match key: "value" pairs
        const match = line.match(/^(\w+(?:\[\d+\])?)\s+"(.*)"/);
        if (match) {
            // Save previous key-value if any
            if (inMultiline && currentKey !== '') {
                currentEntry[currentKey] = unescapeString(currentValue);
            }
            
            currentKey = match[1];
            currentValue = match[2];
            inMultiline = true;
        } else if (line.startsWith('"') && inMultiline) {
            // Multiline string continuation
            if (line.length > 1) {
                const value = line.substring(1, line.endsWith('"') ? line.length - 1 : line.length);
                currentValue += value;
            }
        } else if (line.match(/^(\w+(?:\[\d+\])?)\s*""$/)) {
            // Empty string
            const emptyMatch = line.match(/^(\w+(?:\[\d+\])?)\s*""$/);
            if (emptyMatch) {
                currentEntry[emptyMatch[1]] = '';
                currentKey = emptyMatch[1];
                currentValue = '';
                inMultiline = false;
            }
        }
    }
    
    // Don't forget last entry
    if (inMultiline && currentKey !== '') {
        currentEntry[currentKey] = unescapeString(currentValue);
    }
    
    if (Object.keys(currentEntry).length > 0 && currentEntry.msgid !== undefined) {
        entries.push(currentEntry);
    }
    
    return entries;
}

/**
 * Unescape string from PO format
 */
function unescapeString(str) {
    return str
        .replace(/\\"/g, '"')
        .replace(/\\n/g, '\n')
        .replace(/\\t/g, '\t')
        .replace(/\\r/g, '\r')
        .replace(/\\\\/g, '\\');
}

/**
 * Write MO file from entries
 */
function writeMoFile(entries, filename) {
    const strings = [];
    const originals = [];
    
    for (const entry of entries) {
        if (!entry.msgid || entry.msgid === '') {
            continue;
        }
        
        let msgid = entry.msgid;
        let msgstr = entry.msgstr || '';
        
        // Handle plural forms
        if (entry.msgid_plural) {
            msgid = msgid + '\0' + entry.msgid_plural;
            msgstr = entry['msgstr[0]'] || '';
            
            // Collect all plural forms
            let i = 1;
            while (entry[`msgstr[${i}]`] !== undefined) {
                if (i > 1) {
                    msgstr += '\0';
                }
                msgstr += entry[`msgstr[${i}]`];
                i++;
            }
        }
        
        originals.push(msgid);
        strings.push(msgstr);
    }
    
    // Sort by original strings
    const sorted = originals.map((orig, i) => ({ orig, str: strings[i] }));
    sorted.sort((a, b) => a.orig.localeCompare(b.orig));
    
    // Build MO file
    const offsets = [];
    let ids = '';
    let stringsOut = '';
    const tableSize = sorted.length * 2 * 4;
    
    for (let i = 0; i < sorted.length; i++) {
        const id = sorted[i].orig;
        const str = sorted[i].str;
        
        const lenId = Buffer.byteLength(id);
        const lenStr = Buffer.byteLength(str);
        
        offsets.push(lenId);
        offsets.push(Buffer.byteLength(ids));
        offsets.push(lenStr);
        offsets.push(Buffer.byteLength(stringsOut));
        
        ids += id + '\0';
        stringsOut += str + '\0';
    }
    
    // MO header (28 bytes)
    const header = Buffer.alloc(28);
    
    // Magic number: 0x950412de (little-endian)
    header.writeUInt32LE(0x950412de, 0);
    
    // Revision number: 0
    header.writeUInt32LE(0, 4);
    
    // Number of strings
    header.writeUInt32LE(sorted.length, 8);
    
    // Offset of original table
    header.writeUInt32LE(28, 12);
    
    // Offset of translation table
    header.writeUInt32LE(28 + tableSize, 16);
    
    // Size of hashing table: 0
    header.writeUInt32LE(0, 20);
    
    // Offset of hashing table
    header.writeUInt32LE(28 + tableSize * 2, 24);
    
    // Write file
    const fd = fs.openSync(filename, 'w');
    fs.writeSync(fd, header, 0, header.length, 0);
    
    // Write original strings table
    for (const offset of offsets) {
        const buf = Buffer.alloc(4);
        buf.writeUInt32LE(offset, 0);
        fs.writeSync(fd, buf, 0, 4);
    }
    
    // Write translation strings table
    for (const offset of offsets) {
        const buf = Buffer.alloc(4);
        buf.writeUInt32LE(offset, 0);
        fs.writeSync(fd, buf, 0, 4);
    }
    
    // Write original strings
    const idsBuf = Buffer.from(ids, 'utf8');
    fs.writeSync(fd, idsBuf, 0, idsBuf.length);
    
    // Write translation strings
    const stringsBuf = Buffer.from(stringsOut, 'utf8');
    fs.writeSync(fd, stringsBuf, 0, stringsBuf.length);
    
    fs.closeSync(fd);
}
