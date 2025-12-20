<?php

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔍 DIAGNOSTIC MYSQL XAMPP                                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// 1. Vérifier si le processus MySQL est en cours d'exécution
echo "1️⃣ Vérification du processus MySQL...\n";
$mysqlRunning = false;
exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output, $return);
if (!empty($output) && strpos(implode(' ', $output), 'mysqld.exe') !== false) {
    echo "   ✅ Processus mysqld.exe trouvé\n";
    $mysqlRunning = true;
} else {
    echo "   ❌ Processus mysqld.exe non trouvé\n";
    echo "   ⚠️  MySQL n'est PAS en cours d'exécution\n";
}

// 2. Vérifier le port 3306
echo "\n2️⃣ Vérification du port 3306...\n";
exec('netstat -ano | findstr :3306', $portOutput, $portReturn);
if (!empty($portOutput)) {
    echo "   ✅ Port 3306 est utilisé\n";
    foreach ($portOutput as $line) {
        echo "   " . trim($line) . "\n";
    }
} else {
    echo "   ❌ Port 3306 n'est PAS utilisé\n";
    echo "   ⚠️  MySQL n'écoute pas sur le port 3306\n";
}

// 3. Vérifier les chemins XAMPP
echo "\n3️⃣ Vérification des chemins XAMPP...\n";
$xamppPath = 'C:\\xampp';
$mysqlPath = $xamppPath . '\\mysql\\bin\\mysql.exe';
$mysqldPath = $xamppPath . '\\mysql\\bin\\mysqld.exe';

if (file_exists($mysqlPath)) {
    echo "   ✅ mysql.exe trouvé : {$mysqlPath}\n";
} else {
    echo "   ❌ mysql.exe non trouvé : {$mysqlPath}\n";
}

if (file_exists($mysqldPath)) {
    echo "   ✅ mysqld.exe trouvé : {$mysqldPath}\n";
} else {
    echo "   ❌ mysqld.exe non trouvé : {$mysqldPath}\n";
}

// 4. Vérifier les fichiers de configuration
echo "\n4️⃣ Vérification de la configuration MySQL...\n";
$myIniPath = $xamppPath . '\\mysql\\bin\\my.ini';
if (file_exists($myIniPath)) {
    echo "   ✅ Fichier my.ini trouvé\n";
    $myIni = file_get_contents($myIniPath);
    if (preg_match('/port\s*=\s*(\d+)/i', $myIni, $matches)) {
        echo "   📌 Port configuré : {$matches[1]}\n";
    }
} else {
    echo "   ⚠️  Fichier my.ini non trouvé\n";
}

// 5. Test de connexion directe
echo "\n5️⃣ Test de connexion directe...\n";
if (file_exists($mysqlPath)) {
    exec('"' . $mysqlPath . '" -u root -e "SELECT 1" 2>&1', $mysqlTest, $mysqlReturn);
    if ($mysqlReturn === 0) {
        echo "   ✅ Connexion MySQL réussie\n";
    } else {
        echo "   ❌ Connexion MySQL échouée\n";
        echo "   Erreur : " . implode("\n   ", $mysqlTest) . "\n";
    }
}

// 6. Recommandations
echo "\n════════════════════════════════════════════════════════════════\n";
echo "💡 RECOMMANDATIONS\n";
echo "════════════════════════════════════════════════════════════════\n\n";

if (!$mysqlRunning) {
    echo "🔴 ACTION REQUISE :\n";
    echo "   1. Ouvrez le XAMPP Control Panel\n";
    echo "   2. Arrêtez MySQL (s'il apparaît comme démarré)\n";
    echo "   3. Attendez 5 secondes\n";
    echo "   4. Redémarrez MySQL\n";
    echo "   5. Vérifiez les logs dans XAMPP (bouton 'Logs')\n\n";
    
    echo "   Si MySQL ne démarre toujours pas :\n";
    echo "   - Vérifiez qu'aucun autre service MySQL n'est en cours d'exécution\n";
    echo "   - Vérifiez que le port 3306 n'est pas utilisé par un autre programme\n";
    echo "   - Consultez les logs MySQL dans C:\\xampp\\mysql\\data\\*.err\n\n";
} else {
    echo "✅ MySQL semble être en cours d'exécution\n";
    echo "   Mais la connexion échoue. Vérifiez :\n";
    echo "   - Les permissions de l'utilisateur 'root'\n";
    echo "   - La configuration du firewall Windows\n";
    echo "   - Les logs MySQL pour plus de détails\n\n";
}

echo "📝 Après avoir redémarré MySQL, relancez :\n";
echo "   php fix_mysql_connection.php\n\n";


