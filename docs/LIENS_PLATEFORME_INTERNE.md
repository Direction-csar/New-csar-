# Liens de la plateforme interne CSAR

La plateforme dispose de **quatre espaces internes** réservés au personnel, chacun avec son propre lien de connexion et ses fonctionnalités.

---

## 1. Administration (Admin)

| Élément | Détail |
|--------|--------|
| **URL de connexion** | `https://votredomaine.com/admin/login` |
| **Rôle** | Administrateurs : gestion complète du site et des données |
| **Accès après connexion** | `/admin` ou `/admin/dashboard` |

**Fonctionnalités principales :**
- Tableau de bord
- Demandes (liste, détail, approbation, rejet, export, PDF)
- Utilisateurs (CRUD)
- Entrepôts (CRUD)
- Gestion des stocks
- Produits
- Personnel (CRUD)
- Actualités, galerie
- Communications, messages, newsletter
- Rapports SIM
- Chiffres clés, statistiques
- Logs d’audit, nettoyage BDD
- Contenu, paramètres

**Menu typique :** Tableau de bord, Demandes, Utilisateurs, Entrepôts, Stocks, Personnel, Statistiques, Chiffres clés, Actualités, Galerie, Communications, etc.

---

## 2. DRH (Direction des ressources humaines)

| Élément | Détail |
|--------|--------|
| **URL de connexion** | `https://votredomaine.com/drh/login` |
| **Rôle** | Utilisateurs DRH : gestion RH |
| **Accès après connexion** | `/drh` ou `/drh/dashboard` |

**Fonctionnalités principales :**
- Tableau de bord DRH
- Personnel (liste, création, édition, etc.)
- Documents RH
- Présences (attendance)
- Fiches de paie (salary-slips)
- Profil DRH

**URLs utiles :**
- `/drh` — Tableau de bord
- `/drh/personnel` — Gestion du personnel
- `/drh/documents` — Documents RH
- `/drh/attendance` — Présences
- `/drh/salary-slips` — Fiches de paie
- `/drh/profile` — Profil

---

## 3. DG (Direction générale)

| Élément | Détail |
|--------|--------|
| **URL de connexion** | `https://votredomaine.com/dg/login` |
| **Rôle** | Direction générale : consultation et pilotage (lecture + actions limitées) |
| **Accès après connexion** | `/dg` ou `/dg/dashboard` |

**Fonctionnalités principales (souvent en lecture seule) :**
- Tableau de bord DG (indicateurs, cartes)
- Demandes (consultation, détail, mise à jour de statut si prévu)
- Entrepôts (consultation)
- Stocks (consultation)
- Personnel (consultation)
- Utilisateurs (consultation)
- Rapports (génération, export, téléchargement)
- Carte interactive

**URLs utiles :**
- `/dg` — Tableau de bord
- `/dg/demandes` — Demandes
- `/dg/warehouses` — Entrepôts
- `/dg/stocks` — Stocks
- `/dg/personnel` — Personnel
- `/dg/users` — Utilisateurs
- `/dg/reports` — Rapports
- `/dg/map` — Carte

---

## 4. Agent (espace personnel)

| Élément | Détail |
|--------|--------|
| **URL de connexion** | `https://votredomaine.com/agent/login` |
| **Rôle** | Agents : accès à leur propre profil et à leurs données RH |
| **Accès après connexion** | `/agent` ou `/agent/dashboard` |

**Fonctionnalités principales :**
- Tableau de bord agent
- Mon profil (voir, modifier, photo, mot de passe, fiche PDF)
- Espace RH : documents personnels, fiches de paie, présences, statistiques

**URLs utiles :**
- `/agent` — Tableau de bord
- `/agent/profile` — Mon profil
- `/agent/profile/edit` — Modifier le profil
- `/agent/hr` — Espace RH
- `/agent/hr/documents` — Mes documents
- `/agent/hr/salary-slips` — Mes fiches de paie
- `/agent/hr/attendance` — Mes présences
- `/agent/hr/statistics` — Statistiques

---

## Récapitulatif des URLs de connexion

| Espace | URL de connexion |
|--------|-------------------|
| **Admin** | `/admin/login` |
| **DRH**   | `/drh/login`    |
| **DG**    | `/dg/login`     |
| **Agent** | `/agent/login`  |

En production, remplacez `votredomaine.com` par votre domaine réel (ex. `csar.sn`).

---

## Sécurité

- Chaque espace est protégé par **middleware** (`admin`, `drh`, `DGMiddleware`, `agent`).
- Les utilisateurs doivent avoir le **rôle** correspondant en base de données.
- Ne pas exposer ces URLs publiquement (pas de lien sur le site public) ; les communiquer uniquement aux personnes habilitées.
