@extends('layouts.app')
@section('title', 'Корзина')
@section('content')
<div class="page-heading"><p class="eyebrow">Ваш выбор</p><h1>Корзина</h1><p>Розница — по весу. Опт — целыми упаковками.</p></div>
@foreach($unavailable as $notice)<p class="notice">{{ $notice }}</p>@endforeach
@if(empty($items))
<section class="panel empty-state"><h2>Здесь появятся ваши покупки</h2><p>Выберите овощи и фрукты в каталоге.</p><a class="button" href="{{ route('catalog') }}">Открыть каталог</a></section>
@else
<div class="checkout-grid"><div>
@foreach($items as $item)
<article class="cart-line panel" data-cart-line="{{ $item['cart_key'] }}">
<a href="{{ route('product.show', $item['product']) }}"><img class="cart-image" data-product-image src="{{ $item['product']->image_url }}" alt="{{ $item['product']->name }}"></a>
<div><h2><a href="{{ route('product.show', $item['product']) }}">{{ $item['product']->name }}</a></h2>
<p>{{ $item['purchase_type'] === 'retail' ? 'Розница' : 'Опт · упаковка '.$item['unit_weight_kg'].' кг' }}</p>
<p>{{ number_format($item['price'], 2, ',', ' ') }} ₽ / {{ $item['unit'] }}@if($item['purchase_type'] === 'wholesale') · {{ number_format($item['price'] / $item['unit_weight_kg'], 2, ',', ' ') }} ₽/кг@endif</p>
<form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" class="quantity-form" data-cart-quantity>@csrf @method('PATCH')
<div class="quantity-control"><label for="quantity-{{ $item['cart_key'] }}">Количество, {{ $item['unit'] }}</label>
<div class="quantity-stepper">
<button type="button" data-quantity-step="-1" aria-label="Уменьшить количество: {{ $item['product']->name }}">−</button>
<input id="quantity-{{ $item['cart_key'] }}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="{{ $item['purchase_type'] === 'retail' ? '0.5' : '1' }}" step="{{ $item['purchase_type'] === 'retail' ? '0.5' : '1' }}" max="9999" aria-describedby="quantity-status-{{ $item['cart_key'] }}" required>
<span class="quantity-unit" aria-hidden="true">{{ $item['unit'] }}</span>
<button type="button" data-quantity-step="1" aria-label="Увеличить количество: {{ $item['product']->name }}">+</button>
</div></div><noscript><button class="button button-secondary">Сохранить</button></noscript></form>
<p class="quantity-status" id="quantity-status-{{ $item['cart_key'] }}" data-quantity-status role="status" aria-live="polite">Количество сохраняется автоматически</p>
<form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST">@csrf @method('DELETE')<button class="text-button">Удалить</button></form></div>
<strong class="line-price" data-line-total>{{ number_format($item['subtotal'], 2, ',', ' ') }} ₽</strong>
</article>
@endforeach
</div><aside class="panel order-summary"><h2>Ваш заказ</h2><p><span data-cart-position-count>{{ count($items) }}</span> позиций · <span data-cart-weight>{{ str_replace('.', ',', (string) $weight) }}</span> кг</p><div class="summary-total"><span>Товары</span><strong data-cart-total>{{ number_format($total, 2, ',', ' ') }} ₽</strong></div>
<a class="button button-wide" data-cart-checkout href="{{ route('checkout.show') }}">Оформить заказ</a>
<a class="quiet-link" href="{{ route('catalog') }}">Продолжить покупки</a>
</aside></div>
@endif
<script src="{{ asset('js/cart.js') }}" defer></script>
@endsection
