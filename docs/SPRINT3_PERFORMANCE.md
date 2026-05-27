# Sprint 3 — Performance & UX

## Vue d'ensemble

| Levier | Gain attendu | Effort | Statut |
|--------|--------------|--------|--------|
| Cache Redis (sessions + cache) | -200 ms / requête | Faible | Code OK / serveur à activer |
| Middleware HTTP cache | Bande passante -60% | Faible | Code livré |
| Lazy-loading images | LCP -1 à 2 s sur mobile | Faible | Directive `@lazyImage` livrée |
| CDN Cloudflare | TTFB -300 ms (international) | Moyen | À configurer |
| Compression Brotli/GZIP | Taille payload -70% | Faible | À activer côté nginx |

---

## 1. Activer Redis (cache + sessions)

### Installation Redis sur le serveur

```bash
sudo apt update
sudo apt install -y redis-server php8.2-redis
sudo systemctl enable --now redis-server
redis-cli ping   # doit répondre "PONG"
```

### Configurer Laravel

Modifier `/var/www/csar/.env` :

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
REDIS_CACHE_DB=1
```

Puis :

```bash
cd /var/www/csar
php artisan config:cache
php artisan cache:clear
php artisan queue:restart
```

### Vérifier

```bash
php artisan tinker --execute="cache()->put('test', 'ok', 60); echo cache('test');"
# Doit afficher "ok"
redis-cli KEYS '*'
```

**Gain mesuré** : ~150-300 ms par requête authentifiée (passage MySQL → Redis pour les sessions).

---

## 2. Cache HTTP (middleware `http-cache`)

Un middleware `http-cache` est livré dans `app/Http/Middleware/HttpCache.php`.

### Usage

Ajouter le middleware sur les routes publiques GET avec un TTL en secondes :

```php
Route::get('/missions', [GalleryController::class, 'missions'])
    ->middleware('http-cache:600')  // cache 10 minutes
    ->name('missions_static');

Route::get('/actualites', [NewsController::class, 'index'])
    ->middleware('http-cache:300')  // cache 5 minutes
    ->name('news.index');
```

### Comportement

- Active **uniquement** sur GET/HEAD non-authentifiés et réponses 2xx
- Headers ajoutés : `Cache-Control: public, max-age=N`, `ETag`
- Retourne `304 Not Modified` si l'ETag correspond
- **N'affecte aucune route admin** (l'utilisateur est authentifié)

---

## 3. Lazy-loading des images

### Directive Blade `@lazyImage`

Remplacer dans les vues Blade :

```blade
<img src="{{ asset('images/foo.jpg') }}" alt="Foo" class="w-full">
```

Par :

```blade
@lazyImage(asset('images/foo.jpg'), 'Foo', ['class' => 'w-full', 'width' => 800, 'height' => 600])
```

Génère automatiquement :

```html
<img src="..." alt="Foo" loading="lazy" decoding="async" class="w-full" width="800" height="600">
```

### Bénéfices

- Images hors viewport ne sont chargées qu'au scroll
- `decoding="async"` libère le thread principal
- `width`/`height` évite le Cumulative Layout Shift (CLS)

### Application progressive

Cibler en priorité les pages avec beaucoup d'images :

```bash
# Pages prioritaires
resources/views/public/home.blade.php
resources/views/public/missions.blade.php
resources/views/public/galerie.blade.php
resources/views/public/actualites.blade.php
```

---

## 4. Compression Brotli/GZIP (nginx)

Éditer `/etc/nginx/sites-available/csar.sn` :

```nginx
server {
    # ... config existante ...

    # GZIP
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        application/javascript
        application/json
        application/xml
        text/css
        text/plain
        text/xml
        image/svg+xml;

    # Cache statique
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### Brotli (optionnel, plus efficace que GZIP)

```bash
sudo apt install -y nginx-module-brotli
# Ajouter dans nginx.conf :
#   load_module modules/ngx_http_brotli_filter_module.so;
#   load_module modules/ngx_http_brotli_static_module.so;
# Puis dans le server block :
#   brotli on;
#   brotli_comp_level 6;
#   brotli_types text/css application/javascript image/svg+xml;
```

---

## 5. CDN Cloudflare (optionnel mais fortement recommandé)

### Étapes

1. Créer un compte gratuit sur https://cloudflare.com
2. Ajouter le domaine `csar.sn`
3. Cloudflare scanne les enregistrements DNS existants — vérifier qu'ils sont corrects
4. Changer les **nameservers** chez votre registrar (.SN) vers ceux fournis par Cloudflare
5. Attendre la propagation DNS (1 à 24 h)

### Config Cloudflare recommandée

| Paramètre | Valeur |
|-----------|--------|
| SSL/TLS | Full (strict) |
| Auto Minify | HTML, CSS, JS |
| Brotli | On |
| Rocket Loader | Off (peut casser certains scripts) |
| Browser Cache TTL | 1 month |
| Always Online | On |
| Bot Fight Mode | On |
| DDoS Protection | Standard (gratuit) |

### Page Rules suggérées

```
csar.sn/admin/*      → Cache Level: Bypass (pas de cache pour admin)
csar.sn/api/*        → Cache Level: Bypass
csar.sn/storage/*    → Cache Level: Standard, Edge Cache TTL: 1 month
*csar.sn/*.css       → Cache Everything, 1 month
*csar.sn/*.js        → Cache Everything, 1 month
```

---

## 6. Mesure des performances

### Avant/après — Outils gratuits

```
https://pagespeed.web.dev/?url=https%3A%2F%2Fcsar.sn
https://gtmetrix.com/
https://webpagetest.org/
```

### Métriques cibles (Core Web Vitals)

| Métrique | Cible | Acceptable | Mauvais |
|----------|-------|------------|---------|
| LCP (Largest Contentful Paint) | <2,5 s | 2,5-4 s | >4 s |
| FID (First Input Delay) | <100 ms | 100-300 ms | >300 ms |
| CLS (Cumulative Layout Shift) | <0,1 | 0,1-0,25 | >0,25 |
| TTFB (Time to First Byte) | <800 ms | 800-1800 ms | >1800 ms |

---

## Checklist d'activation

- [ ] Installer Redis sur le serveur (§1)
- [ ] Modifier `.env` pour utiliser Redis (§1)
- [ ] Tester `cache()` en tinker (§1)
- [ ] Activer GZIP nginx (§4)
- [ ] Activer cache statique nginx pour `*.jpg/png/css/js` (§4)
- [ ] Ajouter `http-cache:600` sur `/missions` et `/actualites` (§2)
- [ ] Remplacer `<img>` par `@lazyImage` sur home/missions/galerie (§3)
- [ ] (Optionnel) Configurer Cloudflare (§5)
- [ ] Mesurer avec PageSpeed avant/après (§6)
