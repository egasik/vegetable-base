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
<article class="cart-line panel">
<a href="{{ route('product.show', $item['product']) }}"><img class="cart-image" src="{{ $item['product']->image_path ? asset('storage/'.$item['product']->image_path) : asset('images/product-placeholder.svg') }}" alt="{{ $item['product']->name }}"></a>
<div><h2><a href="{{ route('product.show', $item['product']) }}">{{ $item['product']->name }}</a></h2>
<p>{{ $item['purchase_type'] === 'retail' ? 'Розница' : 'Опт · упаковка '.$item['unit_weight_kg'].' кг' }}</p>
<p>{{ number_format($item['price'], 2, ',', ' ') }} ₽ / {{ $item['unit'] }}@if($item['purchase_type'] === 'wholesale') · {{ number_format($item['price'] / $item['unit_weight_kg'], 2, ',', ' ') }} ₽/кг@endif</p>
<form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" class="quantity-form">@csrf @method('PATCH')
<label>Количество, {{ $item['unit'] }}<input type="number" name="quantity" value="{{ $item['quantity'] }}" min="{{ $item['purchase_type'] === 'retail' ? '0.5' : '1' }}" step="{{ $item['purchase_type'] === 'retail' ? '0.5' : '1' }}" max="9999" required></label><button class="button button-secondary">Обновить</button></form>
<form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST">@csrf @method('DELETE')<button class="text-button">Удалить</button></form></div>
<strong class="line-price">{{ number_format($item['subtotal'], 2, ',', ' ') }} ₽</strong>
</article>
@endforeach
</div><aside class="panel order-summary"><h2>Ваш заказ</h2><p>{{ count($items) }} позиций · {{ $weight }} кг</p><div class="summary-total"><span>Товары</span><strong>{{ number_format($total, 2, ',', ' ') }} ₽</strong></div>
<a class="button button-wide" href="{{ route('checkout.show') }}">Оформить заказ</a>
<a class="quiet-link" href="{{ route('catalog') }}">Продолжить покупки</a>
</aside></div>
@endif
@endsection
