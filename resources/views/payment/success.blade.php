@extends('layouts.app')
@section('title', 'Статус оплаты')
@section('content')
<section class="panel auth-panel"><p class="eyebrow">Заказ №{{ $order->id }}</p><h1>{{ $order->paid_at ? 'Оплата получена' : 'Статус оплаты' }}</h1><p>{{ $order->status_label }}</p>@unless($order->paid_at)<p>Если вы уже оплатили, дождитесь подтверждения и повторите проверку.</p><form action="{{ route('payment.check-status', $order) }}" method="POST">@csrf<button class="button">Проверить оплату</button></form>@endunless<a class="quiet-link" href="{{ route('profile.edit') }}">Перейти к заказам</a></section>
@endsection
