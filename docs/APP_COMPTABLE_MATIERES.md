# Application Android — Comptable des matières (conception)

Statut : **conception validée, implémentation à démarrer**.
Aucun code applicatif n'existe encore ; ce document fixe le périmètre,
le modèle de données réutilisé et les prérequis backend.

## 1. Objectif

Application Flutter dédiée au **Comptable des matières** pour suivre
les mouvements de stock (carburant + matériel) et produire les
validations comptables aujourd'hui faites dans le portail web
`/comptable/mouvements` (P7).

## 2. Modèle de données réutilisé (pas de nouvelle table)

| Donnée métier            | Table existante                  | Colonnes clés |
|--------------------------|----------------------------------|---------------|
| Mouvements stock         | `stock_movements`                | `type`, `quantity`, `reference`, `status` (draft/validated/rejected), `validated_by/at`, `rejection_reason`, `request_mission_id` |
| Articles / matériel      | `products` (ou `stocks`)         | `name`, `category`, `unit` |
| Magasins                 | `warehouses`                     | `name`, `region` |
| Missions DTL (magasinier)| `request_missions`               | `type` (sortie_stock), `status`, `report` |
| Utilisateur comptable    | `users` (`role = comptable`)     | guard `comptable` |

- Carburant = `products.category` dédiée (ex. `Carburant`) ou champ `unit = 'L'` ; trancher avec le stock existant avant implémentation.
- Affectations matériel ↔ agent : **table `equipment_assignments` retenue**
  (voir évolution de schéma ci-dessus) — le filtrage par statut/agent
  est trop fréquent pour un blob `metadata`.

## 3. Écrans

1. **Login** — `POST /api/v1/comptable/login` (Sanctum), jeton stocké.
2. **Tableau de bord** — soldes par catégorie (carburant, matériel), alertes seuils.
3. **Mouvements** — liste filtrable (statut, magasin, type, période, référence bon/facture).
4. **Détail mouvement** — quantités avant/après, justificatifs, mission liée, décision Valider / Rejeter (motif ≥ 10 car., même règle que le web).
5. **Saisie entrée/sortie carburant** — référence bon, quantité, reste calculé.
6. **Matériel** — fiche article, affectation à un agent, retour, inventaire.
7. **Alertes** — seuil bas, écarts inventaire, incohérences (quantité négative, sortie > stock).
8. **Historique / recherche** — par période, agent, matériel, type.
9. **Exports** — PDF/Excel seulement via endpoints backend (pas de génération locale des archives officielles).

## 4. Endpoints backend à créer (requis avant implémentation)

Sous `routes/api.php` ou `routes/comptable-api.php`, Sanctum + rôle `comptable` :

- `GET /api/v1/comptable/movements?status=&warehouse_id=&type=&from=&to=&q=`
- `GET /api/v1/comptable/movements/{id}`
- `POST /api/v1/comptable/movements/{id}/validate`
- `POST /api/v1/comptable/movements/{id}/reject` (`rejection_reason` requis)
- `GET /api/v1/comptable/stocks/summary` (soldes + alertes seuil)
- `GET /api/v1/comptable/movements/export?format=pdf|xlsx` (si backend export prêt)

Réutiliser `App\Http\Controllers\Comptable\MouvementController` : extraire
les règles (transition draft→validated/rejected, motif requis, gel après
validation) dans un service partagé pour éviter la duplication web/API.

## 5. Sécurité

- Auth Sanctum + vérification `role = comptable` côté serveur à chaque requête.
- Aucune donnée hors connexion persistante non chiffrée.
- Mot de passe saisi à chaque décision sensible (option, à trancher).

## 6. Hors ligne (règles à respecter)

- Sync en lecture seule autorisée (cache SQLite des mouvements).
- Saisies hors ligne : file d'attente locale avec `client_ref` unique
  (même pattern idempotence que l'API magasinier) pour éviter les doublons.
- Conflits : le serveur fait foi ; un mouvement déjà validé ne peut pas
  être resoumis — afficher l'erreur 422 renvoyée.

## 7. Tests attendus

- Feature tests endpoints comptable (miroir de `ComptableMouvementsTest`).
- Widget tests Flutter : login, liste, validation, rejet, gestion offline.

## 5. Sécurité

- Auth Sanctum + vérification `role = comptable` côté serveur à chaque requête.
- Aucune donnée hors connexion persistante non chiffrée.
- Mot de passe saisi à chaque décision sensible (option, à trancher).

## 6. Hors ligne (règles à respecter)

- Sync en lecture seule autorisée (cache SQLite des mouvements).
- Saisies hors ligne : file d'attente locale avec `client_ref` unique
  (même pattern idempotence que l'API magasinier) pour éviter les doublons.
- Conflits : le serveur fait foi ; un mouvement déjà validé ne peut pas
  être resoumis — afficher l'erreur 422 renvoyée.

## 7. Tests attendus

- Feature tests endpoints comptable (miroir de `ComptableMouvementsTest`).
- Widget tests Flutter : login, liste, validation, rejet, gestion offline.
