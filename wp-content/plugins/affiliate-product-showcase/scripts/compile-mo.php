<?php
/**
 * Compile PO file to MO file
 * 
 * Usage: php scripts/compile-mo.php [input.po] [output.mo]
 * Example: php scripts/compile-mo.php languages/affiliate-product-showcase-en_US.po languages/affiliate-product-showcase-en_US.mo
 */

if ($argc < 3) {
    echo "Usage: php {$argv[0]} [input.po] [output.mo]\n";
    echo "Example: php {$argv[0]} languages/affiliate-product-showcase-en_US.po languages/affiliate-product-showcase-en_US.mo\n";
    exit(1);
}

$poFile = $argv[1];
$moFile = $argv[2];

if (!file_exists($poFile)) {
    echo "Error: Input file '$poFile' not found.\n";
    exit(1);
}

// Read PO file
$poContent = file_get_contents($poFile);
if ($poContent === false) {
    echo "Error: Could not read input file '$poFile'.\n";
    exit(1);
}

// Parse PO file
$entries = parsePoFile($poContent);

if (empty($entries)) {
    echo "Warning: No translation entries found in PO file.\n";
}

// Write MO file
writeMoFile($entries, $moFile);

echo "Successfully compiled '$poFile' to '$moFile'\n";
echo "Total translations: " . count($entries) . "\n";

/**
 * Parse PO file into array of entries
 */
function parsePoFile($content) {
    $entries = [];
    $lines = explode("\n", $content);
    $currentEntry = [];
    $lastKey = '';
    
    foreach ($lines as $lineNumber => $line) {
        $line = rtrim($line);
        
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            if (!empty($currentEntry) && isset($currentEntry['msgid'])) {
                $entries[] = $currentEntry;
                $currentEntry = [];
                $lastKey = '';
            }
            continue;
        }
        
        // Match key: "value" pairs
        if (preg_match('/^(\w+)\s+"(.*)"/', $line, $matches)) {
            // Save previous key if we have one
            if ($lastKey !== '' && isset($currentEntry[$lastKey])) {
                // This key already exists, append to it
            }
            
            $key = $matches[1];
            $value = stripslashes($matches[2]);
            
            $currentEntry[$key] = $value;
            $lastKey = $key;
        } elseif (preg_match('/^"(.*)"/', $line, $matches) && $lastKey !== '') {
            // Multiline string continuation
            $value = stripslashes($matches[1]);
            $currentEntry[$lastKey] .= $value;
        }
    }
    
    // Don't forget the last entry
    if (!empty($currentEntry) && isset($currentEntry['msgid'])) {
        $entries[] = $currentEntry;
    }
    
    return $entries;
}

/**
 * Write MO file from entries
 */
function writeMoFile($entries, $filename) {
    $strings = [];
    $originals = [];
    
    foreach ($entries as $entry) {
        if (!isset($entry['msgid']) || $entry['msgid'] === '') {
            continue;
        }
        
        $msgid = $entry['msgid'];
        $msgstr = isset($entry['msgstr']) ? $entry['msgstr'] : '';
        
        // Handle plural forms
        if (isset($entry['msgid_plural'])) {
            $msgid = $msgid . chr(0) . $entry['msgid_plural'];
            $msgstr = isset($entry['msgstr[0]']) ? $entry['msgstr[0]'] : '';
            
            // Collect all plural forms
            for ($i = 0; isset($entry["msgstr[$i]"]); $i++) {
                if ($i > 0) {
                    $msgstr .= chr(0);
                }
                $msgstr .= $entry["msgstr[$i]"];
            }
        }
        
        $originals[] = $msgid;
        $strings[] = $msgstr;
    }
    
    // Sort by original strings
    array_multisort($originals, SORT_ASC, SORT_STRING, $strings);
    
    // Build MO file
    $offsets = [];
    $ids = '';
    $stringsOut = '';
    $tableSize = count($originals) * 2 * 4;
    
    for ($i = 0; $i < count($originals); $i++) {
        $id = $originals[$i];
        $str = $strings[$i];
        
        $lenId = strlen($id);
        $lenStr = strlen($str);
        
        $offsets[] = $lenId;
        $offsets[] = strlen($ids);
        $offsets[] = $lenStr;
        $offsets[] = strlen($stringsOut);
        
        $ids .= $id . chr(0);
        $stringsOut .= $str . chr(0);
    }
    
    // MO header
    $header = '';
    
    // Magic number
    $header .= pack('V', 0x950412de); // Little-endian
    
    // Revision
    $header .= pack('V', 0);
    
    // Number of strings
    $header .= pack('V', count($originals));
    
    // Offset of original table
    $header .= pack('V', 28);
    
    // Offset of translation table
    $header .= pack('V', 28 + $tableSize);
    
    // Size of hashing table
    $header .= pack('V', 0);
    
    // Offset of hashing table
    $header .= pack('V', 28 + $tableSize * 2);
    
    // Write file
    $fp = fopen($filename, 'wb');
    if (!$fp) {
        echo "Error: Could not open output file '$filename' for writing.\n";
        exit(1);
    }
    
    fwrite($fp, $header);
    
    // Write original strings table
    foreach ($offsets as $i => $val) {
        if ($i % 2 === 0) {
            fwrite($fp, pack('V', $val)); // Length
        } else {
            fwrite($fp, pack('V', $val)); // Offset
        }
    }
    
    // Write translation strings table
    foreach ($offsets as $i => $val) {
        if ($i % 2 === 0) {
            fwrite($fp, pack('V', $val)); // Length
        } else {
            fwrite($fp, pack('V', $val)); // Offset
        }
    }
    
    // Write original strings
    fwrite($fp, $ids);
    
    // Write translation strings
    fwrite($fp, $stringsOut);
    
    fclose($fp);
}
