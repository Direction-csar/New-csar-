# Sprint 2 — Monitoring & Backup hors-site

## 1. CI GitHub Actions (déjà actif)

Le workflow `.github/workflows/tests.yml` s'exécute automatiquement à chaque
push sur `main`/`develop` et sur les pull requests vers `main`.

Il vérifie :
- Installation Composer
- Lint PHP de `app/`, `routes/`, `tests/`
- Exécution de la suite de tests PHPUnit (SQLite en mémoire)

Voir le statut : https://github.com/Direction-csar/csar.sn/actions

---

## 2. Monitoring Sentry — Activation

### Étape 1 — Créer un projet Sentry

1. Aller sur https://sentry.io → créer un compte gratuit
2. Créer un nouveau projet → choisir **Laravel**
3. Copier le **DSN** affiché (format `https://abc123@o123.ingest.sentry.io/456`)

### Étape 2 — Installer le SDK sur le serveur

```bash
cd /var/www/csar
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=VOTRE_DSN_ICI
```

### Étape 3 — Configurer `.env`

```env
SENTRY_LARAVEL_DSN=https://abc123@o123.ingest.sentry.io/456
SENTRY_TRACES_SAMPLE_RATE=0.2   # 20% des requêtes tracées (perf)
SENTRY_SEND_DEFAULT_PII=false   # ne pas envoyer les données personnelles
```

### Étape 4 — Vider les caches et tester

```bash
php artisan config:cache
php artisan sentry:test
```

Vous devriez voir l'event apparaître dans le dashboard Sentry sous 30 secondes.

> **Note** : le hook dans `bootstrap/app.php` est conditionnel — sans DSN
> configuré, Sentry est totalement inactif (aucun impact sur la prod).

---

## 3. Backup hors-site (rclone)

### Étape 1 — Installer rclone

```bash
curl https://rclone.org/install.sh | sudo bash
rclone version
```

### Étape 2 — Configurer une remote

Au choix selon votre fournisseur :

#### Option A — Backblaze B2 (~6$/mois pour 1 To, recommandé)

```bash
sudo rclone config
# n (new remote)
# name>  b2-csar
# Storage>  b2 (Backblaze B2)
# account>  <votre Key ID>
# key>  <votre Application Key>
# (laisser le reste par défaut)
```

Créer un bucket `csar-backups` sur https://b2.backblazeb2.com

#### Option B — AWS S3

```bash
sudo rclone config
# n
# name>  s3-csar
# Storage>  s3
# (renseigner access key, secret, région eu-west-1)
```

#### Option C — Google Drive (gratuit jusqu'à 15 Go)

```bash
sudo rclone config
# n
# name>  gdrive-csar
# Storage>  drive
# (suivre le flux OAuth)
```

### Étape 3 — Tester l'envoi manuel

```bash
sudo rclone copy /var/backups/csar/db/ b2-csar:csar-backups/db/ --progress
sudo rclone ls b2-csar:csar-backups/
```

### Étape 4 — Activer dans le cron

Éditer le cron root :

```bash
sudo crontab -e
```

Remplacer la ligne backup par :

```cron
0 2 * * * BACKUP_REMOTE="b2-csar:csar-backups" /var/www/csar/scripts/backup.sh >> /var/log/csar-backup.log 2>&1
```

### Étape 5 — Vérification

Le lendemain matin :

```bash
rclone ls b2-csar:csar-backups/db/ | tail -5
tail -20 /var/log/csar-backup.log
```

---

## 4. Restauration depuis backup hors-site

### Base de données

```bash
# Télécharger depuis B2
rclone copy b2-csar:csar-backups/db/csar-20260601-020000.sql.gz /tmp/

# Restaurer
gunzip -c /tmp/csar-20260601-020000.sql.gz | mysql -u root -p csar
```

### Storage

```bash
rclone copy b2-csar:csar-backups/storage/storage-20260601-020000.tar.gz /tmp/
tar -xzf /tmp/storage-20260601-020000.tar.gz -C /var/www/csar/storage/app/
chown -R www-data:www-data /var/www/csar/storage/
```

---

## Coûts indicatifs (mai 2026)

| Provider | Stockage 30 Go | Bande passante sortante |
|----------|---------------|-------------------------|
| Backblaze B2 | ~0,18 $/mois | 0,01 $/Go (gratuit jusqu'à 3x volume) |
| AWS S3 | ~0,69 $/mois | 0,09 $/Go |
| Google Drive | Gratuit (15 Go) | Inclus |

Recommandation : **Backblaze B2** (meilleur rapport prix/perf pour backups).
