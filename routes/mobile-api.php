<?php

use Illuminate\Support\Facades\Route;

// API Mobile pour l'application de collecte SIM
Route::prefix('v1')->name('mobile.api.v1.')->group(function () {

    // Authentification
    Route::post('/login', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'login'])->name('login');
    Route::post('/logout', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'logout'])->name('logout');

    // Données de référence (marchés, produits)
    Route::get('/markets', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getMarkets'])->name('markets');
    Route::get('/products', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProducts'])->name('products');

    // Profil collecteur
    Route::get('/profile', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProfile'])->name('profile');

    // Collectes
    Route::post('/collections', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'submitCollection'])->name('collections.submit');
    Route::get('/collections', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getCollections'])->name('collections.index');
    Route::get('/collections/pending', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getPendingCollections'])->name('collections.pending');

    // Synchronisation
    Route::post('/sync', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'syncPendingCollections'])->name('sync');
    Route::get('/sync/history', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getSyncHistory'])->name('sync.history');

    // Localisation en temps réel
    Route::post('/location', [App\Http\Controllers\Api\Mobile\CollectorLocationController::class, 'updateLocation'])->name('location.update');

    // Stats du collecteur
    Route::get('/stats', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getCollectorStats'])->name('stats');
});
