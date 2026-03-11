# 🔐 IDENTIFIANTS DE CONNEXION - PLATEFORME CSAR

**Date** : 22 Octobre 2025  
**Statut** : ✅ Tous les comptes sont actifs et fonctionnels

---

## 🚀 COMMENT SE CONNECTER

### 1️⃣ Démarrer le serveur Laravel
Ouvrir un terminal et exécuter :
```bash
php artisan serve
```

Le serveur démarrera sur : **http://localhost:8000**

### 2️⃣ Ouvrir le navigateur
Aller sur l'interface souhaitée (voir ci-dessous)

### 3️⃣ Se connecter avec les identifiants

---

## 👥 COMPTES DISPONIBLES

### 🔵 Administrateur
```
Interface : http://localhost:8000/admin
Email     : admin@csar.sn
Password  : password
Rôle      : Gestion complète de la plateforme
```

**Fonctionnalités :**
- Dashboard avec statistiques temps réel
- Gestion des demandes (CRUD complet)
- Gestion des entrepôts et stocks
- Gestion du personnel
- Gestion du contenu (actualités, galerie)
- Rapports et exports
- Notifications et messages
- Audit complet

---

### 🟢 Directeur Général
```
Interface : http://localhost:8000/dg
Email     : dg@csar.sn
Password  : password
Rôle      : Supervision stratégique et rapports
```

**Fonctionnalités :**
- Dashboard exécutif
- Consultation des demandes
- Vue d'ensemble des entrepôts
- Rapports consolidés
- Carte stratégique
- Statistiques globales

---

### 🟣 Directeur RH
```
Interface : http://localhost:8000/drh
Email     : drh@csar.sn
Password  : password
Rôle      : Gestion des ressources humaines
```

**Fonctionnalités :**
- Dashboard RH
- Gestion du personnel (CRUD)
- Bulletins de paie
- Documents RH
- Présences et absences
- Statistiques RH

---

### 🟠 Responsable Entrepôt
```
Interface : http://localhost:8000/entrepot
Email     : responsable@csar.sn
Password  : password
Rôle      : Gestion des stocks de son entrepôt
```

**Fonctionnalités :**
- Dashboard entrepôt
- Gestion des stocks (entrées/sorties)
- Mouvements de stock
- Alertes de stock
- Rapports d'inventaire

---

### 🔴 Agent CSAR
```
Interface : http://localhost:8000/agent
Email     : agent@csar.sn
Password  : password
Rôle      : Consultation profil et documents
```

**Fonctionnalités :**
- Profil personnel
- Téléchargement fiche PDF
- Documents RH personnels

---

## 🌐 INTERFACE PUBLIQUE

### Site Public
```
URL : http://localhost:8000/
```

**Pages disponibles :**
- Page d'accueil
- À propos
- Actualités
- Galerie
- Rapports SIM
- Formulaire de demande d'aide
- Suivi de demande
- Contact
- Carte interactive des entrepôts
- Partenaires

---

## ⚠️ PROBLÈMES DE CONNEXION ?

### Le serveur ne démarre pas ?

**Vérifier que XAMPP est démarré :**
1. Ouvrir XAMPP Control Panel
2. Démarrer **Apache**
3. Démarrer **MySQL**

**Vérifier le port 8000 :**
```bash
# Si le port 8000 est occupé, utiliser un autre port :
php artisan serve --port=8001
```

### Erreur "Route [login] not defined" ?

La connexion se fait directement sur les interfaces, pas sur `/login` :
- ❌ http://localhost:8000/login
- ✅ http://localhost:8000/admin
- ✅ http://localhost:8000/dg
- ✅ http://localhost:8000/drh

### Mot de passe incorrect ?

Tous les comptes utilisent le mot de passe : **`password`**

Si ça ne fonctionne pas, exécuter :
```bash
php verifier_comptes_connexion.php
```

Ce script va réinitialiser tous les mots de passe à `password`.

---

## 🔒 SÉCURITÉ - IMPORTANT !

### ⚠️ AVANT LA PRODUCTION

**CHANGER TOUS LES MOTS DE PASSE !**

Les mots de passe actuels sont :
- ❌ Trop simples (`password`)
- ❌ Identiques pour tous
- ❌ Connus publiquement

**Pour changer un mot de passe :**

1. Se connecter à l'interface
2. Aller dans "Profil" ou "Paramètres"
3. Choisir un mot de passe fort :
   - Minimum 8 caractères
   - Majuscules et minuscules
   - Chiffres et caractères spéciaux
   - Unique pour chaque compte

---

## 📝 NOTES

### Comptes de Test

Ces comptes sont prêts pour :
- ✅ Tests fonctionnels
- ✅ Développement
- ✅ Démonstration
- ❌ Production (sans changer les mots de passe)

### Configuration de la Base de Données

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=csar_platform_2025
DB_USERNAME=root
DB_PASSWORD=
```

### Vérification de l'État

Pour vérifier l'état des comptes à tout moment :
```bash
php verifier_comptes_connexion.php
```

---

## 🎯 CHECKLIST DE CONNEXION

- [ ] XAMPP démarré (Apache + MySQL)
- [ ] Serveur Laravel démarré (`php artisan serve`)
- [ ] Navigateur ouvert sur http://localhost:8000
- [ ] Interface choisie (/admin, /dg, /drh, /entrepot, /agent)
- [ ] Email et mot de passe corrects
- [ ] Connexion réussie ✅

---

## 📞 SUPPORT

Si tu rencontres toujours des problèmes :

1. Vérifier les logs Laravel : `storage/logs/laravel.log`
2. Vérifier que MySQL est démarré
3. Vérifier que la base `csar_platform_2025` existe
4. Exécuter `php verifier_comptes_connexion.php`

---

**✅ Tous les comptes sont fonctionnels et prêts à être utilisés !**

*Dernière vérification : 22 Octobre 2025*  
*Plateforme CSAR - Version 2.0*


