#!/bin/bash

# ============================================
# SCRIPT COMPLET DE CORRECTION .htaccess
# ============================================
# Ce script corrige le fichier .htaccess sur le serveur
# ============================================

echo "=========================================="
echo "CORRECTION DU FICHIER .htaccess"
echo "=========================================="
echo ""

# 1. Aller dans le répertoire du projet
echo "1. Navigation vers le répertoire du projet..."
cd /var/www/csar || exit 1
echo "✅ Répertoire: $(pwd)"
echo ""

# 2. Sauvegarder le fichier .htaccess actuel
echo "2. Sauvegarde du fichier .htaccess actuel..."
sudo cp public/.htaccess public/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Sauvegarde créée"
echo ""

# 3. Corriger le fichier .htaccess
echo "3. Correction du fichier .htaccess..."
sudo tee public/.htaccess > /dev/null << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Configuration pour les uploads de fichiers (commentées car PHP-FPM)
#php_value upload_max_filesize 50M
#php_value post_max_size 60M
#php_value memory_limit 256M
#php_value max_execution_time 300
#php_value max_input_time 300

LimitRequestBody 62914560

# Sécurité
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Cache pour les performances
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
EOF

echo "✅ Fichier .htaccess corrigé"
echo ""

# 4. Vérifier les permissions
echo "4. Vérification des permissions..."
sudo chown www-data:www-data public/.htaccess
sudo chmod 644 public/.htaccess
echo "✅ Permissions configurées"
echo ""

# 5. Tester la configuration Apache
echo "5. Test de la configuration Apache..."
if sudo apache2ctl configtest; then
    echo "✅ Configuration Apache valide"
else
    echo "❌ Erreur dans la configuration Apache"
    exit 1
fi
echo ""

# 6. Redémarrer Apache
echo "6. Redémarrage d'Apache..."
sudo systemctl restart apache2
sleep 2
if systemctl is-active --quiet apache2; then
    echo "✅ Apache redémarré avec succès"
else
    echo "❌ Erreur lors du redémarrage d'Apache"
    exit 1
fi
echo ""

# 7. Vérifier qu'il n'y a plus d'erreurs
echo "7. Vérification des erreurs..."
ERRORS=$(sudo tail -20 /var/log/apache2/error.log | grep -i "php_value" | wc -l)
if [ "$ERRORS" -eq 0 ]; then
    echo "✅ Aucune erreur php_value trouvée"
else
    echo "⚠️ $ERRORS erreur(s) php_value encore présente(s)"
fi
echo ""

# 8. Tester le site
echo "8. Test du site..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://csar.sn/fr)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Site accessible (HTTP $HTTP_CODE)"
else
    echo "⚠️ Site retourne HTTP $HTTP_CODE"
fi
echo ""

# 9. Vérifier les logs d'accès
echo "9. Dernières requêtes dans les logs..."
sudo tail -3 /var/log/apache2/csar-access.log 2>/dev/null || sudo tail -3 /var/log/apache2/access.log
echo ""

echo "=========================================="
echo "CORRECTION TERMINÉE"
echo "=========================================="
echo ""
echo "Vérifiez le site : https://csar.sn/fr"
echo ""







