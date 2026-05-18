#!/usr/bin/env bash
###############################################################################
# CSAR — Diagnostic sécurité production en 1 commande
# Usage : sudo bash scripts/security/check-prod.sh
###############################################################################
set -euo pipefail

PROJECT_DIR="${PROJECT_DIR:-/var/www/csar}"
ENV_FILE="$PROJECT_DIR/.env"

GREEN='\033[0;32m'; RED='\033[0;31m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✅ $*${NC}"; }
ko()   { echo -e "${RED}❌ $*${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $*${NC}"; }
info() { echo -e "${BLUE}ℹ️  $*${NC}"; }

echo "==========================================="
echo "  AUDIT SÉCURITÉ CSAR — Production"
echo "  Cible : $PROJECT_DIR"
echo "==========================================="

# 1) Fichier .env
echo -e "\n=== 1. Configuration .env ==="
if [[ ! -f "$ENV_FILE" ]]; then
  ko "Fichier .env introuvable" && exit 1
fi

check_env() {
  local key="$1" expected="$2" sev="${3:-CRITIQUE}"
  local actual; actual=$(grep -E "^${key}=" "$ENV_FILE" | head -n1 | cut -d= -f2-)
  if [[ "$actual" == "$expected" ]]; then
    ok "$key = $expected"
  else
    [[ "$sev" == "CRITIQUE" ]] && ko "$key = '$actual' (attendu '$expected') [$sev]" \
                              || warn "$key = '$actual' (recommandé '$expected') [$sev]"
  fi
}

check_env APP_ENV production CRITIQUE
check_env APP_DEBUG false CRITIQUE
check_env LOG_LEVEL warning AVERTISSEMENT
check_env SESSION_ENCRYPT true AVERTISSEMENT
check_env SESSION_SECURE_COOKIE true AVERTISSEMENT

# APP_KEY non vide
if grep -q "^APP_KEY=base64:" "$ENV_FILE"; then ok "APP_KEY définie"; else ko "APP_KEY manquante"; fi

# APP_URL en HTTPS
if grep -q "^APP_URL=https://" "$ENV_FILE"; then ok "APP_URL en HTTPS"; else ko "APP_URL pas en HTTPS"; fi

# 2) Permissions
echo -e "\n=== 2. Permissions fichiers ==="
ENV_PERM=$(stat -c '%a' "$ENV_FILE")
if [[ "$ENV_PERM" == "640" || "$ENV_PERM" == "600" ]]; then
  ok ".env permissions = $ENV_PERM"
else
  ko ".env permissions = $ENV_PERM (attendu 640)"
  info "Correction : sudo chmod 640 $ENV_FILE && sudo chown root:www-data $ENV_FILE"
fi

for dir in storage bootstrap/cache; do
  P=$(stat -c '%a' "$PROJECT_DIR/$dir" 2>/dev/null || echo "absent")
  if [[ "$P" =~ ^7[57]5$ ]]; then ok "$dir = $P"; else warn "$dir = $P (recommandé 775)"; fi
done

# 3) Lien storage
echo -e "\n=== 3. Lien public/storage ==="
if [[ -L "$PROJECT_DIR/public/storage" ]]; then ok "Lien symbolique présent"; else ko "Lien absent — exécuter : php artisan storage:link"; fi

# 4) PHP
echo -e "\n=== 4. PHP ==="
PHPV=$(php -r 'echo PHP_VERSION;')
info "PHP version : $PHPV"
if php -r 'exit(version_compare(PHP_VERSION,"8.2.0","<")?1:0);'; then ok "PHP >= 8.2"; else ko "PHP < 8.2 — mise à jour requise"; fi
php -m | grep -qi openssl && ok "ext openssl" || ko "openssl manquant"
php -m | grep -qi pdo_mysql && ok "ext pdo_mysql" || warn "pdo_mysql absent"

# 5) Composer audit
echo -e "\n=== 5. Composer audit ==="
if command -v composer >/dev/null 2>&1; then
  cd "$PROJECT_DIR"
  composer audit --no-dev --format=plain 2>&1 | tail -n 20 || warn "composer audit a retourné des vulnérabilités"
else
  warn "composer absent"
fi

# 6) MySQL / PostgreSQL
echo -e "\n=== 6. Base de données ==="
DB_CONNECTION=$(grep -E "^DB_CONNECTION=" "$ENV_FILE" | cut -d= -f2)
DB_USER=$(grep -E "^DB_USERNAME=" "$ENV_FILE" | cut -d= -f2)
info "Connexion : $DB_CONNECTION (user=$DB_USER)"
[[ "$DB_USER" == "root" ]] && ko "Connexion BDD via root — créer un compte dédié csar_app" || ok "Compte applicatif dédié"

# 7) Services système
echo -e "\n=== 7. Services & firewall ==="
systemctl is-active --quiet nginx       && ok "nginx actif"        || warn "nginx inactif"
systemctl is-active --quiet php8.2-fpm  && ok "php8.2-fpm actif"   || warn "php-fpm inactif (vérifier la version)"
systemctl is-active --quiet redis-server && ok "redis actif"        || warn "redis non installé (recommandé prod)"
systemctl is-active --quiet clamav-daemon && ok "ClamAV actif"      || warn "ClamAV non installé (recommandé prod)"
systemctl is-active --quiet fail2ban    && ok "fail2ban actif"     || warn "fail2ban inactif"

if command -v ufw >/dev/null 2>&1; then
  ufw status | head -n1 | grep -q "active" && ok "UFW actif" || warn "UFW inactif"
fi

# 8) Certificat SSL
echo -e "\n=== 8. Certificat SSL ==="
if [[ -f /etc/letsencrypt/live/csar.sn/fullchain.pem ]]; then
  EXPIRY=$(openssl x509 -enddate -noout -in /etc/letsencrypt/live/csar.sn/fullchain.pem | cut -d= -f2)
  ok "Certificat présent — expire : $EXPIRY"
else
  warn "Certificat Let's Encrypt introuvable"
fi

# 9) En-têtes HTTP en réel
echo -e "\n=== 9. Test en-têtes HTTP (https://csar.sn) ==="
if command -v curl >/dev/null 2>&1; then
  HEADERS=$(curl -sI https://csar.sn/ || true)
  for h in "Strict-Transport-Security" "X-Content-Type-Options" "X-Frame-Options" "Content-Security-Policy" "Referrer-Policy"; do
    echo "$HEADERS" | grep -qi "^$h:" && ok "$h présent" || warn "$h absent"
  done
fi

# 10) Tests Laravel
echo -e "\n=== 10. Caches Laravel ==="
cd "$PROJECT_DIR"
php artisan about 2>/dev/null | grep -E "Environment|Debug Mode|URL" || true

echo -e "\n==========================================="
echo "  Audit terminé."
echo "==========================================="
