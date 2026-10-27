@extends('layouts.app')
@section('title', 'Регистрация')
@section('inline-errors', '1')
@section('content')
<section class="panel auth-panel registration-panel">
    <p class="eyebrow">Для удобных покупок</p><h1>Создать аккаунт</h1>
    <p class="registration-intro">Сохраняйте покупки в корзине и следите за своими заказами. После регистрации отправим код на вашу почту.</p>
    <form action="{{ route('register') }}" method="POST" data-registration>@csrf
        <p class="registration-summary notice notice-error" role="alert" data-registration-summary @if(!$errors->any()) hidden @endif>Проверьте выделенные поля и повторите регистрацию.</p>
        <p class="registration-required">Все поля обязательны, кроме отчества.</p>
        <div class="registration-grid">
            @foreach(['last_name' => 'Фамилия', 'name' => 'Имя', 'middle_name' => 'Отчество'] as $field => $label)
                <div class="registration-field {{ $field === 'middle_name' ? 'registration-full' : '' }}">
                    <label for="register-{{ $field }}">{{ $label }} @if($field === 'middle_name')<span class="field-optional">если есть</span>@endif</label>
                    <input id="register-{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" minlength="2" maxlength="60" @required($field !== 'middle_name') autocomplete="{{ $field === 'last_name' ? 'family-name' : ($field === 'name' ? 'given-name' : 'additional-name') }}" aria-describedby="error-{{ $field }}" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" placeholder="{{ $field === 'name' ? 'Иван' : ($field === 'last_name' ? 'Иванов' : 'Иванович') }}">
                    <p id="error-{{ $field }}" class="field-error" data-error="{{ $field }}" @if(!$errors->has($field)) hidden @endif>{{ $errors->first($field) }}</p>
                </div>
            @endforeach
            <div class="registration-field">
                <label for="register-phone">Телефон</label>
                <input id="register-phone" name="phone" type="tel" inputmode="tel" value="{{ old('phone') }}" placeholder="+7 (999) 123-45-67" maxlength="40" autocomplete="tel" required aria-describedby="phone-help error-phone" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}">
                <p id="phone-help" class="field-help">Для связи по вашему заказу</p>
                <p id="error-phone" class="field-error" data-error="phone" @if(!$errors->has('phone')) hidden @endif>{{ $errors->first('phone') }}</p>
            </div>
            <div class="registration-field">
                <label for="register-email">Почта</label>
                <input id="register-email" name="email" type="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" autocapitalize="none" spellcheck="false" placeholder="name@example.ru" required aria-describedby="email-help error-email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                <p id="email-help" class="field-help">Отправим код подтверждения</p>
                <p id="error-email" class="field-error" data-error="email" @if(!$errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
            </div>
            <div class="registration-field registration-full">
                <label for="register-password">Пароль</label>
                <div class="password-control"><input id="register-password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required aria-describedby="password-rules password-caps error-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"><button type="button" class="password-toggle" data-password-toggle="register-password" aria-label="Показать пароль" aria-pressed="false" hidden>Показать</button></div>
                <ul class="password-rules" id="password-rules"><li data-password-rule="length">От 8 до 72 символов</li><li data-password-rule="letter">Латинская буква</li><li data-password-rule="digit">Хотя бы одна цифра</li><li data-password-rule="special">Символ из @$!%*#?&</li></ul>
                <p class="field-help">Без пробелов и кириллицы. Регистр букв имеет значение.</p>
                <p id="password-caps" class="field-help" hidden>Включён Caps Lock</p>
                <p id="error-password" class="field-error" data-error="password" @if(!$errors->has('password')) hidden @endif>{{ $errors->first('password') }}</p>
            </div>
            <div class="registration-field registration-full">
                <label for="register-password-confirmation">Повторите пароль</label>
                <div class="password-control"><input id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="72" required aria-describedby="error-password_confirmation" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"><button type="button" class="password-toggle" data-password-toggle="register-password-confirmation" aria-label="Показать повтор пароля" aria-pressed="false" hidden>Показать</button></div>
                <p id="error-password_confirmation" class="field-error" data-error="password_confirmation" @if(!$errors->has('password_confirmation')) hidden @endif>{{ $errors->first('password_confirmation') }}</p>
            </div>
        </div>
        <button class="button button-wide" type="submit">Создать аккаунт</button>
        <p class="registration-note">Следующий шаг: подтверждение почты кодом из письма.</p>
    </form>
    <p class="registration-login">Уже есть аккаунт? <a class="quiet-link" href="{{ route('login') }}">Войти</a></p>
</section>
<script src="{{ asset('js/registration.js') }}?v={{ filemtime(public_path('js/registration.js')) }}" defer></script>
@endsection
