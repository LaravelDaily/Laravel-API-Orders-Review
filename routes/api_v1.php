<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderOwnerController;
use App\Http\Controllers\Api\V1\OwnerOrdersController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('orders', OrderController::class);

    Route::get('products', [ProductController::class, 'index']);

    Route::apiResource('owners', OrderOwnerController::class);
    Route::apiResource('owners.orders', OwnerOrdersController::class);
});
