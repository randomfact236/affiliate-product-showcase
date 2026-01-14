#!/bin/bash

# Autoload Optimization Script for Affiliate Product Showcase
# This script optimizes the Composer autoloader for better performance

set -e

echo "=========================================="
echo "Autoload Optimization Script"
echo "=========================================="
echo ""

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not installed or not in PATH"
    exit 1
fi

# Function to check PHP version
check_php_version() {
    local required_version="8.1"
    local php_version=$(php -r 'echo PHP_VERSION;' | cut -d'.' -f1,2)
    
    echo "PHP Version: $php_version"
    echo "Required: $required_version or higher"
    
    if [ "$(printf '%s\n' "$required_version" "$php_version" | sort -V | head -n1)" != "$required_version" ]; then
        echo "Error: PHP version $php_version is not supported. Minimum required: $required_version"
        exit 1
    fi
    echo "✓ PHP version check passed"
    echo ""
}

# Function to generate optimized autoloader
generate_optimized_autoloader() {
    echo "Generating optimized autoloader..."
    
    # Optimized autoloader with classmap-authoritative
    # This option generates a classmap and disables the PSR-4 fallback
    # This provides the fastest autoloading possible
    composer dump-autoload \
        --optimize \
        --classmap-authoritative \
        --no-dev \
        --apcu
    
    echo "✓ Optimized autoloader generated"
    echo ""
}

# Function to generate development autoloader
generate_dev_autoloader() {
    echo "Generating development autoloader..."
    
    # Development autoloader with optimization but no classmap-authoritative
    # This allows for dynamic class loading during development
    composer dump-autoload --optimize
    
    echo "✓ Development autoloader generated"
    echo ""
}

# Function to verify autoload optimization
verify_optimization() {
    echo "Verifying autoload optimization..."
    
    # Check if autoload_classmap.php exists
    if [ ! -f "vendor/composer/autoload_classmap.php" ]; then
        echo "Error: autoload_classmap.php not found"
        exit 1
    fi
    
    # Count number of classes in classmap
    class_count=$(grep -c "^    '.*' => " vendor/composer/autoload_classmap.php || echo "0")
    echo "✓ Found $class_count classes in autoload classmap"
    
    # Check if autoload_static.php exists (indicates optimization)
    if [ -f "vendor/composer/autoload_static.php" ]; then
        echo "✓ Static autoload file present (optimization active)"
    fi
    
    # Check if APCu prefix is set
    if grep -q "APCu prefix" vendor/composer/autoload_real.php; then
        echo "✓ APCu prefix configured (runtime caching enabled)"
    fi
    
    echo ""
}

# Function to show autoload statistics
show_statistics() {
    echo "Autoload Statistics:"
    echo "--------------------"
    
    if [ -f "vendor/composer/autoload_classmap.php" ]; then
        echo "Classmap size: $(wc -l < vendor/composer/autoload_classmap.php) lines"
    fi
    
    if [ -f "vendor/composer/autoload_files.php" ]; then
        echo "Files to autoload: $(wc -l < vendor/composer/autoload_files.php) lines"
    fi
    
    if [ -f "vendor/composer/autoload_namespaces.php" ]; then
        echo "Namespaces: $(wc -l < vendor/composer/autoload_namespaces.php) lines"
    fi
    
    echo ""
}

# Function to clear autoload cache
clear_cache() {
    echo "Clearing autoload caches..."
    
    # Remove composer autoload cache
    rm -rf vendor/composer/cache/
    
    # Clear APCu cache if available
    if php -r "exit(function_exists('apcu_clear_cache') ? 0 : 1);"; then
        php -r "apcu_clear_cache();"
        echo "✓ APCu cache cleared"
    fi
    
    echo "✓ Composer cache cleared"
    echo ""
}

# Main execution
check_php_version

case "${1:-optimize}" in
    optimize)
        generate_optimized_autoloader
        verify_optimization
        show_statistics
        ;;
    dev)
        generate_dev_autoloader
        verify_optimization
        show_statistics
        ;;
    verify)
        verify_optimization
        show_statistics
        ;;
    clear)
        clear_cache
        generate_optimized_autoloader
        verify_optimization
        ;;
    *)
        echo "Usage: $0 {optimize|dev|verify|clear}"
        echo ""
        echo "Commands:"
        echo "  optimize  Generate production-ready optimized autoloader (default)"
        echo "  dev       Generate development autoloader (with PSR-4 fallback)"
        echo "  verify    Verify current autoload optimization status"
        echo "  clear     Clear caches and regenerate autoloader"
        echo ""
        exit 1
        ;;
esac

echo "=========================================="
echo "Autoload optimization completed successfully"
echo "=========================================="
