<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema;
foreach (['warehouses','products'] as $t) {
    echo "--- $t ---\n";
    $cols = Schema::getColumns($t);
    foreach ($cols as $c) echo $c['name'] . " : " . $c['type_name'] . "\n";
}
