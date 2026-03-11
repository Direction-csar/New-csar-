# Support du projet CSAR — Documentation complète

**Dernière mise à jour :** Février 2026  
**Projet :** Plateforme web du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR), Sénégal.

---

## 1. Présentation

La plateforme CSAR est une application web Laravel qui assure :

- **Site institutionnel public** : présentation du CSAR, actualités, galerie, rapports, formulaire de demande d’aide, suivi de demande, SIM (Système d’Information sur les Marchés).
- **Interface Admin** : gestion complète (utilisateurs, demandes, entrepôts, stocks, personnel, actualités, galerie, SIM, audit, communications, etc.).
- **Interface DG (Direction Générale)** : vue stratégique en lecture seule sur les mêmes données (tableau de bord, demandes, entrepôts, stocks, personnel, utilisateurs, rapports, carte).

**Interfaces actuellement désactivées** (redirection vers une page « Interface désactivée ») : DRH, Responsable entrepôt, Agent.

---

## 2. Technologies

| Composant      | Technologie                    |
|----------------|--------------------------------|
| Backend        | Laravel 12.x (PHP 8.2+)        |
| Base de données| MySQL                          |
| Frontend       | Blade, Bootstrap 5, CSS/JS     |
| Sessions/Cache | Base de données (recommandé)   |
| Serveur        | Apache (XAMPP) / Nginx (prod)  |

---

## 3. Structure du projet

```
csar/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Gestion complète (demandes, entrepôts, stocks, personnel, actualités, galerie, SIM, audit, users…)
│   │   ├── DG/             # Vue lecture seule (dashboard, demandes, entrepôts, stocks, personnel, users, rapports, carte)
│   │   ├── Public/         # Site public (accueil, actualités, galerie, demande, suivi, SIM, contact, discours…)
│   │   ├── Auth/           # Connexion Admin / DG
│   │   └── Shared/         # Données partagées Admin/DG (RealtimeDataController)
│   ├── Models/             # Eloquent (User, PublicRequest, Warehouse, Stock, Personnel, SimReport, etc.)
│   └── Services/
├── config/                  # session, cache, database, app…
├── database/migrations/
├── resources/views/
│   ├── admin/               # Vues Admin
│   ├── dg/                   # Vues DG
│   ├── public/               # Vues site public
│   ├── auth/                 # Login, interface désactivée
│   └── layouts/
├── routes/
│   ├── web.php              # Routes principales
│   ├── simple-login.php
│   └── simple-auth.php
├── public/                   # Point d’entrée, assets, images
├── docs/                      # Documentation (dont ce fichier)
├── scripts/                   # Sauvegardes, déploiement
├── ACTIONS_IMMEDIATES.txt     # 5 actions déploiement
├── A_GARDER_DNS_ET_LIENS.txt  # DNS et URLs Admin/DG
└── README.md
```

---

## 4. Accès et URLs

### 4.1 Interfaces actives

| Interface | URL (exemple production)     | Rôle |
|-----------|------------------------------|------|
| **Admin** | `https://csar.sn/admin` ou `/admin/login` | Gestion complète plateforme |
| **DG**    | `https://csar.sn/dg` ou `/dg/login`      | Direction générale, lecture seule |

En local : remplacer par `http://localhost:8000` (ou l’URL de votre serveur).

### 4.2 Interfaces désactivées

- **DRH** : `/drh`, `/drh/login` → page « Interface désactivée »
- **Responsable entrepôt** : `/entrepot/login` → idem
- **Agent** : `/agent/login` → idem

### 4.3 Site public (avec préfixe langue)

- Accueil : `/fr` ou `/en`
- À propos : `/fr/a-propos`
- Institution : `/fr/institution`
- Actualités : `/fr/actualites`, `/fr/actualites/{id}`
- Galerie : `/fr/missions-en-images`, `/fr/gallery`
- Rapports : `/fr/rapports`
- Contact : `/fr/contact`
- Formulaire demande : `/fr/demande` ou `/demande` (direct)
- Suivi demande : `/fr/suivre-ma-demande` ou `/suivi`
- SIM : `/fr/sim`, `/fr/sim/dashboard`, `/fr/sim/prices`, etc.
- Partenaires : `/fr/partenaires`
- Discours : `/fr/discours`, `/fr/discours/{id}`
- Mentions légales : `/fr/politique-confidentialite`, `/fr/conditions-utilisation`

---

## 5. Fonctionnalités par interface

### 5.1 Interface Admin

