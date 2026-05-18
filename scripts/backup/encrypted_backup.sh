#!/usr/bin/env bash
###############################################################################
# CSAR — Sauvegarde MySQL chiffrée GPG + rotation 30 jours
# À placer dans /etc/cron.d/csar-backup :
#   0 2 * * * www-data /var/www/csar/scripts/backup/encrypted_backup.sh
###############################################################################
set -euo pipefail

# === Configuration ===
PROJECT_DIR="${PROJECT_DIR:-/var/www/csar}"
BACKUP_DIR="${BACKUP_DIR:-/backups/csar}"
GPG_RECIPIENT="${GPG_RECIPIENT:-ops@csar.sn}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
TS=$(date +%F_%H%M)

# Charger .env
set -a
# shellcheck disable=SC1090
source <(grep -E "^DB_(CONNECTION|HOST|PORT|DATABASE|USERNAME|PASSWORD)=" "$PROJECT_DIR/.env" | sed 's/^/export /')
set +a

mkdir -p "$BACKUP_DIR"

OUT="$BACKUP_DIR/csar-db-$TS.sql.gz.gpg"

echo "[$(date)] Sauvegarde BDD vers $OUT"

case "${DB_CONNECTION:-mysql}" in
  mysql|mariadb)
    mysqldump \
      --single-transaction --quick --routines --triggers --events \
      -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" \
      -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
    | gzip -9 \
    | gpg --batch --yes --trust-model always -e -r "$GPG_RECIPIENT" -o "$OUT"
    ;;
  pgsql)
    PGPASSWORD="$DB_PASSWORD" pg_dump \
      -h "${DB_HOST:-127.0.0.1}" -p "${DB_PORT:-5432}" \
      -U "$DB_USERNAME" "$DB_DATABASE" \
    | gzip -9 \
    | gpg --batch --yes --trust-model always -e -r "$GPG_RECIPIENT" -o "$OUT"
    ;;
  *) echo "DB_CONNECTION non supportée : $DB_CONNECTION"; exit 1 ;;
esac

chmod 640 "$OUT"
echo "[$(date)] Sauvegarde terminée : $(du -h "$OUT" | cut -f1)"

# Rotation
find "$BACKUP_DIR" -type f -name "csar-db-*.sql.gz.gpg" -mtime +$RETENTION_DAYS -delete
echo "[$(date)] Rotation $RETENTION_DAYS jours OK"

# Sauvegarde des fichiers uploadés (storage/app/public)
STORAGE_OUT="$BACKUP_DIR/csar-storage-$TS.tar.gz.gpg"
tar -czf - -C "$PROJECT_DIR" storage/app/public 2>/dev/null \
  | gpg --batch --yes --trust-model always -e -r "$GPG_RECIPIENT" -o "$STORAGE_OUT"
chmod 640 "$STORAGE_OUT"
echo "[$(date)] Storage : $(du -h "$STORAGE_OUT" | cut -f1)"

# Optionnel : envoi off-site
# rclone copy "$OUT" "$STORAGE_OUT" remote-csar:csar-backups/
