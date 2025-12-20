<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔧 CORRECTION DE LA CONNEXION MYSQL                     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Configurations à tester
$configs = [
    ['host' => '127.0.0.1', 'port' => 3306],
    ['host' => 'localhost', 'port' => 3306],
    ['host' => '127.0.0.1', 'port' => 3307],
    ['host' => 'localhost', 'port' => 3307],
];

$dbName = env('DB_DATABASE', 'csar');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');

echo "📊 Configuration actuelle :\n";
echo "   Base de données : {$dbName}\n";
echo "   Utilisateur : {$username}\n\n";

echo "🔍 Test des différentes configurations...\n\n";

$workingConfig = null;

foreach ($configs as $config) {
    echo "Test avec {$config['host']}:{$config['port']}... ";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']}",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Vérifier si la base de données existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbName}'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "✅ CONNEXION RÉUSSIE - Base de données trouvée!\n";
            $workingConfig = $config;
            break;
        } else {
            echo "⚠️  Connexion OK mais base '{$dbName}' introuvable\n";
            echo "   Bases disponibles : ";
            $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
            echo implode(', ', $dbs) . "\n";
        }
    } catch (PDOException $e) {
        echo "❌ Échec : " . $e->getMessage() . "\n";
    }
}

if ($workingConfig) {
    echo "\n✅ Configuration fonctionnelle trouvée : {$workingConfig['host']}:{$workingConfig['port']}\n\n";
    
    // Mettre à jour le fichier .env
    echo "📝 Mise à jour du fichier .env...\n";
    
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $envContent = file_get_contents($envFile);
        
        // Remplacer DB_HOST
        $envContent = preg_replace(
            '/^DB_HOST=.*/m',
            "DB_HOST={$workingConfig['host']}",
            $envContent
        );
        
        // Remplacer DB_PORT
        $envContent = preg_replace(
            '/^DB_PORT=.*/m',
            "DB_PORT={$workingConfig['port']}",
            $envContent
        );
        
        file_put_contents($envFile, $envContent);
        echo "✅ Fichier .env mis à jour\n\n";
        
        // Vider le cache
        echo "🧹 Nettoyage du cache...\n";
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        echo "✅ Cache vidé\n\n";
        
        // Test final
        echo "🔌 Test de connexion finale...\n";
        try {
            DB::connection()->getPdo();
            echo "✅ Connexion réussie !\n";
        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Fichier .env non trouvé\n";
    }
} else {
    echo "\n❌ Aucune configuration fonctionnelle trouvée\n";
    echo "\n💡 Vérifications à faire :\n";
    echo "   1. MySQL est-il démarré dans XAMPP ?\n";
    echo "   2. Le port MySQL est-il bien 3306 ?\n";
    echo "   3. La base de données '{$dbName}' existe-t-elle ?\n";
    echo "   4. L'utilisateur '{$username}' a-t-il les droits ?\n";
}

echo "\n════════════════════════════════════════════════════════════════\n";


