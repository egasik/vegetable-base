<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Магазин') — Овощная база</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('css/store.css') }}"><script src="{{ asset('js/store.js') }}" defer></script></head>
<body>
<header class="store-header"><nav class="store-nav" aria-label="Основная навигация"><a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">о.</span>Овощная база</a><div class="nav-links"><a href="{{ route('catalog') }}">Каталог</a><a href="{{ route('articles.index') }}">Справочник</a>@auth<a href="{{ route('cart.index') }}">Корзина <span class="cart-count" data-cart-count>{{ count(session('cart', [])) }}</span></a><details class="account-menu"><summary>{{ auth()->user()->name }}</summary><div><a href="{{ route('profile.edit') }}">Личный кабинет</a>@if(auth()->user()->role === 'admin')<a href="{{ route('admin.products.index') }}">Управление</a>@endif<form action="{{ route('logout') }}" method="POST">@csrf<button>Выйти</button></form></div></details>@else<a class="button button-small" href="{{ route('login') }}">Войти</a>@endauth</div></nav></header>
<main class="store-main">@if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif @if(session('error'))<p class="notice notice-error" role="alert">{{ session('error') }}</p>@endif @if($errors->any())<div class="notice notice-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif @yield('content')</main>
<div id="store-toast" class="toast" role="status" aria-live="polite" hidden></div>
<footer class="store-footer"><a class="brand" href="{{ route('home') }}">Овощная база</a><p>Овощи и фрукты · оптом и в розницу</p><a href="{{ route('catalog') }}">Выбрать продукты →</a><small>© {{ date('Y') }}</small></footer>
</body></html>

