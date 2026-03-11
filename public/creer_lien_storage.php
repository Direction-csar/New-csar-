<?php
/**
 * Crée le lien symbolique public/storage -> storage/app/public
 * Nécessaire pour que les images des actualités, publications et galerie s'affichent.
 * À exécuter une fois (navigateur ou CLI : php public/creer_lien_storage.php)
 * Ensuite vous pouvez supprimer ce fichier pour la sécurité.
 */

$link = __DIR__ . '/storage';
$target = __DIR__ . '/../storage/app/public';

if (file_exists($link)) {
    echo "Le lien storage existe déjà.\n";
    exit(0);
}
if (!is_dir($target)) {
    mkdir($target, 0755, true);
}
if (PHP_OS_FAMILY === 'Windows') {
    if (is_dir($target) && !file_exists($link)) {
        if (@symlink($target, $link)) {
            echo "Lien storage créé avec succès.\n";
        } else {
            echo "Échec (droits administrateur ?). Exécutez en console : php artisan storage:link\n";
        }
    }
} else {
    if (@symlink($target, $link)) {
        echo "Lien storage créé avec succès.\n";
    } else {
        echo "Échec. Exécutez : php artisan storage:link\n";
    }
}
