# Plan de Remédiation — Plateforme CSAR

**Date :** 13 mai 2026
**Référence :** `docs/audits/AUDIT_SECURITE_CSAR_2026.md`

---

## 1. Correctifs automatiques déjà appliqués ✅

Les fichiers suivants ont été créés ou modifiés automatiquement lors de l'audit :

| Fichier | Modification |
|---|---|
| `app/Http/Middleware/SecurityHeaders.php` | CSP stricte avec nonce, HSTS, COOP/COEP, Permissions-Policy restrictive |
| `config/cors.php` | Configuration CORS restrictive pour API |
| `app/Http/Controllers/Auth/PasswordResetController.php` | Token reset 30 min au lieu de 60 |
| `public/robots.txt` | Blocage admin, ctc, dg, api, password/reset |
| `public/.well-known/security.txt` | Politique de sécurité RFC 9116 |
| `nginx-csar.conf` | Harmonisation headers (suppression doublons) |
| `app/Policies/NewsPolicy.php` | Politique d'accès actualités |
| `app/Policies/SimReportPolicy.php` | Politique d'accès rapports SIM |
| `app/Policies/DemandePolicy.php` | Politique d'accès demandes |
| `app/Policies/StockPolicy.php` | Politique d'accès stocks |
| `app/Providers/AuthServiceProvider.php` | Enregistrement des 4 policies |
| `app/Http/Controllers/Admin/ActualitesController.php` | Validations mimetypes durcies |
| `app/Http/Controllers/Admin/SimReportsController.php` | Validations mimetypes durcies |
| `app/Jobs/ScanUploadedFileJob.php` | Job ClamAV pour scan antivirus |
| `scripts/security/check-prod.sh` | Script audit serveur |
| `scripts/setup/migrate-to-redis.sh` | Script migration Redis |
| `scripts/backup/encrypted_backup.sh` | Script sauvegarde chiffrée |
| `.github/workflows/security-audit.yml` | Workflow CI audit sécurité |

---

## 2. Actions manuelles à exécuter sur le serveur

### 2.1 Audit rapide (5 min)

Exécuter sur le serveur :

```bash
cd /var/www/csar
bash scripts/security/check-prod.sh
```

Ce script vérifie :
- Configuration `.env` (`APP_DEBUG`, `APP_ENV`, `LOG_LEVEL`, `SESSION_ENCRYPT`)
- Permissions `.env`, `storage/`, `bootstrap/cache/`
- PHP version et extensions
- Composer audit
- MySQL configuration
- Services (nginx, php-fpm, redis, clamav, fail2ban)
- Certificat SSL
- En-têtes HTTP

---

### 2.2 Corrections `.env` (2 min)

```bash
cd /var/www/csar
sudo nano .env
```

Vérifier / modifier les lignes suivantes :

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
```

Puis :

```bash
php artisan config:clear
php artisan config:cache
```

---

### 2.3 Permissions `.env` (2 min)

```bash
sudo chown root:www-data /var/www/csar/.env
sudo chmod 640 /var/www/csar/.env
```

---

### 2.4 Compte MySQL applicatif (10 min)

Si le compte `csar_app` n'existe pas encore :

```bash
sudo mysql
```

Dans l'invite MySQL :

```sql
CREATE USER 'csar_app'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON csar.* TO 'csar_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Puis modifier `.env` :

```env
DB_USERNAME=csar_app
DB_PASSWORD=MOT_DE_PASSE_FORT
```

---

### 2.5 Installation ClamAV (5 min)

```bash
sudo apt update
sudo apt install -y clamav clamav-daemon
sudo systemctl enable --now clamav-daemon
sudo freshclam
```

---

### 2.6 Installation Redis (optionnel, recommandé) (5 min)

Exécuter le script fourni :

```bash
cd /var/www/csar
sudo bash scripts/setup/migrate-to-redis.sh
```

Cela :
- Installe Redis et l'extension PHP
- Sécurise Redis (mot de passe, bind 127.0.0.1)
- Modifie `.env` pour utiliser Redis (cache, session, queue)
- Recharge les caches Laravel

---

### 2.7 Configuration sauvegardes chiffrées (10 min)

