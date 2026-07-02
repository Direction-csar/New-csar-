<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;

$routes = Route::getRoutes();
$found = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'warehouse/v1') !== false) {
        $found[] = $route->methods()[0] . ' ' . $uri . ' -> ' . $route->getName();
    }
}
echo "Found " . count($found) . " warehouse routes:\n";
foreach ($found as $f) echo $f . "\n";
