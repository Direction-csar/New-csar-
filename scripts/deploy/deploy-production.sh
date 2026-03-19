#!/bin/bash

###############################################################################
# Script de déploiement CSAR - Production
# Support 1000+ utilisateurs simultanés
###############################################################################

set -e

echo "🚀 Déploiement CSAR - Production"
echo "=================================="
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables
PROJECT_DIR="/var/www/csar"
BACKUP_DIR="/var/backups/csar"
LOG_FILE="/var/log/csar-deploy.log"

# Fonction de log
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERREUR:${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ATTENTION:${NC} $1" | tee -a "$LOG_FILE"
}

# Vérifier si on est root
if [ "$EUID" -ne 0 ]; then 
    error "Ce script doit être exécuté en tant que root (sudo)"
fi

# Vérifier que le projet existe
if [ ! -d "$PROJECT_DIR" ]; then
    error "Le répertoire du projet n'existe pas: $PROJECT_DIR"
fi

cd "$PROJECT_DIR"

# 1. Backup
log "📦 Création du backup..."
mkdir -p "$BACKUP_DIR"
BACKUP_NAME="csar-backup-$(date +'%Y%m%d-%H%M%S').tar.gz"
tar -czf "$BACKUP_DIR/$BACKUP_NAME" \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    . || error "Échec du backup"
log "✅ Backup créé: $BACKUP_NAME"

# 2. Mode maintenance
log "🔧 Activation du mode maintenance..."
php artisan down --retry=60 || warning "Mode maintenance déjà actif"

# 3. Git pull
log "📥 Récupération des dernières modifications..."
git fetch origin
git pull origin main || error "Échec du git pull"

# 4. Composer
log "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction || error "Échec de composer install"

# 5. NPM
log "📦 Installation des dépendances NPM..."
npm ci --production || error "Échec de npm ci"

# 6. Build assets
log "🏗️  Build des assets..."
npm run build || error "Échec du build"

# 7. Migrations
log "🗄️  Exécution des migrations..."
php artisan migrate --force || error "Échec des migrations"

# 8. Cache
log "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

log "⚡ Optimisation des caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Permissions
log "🔐 Configuration des permissions..."
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/storage"
chmod -R 775 "$PROJECT_DIR/bootstrap/cache"

# 10. Optimisation performance
log "🚀 Optimisation des performances..."
php artisan csar:optimize --force || warning "Échec de l'optimisation"

# 11. Redémarrage des services
log "🔄 Redémarrage des services..."

# PHP-FPM
systemctl restart php8.2-fpm || error "Échec redémarrage PHP-FPM"

# Nginx
nginx -t || error "Configuration Nginx invalide"
systemctl reload nginx || error "Échec reload Nginx"

# Queue workers
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart csar-worker:* || warning "Échec redémarrage workers"
fi

# Redis
systemctl restart redis-server || warning "Échec redémarrage Redis"

# 12. Vérifications post-déploiement
log "✅ Vérifications post-déploiement..."

# Vérifier PHP
php -v > /dev/null || error "PHP non fonctionnel"

# Vérifier Redis
redis-cli ping > /dev/null || error "Redis non fonctionnel"

# Vérifier la base de données
php artisan db:show > /dev/null || error "Base de données non accessible"

# Vérifier les workers
if command -v supervisorctl &> /dev/null; then
    WORKERS_STATUS=$(supervisorctl status csar-worker:* | grep -c "RUNNING" || echo "0")
    if [ "$WORKERS_STATUS" -lt 1 ]; then
        warning "Aucun worker actif"
    else
        log "✅ $WORKERS_STATUS workers actifs"
    fi
fi

# 13. Désactivation du mode maintenance
log "✅ Désactivation du mode maintenance..."
php artisan up

# 14. Test de santé
log "🏥 Test de santé de l'application..."
HEALTH_CHECK=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/health || echo "000")
if [ "$HEALTH_CHECK" = "200" ]; then
    log "✅ Application opérationnelle (HTTP $HEALTH_CHECK)"
else
    error "Application non accessible (HTTP $HEALTH_CHECK)"
fi

# 15. Nettoyage
log "🧹 Nettoyage des anciens backups (>30 jours)..."
find "$BACKUP_DIR" -name "csar-backup-*.tar.gz" -mtime +30 -delete || warning "Échec nettoyage backups"

# Résumé
echo ""
echo "=================================="
log "✅ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS"
echo "=================================="
echo ""
log "📊 Informations:"
log "   - Backup: $BACKUP_NAME"
log "   - Version Git: $(git rev-parse --short HEAD)"
log "   - Date: $(date +'%Y-%m-%d %H:%M:%S')"
log "   - Workers: $WORKERS_STATUS actifs"
echo ""
log "🔗 URLs:"
log "   - Site public: https://csar.sn"
log "   - Admin: https://csar.sn/admin"
log "   - DG: https://csar.sn/dg"
echo ""
log "📝 Logs: $LOG_FILE"
echo ""

exit 0
