# Héberger la plateforme CSAR sur Ubuntu 24.04 (147.93.85.131)

Tout le code est poussé sur GitHub. Suivez l’une des deux méthodes ci-dessous **sur le serveur** (en SSH, en tant que root).

---

## Méthode 1 : Script automatique (recommandé)

Sur le serveur, récupérez le script depuis le dépôt puis lancez-le.

```bash
cd /var/www
rm -rf csar 2>/dev/null
git clone https://github.com/Direction-csar/csar.sn.git csar
cd csar
bash scripts/deploy/heberger_ubuntu_24.sh
```

Le script vous demandera :
- **URL du site** : entrez `147.93.85.131` ou votre domaine (ex. `www.csar.sn`)
- **Mot de passe MySQL** pour l’utilisateur `csar_user` (à saisir deux fois)

Il installera Nginx, PHP 8.2, MySQL, clonera le projet, configurera Laravel, la base, Nginx, et **activera la page « Site en maintenance »** à la fin.

---

## Méthode 2 : Commandes pas à pas

Si vous préférez exécuter les commandes vous-même (ou si le script échoue).

### 1. Installer les paquets

```bash
apt update -y
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update -y
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
  php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl \
  mysql-server nginx composer git unzip certbot python3-certbot-nginx
systemctl start mysql
systemctl enable mysql
```

### 2. Base de données (remplacez VOTRE_MOT_DE_PASSE)

```bash
mysql -u root -e "
CREATE DATABASE IF NOT EXISTS csar_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'csar_user'@'localhost' IDENTIFIED BY 'VOTRE_MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON csar_platform.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
"
```

### 3. Cloner le projet et configurer Laravel

```bash
cd /var/www
git clone https://github.com/Direction-csar/csar.sn.git csar
cd csar

composer install --no-dev --optimize-autoloader --no-interaction
cp .env.production.example .env
# Éditez .env : DB_PASSWORD=..., APP_URL=http://147.93.85.131 (ou votre domaine)
nano .env

php artisan key:generate --force
chown -R www-data:www-data /var/www/csar
chmod -R 775 storage bootstrap/cache
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

### 4. Nginx

Créez le vhost (remplacez 147.93.85.131 par votre domaine si besoin) :

```bash
cat > /etc/nginx/sites-available/csar << 'EOF'
server {
    listen 80;
    server_name 147.93.85.131 _;
    root /var/www/csar/public;
    index index.php;
    charset utf-8;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

### 5. Mettre le site en maintenance

```bash
cd /var/www/csar
php artisan down --render="errors.503"
```

---

## Après le déploiement

- **Voir la page de maintenance** : ouvrez http://147.93.85.131 (ou votre domaine).
- **Rouvrir le site** : `cd /var/www/csar && php artisan up`
- **Logs** : `tail -f /var/www/csar/storage/logs/laravel.log`
- **SSL (optionnel)** : `certbot --nginx -d www.csar.sn -d csar.sn` (après avoir pointé le DNS vers 147.93.85.131)
