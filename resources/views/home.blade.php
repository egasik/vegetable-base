@extends('layouts.app')
@section('title', 'Главная')
@section('content')
    <h1 class="text-3xl font-bold mb-6">Добро пожаловать на Овощную базу!</h1>
    <h2 class="text-2xl mb-4">Новые поступления</h2>
    <div class="grid grid-cols-4 gap-4">
        @foreach($products as $product)
            <div class="bg-white p-4 rounded shadow">
                <h3 class="font-bold">{{ $product->name }}</h3>
                <p class="text-green-600">{{ $product->price }} руб.</p>
                <a href="{{ route('product.show', $product) }}" class="text-blue-500">Подробнее</a>
            </div>
        @endforeach
    </div>
@endsection