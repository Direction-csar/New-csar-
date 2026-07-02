<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$wh = DB::table('warehouses')->select('region', DB::raw('count(*) as count'))->groupBy('region')->get();
foreach ($wh as $w) {
    echo $w->region . ": " . $w->count . "\n";
}
echo "---TOTAL: " . DB::table('warehouses')->count() . " magasins\n";

$products = DB::table('products')->get(['name', 'category']);
echo "\n---PRODUITS---\n";
foreach ($products as $p) {
    echo $p->name . " (" . $p->category . ")\n";
}
