<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosApiController;

// Rutas API para productos
Route::prefix('api')->group(function () {
    Route::get('/productos', [ProductosApiController::class, 'index']);
    Route::get('/productos/{id}', [ProductosApiController::class, 'show']);
});
