# Tests de sécurité — CSAR

**Dernière mise à jour :** 26 octobre 2026. **Environnement :** local `C:\xampp\csar.sn` (PHP 8.2, Laravel 12.62.0, SQLite en mémoire pour les tests). Aucune action sur la production.

Seuls les résultats de la section 1 et de la section 4 ont été exécutés lors de cette mise à jour. Les autres contrôles des versions précédentes ne sont pas considérés comme validés.

## 1. Tests automatisés exécutés (26/10/2026)

| Commande | Résultat |
|---|---|
| `php artisan test --filter DgDocumentsAndRejectTest` | ✅ 13/13 (54 assertions) |
| `php artisan test --filter DgReportsTest` | ✅ 7/7 (40 assertions) |
| `php artisan test --filter 'DgDocumentsAndRejectTest\|DgPortalTest'` | ✅ 25/25 lors d'un passage ; 🔴 1 échec unique sur le test d'historique du motif de rejet, non reproduit ensuite (cause non identifiée) |
| `php artisan test` (suite complète) | ✅ 202 passed, 1 skipped (679 assertions) |

La suite complète inclut `ApkDownloadTest` et `UserSecretsHiddenTest`, sans décompte séparé dans cette mise à jour.

Couverture ajoutée sur le lot DG :

- Dépôt de signature et de scan par le DG, avec passage à `signee` puis `scannee`.
- Refus d'un scan sans signature préalable, sans fichier conservé.
- Refus d'un type de fichier non autorisé.
- Refus des rôles admin, signataire et scanneur sur les routes DG ; refus de l'utilisateur admin sur le guard `dg`.
- Refus d'un signataire sur la route admin de scan (403), sans fichier stocké.
- Rejet exigeant un motif côté serveur, motif conservé dans `workflow_history`.
- Modale Bootstrap de rejet présente et absence de `prompt()` de rejet.

Rapports (`DgReportsTest`) :

- Page `/dg/reports` accessible (200, plus de 302).
- PDF réel (`%PDF`, `Content-Type: application/pdf`, nom horodaté).
- CSV réel avec BOM UTF-8 et données de la base.
- Exports CSV pour requests, warehouses, users, personnel, stocks.
- Absence de CNI, de mot de passe et de secret 2FA dans les exports.
- Types et formats inconnus refusés par validation.
- Accès refusé aux invités et aux rôles non DG.


## 2. Contrôles antérieurs (non re-exécutés, non attestés)

Les tableaux ci-dessous datent de versions précédentes du document. Ils ne sont pas re-exécutés dans cette mise à jour et doivent être re-vérifiés avant toute conclusion.

### 2.1 Vérifications serveur (lecture seule, non re-exécutées)

| Vérification | Méthode | Résultat |
|---|---|---|
| Migrations `2026_10_21_*` appliquées | `php artisan db` + `SELECT migration FROM migrations` | ✅ 3 lignes |
| Colonnes `dtl_*` sur `public_requests` | `SHOW COLUMNS` | ✅ 3 colonnes |
| Colonnes `status`, `rejection_reason`, `public_request_id`, `request_mission_id` sur `stock_movements` | `SHOW COLUMNS` | ✅ 4 colonnes, `status` indexé |
| Tables `request_missions`, `equipment_assignments` | `SHOW COLUMNS … LIKE 'id'` | ✅ créées |
| Rôles `comptable`, `inspecteur`, `magasinier` | `SELECT name FROM roles` | ✅ 3 présents |
| Données conservées | `COUNT(*)` | 32 demandes, 3 mouvements — cohérent |
| Routes de téléchargement APK | `curl -sI http://localhost/…` × 5 | ✅ `200 OK`, `Content-Type: application/vnd.android.package-archive`, tailles conformes |
| `git pull` déploiement | `git log --oneline -1` | ⚠️ a nécessité `git pull origin feature/admin-ui-refonte` explicite (serveur sur `main`) |

