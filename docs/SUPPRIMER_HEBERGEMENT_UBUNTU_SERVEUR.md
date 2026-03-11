# Supprimer l'hébergement CSAR sur le serveur Ubuntu

À exécuter sur votre serveur (ex. `root@srv1460145` — IP 147.93.85.131).

---

## Étape 0 : Vérifier ce qui est en place

Sur le serveur, exécutez :

```bash
# Serveur web (nginx ou apache)
systemctl is-active nginx 2>/dev/null && echo "Nginx actif" || true
systemctl is-active apache2 2>/dev/null && echo "Apache actif" || true

# Dossier du site
ls -la /var/www/

# Site Nginx activé ?
ls -la /etc/nginx/sites-enabled/ 2>/dev/null | head -20

# Base de données
mysql -u root -e "SHOW DATABASES;" 2>/dev/null || echo "MySQL nécessite un mot de passe (utilisez: mysql -u root -p)"
```

Cela permet de confirmer le chemin du site (souvent `/var/www/csar`) et le type de serveur web.

---

## Méthode 1 : Script automatique (recommandé)

### 1. Créer le script sur le serveur

Copiez-collez **tout le bloc** ci-dessous dans le terminal (en une fois), puis Entrée :

```bash
cat > /tmp/supprimer_csar.sh << 'FIN_SCRIPT'
#!/bin/bash
set -e
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

APP_DIR="/var/www/csar"
DOMAIN="www.csar.sn"
DB_NAME="csar_platform"
DB_USER="csar_user"

echo "=========================================="
echo "  SUPPRESSION DU SITE CSAR"
echo "=========================================="
echo ""
echo "  Cette action va supprimer:"
echo "  - Fichiers du site ($APP_DIR)"
echo "  - Configuration Nginx/Apache du site"
echo "  - Certificats SSL (si présents)"
echo "  - Base de données $DB_NAME et utilisateur $DB_USER"
echo ""
read -p "Tapez OUI pour confirmer: " confirmation
if [ "$confirmation" != "OUI" ]; then
    echo -e "${RED}Annulé.${NC}"
    exit 1
fi

# Détection serveur web
WEB_SERVER=""
systemctl is-active --quiet nginx 2>/dev/null && WEB_SERVER="nginx"
[ -z "$WEB_SERVER" ] && systemctl is-active --quiet apache2 2>/dev/null && WEB_SERVER="apache2"
echo -e "${YELLOW}Serveur web: $WEB_SERVER${NC}"

# 1. Désactiver le site
if [ "$WEB_SERVER" = "nginx" ]; then
    rm -f /etc/nginx/sites-enabled/csar
    rm -f /etc/nginx/sites-available/csar
    nginx -t 2>/dev/null && systemctl reload nginx
elif [ "$WEB_SERVER" = "apache2" ]; then
    a2dissite csar.conf 2>/dev/null || true
    rm -f /etc/apache2/sites-available/csar.conf
    systemctl reload apache2 2>/dev/null || true
fi

# 2. Certificats SSL
if command -v certbot &>/dev/null; then
    certbot delete --cert-name "$DOMAIN" --non-interactive 2>/dev/null || true
    certbot delete --cert-name "csar.sn" --non-interactive 2>/dev/null || true
fi

# 3. Fichiers du site
rm -rf "$APP_DIR"
echo -e "${GREEN}Fichiers supprimés: $APP_DIR${NC}"

# 4. Base de données (à faire manuellement si mot de passe demandé)
echo ""
read -p "Supprimer la base de données et l'utilisateur MySQL ? (O/n): " rep
if [ "$rep" = "O" ] || [ "$rep" = "o" ] || [ "$rep" = "" ]; then
    if mysql -u root -e "DROP DATABASE IF EXISTS $DB_NAME; DROP USER IF EXISTS '$DB_USER'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null; then
        echo -e "${GREEN}Base de données supprimée.${NC}"
    else
        echo -e "${YELLOW}Exécutez manuellement:${NC}"
        echo "  mysql -u root -p"
        echo "  DROP DATABASE IF EXISTS $DB_NAME;"
        echo "  DROP USER IF EXISTS '$DB_USER'@'localhost';"
        echo "  FLUSH PRIVILEGES;"
        echo "  EXIT;"
    fi
fi

echo ""
echo -e "${GREEN}=== SUPPRESSION TERMINÉE ===${NC}"
echo "Vérifiez: ls /var/www/csar  (doit afficher: No such file or directory)"
FIN_SCRIPT
chmod +x /tmp/supprimer_csar.sh
echo "Script créé: /tmp/supprimer_csar.sh"
```

### 2. Lancer le script

```bash
bash /tmp/supprimer_csar.sh
```

- Quand il demande la confirmation, tapez **OUI** puis Entrée.
- Pour supprimer aussi la base de données, répondez **O** (ou Entrée) à la question MySQL.

---

## Méthode 2 : Commandes manuelles

Si vous préférez tout faire à la main (adaptez si votre site n’est pas sous `/var/www/csar` ou si vous utilisez Apache) :

```bash
# 1. Désactiver le site (Nginx)
rm -f /etc/nginx/sites-enabled/csar
rm -f /etc/nginx/sites-available/csar
nginx -t && systemctl reload nginx

# 2. Certificats SSL (optionnel)
certbot delete --cert-name www.csar.sn --non-interactive 2>/dev/null || true
certbot delete --cert-name csar.sn --non-interactive 2>/dev/null || true

# 3. Supprimer les fichiers du site
rm -rf /var/www/csar

# 4. Base de données (entrez le mot de passe root MySQL si demandé)
mysql -u root -p -e "DROP DATABASE IF EXISTS csar_platform; DROP USER IF EXISTS 'csar_user'@'localhost'; FLUSH PRIVILEGES;"
```

---

## Vérification après suppression

```bash
ls -la /var/www/csar                    # Doit: No such file or directory
ls /etc/nginx/sites-enabled/csar 2>&1    # Doit: No such file or directory
mysql -u root -p -e "SHOW DATABASES;"   # Ne doit plus contenir csar_platform
```

---

## Si le site est ailleurs que /var/www/csar

Si lors de l’étape 0 vous voyez un autre chemin (par ex. `/var/www/html` ou `/home/...`), remplacez dans le script la variable `APP_DIR` par ce chemin, ou exécutez les commandes manuelles en adaptant ce chemin et les noms de site Nginx/Apache en conséquence.
