# Script to add ABSPATH protection to all PHP files in src directory
$srcPath = "wp-content/plugins/affiliate-product-showcase/src"

# Get all PHP files
$phpFiles = Get-ChildItem -Path $srcPath -Recurse -Filter "*.php"

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Check if ABSPATH check already exists
    if ($content -match "ABSPATH") {
        Write-Host "Skipping $($file.FullName) - already has ABSPATH protection"
        continue
    }
    
    # Find namespace declaration and add ABSPATH after it
    if ($content -match '(namespace\s+[A-Za-z0-9_\\\\]+;)') {
        # Add ABSPATH after namespace declaration
        $newContent = $content -replace '(namespace\s+[A-Za-z0-9_\\\\]+;)', "`$1`r`n`r`nif ( ! defined( 'ABSPATH' ) ) {`r`n`texit;`r`n}"
        Set-Content -Path $file.FullName -Value $newContent -NoNewline
        Write-Host "Added ABSPATH protection to $($file.FullName)"
    } else {
        # Files without namespace - add after <?php
        $newContent = $content -replace '(<\?php\s*)', "`$1`r`n`r`nif ( ! defined( 'ABSPATH' ) ) {`r`n`texit;`r`n}"
        Set-Content -Path $file.FullName -Value $newContent -NoNewline
        Write-Host "Added ABSPATH protection to $($file.FullName) (no namespace)"
    }
}

Write-Host "`nABSPATH protection added to all PHP files."
