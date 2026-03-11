@echo off
REM ========================================
REM Configuration du système de backup automatique
REM CSAR Platform - Windows
REM ========================================

echo.
echo ╔════════════════════════════════════════════════════════╗
echo ║   Configuration du Backup Automatique - CSAR Platform  ║
echo ╚════════════════════════════════════════════════════════╝
echo.

REM Vérifier les droits administrateur
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERREUR] Ce script nécessite les droits administrateur
    echo Faites un clic droit et sélectionnez "Exécuter en tant qu'administrateur"
    pause
    exit /b 1
)

echo [1/4] Vérification des prérequis...
echo.

REM Vérifier PHP
php -v >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERREUR] PHP n'est pas installé ou pas dans le PATH
    pause
    exit /b 1
)
echo ✓ PHP installé

REM Vérifier MySQL
mysql --version >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERREUR] MySQL n'est pas installé ou pas dans le PATH
    pause
    exit /b 1
)
echo ✓ MySQL installé

REM Vérifier le script de backup
if not exist "%~dp0database_backup.php" (
    echo [ERREUR] Script database_backup.php introuvable
    pause
    exit /b 1
)
echo ✓ Script de backup trouvé

echo.
echo [2/4] Test de la sauvegarde...
echo.

REM Exécuter un test de backup
php "%~dp0database_backup.php"
if %errorLevel% neq 0 (
    echo [ERREUR] Le test de backup a échoué
    echo Vérifiez les logs dans storage/logs/backup.log
    pause
    exit /b 1
)
echo ✓ Test de backup réussi

echo.
echo [3/4] Configuration du planificateur de tâches...
echo.

REM Créer la tâche planifiée (tous les jours à 2h du matin)
schtasks /create /tn "CSAR_Database_Backup" /tr "php \"%~dp0database_backup.php\"" /sc daily /st 02:00 /f
if %errorLevel% neq 0 (
    echo [ERREUR] Échec de la création de la tâche planifiée
    pause
    exit /b 1
)
echo ✓ Tâche planifiée créée (tous les jours à 2h00)

echo.
echo [4/4] Vérification finale...
echo.

REM Vérifier que la tâche existe
schtasks /query /tn "CSAR_Database_Backup" >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERREUR] La tâche planifiée n'a pas été créée correctement
    pause
    exit /b 1
)
echo ✓ Tâche planifiée configurée

echo.
echo ╔════════════════════════════════════════════════════════╗
echo ║              ✅ CONFIGURATION TERMINÉE ✅             ║
echo ╚════════════════════════════════════════════════════════╝
echo.
echo Les sauvegardes automatiques sont maintenant configurées
echo - Fréquence : Quotidienne à 2h00
echo - Emplacement : storage/backups/
echo - Rétention : 30 jours
echo - Logs : storage/logs/backup.log
echo.
echo Commandes utiles :
echo   - Exécuter manuellement : php database_backup.php
echo   - Voir les tâches : schtasks /query /tn "CSAR_Database_Backup"
echo   - Désactiver : schtasks /delete /tn "CSAR_Database_Backup" /f
echo.

pause











































