#!/bin/bash

# Script pour corriger l'erreur 413 Request Entity Too Large
# Usage: bash scripts/fix_upload_limits.sh

echo "🔧 Correction des limites d'upload pour Nginx et PHP..."
echo "======================================================"
echo ""

# Configuration Nginx
echo "📝 Configuration de Nginx..."
NGINX_CONFIG="/etc/nginx/sites-available/csar"

if [ -f "$NGINX_CONFIG" ]; then
    # Vérifier si client_max_body_size existe déjà
    if grep -q "client_max_body_size" "$NGINX_CONFIG"; then
        echo "   ⚠️  client_max_body_size existe déjà, mise à jour..."
        sed -i 's/client_max_body_size.*/client_max_body_size 100M;/' "$NGINX_CONFIG"
    else
        echo "   ➕ Ajout de client_max_body_size..."
        # Ajouter après la ligne server_name ou root
        sed -i '/server_name/a\    client_max_body_size 100M;' "$NGINX_CONFIG"
    fi
    
    echo "   ✅ Configuration Nginx mise à jour"
else
    echo "   ❌ Fichier de configuration Nginx non trouvé: $NGINX_CONFIG"
    exit 1
fi

# Configuration PHP-FPM
echo ""
echo "📝 Configuration de PHP-FPM..."
PHP_INI="/etc/php/8.2/fpm/php.ini"

if [ -f "$PHP_INI" ]; then
    # Mettre à jour upload_max_filesize
    if grep -q "^upload_max_filesize" "$PHP_INI"; then
        sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
    else
        echo "upload_max_filesize = 100M" >> "$PHP_INI"
    fi
    
    # Mettre à jour post_max_size
    if grep -q "^post_max_size" "$PHP_INI"; then
        sed -i 's/^post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
    else
        echo "post_max_size = 100M" >> "$PHP_INI"
    fi
    
    # Mettre à jour memory_limit si nécessaire
    if grep -q "^memory_limit" "$PHP_INI"; then
        sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    fi
    
    # Mettre à jour max_execution_time
    if grep -q "^max_execution_time" "$PHP_INI"; then
        sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    fi
    
    echo "   ✅ Configuration PHP mise à jour"
else
    echo "   ❌ Fichier php.ini non trouvé: $PHP_INI"
    exit 1
fi

# Vérifier la configuration Nginx
echo ""
echo "🔍 Vérification de la configuration Nginx..."
if nginx -t; then
    echo "   ✅ Configuration Nginx valide"
else
    echo "   ❌ Erreur dans la configuration Nginx"
    exit 1
fi

# Recharger les services
echo ""
echo "🔄 Rechargement des services..."
systemctl reload nginx
systemctl restart php8.2-fpm

echo ""
echo "✅ Correction terminée !"
echo ""
echo "📋 Limites configurées :"
echo "   - Nginx client_max_body_size: 100M"
echo "   - PHP upload_max_filesize: 100M"
echo "   - PHP post_max_size: 100M"
echo "   - PHP memory_limit: 256M"
echo "   - PHP max_execution_time: 300s"
echo ""
echo "💡 Vous pouvez maintenant publier des actualités avec des fichiers jusqu'à 100MB"

