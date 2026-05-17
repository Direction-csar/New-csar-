#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# CSAR — Déploiement sécurité (à exécuter UNE FOIS sur le serveur)
# ----------------------------------------------------------------------------
# Ce script applique les améliorations de sécurité côté serveur :
#   1. Tailscale autostart
#   2. Cron de sauvegarde
#   3. Logrotate pour les logs Laravel
#   4. PHP hardening (display_errors, expose_php)
#   5. Optimisation Laravel
#
# Usage : sudo bash /var/www/csar/scripts/deploy-security.sh
# ----------------------------------------------------------------------------

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    echo "[ERROR] Lance ce script avec sudo"
    exit 1
fi

APP_PATH="/var/www/csar"
SCRIPTS="$APP_PATH/scripts"

echo "===================================================================="
echo " CSAR — Déploiement sécurité"
echo "===================================================================="

# --- 1. Tailscale autostart -------------------------------------------------
echo ""
echo "[1/5] Tailscale autostart..."
if command -v tailscale >/dev/null 2>&1; then
    systemctl enable tailscaled
    systemctl restart tailscaled
    echo "      → tailscaled activé au boot"
else
    echo "      ⚠ Tailscale non installé, étape ignorée"
fi

# --- 2. Cron de sauvegarde --------------------------------------------------
echo ""
echo "[2/5] Cron de sauvegarde quotidien (02h00)..."
chmod +x "$SCRIPTS/backup.sh"
CRON_LINE="0 2 * * * /var/www/csar/scripts/backup.sh >> /var/log/csar-backup.log 2>&1"
CRON_FILE="/etc/cron.d/csar-backup"
echo "$CRON_LINE" > "$CRON_FILE"
chmod 644 "$CRON_FILE"
echo "      → /etc/cron.d/csar-backup créé"

# --- 3. Logrotate pour Laravel ---------------------------------------------
echo ""
echo "[3/5] Logrotate Laravel..."
cat > /etc/logrotate.d/csar-laravel <<'EOF'
/var/www/csar/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
EOF
echo "      → /etc/logrotate.d/csar-laravel créé"

# --- 4. PHP hardening -------------------------------------------------------
echo ""
echo "[4/5] PHP hardening..."
PHP_INI="$(php -r 'echo php_ini_loaded_file();' 2>/dev/null || echo '')"
if [[ -n "$PHP_INI" && -f "$PHP_INI" ]]; then
    # Sauvegarde
    cp -n "$PHP_INI" "${PHP_INI}.csar-bak" || true

    sed -i 's/^expose_php\s*=.*/expose_php = Off/' "$PHP_INI"
    sed -i 's/^display_errors\s*=.*/display_errors = Off/' "$PHP_INI"
    sed -i 's/^display_startup_errors\s*=.*/display_startup_errors = Off/' "$PHP_INI"
    sed -i 's/^;\?\s*log_errors\s*=.*/log_errors = On/' "$PHP_INI"
    sed -i 's/^;\?\s*allow_url_fopen\s*=.*/allow_url_fopen = Off/' "$PHP_INI"
    sed -i 's/^;\?\s*allow_url_include\s*=.*/allow_url_include = Off/' "$PHP_INI"

    echo "      → $PHP_INI durci (backup: ${PHP_INI}.csar-bak)"
else
    echo "      ⚠ php.ini introuvable, étape ignorée"
fi

# --- 5. Optimisation Laravel -----------------------------------------------
echo ""
echo "[5/5] Optimisation Laravel..."
chmod +x "$SCRIPTS/deploy-optimize.sh"
bash "$SCRIPTS/deploy-optimize.sh"

echo ""
echo "===================================================================="
echo " ✓ Déploiement sécurité terminé."
echo "===================================================================="
echo ""
echo "Vérifications recommandées :"
echo "  - tailscale status"
echo "  - sudo systemctl status cloudflared"
echo "  - curl -I https://csar.sn"
echo "  - Tester https://securityheaders.com/?q=csar.sn"
echo ""
