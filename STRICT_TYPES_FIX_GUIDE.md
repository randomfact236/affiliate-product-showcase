# Strict Types and Security Headers Fix Guide

## Issue 1: Add strict_types to All PHP Files

### Problem
41 PHP files in `wp-content/plugins/affiliate-product-showcase/src/` are missing `declare(strict_types=1);` statement.

### Solution

#### Step 1: Create a Backup (HIGHLY RECOMMENDED)
```powershell
# Create a timestamped backup
$backupDate = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "wp-content\plugins\affiliate-product-showcase\src_backup_$backupDate"
Copy-Item -Path "wp-content\plugins\affiliate-product-showcase\src" -Destination $backupPath -Recurse
Write-Host "Backup created at: $backupPath"
```

#### Step 2: Safe Bulk Addition Command
```powershell
# Navigate to src directory
cd wp-content/plugins/affiliate-product-showcase/src

# Find all PHP files missing declare(strict_types=1); and add it safely
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -notmatch "declare\(strict_types=1\);") {
        # Insert declare statement after <?php
        $newContent = $content -replace "(<\?php\s*)", "`$1`ndeclare(strict_types=1);`n"
        Set-Content -Path $_.FullName -Value $newContent -NoNewline
        Write-Host "Updated: $($_.FullName)"
    }
}

Write-Host "Done! Check files updated above."
```

**Alternative (More Precise) Version:**
```powershell
cd wp-content/plugins/affiliate-product-showcase/src

# More precise version that handles different <?php formats
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -notmatch "declare\(strict_types=1\);") {
        if ($content -match "<\?php\r?\n") {
            # Case 1: <?php followed by newline
            $newContent = $content -replace "(<\?php\r?\n)", "`$1declare(strict_types=1);`r`n"
        } elseif ($content -match "<\?php\s") {
            # Case 2: <?php followed by space or other whitespace
            $newContent = $content -replace "(<\?php\s)", "`$1`r`ndeclare(strict_types=1);`r`n"
        } else {
            # Case 3: <?php at end of line or just <?php
            $newContent = $content -replace "(<\?php)", "`$1`r`ndeclare(strict_types=1);`r`n"
        }
        Set-Content -Path $_.FullName -Value $newContent -NoNewline
        Write-Host "Updated: $($_.FullName)"
    }
}
```

#### Step 3: Verification Command
```powershell
cd wp-content/plugins/affiliate-product-showcase/src

# Count files with strict_types
$withStrict = (Get-ChildItem -Recurse -Filter *.php | Where-Object { Get-Content $_.FullName | Select-String "declare\(strict_types=1\);" }).Count

# Count files without strict_types
$withoutStrict = (Get-ChildItem -Recurse -Filter *.php | Where-Object { -not (Get-Content $_.FullName | Select-String "declare\(strict_types=1\);") }).Count

# Total PHP files
$totalFiles = (Get-ChildItem -Recurse -Filter *.php).Count

Write-Host "=== Verification Results ==="
Write-Host "Total PHP files: $totalFiles"
Write-Host "Files WITH strict_types: $withStrict"
Write-Host "Files WITHOUT strict_types: $withoutStrict"

if ($withoutStrict -eq 0) {
    Write-Host "✅ SUCCESS: All PHP files now have strict_types!"
} else {
    Write-Host "⚠️  WARNING: $withoutStrict files still missing strict_types"
    Get-ChildItem -Recurse -Filter *.php | Where-Object { -not (Get-Content $_.FullName | Select-String "declare\(strict_types=1\);") } | Select-Object FullName
}
```

#### Step 4: Detailed File Listing (Optional)
```powershell
# List all files that still don't have strict_types (should be empty after fix)
cd wp-content/plugins/affiliate-product-showcase/src
Get-ChildItem -Recurse -Filter *.php | Where-Object { -not (Get-Content $_.FullName | Select-String "declare\(strict_types=1\);") } | Select-Object FullName
```

### Testing Instructions

#### 1. Manual File Check
```powershell
# Check a few random files to verify the change
Get-Content wp-content/plugins/affiliate-product-showcase/src/Admin/Settings.php -First 5
Get-Content wp-content/plugins/affiliate-product-showcase/src/Services/ProductService.php -First 5
```

Expected output should show:
```php
<?php
declare(strict_types=1);

