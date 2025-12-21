#!/usr/bin/env php
<?php

/**
 * Script pour détecter et corriger automatiquement toutes les routes dupliquées
 * Usage: php scripts/fix_all_duplicate_routes.php
 */

$routesFile = __DIR__ . '/../routes/web.php';

if (!file_exists($routesFile)) {
    echo "❌ Fichier routes/web.php introuvable\n";
    exit(1);
}

$content = file_get_contents($routesFile);
$lines = explode("\n", $content);

// Trouver le groupe {locale}
$localeGroupStart = null;
$localeGroupEnd = null;
$inLocaleGroup = false;
$braceCount = 0;

foreach ($lines as $lineNum => $line) {
    if (preg_match('/Route::group\(\[.*prefix.*\{locale\}/', $line)) {
        $localeGroupStart = $lineNum;
        $inLocaleGroup = true;
        $braceCount = 1;
    } elseif ($inLocaleGroup) {
        $braceCount += substr_count($line, '{') - substr_count($line, '}');
        if ($braceCount === 0) {
            $localeGroupEnd = $lineNum;
            break;
        }
    }
}

if ($localeGroupStart === null || $localeGroupEnd === null) {
    echo "❌ Impossible de trouver le groupe {locale}\n";
    exit(1);
}

echo "✅ Groupe {locale} trouvé : lignes " . ($localeGroupStart + 1) . " à " . ($localeGroupEnd + 1) . "\n\n";

// Collecter toutes les routes dans le groupe {locale}
$routesInLocaleGroup = [];
for ($i = $localeGroupStart; $i <= $localeGroupEnd; $i++) {
    if (preg_match('/->name\([\'"]([^\'"]+)[\'"]\)/', $lines[$i], $matches)) {
        $routeName = $matches[1];
        $routesInLocaleGroup[$routeName] = $i + 1;
    }
}

// Trouver les routes dupliquées hors du groupe
$duplicates = [];
for ($i = 0; $i < count($lines); $i++) {
    // Ignorer les lignes dans le groupe {locale}
    if ($i >= $localeGroupStart && $i <= $localeGroupEnd) {
        continue;
    }
    
    // Ignorer les lignes commentées
    if (preg_match('/^\s*\/\//', $lines[$i])) {
        continue;
    }
    
    if (preg_match('/->name\([\'"]([^\'"]+)[\'"]\)/', $lines[$i], $matches)) {
        $routeName = $matches[1];
        if (isset($routesInLocaleGroup[$routeName])) {
            $duplicates[] = [
                'name' => $routeName,
                'line' => $i + 1,
                'content' => trim($lines[$i]),
                'localeLine' => $routesInLocaleGroup[$routeName]
            ];
        }
    }
}

if (empty($duplicates)) {
    echo "✅ Aucune route dupliquée trouvée !\n";
    exit(0);
}

echo "❌ Routes dupliquées trouvées :\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($duplicates as $dup) {
    echo "Route: {$dup['name']}\n";
    echo "  - Ligne {$dup['localeLine']} (dans groupe {locale}): ACTIVE\n";
    echo "  - Ligne {$dup['line']} (hors groupe): {$dup['content']}\n";
    echo "    → DOIT ÊTRE COMMENTÉE\n\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Total: " . count($duplicates) . " route(s) dupliquée(s) à corriger\n";

exit(1);

