#!/bin/bash

# Accessibility Testing Script for Affiliate Product Showcase
# This script runs automated accessibility tests using Pa11y CI

set -e

echo "=========================================="
echo "Accessibility Testing Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if npm is available
check_npm() {
    if ! command -v npm &> /dev/null; then
        echo -e "${RED}Error: npm is not installed or not in PATH${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓${NC} npm found"
}

# Function to check if Pa11y CI is installed
check_pa11y() {
    if ! command -v pa11y-ci &> /dev/null; then
        echo -e "${YELLOW}Pa11y CI not found. Installing...${NC}"
        npm install -g pa11y-ci
        echo -e "${GREEN}✓${NC} Pa11y CI installed"
    else
        echo -e "${GREEN}✓${NC} Pa11y CI found"
    fi
}

# Function to check if WordPress is running
check_wordpress() {
    local base_url=${WP_BASE_URL:-"http://localhost:8000"}
    
    echo "Checking WordPress at $base_url..."
    
    if curl -s --head --request GET "$base_url" | grep "200 OK" > /dev/null; then
        echo -e "${GREEN}✓${NC} WordPress is running"
    else
        echo -e "${RED}Error: WordPress is not running at $base_url${NC}"
        echo "Please start WordPress and set WP_BASE_URL environment variable if needed"
        exit 1
    fi
}

# Function to generate test URLs
generate_test_urls() {
    local base_url=${WP_BASE_URL:-"http://localhost:8000"}
    
    echo "Generating test URLs..."
    
    # Create temporary URLs file
    cat > .pa11y-ci.urls <<EOF
$base_url/wp-admin/admin.php?page=affiliate-product-showcase
$base_url/products/
EOF
    
    # Try to find a sample product page
    if curl -s "$base_url/products/" | grep -q "class=\"product"; then
        # Extract first product link
        first_product=$(curl -s "$base_url/products/" | grep -oP 'href="[^"]*product/[^"]*"' | head -1 | cut -d'"' -f2)
        if [ ! -z "$first_product" ]; then
            echo "$first_product" >> .pa11y-ci.urls
        fi
    fi
    
    echo -e "${GREEN}✓${NC} Test URLs generated"
}

# Function to run accessibility tests
run_tests() {
    echo ""
    echo "=========================================="
    echo "Running Accessibility Tests"
    echo "=========================================="
    echo ""
    
    # Create output directory
    mkdir -p accessibility-reports
    mkdir -p accessibility-screenshots
    
    # Run Pa11y CI with configuration
    npx pa11y-ci \
        --config .a11y.json \
        --reporter json \
        --reporter cli \
        --sitemap false || true
    
    # Check if tests passed
    if [ -f "pa11y-ci-report.json" ]; then
        echo ""
        echo "=========================================="
        echo "Test Results Summary"
        echo "=========================================="
        
        # Count errors
        total_issues=$(jq '[.results[].issues[]] | length' pa11y-ci-report.json 2>/dev/null || echo "0")
        errors=$(jq '[.results[].issues[] | select(.type=="error")] | length' pa11y-ci-report.json 2>/dev/null || echo "0")
        warnings=$(jq '[.results[].issues[] | select(.type=="warning")] | length' pa11y-ci-report.json 2>/dev/null || echo "0")
        notices=$(jq '[.results[].issues[] | select(.type=="notice")] | length' pa11y-ci-report.json 2>/dev/null || echo "0")
        
        echo "Total Issues: $total_issues"
        echo "Errors: $errors"
        echo "Warnings: $warnings"
        echo "Notices: $notices"
        echo ""
        
        # Move report to accessibility-reports
        mv pa11y-ci-report.json accessibility-reports/
        
        if [ "$errors" -gt 0 ]; then
            echo -e "${RED}✗${NC} Accessibility tests FAILED - $errors error(s) found"
            echo "Full report: accessibility-reports/pa11y-ci-report.json"
            return 1
        else
            echo -e "${GREEN}✓${NC} Accessibility tests PASSED - No errors found"
            return 0
        fi
    else
        echo -e "${YELLOW}Warning: No report generated${NC}"
        return 0
    fi
}

