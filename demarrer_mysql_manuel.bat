@echo off
chcp 65001 >nul
echo ════════════════════════════════════════════════════════════
echo     DÉMARRAGE MANUEL MYSQL (SANS OPTION INCOMPATIBLE)
echo ════════════════════════════════════════════════════════════
echo.

REM Vérifier si MySQL est déjà en cours d'exécution
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ MySQL est déjà en cours d'exécution
    echo.
    goto :test
)

echo 🔄 Arrêt de MySQL dans XAMPP (si actif)...
taskkill /F /IM mysqld.exe >nul 2>&1
timeout /t 2 /nobreak >nul

echo 🚀 Démarrage de MySQL directement...
echo.

REM Démarrer MySQL directement avec les options de base
start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone --console

echo ⏳ Attente du démarrage de MySQL (5 secondes)...
timeout /t 5 /nobreak >nul

:test
echo 🔍 Test de connexion...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 'MySQL fonctionne!' AS Status;" 2>nul
if "%ERRORLEVEL%"=="0" (
    echo.
    echo ✅ MySQL est démarré et fonctionne correctement !
    echo.
    echo 📊 Informations :
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "SHOW DATABASES;"
    echo.
    echo 💡 Vous pouvez maintenant utiliser votre application Laravel
    echo.
) else (
    echo.
    echo ❌ MySQL ne répond pas encore
    echo    Attendez quelques secondes et réessayez
    echo    Ou vérifiez les logs dans C:\xampp\mysql\data\*.err
    echo.
)

pause


