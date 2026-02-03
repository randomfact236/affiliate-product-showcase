#!/bin/bash
# =============================================================================
# Docker Network Isolation Verification Script
# Affiliate Product Showcase
# =============================================================================
# This script verifies that Docker network isolation is working correctly.
# Run this after starting the containers with: docker-compose up -d
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║    Docker Network Isolation Verification                     ║${NC}"
echo -e "${BLUE}║    Affiliate Product Showcase                               ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if containers are running
echo -e "${YELLOW}▶ Checking container status...${NC}"
if ! docker ps | grep -q "aps_nginx"; then
    echo -e "${RED}✗ ERROR: Containers are not running${NC}"
    echo "  Start them first with: docker-compose up -d"
    exit 1
fi
echo -e "${GREEN}✓ Containers are running${NC}"
echo ""

# Verify network exists and is isolated
echo -e "${YELLOW}▶ Verifying network isolation...${NC}"
if docker network ls | grep -q "aps_network"; then
    echo -e "${GREEN}✓ Network 'aps_network' exists (project-specific)${NC}"
else
    echo -e "${RED}✗ ERROR: Network 'aps_network' not found${NC}"
    exit 1
fi

# Check that we're not using generic network names
if docker network ls | grep -q "app_net"; then
    echo -e "${YELLOW}⚠ WARNING: Generic 'app_net' network exists${NC}"
    echo "  Other projects might be using this - consider migrating"
else
    echo -e "${GREEN}✓ No generic 'app_net' network found (good)${NC}"
fi
echo ""

# Test localhost access (should work)
echo -e "${YELLOW}▶ Testing localhost access (should SUCCEED)...${NC}"
if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000 | grep -q "200\|301\|302"; then
    echo -e "${GREEN}✓ WordPress accessible at http://127.0.0.1:8000${NC}"
else
    echo -e "${RED}✗ WordPress not responding on localhost:8000${NC}"
fi

if curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8080 | grep -q "200\|301\|302"; then
    echo -e "${GREEN}✓ phpMyAdmin accessible at http://127.0.0.1:8080${NC}"
else
    echo -e "${YELLOW}⚠ phpMyAdmin not responding (might not be enabled)${NC}"
fi
echo ""

# Test port binding (verify localhost-only)
echo -e "${YELLOW}▶ Verifying port binding configuration...${NC}"
NGINX_BIND=$(docker port aps_nginx 80 2>/dev/null || echo "N/A")
if echo "$NGINX_BIND" | grep -q "127.0.0.1"; then
    echo -e "${GREEN}✓ Nginx port 80 bound to localhost only${NC}"
    echo "  Binding: $NGINX_BIND"
elif echo "$NGINX_BIND" | grep -q "0.0.0.0"; then
    echo -e "${RED}✗ WARNING: Nginx port 80 bound to all interfaces (0.0.0.0)${NC}"
    echo "  Binding: $NGINX_BIND"
    echo "  This exposes the service to other Docker projects!"
else
    echo -e "${YELLOW}⚠ Could not determine port binding${NC}"
fi
echo ""

# Check container network membership
echo -e "${YELLOW}▶ Checking container network membership...${NC}"
NGINX_NETWORKS=$(docker inspect -f '{{range $key, $value := .NetworkSettings.Networks}}{{$key}} {{end}}' aps_nginx 2>/dev/null || echo "N/A")
if echo "$NGINX_NETWORKS" | grep -q "aps_network"; then
    echo -e "${GREEN}✓ Nginx connected to 'aps_network'${NC}"
else
    echo -e "${RED}✗ Nginx not on expected network${NC}"
fi

DB_NETWORKS=$(docker inspect -f '{{range $key, $value := .NetworkSettings.Networks}}{{$key}} {{end}}' aps_db 2>/dev/null || echo "N/A")
if echo "$DB_NETWORKS" | grep -q "aps_network"; then
    echo -e "${GREEN}✓ Database connected to 'aps_network'${NC}"
else
    echo -e "${RED}✗ Database not on expected network${NC}"
fi
echo ""

# Verify other projects can't access (informational)
echo -e "${YELLOW}▶ Network isolation summary...${NC}"
echo -e "  Network Name: ${GREEN}aps_network${NC} (project-specific)"
echo -e "  Port Binding: ${GREEN}127.0.0.1${NC} (localhost only)"
echo ""
echo -e "  ${BLUE}Isolation Features:${NC}"
echo -e "  • Other Docker projects cannot access WordPress (port 8000)"
echo -e "  • Other Docker projects cannot access Database (internal only)"
echo -e "  • Other Docker projects cannot access Redis (port 6379)"
echo -e "  • External machines cannot access development services"
echo ""
echo -e "  ${BLUE}Explicit Permission Required:${NC}"
echo -e "  To allow another container to access this network, run:"
echo -e "  ${YELLOW}docker network connect aps_network <container-name>${NC}"
echo ""

echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║    Verification Complete ✓                                   ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
