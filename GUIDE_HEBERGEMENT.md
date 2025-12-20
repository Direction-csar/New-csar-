# 🚀 Guide Complet d'Hébergement - Plateforme CSAR

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Hébergement sur Hostinger](#hébergement-sur-hostinger)
3. [Hébergement sur VPS (Ubuntu/Debian)](#hébergement-sur-vps)
4. [Hébergement sur cPanel](#hébergement-sur-cpanel)
5. [Configuration SSL/HTTPS](#configuration-sslhttps)
6. [Optimisation des Performances](#optimisation-des-performances)
7. [Sécurité](#sécurité)
8. [Maintenance](#maintenance)

---

## 📦 Prérequis

### Exigences Système
- **PHP** : 8.2 ou supérieur
- **MySQL** : 5.7+ ou MariaDB 10.3+
- **Composer** : Dernière version
- **Apache** : 2.4+ avec mod_rewrite activé
- **Extensions PHP requises** :
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD ou Imagick (pour les images)
  - Zip

### Espace Disque
- Minimum : 500 MB
- Recommandé : 2 GB+

### Bandwidth
- Minimum : 10 GB/mois
- Recommandé : Illimité

---

## 🌐 Hébergement sur Hostinger

### Étape 1 : Préparer les Fichiers

1. **Compresser le projet** (exclure certains dossiers) :
```bash
# Créer un fichier .zip avec tous les fichiers sauf :
# - node_modules/
# - vendor/ (sera réinstallé)
# - .git/
# - storage/logs/*
# - .env (sera créé sur le serveur)
```

2. **Ou utiliser Git** (recommandé) :
```bash
git clone https://github.com/sultan2096/Csar2025.git
cd Csar2025
```

### Étape 2 : Upload sur Hostinger

1. Connectez-vous à votre **hPanel Hostinger**
2. Allez dans **Gestionnaire de fichiers**
3. Naviguez vers `public_html` (ou votre domaine)
4. **Supprimez** le fichier `index.html` par défaut s'il existe
5. **Uploadez** tous les fichiers du projet

### Étape 3 : Configuration de la Base de Données

1. Dans hPanel, allez dans **Bases de données MySQL**
2. Créez une nouvelle base de données : `csar_platform`
3. Créez un utilisateur MySQL
4. Notez les informations :
   - Nom de la base : `u123456789_csar`
   - Utilisateur : `u123456789_admin`
   - Mot de passe : `votre_mot_de_passe`
   - Hôte : `localhost` (ou l'adresse fournie)

### Étape 4 : Configuration du Fichier .env

1. Dans le gestionnaire de fichiers, créez/modifiez `.env`
2. Utilisez ce template :

```env
APP_NAME="CSAR Platform"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://votre-domaine.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_csar
DB_USERNAME=u123456789_admin
DB_PASSWORD=votre_mot_de_passe

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@votre-domaine.com
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Étape 5 : Installation via SSH (Recommandé)

1. Dans hPanel, activez **SSH Access**
2. Connectez-vous via SSH :
```bash
ssh u123456789@votre-domaine.com -p 65002
```

3. Naviguez vers votre dossier :
```bash
cd public_html
# ou
cd domains/votre-domaine.com/public_html
```

4. Installez les dépendances :
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

5. Générez la clé d'application :
```bash
php artisan key:generate
```

6. Exécutez les migrations :
```bash
php artisan migrate --force
```

7. Créez le lien symbolique pour le stockage :
```bash
php artisan storage:link
```

8. Optimisez l'application :
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Étape 6 : Configuration Apache

1. Dans hPanel, allez dans **Gestionnaire de fichiers**
2. Créez/modifiez `.htaccess` dans `public_html` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

3. Le fichier `public/.htaccess` est déjà configuré correctement

### Étape 7 : Permissions des Dossiers

Via SSH, configurez les permissions :
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
```

---

## 🖥️ Hébergement sur VPS (Ubuntu/Debian)

### Étape 1 : Préparation du Serveur

```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation des dépendances
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
    mysql-server nginx composer git unzip
```

### Étape 2 : Configuration MySQL

```bash
sudo mysql_secure_installation

# Créer la base de données
sudo mysql -u root -p
```

Dans MySQL :
```sql
CREATE DATABASE csar_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'csar_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
GRANT ALL PRIVILEGES ON csar_platform.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Étape 3 : Installation de l'Application

```bash
# Cloner le projet
cd /var/www
sudo git clone https://github.com/sultan2096/Csar2025.git csar
cd csar

# Installer les dépendances
sudo composer install --no-dev --optimize-autoloader
sudo npm install
sudo npm run build

# Configurer les permissions
sudo chown -R www-data:www-data /var/www/csar
sudo chmod -R 755 /var/www/csar
sudo chmod -R 775 /var/www/csar/storage
sudo chmod -R 775 /var/www/csar/bootstrap/cache
```

### Étape 4 : Configuration Nginx

Créez `/etc/nginx/sites-available/csar` :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com www.votre-domaine.com;
    root /var/www/csar/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activez le site :
```bash
sudo ln -s /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Étape 5 : Configuration PHP-FPM

Éditez `/etc/php/8.2/fpm/php.ini` :
```ini
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 300
```

Redémarrez PHP-FPM :
```bash
sudo systemctl restart php8.2-fpm
```

### Étape 6 : Configuration Laravel

```bash
cd /var/www/csar
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Étape 7 : SSL avec Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com
```

---

## 🎛️ Hébergement sur cPanel

### Étape 1 : Upload des Fichiers

1. Connectez-vous à **cPanel**
2. Allez dans **Gestionnaire de fichiers**
3. Naviguez vers `public_html`
4. Uploadez tous les fichiers

### Étape 2 : Configuration de la Base de Données

1. **MySQL Databases** → Créez une base de données
2. Créez un utilisateur MySQL
3. Ajoutez l'utilisateur à la base de données avec tous les privilèges

### Étape 3 : Configuration .env

Créez `.env` dans le dossier racine avec les informations de votre base de données cPanel.

### Étape 4 : Installation via Terminal

1. Dans cPanel, allez dans **Terminal**
2. Exécutez :
```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
```

### Étape 5 : Point d'Entrée

Dans cPanel, allez dans **Select PHP Version** et définissez le **Document Root** vers `public_html/public` ou modifiez le `.htaccess` racine pour rediriger vers `public`.

---

## 🔒 Configuration SSL/HTTPS

### Let's Encrypt (Gratuit)

```bash
# Sur VPS
sudo certbot --nginx -d votre-domaine.com

# Sur Hostinger/cPanel
# Utilisez l'outil SSL intégré dans le panneau
```

### Redirection HTTP vers HTTPS

Ajoutez dans votre configuration Nginx ou `.htaccess` :

**Nginx** :
```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    return 301 https://$server_name$request_uri;
}
```

**.htaccess** :
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## ⚡ Optimisation des Performances

### 1. Cache Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2. Optimisation Composer

```bash
composer install --no-dev --optimize-autoloader --no-scripts
```

### 3. Compression Gzip

Déjà configuré dans `.htaccess` et Nginx.

### 4. CDN pour les Assets

Configurez un CDN (Cloudflare, AWS CloudFront) pour servir les fichiers statiques.

### 5. Cache Opcode (OPcache)

Activez OPcache dans `php.ini` :
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
```

---

## 🛡️ Sécurité

### 1. Permissions des Fichiers

```bash
# Fichiers
find . -type f -exec chmod 644 {} \;

# Dossiers
find . -type d -exec chmod 755 {} \;

# Storage et cache
chmod -R 775 storage bootstrap/cache
```

### 2. Protection .env

Le fichier `.htaccess` protège déjà `.env`, mais vérifiez :
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 3. Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Mises à Jour

```bash
# Mettre à jour Composer
composer self-update

# Mettre à jour les dépendances
composer update --no-dev

# Mettre à jour Laravel
composer update laravel/framework --no-dev
```

---

## 🔧 Maintenance

### Commandes Régulières

```bash
# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser
php artisan optimize

# Vérifier les logs
tail -f storage/logs/laravel.log
```

### Sauvegarde Automatique

Créez un script de sauvegarde (`backup.sh`) :

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/csar"

# Sauvegarder la base de données
mysqldump -u csar_user -p'mot_de_passe' csar_platform > $BACKUP_DIR/db_$DATE.sql

# Sauvegarder les fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/csar

# Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

Ajoutez au cron :
```bash
0 2 * * * /path/to/backup.sh
```

---

## 📞 Support et Dépannage

### Problèmes Courants

1. **Erreur 500** :
   - Vérifiez les logs : `storage/logs/laravel.log`
   - Vérifiez les permissions
   - Vérifiez `.env`

2. **Erreur 404** :
   - Vérifiez `mod_rewrite` activé
   - Vérifiez `.htaccess` présent
   - Vérifiez la configuration Nginx/Apache

3. **Problème de connexion MySQL** :
   - Vérifiez les credentials dans `.env`
   - Vérifiez que MySQL est démarré
   - Vérifiez les permissions utilisateur

### Logs Importants

- Laravel : `storage/logs/laravel.log`
- Nginx : `/var/log/nginx/error.log`
- Apache : `/var/log/apache2/error.log`
- PHP : `/var/log/php8.2-fpm.log`

---

## ✅ Checklist de Déploiement

- [ ] Serveur configuré avec PHP 8.2+
- [ ] MySQL/MariaDB installé et configuré
- [ ] Base de données créée
- [ ] Fichiers uploadés sur le serveur
- [ ] `.env` configuré avec les bonnes informations
- [ ] `composer install` exécuté
- [ ] `php artisan key:generate` exécuté
- [ ] Migrations exécutées
- [ ] `php artisan storage:link` exécuté
- [ ] Permissions configurées (storage, cache)
- [ ] SSL/HTTPS configuré
- [ ] Caches optimisés
- [ ] Tests de fonctionnement effectués
- [ ] Sauvegardes configurées

---

**🎉 Votre plateforme CSAR est maintenant hébergée et prête à être utilisée !**

Pour toute question, consultez la documentation Laravel : https://laravel.com/docs

