<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DeliveryPhotoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;

// --- ПУБЛИЧНАЯ ЧАСТЬ (Гость) ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

Route::get('/articles', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/article/{article}', [PublicArticleController::class, 'show'])->name('articles.show');

// --- ЛИЧНЫЙ КАБИНЕТ + ОПЛАТА (Пользователь) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/delivery-photos/{photo}', [DeliveryPhotoController::class, 'show'])->name('delivery-photos.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/checkout', [CheckoutController::class, 'show'])->middleware('verified')->name('checkout.show');
    Route::post('/checkout/address', [CheckoutController::class, 'address'])->middleware(['verified', 'throttle:10,1'])->block(10, 10)->name('checkout.address');
    Route::post('/orders', [OrderController::class, 'store'])->middleware('verified')->block(10, 10)->name('orders.store');

    // Оплата заказов
    Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{order}/process', [PaymentController::class, 'process'])->name('payment.process');
    Route::get('/payment/{order}/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/{order}/fail', [PaymentController::class, 'fail'])->name('payment.fail');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', Admin\ProductController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('articles', Admin\ArticleController::class);

    // Управление заказами
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->withTrashed()->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/comment', [Admin\OrderController::class, 'addComment'])->name('orders.add-comment');
    Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{id}/restore', [Admin\OrderController::class, 'restore'])->name('orders.restore');
    Route::delete('/orders/{id}/force', [Admin\OrderController::class, 'forceDelete'])->name('orders.force-delete');
});

// Webhook от ЮKassa (без авторизации)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
Route::post('/payment/{order}/check-status', [PaymentController::class, 'checkStatus'])->middleware(['auth', 'throttle:10,1'])->name('payment.check-status');
// Подключаем стандартные роуты Breeze (логин, регистрация)
require __DIR__.'/auth.php';

// Корзина
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->block(10, 10)->name('cart.add');
    Route::patch('/cart/{cartKey}', [CartController::class, 'update'])->block(10, 10)->name('cart.update');
    Route::delete('/cart/{cartKey}', [CartController::class, 'remove'])->block(10, 10)->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->block(10, 10)->name('cart.clear');
});
