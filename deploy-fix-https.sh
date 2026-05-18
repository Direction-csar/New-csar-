#!/bin/bash
# Correctif HTTPS / mixed-content (TinyMCE upload + images storage)
set -e
APP=/var/www/csar
cd "$APP"

echo "=== 1. AppServiceProvider : forcer HTTPS en production ==="
sudo tee app/Providers/AppServiceProvider.php > /dev/null << 'PHPEOF'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.csar');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
PHPEOF
echo "✅ AppServiceProvider mis à jour"

echo "=== 2. TrustProxies : faire confiance à nginx ==="
sudo sed -i "s|protected \$proxies;|protected \$proxies = '*';|" app/Http/Middleware/TrustProxies.php
grep "protected \$proxies" app/Http/Middleware/TrustProxies.php
echo "✅ TrustProxies = '*'"

echo "=== 3. .env : vérifier APP_ENV + APP_URL ==="
sudo sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sudo sed -i "s|^APP_URL=.*|APP_URL=https://csar.sn|" .env
grep -E "^APP_(ENV|URL)=" .env
echo "✅ .env corrigé"

echo "=== 4. nginx : retirer /storage du deny + ajouter fastcgi HTTPS ==="
NGINX_CONF=/etc/nginx/sites-available/csar
if [ ! -f "$NGINX_CONF" ]; then
    echo "⚠ $NGINX_CONF introuvable, recherche..."
    NGINX_CONF=$(sudo find /etc/nginx -name "csar*" -type f | head -1)
    echo "Trouvé : $NGINX_CONF"
fi

# Backup
sudo cp "$NGINX_CONF" "${NGINX_CONF}.bak-$(date +%s)"

# Modifier nginx via Python (plus fiable)
sudo python3 - "$NGINX_CONF" << 'PYEOF'
import sys, re
path = sys.argv[1]
content = open(path).read()
orig = content

# 1) Retirer 'storage|' de la liste deny all
content = content.replace(
    '(storage|database|bootstrap/cache|vendor|node_modules)',
    '(database|bootstrap/cache|vendor|node_modules)'
)

# 2) Ajouter fastcgi_param HTTPS on; après include fastcgi_params; si absent
if 'fastcgi_param HTTPS on' not in content:
    content = content.replace(
        'include fastcgi_params;',
        'include fastcgi_params;\n\n        # HTTPS transmis a Laravel (fix mixed-content)\n        fastcgi_param HTTPS on;\n        fastcgi_param HTTP_X_FORWARDED_PROTO https;',
        1
    )

if content != orig:
    open(path, 'w').write(content)
    print('OK nginx modifie')
else:
    print('Aucun changement nginx (deja a jour)')
PYEOF

# Test config nginx
echo "Test config nginx :"
sudo nginx -t

echo "=== 5. Symlink storage ==="
sudo php artisan storage:link || true
ls -la public/storage 2>&1 | head -2

echo "=== 6. Permissions ==="
sudo chown -R www-data:www-data app/Providers/AppServiceProvider.php app/Http/Middleware/TrustProxies.php .env
sudo chmod -R 755 storage public/storage 2>&1 | head -3
sudo chown -R www-data:www-data storage

echo "=== 7. Caches Laravel ==="
sudo php artisan config:clear
sudo php artisan route:clear
sudo php artisan view:clear
sudo php artisan config:cache
sudo php artisan route:cache

echo "=== 8. Recharger nginx + PHP-FPM ==="
sudo systemctl reload nginx
sudo systemctl reload php8.5-fpm 2>/dev/null || sudo systemctl reload php8.4-fpm 2>/dev/null || sudo systemctl reload php8.3-fpm 2>/dev/null || sudo systemctl reload php-fpm 2>/dev/null || echo "PHP-FPM non rechargé (à faire manuellement)"

echo ""
echo "==========================================="
echo "✅ CORRECTIFS HTTPS APPLIQUÉS !"
echo ""
echo "👉 Test : https://csar.sn/ctc/actualites/create"
echo "    - Upload image TinyMCE doit fonctionner"
echo "    - Images /storage/news/... doivent s'afficher"
echo "==========================================="
