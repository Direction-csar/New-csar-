#!/bin/bash
#
# Sauvegarde complète de la plateforme CSAR
# - Base de données (MySQL ou SQLite)
# - Fichiers uploadés (storage/app)
# - Fichier .env
# - Code source (archive git)
#
# Usage : bash backup-platform.sh

set -e

PROJECT_DIR="/var/www/csar"
BACKUP_DIR="${PROJECT_DIR}/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_NAME="csar_backup_${TIMESTAMP}"
BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}"

mkdir -p "${BACKUP_PATH}"

cd "${PROJECT_DIR}"

echo "=== Démarrage de la sauvegarde CSAR ==="
echo "Date : $(date)"
echo "Destination : ${BACKUP_PATH}"

# Charger les variables .env
if [ -f "${PROJECT_DIR}/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source "${PROJECT_DIR}/.env"
    set +a
    cp "${PROJECT_DIR}/.env" "${BACKUP_PATH}/env_backup"
    echo "[OK] Fichier .env copié"
else
    echo "[ERREUR] Fichier .env introuvable"
    exit 1
fi

# Sauvegarde de la base de données
DB_CONNECTION="${DB_CONNECTION:-sqlite}"

if [ "$DB_CONNECTION" == "mysql" ] || [ "$DB_CONNECTION" == "mariadb" ]; then
    DB_HOST="${DB_HOST:-127.0.0.1}"
    DB_PORT="${DB_PORT:-3306}"
    DB_DATABASE="${DB_DATABASE:-csar}"
    DB_USERNAME="${DB_USERNAME:-root}"

    echo "[INFO] Sauvegarde de la base MySQL/MariaDB : ${DB_DATABASE}"
    mysqldump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" \
        --password="$DB_PASSWORD" --single-transaction --no-tablespaces --routines --triggers \
        "$DB_DATABASE" > "${BACKUP_PATH}/database.sql"
    echo "[OK] Base de données exportée"

elif [ "$DB_CONNECTION" == "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        DB_FILE="${PROJECT_DIR}/database/database.sqlite"
    fi
    echo "[INFO] Sauvegarde de la base SQLite : ${DB_FILE}"
    cp "$DB_FILE" "${BACKUP_PATH}/database.sqlite"
    echo "[OK] Base de données SQLite copiée"
else
    echo "[AVERTISSEMENT] Type de base de données non géré : ${DB_CONNECTION}"
fi

# Sauvegarde des fichiers uploadés
if [ -d "${PROJECT_DIR}/storage/app" ]; then
    echo "[INFO] Archivage des fichiers storage/app..."
    tar -czf "${BACKUP_PATH}/storage_app.tar.gz" -C "${PROJECT_DIR}" storage/app
    echo "[OK] Fichiers stockés dans storage_app.tar.gz"
else
    echo "[AVERTISSEMENT] Dossier storage/app introuvable"
fi

# Sauvegarde du code source via git
echo "[INFO] Création d'une archive du code source..."
git archive --format=tar.gz --output="${BACKUP_PATH}/source.tar.gz" HEAD
echo "[OK] Code source archivé"

# Métadonnées
cat > "${BACKUP_PATH}/backup-info.txt" <<EOF
Plateforme : CSAR
Date de sauvegarde : ${TIMESTAMP}
Connexion base : ${DB_CONNECTION}
Base de données : ${DB_DATABASE:-N/A}
Projet : ${PROJECT_DIR}
EOF

echo ""
echo "=== Sauvegarde terminée ==="
echo "Dossier : ${BACKUP_PATH}"
echo "Fichiers :"
ls -lh "${BACKUP_PATH}"
