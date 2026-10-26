@extends('admin.layouts.app')
@section('title', 'Редактировать товар')
@section('content')
    <h1 class="text-4xl font-semibold text-[#2D3528] mb-6">✏️ Редактирование: {{ $product->name }}</h1>
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-2xl shadow-sm max-w-2xl border border-[#F9F0E0]">
        @csrf @method('PUT')
        @include('admin.products._form')
        <div class="flex gap-4 mt-6">
            <button type="submit" class="flex-1 bg-[#CC3A63] text-white font-bold py-3 rounded-xl btn-animated"> Сохранить изменения</button>
            <a href="{{ route('admin.products.index') }}" class="flex-1 bg-gray-300 text-[#2D3528] font-bold py-3 rounded-xl btn-animated text-center">Отмена</a>
        </div>
    </form>
@endsection
