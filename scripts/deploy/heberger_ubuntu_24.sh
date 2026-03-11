#!/bin/bash
# Hébergement plateforme CSAR sur Ubuntu 24.04
# Serveur: root@srv1460145 (147.93.85.131)
# À exécuter sur le serveur après clonage ou en copiant ce script

set -e
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Exécutez en root : sudo bash $0${NC}"
    exit 1
fi

APP_DIR="/var/www/csar"
DB_NAME="csar_platform"
DB_USER="csar_user"

echo ""
echo "=============================================="
echo "  Hébergement Plateforme CSAR - Ubuntu 24.04"
echo "=============================================="
echo ""

# Domaine ou IP
read -p "URL du site (IP ou domaine, ex: 147.93.85.131 ou www.csar.sn): " DOMAIN
DOMAIN=${DOMAIN:-147.93.85.131}

# Mot de passe MySQL pour csar_user
echo ""
read -sp "Mot de passe MySQL pour $DB_USER: " DB_PASS
echo ""
read -sp "Confirmez le mot de passe: " DB_PASS2
echo ""
if [ "$DB_PASS" != "$DB_PASS2" ]; then
    echo -e "${RED}Les mots de passe ne correspondent pas.${NC}"
    exit 1
fi

echo ""
echo "1. Mise à jour et installation des paquets..."
export DEBIAN_FRONTEND=noninteractive
apt update -y
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update -y
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-opcache \
    mysql-server nginx composer git unzip certbot python3-certbot-nginx

echo ""
echo "2. Démarrage MySQL..."
systemctl start mysql
systemctl enable mysql

echo ""
echo "3. Création base de données et utilisateur..."
mysql -u root <<MYSQLEOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
MYSQLEOF

echo ""
echo "4. Clonage du projet depuis GitHub..."
if [ -d "$APP_DIR" ]; then
    echo "   Mise à jour du dépôt existant..."
    cd "$APP_DIR"
    git fetch origin
    git reset --hard origin/main
else
    cd /var/www
    git clone https://github.com/Direction-csar/csar.sn.git csar
    cd "$APP_DIR"
fi

echo ""
echo "5. Dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f "package.json" ]; then
    if command -v npm &>/dev/null; then
        echo "   Dépendances NPM..."
        npm ci 2>/dev/null || npm install
        npm run build 2>/dev/null || true
    else
        apt install -y nodejs npm
        npm ci 2>/dev/null || npm install
        npm run build 2>/dev/null || true
    fi
fi

echo ""
echo "6. Fichier .env..."
if [ -f ".env.production.example" ]; then
    cp .env.production.example .env
else
    cp .env.example .env
fi
sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN|g" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|g" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|g" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|g" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env

php artisan key:generate --force

echo ""
echo "7. Permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo ""
echo "8. Migrations et optimisations..."
php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache 2>/dev/null || true

echo ""
echo "9. Configuration Nginx..."
PHP_SOCK=$(ls /var/run/php/php*-fpm.sock 2>/dev/null | head -1)
if [ -z "$PHP_SOCK" ]; then
    PHP_SOCK="/var/run/php/php8.2-fpm.sock"
fi

cat > /etc/nginx/sites-available/csar <<NGINXEOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN _;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
NGINXEOF

ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo ""
echo "10. Mise en mode MAINTENANCE (page « Site en maintenance »)..."
php artisan down --render="errors.503"

echo ""
echo -e "${GREEN}=============================================="
echo "  Hébergement terminé"
echo "==============================================${NC}"
echo ""
echo "  URL : http://$DOMAIN"
echo "  Le site affiche la page « Site en maintenance »."
echo ""
echo "  Pour rouvrir le site : cd $APP_DIR && php artisan up"
echo "  Logs : tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
