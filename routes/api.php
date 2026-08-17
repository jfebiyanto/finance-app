<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are protected by Sanctum token authentication. Issue a
| token from the web UI (Profile -> API Tokens), then send it via:
|   Authorization: Bearer <token>
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Convenience endpoint to verify a token / fetch the current user.
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    })->name('api.user');

    Route::prefix('v1')->group(function () {
        // Reference data for clients building a transaction entry form.
        Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');

        // Daily transaction data entry (receipt scanning friendly).
        Route::get('/transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
        Route::post('/transactions/bulk', [TransactionController::class, 'bulk'])->name('api.transactions.bulk');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
    });
});
