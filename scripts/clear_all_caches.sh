#!/bin/bash

# Script pour vider tous les caches Laravel
# Usage: bash scripts/clear_all_caches.sh

cd /var/www/csar || exit 1

echo "🧹 Nettoyage de tous les caches Laravel..."
echo "=========================================="
echo ""

# Vider tous les caches
echo "1️⃣  Vidage des caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

echo ""
echo "2️⃣  Vérification des routes..."
php artisan route:list | grep -E "partners|gallery|map" | head -10

echo ""
echo "3️⃣  Mise en cache..."
php artisan config:cache
php artisan view:cache

# Ne pas mettre en cache les routes si elles ont des erreurs
echo ""
echo "4️⃣  Test du cache des routes..."
if php artisan route:cache 2>&1 | grep -q "Unable to prepare route"; then
    echo "❌ Erreur détectée dans les routes. Le cache des routes n'a pas été créé."
    echo "⚠️  Vérifiez les logs pour plus de détails."
else
    echo "✅ Cache des routes créé avec succès !"
fi

echo ""
echo "✅ Nettoyage terminé !"

