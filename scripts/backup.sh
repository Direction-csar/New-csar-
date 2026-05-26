#!/usr/bin/env bash
# ----------------------------------------------------------------------------
# CSAR — Script de sauvegarde automatique
# ----------------------------------------------------------------------------
# Usage  : sudo /var/www/csar/scripts/backup.sh
# Cron   : 0 2 * * * /var/www/csar/scripts/backup.sh >> /var/log/csar-backup.log 2>&1
#
# Sauvegarde :
#   - Base de données MySQL (csar)
#   - Dossier storage/app/public (uploads)
# Rétention : 14 jours
# ----------------------------------------------------------------------------

set -euo pipefail

# === Configuration =========================================================
APP_PATH="/var/www/csar"
BACKUP_DIR="/var/backups/csar"
RETENTION_DAYS=14
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

# Charger les credentials DB depuis le .env de Laravel
if [[ ! -f "$APP_PATH/.env" ]]; then
    echo "[ERROR] $APP_PATH/.env introuvable"
    exit 1
fi

DB_CONNECTION="$(grep -E '^DB_CONNECTION=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"' || echo mysql)"
DB_HOST="$(grep -E '^DB_HOST=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"' || echo 127.0.0.1)"
DB_PORT="$(grep -E '^DB_PORT=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"' || echo 3306)"
DB_NAME="$(grep -E '^DB_DATABASE=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"')"
DB_USER="$(grep -E '^DB_USERNAME=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"')"
DB_PASS="$(grep -E '^DB_PASSWORD=' "$APP_PATH/.env" | cut -d= -f2- | tr -d '"')"

# === Préparation ===========================================================
mkdir -p "$BACKUP_DIR"/{db,storage}
echo "[$(date)] === Démarrage backup CSAR ==="

# === Sauvegarde DB =========================================================
DB_FILE="$BACKUP_DIR/db/csar-${TIMESTAMP}.sql.gz"
echo "[$(date)] Dump DB -> $DB_FILE"

MYSQL_PWD="$DB_PASS" mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USER" \
    --single-transaction \
    --routines --triggers --events \
    --default-character-set=utf8mb4 \
    --no-tablespaces \
    "$DB_NAME" | gzip -9 > "$DB_FILE"

DB_SIZE="$(du -h "$DB_FILE" | awk '{print $1}')"
echo "[$(date)] DB OK ($DB_SIZE)"

# === Sauvegarde Storage ====================================================
STORAGE_FILE="$BACKUP_DIR/storage/storage-${TIMESTAMP}.tar.gz"
echo "[$(date)] Archive storage -> $STORAGE_FILE"

tar -czf "$STORAGE_FILE" \
    -C "$APP_PATH/storage/app" public \
    2>/dev/null || true

STORAGE_SIZE="$(du -h "$STORAGE_FILE" | awk '{print $1}')"
echo "[$(date)] Storage OK ($STORAGE_SIZE)"

# === Nettoyage rétention ===================================================
echo "[$(date)] Suppression sauvegardes > ${RETENTION_DAYS} jours"
find "$BACKUP_DIR/db"      -type f -name "*.sql.gz" -mtime +"$RETENTION_DAYS" -delete
find "$BACKUP_DIR/storage" -type f -name "*.tar.gz" -mtime +"$RETENTION_DAYS" -delete

# === Sauvegarde hors-site (rclone) =========================================
# Active si la variable BACKUP_REMOTE est définie (ex: BACKUP_REMOTE="s3:csar-backups")
# Installer rclone : curl https://rclone.org/install.sh | sudo bash
# Configurer une remote : rclone config (S3, Backblaze B2, Google Drive, etc.)
if [[ -n "${BACKUP_REMOTE:-}" ]] && command -v rclone >/dev/null 2>&1; then
    echo "[$(date)] Upload hors-site -> $BACKUP_REMOTE"
    rclone copy "$DB_FILE"      "$BACKUP_REMOTE/db/"      --quiet || echo "[WARN] Upload DB hors-site échoué"
    rclone copy "$STORAGE_FILE" "$BACKUP_REMOTE/storage/" --quiet || echo "[WARN] Upload storage hors-site échoué"
    # Rétention distante : suppression des fichiers > 30 jours
    rclone delete --min-age 30d "$BACKUP_REMOTE/db/"      --quiet || true
    rclone delete --min-age 30d "$BACKUP_REMOTE/storage/" --quiet || true
    echo "[$(date)] Upload hors-site terminé"
elif [[ -n "${BACKUP_REMOTE:-}" ]]; then
    echo "[WARN] BACKUP_REMOTE défini mais rclone non installé — backup local uniquement"
fi

# === Résumé ================================================================
TOTAL_SIZE="$(du -sh "$BACKUP_DIR" | awk '{print $1}')"
echo "[$(date)] === Backup terminé — total $TOTAL_SIZE ==="
