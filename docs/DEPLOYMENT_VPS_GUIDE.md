# Guide de déploiement CSAR sur VPS Ubuntu 24.04

## Informations du serveur
- **OS**: Ubuntu 24.04.4 LTS
- **IP**: 187.124.41.93
- **IPv6**: 2a02:4780:7:4189::1
- **RAM**: ~11% utilisée
- **Disque**: 6.6% de 47.39GB utilisé

## Étape 1: Mise à jour du système

```bash
# Se connecter en SSH
ssh root@187.124.41.93

# Mettre à jour le système
apt update && apt upgrade -y

# Installer les paquets essentiels
apt install -y software-properties-common curl wget git unzip
```

## Étape 2: Installation de PHP 8.2+

```bash
# Ajouter le repository PHP
add-apt-repository ppa:ondrej/php -y
apt update

# Installer PHP et extensions nécessaires pour Laravel
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl \
    php8.2-redis php8.2-imagick

# Vérifier l'installation
php -v
```

## Étape 3: Installation de Composer

```bash
# Télécharger et installer Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Vérifier
composer --version
```

## Étape 4: Installation de MySQL/MariaDB

```bash
# Installer MariaDB
apt install -y mariadb-server mariadb-client

# Sécuriser l'installation
mysql_secure_installation

# Se connecter à MySQL
mysql -u root -p

# Créer la base de données et l'utilisateur
CREATE DATABASE csar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'csar_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL PRIVILEGES ON csar_db.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Étape 5: Installation de Nginx

```bash
# Installer Nginx
apt install -y nginx

# Démarrer et activer Nginx
systemctl start nginx
systemctl enable nginx

# Vérifier le statut
systemctl status nginx
```

## Étape 6: Cloner le projet depuis GitHub

```bash
# Créer le répertoire pour le projet
mkdir -p /var/www
cd /var/www

# Cloner le projet (remplacer VOTRE_TOKEN_GITHUB par votre token personnel)
git clone https://VOTRE_TOKEN_GITHUB@github.com/Direction-csar/csar.sn.git csar

# OU utiliser SSH si configuré:
# git clone git@github.com:Direction-csar/csar.sn.git csar

# Aller dans le répertoire
cd csar

# Définir les permissions
chown -R www-data:www-data /var/www/csar
chmod -R 755 /var/www/csar
chmod -R 775 /var/www/csar/storage
chmod -R 775 /var/www/csar/bootstrap/cache
```

## Étape 7: Configuration de Laravel

```bash
cd /var/www/csar

# Installer les dépendances Composer
composer install --optimize-autoloader --no-dev

# Copier le fichier .env
cp .env.example .env

# Éditer le fichier .env
nano .env
```

### Configuration .env importante:

```env
APP_NAME="CSAR"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://187.124.41.93

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csar_db
DB_USERNAME=csar_user
DB_PASSWORD=VotreMotDePasseSecurise

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuration mail (à adapter selon votre service)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@csar.sn
MAIL_FROM_NAME="${APP_NAME}"
```

### Continuer la configuration:

```bash
# Générer la clé d'application
php artisan key:generate

# Créer le lien symbolique pour le storage
php artisan storage:link

# Exécuter les migrations
php artisan migrate --force

# Optimiser Laravel pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Définir les permissions finales
chown -R www-data:www-data /var/www/csar
chmod -R 755 /var/www/csar
chmod -R 775 /var/www/csar/storage
chmod -R 775 /var/www/csar/bootstrap/cache
```

## Étape 8: Configuration Nginx

```bash
# Créer le fichier de configuration Nginx
nano /etc/nginx/sites-available/csar
```

### Contenu du fichier `/etc/nginx/sites-available/csar`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 187.124.41.93 csar.sn www.csar.sn;
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
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Optimisations
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Limites de taille
    client_max_body_size 100M;
}
```

### Activer le site:

```bash
# Créer le lien symbolique
ln -s /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/

# Supprimer le site par défaut
rm /etc/nginx/sites-enabled/default

# Tester la configuration
nginx -t

# Redémarrer Nginx
systemctl restart nginx
```

## Étape 9: Configuration du Firewall

