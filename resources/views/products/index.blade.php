@extends('layouts.app')
@section('title', 'Каталог')
@section('content')
    <h1 class="text-4xl font-black mb-8 text-[#422168]"> Каталог товаров</h1>
    
    {{-- Фильтры --}}
    <form action="{{ route('catalog') }}" method="GET" class="bg-white p-6 rounded-2xl shadow-lg mb-8 border-2 border-[#00F3B5] grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Поиск</label>
            <input type="text" name="search" placeholder="Название..." value="{{ request('search') }}" 
                   class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none transition-colors">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Категория</label>
            <select name="category_id" class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none transition-colors">
                <option value="">Все</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <div>
                <label class="block text-xs mb-1">От ₽</label>
                <input type="number" name="min_price" value="{{ request('min_price') }}" class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs mb-1">До ₽</label>
                <input type="number" name="max_price" value="{{ request('max_price') }}" class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
            </div>
        </div>
        <button type="submit" class="bg-[#0D7D4C] text-white font-bold py-2 rounded-lg btn-animated hover:bg-[#422168]">
            🔍 Найти
        </button>
    </form>

    {{-- Сетка товаров --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($products as $product)
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-4 border-[#E8FC8C] card-hover flex flex-col">
                
                {{-- Фото товара --}}
                <a href="{{ route('product.show', $product) }}" class="block h-48 bg-[#E8FC8C] flex items-center justify-center overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    @else
                        <span class="text-7xl">🍅</span>
                    @endif
                </a>

                {{-- Информация о товаре --}}
                <div class="p-5 flex flex-col flex-1">
                    {{-- Категория --}}
                    @if($product->category)
                        <span class="text-xs text-[#0D7D4C] font-bold uppercase tracking-wider mb-1">{{ $product->category->name }}</span>
                    @endif

                    <a href="{{ route('product.show', $product) }}" class="block">
                        <h3 class="text-xl font-black text-[#422168] mb-2 hover:text-[#0D7D4C] transition-colors">{{ $product->name }}</h3>
                    </a>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2 flex-1">{{ Str::limit($product->description, 80) }}</p>

                    {{-- Цены --}}
                    <div class="flex flex-col gap-1 mb-4">
                        @if($product->is_retail)
                            <span class="text-2xl font-black text-[#0D7D4C]">
                                {{ $product->retail_price }} ₽ <span class="text-sm font-normal text-gray-500">/ кг</span>
                            </span>
                        @endif
                        @if($product->is_wholesale)
                            <span class="text-xs font-bold text-[#422168] bg-[#E8FC8C] px-2 py-1 rounded inline-block w-max">
                                 Опт: {{ $product->wholesale_price }} ₽ за {{ $product->wholesale_unit_kg }} кг
                            </span>
                        @endif
                    </div>

                    {{-- Кнопки добавления в корзину --}}
@auth
    <div class="mt-auto space-y-2">
        @if($product->is_retail && $product->is_wholesale)
            {{-- Оба типа доступны — две кнопки --}}
            <div class="grid grid-cols-2 gap-2">
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_type" value="retail">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="w-full bg-[#00F3B5] text-[#422168] font-bold py-2 rounded-lg btn-animated hover:bg-[#0D7D4C] hover:text-white transition-colors text-sm">
                        В розницу
                    </button>
                </form>
                <form action="{{ route('cart.add', $product) }}" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_type" value="wholesale">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="w-full bg-[#422168] text-[#CAF204] font-bold py-2 rounded-lg btn-animated hover:bg-[#0D7D4C] transition-colors text-sm">
                        В опт
                    </button>
                </form>
            </div>
        @elseif($product->is_retail)
            {{-- Только розница --}}
            <form action="{{ route('cart.add', $product) }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_type" value="retail">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="w-full bg-[#CAF204] text-[#422168] font-bold py-3 rounded-xl btn-animated hover:bg-[#00F3B5] transition-colors">
                    В корзину
                </button>
            </form>
        @else
            {{-- Только опт --}}
            <form action="{{ route('cart.add', $product) }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_type" value="wholesale">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="w-full bg-[#422168] text-[#CAF204] font-bold py-3 rounded-xl btn-animated hover:bg-[#0D7D4C] transition-colors">
                    В опт
                </button>
            </form>
        @endif
    </div>
@else
    <a href="{{ route('login') }}" class="block w-full bg-gray-200 text-[#422168] font-bold py-3 rounded-xl text-center btn-animated mt-auto">
        Войдите, чтобы купить
    </a>
@endauth
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-4 border-[#E8FC8C]">
                <p class="text-6xl mb-4">🔍</p>
                <p class="text-xl text-[#422168] font-bold">Товары не найдены</p>
                <p class="text-gray-500 mt-2">Попробуйте изменить фильтры поиска</p>
            </div>
        @endforelse
    </div>

    {{-- Пагинация --}}
    <div class="mt-8">{{ $products->links() }}</div>
@endsection