# Function to generate detailed report
generate_report() {
    echo ""
    echo "=========================================="
    echo "Generating Detailed Report"
    echo "=========================================="
    
    if [ -f "accessibility-reports/pa11y-ci-report.json" ]; then
        # Generate HTML report
        cat > accessibility-reports/report.html <<'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessibility Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        .summary { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .issue { background: white; padding: 15px; margin-bottom: 10px; border-left: 4px solid #ccc; }
        .error { border-left-color: #dc3545; }
        .warning { border-left-color: #ffc107; }
        .notice { border-left-color: #17a2b8; }
        .url { color: #666; font-size: 14px; margin-top: 10px; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Accessibility Test Report</h1>
    <p>Generated: $(date)</p>
EOF
        
        # Add summary
        cat >> accessibility-reports/report.html <<EOF
    <div class="summary">
        <h2>Summary</h2>
        <ul>
            <li>Total Pages Tested: $(jq '.results | length' accessibility-reports/pa11y-ci-report.json)</li>
            <li>Total Issues: $(jq '[.results[].issues[]] | length' accessibility-reports/pa11y-ci-report.json)</li>
            <li>Errors: $(jq '[.results[].issues[] | select(.type=="error")] | length' accessibility-reports/pa11y-ci-report.json)</li>
            <li>Warnings: $(jq '[.results[].issues[] | select(.type=="warning")] | length' accessibility-reports/pa11y-ci-report.json)</li>
            <li>Notices: $(jq '[.results[].issues[] | select(.type=="notice")] | length' accessibility-reports/pa11y-ci-report.json)</li>
        </ul>
    </div>
EOF
        
        # Add issues
        cat >> accessibility-reports/report.html <<'EOF'
    <h2>Issues Found</h2>
EOF
        
        jq -r '.results[] | 
            "<div class=\"\">
                <h3>\(.pageUrl)</h3>
                "\(.issues[] | 
                    "<div class=\"issue \(.type)\">
                        <h4>\(.code) - \(.message)</h4>
                        <p><strong>Type:</strong> \(.type | ascii_upcase)</p>
                        <p><strong>Context:</strong> <code>\(.context // "N/A")</code></p>
                        <p><strong>Selector:</strong> <code>\(.selector // "N/A")</code></p>
                    </div>"
                )
            </div>"
        ' accessibility-reports/pa11y-ci-report.json >> accessibility-reports/report.html
        
        cat >> accessibility-reports/report.html <<'EOF'
</body>
</html>
EOF
        
        echo -e "${GREEN}✓${NC} HTML report generated: accessibility-reports/report.html"
    fi
}

# Function to verify test results
verify_results() {
    echo ""
    echo "=========================================="
    echo "Verification"
    echo "=========================================="
    
    if [ -f "accessibility-reports/pa11y-ci-report.json" ]; then
        errors=$(jq '[.results[].issues[] | select(.type=="error")] | length' accessibility-reports/pa11y-ci-report.json 2>/dev/null || echo "0")
        
        if [ "$errors" -eq 0 ]; then
            echo -e "${GREEN}✓ VERIFICATION PASSED${NC}"
            echo "No accessibility errors found"
            return 0
        else
            echo -e "${RED}✗ VERIFICATION FAILED${NC}"
            echo "Found $errors accessibility error(s)"
            echo ""
            echo "Top 5 critical issues:"
            jq -r '.results[].issues[] | select(.type=="error") | "  - \(.code): \(.message)"' accessibility-reports/pa11y-ci-report.json | head -5
            return 1
        fi
    else
        echo -e "${RED}✗ VERIFICATION FAILED${NC}"
        echo "No test results found"
        return 1
    fi
}

# Main execution
check_npm
check_pa11y

case "${1:-test}" in
    test)
        check_wordpress
        generate_test_urls
        run_tests
        generate_report
        verify_results
        ;;
    verify)
        verify_results
        ;;
    report)
        generate_report
        ;;
    ci)
        check_wordpress
        generate_test_urls
        run_tests
        generate_report
        verify_results
        exit $?
        ;;
    *)
        echo "Usage: $0 {test|verify|report|ci}"
        echo ""
        echo "Commands:"
        echo "  test    Run full accessibility test suite (default)"
        echo "  verify  Verify existing test results"
        echo "  report  Generate detailed HTML report from existing results"
        echo "  ci      Run tests in CI mode (exit with error if tests fail)"
        echo ""
        echo "Environment Variables:"
        echo "  WP_BASE_URL  Base URL of WordPress installation (default: http://localhost:8000)"
        echo ""
        exit 1
        ;;
esac
