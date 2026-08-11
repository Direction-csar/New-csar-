#!/bin/bash
# =============================================================================
# CSAR — Vérification de la configuration .env en production
# À exécuter sur le VPS : bash verify-env.sh
# =============================================================================

set -e

ENV_FILE=".env"

if [ ! -f "$ENV_FILE" ]; then
    echo "❌ ERREUR: Fichier .env introuvable"
    exit 1
fi

echo "============================================"
echo "  CSAR — Vérification .env de production"
echo "============================================"
echo ""

ERRORS=0
WARNINGS=0

check() {
    local key=$1
    local expected=$2
    local severity=$3
    local value=$(grep "^${key}=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2-)

    if [ -z "$value" ] || [ "$value" = "" ]; then
        echo "❌ $key — MANQUANT"
        ERRORS=$((ERRORS + 1))
    elif [ -n "$expected" ] && [ "$value" != "$expected" ]; then
        echo "⚠️  $key='$value' (attendu: '$expected')"
        WARNINGS=$((WARNINGS + 1))
    else
        echo "✅ $key"
    fi
}

check_not_empty() {
    local key=$1
    local value=$(grep "^${key}=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2-)

    if [ -z "$value" ] || [ "$value" = "" ]; then
        echo "❌ $key — MANQUANT/VIDE"
        ERRORS=$((ERRORS + 1))
    else
        echo "✅ $key — configuré"
    fi
}

check_not_default() {
    local key=$1
    local bad_value=$2
    local value=$(grep "^${key}=" "$ENV_FILE" 2>/dev/null | cut -d'=' -f2-)

    if [ "$value" = "$bad_value" ]; then
        echo "❌ $key='$value' — VALEUR PAR DÉFAUT NON SÉCURISÉE"
        ERRORS=$((ERRORS + 1))
    else
        echo "✅ $key — OK"
    fi
}

echo "--- Sécurité applicative ---"
check "APP_ENV" "production"
check "APP_DEBUG" "false"
check_not_default "APP_KEY" ""

echo ""
echo "--- Base de données ---"
check_not_empty "DB_CONNECTION"
check_not_empty "DB_HOST"
check_not_empty "DB_DATABASE"
check_not_empty "DB_USERNAME"
check_not_default "DB_PASSWORD" ""

echo ""
echo "--- Sessions & Cache ---"
check "SESSION_ENCRYPT" "true"
check "SESSION_DRIVER" ""
check "CACHE_STORE" ""

echo ""
echo "--- Mail ---"
check_not_empty "MAIL_HOST"
check_not_empty "MAIL_FROM_ADDRESS"
check_not_empty "MAIL_ADMIN_EMAIL"

echo ""
echo "--- Paiement PayPal ---"
check_not_empty "PAYPAL_CLIENT_ID"
check_not_empty "PAYPAL_CLIENT_SECRET"
check_not_empty "PAYPAL_WEBHOOK_ID"
check "PAYPAL_MODE" "live"

echo ""
echo "--- Paiement PayDunya ---"
check_not_empty "PAYDUNYA_API_KEY"
check_not_empty "PAYDUNYA_PRIVATE_KEY"
check "PAYDUNYA_MODE" "live"

echo ""
echo "--- Google OAuth ---"
check_not_empty "GOOGLE_CLIENT_ID"
check_not_empty "GOOGLE_CLIENT_SECRET"

echo ""
echo "--- Sentry (optionnel) ---"
check_not_empty "SENTRY_LARAVEL_DSN"

echo ""
echo "============================================"
echo "  Résultat: $ERRORS erreur(s), $WARNINGS avertissement(s)"
echo "============================================"

if [ $ERRORS -gt 0 ]; then
    echo ""
    echo "❌ DES ERREURS CRITIQUES ONT ÉTÉ DÉTECTÉES"
    echo "   Corrigez-les avant de mettre en production."
    exit 1
else
    echo ""
    echo "✅ Configuration .env validée."
    exit 0
fi
