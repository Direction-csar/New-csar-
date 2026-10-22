<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\ComptableApiController;

/**
 * API mobile « CSAR Comptable des matières ».
 * Authentification Sanctum ; le rôle « comptable » est revérifié
 * côté serveur sur chaque endpoint (denyNonComptable).
 */
Route::prefix('v1')->name('comptable.api.v1.')->group(function () {

    Route::post('/login', [ComptableApiController::class, 'login'])
        ->name('login')->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [ComptableApiController::class, 'dashboard'])->name('dashboard');
        Route::get('/movements', [ComptableApiController::class, 'movements'])->name('movements');
        Route::get('/movements/{id}', [ComptableApiController::class, 'movement'])->name('movements.show');
        Route::post('/movements/{id}/validate', [ComptableApiController::class, 'validateMovement'])->name('movements.validate');
        Route::post('/movements/{id}/reject', [ComptableApiController::class, 'rejectMovement'])->name('movements.reject');
        Route::get('/warehouses', [ComptableApiController::class, 'warehouses'])->name('warehouses');
        // Carburant (soldes par produit, unité 'L')
        Route::get('/fuel/summary', [ComptableApiController::class, 'fuelSummary'])->name('fuel.summary');
        // Matériel : affectations aux agents, retours et pertes
        Route::get('/equipment-assignments', [ComptableApiController::class, 'equipmentAssignments'])->name('equipment.index');
        Route::post('/equipment-assignments', [ComptableApiController::class, 'storeEquipmentAssignment'])->name('equipment.store');
        Route::post('/equipment-assignments/{id}/close', [ComptableApiController::class, 'closeEquipmentAssignment'])->name('equipment.close');
    });
});
