@extends('layouts.app')
@section('title', 'Подтверждение смены email')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-4xl font-black text-[#422168] text-center mb-8">Подтверждение смены email</h1>

    @if(session('success'))
        <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl font-bold mb-6 border-l-8 border-[#0D7D4C]">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-xl font-bold mb-6 border-l-8 border-red-500">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-8 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
        <div class="text-center mb-6">
            <div class="w-20 h-20 mx-auto bg-[#CAF204] rounded-full flex items-center justify-center text-4xl mb-4">✉️</div>
            <h2 class="text-2xl font-black text-[#422168] mb-2">Проверьте почту</h2>
            <p class="text-sm text-gray-600">
                Код отправлен на новый email:<br>
                <strong class="text-[#422168]">{{ $code->new_value }}</strong>
            </p>
        </div>

        <form action="{{ route('profile.email-change.confirm') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-bold text-[#0D7D4C] mb-2 text-center">Введите код из письма</label>
                <input type="text" name="code" id="code-input" maxlength="6" required autofocus
                       data-purpose="email_change"
                       placeholder="000000"
                       class="w-full border-2 border-[#E8FC8C] p-4 rounded-xl text-center text-3xl font-mono tracking-widest focus:border-[#CAF204] focus:outline-none">
                <p id="code-status" class="text-xs mt-2 text-center"></p>
            </div>

            <button type="submit" id="submit-btn" disabled
                    class="w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed">
                Подтвердить и сменить email
            </button>
        </form>

        <form action="{{ route('profile.email-change.regenerate') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" id="resend-btn" 
                    class="w-full bg-[#CAF204] text-[#422168] font-bold py-3 rounded-xl btn-animated
                           {{ $secondsUntilResend > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $secondsUntilResend > 0 ? 'disabled' : '' }}>
                @if($secondsUntilResend > 0)
                    Отправить код повторно (<span id="resend-timer">{{ (int)$secondsUntilResend }}</span> сек)
                @else
                    Отправить код повторно
                @endif
            </button>
        </form>

        <div class="mt-4 p-4 bg-gray-50 rounded-xl text-sm text-gray-600">
            <p class="mb-2"><strong>Не получили письмо?</strong></p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>Проверьте папку "Спам"</li>
                <li>Убедитесь, что email указан верно</li>
                <li>Нажмите "Отправить код повторно" (не ранее чем через 60 секунд)</li>
            </ul>
        </div>
    </div>
</div>

@include('profile._code-validator-script')

@if($secondsUntilResend > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resend-btn');
    const timerSpan = document.getElementById('resend-timer');
    let seconds = {{ $secondsUntilResend }};

    const interval = setInterval(function() {
    seconds = Math.max(0, seconds - 1); // Округляем и гарантируем неотрицательное значение
    if (seconds <= 0) {
        clearInterval(interval);
        resendBtn.disabled = false;
        resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        resendBtn.textContent = 'Отправить код повторно';
    } else {
        timerSpan.textContent = Math.floor(seconds); // Округляем до целого
    }
}, 1000);
});
</script>
@endif
@endsection