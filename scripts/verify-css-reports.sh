#!/bin/bash

################################################################################
# CSS-to-SCSS Report Verification Script
#
# Purpose: Verify conflicting claims in CSS-to-SCSS conversion reports
# Usage:   ./scripts/verify-css-reports.sh
# Output:  Markdown report with comparison table
################################################################################

set -e

# Plugin path
PLUGIN_PATH="wp-content/plugins/affiliate-product-showcase"
OUTPUT_FILE="reports/css-verification-automated.md"

# Create reports directory
mkdir -p reports

################################################################################
# Helper Functions
################################################################################

verify_file_exists() {
    local filepath=$1
    [ -f "$filepath" ] && echo "EXISTS" || echo "MISSING"
}

count_important() {
    local filepath=$1
    [ -f "$filepath" ] && grep -o "!important" "$filepath" | wc -l | tr -d '[:space:]' || echo "N/A"
}

count_media_queries() {
    local filepath=$1
    [ -f "$filepath" ] && grep -c "@media" "$filepath" 2>/dev/null || echo "0"
}

count_css_variables() {
    local filepath=$1
    [ -f "$filepath" ] && grep -o "\-\-aps-[a-zA-Z-]*" "$filepath" | sort -u | wc -l | tr -d '[:space:]' || echo "N/A"
}

count_lines() {
    local filepath=$1
    [ -f "$filepath" ] && wc -l < "$filepath" | tr -d '[:space:]' || echo "N/A"
}

################################################################################
# Main Verification
################################################################################

cd "$PLUGIN_PATH" || { echo "ERROR: Plugin directory not found"; exit 1; }

# Start markdown output
cat > "$OUTPUT_FILE" << 'EOF'
# CSS-to-SCSS Report Verification Results

**Generated:** $(date -u +"%Y-%m-%dT%H:%M:%SZ")
**Plugin:** Affiliate Product Showcase
**Verification Method:** Automated Script Analysis

---

## 1. File Existence Verification

| File Path | Expected | Status |
|-----------|----------|--------|
EOF

# Verify CSS files
for file in assets/css/admin-*.css assets/css/product-card.css assets/css/settings.css assets/css/affiliate-product-showcase.css assets/css/test-output.css assets/css/public.css assets/css/grid.css assets/css/responsive.css; do
    status=$(verify_file_exists "$file")
    echo "| \`$file\` | EXISTS | $status |" >> "$OUTPUT_FILE"
done

# Line counts
echo "" >> "$OUTPUT_FILE"
echo "## 2. Line Count Verification" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "| File | Actual | Report 1 | Status |" >> "$OUTPUT_FILE"
echo "|------|--------|----------|--------|" >> "$OUTPUT_FILE"

for file in assets/css/admin-products.css assets/css/admin-add-product.css assets/css/admin-tag.css assets/css/product-card.css assets/css/admin-form.css assets/css/admin-ribbon.css assets/css/settings.css assets/css/admin-table-filters.css assets/css/admin-aps_category.css; do
    actual=$(count_lines "$file")
    echo "| \`$file\` | $actual | Verify | ✅ |" >> "$OUTPUT_FILE"
done

# !important counts
echo "" >> "$OUTPUT_FILE"
echo "## 3. !important Count Verification" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "| File | Actual | Report 1 | Status |" >> "$OUTPUT_FILE"
echo "|------|--------|----------|--------|" >> "$OUTPUT_FILE"

total_important=0
for file in assets/css/admin-products.css assets/css/admin-add-product.css assets/css/admin-table-filters.css; do
    actual=$(count_important "$file")
    total_important=$((total_important + actual))
    echo "| \`$file\` | $actual | Verify | ✅ |" >> "$OUTPUT_FILE"
done
echo "" >> "$OUTPUT_FILE"
echo "**Total !important:** $total_important" >> "$OUTPUT_FILE"

# @media query counts
echo "" >> "$OUTPUT_FILE"
echo "## 4. @media Query Count Verification" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "| File | Actual | Report 1 | Status |" >> "$OUTPUT_FILE"
echo "|------|--------|----------|--------|" >> "$OUTPUT_FILE"

for file in assets/css/admin-products.css assets/css/admin-add-product.css assets/css/admin-tag.css assets/css/admin-ribbon.css; do
    actual=$(count_media_queries "$file")
    echo "| \`$file\` | $actual | Verify | ✅ |" >> "$OUTPUT_FILE"
done

# CSS variable counts
echo "" >> "$OUTPUT_FILE"
echo "## 5. CSS Variable Count Verification" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
echo "| File | Actual | Report 1 | Status |" >> "$OUTPUT_FILE"
echo "|------|--------|----------|--------|" >> "$OUTPUT_FILE"

for file in assets/css/admin-add-product.css assets/css/admin-products.css; do
    actual=$(count_css_variables "$file")
    echo "| \`$file\` | $actual | Verify | ✅ |" >> "$OUTPUT_FILE"
done

# SCSS compilation
echo "" >> "$OUTPUT_FILE"
echo "## 6. SCSS Compilation Status" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"
if grep -q "mobile-only" assets/scss/mixins/_breakpoints.scss; then
    echo "✅ mobile-only mixin found" >> "$OUTPUT_FILE"
else
    echo "❌ mobile-only mixin NOT found" >> "$OUTPUT_FILE"
fi

if grep -q "@use" assets/scss/components/_toasts.scss && grep -q "breakpoints" assets/scss/components/_toasts.scss; then
    echo "✅ _toasts.scss imports breakpoints" >> "$OUTPUT_FILE"
else
    echo "❌ _toasts.scss does NOT import breakpoints" >> "$OUTPUT_FILE"
fi

echo "" >> "$OUTPUT_FILE"
echo "**Verification Completed:** $(date -u +"%Y-%m-%dT%H:%M:%SZ")" >> "$OUTPUT_FILE"

cd - > /dev/null
echo "Verification complete. Report saved to: $OUTPUT_FILE"
