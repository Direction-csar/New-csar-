@echo off
chcp 65001 >nul
title 🚀 CSAR - Démarrage Local
color 0A

echo ===================================================================
echo     🚀 DÉMARRAGE PLATEFORME CSAR EN LOCAL
echo ===================================================================
echo.

REM Vérifier que nous sommes dans le bon répertoire
if not exist "artisan" (
    echo ❌ ERREUR: Vous n'êtes pas dans le répertoire du projet CSAR
    echo.
    echo Veuillez naviguer vers: C:\xampp\htdocs\csar
    echo.
    pause
    exit /b 1
)

REM Vérifier si XAMPP est démarré
echo [1/5] Vérification de XAMPP...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ MySQL est démarré
) else (
    echo ⚠️  MySQL n'est pas démarré
    echo    Veuillez démarrer XAMPP et MySQL
    echo.
    pause
    exit /b 1
)

REM Vérifier si le port 8000 est déjà utilisé
echo.
echo [2/5] Vérification du port 8000...
netstat -ano | findstr :8000 > nul
if %errorlevel% equ 0 (
    echo ⚠️  Un serveur est déjà en cours d'exécution sur le port 8000
    echo.
    choice /C YN /M "Voulez-vous le redémarrer"
    if errorlevel 2 goto :skip_kill
    if errorlevel 1 (
        echo 🔄 Arrêt du serveur existant...
        for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8000') do taskkill /F /PID %%a > nul 2>&1
        timeout /t 2 > nul
    )
)
:skip_kill

REM Nettoyage du cache
echo.
echo [3/5] Nettoyage du cache...
php artisan config:clear > nul 2>&1
php artisan cache:clear > nul 2>&1
php artisan route:clear > nul 2>&1
php artisan view:clear > nul 2>&1
echo ✅ Cache vidé

REM Vérification de la base de données
echo.
echo [4/5] Vérification de la base de données...
php artisan migrate:status > nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Base de données accessible
) else (
    echo ⚠️  Problème de connexion à la base de données
    echo    Vérifiez que MySQL est démarré dans XAMPP
)

REM Affichage des informations de connexion
echo.
echo [5/5] Démarrage du serveur...
echo.
echo ═══════════════════════════════════════════════════════════════
echo     🔐 IDENTIFIANTS DE CONNEXION
echo ═══════════════════════════════════════════════════════════════
echo.
echo 👤 ADMINISTRATEUR
echo    📧 Email: admin@csar.sn
echo    🔑 Mot de passe: password
echo    🌐 URL: http://localhost:8000/admin/login
echo.
echo 👔 DIRECTEUR GÉNÉRAL (DG)
echo    📧 Email: dg@csar.sn
echo    🔑 Mot de passe: password
echo    🌐 URL: http://localhost:8000/dg/login
echo.
echo 📦 GESTIONNAIRE D'ENTREPÔT
echo    📧 Email: entrepot@csar.sn
echo    🔑 Mot de passe: password
echo    🌐 URL: http://localhost:8000/entrepot/login
echo.
echo 👨‍💼 DRH
echo    📧 Email: drh@csar.sn
echo    🔑 Mot de passe: password
echo    🌐 URL: http://localhost:8000/drh/login
echo.
echo 🚚 AGENT
echo    📧 Email: agent@csar.sn
echo    🔑 Mot de passe: password
echo    🌐 URL: http://localhost:8000/agent/login
echo.
echo 🌍 SITE PUBLIC
echo    🌐 URL: http://localhost:8000
echo.
echo ═══════════════════════════════════════════════════════════════
echo.
echo 💡 Le navigateur va s'ouvrir automatiquement...
echo    ⚠️  NE FERMEZ PAS CETTE FENÊTRE
echo    ⏹️  Appuyez sur Ctrl+C pour arrêter le serveur
echo.

REM Attendre un peu avant d'ouvrir le navigateur
timeout /t 2 > nul

REM Ouvrir le navigateur sur la page d'accueil
start http://localhost:8000

REM Démarrer le serveur Laravel
echo 🚀 Serveur en cours de démarrage...
echo.
php artisan serve --host=127.0.0.1 --port=8000

echo.
echo ⏹️  Serveur arrêté.
pause





