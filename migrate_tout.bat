@echo off
chcp 65001 >nul
cls
echo ════════════════════════════════════════════════════════════
echo     MIGRATION COMPLÈTE - PLATEFORME CSAR
echo ════════════════════════════════════════════════════════════
echo.

cd /D C:\xampp\htdocs\csar

echo 🔍 Vérification de la connexion MySQL...
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch (Exception \$e) { echo 'ERREUR'; exit(1); }" 2>nul
if errorlevel 1 (
    echo ❌ Connexion MySQL échouée
    echo    Vérifiez que MySQL est démarré
    pause
    exit /b 1
)
echo ✅ Connexion MySQL OK
echo.

echo 🧹 Nettoyage du cache...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo ✅ Cache nettoyé
echo.

echo 📊 Exécution des migrations...
php artisan migrate --force
if errorlevel 1 (
    echo.
    echo ❌ Erreur lors des migrations
    pause
    exit /b 1
)
echo.

echo ✅✅✅ MIGRATIONS TERMINÉES AVEC SUCCÈS ! ✅✅✅
echo.
echo 🌐 Votre application est maintenant accessible sur :
echo    http://localhost:8000
echo.
echo 💡 Prochaines étapes :
echo    1. Testez l'application dans votre navigateur
echo    2. Si besoin, exécutez : php artisan db:seed
echo.
pause


