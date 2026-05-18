#!/usr/bin/env bash
###############################################################################
# CSAR — Migration des caches/sessions/queues vers Redis (production)
# Usage : sudo bash scripts/setup/migrate-to-redis.sh
###############################################################################
set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-/var/www/csar}"
ENV_FILE="$PROJECT_DIR/.env"

echo "=== 1. Installation Redis + extension PHP ==="
sudo apt update
sudo apt install -y redis-server php8.2-redis || sudo apt install -y redis-server php-redis

echo "=== 2. Sécuriser Redis (bind 127.0.0.1 + password) ==="
REDIS_PASS=$(openssl rand -base64 32 | tr -d '/+=')
sudo sed -i 's/^# *requirepass .*/requirepass '"$REDIS_PASS"'/' /etc/redis/redis.conf || \
  echo "requirepass $REDIS_PASS" | sudo tee -a /etc/redis/redis.conf >/dev/null
sudo sed -i 's/^bind .*/bind 127.0.0.1 ::1/' /etc/redis/redis.conf
sudo systemctl enable --now redis-server
sudo systemctl restart redis-server

echo "=== 3. Mise à jour .env ==="
update_env() {
  local key="$1" val="$2"
  if grep -qE "^${key}=" "$ENV_FILE"; then
    sudo sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
  else
    echo "${key}=${val}" | sudo tee -a "$ENV_FILE" >/dev/null
  fi
}

update_env CACHE_STORE redis
update_env SESSION_DRIVER redis
update_env QUEUE_CONNECTION redis
update_env REDIS_HOST 127.0.0.1
update_env REDIS_PORT 6379
update_env REDIS_PASSWORD "$REDIS_PASS"
update_env REDIS_CLIENT phpredis

echo "=== 4. Caches Laravel ==="
cd "$PROJECT_DIR"
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo "=== 5. Test Redis ==="
redis-cli -a "$REDIS_PASS" ping

echo "=============================================="
echo "✅ Migration Redis terminée."
echo "Mot de passe Redis stocké dans .env (à sauvegarder en lieu sûr)."
echo "=============================================="
