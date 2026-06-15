@extends('admin.layouts.app')
@section('title', 'Редактировать категорию')
@section('content')
    <h1 class="text-4xl font-black text-[#422168] mb-6">✏️ Категория: {{ $category->name }}</h1>
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="bg-white p-8 rounded-2xl shadow-xl max-w-2xl border-4 border-[#E8FC8C]">
        @csrf @method('PUT')
        @include('admin.categories._form')
        <div class="flex gap-4 mt-6">
            <button type="submit" class="flex-1 bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated">💾 Сохранить</button>
            <a href="{{ route('admin.categories.index') }}" class="flex-1 bg-gray-300 text-[#422168] font-bold py-3 rounded-xl btn-animated text-center">Отмена</a>
        </div>
    </form>
@endsection