#!/bin/bash
#
# Installe la sauvegarde automatique de CSAR toutes les 5 heures via cron.
#
# Usage : bash install-auto-backup.sh

set -e

CRON_LINE='0 */5 * * * bash /var/www/csar/backup-platform.sh >> /var/log/csar-backup.log 2>&1'

echo "=== Installation de la sauvegarde automatique ==="

# Récupérer la crontab actuelle
crontab -l > /tmp/current_crontab 2>/dev/null || true

# Vérifier si la ligne existe déjà pour éviter les doublons
if grep -Fxq "$CRON_LINE" /tmp/current_crontab; then
    echo "[INFO] La tâche de sauvegarde automatique est déjà installée."
else
    echo "$CRON_LINE" >> /tmp/current_crontab
    crontab /tmp/current_crontab
    echo "[OK] Tâche cron ajoutée : sauvegarde automatique toutes les 5 heures"
fi

rm -f /tmp/current_crontab

echo ""
echo "Liste des tâches cron actuelles :"
crontab -l
