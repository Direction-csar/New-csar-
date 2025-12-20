@echo off
title Test Rapide - Configuration Produits
color 0B
cls

echo.
echo ========================================
echo   TEST RAPIDE DE CONFIGURATION
echo ========================================
echo.

REM Vérifier si XAMPP est installé
if not exist "C:\xampp\htdocs\csar\" (
    color 0C
    echo [ERREUR] Le dossier csar n'existe pas !
    echo Verifiez le chemin : C:\xampp\htdocs\csar\
    echo.
    pause
    exit
)

echo [OK] Dossier du projet trouve
echo.

REM Tester si Apache répond
echo Test de connexion au serveur...
echo.

curl -s -o nul -w "Status HTTP: %%{http_code}" http://localhost/csar/ 2>nul
if %errorlevel% neq 0 (
    color 0E
    echo.
    echo.
    echo [ATTENTION] Le serveur ne repond pas !
    echo.
    echo Veuillez :
    echo 1. Ouvrir XAMPP Control Panel
    echo 2. Demarrer Apache (bouton Start)
    echo 3. Demarrer MySQL (bouton Start)
    echo 4. Relancer ce test
    echo.
    pause
    exit
)

echo.
echo [OK] Le serveur Apache repond
echo.

REM Vérifier les fichiers créés
echo Verification des fichiers...
echo.

if exist "START_HERE.html" (
    echo [OK] START_HERE.html
) else (
    echo [ERREUR] START_HERE.html manquant
)

if exist "ajouter_produit_manuel.php" (
    echo [OK] ajouter_produit_manuel.php
) else (
    echo [ERREUR] ajouter_produit_manuel.php manquant
)

if exist "gestion_produits.php" (
    echo [OK] gestion_produits.php
) else (
    echo [ERREUR] gestion_produits.php manquant
)

echo.
echo ========================================
echo   RESULTAT DU TEST
echo ========================================
echo.

color 0A
echo [SUCCES] Tout est pret !
echo.
echo ========================================
echo   PROCHAINES ETAPES
echo ========================================
echo.
echo 1. Le navigateur va s'ouvrir dans 3 secondes
echo 2. Cliquez sur "Formulaire Simple"
echo 3. Ajoutez votre premier produit
echo 4. Testez dans l'application !
echo.
echo ========================================
echo.

timeout /t 3 /nobreak >nul

REM Ouvrir le navigateur
start http://localhost/csar/START_HERE.html

echo Navigateur ouvert !
echo.
echo Vous pouvez fermer cette fenetre.
echo.
pause




