namespace ...
```

#### 2. PHP Syntax Validation
```powershell
# Validate PHP syntax on all files
cd wp-content/plugins/affiliate-product-showcase
Get-ChildItem -Recurse -Path src -Filter *.php | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($result -notmatch "No syntax errors") {
        Write-Host "SYNTAX ERROR in $($_.FullName):"
        Write-Host $result
    }
}
```

#### 3. Run PHPUnit Tests
```powershell
cd wp-content/plugins/affiliate-product-showcase
./vendor/bin/phpunit
```

All tests should pass if strict_types was added correctly.

---

## Issue 2: Security Headers (ALREADY COMPLETED ✅)

### Status
Security headers are **already fully implemented** and working correctly!

### Implementation Details

**File:** `wp-content/plugins/affiliate-product-showcase/src/Security/Headers.php`
- ✅ Comprehensive security headers implemented
- ✅ Uses `wp_headers` filter for reliable injection
- ✅ Separate policies for admin, frontend, and REST API
- ✅ OWASP-compliant headers

**File:** `wp-content/plugins/affiliate-product-showcase/src/Admin/Admin.php`
- ✅ Properly imports and initializes Headers class
- ✅ Security headers are activated in `init()` method

### Security Headers Implemented

#### Admin Pages (is_admin()):
- **Content-Security-Policy**: Restricts resources with admin-friendly directives
- **X-Content-Type-Options**: `nosniff` - Prevents MIME type sniffing
- **X-Frame-Options**: `SAMEORIGIN` - Prevents clickjacking
- **X-XSS-Protection**: `1; mode=block` - Enables browser XSS filters
- **Referrer-Policy**: `strict-origin-when-cross-origin` - Controls referrer leakage
- **Permissions-Policy**: Restricts geolocation, microphone, camera, payment

#### Frontend Pages:
- Same headers with stricter CSP directives
- Blocks inline scripts where possible

#### REST API:
- X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
- No CSP to ensure JSON responses work correctly

### Testing Security Headers

#### 1. Browser Developer Tools Test
```bash
# Open any admin page in WordPress
# Press F12, go to Network tab
# Refresh the page
# Click on the main document request
# Look at "Response Headers" section
```

Expected headers:
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; ...
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()
```

#### 2. curl Test
```powershell
# Test headers via command line
$headers = curl -I http://localhost/wp-admin/ -H "Cookie: wordpress_test_cookie=WP+Cookie+check"
$headers | Select-String "Content-Security-Policy|X-Content-Type-Options|X-Frame-Options|X-XSS-Protection|Referrer-Policy|Permissions-Policy"
```

#### 3. Online Security Header Test
```bash
# Use online tools like:
# - https://securityheaders.com/
# - https://observatory.mozilla.org/
# Enter your admin URL and test
```

Expected score: **A or A+** on securityheaders.com

---

## Complete Execution Workflow

### Phase 1: Preparation (5 minutes)
1. ✅ Review this guide
2. ⬜ Create backup (recommended)
3. ⬜ Verify current state (41 files missing)

### Phase 2: Execute Fix (2 minutes)
1. ⬜ Run the bulk addition command
2. ⬜ Verify no errors occurred
3. ⬜ Run verification command

### Phase 3: Testing (5 minutes)
1. ⬜ Manual file check (2-3 random files)
2. ⬜ PHP syntax validation
3. ⬜ Run PHPUnit tests
4. ⬜ Test security headers in browser

### Phase 4: Final Verification (2 minutes)
1. ⬜ Confirm 0 files missing strict_types
2. ⬜ Confirm security headers present in browser
3. ⬜ All tests passing

