# Plan de corrections — Sécurité CSAR

**Référence :** vulnérabilités V1–V12 de `AUDIT_SECURITE.md`.
**Règle :** corrections minimales, compatibles avec l'architecture existante ; aucun déploiement sans autorisation.

## Priorité 1 — Critique (immédiat)

### V1 — Token GitHub exposé (PAT `ghp_…`)

- **Fait** : URL des remotes `origin`/`csar-sn` nettoyées (`.git/config` local). Le credential helper `manager` est actif et prendra le relais.
- **Reste à faire (action humaine requise)** :
  1. GitHub → Settings → Developer settings → Personal access tokens → **révoquer** le token exposé.
  2. Créer un nouveau PAT (scope `repo` minimal) et l'enregistrer dans le Gestionnaire d'identifiants Windows.
  3. Premier `git push` : saisir le nouveau token à la fenêtre d'authentification.
  4. Vérifier l'historique d'activité GitHub du compte (connexions suspectes) : Settings → Security log.
- **Ne pas** considérer le token sécurisé car supprimé d'un commit : il était dans `.git/config` (non tracké) — la révocation est la seule protection.

### V2 — `DB_PASSWORD` et cookies de session exposés

- **À faire (serveur, avec autorisation)** :
  1. Changer le mot de passe MySQL : `ALTER USER 'csar'@'127.0.0.1' IDENTIFIED BY '<nouveau>';` (et `'csar'@'localhost'` si le compte existe).
  2. Mettre à jour `DB_PASSWORD` dans `/var/www/csar/.env` puis `php artisan config:cache`.
  3. Invalider toutes les sessions : `TRUNCATE sessions;` (driver database) — déconnecte tout le monde, planifier hors heures de pointe.
  4. Vérifier les comptes actifs : `SELECT id,email,role FROM users WHERE is_active=1;` — désactiver tout compte inconnu.
- **Ne jamais** afficher la nouvelle valeur dans le terminal ou un rapport.

## Priorité 2 — Élevé

### V3 — Fuite `two_factor_secret` via l'API ✅ CORRIGÉ

- `app/Models/User.php` : `two_factor_secret` + `two_factor_recovery_codes` ajoutés à `$hidden`.
- Test de régression : `tests/Feature/UserSecretsHiddenTest.php` (2 tests, verts).
- **Action complémentaire** : envisager de régénérer les secrets 2FA des comptes dont `two_factor_enabled=1` si une exposition antérieure est suspectée.

## Priorité 3 — Moyen

### V4 — Tokens mobiles en `SharedPreferences`

- **Correction cible** : migrer le stockage du token vers `flutter_secure_storage` (déjà présent dans `mobile-app`, à ajouter dans `distribution-app`, `scanner-app`, app Magasinier).
- Fichiers concernés : `lib/services/auth_service.dart` / `distribution_auth_service.dart` dans chaque app.
- Migration en douceur : lire l'ancien `SharedPreferences`, écrire dans le secure storage, supprimer l'ancienne clé au premier lancement post-mise à jour.
- **Nécessite une regénération des APK signés** — à planifier, pas avant validation.

### V5 — `debug.keystore` commité ✅ CORRIGÉ

- Détracké via `git rm --cached` (le fichier reste en local, `.gitignore` le couvre).
- Vérification à faire : aucun `key.properties` ni keystore release n'a jamais été commité (historique contrôlé — RAS).

### V6 — Bypass admin dans `CheckWorkflowRole`

- **Constat** : `admin`/`super_admin` passent tous les filtres de rôle, et figurent explicitement dans les listes `workflow.role` des actions signature/validation DG.
- **Décision métier requise** : l'administrateur doit-il pouvoir signer à la place de la Directrice ?
  - Si **non** : retirer `admin`/`super_admin` du bypass et des listes de rôles de signature/validation.
  - Si **oui** (délégation) : conserver, documenter la délégation et journaliser l'action (`logWorkflowAction` le fait déjà partiellement).

### V7 — CSP `script-src 'unsafe-inline'` en production

- Le nonce CSP est généré mais pas appliqué à `script-src`. Correction : retirer `'unsafe-inline'` en prod et porter les scripts inline sur `nonce="{{ csp_nonce() }}"` — chantier moyen (toutes les vues Blade à balayer). **Faire en ticket dédié.**

## Priorité 4 — Faible / hygiène

| # | Action |
|---|--------|
| V8 | Filtrer `/api/system/logs` : exclure les lignes contenant `password`, `token`, `secret`, `Authorization` avant de les renvoyer à l'admin ; envisager un canal `security` dédié. |
| V9 | Audit des `'{!! !!}'` Blade : remplacer par `{{ }}` partout où la donnée vient de l'utilisateur ; conserver uniquement pour du HTML généré de confiance. |
| V10 | Sur le serveur : `composer audit` et `npm audit` → traiter les CVE remontées en versions mineures compatibles. |
| V11 | Clarifier la branche de production : soit `main` (merger la feature via PR), soit la branche feature. Actuellement le serveur est sur `main` local en avance de 2 — risque de divergence au prochain `git pull origin main`. |
| V12 | Normaliser les grants MySQL : documenter que l'app utilise `csar@127.0.0.1` (TCP) ; créer ou corriger `csar@localhost` si le CLI socket est souhaité, ou simplement noter d'utiliser `-h 127.0.0.1` / `php artisan db`. |

## État d'avancement

| # | Correction | État | Test |
|---|-----------|------|------|
| V1 | URLs remotes nettoyées | Fait (local) ; révocation GitHub **à faire par l'utilisateur** | `git remote -v` |
| V2 | — | Procédure prête, à exécuter sur serveur avec autorisation | — |
| V3 | `$hidden` étendu | **Fait + push en attente** | `UserSecretsHiddenTest` ✅ |
| V5 | keystore détracké | **Fait** (commit en attente) | `git ls-files` |
| V4, V6–V12 | — | Planifiées | — |
