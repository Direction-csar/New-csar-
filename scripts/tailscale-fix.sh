#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# CSAR — Fix Tailscale autostart (à exécuter UNE FOIS sur le serveur)
# ----------------------------------------------------------------------------
# Usage : sudo bash /var/www/csar/scripts/tailscale-fix.sh
#
# - Active tailscaled au démarrage
# - Redémarre le service
# - Vérifie le statut
# ----------------------------------------------------------------------------

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    echo "[ERROR] Lance ce script avec sudo : sudo bash $0"
    exit 1
fi

echo "[1/4] Activation du service tailscaled au boot..."
systemctl enable tailscaled

echo "[2/4] Démarrage du service tailscaled..."
systemctl restart tailscaled

echo "[3/4] Reconnexion à Tailscale..."
tailscale up --accept-routes

echo "[4/4] Statut Tailscale :"
tailscale status | head -10
echo ""
echo "[OK] Tailscale est configuré pour démarrer automatiquement."
echo "     IP Tailscale : $(tailscale ip -4 || echo '???')"
