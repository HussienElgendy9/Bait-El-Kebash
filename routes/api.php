<?php

use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api_v1.php';
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:api-register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user', [AuthController::class, 'update']);
    Route::post('/user/phone/request', [AuthController::class, 'requestPhoneChange'])->middleware('throttle:phone-request');
    Route::post('/user/phone/verify', [AuthController::class, 'verifyPhoneChange'])->middleware('throttle:phone-verify');

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/products', [ProductController::class, 'index']);
    });