### 2.2 Contrôles statiques (non re-exécutés)

| Contrôle | Résultat |
|---|---|
| Secrets dans le code (regex `ghp_`, `sk_`, `AKIA`, clés PEM, mots de passe littéraux) | ✅ Aucun dans les fichiers trackés |
| `.env` jamais commité | ✅ `git log --all -- .env` vide |
| Token GitHub dans `.git/config` | 🔴 Confirmé → retiré, révocation demandée (V1) |
| `User::$hidden` vs secrets 2FA | 🔴 Confirmé → corrigé (V3) |
| `cleartextTraffic` / HTTP dans les apps Flutter | ✅ Aucun — `https://www.csar.sn` partout |
| Stockage token mobile | 🟠 `SharedPreferences` non chiffré (V4) |
| Keystore commité | 🟠 `debug.keystore` → détracké (V5) |
| Injections SQL | ✅ Pas de concaténation de paramètres utilisateur dans du SQL brut (les `selectRaw`/`DB::raw` audités n'interpolent pas d'entrée) |
| Uploads | ✅ `mimes` + `mimetypes` + `max` sur les champs fichiers audités |
| Rate limiting | ✅ login ×4 (throttle 5/min + RateLimiter IP+email), chatbot 20/min, API `throttle:api` |
| 2FA | ✅ `enforce.2fa` sur admin/dg/drh/ctc |

## 4. `composer audit` (exécuté le 26/10/2026, sans mise à jour)

Commande : `C:\xampp\php\php.exe composer.phar audit` (Composer n'est pas dans le PATH ; `composer.phar` est présent à la racine du projet). Résultat : **31 avis de sécurité sur 8 paquets**.

| Paquet | Version installée | Sévérité la plus haute | Versions affectées (extrait) |
|---|---|---|---|
| `laravel/framework` | v12.62.0 | **critical** (et medium, low) | `<12.68.0` |
| `guzzlehttp/guzzle` | 7.13.1 | **high** (et medium) | `<7.15.2` (et `<7.15.1`, `<7.14.2`) |
| `league/commonmark` | 2.8.2 | **high** (et medium) | `<2.10.0` selon l'avis |
| `dompdf/dompdf` | v3.1.5 | medium (et low) | `<3.1.6` |
| `phpseclib/phpseclib` | 3.0.55 | medium | `<3.0.57` |
| `league/flysystem` | 3.35.1 | low | `<=3.35.2` |
| `symfony/http-foundation` | v7.4.14 | non qualifiée (plage tronquée) | à vérifier |
| `symfony/yaml` | v7.4.14 | non qualifiée (plage tronquée) | à vérifier |

Points à noter :

- Mise à jour ciblée appliquée ensuite (voir section 7). Cette section 4 décrit l'état **avant** correctif.
- `dompdf` est directement concerné par la génération de rapports PDF (avis SVG, BMP, lecture de fichiers via data-URI). Les vues PDF ne contiennent que des données de l'application, mais la mise à jour vers 3.1.6 ou plus est recommandée.
- Pour `symfony/http-foundation` et `symfony/yaml`, la sortie tronquée ne permet pas de confirmer si la version 7.4.14 est dans la plage affectée : à qualifier avec `composer audit` complet.

## 5. Points ouverts

- **Dépendances** : corrigées (section 7). Reste à déployer sur l'environnement de préproduction avant production.
- **Approbation DG** : corrigé (section 7), modale remplaçant `prompt()`.
- **Rapports** : corrigé (section 7). Les rapports générés sont persistés.
- **Base de données** : les requêtes de rapports ont été exécutées sur SQLite uniquement. MySQL n'a pas été testé ; `DATE()` et `whereDate()` sont utilisés pour rester portables.
- **Route admin de scan** : contrôle de rôle ajouté avant stockage ; le middleware `workflow.role` bloque déjà les rôles non listés (403).
- **Token GitHub dans `.git/config`** : révocation demandée (V1), à confirmer côté utilisateur.
- **Stockage des tokens mobiles** (`SharedPreferences`, V4) et audit des `{!! !!}` Blade (V9) : non traités.
- **Test de restauration** du dump de sauvegarde sur environnement isolé : à planifier (voir `SAUVEGARDES_RESTAURATION.md`).
- **Pentest** boîte grise authentifié sur préproduction : recommandé avant montée de charge.

## 7. Mise à jour du 26/10/2026 (correctifs appliqués)

Dépendances (contraintes `composer.json` inchangées, `composer.lock` mis à jour par résolution ciblée) :

| Paquet | Avant | Après | Avis corrigés |
|---|---|---|---|
| `laravel/framework` | v12.62.0 | v12.70.1 | critical (TokenGuard), relations, XSS page debug |
| `guzzlehttp/guzzle` | 7.13.1 | 7.16.1 | cookies, Referer, Proxy-Authorization, host |
| `dompdf/dompdf` | v3.1.5 | v3.1.6 | SVG, BMP, data-URI, chroot |
| `league/commonmark` | 2.8.2 | 2.10.4 | DoS, XSS attributs |
| `league/flysystem` | 3.35.1 | 3.36.0 | normalisation de chemin |
| `phpseclib/phpseclib` | 3.0.55 | 3.0.57 | X25519 non constant-time |
| `symfony/http-foundation` | v7.4.14 | v7.4.21 | ReDoS HeaderUtils |
| `symfony/yaml` | v7.4.14 | v7.4.21 | récursion YAML |

- `composer audit` après correctifs : **aucun avis**.
- Retrait transitif : `thecodingmachine/safe` n'est plus requis (aucun usage dans l'application).
- Paquets directs hors périmètre sécurité non mis à jour (ex. `doctrine/dbal`, `laravel/sanctum`, `phpunit`) : mises à jour mineures disponibles, non appliquées.

