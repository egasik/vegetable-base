<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Восстановление пароля - Овощная база</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --color-purple: #2D3528; --color-light-lime: #F9F0E0; --color-lime: #A2AB73; --color-mint: #F9F0E0; --color-green: #CC3A63; }
        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-animated:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 20px -5px rgba(66, 33, 104, 0.3); }
        .input-focus:focus { border-color: var(--color-lime); box-shadow: 0 0 0 3px rgba(202, 242, 4, 0.3); outline: none; }
    </style>
</head>
<body class="bg-[#F9F0E0] min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-sm w-full max-w-md border border-[#CC3A63]">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-[#2D3528]">🔑 Восстановление пароля</h1>
            <p class="text-sm text-gray-600 mt-2">Забыли пароль? Без проблем. Введите ваш email, и мы пришлем ссылку для сброса.</p>
        </div>

        @if (session('status'))
            <div class="bg-[#F9F0E0] text-[#2D3528] p-4 rounded-xl mb-4 font-bold text-sm">
                ✅ {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-6">
                <label class="block text-sm font-bold text-[#2D3528] mb-2">Email адрес</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border-2 border-[#F9F0E0] p-3 rounded-xl input-focus transition-all">
            </div>
            <button type="submit" class="w-full bg-[#CC3A63] text-white font-semibold py-3 rounded-xl btn-animated text-lg mb-4">
                Отправить ссылку
            </button>
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-[#2D3528] hover:text-[#CC3A63] font-bold transition-colors">← Вернуться ко входу</a>
            </div>
        </form>
    </div>
</body>
</html>
