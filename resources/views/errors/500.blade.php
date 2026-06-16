<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Ошибка сервера</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --color-purple: #422168; --color-light-lime: #E8FC8C; --color-lime: #CAF204; --color-mint: #00F3B5; --color-green: #0D7D4C; }
        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-animated:hover { transform: translateY(-2px) scale(1.02); }
    </style>
</head>
<body class="bg-[#E8FC8C] min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <h1 class="text-9xl font-black text-[#422168] mb-4">500</h1>
        <h2 class="text-3xl font-bold text-red-600 mb-4">Ошибка на сервере</h2>
        <p class="text-[#422168] text-lg mb-8 max-w-md mx-auto">Что-то пошло не так на нашей кухне. Мы уже работаем над исправлением.</p>
        <a href="{{ route('home') }}" class="inline-block bg-[#0D7D4C] text-white font-black py-3 px-8 rounded-xl btn-animated text-lg">
            🏠 Вернуться на главную
        </a>
    </div>
</body>
</html>