```bash
# Créer le dossier de sauvegardes
sudo mkdir -p /backups/csar
sudo chown www-data:www-data /backups/csar

# Générer une paire de clés GPG pour l'équipe ops
sudo -u www-data gpg --full-generate-key
# (suivre les instructions, email : ops@csar.sn)

# Ajouter la clé au fichier de sauvegarde
nano scripts/backup/encrypted_backup.sh
# Modifier GPG_RECIPIENT="ops@csar.sn" si nécessaire

# Tester le script
sudo -u www-data bash scripts/backup/encrypted_backup.sh
```

Ajouter au cron :

```bash
sudo nano /etc/cron.d/csar-backup
```

Contenu :

```
0 2 * * * www-data /var/www/csar/scripts/backup/encrypted_backup.sh
```

---

### 2.8 Firewall et Fail2ban (10 min)

```bash
# UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Fail2ban
sudo apt install -y fail2ban
sudo systemctl enable --now fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

Dans `jail.local`, activer au minimum :

```ini
[sshd]
enabled = true
bantime = 3600
maxretry = 5

[nginx-http-auth]
enabled = true
```

---

### 2.9 Mises à jour automatiques (5 min)

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
# Sélectionner "Yes" pour les mises à jour automatiques de sécurité
```

---

## 3. Application des Policies dans les contrôleurs

Les policies sont créées mais doivent être **explicitement appelées** dans les contrôleurs. Exemple pour `ActualitesController` :

### 3.1 Ajouter les appels `authorize()`

Dans `app/Http/Controllers/Admin/ActualitesController.php` :

```php
use Illuminate\Auth\Access\AuthorizationException;

// Dans la méthode show() :
public function show(Request $request, $id)
{
    $actualite = \App\Models\News::findOrFail($id);
    $this->authorize('view', $actualite);  // ← Ajouter
    // ... suite
}

// Dans la méthode edit() :
public function edit(Request $request, $id)
{
    $actualite = \App\Models\News::findOrFail($id);
    $this->authorize('update', $actualite);  // ← Ajouter
    // ... suite
}

// Dans la méthode update() :
public function update(Request $request, $id)
{
    $actualite = \App\Models\News::findOrFail($id);
    $this->authorize('update', $actualite);  // ← Ajouter
    // ... suite
}

// Dans la méthode destroy() :
public function destroy(Request $request, $id)
{
    $actualite = \App\Models\News::findOrFail($id);
    $this->authorize('delete', $actualite);  // ← Ajouter
    // ... suite
}
```

Faire de même pour :
- `SimReportsController` (policy `SimReport`)
- `DemandeController` (policy `Demande`)
- `StockController` (policy `Stock`)

---

## 4. Activation du scan ClamAV sur les uploads

Dans les contrôleurs d'upload (après `store()`), ajouter :

```php
use App\Jobs\ScanUploadedFileJob;

// Après $news = News::create($data);
if ($request->hasFile('featured_image')) {
    ScanUploadedFileJob::dispatch(
        'public',
        $news->featured_image,
        News::class,
        $news->id
    );
}
```

---

## 5. Intégration CI/CD (GitHub Actions)

Le workflow `.github/workflows/security-audit.yml` est déjà créé. Pour l'activer :

1. Pousser le code sur GitHub.
2. Activer les Actions dans le dépôt.
3. Le workflow s'exécutera automatiquement sur chaque push/PR et chaque lundi à 06h UTC.

---

## 6. Checklist de validation avant lancement officiel

- [ ] `APP_DEBUG=false` sur le serveur
- [ ] `APP_ENV=production`
- [ ] `LOG_LEVEL=warning`
- [ ] `SESSION_ENCRYPT=true`
- [ ] Permissions `.env` = 640
- [ ] Compte MySQL `csar_app` créé (pas root)
- [ ] Redis installé (optionnel mais recommandé)
- [ ] ClamAV installé
- [ ] Sauvegardes chiffrées configurées + cron actif
- [ ] Firewall UFW actif
- [ ] Fail2ban actif
- [ ] `unattended-upgrades` actif
- [ ] Policies appliquées dans les contrôleurs
- [ ] Scan ClamAV intégré sur les uploads
- [ ] Certificat SSL valide
- [ ] Audit serveur `check-prod.sh` sans erreur critique
- [ ] Pentest externe réalisé (recommandé)

---

## 7. Support

Pour toute question sur ce plan de remédiation, consulter :
- `docs/audits/AUDIT_SECURITE_CSAR_2026.md` — rapport complet
- `scripts/security/check-prod.sh` — diagnostic serveur

---

**Fin du plan de remédiation.**
