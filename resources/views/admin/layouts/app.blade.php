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
    <aside class="w-64 bg-[#422168] text-white min-h-screen shadow-2xl">
        <div class="p-6 border-b border-[#0D7D4C]">
            <h1 class="text-2xl font-black text-[#CAF204]">🥬 Админ-панель</h1>
            <p class="text-xs text-[#00F3B5] mt-1">Овощная база</p>
        </div>
        <nav class="p-4 space-y-2">
            <a href="{{ route('home') }}" target="_blank" class="sidebar-link block py-3 px-4 rounded-lg text-[#E8FC8C]">
                🌐 На сайт
            </a>
            <a href="{{ route('admin.products.index') }}" class="sidebar-link block py-3 px-4 rounded-lg {{ request()->routeIs('admin.products.*') ? 'active text-[#422168] font-bold' : 'text-[#E8FC8C]' }}">
                🥕 Товары
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sidebar-link block py-3 px-4 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'active text-[#422168] font-bold' : 'text-[#E8FC8C]' }}">
                📂 Категории
            </a>
            <a href="{{ route('admin.articles.index') }}" class="sidebar-link block py-3 px-4 rounded-lg {{ request()->routeIs('admin.articles.*') ? 'active text-[#422168] font-bold' : 'text-[#E8FC8C]' }}">
                📖 Справочник
            </a>
            <form action="{{ route('logout') }}" method="POST" class="mt-8">
                @csrf
                <button type="submit" class="w-full sidebar-link py-3 px-4 rounded-lg text-[#E8FC8C] text-left">
                    🚪 Выйти
                </button>
            </form>
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