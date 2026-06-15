@extends('layouts.app')
@section('title', 'Справочник садовода')
@section('content')
    <section class="text-center py-12 bg-white rounded-3xl shadow-xl mb-12 border-4 border-[#0D7D4C]">
        <h1 class="text-5xl font-black mb-4 text-[#0D7D4C]">📖 Справочник садовода</h1>
        <p class="text-xl text-[#422168]">Полезные статьи о выращивании и уходе за овощными культурами</p>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($articles as $article)
            <a href="{{ route('articles.show', $article) }}" class="bg-white rounded-2xl shadow-lg card-hover overflow-hidden block border-b-8 border-[#CAF204]">
                @php $cover = $article->blocks->where('type', 'image')->first(); @endphp
                <div class="h-48 bg-[#E8FC8C] flex items-center justify-center text-7xl">
                    @if($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" class="w-full h-full object-cover">
                    @else
                        🌱
                    @endif
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-black text-[#422168] mb-2">{{ $article->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $article->created_at->format('d.m.Y') }} · Блоков: {{ $article->blocks->count() }}</p>
                </div>
            </a>
        @empty
            <div class="col-span-3 bg-white p-8 rounded-2xl text-center text-gray-500">Статей пока нет</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $articles->links() }}</div>
@endsection