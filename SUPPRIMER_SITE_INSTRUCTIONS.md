# 🗑️ Instructions pour Supprimer le Site CSAR du Serveur Hostinger

## ⚠️ ATTENTION
Cette opération est **IRRÉVERSIBLE**. Tous les fichiers, la base de données et la configuration seront supprimés définitivement.

## 📋 Informations du Serveur

- **Serveur SSH**: `srv1177675.hstgr.cloud` ou `72.61.16.34`
- **Domaine**: `csar.sn`
- **Répertoire du site**: `/var/www/csar`
- **Base de données**: `csar_platform`
- **Utilisateur MySQL**: `csar_user`
- **Configuration Apache**: `/etc/apache2/sites-available/csar.conf`

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

### 2. Désactiver le site Apache

```bash
sudo a2dissite csar.conf
sudo systemctl reload apache2
```

### 3. Supprimer la configuration Apache

```bash
sudo rm /etc/apache2/sites-available/csar.conf
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

```bash
sudo rm /var/log/apache2/csar-error.log
sudo rm /var/log/apache2/csar-access.log
```

### 7. Vérifier la suppression

```bash
# Vérifier que le répertoire n'existe plus
ls -la /var/www/csar

# Vérifier que la configuration Apache n'existe plus
ls -la /etc/apache2/sites-available/csar.conf

# Vérifier que le site n'est plus activé
ls -la /etc/apache2/sites-enabled/csar.conf
```

---

## ✅ Vérification Finale

Après la suppression, vérifiez que :

1. ✅ Le répertoire `/var/www/csar` n'existe plus
2. ✅ Le fichier `/etc/apache2/sites-available/csar.conf` n'existe plus
3. ✅ Le site n'est plus listé dans `apache2ctl -S`
4. ✅ La base de données `csar_platform` n'existe plus dans MySQL
5. ✅ L'utilisateur `csar_user` n'existe plus dans MySQL

---

## 🔒 Sécurité

Après la suppression, assurez-vous de :

- ✅ Changer les mots de passe root du serveur si nécessaire
- ✅ Vérifier qu'aucun autre service n'utilise les mêmes identifiants
- ✅ Nettoyer les sauvegardes si vous ne souhaitez plus les conserver

---

## 📞 Support

Si vous rencontrez des problèmes lors de la suppression, contactez le support Hostinger ou consultez la documentation Apache/MySQL.





