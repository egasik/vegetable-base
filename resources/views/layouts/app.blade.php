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
                <a href="{{ route('articles.index') }}" class="hover:text-[#00F3B5] transition-colors duration-300">📖 Справочник</a>
                
                @auth
                    {{-- Блок профиля пользователя --}}
                    <div class="relative group">
                        <button class="flex items-center space-x-2 hover:opacity-80 transition-opacity cursor-pointer">
                            {{-- Аватар --}}
                            <div class="w-10 h-10 rounded-full bg-[#E8FC8C] flex items-center justify-center text-xl overflow-hidden border-2 border-[#CAF204]">
                                @if(auth()->user()->avatar_path)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[#422168]">👤</span>
                                @endif
                            </div>
                            {{-- Имя и фамилия --}}
                            <div class="text-left hidden md:block">
                                <p class="text-sm font-bold text-white leading-tight">
                                    {{ auth()->user()->name }} {{ auth()->user()->last_name }}
                                </p>
                                <p class="text-xs text-[#CAF204] leading-tight">
                                    @if(auth()->user()->role === 'admin')
                                        Администратор
                                    @else
                                        Покупатель
                                    @endif
                                </p>
                            </div>
                        </button>
                        
                        {{-- Выпадающее меню --}}
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border-2 border-[#E8FC8C] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="p-4 border-b-2 border-[#E8FC8C] bg-[#E8FC8C]/30 rounded-t-xl">
                                <p class="font-bold text-[#422168]">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-xs text-gray-600">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-[#422168] hover:bg-[#E8FC8C] rounded-lg transition-colors font-semibold">
                                     Личный кабинет
                                </a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.products.index') }}" class="block px-4 py-2 text-[#422168] hover:bg-[#CAF204] rounded-lg transition-colors font-semibold">
                                         Админ-панель
                                    </a>
                                @endif
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-semibold">
                                         Выйти
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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