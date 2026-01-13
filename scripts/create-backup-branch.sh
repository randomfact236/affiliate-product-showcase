#!/bin/bash

# Script to create a backup branch with date and timestamp
# Usage: ./scripts/create-backup-branch.sh [branch_prefix]

set -e

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get current date and time in format YYYY-MM-DD-HHMMSS
DATETIME=$(date +"%Y-%m-%d-%H%M%S")

# Default branch prefix if none provided
DEFAULT_PREFIX="backup"

# Get branch prefix from argument or use default
BRANCH_PREFIX=${1:-$DEFAULT_PREFIX}

# Create branch name with date and time
BRANCH_NAME="${BRANCH_PREFIX}-${DATETIME}"

# Function to print colored output
print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_error "Not a git repository. Please run this script from within a git repository."
    exit 1
fi

# Get current branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

print_info "Current branch: ${CURRENT_BRANCH}"
print_info "Creating backup branch: ${BRANCH_NAME}"

# Check if branch already exists
if git show-ref --verify --quiet refs/heads/"${BRANCH_NAME}"; then
    print_warning "Branch ${BRANCH_NAME} already exists locally."
    read -p "Do you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Operation cancelled."
        exit 1
    fi
fi

# Create the new branch
print_info "Creating new branch from current state..."
git checkout -b "${BRANCH_NAME}"

# Push to remote with upstream tracking
print_info "Pushing branch to remote..."
git push -u origin "${BRANCH_NAME}" --no-verify

# Switch back to original branch
print_info "Switching back to ${CURRENT_BRANCH}..."
git checkout "${CURRENT_BRANCH}"

print_success "Backup branch created successfully!"
echo ""
echo -e "${GREEN}Backup Details:${NC}"
echo -e "  Branch Name: ${BLUE}${BRANCH_NAME}${NC}"
echo -e "  Remote: ${BLUE}origin/${BRANCH_NAME}${NC}"
echo -e "  Date/Time: ${BLUE}${DATETIME}${NC}"
echo -e "  Based on: ${BLUE}${CURRENT_BRANCH}${NC}"
echo ""
print_info "To view all backup branches: git branch -a | grep backup"
print_info "To delete this backup: git branch -D ${BRANCH_NAME} && git push origin --delete ${BRANCH_NAME}"
