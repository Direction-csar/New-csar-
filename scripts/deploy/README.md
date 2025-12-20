# 📦 Scripts de Déploiement - CSAR Platform

Ce dossier contient les scripts automatisés pour déployer la Plateforme CSAR sur différents types d'hébergement.

## 📋 Scripts Disponibles

### 1. `deploy_hostinger.sh`
Script de déploiement pour Hostinger (hébergement partagé).

**Usage :**
```bash
chmod +x deploy_hostinger.sh
./deploy_hostinger.sh
```

**Fonctionnalités :**
- Installation des dépendances Composer
- Compilation des assets (NPM)
- Génération de la clé d'application
- Exécution des migrations
- Optimisation de l'application
- Configuration des permissions

### 2. `deploy_vps.sh`
Script de déploiement complet pour VPS Ubuntu/Debian.

**Usage :**
```bash
chmod +x deploy_vps.sh
sudo ./deploy_vps.sh
```

**Fonctionnalités :**
- Installation des dépendances système (PHP, MySQL, Nginx)
- Configuration MySQL
- Clonage du projet
- Configuration Nginx
- Configuration PHP-FPM
- Installation SSL avec Let's Encrypt
- Optimisation complète

### 3. `backup.sh`
Script de sauvegarde automatique.

**Usage :**
```bash
chmod +x backup.sh
./backup.sh
```

**Pour automatiser (cron) :**
```bash
# Sauvegarde quotidienne à 2h du matin
0 2 * * * /chemin/vers/scripts/deploy/backup.sh
```

**Fonctionnalités :**
- Sauvegarde de la base de données (MySQL dump)
- Sauvegarde des fichiers de l'application
- Sauvegarde du dossier storage
- Nettoyage automatique des sauvegardes anciennes (>30 jours)

### 4. `restore.sh`
Script de restauration depuis une sauvegarde.

**Usage :**
```bash
chmod +x restore.sh
./restore.sh 20250109_020000
```

**Fonctionnalités :**
- Restauration de la base de données
- Restauration des fichiers
- Restauration du dossier storage
- Sauvegarde de l'état actuel avant restauration
- Réinstallation des dépendances

## ⚙️ Configuration

### Variables à Modifier

Avant d'utiliser les scripts, vous devrez peut-être modifier certaines variables :

**backup.sh et restore.sh :**
- `DB_NAME` : Nom de votre base de données
- `DB_USER` : Utilisateur MySQL
- `DB_PASS` : Mot de passe MySQL
- `APP_DIR` : Chemin vers l'application
- `BACKUP_DIR` : Dossier de sauvegarde

**deploy_vps.sh :**
- Les variables sont demandées interactivement lors de l'exécution

## 🔒 Sécurité

⚠️ **Important :**
- Ne commitez jamais les mots de passe dans les scripts
- Utilisez des variables d'environnement pour les informations sensibles
- Protégez les scripts avec des permissions appropriées
- Vérifiez les sauvegardes régulièrement

## 📝 Notes

- Les scripts sont conçus pour être exécutés sur Linux/Unix
- Pour Windows, utilisez WSL ou Git Bash
- Assurez-vous d'avoir les permissions nécessaires avant d'exécuter les scripts
- Lisez toujours les scripts avant de les exécuter

## 🐛 Dépannage

### Erreur de permissions
```bash
chmod +x script.sh
```

### Erreur "command not found"
Vérifiez que les outils nécessaires sont installés :
- PHP
- Composer
- MySQL/MariaDB
- Git

### Erreur de connexion MySQL
Vérifiez les credentials dans les variables du script ou dans `.env`

## 📚 Documentation Complète

Pour plus de détails sur le déploiement, consultez :
- [README_DEPLOYMENT.md](../../README_DEPLOYMENT.md) - Guide de déploiement rapide
- [GUIDE_HEBERGEMENT.md](../../GUIDE_HEBERGEMENT.md) - Guide complet d'hébergement

