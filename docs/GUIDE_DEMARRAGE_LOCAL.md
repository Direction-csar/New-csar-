# 🚀 Guide de Démarrage Local - CSAR Platform

## 📋 Prérequis

1. **XAMPP installé** et démarré
   - Apache (optionnel pour Laravel)
   - **MySQL** (obligatoire)

2. **PHP 8.2+** (inclus dans XAMPP)

3. **Composer** installé

## 🚀 Démarrage Rapide

### Méthode 1 : Script Automatique (Recommandé)

Double-cliquez sur le fichier :
```
DEMARRER_SITE_LOCAL.bat
```

Le script va :
- ✅ Vérifier que MySQL est démarré
- ✅ Nettoyer le cache
- ✅ Démarrer le serveur Laravel
- ✅ Ouvrir automatiquement votre navigateur

### Méthode 2 : Ligne de Commande

1. **Ouvrir PowerShell ou CMD** dans le dossier du projet :
   ```powershell
   cd C:\xampp\htdocs\csar
   ```

2. **Vérifier que MySQL est démarré** dans XAMPP Control Panel

3. **Démarrer le serveur** :
   ```bash
   php artisan serve
   ```

4. **Ouvrir votre navigateur** sur :
   ```
   http://localhost:8000
   ```

## 🔐 Identifiants de Connexion

### 👤 Administrateur
- **Email** : `admin@csar.sn`
- **Mot de passe** : `password`
- **URL** : http://localhost:8000/admin/login

### 👔 Directeur Général (DG)
- **Email** : `dg@csar.sn`
- **Mot de passe** : `password`
- **URL** : http://localhost:8000/dg/login

### 📦 Gestionnaire d'Entrepôt
- **Email** : `entrepot@csar.sn`
- **Mot de passe** : `password`
- **URL** : http://localhost:8000/entrepot/login

### 👨‍💼 DRH
- **Email** : `drh@csar.sn`
- **Mot de passe** : `password`
- **URL** : http://localhost:8000/drh/login

### 🚚 Agent
- **Email** : `agent@csar.sn`
- **Mot de passe** : `password`
- **URL** : http://localhost:8000/agent/login

### 🌍 Site Public
- **URL** : http://localhost:8000

## ⚠️ Problèmes Courants

### Le serveur ne démarre pas

**Erreur** : "Port 8000 already in use"

**Solution** :
1. Fermez la fenêtre du serveur précédent
2. Ou utilisez un autre port :
   ```bash
   php artisan serve --port=8001
   ```

### Erreur de connexion à la base de données

**Solution** :
1. Ouvrez **XAMPP Control Panel**
2. Démarrez **MySQL**
3. Vérifiez que la base de données `csar_platform` existe
4. Vérifiez le fichier `.env` :
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=csar_platform
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Erreur "Class not found" ou "Composer autoload"

**Solution** :
```bash
composer install
composer dump-autoload
```

### Le cache pose problème

**Solution** :
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 📝 Commandes Utiles

### Arrêter le serveur
Appuyez sur `Ctrl + C` dans la fenêtre du serveur

### Vérifier les migrations
```bash
php artisan migrate:status
```

### Exécuter les migrations
```bash
php artisan migrate
```

### Créer le lien de stockage
```bash
php artisan storage:link
```

## 🎯 URLs Importantes

- **Page d'accueil** : http://localhost:8000
- **Admin** : http://localhost:8000/admin/login
- **DG** : http://localhost:8000/dg/login
- **DRH** : http://localhost:8000/drh/login
- **Entrepôt** : http://localhost:8000/entrepot/login
- **Agent** : http://localhost:8000/agent/login

## ✅ Vérification

Une fois le serveur démarré, vous devriez voir :
```
Laravel development server started: http://127.0.0.1:8000
```

Le site est maintenant accessible localement ! 🎉





