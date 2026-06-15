<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Овощная база - @yield('title', 'Магазин')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-purple: #422168;
            --color-light-lime: #E8FC8C;
            --color-lime: #CAF204;
            --color-mint: #00F3B5;
            --color-green: #0D7D4C;
        }

        /* Базовые анимации для "живости" */
        .btn-animated {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-animated:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px -5px rgba(66, 33, 104, 0.3);
        }

        .btn-animated:active {
            transform: translateY(0) scale(0.98);
        }

        /* Эффект "волны" или подсветки при наведении на карточку */
        .card-hover {
            transition: all 0.4s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(13, 125, 76, 0.4);
            border-color: var(--color-lime);
        }

        /* Пульсация для акцентных элементов */
        @keyframes pulse-lime {
            0%, 100% { box-shadow: 0 0 0 0 rgba(202, 242, 4, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(202, 242, 4, 0); }
        }
        
        .pulse-hover:hover {
            animation: pulse-lime 1.5s infinite;
        }

        /* Градиентный фон для шапки */
        .header-gradient {
            background: linear-gradient(135deg, var(--color-green) 0%, var(--color-purple) 100%);
        }
    </style>
</head>
<body class="bg-[#E8FC8C] text-[#422168] min-h-screen flex flex-col">
    <nav class="header-gradient p-4 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight hover:text-[#CAF204] transition-colors duration-300">
                🥬 Овощная база
            </a>
            <div class="flex items-center space-x-6 font-semibold">
                <a href="{{ route('catalog') }}" class="hover:text-[#00F3B5] transition-colors duration-300">Каталог</a>
                @auth
                    <a href="{{ route('profile.edit') }}" class="hover:text-[#00F3B5] transition-colors duration-300">Кабинет</a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.products.index') }}" class="bg-[#CAF204] text-[#422168] px-4 py-1 rounded-full btn-animated">Админ</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-[#00F3B5] transition-colors duration-300">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="bg-[#00F3B5] text-[#422168] px-4 py-1 rounded-full btn-animated">Войти</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6 flex-grow">
        @yield('content')
    </main>

    <footer class="bg-[#422168] text-[#E8FC8C] p-4 text-center mt-auto">
        <p>&copy; {{ date('Y') }} Овощная база. Все права защищены.</p>
    </footer>
</body>
</html>