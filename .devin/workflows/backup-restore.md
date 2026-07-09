---
description: Sauvegarder et restaurer la plateforme CSAR
---

## Où sont stockées les données ?

Toutes les données de la plateforme se trouvent sur le serveur **192.168.2.141** dans `/var/www/csar` :

- **Code source** : géré par Git/GitHub.
- **Base de données** : configurée dans le fichier `.env` (MySQL/MariaDB ou SQLite).
- **Fichiers uploadés** (photos du personnel, contrats PDF, documents) : dossier `storage/app`.
- **Configuration secrète** : fichier `.env` (mot de passe base, clés API, etc.).

## Sauvegarder la plateforme

1. Se connecter au serveur (SSH) ou exécuter le script à distance.
2. Lancer le script de sauvegarde :

```bash
ssh msow@192.168.2.141
cd /var/www/csar
bash backup-platform.sh
```

3. Le script crée un dossier `/var/www/csar/backups/csar_backup_YYYYMMDD_HHMMSS/` contenant :
   - `database.sql` : export de la base MySQL/MariaDB.
   - `storage_app.tar.gz` : tous les fichiers uploadés.
   - `env_backup` : copie du fichier `.env`.
   - `source.tar.gz` : archive du code source (depuis Git).
   - `backup-info.txt` : récapitulatif.

4. **Copier les sauvegardes hors du serveur** pour éviter une perte en cas de panne :
   - Sur un NAS, une clé USB, un autre serveur, ou un cloud (Google Drive, Dropbox, S3, etc.).
   - Exemple avec `scp` :

```bash
scp -r msow@192.168.2.141:/var/www/csar/backups/csar_backup_YYYYMMDD_HHMMSS /chemin/local/
```

## Automatiser la sauvegarde (recommandé)

Un script d'installation automatique est disponible. Exécutez-le sur le serveur :

```bash
ssh msow@192.168.2.141
cd /var/www/csar
bash install-auto-backup.sh
```

Cela configure une tâche cron qui lance une sauvegarde complète **toutes les 5 heures**.

Le script `backup-platform.sh` conserve automatiquement les **15 dernières sauvegardes** pour éviter de remplir le disque.

Vérifier les logs :

```bash
tail -f /var/log/csar-backup.log
```

**Astuce** : pour une vraie protection, copier automatiquement les sauvegardes vers un emplacement externe (cloud, autre serveur) avec `rsync` ou `rclone`.

## Restaurer la plateforme sur un nouveau serveur

### Prérequis
- Un serveur avec PHP, MySQL/MariaDB (ou SQLite), Composer, Node.js, Git.
- Le fichier `.env` de sauvegarde.
- Le dump SQL et l’archive `storage_app.tar.gz`.

### Étapes

1. Installer le code source sur le nouveau serveur :

```bash
cd /var/www
git clone https://github.com/Direction-csar/csar.sn.git csar
cd csar
composer install --no-dev --optimize-autoloader
```

2. Copier le backup sur le serveur :

```bash
scp -r /chemin/local/csar_backup_YYYYMMDD_HHMMSS msow@IP_DU_NOUVEAU_SERVEUR:/tmp/
```

3. Restaurer les données :

```bash
bash restore-platform.sh /tmp/csar_backup_YYYYMMDD_HHMMSS
```

4. Finaliser l’installation :

```bash
php artisan key:generate  # si le .env n'a pas de APP_KEY
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

5. Vérifier l’accès au portail.

## Vérifier une sauvegarde

Avant de compter sur une sauvegarde, il est conseillé de la vérifier :

```bash
ls -lh /var/backups/csar/csar_backup_YYYYMMDD_HHMMSS
# Vérifier que database.sql n'est pas vide
wc -l /var/backups/csar/csar_backup_YYYYMMDD_HHMMSS/database.sql
```

## En cas de panne

1. Identifier la cause (serveur, disque, base de données corrompue, etc.).
2. Utiliser la **dernière sauvegarde** disponible hors site.
3. Suivre la procédure de restauration ci-dessus.
4. Une fois le service rétabli, vérifier les données critiques (agents, contrats, documents).
