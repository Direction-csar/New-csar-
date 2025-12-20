#!/bin/bash

# Script de restauration pour la Plateforme CSAR
# Usage: ./restore.sh [backup_date]
# Exemple: ./restore.sh 20250109_020000

set -e

# Configuration
BACKUP_DIR="/backups/csar"
APP_DIR="/var/www/csar"
DB_NAME="csar_platform"
DB_USER="csar_user"
DB_PASS=""  # À configurer

if [ -z "$1" ]; then
    echo "❌ Veuillez spécifier une date de sauvegarde"
    echo "Usage: ./restore.sh 20250109_020000"
    echo ""
    echo "Sauvegardes disponibles:"
    ls -lh $BACKUP_DIR | grep -E "db_|files_|storage_"
    exit 1
fi

BACKUP_DATE=$1

echo "🔄 Restauration de la Plateforme CSAR - $BACKUP_DATE"
echo "===================================================="

# Vérifier que les fichiers de sauvegarde existent
if [ ! -f "$BACKUP_DIR/db_$BACKUP_DATE.sql.gz" ]; then
    echo "❌ Fichier de sauvegarde DB introuvable: db_$BACKUP_DATE.sql.gz"
    exit 1
fi

if [ ! -f "$BACKUP_DIR/files_$BACKUP_DATE.tar.gz" ]; then
    echo "❌ Fichier de sauvegarde fichiers introuvable: files_$BACKUP_DATE.tar.gz"
    exit 1
fi

# Confirmation
read -p "⚠️  Cette opération va écraser les données actuelles. Continuer ? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Restauration annulée"
    exit 1
fi

# Restaurer la base de données
echo "📊 Restauration de la base de données..."
gunzip -c $BACKUP_DIR/db_$BACKUP_DATE.sql.gz | mysql -u $DB_USER -p$DB_PASS $DB_NAME
echo "✅ Base de données restaurée"

# Sauvegarder l'état actuel avant restauration
echo "💾 Sauvegarde de l'état actuel avant restauration..."
CURRENT_DATE=$(date +%Y%m%d_%H%M%S)
tar -czf $BACKUP_DIR/pre_restore_$CURRENT_DATE.tar.gz $APP_DIR 2>/dev/null || true

# Restaurer les fichiers
echo "📁 Restauration des fichiers..."
cd /var/www
rm -rf csar_backup_$CURRENT_DATE
mv csar csar_backup_$CURRENT_DATE
tar -xzf $BACKUP_DIR/files_$BACKUP_DATE.tar.gz
echo "✅ Fichiers restaurés"

# Restaurer storage si disponible
if [ -f "$BACKUP_DIR/storage_$BACKUP_DATE.tar.gz" ]; then
    echo "💿 Restauration du dossier storage..."
    cd $APP_DIR
    rm -rf storage_backup
    mv storage storage_backup
    tar -xzf $BACKUP_DIR/storage_$BACKUP_DATE.tar.gz
    echo "✅ Storage restauré"
fi

# Réinstaller les dépendances
echo "📦 Réinstallation des dépendances..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader --no-interaction

# Optimisation
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
echo "🔐 Configuration des permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

echo ""
echo "✅ Restauration terminée avec succès !"
echo "📝 L'ancienne version a été sauvegardée dans: csar_backup_$CURRENT_DATE"

