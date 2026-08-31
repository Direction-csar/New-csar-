<?php

use Illuminate\Support\Facades\Route;

// API Mobile pour l'application de collecte SIM
Route::prefix('v1')->name('mobile.api.v1.')->group(function () {

    // Authentification (publiques)
    Route::post('/login', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'login'])->name('login')
        ->middleware('throttle:5,1');
    Route::post('/logout', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'logout'])->name('logout');

    // Routes protégées (token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Données de référence (marchés, produits)
        Route::get('/markets', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getMarkets'])->name('markets');
        Route::get('/products', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProducts'])->name('products');
        Route::get('/product-categories', [App\Http\Controllers\Api\Mobile\SimCollectionController::class, 'getProductCategories'])->name('product-categories');

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

        // Distribution (aide alimentaire / bons-matière)
        Route::prefix('distribution')->group(function () {
            Route::get('/sync', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'sync']);
            Route::post('/beneficiaires', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'storeBeneficiaire']);
            Route::post('/beneficiaires/batch', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'batch']);
            Route::post('/scan', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'scan']);
            Route::get('/tickets/{code}', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'ticket']);

            // Nouvelles routes pour le système complet
            Route::get('/events', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'events']);
            Route::get('/events/{id}/plannings', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'eventPlannings']);
            Route::get('/plannings', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'myPlannings']);
            Route::get('/plannings/{id}/beneficiaries', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'planningBeneficiaries']);
            Route::post('/beneficiaries/{id}/validate', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'validateBeneficiary']);
            Route::post('/beneficiaries/{id}/ticket', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'generateTicket']);
            Route::post('/tickets/{qrToken}/collect', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'collectKit']);
            Route::get('/check-duplicate', [App\Http\Controllers\Api\Mobile\DistributionController::class, 'checkDuplicate']);
        });
    });
});
