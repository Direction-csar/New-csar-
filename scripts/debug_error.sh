#!/bin/bash

# Script pour afficher l'erreur complète depuis les logs Laravel
# Usage: bash scripts/debug_error.sh

cd /var/www/csar || exit 1

echo "🔍 Recherche de la dernière erreur dans les logs..."
echo "=================================================="
echo ""

# Afficher les 100 dernières lignes et chercher l'erreur
tail -100 storage/logs/laravel.log | grep -A 50 "local.ERROR\|Exception\|Error" | tail -80

echo ""
echo "📋 Dernières lignes du log (pour contexte):"
echo "=========================================="
tail -30 storage/logs/laravel.log

