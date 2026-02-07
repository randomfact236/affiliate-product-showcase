@echo off
echo ========================================================
echo AFFILIATE PRODUCT SHOWCASE - COMPREHENSIVE SECURITY SCAN
echo ========================================================
echo.

echo [1/11] Running PHPStan (Static Analysis)...
vendor\bin\phpstan analyse --memory-limit=1G
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHPStan found issues!
) else (
    echo ✅ PHPStan passed
)
echo.

echo [2/11] Running Psalm (Type Checking)...
vendor\bin\psalm --config=psalm.xml.dist --show-info=false --threads=4
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Psalm found issues!
) else (
    echo ✅ Psalm passed
)
echo.

echo [3/11] Running PHPCS (WordPress Standards)...
vendor\bin\phpcs --standard=WordPress --extensions=php --colors src/
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHPCS found issues!
) else (
    echo ✅ PHPCS passed
)
echo.

echo [4/11] Running Composer Audit (Security Vulnerabilities)...
composer audit
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Composer audit found vulnerabilities!
) else (
    echo ✅ Composer audit passed
)
echo.

echo [5/11] Running NPM Audit (JS Security)...
npm audit
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️  NPM audit found issues (check output)
) else (
    echo ✅ NPM audit passed
)
echo.

echo [6/11] Running ESLint (JavaScript Linting)...
npm run lint:js
if %ERRORLEVEL% NEQ 0 (
    echo ❌ ESLint found issues!
) else (
    echo ✅ ESLint passed
)
echo.

echo [7/11] Running Stylelint (CSS Linting)...
npm run lint:css
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Stylelint found issues!
) else (
    echo ✅ Stylelint passed
)
echo.

echo [8/11] Running PHPUnit (Unit Tests)...
vendor\bin\phpunit --configuration phpunit.xml.dist --coverage-text
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHPUnit tests failed!
) else (
    echo ✅ PHPUnit tests passed
)
echo.

echo [9/11] Running Custom Security Checks...
echo Checking for missing nonces...
findstr /S /N "register_rest_route" wp-content\plugins\affiliate-product-showcase\src\Rest\*.php
echo.
echo Checking for missing capability checks...
findstr /S /N "permissions_check" wp-content\plugins\affiliate-product-showcase\src\Rest\*.php
echo.
echo Checking for unsanitized input...
findstr /S /N "\$_GET\|\$_POST\|\$_REQUEST" wp-content\plugins\affiliate-product-showcase\src\*.php
echo.
echo Checking for unescaped output...
findstr /S /N "echo\|print" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for SQL injection patterns...
findstr /S /N "prepare\|get_var\|get_results" wp-content\plugins\affiliate-product-showcase\src\Services\*.php
echo.
echo ✅ Custom security checks complete
echo.

echo [10/11] Running WordPress Compliance Checks...
echo Checking for direct file access protection...
findstr /S /N "ABSPATH" wp-content\plugins\affiliate-product-showcase\src\*.php
echo.
echo Checking for uninstall cleanup...
if exist "wp-content\plugins\affiliate-product-showcase\uninstall.php" (
    echo ✅ uninstall.php exists
) else (
    echo ⚠️  uninstall.php missing
)
echo.
echo Checking for transient usage...
findstr /S /N "set_transient\|get_transient\|delete_transient" wp-content\plugins\affiliate-product-showcase\src\*.php
echo.
echo Checking for hook usage...
findstr /S /N "add_action\|add_filter" wp-content\plugins\affiliate-product-showcase\src\*.php
echo.
echo ✅ WordPress compliance checks complete
echo.

echo [11/11] Running Accessibility Checks...
echo Checking for semantic HTML structure...
findstr /S /N "<header\|<nav\|<main\|<article\|<section\|<aside\|<footer" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for alt text on images...
findstr /S /N "alt=" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for ARIA attributes...
findstr /S /N "aria-\|role=" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for form labels...
findstr /S /N "<label\|aria-label\|aria-labelledby" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for skip links...
findstr /S /N "skip-link\|skip to main" wp-content\plugins\affiliate-product-showcase\src\Public\*.php
echo.
echo Checking for focus indicators...
findstr /S /N "focus-visible\|:focus" wp-content\plugins\affiliate-product-showcase\assets\css\*.css
echo.
echo Checking for color contrast issues...
echo Note: Manual verification required for color contrast ratios
echo.
echo ✅ Accessibility checks complete
echo.

echo ========================================================
echo SCAN COMPLETE
echo ========================================================
echo.
echo Summary:
echo - PHP Analysis: PHPStan, Psalm, PHPCS
echo - Security: Composer Audit, NPM Audit, Custom Checks
echo - Frontend: ESLint, Stylelint
echo - Testing: PHPUnit
echo - WordPress Compliance: Hooks, Transients, Uninstall
echo - Accessibility: Semantic HTML, ARIA, Forms, Focus
echo.
echo Note: Some accessibility checks (color contrast, keyboard navigation, 
echo screen reader compatibility) require manual testing with tools like:
echo - axe DevTools browser extension
echo - WAVE accessibility tool
echo - Manual keyboard navigation testing
echo - Screen reader testing (NVDA, JAWS, VoiceOver)
echo.
pause