```bash
# Installer UFW si nécessaire
apt install -y ufw

# Autoriser SSH, HTTP et HTTPS
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

# Activer le firewall
ufw --force enable

# Vérifier le statut
ufw status
```

## Étape 10: Installation de Certbot pour SSL (Optionnel mais recommandé)

```bash
# Installer Certbot
apt install -y certbot python3-certbot-nginx

# Obtenir un certificat SSL (remplacer par votre domaine)
certbot --nginx -d csar.sn -d www.csar.sn

# Le renouvellement automatique est configuré par défaut
# Tester le renouvellement
certbot renew --dry-run
```

## Étape 11: Configuration du Scheduler Laravel

```bash
# Éditer le crontab
crontab -e

# Ajouter cette ligne:
* * * * * cd /var/www/csar && php artisan schedule:run >> /dev/null 2>&1
```

## Étape 12: Configuration de Supervisor pour les queues

```bash
# Installer Supervisor
apt install -y supervisor

# Créer le fichier de configuration
nano /etc/supervisor/conf.d/csar-worker.conf
```

### Contenu du fichier:

```ini
[program:csar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/csar/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/csar/storage/logs/worker.log
stopwaitsecs=3600
```

### Activer Supervisor:

```bash
# Recharger la configuration
supervisorctl reread
supervisorctl update

# Démarrer les workers
supervisorctl start csar-worker:*

# Vérifier le statut
supervisorctl status
```

## Étape 13: Optimisations de performance

```bash
cd /var/www/csar

# Installer Redis (optionnel mais recommandé)
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

# Mettre à jour .env pour utiliser Redis
# CACHE_DRIVER=redis
# SESSION_DRIVER=redis
# QUEUE_CONNECTION=redis

# Reconfigurer après modification
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

## Étape 14: Vérification finale

```bash
# Vérifier les services
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql
systemctl status supervisor

# Vérifier les logs
tail -f /var/www/csar/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Tester l'application
curl http://187.124.41.93
```

## Accès à l'application

- **URL principale**: http://187.124.41.93
- **Admin**: http://187.124.41.93/admin/login
- **DG**: http://187.124.41.93/dg/login

## Maintenance courante

```bash
# Mettre à jour le code depuis GitHub
cd /var/www/csar
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
supervisorctl restart csar-worker:*

# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Voir les logs
tail -f storage/logs/laravel.log
```

## Sécurité importante

1. **Changer les mots de passe par défaut**
2. **Configurer le pare-feu correctement**
3. **Activer SSL avec Let's Encrypt**
4. **Sauvegardes régulières de la base de données**
5. **Mettre à jour régulièrement le système**

## Sauvegarde de la base de données

```bash
# Créer un script de sauvegarde
nano /root/backup-csar.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups"
mkdir -p $BACKUP_DIR

# Sauvegarder la base de données
mysqldump -u csar_user -p'VotreMotDePasseSecurise' csar_db > $BACKUP_DIR/csar_db_$DATE.sql

# Sauvegarder les fichiers uploadés
tar -czf $BACKUP_DIR/csar_storage_$DATE.tar.gz /var/www/csar/storage/app/public

# Garder seulement les 7 dernières sauvegardes
find $BACKUP_DIR -name "csar_db_*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "csar_storage_*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Rendre le script exécutable
chmod +x /root/backup-csar.sh

# Ajouter au crontab (sauvegarde quotidienne à 2h du matin)
crontab -e
# Ajouter: 0 2 * * * /root/backup-csar.sh >> /var/log/csar-backup.log 2>&1
```

## Dépannage

### Erreur 500
```bash
# Vérifier les logs
tail -f /var/www/csar/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Vérifier les permissions
chown -R www-data:www-data /var/www/csar
chmod -R 775 /var/www/csar/storage
chmod -R 775 /var/www/csar/bootstrap/cache
```

### Problème de base de données
```bash
# Vérifier la connexion MySQL
mysql -u csar_user -p csar_db

# Relancer les migrations
php artisan migrate:fresh --seed --force
```

### Performance lente
```bash
# Optimiser Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Vérifier Redis
redis-cli ping
```

---

**Votre application CSAR sera accessible sur http://187.124.41.93 une fois le déploiement terminé !**
