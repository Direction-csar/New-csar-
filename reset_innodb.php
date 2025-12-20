<?php

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔄 RÉINITIALISATION INNODB                               ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  ATTENTION : Cette opération va supprimer les fichiers InnoDB corrompus\n";
echo "   MySQL devra recréer ces fichiers au prochain démarrage.\n\n";

echo "📋 Cette opération va :\n";
echo "   1. Vérifier que MySQL est arrêté\n";
echo "   2. Supprimer les fichiers InnoDB corrompus\n";
echo "   3. Retirer le mode de récupération du my.ini\n";
echo "   4. Préparer MySQL pour un redémarrage propre\n\n";

// Vérifier que MySQL est arrêté
echo "1️⃣ Vérification que MySQL est arrêté...\n";
exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $return);
if (!empty($output) && strpos(implode(' ', $output), 'mysqld.exe') !== false) {
    echo "   ❌ MySQL est encore en cours d'exécution !\n";
    echo "   ⚠️  Veuillez arrêter MySQL dans XAMPP avant de continuer.\n";
    exit(1);
} else {
    echo "   ✅ MySQL est arrêté\n";
}

$mysqlDataDir = 'C:\\xampp\\mysql\\data';
$myIniPath = 'C:\\xampp\\mysql\\bin\\my.ini';

// Fichiers InnoDB à supprimer
$innodbFiles = [
    'ibdata1',
    'ib_logfile0',
    'ib_logfile1',
    'ibtmp1'
];

echo "\n2️⃣ Suppression des fichiers InnoDB corrompus...\n";
$deleted = [];
$notFound = [];

foreach ($innodbFiles as $file) {
    $filePath = $mysqlDataDir . '\\' . $file;
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo "   ✅ Supprimé : {$file}\n";
            $deleted[] = $file;
        } else {
            echo "   ❌ Erreur lors de la suppression : {$file}\n";
            echo "      Vérifiez les permissions\n";
        }
    } else {
        echo "   ⚠️  Non trouvé : {$file} (déjà supprimé ou n'existe pas)\n";
        $notFound[] = $file;
    }
}

if (empty($deleted) && !empty($notFound)) {
    echo "\n   ℹ️  Tous les fichiers étaient déjà absents\n";
}

// Retirer le mode de récupération du my.ini
echo "\n3️⃣ Retrait du mode de récupération du my.ini...\n";
if (file_exists($myIniPath)) {
    $content = file_get_contents($myIniPath);
    
    // Sauvegarder
    $backupPath = $myIniPath . '.backup.' . date('Y-m-d_H-i-s');
    copy($myIniPath, $backupPath);
    echo "   💾 Sauvegarde créée : {$backupPath}\n";
    
    // Retirer innodb_force_recovery
    if (preg_match('/innodb_force_recovery\s*=\s*\d+/i', $content)) {
        $content = preg_replace('/innodb_force_recovery\s*=\s*\d+\s*\n?/i', '', $content);
        
        if (file_put_contents($myIniPath, $content)) {
            echo "   ✅ Mode de récupération retiré du my.ini\n";
        } else {
            echo "   ❌ Erreur lors de l'écriture du my.ini\n";
        }
    } else {
        echo "   ℹ️  Le mode de récupération n'était pas présent\n";
    }
} else {
    echo "   ❌ Fichier my.ini non trouvé : {$myIniPath}\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";
echo "✅ RÉINITIALISATION TERMINÉE\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "📋 PROCHAINES ÉTAPES :\n\n";
echo "1️⃣  Dans XAMPP Control Panel, démarrez MySQL\n";
echo "    MySQL va créer de nouveaux fichiers InnoDB propres\n\n";
echo "2️⃣  Une fois MySQL démarré, exécutez :\n";
echo "    php artisan migrate\n";
echo "    (Pour recréer les tables Laravel)\n\n";
echo "3️⃣  Si vous aviez des données importantes, vous devrez les réimporter\n";
echo "    depuis une sauvegarde précédente.\n\n";

echo "💡 NOTE : Si vous avez des bases de données importantes dans\n";
echo "   C:\\xampp\\mysql\\data\\, elles devraient être préservées.\n";
echo "   Seuls les fichiers système InnoDB ont été supprimés.\n\n";


