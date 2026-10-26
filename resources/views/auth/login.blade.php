@extends('layouts.app')
@section('title', 'Вход')
@section('content')
<section class="panel auth-panel"><p class="eyebrow">Снова за свежим</p><h1>Добро пожаловать</h1><p>Войдите, чтобы добавлять товары в корзину и видеть свои заказы.</p><form action="{{ route('login') }}" method="POST">@csrf<label>Почта<input class="profile-input" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label><label>Пароль<input class="profile-input" name="password" type="password" autocomplete="current-password" required></label><label style="display:block;margin:16px 0"><input type="checkbox" name="remember"> Запомнить меня</label><button class="button button-wide">Войти</button></form><a class="quiet-link" href="{{ route('password.request') }}">Забыли пароль?</a><p style="margin-top:24px">Первый раз у нас? <a class="quiet-link" href="{{ route('register') }}">Создать аккаунт</a></p></section>
@endsection
