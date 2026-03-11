@echo off
chcp 65001 >nul
cd /d "C:\xampp\htdocs\csar"
echo.
echo ========================================
echo   CSAR - Ouvrir la plateforme
echo ========================================
echo   Dossier : %CD%
echo.
echo 1. Vider les caches Laravel...
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear
C:\xampp\php\php.exe artisan view:clear
echo.
echo 2. Demarrage du serveur sur http://127.0.0.1:8000
echo.
echo    Ouvre dans ton navigateur EXACTEMENT :
echo    http://127.0.0.1:8000
echo    ou
echo    http://127.0.0.1:8000/fr
echo.
echo    (N'utilise PAS localhost/csar ni autre URL)
echo.
echo 3. Appuyer sur Ctrl+C pour arreter le serveur.
echo.
echo Ouverture de http://127.0.0.1:8000/fr dans le navigateur...
start "" "http://127.0.0.1:8000/fr"
echo.
C:\xampp\php\php.exe artisan serve
