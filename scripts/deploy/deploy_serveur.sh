#!/bin/bash
# À exécuter sur le serveur depuis /var/www/csar (après avoir envoyé le projet avec scp)
# Usage: cd /var/www/csar && ./scripts/deploy/deploy_serveur.sh

set -e
APP_DIR="/var/www/csar"
DOMAIN="csar.sn"
DB_NAME="csar_platform"
DB_USER="csar_user"

echo "=== Déploiement CSAR sur le serveur ==="
echo ""

if [ ! -f "$APP_DIR/composer.json" ]; then
    echo "Erreur: composer.json introuvable. Exécutez ce script depuis /var/www/csar (après avoir envoyé le projet avec scp)."
    exit 1
fi

cd "$APP_DIR"

# Mot de passe MySQL
read -sp "Mot de passe MySQL pour $DB_USER: " DB_PASS
echo ""
if [ -z "$DB_PASS" ]; then
    echo "Erreur: mot de passe vide."
    exit 1
fi

echo "[1/8] Composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "[2/8] Fichier .env..."
if [ ! -f .env ]; then
    cp .env.example .env
fi
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=3306|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env

echo "[3/8] Clé Laravel..."
php artisan key:generate --force

echo "[4/8] Migrations..."
php artisan migrate --force

echo "[5/8] Storage link..."
php artisan storage:link 2>/dev/null || true

echo "[6/8] Cache..."
php artisan config:cache
php artisan route:cache

echo "[7/8] Permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo "[8/8] Nginx..."
cat > /etc/nginx/sites-available/csar <<'NGINX'
server {
    listen 80;
    listen [::]:80;
    server_name csar.sn www.csar.sn;
    root /var/www/csar/public;
    index index.php;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    charset utf-8;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo ""
echo "=== Déploiement terminé ==="
echo "Testez : http://csar.sn ou http://72.61.16.34"
echo "Pour HTTPS : certbot --nginx -d csar.sn -d www.csar.sn"
echo ""
