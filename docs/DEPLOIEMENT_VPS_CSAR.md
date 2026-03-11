# 🚀 Guide de Déploiement sur VPS - www.csar.sn

## 📋 Informations du Serveur

- **OS** : Ubuntu 24.04.3 LTS
- **IP** : 72.61.16.34
- **Domaine** : www.csar.sn
- **Repository GitHub** : https://github.com/Direction-csar/www.csar.sn.git

---

## 🎯 Déploiement Automatique (Recommandé)

### Étape 1 : Connexion au Serveur

```bash
ssh root@72.61.16.34
```

### Étape 2 : Télécharger le Script de Déploiement

```bash
cd /tmp
wget https://raw.githubusercontent.com/Direction-csar/www.csar.sn/main/scripts/deploy/deploy_csar_vps.sh
chmod +x deploy_csar_vps.sh
```

### Étape 3 : Exécuter le Script

```bash
sudo ./deploy_csar_vps.sh
```

Le script va :
- ✅ Mettre à jour le système
- ✅ Installer PHP 8.2, MySQL, Nginx, Composer
- ✅ Configurer la base de données
- ✅ Cloner le projet depuis GitHub
- ✅ Installer les dépendances
- ✅ Configurer Nginx
- ✅ Configurer SSL avec Let's Encrypt
- ✅ Optimiser l'application
- ✅ Configurer les sauvegardes automatiques

---

## 🔧 Déploiement Manuel (Étape par Étape)

### 1. Connexion au Serveur

```bash
ssh root@72.61.16.34
```

### 2. Mise à Jour du Système

```bash
apt update && apt upgrade -y
```

### 3. Installation des Dépendances

```bash
# Ajouter le dépôt PHP
add-apt-repository -y ppa:ondrej/php
apt update

# Installer PHP 8.2 et extensions
apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    php8.2-intl php8.2-opcache

# Installer MySQL, Nginx, Composer
apt install -y mysql-server nginx composer git unzip \
    certbot python3-certbot-nginx
```

### 4. Configuration MySQL

```bash
# Démarrer MySQL
systemctl start mysql
systemctl enable mysql

# Sécuriser MySQL (remplacer 'VOTRE_MOT_DE_PASSE' par un mot de passe fort)
mysql_secure_installation

# Créer la base de données
mysql -u root -p <<EOF
CREATE DATABASE csar_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'csar_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON csar_platform.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
EOF
```

### 5. Cloner le Projet

```bash
cd /var/www
git clone https://github.com/Direction-csar/www.csar.sn.git csar
cd csar
```

### 6. Installation des Dépendances

```bash
# Dépendances Composer
composer install --no-dev --optimize-autoloader

# Dépendances NPM (si Node.js n'est pas installé)
apt install -y nodejs npm
npm install
npm run build
```

### 7. Configuration .env

```bash
# Copier le fichier .env.example
cp .env.example .env

# Éditer le fichier .env
nano .env
```

Configuration minimale dans `.env` :

```env
APP_NAME="CSAR Platform"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://www.csar.sn

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csar_platform
DB_USERNAME=csar_user
DB_PASSWORD=VOTRE_MOT_DE_PASSE

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 8. Génération de la Clé et Migrations

```bash
# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate --force

# Créer le lien symbolique pour le stockage
php artisan storage:link
```

### 9. Configuration des Permissions

```bash
chown -R www-data:www-data /var/www/csar
chmod -R 755 /var/www/csar
chmod -R 775 /var/www/csar/storage
chmod -R 775 /var/www/csar/bootstrap/cache
```

### 10. Configuration Nginx

Créer le fichier `/etc/nginx/sites-available/csar` :

```bash
nano /etc/nginx/sites-available/csar
```

Contenu :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name www.csar.sn csar.sn;
    root /var/www/csar/public;

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
    gzip_types text/plain text/css text/xml text/javascript 
               application/json application/javascript 
               application/xml+rss application/rss+xml 
               font/truetype font/opentype 
               application/vnd.ms-fontobject image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache pour les assets statiques
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Activer le site :

```bash
ln -s /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

### 11. Configuration PHP-FPM

Éditer `/etc/php/8.2/fpm/php.ini` :

```bash
nano /etc/php/8.2/fpm/php.ini
```

Modifier :
```ini
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 300
```

Redémarrer PHP-FPM :

```bash
systemctl restart php8.2-fpm
```

### 12. Configuration SSL (Let's Encrypt)

```bash
certbot --nginx -d www.csar.sn -d csar.sn
```

### 13. Optimisation Laravel

```bash
cd /var/www/csar
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 14. Configuration du Firewall

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

---

## 🔄 Mise à Jour de l'Application

Pour mettre à jour l'application après des modifications :

```bash
cd /var/www/csar
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
systemctl reload nginx
```

---

## 💾 Sauvegardes

### Sauvegarde Manuelle

```bash
# Sauvegarder la base de données
mysqldump -u csar_user -p csar_platform | gzip > /backups/csar/db_$(date +%Y%m%d_%H%M%S).sql.gz

# Sauvegarder les fichiers
tar -czf /backups/csar/files_$(date +%Y%m%d_%H%M%S).tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    /var/www/csar
```

### Sauvegarde Automatique (Cron)

Le script de déploiement configure automatiquement une sauvegarde quotidienne à 2h du matin.

Pour vérifier :
```bash
crontab -l
```

---

## 🐛 Dépannage

### Vérifier les Logs

```bash
# Logs Laravel
tail -f /var/www/csar/storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Logs PHP-FPM
tail -f /var/log/php8.2-fpm.log
```

### Vérifier le Statut des Services

```bash
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql
```

### Redémarrer les Services

```bash
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl restart mysql
```

### Vérifier les Permissions

```bash
ls -la /var/www/csar/storage
ls -la /var/www/csar/bootstrap/cache
```

### Vider les Caches

```bash
cd /var/www/csar
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔒 Sécurité

### Points Importants

1. **Firewall** : UFW est configuré pour autoriser uniquement SSH, HTTP et HTTPS
2. **SSL** : Let's Encrypt est configuré pour HTTPS
3. **Permissions** : Les fichiers sont protégés avec les bonnes permissions
4. **.env** : Ne jamais commiter le fichier .env

### Mises à Jour de Sécurité

```bash
# Mettre à jour le système régulièrement
apt update && apt upgrade -y

# Mettre à jour Composer
composer self-update

# Mettre à jour les dépendances Laravel
cd /var/www/csar
composer update --no-dev
```

---

## 📞 Support

Pour toute question ou problème :
- 📧 Email : support@csar.sn
- 📖 Documentation : Consultez `GUIDE_HEBERGEMENT.md`

---

**🎉 Votre plateforme CSAR est maintenant hébergée sur www.csar.sn !**

