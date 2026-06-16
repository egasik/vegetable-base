<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Овощная база</title>
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
        }
        .btn-animated:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 10px 20px -5px rgba(66, 33, 104, 0.3);
        }
        .btn-animated:active { transform: translateY(0) scale(0.98); }
        .input-focus:focus {
            border-color: var(--color-lime);
            box-shadow: 0 0 0 3px rgba(202, 242, 4, 0.3);
            outline: none;
        }
    </style>
</head>
<body class="bg-[#E8FC8C] min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border-4 border-[#00F3B5]">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-[#422168]">🌱 Регистрация</h1>
            <p class="text-[#0D7D4C] mt-2 font-semibold">Создайте аккаунт покупателя</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Имя -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Имя</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Email адрес</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
            </div>

            <!-- Пароль -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Пароль</label>
                <input type="password" name="password" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
            </div>

            <!-- Подтверждение пароля -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-[#422168] mb-2">Подтвердите пароль</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
            </div>

            <!-- Кнопка регистрации -->
            <button type="submit" class="w-full bg-[#CAF204] text-[#422168] font-black py-3 rounded-xl btn-animated text-lg mb-4">
                Зарегистрироваться
            </button>

            <!-- Ссылка на вход -->
            <div class="text-center text-sm text-[#422168]">
                Уже есть аккаунт? 
                <a href="{{ route('login') }}" class="font-black text-[#0D7D4C] hover:text-[#422168] underline decoration-[#CAF204] decoration-2 underline-offset-4 transition-all">
                    Войти
                </a>
            </div>
        </form>
    </div>
</body>
</html>