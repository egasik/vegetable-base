@extends('admin.layouts.app')
@section('title', 'Товары')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-black text-[#422168]">🥕 Управление товарами</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-[#CAF204] text-[#422168] px-6 py-3 rounded-xl font-bold btn-animated">
            + Добавить товар
        </a>
    </div>

    <form action="{{ route('admin.products.index') }}" method="GET" class="bg-white p-4 rounded-2xl shadow mb-6 flex gap-4">
        <input type="text" name="search" placeholder="Поиск..." value="{{ request('search') }}" class="flex-1 border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
        <select name="category_id" class="border-2 border-[#E8FC8C] p-2 rounded-lg">
            <option value="">Все категории</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-[#0D7D4C] text-white px-6 py-2 rounded-lg btn-animated">Найти</button>
    </form>

    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-[#422168] text-[#CAF204]">
                <tr>
                    <th class="p-4 text-left">Название</th>
                    <th class="p-4 text-left">Категория</th>
                    <th class="p-4 text-left">Цена</th>
                    <th class="p-4 text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b border-[#E8FC8C] card-hover">
                        <td class="p-4 font-bold">{{ $product->name }}</td>
                        <td class="p-4">
                            <span class="bg-[#00F3B5] text-[#422168] px-3 py-1 rounded-full text-xs font-bold">
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="p-4 text-[#0D7D4C] font-black">{{ $product->price }} ₽</td>
                        <td class="p-4 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="bg-[#CAF204] text-[#422168] px-4 py-2 rounded-lg btn-animated text-sm font-bold">✏️ Изменить</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Удалить товар?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg btn-animated text-sm font-bold">🗑 Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-500">Товары не найдены</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection