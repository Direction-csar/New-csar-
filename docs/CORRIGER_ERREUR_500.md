# 🔧 Correction de l'Erreur 500

## Commandes à exécuter sur le serveur pour diagnostiquer

```bash
# 1. Aller dans le répertoire du projet
cd /var/www/csar

# 2. Vérifier les logs Laravel
tail -50 storage/logs/laravel.log

# 3. Vérifier les logs Apache
tail -50 /var/log/apache2/error.log

# 4. Vérifier les permissions
ls -la storage/logs/
ls -la bootstrap/cache/

# 5. Vérifier les permissions et les corriger si nécessaire
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 6. Vérifier la syntaxe PHP des fichiers modifiés
php -l resources/views/public/about/index.blade.php
php -l resources/views/public/contact.blade.php

# 7. Vider tous les caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```







