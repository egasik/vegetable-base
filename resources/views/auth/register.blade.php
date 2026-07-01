<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Овощная база</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/imask@7.1.3/dist/imask.min.js"></script>
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
        .phone-prefix {
            background-color: #1a1a1a;
            color: #00F3B5;
            padding: 0.75rem 0.5rem;
            border-radius: 0.75rem 0 0 0.75rem;
            font-weight: bold;
            user-select: none;
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

            <!-- Фамилия -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Фамилия *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required autofocus
       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all"
       pattern="[А-ЯЁ][а-яА-ЯёЁ]*" title="Начинается с заглавной буквы, только буквы">  </div>

            <!-- Имя -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Имя *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all"
       pattern="[А-ЯЁ][а-яА-ЯёЁ]*" title="Начинается с заглавной буквы, только буквы"> </div>

            <!-- Отчество -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Отчество</label>
                <input type="text" name="middle_name" value="{{ old('middle_name') }}"
       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all"
       pattern="[А-ЯЁ][а-яА-ЯёЁ]*" title="Начинается с заглавной буквы, только буквы"> </div>

            <!-- Телефон с маской -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Номер телефона *</label>
                <div class="flex">
                    <span class="phone-prefix">+7</span>
                    <input type="text" name="phone" id="phone-input" value="{{ old('phone') }}" required
                           placeholder="(9__)-___-__-__"
                           class="flex-1 border-2 border-[#E8FC8C] border-l-0 p-3 rounded-r-xl input-focus transition-all font-mono">
                </div>
                <p class="text-xs text-gray-500 mt-1">Формат: +7-(9XX)-XXX-XX-XX</p>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Email адрес *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
            </div>

                        <!-- Пароль -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#422168] mb-2">Пароль *</label>
                <input type="password" name="password" id="password" required minlength="8"
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
                <ul class="text-xs text-gray-500 mt-2 space-y-1">
                    <li id="pw-length" class="flex items-center gap-1">
                        <span>○</span> Минимум 8 символов
                    </li>
                    <li id="pw-letter" class="flex items-center gap-1">
                        <span>○</span> Минимум 1 буква
                    </li>
                    <li id="pw-number" class="flex items-center gap-1">
                        <span>○</span> Минимум 1 цифра
                    </li>
                    <li id="pw-special" class="flex items-center gap-1">
                        <span>○</span> Минимум 1 спецсимвол (@$!%*#?&)
                    </li>
                </ul>
            </div>

            <!-- Подтверждение пароля -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-[#422168] mb-2">Подтвердите пароль *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                       class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl input-focus transition-all">
                <p id="pw-match" class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <span>○</span> Пароли совпадают
                </p>
            </div>

            <!-- Кнопка регистрации -->
            <button type="submit" id="submit-btn" class="w-full bg-[#CAF204] text-[#422168] font-black py-3 rounded-xl btn-animated text-lg mb-4">
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

    <script>
        // Маска для телефона
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone-input');
            
            IMask(phoneInput, {
                mask: '(900)-000-00-00',
                lazy: false,
                overwrite: true,
                definitions: {
                    '9': /[9]/, // Первая цифра всегда 9
                    '0': /\d/   // Остальные - любые цифры
                }
            });

            // При отправке формы добавляем +7- в начало
            phoneInput.closest('form').addEventListener('submit', function(e) {
                if (phoneInput.value) {
                    phoneInput.value = '+7-' + phoneInput.value;
                }
            });
        });
                // Валидация пароля в реальном времени
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submit-btn');

        function validatePassword() {
            const password = passwordInput.value;
            const confirm = passwordConfirmInput.value;

            // Проверка длины
            const lengthOk = password.length >= 8;
            updateIndicator('pw-length', lengthOk);

            // Проверка наличия буквы
            const letterOk = /[a-zA-Zа-яА-ЯёЁ]/.test(password);
            updateIndicator('pw-letter', letterOk);

            // Проверка наличия цифры
            const numberOk = /[0-9]/.test(password);
            updateIndicator('pw-number', numberOk);

            // Проверка наличия спецсимвола
            const specialOk = /[@$!%*#?&]/.test(password);
            updateIndicator('pw-special', specialOk);

            // Проверка совпадения паролей
            const matchOk = password === confirm && password.length > 0;
            updateIndicator('pw-match', matchOk);

            // Блокировка кнопки, если все условия не выполнены
            const allValid = lengthOk && letterOk && numberOk && specialOk && matchOk;
            submitBtn.disabled = !allValid;
            submitBtn.style.opacity = allValid ? '1' : '0.5';
            submitBtn.style.cursor = allValid ? 'pointer' : 'not-allowed';
        }

        function updateIndicator(elementId, isValid) {
            const el = document.getElementById(elementId);
            if (isValid) {
                el.innerHTML = '<span class="text-[#0D7D4C]">✓</span> ' + el.textContent.substring(2);
                el.classList.remove('text-gray-500');
                el.classList.add('text-[#0D7D4C]', 'font-bold');
            } else {
                el.innerHTML = '<span>○</span> ' + el.textContent.substring(2);
                el.classList.remove('text-[#0D7D4C]', 'font-bold');
                el.classList.add('text-gray-500');
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        passwordConfirmInput.addEventListener('input', validatePassword);

        validatePassword();
    </script>
</body>
</html>