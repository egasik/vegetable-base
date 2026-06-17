@extends('layouts.app')
@section('title', 'Каталог')
@section('content')
    <h1 class="text-4xl font-black mb-8 text-[#422168]">Каталог товаров</h1>
    
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
            Найти 🔍
        </button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($products as $product)
            <div class="bg-white rounded-2xl shadow-md overflow-hidden card-hover border-b-8 border-[#CAF204]">
                                <div class="h-48 bg-[#E8FC8C] flex items-center justify-center overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    @else
                        <span class="text-7xl">🍅</span>
                    @endif
                </div>
                <div class="p-6">
                    <span class="text-xs font-bold text-[#00F3B5] uppercase tracking-wider">{{ $product->category->name }}</span>
                    <h3 class="text-xl font-bold mt-1 mb-2 text-[#422168]">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ Str::limit($product->description, 60) }}</p>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col gap-1 mb-4">
    @if($product->is_retail)
        <span class="text-2xl font-black text-[#0D7D4C]">
            {{ $product->retail_price }} ₽ <span class="text-sm font-normal text-gray-500">/ кг</span>
        </span>
    @endif
    
    @if($product->is_wholesale)
        <span class="text-xs font-bold text-[#422168] bg-[#E8FC8C] px-2 py-1 rounded inline-block w-max">
            📦 Опт: {{ $product->wholesale_price }} ₽ за {{ $product->wholesale_unit_kg }} кг
        </span>
    @endif
</div>
                        <a href="{{ route('product.show', $product) }}" class="bg-[#422168] text-white px-4 py-2 rounded-lg btn-animated text-sm">
                            Купить
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 bg-white rounded-2xl">
                <p class="text-xl text-gray-500">Товары не найдены. Попробуйте изменить фильтры.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
@endsection