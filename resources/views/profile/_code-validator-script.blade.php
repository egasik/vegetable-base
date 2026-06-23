<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code-input');
    const codeStatus = document.getElementById('code-status');
    const submitBtn = document.getElementById('submit-btn');
    const purpose = codeInput.dataset.purpose;
    let checkTimeout = null;

    codeInput.addEventListener('input', function() {
        const code = this.value.trim();
        
        // Сброс состояния
        submitBtn.disabled = true;
        submitBtn.className = 'w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed';
        codeStatus.textContent = '';
        codeInput.style.borderColor = '';
        codeInput.style.backgroundColor = '';

        if (code.length !== 6) {
            return;
        }

        // Задержка перед запросом
        clearTimeout(checkTimeout);
        checkTimeout = setTimeout(() => checkCode(code), 300);
    });

    function checkCode(code) {
        fetch('{{ route("verify.check-code") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ code, purpose })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.valid) {
                codeStatus.textContent = '✓ ' + data.message;
                codeStatus.className = 'text-xs mt-2 text-center text-green-600 font-bold';
                codeInput.style.borderColor = '#0D7D4C';
                codeInput.style.backgroundColor = '#E8FC8C';
                
                submitBtn.disabled = false;
                submitBtn.className = 'w-full bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated pulse-hover';
            } else {
                codeStatus.textContent = '✗ ' + data.message + (data.attempts_left !== undefined ? ` (осталось: ${data.attempts_left})` : '');
                codeStatus.className = 'text-xs mt-2 text-center text-red-600 font-bold';
                codeInput.style.borderColor = '#EF4444';
                codeInput.style.backgroundColor = '#FEE2E2';
            }
        })
        .catch(error => {
            console.error('Ошибка проверки кода:', error);
            codeStatus.textContent = '⚠ Ошибка соединения с сервером';
            codeStatus.className = 'text-xs mt-2 text-center text-orange-600 font-bold';
        });
    }
});
</script>