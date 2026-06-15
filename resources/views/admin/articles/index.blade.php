@extends('admin.layouts.app')
@section('title', 'Справочник садовода')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-black text-[#422168]">📖 Справочник садовода</h1>
        <a href="{{ route('admin.articles.create') }}" class="bg-[#CAF204] text-[#422168] px-6 py-3 rounded-xl font-bold btn-animated">
            + Написать статью
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($articles as $article)
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover border-l-8 {{ $article->is_published ? 'border-[#0D7D4C]' : 'border-gray-400' }}">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-black text-[#422168]">{{ $article->title }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full font-bold {{ $article->is_published ? 'bg-[#00F3B5] text-[#422168]' : 'bg-gray-200 text-gray-600' }}">
                        {{ $article->is_published ? 'Опубликовано' : 'Черновик' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Автор: {{ $article->author->name }} · Блоков: {{ $article->blocks->count() }}</p>
                <div class="flex gap-2">
                    <a href="{{ route('articles.show', $article) }}" target="_blank" class="flex-1 bg-[#E8FC8C] text-[#422168] py-2 rounded-lg btn-animated text-center text-sm font-bold">👁 Просмотр</a>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="flex-1 bg-[#CAF204] text-[#422168] py-2 rounded-lg btn-animated text-center text-sm font-bold">✏️ Изменить</a>
                    <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="flex-1" onsubmit="return confirm('Удалить статью?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg btn-animated text-sm font-bold">🗑</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-white p-8 rounded-2xl text-center text-gray-500">Статей пока нет. Напишите первую!</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $articles->links() }}</div>
@endsection