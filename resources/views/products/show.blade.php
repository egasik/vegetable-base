@extends('layouts.app')
@section('title', $product->name)

@section('content')
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl mx-auto overflow-hidden grid md:grid-cols-2 border-4 border-[#E8FC8C]">
        {{-- Фото товара --}}
        <div class="h-96 bg-[#E8FC8C] flex items-center justify-center overflow-hidden">
            @if($product->image_path)
                <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
            @else
                <span class="text-9xl">🥦</span>
            @endif
        </div>

        <div class="p-8 flex flex-col justify-between">
            <div>
                {{-- Бейджи типа продажи --}}
                <div class="flex gap-2 mb-4">
                    @if($product->is_retail && $product->is_wholesale)
                        <span class="bg-[#422168] text-[#CAF204] px-3 py-1 rounded-full text-xs font-bold">ОПТ и РОЗНИЦА</span>
                    @elseif($product->is_retail)
                        <span class="bg-[#00F3B5] text-[#422168] px-3 py-1 rounded-full text-xs font-bold">ТОЛЬКО РОЗНИЦА</span>
                    @else
                        <span class="bg-[#0D7D4C] text-white px-3 py-1 rounded-full text-xs font-bold">ТОЛЬКО ОПТ</span>
                    @endif
                </div>

                <h1 class="text-4xl font-black mb-4 text-[#422168]">{{ $product->name }}</h1>
                <p class="text-gray-700 mb-6 leading-relaxed">{{ $product->description }}</p>

                {{-- Форма добавления в корзину --}}
                <form action="{{ route('cart.add', $product) }}" method="POST" id="addToCartForm">
                    @csrf

                    {{-- Выбор типа покупки (если доступны оба) --}}
                    @if($product->is_retail && $product->is_wholesale)
                        <div class="space-y-3 mb-6">
                            <label class="flex items-center justify-between bg-[#E8FC8C]/50 p-4 rounded-xl border-2 border-[#CAF204] cursor-pointer hover:bg-[#E8FC8C] transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="purchase_type" value="retail" checked class="w-5 h-5 accent-[#0D7D4C]" onchange="updatePriceDisplay('retail')">
                                    <div>
                                        <p class="font-bold text-[#422168]">Розница</p>
                                        <p class="text-xs text-gray-600">Продажа по килограммам</p>
                                    </div>
                                </div>
                                <p class="text-2xl font-black text-[#0D7D4C]">{{ $product->retail_price }} ₽ / кг</p>
                            </label>

                            <label class="flex items-center justify-between bg-[#E8FC8C]/50 p-4 rounded-xl border-2 border-[#422168] cursor-pointer hover:bg-[#E8FC8C] transition-colors">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="purchase_type" value="wholesale" class="w-5 h-5 accent-[#0D7D4C]" onchange="updatePriceDisplay('wholesale')">
                                    <div>
                                        <p class="font-bold text-[#422168]">Опт</p>
                                        <p class="text-xs text-gray-600">Мешок {{ $product->wholesale_unit_kg }} кг</p>
                                    </div>
                                </div>
                                <p class="text-2xl font-black text-[#0D7D4C]">{{ $product->wholesale_price }} ₽ / мешок</p>
                            </label>
                        </div>
                    @elseif($product->is_retail)
                        {{-- Только розница — скрытое поле --}}
                        <input type="hidden" name="purchase_type" value="retail">
                        <div class="bg-[#E8FC8C]/50 p-4 rounded-xl border-2 border-[#CAF204] mb-6">
                            <p class="text-3xl font-black text-[#0D7D4C]">{{ $product->retail_price }} ₽ / кг</p>
                        </div>
                    @else
                        {{-- Только опт — скрытое поле --}}
                        <input type="hidden" name="purchase_type" value="wholesale">
                        <div class="bg-[#E8FC8C]/50 p-4 rounded-xl border-2 border-[#422168] mb-6">
                            <p class="text-3xl font-black text-[#0D7D4C]">{{ $product->wholesale_price }} ₽ / мешок ({{ $product->wholesale_unit_kg }} кг)</p>
                        </div>
                    @endif

                    {{-- Количество --}}
                    <div class="flex items-center gap-4 mb-6">
                        <span class="font-bold text-[#422168]" id="qty_label">
                            @if($product->is_retail && !$product->is_wholesale)
                                Количество (кг):
                            @elseif(!$product->is_retail && $product->is_wholesale)
                                Количество (мешков):
                            @else
                                Количество:
                            @endif
                        </span>
                        <div class="flex items-center border-2 border-[#0D7D4C] rounded-lg overflow-hidden">
                            <button type="button" onclick="document.getElementById('qty').stepDown()" class="px-3 py-2 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">-</button>
                            <input type="number" id="qty" name="quantity" value="1" min="1" class="w-16 text-center focus:outline-none font-bold">
                            <button type="button" onclick="document.getElementById('qty').stepUp()" class="px-3 py-2 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">+</button>
                        </div>
                    </div>

                    {{-- Кнопка добавления в корзину --}}
                    <button type="submit" class="w-full bg-[#0D7D4C] text-white font-bold py-4 rounded-xl btn-animated pulse-hover hover:bg-[#422168] text-lg mb-3">
                         Добавить в корзину
                    </button>
                </form>

                {{-- Ссылка на корзину --}}
                <a href="{{ route('cart.index') }}" class="block text-center text-sm text-[#422168] hover:text-[#0D7D4C] font-bold transition-colors">
                    Перейти в корзину →
                </a>
            </div>
        </div>
    </div>

    <script>
        function updatePriceDisplay(type) {
            const qtyLabel = document.getElementById('qty_label');
            if (type === 'retail') {
                qtyLabel.textContent = 'Количество (кг):';
            } else {
                qtyLabel.textContent = 'Количество (мешков):';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const selected = document.querySelector('input[name="purchase_type"]:checked');
            if(selected) updatePriceDisplay(selected.value);
        });
    </script>
@endsection