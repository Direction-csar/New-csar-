#!/bin/bash
#
# Restauration complète de la plateforme CSAR
#
# Usage : bash restore-platform.sh /var/backups/csar/csar_backup_YYYYMMDD_HHMMSS

set -e

if [ -z "$1" ]; then
    echo "Usage : bash restore-platform.sh <chemin_du_dossier_de_sauvegarde>"
    exit 1
fi

BACKUP_PATH="$1"
PROJECT_DIR="/var/www/csar"

if [ ! -d "$BACKUP_PATH" ]; then
    echo "[ERREUR] Dossier de sauvegarde introuvable : ${BACKUP_PATH}"
    exit 1
fi

cd "${PROJECT_DIR}"

echo "=== Début de la restauration CSAR ==="
echo "Sauvegarde : ${BACKUP_PATH}"
echo "Date       : $(date)"

# Restaurer le .env
if [ -f "${BACKUP_PATH}/env_backup" ]; then
    cp "${BACKUP_PATH}/env_backup" "${PROJECT_DIR}/.env"
    echo "[OK] .env restauré"
fi

# Charger les variables .env pour connaître le type de base
set -a
# shellcheck disable=SC1091
source "${PROJECT_DIR}/.env"
set +a

DB_CONNECTION="${DB_CONNECTION:-sqlite}"

# Restaurer la base de données
if [ "$DB_CONNECTION" == "mysql" ] || [ "$DB_CONNECTION" == "mariadb" ]; then
    DB_HOST="${DB_HOST:-127.0.0.1}"
    DB_PORT="${DB_PORT:-3306}"
    DB_DATABASE="${DB_DATABASE:-csar}"
    DB_USERNAME="${DB_USERNAME:-root}"

    echo "[INFO] Restauration de la base MySQL/MariaDB : ${DB_DATABASE}"

    if [ ! -f "${BACKUP_PATH}/database.sql" ]; then
        echo "[ERREUR] Fichier database.sql introuvable dans la sauvegarde"
        exit 1
    fi

    mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password="$DB_PASSWORD" \
        -e "CREATE DATABASE IF NOT EXISTS ${DB_DATABASE};"
    mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" --password="$DB_PASSWORD" \
        "$DB_DATABASE" < "${BACKUP_PATH}/database.sql"
    echo "[OK] Base de données restaurée"

elif [ "$DB_CONNECTION" == "sqlite" ]; then
    DB_FILE="${PROJECT_DIR}/database/database.sqlite"
    if [ -f "${BACKUP_PATH}/database.sqlite" ]; then
        cp "${BACKUP_PATH}/database.sqlite" "$DB_FILE"
        echo "[OK] Base SQLite restaurée"
    else
        echo "[AVERTISSEMENT] database.sqlite introuvable dans la sauvegarde"
    fi
fi

# Restaurer les fichiers uploadés
if [ -f "${BACKUP_PATH}/storage_app.tar.gz" ]; then
    echo "[INFO] Restauration des fichiers storage/app..."
    tar -xzf "${BACKUP_PATH}/storage_app.tar.gz" -C "${PROJECT_DIR}"
    echo "[OK] Fichiers restaurés"
fi

# Restaurer le code source si nécessaire
if [ -f "${BACKUP_PATH}/source.tar.gz" ]; then
    echo "[INFO] Extraction du code source..."
    # Attention : cette opération écrase les fichiers du projet. À utiliser sur un nouveau serveur.
    # tar -xzf "${BACKUP_PATH}/source.tar.gz" -C "${PROJECT_DIR}"
    echo "[INFO] source.tar.gz disponible. Sur un nouveau serveur, décompressez-le dans ${PROJECT_DIR}"
fi

# Post-restauration
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan storage:link

echo ""
echo "=== Restauration terminée ==="
echo "Vérifiez que l'application est accessible."
