(() => {
    const forms = [...document.querySelectorAll('[data-cart-quantity]')];
    if (!forms.length) return;
    const checkout = document.querySelector('[data-cart-checkout]');
    const money = new Intl.NumberFormat('ru-RU', {style: 'currency', currency: 'RUB'});
    const number = new Intl.NumberFormat('ru-RU', {maximumFractionDigits: 3});
    let queue = Promise.resolve();
    const states = forms.map(form => ({form, input: form.elements.quantity,
        status: form.closest('[data-cart-line]').querySelector('[data-quantity-status]'),
        saved: form.elements.quantity.value, dirty: false, uncertain: false, queued: false, timer: null}));
    const setStatus = (state, message, error = false) => {
        state.status.textContent = message;
        state.status.classList.toggle('quantity-error', error);
        state.input.setAttribute('aria-invalid', String(error));
    };
    const syncCheckout = () => {
        const busy = states.some(state => state.dirty || state.queued || state.timer);
        checkout.setAttribute('aria-disabled', String(busy));
        checkout.classList.toggle('cart-checkout-pending', busy);
        document.querySelectorAll('[data-cart-line] form:not([data-cart-quantity]) button').forEach(button => button.disabled = busy);
    };
    const valid = state => {
        if (state.input.validity.valid) return true;
        setStatus(state, Number(state.input.step) === .5
            ? 'Укажите вес от 0,5 кг с шагом 0,5 кг, не более 9999 кг.'
            : 'Укажите целое число упаковок от 1 до 9999.', true);
        return false;
    };
    const save = state => {
        clearTimeout(state.timer); state.timer = null;
        if (state.queued) return;
        if (!valid(state)) { syncCheckout(); return; }
        state.queued = true; syncCheckout();
        queue = queue.then(async () => {
            if (!valid(state)) return;
            const quantity = state.input.value;
            const body = new FormData(state.form);
            body.set('quantity', quantity);
            setStatus(state, 'Сохраняем количество…');
            state.form.setAttribute('aria-busy', 'true');
            const response = await fetch(state.form.action, {method: 'POST', body, headers: {
                'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
            }});
            if (response.status === 401 || response.redirected) {
                window.location.assign('/login');
                throw new Error('Войдите в аккаунт, чтобы сохранить количество.');
            }
            if (response.status === 419) throw new Error('Сессия истекла. Обновите страницу корзины.');
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Не удалось сохранить количество. Измените его, чтобы повторить.');
            if (!Array.isArray(data.items) || typeof data.total !== 'number') throw new Error('Не удалось получить сумму. Обновите корзину.');
            data.items.forEach(item => {
                const row = document.querySelector(`[data-cart-line="${CSS.escape(item.cart_key)}"]`);
                if (row) row.querySelector('[data-line-total]').textContent = money.format(item.subtotal);
            });
            document.querySelector('[data-cart-total]').textContent = money.format(data.total);
            document.querySelector('[data-cart-weight]').textContent = number.format(data.weight);
            document.querySelector('[data-cart-position-count]').textContent = data.count;
            document.querySelectorAll('[data-cart-count]').forEach(node => node.textContent = data.count);
            state.saved = quantity;
            state.uncertain = false;
            state.dirty = state.input.value !== quantity;
            setStatus(state, state.dirty ? 'Ожидаем сохранения…' : 'Количество сохранено');
        }).catch(error => {
            state.dirty = true;
            state.uncertain = true;
            setStatus(state, error.message || 'Нет соединения. Измените количество, чтобы повторить.', true);
        }).finally(() => {
            state.queued = false;
            state.form.removeAttribute('aria-busy');
            if (state.dirty && state.input.value !== state.saved && !state.status.classList.contains('quantity-error')) save(state);
            syncCheckout();
        });
    };
    states.forEach(state => {
        const syncSteps = () => {
            const value = Number(state.input.value);
            state.form.querySelector('[data-quantity-step="-1"]').disabled = state.input.value !== '' && value <= Number(state.input.min);
            state.form.querySelector('[data-quantity-step="1"]').disabled = value >= Number(state.input.max);
        };
        const changed = () => {
            syncSteps();
            state.dirty = state.input.value !== state.saved || state.uncertain;
            clearTimeout(state.timer); state.timer = null;
            if (state.dirty) {
                setStatus(state, 'Ожидаем сохранения…');
                state.timer = setTimeout(() => save(state), 400);
            } else setStatus(state, 'Количество сохранено');
            syncCheckout();
        };
        state.input.addEventListener('input', changed);
        state.form.querySelectorAll('[data-quantity-step]').forEach(button => {
            button.addEventListener('click', () => {
                const step = Number(state.input.step);
                const current = state.input.valueAsNumber;
                const next = Number.isFinite(current) ? current + Number(button.dataset.quantityStep) * step : Number(state.input.min);
                state.input.value = Math.min(Number(state.input.max), Math.max(Number(state.input.min), Math.round(next * 1000) / 1000));
                state.input.dispatchEvent(new Event('input', {bubbles: true}));
            });
        });
        syncSteps();
        state.input.addEventListener('change', () => { changed(); if (state.dirty) save(state); });
        state.form.addEventListener('submit', event => { event.preventDefault(); if (state.dirty) save(state); });
    });
    checkout.addEventListener('click', event => {
        if (checkout.getAttribute('aria-disabled') !== 'true') return;
        event.preventDefault();
        states.filter(state => state.dirty && !state.queued).forEach(save);
        const invalid = states.find(state => !state.input.validity.valid);
        if (invalid) invalid.input.focus();
    });
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
})();
