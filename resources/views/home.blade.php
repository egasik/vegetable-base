@extends('layouts.app')
@section('title', 'Главная')
@section('content')
    <section class="text-center py-12 bg-white rounded-3xl shadow-xl mb-12 border-4 border-[#0D7D4C]">
        <h1 class="text-5xl font-black mb-4 text-[#0D7D4C]">Свежесть с грядки <span class="text-[#CAF204]">каждый день!</span></h1>
        <p class="text-xl text-[#422168] mb-8 max-w-2xl mx-auto">Оптовая и розничная продажа овощей и фруктов напрямую от производителей.</p>
        <a href="{{ route('catalog') }}" class="inline-block bg-[#CAF204] text-[#422168] font-bold py-4 px-8 rounded-full text-lg btn-animated pulse-hover">
            Перейти в каталог →
        </a>
    </section>

    <h2 class="text-3xl font-bold mb-8 text-[#422168] border-l-8 border-[#00F3B5] pl-4">Новые поступления</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($products as $product)
            <div class="bg-white p-6 rounded-2xl shadow-md card-hover border-2 border-transparent">
                                <div class="h-40 bg-[#E8FC8C] rounded-xl mb-4 flex items-center justify-center overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                    @else
                        <span class="text-6xl">🥕</span>
                    @endif
                </div>
                <h3 class="font-bold text-xl mb-2 text-[#422168]">{{ $product->name }}</h3>
                <div class="mb-4">
    @if($product->is_retail)
        <p class="text-[#0D7D4C] font-black text-xl">{{ $product->retail_price }} ₽ / кг</p>
    @elseif($product->is_wholesale)
        <p class="text-[#422168] font-bold text-sm bg-[#E8FC8C] inline-block px-2 py-1 rounded">
            Опт: {{ $product->wholesale_price }} ₽
        </p>
    @endif
</div>
                <a href="{{ route('product.show', $product) }}" class="block text-center bg-[#422168] text-white py-2 rounded-lg btn-animated">
                    Подробнее
                </a>
            </div>
        @endforeach
    </div>
@endsection