---

## Rollback Instructions (If Needed)

### Restore from Backup
```powershell
# List available backups
Get-ChildItem wp-content/plugins/affiliate-product-showcase/ | Where-Object { $_.Name -match "src_backup_\d+" }

# Restore specific backup (replace with your backup path)
$backupPath = "wp-content\plugins\affiliate-product-showcase\src_backup_20260114_223700"
Remove-Item -Path "wp-content\plugins\affiliate-product-showcase\src" -Recurse -Force
Copy-Item -Path $backupPath -Destination "wp-content\plugins\affiliate-product-showcase\src" -Recurse
Write-Host "Restored from: $backupPath"
```

---

## Summary

### Issue 1: Strict Types
- **Status**: Ready to fix
- **Files affected**: 41 PHP files
- **Complexity**: Low (safe bulk operation)
- **Time required**: ~10 minutes including testing
- **Risk**: Low (easily reversible with backup)

### Issue 2: Security Headers
- **Status**: ✅ **COMPLETE**
- **Implementation**: Fully implemented in Headers.php
- **Integration**: Properly called from Admin.php
- **Testing required**: Verify headers in browser dev tools

---

## Quick Reference Commands

### Check Current Status
```powershell
cd wp-content/plugins/affiliate-product-showcase/src
(Get-ChildItem -Recurse -Filter *.php | Where-Object { -not (Get-Content $_.FullName | Select-String "declare\(strict_types=1\);") }).Count
```

### Apply Fix
```powershell
cd wp-content/plugins/affiliate-product-showcase/src
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    if ($content -notmatch "declare\(strict_types=1\);") {
        if ($content -match "<\?php\r?\n") {
            $newContent = $content -replace "(<\?php\r?\n)", "`$1declare(strict_types=1);`r`n"
        } else {
            $newContent = $content -replace "(<\?php)", "`$1`r`ndeclare(strict_types=1);`r`n"
        }
        Set-Content -Path $_.FullName -Value $newContent -NoNewline
        Write-Host "Updated: $($_.FullName)"
    }
}
```

### Verify Fix
```powershell
cd wp-content/plugins/affiliate-product-showcase/src
$withoutStrict = (Get-ChildItem -Recurse -Filter *.php | Where-Object { -not (Get-Content $_.FullName | Select-String "declare\(strict_types=1\);") }).Count
Write-Host "Files without strict_types: $withoutStrict"
```

---

## Additional Notes

### Why declare(strict_types=1); is Important
- **Type Safety**: Catches type mismatches at development time
- **Better Code Quality**: Encourages proper type declarations
- **Performance**: Can lead to minor performance improvements
- **Documentation**: Makes code intent clearer
- **Modern PHP**: Follows modern PHP best practices

### Security Headers Importance
- **CSP**: Prevents XSS attacks by controlling resource sources
- **X-Content-Type-Options**: Prevents MIME sniffing attacks
- **X-Frame-Options**: Prevents clickjacking
- **X-XSS-Protection**: Legacy XSS protection (still useful)
- **Referrer-Policy**: Controls privacy of referrer information
- **Permissions-Policy**: Disables unnecessary browser features

### Common Issues and Solutions

**Issue**: Command fails with "Access Denied"
**Solution**: Run PowerShell as Administrator

**Issue**: Some files still missing strict_types after fix
**Solution**: Check file permissions and try running command again

**Issue**: PHP syntax errors after adding strict_types
**Solution**: Restore from backup and fix type mismatches manually

---

## Support

If you encounter any issues:
1. Check the error messages carefully
2. Verify you have a backup before attempting fix
3. Test on a staging environment first if possible
4. Consult PHP documentation for strict_types: https://www.php.net/manual/en/language.types.declarations.php
5. Consult OWASP for security headers: https://owasp.org/www-project-secure-headers/

---

**Last Updated**: January 14, 2026
**Plugin Version**: 1.0.0
**PHP Version**: 8.0+
