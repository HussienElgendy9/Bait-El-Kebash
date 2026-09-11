<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EmailVerificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\PhoneController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Middleware\RequireSpaSession;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::middleware(RequireSpaSession::class)->group(function () {
        Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:api-register');
        Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:api-login');
        Route::post('auth/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:api-login');
        Route::post('auth/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:api-login');
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AccountController::class, 'show']);
        Route::patch('me', [AccountController::class, 'update']);
        Route::middleware(RequireSpaSession::class)->group(function () {
            Route::get('cart', [CartController::class, 'show']);
            Route::post('cart/items', [CartController::class, 'store']);
            Route::patch('cart/items/{product}', [CartController::class, 'update']);
            Route::delete('cart/items/{product}', [CartController::class, 'destroyItem'])->whereNumber('product');
            Route::delete('cart', [CartController::class, 'destroy']);
            Route::put('me/password', [AccountController::class, 'password']);
            Route::post('auth/confirm-password', [AccountController::class, 'confirm'])->middleware('throttle:api-login');
            Route::delete('me', [AccountController::class, 'destroy']);
        });
        Route::post('me/phone/request', [PhoneController::class, 'store'])->middleware('throttle:phone-request');
        Route::post('me/phone/verify', [PhoneController::class, 'verify'])->middleware('throttle:phone-verify');
        Route::post('me/email/verification-notification', [EmailVerificationController::class, 'store'])->middleware('throttle:6,1');
        Route::get('auth/verify-email/{id}/{hash}', [EmailVerificationController::class, 'show'])->middleware('signed')->name('api.v1.verification.verify');
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::prefix('admin')->name('api.v1.admin.')->middleware('admin')->group(function () {
            Route::get('dashboard', [Admin\DashboardController::class, 'index']);
            Route::apiResource('products', Admin\ProductController::class);
            Route::apiResource('categories', Admin\CategoryController::class);
            Route::apiResource('orders', Admin\OrderController::class)->only(['index', 'show', 'update']);
            Route::patch('orders/{order}/items/{item}', [Admin\OrderController::class, 'updateItem'])->whereNumber('item');
            Route::apiResource('users', Admin\UserController::class)->only(['index', 'show', 'update', 'destroy']);
            Route::get('notifications', [Admin\NotificationController::class, 'index']);
            Route::post('notifications/read-all', [Admin\NotificationController::class, 'readAll']);
            Route::patch('notifications/{notification}', [Admin\NotificationController::class, 'update']);
        });
    });
});
