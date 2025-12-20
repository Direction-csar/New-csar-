# 🚀 Guide de Déploiement Rapide - CSAR Platform

## 📋 Vue d'Ensemble

Ce guide vous permet de déployer rapidement la Plateforme CSAR sur différents types d'hébergement.

## 🎯 Options d'Hébergement

### 1. 🌐 Hostinger (Hébergement Partagé)
**Recommandé pour** : Démarrage rapide, budget limité

- ✅ Facile à configurer
- ✅ Support inclus
- ✅ SSL gratuit
- ⚠️ Limitations de ressources

**Guide complet** : [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md#hébergement-sur-hostinger)

### 2. 🖥️ VPS (Ubuntu/Debian)
**Recommandé pour** : Contrôle total, meilleures performances

- ✅ Contrôle complet
- ✅ Performances optimales
- ✅ Scalabilité
- ⚠️ Nécessite des connaissances techniques

**Guide complet** : [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md#hébergement-sur-vps)

### 3. 🎛️ cPanel
**Recommandé pour** : Interface graphique, facilité d'utilisation

- ✅ Interface graphique
- ✅ Outils intégrés
- ✅ Gestion simplifiée
- ⚠️ Coût plus élevé

**Guide complet** : [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md#hébergement-sur-cpanel)

---

## ⚡ Déploiement Rapide

### Option A : Script Automatique (Hostinger)

```bash
# Via SSH sur Hostinger
chmod +x scripts/deploy/deploy_hostinger.sh
./scripts/deploy/deploy_hostinger.sh
```

### Option B : Script Automatique (VPS)

```bash
# Sur VPS Ubuntu/Debian
chmod +x scripts/deploy/deploy_vps.sh
sudo ./scripts/deploy/deploy_vps.sh
```

### Option C : Déploiement Manuel

Suivez les étapes dans [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md)

---

## 📝 Checklist de Déploiement

### Avant le Déploiement
- [ ] Serveur avec PHP 8.2+ configuré
- [ ] MySQL/MariaDB installé
- [ ] Composer installé
- [ ] Accès SSH (pour VPS)
- [ ] Nom de domaine configuré

### Pendant le Déploiement
- [ ] Fichiers uploadés sur le serveur
- [ ] Base de données créée
- [ ] Fichier `.env` configuré
- [ ] Dépendances installées (`composer install`)
- [ ] Clé d'application générée (`php artisan key:generate`)
- [ ] Migrations exécutées (`php artisan migrate`)
- [ ] Lien de stockage créé (`php artisan storage:link`)
- [ ] Permissions configurées (storage, cache)
- [ ] Caches optimisés

### Après le Déploiement
- [ ] SSL/HTTPS configuré
- [ ] Tests de fonctionnement effectués
- [ ] Sauvegardes automatiques configurées
- [ ] Monitoring configuré (optionnel)

---

## 🔧 Configuration .env

Exemple de configuration `.env` pour la production :

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
DB_DATABASE=nom_de_votre_base
DB_USERNAME=nom_utilisateur
DB_PASSWORD=mot_de_passe_securise

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.votre-hebergeur.com
MAIL_PORT=465
MAIL_USERNAME=noreply@votre-domaine.com
MAIL_PASSWORD=mot_de_passe_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@votre-domaine.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 💾 Sauvegardes

### Configuration Automatique

```bash
# Rendre le script exécutable
chmod +x scripts/deploy/backup.sh

# Ajouter au cron (sauvegarde quotidienne à 2h du matin)
crontab -e
# Ajouter cette ligne :
0 2 * * * /chemin/vers/scripts/deploy/backup.sh
```

### Restauration

```bash
# Lister les sauvegardes disponibles
ls -lh /backups/csar

# Restaurer une sauvegarde
chmod +x scripts/deploy/restore.sh
./scripts/deploy/restore.sh 20250109_020000
```

---

## 🔒 Sécurité

### Points Importants

1. **Fichier .env** : Ne jamais le commiter dans Git
2. **Permissions** : Storage et cache en 775, fichiers en 644
3. **SSL** : Toujours utiliser HTTPS en production
4. **Firewall** : Configurer un firewall (UFW sur Ubuntu)
5. **Mises à jour** : Maintenir Laravel et les dépendances à jour

---

## 🐛 Dépannage

### Erreur 500
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier les permissions
chmod -R 775 storage bootstrap/cache

# Vider les caches
php artisan config:clear
php artisan cache:clear
```

### Erreur 404
```bash
# Vérifier mod_rewrite (Apache)
# Vérifier la configuration Nginx
# Vérifier que .htaccess est présent dans public/
```

### Problème de connexion MySQL
```bash
# Vérifier les credentials dans .env
# Tester la connexion
mysql -u utilisateur -p nom_base

# Vérifier que MySQL est démarré
sudo systemctl status mysql
```

---

## 📚 Ressources

- **Guide Complet** : [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md)
- **Documentation Laravel** : https://laravel.com/docs
- **Documentation Hostinger** : https://www.hostinger.com/tutorials

---

## 📞 Support

Pour toute question concernant le déploiement :
- 📧 Email : support@csar.sn
- 📖 Consultez [GUIDE_HEBERGEMENT.md](GUIDE_HEBERGEMENT.md) pour plus de détails

---

**🎉 Bon déploiement !**

