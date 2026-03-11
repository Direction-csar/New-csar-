# Hébergement CSAR sur Ubuntu 24.04 (VPS)

Serveur : **72.61.16.34** (Ubuntu 24.04.3 LTS)

---

## Option 1 : Script automatique

Si le projet est sur GitHub, copiez le fichier `scripts/deploy/deploy_csar_vps.sh` sur le serveur puis :

```bash
chmod +x deploy_csar_vps.sh
./deploy_csar_vps.sh
```

Le script installe PHP 8.2, MySQL, Nginx, clone le dépôt, configure .env, migrations, Nginx et option SSL.

---

## Option 2 : Déploiement manuel

### 1. Installer les paquets (en root sur le serveur)

```bash
apt update && apt upgrade -y
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl mysql-server nginx composer git unzip
```

### 2. MySQL : créer la base

```bash
systemctl start mysql && systemctl enable mysql
mysql -u root -p
```

Dans MySQL :
```sql
CREATE DATABASE csar_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'csar_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL ON csar_platform.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Mettre le code dans /var/www/csar

Depuis votre PC (PowerShell) pour envoyer le projet :
```powershell
scp -r C:\xampp\htdocs\csar root@72.61.16.34:/var/www/
```

Sur le serveur :
```bash
cd /var/www/csar
composer install --no-dev --optimize-autoloader --no-interaction
cp .env.example .env
nano .env
```

Dans .env : APP_ENV=production, APP_DEBUG=false, APP_URL=http://72.61.16.34, DB_DATABASE=csar_platform, DB_USERNAME=csar_user, DB_PASSWORD=VotreMotDePasseSecurise

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data /var/www/csar
chmod -R 775 /var/www/csar/storage /var/www/csar/bootstrap/cache
```

### 4. Nginx

Créer /etc/nginx/sites-available/csar avec :

```nginx
server {
    listen 80;
    server_name 72.61.16.34;
    root /var/www/csar/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Puis :
```bash
ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

### 5. Tester

Ouvrir dans le navigateur : **http://72.61.16.34**

### 6. SSL (optionnel, avec domaine)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d votredomaine.com
```

---

## Commandes utiles

- Logs Laravel : `tail -f /var/www/csar/storage/logs/laravel.log`
- Redémarrer Nginx : `systemctl restart nginx`
- Redémarrer PHP-FPM : `systemctl restart php8.2-fpm`
