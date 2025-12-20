<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

echo "═══════════════════════════════════════════════════════════════\n";
echo "       RÉINITIALISATION DU RATE LIMITING DG\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Réinitialiser tous les rate limits pour DG
$ips = ['127.0.0.1', '::1', 'localhost'];

foreach ($ips as $ip) {
    $key = 'dg-login:' . $ip;
    RateLimiter::clear($key);
    echo "✅ Rate limit effacé pour: {$ip}\n";
}

// Nettoyer aussi le cache général
Cache::flush();
echo "✅ Cache complètement vidé\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Réinitialisation terminée\n";
echo "═══════════════════════════════════════════════════════════════\n";




