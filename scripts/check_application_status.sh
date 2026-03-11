#!/bin/bash

# Script pour vérifier le statut de l'application
# Usage: bash scripts/check_application_status.sh

cd /var/www/csar || exit 1

echo "🔍 Vérification du statut de l'application..."
echo "=========================================="
echo ""

echo "1️⃣  Vérification des permissions..."
ls -la storage/logs/ | head -5
ls -la bootstrap/cache/ | head -5
echo ""

echo "2️⃣  Vérification des routes (sans cache)..."
php artisan route:list | grep -E "home|reports|messages" | head -10
echo ""

echo "3️⃣  Test de la connexion à la base de données..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Connexion OK'; } catch(Exception \$e) { echo '❌ Erreur: ' . \$e->getMessage(); }"
echo ""

echo "4️⃣  Dernières erreurs dans les logs..."
tail -20 storage/logs/laravel.log 2>/dev/null || echo "Aucun log trouvé"
echo ""

echo "5️⃣  Test de la route home..."
php artisan route:list | grep "home" | head -5
echo ""

echo "✅ Vérification terminée !"




