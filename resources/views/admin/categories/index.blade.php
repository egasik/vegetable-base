@extends('admin.layouts.app')
@section('title', 'Категории')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-black text-[#422168]"> Управление категориями</h1>
        <a href="{{ route('admin.categories.create') }}" class="bg-[#CAF204] text-[#422168] px-6 py-3 rounded-xl font-bold btn-animated">
            + Добавить категорию
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-8 border-red-500 text-red-700 p-4 rounded-xl mb-6">⚠️ {{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover border-b-8 border-[#00F3B5]">
                <h3 class="text-2xl font-black text-[#422168] mb-2">{{ $category->name }}</h3>
                <p class="text-gray-600 text-sm mb-4">{{ Str::limit($category->description, 80) ?? 'Без описания' }}</p>
                <p class="text-[#0D7D4C] font-bold mb-4">Товаров: <span class="text-2xl">{{ $category->products_count }}</span></p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="flex-1 bg-[#CAF204] text-[#422168] py-2 rounded-lg btn-animated text-center font-bold">✏️</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="flex-1" onsubmit="return confirm('Удалить категорию?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg btn-animated font-bold">🗑</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white p-8 rounded-2xl text-center text-gray-500">Категорий пока нет</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $categories->links() }}</div>
@endsection