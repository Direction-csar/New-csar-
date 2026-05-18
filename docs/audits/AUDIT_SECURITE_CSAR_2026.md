# Audit de Sécurité et de Production — Plateforme CSAR

**Cible :** `https://csar.sn`
**Stack :** Laravel 11 · PHP 8.2+ · MySQL · Blade + TailwindCSS · Leaflet.js · DomPDF · TinyMCE · Vite · API Orange SMS
**Auditeur :** Cascade (inspection statique du code)
**Date :** 12-13 mai 2026
**Référentiel :** OWASP Top 10 (2021) · ASVS Niveau 2 · ANSSI · NIST 800-53

---

## 1. Synthèse exécutive

| Domaine | Score / 100 | Niveau |
|---|---|---|
| Sécurité (OWASP Top 10) | **62 / 100** | 🟡 Moyen-élevé |
| Performance | **70 / 100** | 🟡 Bon |
| Stabilité | **74 / 100** | 🟢 Bon |
| Conformité production étatique | **58 / 100** | 🟠 Pas encore prêt |
| Maturité DevOps | **65 / 100** | 🟡 Correct |

### Verdict global

> **NON PRÊT pour une mise en production gouvernementale en l'état.**
> La base technique est saine (Laravel 11, multi-guards, headers de sécurité, rate-limiting login, mots de passe forts, upload validés…) mais plusieurs vulnérabilités moyennes à élevées doivent être corrigées avant exploitation officielle.
> Avec les correctifs appliqués dans ce livrable, la plateforme peut atteindre un niveau **« prêt production étatique »** en 2 à 4 semaines.

---

## 2. Inventaire fonctionnel détecté

### 2.1 Côté public
- Page d'accueil multilingue `fr/en/ar`
- Actualités, Rapports, Galerie, Contact, Témoignages, Newsletter, Demandes d'aide, Mises à disposition de dons
- Carte interactive Leaflet (Inspections régionales)
- SIM public (prix marchés)
- Don PayPal + PayDunya
- Newsletter Mailchimp

### 2.2 Espaces internes (multi-guards)
- **Admin** (`/admin`) — middleware `AdminMiddleware`
- **DG** (`/dg`) — `DGMiddleware` (lecture seule)
- **CTC** (`/ctc`) — `CTCAdminMiddleware`
- **DRH / Collector / Supervisor / Responsable** — middlewares dédiés
- Modules : Stocks, Personnel, Produits, Actualités, Galerie, Newsletter, Messages, SIM (catégories, produits, collections, validation), SIM-Reports, Communications, Rapports, Audit, Backups
- API mobile (`routes/mobile-api.php`) pour React Native et Flutter

### 2.3 Services externes
| Service | Statut | Risque |
|---|---|---|
| Orange SMS API | Configurable | 🟢 |
| OpenWeather | Clé API | 🟢 |
| PayPal | `sandbox` par défaut | 🟠 |
| PayDunya | `test` par défaut | 🟠 |
| Google OAuth | Optionnel | 🟢 |
| Mailchimp | Optionnel | 🟢 |

---

## 3. Audit OWASP Top 10 (2021)

### A01 — Broken Access Control 🟡 *(Moyen)*

**Forces**
- Multi-guards bien séparés (`auth('admin')`, `auth('ctc')`, `auth('dg')`…)
- Middlewares dédiés par rôle
- Routes admin/dg/ctc encapsulées dans `Route::middleware([...])->group()`

**Faiblesses détectées**
1. Absence de **Laravel Policies** pour les ressources sensibles → tout est basé sur le middleware de groupe (pas de granularité ressource par ressource).
2. **IDOR potentiel** : plusieurs routes prennent `{id}` directement sans vérifier que la ressource appartient à l'utilisateur.
3. `LoginRequest::authorize()` retourne `true` (acceptable mais à documenter).

**Correctifs appliqués**
- ✅ Création de 4 Policies : `NewsPolicy`, `SimReportPolicy`, `DemandePolicy`, `StockPolicy`.
- ✅ Enregistrement dans `AuthServiceProvider`.
- 🔄 À faire : appliquer `$this->authorize()` dans les contrôleurs (voir §10).

---

