<?php
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\ProductController as StoreProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     return view('store.index');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/',[HomeController::class, 'index'])->name('home');

Route::name('store.')
    ->group(function () {
        Route::get('/',[HomeController::class, 'index'])->name('home');
        Route::get('/store',[StoreProductController::class, 'index'])->name('index');
        Route::get('/store/product/{id}',[StoreProductController::class, 'show'])->name('product');
        //
    });

Route::middleware(['auth'])
    ->prefix('cart')
    ->name('cart.')
    ->group(function () {

        Route::get('/',[CartController::class, 'index'])->name('index');
        Route::post('/add',[CartController::class, 'add'])->name('add');
        Route::post('/dec',[CartController::class, 'decrease'])->name('decrease');
        Route::post('/remove',[CartController::class, 'remove'])->name('remove');
        Route::post('/clear',[CartController::class, 'clear'])->name('clear');
        Route::post('/checkout',[CartController::class, 'checkout'])->name('checkout');
});

Route::middleware(['auth','admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('orders', OrderController::class);
        Route::resource('users', UserController::class)->except(['create', 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
