# API Stock & Inspections — Contrat mobile (APK Magasinier / APK Inspecteur)

Base URL : `{hôte}/api/warehouse/v1`
Authentification : jeton Sanctum (`Authorization: Bearer <token>`), obtenu par login.
Toutes les réponses sont en JSON. Les codes d'erreur métier renvoient `{"message": "…"}` avec HTTP 4xx.

## 1. Authentification

### Magasinier
`POST /login` — `{email, password}` → `{token, user}` (rôle `magasinier`, `admin`, `super_admin`).

### Inspecteur (APK Inspecteur)
`POST /login/inspecteur` — `{email, password}` → `{token, user}` (rôle `inspecteur` uniquement).

## 2. Circuit opérationnel (DG → DTL → Inspecteur → Magasinier)

Statuts d'une opération (`stock_operations.status`) :
`soumise` → `affectee_inspecteur` → `transmise_magasinier` → `executee` → `verifiee`
Écarts possibles : `anomalie`, `rejetee`, `annulee`.

**Règle centrale** : une opération exécutée crée des mouvements `en_attente`
qui ne modifient **pas** le stock officiel tant que l'inspecteur n'a pas vérifié.

| Endpoint | Rôle | Description |
|---|---|---|
| `GET /operations?status=` | inspecteur, magasinier, admin | Liste des opérations visibles (filtrées par affectation). |
| `GET /operations/{id}` | idem | Détail + mouvements liés. |
| `GET /operations/magasinier-candidates?warehouse_id=` | inspecteur, dtl, dg, admin | Magasiniers actifs rattachés au magasin (pour la transmission). |
| `POST /operations/{id}/transmit` | inspecteur | `{assigned_magasinier_id, inspector_comment?, latitude?, longitude?}` — uniquement si `affectee_inspecteur`. |
| `POST /operations/{id}/execute` | magasinier | `{quantity_executed, quantity_executed_kg?, position?, observation?, movement_date?, client_ref?, photo_base64?, signature_base64?}` — idempotent via `client_ref`. |
| `POST /operations/{id}/verify` | inspecteur | `{inspector_comment?, latitude?, longitude?}` — intègre au stock officiel. |
| `POST /operations/{id}/anomaly` | inspecteur | `{anomaly_report, photo_base64?, latitude?, longitude?}`. |
| `POST /operations/{id}/reject` | inspecteur, dtl, dg, admin | `{rejection_reason}`. |

## 2bis. Mouvements directs — vérification obligatoire

**Règle centrale** : *tout* mouvement qui peut modifier le stock officiel —
saisi via `POST /movements`, `POST /sync`, `POST /transfers`, `POST /inventory`,
le portail Web ou l'APK — est créé avec `status = "en_attente"` et **ne compte
pas** dans le stock officiel (`warehouses.current_stock`, `/stock-status`,
`/warehouses/{id}/sheet`). Il n'est intégré qu'après vérification.

Statuts mouvement : `en_attente` → `verifie` (ou `rejetee` / `anomalie`).
Les anciens mouvements sans statut (`null`) ou `verified` restent officiels.

### File de vérification

| Endpoint | Rôle | Description |
|---|---|---|
| `GET /pending-movements?warehouse_id=&type=` | inspecteur, magasinier, dtl, dg, admin | Mouvements `en_attente` (hors opérations). Magasinier : ses magasins + réceptions de transfert. Chaque item : `id, reference, type, type_label, quantity, reason, warehouse, creator, created_at, is_adjustment, is_transfer_in, receipt_confirmed`. |
| `POST /pending-movements/{id}/verify` | inspecteur, dtl, dg, admin | Intègre le mouvement au stock officiel (`validated_by`, `validated_at`). Les ajustements/inventaires exigent dtl/dg/admin ; les sorties revérifient le solde officiel. |
| `POST /pending-movements/{id}/reject` | inspecteur, dtl, dg, admin | `{rejection_reason}` — le mouvement ne comptera jamais. |
| `POST /pending-movements/{id}/confirm-receipt` | magasinier (destinataire), inspecteur, dtl, dg, admin | Confirme la réception physique d'une jambe `entry` de transfert (`metadata.receipt_confirmed_*`). Idempotent. |

### Transferts

Un transfert crée deux mouvements `en_attente` liés par `numero_transfert` :
- jambe `transfer` (sortie, magasin d'origine) : vérifiée par l'inspecteur/DTL/DG ;
- jambe `entry` + `metadata.transfer_type = "in"` (réception, magasin destinataire) :
  **doit** d'abord être confirmée par le magasinier destinataire
  (`confirm-receipt`) avant de pouvoir être vérifiée — la direction peut passer
  outre. Pour les transferts issus d'une `stock_operation`, la vérification de
  l'opération est bloquée tant que la réception n'est pas confirmée.

### Inventaires & ajustements

`type = "adjustment"` (inventaire physique) : vérification réservée à
**DTL / DG / admin** (l'inspecteur ne peut pas valider une correction de stock).
Le delta appliqué = `quantity_after − stock officiel au moment de la vérification`
(auto-corrige si d'autres mouvements ont été validés entre-temps).

### Hors ligne

`POST /sync` reste idempotent (`client_ref`) : les mouvements synchronisés sont
créés `en_attente`, jamais dédoublonnés, et suivent le même circuit de
vérification. Les portails Web exposent la même file sous
`{portail}/operations-pending` (ou `dg|dtl/stock-operations-pending`).

## 3. Stock (lecture + saisie magasinier)

| Endpoint | Rôle | Description |
|---|---|---|
| `GET /stock-ops/warehouses` | inspecteur, magasinier | Magasins accessibles (magasinier : uniquement les siens). |
| `GET /stock-ops/status?warehouse_id=` | idem | Stock officiel par produit/format + péremptions. |
| `GET /warehouses` | magasinier | Ses magasins affectés. |
| `GET /warehouses/{id}/sheet` | magasinier | Fiche de stock du magasin. |
| `GET /products` | magasinier | Produits et formats. |
| `POST /movements` | magasinier | Entrée/sortie → mouvement `en_attente` (voir §2bis). |
| `POST /sync` | magasinier | Synchronisation groupée idempotente (`client_ref`). |
| `POST /transfers` | magasinier | Transfert entre ses magasins. |
| `POST /inventory` | magasinier | Inventaire physique (écart → mouvements). |
| `GET /stock-status`, `/alerts`, `/receipt/{reference}`, `/stats` | magasinier | Consultations. |
| `GET /missions`, `POST /missions/{id}/report` | magasinier | Missions DTL existantes (sorties planifiées). |

## 4. Synchronisation Web ↔ APK

- Web et APK appellent les **mêmes services** (`WarehouseStockService`, `StockOperationService`) sur la même base.
- Toute saisie Web apparaît dans l'APK et réciproquement.
- Idempotence : envoyer `client_ref` unique par opération ; un doublon est ignoré sans erreur.
- Concurrence : les écritures de stock utilisent `lockForUpdate` ; une sortie supérieure au stock produit/format est refusée (HTTP 422).
- Les photos/signatures transitent en `photo_base64` / `signature_base64` (data URL base64 acceptée) et sont stockées hors zone publique.

## 5. Traçabilité

Chaque action (transmission, exécution, vérification, anomalie, rejet, mouvement direct) écrit une entrée `audit_logs` avec acteur, date et référence d'opération. Aucun mouvement vérifié n'est supprimé physiquement (soft delete).
