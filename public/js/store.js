document.querySelectorAll('[data-add-to-cart]').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        const toast = document.getElementById('store-toast');
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (response.status === 401 || response.redirected) { window.location.assign('/login'); return; }
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Не удалось добавить товар.');
            document.querySelectorAll('[data-cart-count]').forEach(node => node.textContent = data.count);
            toast.textContent = data.message;
        } catch (error) { toast.textContent = error.message || 'Проверьте соединение и попробуйте снова.'; }
        finally { button.disabled = false; toast.hidden = false; clearTimeout(window.storeToastTimer); window.storeToastTimer = setTimeout(() => toast.hidden = true, 5000); }
    });
});

// Server limits remain authoritative; this timer explains when the next action is available.
document.querySelectorAll('[data-code-resend]').forEach(button => {
    const expiry = document.querySelector('[data-code-expiry]');
    const update = () => {
        const now = Math.floor(Date.now() / 1000);
        const wait = Math.max(0, Number(button.dataset.codeResend) - now);
        button.disabled = wait > 0;
        button.textContent = wait ? `Повторная отправка через ${wait} с` : 'Отправить код на почту';
        if (expiry) {
            const left = Math.max(0, Number(expiry.dataset.codeExpiry) - now);
            expiry.textContent = left ? `Код действует ещё ${Math.floor(left / 60)}:${String(left % 60).padStart(2, '0')}` : 'Запросите новый код.';
        }
    };
    update();
    setInterval(update, 1000);
});
