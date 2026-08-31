<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// E-commerce API (token protected)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('products', [\App\Http\Controllers\Api\ProductApiController::class, 'index']);
    Route::get('products/{product}', [\App\Http\Controllers\Api\ProductApiController::class, 'show']);
    Route::get('products/{product}/stock', [\App\Http\Controllers\Api\ProductApiController::class, 'checkStock']);
    Route::get('categories', [\App\Http\Controllers\Api\ProductApiController::class, 'categories']);
});
