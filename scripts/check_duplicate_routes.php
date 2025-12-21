#!/usr/bin/env php
<?php

/**
 * Script pour détecter les routes dupliquées dans routes/web.php
 * Usage: php scripts/check_duplicate_routes.php
 */

$routesFile = __DIR__ . '/../routes/web.php';

if (!file_exists($routesFile)) {
    echo "❌ Fichier routes/web.php introuvable\n";
    exit(1);
}

$content = file_get_contents($routesFile);
$lines = explode("\n", $content);

$routeNames = [];
$duplicates = [];

foreach ($lines as $lineNum => $line) {
    // Chercher les routes avec ->name()
    if (preg_match('/->name\([\'"]([^\'"]+)[\'"]\)/', $line, $matches)) {
        $routeName = $matches[1];
        $lineNumber = $lineNum + 1;
        
        if (isset($routeNames[$routeName])) {
            $duplicates[$routeName][] = [
                'line' => $lineNumber,
                'content' => trim($line)
            ];
            if (!isset($duplicates[$routeName][0])) {
                $duplicates[$routeName][0] = [
                    'line' => $routeNames[$routeName],
                    'content' => 'Voir ligne ' . $routeNames[$routeName]
                ];
            }
        } else {
            $routeNames[$routeName] = $lineNumber;
        }
    }
}

if (empty($duplicates)) {
    echo "✅ Aucune route dupliquée trouvée !\n";
    exit(0);
}

echo "❌ Routes dupliquées trouvées :\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($duplicates as $routeName => $occurrences) {
    echo "Route: {$routeName}\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($occurrences as $occurrence) {
        echo "  Ligne {$occurrence['line']}: {$occurrence['content']}\n";
    }
    echo "\n";
}

exit(1);

