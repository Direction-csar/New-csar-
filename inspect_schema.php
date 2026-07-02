<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

foreach (['warehouses', 'stock_types', 'products', 'stocks'] as $t) {
    echo "=== $t ===\n";
    if (Schema::hasTable($t)) {
        echo implode(', ', Schema::getColumnListing($t)) . "\n";
        echo "rows: " . DB::table($t)->count() . "\n";
    } else {
        echo "(table absente)\n";
    }
    echo "\n";
}

echo "=== roles distincts users ===\n";
print_r(DB::table('users')->select('role')->distinct()->pluck('role')->toArray());
