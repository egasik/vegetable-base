<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Страница не найдена</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --color-purple: #2D3528; --color-light-lime: #F9F0E0; --color-lime: #A2AB73; --color-mint: #F9F0E0; --color-green: #CC3A63; }
        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-animated:hover { transform: translateY(-2px) scale(1.02); }
    </style>
</head>
<body class="bg-[#F9F0E0] min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <h1 class="text-9xl font-semibold text-[#2D3528] mb-4">404</h1>
        <h2 class="text-3xl font-bold text-[#CC3A63] mb-4">Упс! Овощи закончились</h2>
        <p class="text-[#2D3528] text-lg mb-8 max-w-md mx-auto">Страница, которую вы ищете, не существует или была перемещена. Возможно, она еще растет на грядке.</p>
        <a href="{{ route('home') }}" class="inline-block bg-[#CC3A63] text-white font-semibold py-3 px-8 rounded-xl btn-animated text-lg">
            🏠 Вернуться на главную
        </a>
    </div>
</body>
</html>
