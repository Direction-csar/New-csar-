<?php
$file = '/var/www/csar/routes/api.php';
$content = file_get_contents($file);

$needle = "Route::get('/warehouse/v1/stats', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getStats'])->name('warehouse.api.v1.stats');";
$addition = "\n    Route::get('/warehouse/v1/products', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getProducts'])->name('warehouse.api.v1.products');";

if (strpos($content, 'warehouse.api.v1.products') !== false) {
    echo "Route déjà présente\n";
    exit;
}

if (strpos($content, $needle) !== false) {
    $content = str_replace($needle, $needle . $addition, $content);
    file_put_contents($file, $content);
    echo "Route products ajoutée\n";
} else {
    echo "Ancre 'stats' introuvable\n";
}
