<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\VerificationController;

// --- ПУБЛИЧНАЯ ЧАСТЬ (Гость) ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

Route::get('/articles', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/article/{article}', [PublicArticleController::class, 'show'])->name('articles.show');

// Webhook от ЮKassa (без авторизации)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Подключаем стандартные роуты Breeze (логин, регистрация)
require __DIR__.'/auth.php';

// --- МАРШРУТЫ ДЛЯ АВТОРИЗОВАННЫХ НО НЕ ПОДТВЕРЖДЁННЫХ ---
Route::middleware(['auth'])->group(function () {
    // Подтверждение email (должно быть ДО middleware verified, чтобы не было бесконечного редиректа)
    Route::get('/profile/verify-email', [VerificationController::class, 'showEmailConfirmForm'])->name('profile.verify-email');
    Route::post('/profile/verify-email', [VerificationController::class, 'confirmEmail'])->name('profile.verify-email.confirm');
    Route::post('/profile/verify-email/regenerate', [VerificationController::class, 'regenerateEmailCode'])->name('profile.verify-email.regenerate');
    Route::post('/verify/check-code', [VerificationController::class, 'checkCode'])->name('verify.check-code');
});

// --- МАРШРУТЫ ДЛЯ ПОДТВЕРЖДЁННЫХ ПОЛЬЗОВАТЕЛЕЙ ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Личный кабинет
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-email/regenerate', [VerificationController::class, 'regenerateEmailChangeCode'])->name('profile.email-change.regenerate');
Route::post('/profile/change-password/regenerate', [VerificationController::class, 'regeneratePasswordCode'])->name('profile.password-change.regenerate');
    // AJAX-проверка кода
    
    // Смена email
    Route::get('/profile/change-email', [VerificationController::class, 'showEmailChangeForm'])->name('profile.email-change');
    Route::post('/profile/change-email', [VerificationController::class, 'requestEmailChange'])->name('profile.email-change.request');
    Route::post('/profile/change-email/confirm', [VerificationController::class, 'confirmEmailChange'])->name('profile.email-change.confirm');
    
    // Смена пароля
    Route::get('/profile/change-password', [VerificationController::class, 'showPasswordChangeForm'])->name('profile.password-change');
    Route::post('/profile/change-password', [VerificationController::class, 'requestPasswordChange'])->name('profile.password-change.request');
    Route::post('/profile/change-password/confirm', [VerificationController::class, 'confirmPasswordChange'])->name('profile.password-change.confirm');
    
    // Заказы
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    
    // Оплата
    Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{order}/process', [PaymentController::class, 'process'])->name('payment.process');
    Route::get('/payment/{order}/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/{order}/fail', [PaymentController::class, 'fail'])->name('payment.fail');
    Route::post('/payment/{order}/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check-status');
    
    // Корзина
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{cartKey}', [CartController::class, 'update'])->name('cart.update');
    Route::patch('/cart/{cartKey}/type', [CartController::class, 'changeType'])->name('cart.change-type');
    Route::delete('/cart/{cartKey}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
});

// --- АДМИН-ПАНЕЛЬ ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', Admin\ProductController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('articles', Admin\ArticleController::class);
    
    // Управление заказами
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/comment', [Admin\OrderController::class, 'addComment'])->name('orders.add-comment');
    Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::patch('/orders/{id}/restore', [Admin\OrderController::class, 'restore'])->name('orders.restore');
    Route::delete('/orders/{id}/force', [Admin\OrderController::class, 'forceDelete'])->name('orders.force-delete');
});