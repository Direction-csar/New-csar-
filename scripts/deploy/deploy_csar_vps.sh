#!/bin/bash

# Script de déploiement pour le VPS CSAR (www.csar.sn)
# Serveur: Ubuntu 24.04.3 LTS
# IP: 72.61.16.34
# Domaine: www.csar.sn

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
DOMAIN="www.csar.sn"
DB_NAME="csar_platform"
DB_USER="csar_user"
DB_PASS=""

# Demander le mot de passe MySQL
echo -e "${YELLOW}⚠️  Configuration de la base de données${NC}"
read -sp "Entrez le mot de passe MySQL pour $DB_USER: " DB_PASS
echo ""
read -sp "Confirmez le mot de passe: " DB_PASS_CONFIRM
echo ""

if [ "$DB_PASS" != "$DB_PASS_CONFIRM" ]; then
    echo -e "${RED}❌ Les mots de passe ne correspondent pas${NC}"
    exit 1
fi

# Mise à jour du système
echo "📦 Mise à jour du système..."
export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y

# Installation des dépendances
echo "📦 Installation des dépendances système..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update

apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-opcache mysql-server nginx composer git unzip \
    certbot python3-certbot-nginx

# Configuration MySQL
echo "🗄️  Configuration de MySQL..."
systemctl start mysql
systemctl enable mysql

# Sécurisation MySQL
mysql -u root <<EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASS';
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# Cloner ou mettre à jour l'application
if [ -d "$APP_DIR" ]; then
    echo "📁 Mise à jour du projet existant..."
    cd $APP_DIR
    git pull origin main
else
    echo "📥 Clonage du projet depuis GitHub..."
    cd /var/www
    git clone https://github.com/Direction-csar/www.csar.sn.git csar
    cd csar
fi

# Installation des dépendances
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        echo "📦 Installation des dépendances NPM..."
        npm install
        npm run build
    else
        echo -e "${YELLOW}⚠️  NPM n'est pas installé, installation en cours...${NC}"
        apt install -y nodejs npm
        npm install
        npm run build
    fi
fi

# Configuration .env
echo "⚙️  Configuration du fichier .env..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        echo -e "${RED}❌ Fichier .env.example introuvable${NC}"
        exit 1
    fi
fi

# Mise à jour du .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|g" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|g" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|g" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" .env

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
    server_name $DOMAIN csar.sn;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    # Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Sécurité
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
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
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini

# Configuration OPcache
cat >> /etc/php/8.2/fpm/php.ini <<EOF

[opcache]
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
EOF

systemctl restart php8.2-fpm

# Configuration du firewall
echo "🔥 Configuration du firewall..."
if command -v ufw &> /dev/null; then
    ufw --force enable
    ufw allow 22/tcp
    ufw allow 80/tcp
    ufw allow 443/tcp
    echo -e "${GREEN}✅ Firewall configuré${NC}"
fi

# SSL avec Let's Encrypt
echo "🔒 Configuration SSL avec Let's Encrypt..."
read -p "Voulez-vous configurer SSL avec Let's Encrypt ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    certbot --nginx -d $DOMAIN -d csar.sn --non-interactive --agree-tos --email admin@$DOMAIN --redirect
    echo -e "${GREEN}✅ SSL configuré${NC}"
else
    echo -e "${YELLOW}⚠️  SSL ignoré, vous pouvez le configurer plus tard avec:${NC}"
    echo "   certbot --nginx -d $DOMAIN -d csar.sn"
fi

# Création du script de sauvegarde
echo "💾 Configuration des sauvegardes..."
mkdir -p /backups/csar
cat > /backups/csar/backup.sh <<'BACKUP_SCRIPT'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/csar"
APP_DIR="/var/www/csar"
DB_NAME="csar_platform"
DB_USER="csar_user"
DB_PASS="$DB_PASS"

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz
tar -czf $BACKUP_DIR/files_$DATE.tar.gz --exclude='node_modules' --exclude='vendor' --exclude='.git' $APP_DIR

find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
BACKUP_SCRIPT

chmod +x /backups/csar/backup.sh

# Ajouter au cron (sauvegarde quotidienne à 2h)
(crontab -l 2>/dev/null; echo "0 2 * * * /backups/csar/backup.sh") | crontab -

echo ""
echo -e "${GREEN}🎉 Déploiement terminé avec succès !${NC}"
echo ""
echo "📝 Informations importantes :"
echo "   - Application: $APP_DIR"
echo "   - Domaine: https://$DOMAIN"
echo "   - Base de données: $DB_NAME"
echo "   - Utilisateur DB: $DB_USER"
echo "   - Sauvegardes: /backups/csar (quotidiennes à 2h)"
echo ""
echo "🔧 Commandes utiles :"
echo "   - Logs Laravel: tail -f $APP_DIR/storage/logs/laravel.log"
echo "   - Logs Nginx: tail -f /var/log/nginx/error.log"
echo "   - Redémarrer Nginx: systemctl restart nginx"
echo "   - Redémarrer PHP-FPM: systemctl restart php8.2-fpm"
echo ""

