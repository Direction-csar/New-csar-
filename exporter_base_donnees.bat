@echo off
chcp 65001 >nul
title Export Base de Données CSAR
color 0B

echo.
echo ╔═══════════════════════════════════════════════════════════╗
echo ║                                                           ║
echo ║      📦 EXPORT DE LA BASE DE DONNÉES CSAR                ║
echo ║                                                           ║
echo ╚═══════════════════════════════════════════════════════════╝
echo.
echo.
echo 🎯 Ce script va créer une sauvegarde complète de votre base de données
echo    pour la migrer vers votre nouveau serveur.
echo.
echo ═══════════════════════════════════════════════════════════════
echo  OPTIONS D'EXPORT
echo ═══════════════════════════════════════════════════════════════
echo.
echo [1] Export via Laravel (Recommandé)
echo [2] Export via mysqldump
echo [3] Ouvrir phpMyAdmin
echo [4] Annuler
echo.
set /p choice="Votre choix (1-4) : "

if "%choice%"=="1" goto export_laravel
if "%choice%"=="2" goto export_mysqldump
if "%choice%"=="3" goto phpmyadmin
if "%choice%"=="4" goto end

:export_laravel
echo.
echo 🔄 Export via Laravel...
echo.
php artisan db:table:export --format=sql
echo.
echo ✅ Export terminé !
echo 📁 Fichier créé : storage/app/export/csar_export.sql
goto end

:export_mysqldump
echo.
echo 🔄 Export via mysqldump...
echo.
set TIMESTAMP=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set FILENAME=csar_backup_%TIMESTAMP%.sql

mysqldump -u root -p csar > %FILENAME%
echo.
echo ✅ Export terminé !
echo 📁 Fichier créé : %FILENAME%
goto end

:phpmyadmin
echo.
echo 🌐 Ouverture de phpMyAdmin...
start http://localhost/phpmyadmin
echo.
echo 📝 INSTRUCTIONS :
echo    1. Cliquez sur la base "csar" dans le menu gauche
echo    2. Cliquez sur l'onglet "Exporter" en haut
echo    3. Choisissez "Méthode rapide" et format "SQL"
echo    4. Cliquez sur "Exécuter"
echo    5. Sauvegardez le fichier
echo.
goto end

:end
echo.
echo ═══════════════════════════════════════════════════════════════
echo  📄 PROCHAINES ÉTAPES
echo ═══════════════════════════════════════════════════════════════
echo.
echo 1. Conservez précieusement le fichier SQL créé
echo 2. Copiez aussi le dossier : storage\app\public\
echo 3. Consultez GUIDE_MIGRATION_SERVEUR.md pour la suite
echo.
pause









































