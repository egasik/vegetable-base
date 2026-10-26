@extends('layouts.app')
@section('title', 'Оплата не завершена')
@section('content')
<section class="panel auth-panel"><h1>Оплата не завершена</h1><p>Заказ №{{ $order->id }} сохранён в личном кабинете.</p><a class="button" href="{{ route('payment.success', $order) }}">Проверить статус платежа</a><a class="quiet-link" href="{{ route('profile.edit') }}">Мои заказы</a></section>
@endsection
