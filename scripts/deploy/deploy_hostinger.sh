#!/bin/bash

# Script de déploiement pour Hostinger
# Usage: ./deploy_hostinger.sh

set -e

echo "🚀 Déploiement de la Plateforme CSAR sur Hostinger"
echo "=================================================="
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifications
echo "📋 Vérification des prérequis..."

if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP n'est pas installé${NC}"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo -e "${RED}❌ Composer n'est pas installé${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Prérequis vérifiés${NC}"
echo ""

# Installation des dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f "package.json" ]; then
    if command -v npm &> /dev/null; then
        echo "📦 Installation des dépendances NPM..."
        npm install
        npm run build
    else
        echo -e "${YELLOW}⚠️  NPM n'est pas installé, skip des assets${NC}"
    fi
fi

echo -e "${GREEN}✅ Dépendances installées${NC}"
echo ""

# Configuration .env
if [ ! -f ".env" ]; then
    echo "⚙️  Création du fichier .env..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${YELLOW}⚠️  Veuillez configurer le fichier .env avec vos informations${NC}"
    else
        echo -e "${RED}❌ Fichier .env.example introuvable${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✅ Fichier .env existe déjà${NC}"
fi

# Génération de la clé
echo "🔑 Génération de la clé d'application..."
php artisan key:generate --force

# Migrations
echo "🗄️  Exécution des migrations..."
read -p "Exécuter les migrations ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo -e "${GREEN}✅ Migrations exécutées${NC}"
else
    echo -e "${YELLOW}⚠️  Migrations ignorées${NC}"
fi

# Lien de stockage
echo "🔗 Création du lien de stockage..."
php artisan storage:link

# Optimisation
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✅ Optimisation terminée${NC}"
echo ""

# Permissions
echo "🔐 Configuration des permissions..."
if [ -d "storage" ]; then
    chmod -R 775 storage
    echo -e "${GREEN}✅ Permissions storage configurées${NC}"
fi

if [ -d "bootstrap/cache" ]; then
    chmod -R 775 bootstrap/cache
    echo -e "${GREEN}✅ Permissions cache configurées${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Déploiement terminé avec succès !${NC}"
echo ""
echo "📝 Prochaines étapes :"
echo "   1. Vérifiez la configuration dans .env"
echo "   2. Testez l'application dans votre navigateur"
echo "   3. Configurez SSL/HTTPS si nécessaire"
echo ""

