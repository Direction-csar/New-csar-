#!/bin/bash

# Script de déploiement pour VPS Ubuntu/Debian
# Usage: ./deploy_vps.sh

set -e

echo "🚀 Déploiement de la Plateforme CSAR sur VPS"
echo "============================================="
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Vérification des droits root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}❌ Veuillez exécuter ce script en tant que root (sudo)${NC}"
    exit 1
fi

# Variables
APP_DIR="/var/www/csar"
APP_USER="www-data"
DOMAIN=""
DB_NAME="csar_platform"
DB_USER="csar_user"
DB_PASS=""

# Demander le domaine
read -p "Entrez votre nom de domaine (ex: csar.sn): " DOMAIN

# Demander le mot de passe MySQL
read -sp "Entrez le mot de passe MySQL pour $DB_USER: " DB_PASS
echo ""

# Mise à jour du système
echo "📦 Mise à jour du système..."
apt update && apt upgrade -y

# Installation des dépendances
echo "📦 Installation des dépendances système..."
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    mysql-server nginx composer git unzip

# Configuration MySQL
echo "🗄️  Configuration de MySQL..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# Cloner ou copier l'application
if [ -d "$APP_DIR" ]; then
    echo "📁 Le dossier $APP_DIR existe déjà"
    read -p "Voulez-vous le supprimer et réinstaller ? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf $APP_DIR
    else
        echo -e "${YELLOW}⚠️  Installation annulée${NC}"
        exit 1
    fi
fi

echo "📥 Clonage du projet..."
cd /var/www
git clone https://github.com/sultan2096/Csar2025.git csar
cd csar

# Installation des dépendances
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        echo "📦 Installation des dépendances NPM..."
        npm install
        npm run build
    fi
fi

# Configuration .env
echo "⚙️  Configuration du fichier .env..."
if [ -f ".env.example" ]; then
    cp .env.example .env
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|g" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|g" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|g" .env
    sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env
fi

# Génération de la clé
php artisan key:generate --force

# Permissions
echo "🔐 Configuration des permissions..."
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Migrations
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

# Lien de stockage
php artisan storage:link

# Optimisation
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configuration Nginx
echo "🌐 Configuration de Nginx..."
cat > /etc/nginx/sites-available/csar <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
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

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Activer le site
ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test et rechargement Nginx
nginx -t
systemctl reload nginx

# Configuration PHP-FPM
echo "⚙️  Configuration PHP-FPM..."
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini
systemctl restart php8.2-fpm

# SSL avec Let's Encrypt
echo "🔒 Configuration SSL..."
read -p "Voulez-vous configurer SSL avec Let's Encrypt ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    apt install -y certbot python3-certbot-nginx
    certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN
fi

echo ""
echo -e "${GREEN}🎉 Déploiement terminé avec succès !${NC}"
echo ""
echo "📝 Informations importantes :"
echo "   - Application: $APP_DIR"
echo "   - Domaine: https://$DOMAIN"
echo "   - Base de données: $DB_NAME"
echo "   - Utilisateur DB: $DB_USER"
echo ""

