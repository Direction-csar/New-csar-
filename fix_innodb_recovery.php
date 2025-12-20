<?php

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔧 CORRECTION INNODB - MODE RÉCUPÉRATION                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$myIniPath = 'C:\\xampp\\mysql\\bin\\my.ini';

if (!file_exists($myIniPath)) {
    echo "❌ Fichier my.ini non trouvé : {$myIniPath}\n";
    exit(1);
}

echo "📝 Lecture du fichier my.ini...\n";
$content = file_get_contents($myIniPath);

// Vérifier si le mode de récupération est déjà configuré
if (preg_match('/innodb_force_recovery\s*=\s*\d+/i', $content)) {
    echo "⚠️  Le mode de récupération InnoDB est déjà configuré\n";
    preg_match('/innodb_force_recovery\s*=\s*(\d+)/i', $content, $matches);
    echo "   Niveau actuel : {$matches[1]}\n\n";
    
    if ($matches[1] < 4) {
        echo "🔄 Augmentation du niveau de récupération à 4...\n";
        $content = preg_replace(
            '/innodb_force_recovery\s*=\s*\d+/i',
            'innodb_force_recovery=4',
            $content
        );
    } else {
        echo "✅ Le niveau de récupération est déjà à 4 ou plus\n";
        echo "   Pas de modification nécessaire\n\n";
        exit(0);
    }
} else {
    echo "➕ Ajout du mode de récupération InnoDB...\n";
    
    // Trouver la section [mysqld] et ajouter après innodb_lock_wait_timeout
    $pattern = '/(innodb_lock_wait_timeout=\d+)/i';
    if (preg_match($pattern, $content)) {
        $content = preg_replace(
            $pattern,
            "$1\ninnodb_force_recovery=4",
            $content
        );
    } else {
        // Si on ne trouve pas, ajouter après la dernière ligne innodb
        $content = preg_replace(
            '/(innodb_flush_log_at_trx_commit=\d+)/i',
            "$1\ninnodb_force_recovery=4",
            $content
        );
    }
}

// Sauvegarder le fichier original
$backupPath = $myIniPath . '.backup.' . date('Y-m-d_H-i-s');
echo "💾 Sauvegarde du fichier original : {$backupPath}\n";
copy($myIniPath, $backupPath);

// Écrire le nouveau contenu
echo "✏️  Écriture de la nouvelle configuration...\n";
if (file_put_contents($myIniPath, $content)) {
    echo "✅ Fichier my.ini mis à jour avec succès\n\n";
} else {
    echo "❌ Erreur lors de l'écriture du fichier\n";
    echo "   Vérifiez les permissions d'écriture\n";
    exit(1);
}

echo "════════════════════════════════════════════════════════════════\n";
echo "📋 INSTRUCTIONS\n";
echo "════════════════════════════════════════════════════════════════\n\n";
echo "1️⃣  Arrêtez MySQL dans le XAMPP Control Panel\n";
echo "2️⃣  Attendez 5 secondes\n";
echo "3️⃣  Redémarrez MySQL dans le XAMPP Control Panel\n";
echo "4️⃣  Une fois MySQL redémarré, exécutez :\n";
echo "    php fix_mysql_connection.php\n\n";
echo "💡 Le mode de récupération InnoDB niveau 4 permet :\n";
echo "   - De démarrer MySQL malgré les erreurs de log\n";
echo "   - De lire les données (mais pas d'écrire)\n";
echo "   - De récupérer vos données importantes\n\n";
echo "⚠️  IMPORTANT : En mode récupération, vous ne pourrez pas :\n";
echo "   - Créer/modifier/supprimer des tables\n";
echo "   - Insérer/mettre à jour des données\n";
echo "   - Exécuter des migrations\n\n";
echo "   Une fois vos données récupérées, vous devrez :\n";
echo "   - Exporter vos données\n";
echo "   - Réinitialiser InnoDB (supprimer ibdata1, ib_logfile*)\n";
echo "   - Retirer innodb_force_recovery du my.ini\n";
echo "   - Redémarrer MySQL et réimporter vos données\n\n";


