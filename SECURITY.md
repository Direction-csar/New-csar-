# Politique de sécurité — Plateforme CSAR

> Référentiel : SENUM DCyber — Guide synthétique de sécurisation Web (mai 2026)

## 1. Architecture sécurité

### 1.1 En-têtes HTTP de sécurité

Implémentés via `App\Http\Middleware\SecurityHeaders` :

| En-tête | Valeur | But |
|---|---|---|
| `Content-Security-Policy` | nonce dynamique + sources whitelistées | Anti-XSS |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | Force HTTPS |
| `X-Frame-Options` | `DENY` | Anti-clickjacking |
| `X-Content-Type-Options` | `nosniff` | Bloque MIME sniffing |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limite la fuite de Referer |
| `Permissions-Policy` | restrictif (pas de caméra/micro/USB…) | Limite APIs navigateur |
| `Cross-Origin-Opener-Policy` | `same-origin` | Isolation cross-origin |
| `Cross-Origin-Resource-Policy` | `same-origin` | Limite cross-origin reads |

Les violations CSP sont collectées sur l'endpoint **`POST /csp-violations`** et journalisées dans `storage/logs/security-*.log` (canal `security`, rétention 90 jours).

### 1.2 CORS

Configuré dans `config/cors.php` :
- Origines explicites uniquement (`https://csar.sn`, sous-domaines via regex)
- Pas de wildcard `*`
- `supports_credentials = true`
- En-têtes autorisés restreints

### 1.3 Authentification

- **Multi-guards** : admin, dg, drh, ctc, supervisor, collector
- **Rate limiting** : 5 tentatives / IP / 5 min sur les routes login
- **Verrouillage** : compte désactivable via flag `is_active`
- **Multi-session** : `MultiSessionMiddleware` empêche la concurrence de sessions
- **CSRF** : actif sur toutes les routes web (`VerifyCsrfToken`)
- **Forçage HTTPS** : `URL::forceScheme('https')` en production

### 1.4 Mots de passe

Règle `App\Rules\StrongPassword` :
- Minimum 12 caractères
- Majuscule + minuscule + chiffre + caractère spécial
- Blacklist des mots communs

### 1.5 Uploads

Règle `App\Rules\SecureFileUpload` :
- Validation du MIME réel via `finfo` (pas l'extension)
- Blacklist des exécutables (`.php`, `.exe`, `.sh`, `.js`…)
- Limite de taille configurable

## 2. Infrastructure

### 2.1 Reverse proxy

- **Cloudflare Tunnel** (`cloudflared`) → nginx local
- `TrustProxies` à `*` pour respecter les en-têtes `X-Forwarded-*`
- Nginx transmet `HTTPS=on` et `X-Forwarded-Proto=https` à PHP-FPM

### 2.2 Sauvegardes

- Script : `scripts/backup.sh`
- Cron quotidien recommandé : `0 2 * * * /var/www/csar/scripts/backup.sh`
- Rétention : 14 jours
- Cibles : base MySQL + `storage/app/public`

### 2.3 Optimisation production

- Script : `scripts/deploy-optimize.sh`
- Caches : config, routes, vues
- Permissions strictes (`www-data:www-data`)

## 3. Procédures

### 3.1 Signaler une vulnérabilité

Email : security@csar.sn (à configurer)

### 3.2 Rotation des secrets

- `APP_KEY` : ne JAMAIS changer après mise en production (invaliderait les sessions/cookies)
- Clés API tierces (Brevo, Cloudflare) : tous les 6 mois minimum

### 3.3 Mises à jour

Mensuellement :
```bash
sudo apt update && sudo apt upgrade -y
sudo -u www-data composer audit
sudo -u www-data composer update --no-dev
```

## 4. Conformité

| Référence | Statut |
|---|---|
| OWASP Top 10 (A01-A10) | ✅ Couvert |
| ANSSI — Recommandations mots de passe | ✅ |
| SENUM DCyber — CSP | ✅ |
| SENUM DCyber — CORS | ✅ |
| RGPD — droit à l'oubli | ⏳ À documenter |

## 5. Checklist déploiement production

- [ ] `APP_ENV=production` dans `.env`
- [ ] `APP_DEBUG=false` dans `.env`
- [ ] `APP_URL=https://csar.sn`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Permissions `storage/` et `bootstrap/cache/` à `775`
- [ ] Cron de sauvegarde actif
- [ ] Tailscale activé au boot (`systemctl enable tailscaled`)
- [ ] Cloudflare Tunnel actif (`systemctl status cloudflared`)
- [ ] HSTS testé sur https://hstspreload.org
- [ ] Headers vérifiés sur https://securityheaders.com
- [ ] CSP testé via console navigateur (aucune violation)
