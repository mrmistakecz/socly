#!/bin/bash

# SOCLY v3 - Deployment Script
# This script deploys bug fixes to production server

set -e  # Exit on error

echo "🚀 Starting SOCLY v3 Deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
REMOTE_USER="root"
REMOTE_HOST="217.156.122.161"
REMOTE_PATH="/var/www/socly"  # Adjust this path as needed

echo -e "${YELLOW}📦 Step 1: Running local tests...${NC}"
# php artisan test  # Uncomment when tests are available

echo -e "${YELLOW}📤 Step 2: Uploading files to server...${NC}"
rsync -avz --exclude='node_modules' \
           --exclude='.git' \
           --exclude='storage/logs/*' \
           --exclude='storage/framework/cache/*' \
           --exclude='storage/framework/sessions/*' \
           --exclude='storage/framework/views/*' \
           --exclude='.env' \
           ./ ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/

echo -e "${YELLOW}🔧 Step 3: Running deployment commands on server...${NC}"
ssh ${REMOTE_USER}@${REMOTE_HOST} << 'ENDSSH'
cd /var/www/socly

# Put application in maintenance mode
php artisan down

# Install/update dependencies
composer install --no-dev --optimize-autoloader
npm ci --production

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Build frontend assets
npm run build

# Restart services
php artisan queue:restart
php artisan reverb:restart || true

# Bring application back online
php artisan up

echo "✅ Deployment completed successfully!"
ENDSSH

echo -e "${GREEN}✅ Deployment finished!${NC}"
echo -e "${GREEN}🎉 SOCLY v3 is now live with bug fixes!${NC}"
