@foreach(['retail', 'wholesale'] as $type)
@if(($type === 'retail' && $product->is_retail && $product->retail_price > 0) || ($type === 'wholesale' && $product->is_wholesale && $product->wholesale_price > 0 && $product->wholesale_unit_kg > 0))
<div class="purchase-option"><span class="purchase-price">{{ number_format($type === 'retail' ? $product->retail_price : $product->wholesale_price, 2, ',', ' ') }} ₽</span><span> / {{ $type === 'retail' ? 'кг' : 'уп.' }}</span>
<p class="purchase-note">@if($type === 'retail')Розница · шаг 0,5 кг @else Опт · {{ $product->wholesale_unit_kg }} кг в упаковке<br>{{ number_format($product->wholesale_price / $product->wholesale_unit_kg, 2, ',', ' ') }} ₽/кг @endif</p>
@auth<form action="{{ route('cart.add', $product) }}" method="POST" data-add-to-cart>@csrf<input type="hidden" name="purchase_type" value="{{ $type }}"><div class="purchase-controls"><label>{{ $type === 'retail' ? 'Вес, кг' : 'Упаковки' }}<input aria-label="{{ $product->name }}: {{ $type === 'retail' ? 'вес в кг' : 'число упаковок' }}" type="number" name="quantity" min="{{ $type === 'retail' ? '0.5' : '1' }}" step="{{ $type === 'retail' ? '0.5' : '1' }}" max="9999" value="1" required></label><button type="submit" class="button">В корзину</button></div></form>@endauth
</div>
@endif
@endforeach
@guest<a class="button button-secondary button-wide" style="margin-top:16px" href="{{ route('login') }}">Войти для покупки</a>@endguest
@if(!$product->is_retail && !$product->is_wholesale)<p>Товар временно недоступен</p>@endif
