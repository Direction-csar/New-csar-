# 🔐 TOUS LES IDENTIFIANTS DE CONNEXION - PLATEFORME CSAR

## 📋 Tableau Récapitulatif Complet

| Rôle | Email | Mot de passe | URL de connexion | Interface |
|------|-------|--------------|------------------|-----------|
| **Administrateur** | admin@csar.sn | `password` ou `admin123` | https://www.csar.sn/admin | `/admin` |
| **Directeur Général (DG)** | dg@csar.sn | `password` ou `dg123` | https://www.csar.sn/dg | `/dg` |
| **DRH** | drh@csar.sn | `password` ou `drh123` | https://www.csar.sn/drh | `/drh` |
| **Responsable Entrepôt** | responsable@csar.sn | `password` ou `resp123` | https://www.csar.sn/entrepot | `/entrepot` |
| **Agent CSAR** | agent@csar.sn | `password` ou `agent123` | https://www.csar.sn/agent | `/agent` |

---

## 👥 Détails par Rôle

### 🔵 1. ADMINISTRATEUR
```
📧 Email        : admin@csar.sn
🔑 Mot de passe : password (ou admin123)
🌐 URL          : https://www.csar.sn/admin
                : https://www.csar.sn/admin/login
📊 Dashboard    : https://www.csar.sn/admin/dashboard
```

**Fonctionnalités :**
- Gestion complète de la plateforme
- Gestion des utilisateurs
- Gestion des demandes
- Gestion des stocks et entrepôts
- Gestion du personnel
- Gestion du contenu (actualités, galerie)
- Rapports et statistiques
- Audit et logs

---

### 🟢 2. DIRECTEUR GÉNÉRAL (DG)
```
📧 Email        : dg@csar.sn
🔑 Mot de passe : password (ou dg123)
🌐 URL          : https://www.csar.sn/dg
                : https://www.csar.sn/dg/login
📊 Dashboard    : https://www.csar.sn/dg/dashboard
```

**Fonctionnalités :**
- Dashboard exécutif
- Consultation des demandes
- Vue d'ensemble des entrepôts
- Rapports consolidés
- Statistiques globales
- Carte stratégique
- Supervision générale

---

### 🟣 3. DIRECTEUR RH (DRH)
```
📧 Email        : drh@csar.sn
🔑 Mot de passe : password (ou drh123)
🌐 URL          : https://www.csar.sn/drh
                : https://www.csar.sn/drh/login
📊 Dashboard    : https://www.csar.sn/drh/dashboard
```

**Fonctionnalités :**
- Dashboard RH
- Gestion du personnel (CRUD complet)
- Bulletins de paie
- Documents RH
- Présences et absences
- Statistiques RH
- Gestion des fiches de personnel

---

### 🟠 4. RESPONSABLE ENTREPÔT
```
📧 Email        : responsable@csar.sn
🔑 Mot de passe : password (ou resp123)
🌐 URL          : https://www.csar.sn/entrepot
                : https://www.csar.sn/entrepot/login
📊 Dashboard    : https://www.csar.sn/entrepot/dashboard
```

**Alternative :**
```
📧 Email        : entrepot@csar.sn
🔑 Mot de passe : password
```

**Fonctionnalités :**
- Dashboard entrepôt
- Gestion des stocks (entrées/sorties)
- Mouvements de stock
- Alertes de stock
- Rapports d'inventaire
- Gestion des produits

---

### 🔴 5. AGENT CSAR
```
📧 Email        : agent@csar.sn
🔑 Mot de passe : password (ou agent123)
🌐 URL          : https://www.csar.sn/agent
                : https://www.csar.sn/agent/login
📊 Dashboard    : https://www.csar.sn/agent/dashboard
```

**Fonctionnalités :**
- Profil personnel
- Téléchargement fiche PDF
- Documents RH personnels
- Consultation des missions
- Coordination terrain

---

## 🌐 Interface Publique

```
🌍 URL          : https://www.csar.sn
                : https://www.csar.sn/fr (version française)
                : https://www.csar.sn/en (version anglaise)
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

## 🔄 Si les Mots de Passe ne Fonctionnent Pas

### Option 1 : Réinitialiser avec le script

Sur le serveur, exécutez :

```bash
cd /var/www/csar
php artisan tinker
```

Puis dans tinker :

```php
// Réinitialiser tous les mots de passe à "password"
$users = \App\Models\User::all();
foreach($users as $user) {
    $user->password = bcrypt('password');
    $user->save();
}
echo "Tous les mots de passe ont été réinitialisés à 'password'";
exit
```

### Option 2 : Créer les utilisateurs manuellement

```bash
cd /var/www/csar
php artisan tinker
```

```php
use App\Models\User;
use App\Models\Role;

// Créer Admin
User::updateOrCreate(
    ['email' => 'admin@csar.sn'],
    [
        'name' => 'Administrateur CSAR',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'role' => 'admin',
        'is_active' => true
    ]
);

// Créer DG
User::updateOrCreate(
    ['email' => 'dg@csar.sn'],
    [
        'name' => 'Directeur Général',
        'password' => bcrypt('password'),
        'role_id' => 2,
        'role' => 'dg',
        'is_active' => true
    ]
);

// Créer DRH
User::updateOrCreate(
    ['email' => 'drh@csar.sn'],
    [
        'name' => 'Directeur RH',
        'password' => bcrypt('password'),
        'role_id' => 5,
        'role' => 'drh',
        'is_active' => true
    ]
);

// Créer Responsable
User::updateOrCreate(
    ['email' => 'responsable@csar.sn'],
    [
        'name' => 'Responsable Entrepôt',
        'password' => bcrypt('password'),
        'role_id' => 3,
        'role' => 'responsable',
        'is_active' => true
    ]
);

// Créer Agent
User::updateOrCreate(
    ['email' => 'agent@csar.sn'],
    [
        'name' => 'Agent CSAR',
        'password' => bcrypt('password'),
        'role_id' => 4,
        'role' => 'agent',
        'is_active' => true
    ]
);

echo "Tous les utilisateurs ont été créés/réinitialisés !";
exit
```

---

## ⚠️ IMPORTANT - SÉCURITÉ

### 🔒 Avant la Production

**CHANGER TOUS LES MOTS DE PASSE !**

Les mots de passe par défaut sont :
- ❌ Trop simples
- ❌ Identiques pour tous
- ❌ Connus publiquement

**Recommandations :**
- Utiliser des mots de passe forts (minimum 12 caractères)
- Mélanger majuscules, minuscules, chiffres et caractères spéciaux
- Utiliser un mot de passe unique pour chaque compte
- Activer l'authentification à deux facteurs si disponible

---

## 📝 Notes

- **Environnement de développement** : Les mots de passe par défaut sont acceptables
- **Environnement de production** : **OBLIGATOIRE** de changer tous les mots de passe
- Tous les comptes sont créés automatiquement lors des migrations si le seeder est exécuté

---

## 🎯 Checklist de Connexion

- [ ] Site accessible sur https://www.csar.sn
- [ ] Interface de connexion accessible
- [ ] Email correct (selon le rôle)
- [ ] Mot de passe correct (`password` par défaut)
- [ ] Connexion réussie ✅
- [ ] Redirection vers le dashboard correspondant

---

**✅ Tous les identifiants sont prêts à être utilisés !**

*Plateforme CSAR - www.csar.sn*

