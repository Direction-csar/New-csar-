#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# CSAR — Déploiement de la 2FA (UNE FOIS, après git pull)
# ----------------------------------------------------------------------------
# Étapes :
#   1. composer install (récupère pragmarx/google2fa + bacon/bacon-qr-code)
#   2. Migration de la table users (ajoute les colonnes two_factor_*)
#   3. Cache routes/config/views
# ----------------------------------------------------------------------------

set -euo pipefail

APP_PATH="/var/www/csar"
WEB_USER="www-data"

if [[ $EUID -ne 0 ]]; then
    echo "[ERROR] sudo requis"
    exit 1
fi

cd "$APP_PATH"

echo "[1/4] Installation des dépendances 2FA..."
sudo -u "$WEB_USER" composer install --no-dev --optimize-autoloader --no-interaction

echo "[2/4] Migration base de données..."
sudo -u "$WEB_USER" php artisan migrate --force

echo "[3/4] Rebuild caches Laravel..."
sudo -u "$WEB_USER" php artisan optimize:clear
sudo -u "$WEB_USER" php artisan config:cache
sudo -u "$WEB_USER" php artisan route:cache
sudo -u "$WEB_USER" php artisan view:cache

echo "[4/4] Reload PHP-FPM..."
PHP_FPM_SVC="$(systemctl list-units --type=service | grep -oE 'php[0-9.]+-fpm\.service' | head -1 || echo php8.5-fpm)"
systemctl reload "$PHP_FPM_SVC" 2>/dev/null || systemctl restart "$PHP_FPM_SVC"

echo ""
echo "[OK] 2FA déployée. Pour activer pour un admin :"
echo "  1. Connectez-vous avec le compte admin"
echo "  2. Visitez https://csar.sn/admin/2fa/setup"
echo "  3. Scannez le QR code dans Google Authenticator / Authy"
echo "  4. Saisissez le code à 6 chiffres pour confirmer"
echo "  5. Sauvegardez les codes de récupération affichés"
