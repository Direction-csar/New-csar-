# Rapport de validation fonctionnelle — Gestion des stocks CSAR

**Date** : 24/11/2026 — **Environnement** : local (`APP_ENV=local`, SQLite `database/database.sqlite`), données 100 % fictives (`demo.*@csar.test`, « DEMO Magasin … »).
**Périmètre** : circuit de vérification obligatoire pour TOUS les mouvements de stock (Web, APK, API, sync hors ligne), portails Web, APK Magasinier & Inspecteur, sécurité, non-régression.

---

## 1. Scénario de démonstration exécuté (API HTTP réelle = même chemin que les APK)

Script : `_demo_circuit.php` (local, non versionné). **40+ contrôles, tous OK.**

| Étape | Action | Résultat vérifié |
|---|---|---|
| 1 | Login APK magasinier Dakar / Thiès / inspecteur (`/login`, `/login/inspecteur`) | 3 tokens OK |
| 2 | Entrée 20 sacs Riz [50kg] via `POST /movements` | HTTP 201, statut `en_attente`, **stock officiel inchangé** |
| 2b | Le magasinier tente de vérifier son propre mouvement | **403 refusé** |
| 2c | Vérification par l'inspecteur (`POST /pending-movements/{id}/verify`) | 200, stock officiel **+20** |
| 2d | Renvoi du même `client_ref` | `duplicate`, aucun doublon |
| 3 | Sortie 10 sacs | `en_attente` → vérification → **−10** ; sortie démesurée (99 999) → **422** |
| 4 | Transfert 15 sacs Dakar → Thiès (`POST /transfers`) | 2 jambes `en_attente`, aucun stock officiel bougé |
| 4b | Vérification de la jambe « in » **avant** confirmation réception | **409 refusé** |
| 4c | `confirm-receipt` par le magasinier Thiès | 200 ; par le magasinier Dakar → **403** |
| 4d | Vérification inspecteur des 2 jambes | source **−15**, destination **+15** |
| 5 | Inventaire (écart −7) via `POST /inventory` | `en_attente`, stock officiel inchangé |
| 5b | L'inspecteur tente de valider l'ajustement | **403** (réservé DTL/DG) |
| 5c | Validation par la DTL (service = code du portail Web) | OK, stock officiel **−7** |
| 6 | Sync hors ligne `/sync` (5 sacs) + rejeu identique | `en_attente`, rejeu → `duplicate`, vérification → **+5** |
| 7 | Circuit `stock_operations` : DG crée → DTL affecte → inspecteur transmet → magasinier exécute via API → inspecteur vérifie | mouvement `en_attente` après exécution, stock officiel **−8** seulement après vérification |
| 8 | Contournements : magasinier lit sa file OK ; magasinier Dakar → magasin Thiès **403** ; inspecteur → `POST /movements` **403** | tous bloqués |

**Stock final** : Dakar 850 sacs, Thiès 170 sacs — cohérent avec les mouvements vérifiés.

## 2. Interfaces Web — captures (`_captures/`)

32 pages capturées via navigateur headless, **aucune page blanche, aucune erreur JS** (après correction d'un bug d'apostrophe, voir §4) :

- **Magasinier** (`demo.magasinier@`) : dashboard, magasins, fiche stock, opérations, mouvements en attente, formulaires transfert/inventaire.
- **Inspecteur** (`demo.inspecteur@`) : missions, opérations, mouvements en attente.
- **DTL** (`demo.dtl@`) : dashboard, opérations de stock, mouvements en attente.
- **DG** (`demo.dg@`, 2FA TOTP validée) : dashboard exécutif, situation des stocks, stocks, entrepôts, opérations, file en attente, création d'instruction.
- **Admin** (`demo.admin@`, 2FA) : magasins (CRUD), stocks.

## 3. Tests de permissions Web

| Test | Résultat |
|---|---|
| Magasinier Dakar → `/magasinier/magasins/3` (Thiès) | **HTTP 403** |
| Inspecteur → `/magasinier/magasins/2` | redirigé login (pas d'accès) |
| Inspecteur → `/dg/stocks` | redirigé `/dg/login` |
| Inspecteur → `POST /movements` (API) | **403** (garde de rôle ajoutée) |
| Magasinier → vérifier son propre mouvement | **403** |
| Magasinier non destinataire → `confirm-receipt` | **403** |
| Réception transfert vérifiée avant confirmation | **409** |

## 4. Anomalies trouvées et corrigées pendant la validation

| # | Anomalie | Gravité | Correction |
|---|---|---|---|
| 1 | `stock_movements.stock_id` NOT NULL + FK alors que les mouvements sont désormais au niveau magasin → **HTTP 500** sur tout mouvement API | **Bloquant** | Migration `2026_11_24_000001_make_stock_id_nullable_on_stock_movements` |
| 2 | Token inspecteur pouvait créer des mouvements via `/movements`, `/sync`, `/transfers`, `/inventory`, `/missions/{id}/report` | **Élevée** (contournement) | Garde `warehouseWriteGuard` : écriture réservée à magasinier/admin/super_admin |
| 3 | Transfert impossible entre magasins distincts : le déclarant devait avoir accès aux 2 magasins (API + service + formulaire Web) | Moyenne | Accès requis sur la **source** seulement ; la réception est confirmée par le magasinier destinataire (contrôle conservé) ; liste des destinations complète sur le Web |
| 4 | Apostrophe `d'abord` dans un `innerHTML` JS → tout le script des formulaires mouvement/transfert plantait (`Unexpected identifier 'abord'`) | Moyenne | Guillemets corrigés, 0 erreur JS |

## 5. Tests automatisés

- **Suite Laravel complète : 469 passed, 1 skipped** (2 280 assertions) — aucune régression (distribution, RH, demandes, workflow, etc.).
- `WarehouseKeeperApiTest`, `MagasinierPortalTest`, `StockOperationsCircuitTest` : 47 tests ciblés OK après les correctifs.

## 6. Limites de cette phase

- **APK** : builds debug prêts (`mobile/csar_magasinier/.../app-debug.apk`, `inspector-app/.../app-debug.apk`) mais **aucun appareil/émulateur Android disponible** sur ce poste → tests sur téléphone à faire par vous (login, photos, signature, GPS, sync). L'API sous-jacente est couverte par la simulation E2E ci-dessus.
- **Captures mobiles** : non produites (pas d'émulateur).
- **DG Web** : 2FA obligatoire (conservée, vérifiée avec TOTP réel).

## 7. Inventaire production — procédure `stock:audit`

Commande **lecture seule**, déjà sur la branche. Sur le serveur après `git pull` :

```bash
cd /var/www/csar
git pull
php artisan migrate --force   # applique la migration stock_id nullable
php artisan stock:audit
cat storage/app/audit-stock-*.json   # à m'envoyer pour analyse
```

Le rapport JSON inventorie : magasins (+responsables), comptes magasinier/inspecteur/admin, produits, stocks, mouvements, alertes, missions — base pour distinguer réel vs fictif **avant tout nettoyage** (aucune suppression sans validation).

## 8. Livrables

- APK : `mobile/csar_magasinier/build/app/outputs/flutter-apk/app-debug.apk` (~199 Mo), `inspector-app/build/app/outputs/flutter-apk/app-debug.apk` (~178 Mo)
- Captures Web : `_captures/*.png` (32 pages + page d'accès refusé)
- Scénario démo rejouable : `_demo_circuit.php` (affiche chaque contrôle OK/ÉCHEC)
- Doc API : `docs/API-STOCK-MOBILE.md`
