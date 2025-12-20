<?php
// Test simple de connexion MySQL
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';

echo "Test de connexion MySQL...\n";
echo "Host: {$host}:{$port}\n";
echo "User: {$user}\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port}",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
    echo "✅ Connexion réussie !\n";
    
    // Tester une requête simple
    $stmt = $pdo->query("SELECT 1 AS test");
    $result = $stmt->fetch();
    echo "✅ Requête test réussie : " . $result['test'] . "\n";
    
    // Lister les bases de données
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\n📊 Bases de données disponibles :\n";
    foreach ($dbs as $db) {
        echo "   - {$db}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";
}


