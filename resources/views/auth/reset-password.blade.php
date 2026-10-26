<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля - Овощная база</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --color-purple: #2D3528; --color-light-lime: #F9F0E0; --color-lime: #A2AB73; --color-mint: #F9F0E0; --color-green: #CC3A63; }
        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-animated:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 20px -5px rgba(66, 33, 104, 0.3); }
        .input-focus:focus { border-color: var(--color-lime); box-shadow: 0 0 0 3px rgba(202, 242, 4, 0.3); outline: none; }
    </style>
</head>
<body class="bg-[#F9F0E0] min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-sm w-full max-w-md border border-[#F9F0E0]">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold text-[#2D3528]">🔄 Новый пароль</h1>
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

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#2D3528] mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                       class="w-full border-2 border-[#F9F0E0] p-3 rounded-xl input-focus transition-all">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#2D3528] mb-2">Новый пароль</label>
                <input type="password" name="password" required
                       class="w-full border-2 border-[#F9F0E0] p-3 rounded-xl input-focus transition-all">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-[#2D3528] mb-2">Подтвердите пароль</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border-2 border-[#F9F0E0] p-3 rounded-xl input-focus transition-all">
            </div>
            <button type="submit" class="w-full bg-[#A2AB73] text-[#2D3528] font-semibold py-3 rounded-xl btn-animated text-lg">
                Сбросить пароль
            </button>
        </form>
    </div>
</body>
</html>
