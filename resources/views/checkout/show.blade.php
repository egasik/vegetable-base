@extends('layouts.app')
@section('title', 'Оформление заказа')
@section('content')
<div class="page-heading"><p class="eyebrow">Остался один шаг</p><h1>Оформление заказа</h1></div><div class="checkout-grid"><section class="panel"><h2>Куда доставить</h2><form action="{{ route('checkout.address') }}" method="POST">@csrf<label>Город, улица и дом<input style="display:block;width:100%;margin:12px 0" name="delivery_address" value="{{ old('delivery_address', session('checkout_address.delivery_address')) }}" maxlength="255" required placeholder="Город, улица, дом"></label><button class="button button-secondary">Проверить адрес</button></form>
@if(session('checkout_address'))<div class="notice" style="margin-top:20px">{{ session('checkout_address.delivery_address') }}</div>@endif
@if(config('services.yandex.maps_key'))
<div id="delivery-map" data-lat="{{ session('checkout_address.latitude', 52.2864) }}" data-lon="{{ session('checkout_address.longitude', 104.2807) }}" style="height:300px;margin-top:20px;border-radius:12px;overflow:hidden" aria-label="Карта адреса доставки"></div>
<form id="map-address-form" action="{{ route('checkout.address') }}" method="POST">@csrf<input type="hidden" name="latitude"><input type="hidden" name="longitude"><p id="map-hint">Выберите дом на карте или найдите его по адресу выше.</p><button class="button button-secondary" id="map-check" disabled>Проверить выбранный дом</button></form>
@endif
<form action="{{ route('orders.store') }}" method="POST" style="margin-top:24px">@csrf<input type="hidden" name="from_cart" value="1"><input type="hidden" name="checkout_token" value="{{ session('checkout_token') }}"><label>Телефон для доставки<input style="display:block;width:100%;margin:12px 0" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone) }}" maxlength="30" required type="tel"></label><label>Квартира, подъезд и комментарий<input style="display:block;width:100%;margin:12px 0" name="delivery_details" value="{{ old('delivery_details') }}" maxlength="500"></label><label style="display:block;margin:20px 0"><input type="checkbox" name="address_confirmed" value="1" required> Подтверждаю найденный адрес доставки</label><button class="button button-wide" @disabled(!session('checkout_address'))>Создать заказ и перейти к оплате</button></form></section><aside class="panel order-summary"><h2>Ваш выбор</h2>@foreach($items as $item)<p><strong>{{ $item['product_name'] }}</strong><br>{{ $item['quantity'] }} {{ $item['unit'] }} · {{ number_format($item['subtotal'], 2, ',', ' ') }} ₽</p>@endforeach<div class="summary-total"><span>Итого</span><strong>{{ number_format($total, 2, ',', ' ') }} ₽</strong></div><a class="quiet-link" href="{{ route('cart.index') }}">Изменить корзину</a></aside></div>
@if(config('services.yandex.maps_key'))
<script src="https://api-maps.yandex.ru/2.1/?apikey={{ urlencode(config('services.yandex.maps_key')) }}&lang=ru_RU" defer></script>
<script src="{{ asset('js/checkout-map.js') }}" defer></script>
@endif
<script src="{{ asset('js/checkout-form.js') }}" defer></script>
@endsection

