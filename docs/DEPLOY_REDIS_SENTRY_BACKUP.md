# Déploiement : Redis, Sentry & Backup hors-site

## 1. Redis — Cache & Sessions

### Installation

```bash
sudo apt install redis-server php8.5-redis
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Vérifier
redis-cli ping    # → PONG
php -m | grep redis
```

### Configuration .env (sur le serveur)

```bash
cd /var/www/csar
# Activer Redis pour cache et sessions
sudo sed -i 's/CACHE_STORE=database/CACHE_STORE=redis/' .env
sudo sed -i 's/SESSION_DRIVER=database/SESSION_DRIVER=redis/' .env

# Ajouter si absent
grep -q 'REDIS_CLIENT' .env || echo 'REDIS_CLIENT=phpredis' >> .env

# Appliquer
php artisan config:cache
php artisan cache:clear
```

### Sécurisation Redis

```bash
# Mot de passe Redis (optionnel mais recommandé)
sudo sed -i 's/# requirepass .*/requirepass VotreMotDePasse/' /etc/redis/redis.conf
sudo systemctl restart redis-server

# Puis dans .env :
# REDIS_PASSWORD=VotreMotDePasse
```

### Vérification

```bash
php artisan tinker --execute="Cache::put('test', 'ok', 60); echo Cache::get('test');"
# → ok
```

---

## 2. Sentry — Monitoring d'erreurs

### Création du projet

1. Aller sur https://sentry.io (ou votre instance self-hosted)
2. Créer un projet **Laravel**
3. Copier le **DSN** (format : `https://xxx@sentry.io/123`)

### Installation du package

```bash
cd /var/www/csar
composer require sentry/sentry-laravel

# Publier la config
php artisan sentry:publish
```

### Configuration .env

```bash
# Ajouter dans .env :
SENTRY_LARAVEL_DSN=https://votre-cle@sentry.io/votre-projet-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Vérification

```bash
php artisan sentry:test
# Devrait envoyer une exception de test à Sentry
```

### Ce qui est déjà en place

Le fichier `bootstrap/app.php` capture déjà les exceptions si Sentry est configuré :

```php
if (app()->bound('sentry') && config('sentry.dsn')) {
    $exceptions->reportable(function (\Throwable $e) {
        \Sentry\Laravel\Integration::captureUnhandledException($e);
    });
}
```

---

## 3. Backup hors-site avec rclone

### Installation rclone

```bash
curl https://rclone.org/install.sh | sudo bash
rclone version
```

### Configuration du remote

```bash
# Mode interactif
rclone config

# Options populaires :
# - Google Drive (gdrive)
# - Backblaze B2 (b2)
# - Wasabi / S3 (s3)
# - SFTP vers un autre serveur
```

Exemple pour **Google Drive** :
```
n       → nouveau remote
csar-backup → nom
drive   → type (Google Drive)
# Suivre les instructions pour l'auth OAuth
```

### Configuration .env

```bash
echo 'BACKUP_REMOTE=csar-backup' >> /var/www/csar/.env
echo 'BACKUP_REMOTE_PATH=csar/backups' >> /var/www/csar/.env
```

### Test

```bash
# Vérifier que rclone accède au remote
rclone lsd csar-backup:

# Lancer un backup avec upload
sudo bash /var/www/csar/scripts/backup.sh
```

### Automatisation (cron)

```bash
sudo crontab -e
# Ajouter :
0 3 * * * /bin/bash /var/www/csar/scripts/backup.sh >> /var/log/csar-backup.log 2>&1
```

---

## Commandes rapides — tout activer

```bash
# === Sur le serveur ===
cd /var/www/csar

# 1. Redis
sudo apt install -y redis-server php8.5-redis
sudo systemctl enable --now redis-server
sed -i 's/CACHE_STORE=database/CACHE_STORE=redis/' .env
sed -i 's/SESSION_DRIVER=database/SESSION_DRIVER=redis/' .env

# 2. Sentry
composer require sentry/sentry-laravel
php artisan sentry:publish
# Ajouter SENTRY_LARAVEL_DSN dans .env

# 3. rclone
curl https://rclone.org/install.sh | sudo bash
rclone config
# Configurer le remote "csar-backup"

# 4. Appliquer
php artisan config:cache
php artisan cache:clear
sudo systemctl restart php8.5-fpm

# 5. Vérifier
redis-cli ping
php artisan sentry:test
rclone lsd csar-backup:
sudo bash /var/www/csar/scripts/backup.sh
```

---

## Monitoring post-déploiement

| Service | Vérification |
|---------|-------------|
| Redis | `redis-cli info memory` |
| Sentry | Dashboard Sentry → Issues |
| Backup | `ls -la /var/backups/csar/` + `rclone ls csar-backup:csar/backups/` |
| Perf | `php artisan security:healthcheck --json` |
