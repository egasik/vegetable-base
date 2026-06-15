<?php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ArticleController as PublicArticleController;

// --- ПУБЛИЧНАЯ ЧАСТЬ (Гость) ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

// --- ЛИЧНЫЙ КАБИНЕТ (Пользователь) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

// --- АДМИН-ПАНЕЛЬ (Администратор) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', Admin\ProductController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('articles', Admin\ArticleController::class);
});

Route::get('/articles', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/article/{article}', [PublicArticleController::class, 'show'])->name('articles.show');
// Подключаем стандартные роуты Breeze (логин, регистрация)
require __DIR__.'/auth.php';