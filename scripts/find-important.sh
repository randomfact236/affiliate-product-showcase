#!/bin/bash
###############################################################################
# Script to find and count !important declarations in CSS files
#
# This script recursively searches through the project directory,
# finds all CSS files, and counts occurrences of !important declarations.
#
# Usage:
#   ./scripts/find-important.sh [directory]
#
# Arguments:
#   directory: Optional directory to search (default: current directory)
#
# Output:
#   - Total count of !important declarations
#   - Count per file
#   - Line numbers where !important is found
###############################################################################

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default directory to search
SEARCH_DIR="${1:-.}"

# Check if directory exists
if [ ! -d "$SEARCH_DIR" ]; then
    echo -e "${RED}Error: Directory '$SEARCH_DIR' does not exist.${NC}" >&2
    exit 1
fi

echo -e "${BLUE}Searching for CSS files in: $SEARCH_DIR${NC}"
echo

# Find all CSS files (including SCSS, SASS, LESS)
CSS_FILES=$(find "$SEARCH_DIR" -type f \( -name "*.css" -o -name "*.scss" -o -name "*.sass" -o -name "*.less" \) \
    ! -path "*/node_modules/*" \
    ! -path "*/.git/*" \
    ! -path "*/vendor/*" \
    ! -path "*/build/*" \
    ! -path "*/dist/*" \
    ! -path "*/.cache/*" \
    2>/dev/null | sort)

FILE_COUNT=$(echo "$CSS_FILES" | grep -c . || echo "0")

if [ "$FILE_COUNT" -eq 0 ]; then
    echo "No CSS files found."
    exit 0
fi

echo "Found $FILE_COUNT CSS files to analyze."
echo

# Initialize counters
TOTAL_COUNT=0
FILES_WITH_IMPORTANT=0
MAX_FILENAME_LENGTH=0

# First pass: find files with !important and determine max filename length
while IFS= read -r file; do
    if [ -n "$file" ]; then
        # Count !important occurrences (case-insensitive, with optional whitespace)
        COUNT=$(grep -c -iE '!\s*important' "$file" 2>/dev/null || echo "0")
        
        if [ "$COUNT" -gt 0 ]; then
            FILES_WITH_IMPORTANT=$((FILES_WITH_IMPORTANT + 1))
            
            # Get relative path
            REL_PATH="${file#./}"
            
            # Track max filename length for formatting
            FILENAME_LENGTH=${#REL_PATH}
            if [ $FILENAME_LENGTH -gt $MAX_FILENAME_LENGTH ]; then
                MAX_FILENAME_LENGTH=$FILENAME_LENGTH
            fi
        fi
    fi
done <<< "$CSS_FILES"

# Print header
echo "================================================================================"
echo "!IMPORTANT DECLARATION ANALYSIS REPORT"
echo "================================================================================"
echo

if [ "$FILES_WITH_IMPORTANT" -eq 0 ]; then
    echo -e "${GREEN}✓ No !important declarations found!${NC}"
    exit 0
fi

# Second pass: collect and display results
echo "Total files with !important: $FILES_WITH_IMPORTANT"
echo

echo "--------------------------------------------------------------------------------"
echo "DETAILS BY FILE:"
echo "--------------------------------------------------------------------------------"
echo

# Create temporary file for sorting results
TEMP_FILE=$(mktemp)

while IFS= read -r file; do
    if [ -n "$file" ]; then
        COUNT=$(grep -c -iE '!\s*important' "$file" 2>/dev/null || echo "0")
        
        if [ "$COUNT" -gt 0 ]; then
            # Get relative path
            REL_PATH="${file#./}"
            
            # Get line numbers
            LINES=$(grep -n -iE '!\s*important' "$file" 2>/dev/null | cut -d: -f1 | tr '\n' ',' | sed 's/,$//')
            
            # Store in temp file for sorting
            echo "$COUNT|$REL_PATH|$LINES" >> "$TEMP_FILE"
            
            TOTAL_COUNT=$((TOTAL_COUNT + COUNT))
        fi
    fi
done <<< "$CSS_FILES"

# Sort by count (descending) and display
sort -t '|' -k1 -rn "$TEMP_FILE" | while IFS='|' read -r count path lines; do
    echo -e "${BLUE}📄 $path${NC}"
    echo "   Count: $count"
    if [ -n "$lines" ]; then
        echo "   Lines: $lines"
    fi
    echo
done

# Clean up temp file
rm -f "$TEMP_FILE"

echo "--------------------------------------------------------------------------------"
echo "SUMMARY:"
echo "--------------------------------------------------------------------------------"
echo "Files analyzed: $FILE_COUNT"
echo "Files with !important: $FILES_WITH_IMPORTANT"
echo "Total !important declarations: $TOTAL_COUNT"
echo

# Severity assessment
if [ "$TOTAL_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✅ EXCELLENT: No !important declarations found!${NC}"
elif [ "$TOTAL_COUNT" -lt 10 ]; then
    echo -e "${YELLOW}⚠️  LOW: Minimal use of !important declarations.${NC}"
elif [ "$TOTAL_COUNT" -lt 50 ]; then
    echo -e "${YELLOW}⚠️  MODERATE: Consider reducing !important usage.${NC}"
else
    echo -e "${RED}❌ HIGH: Excessive use of !important! This can lead to CSS specificity issues.${NC}"
fi
echo
