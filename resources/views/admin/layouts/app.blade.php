<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - @yield('title', 'Управление')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-purple: #422168;
            --color-light-lime: #E8FC8C;
            --color-lime: #CAF204;
            --color-mint: #00F3B5;
            --color-green: #0D7D4C;
        }
        .btn-animated {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-animated:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px -5px rgba(66, 33, 104, 0.3);
        }
        .btn-animated:active { transform: translateY(0) scale(0.98); }
        .card-hover { transition: all 0.4s ease; }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(13, 125, 76, 0.4);
            border-color: var(--color-lime);
        }
        .sidebar-link {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: linear-gradient(90deg, var(--color-lime) 0%, transparent 100%);
            border-left-color: var(--color-purple);
            color: var(--color-purple);
            padding-left: 1.5rem;
        }
    </style>
</head>
<body class="bg-[#E8FC8C] text-[#422168] min-h-screen flex">
    <!-- Боковое меню -->
    <!-- Боковое меню -->
<aside class="w-64 bg-[#422168] text-white min-h-screen shadow-2xl">
    <div class="p-6 border-b border-[#0D7D4C]">
        <h1 class="text-2xl font-black text-[#CAF204]">Админ-панель</h1>
        <p class="text-xs text-[#00F3B5] mt-1">Овощная база</p>
        
        {{-- Профиль администратора --}}
        <div class="mt-4 flex items-center space-x-3">
            <div class="w-12 h-12 rounded-full bg-[#E8FC8C] flex items-center justify-center text-2xl overflow-hidden border-2 border-[#CAF204]">
                @if(auth()->user()->avatar_path)
                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" class="w-full h-full object-cover">
                @else
                    👤
                @endif
            </div>
            <div>
                <p class="text-sm font-bold text-white">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</p>
                <p class="text-xs text-[#CAF204]">Администратор</p>
            </div>
        </div>
    </div>

    {{-- Навигация --}}
    <nav class="mt-6">
        <a href="{{ route('admin.products.index') }}" class="block px-6 py-3 text-white hover:bg-[#0D7D4C] transition-colors font-semibold {{ request()->routeIs('admin.products.*') ? 'bg-[#0D7D4C]' : '' }}">
            Товары
        </a>
        
        <a href="{{ route('admin.categories.index') }}" class="block px-6 py-3 text-white hover:bg-[#0D7D4C] transition-colors font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-[#0D7D4C]' : '' }}">
            Категории
        </a>
        
        <a href="{{ route('admin.orders.index') }}" class="block px-6 py-3 text-white hover:bg-[#0D7D4C] transition-colors font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-[#0D7D4C]' : '' }}">
            Заказы
            @php
               $pendingOrders = \App\Models\Order::whereIn('status', ['pending', 'paid', 'shipping'])->count();
            @endphp
            @if($pendingOrders > 0)
                <span class="ml-2 bg-[#CAF204] text-[#422168] text-xs font-bold px-2 py-1 rounded-full">
                    {{ $pendingOrders }}
                </span>
            @endif
        </a>
        
        <a href="{{ route('admin.articles.index') }}" class="block px-6 py-3 text-white hover:bg-[#0D7D4C] transition-colors font-semibold {{ request()->routeIs('admin.articles.*') ? 'bg-[#0D7D4C]' : '' }}">
            Статьи
        </a>
        
        <div class="border-t border-[#0D7D4C] mt-4 pt-4">
            <a href="{{ route('home') }}" class="block px-6 py-3 text-white hover:bg-[#0D7D4C] transition-colors font-semibold">
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
    <main class="flex-1 p-8">
        @if(session('success'))
            <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl mb-6 font-bold border-l-8 border-[#0D7D4C]">
                ✅ {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>