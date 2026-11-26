# Audit de sécurité — Plateforme CSAR

**Date :** 22 octobre 2026
**Périmètre :** backend Laravel 12 (`New-csar-`), API Sanctum, portails web, 4 applications Flutter (Distribution, Scanner, Magasinier, Comptable) + SIM Collecte.
**Références :** OWASP Top 10:2025, OWASP ASVS 5.0.
**Méthode :** audit statique du code + vérifications en lecture seule. Aucun test d'intrusion ni scan agressif sur la production.

## 1. Architecture

- **Backend** : Laravel 12 / PHP 8.2, Sanctum 4.3 (tokens API), Google2FA, dompdf (exports PDF), Socialite.
- **Routes** : `web.php` (portails + public), `api.php` (`/api`), `mobile-api.php` (`/mobile/v1` — SIM + Distribution), `warehouse-api.php` (`/api/warehouse/v1` — Magasinier), `comptable-api.php` (`/api/comptable/v1`).
- **Guards** : `web`, `admin`, et guards par direction (`dtl`, `comptable`, `inspecteur`, `drh`, `cpm`…) via `DirectionPortalMiddleware`.
- **Middlewares clés** : `SecurityHeaders` (CSP + nonce, HSTS, COOP/CORP), `enforce.2fa` (portails admin/dg/drh/ctc), `workflow.role`, `direction:{slug}`, `throttle` + `RateLimiter`.
- **Base** : MySQL `csar`, utilisateur `csar`, sessions en table `sessions`, caches Laravel.

## 2. Points forts confirmés

- **Authentification** : hachage `hashed` (bcrypt), `is_active` vérifié au login, verrouillage progressif par `RateLimiter` (5/min par IP + par email) sur `/api/login`, `/mobile/v1/login`, `/api/warehouse/v1/login`, `/api/comptable/v1/login`.
- **2FA** : middleware `enforce.2fa` obligatoire sur les groupes admin, dg, drh et ctc (`routes/web.php`).
- **Autorisation API** : `denyNonComptable` sur **chaque** endpoint comptable ; `denyWarehouseAccess` par magasin (magasinier limité à ses entrepôts) ; `DistributionController` filtre systématiquement par `Auth::id()` ; missions magasinier scopées à `assigned_to`.
- **Workflow métier** : `workflow.role` sur signature/scan/dg-validate ; portails par direction ; transitions de statut gardées (pas de double validation, rejet de mouvement validé refusé, mission déjà clôturée → 409).
- **Intégrité données** : transactions + `lockForUpdate` sur les mouvements de stock, idempotence `client_ref`, justificatif obligatoire pour les pertes.
- **En-têtes** : CSP avec nonce par requête, HSTS prod, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, Permissions-Policy, COOP/CORP, suppression `X-Powered-By`/`Server`.
- **CORS** : origines explicitement listées (`csar.sn`, `www.csar.sn`, sous-domaines regex), pas de `*`.
- **Sessions** : cookie `HttpOnly`, `SameSite=lax`, `Secure` automatique hors local, durée 480 min.
- **Uploads** : validation `mimes` + `mimetypes` + `max` sur images/documents.
- **Mobile** : toutes les URL en `https://www.csar.sn`, aucun `cleartextTraffic`, validations métier (anti-doublons, `client_ref`).
- **Dépôt** : aucun `.env`, mot de passe ou clé privée n'a jamais été commité (vérifié sur tout l'historique Git).

## 3. Vulnérabilités et risques identifiés

| # | Sévérité | Statut | Élément |
|---|----------|--------|---------|
| V1 | **Critique** | Confirmé | PAT GitHub en clair dans `.git/config` (remotes `origin` + `csar-sn`), affiché en clair dans le terminal → compromis. **Corrigé localement** (URLs nettoyées) ; **révocation GitHub à faire**. |
| V2 | **Critique** | Confirmé | `DB_PASSWORD` et cookies de session `csar_session` affichés en clair dans la session terminal partagée → compromis. Rotation + purge de la table `sessions`. |
| V3 | **Élevé** | Corrigé + test | `User::$hidden` ne masquait pas `two_factor_secret` ni `two_factor_recovery_codes` → fuite dans `Auth::user()` renvoyé par `/api/login`. Fix appliqué + `UserSecretsHiddenTest`. |
| V4 | **Moyen** | Confirmé | Tokens Sanctum stockés en `SharedPreferences` (non chiffré) dans les 4 apps Flutter ; `flutter_secure_storage` installé mais inutilisé côté token. |
| V5 | **Moyen** | Confirmé | `debug.keystore` commité dans `CSARSimCollect/android/app/` → détracké (`.gitignore` couvrait déjà `*.keystore`). |
| V6 | **Moyen** | À trancher métier | `CheckWorkflowRole` : `admin`/`super_admin` bypassent tout contrôle de rôle → un admin peut signer/valider à la place de la Directrice. Conforme à la spec ? À confirmer. |
| V7 | **Moyen** | À vérifier | CSP `script-src` conserve `'unsafe-inline'` même en prod — le nonce existe mais n'est pas appliqué ; protection XSS réduite. |
| V8 | **Faible** | Risque | `/api/system/logs` expose `laravel.log` aux admins ; si des données sensibles sont loguées (mots de passe, tokens), elles sont exposées. Prévoir filtrage. |
| V9 | **Faible** | Risque | `{!! !!}` (HTML non échappé) dans ~15 vues Blade — à auditer au cas par cas (contenu admin de confiance vs entrée utilisateur). |
| V10 | **À vérifier** | Serveur | `composer audit` / `npm audit` non exécutés localement (Composer absent du PATH) — à lancer sur le serveur. |
| V11 | **À vérifier** | Serveur | Le serveur suit `main` (en avance de 2 sur `origin/main`) et a fusionné la branche feature — stratégie de branche de prod à clarifier. |
| V12 | **À vérifier** | Serveur | `'csar'@'localhost'` refusé mais `'csar'@'127.0.0.1'` accepté : grants host-scopés ; le CLI socket échoue (utiliser `php artisan db` ou `-h 127.0.0.1`). |

## 4. État hors périmètre code

- **Serveur** (192.168.2.141) : nginx 1.28.3, HTTPS actif, certificats Cloudflare visibles côté réponses. Pare-feu/SSH/permissions non audités (pas d'accès root). Voir `SAUVEGARDES_RESTAURATION.md`.
- **Sauvegardes** : workflow `backup-platform.sh` documenté ; dump `/tmp/csar_db_dump.sql` créé avec succès via `sudo mysqldump`.
- **Base prod vérifiée** : les 3 migrations `2026_10_21_*` sont appliquées (colonnes, tables `request_missions`/`equipment_assignments`, rôles inspecteur/magasinier/comptable confirmés en lecture seule).

## 5. Matrice d'accès vérifiée (rôles API)

| Endpoint | Non authentifié | Rôle requis | Contrôle supplémentaire |
|---|---|---|---|
| `/api/*` (général) | 401 | sanctum | `admin` requis sur `/users`, `/system/*` |
| `/mobile/v1/*` | 401 | collector (SimCollector) | zones assignées `assigned_zones` |
| `/api/warehouse/v1/*` | 401 | magasinier/admin | magasin rattaché (`denyWarehouseAccess`) |
| `/api/comptable/v1/*` | 401 | comptable strict | `denyNonComptable` sur chaque endpoint |
| `/api/chatbot/ask` | public | — | `RateLimiter` 20/min par IP |