- **Tableau de bord** : indicateurs temps réel (demandes, entrepôts, utilisateurs, stocks), carte, graphiques, génération de rapports.
- **Demandes** : liste, détail, approbation/rejet, export, PDF, suppression (pas de création côté Admin ; les demandes viennent du public).
- **Entrepôts** : CRUD, export.
- **Stocks / Produits** : CRUD stocks, produits, mouvements, reçus, export.
- **Personnel** : CRUD, statut, export, réinitialisation mot de passe.
- **Utilisateurs** : CRUD, activation/désactivation, réinitialisation mot de passe, export, graphiques.
- **Actualités** : CRUD, documents, prévisualisation.
- **Galerie** : CRUD, statut, médias.
- **Rapports SIM** : CRUD rapports SIM, upload, génération, téléchargement, planification, export.
- **SIM (gestion opérationnelle)** : tableau de bord SIM, régions, départements, marchés, catégories, produits, collectes (validation/rejet/publication), demandes d’accès aux données.
- **Chiffres clés** : édition, statut, réinitialisation.
- **Contenu** : gestion des contenus (content).
- **Communications** : tableau de bord communications, stats.
- **Messages** : liste, détail, réponse, marquer lu, suppression.
- **Newsletter** : abonnés, export, stats, envoi.
- **Audit & sécurité** : logs, sessions, révocation de sessions, rapports sécurité.
- **Notifications** : centre de notifications (liste, marquer lu, suppression).
- **Statistiques** : page statistiques, export.
- **Nettoyage BDD** : outil de nettoyage et vérification connexion.
- **Profil** : modification du profil admin.

### 5.2 Interface DG

- **Tableau de bord exécutif** : mêmes indicateurs qu’Admin (demandes, entrepôts, etc.), tendances 7 jours, taux de traitement (réel), pas de données fictives (délai/satisfaction affichés « — » si non disponibles).
- **Demandes** : consultation, détail, mise à jour statut (selon règles métier).
- **Entrepôts** : liste, détail (lecture seule).
- **Stocks** : liste, détail (lecture seule).
- **Personnel** : liste, détail, stats (total, actifs, cadres, congés) ; indicateurs « Performance » (présence, formations, satisfaction) basés sur données réelles ou « — ».
- **Utilisateurs** : liste, fiche (lecture seule).
- **Rapports** : liste des rapports (fichiers générés + PDF dans `public/rapport`), aperçu (route `dg.reports.show`), téléchargement, génération/export (API).
- **Carte** : carte interactive entrepôts / demandes.
- Données partagées avec Admin via `Shared\RealtimeDataController` (statuts des demandes : `pending`, `approved`, `rejected`).

### 5.3 Site public

- **Accueil** : contenu institutionnel, chiffres clés, actualités, discours (données BDD).
- **À propos, Institution** : textes et structure CSAR (tutelles, organisation).
- **Actualités** : liste, article, téléchargement de documents.
- **Galerie** : galeries, médias.
- **Rapports** : liste et téléchargement des rapports publics.
- **Contact** : formulaire de contact.
- **Demande d’aide** : formulaire de demande (enregistrement en BDD, code de suivi).
- **Suivi de demande** : saisie du code, consultation du statut, téléchargement PDF si prévu.
- **SIM** : pages SIM (présentation, dashboard, prix, demande d’accès, approvisionnement, régional, distributions, magasins, opérations, téléchargement de rapports SIM).
- **Partenaires** : liste des partenaires techniques.
- **Discours** : liste et détail des discours.
- **Newsletter** : inscription, désinscription.
- **Multilangue** : préfixes `fr` et `en`, cookie de langue.

---

## 6. Base de données (principaux modèles)

- **users** : utilisateurs (admin, dg, rôles désactivés : drh, agent, responsable).
- **public_requests** : demandes du public (statuts : `pending`, `approved`, `rejected`).
- **warehouses** : entrepôts (nom, région, coordonnées, etc.).
- **stocks** : stocks par entrepôt/produit (quantités, seuils).
- **stock_movements** : mouvements d’entrée/sortie.
- **products** : produits (référence, libellé, etc.).
- **personnel** : fiches personnel (poste, département, entrepôt, statut de validation).
- **actualités / galerie** : contenus et médias du site public.
- **sim_reports** : rapports SIM (publics, téléchargeables).
- **sim_*** : régions, départements, marchés, catégories, produits SIM, collectes, demandes d’accès.
- **newsletter_subscribers** : abonnés newsletter.
- **messages** : messages (contact / communication).
- **audit_logs** : logs d’audit.
- **sessions** : sessions (si `SESSION_DRIVER=database`).
- **cache** : cache (si `CACHE_STORE=database`).

