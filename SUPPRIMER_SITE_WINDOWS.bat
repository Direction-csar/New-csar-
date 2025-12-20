@echo off
chcp 65001 >nul
echo ============================================
echo    SUPPRESSION DU SITE CSAR DU SERVEUR
echo ============================================
echo.
echo ⚠️  ATTENTION: Cette action va supprimer:
echo    - Tous les fichiers du site sur le serveur
echo    - La configuration Apache
echo    - La base de données
echo    - L'utilisateur MySQL
echo.
echo Cette action est IRRÉVERSIBLE !
echo.
pause
echo.

echo ============================================
echo    ÉTAPES DE SUPPRESSION
echo ============================================
echo.
echo 1. Vous allez vous connecter au serveur SSH
echo 2. Le script de suppression sera téléchargé
echo 3. Le script sera exécuté pour supprimer le site
echo.
echo ============================================
echo    INFORMATIONS DU SERVEUR
echo ============================================
echo.
echo Serveur: srv1177675.hstgr.cloud
echo IP: 72.61.16.34
echo Domaine: csar.sn
echo.
pause
echo.

echo ============================================
echo    CONNEXION SSH
echo ============================================
echo.
echo Vous allez maintenant vous connecter au serveur.
echo Entrez votre mot de passe root quand demandé.
echo.
pause

echo Connexion au serveur...
ssh root@72.61.16.34

echo.
echo ============================================
echo    ÉTAPES SUIVANTES
echo ============================================
echo.
echo Une fois connecté au serveur, exécutez:
echo.
echo 1. Télécharger le script:
echo    cat ^> /tmp/supprimer_site.sh ^<^< 'EOF'
echo    [Copiez le contenu de SUPPRIMER_SITE_SERVEUR.sh]
echo    EOF
echo.
echo 2. Rendre le script exécutable:
echo    chmod +x /tmp/supprimer_site.sh
echo.
echo 3. Exécuter le script:
echo    sudo bash /tmp/supprimer_site.sh
echo.
echo 4. Confirmer en tapant: OUI
echo.
echo ============================================
echo    OU UTILISEZ LA MÉTHODE MANUELLE
echo ============================================
echo.
echo Consultez SUPPRIMER_SITE_INSTRUCTIONS.md
echo pour les instructions détaillées.
echo.
pause





