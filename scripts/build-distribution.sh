#!/bin/bash

# Affiliate Product Showcase - Distribution Build Script
# Creates a distribution package including compiled assets for WordPress.org marketplace

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Distribution Build Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Get plugin directory
PLUGIN_DIR="wp-content/plugins/affiliate-product-showcase"
DIST_DIR="dist"
DIST_ZIP="affiliate-product-showcase-${1:-latest}.zip"

# Check if plugin directory exists
if [ ! -d "$PLUGIN_DIR" ]; then
    echo -e "${RED}Error: Plugin directory not found: $PLUGIN_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Building assets...${NC}"
cd "$PLUGIN_DIR"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${RED}Error: npm is not installed${NC}"
    exit 1
fi

# Build production assets
npm run build
echo -e "${GREEN}✓ Assets built successfully${NC}"
echo ""

cd ../..

echo -e "${YELLOW}Step 2: Creating distribution package...${NC}"

# Clean previous distribution
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

# Copy plugin files to distribution directory
echo "Copying plugin files..."
cp -r "$PLUGIN_DIR" "$DIST_DIR/"

# Create distribution package
echo "Creating zip archive..."
cd "$DIST_DIR"

# Create zip excluding development files
zip -r "../$DIST_ZIP" "affiliate-product-showcase" \
    -x "affiliate-product-showcase/node_modules/*" \
    -x "affiliate-product-showcase/.git/*" \
    -x "affiliate-product-showcase/.gitignore" \
    -x "affiliate-product-showcase/.env.example" \
    -x "affiliate-product-showcase/.DS_Store" \
    -x "affiliate-product-showcase/*.log" \
    -x "affiliate-product-showcase/tests/*" \
    -x "affiliate-product-showcase/.vscode/*" \
    -x "affiliate-product-showcase/.idea/*" \
    -x "affiliate-product-showcase/tsconfig.json" \
    -x "affiliate-product-showcase/vite.config.js" \
    -x "affiliate-product-showcase/tailwind.config.js" \
    -x "affiliate-product-showcase/postcss.config.js" \
    -x "affiliate-product-showcase/package.json" \
    -x "affiliate-product-showcase/package-lock.json"

cd ..

# Verify zip was created
if [ ! -f "$DIST_ZIP" ]; then
    echo -e "${RED}Error: Distribution package was not created${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Distribution package created: $DIST_ZIP${NC}"
echo ""

# Get file size
FILE_SIZE=$(du -h "$DIST_ZIP" | cut -f1)
echo "Package size: $FILE_SIZE"

# List package contents
echo ""
echo -e "${YELLOW}Package contents:${NC}"
unzip -l "$DIST_ZIP" | head -20

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Build Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Distribution package: $DIST_ZIP"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Upload $DIST_ZIP to WordPress.org"
echo "2. Test the plugin from the downloaded package"
echo "3. Update version number and changelog if needed"
echo ""
