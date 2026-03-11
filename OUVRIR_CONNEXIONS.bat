@echo off
chcp 65001 >nul
title 🔐 Connexions CSAR
color 0A

echo ═══════════════════════════════════════════════════════════════
echo     🔐 OUVERTURE DES PAGES DE CONNEXION
echo ═══════════════════════════════════════════════════════════════
echo.

echo Ouverture de la page avec tous les boutons de connexion...
echo.

start http://localhost:8000/connexion-tous.html

echo ✅ Page ouverte dans votre navigateur!
echo.
echo Cliquez sur le bouton correspondant au rôle que vous voulez:
echo.
echo   👨‍💼 Admin
echo   👔 DG (Directeur Général)
echo   📦 Entrepôt
echo   🚚 Agent
echo   👥 DRH
echo.
echo ═══════════════════════════════════════════════════════════════
echo.

pause









