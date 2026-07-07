#!/bin/bash
# Script de déploiement CSAR.sn sur serveur Linux
# À copier sur le serveur et exécuter avec: bash deploy-server.sh

set -e

echo "=== DEPLOIEMENT CSAR.SN ==="

# Configuration (à adapter si nécessaire)
PROJECT_DIR="/var/www/csar.sn"
PHP_CMD="php"

cd "$PROJECT_DIR"

echo "[1/6] Git pull..."
git pull origin main

echo "[2/6] Composer install..."
$PHP_CMD /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "[3/6] Migrations base de données..."
$PHP_CMD artisan migrate --force

echo "[4/6] Seeding données SIM..."
$PHP_CMD artisan db:seed --class=SimRealDataSeeder --force

echo "[5/6] Cache clear..."
$PHP_CMD artisan optimize:clear

echo "[6/6] Permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "=== DEPLOIEMENT TERMINE ==="
echo "Verifiez les routes:"
echo "  /archives/drh"
echo "  /archives/dtl"
echo "  /admin/sim/markets-geolocation"
