<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('store.home');
Route::get('/store', [ProductController::class, 'index'])->name('store.index');
Route::get('/store/product/{id}', [ProductController::class, 'show'])->name('store.product');

Route::prefix('cart')->name('cart.')->middleware('auth')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    foreach (['add' => 'add', 'dec' => 'decrease', 'remove' => 'remove', 'clear' => 'clear', 'checkout' => 'checkout'] as $path => $method) {
        Route::post($path, [CartController::class, $method])->name($path === 'dec' ? 'decrease' : $path);
    }
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('orders', OrderController::class);
    Route::put('orders/{order}/items/{item}', [OrderController::class, 'updateItem'])->name('orders.items.update');
    Route::resource('users', UserController::class)->except(['create', 'store']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
