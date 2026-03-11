# Déployer CSAR sur le serveur (72.61.16.34)

Suivez les étapes **dans l’ordre**. Vous êtes déjà connecté en SSH en tant que `root`.

---

## Étape 1 : Sur le serveur – Installer les paquets

Copiez-collez tout le bloc dans le terminal SSH :

```bash
export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl mysql-server nginx composer unzip git
systemctl start mysql && systemctl enable mysql
systemctl start php8.2-fpm nginx && systemctl enable php8.2-fpm nginx
```

---

## Étape 2 : Sur le serveur – Créer la base MySQL

Choisissez un **mot de passe fort** pour la base (remplacez `VotreMotDePasseSecurise` ci-dessous).

```bash
mysql -u root <<'MYSQL'
CREATE DATABASE IF NOT EXISTS csar_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'csar_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecurise';
GRANT ALL PRIVILEGES ON csar_platform.* TO 'csar_user'@'localhost';
FLUSH PRIVILEGES;
MYSQL
```

**Important :** notez ce mot de passe, vous en aurez besoin pour le fichier `.env`.

---

## Étape 3 : Mettre le code sur le serveur

**Option A – Depuis GitHub (recommandé si le projet est sur GitHub)**

Sur le serveur (SSH) :

```bash
cd /var/www
rm -rf csar
git clone https://github.com/sultan2096/Csar2025.git csar
cd csar
```

**Option B – Depuis votre PC (PowerShell)**

Ouvrez **PowerShell** sur votre PC et exécutez :

```powershell
scp -r C:\xampp\htdocs\csar root@72.61.16.34:/var/www/
```

Entrez le mot de passe SSH quand il est demandé.

---

## Étape 4 : Sur le serveur – Configurer Laravel

Revenez dans le terminal **SSH du serveur** et exécutez le script de déploiement :

```bash
cd /var/www/csar && chmod +x scripts/deploy/deploy_serveur.sh && ./scripts/deploy/deploy_serveur.sh
```

Le script vous demandera le **mot de passe MySQL** que vous avez mis à l’étape 2 (pour `csar_user`).

Si vous préférez faire à la main au lieu du script, utilisez les commandes de l’**Annexe** en bas de ce fichier.

---

## Étape 5 : Sur le serveur – Activer le site et tester

```bash
ln -sf /etc/nginx/sites-available/csar /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

Ouvrez dans un navigateur : **http://72.61.16.34** puis **http://csar.sn** (si le DNS pointe déjà vers 72.61.16.34).

---

## Étape 6 : HTTPS (SSL) avec Let's Encrypt

Une fois que **http://csar.sn** fonctionne :

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d csar.sn -d www.csar.sn
```

Indiquez votre adresse e-mail et acceptez les conditions. Après ça, le site sera en **https://csar.sn**.

---

## En cas de problème

- **Erreur 502 / page blanche :**  
  `systemctl restart php8.2-fpm nginx`

- **Voir les erreurs Laravel :**  
  `tail -50 /var/www/csar/storage/logs/laravel.log`

- **Vérifier Nginx :**  
  `nginx -t`

- **Permissions :**  
  `chown -R www-data:www-data /var/www/csar && chmod -R 775 /var/www/csar/storage /var/www/csar/bootstrap/cache`

---

## Annexe – Commandes manuelles (si vous n’utilisez pas le script)

Après avoir envoyé le projet (étape 3) :

```bash
cd /var/www/csar
composer install --no-dev --optimize-autoloader --no-interaction
cp .env.example .env
nano .env
```

Dans `.env`, modifiez au minimum :

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://csar.sn`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=csar_platform`
- `DB_USERNAME=csar_user`
- `DB_PASSWORD=VotreMotDePasseSecurise` (celui de l’étape 2)

Puis :

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
chown -R www-data:www-data /var/www/csar
chmod -R 775 storage bootstrap/cache
```

La configuration Nginx est créée par le script `deploy_serveur.sh` ; sinon créez `/etc/nginx/sites-available/csar` comme dans le fichier `HEBERGEMENT_UBUNTU_VPS.md` en mettant `server_name csar.sn www.csar.sn;`.
