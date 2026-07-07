<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\WarehouseKeeperController;

Route::prefix('v1')->name('warehouse.api.v1.')->group(function () {

    // Authentification (pas de middleware)
    Route::post('/login', [WarehouseKeeperController::class, 'login'])->name('login');

    // Routes protégées (token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/warehouses', [WarehouseKeeperController::class, 'getWarehouses'])->name('warehouses');
        Route::get('/warehouses/{id}/sheet', [WarehouseKeeperController::class, 'getStockSheet'])->name('sheet');
        Route::post('/movements', [WarehouseKeeperController::class, 'storeMovement'])->name('movements.store');
        Route::post('/sync', [WarehouseKeeperController::class, 'syncMovements'])->name('sync');
        Route::get('/products', [WarehouseKeeperController::class, 'getProducts'])->name('products');
        Route::get('/stock-status', [WarehouseKeeperController::class, 'getStockStatus'])->name('stock-status');
        Route::get('/alerts', [WarehouseKeeperController::class, 'getAlerts'])->name('alerts');
        Route::get('/receipt/{reference}', [WarehouseKeeperController::class, 'getReceipt'])->name('receipt');
        Route::get('/stats', [WarehouseKeeperController::class, 'getStats'])->name('stats');
    });
});
