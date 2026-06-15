@extends('layouts.app')
@section('title', $product->name)
@section('content')
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl mx-auto overflow-hidden grid md:grid-cols-2 border-4 border-[#E8FC8C]">
        <div class="h-96 bg-[#E8FC8C] flex items-center justify-center text-9xl">
            🥦
        </div>
        <div class="p-8 flex flex-col justify-between">
            <div>
                <span class="inline-block bg-[#00F3B5] text-[#422168] px-3 py-1 rounded-full text-xs font-bold mb-4">
                    {{ $product->category->name }}
                </span>
                <h1 class="text-4xl font-black mb-4 text-[#422168]">{{ $product->name }}</h1>
                <p class="text-gray-700 mb-6 leading-relaxed">{{ $product->description }}</p>
                <div class="bg-[#E8FC8C] p-4 rounded-xl mb-6">
                    <p class="text-sm text-[#0D7D4C] font-bold">Цена за кг</p>
                    <p class="text-4xl font-black text-[#422168]">{{ $product->price }} ₽</p>
                </div>
            </div>
            
            <form action="{{ route('orders.store') }}" method="POST" class="flex items-center gap-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="flex items-center border-2 border-[#0D7D4C] rounded-lg overflow-hidden">
                    <button type="button" onclick="document.getElementById('qty').stepDown()" class="px-3 py-2 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">-</button>
                    <input type="number" id="qty" name="quantity" value="1" min="1" class="w-12 text-center focus:outline-none font-bold">
                    <button type="button" onclick="document.getElementById('qty').stepUp()" class="px-3 py-2 bg-[#E8FC8C] hover:bg-[#CAF204] transition-colors font-bold">+</button>
                </div>
                <button type="submit" class="flex-grow bg-[#0D7D4C] text-white font-bold py-3 rounded-lg btn-animated pulse-hover hover:bg-[#422168]">
                    🛒 В корзину
                </button>
            </form>
        </div>
    </div>
@endsection