(() => {
    const form = document.querySelector('[data-registration]');
    if (!form) return;
    form.noValidate = true;
    const fields = ['last_name', 'name', 'middle_name', 'phone', 'email', 'password', 'password_confirmation'];
    const touched = new Set();
    const summary = form.querySelector('[data-registration-summary]');
    const phone = form.elements.phone;
    const password = form.elements.password;
    const namePattern = /^[А-ЯЁ][А-Яа-яЁё]+(?:[ -][А-ЯЁ][А-Яа-яЁё]+)*$/u;
    const rules = {
        length: value => value.length >= 8 && value.length <= 72,
        letter: value => /[a-zA-Z]/.test(value),
        digit: value => /[0-9]/.test(value),
        special: value => /[@$!%*#?&]/.test(value),
    };
    const showError = (name, message) => {
        const node = form.querySelector(`[data-error="${name}"]`);
        node.textContent = message;
        node.hidden = !message;
        form.elements[name].setAttribute('aria-invalid', String(Boolean(message)));
    };
    const errorFor = name => {
        const input = form.elements[name];
        const value = name.includes('password') ? input.value : input.value.trim();
        if (!value) return name === 'middle_name' ? '' : 'Заполните это поле.';
        if (['name', 'last_name', 'middle_name'].includes(name)) {
            if (value.length < 2 || value.length > 60) return 'Введите от 2 до 60 символов.';
            if (!namePattern.test(value)) return 'Используйте кириллицу с заглавной буквы. В составном имени допустим пробел или дефис.';
        }
        if (name === 'phone' && !/^\+7 \([0-9]{3}\) [0-9]{3}-[0-9]{2}-[0-9]{2}$/.test(value)) return 'Введите полный номер: +7 (999) 123-45-67.';
        if (name === 'email' && (input.validity.typeMismatch || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || value.length > 254)) return 'Введите почту в формате name@example.ru.';
        if (name === 'password') {
            if (!rules.length(value)) return 'Пароль должен содержать от 8 до 72 символов.';
            if (!/^[\x21-\x7E]+$/.test(value)) return 'Используйте латиницу, цифры и символы без пробелов.';
            if (!Object.values(rules).every(check => check(value))) return 'Добавьте латинскую букву, цифру и символ из @$!%*#?&.';
        }
        if (name === 'password_confirmation' && value !== password.value) return 'Пароли не совпадают.';
        return '';
    };
    const validate = name => { const message = errorFor(name); showError(name, message); return !message; };
    const formatPhone = (raw, caret = raw.length) => {
        let digits = raw.replace(/\D/g, '');
        let digitsBefore = raw.slice(0, caret).replace(/\D/g, '').length;
        if (!digits) { phone.value = ''; return; }
        const countryPresent = /^\s*\+7/.test(raw) || ([1, 11].includes(digits.length) && /^[78]/.test(digits));
        if (countryPresent) digits = digits.slice(1);
        else digitsBefore++;
        digits = digits.slice(0, 10);
        let result = '+7';
        if (digits.length) result += ' (' + digits.slice(0, 3);
        if (digits.length >= 3) result += ') ';
        if (digits.length > 3) result += digits.slice(3, 6);
        if (digits.length > 6) result += '-' + digits.slice(6, 8);
        if (digits.length > 8) result += '-' + digits.slice(8, 10);
        phone.value = result;
        let found = 0, position = result.length;
        for (let i = 0; i < result.length; i++) {
            if (/\d/.test(result[i])) found++;
            if (found === digitsBefore) { position = i + 1; break; }
        }
        if (caret === raw.length) position = result.length;
        if (document.activeElement === phone) phone.setSelectionRange(position, position);
    };
    if (phone.value) formatPhone(phone.value);
    phone.addEventListener('input', () => formatPhone(phone.value, phone.selectionStart));
    phone.addEventListener('keydown', event => {
        if (event.key !== 'Backspace' || phone.selectionStart !== phone.selectionEnd) return;
        const cursor = phone.selectionStart;
        if (cursor <= 3 || /\d/.test(phone.value[cursor - 1] || '')) return;
        let previous = cursor - 1;
        while (previous > 2 && !/\d/.test(phone.value[previous])) previous--;
        if (previous <= 2) return;
        event.preventDefault();
        const next = phone.value.slice(0, previous) + phone.value.slice(previous + 1);
        formatPhone(next, previous);
        if (touched.has('phone')) validate('phone');
    });
    phone.addEventListener('paste', event => {
        const raw = event.clipboardData.getData('text');
        const digits = raw.replace(/\D/g, '');
        if (/[^+0-9()\s-]/.test(raw) || digits.length > 11 || (digits.length === 11 && !/^[78]/.test(digits))) {
            event.preventDefault(); touched.add('phone'); showError('phone', 'Вставьте российский номер из 10 цифр или с кодом +7 либо 8.');
        }
    });
    fields.forEach(name => {
        const input = form.elements[name];
        input.addEventListener('blur', () => { touched.add(name); validate(name); });
        input.addEventListener('input', () => {
            if (touched.has(name) || input.getAttribute('aria-invalid') === 'true') validate(name);
            if (name === 'password') {
                Object.entries(rules).forEach(([rule, check]) => form.querySelector(`[data-password-rule="${rule}"]`).classList.toggle('is-valid', check(input.value)));
                if (touched.has('password_confirmation')) validate('password_confirmation');
            }
            if (summary.hidden === false && fields.every(field => !errorFor(field))) summary.hidden = true;
        });
    });
    form.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.hidden = false;
        const input = document.getElementById(button.dataset.passwordToggle);
        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.textContent = visible ? 'Скрыть' : 'Показать';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', (visible ? 'Скрыть' : 'Показать') + (input === password ? ' пароль' : ' повтор пароля'));
        });
    });
    const caps = document.getElementById('password-caps');
    password.addEventListener('keyup', event => caps.hidden = !event.getModifierState('CapsLock'));
    password.addEventListener('blur', () => caps.hidden = true);
    form.addEventListener('submit', event => {
        const valid = fields.map(name => { touched.add(name); return validate(name); }).every(Boolean);
        if (!valid) {
            event.preventDefault(); summary.hidden = false;
            form.elements[fields.find(name => errorFor(name))].focus();
            return;
        }
        form.elements.email.value = form.elements.email.value.trim().toLowerCase();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true; button.textContent = 'Создаём аккаунт…'; button.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        const button = form.querySelector('button[type="submit"]');
        button.disabled = false; button.textContent = 'Создать аккаунт'; button.removeAttribute('aria-busy');
    });
})();