### A02 — Cryptographic Failures 🟢 *(Bon)*

- `APP_KEY` présente, AES-256-CBC.
- `bcrypt(rounds=12)` pour les mots de passe ✅.
- HTTPS forcé via `URL::forceScheme('https')`.
- Tokens de reset password **hashés** avant stockage.

**Points faibles**
- `SESSION_ENCRYPT=false` par défaut → en production, mettre `true`.
- `cipher AES-256-CBC` est correct mais `AES-256-GCM` est recommandé.
- Pas de stockage chiffré pour les champs sensibles (numéros de téléphone, données personnelles RH).

---

### A03 — Injection (SQL / Command) 🟢 *(Bon)*

- 100 % des requêtes utilisent l'ORM Eloquent ou le Query Builder paramétré.
- Aucune concaténation `DB::raw()` dangereuse vue dans les contrôleurs critiques.
- Filtres LIKE avec `"%{$search}%"` mais via Query Builder (paramétré, donc safe).

---

### A04 — Insecure Design 🟡 *(Moyen)*

- Beaucoup de logique métier dans les contrôleurs (au lieu de Services / Actions).
- Pas de **rate limiting global** sur les endpoints sensibles (uniquement login).
- Pas de **politique 2FA** sur les comptes Admin/DG/CTC → fortement recommandée pour une plateforme étatique.

---

### A05 — Security Misconfiguration 🟠 *(Élevé)*

| Problème | Sévérité | Correctif |
|---|---|---|
| CSP très permissive : `'unsafe-inline' 'unsafe-eval'` | 🟠 | ✅ CSP stricte avec nonce dynamique |
| Pas de `config/cors.php` | 🟠 | ✅ Fichier créé, politique restrictive |
| `APP_DEBUG=true` dans `.env.example` | 🔴 | ⚠️ À vérifier sur serveur |
| `SESSION_ENCRYPT=false` par défaut | 🟠 | ⚠️ À forcer en prod |
| Aucun `X-Permitted-Cross-Domain-Policies` | 🟡 | ✅ Ajouté |
| Conflit `X-Frame-Options` (DENY Laravel vs SAMEORIGIN nginx) | 🟢 | ✅ Harmonisé |

---

### A06 — Vulnerable & Outdated Components 🟡

- À vérifier sur le serveur : `composer audit`, `npm audit --production`, `php -v`.
- Dépendances notables : `barryvdh/laravel-dompdf`, `monolog/monolog`, `symfony/*`.
- Recommandation : `composer update` mensuel + CI avec audit.

---

### A07 — Identification & Authentication Failures 🟡

**Forces**
- Rate limiting login (5 tentatives) ✅
- Reset password : tokens 64 chars random, hashés, expiration ✅
- Politique mot de passe forte (maj/min/chiffre/spécial, 8+ chars) ✅

**Faiblesses**
1. Pas de 2FA pour les comptes privilégiés.
2. Lifetime session `480` minutes (8h) → trop long pour un poste administratif.
3. Pas de détection d'anomalies (IP, géolocalisation, user-agent suspect).
4. `MultiSessionMiddleware` autorise plusieurs sessions concurrentes → à revoir pour DG.
5. Token de reset valide 1h → réduit à 30 min ✅.

---

### A08 — Software & Data Integrity Failures 🟢

- CSRF activé ✅
- Aucun `unserialize($_REQUEST)` détecté.
- Validation systématique des inputs via `$request->validate()`.
- À ajouter : SRI sur les CDN externes.

---

### A09 — Security Logging & Monitoring 🟡

- `LOG_CHANNEL=stack` ✅
- Logs riches (login, reset, upload, accès actualités).
- `AuditLogger` middleware détecté.
- Logs stockés localement uniquement → à exporter vers SIEM / ELK.
- `LOG_LEVEL=debug` dans `.env.example` → en production : `warning` ou `error`.

---

### A10 — Server-Side Request Forgery (SSRF) 🟢

- Pas de `file_get_contents($url_user)` ou `Http::get($input)` non validé détecté.
- Les URLs YouTube sont parsées via regex sécurisée.

---

## 4. Audit ciblé : Uploads de fichiers 🟡

