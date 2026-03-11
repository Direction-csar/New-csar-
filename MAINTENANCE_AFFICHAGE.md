# Afficher la page « Site en maintenance » en ligne

La page de maintenance (logo CSAR, message, fond aux couleurs du drapeau, animations) peut être affichée de deux manières.

## 1. Avec Laravel (recommandé en production)

Quand le site tourne sous Laravel, activez le mode maintenance pour que **toutes** les URLs affichent cette page :

```bash
php artisan down --render="errors.503"
```

Pour désactiver la maintenance et revenir au site normal :

```bash
php artisan up
```

La vue utilisée est `resources/views/errors/503.blade.php` (logo CSAR, texte, fond drapeau, barre de chargement et points animés).

## 2. Fichier HTML statique (sans Laravel)

Le fichier **`public/maintenance.html`** est une version autonome (même design, même animation). Utile si vous hébergez uniquement une page statique ou si vous redirigez tout le trafic vers cette page.

### Option A : Redirection Apache (.htaccess)

À la racine du site (ou dans `public/` selon la config), vous pouvez rediriger toutes les requêtes vers la page de maintenance :

```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/maintenance\.html$
RewriteCond %{REQUEST_URI} !^/images/
RewriteRule ^(.*)$ /maintenance.html [R=302,L]
```

Ensuite, ouvrez par exemple : `https://votredomaine.com/maintenance.html`

### Option B : Remplacer la page d’accueil

- Renommez temporairement votre `index.php` (Laravel).
- Copiez `maintenance.html` en `index.html` à la racine de `public/` (ou à la racine du site si le document root pointe dessus).
- Pour le logo, le chemin `images/csar-logo.svg` doit être valide depuis l’URL de la page (par exemple `public/images/csar-logo.svg` si la page est dans `public/`).

### Option C : Nginx

Exemple pour afficher uniquement la page de maintenance :

```nginx
location / {
    return 302 /maintenance.html;
}
location = /maintenance.html {
    root /chemin/vers/votre/site/public;
}
```

---

Résumé : en production Laravel, utilisez `php artisan down --render="errors.503"` puis `php artisan up` pour reprendre. Pour un affichage statique ou une redirection manuelle, utilisez `public/maintenance.html` et l’une des options ci-dessus.
