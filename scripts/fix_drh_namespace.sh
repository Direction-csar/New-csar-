#!/bin/bash

# Script pour corriger le problème de casse du dossier DRH
# Le dossier s'appelle "Drh" mais le namespace est "DRH"
# Sur Linux, il faut que le dossier corresponde au namespace

cd /var/www/csar || exit 1

echo "🔧 Correction du problème de casse DRH..."

# Vérifier si le dossier Drh existe
if [ -d "app/Http/Controllers/Drh" ]; then
    echo "📁 Dossier Drh trouvé"
    
    # Renommer le dossier en DRH
    if [ ! -d "app/Http/Controllers/DRH" ]; then
        mv "app/Http/Controllers/Drh" "app/Http/Controllers/DRH"
        echo "✅ Dossier renommé de Drh en DRH"
    else
        echo "⚠️  Le dossier DRH existe déjà"
        # Copier les fichiers si nécessaire
        if [ -d "app/Http/Controllers/Drh" ]; then
            cp -r app/Http/Controllers/Drh/* app/Http/Controllers/DRH/
            rm -rf app/Http/Controllers/Drh
            echo "✅ Fichiers copiés et ancien dossier supprimé"
        fi
    fi
else
    echo "✅ Le dossier DRH existe déjà ou n'a pas besoin d'être renommé"
fi

# Vérifier PersonnelController qui utilise le mauvais namespace
if [ -f "app/Http/Controllers/DRH/PersonnelController.php" ]; then
    echo "🔧 Correction du namespace dans PersonnelController.php..."
    sed -i 's/namespace App\\Http\\Controllers\\Drh;/namespace App\\Http\\Controllers\\DRH;/' app/Http/Controllers/DRH/PersonnelController.php
    echo "✅ Namespace corrigé"
fi

# Vider le cache de l'autoloader
echo "🔄 Régénération de l'autoloader..."
composer dump-autoload

echo "✅ Correction terminée !"

