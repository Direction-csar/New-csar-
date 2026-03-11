# ⚙️ CONFIGURATION ENVIRONNEMENT - CSAR ADMIN

**Guide complet de configuration des variables d'environnement**

---

## 📝 FICHIER .env COMPLET

Copiez et adaptez les sections suivantes dans votre fichier `.env` :

### ✅ OBLIGATOIRE - Application de Base

```env
# Application
APP_NAME="CSAR Platform"
APP_ENV=production
APP_KEY=base64:XXXXX_GENERER_AVEC_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://csar.sn

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=csar_production
DB_USERNAME=csar_user
DB_PASSWORD=mot_de_passe_fort_123!

# Email SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@csar.sn
MAIL_FROM_NAME="CSAR Platform"

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### ⚠️ FORTEMENT RECOMMANDÉ - Backups et Monitoring

```env
# Backups automatiques
BACKUP_CLOUD_DISK=s3
BACKUP_RETENTION_DAYS=30
BACKUP_NOTIFICATION_EMAILS=admin@csar.sn,it@csar.sn

# AWS S3 pour backups
AWS_ACCESS_KEY_ID=AKIAXXXXXXXXX
AWS_SECRET_ACCESS_KEY=xxxxxxxxxxxxxxxx
AWS_DEFAULT_REGION=us-east-1
AWS_BACKUP_BUCKET=csar-backups
```

### 💡 OPTIONNEL - Newsletter

**Choisir UN seul provider** :

```env
# Newsletter provider
NEWSLETTER_PROVIDER=mailchimp
NEWSLETTER_FROM_NAME=CSAR
NEWSLETTER_REPLY_TO=noreply@csar.sn
```

**Option A - Mailchimp** :
```env
NEWSLETTER_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxx-us1
NEWSLETTER_LIST_ID=abc123def
```

**Option B - SendGrid** :
```env
NEWSLETTER_API_KEY=SG.xxxxxxxxxxxxxxxxxxxxx
NEWSLETTER_LIST_ID=list-id
NEWSLETTER_SENDER_ID=sender-id
```

**Option C - Brevo (Sendinblue)** :
```env
NEWSLETTER_API_KEY=xkeysib-xxxxxxxxxxxxxxxxxxxxx
NEWSLETTER_LIST_ID=12
```

### 💡 OPTIONNEL - SMS

**Choisir UN seul provider** :

```env
# SMS provider
SMS_PROVIDER=twilio
SMS_MAX_PER_MONTH=1000
```

**Option A - Twilio (Recommandé)** :
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_FROM_NUMBER=+221701234567
```

**Option B - Vonage (Nexmo)** :
```env
VONAGE_API_KEY=xxxxxxxx
VONAGE_API_SECRET=xxxxxxxxxxxxxxxx
VONAGE_FROM=CSAR
```

**Option C - InfoBip** :
```env
INFOBIP_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
INFOBIP_BASE_URL=https://api.infobip.com
INFOBIP_FROM=CSAR
```

**Option D - Africa's Talking** :
```env
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AFRICASTALKING_FROM=CSAR
```

### 💡 AVANCÉ - Organisation

```env
# Informations organisation
ORG_NAME=CSAR
ORG_FULL_NAME="Commissariat à la Sécurité Alimentaire et à la Résilience"
ORG_COUNTRY="République du Sénégal"
ORG_MOTTO="Un Peuple, Un But, Une Foi"
ORG_EMAIL=contact@csar.sn
ORG_PHONE="+221 XX XXX XX XX"
ORG_ADDRESS="Dakar, Sénégal"
ORG_WEBSITE=https://csar.sn
```

---

## 📋 GUIDE DE CONFIGURATION PAR ENVIRONNEMENT

### 🖥️ Développement Local

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_DATABASE=csar_dev
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
SESSION_SECURE_COOKIE=false
```

### 🧪 Staging (Pré-production)

```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.csar.sn

