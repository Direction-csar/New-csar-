# Module Carburant — Rapport de finalisation

**Date** : 2026-10-29
**Périmètre** : Laravel (`c:\xampp\csar.sn`) + application Flutter `mobile/csar_comptable`
**Environnement** : local uniquement — aucun déploiement ni changement en production.

## 1. Livrables

### 1.1 Base de données

Migration `2026_10_29_000001_create_fuel_management_tables.php` (exécutée en local) :

- **`fuel_entries`** — approvisionnements (référence `ENT-CARB-YYYY-NNNNN`, litres, prix, fournisseur)
- **`fuel_exits`** — bons de sortie (`SOR-CARB-YYYY-NNNNN`, quantités demandée/autorisée/livrée)
- **`fuel_reimbursements`** — remboursements (`REM-CARB-YYYY-NNNNN`, avances, doublons, régularisation)
- **`fuel_attachments`** — justificatifs polymorphes privés (PDF/JPEG/PNG, ≤ 8 Mo, SHA-256)
- **`fuel_status_logs`** — piste d'audit non modifiable des transitions

### 1.2 Couche métier

`app/Services/Comptable/FuelService.php` — point d'entrée unique Web + API :

- Références auto, verrous pessimistes (`lockForUpdate`), idempotence (`idempotency_key` + no-op sur revalidation)
- Stock physique en litres via `stock_movements` (`unite='L'`) + ligne `stocks` synchronisée (fix `stock_id NOT NULL`)
- Workflows : entrée (draft → validated/rejected), sortie (draft → authorized → validated), remboursement (draft → submitted → in_review → approved → paid → closed / rejected)
- Justificatifs sur disque privé, détection de doublons de remboursement, agrégats dashboard

### 1.3 Portail Web (`/comptable`)

- `app/Http/Controllers/Comptable/FuelController.php` — 29 routes dans `routes/web.php`
- Vues Blade `resources/views/comptable/carburant/` : dashboard, entrées, sorties, remboursements, nav, badges de statut
- Gabarits PDF DomPDF : `pdf/entry`, `pdf/exit`, `pdf/reimb`
- Exports CSV des trois listes

### 1.4 API mobile (`/api/comptable/v1`, Sanctum)

- `app/Http/Controllers/Api/Mobile/FuelApiController.php` — **26 routes** dans `routes/comptable-api.php`
- Dashboard (`stock`, `stock_value`, `pending`), CRUD + détails, toutes les transitions métier
- Upload / téléchargement / suppression de justificatifs (fichier jamais public)
- PDF par type (`entry|exit|reimb`)
- Garde : rôle `comptable` ou `admin` ; approbation et paiement des remboursements réservés à `admin` *(règle provisoire à confirmer institutionnellement)*

### 1.5 Application Flutter (`mobile/csar_comptable`)

| Fichier | Rôle |
| --- | --- |
| `lib/models/fuel.dart` | Modèles `FuelEntry`, `FuelExit`, `FuelReimbursement`, `FuelAttachment` + libellés FR |
| `lib/services/api_service.dart` | Méthodes fuel : dashboard, listes, créations, transitions, multipart upload, téléchargements |
| `lib/screens/fuel/fuel_screen.dart` | Hub 4 onglets (soldes, entrées, sorties, remboursements) |
| `lib/screens/fuel/fuel_entries_screen.dart` | Liste filtrable, saisie, détail, valider/rejeter, PDF, justificatifs |
| `lib/screens/fuel/fuel_exits_screen.dart` | Idem + stock dispo, autoriser, livrer |
| `lib/screens/fuel/fuel_reimbs_screen.dart` | Idem + workflow complet (soumettre → clôturer) |
| `lib/screens/fuel/fuel_widgets.dart` | `FuelStatusChip`, `FuelErrorView`, `FuelAttachmentsSection`, `openFuelPdf` |
| `lib/screens/main_shell_screen.dart` | 4ᵉ onglet « Carburant » (icône station-service) |
| `pubspec.yaml` | `file_picker`, `path_provider`, `open_filex` |

## 2. Vérifications et tests

| Vérification | Résultat |
| --- | --- |
| `php -l FuelApiController.php` | OK |
| `php artisan route:list --path=v1/fuel` | 26 routes |
| Routes Web `/comptable` | 29 routes |
| Compilation vues Blade + PDF | OK |
| `dart analyze lib` (csar_comptable) | **0 issue** |
| `scripts/fuel_smoke.php` | Passé (entrées, sorties, stock, idempotence, remboursements) |
| `tests/Feature/FuelApiTest.php` (nouveau) | **14/14 — 58 assertions** |
| `tests/Feature/ComptableApiTest.php` (régression) | **14/14** |

`FuelApiTest` couvre : auth 401/403, création/validation/rejet d'entrée avec crédit du stock, idempotence de revalidation, blocage de sortie sans stock, cycle autoriser → livrer avec débit, plafond de livraison, workflow remboursement complet (comptable + admin), séparation stock/remboursement, payload dashboard.

## 3. Points de vigilance

- **Règle `admin` provisoire** : approbation/paiement des remboursements — à confirmer avec l'institution.
- **`$disk->download()`** (l.525 du contrôleur API) : alerte Intelephense « Undefined method » — fausse positive, méthode réelle de `FilesystemAdapter`.
- **`flutter pub get`** affiche « symlink support » sous Windows sans mode développeur : les packages sont résolus (`dart analyze` OK) mais le **build APK** exige le mode développeur Windows.
- Mises à jour `dart analyze` : `DropdownButtonFormField.initialValue` utilisé (Flutter 3.41).
- Conforme à la convention de test du projet : schéma SQLite minimal créé en `setUp`, `Sanctum::actingAs`.

## 4. Reste à faire (hors périmètre livré)

- Confirmer la politique de rôle pour l'approbation financière.
- Build APK + test manuel sur device (upload réel, ouverture PDF) — nécessite le mode développeur Windows.
- Aucun commit/push ni déploiement effectué, comme demandé.
