document.addEventListener('DOMContentLoaded', () => {
    const address = document.querySelector('[name="delivery_address"]');
    const confirmation = document.querySelector('[name="address_confirmed"]');
    if (!address || !confirmation) return;
    const orderForm = confirmation.form;
    const submit = orderForm.querySelector('button');
    address.addEventListener('input', () => {
        confirmation.checked = false;
        confirmation.disabled = true;
        submit.disabled = true;
        submit.textContent = 'Сначала проверьте изменённый адрес';
    });
    // Keep delivery details while address verification reloads this checkout page.
    const token = orderForm.querySelector('[name="checkout_token"]').value;
    const key = `checkout-draft-${token}`;
    const fields = ['contact_phone', 'delivery_details'];
    try {
        const draft = JSON.parse(sessionStorage.getItem(key) || '{}');
        fields.forEach(name => {
            const input = orderForm.elements[name];
            if (!input.value && draft[name]) input.value = draft[name];
            input.addEventListener('input', () => {
                draft[name] = input.value;
                sessionStorage.setItem(key, JSON.stringify(draft));
            });
        });
    } catch (_) { /* Storage may be unavailable; regular form submission still works. */ }
    orderForm.addEventListener('submit', () => {
        submit.disabled = true;
        submit.textContent = 'Создаём заказ…';
    });
    window.addEventListener('pageshow', event => {
        if (event.persisted) window.location.reload();
    });
});