DB_DATABASE=csar_staging
MAIL_MAILER=smtp
SESSION_SECURE_COOKIE=true
```

### 🚀 Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://csar.sn

DB_DATABASE=csar_production
MAIL_MAILER=smtp
SESSION_SECURE_COOKIE=true

# Tous les services activés
BACKUP_CLOUD_DISK=s3
NEWSLETTER_PROVIDER=mailchimp
SMS_PROVIDER=twilio
```

---

## 🔑 OBTENIR LES CLÉS API

### Mailchimp
1. Créer un compte sur https://mailchimp.com
2. Account > Extras > API keys
3. Generate New Key
4. Copier la clé (format: xxxxx-us1)

### SendGrid
1. Créer un compte sur https://sendgrid.com
2. Settings > API Keys
3. Create API Key
4. Copier la clé (commence par SG.)

### Twilio
1. Créer un compte sur https://twilio.com
2. Console > Account SID et Auth Token
3. Acheter un numéro (+221 pour Sénégal)
4. Copier les credentials

### AWS S3
1. Créer un compte AWS
2. IAM > Users > Create User
3. Attacher politique AmazonS3FullAccess
4. Security credentials > Create access key
5. Copier Access Key ID et Secret Access Key

---

## ✅ VALIDATION DE LA CONFIGURATION

### Test de Configuration

```bash
# 1. Vérifier .env
php artisan config:show

# 2. Test connexion base de données
php artisan migrate:status

# 3. Test email
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# 4. Test backups
php scripts/backup/database_backup.php

# 5. Test monitoring
php artisan system:monitor

# 6. Test newsletter (si configurée)
php artisan tinker
>>> $ns = new \App\Services\NewsletterService();
>>> $ns->subscribe('test@example.com', 'Test', 'User');

# 7. Test SMS (si configuré)
php artisan tinker
>>> $sms = new \App\Services\SmsService();
>>> $sms->send('+221701234567', 'Test CSAR');
```

### Checklist de Validation

- [ ] APP_KEY généré
- [ ] Connexion DB OK
- [ ] Emails fonctionnels
- [ ] HTTPS activé (production)
- [ ] Backups testés
- [ ] Monitoring actif
- [ ] Newsletter testée (si activée)
- [ ] SMS testé (si activé)

---

## ⚠️ SÉCURITÉ

### Ne JAMAIS commiter .env

```bash
# Vérifier que .env est dans .gitignore
cat .gitignore | grep .env
```

### Mot de Passe Fort

```
Recommandations :
- Minimum 12 caractères
- Majuscules + minuscules
- Chiffres + caractères spéciaux
- Pas de mots du dictionnaire
- Unique par service

Générateur : https://passwordsgenerator.net/
```

### Rotation des Clés

| Clé | Fréquence de rotation |
|-----|----------------------|
| APP_KEY | Jamais (sauf compromission) |
| DB_PASSWORD | Tous les 90 jours |
| API Keys | Tous les 6 mois |
| Clés AWS | Tous les 90 jours |

---

## 🆘 DÉPANNAGE

### Erreur : "No application encryption key"
```bash
php artisan key:generate
```

### Erreur : "Access denied for user"
```bash
# Vérifier DB_* dans .env
# Tester connexion MySQL :
mysql -u csar_user -p csar_production
```

### Erreur : "failed to open stream"
```bash
# Permissions storage
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage
```

### Erreur Email non envoyé
```bash
# Tester config mail :
php artisan tinker
>>> config('mail.mailers.smtp');
```

---

## 📖 RESSOURCES

- **Documentation** : [README_DOCUMENTATION_COMPLETE.md](README_DOCUMENTATION_COMPLETE.md)
- **Déploiement** : [GUIDE_DEPLOIEMENT_PRODUCTION.md](GUIDE_DEPLOIEMENT_PRODUCTION.md)
- **Support** : support@csar.sn

---

© 2025 CSAR - Configuration complète pour production











































