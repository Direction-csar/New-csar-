@echo off
chcp 65001 >nul
echo ════════════════════════════════════════════════════════════
echo     CORRECTION DÉMARRAGE MYSQL XAMPP
echo ════════════════════════════════════════════════════════════
echo.

REM Arrêter MySQL s'il est en cours d'exécution
echo 🔄 Arrêt de MySQL...
taskkill /F /IM mysqld.exe >nul 2>&1
timeout /t 3 /nobreak >nul

REM Vérifier que MySQL est bien arrêté
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ❌ Impossible d'arrêter MySQL complètement
    echo    Veuillez l'arrêter manuellement dans XAMPP
    pause
    exit /b 1
)

echo ✅ MySQL est arrêté
echo.

REM Démarrer MySQL directement avec la configuration correcte
echo 🚀 Démarrage de MySQL...
echo    (Cette opération peut prendre 10-15 secondes)
echo.

start /MIN "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone

echo ⏳ Attente du démarrage complet (10 secondes)...
timeout /t 10 /nobreak >nul

echo.
echo 🔍 Test de connexion...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 'MySQL fonctionne!' AS Status;" 2>nul
if "%ERRORLEVEL%"=="0" (
    echo.
    echo ✅✅✅ MySQL est démarré et fonctionne ! ✅✅✅
    echo.
    echo 📊 Bases de données disponibles :
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"
    echo.
    echo 💡 Vous pouvez maintenant :
    echo    1. Utiliser votre application Laravel
    echo    2. Exécuter : php artisan migrate
    echo    3. Accéder à http://localhost:8000
    echo.
) else (
    echo.
    echo ⚠️  MySQL démarre mais ne répond pas encore
    echo    Cela peut prendre quelques secondes supplémentaires
    echo.
    echo    Vérifiez les logs dans :
    echo    C:\xampp\mysql\data\*.err
    echo.
    echo    Ou réessayez dans 10 secondes avec :
    echo    C:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 1;"
    echo.
)

pause


