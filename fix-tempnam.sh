#!/bin/bash

# Script pour fixer le problème tempnam() sur le serveur
# Ce script modifie public/index.php pour définir les variables d'environnement

cd /var/www/csar

# Sauvegarder public/index.php
cp public/index.php public/index.php.backup

# Ajouter les variables d'environnement au début de public/index.php
sed -i '/<?php/a\
\
// Fix tempnam() problem - set custom temp directory\
$tmpDir = __DIR__ . "/../storage/tmp";\
if (!is_dir($tmpDir)) {\
    @mkdir($tmpDir, 0777, true);\
}\
putenv("TMPDIR=" . $tmpDir);\
putenv("TMP=" . $tmpDir);\
putenv("TEMP=" . $tmpDir);\
$_ENV["TMPDIR"] = $tmpDir;\
$_ENV["TMP"] = $tmpDir;\
$_ENV["TEMP"] = $tmpDir;\
' public/index.php

echo "Done! public/index.php has been modified to fix tempnam() issue."
