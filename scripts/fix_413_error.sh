#!/bin/bash

# Script pour corriger l'erreur 413 Request Entity Too Large
# Usage: bash scripts/fix_413_error.sh

echo "🔧 Correction de l'erreur 413 Request Entity Too Large..."
echo "=========================================================="
echo ""

# Configuration Nginx
NGINX_CONFIG="/etc/nginx/sites-available/csar"

if [ -f "$NGINX_CONFIG" ]; then
    echo "📝 Configuration de Nginx..."
    
    # Vérifier si client_max_body_size existe déjà
    if grep -q "client_max_body_size" "$NGINX_CONFIG"; then
        echo "   ⚠️  client_max_body_size existe déjà, mise à jour à 100M..."
        sed -i 's/client_max_body_size.*/client_max_body_size 100M;/' "$NGINX_CONFIG"
    else
        echo "   ➕ Ajout de client_max_body_size 100M..."
        # Ajouter après server_name ou au début du bloc server
        sed -i '/server_name/a\    client_max_body_size 100M;' "$NGINX_CONFIG"
    fi
    
    echo "   ✅ Configuration Nginx mise à jour"
    
    # Tester la configuration Nginx
    echo "   🧪 Test de la configuration Nginx..."
    if nginx -t; then
        echo "   ✅ Configuration Nginx valide"
        systemctl reload nginx
        echo "   ✅ Nginx rechargé"
    else
        echo "   ❌ Erreur dans la configuration Nginx"
        exit 1
    fi
else
    echo "   ⚠️  Fichier de configuration Nginx non trouvé: $NGINX_CONFIG"
fi

echo ""

# Configuration PHP-FPM
PHP_INI="/etc/php/8.2/fpm/php.ini"

if [ -f "$PHP_INI" ]; then
    echo "📝 Configuration de PHP-FPM..."
    
    # Mettre à jour upload_max_filesize
    if grep -q "^upload_max_filesize" "$PHP_INI"; then
        sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
        echo "   ✅ upload_max_filesize mis à jour à 100M"
    else
        echo "upload_max_filesize = 100M" >> "$PHP_INI"
        echo "   ✅ upload_max_filesize ajouté (100M)"
    fi
    
    # Mettre à jour post_max_size (doit être >= upload_max_filesize)
    if grep -q "^post_max_size" "$PHP_INI"; then
        sed -i 's/^post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
        echo "   ✅ post_max_size mis à jour à 100M"
    else
        echo "post_max_size = 100M" >> "$PHP_INI"
        echo "   ✅ post_max_size ajouté (100M)"
    fi
    
    # Mettre à jour memory_limit si nécessaire
    if grep -q "^memory_limit" "$PHP_INI"; then
        sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
        echo "   ✅ memory_limit mis à jour à 256M"
    fi
    
    # Mettre à jour max_execution_time pour les gros uploads
    if grep -q "^max_execution_time" "$PHP_INI"; then
        sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
        echo "   ✅ max_execution_time mis à jour à 300s"
    fi
    
    # Redémarrer PHP-FPM
    echo "   🔄 Redémarrage de PHP-FPM..."
    systemctl restart php8.2-fpm
    echo "   ✅ PHP-FPM redémarré"
else
    echo "   ⚠️  Fichier php.ini non trouvé: $PHP_INI"
fi

echo ""
echo "✅ Correction terminée !"
echo ""
echo "📋 Résumé des modifications :"
echo "   - Nginx client_max_body_size: 100M"
echo "   - PHP upload_max_filesize: 100M"
echo "   - PHP post_max_size: 100M"
echo "   - PHP memory_limit: 256M"
echo "   - PHP max_execution_time: 300s"
echo ""
echo "💡 Vous pouvez maintenant uploader des fichiers jusqu'à 50MB"
echo "   (avec une marge de sécurité de 100MB configurée)"

