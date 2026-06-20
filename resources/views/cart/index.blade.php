@extends('layouts.app')
@section('title', 'Корзина')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-black text-[#422168] mb-8">Корзина</h1>

    @if(session('success'))
        <div class="bg-[#00F3B5] text-[#422168] p-4 rounded-xl font-bold mb-6 border-l-8 border-[#0D7D4C]">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-xl font-bold mb-6 border-l-8 border-red-500">
            {{ session('error') }}
        </div>
    @endif

    @if(empty($items))
        <div class="bg-white p-12 rounded-3xl shadow-xl text-center border-4 border-[#E8FC8C]">
            <p class="text-6xl mb-4">🛒</p>
            <p class="text-2xl font-bold text-[#422168] mb-4">Корзина пуста</p>
            <a href="{{ route('catalog') }}" class="inline-block bg-[#0D7D4C] text-white font-bold py-3 px-8 rounded-xl btn-animated">
                Перейти в каталог
            </a>
        </div>
    @else
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Список товаров --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $item)
                    <div class="bg-white p-6 rounded-2xl shadow-lg border-4 border-[#E8FC8C] flex gap-6">
                        {{-- Фото --}}
                        <div class="w-32 h-32 bg-[#E8FC8C] rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                            @if($item['product']->image_path)
                                <img src="{{ asset('storage/' . $item['product']->image_path) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-5xl">🥦</span>
                            @endif
                        </div>

                        {{-- Информация --}}
<div class="flex-1">
    <h3 class="text-xl font-black text-[#422168] mb-2">{{ $item['product']->name }}</h3>
    
    {{-- Кнопки добавления с другим типом --}}
@if($item['product']->is_retail && $item['product']->is_wholesale)
    <form action="{{ route('cart.change-type', $item['cart_key']) }}" method="POST" class="mb-3">
        @csrf
        @method('PATCH')
        <div class="flex gap-2">
            @if($item['purchase_type'] === 'wholesale')
                {{-- Сейчас опт — предлагаем добавить розницу --}}
                <button type="submit" name="purchase_type" value="retail" 
                        class="px-3 py-1 rounded-lg text-xs font-bold bg-[#00F3B5] text-[#422168] hover:bg-[#0D7D4C] hover:text-white transition-colors">
                    + Добавить также в розницу
                </button>
            @else
                {{-- Сейчас розница — предлагаем добавить опт --}}
                <button type="submit" name="purchase_type" value="wholesale" 
                        class="px-3 py-1 rounded-lg text-xs font-bold bg-[#422168] text-[#CAF204] hover:bg-[#0D7D4C] transition-colors">
                    + Добавить также в опт
                </button>
            @endif
        </div>
    </form>
@endif

{{-- Текущий тип (просто отображение) --}}
<p class="text-sm text-gray-600 mb-3">
    @if($item['purchase_type'] === 'retail')
        <span class="bg-[#00F3B5] text-[#422168] px-2 py-1 rounded text-xs font-bold">Розница</span>
        <span class="ml-2">{{ $item['price'] }} ₽/кг</span>
    @else
        <span class="bg-[#422168] text-[#CAF204] px-2 py-1 rounded text-xs font-bold">Опт</span>
        <span class="ml-2">{{ $item['price'] }} ₽/мешок</span>
    @endif
</p>

    {{-- Форма обновления количества --}}
    <form action="{{ route('cart.update', $item['cart_key']) }}" method="POST" class="flex items-center gap-3 mb-3">
        @csrf
        @method('PATCH')
        <div class="flex items-center border-2 border-[#0D7D4C] rounded-lg overflow-hidden">
            <button type="button" onclick="decrementQuantity(this)" 
                    class="px-3 py-1 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">-</button>
            <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" 
                   class="w-16 text-center focus:outline-none font-bold" onchange="this.form.submit()">
            <button type="button" onclick="incrementQuantity(this)" 
                    class="px-3 py-1 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">+</button>
        </div>
    </form>

    {{-- Удалить --}}
    <form action="{{ route('cart.remove', $item['cart_key']) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold">
            Удалить
        </button>
    </form>
</div>

                        {{-- Цена --}}
                        <div class="text-right flex-shrink-0">
                            <p class="text-2xl font-black text-[#0D7D4C]">{{ $item['subtotal'] }} ₽</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Итого --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#CAF204] sticky top-24">
                    <h3 class="text-2xl font-black text-[#422168] mb-6">Итого</h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-lg">
                            <span class="text-gray-600">Товаров:</span>
                            <span class="font-bold">{{ count($items) }}</span>
                        </div>
                        <div class="flex justify-between text-2xl font-black text-[#0D7D4C] border-t-4 border-[#E8FC8C] pt-3">
                            <span>Сумма:</span>
                            <span>{{ $total }} ₽</span>
                        </div>
                    </div>

                    {{-- Форма оформления заказа --}}
                    <form action="{{ route('orders.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="from_cart" value="1">
                        <button type="submit" class="w-full bg-[#0D7D4C] text-white font-black py-4 rounded-xl btn-animated pulse-hover text-lg">
                            Оформить заказ
                        </button>
                    </form>

                    {{-- Форма очистки корзины --}}
                    <form action="{{ route('cart.clear') }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-gray-200 text-[#422168] font-bold py-3 rounded-xl btn-animated">
                            Очистить корзину
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function incrementQuantity(button) {
    const input = button.closest('form').querySelector('input[name="quantity"]');
    input.stepUp();
    button.closest('form').submit();
}

function decrementQuantity(button) {
    const input = button.closest('form').querySelector('input[name="quantity"]');
    if (parseInt(input.value) > 1) {
        input.stepDown();
        button.closest('form').submit();
    }
}
</script>
@endsection