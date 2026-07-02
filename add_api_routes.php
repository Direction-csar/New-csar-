<?php
$file = '/var/www/csar/routes/api.php';
$content = file_get_contents($file);

$old = "    Route::get('/warehouse/v1/products', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getProducts'])->name('warehouse.api.v1.products');\n});";
$new = "    Route::get('/warehouse/v1/products', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getProducts'])->name('warehouse.api.v1.products');
    Route::post('/warehouse/v1/transfers', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'storeTransfer'])->name('warehouse.api.v1.transfers.store');
    Route::post('/warehouse/v1/inventory', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'storeInventory'])->name('warehouse.api.v1.inventory.store');
    Route::get('/warehouse/v1/stock-status', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getStockStatus'])->name('warehouse.api.v1.stock-status');
    Route::get('/warehouse/v1/alerts', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getAlerts'])->name('warehouse.api.v1.alerts');
    Route::get('/warehouse/v1/receipt/{reference}', [App\\Http\\Controllers\\Api\\Mobile\\WarehouseKeeperController::class, 'getReceipt'])->name('warehouse.api.v1.receipt');
});";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "Routes added successfully.\n";
} else {
    echo "Pattern not found. Checking file end...\n";
    echo substr($content, -500);
}
