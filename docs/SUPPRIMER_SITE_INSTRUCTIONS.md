# 🗑️ Instructions pour Supprimer le Site CSAR du Serveur Ubuntu

## ⚠️ ATTENTION
Cette opération est **IRRÉVERSIBLE**. Tous les fichiers, la base de données et la configuration seront supprimés définitivement.

## 📋 Informations du Serveur

- **Serveur SSH**: `srv1177675.hstgr.cloud` ou `72.61.16.34`
- **OS**: Ubuntu 24.04.3 LTS
- **Domaine**: `www.csar.sn` / `csar.sn`
- **Répertoire du site**: `/var/www/csar`
- **Base de données**: `csar_platform`
- **Utilisateur MySQL**: `csar_user`
- **Serveur Web**: Nginx (peut aussi être Apache)
- **Configuration Nginx**: `/etc/nginx/sites-available/csar`
- **Configuration Apache**: `/etc/apache2/sites-available/csar.conf` (si Apache)

## 🚀 Méthode 1: Script Automatique (Recommandé)

### Étape 1: Télécharger le script sur le serveur

```bash
# Se connecter au serveur
ssh root@72.61.16.34
# OU
ssh root@srv1177675.hstgr.cloud
```

### Étape 2: Télécharger le script

```bash
# Créer le script directement sur le serveur
cat > /tmp/supprimer_site.sh << 'EOF'
#!/bin/bash
# [Le contenu du script SUPPRIMER_SITE_SERVEUR.sh]
EOF

# Ou télécharger depuis votre machine locale
# (Depuis votre machine Windows, utilisez WinSCP ou FileZilla pour uploader SUPPRIMER_SITE_SERVEUR.sh)
```

### Étape 3: Rendre le script exécutable et l'exécuter

```bash
chmod +x /tmp/supprimer_site.sh
sudo bash /tmp/supprimer_site.sh
```

Le script vous demandera de confirmer en tapant `OUI`.

---

## 🔧 Méthode 2: Suppression Manuelle

Si vous préférez supprimer manuellement, suivez ces étapes :

### 1. Se connecter au serveur

```bash
ssh root@72.61.16.34
```

### 2. Désactiver le site (Nginx ou Apache)

**Pour Nginx:**
```bash
sudo rm /etc/nginx/sites-enabled/csar
sudo nginx -t
sudo systemctl reload nginx
```

**Pour Apache:**
```bash
sudo a2dissite csar.conf
sudo systemctl reload apache2
```

### 3. Supprimer la configuration serveur web

**Pour Nginx:**
```bash
sudo rm /etc/nginx/sites-available/csar
```

**Pour Apache:**
```bash
sudo rm /etc/apache2/sites-available/csar.conf
```

### 3b. Supprimer les certificats SSL (si présents)

```bash
sudo certbot delete --cert-name www.csar.sn
sudo certbot delete --cert-name csar.sn
```

### 4. Supprimer les fichiers du site

```bash
sudo rm -rf /var/www/csar
```

### 5. Supprimer la base de données

```bash
mysql -u root -p
```

Dans MySQL, exécutez :

```sql
DROP DATABASE csar_platform;
DROP USER 'csar_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 6. Supprimer les logs (optionnel)

**Pour Nginx:**
```bash
# Les logs Nginx sont généralement partagés, vérifiez d'abord
sudo tail /var/log/nginx/access.log | grep csar
sudo tail /var/log/nginx/error.log | grep csar
```

**Pour Apache:**
```bash
sudo rm /var/log/apache2/csar-error.log
sudo rm /var/log/apache2/csar-access.log
```

### 7. Vérifier la suppression

```bash
# Vérifier que le répertoire n'existe plus
ls -la /var/www/csar

# Pour Nginx:
ls -la /etc/nginx/sites-available/csar
ls -la /etc/nginx/sites-enabled/csar

# Pour Apache:
ls -la /etc/apache2/sites-available/csar.conf
ls -la /etc/apache2/sites-enabled/csar.conf

# Vérifier les certificats SSL
sudo certbot certificates
```

---

## ✅ Vérification Finale

Après la suppression, vérifiez que :

1. ✅ Le répertoire `/var/www/csar` n'existe plus
2. ✅ La configuration serveur web n'existe plus:
   - Nginx: `/etc/nginx/sites-available/csar` et `/etc/nginx/sites-enabled/csar`
   - Apache: `/etc/apache2/sites-available/csar.conf` et `/etc/apache2/sites-enabled/csar.conf`
3. ✅ Le site n'est plus actif:
   - Nginx: `nginx -T | grep csar` (ne doit rien retourner)
   - Apache: `apache2ctl -S` (ne doit pas lister csar)
4. ✅ Les certificats SSL sont supprimés: `certbot certificates`
5. ✅ La base de données `csar_platform` n'existe plus dans MySQL
6. ✅ L'utilisateur `csar_user` n'existe plus dans MySQL

---

## 🔒 Sécurité

Après la suppression, assurez-vous de :

- ✅ Changer les mots de passe root du serveur si nécessaire
- ✅ Vérifier qu'aucun autre service n'utilise les mêmes identifiants
- ✅ Nettoyer les sauvegardes si vous ne souhaitez plus les conserver

---

## 📞 Support

Si vous rencontrez des problèmes lors de la suppression, contactez le support de votre hébergeur ou consultez la documentation Nginx/Apache/MySQL.

## 🔄 Alternative: Utilisation du Script Automatique

Le script `SUPPRIMER_SITE_SERVEUR.sh` détecte automatiquement si vous utilisez Nginx ou Apache et effectue toutes les étapes ci-dessus automatiquement.

**Pour utiliser le script:**

1. Connectez-vous au serveur:
```bash
ssh root@72.61.16.34
```

2. Téléchargez ou créez le script sur le serveur:
```bash
# Option 1: Créer le script directement
nano /tmp/supprimer_site.sh
# Copiez-collez le contenu de SUPPRIMER_SITE_SERVEUR.sh

# Option 2: Télécharger depuis votre machine locale via SCP
# (Depuis votre machine Windows, utilisez WinSCP ou FileZilla)
```

3. Rendez le script exécutable et exécutez-le:
```bash
chmod +x /tmp/supprimer_site.sh
sudo bash /tmp/supprimer_site.sh
```

Le script vous demandera de confirmer en tapant `OUI`.





