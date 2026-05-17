#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# CSAR — Optimisation Laravel pour la production
# ----------------------------------------------------------------------------
# Usage : sudo bash /var/www/csar/scripts/deploy-optimize.sh
#
# - Met les permissions correctes
# - Cache la config / routes / vues
# - Recharge PHP-FPM et Nginx
# ----------------------------------------------------------------------------

set -euo pipefail

APP_PATH="/var/www/csar"
WEB_USER="www-data"

if [[ $EUID -ne 0 ]]; then
    echo "[ERROR] Lance ce script avec sudo"
    exit 1
fi

cd "$APP_PATH"

echo "[1/8] Permissions storage et bootstrap/cache..."
chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[2/8] Composer optimize-autoloader..."
sudo -u "$WEB_USER" composer install --no-dev --optimize-autoloader --no-interaction || true

echo "[3/8] Clear caches..."
sudo -u "$WEB_USER" php artisan optimize:clear

echo "[4/8] Cache config..."
sudo -u "$WEB_USER" php artisan config:cache

echo "[5/8] Cache routes..."
sudo -u "$WEB_USER" php artisan route:cache

echo "[6/8] Cache views..."
sudo -u "$WEB_USER" php artisan view:cache

echo "[7/8] Storage symlink..."
sudo -u "$WEB_USER" php artisan storage:link || true

echo "[8/8] Reload services..."
PHP_FPM_SVC="$(systemctl list-units --type=service | grep -oE 'php[0-9.]+-fpm\.service' | head -1 || echo php8.5-fpm)"
systemctl reload "$PHP_FPM_SVC" 2>/dev/null || systemctl restart "$PHP_FPM_SVC"
nginx -t && systemctl reload nginx

echo ""
echo "[OK] Déploiement optimisé terminé."