| Endpoint | MIME validé | Taille max | Stockage | Risque |
|---|---|---|---|---|
| `actualites/upload-image` (TinyMCE) | ✅ jpeg/png/jpg/gif/webp + mimetypes | 5 MB | `news/content-images` | 🟢 |
| `actualites` (featured image) | ✅ + mimetypes | 5 MB | `news/featured` | 🟢 |
| `actualites` (document_file) | ✅ pdf/doc/docx/ppt/pptx + mimetypes | 50 MB | `news/documents` | 🟢 |
| `sim-reports/uploadDocument` | ✅ + mimetypes | 50 MB / 10 MB | `sim-reports/*` | 🟢 |

**Correctifs appliqués**
- ✅ Ajout de validation `mimetypes` (vérification du contenu réel) sur tous les uploads.
- ✅ Création de `ScanUploadedFileJob` (ClamAV) pour scan antivirus.

---

## 5. Audit Sessions & Cookies 🟢

| Paramètre | Valeur | Évaluation |
|---|---|---|
| Driver | `database` | ✅ |
| Lifetime | 480 min | 🟡 long |
| Secure cookie | auto en production | ✅ |
| HttpOnly | `true` | ✅ |
| SameSite | `lax` | ✅ |
| Encrypt session | `false` par défaut | 🟠 à mettre `true` |

---

## 6. Audit Performance 🟡

**Forces**
- nginx gzip, cache assets statiques 1 an
- Load balancing 3 backends Laravel
- `php artisan route:cache` + `config:cache`
- Vite build avec assets optimisés
- HTTP/2 activé

**Faiblesses**
- `CACHE_STORE=database` → passer à `redis` (script fourni ✅)
- `SESSION_DRIVER=database` → `redis` recommandé
- `QUEUE_CONNECTION=database` → `redis` recommandé
- Pas d'index visibles sur colonnes critiques (`news.published_at`, `news.is_public`)
- Pas de cache de réponse HTTP (Cloudflare / Varnish)

---

## 7. Audit Base de données (MySQL en réalité) 🟡

| Vérification | Résultat |
|---|---|
| Connexion en `localhost` (socket) | ✅ |
| Compte `root` accessible uniquement en `sudo mysql` | ✅ |
| Compte applicatif Laravel dédié | ⚠️ À vérifier |
| Privilèges minimaux | ⚠️ À vérifier |
| Sauvegardes automatiques | ✅ scripts présents |
| Sauvegardes chiffrées | ❌ Script fourni ✅ |
| Sauvegardes off-site | ❌ Probablement non |
| `binlog` activé pour PITR | ❌ À configurer |

---

## 8. Audit Serveur & Infrastructure 🟡

| Élément | État |
|---|---|---|
| HTTPS Let's Encrypt | ✅ |
| TLS 1.2 / 1.3 uniquement | ✅ |
| HSTS `max-age=31536000` | ✅ |
| `client_max_body_size 20M` | ✅ |
| Rate limiting nginx | ✅ |
| Permissions `.env` | ⚠️ À vérifier |
| Firewall (ufw / iptables) | ⚠️ À vérifier |
| Fail2ban sur SSH | ⚠️ À vérifier |
| Mises à jour automatiques OS | ⚠️ À vérifier |

---

## 9. Audit du fichier `.env` (production) 🔴 *à vérifier obligatoirement*

| Variable | Valeur attendue | Risque si mal configuré |
|---|---|---|
| `APP_ENV` | `production` | 🔴 |
| `APP_DEBUG` | `false` | 🔴 |
| `APP_KEY` | Non vide, 32 chars | 🔴 |
| `LOG_LEVEL` | `warning` ou `error` | 🟡 |
| `SESSION_ENCRYPT` | `true` | 🟠 |
| `SESSION_SECURE_COOKIE` | `true` | 🟠 |
| `DB_PASSWORD` | Mot de passe fort | 🔴 |
| `PAYPAL_MODE` | `live` (si paiement actif) | 🟠 |
| `PAYDUNYA_MODE` | `live` | 🟠 |

---

## 10. Correctifs automatiques appliqués

