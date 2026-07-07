#!/bin/bash
# Script de déploiement CSAR.sn sur serveur Linux
# À exécuter sur le serveur avec: bash deploy-server.sh

set -euo pipefail

echo "=== DEPLOIEMENT CSAR.SN ==="

# Configuration
PHP_CMD="php"

# Détecter le répertoire projet depuis l'emplacement du script
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "[1/8] Git pull..."
git pull origin main

echo "[2/8] Composer install..."
$PHP_CMD /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "[3/8] Migrations base de données..."
$PHP_CMD artisan migrate --force

echo "[4/8] Seeding données SIM..."
$PHP_CMD artisan db:seed --class=SimRealDataSeeder --force

echo "[5/8] Liens storage (fichiers publics)..."
$PHP_CMD artisan storage:link || true

echo "[6/8] Cache clear + warming..."
$PHP_CMD artisan optimize:clear
$PHP_CMD artisan optimize

echo "[7/8] Permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "[8/8] Vérifications..."
$PHP_CMD artisan route:list --name="archives" >/dev/null && echo "  Routes archives OK"
$PHP_CMD artisan route:list --name="warehouse" >/dev/null && echo "  Routes warehouse OK"

echo ""
echo "=== DEPLOIEMENT TERMINE ==="
echo "Vérifiez les accès:"
echo "  Portail admin : /login"
echo "  Portail DRH   : /drh/login  ->  Archives DRH"
echo "  Portail CPM   : /cpm/login"
echo "  Portail DPSE  : /dpse/login"
echo "  Portail DTL   : /dtl/login"
echo "  API mobile    : /api/warehouse/v1/login"
