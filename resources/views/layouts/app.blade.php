<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Магазин') — Овощная база</title>
    <meta name="description" content="@yield('description', 'Овощи и фрукты для дома и бизнеса. Покупайте в розницу по весу или оптом упаковками в интернет-магазине «Овощная база».')">
    <meta property="og:title" content="@yield('title', 'Овощная база')"><meta property="og:type" content="website"><meta property="og:locale" content="ru_RU"><meta property="og:image" content="{{ asset('images/logo.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/store.css') }}"><link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <script src="{{ asset('js/store.js') }}" defer></script><script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}" defer></script>
</head>
<body class="harvest-store">
<a class="skip-link" href="#main-content">Перейти к содержимому</a>
<header class="store-header"><nav class="store-nav" aria-label="Основная навигация">
    <a class="brand" href="{{ route('home') }}"><span class="brand-logo"><img src="{{ asset('images/favicon.svg') }}" alt="" width="48" height="48" fetchpriority="high"></span><span>Овощная база<small>Для дома и бизнеса</small></span></a>
    <button class="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="store-menu" aria-label="Открыть меню"><span></span><span></span></button>
    <div class="nav-links" id="store-menu">
        <a href="{{ route('catalog') }}" @if(request()->routeIs('catalog', 'product.show')) aria-current="page" @endif>Каталог</a>
        <a href="{{ route('articles.index') }}" @if(request()->routeIs('articles.*')) aria-current="page" @endif>Справочник</a>
        @auth<details class="account-menu"><summary>{{ auth()->user()->name }}</summary><div><a href="{{ route('profile.edit') }}">Личный кабинет</a>@if(auth()->user()->role === 'admin')<a href="{{ route('admin.products.index') }}">Управление</a>@endif<form action="{{ route('logout') }}" method="POST">@csrf<button>Выйти</button></form></div></details>@else<a href="{{ route('login') }}" @if(request()->routeIs('login', 'register')) aria-current="page" @endif>Войти</a>@endauth
        <a class="nav-basket" href="{{ route('cart.index') }}" @if(request()->routeIs('cart.*', 'checkout.*')) aria-current="page" @endif>Корзина <span class="cart-count" data-cart-count>{{ count(session('cart', [])) }}</span><span aria-hidden="true">↗</span></a>
    </div>
</nav></header>
<main class="store-main" id="main-content">@if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif @if(session('error'))<p class="notice notice-error" role="alert">{{ session('error') }}</p>@endif @if($errors->any() && !View::hasSection('inline-errors'))<div class="notice notice-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif @yield('content')</main>
<div id="store-toast" class="toast" role="status" aria-live="polite" hidden></div>
<footer class="store-footer"><div><a class="brand" href="{{ route('home') }}">Овощная база</a><p>Хорошие продукты для ваших планов.</p></div><nav aria-label="Навигация в подвале"><a href="{{ route('catalog') }}">Каталог</a><a href="{{ route('articles.index') }}">Справочник</a><a href="{{ route('cart.index') }}">Перейти в корзину ↗</a></nav><small>Иркутск и Иркутская область<br>© {{ date('Y') }} Овощная база</small></footer>
</body></html>
