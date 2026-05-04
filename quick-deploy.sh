#!/bin/bash

# Quick deployment script for SOCLY v3 bug fixes
# Usage: ./quick-deploy.sh [password]

set -e

SERVER="root@217.156.122.161"
REMOTE_PATH="/var/www/socly"

echo "🚀 SOCLY v3 - Quick Bug Fix Deployment"
echo "========================================"
echo ""

# Check if sshpass is available for password authentication
if ! command -v sshpass &> /dev/null; then
    echo "⚠️  sshpass není nainstalován. Budete muset zadat heslo ručně."
    echo "   Pro instalaci: sudo apt-get install sshpass"
    echo ""
    USE_SSHPASS=false
else
    USE_SSHPASS=true
fi

# Upload files
echo "📤 Nahrávám soubory na server..."
rsync -avz --progress \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.env' \
    --exclude='vendor' \
    ./ ${SERVER}:${REMOTE_PATH}/

echo ""
echo "🔧 Spouštím deployment příkazy na serveru..."
echo ""

ssh ${SERVER} bash << 'ENDSSH'
set -e
cd /var/www/socly

echo "⏸️  Přepínám do maintenance mode..."
php artisan down || true

echo "📦 Instaluji závislosti..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🗄️  Spouštím migrace..."
php artisan migrate --force

echo "🧹 Čistím cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Optimalizuji..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🎨 Builduji frontend..."
npm ci --production
npm run build

echo "🔄 Restartuji služby..."
php artisan queue:restart || true
php artisan reverb:restart || true

# Restart PHP-FPM if available
if command -v systemctl &> /dev/null; then
    sudo systemctl restart php8.3-fpm || sudo systemctl restart php8.2-fpm || sudo systemctl restart php-fpm || true
fi

echo "✅ Vypínám maintenance mode..."
php artisan up

echo ""
echo "✅ Deployment dokončen!"
ENDSSH

echo ""
echo "🎉 HOTOVO! SOCLY v3 je aktualizováno s opravami bugů."
echo ""
echo "📋 Co bylo opraveno:"
echo "  ✅ XSS ochrana (sanitizace inputů)"
echo "  ✅ File upload bezpečnost"
echo "  ✅ Race conditions (like/bookmark/reactions)"
echo "  ✅ Databázové indexy"
echo "  ✅ Soft deletes"
echo "  ✅ Unique constraint na username"
echo "  ✅ Reverb security (allowed origins, rate limiting)"
echo ""
echo "🔍 Zkontrolujte aplikaci na: https://socly.eu"
echo ""