Rapports et workflow :

- `DG\ReportsController::index()` : erreur journalisée avec trace, message générique, redirection vers le tableau de bord DG.
- Rapports générés persistés dans `storage/app/reports` (privé, hors `public/`), nom unique, téléchargement via routes DG authentifiées, purge après 30 jours (fichiers `csar-dg-*` uniquement).
- Exports CSV : non persistés (téléchargement direct).
- Champs exposés : aucun CNI, mot de passe, secret 2FA ou token.
- Approbation DG : modale Bootstrap, commentaire optionnel, erreurs serveur affichées.

Tests :

- Suite complète SQLite (`php artisan test`) : **204 passed, 1 skipped (687 assertions)**.
- Tests DG/rapports : 35 passed (lot DG ciblé).
- MySQL/MariaDB : **non validé** (voir section 8).

## 8. Validation MySQL : non réalisée

- Base dédiée `csar_test_mysql` créée sur le MariaDB local XAMPP (10.4.32). Aucune base existante modifiée, aucune migration sur la production.
- Lors de la tentative, `mysqld` lancé avec `--console` s'arrête juste après « ready for connections » (signal `^C` reçu du contexte d'appel). Lancé avec `--standalone` et `--bind-address=127.0.0.1`, il reste actif. La validation n'a pas été exécutée : elle a été annulée à la demande de l'utilisateur.
- Commande à lancer (les tests créent et réinitialisent uniquement `csar_test_mysql`) :

```powershell
$env:DB_CONNECTION='mysql'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='3306'; $env:DB_DATABASE='csar_test_mysql'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
C:\xampp\php\php.exe artisan test
```

- Le serveur MariaDB local est 10.4 ; la production (si MySQL 8) n'est pas représentée. Un test sur la version cible reste nécessaire.

## 6. Preuves expurgées

Les sorties sensibles (mots de passe, cookies, tokens) ont été volontairement omises de ce document. Les valeurs exposées lors de la session sont listées dans `PLAN_CORRECTIONS.md` (V1, V2) avec la procédure de révocation.
