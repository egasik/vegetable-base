@extends('layouts.app')
@section('title', 'Подтверждение почты')
@section('content')
<section class="panel" style="max-width:520px;margin:auto"><p class="eyebrow">Перед оформлением заказа</p><h1 style="font-size:2rem">Подтвердите почту</h1><p>Запросите шестизначный код для {{ auth()->user()->email }}. Он действует 10 минут.</p><p>Осталось попыток: {{ $attemptsLeft }}. <span data-code-expiry="{{ $expiresAt }}"></span></p><form action="{{ route('verification.verify') }}" method="POST">@csrf<label>Код из письма<input style="display:block;width:100%;margin:12px 0" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required></label><button class="button button-wide">Подтвердить</button></form><form action="{{ route('verification.send') }}" method="POST">@csrf<button class="text-button" data-code-resend="{{ $resendAt }}">Отправить код на почту</button></form><a href="{{ route('profile.edit') }}">Изменить адрес почты</a></section>
@endsection