Les statistiques DG/Admin (demandes, entrepôts, etc.) proviennent des mêmes tables ; les statuts de demande utilisent `pending` / `approved` / `rejected` en base.

---

## 7. Routes principales (résumé)

- **Publique (localisées)** : `/{locale}/` (home), `/a-propos`, `/institution`, `/actualites`, `/rapports`, `/contact`, `/demande`, `/suivre-ma-demande`, `/sim`, `/sim/...`, `/partenaires`, `/discours`, etc.
- **Publique (sans préfixe)** : `/demande`, `/suivi`, `/login` → redirection.
- **Admin** : `admin/login`, `admin/` (dashboard), `admin/demandes`, `admin/entrepots`, `admin/stock`, `admin/personnel`, `admin/users`, `admin/actualites`, `admin/galerie`, `admin/sim-reports`, `admin/sim/*`, `admin/audit`, `admin/communications`, `admin/messages`, `admin/newsletter`, etc.
- **DG** : `dg/login`, `dg/` (dashboard), `dg/demandes`, `dg/warehouses`, `dg/stocks`, `dg/personnel`, `dg/users`, `dg/reports`, `dg/reports/{filename}` (show), `dg/map`.
- **DRH / Entrepot / Agent** : toutes les URLs sous `drh`, `entrepot`, `agent` → vue `auth.interface-desactivee`.

---

## 8. Installation et démarrage

1. **Cloner / récupérer le projet**  
   `git clone ...` puis `cd csar`.

2. **Dépendances**  
   `composer install`

3. **Environnement**  
   - Copier `.env.example` vers `.env`.  
   - Configurer : `DB_*`, `APP_URL`, `SESSION_DRIVER=database`, `CACHE_STORE=database` (recommandé).  
   - `php artisan key:generate`

4. **Base de données**  
   - Créer la base MySQL.  
   - `php artisan migrate`  
   - (Optionnel) `php artisan db:seed`

5. **Lancer le serveur**  
   - `php artisan serve` (ou Apache/Nginx pointant vers `public/`).

6. **Sauvegardes**  
   - Voir `ACTIONS_IMMEDIATES.txt` et `scripts/backup/` pour les sauvegardes et tâches planifiées.

---

## 9. Configuration importante

- **Sessions** : `SESSION_DRIVER=database` pour partager l’état entre instances (table `sessions`).
- **Cache** : `CACHE_STORE=database` pour cohérence (table `cache`).
- **Statuts des demandes** : en base toujours `pending`, `approved`, `rejected` ; l’affichage peut être traduit (ex. « En attente », « Approuvée », « Rejetée ») dans les vues.
- **DG = mêmes données qu’Admin** : les comptages et listes DG viennent des mêmes modèles et du `RealtimeDataController` ; aucune donnée fictive (délai/satisfaction en « — » si non calculés).

---

## 10. Fichiers de référence

| Fichier | Contenu |
|--------|---------|
| `ACTIONS_IMMEDIATES.txt` | 5 actions de déploiement (doc, .env, backups, tests). |
| `A_GARDER_DNS_ET_LIENS.txt` | DNS Netim, URLs Admin et DG. |
| `README.md` | Présentation rapide et instructions d’installation. |
| `docs/SUPPORT_PROJET_CSAR_COMPLET.md` | Ce support complet. |
| `docs/` | Autres guides (SIM, rapports, corrections, hébergement, etc.). |

---

## 11. Résumé des fonctionnalités « ajoutées » ou consolidées

- **Un seul système de demandes** : formulaire public → `public_requests` ; Admin et DG utilisent les mêmes données et statuts.
- **DG aligné sur Admin** : tableau de bord, demandes, entrepôts, stocks, personnel, utilisateurs, rapports (y compris route `dg.reports.show`), carte ; indicateurs sans source en BDD affichés « — ».
- **Interfaces DRH / Responsable / Agent** : désactivées (page dédiée), seuls Admin et DG sont utilisés.
- **Sessions et cache en BDD** : recommandés pour la cohérence en production.
- **SIM** : module complet côté public (pages, demande d’accès) et Admin (rapports SIM, régions, départements, marchés, catégories, produits, collectes, demandes d’accès).
- **Documentation** : une seule référence à jour pour l’ensemble du projet dans ce fichier.

---

*Document généré pour la plateforme CSAR — Février 2026.*
