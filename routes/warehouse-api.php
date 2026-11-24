<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\WarehouseKeeperController;

Route::prefix('v1')->name('warehouse.api.v1.')->group(function () {

    // Authentification (pas de middleware, mais rate-limited)
    Route::post('/login', [WarehouseKeeperController::class, 'login'])->name('login')
        ->middleware('throttle:5,1');
    // Authentification APK Inspecteur (rôle inspecteur uniquement)
    Route::post('/login/inspecteur', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'loginInspector'])->name('login.inspector')
        ->middleware('throttle:5,1');

    // Routes protégées (token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/warehouses', [WarehouseKeeperController::class, 'getWarehouses'])->name('warehouses');
        Route::get('/warehouses/{id}/sheet', [WarehouseKeeperController::class, 'getStockSheet'])->name('sheet');
        Route::post('/movements', [WarehouseKeeperController::class, 'storeMovement'])->name('movements.store');
        Route::post('/sync', [WarehouseKeeperController::class, 'syncMovements'])->name('sync');
        Route::get('/products', [WarehouseKeeperController::class, 'getProducts'])->name('products');
        Route::post('/transfers', [WarehouseKeeperController::class, 'storeTransfer'])->name('transfers.store');
        Route::post('/inventory', [WarehouseKeeperController::class, 'storeInventory'])->name('inventory.store');
        Route::get('/stock-status', [WarehouseKeeperController::class, 'getStockStatus'])->name('stock-status');
        Route::get('/alerts', [WarehouseKeeperController::class, 'getAlerts'])->name('alerts');
        Route::get('/receipt/{reference}', [WarehouseKeeperController::class, 'getReceipt'])->name('receipt');
        Route::get('/stats', [WarehouseKeeperController::class, 'getStats'])->name('stats');
        // Missions de sortie de stock affectées par le DTL
        Route::get('/missions', [WarehouseKeeperController::class, 'getMissions'])->name('missions');
        Route::post('/missions/{id}/report', [WarehouseKeeperController::class, 'reportMission'])->name('missions.report');

        // Circuit opérationnel DG → DTL → Inspecteur → Magasinier (APK Magasinier + APK Inspecteur)
        Route::get('/operations', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'index'])->name('operations.index');
        Route::get('/operations/magasinier-candidates', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'magasinierCandidates'])->name('operations.magasinier-candidates');
        Route::get('/operations/{id}', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'show'])->name('operations.show');
        Route::post('/operations/{id}/transmit', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'transmit'])->name('operations.transmit');
        Route::post('/operations/{id}/execute', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'execute'])->name('operations.execute');
        Route::post('/operations/{id}/verify', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'verify'])->name('operations.verify');
        Route::post('/operations/{id}/anomaly', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'anomaly'])->name('operations.anomaly');
        Route::post('/operations/{id}/reject', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'reject'])->name('operations.reject');
        Route::get('/stock-ops/status', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'stockStatus'])->name('operations.stock-status');
        Route::get('/stock-ops/warehouses', [\App\Http\Controllers\Api\Mobile\StockOperationApiController::class, 'warehouses'])->name('operations.warehouses');
    });
});
