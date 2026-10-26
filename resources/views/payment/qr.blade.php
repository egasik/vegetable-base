@extends('layouts.app')
@section('title', 'Оплата по СБП')
@section('content')
<section class="panel auth-panel"><h1>Оплата по СБП</h1><p>Заказ №{{ $order->id }} · {{ number_format($order->total_amount, 2, ',', ' ') }} ₽</p><p>Отсканируйте QR-код приложением вашего банка.</p><div style="background:white;padding:16px;display:flex;justify-content:center">{!! $qrCode !!}</div><form action="{{ route('payment.check-status', $order) }}" method="POST" style="margin-top:24px">@csrf<button class="button button-wide">Я оплатил — проверить</button></form><a class="quiet-link" href="{{ route('profile.edit') }}">Вернуться к заказам</a></section>
@endsection
