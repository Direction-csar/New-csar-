#!/bin/bash

# Script de sauvegarde automatique pour la Plateforme CSAR
# Usage: ./backup.sh
# À ajouter au cron: 0 2 * * * /path/to/backup.sh

set -e

# Configuration
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/csar"
APP_DIR="/var/www/csar"
DB_NAME="csar_platform"
DB_USER="csar_user"
DB_PASS=""  # À configurer

# Créer le dossier de sauvegarde s'il n'existe pas
mkdir -p $BACKUP_DIR

echo "💾 Sauvegarde de la Plateforme CSAR - $DATE"
echo "============================================"

# Sauvegarder la base de données
echo "📊 Sauvegarde de la base de données..."
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz
echo "✅ Base de données sauvegardée: db_$DATE.sql.gz"

# Sauvegarder les fichiers
echo "📁 Sauvegarde des fichiers..."
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    $APP_DIR 2>/dev/null || true
echo "✅ Fichiers sauvegardés: files_$DATE.tar.gz"

# Sauvegarder uniquement storage (important)
echo "💿 Sauvegarde du dossier storage..."
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz $APP_DIR/storage 2>/dev/null || true
echo "✅ Storage sauvegardé: storage_$DATE.tar.gz"

# Supprimer les sauvegardes de plus de 30 jours
echo "🧹 Nettoyage des anciennes sauvegardes..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
echo "✅ Nettoyage terminé"

# Afficher l'espace utilisé
echo ""
echo "📊 Espace utilisé par les sauvegardes:"
du -sh $BACKUP_DIR

echo ""
echo "✅ Sauvegarde terminée avec succès !"

