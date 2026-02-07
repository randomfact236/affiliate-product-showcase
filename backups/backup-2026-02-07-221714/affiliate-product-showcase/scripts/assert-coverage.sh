#!/bin/bash

###############################################################################
# Coverage Assertion Script
# 
# Runs PHPUnit test coverage and asserts minimum threshold of 95%
# Blocks push if coverage is below threshold
###############################################################################

set -e

# Configuration
THRESHOLD=95
PLUGIN_DIR="wp-content/plugins/affiliate-product-showcase"
COVERAGE_FILE="$(dirname "$0")/../tests/coverage.txt"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}🔍 Running test coverage analysis...${NC}"

# Navigate to project root (3 levels up from plugin)
cd "$(dirname "$0")/../../.."

# Run PHPUnit with coverage
if ! composer --working-dir="${PLUGIN_DIR}" test:coverage -- --coverage-text="${COVERAGE_FILE}" 2>/dev/null; then
  echo -e "${RED}❌ Error: Tests failed${NC}"
  rm -f "${COVERAGE_FILE}"
  exit 1
fi

# Check if coverage file exists
if [ ! -f "${COVERAGE_FILE}" ]; then
  echo -e "${RED}❌ Error: Coverage file not generated${NC}"
  exit 1
fi

# Parse coverage percentage from PHPUnit output
COVERAGE=$(grep -oP 'Lines:\s+\K[\d\.]+(?=%)' "${COVERAGE_FILE}" | head -1)

# Remove coverage file
rm -f "${COVERAGE_FILE}"

# Validate coverage value
if [ -z "$COVERAGE" ]; then
  echo -e "${RED}❌ Error: Could not parse coverage percentage${NC}"
  echo -e "${YELLOW}Please ensure PHPUnit is configured to output text coverage${NC}"
  exit 1
fi

# Convert to integer for comparison
COVERAGE_INT=${COVERAGE%.*}

echo -e "\n${CYAN}📊 Coverage Report:${NC}"
echo -e "  Current coverage: ${GREEN}${COVERAGE}%${NC}"
echo -e "  Required threshold: ${YELLOW}${THRESHOLD}%${NC}"

# Compare coverage
if [ "$COVERAGE_INT" -lt "$THRESHOLD" ]; then
  DIFF=$((THRESHOLD - COVERAGE_INT))
  echo -e "\n${RED}❌ Coverage ${COVERAGE}% is below threshold ${THRESHOLD}%${NC}"
  echo -e "${YELLOW}You need to increase coverage by ${DIFF}% before pushing${NC}"
  echo -e "${CYAN}Tip: Run 'composer test:coverage' to see detailed coverage report${NC}\n"
  exit 1
fi

echo -e "\n${GREEN}✅ Coverage threshold met! (${COVERAGE}% ≥ ${THRESHOLD}%)${NC}\n"
exit 0
