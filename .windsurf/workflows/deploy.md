---
description: Deploy Socly to production VPS (socly.eu)
---

## Production Deployment — socly.eu

**Server**: 217.156.122.161 (Debian 13, Avahosting)
**Stack**: PHP 8.4, Nginx 1.26, PostgreSQL 17, Node 20

### Quick Deploy (after code changes)

// turbo
1. Build frontend locally:
```bash
npm run build
```

2. Commit and push:
```bash
git add -A && git commit -m "deploy: description" && git push origin main
```

// turbo
3. Deploy on server:
```bash
ssh root@217.156.122.161 "cd /var/www/socly && git pull origin main && composer install --no-dev --optimize-autoloader --no-interaction && npm ci --production=false && npm run build && php artisan migrate --force && php artisan optimize && chown -R www-data:www-data storage bootstrap/cache && systemctl restart socly-reverb socly-queue && systemctl reload php8.4-fpm nginx"
```

### Services

- **socly-reverb** — WebSocket server (port 6001, proxied via Nginx /app)
- **socly-queue** — Queue worker
- **nginx** — Web server with SSL (Let's Encrypt)
- **php8.4-fpm** — PHP FastCGI

### Useful Commands

```bash
# Check service status
systemctl status socly-reverb socly-queue

# View logs
tail -f /var/log/socly-reverb.log
tail -f /var/log/socly-queue.log
tail -f /var/www/socly/storage/logs/laravel.log

# Clear caches
php artisan optimize:clear

# Restart all
systemctl restart socly-reverb socly-queue php8.4-fpm nginx
```
