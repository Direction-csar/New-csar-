#!/bin/bash
# Isole le portail DRH : layout + routes dynamiques pour les vues personnel
set -e
APP=/var/www/csar
cd "$APP"

echo "=== 1. Modification des vues personnel (layout + routes conditionnels) ==="

for f in index show edit create; do
    file="resources/views/admin/personnel/$f.blade.php"
    if [ ! -f "$file" ]; then
        echo "⚠ $file introuvable"
        continue
    fi
    echo "→ $file"

    # 1) @extends layout conditionnel
    sudo sed -i "1s|@extends('layouts.admin')|@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.drh-portal')|" "$file"

    # 2) Ajouter @php $pPrefix juste après le premier @section('title'...)
    if ! sudo grep -q '$pPrefix' "$file"; then
        sudo sed -i "0,/^@section('title'.*$/s//&\n\n@php \$pPrefix = Auth::user()->role === 'admin' ? 'admin.personnel.' : 'admin.drh.personnel.'; @endphp/" "$file"
    fi

    # 3) Remplacer les route('admin.personnel.X') par route($pPrefix.'X')
    sudo sed -i "s/route('admin\.personnel\.\([a-zA-Z0-9_-]*\)'/route(\$pPrefix.'\1'/g" "$file"
    # Idem pour double-quotes (utilisé en JS)
    sudo sed -i "s/route(\"admin\.personnel\.\([a-zA-Z0-9_-]*\)\"/route(\$pPrefix.'\1'/g" "$file"
done
echo "✅ Vues personnel modifiées"

echo "=== 2. Permissions ==="
sudo chown -R www-data:www-data resources/views/admin/personnel

echo "=== 3. Cache ==="
sudo php artisan view:clear
sudo php artisan route:clear
sudo php artisan config:clear

echo "=== 4. Vérification ==="
echo ""
echo "Routes DRH disponibles :"
sudo php artisan route:list 2>/dev/null | grep "admin.drh" | head -20

echo ""
echo "==========================================="
echo "✅ ISOLATION DRH TERMINÉE !"
echo ""
echo "👉 Test : https://csar.sn/drh/login"
echo "    - Le menu reste visible sur toutes les pages personnel"
echo "    - Les liens 'Voir/Modifier/Supprimer' restent dans /admin/drh/personnel/..."
echo "==========================================="
