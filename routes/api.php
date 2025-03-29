<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* APP_URL/api/ */
Route::get('/', function () {
    return response()->json([
        'message' => 'Order Stock Management API!',
        'status' => 200,
    ], 200);
});

/* APP_URL/api/login APP_URL/api/logout APP_URL/api/user */
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

/* Versioned API routes: APP_URL/api/v1/ */
Route::prefix('v1')->group(function () {
    require __DIR__.'/api_v1.php';
});
