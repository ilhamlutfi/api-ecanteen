<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers', SupplierController::class);

    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('user-list', [UserController::class, 'index']);

Route::apiResource('products', ProductController::class);

Route::post('registration', [AuthController::class, 'registration']);
Route::post('login', [AuthController::class, 'login']);

