<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - @yield('title', 'Управление')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-shell bg-[#F9F0E0] text-[#2D3528] min-h-screen flex">
    <!-- Боковое меню -->
    <!-- Боковое меню -->
<aside class="admin-sidebar w-64 bg-[#2D3528] text-white min-h-screen shadow-sm">
    <div class="p-6 border-b border-[#CC3A63]">
        <h1 class="text-2xl font-semibold text-[#A2AB73]">Админ-панель</h1>
        <p class="text-xs text-[#F9F0E0] mt-1">Овощная база</p>
        
        {{-- Профиль администратора --}}
        <div class="mt-4 flex items-center space-x-3">
            <div class="admin-avatar w-12 h-12 rounded-full bg-[#F9F0E0] flex items-center justify-center text-2xl overflow-hidden border-2 border-[#A2AB73]">
                @if(auth()->user()->avatar_path)
                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" class="w-full h-full object-cover">
                @else
                    👤
                @endif
            </div>
            <div>
                <p class="text-sm font-bold text-white">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</p>
                <p class="text-xs text-[#A2AB73]">Администратор</p>
            </div>
        </div>
    </div>

    {{-- Навигация --}}
    <nav class="admin-navigation mt-6" aria-label="Разделы управления">
        <a href="{{ route('admin.products.index') }}" class="block px-6 py-3 text-white hover:bg-[#CC3A63] transition-colors font-semibold {{ request()->routeIs('admin.products.*') ? 'bg-[#CC3A63] admin-nav-active' : '' }}">
            Товары
        </a>
        
        <a href="{{ route('admin.categories.index') }}" class="block px-6 py-3 text-white hover:bg-[#CC3A63] transition-colors font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-[#CC3A63] admin-nav-active' : '' }}">
            Категории
        </a>
        
        <a href="{{ route('admin.orders.index') }}" class="block px-6 py-3 text-white hover:bg-[#CC3A63] transition-colors font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-[#CC3A63] admin-nav-active' : '' }}">
            Заказы
            @php
               $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'paid', 'shipping'])->count();
            @endphp
            @if($pendingOrders > 0)
                <span class="ml-2 bg-[#A2AB73] text-[#2D3528] text-xs font-bold px-2 py-1 rounded-full">
                    {{ $pendingOrders }}
                </span>
            @endif
        </a>
        
        <a href="{{ route('admin.articles.index') }}" class="block px-6 py-3 text-white hover:bg-[#CC3A63] transition-colors font-semibold {{ request()->routeIs('admin.articles.*') ? 'bg-[#CC3A63] admin-nav-active' : '' }}">
            Статьи
        </a>
        
        <div class="border-t border-[#CC3A63] mt-4 pt-4">
            <a href="{{ route('home') }}" class="block px-6 py-3 text-white hover:bg-[#CC3A63] transition-colors font-semibold">
                ← На сайт
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left px-6 py-3 text-red-300 hover:bg-red-900/30 transition-colors font-semibold">
                    Выйти
                </button>
            </form>
        </div>
    </nav>
</aside>

    <!-- Основной контент -->
    <main class="admin-main flex-1 p-8">
        <header class="admin-topbar">
            <span class="admin-eyebrow">Овощная база / Управление</span>
            <span class="admin-section">@yield('title', 'Админ-панель')</span>
        </header>
        @if(session('success'))
            <div class="bg-[#F9F0E0] text-[#2D3528] p-4 rounded-xl mb-6 font-bold border-l-2 border-[#CC3A63]">
                ✅ {{ session('success') }}
            </div>
        @endif
        @php($formErrors = session('errors', $errors))
        @if($formErrors->any())
            <div class="notice notice-error" role="alert">
                <strong>Проверьте заполнение формы</strong>
                <ul>@foreach($formErrors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if(session('error'))<div class="notice notice-error" role="alert">{{ session('error') }}</div>@endif
        @yield('content')
    </main>
</body>
</html>


