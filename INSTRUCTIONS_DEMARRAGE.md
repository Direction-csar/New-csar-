# 🚀 Instructions de Démarrage - Plateforme CSAR

## Problème Actuel

MySQL refuse les connexions car il n'est pas démarré correctement. XAMPP utilise une option incompatible (`--initialize-insecure`) qui fait planter MySQL.

## ✅ Solution : Utiliser le Script de Démarrage Manuel

### 1. Démarrer MySQL

Ouvrez un **nouveau terminal PowerShell** et exécutez :

```bash
cd C:\xampp\htdocs\csar
.\fix_mysql_start.bat
```

**Important :** 
- Laissez cette fenêtre **OUVERTE** (ne la fermez pas)
- MySQL s'arrêtera si vous fermez cette fenêtre

### 2. Exécuter les Migrations

Une fois MySQL démarré (après avoir exécuté le script ci-dessus), ouvrez un **autre terminal** et exécutez :

```bash
cd C:\xampp\htdocs\csar
php artisan migrate --force
```

### 3. Démarrer le Serveur Laravel (s'il n'est pas déjà lancé)

```bash
php artisan serve
```

### 4. Accéder à l'Application

Ouvrez votre navigateur sur : **http://localhost:8000**

---

## 📋 Procédure Complète (Démarrage à Froid)

### Terminal 1 : MySQL
```bash
cd C:\xampp\htdocs\csar
.\fix_mysql_start.bat
```
→ Laisser ouvert

### Terminal 2 : Migrations + Serveur
```bash
cd C:\xampp\htdocs\csar
php artisan migrate --force
php artisan serve
```
→ Laisser ouvert

### Navigateur
Accédez à : http://localhost:8000

---

## ⚠️ Remarques Importantes

1. **MySQL doit être démarré avec `fix_mysql_start.bat`**
   - Ne PAS utiliser le bouton "Start" de XAMPP pour MySQL
   - XAMPP ajoute une option incompatible qui fait planter MySQL

2. **Deux fenêtres doivent rester ouvertes :**
   - Fenêtre 1 : MySQL (`fix_mysql_start.bat`)
   - Fenêtre 2 : Serveur Laravel (`php artisan serve`)

3. **Pour arrêter l'application :**
   - Fermez la fenêtre du serveur Laravel (Ctrl+C)
   - Fermez la fenêtre MySQL (Ctrl+C)

---

## 🔧 Vérification Rapide

### Vérifier que MySQL fonctionne :
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 'MySQL OK' AS Status;"
```

### Vérifier que le serveur Laravel fonctionne :
Ouvrez http://localhost:8000 dans votre navigateur

---

## 🆘 Dépannage

### MySQL refuse les connexions
- Assurez-vous que `fix_mysql_start.bat` est en cours d'exécution
- Attendez 10-15 secondes après avoir lancé le script
- Vérifiez les logs : `C:\xampp\mysql\data\*.err`

### Erreur "Port 8000 already in use"
- Un serveur Laravel est déjà en cours d'exécution
- Fermez l'autre serveur ou utilisez un autre port :
  ```bash
  php artisan serve --port=8001
  ```

### Les migrations échouent
- Vérifiez que MySQL est accessible :
  ```bash
  php test_mysql_simple.php
  ```
- Si MySQL n'est pas accessible, relancez `fix_mysql_start.bat`

---

## 💾 Scripts Disponibles

- `fix_mysql_start.bat` - Démarre MySQL correctement
- `test_mysql_simple.php` - Teste la connexion MySQL
- `executer_migrations.php` - Exécute les migrations avec vérification
- `DEMARRER_SITE_LOCAL.bat` - Script de démarrage complet (peut ne pas fonctionner avec MySQL)

---

## ✨ Identifiants de Connexion

### Administrateur
- Email : `admin@csar.sn`
- Mot de passe : `password`
- URL : http://localhost:8000/admin/login

### Directeur Général
- Email : `dg@csar.sn`
- Mot de passe : `password`
- URL : http://localhost:8000/dg/login

### Site Public
- URL : http://localhost:8000


