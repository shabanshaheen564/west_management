#!/bin/bash
# ══════════════════════════════════════════════════════════════════════════
#  Smart Waste Management GIS System — Automated Install Script
#  نظام إدارة النفايات الذكي — سكريبت التثبيت التلقائي
# ══════════════════════════════════════════════════════════════════════════

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║    🗑  Smart Waste Management GIS System                ║${NC}"
echo -e "${GREEN}║       نظام إدارة النفايات الذكي                        ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check PHP
echo -e "${CYAN}[1/8] Checking PHP version...${NC}"
php_version=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✓ PHP $php_version${NC}"

# Check Composer
echo -e "${CYAN}[2/8] Checking Composer...${NC}"
composer --version > /dev/null 2>&1 && echo -e "${GREEN}✓ Composer OK${NC}" || { echo -e "${RED}✗ Composer not found${NC}"; exit 1; }

# Copy .env
echo -e "${CYAN}[3/8] Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env created${NC}"
else
    echo -e "${YELLOW}⚠ .env already exists, skipping${NC}"
fi

# Install dependencies
echo -e "${CYAN}[4/8] Installing PHP dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Generate app key
echo -e "${CYAN}[5/8] Generating application key...${NC}"
php artisan key:generate --force
echo -e "${GREEN}✓ App key generated${NC}"

# Database
echo -e "${CYAN}[6/8] Running database migrations...${NC}"
echo -e "${YELLOW}Make sure your .env DB settings are correct!${NC}"
read -p "Press Enter to continue with migrations, or Ctrl+C to cancel..."
php artisan migrate --force
echo -e "${GREEN}✓ Migrations complete${NC}"

# Publish Spatie Permission
echo -e "${CYAN}[6b] Publishing Spatie Permission...${NC}"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force 2>/dev/null
php artisan migrate --force 2>/dev/null
echo -e "${GREEN}✓ Permissions tables created${NC}"

# Seed database
echo -e "${CYAN}[7/8] Seeding sample data...${NC}"
php artisan db:seed --force
echo -e "${GREEN}✓ Sample data seeded${NC}"

# Storage link
echo -e "${CYAN}[8/8] Creating storage link...${NC}"
php artisan storage:link 2>/dev/null || true
echo -e "${GREEN}✓ Storage linked${NC}"

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║              🎉 Installation Complete!                  ║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║                                                          ║${NC}"
echo -e "${GREEN}║  Run: php artisan serve                                  ║${NC}"
echo -e "${GREEN}║  Visit: http://localhost:8000                            ║${NC}"
echo -e "${GREEN}║                                                          ║${NC}"
echo -e "${GREEN}║  Admin Login:                                            ║${NC}"
echo -e "${GREEN}║  Email:    admin@waste.local                             ║${NC}"
echo -e "${GREEN}║  Password: Admin@123456                                  ║${NC}"
echo -e "${GREEN}║                                                          ║${NC}"
echo -e "${YELLOW}║  ⚠ Set ORS_API_KEY in .env for route optimization       ║${NC}"
echo -e "${YELLOW}║    Get free key at: openrouteservice.org                ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
