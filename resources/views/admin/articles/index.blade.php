@extends('admin.layouts.app')
@section('title', 'Справочник садовода')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-4xl font-semibold text-[#2D3528]">📖 Справочник садовода</h1>
        <a href="{{ route('admin.articles.create') }}" class="bg-[#A2AB73] text-[#2D3528] px-6 py-3 rounded-xl font-bold btn-animated">
            + Написать статью
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($articles as $article)
            <div class="bg-white p-6 rounded-2xl shadow-lg card-hover border-l-2 {{ $article->is_published ? 'border-[#CC3A63]' : 'border-gray-400' }}">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-semibold text-[#2D3528]">{{ $article->title }}</h3>
                    <span class="text-xs px-2 py-1 rounded-full font-bold {{ $article->is_published ? 'bg-[#F9F0E0] text-[#2D3528]' : 'bg-gray-200 text-gray-600' }}">
                        {{ $article->is_published ? 'Опубликовано' : 'Черновик' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Автор: {{ $article->author->name }} · Блоков: {{ $article->blocks->count() }}</p>
                <div class="flex gap-2">
                    <a href="{{ route('articles.show', $article) }}" target="_blank" class="flex-1 bg-[#F9F0E0] text-[#2D3528] py-2 rounded-lg btn-animated text-center text-sm font-bold">👁 Просмотр</a>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="flex-1 bg-[#A2AB73] text-[#2D3528] py-2 rounded-lg btn-animated text-center text-sm font-bold">✏️ Изменить</a>
                    <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="flex-1" onsubmit="return confirm('Удалить статью?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 text-white py-2 rounded-lg btn-animated text-sm font-bold">🗑 Удалить</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-white p-8 rounded-2xl text-center text-gray-500">Статей пока нет. Напишите первую!</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
