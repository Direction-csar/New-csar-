<?php

use Illuminate\Support\Facades\Route;

// API Mobile pour l'application de collecte SIM
Route::prefix('mobile/v1')->name('mobile.api.v1.')->group(function () {
    
    // Routes publiques (sans authentification)
    Route::post('/login', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'login'])->name('login');
    
    // Routes protégées (nécessitent l'authentification mobile)
    Route::middleware('auth:sanctum', 'ability:mobile-collect')->group(function () {
        
        // Profil et authentification
        Route::get('/profile', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProfile'])->name('profile');
        Route::post('/logout', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'logout'])->name('logout');
        
        // Données de référence
        Route::get('/markets', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getMarkets'])->name('markets');
        Route::get('/products', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProducts'])->name('products');
        
        // Collecte de données
        Route::post('/collections', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'submitCollection'])->name('collections.submit');
        Route::get('/collections/pending', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getPendingCollections'])->name('collections.pending');
        
        // Synchronisation
        Route::post('/sync', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'syncPendingCollections'])->name('sync');
        Route::get('/sync/history', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getSyncHistory'])->name('sync.history');
    });
});
