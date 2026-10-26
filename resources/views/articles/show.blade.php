@extends('layouts.app')
@section('title', $article->title)
@section('content')
    <article class="max-w-3xl mx-auto bg-white rounded-3xl shadow-sm p-8 border border-[#F9F0E0]">
        <h1 class="text-4xl font-semibold text-[#2D3528] mb-2 border-l-2 border-[#A2AB73] pl-4">{{ $article->title }}</h1>
        <p class="text-sm text-gray-500 mb-8">Автор: {{ $article->author->name }} · {{ $article->created_at->format('d.m.Y') }}</p>

        <div class="space-y-6">
            @foreach($article->blocks as $block)
                @if($block->type === 'header')
                    <h2 class="text-2xl font-semibold text-[#2D3528] border-l-2 border-[#A2AB73] pl-4">{{ $block->content }}</h2>
                @elseif($block->type === 'text')
                    <p class="text-gray-700 leading-relaxed text-lg bg-[#F9F0E0]/30 p-4 rounded-xl">{{ $block->content }}</p>
                @elseif($block->type === 'image')
                    <div class="bg-[#F9F0E0] p-4 rounded-2xl">
                        <img src="{{ asset('storage/' . $block->file_path) }}" class="w-full rounded-xl shadow-lg">
                    </div>
                @endif
            @endforeach
        </div>

        <a href="{{ route('articles.index') }}" class="inline-block mt-8 bg-[#2D3528] text-white px-6 py-3 rounded-xl btn-animated font-bold">
            ← К списку статей
        </a>
    </article>
@endsection