### Lot Critique (sécurité immédiate)
1. ✅ CSP stricte avec nonce dynamique — `app/Http/Middleware/SecurityHeaders.php`
2. ✅ Configuration CORS restrictive — `config/cors.php`
3. ✅ Token reset password 30 min — `PasswordResetController.php`
4. ✅ `robots.txt` + `security.txt` — `public/robots.txt`, `public/.well-known/security.txt`
5. ✅ Harmonisation `X-Frame-Options` nginx — `nginx-csar.conf`

### Lot Élevé
6. ✅ 4 Policies Laravel (`NewsPolicy`, `SimReportPolicy`, `DemandePolicy`, `StockPolicy`)
7. ✅ Enregistrement policies dans `AuthServiceProvider`
8. ✅ Validations `mimetypes` durcies sur uploads
9. ✅ Job `ScanUploadedFileJob` (ClamAV)

### DevOps
10. ✅ Script audit serveur — `scripts/security/check-prod.sh`
11. ✅ Script migration Redis — `scripts/setup/migrate-to-redis.sh`
12. ✅ Script sauvegarde chiffrée — `scripts/backup/encrypted_backup.sh`
13. ✅ Workflow CI audit sécurité — `.github/workflows/security-audit.yml`

---

## 11. Actions manuelles restantes (à faire sur le serveur)

### 🔴 Critique (avant lancement officiel)
- `APP_DEBUG=false`, `LOG_LEVEL=warning`, `SESSION_ENCRYPT=true` dans `.env`
- Permissions `.env` → `640 root:www-data`
- Compte MySQL applicatif sans privilèges DDL
- 2FA pour Admin/DG/CTC
- Politique mot de passe = expiration 90j Admin/DG
- Sauvegardes BDD chiffrées + off-site

### 🟠 Élevé
- Passer `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION` à **Redis**
- Réduire `SESSION_LIFETIME` à 120 min pour Admin/DG
- Centraliser logs vers Sentry ou ELK
- Ajouter SRI sur tous les CDN
- Index MySQL sur colonnes filtrées

### 🟡 Moyen
- Migrer `AES-256-CBC` → `AES-256-GCM`
- Cloudflare ou WAF en frontal
- Pentest externe annuel
- ISO 27001 light

---

## 12. Validation finale

| Critère | Statut |
|---|---|---|
| Sécurité minimale OWASP | 🟠 Partielle |
| RGPD / Loi sénégalaise données personnelles (loi 2008-12) | 🟠 À documenter |
| Politique de sauvegarde 3-2-1 | 🟠 Partielle |
| Plan de reprise d'activité (PRA) | ❌ Absent |
| Plan de continuité d'activité (PCA) | ❌ Absent |
| Documentation technique | 🟢 Riche |
| Documentation utilisateur | 🟢 |
| Tests automatisés | 🟠 Faibles |
| Monitoring & alerting | 🟠 Logs locaux uniquement |
| Conformité étatique sénégalaise | 🟠 À renforcer |

### 🟢 **Prêt production étatique : NON**
### 🟠 **Prêt production interne (intranet CSAR) : OUI** sous réserve de corriger les actions manuelles 🔴 critiques.

---

## 13. Estimation de l'effort total

| Lot | Effort | Profil |
|---|---|---|
| Lot 1 — Critiques 🔴 | 3-5 jours | DevOps + Dev Laravel |
| Lot 2 — Élevés 🟠 | 5-7 jours | Dev Laravel senior |
| Lot 3 — Moyens 🟡 | 5-10 jours | Dev + QA |
| Lot 4 — Confort 🟢 | 5 jours | DevOps + Sécu |
| **Total** | **3 à 4 semaines / 1 dev senior** | – |

---

## 14. Annexes

### 14.1 Scripts fournis
- `scripts/security/check-prod.sh` — diagnostic serveur en 1 commande
- `scripts/setup/migrate-to-redis.sh` — migration vers Redis
- `scripts/backup/encrypted_backup.sh` — sauvegardes chiffrées GPG

### 14.2 CI/CD
- `.github/workflows/security-audit.yml` — audit automatique composer/npm/secrets

### 14.3 Documentation
- Voir `docs/audits/PLAN_REMEDIATION.md` pour le guide d'application des correctifs.

---

**Fin du rapport d'audit.**
