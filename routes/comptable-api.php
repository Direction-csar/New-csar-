<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\ComptableApiController;
use App\Http\Controllers\Api\Mobile\FuelApiController;

/**
 * API mobile « CSAR Comptable des matières ».
 * Authentification Sanctum ; le rôle « comptable » est revérifié
 * côté serveur sur chaque endpoint (denyNonComptable).
 */
Route::prefix('v1')->name('comptable.api.v1.')->group(function () {

    Route::post('/login', [ComptableApiController::class, 'login'])
        ->name('login')->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ComptableApiController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [ComptableApiController::class, 'dashboard'])->name('dashboard');
        Route::get('/movements', [ComptableApiController::class, 'movements'])->name('movements');
        Route::get('/movements/{id}', [ComptableApiController::class, 'movement'])->name('movements.show');
        Route::post('/movements/{id}/validate', [ComptableApiController::class, 'validateMovement'])->name('movements.validate');
        Route::post('/movements/{id}/reject', [ComptableApiController::class, 'rejectMovement'])->name('movements.reject');
        Route::get('/warehouses', [ComptableApiController::class, 'warehouses'])->name('warehouses');
        // Carburant (soldes par produit, unité 'L')

        // Carburant — module complet (entrées, sorties, remboursements)
        Route::prefix('fuel')->name('fuel.')->group(function () {
            Route::get('/dashboard', [FuelApiController::class, 'dashboard'])->name('dashboard');

            Route::get('/entries', [FuelApiController::class, 'entriesIndex'])->name('entries.index');
            Route::post('/entries', [FuelApiController::class, 'entriesStore'])->name('entries.store');
            Route::get('/entries/{id}', [FuelApiController::class, 'entriesShow'])->name('entries.show');
            Route::post('/entries/{id}/validate', [FuelApiController::class, 'entriesValidate'])->name('entries.validate');
            Route::post('/entries/{id}/reject', [FuelApiController::class, 'entriesReject'])->name('entries.reject');

            Route::get('/exits', [FuelApiController::class, 'exitsIndex'])->name('exits.index');
            Route::post('/exits', [FuelApiController::class, 'exitsStore'])->name('exits.store');
            Route::get('/exits/{id}', [FuelApiController::class, 'exitsShow'])->name('exits.show');
            Route::post('/exits/{id}/authorize', [FuelApiController::class, 'exitsAuthorize'])->name('exits.authorize');
            Route::post('/exits/{id}/validate', [FuelApiController::class, 'exitsValidate'])->name('exits.validate');
            Route::post('/exits/{id}/reject', [FuelApiController::class, 'exitsReject'])->name('exits.reject');

            Route::get('/reimbursements', [FuelApiController::class, 'reimbsIndex'])->name('reimbs.index');
            Route::post('/reimbursements', [FuelApiController::class, 'reimbsStore'])->name('reimbs.store');
            Route::get('/reimbursements/{id}', [FuelApiController::class, 'reimbsShow'])->name('reimbs.show');
            Route::post('/reimbursements/{id}/submit', [FuelApiController::class, 'reimbsSubmit'])->name('reimbs.submit');
            Route::post('/reimbursements/{id}/review', [FuelApiController::class, 'reimbsReview'])->name('reimbs.review');
            Route::post('/reimbursements/{id}/approve', [FuelApiController::class, 'reimbsApprove'])->name('reimbs.approve');
            Route::post('/reimbursements/{id}/reject', [FuelApiController::class, 'reimbsReject'])->name('reimbs.reject');
            Route::post('/reimbursements/{id}/pay', [FuelApiController::class, 'reimbsPay'])->name('reimbs.pay');
            Route::post('/reimbursements/{id}/close', [FuelApiController::class, 'reimbsClose'])->name('reimbs.close');

            Route::post('/{type}/{id}/attachments', [FuelApiController::class, 'attachmentStore'])
                ->where('type', 'entry|exit|reimb')->name('attachments.store');
            Route::get('/attachments/{id}/download', [FuelApiController::class, 'attachmentDownload'])
                ->name('attachments.download');
            Route::delete('/attachments/{id}', [FuelApiController::class, 'attachmentDelete'])
                ->name('attachments.delete');
            Route::get('/{type}/{id}/pdf', [FuelApiController::class, 'pdf'])
                ->where('type', 'entry|exit|reimb')->name('pdf');
        });
        Route::get('/fuel/summary', [ComptableApiController::class, 'fuelSummary'])->name('fuel.summary');
        // Matériel : affectations aux agents, retours et pertes
        Route::get('/equipment-assignments', [ComptableApiController::class, 'equipmentAssignments'])->name('equipment.index');
        Route::post('/equipment-assignments', [ComptableApiController::class, 'storeEquipmentAssignment'])->name('equipment.store');
        Route::post('/equipment-assignments/{id}/close', [ComptableApiController::class, 'closeEquipmentAssignment'])->name('equipment.close');
    });
});
